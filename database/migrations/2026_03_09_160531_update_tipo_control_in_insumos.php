<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar constraint anterior si existe
        DB::statement("ALTER TABLE insumos DROP CONSTRAINT IF EXISTS insumos_tipo_control_check");

        // Migrar valores 'estimado' a 'a_granel'
        DB::statement("UPDATE insumos SET tipo_control = 'a_granel' WHERE tipo_control = 'estimado'");

        // Aplicar nuevo constraint con los 3 valores válidos
        DB::statement("ALTER TABLE insumos ADD CONSTRAINT insumos_tipo_control_check CHECK (tipo_control IN ('exacto', 'a_granel', 'extra'))");

        // Cambiar el default
        DB::statement("ALTER TABLE insumos ALTER COLUMN tipo_control SET DEFAULT 'a_granel'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE insumos DROP CONSTRAINT IF EXISTS insumos_tipo_control_check");
        DB::statement("ALTER TABLE insumos ALTER COLUMN tipo_control SET DEFAULT 'estimado'");
    }
};