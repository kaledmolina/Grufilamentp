<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use App\Models\User;
use Illuminate\Http\Request;
// 👇 Importa la clase de Notificación de Filament
use Filament\Notifications\Notification as FilamentNotification;
// 👇 CORRECCIÓN: Importa la clase de Acción para las notificaciones
use Filament\Notifications\Actions\Action;

class OrderController extends Controller
{
    /**
     * Devuelve las órdenes asignadas al técnico autenticado.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = Orden::where('technician_id', $user->id)
            ->latest()
            ->get();

        return response()->json($orders);
    }

    /**
     * Muestra los detalles de una orden específica.
     */
    public function show(Request $request, Orden $orden)
    {
        // Verifica que el técnico que solicita la orden sea el mismo que la tiene asignada.
        if ($request->user()->id !== $orden->technician_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Devolvemos la orden con todos sus detalles.
        return response()->json($orden);
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

        // NUEVA VALIDACIÓN: Verificar si el técnico ya tiene una orden "en proceso".
        $hasActiveOrder = Orden::where('technician_id', $user->id)
                                ->where('status', 'en proceso')
                                ->exists();

        if ($hasActiveOrder) {
            return response()->json(['message' => 'Ya tienes una orden en proceso. Debes completarla antes de tomar otra.'], 422);
        }

        // Verificar que la orden esté en un estado que se pueda aceptar
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

        // Verificar que la orden le pertenece al técnico
        if ($orden->technician_id !== $user->id) {
            return response()->json(['message' => 'No autorizado para modificar esta orden.'], 403);
        }

        // Verificar que la orden esté "en proceso" para poder cerrarla
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
     * Permite al técnico rechazar una orden y notifica directamente desde aquí.
     */
    public function rejectOrder(Request $request, Orden $orden)
    {
        $user = $request->user();

        // Verificar que la orden le pertenece al técnico
        if ($orden->technician_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Verificar que la orden esté en estado 'abierta'
        if ($orden->status !== 'abierta') {
            return response()->json(['message' => 'Esta orden ya no se puede rechazar.'], 422);
        }

        // Actualiza la orden: estado a 'rechazada' y quita al técnico.
        $orden->status = 'rechazada';
        $orden->technician_id = null;
        $orden->save();

        // --- Lógica de Notificación Directa ---
        
        // 1. Obtener a los usuarios que recibirán la notificación
        $recipients = User::role(['administrador', 'operador'])->get();

        // 2. Crear la notificación de Filament
        $notification = FilamentNotification::make()
            ->title('Orden Rechazada')
            ->icon('heroicon-o-exclamation-triangle')
            ->body("El técnico {$user->name} ha rechazado la orden #{$orden->numero_orden}. Se requiere reasignación.")
            ->actions([
                // CORRECCIÓN: Se usa la clase Action importada correctamente.
                Action::make('view')
                    ->label('Ver Orden')
                    ->url(route('filament.admin.resources.ordens.edit', ['record' => $orden])),
            ])
            ->danger(); // Color rojo

        // 3. Enviar la notificación a cada destinatario
        foreach ($recipients as $recipient) {
            $notification->sendToDatabase($recipient);
        }
        
        return response()->json(['message' => 'Orden rechazada correctamente.']);
    }
}
