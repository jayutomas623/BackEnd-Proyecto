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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories'); // Relación con la categoría
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2); // Precio con 2 decimales
            $table->string('imagen')->nullable();
            $table->integer('tiempo_preparacion')->default(0); // Tiempo estimado en minutos
            $table->boolean('disponibilidad')->default(true); // ¿Hay stock para vender hoy?
            $table->integer('dias_sin_venta')->default(0); // Dato clave para tu IA y combos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
