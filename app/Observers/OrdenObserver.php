<?php

namespace App\Observers;

use App\Models\Orden;
use App\Models\User;
use App\Notifications\OrderAssignedNotification;

class OrdenObserver
{
    /**
     * Se ejecuta después de que una orden es actualizada.
     */
    public function updated(Orden $orden): void
    {
        // Comprobamos si el campo 'technician_id' acaba de ser modificado y no es nulo
        if ($orden->isDirty('technician_id') && !is_null($orden->technician_id)) {
            $tecnico = User::find($orden->technician_id);
            if ($tecnico) {
                $tecnico->notify(new OrderAssignedNotification($orden));
            }
        }
    }
}
