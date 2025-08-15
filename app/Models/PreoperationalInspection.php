<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class PreoperationalInspection extends Model
{
    use HasFactory;

    // Permite la asignación masiva de todos los campos
    protected $guarded = [];
    protected $casts = [
        'fecha_inspeccion' => 'date',
        // Se añaden los casts para los nuevos campos de fecha
        'fecha_tecnomecanica' => 'date',
        'fecha_soat' => 'date',
        'fecha_mantenimiento' => 'date',
        'fecha_ultimo_aceite' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
