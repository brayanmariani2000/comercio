<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si el usuario admin ya existe
        $adminExists = User::where('email', 'JUANMARIANI1@gmail.com')->exists();
        
        if (!$adminExists) {
            User::create([
                'name' => 'JUANMARIANI',
                'email' => 'JUANMARIANI1@gmail.com',
                'cedula' => 'V-00000000',
                'telefono' => '0414-0000000',
                'password' => Hash::make('123456789'), // Cambia esta contraseña
                'tipo_usuario' => 'administrador',
                'verificado' => 1,
                'email_verified_at' => now(),
                'tipo_persona' => 'natural',
                'genero' => 'masculino',
                'ultimo_acceso' => now(),
                'preferencias' => json_encode([
                    'notificaciones_email' => true,
                    'notificaciones_push' => true,
                    'tema_oscuro' => false,
                    'idioma' => 'es'
                ])
            ]);
            
            $this->command->info('✅ Usuario administrador creado exitosamente!');
            $this->command->info('📧 Email:JUANMARIANI1@gmail.com');
            $this->command->info('🔑 Contraseña:123456789');
        } else {
            $this->command->info('⚠️ El usuario administrador ya existe.');
        }
    }
}