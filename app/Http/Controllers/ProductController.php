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

        // --- LOG DE PRODUCTO CREADO ---
        \App\Models\OrderLog::create([
            'order_id'        => null,
            'user_id'         => auth()->id(),
            'estado_anterior' => null,
            'estado_nuevo'    => 'creado',
            'accion'          => 'producto_creado',
            'detalle'         => "Producto '{$producto->nombre}' creado — Bs. {$producto->precio}",
        ]);

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

        // --- LOG DE PRODUCTO ACTUALIZADO ---
        \App\Models\OrderLog::create([
            'order_id'        => null,
            'user_id'         => auth()->id(),
            'estado_anterior' => null,
            'estado_nuevo'    => 'actualizado',
            'accion'          => 'producto_editado',
            'detalle'         => "Producto '{$producto->nombre}' editado",
        ]);

        return response()->json(['mensaje' => 'Producto actualizado', 'producto' => $producto]);
    }

    public function destroy($id)
    {
        $producto = Product::findOrFail($id);
        $producto->modificadores()->detach();
        \App\Models\OrderDetail::where('product_id', $id)
            ->update(['product_id' => null]);
            
        // --- LOG DE PRODUCTO ELIMINADO ---
        \App\Models\OrderLog::create([
            'order_id'        => null,
            'user_id'         => auth()->id(),
            'estado_anterior' => null,
            'estado_nuevo'    => 'eliminado',
            'accion'          => 'producto_eliminado',
            'detalle'         => "Producto '{$producto->nombre}' eliminado",
        ]);

        $producto->delete();
        
        return response()->json(['mensaje' => 'Producto eliminado correctamente']);
    }
}