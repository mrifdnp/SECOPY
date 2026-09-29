<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;
use Carbon\Carbon;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('settings')->delete();

        $data = array(
            array(
                'id'  => '018da67e-0dc7-7f24-a5fd-64c32ac16ad6',
                'key' => 'theme',
                'value'  => '{"data-layout":"vertical","data-layout-mode":"light","data-sidebar-visibility":"show","data-layout-width":"fluid","data-topbar":"light","data-sidebar-size":"lg","data-layout-style":"default","data-sidebar":"dark","data-sidebar-image":"none"}',
                'only_super'  => 1,
                'no_delete'  => 1,
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
                'id'  => '018da67e-0dc7-7e6e-9168-8555000de560',
                'key' => 'password_email',
                'value'  => 'p15t476q4jxwm5lA4rsx28lt22',
                'only_super'  => 1,
                'no_delete'  => 1,
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
                'id'  => '018da67e-0dc7-7c8d-ac4e-872a6a5ffada',
                'key' => 'admin_email',
                'value'  => 'noreply@project.indonesiait.com',
                'only_super'  => 1,
                'no_delete'  => 1,
                'created_by'  => 'support@indonesiait.com',
                'updated_by'  => 'support@indonesiait.com',
                'restored_by'  => 'support@indonesiait.com',
                'restored_at'  => '2020-07-12 22:11:16',
                'deleted_by'  => null,
                'deleted_at'  => null,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ),
        );

        DB::table('settings')->insert($data);
    }
}
