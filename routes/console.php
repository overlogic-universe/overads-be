<?php

use App\Jobs\PostAdJob;
use App\Models\AdSchedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::call(function () {
//     AdSchedule::where('status','pending')
//         ->where('scheduled_at','<=',now())
//         ->each(fn ($s) => dispatch(new PostAdJob($s)));
// })->everyMinute();
