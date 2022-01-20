<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = Admin::create([
            'name' => 'admin',
            'email' => 'admin@ph.com',
            'phone' => 380679938836,
        ]);
        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'api-admin']);

        $user->assignRole($role);
    }
}
