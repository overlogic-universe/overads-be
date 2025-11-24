<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdStoreRequest;
use App\Http\Resources\AdResource;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdController extends Controller
{
    public function index(Request $req){ return AdResource::collection($req->user()->ads()->paginate(15)); }
    public function store(AdStoreRequest $req){
        $data = $req->only(['name','type','description','theme','platforms']);
        $data['platforms'] = $req->platforms;
        if($req->hasFile('reference_media')){
            $path = $req->file('reference_media')->store('ads', 'public');
            $data['reference_media'] = $path;
        }
        $ad = $req->user()->ads()->create($data);
        return new AdResource($ad);
    }
    public function show(Request $req, Ad $ad){ $this->authorizeOwner($req->user(), $ad); return new AdResource($ad); }
    public function update(AdStoreRequest $req, Ad $ad){
        $this->authorizeOwner($req->user(), $ad);
        $data = $req->only(['name','type','description','theme','platforms']);
        if($req->hasFile('reference_media')){
            if($ad->reference_media) Storage::disk('public')->delete($ad->reference_media);
            $data['reference_media'] = $req->file('reference_media')->store('ads','public');
        }
        $ad->update($data);
        return new AdResource($ad);
    }
    public function destroy(Request $req, Ad $ad){
        $this->authorizeOwner($req->user(), $ad);
        if($ad->reference_media) Storage::disk('public')->delete($ad->reference_media);
        $ad->delete();
        return response()->json(['deleted'=>true]);
    }
    protected function authorizeOwner($user, Ad $ad){
        if($user->id !== $ad->user_id && !$user->is_admin) abort(403,'Forbidden');
    }
}
