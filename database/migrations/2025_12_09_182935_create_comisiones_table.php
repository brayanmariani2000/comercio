<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comisiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendedor_id')->constrained('vendedores');
            $table->foreignId('pedido_id')->constrained('pedidos');
            $table->decimal('monto_venta', 12, 2);
            $table->decimal('porcentaje_comision', 5, 2); // % que se queda la plataforma
            $table->decimal('monto_comision', 12, 2);
            $table->decimal('monto_vendedor', 12, 2);
            $table->enum('estado', ['pendiente', 'calculada', 'pagada', 'retenida'])->default('pendiente');
            $table->date('fecha_pago')->nullable();
            $table->string('referencia_pago')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->index(['vendedor_id', 'estado']);
        });
        
        // Historial de pagos a vendedores
        Schema::create('pagos_vendedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendedor_id')->constrained('vendedores');
            $table->string('numero_pago')->unique();
            $table->decimal('monto_total', 12, 2);
            $table->enum('metodo_pago', ['transferencia', 'pago_movil', 'efectivo', 'paypal'])->default('transferencia');
            $table->string('referencia')->nullable();
            $table->string('banco')->nullable();
            $table->enum('estado', ['pendiente', 'procesando', 'completado', 'fallido'])->default('pendiente');
            $table->json('comisiones_incluidas')->nullable(); // IDs de comisiones incluidas
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_vendedores');
        Schema::dropIfExists('comisiones');
    }
};