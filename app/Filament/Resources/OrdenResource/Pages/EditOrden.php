<?php

namespace App\Filament\Resources\OrdenResource\Pages;

use App\Filament\Resources\OrdenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Services\FcmService; // Importa el servicio
use App\Models\User;
use Filament\Notifications\Notification; // Importa las notificaciones de Filament

class EditOrden extends EditRecord
{
    protected static string $resource = OrdenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Este método se ejecuta DESPUÉS de que se guardan los cambios en una orden.
     */
    protected function afterSave(): void
    {
        $orden = $this->record;

        // Verificamos si el campo 'technician_id' fue el que cambió.
        if ($orden->wasChanged('technician_id') && !is_null($orden->technician_id)) {
            
            $tecnico = User::find($orden->technician_id);

            // Si el técnico no existe o no tiene token, notificamos al operador y salimos.
            if (!$tecnico || !$tecnico->fcm_token) {
                Notification::make()
                    ->title('Técnico sin Dispositivo Registrado')
                    ->body('Los cambios se guardaron, pero el técnico no puede recibir notificaciones push.')
                    ->warning()
                    ->persistent()
                    ->send();
                return;
            }

            // Si el técnico sí tiene token, preparamos y enviamos la notificación.
            $title = '¡Orden Actualizada!';
            $body = "Se te ha asignado la orden #{$orden->id}.";
            $data = ['order_id' => (string)$orden->id];

            app(FcmService::class)->send($tecnico->fcm_token, $title, $body, $data);
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
