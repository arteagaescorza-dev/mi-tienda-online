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
        // 1. Ampliar package_types con punto de reorden ROP y familia de catalogo (TASK-INT-13 / TASK-LOG-06)
        if (Schema::hasTable('package_types')) {
            Schema::table('package_types', function (Blueprint $table) {
                if (!Schema::hasColumn('package_types', 'reorder_point')) {
                    $table->integer('reorder_point')->default(40)->after('stock_available');
                }
                if (!Schema::hasColumn('package_types', 'family')) {
                    $table->string('family', 60)->default('all')->after('type');
                }
            });
        }

        // 2. Asociar tipo de empaque a shipments para trazabilidad
        if (Schema::hasTable('shipments')) {
            Schema::table('shipments', function (Blueprint $table) {
                if (!Schema::hasColumn('shipments', 'package_type_id')) {
                    $table->unsignedBigInteger('package_type_id')->nullable()->after('order_id');
                    $table->foreign('package_type_id')->references('id')->on('package_types')->nullOnDelete();
                }
                if (!Schema::hasColumn('shipments', 'packaging_fulfilled_at')) {
                    $table->timestamp('packaging_fulfilled_at')->nullable()->after('status');
                }
            });
        }

        // 3. Crear Kardex de Movimientos de Insumos de Empaque (TASK-INT-13)
        if (!Schema::hasTable('packaging_movements')) {
            Schema::create('packaging_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('package_type_id');
                $table->unsignedBigInteger('shipment_id')->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('type', 40)->default('CONSUMO_EMPAQUE_ENVIO'); // CONSUMO_EMPAQUE_ENVIO, AJUSTE_INVENTARIO, ENTRADA_REABASTECIMIENTO
                $table->integer('quantity'); // ej: -1 para consumo, +50 para reabastecimiento
                $table->integer('stock_before');
                $table->integer('stock_after');
                $table->string('notes', 255)->nullable();
                $table->timestamps();

                $table->index(['package_type_id', 'type']);
                $table->foreign('package_type_id')->references('id')->on('package_types')->cascadeOnDelete();
                $table->foreign('shipment_id')->references('id')->on('shipments')->nullOnDelete();
                $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packaging_movements');

        if (Schema::hasTable('shipments')) {
            Schema::table('shipments', function (Blueprint $table) {
                if (Schema::hasColumn('shipments', 'package_type_id')) {
                    $table->dropForeign(['package_type_id']);
                    $table->dropColumn('package_type_id');
                }
                if (Schema::hasColumn('shipments', 'packaging_fulfilled_at')) {
                    $table->dropColumn('packaging_fulfilled_at');
                }
            });
        }

        if (Schema::hasTable('package_types')) {
            Schema::table('package_types', function (Blueprint $table) {
                if (Schema::hasColumn('package_types', 'reorder_point')) {
                    $table->dropColumn('reorder_point');
                }
                if (Schema::hasColumn('package_types', 'family')) {
                    $table->dropColumn('family');
                }
            });
        }
    }
};
