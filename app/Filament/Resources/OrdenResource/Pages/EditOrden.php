<?php

namespace App\Filament\Resources\OrdenResource\Pages;

use App\Filament\Resources\OrdenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Services\FcmService; // <-- Importa el servicio
use App\Models\User;

class EditOrden extends EditRecord
{
    protected static string $resource = OrdenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // 👇 AÑADE ESTE MÉTODO COMPLETO
    protected function afterSave(): void
    {
        $orden = $this->record; // Obtenemos la orden que se acaba de guardar

        // Verificamos si el campo 'technician_id' fue el que cambió.
        // Esto evita enviar notificaciones si solo se edita la dirección, por ejemplo.
        if ($orden->wasChanged('technician_id') && !is_null($orden->technician_id)) {
            $tecnico = User::find($orden->technician_id);

            if ($tecnico && $tecnico->fcm_token) {
                $title = '¡Orden Actualizada!';
                $body = "Se te ha asignado la orden #{$orden->id}.";
                $data = ['order_id' => (string)$orden->id];

                (new FcmService())->send($tecnico->fcm_token, $title, $body, $data);
            }
        }
    }
}
