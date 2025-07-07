<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use App\Models\User;
use Illuminate\Http\Request;
// 👇 Importa la clase de Notificación de Filament
use Filament\Notifications\Notification as FilamentNotification;

class OrderController extends Controller
{
    // ... (tus métodos index, show, acceptOrder y closeOrder no cambian) ...

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
                FilamentNotification\Actions\Action::make('view')
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