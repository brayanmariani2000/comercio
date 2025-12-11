<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos');
            $table->string('codigo_qr');
            $table->string('serial_compra');
            $table->string('tipo_validacion')->default('qr'); // qr, serial, ambos
            $table->string('dispositivo')->nullable(); // Info del dispositivo que escaneó
            $table->string('ubicacion')->nullable(); // GPS/Ubicación
            $table->text('resultado');
            $table->boolean('valido')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validaciones');
    }
};