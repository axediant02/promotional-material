<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            FreshProductionUserSeeder::class,
            FolderSeeder::class,
            MediaFileSeeder::class,
            AssignedClientSeeder::class,
            ClientRequestSeeder::class,
        ]);
    }
}
