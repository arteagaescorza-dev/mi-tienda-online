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
        // 1. Curvas de Tallas Textiles
        if (!Schema::hasTable('apparel_size_curves')) {
            Schema::create('apparel_size_curves', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('category_scope')->nullable(); // textil, pantalones, lenceria, universal
                $table->text('description')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Tallas Individuales por Curva
        if (!Schema::hasTable('apparel_sizes')) {
            Schema::create('apparel_sizes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('size_curve_id')->constrained('apparel_size_curves')->onDelete('cascade');
                $table->string('code', 20); // CH, M, G, XL, 2XL, 28, 30, 32B
                $table->string('label', 100); // Chica / S, 32B Copa B
                $table->integer('sort_order')->default(0);
                $table->decimal('waist_min_cm', 5, 2)->nullable();
                $table->decimal('waist_max_cm', 5, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['size_curve_id', 'sort_order']);
            });
        }

        // 3. Formatos y Volúmenes de Perfumería / Cosmética Líquida
        if (!Schema::hasTable('fragrance_volumes')) {
            Schema::create('fragrance_volumes', function (Blueprint $table) {
                $table->id();
                $table->decimal('value', 6, 2); // 10.00, 50.00, 100.00
                $table->string('unit', 20)->default('ml'); // ml, oz, g
                $table->string('label', 100); // 100ml / 3.4 oz Full Size
                $table->string('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['sort_order', 'is_active']);
            });
        }

        // 4. Mejoras en Categorías (Activo/Inactivo y SoftDeletes)
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('parent_id');
                }
                if (!Schema::hasColumn('categories', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (Schema::hasColumn('categories', 'is_active')) {
                    $table->dropColumn('is_active');
                }
                if (Schema::hasColumn('categories', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }

        Schema::dropIfExists('fragrance_volumes');
        Schema::dropIfExists('apparel_sizes');
        Schema::dropIfExists('apparel_size_curves');
    }
};
