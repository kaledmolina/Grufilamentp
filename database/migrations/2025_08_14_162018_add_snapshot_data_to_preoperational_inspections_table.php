<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('preoperational_inspections', function (Blueprint $table) {
            // Datos del Conductor
            $table->string('nombre_conductor')->after('vehicle_id');
            $table->string('licencia_conductor')->nullable()->after('nombre_conductor');

            // Datos del Vehículo
            $table->string('placa_vehiculo')->after('licencia_conductor');
            $table->string('modelo_vehiculo')->nullable()->after('placa_vehiculo');
            $table->string('tipo_vehiculo')->nullable()->after('modelo_vehiculo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preoperational_inspections', function (Blueprint $table) {
            $table->dropColumn([
                'nombre_conductor',
                'licencia_conductor',
                'placa_vehiculo',
                'modelo_vehiculo',
                'tipo_vehiculo',
            ]);
        });
    }
};
