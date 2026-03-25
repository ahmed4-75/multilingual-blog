<?php

namespace Database\Seeders;

use App\Models\React;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reactos = ['like','smile','love','angry','laugh'];
        $data = collect($reactos)->map(fn($react) => ['name'=> $react])->toArray();
        React::upsert($data,['name']);
    }
}
