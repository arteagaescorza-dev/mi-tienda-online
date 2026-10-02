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
            $table->string('tax_id', 50)->nullable()->after('vendor_code'); // RFC / Tax ID / NIF
            $table->string('legal_name', 255)->nullable()->after('name'); // Razon Social Fiscal
            $table->string('fiscal_regime', 150)->nullable()->after('tax_id'); // Regimen Fiscal
            $table->text('fiscal_address')->nullable()->after('fiscal_regime'); // Domicilio Fiscal Completo
            $table->string('fiscal_zip', 20)->nullable()->after('fiscal_address'); // Codigo Postal Fiscal
            $table->string('invoice_email', 150)->nullable()->after('contact_email'); // Email para Facturacion CFDI
            $table->string('origin', 30)->default('manual')->after('type'); // 'manual' o 'scraping'
            $table->json('brands')->nullable()->after('credentials'); // Marcas comerciales que se desprenden del proveedor
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'tax_id',
                'legal_name',
                'fiscal_regime',
                'fiscal_address',
                'fiscal_zip',
                'invoice_email',
                'origin',
                'brands',
            ]);
        });
    }
};
