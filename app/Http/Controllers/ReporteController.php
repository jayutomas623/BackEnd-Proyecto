<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function cierreCaja(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $inicio = Carbon::parse($request->fecha_inicio)->startOfDay();
        $fin    = Carbon::parse($request->fecha_fin)->endOfDay();

        // Solo pedidos entregados en el rango
        $pedidos = Order::with(['user', 'details.product'])
            ->whereBetween('created_at', [$inicio, $fin])
            ->where('estado', 'entregado')
            ->orderBy('created_at', 'desc')
            ->get();

        // Totales por método de pago
        $totalEfectivo = $pedidos->where('tipo_pago', 'efectivo')->sum('total');
        $totalQR       = $pedidos->where('tipo_pago', 'qr')->sum('total');
        $totalGeneral  = $pedidos->sum('total');

        // Top 10 productos más vendidos
        $topProductos = OrderDetail::select(
                'products.nombre',
                DB::raw('SUM(order_details.cantidad) as total_vendido'),
                DB::raw('SUM(order_details.subtotal) as total_ingresos')
            )
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$inicio, $fin])
            ->where('orders.estado', 'entregado')
            ->whereNotNull('order_details.product_id')
            ->groupBy('products.id', 'products.nombre')
            ->orderByDesc('total_vendido')
            ->take(10)
            ->get();

        // Desglose por ubicación
        $porUbicacion = [
            'mesa'        => $pedidos->where('ubicacion_tipo', 'mesa')->sum('total'),
            'rincon'      => $pedidos->where('ubicacion_tipo', 'rincon')->sum('total'),
            'para_llevar' => $pedidos->whereNull('ubicacion_tipo')->sum('total'),
        ];

        return response()->json([
            'resumen' => [
                'total_general'  => round($totalGeneral, 2),
                'total_efectivo' => round($totalEfectivo, 2),
                'total_qr'       => round($totalQR, 2),
                'total_pedidos'  => $pedidos->count(),
            ],
            'por_ubicacion' => $porUbicacion,
            'top_productos' => $topProductos,
            'pedidos'       => $pedidos,
        ]);
    }

    public function historialPedidos(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin'    => 'nullable|date|after_or_equal:fecha_inicio',
            'tipo_pago'    => 'nullable|in:efectivo,qr',
            'ubicacion'    => 'nullable|in:mesa,rincon,para_llevar',
            'cajero_id'    => 'nullable|integer|exists:users,id',
        ]);

        $query = Order::with(['user', 'details.product'])
            ->orderBy('created_at', 'desc');

        // Filtro fechas
        if ($request->fecha_inicio) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }
        if ($request->fecha_fin) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        // Filtro tipo de pago
        if ($request->tipo_pago) {
            $query->where('tipo_pago', $request->tipo_pago);
        }

        // Filtro ubicación
        if ($request->ubicacion === 'para_llevar') {
            $query->whereNull('ubicacion_tipo');
        } elseif ($request->ubicacion) {
            $query->where('ubicacion_tipo', $request->ubicacion);
        }

        // Filtro cajero
        if ($request->cajero_id) {
            $query->where('user_id', $request->cajero_id);
        }

        $pedidos = $query->paginate(20);

        return response()->json($pedidos);
    }

    public function cajeros()
    {
        $cajeros = \App\Models\User::with('role')
            ->whereHas('role', fn($q) => $q->whereIn('nombre', ['Administrador', 'Cajero']))
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    
        return response()->json($cajeros);
    }
}