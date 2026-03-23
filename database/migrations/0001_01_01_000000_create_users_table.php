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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles'); // Relación con la tabla roles 
            $table->string('name'); // Equivale a 'nombre_completo' en tu documento 
            $table->string('email')->unique(); // Equivale a 'correo' 
            $table->string('password'); // Contraseña 
            $table->string('ci')->nullable(); // Carnet de identidad [cite: 786]
            $table->string('telefono')->nullable(); // Número de teléfono [cite: 585, 1324]
            $table->boolean('estado')->default(true); // Cuenta activa o inactiva [cite: 1324, 1348]
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps(); // Genera automáticamente los campos de fecha
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
