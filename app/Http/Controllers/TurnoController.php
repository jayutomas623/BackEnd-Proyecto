<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Models\Order;
use App\Models\OrderLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TurnoController extends Controller
{
    // Turno activo del usuario actual
    public function actual(Request $request)
    {
        $turno = Turno::with('user')
            ->where('user_id', $request->user()->id)
            ->where('estado', 'abierto')
            ->first();

        return response()->json($turno);
    }

    // Abrir turno
    public function abrir(Request $request)
    {
        $request->validate([
            'monto_inicial' => 'required|numeric|min:0',
        ]);

        // Verificar que no haya un turno abierto
        $turnoExistente = Turno::where('user_id', $request->user()->id)
            ->where('estado', 'abierto')
            ->first();

        if ($turnoExistente) {
            return response()->json([
                'message' => 'Ya tienes un turno abierto.',
                'turno'   => $turnoExistente,
            ], 422);
        }

        $turno = Turno::create([
            'user_id'        => $request->user()->id,
            'monto_inicial'  => $request->monto_inicial,
            'abierto_en'     => Carbon::now(),
            'estado'         => 'abierto',
        ]);

        $turno->refresh();

        OrderLog::create([
            'order_id'        => null,
            'user_id'         => $request->user()->id,
            'estado_anterior' => null,
            'estado_nuevo'    => 'abierto',
            'accion'          => 'turno_abierto',
            'detalle'         => "Turno abierto con Bs. {$request->monto_inicial} en caja",
        ]);

        return response()->json(['mensaje' => 'Turno abierto', 'turno' => $turno], 201);
    }

    // Cerrar turno
    public function cerrar(Request $request, $id)
    {
        $request->validate([
            'monto_cierre_real' => 'required|numeric|min:0',
            'observaciones'     => 'nullable|string|max:500',
        ]);

        $turno = Turno::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->where('estado', 'abierto')
            ->firstOrFail();

        // Calcular ventas en efectivo durante el turno
        $ventasEfectivo = Order::where('tipo_pago', 'efectivo')
            ->where('estado', 'entregado')
            ->where('created_at', '>=', $turno->abierto_en)
            ->sum('total');

        $esperado   = $turno->monto_inicial + $ventasEfectivo;
        $real       = $request->monto_cierre_real;
        $diferencia = $real - $esperado;

        $turno->update([
            'monto_cierre_esperado' => round($esperado, 2),
            'monto_cierre_real'     => $real,
            'diferencia'            => round($diferencia, 2),
            'cerrado_en'            => Carbon::now(),
            'estado'                => 'cerrado',
            'observaciones'         => $request->observaciones,
        ]);

        OrderLog::create([
            'order_id'        => null,
            'user_id'         => $request->user()->id,
            'estado_anterior' => 'abierto',
            'estado_nuevo'    => 'cerrado',
            'accion'          => 'turno_cerrado',
            'detalle'         => "Turno cerrado — Esperado: Bs. {$esperado} · Real: Bs. {$real} · Diferencia: Bs. {$diferencia}",
        ]);

        return response()->json([
            'mensaje' => 'Turno cerrado correctamente',
            'turno'   => $turno,
        ]);
    }

    // Historial de turnos (admin)
    public function historial(Request $request)
    {
        $query = Turno::with('user')->orderBy('abierto_en', 'desc');

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->estado) {
            $query->where('estado', $request->estado);
        }
        if ($request->fecha) {
            $query->whereDate('abierto_en', $request->fecha);
        }

        return response()->json($query->paginate(20));
    }

    // Resumen del turno actual (ventas en tiempo real)
    public function resumen(Request $request, $id)
    {
        $turno = Turno::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $pedidos = Order::with('details.product')
            ->where('estado', 'entregado')
            ->where('created_at', '>=', $turno->abierto_en)
            ->when($turno->cerrado_en, fn($q) => $q->where('created_at', '<=', $turno->cerrado_en))
            ->get();

        $efectivo = $pedidos->where('tipo_pago', 'efectivo')->sum('total');
        $qr       = $pedidos->where('tipo_pago', 'qr')->sum('total');

        return response()->json([
            'turno'            => $turno,
            'ventas_efectivo'  => round($efectivo, 2),
            'ventas_qr'        => round($qr, 2),
            'total_ventas'     => round($efectivo + $qr, 2),
            'total_pedidos'    => $pedidos->count(),
            'monto_esperado'   => round($turno->monto_inicial + $efectivo, 2),
        ]);
    }
}