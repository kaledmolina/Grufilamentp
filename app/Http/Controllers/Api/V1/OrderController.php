<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Devuelve las órdenes asignadas al técnico autenticado.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = Orden::where('technician_id', $user->id)
            ->with('client') // Carga la relación con el cliente
            ->latest()
            ->get();

        return response()->json($orders);
    }

    /**
     * Permite al técnico "tomar" una orden, cambiando su estado.
     */
    public function acceptOrder(Request $request, Orden $orden)
    {
        $user = $request->user();

        // Verificar que la orden le pertenece al técnico
        if ($orden->technician_id !== $user->id) {
            return response()->json(['message' => 'No autorizado para modificar esta orden.'], 403);
        }

        // Verificar que la orden esté en un estado que se pueda aceptar
        if (!in_array($orden->status, ['abierta', 'programada'])) {
            return response()->json(['message' => 'Esta orden ya no se puede procesar.'], 422);
        }

        $orden->status = 'en proceso';
        $orden->save();

        return response()->json([
            'message' => 'Orden aceptada correctamente.',
            'order' => $orden
        ]);
    }
}
