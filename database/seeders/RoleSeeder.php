<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->delete();

        $data = array(
            array(
                'id'  => '018da67e-0dc7-70ce-b1a9-d888ed780384',
                'name'  => 'super',
                'description' => 'For Developer Only',
                'guard_name'  => 'web',
                'modules_schema' => '[{"id":"f81c694f-8f78-4d6d-8539-9d952859e6bf"},{"id":"018da67c-047d-71e8-b268-7e59d909db68","children":[{"id":"018da67b-4016-7183-b675-a4ee823a1597","children":[{"id":"018da67c-449f-7434-9cd4-c50a3ff411b8"},{"id":"018da67c-84fc-753f-af8b-d35036231f13"}]},{"id":"018da679-f7e5-789a-a3ab-08861f33b44e"},{"id":"018da67e-0dc7-7cf2-831f-29a78f8016e3"}]},{"id":"018da67c-c292-733e-bfff-2aa818a17c1d"},{"id":"018da67b-8f64-7203-b7d5-91150dfea659"}]',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67e-0dc7-742b-886a-270afb6fe3b7',
                'name'  => 'admin',
                'description' => 'For Administrator',
                'guard_name'  => 'web',
                'modules_schema' => '[{"id":"f81c694f-8f78-4d6d-8539-9d952859e6bf"},{"id":"018da67c-047d-71e8-b268-7e59d909db68","children":[{"id":"018da67b-4016-7183-b675-a4ee823a1597","children":[{"id":"018da67c-449f-7434-9cd4-c50a3ff411b8"},{"id":"018da67c-84fc-753f-af8b-d35036231f13"}]},{"id":"018da679-f7e5-789a-a3ab-08861f33b44e"},{"id":"018da67e-0dc7-7cf2-831f-29a78f8016e3"}]},{"id":"018da67c-c292-733e-bfff-2aa818a17c1d"},{"id":"018da67b-8f64-7203-b7d5-91150dfea659"}]',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67e-0dc7-75ba-9c71-d3cf1c1f8822',
                'name'  => 'operator',
                'description' => 'For Operator',
                'guard_name'  => 'web',
                'modules_schema' => '[{"id":"018da67b-4016-7183-b675-a4ee823a1597"}]',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
        );

        DB::table('roles')->insert($data);
    }
}
