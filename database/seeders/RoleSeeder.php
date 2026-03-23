<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['nombre_rol' => 'Administrador']); 
        Role::create(['nombre_rol' => 'Cliente']);       
        Role::create(['nombre_rol' => 'Cajero']);        
        Role::create(['nombre_rol' => 'Cocinero']);      
    }
}