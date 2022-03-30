<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;


class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("SET foreign_key_checks=0");
        Permission::truncate();
        DB::statement("SET foreign_key_checks=1");

        Permission::create(['name' => 'Statistic', 'guard_name' => 'api-admin']);

        Permission::create(['name' => 'Roles', 'guard_name' => 'api-admin']);

        Permission::create(['name' => 'Administrators', 'guard_name' => 'api-admin']);

        Permission::create(['name' => 'Moderation', 'guard_name' => 'api-admin']);

        Permission::create(['name' => 'Notifications', 'guard_name' => 'api-admin']);

        Permission::create(['name' => 'Users', 'guard_name' => 'api-admin']);
    }
}
