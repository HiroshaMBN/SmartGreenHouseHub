<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdmin extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
 
    User::create([
        'instance_id' => 1,
        'contact_id' => 1,
        'first_name' => 'super',
        'last_name' => 'admin',
        'email' => 'superadmin@greenhouse.lk',
        'is_active' =>1,
        'type'=>'super_admin',
        'password' => bcrypt('admin123'),
    ]);
    }
}
