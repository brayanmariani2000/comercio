<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendedor_id')->nullable()->constrained('vendedores')->nullOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pedido_id')->nullable()->constrained()->nullOnDelete();
            $table->string('asunto')->nullable();
            $table->string('estado')->default('abierta'); // abierta, cerrada, archivada
            $table->timestamp('ultimo_mensaje_at')->nullable();
            
            // Metadatos de cierre
            $table->foreignId('cerrada_por')->nullable()->constrained('users');
            $table->string('motivo_cierre')->nullable();
            $table->timestamp('fecha_cierre')->nullable();
            
            $table->json('etiquetas')->nullable();
            $table->integer('prioridad')->default(1); // 1: normal, 2: media, 3: alta
            
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index(['user_id', 'updated_at']);
            $table->index(['vendedor_id', 'updated_at']);
        });

        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversacion_id')->constrained('conversaciones')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Si es null, puede ser del vendedor o sistema
            $table->foreignId('vendedor_id')->nullable()->constrained('vendedores')->nullOnDelete(); // Si es null, es usuario o sistema
            
            $table->text('mensaje');
            $table->json('adjuntos')->nullable();
            
            $table->boolean('leido')->default(false);
            $table->timestamp('leido_at')->nullable();
            
            $table->string('tipo')->default('texto'); // texto, imagen, archivo, sistema
            $table->boolean('sistema')->default(false);
            
            // Referencias opcionales
            $table->foreignId('referencia_pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
            $table->foreignId('referencia_producto_id')->nullable()->constrained('productos')->nullOnDelete();
            
            $table->timestamps();
            
            $table->index(['conversacion_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
        Schema::dropIfExists('conversaciones');
    }
};