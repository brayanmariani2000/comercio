<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados_venezuela', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('capital');
            $table->string('region');
            $table->integer('municipios');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
        
        Schema::create('municipios_venezuela', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estado_id')->constrained('estados_venezuela');
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
        
        Schema::create('ciudades_venezuela', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios_venezuela');
            $table->string('nombre');
            $table->string('codigo_postal')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ciudades_venezuela');
        Schema::dropIfExists('municipios_venezuela');
        Schema::dropIfExists('estados_venezuela');
    }
};