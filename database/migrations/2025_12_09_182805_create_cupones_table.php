<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cupones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['porcentaje', 'monto_fijo', 'envio_gratis'])->default('porcentaje');
            $table->decimal('valor', 10, 2); // Porcentaje o monto fijo
            $table->decimal('minimo_compra', 12, 2)->nullable();
            $table->integer('usos_maximos')->nullable();
            $table->integer('usos_actuales')->default(0);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('activo')->default(true);
            $table->json('categorias_aplicables')->nullable();
            $table->json('productos_aplicables')->nullable();
            $table->json('usuarios_aplicables')->nullable(); // IDs de usuarios específicos
            $table->timestamps();
        });
        
        Schema::create('cupon_usos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cupon_id')->constrained('cupones')->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('pedido_id')->constrained('pedidos');
            $table->decimal('descuento_aplicado', 12, 2);
            $table->timestamps();
            
            $table->unique(['cupon_id', 'pedido_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cupon_usos');
        Schema::dropIfExists('cupones');
    }
};