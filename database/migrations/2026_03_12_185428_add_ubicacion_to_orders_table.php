<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('ubicacion_tipo', ['mesa', 'rincon'])->nullable()->after('tipo_pago');
            $table->unsignedTinyInteger('ubicacion_numero')->nullable()->after('ubicacion_tipo');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['ubicacion_tipo', 'ubicacion_numero']);
        });
    }
};