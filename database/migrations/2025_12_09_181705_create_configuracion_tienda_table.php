<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_tienda', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_tienda');
            $table->string('rif')->unique(); // RIF para Venezuela
            $table->string('direccion');
            $table->string('telefono');
            $table->string('email');
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('moneda')->default('Bs.'); // Bolívares
            $table->string('simbolo_moneda')->default('Bs.');
            $table->decimal('iva', 5, 2)->default(16.00); // IVA en Venezuela
            $table->string('ciudad')->default('Caracas');
            $table->string('estado')->default('Distrito Capital');
            $table->string('codigo_postal')->nullable();
            $table->text('terminos_condiciones')->nullable();
            $table->text('politica_envios')->nullable();
            $table->text('politica_devoluciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_tienda');
    }
};