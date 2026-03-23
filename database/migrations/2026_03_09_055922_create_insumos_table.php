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
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: Coca-Cola personal o Carne Molida
            
            // El diferenciador clave que propusiste:
            $table->enum('tipo_control', ['exacto', 'estimado']); 
            
            // 1. Para el control "Exacto" (Retail/Envasados)
            $table->integer('cantidad_exacta')->nullable()->default(0); 
            
            // 2. Para el control "Estimado" (Manual/A granel)
            $table->enum('estado', ['disponible', 'pronta_reposicion', 'agotado'])->default('disponible');
            
            $table->string('unidad_medida')->nullable(); // Ej: unidades, kg, bultos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};
