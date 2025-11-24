<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScheduleCreateRequest;
use App\Jobs\ExecuteScheduleJob;
use App\Models\Ad;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ScheduleController extends Controller
{
    public function index(Request $req){
        if($req->user()->is_admin) return Schedule::paginate(20);
        return $req->user()->schedules()->paginate(20);
    }

    public function store(ScheduleCreateRequest $req){
        $ad = Ad::findOrFail($req->ad_id);
        if($ad->user_id !== $req->user()->id && !$req->user()->is_admin) abort(403);

        $dt = Carbon::createFromFormat('Y-m-d H:i', $req->date.' '.$req->time, $req->timezone);
        $utc = $dt->setTimezone('UTC');

        $schedule = Schedule::create([
            'user_id'=>$req->user()->id,
            'ad_id'=>$ad->id,
            'scheduled_at'=>$utc,
            'platforms'=>$req->platforms,
            'status'=>'pending',
            'timezone'=>$req->timezone
        ]);

        // dispatch job to run at scheduled time
        ExecuteScheduleJob::dispatch($schedule)->delay($utc);

        $schedule->status = 'queued';
        $schedule->save();

        return response()->json($schedule, 201);
    }

    public function show(Request $req, Schedule $schedule){
        if(!$req->user()->is_admin && $schedule->user_id !== $req->user()->id) abort(403);
        return response()->json($schedule);
    }
}
