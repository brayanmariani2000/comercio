<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('codigos_verificacion', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('codigo');
            $table->enum('tipo', ['registro', 'recuperacion', 'verificacion', 'pago', 'login'])->default('registro');
            $table->integer('intentos')->default(0);
            $table->boolean('verificado')->default(false);
            $table->timestamp('fecha_verificacion')->nullable();
            $table->timestamp('expiracion')->nullable();
            $table->timestamps();
            
            $table->index(['email', 'codigo', 'verificado']);
            $table->index(['telefono', 'codigo', 'verificado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codigos_verificacion');
    }
};