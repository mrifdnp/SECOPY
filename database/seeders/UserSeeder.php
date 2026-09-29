<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->delete();

        $data = array(
            array(
                'id'  => '018da67e-0dc7-7978-86aa-d33f42c0b1f4',
                'name'  => 'Super',
                'email' => 'support@indonesiait.com',
                'password'  => '$2y$10$krEOk35RlFKiEjcP5YHuN.lcIjDzks3Si9iBTuAh20wTPXhtJcJ0.',
                'default_role'  => '018da67e-0dc7-70ce-b1a9-d888ed780384',
                'remember_token'  => 'Emobjsi3yMsOxPlpXPUKfYADxMzIDuuE8raVjQUaaNaIcdKHsnzukTMFJWsR',
                'only_super'  => '1',
                'no_delete'  => '1',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67e-0dc7-7e30-a4ef-ab6288ca6f57',
                'name'  => 'Admin',
                'email' => 'admin@indonesiait.com',
                'password'  => '$2y$10$u4OAbvMDW6cZvpJ8x0oKJ.gRyidyDeETR7TnzMejLd5XXsgymi4Cu',
                'default_role'  => '018da67e-0dc7-70ce-b1a9-d888ed780384',
                'remember_token'  => 'Emobjsi3yMsOxPlpXPUKfYADxMzIDuuE8raVjQUaaNaIcdKHsnzukTMFJWsR',
                'only_super'  => '0',
                'no_delete'  => '1',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
        );

        DB::table('users')->insert($data);
    }
}
