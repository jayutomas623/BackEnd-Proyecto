<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class MenuSeeder extends Seeder
{
    public function run(): void
    {

        $catBebidas = Category::create(['nombre' => 'Bebidas Calientes', 'orden' => 1]);
        $catComida = Category::create(['nombre' => 'Comida Rápida', 'orden' => 2]);


        Product::create([
            'category_id' => $catBebidas->id,
            'nombre' => 'Café K\'usillu Especial',
            'descripcion' => 'Delicioso café de destilado intenso.',
            'precio' => 15.50,
            'tiempo_preparacion' => 5
        ]);

        Product::create([
            'category_id' => $catComida->id,
            'nombre' => 'Hamburguesa Andina',
            'descripcion' => 'Hamburguesa con carne de primera y salsa de la casa.',
            'precio' => 25.00,
            'tiempo_preparacion' => 15
        ]);
    }
}