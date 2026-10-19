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
        if (Schema::hasTable('allies')) {
            Schema::table('allies', function (Blueprint $table) {
                if (!Schema::hasColumn('allies', 'allowed_discount_types')) {
                    $table->json('allowed_discount_types')->nullable()->after('notes');
                }
                if (!Schema::hasColumn('allies', 'max_discount_pct')) {
                    $table->decimal('max_discount_pct', 5, 2)->default(20.00)->after('allowed_discount_types');
                }
            });
        }

        if (Schema::hasTable('ally_campaigns_dir_a')) {
            Schema::table('ally_campaigns_dir_a', function (Blueprint $table) {
                if (!Schema::hasColumn('ally_campaigns_dir_a', 'discount_type')) {
                    $table->string('discount_type', 50)->nullable()->after('benefit');
                }
                if (!Schema::hasColumn('ally_campaigns_dir_a', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('allies')) {
            Schema::table('allies', function (Blueprint $table) {
                if (Schema::hasColumn('allies', 'max_discount_pct')) {
                    $table->dropColumn('max_discount_pct');
                }
                if (Schema::hasColumn('allies', 'allowed_discount_types')) {
                    $table->dropColumn('allowed_discount_types');
                }
            });
        }

        if (Schema::hasTable('ally_campaigns_dir_a')) {
            Schema::table('ally_campaigns_dir_a', function (Blueprint $table) {
                if (Schema::hasColumn('ally_campaigns_dir_a', 'rejection_reason')) {
                    $table->dropColumn('rejection_reason');
                }
                if (Schema::hasColumn('ally_campaigns_dir_a', 'discount_type')) {
                    $table->dropColumn('discount_type');
                }
            });
        }
    }
};
