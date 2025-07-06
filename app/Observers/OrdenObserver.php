<?php

namespace App\Observers;

use App\Models\Orden;
use App\Models\User;
use App\Notifications\OrderAssignedNotification;
use Illuminate\Support\Facades\Log; // <-- Asegúrate que esto esté importado

class OrdenObserver
{
    public function updated(Orden $orden): void
    {
        // Solo se ejecuta si el campo 'technician_id' fue modificado
        if ($orden->isDirty('technician_id') && !is_null($orden->technician_id)) {

            Log::info("✅ Observer de Orden activado para la orden #{$orden->id}.");

            $tecnico = User::find($orden->technician_id);

            if ($tecnico && $tecnico->fcm_token) {
                Log::info("✅ Técnico #{$tecnico->id} encontrado con token. Intentando notificar...");
                $tecnico->notify(new OrderAssignedNotification($orden));
                Log::info("✅ Notificación para la orden #{$orden->id} enviada a la cola.");
            } else {
                Log::error("❌ ERROR: No se pudo notificar. Técnico no encontrado o no tiene token FCM.");
            }
        }
    }
}
