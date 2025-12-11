<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Datos específicos para Venezuela
            $table->string('cedula')->unique()->nullable()->after('email');
            $table->string('rif')->nullable()->after('cedula');
            $table->enum('tipo_persona', ['natural', 'juridica'])->default('natural')->after('rif');
            $table->string('telefono')->nullable()->after('tipo_persona');
            $table->date('fecha_nacimiento')->nullable()->after('telefono');
            $table->enum('genero', ['masculino', 'femenino', 'otro'])->nullable()->after('fecha_nacimiento');
            
            // Dirección en Venezuela
            $table->string('direccion')->nullable()->after('genero');
            $table->foreignId('estado_id')->nullable()->constrained('estados_venezuela')->after('direccion');
            $table->foreignId('ciudad_id')->nullable()->constrained('ciudades_venezuela')->after('estado_id');
            $table->string('codigo_postal')->nullable()->after('ciudad_id');
            
            // Información adicional
            $table->string('avatar')->nullable()->after('codigo_postal');
            $table->enum('tipo_usuario', ['comprador', 'vendedor', 'administrador', 'supervisor'])->default('comprador')->after('avatar');
            $table->decimal('rating_promedio', 3, 2)->default(0)->after('tipo_usuario');
            $table->integer('total_compras')->default(0)->after('rating_promedio');
            $table->decimal('monto_total_compras', 12, 2)->default(0)->after('total_compras');
            $table->boolean('verificado')->default(false)->after('monto_total_compras');
            $table->boolean('suspendido')->default(false)->after('verificado');
            $table->timestamp('ultimo_acceso')->nullable()->after('suspendido');
            $table->json('preferencias')->nullable()->after('ultimo_acceso');
            
            // Métodos de pago guardados
            $table->json('metodos_pago_guardados')->nullable()->after('preferencias');
            
            // Índices
            $table->index('cedula');
            $table->index('tipo_usuario');
            $table->index(['tipo_usuario', 'verificado']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'cedula', 'rif', 'tipo_persona', 'telefono', 'fecha_nacimiento', 
                'genero', 'direccion', 'estado_id', 'ciudad_id', 'codigo_postal',
                'avatar', 'tipo_usuario', 'rating_promedio', 'total_compras',
                'monto_total_compras', 'verificado', 'suspendido', 'ultimo_acceso',
                'preferencias', 'metodos_pago_guardados'
            ]);
        });
    }
};