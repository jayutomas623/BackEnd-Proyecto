<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use Illuminate\Http\Request;

class InsumoController extends Controller
{

    public function destroy($id)
    {
        $insumo = Insumo::findOrFail($id);

        if ($insumo->tipo_control === 'exacto') {
            $producto = \App\Models\Product::where('insumo_id', $insumo->id)->first();
            if ($producto) {
                $producto->modificadores()->detach();
                \App\Models\OrderDetail::where('product_id', $producto->id)
                    ->update(['product_id' => null]);
                $producto->delete();
            }
        }

        $insumo->delete();
        return response()->json(['mensaje' => 'Insumo eliminado correctamente']);
    }
    
    public function index()
    {
        $insumos = Insumo::orderBy('nombre')->get();
        return response()->json($insumos);
    }

    public function store(Request $request)
    {
        // --- CORRECCIÓN APLICADA AQUÍ: Se agregó 'nullable|' al inicio de precio y category_id ---
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'tipo_control'    => 'required|in:exacto,a_granel,extra',
            'cantidad_exacta' => 'nullable|numeric|min:0',
            'precio'          => 'nullable|required_if:tipo_control,exacto|numeric|min:0',
            'category_id'     => 'nullable|required_if:tipo_control,exacto|exists:categories,id'
        ]);

        $insumo = Insumo::create($request->only(['nombre', 'tipo_control', 'cantidad_exacta', 'estado', 'unidad_medida']));

        if ($insumo->tipo_control === 'exacto') {
            \App\Models\Product::create([
                'nombre' => $insumo->nombre,
                'category_id' => $request->category_id,
                'precio' => $request->precio,
                'disponibilidad' => true,
                'insumo_id' => $insumo->id
            ]);
        }

        return response()->json([
            'mensaje' => 'Registrado exitosamente',
            'insumo' => $insumo
        ], 201);
    }

    public function actualizarCantidad(Request $request, $id)
    {
        $request->validate(['cantidad_exacta' => 'required|integer|min:0']);

        $insumo = Insumo::findOrFail($id);
        
        // --- Capturamos la cantidad anterior antes de modificar ---
        $prev = $insumo->cantidad_exacta; 

        $insumo->cantidad_exacta = $request->cantidad_exacta;
        
        if ($insumo->cantidad_exacta === 0) {
            $insumo->estado = 'agotado';
        } elseif ($insumo->estado === 'agotado' && $insumo->cantidad_exacta > 0) {
            $insumo->estado = 'disponible';
        }

        $insumo->save();

        // --- LOG DE CAMBIO DE STOCK ---
        if ($prev !== $request->cantidad_exacta) { // Solo registrar si realmente cambió
            \App\Models\OrderLog::create([
                'order_id'        => null,
                'user_id'         => auth()->id(),
                'estado_anterior' => (string) $prev,
                'estado_nuevo'    => (string) $request->cantidad_exacta,
                'accion'          => 'stock',
                'detalle'         => "Stock de '{$insumo->nombre}' cambiado de {$prev} a {$request->cantidad_exacta}",
            ]);
        }

        return response()->json(['mensaje' => 'Cantidad actualizada', 'insumo' => $insumo]);
    }

    public function actualizarEstado(Request $request, $id)
    {
        $request->validate(['estado' => 'required|in:disponible,pronta_reposicion,agotado']);

        $insumo = Insumo::findOrFail($id);
        
        // --- Guardar el estado previo antes de actualizar ---
        $estadoPrev = $insumo->estado; 
        
        $insumo->estado = $request->estado;
        $insumo->save();

        // --- LOG DE CAMBIO DE ESTADO ---
        \App\Models\OrderLog::create([
            'order_id'        => null,
            'user_id'         => auth()->id(),
            'estado_anterior' => $estadoPrev,
            'estado_nuevo'    => $request->estado,
            'accion'          => 'insumo_estado',
            'detalle'         => "Estado de '{$insumo->nombre}' cambiado a {$request->estado}",
        ]);

        return response()->json(['mensaje' => 'Estado del insumo actualizado', 'insumo' => $insumo]);
    }

    public function alertas()
    {
        $alertas = \App\Models\Insumo::where(function($q) {
            $q->where('tipo_control', 'exacto')
              ->where('cantidad_exacta', '<=', 3);
        })->orWhere(function($q) {
            $q->whereIn('tipo_control', ['a_granel', 'extra'])
              ->whereIn('estado', ['pronta_reposicion', 'agotado']);
        })->orderByRaw("CASE estado WHEN 'agotado' THEN 1 WHEN 'pronta_reposicion' THEN 2 ELSE 3 END")
          ->get();

        return response()->json($alertas);
    }
}