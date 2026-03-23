<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $pedidos = Order::with(['user', 'details.product'])
                        ->orderBy('created_at', 'desc')
                        ->get();

        return response()->json($pedidos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'carrito'          => 'required|array',
            'total'            => 'required|numeric',
            'tipo_pago'        => 'required|string',
            'ubicacion_tipo'   => 'nullable|in:mesa,rincon',
            'ubicacion_numero' => 'nullable|integer|min:1',
        ]);

        $order = Order::create([
            'user_id'          => $request->user()->id,
            'total'            => $request->total,
            'tipo_pago'        => $request->tipo_pago,
            'estado'           => 'espera',
            'ubicacion_tipo'   => $request->ubicacion_tipo,
            'ubicacion_numero' => $request->ubicacion_numero,
        ]);

        foreach ($request->carrito as $item) {
            $notas = null;
            if (isset($item['extrasNombres']) && count($item['extrasNombres']) > 0) {
                $notas = implode(', ', $item['extrasNombres']);
            }

            OrderDetail::create([
                'order_id'        => $order->id,
                'product_id'      => $item['id'],
                'cantidad'        => $item['cantidad'],
                'precio_unitario' => $item['precio'],
                'subtotal'        => $item['precio'] * $item['cantidad'],
                'notas_extras'    => $notas,
            ]);

            $producto = \App\Models\Product::find($item['id']);
            if ($producto && $producto->insumo_id) {
                $insumo = \App\Models\Insumo::find($producto->insumo_id);
                if ($insumo && $insumo->tipo_control === 'exacto') {
                    $insumo->cantidad_exacta -= $item['cantidad'];
                    if ($insumo->cantidad_exacta <= 0) {
                        $insumo->cantidad_exacta = 0;
                        $insumo->estado = 'agotado';
                    }
                    $insumo->save();
                }
            }
        }

        return response()->json(['mensaje' => 'Pedido registrado', 'order_id' => $order->id], 201);
    }

    public function update(Request $request, $id)
    {
        $order = Order::with('details')->findOrFail($id);

        if ($order->estado !== 'espera') {
            return response()->json(['message' => 'Solo se pueden editar pedidos en espera.'], 422);
        }

        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'No tienes permiso para editar este pedido.'], 403);
        }

        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.id'         => 'nullable|integer|exists:order_details,id',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.cantidad'   => 'required|integer|min:1',
            'tipo_pago'          => 'nullable|in:efectivo,qr',
        ]);

        $nuevoTotal  = 0;
        $idsEnviados = collect($request->items)->pluck('id')->filter()->toArray();

        $order->details()
              ->whereNotNull('product_id')
              ->whereNotIn('id', $idsEnviados)
              ->delete();

        foreach ($request->items as $item) {
            $product  = \App\Models\Product::findOrFail($item['product_id']);
            $subtotal = $product->precio * $item['cantidad'];
            $nuevoTotal += $subtotal;

            if (!empty($item['id'])) {
                OrderDetail::where('id', $item['id'])->update([
                    'cantidad' => $item['cantidad'],
                    'subtotal' => $subtotal,
                ]);
            } else {
                $order->details()->create([
                    'product_id'      => $item['product_id'],
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $product->precio,
                    'subtotal'        => $subtotal,
                ]);
            }
        }

        $order->update([
            'total'     => $nuevoTotal,
            'tipo_pago' => $request->tipo_pago ?? $order->tipo_pago,
        ]);

        return response()->json(
            Order::with(['details.product', 'user'])->find($order->id)
        );
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:espera,preparando,listo,entregado,cancelado',
        ]);

        $order         = Order::findOrFail($id);
        $order->estado = $request->estado;
        $order->save();

        return response()->json([
            'mensaje' => 'Estado actualizado a: ' . $order->estado,
            'orden'   => $order,
        ]);
    }

    public function myOrders(Request $request)
    {
        $pedidos = Order::with('details.product')
                        ->where('user_id', $request->user()->id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return response()->json($pedidos);
    }

    public function rateOrder(Request $request, $id)
    {
        $request->validate([
            'calificacion' => 'required|integer|min:1|max:5',
            'comentario'   => 'nullable|string',
        ]);

        $order = Order::where('id', $id)
                      ->where('user_id', $request->user()->id)
                      ->firstOrFail();

        $order->calificacion = $request->calificacion;
        $order->comentario   = $request->comentario;
        $order->save();

        return response()->json(['mensaje' => '¡Gracias por tu calificación!']);
    }
}