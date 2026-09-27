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
        Schema::table('support_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('support_cases', 'case_type')) {
                $table->string('case_type', 50)->default('inquiry')->after('subject');
            }
            if (!Schema::hasColumn('support_cases', 'metadata')) {
                $table->json('metadata')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_cases', function (Blueprint $table) {
            if (Schema::hasColumn('support_cases', 'metadata')) {
                $table->dropColumn('metadata');
            }
            if (Schema::hasColumn('support_cases', 'case_type')) {
                $table->dropColumn('case_type');
            }
        });
    }
};
