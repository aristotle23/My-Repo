<?php

namespace Database\Seeders;

use App\Models\AccessConfig;
use App\Models\Provider;
use App\Models\User;
use App\ThirdParty\IdentityProviders\Mono;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        /* Provider::create([
            "name" => "mono",
            "class_path" => Mono::class,
            "is_active" => 1,
        ]);
        AccessConfig::create([
            "access_code" => 1234567890,
            "is_active" => 1,
        ]); */
    }
}
