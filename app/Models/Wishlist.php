<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    protected $table = 'wishlists';

    protected $fillable = [
        'user_id',
        'nombre',
        'descripcion',
        'predeterminada',
        'publica',
    ];

    protected $casts = [
        'predeterminada' => 'boolean',
        'publica' => 'boolean',
    ];

    // RELACIONES
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'wishlist_items')
            ->withPivot('notas', 'prioridad')
            ->withTimestamps();
    }

    // MÉTODOS
    public function agregarProducto($productoId, $notas = null, $prioridad = 'media')
    {
        // Verificar si ya existe
        if ($this->productos()->where('producto_id', $productoId)->exists()) {
            return false;
        }

        return $this->items()->create([
            'producto_id' => $productoId,
            'notas' => $notas,
            'prioridad' => $prioridad,
        ]);
    }

    public function eliminarProducto($productoId)
    {
        return $this->items()->where('producto_id', $productoId)->delete();
    }

    public function tieneProducto($productoId)
    {
        return $this->items()->where('producto_id', $productoId)->exists();
    }

    public function vaciar()
    {
        return $this->items()->delete();
    }

    public function contarProductos()
    {
        return $this->items()->count();
    }

    // SCOPE
    public function scopePredeterminada($query)
    {
        return $query->where('predeterminada', true);
    }

    public function scopePublicas($query)
    {
        return $query->where('publica', true);
    }

    public function scopeDelUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
