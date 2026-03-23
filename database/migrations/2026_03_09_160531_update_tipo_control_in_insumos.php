<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ampliamos la lista temporalmente para que acepte tanto las opciones viejas como las nuevas
        DB::statement("ALTER TABLE insumos MODIFY COLUMN tipo_control ENUM('exacto', 'estimado', 'a_granel', 'extra') NOT NULL DEFAULT 'estimado'");
    
        // 2. Ahora que 'a_granel' ya es una opción válida, movemos todos los productos viejos hacia ahí
        DB::statement("UPDATE insumos SET tipo_control = 'a_granel' WHERE tipo_control = 'estimado'");
    
        // 3. Finalmente, limpiamos la lista dejando solo tus 3 nuevas opciones oficiales
        DB::statement("ALTER TABLE insumos MODIFY COLUMN tipo_control ENUM('exacto', 'a_granel', 'extra') NOT NULL DEFAULT 'a_granel'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            //
        });
    }
};
