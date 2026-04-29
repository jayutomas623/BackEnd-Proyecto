<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('monto_inicial', 10, 2)->default(0);
            $table->decimal('monto_cierre_esperado', 10, 2)->nullable();
            $table->decimal('monto_cierre_real', 10, 2)->nullable();
            $table->decimal('diferencia', 10, 2)->nullable();
            $table->timestamp('abierto_en')->useCurrent();
            $table->timestamp('cerrado_en')->nullable();
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
            $table->text('observaciones')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};