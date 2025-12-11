<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_precios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->decimal('precio_anterior', 12, 2);
            $table->decimal('precio_nuevo', 12, 2);
            $table->enum('tipo_cambio', ['aumento', 'disminucion', 'oferta', 'normal']);
            $table->decimal('porcentaje_cambio', 5, 2)->nullable();
            $table->text('razon')->nullable();
            $table->timestamps();
            
            $table->index(['producto_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_precios');
    }
};