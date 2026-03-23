<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Quién hizo el pedido
            $table->decimal('total', 10, 2); // Total a pagar
            $table->string('tipo_pago')->default('efectivo'); // 'efectivo' o 'qr'
            // Los estados que definiste para el seguimiento:
            $table->enum('estado', ['espera', 'preparando', 'listo', 'entregado', 'cancelado'])->default('espera'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
