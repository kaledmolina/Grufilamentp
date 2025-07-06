<?php

namespace App\Observers;

use App\Models\Orden;
use App\Models\User;
use App\Services\FcmService; // <-- Importante que use el servicio

class OrdenObserver
{
    public function updated(Orden $orden): void
    {
        // Si el técnico fue asignado o cambiado...
        if ($orden->isDirty('technician_id') && !is_null($orden->technician_id)) {
            
            $tecnico = User::find($orden->technician_id);

            // ...y si el técnico existe y tiene un token guardado.
            if ($tecnico && $tecnico->fcm_token) {
                $title = '¡Nueva Orden Asignada!';
                $body = "Se te ha asignado la orden #{$orden->id}.";
                $data = ['order_id' => (string)$orden->id];

                // Usa el servicio para enviar la notificación
                (new FcmService())->send($tecnico->fcm_token, $title, $body, $data);
            }
        }
    }
}