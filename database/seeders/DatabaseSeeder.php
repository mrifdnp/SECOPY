<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SettingsSeeder::class);
        $this->call(ModuleSeeder::class);
        $this->call(RoleSeeder::class);        
        $this->call(UserSeeder::class);
        $this->call(UserPrivilegeSeeder::class);        
        $this->call(RolePrivilegeSeeder::class);
    }
}
