<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
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
        $user = User::create([
            'name' => 'admin',
            'email' => 'admin@ph.com',
            'phone' => 380679938836,
            'login' => 'admin',
        ]);
        $role = Role::create(['name' => 'Super Admin']);

        $user->assignRole($role);
    }
}
