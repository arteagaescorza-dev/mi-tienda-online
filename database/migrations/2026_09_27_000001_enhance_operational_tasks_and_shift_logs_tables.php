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
        // 1. Ampliar campos en operational_tasks si no existen
        Schema::table('operational_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('operational_tasks', 'reference_code')) {
                $table->string('reference_code', 50)->nullable()->after('title');
            }
            if (!Schema::hasColumn('operational_tasks', 'description')) {
                $table->text('description')->nullable()->after('reference_code');
            }
            if (!Schema::hasColumn('operational_tasks', 'completed_by')) {
                $table->string('completed_by', 100)->nullable()->after('completed_at');
            }
        });

        // 2. Crear tabla shift_logs para bitácora inmutable de turno
        if (!Schema::hasTable('shift_logs')) {
            Schema::create('shift_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('operational_task_id')->nullable();
                $table->string('event_type', 50)->default('task_completed');
                $table->string('message', 255);
                $table->string('operator_name', 100)->default('Carlos A.');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['event_type', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_logs');

        Schema::table('operational_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('operational_tasks', 'completed_by')) {
                $table->dropColumn('completed_by');
            }
            if (Schema::hasColumn('operational_tasks', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('operational_tasks', 'reference_code')) {
                $table->dropColumn('reference_code');
            }
        });
    }
};
