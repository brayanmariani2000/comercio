<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductoImagen extends Model
{
    use HasFactory;
    
    protected $table = 'producto_imagenes';

    protected $fillable = [
        'producto_id',
        'imagen',
        'orden',
        'principal',
        'titulo',
        'descripcion',
        'tipo',
        'tamaño',
        'formato',
        'ancho',
        'alto',
    ];

    protected $casts = [
        'principal' => 'boolean',
        'orden' => 'integer',
        'ancho' => 'integer',
        'alto' => 'integer',
    ];

    // RELACIONES
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // MÉTODOS
    public function getUrlAttribute()
    {
        return Storage::url($this->imagen);
    }

    public function getThumbnailUrlAttribute()
    {
        $path = str_replace('.', '_thumb.', $this->imagen);
        return Storage::exists($path) ? Storage::url($path) : $this->url;
    }

    public function getMediumUrlAttribute()
    {
        $path = str_replace('.', '_medium.', $this->imagen);
        return Storage::exists($path) ? Storage::url($path) : $this->url;
    }

    public function getLargeUrlAttribute()
    {
        $path = str_replace('.', '_large.', $this->imagen);
        return Storage::exists($path) ? Storage::url($path) : $this->url;
    }

    public function marcarComoPrincipal()
    {
        // Quitar principal de otras imágenes
        $this->producto->imagenes()->update(['principal' => false]);
        
        $this->principal = true;
        $this->save();
        
        return $this;
    }

    public function moverArriba()
    {
        $imagenAnterior = $this->producto->imagenes()
            ->where('orden', '<', $this->orden)
            ->orderBy('orden', 'desc')
            ->first();
            
        if ($imagenAnterior) {
            $ordenTemp = $imagenAnterior->orden;
            $imagenAnterior->orden = $this->orden;
            $imagenAnterior->save();
            
            $this->orden = $ordenTemp;
            $this->save();
        }
        
        return $this;
    }

    public function moverAbajo()
    {
        $imagenSiguiente = $this->producto->imagenes()
            ->where('orden', '>', $this->orden)
            ->orderBy('orden')
            ->first();
            
        if ($imagenSiguiente) {
            $ordenTemp = $imagenSiguiente->orden;
            $imagenSiguiente->orden = $this->orden;
            $imagenSiguiente->save();
            
            $this->orden = $ordenTemp;
            $this->save();
        }
        
        return $this;
    }

    public function obtenerInformacionArchivo()
    {
        $path = storage_path('app/public/' . $this->imagen);
        
        if (!file_exists($path)) {
            return null;
        }
        
        $size = filesize($path);
        $mime = mime_content_type($path);
        $dimensions = getimagesize($path);
        
        return [
            'tamaño_bytes' => $size,
            'tamaño_formateado' => $this->formatearTamaño($size),
            'mime_type' => $mime,
            'ancho' => $dimensions[0] ?? null,
            'alto' => $dimensions[1] ?? null,
            'ultima_modificacion' => filemtime($path),
        ];
    }

    private function formatearTamaño($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    // SCOPE
    public function scopePrincipales($query)
    {
        return $query->where('principal', true);
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('orden');
    }
}