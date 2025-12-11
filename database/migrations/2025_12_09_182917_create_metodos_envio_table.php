<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metodos_envio', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // MRW, Zoom, Delivery, etc.
            $table->string('codigo')->unique();
            $table->text('descripcion')->nullable();
            $table->decimal('costo_base', 10, 2);
            $table->enum('tipo_costo', ['fijo', 'por_peso', 'por_distancia', 'gratis'])->default('fijo');
            $table->json('configuracion')->nullable(); // Configuración específica
            $table->json('zonas_cobertura')->nullable(); // Estados/ciudades donde opera
            $table->integer('dias_entrega_min')->default(1);
            $table->integer('dias_entrega_max')->default(5);
            $table->boolean('activo')->default(true);
            $table->boolean('envio_gratis_minimo')->default(false);
            $table->decimal('minimo_envio_gratis', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metodos_envio');
    }
};