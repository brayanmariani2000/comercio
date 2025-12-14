<?php

use App\Models\User;
use App\Models\Vendedor;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $email = 'vendedor@prueba.com';
    $password = 'password';

    $user = User::firstOrCreate(
        ['email' => $email],
        [
            'name' => 'Vendedor Prueba',
            'password' => Hash::make($password),
            'tipo_usuario' => 'vendedor',
            'email_verified_at' => now(),
        ]
    );

    // Ensure password is set correctly if user existed but we want to login
    $user->password = Hash::make($password);
    $user->tipo_usuario = 'vendedor';
    $user->save();

    $vendedor = Vendedor::firstOrCreate(
        ['user_id' => $user->id],
        Vendedor::factory()->make([
            'user_id' => $user->id,
            'email' => $user->email, 
            'activo' => true, 
            'verificado' => true,
            'tipo_vendedor' => 'individual'
        ])->toArray()
    );

    // Force verified/active
    $vendedor->activo = true;
    $vendedor->verificado = true;
    $vendedor->save();

    echo "SUCCESS: User created/updated.\n";
    echo "Email: " . $user->email . "\n";
    echo "Password: " . $password . "\n";
    echo "Vendor ID: " . $vendedor->id . "\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
