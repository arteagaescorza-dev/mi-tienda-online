<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tipos de Aliado Comercial / Categorías
        if (!Schema::hasTable('ally_types')) {
            Schema::create('ally_types', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->string('default_universe', 30)->default('marte');
                $table->boolean('is_active')->default(true);
                $table->string('status', 20)->default('active'); // 'active' | 'archived'
                $table->softDeletes();
                $table->timestamps();

                $table->index(['status', 'is_active']);
            });

            // Semillas iniciales deterministas de tipos de aliado
            $defaultTypes = [
                [
                    'code' => 'sauna',
                    'name' => 'Sauna & Baños de Vapor',
                    'description' => 'Establecimientos de sauna, vapor, spa y wellness para audiencia de relajación y estilo de vida.',
                    'default_universe' => 'marte',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'circuit_party',
                    'name' => 'Festival Circuit & Productor de Fiestas',
                    'description' => 'Festivales nocturnos, eventos masivos circuit y fiestas de temática con venta de boletos/cover.',
                    'default_universe' => 'marte',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'onlyfans_creator',
                    'name' => 'Creador de Contenido & Modelo',
                    'description' => 'Influencers, creadores digitales y modelos con base de seguidores activa en redes sociales.',
                    'default_universe' => 'marte',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'beauty_spa',
                    'name' => 'Boutique, Belleza & Spa',
                    'description' => 'Boutiques de diseño, spas de belleza y cuidado personal enfocados en moda y bienestar.',
                    'default_universe' => 'venus',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'drag_ballroom',
                    'name' => 'Ballroom & Drag House',
                    'description' => 'Casas de ballroom, colectivos artísticos drag y productores de pasarelas culturales.',
                    'default_universe' => 'tiresias',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'fitness_gym',
                    'name' => 'Fitness, Gimnasio & Entrenadores',
                    'description' => 'Gimnasios especializados, centros de crossfit y entrenadores personales de alto rendimiento.',
                    'default_universe' => 'marte',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'bar_nightclub',
                    'name' => 'Bar, Club & Vida Nocturna',
                    'description' => 'Bares cocteleros, terrazas lounge y clubes nocturnos con promociones de hospitalidad.',
                    'default_universe' => 'marte',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'general',
                    'name' => 'Alianza Comercial General',
                    'description' => 'Empresas y marcas comerciales con acuerdos generales de intercambio de beneficios.',
                    'default_universe' => 'marte',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            DB::table('ally_types')->insert($defaultTypes);
        }

        // 2. Tipos de Descuento (Dirección A y Dirección B)
        if (!Schema::hasTable('ally_discount_types')) {
            Schema::create('ally_discount_types', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 150);
                $table->string('direction', 20)->default('dir_a'); // 'dir_a' | 'dir_b' | 'both'
                $table->string('value_type', 30)->default('percentage'); // 'percentage' | 'fixed_amount' | 'free_item' | 'access'
                $table->decimal('max_value_default', 8, 2)->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('status', 20)->default('active'); // 'active' | 'archived'
                $table->softDeletes();
                $table->timestamps();

                $table->index(['direction', 'status', 'is_active']);
            });

            // Semillas iniciales deterministas de tipos de descuento
            $defaultDiscounts = [
                // Dirección A: Beneficios en evento / establecimiento del aliado
                [
                    'code' => 'cover',
                    'name' => 'Cover Libre (100% Cortesía)',
                    'direction' => 'dir_a',
                    'value_type' => 'free_item',
                    'max_value_default' => 100.00,
                    'description' => 'Entrada gratuita / cover libre en el establecimiento o evento del socio aliado.',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'cover_2x1',
                    'name' => 'Cover 2x1 en Puerta',
                    'direction' => 'dir_a',
                    'value_type' => 'percentage',
                    'max_value_default' => 50.00,
                    'description' => 'Promoción de 2x1 en cover o taquilla de acceso.',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'free_drink',
                    'name' => 'Trago de Cortesía',
                    'direction' => 'dir_a',
                    'value_type' => 'free_item',
                    'max_value_default' => 0.00,
                    'description' => 'Trago o bebida de cortesía al ingresar al establecimiento con voucher verificado.',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'vip_access',
                    'name' => 'Acceso VIP / Pulsera Preferente',
                    'direction' => 'dir_a',
                    'value_type' => 'access',
                    'max_value_default' => 0.00,
                    'description' => 'Brazalete o pulsera de acceso preferencial / zona VIP en el evento del aliado.',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'service_promo',
                    'name' => 'Promo en Servicio o Tratamiento',
                    'direction' => 'dir_a',
                    'value_type' => 'percentage',
                    'max_value_default' => 20.00,
                    'description' => 'Descuento especial en cartas de servicio, cabinas o tratamientos en instalaciones del socio.',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Dirección B: Beneficios en Tienda Online para comunidad del aliado
                [
                    'code' => 'store_discount',
                    'name' => '% Descuento en Tienda Online',
                    'direction' => 'dir_b',
                    'value_type' => 'percentage',
                    'max_value_default' => 20.00,
                    'description' => 'Porcentaje de descuento sobre catálogo en compras online con cupón UTM del aliado.',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'free_shipping',
                    'name' => 'Envío Gratis en Pedido Online',
                    'direction' => 'dir_b',
                    'value_type' => 'free_item',
                    'max_value_default' => 0.00,
                    'description' => 'Exención de costo de flete en órdenes enviadas con el código exclusivo del socio.',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'gift_purchase',
                    'name' => 'Obsequio o Prenda de Regalo',
                    'direction' => 'dir_b',
                    'value_type' => 'free_item',
                    'max_value_default' => 0.00,
                    'description' => 'Prenda o accesorio de cortesía añadido al paquete por compra mínima referida.',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'exclusive_presale',
                    'name' => 'Acceso Anticipado a Preventa',
                    'direction' => 'dir_b',
                    'value_type' => 'access',
                    'max_value_default' => 0.00,
                    'description' => 'Acceso temprano a lanzamientos de colecciones y prendas de edición limitada.',
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            DB::table('ally_discount_types')->insert($defaultDiscounts);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ally_discount_types');
        Schema::dropIfExists('ally_types');
    }
};

