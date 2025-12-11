<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->text('pregunta');
            $table->boolean('anonima')->default(false);
            $table->integer('vistas')->default(0);
            $table->timestamps();
        });
        
        Schema::create('respuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregunta_id')->constrained('preguntas')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained(); // Puede ser vendedor o usuario
            $table->foreignId('vendedor_id')->nullable()->constrained('vendedores');
            $table->text('respuesta');
            $table->boolean('oficial')->default(false); // Respuesta oficial del vendedor
            $table->integer('likes')->default(0);
            $table->timestamps();
        });
        
        Schema::create('pregunta_votos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregunta_id')->constrained('preguntas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->boolean('util')->default(true);
            $table->timestamps();
            
            $table->unique(['pregunta_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pregunta_votos');
        Schema::dropIfExists('respuestas');
        Schema::dropIfExists('preguntas');
    }
};