<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Vendedor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition()
    {
        $nombre = $this->faker->unique()->words(3, true);
        
        return [
            'vendedor_id' => Vendedor::factory(),
            'categoria_id' => Categoria::factory(),
            'nombre' => ucfirst($nombre),
            'slug' => Str::slug($nombre),
            'descripcion' => $this->faker->paragraph(),
            'especificaciones' => [
                'Color' => $this->faker->colorName,
                'Material' => $this->faker->word,
                'Peso' => $this->faker->randomFloat(2, 0.1, 10) . ' kg',
            ],
            'precio' => $this->faker->randomFloat(2, 10, 5000),
            'stock' => $this->faker->numberBetween(0, 100),
            'stock_minimo' => 5,
            'marca' => $this->faker->word,
            'modelo' => $this->faker->bothify('MOD-####'),
            // 'condicion' => $this->faker->randomElement(['nuevo', 'usado', 'reacondicionado']),
            'nuevo' => $this->faker->boolean(80),
            'destacado' => $this->faker->boolean(20),
            'oferta' => $this->faker->boolean(15),
            'envio_gratis' => $this->faker->boolean(30),
            'costo_envio' => $this->faker->randomFloat(2, 5, 50),
            'dias_entrega' => $this->faker->numberBetween(1, 15),
            'activo' => true,
            'aprobado' => true,
            // 'tipo_envio' => $this->faker->randomElement(['nacional', 'internacional']),
            // 'peso' => $this->faker->randomFloat(3, 0.1, 20),
            // 'dimensiones' => [
            //    'largo' => $this->faker->numberBetween(10, 100),
            //    'ancho' => $this->faker->numberBetween(10, 100),
            //    'alto' => $this->faker->numberBetween(5, 50),
            // ],
        ];
    }
}
