<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('direccion')->nullable()->after('telefono');
            $table->date('fecha_nacimiento')->nullable()->after('direccion');
            $table->date('fecha_contratacion')->nullable()->after('fecha_nacimiento');
            $table->string('contacto_emergencia_nombre')->nullable()->after('fecha_contratacion');
            $table->string('contacto_emergencia_telefono')->nullable()->after('contacto_emergencia_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'direccion',
                'fecha_nacimiento',
                'fecha_contratacion',
                'contacto_emergencia_nombre',
                'contacto_emergencia_telefono',
            ]);
        });
    }
};