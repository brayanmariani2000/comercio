<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodigoVerificacion extends Model
{
    use HasFactory;

    protected $table = 'codigos_verificacion';

    protected $fillable = [
        'email',
        'codigo',
        'tipo',
        'expiracion',
        'verificado',
        'fecha_verificacion',
    ];

    protected $casts = [
        'expiracion' => 'datetime',
        'fecha_verificacion' => 'datetime',
        'verificado' => 'boolean',
    ];

    // MÉTODOS
    public function estaExpirado()
    {
        return $this->expiracion < now();
    }

    public function estaVerificado()
    {
        return $this->verificado;
    }

    public function puedeUsarse()
    {
        return !$this->estaExpirado() && !$this->estaVerificado();
    }

    public function marcarComoVerificado()
    {
        $this->verificado = true;
        $this->fecha_verificacion = now();
        $this->save();
    }

    // SCOPE
    public function scopeValidos($query)
    {
        return $query->where('verificado', false)
            ->where('expiracion', '>', now());
    }

    public function scopePorEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeNoVerificados($query)
    {
        return $query->where('verificado', false);
    }
}
