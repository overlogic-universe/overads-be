<?php

namespace App\Http\Controllers;

use App\Models\Pricing;

class PricingController extends Controller
{
    public function index()
    {
        return response()->json(Pricing::all());
    }
}
