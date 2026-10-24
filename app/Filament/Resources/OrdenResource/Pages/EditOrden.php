<?php

namespace App\Filament\Resources\OrdenResource\Pages;

use App\Filament\Resources\OrdenResource;
use App\Models\User;
use App\Services\FcmService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditOrden extends EditRecord
{
    protected static string $resource = OrdenResource::class;

    /**
     * Este método se ejecuta DESPUÉS de que se guardan los cambios en una orden.
     */
    protected function afterSave(): void
    {
        $orden = $this->record;

        // Verificamos si el campo 'technician_id' fue el que cambió.
        if ($orden->wasChanged('technician_id') && ! is_null($orden->technician_id)) {

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
                'title' => '¡Orden Actualizada!',
                'body' => "Se te ha asignado la orden #{$orden->id}.",
            ];
            $data = ['order_id' => (string) $orden->id];

            $enviado = false;
            try {
                $enviado = app(FcmService::class)->sendToUser($tecnico, $notification, $data);
            } catch (\Throwable $e) {
                Log::error('Error enviando push en EditOrden: ' . $e->getMessage());
            }

            if (! $enviado) {
                Notification::make()
                    ->title('Notificación no enviada al dispositivo')
                    ->warning()
                    ->send();
            }
        }
    }

    /**
     * Este método muestra siempre la notificación de éxito al guardar.
     */
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Orden Actualizada')
            ->body('Los cambios han sido guardados exitosamente.');
    }
}
