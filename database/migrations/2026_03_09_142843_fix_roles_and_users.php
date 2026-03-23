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
        // 1. Apagamos la protección de llaves foráneas temporalmente
        Schema::disableForeignKeyConstraints();
    
        // 2. Ahora sí nos dejará borrarla sin problema
        Schema::dropIfExists('roles');
    
        // 3. La volvemos a crear de forma perfecta
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); 
            $table->timestamps();
        });
    
        // 4. Volvemos a encender la protección
        Schema::enableForeignKeyConstraints();
    
        // 5. Le añadimos la columna de rol a los usuarios si no la tienen
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->constrained('roles');
            }
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
