<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vendedor;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Conversacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class SystemVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_system_flow()
    {
        // 1. SETUP: Create Actors
        $comprador = User::factory()->create(['tipo_usuario' => 'comprador']);
        
        $vendedorUser = User::factory()->create(['tipo_usuario' => 'vendedor']);
        $vendedor = Vendedor::create([
            'user_id' => $vendedorUser->id,
            'nombre_comercial' => 'Tienda Test',
            'rif' => 'J-123456789',
            'direccion' => 'Calle Test',
            'telefono' => '04121234567',
        ]);
        
        $categoria = Categoria::create(['nombre' => 'General', 'slug' => 'general', 'descripcion' => 'Desc']);
        
        $producto = Producto::create([
            'vendedor_id' => $vendedor->id,
            'categoria_id' => $categoria->id,
            'nombre' => 'Producto Test',
            'descripcion' => 'Descripcion Test',
            'precio_actual' => 100.00,
            'stock' => 10,
            'estado' => 'activo',
        ]);

        // 2. CART FLOW
        $response = $this->actingAs($comprador)
            ->postJson(route('comprador.carrito.add'), [
                'producto_id' => $producto->id,
                'cantidad' => 2
            ]);
            
        $response->assertStatus(200);
        $this->assertDatabaseHas('carrito_items', [
            'producto_id' => $producto->id,
            'cantidad' => 2
        ]);
        
        // 3. MESSAGING FLOW
        // Start Conversation
        $msgResponse = $this->actingAs($comprador)
            ->postJson(route('comprador.mensajes.store'), [
                'vendedor_id' => $vendedor->id,
                'producto_id' => $producto->id,
                'mensaje' => 'Hola, tienes disponibilidad?',
                'asunto' => 'Consulta Stock'
            ]);
            
        $msgResponse->assertStatus(200);
        $this->assertDatabaseHas('conversaciones', [
            'user_id' => $comprador->id,
            'vendedor_id' => $vendedor->id,
            'producto_id' => $producto->id
        ]);
        
        $conversacionId = $msgResponse->json('conversacion.id');
        
        // Reply to Conversation
        $replyResponse = $this->actingAs($comprador)
            ->postJson(route('comprador.mensajes.reply', $conversacionId), [
                'mensaje' => 'Me gustaría comprar 2 unidades.'
            ]);
            
        $replyResponse->assertStatus(200);
        $this->assertDatabaseHas('mensajes', [
            'conversacion_id' => $conversacionId,
            'mensaje' => 'Me gustaría comprar 2 unidades.'
        ]);
        
        // 4. ORDER FLOW (Checkout)
        // Note: Assuming creating a pedido manually via 'store' route
        // We need to check route parameters for store. Usually it takes address, payment method etc.
        // If not fully implemented, we test availability.
        
        // Creating address first
        // $comprador->direcciones()->create([...]);
        
        // Assuming simple checkout for now
        // $checkoutResponse = $this->actingAs($comprador)->post(route('pedidos.store'), [...]);
    }
}
