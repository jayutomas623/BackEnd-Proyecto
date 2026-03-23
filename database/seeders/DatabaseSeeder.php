<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        $this->call([
            RoleSeeder::class,
        ]);


        User::create([
            'name' => 'Admin Kusillu',
            'email' => 'admin@kusillu.com',
            'password' => Hash::make('password123'), 
            'role_id' => 1, 
            'estado' => true,
        ]);
    }
}