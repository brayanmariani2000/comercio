<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Vendedor;

class ProductoSeeder extends Seeder
{
    public function run()
    {
        // Crear categorias y vendedores si no existen suficientes
        $categorias = Categoria::count() < 5 
            ? Categoria::factory(10)->create() 
            : Categoria::all();
            
        $vendedores = Vendedor::count() < 3 
            ? Vendedor::factory(5)->create() 
            : Vendedor::all();
            
        // Crear 50 productos distribuidos
        Producto::factory(50)->make()->each(function ($producto) use ($categorias, $vendedores) {
            $producto->categoria_id = $categorias->random()->id;
            $producto->vendedor_id = $vendedores->random()->id;
            $producto->save();
        });
        
        $this->command->info('Productos sembrados correctamente!');
    }
}
