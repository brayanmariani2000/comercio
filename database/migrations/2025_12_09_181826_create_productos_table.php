<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendedor_id')->constrained('vendedores')->onDelete('cascade');
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->string('codigo')->unique(); // Código interno
            $table->string('sku')->unique(); // SKU para inventario
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion');
            $table->text('especificaciones')->nullable(); // JSON con especificaciones
            $table->decimal('precio', 12, 2); // Precio en Bs.
            $table->decimal('precio_descuento', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(5);
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('garantia')->nullable(); // Ej: "6 meses", "1 año"
            $table->boolean('nuevo')->default(true);
            $table->boolean('destacado')->default(false);
            $table->boolean('oferta')->default(false);
            $table->boolean('envio_gratis')->default(false);
            $table->decimal('costo_envio', 10, 2)->nullable();
            $table->integer('dias_entrega')->default(3);
            $table->integer('ventas')->default(0);
            $table->integer('vistas')->default(0);
            $table->decimal('calificacion_promedio', 3, 2)->default(0);
            $table->integer('total_resenas')->default(0);
            $table->boolean('activo')->default(true);
            $table->boolean('aprobado')->default(false); // Para moderación
            $table->timestamps();
            
            // Índices para búsqueda
            $table->index('nombre');
            $table->index('precio');
            $table->index(['activo', 'aprobado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};