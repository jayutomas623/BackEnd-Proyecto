<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $productos = Product::with(['category', 'insumoRetail', 'modificadores'])->orderBy('category_id')->get();
        return response()->json($productos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|string',
            'insumo_id' => 'nullable|exists:insumos,id', 
            'modificadores' => 'nullable|array',        
            'modificadores.*' => 'exists:insumos,id'
        ]);

        $producto = Product::create($request->all());

        if ($request->has('modificadores')) {
            $producto->modificadores()->sync($request->modificadores);
        }

        return response()->json(['mensaje' => 'Producto creado con éxito', 'producto' => $producto], 201);
    }

    public function update(Request $request, $id)
    {
        $producto = Product::findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|string',
            'disponibilidad' => 'boolean',
            'insumo_id' => 'nullable|exists:insumos,id',
            'modificadores' => 'nullable|array',
            'modificadores.*' => 'exists:insumos,id'
        ]);

        $producto->update($request->all());

        if ($request->has('modificadores')) {
            $producto->modificadores()->sync($request->modificadores);
        } else {
            $producto->modificadores()->sync([]);
        }

        return response()->json(['mensaje' => 'Producto actualizado', 'producto' => $producto]);
    }

    public function destroy($id)
    {
        $producto = Product::findOrFail($id);
        $producto->modificadores()->detach();
        \App\Models\OrderDetail::where('product_id', $id)
            ->update(['product_id' => null]);
        $producto->delete();
        return response()->json(['mensaje' => 'Producto eliminado correctamente']);
    }
}