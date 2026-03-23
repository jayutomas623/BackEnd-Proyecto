<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;

class MenuController extends Controller
{
    public function getCategories()
    {
        $categorias = Category::where('estado', true)->orderBy('orden')->get();
        return response()->json($categorias);
    }

    public function getProducts()
    {
        $productos = Product::with(['category', 'insumoRetail', 'modificadores'])
                    ->where('disponibilidad', true)
                    ->get();
        return response()->json($productos);
    }
}