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
            $table->foreignId('user_id')->constrained(); // Comprador
            $table->foreignId('vendedor_id')->constrained('vendedores'); // Vendedor
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos');
            $table->string('asunto')->nullable();
            $table->enum('estado', ['abierta', 'cerrada', 'archivada'])->default('abierta');
            $table->timestamp('ultimo_mensaje_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'vendedor_id', 'producto_id']);
        });
        
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversacion_id')->constrained('conversaciones')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained(); // null = sistema
            $table->foreignId('vendedor_id')->nullable()->constrained('vendedores');
            $table->text('mensaje');
            $table->json('adjuntos')->nullable(); // Fotos, documentos
            $table->boolean('leido')->default(false);
            $table->timestamp('leido_at')->nullable();
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