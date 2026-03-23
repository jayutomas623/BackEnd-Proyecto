<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 
        'nombre', 
        'descripcion', 
        'precio', 
        'imagen', 
        'disponibilidad',
        'insumo_id' 
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function insumoRetail()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }

    public function modificadores()
    {
        return $this->belongsToMany(Insumo::class, 'insumo_product');
    }
}