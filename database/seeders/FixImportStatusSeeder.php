<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixImportStatusSeeder extends Seeder
{
    public function run()
    {
        $productModule = Module::where('url', 'product')->first();
        $newModule = Module::where('url', 'import-status')->first();

        if ($productModule && $newModule) {
            $privileges = DB::table('role_privileges')->where('modules_id', $productModule->id)->get();
            foreach ($privileges as $p) {
                // Delete existing first to avoid duplicate if rerunning without constraints
                DB::table('role_privileges')
                    ->where('roles_id', $p->roles_id)
                    ->where('modules_id', $newModule->id)
                    ->delete();
                    
                DB::table('role_privileges')->insert([
                    'id' => (string) Str::uuid(),
                    'roles_id' => $p->roles_id,
                    'modules_id' => $newModule->id,
                    'privileges' => $p->privileges,
                    'remark' => ''
                ]);
            }
        }
    }
}
