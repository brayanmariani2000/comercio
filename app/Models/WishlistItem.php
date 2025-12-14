<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
{
    use HasFactory;

    protected $table = 'wishlist_items';

    protected $fillable = [
        'wishlist_id',
        'producto_id',
        'notas',
        'prioridad',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // RELACIONES
    public function wishlist()
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // MÉTODOS
    public function actualizarNotas($notas)
    {
        $this->notas = $notas;
        $this->save();
    }

    public function cambiarPrioridad($prioridad)
    {
        $this->prioridad = $prioridad;
        $this->save();
    }

    public function productoDisponible()
    {
        return $this->producto && $this->producto->activo && $this->producto->stock > 0;
    }

    // SCOPE
    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    public function scopeConProducto($query)
    {
        return $query->with(['producto' => function($q) {
            $q->with(['imagenes', 'vendedor']);
        }]);
    }
}
