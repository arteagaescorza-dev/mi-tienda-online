<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'discrete_packaging')) {
                $table->boolean('discrete_packaging')->default(true);
            }
            if (!Schema::hasColumn('users', 'anonymized_at')) {
                $table->timestamp('anonymized_at')->nullable();
            }
        });

        if (!Schema::hasTable('customer_addresses')) {
            Schema::create('customer_addresses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('label_type', 20)->default('casa');
                $table->string('street', 190);
                $table->string('postal_code', 10);
                $table->string('colony', 120);
                $table->string('city', 120);
                $table->string('state', 120);
                $table->string('references', 255)->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index(['user_id', 'is_default']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');

        Schema::table('users', function (Blueprint $table) {
            $cols = array_values(array_filter(
                ['discrete_packaging', 'anonymized_at'],
                fn (string $col) => Schema::hasColumn('users', $col)
            ));
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
