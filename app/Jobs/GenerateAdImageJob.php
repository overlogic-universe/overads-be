<?php

namespace App\Jobs;

use App\Models\AdGeneration;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateAdImageJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1200;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public AdGeneration $generation,
        public int $userId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // code...
            $user = User::where('id', $this->userId)->first();
            if ($user->credit <= 0) {
                throw new \Exception('Kredit rendah');
            }
            Log::info('[AdImageJob] Started', [
                'generation_id' => $this->generation->id,
            ]);
            $setting = Setting::where('user_id', $this->userId)->first();
            Log::info('[AdImageJob] Setting', [
                'generation_id' => $this->generation,
            ]);
            if (! $setting) {
                throw new \Exception('Instagram setting not found');
            }
            $accessToken = $setting->apiKey;
            $igUserId = $setting->igId;

            $this->generation->update(['status' => 'processing']);

            Log::info('[AdImageJob] Sending prompt to HF', [
                'prompt' => $this->generation->prompt,
            ]);

            $res = Http::post(
                'https://llamameta-fake-flux-pro-unlimited.hf.space/gradio_api/call/generate_image',
                ['data' => [$this->generation->prompt, 'flux']]
            );

            Log::info('[AdImageJob] Generate request sent', [
                'event_id' => $res->json('event_id'),
            ]);

            $eventId = $res->json('event_id');

            Log::info('[AdImageJob] Waiting for result…');

            $geminiApiKey = config('services.xendit.webhook_secret');


            $prompt = "Create a short, engaging Instagram caption for this ad: \"{$this->generation->prompt}\". Include a clear call to action and up to 10 relevant hashtags. Output only the caption.";

            $geminiRes = Http::withHeaders([
                'x-goog-api-key' => $geminiApiKey,
                'Content-Type' => 'application/json',
            ])->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-3-pro-preview:generateContent',
                [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt,
                                ],
                            ],
                        ],
                    ],
                ]
            );

            $streamResponse = Http::timeout(1200)
                ->withHeaders([
                    'Accept' => 'text/event-stream',
                ])
                ->get("https://llamameta-fake-flux-pro-unlimited.hf.space/gradio_api/call/generate_image/{$eventId}");

            $body = $streamResponse->body();

            /**
             * 3️⃣ Parse SSE
             */
            $lines = explode("\n", $body);
            $jsonData = null;

            foreach ($lines as $line) {
                $line = trim($line);

                if (str_starts_with($line, 'data:')) {
                    $data = trim(substr($line, 5));

                    if ($data !== 'null' && str_starts_with($data, '[')) {
                        $jsonData = json_decode($data, true);
                        break;
                    }
                }
            }

            if (! $jsonData || ! isset($jsonData[0]['url'])) {
                Log::error('[AdImageJob] Failed to parse HF stream', [
                    'raw' => $body,
                ]);

                throw new \Exception('Invalid HF stream response');
            }

            $imageUrl = $jsonData[0]['url'];

            Log::info('[AdImageJob] Image URL extracted', [
                'url' => $imageUrl,
            ]);

            $imageBinary = Http::get($imageUrl)->body();

            $path = "ads/{$this->generation->id}.webp";

            Storage::disk('public')->put($path, $imageBinary);

            $this->generation->update([
                'status' => 'generated',
                'result_media' => $imageUrl,
                'caption' => 'caption',
            ]);

            // $imagePublicUrl = asset("storage/{$path}");

            // get caption dari gemini
            // upload ke instagram
            $mediaRes = Http::withToken($accessToken)->post(
                "https://graph.instagram.com/v24.0/{$igUserId}/media",
                [
                    'caption' => '',
                    'image_url' => $imageUrl,
                ]
            );
            sleep(10);

            if (! $mediaRes->successful()) {
                Log::error('[IG] Media create failed', [
                    'response' => $mediaRes->body(),
                ]);
                throw new \Exception('Failed to create IG media');
            }
            $creationId = $mediaRes->json('id');

            // 2️⃣ Publish media
            $publishRes = Http::withToken($accessToken)->post(
                "https://graph.instagram.com/v24.0/{$igUserId}/media_publish",
                [
                    'creation_id' => $creationId,
                ]
            );

            if (! $publishRes->successful()) {
                Log::error('[IG] Media publish failed', [
                    'response' => $publishRes->body(),
                ]);
                throw new \Exception('Failed to publish IG media');
            }

            $this->generation->update([
                'status' => 'uploaded',
                // 'result_media' => $path,
            ]);

            Log::info('[AdImageJob] Finished successfully', [
                'generation_id' => $this->generation->id,
                'path' => $path,
            ]);
            if ($user->credit >= 1) {
                $user->update([
                    'credit' => $user->credit - 1,
                ]);
            } else {
                throw new \Exception('Kredit rendah');
            }
        } catch (\Throwable $th) {
            Log::info('[AdImageJob] Failed', [
            ]);
            // throw $th;
            Log::error($th);

            $this->generation->update(['status' => 'failed']);
        }
    }
}
