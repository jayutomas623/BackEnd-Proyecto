<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    public $timestamps  = false;
    const CREATED_AT    = 'created_at';
    const UPDATED_AT    = null;

    protected $fillable = [
        'order_id', 'user_id', 'estado_anterior',
        'estado_nuevo', 'accion', 'detalle',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}