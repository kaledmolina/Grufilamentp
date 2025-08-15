<?php
// Abre el archivo app/Filament/Resources/PreoperationalInspectionResource/Pages/CreatePreoperationalInspection.php
// y reemplaza su contenido con este código.

namespace App\Filament\Resources\PreoperationalInspectionResource\Pages;

use App\Filament\Resources\PreoperationalInspectionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use App\Models\PreoperationalInspection; // <-- Importar el modelo de Inspección
use Filament\Notifications\Notification; // <-- Importar las notificaciones de Filament

class CreatePreoperationalInspection extends CreateRecord
{
    protected static string $resource = PreoperationalInspectionResource::class;

    /**
     * Este método se ejecuta ANTES de que se intente crear el registro.
     * Es el lugar perfecto para nuestra validación personalizada.
     */
    protected function beforeCreate(): void
    {
        $data = $this->form->getState();
        $user = User::find($data['user_id']);
        $vehicleId = $user?->vehicle?->id;
        $inspectionDate = $data['fecha_inspeccion'];

        // Si no hay un vehículo asignado, no podemos verificar, así que salimos.
        if (!$vehicleId) {
            return;
        }

        // Verificamos si ya existe una inspección para este vehículo en esta fecha.
        $existingInspection = PreoperationalInspection::where('vehicle_id', $vehicleId)
            ->where('fecha_inspeccion', $inspectionDate)
            ->exists();

        // Si ya existe, enviamos una notificación de error y detenemos el proceso.
        if ($existingInspection) {
            Notification::make()
                ->title('Inspección Duplicada')
                ->body('Ya existe una inspección para este vehículo en la fecha seleccionada. Por favor, edite la existente o elija otra fecha.')
                ->danger()
                ->send();
            
            $this->halt();
        }
    }

    /**
     * Este método se ejecuta justo antes de que los datos del formulario se guarden
     * en la base de datos, asegurando que se incluyan todos los campos necesarios.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::find($data['user_id']);
        $vehicle = $user?->vehicle;

        // Asignamos los IDs y todos los datos "snapshot" al array que se guardará.
        $data['user_id'] = $user?->id;
        $data['vehicle_id'] = $vehicle?->id;
        $data['nombre_conductor'] = $user?->name;
        $data['licencia_conductor'] = $user?->licencia_conduccion;
        $data['placa_vehiculo'] = $vehicle?->placa;
        $data['modelo_vehiculo'] = $vehicle?->modelo;
        $data['marca_vehiculo'] = $vehicle?->marca;
        $data['tarjeta_propiedad'] = $vehicle?->tarjeta_propiedad;
        $data['fecha_tecnomecanica'] = $vehicle?->fecha_tecnomecanica;
        $data['fecha_soat'] = $vehicle?->fecha_soat;
        $data['mantenimiento_preventivo_taller'] = $vehicle?->mantenimiento_preventivo_taller;
        $data['fecha_mantenimiento'] = $vehicle?->fecha_mantenimiento;
        $data['fecha_ultimo_aceite'] = $vehicle?->fecha_ultimo_aceite;

        return $data;
    }
}
