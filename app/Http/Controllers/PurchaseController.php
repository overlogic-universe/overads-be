<?php

namespace App\Http\Controllers;

use App\Models\Pricing;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function purchase(Request $req){
        $req->validate(['pricing_id'=>'required|exists:pricings,id','payment_method'=>'nullable|string']);
        $pricing = Pricing::findOrFail($req->pricing_id);
        $user = $req->user();

        return DB::transaction(function() use($user,$pricing,$req){
            $purchase = Purchase::create([
                'user_id'=>$user->id,
                'pricing_id'=>$pricing->id,
                'payment_method'=>$req->payment_method ?? 'simulated',
                'amount_cents'=>$pricing->price_cents,
                'status'=>'completed',
            ]);
            $user->credits += $pricing->credits_given;
            $user->save();
            return response()->json(['purchase'=>$purchase,'user_credits'=>$user->credits],201);
        });
    }
}
