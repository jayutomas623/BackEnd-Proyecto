<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getStats()
    {
        $hoy = Carbon::today();
    
        $ventasHoy  = Order::whereDate('created_at', $hoy)->where('estado', 'entregado')->sum('total');
        $pedidosHoy = Order::whereDate('created_at', $hoy)->count();
    
        $pedidosActivos = Order::whereIn('estado', ['espera', 'preparando', 'listo'])
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');
    
        $topProductos = OrderDetail::select('products.nombre', DB::raw('SUM(order_details.cantidad) as total_vendido'))
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.estado', 'entregado')
            ->groupBy('products.id', 'products.nombre')
            ->orderByDesc('total_vendido')
            ->take(3)
            ->get();
    
        // ✅ PostgreSQL: EXTRACT en lugar de HOUR
        $ventasPorHora = Order::whereDate('created_at', $hoy)
            ->where('estado', 'entregado')
            ->select(
                DB::raw('EXTRACT(HOUR FROM created_at)::int as hora'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as pedidos')
            )
            ->groupBy(DB::raw('EXTRACT(HOUR FROM created_at)::int'))
            ->orderBy(DB::raw('EXTRACT(HOUR FROM created_at)::int'))
            ->get();
    
        $horasCompletas = collect();
        for ($h = 8; $h <= 23; $h++) {
            $encontrado = $ventasPorHora->firstWhere('hora', $h);
            $horasCompletas->push([
                'hora'    => sprintf('%02d:00', $h),
                'total'   => $encontrado ? round($encontrado->total, 2) : 0,
                'pedidos' => $encontrado ? $encontrado->pedidos : 0,
            ]);
        }
    
        $ultimos7 = collect();
        for ($i = 6; $i >= 0; $i--) {
            $fecha   = Carbon::today()->subDays($i);
            $ventas  = Order::whereDate('created_at', $fecha)->where('estado', 'entregado')->sum('total');
            $pedidos = Order::whereDate('created_at', $fecha)->count();
            $ultimos7->push([
                'fecha'     => $fecha->format('d/m'),
                'diaNombre' => $fecha->locale('es')->dayName,
                'total'     => round($ventas, 2),
                'pedidos'   => $pedidos,
                'esHoy'     => $i === 0,
            ]);
        }
    
        return response()->json([
            'ventas_hoy'      => $ventasHoy,
            'pedidos_hoy'     => $pedidosHoy,
            'top_productos'   => $topProductos,
            'pedidos_activos' => [
                'espera'     => $pedidosActivos['espera']     ?? 0,
                'preparando' => $pedidosActivos['preparando'] ?? 0,
                'listo'      => $pedidosActivos['listo']      ?? 0,
                'total'      => array_sum($pedidosActivos->toArray()),
            ],
            'ventas_por_hora' => $horasCompletas,
            'ventas_7_dias'   => $ultimos7,
        ]);
    }

    public function getCalificaciones()
    {
        $resumen = \App\Models\Order::whereNotNull('calificacion')
            ->select(
                DB::raw('ROUND(AVG(calificacion), 1) as promedio'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN calificacion = 5 THEN 1 ELSE 0 END) as cinco'),
                DB::raw('SUM(CASE WHEN calificacion = 4 THEN 1 ELSE 0 END) as cuatro'),
                DB::raw('SUM(CASE WHEN calificacion = 3 THEN 1 ELSE 0 END) as tres'),
                DB::raw('SUM(CASE WHEN calificacion = 2 THEN 1 ELSE 0 END) as dos'),
                DB::raw('SUM(CASE WHEN calificacion = 1 THEN 1 ELSE 0 END) as uno'),
            )
            ->first();
    
        $recientes = \App\Models\Order::whereNotNull('calificacion')
            ->with('user')
            ->orderByDesc('updated_at')
            ->take(5)
            ->get(['id', 'user_id', 'calificacion', 'comentario', 'updated_at']);
    
        return response()->json([
            'resumen'   => $resumen,
            'recientes' => $recientes,
        ]);
    }
}