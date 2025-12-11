<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_pedido')->unique(); // Ej: PED-20241215-001
            $table->string('codigo_qr')->unique(); // Código único para QR
            $table->string('serial_compra')->unique(); // Serial para validación
            $table->foreignId('user_id')->constrained();
            $table->foreignId('vendedor_id')->constrained('vendedores');
            
            // Información del cliente
            $table->string('nombre_cliente');
            $table->string('cedula_cliente'); // Cédula venezolana
            $table->string('telefono_cliente');
            $table->string('email_cliente');
            $table->text('direccion_envio');
            $table->string('ciudad_envio');
            $table->string('estado_envio');
            
            // Detalles del pedido
            $table->decimal('subtotal', 12, 2);
            $table->decimal('envio', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0); // 16% IVA Venezuela
            $table->decimal('total', 12, 2);
            
            // Método de pago (Venezuela)
            $table->enum('metodo_pago', [
                'transferencia_bancaria',
                'pago_movil',
                'efectivo',
                'tarjeta_debito',
                'tarjeta_credito',
                'paypal',
                'zelle',
                'binance'
            ])->default('transferencia_bancaria');
            
            // Referencia de pago
            $table->string('referencia_pago')->nullable();
            $table->string('banco')->nullable();
            
            // Estados
            $table->enum('estado_pago', ['pendiente', 'verificando', 'confirmado', 'rechazado'])->default('pendiente');
            $table->enum('estado_pedido', [
                'pendiente',
                'confirmado',
                'preparando',
                'enviado',
                'entregado',
                'cancelado',
                'reclamado'
            ])->default('pendiente');
            
            // Fechas importantes
            $table->timestamp('fecha_pago')->nullable();
            $table->timestamp('fecha_envio')->nullable();
            $table->timestamp('fecha_entrega')->nullable();
            
            // Información adicional
            $table->text('notas')->nullable();
            $table->text('comentario_reclamo')->nullable();
            
            // Para validaciones
            $table->integer('intentos_validacion')->default(0);
            $table->timestamp('ultima_validacion')->nullable();
            
            $table->timestamps();
            
            // Índices para búsqueda rápida
            $table->index('codigo_qr');
            $table->index('serial_compra');
            $table->index('numero_pedido');
            $table->index(['user_id', 'estado_pedido']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};