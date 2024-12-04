<?php

namespace Database\Seeders;

use App\Models\Classification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Classification::firstOrCreate([
            'code' => 'STAFF'
        ], [
            'type' => 'Staff',
            'description' => 'Jenis surat yang berkaitan dengan staff'
        ]);
    }
}    