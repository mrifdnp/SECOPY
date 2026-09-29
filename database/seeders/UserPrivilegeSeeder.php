<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserPrivilegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('user_privileges')->delete();

        $data = array(
            array(
                'id'  => '018da67e-0dc7-749f-9332-f5fed5f91b63',
                'users_id' => '018da67e-0dc7-7978-86aa-d33f42c0b1f4',
                'roles_id' => '018da67e-0dc7-70ce-b1a9-d888ed780384',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
        );

        DB::table('user_privileges')->insert($data);
    }
}
