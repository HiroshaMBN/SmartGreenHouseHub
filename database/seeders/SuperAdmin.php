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
        // User::create([

        //     'name' => 'Hardik',

        //     'email' => 'admin@gmail.com',

        //     'password' => bcrypt('123456'),

        // ]);
    User::create([
        'instance_id' => 1,
        'contact_id' => 1,
        'first_name' => 'super',
        'last_name' => 'admin',
        'email' => 'superadmin@gmail.com',
        'password' => bcrypt('admin123'),
    ]);
    }
}
