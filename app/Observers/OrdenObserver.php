<?php
// Abre el archivo app/Observers/OrdenObserver.php
// y reemplaza su contenido con este código corregido.

namespace App\Observers;

use App\Models\Orden;
use App\Models\User;
use App\Services\FcmService; // Importante que use el servicio

class OrdenObserver
{
    /**
     * Se ejecuta DESPUÉS de que se crea una nueva orden.
     */
    public function created(Orden $orden): void
    {
        // Verificamos si se asignó un técnico al momento de crear la orden.
        if ($orden->technician_id) {
            $this->sendNotification($orden);
        }
    }

    /**
     * Se ejecuta DESPUÉS de que se actualiza una orden existente.
     */
    public function updated(Orden $orden): void
    {
        // Verificamos si el técnico fue asignado o cambiado durante la actualización.
        if ($orden->isDirty('technician_id') && !is_null($orden->technician_id)) {
            $this->sendNotification($orden);
        }
    }

    /**
     * Método centralizado para enviar la notificación.
     * Evita repetir código.
     */
    protected function sendNotification(Orden $orden): void
    {
        $tecnico = User::find($orden->technician_id);

        // Si el técnico existe y tiene un token guardado, se envía la notificación.
        if ($tecnico && $tecnico->fcm_token) {
            $title = '¡Nueva Orden Asignada!';
            $body = "Se te ha asignado la orden #{$orden->id}.";
            $data = ['order_id' => (string)$orden->id];

            // Usa el servicio para enviar la notificación
            (new FcmService())->send($tecnico->fcm_token, $title, $body, $data);
        }
    }
}