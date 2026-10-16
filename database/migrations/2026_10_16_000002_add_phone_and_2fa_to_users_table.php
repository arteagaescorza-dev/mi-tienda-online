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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->after('email')->index();
            }
            if (!Schema::hasColumn('users', 'two_factor_confirmed')) {
                $table->boolean('two_factor_confirmed')->default(false)->after('role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('users', 'phone')) {
                $cols[] = 'phone';
            }
            if (Schema::hasColumn('users', 'two_factor_confirmed')) {
                $cols[] = 'two_factor_confirmed';
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};

