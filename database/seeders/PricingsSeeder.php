<?php

namespace Database\Seeders;

use App\Models\Pricing;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PricingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['slug'=>'starter','name'=>'Starter','price_cents'=>1000,'credits_given'=>100,'billing_period_days'=>30,'description'=>'Starter pack'],
            ['slug'=>'business','name'=>'Business','price_cents'=>10000,'credits_given'=>1000,'billing_period_days'=>30,'description'=>'For growing businesses'],
            ['slug'=>'pro','name'=>'Pro','price_cents'=>40000,'credits_given'=>5000,'billing_period_days'=>30,'description'=>'Best for agencies'],
        ];
        foreach($data as $p) Pricing::updateOrCreate(['slug'=>$p['slug']], $p);
    }
}
