<?php

namespace App\Notifications;

use App\Models\Orden;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderRejectedByTechnician extends Notification
{
    use Queueable;

    public Orden $orden;
    public User $technician;

    public function __construct(Orden $orden, User $technician)
    {
        $this->orden = $orden;
        $this->technician = $technician;
    }

    public function via(object $notifiable): array
    {
        // Esta notificación se guardará en la base de datos.
        return ['database'];
    }

    // Define cómo se almacenará la notificación en la base de datos.
    public function toDatabase(object $notifiable): array
    {
        return [
            'order_id' => $this->orden->id,
            'order_number' => $this->orden->numero_orden,
            'technician_name' => $this->technician->name,
            'message' => "El técnico {$this->technician->name} ha rechazado la orden #{$this->orden->numero_orden}. Se requiere reasignación.",
        ];
    }
}