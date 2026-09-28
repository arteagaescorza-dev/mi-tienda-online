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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('vendor_code', 50)->nullable()->after('name');
            $table->string('contact_name', 150)->nullable()->after('website');
            $table->string('phone', 50)->nullable()->after('contact_email');
            $table->string('city', 100)->nullable()->after('phone');
            $table->integer('lead_time_days')->default(3)->after('city');
            $table->string('payment_terms', 50)->default('contado')->after('lead_time_days');
            $table->string('status', 30)->default('connected')->after('payment_terms');
            $table->string('extraction_method', 100)->nullable()->default('Manual / Facturacion Directa')->after('status');
            $table->decimal('sla_on_time_pct', 5, 2)->default(98.00)->after('extraction_method');
            $table->decimal('sla_fill_rate_pct', 5, 2)->default(98.00)->after('sla_on_time_pct');
            $table->decimal('avg_delay_days', 4, 2)->default(0.00)->after('sla_fill_rate_pct');

            $table->index(['status', 'type']);
            $table->index('vendor_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex(['status', 'type']);
            $table->dropIndex(['vendor_code']);
            $table->dropColumn([
                'vendor_code',
                'contact_name',
                'phone',
                'city',
                'lead_time_days',
                'payment_terms',
                'status',
                'extraction_method',
                'sla_on_time_pct',
                'sla_fill_rate_pct',
                'avg_delay_days',
            ]);
        });
    }
};
