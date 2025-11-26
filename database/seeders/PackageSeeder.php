<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Package::create([
            'name' => 'Starter',
            'original_price' => 100000,
            'price' => 50000,
            'benefits' => [
                'Generate konten hingga 5 kali',
                '1 platform (Facebook atau Instagram)',
                'Template dasar (teks & gambar)',
                '+1 kredit gratis untuk pengguna baru',
            ],
        ]);

        Package::create([
            'name' => 'Business',
            'original_price' => 350000,
            'price' => 180000,
            'benefits' => [
                'Generate konten hingga 20 kali',
                'Integrasi penuh Facebook & Instagram',
                'Template kustom dengan variasi konten AI',
                'Penjadwalan otomatis (bisa kustomisasi)',
                'Analitik performa dasar',
                'Dukungan pelanggan premium',
                '+3 kredit gratis untuk pengguna baru',
            ],
        ]);

        Package::create([
            'name' => 'Pro',
            'original_price' => 200000,
            'price' => 100000,
            'benefits' => [
                'Generate konten hingga 10 kali',
                '2 platform (Facebook & Instagram)',
                'Template premium dengan gaya merek berbasis AI',
                'Penjadwalan otomatis unggahan konten',
                'Dukungan pelanggan prioritas',
                '+2 kredit gratis untuk pengguna baru',
            ],
        ]);
    }
}
