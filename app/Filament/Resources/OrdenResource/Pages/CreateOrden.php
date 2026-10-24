<?php

namespace App\Filament\Resources\OrdenResource\Pages;

use App\Filament\Resources\OrdenResource;
use App\Models\User;
use App\Services\FcmService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateOrden extends CreateRecord
{
    protected static string $resource = OrdenResource::class;

    protected function afterCreate(): void
    {
        $orden = $this->record;

        // Si no se asignó un técnico, no hacemos nada más.
        if (! $orden->technician_id) {
            return;
        }

        $tecnico = User::find($orden->technician_id);

        // Si el técnico no existe o no tiene tokens registrados
        if (! $tecnico || $tecnico->fcmTokens()->count() === 0) {
            Notification::make()
                ->title('Notificación no enviada al dispositivo')
                ->warning()
                ->send();

            return;
        }

        $notification = [
            'title' => '¡Nueva Orden Asignada!',
            'body' => "Se te ha asignado la orden #{$orden->id}.",
        ];
        $data = ['order_id' => (string) $orden->id];

        $enviado = false;
        try {
            $enviado = app(FcmService::class)->sendToUser($tecnico, $notification, $data);
        } catch (\Throwable $e) {
            Log::error('Error enviando push en CreateOrden: ' . $e->getMessage());
        }

        if (! $enviado) {
            Notification::make()
                ->title('Notificación no enviada al dispositivo')
                ->warning()
                ->send();
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Orden Creada')
            ->body('La orden ha sido creada exitosamente.');
    }
}
