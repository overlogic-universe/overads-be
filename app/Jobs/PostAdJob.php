<?php

namespace App\Jobs;

use App\Models\AdSchedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class PostAdJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public AdSchedule $schedule)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->schedule->update(['status' => 'processing']);

        $ads = $this->schedule->ads;
        $media = $ads->generations()->latest()->first()->result_media;
        $url = Storage::url($media);

        // SIMULASI POST (ganti Meta API)
        $response = [
            'platform' => $this->schedule->platform,
            'media_url' => $url
        ];

        $this->schedule->update([
            'status' => 'posted',
            'platform_response' => $response
        ]);
    }
}
