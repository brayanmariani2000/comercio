<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reclamos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_reclamo')->unique();
            $table->foreignId('pedido_id')->constrained('pedidos');
            $table->foreignId('user_id')->constrained();
            $table->enum('tipo_reclamo', [
                'producto_defectuoso',
                'producto_incorrecto',
                'no_recibido',
                'tardio',
                'garantia',
                'otro'
            ]);
            $table->text('descripcion');
            $table->json('evidencias')->nullable(); // Fotos/archivos
            $table->enum('estado', ['abierto', 'en_revision', 'resuelto', 'cerrado'])->default('abierto');
            $table->text('solucion')->nullable();
            $table->timestamp('fecha_resolucion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reclamos');
    }
};