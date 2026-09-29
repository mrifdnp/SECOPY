<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RolePrivilege;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RolePrivilegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('role_privileges')->delete();

        $data = array(
            array(
                'id'  => '018da67e-0dc7-7a42-b238-d72eefa621e6',
                'roles_id' => '018da67e-0dc7-70ce-b1a9-d888ed780384',
                'modules_id' => '018da67c-047d-71e8-b268-7e59d909db68',
                'privileges' => '{"access":1,"create":1,"read":1,"update":1,"delete":1,"trash":1}',
                'remark' => 'super',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67e-0dc7-7810-b760-c324f01045ad',
                'roles_id' => '018da67e-0dc7-70ce-b1a9-d888ed780384',
                'modules_id' => '018da67b-4016-7183-b675-a4ee823a1597',
                'privileges' => '{"access":1,"create":1,"read":1,"update":1,"delete":1,"trash":1}',
                'remark' => 'super',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67e-0dc7-7f98-9fd9-f32dacb9c06c',
                'roles_id' => '018da67e-0dc7-70ce-b1a9-d888ed780384',
                'modules_id' => 'f81c694f-8f78-4d6d-8539-9d952859e6bf',
                'privileges' => '{"access":1,"create":1,"read":1,"update":1,"delete":1,"trash":1}',
                'remark' => 'super',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67e-0dc7-7b19-bb80-ae7ff16c48f5',
                'roles_id' => '018da67e-0dc7-70ce-b1a9-d888ed780384',
                'modules_id' => '018da67c-449f-7434-9cd4-c50a3ff411b8',
                'privileges' => '{"access":1,"create":1,"read":1,"update":1,"delete":1,"trash":1}',
                'remark' => 'super',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67e-0dc7-78fd-a47a-afe30b8686fd',
                'roles_id' => '018da67e-0dc7-70ce-b1a9-d888ed780384',
                'modules_id' => '018da679-f7e5-789a-a3ab-08861f33b44e',
                'privileges' => '{"access":1,"create":1,"read":1,"update":1,"delete":1,"trash":1}',
                'remark' => 'super',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67e-0dc7-7fbf-a939-3617b4ecb9a5',
                'roles_id' => '018da67e-0dc7-70ce-b1a9-d888ed780384',
                'modules_id' => '018da67c-c292-733e-bfff-2aa818a17c1d',
                'privileges' => '{"access":1,"create":1,"read":1,"update":1,"delete":1,"trash":1}',
                'remark' => 'super',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67e-0dc7-786e-9979-4807127efe42',
                'roles_id' => '018da67e-0dc7-70ce-b1a9-d888ed780384',
                'modules_id' => '018da67e-0dc7-7cf2-831f-29a78f8016e3',
                'privileges' => '{"access":1,"create":1,"read":1,"update":1,"delete":1,"trash":1}',
                'remark' => 'super',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67e-0dc7-785f-af69-5d87721d0667',
                'roles_id' => '018da67e-0dc7-70ce-b1a9-d888ed780384',
                'modules_id' => '018da67b-8f64-7203-b7d5-91150dfea659',
                'privileges' => '{"access":1,"create":1,"read":1,"update":1,"delete":1,"trash":1}',
                'remark' => 'super',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67e-0dc7-700c-b4b8-b6a93192fa12',
                'roles_id' => '018da67e-0dc7-70ce-b1a9-d888ed780384',
                'modules_id' => '018da67c-84fc-753f-af8b-d35036231f13',
                'privileges' => '{"access":1,"create":1,"read":1,"update":1,"delete":1,"trash":1}',
                'remark' => 'super',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
        );

        DB::table('role_privileges')->insert($data);
    }
}
