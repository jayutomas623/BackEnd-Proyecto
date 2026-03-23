<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::orderBy('orden')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'orden'  => 'nullable|integer',
        ]);
        $cat = Category::create([
            'nombre' => $request->nombre,
            'orden'  => $request->orden ?? 0,
            'estado' => true,
        ]);
        return response()->json($cat, 201);
    }

    public function update(Request $request, $id)
    {
        $cat = Category::findOrFail($id);
        $request->validate(['nombre' => 'required|string|max:255']);
        $cat->update($request->only(['nombre', 'orden', 'estado']));
        return response()->json($cat);
    }

    public function destroy($id)
    {
        $cat = Category::findOrFail($id);
        if ($cat->products()->count() > 0) {
            return response()->json([
                'mensaje' => 'No puedes eliminar una categoría que tiene productos asignados.'
            ], 422);
        }
        $cat->delete();
        return response()->json(['mensaje' => 'Categoría eliminada']);
    }
}