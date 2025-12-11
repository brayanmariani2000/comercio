<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resenas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos');
            $table->integer('calificacion')->default(5); // 1-5 estrellas
            $table->string('titulo')->nullable();
            $table->text('comentario');
            $table->json('ventajas')->nullable(); // ["Buen precio", "Calidad", etc.]
            $table->json('desventajas')->nullable();
            $table->boolean('recomendado')->default(true);
            $table->json('imagenes')->nullable(); // Fotos adjuntas
            $table->integer('likes')->default(0);
            $table->boolean('verificada_compra')->default(false);
            $table->boolean('aprobada')->default(false); // Moderación
            $table->boolean('activa')->default(true);
            $table->timestamps();
            
            $table->unique(['user_id', 'producto_id']);
            $table->index(['producto_id', 'aprobada']);
            $table->index('calificacion');
        });
        
        // Likes de reseñas
        Schema::create('resena_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resena_id')->constrained('resenas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->boolean('like')->default(true); // true = like, false = dislike
            $table->timestamps();
            
            $table->unique(['resena_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resena_likes');
        Schema::dropIfExists('resenas');
    }
};