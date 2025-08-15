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
            // Añade la nueva columna, que puede ser nula.
            $table->string('marca_vehiculo')->nullable()->after('modelo_vehiculo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preoperational_inspections', function (Blueprint $table) {
            $table->dropColumn('marca_vehiculo');
        });
    }
};

