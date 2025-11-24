<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function getApiKey(Request $req){
        $this->authorizeAdmin($req->user());
        $setting = Setting::where('key','n8n_webhook_secret')->first();
        return response()->json(['value'=>$setting?->value]);
    }
    public function updateApiKey(Request $req){
        $this->authorizeAdmin($req->user());
        $req->validate(['value'=>'required|string']);
        $s = Setting::updateOrCreate(['key'=>'n8n_webhook_secret'], ['value'=>$req->value, 'user_id'=>$req->user()->id]);
        return response()->json(['ok'=>true]);
    }
    protected function authorizeAdmin($user){
        if(!$user || !$user->is_admin) abort(403,'Forbidden');
    }
}
