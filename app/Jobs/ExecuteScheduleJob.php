<?php

namespace App\Jobs;

use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExecuteScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $schedule;

    /**
     * Create a new job instance.
     */
    public function __construct($schedule)
    {
        $this->schedule = $schedule;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $s = Schedule::with('ad', 'user')->find($this->schedule->id);
        if (! $s) {
            return;
        }
        $s->status = 'running';
        $s->save();

        $payload = [
            'user' => ['id' => $s->user->id, 'full_name' => $s->user->full_name, 'business_name' => $s->user->business_name],
            'ad' => [
                'id' => $s->ad->id, 'name' => $s->ad->name, 'type' => $s->ad->type, 'description' => $s->ad->description,
                'theme' => $s->ad->theme, 'reference_media_url' => $s->ad->reference_media ? asset('storage/'.$s->ad->reference_media) : null,
            ],
            'schedule' => ['id' => $s->id, 'scheduled_at' => $s->scheduled_at->toISOString(), 'platforms' => $s->platforms],
        ];

        $webhook = config('services.n8n.webhook_url') ?? env('N8N_WEBHOOK_URL');
        $headers = [];
        if (config('services.n8n.api_key') ?? env('N8N_API_KEY')) {
            $headers['X-Api-Key'] = config('services.n8n.api_key') ?? env('N8N_API_KEY');
        }

        try {
            $response = Http::withHeaders($headers)->post($webhook, $payload);
            $s->status = $response->successful() ? 'done' : 'failed';
            $s->n8n_execution_id = $response->json('execution_id') ?? null;
            $s->response = ['status' => $response->status(), 'body' => $response->json()];
            $s->save();
        } catch (\Throwable $e) {
            Log::error('n8n call failed: '.$e->getMessage());
            $s->status = 'failed';
            $s->response = ['error' => $e->getMessage()];
            $s->save();
            $this->fail($e);
        }
    }

    public function failed(\Throwable $exception)
    {
        $s = Schedule::find($this->schedule->id);
        if ($s) {
            $s->status = 'failed';
            $s->response = ['exception' => $exception->getMessage()];
            $s->save();
        }
    }
}
