<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora_sistema', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('accion'); // create, update, delete, login, logout, etc.
            $table->string('modelo')->nullable(); // User, Producto, Pedido, etc.
            $table->unsignedBigInteger('modelo_id')->nullable();
            $table->text('descripcion');
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('ip_address');
            $table->string('user_agent');
            $table->string('url');
            $table->string('metodo_http'); // GET, POST, PUT, DELETE
            $table->timestamps();
            
            $table->index(['user_id', 'accion', 'created_at']);
            $table->index(['modelo', 'modelo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora_sistema');
    }
};