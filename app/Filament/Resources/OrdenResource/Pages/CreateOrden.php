<?php

namespace App\Filament\Resources\OrdenResource\Pages;

use App\Filament\Resources\OrdenResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\FcmService; // <-- Importa el servicio
use App\Models\User;

class CreateOrden extends CreateRecord
{
    protected static string $resource = OrdenResource::class;

    // 👇 AÑADE ESTE MÉTODO COMPLETO
    protected function afterCreate(): void
    {
        $orden = $this->record; // Obtenemos la orden recién creada

        // Si se asignó un técnico al crear la orden...
        if ($orden->technician_id) {
            $tecnico = User::find($orden->technician_id);

            // ...y si el técnico existe y tiene un token.
            if ($tecnico && $tecnico->fcm_token) {
                $title = '¡Nueva Orden Asignada!';
                $body = "Se te ha asignado la orden #{$orden->id}.";
                $data = ['order_id' => (string)$orden->id];

                // Llamamos directamente al servicio para enviar la notificación.
                (new FcmService())->send($tecnico->fcm_token, $title, $body, $data);
            }
        }
    }
}
