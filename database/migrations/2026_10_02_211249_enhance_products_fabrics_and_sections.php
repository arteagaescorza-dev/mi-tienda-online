<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'fabric_id')) {
                $table->foreignId('fabric_id')->nullable()->after('brand')->constrained('apparel_fabrics')->nullOnDelete();
            }
            if (!Schema::hasColumn('products', 'fabric')) {
                $table->string('fabric', 120)->nullable()->after('fabric_id');
            }
            if (!Schema::hasColumn('products', 'section_name')) {
                $table->string('section_name', 120)->nullable()->after('fabric');
            }
        });

        // 1. Asegurar Telas Semilla en apparel_fabrics
        $fabrics = [
            ['name' => 'Modal Seamless', 'description' => 'Tejido elástico sin costuras de alta transpirabilidad.'],
            ['name' => 'Seda / Satín Premium', 'description' => 'Acabado brillante de lujo para lencería de autor.'],
            ['name' => 'Algodón Pima Peruano', 'description' => 'Fibras extra largas de alta suavidad y resistencia.'],
            ['name' => 'Micro-Modal Ecológico', 'description' => 'Tejido ultra sedoso transpirable a base de haya.'],
            ['name' => 'Microfibra Deportiva Dry-Fit', 'description' => 'Secado rápido para arneses y suspensorios.'],
            ['name' => 'Malla Elástica Mesh Transpirable', 'description' => 'Tejido de red ligera para línea ergonómica.'],
            ['name' => 'Spandex / Elastano 4-Way', 'description' => 'Compresión afirmativa y ajuste dinámico.'],
        ];

        foreach ($fabrics as $f) {
            $slug = Str::slug($f['name']);
            if (!DB::table('apparel_fabrics')->where('name', $f['name'])->exists()) {
                DB::table('apparel_fabrics')->insert([
                    'name' => $f['name'],
                    'slug' => $slug,
                    'description' => $f['description'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Asegurar Secciones Oficiales de E-Commerce en cms_sections (Regla 7)
        $sections = [
            [
                'name' => 'Línea Básicos Esenciales',
                'title' => 'LÍNEA BÁSICOS ESENCIALES',
                'subtitle' => 'Prendas lisas de alta rotación',
                'type' => 'catalog_section',
                'universe' => 'all',
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'name' => 'Línea Diseñador & Autor',
                'title' => 'LÍNEA DISEÑADOR & AUTOR',
                'subtitle' => 'Prendas con diseño, estampados y tornasoles',
                'type' => 'catalog_section',
                'universe' => 'all',
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'name' => 'Colección Temporada / Drops',
                'title' => 'COLECCIÓN TEMPORADA / DROPS',
                'subtitle' => 'Lanzamientos de edición limitada',
                'type' => 'catalog_section',
                'universe' => 'all',
                'status' => 'active',
                'sort_order' => 3,
            ],
            [
                'name' => 'Catálogo General',
                'title' => 'CATÁLOGO GENERAL',
                'subtitle' => 'Vitrina multiverso estándar',
                'type' => 'catalog_section',
                'universe' => 'all',
                'status' => 'active',
                'sort_order' => 4,
            ],
        ];

        foreach ($sections as $s) {
            if (!DB::table('cms_sections')->where('name', $s['name'])->exists()) {
                DB::table('cms_sections')->insert([
                    'universe' => $s['universe'],
                    'name' => $s['name'],
                    'type' => $s['type'],
                    'title' => $s['title'],
                    'subtitle' => $s['subtitle'],
                    'badge' => 'E-Commerce',
                    'query_rules' => json_encode(['section' => $s['name']]),
                    'sort_order' => $s['sort_order'],
                    'status' => $s['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'fabric_id')) {
                $table->dropForeign(['fabric_id']);
                $table->dropColumn('fabric_id');
            }
            if (Schema::hasColumn('products', 'fabric')) {
                $table->dropColumn('fabric');
            }
            if (Schema::hasColumn('products', 'section_name')) {
                $table->dropColumn('section_name');
            }
        });
    }
};
