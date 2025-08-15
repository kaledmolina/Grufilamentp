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
        Schema::create('preoperational_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->date('fecha_inspeccion');
            $table->integer('kilometraje_actual');

            // --- SECCIONES DEL FORMULARIO ---
            // NIVELES
            $table->string('nivel_refrigerante')->default('N/A');
            $table->string('nivel_frenos')->default('N/A');
            $table->string('nivel_aceite_motor')->default('N/A');
            $table->string('nivel_hidraulico')->default('N/A');
            $table->string('nivel_limpiavidrios')->default('N/A');

            // LUCES
            $table->string('luces_altas')->default('N/A');
            $table->string('luces_bajas')->default('N/A');
            $table->string('luces_direccionales')->default('N/A');
            $table->string('luces_freno')->default('N/A');
            $table->string('luces_reversa')->default('N/A');
            $table->string('luces_parqueo')->default('N/A');

            // EQUIPO DE CARRETERA
            $table->string('equipo_extintor')->default('N/A');
            $table->string('equipo_tacos')->default('N/A');
            $table->string('equipo_herramienta')->default('N/A');
            $table->string('equipo_linterna')->default('N/A');
            $table->string('equipo_gato')->default('N/A');
            $table->string('equipo_botiquin')->default('N/A');

            // VARIOS
            $table->string('varios_llantas')->default('N/A');
            $table->string('varios_bateria')->default('N/A');
            $table->string('varios_rines')->default('N/A');
            $table->string('varios_cinturon')->default('N/A');
            $table->string('varios_pito_reversa')->default('N/A');
            $table->string('varios_pito')->default('N/A');
            $table->string('varios_freno_emergencia')->default('N/A');
            $table->string('varios_espejos')->default('N/A');
            $table->string('varios_plumillas')->default('N/A');
            $table->string('varios_panoramico')->default('N/A');

            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Asegura que solo haya una inspección por vehículo por día
            $table->unique(['vehicle_id', 'fecha_inspeccion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preoperational_inspections');
    }
};
