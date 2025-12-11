<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('rif')->unique(); // RIF del vendedor
            $table->string('razon_social');
            $table->string('nombre_comercial');
            $table->string('direccion_fiscal');
            $table->string('telefono');
            $table->string('email');
            $table->string('ciudad');
            $table->string('estado');
            $table->enum('tipo_vendedor', ['individual', 'empresa'])->default('individual');
            $table->decimal('calificacion_promedio', 3, 2)->default(0);
            $table->integer('total_ventas')->default(0);
            $table->boolean('verificado')->default(false);
            $table->boolean('activo')->default(true);
            $table->json('metodos_pago')->nullable(); // ['transferencia', 'efectivo', 'pago_movil', 'tarjeta']
            $table->json('zonas_envio')->nullable(); // Zonas donde hace envíos
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendedores');
    }
};