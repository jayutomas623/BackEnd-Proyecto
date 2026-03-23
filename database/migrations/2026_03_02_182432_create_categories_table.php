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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: Desayunos, Almuerzos, Bebidas
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable(); // URL o ruta de la imagen representativa
            $table->integer('orden')->default(0); // Para controlar en qué orden se muestran
            $table->boolean('estado')->default(true); // Activa o inactiva
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
