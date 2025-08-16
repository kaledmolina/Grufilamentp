<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PreoperationalInspection;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class PreoperationalInspectionController extends Controller
{
    /**
     * Almacena una nueva inspección preoperacional.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $vehicle = $user->vehicle;

        if (!$vehicle) {
            return response()->json(['message' => 'No tienes un vehículo asignado.'], 422);
        }

        // Validación para evitar duplicados
        $alreadyExists = PreoperationalInspection::where('vehicle_id', $vehicle->id)
            ->where('fecha_inspeccion', now()->toDateString())
            ->exists();

        if ($alreadyExists) {
            return response()->json(['message' => 'Ya has registrado una inspección para este vehículo hoy.'], 422);
        }

        $data = $request->all();

        // Añade los datos del snapshot del usuario y vehículo
        $data['user_id'] = $user->id;
        $data['vehicle_id'] = $vehicle->id;
        $data['nombre_conductor'] = $user->name;
        $data['licencia_conductor'] = $user->licencia_conduccion;
        $data['placa_vehiculo'] = $vehicle->placa;
        $data['modelo_vehiculo'] = $vehicle->modelo;
        $data['tarjeta_propiedad'] = $vehicle->tarjeta_propiedad;
        $data['fecha_tecnomecanica'] = $vehicle->fecha_tecnomecanica;
        $data['fecha_soat'] = $vehicle->fecha_soat;
        $data['mantenimiento_preventivo_taller'] = $vehicle->mantenimiento_preventivo_taller;
        $data['fecha_mantenimiento'] = $vehicle->fecha_mantenimiento;
        $data['fecha_ultimo_aceite'] = $vehicle->fecha_ultimo_aceite;
        $data['fecha_inspeccion'] = now();

        $inspection = PreoperationalInspection::create($data);

        return response()->json([
            'message' => 'Inspección guardada exitosamente.',
            'inspection' => $inspection
        ], 201);
    }

    public function checkToday(Request $request)
    {
        $user = $request->user();

        $inspectionToday = PreoperationalInspection::where('user_id', $user->id)
            ->whereDate('fecha_inspeccion', today())
            ->exists();

        return response()->json([
            'completed_today' => $inspectionToday,
        ]);
    }
}