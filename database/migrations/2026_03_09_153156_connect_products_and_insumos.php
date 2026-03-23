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
        // 1. PUENTE RETAIL: Añadimos una columna a los productos para enlazarlos a un insumo exacto (Ej: Coca-Cola)
        Schema::table('products', function (Blueprint $table) {
            // Usamos nullable porque una Hamburguesa NO es un producto retail
            $table->foreignId('insumo_id')->nullable()->constrained('insumos')->nullOnDelete();
        });
    
        // 2. PUENTE MODIFICADORES: Creamos una tabla pivote para los ingredientes extra (Ej: Llajua, Sin Tomate)
        // Esta tabla conecta 1 Hamburguesa con MUCHOS insumos estimados
        Schema::create('insumo_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
