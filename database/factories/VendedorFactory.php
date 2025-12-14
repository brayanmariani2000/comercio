<?php

namespace Database\Factories;

use App\Models\Vendedor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendedorFactory extends Factory
{
    protected $model = Vendedor::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'rif' => 'J-' . $this->faker->numerify('########-#'),
            'razon_social' => $this->faker->company,
            'nombre_comercial' => $this->faker->companySuffix,
            'direccion_fiscal' => $this->faker->address,
            'telefono' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->companyEmail,
            'ciudad' => $this->faker->city,
            'estado' => $this->faker->state,
            'tipo_vendedor' => $this->faker->randomElement(['individual', 'empresa']),
            'activo' => true,
            'verificado' => $this->faker->boolean(80),
            // 'membresia' => $this->faker->randomElement(['basico', 'profesional', 'premium']),
        ];
    }
}
