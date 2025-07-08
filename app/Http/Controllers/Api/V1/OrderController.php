<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use App\Models\User;
use Illuminate\Http\Request;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;

class OrderController extends Controller
{
    /**
     * Devuelve las órdenes asignadas al técnico autenticado.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Empezamos la consulta
        $query = Orden::where('technician_id', $user->id);

        // Aplicamos el filtro de estado si viene en la petición
        if ($request->has('status') && $request->status !== 'todas') {
            $query->where('status', $request->status);
        }

        // Ordenamos por la más reciente y paginamos los resultados
        $orders = $query->latest()->paginate(15); // Muestra 15 órdenes por página

        return response()->json($orders);
    }

    /**
     * Muestra los detalles de una orden específica.
     */
    public function show(Request $request, Orden $orden)
    {
        if ($request->user()->id !== $orden->technician_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json($orden);
    }

    /**
     * Permite al técnico "tomar" una orden, cambiando su estado.
     */
    public function acceptOrder(Request $request, Orden $orden)
    {
        $user = $request->user();

        if ($orden->technician_id !== $user->id) {
            return response()->json(['message' => 'No autorizado para modificar esta orden.'], 403);
        }

        $hasActiveOrder = Orden::where('technician_id', $user->id)
                                ->where('status', 'en proceso')
                                ->exists();

        if ($hasActiveOrder) {
            return response()->json(['message' => 'Ya tienes una orden en proceso. Debes completarla antes de tomar otra.'], 422);
        }

        if ($orden->status !== 'abierta') {
            return response()->json(['message' => 'Esta orden ya no se puede procesar.'], 422);
        }

        $orden->status = 'en proceso';
        $orden->save();

        return response()->json([
            'message' => 'Orden aceptada correctamente.',
            'order' => $orden
        ]);
    }

    /**
     * Permite al técnico "cerrar" una orden, cambiando su estado.
     */
    public function closeOrder(Request $request, Orden $orden)
    {
        $user = $request->user();

        if ($orden->technician_id !== $user->id) {
            return response()->json(['message' => 'No autorizado para modificar esta orden.'], 403);
        }

        if ($orden->status !== 'en proceso') {
            return response()->json(['message' => 'Esta orden no se puede cerrar en su estado actual.'], 422);
        }

        $orden->status = 'cerrada';
        $orden->save();

        return response()->json([
            'message' => 'Orden cerrada exitosamente.',
            'order' => $orden
        ]);
    }
    
    /**
     * Permite al técnico rechazar una orden y notifica directamente a los administradores/operadores.
     */
    public function rejectOrder(Request $request, Orden $orden)
    {
        $user = $request->user();

        if ($orden->technician_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($orden->status !== 'abierta') {
            return response()->json(['message' => 'Esta orden ya no se puede rechazar.'], 422);
        }

        // Actualiza la orden
        $orden->status = 'rechazada';
        $orden->technician_id = null;
        $orden->save();

        // --- Lógica de Notificación Directa ---
        
        $recipients = User::role(['administrador', 'operador'])->get();

        $notification = FilamentNotification::make()
            ->title('Orden Rechazada')
            ->icon('heroicon-o-exclamation-triangle')
            ->body("El técnico {$user->name} ha rechazado la orden #{$orden->numero_orden}. Se requiere reasignación.")
            ->actions([
                Action::make('view')
                    ->label('Ver Orden')
                    ->url(route('filament.admin.resources.ordens.edit', ['record' => $orden])),
            ])
            ->danger();

        foreach ($recipients as $recipient) {
            $notification->sendToDatabase($recipient);
        }
        
        return response()->json(['message' => 'Orden rechazada correctamente.']);
    }
}