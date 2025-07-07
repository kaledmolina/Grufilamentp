<?php
namespace App\Notifications;

use App\Models\Orden;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

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
        return ['database'];
    }

    // 👇 MÉTODO ACTUALIZADO PARA FORMATEAR LA NOTIFICACIÓN PARA FILAMENT
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Orden Rechazada')
            ->icon('heroicon-o-exclamation-triangle')
            ->body("El técnico {$this->technician->name} ha rechazado la orden #{$this->orden->numero_orden}. Se requiere reasignación.")
            ->actions([
                FilamentNotification\Actions\Action::make('view')
                    ->label('Ver Orden')
                    ->url(route('filament.admin.resources.ordens.edit', ['record' => $this->orden])),
            ])
            ->getDatabaseMessage();
    }
}