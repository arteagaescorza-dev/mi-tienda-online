<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Activa Row Level Security (RLS) en todas las tablas del esquema public en PostgreSQL/Supabase.
     *
     * En Supabase, el esquema `public` se expone por defecto al Data API (PostgREST).
     * Habilitar RLS sin políticas para `anon`/`authenticated` bloquea cualquier acceso
     * anónimo directo vía REST API, manteniendo acceso total para Laravel (rol `postgres`).
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $tables = DB::select("
            SELECT tablename
            FROM pg_tables
            WHERE schemaname = 'public'
        ");

        foreach ($tables as $table) {
            $name = str_replace('"', '""', $table->tablename);
            DB::statement("ALTER TABLE \"public\".\"{$name}\" ENABLE ROW LEVEL SECURITY");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $tables = DB::select("
            SELECT tablename
            FROM pg_tables
            WHERE schemaname = 'public'
        ");

        foreach ($tables as $table) {
            $name = str_replace('"', '""', $table->tablename);
            DB::statement("ALTER TABLE \"public\".\"{$name}\" DISABLE ROW LEVEL SECURITY");
        }
    }
};
