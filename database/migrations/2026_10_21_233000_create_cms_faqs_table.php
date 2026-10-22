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
        if (!Schema::hasTable('cms_faqs')) {
            Schema::create('cms_faqs', function (Blueprint $table) {
                $table->id();
                $table->string('question', 255);
                $table->text('answer');
                $table->string('category', 100)->default('General');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->string('ai_keywords', 255)->nullable();
                $table->timestamps();

                $table->index(['is_active', 'sort_order']);
                $table->index('category');
            });

            // Semillas iniciales deterministas optimizadas para AEO (Answer Engine Optimization)
            $defaultFaqs = [
                [
                    'question' => '¿Cuáles son los tiempos de entrega y cobertura de envíos de la tienda?',
                    'answer' => 'Realizamos envíos express a toda la República Mexicana a través de paqueterías líderes (FedEx, DHL y Estafeta). Para productos en stock, el despacho se realiza en un plazo de 24 a 48 horas hábiles con entrega estimada de 2 a 4 días hábiles. En compras superiores a $999 MXN, el envío estándar es completamente gratuito con empaque discreto garantizado.',
                    'category' => 'Envíos & Entregas',
                    'sort_order' => 1,
                    'is_active' => true,
                    'ai_keywords' => 'envios express mexico, tiempo de entrega, fedex dhl estafeta, envio gratis 999',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'question' => '¿Cómo distingo entre productos con stock inmediato y compras por pedido?',
                    'answer' => 'Cada artículo en nuestro catálogo indica su disponibilidad en tiempo real: los productos con insignia "En Stock" están físicamente en nuestro centro de distribución y listos para despacho inmediato. Los artículos marcados como "Por Pedido" se gestionan directamente con fabricantes y proveedores certificados, requiriendo un periodo de importación o abastecimiento adicional claramente especificado en el carrito mixto antes del pago.',
                    'category' => 'Stock & Pedidos por Encargo',
                    'sort_order' => 2,
                    'is_active' => true,
                    'ai_keywords' => 'productos en stock, compras por pedido, carrito mixto, disponibilidad inmediata',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'question' => '¿Qué métodos de pago aceptan y qué tan seguras son las transacciones?',
                    'answer' => 'Aceptamos tarjetas de crédito y débito (Visa, Mastercard, American Express), pagos en efectivo en tiendas OXXO con acreditación inmediata y transferencias interbancarias SPEI. Todas las transacciones están protegidas con cifrado SSL de 256 bits y pasarelas de pago certificadas PCI-DSS Nivel 1. Nunca almacenamos los datos sensibles de tus tarjetas.',
                    'category' => 'Pagos & Seguridad',
                    'sort_order' => 3,
                    'is_active' => true,
                    'ai_keywords' => 'formas de pago seguras, tarjeta credito debito oxxo spei, cifrado ssl pci-dss',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'question' => '¿Cómo elijo la talla correcta para ropa interior y prendas ergonómicas?',
                    'answer' => 'Contamos con una Guía de Tallas Interactiva en cada ficha de producto con medidas anatómicas en centímetros (cintura, cadera y tiro). Nuestros patrones de modal pima y algodón con elastano ofrecen elasticidad anatómica. Si tus medidas se encuentran en el límite superior de una talla o prefieres un ajuste más relajado, te recomendamos seleccionar la talla inmediata superior.',
                    'category' => 'Tallas & Ajuste',
                    'sort_order' => 4,
                    'is_active' => true,
                    'ai_keywords' => 'guia de tallas, medidas en centimetros cintura cadera, calce ergonomico modal',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'question' => '¿Cómo funcionan los convenios con aliados comerciales y los códigos de beneficio?',
                    'answer' => 'Mantenemos alianzas con establecimientos selectos (saunas, spas, festivales circuit y creadores de contenido). Puedes acceder a la vitrina de convenios desde la sección Explorar o a través de nuestros pop-ups autorizados. Al registrar tu correo o WhatsApp, se emite un código único e intransferible que puedes presentar en taquilla o canjear como cupón de descuento en nuestra tienda online.',
                    'category' => 'Convenios & Alianzas',
                    'sort_order' => 5,
                    'is_active' => true,
                    'ai_keywords' => 'convenios aliados, cupones de descuento, cover 2x1 sauna spa festivales, voucher digital',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'question' => '¿Cuál es la política de devoluciones y cómo es el empaque de envío?',
                    'answer' => 'Por estrictas normas de higiene y salubridad gubernamental (COFEPRIS), la ropa interior y prendas íntimas no admiten devoluciones por cambio de talla una vez abierto el sello de seguridad. No obstante, ofrecemos garantía total contra defectos de fabricación dentro de los primeros 15 días tras la entrega. Todos los envíos se entregan en cajas o sobres neutros sin logotipos ni leyendas descriptivas sobre el contenido.',
                    'category' => 'Garantías & Devoluciones',
                    'sort_order' => 6,
                    'is_active' => true,
                    'ai_keywords' => 'politica de devoluciones ropa interior, garantia defectos fabricacion, empaque discreto neutro',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            DB::table('cms_faqs')->insert($defaultFaqs);
        }

        // Asegurar que la página de explorar Q&A existe en cms_pages
        if (Schema::hasTable('cms_pages')) {
            $qnaPage = DB::table('cms_pages')->where('slug', 'q&a')->first();
            if (!$qnaPage) {
                DB::table('cms_pages')->insert([
                    'slug' => 'q&a',
                    'title' => 'Q&A Preguntas Frecuentes',
                    'icon' => 'help-circle',
                    'show_in_navbar' => true,
                    'show_in_mobile' => true,
                    'show_in_footer' => true,
                    'navbar_order' => 5,
                    'footer_order' => 5,
                    'seo_title' => 'Preguntas Frecuentes & Q&A | Respuestas Oficiales Optimizadas AEO',
                    'seo_description' => 'Centro oficial de respuestas verificadas sobre envíos express, compras por pedido, pasarelas de pago seguro y beneficios con aliados comerciales.',
                    'seo_keywords' => 'preguntas frecuentes, q&a, envios mexico, formas de pago, guia de tallas, compras por pedido',
                    'status' => 'active',
                    'text_color' => '#ffffff',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_faqs');
    }
};
