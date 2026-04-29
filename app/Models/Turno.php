<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'user_id', 'monto_inicial', 'monto_cierre_esperado',
        'monto_cierre_real', 'diferencia',
        'abierto_en', 'cerrado_en', 'estado', 'observaciones',
    ];
    
    protected $casts = [
        'abierto_en' => 'datetime',
        'cerrado_en' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}