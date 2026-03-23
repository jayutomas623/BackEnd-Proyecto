<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 
        'tipo_control', 
        'cantidad_exacta', 
        'estado',
        'unidad_medida'
    ];

    public function productoRetail()
    {
        return $this->hasOne(Product::class, 'insumo_id');
    }

    public function platosQueLoUsan()
    {
        return $this->belongsToMany(Product::class, 'insumo_product');
    }
}