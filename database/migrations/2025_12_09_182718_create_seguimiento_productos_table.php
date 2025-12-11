<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimiento_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->enum('accion', ['visto', 'click', 'busqueda', 'comparar', 'carrito_agregado', 'carrito_eliminado', 'comprado']);
            $table->json('metadata')->nullable(); // Información adicional
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'producto_id', 'accion']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimiento_productos');
    }
};