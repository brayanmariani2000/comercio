<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reclamos', function (Blueprint $table) {
            $table->string('categoria_reclamo')->nullable()->after('tipo_reclamo');
            $table->integer('prioridad')->default(3)->after('tipo_reclamo'); // 1: Alta, 2: Media, 3: Baja
            $table->foreignId('asignado_a')->nullable()->constrained('users')->after('prioridad');
            $table->timestamp('fecha_limite_respuesta')->nullable()->after('asignado_a');
            $table->boolean('resolucion_aceptada')->nullable()->after('solucion');
            $table->text('comentario_resolucion')->nullable()->after('resolucion_aceptada');
            $table->boolean('reembolso_solicitado')->default(false)->after('comentario_resolucion');
            $table->decimal('monto_reembolso', 12, 2)->default(0)->after('reembolso_solicitado');
            $table->foreignId('producto_reemplazo_id')->nullable()->constrained('productos')->after('monto_reembolso');
            $table->integer('tiempo_respuesta')->nullable()->after('fecha_resolucion'); // En horas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reclamos', function (Blueprint $table) {
            $table->dropForeign(['asignado_a']);
            $table->dropForeign(['producto_reemplazo_id']);
            $table->dropColumn([
                'categoria_reclamo',
                'prioridad',
                'asignado_a',
                'fecha_limite_respuesta',
                'resolucion_aceptada',
                'comentario_resolucion',
                'reembolso_solicitado',
                'monto_reembolso',
                'producto_reemplazo_id',
                'tiempo_respuesta'
            ]);
        });
    }
};
