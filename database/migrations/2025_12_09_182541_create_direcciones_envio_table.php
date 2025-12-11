<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direcciones_envio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('alias')->default('Casa'); // Casa, Trabajo, etc.
            $table->string('nombre_completo');
            $table->string('cedula');
            $table->string('telefono');
            $table->text('direccion');
            $table->foreignId('estado_id')->constrained('estados_venezuela');
            $table->foreignId('ciudad_id')->constrained('ciudades_venezuela');
            $table->string('codigo_postal')->nullable();
            $table->text('instrucciones')->nullable(); // Instrucciones para el repartidor
            $table->boolean('principal')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'principal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direcciones_envio');
    }
};