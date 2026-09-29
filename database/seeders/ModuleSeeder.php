<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('modules')->delete();

        $data = array(
            array(
                'id'  => '018da679-f7e5-789a-a3ab-08861f33b44e',
                'name' => 'Module',
                'icon'  => 'ri-computer-fill',
                'url'  => 'module',
                'support'  => '{\"a\":\"1\",\"c\":\"1\",\"r\":\"1\",\"u\":\"1\",\"d\":\"1\",\"t\":\"1\"}',
                'only_super'  => '1',
                'no_delete'  => '1',
                'created_by'  => 'support@indonesiait.com',
                'updated_by'  => null,
                'restored_by'  => null,
                'restored_at'  => null,
                'deleted_by'  => null,
                'deleted_at'  => null,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67b-4016-7183-b675-a4ee823a1597',
                'name' => 'Users',
                'icon'  => 'ri-user-3-fill',
                'url'  => '#',
                'support'  => '{\"a\":\"1\",\"c\":null,\"r\":null,\"u\":null,\"d\":null,\"t\":null}',
                'only_super'  => '1',
                'no_delete'  => '1',
                'created_by'  => 'support@indonesiait.com',
                'updated_by'  => null,
                'restored_by'  => null,
                'restored_at'  => null,
                'deleted_by'  => null,
                'deleted_at'  => null,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67b-8f64-7203-b7d5-91150dfea659',
                'name' => 'Theme',
                'icon'  => 'ri-image-fill',
                'url'  => 'theme',
                'support'  => '{\"a\":\"1\",\"c\":\"1\",\"r\":\"1\",\"u\":\"1\",\"d\":\"1\",\"t\":\"1\"}',
                'only_super'  => '0',
                'no_delete'  => '1',
                'created_by'  => 'support@indonesiait.com',
                'updated_by'  => null,
                'restored_by'  => null,
                'restored_at'  => null,
                'deleted_by'  => null,
                'deleted_at'  => null,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67c-047d-71e8-b268-7e59d909db68',
                'name' => 'System',
                'icon'  => 'ri-settings-2-fill',
                'url'  => '#',
                'support'  => '{\"a\":\"1\",\"c\":null,\"r\":null,\"u\":null,\"d\":null,\"t\":null}',
                'only_super'  => '0',
                'no_delete'  => '1',
                'created_by'  => 'support@indonesiait.com',
                'updated_by'  => null,
                'restored_by'  => null,
                'restored_at'  => null,
                'deleted_by'  => null,
                'deleted_at'  => null,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67c-449f-7434-9cd4-c50a3ff411b8',
                'name' => 'User',
                'icon'  => 'ri-user-2-fill',
                'url'  => 'user',
                'support'  => '{\"a\":\"1\",\"c\":\"1\",\"r\":\"1\",\"u\":\"1\",\"d\":\"1\",\"t\":\"1\"}',
                'only_super'  => '0',
                'no_delete'  => '1',
                'created_by'  => 'support@indonesiait.com',
                'updated_by'  => null,
                'restored_by'  => null,
                'restored_at'  => null,
                'deleted_by'  => null,
                'deleted_at'  => null,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67c-84fc-753f-af8b-d35036231f13',
                'name' => 'Groups',
                'icon'  => 'ri-group-fill',
                'url'  => 'role',
                'support'  => '{\"a\":\"1\",\"c\":\"1\",\"r\":\"1\",\"u\":\"1\",\"d\":\"1\",\"t\":\"1\"}',
                'only_super'  => '0',
                'no_delete'  => '1',
                'created_by'  => 'support@indonesiait.com',
                'updated_by'  => null,
                'restored_by'  => null,
                'restored_at'  => null,
                'deleted_by'  => null,
                'deleted_at'  => null,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67c-c292-733e-bfff-2aa818a17c1d',
                'name' => 'Option',
                'icon'  => 'ri-code-fill',
                'url'  => 'setting',
                'support'  => '{\"a\":\"1\",\"c\":\"1\",\"r\":\"1\",\"u\":\"1\",\"d\":\"1\",\"t\":\"1\"}',
                'only_super'  => '0',
                'no_delete'  => '1',
                'created_by'  => 'support@indonesiait.com',
                'updated_by'  => null,
                'restored_by'  => null,
                'restored_at'  => null,
                'deleted_by'  => null,
                'deleted_at'  => null,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
            array(
                'id'  => '018da67e-0dc7-7cf2-831f-29a78f8016e3',
                'name' => 'Data Relation',
                'icon'  => 'ri-database-2-fill',
                'url'  => 'datarelation',
                'support'  => '{\"a\":\"1\",\"c\":null,\"r\":null,\"u\":null,\"d\":null,\"t\":null}',
                'only_super'  => '0',
                'no_delete'  => '1',
                'created_by'  => 'support@indonesiait.com',
                'updated_by'  => null,
                'restored_by'  => null,
                'restored_at'  => null,
                'deleted_by'  => null,
                'deleted_at'  => null,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
        );

        DB::table('modules')->insert($data);
    }
}
