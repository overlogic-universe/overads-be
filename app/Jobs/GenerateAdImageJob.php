<?php

namespace App\Jobs;

use App\Models\AdGeneration;
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
    public function __construct(public AdGeneration $generation)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            //code...
            Log::info('[AdImageJob] Started', [
                'generation_id' => $this->generation->id,
            ]);

            $this->generation->update(['status' => 'processing']);

            Log::info('[AdImageJob] Sending prompt to HF', [
                'prompt' => $this->generation->prompt,
            ]);

            $res = Http::post(
                'https://llamameta-fake-flux-pro-unlimited.hf.space/gradio_api/call/generate_image',
                ['data' => [$this->generation->prompt, 'turbo']]
            );

            Log::info('[AdImageJob] Generate request sent', [
                'event_id' => $res->json('event_id'),
            ]);

            $eventId = $res->json('event_id');

            Log::info('[AdImageJob] Waiting for result…');

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

            if (!$jsonData || !isset($jsonData[0]['url'])) {
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
                'result_media' => $path,
            ]);

            Log::info('[AdImageJob] Finished successfully', [
                'generation_id' => $this->generation->id,
                'path' => $path,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            $this->generation->update(['status' => 'fail']);
        }
    }
}
