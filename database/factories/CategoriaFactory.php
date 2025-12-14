<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    public function definition()
    {
        $nombre = $this->faker->unique()->word;
        return [
            'nombre' => ucfirst($nombre),
            'slug' => Str::slug($nombre),
            'descripcion' => $this->faker->sentence,
            // 'color' => $this->faker->hexColor,
            'orden' => $this->faker->numberBetween(1, 100),
            'activo' => true,
            'mostrar_en_inicio' => $this->faker->boolean,
            // 'destacada' => $this->faker->boolean(20),
        ];
    }
}
