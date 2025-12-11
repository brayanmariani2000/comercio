<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('titulo');
            $table->text('mensaje');
            $table->enum('tipo', [
                'pedido', 'pago', 'envio', 'reclamo', 'promocion', 
                'producto', 'sistema', 'seguridad', 'vendedor'
            ])->default('sistema');
            $table->json('data')->nullable(); // Datos adicionales
            $table->boolean('leida')->default(false);
            $table->timestamp('leida_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'leida', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};