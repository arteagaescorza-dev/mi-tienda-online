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
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'universe')) {
                $table->string('universe', 30)->default('terra')->after('phone')->nullable();
            }
            if (!Schema::hasColumn('leads', 'lifecycle_stage')) {
                $table->string('lifecycle_stage', 30)->default('prospect')->after('status')->nullable();
            }
            if (!Schema::hasColumn('leads', 'lead_score')) {
                $table->unsignedSmallInteger('lead_score')->default(50)->after('lifecycle_stage')->nullable();
            }
            if (!Schema::hasColumn('leads', 'last_interaction_at')) {
                $table->timestamp('last_interaction_at')->after('last_contacted_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['universe', 'lifecycle_stage', 'lead_score', 'last_interaction_at'] as $col) {
                if (Schema::hasColumn('leads', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

