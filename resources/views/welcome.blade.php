@extends('layouts.app')

@section('title','Mi Tienda')

@section('body_class', 'landing-page')

@section('content')
    @php($defaultCardSizes = ['XS', 'S', 'M', 'L', 'XL'])
    <section id="inicio" class="hero-carousel hero-editorial" data-hero-carousel>
        @php($heroSlides = [
                ['tag' => 'Marcas oficiales // Torzo & Guillermo Luna', 'title' => 'Moda contemporanea & marcas asociadas', 'description' => 'Descubre lenceria ergonomica, alta costura y prendas seleccionadas con envios protegidos a todo Mexico.', 'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=1000&auto=format&fit=crop&q=80', 'top' => '250+ prendas exclusivas', 'bottom' => 'Torzo & Guillermo Luna', 'primary' => 'Explorar catalogo', 'secondary' => 'Ver marcas asociadas'],
                ['tag' => 'Promocion de temporada // Envio nacional', 'title' => 'Envio gratis en compras de $999+', 'description' => 'Lleva tus prendas favoritas y recibe opciones de entrega seguras para todo Mexico.', 'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1000&auto=format&fit=crop&q=80', 'top' => 'Envio $0 en $999+', 'bottom' => 'Cobertura nacional', 'primary' => 'Comprar ahora', 'secondary' => 'Ver mi carrito'],
                ['tag' => 'Atencion personalizada // Pedido directo', 'title' => 'Compra con acompanamiento', 'description' => 'Resuelve dudas de tallas, modelos y pagos con una experiencia de compra directa y clara.', 'image' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=1000&auto=format&fit=crop&q=80', 'top' => 'Atencion personalizada', 'bottom' => 'Pedido seguro', 'primary' => 'Consultar ahora', 'secondary' => 'Ver catalogo'],
        ])
        <div class="hero-editorial-track carousel-track" data-carousel-track>
            @foreach($heroSlides as $index => $slide)
                <article class="carousel-slide hero-editorial-slide {{ $index === 0 ? 'is-active' : '' }}" data-carousel-slide>
                    <div class="hero-editorial-inner">
                        <div class="hero-editorial-copy">
                            <span class="hero-editorial-tag"><span class="hero-live-dot" aria-hidden="true"></span>{{ $slide['tag'] }}</span>
                            <h2>{{ $slide['title'] }}</h2>
                            <p>{{ $slide['description'] }}</p>
                            <div class="hero-editorial-features">
                                <span><svg aria-hidden="true" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h11v10H3zM14 9h4l3 3v4h-7zM7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM17 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"></path></svg>Envios a todo Mexico</span>
                                <span><svg aria-hidden="true" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 4 7v5c0 4.5 3 7.5 8 9 5-1.5 8-4.5 8-9V7l-8-4Z"></path></svg>Compra protegida</span>
                            </div>
                            <div class="hero-editorial-actions">
                                <a class="btn primary" href="#productos">{{ $slide['primary'] }} <svg aria-hidden="true" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h13M13 6l6 6-6 6"></path></svg></a>
                                <a class="btn ghost" href="#marcas">{{ $slide['secondary'] }}</a>
                            </div>
                        </div>
                        <div class="hero-editorial-visual">
                            <div class="hero-editorial-image">
                                <img src="{{ $slide['image'] }}" alt="{{ $slide['title'] }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                                <span class="hero-editorial-badge hero-editorial-badge-top">{{ $slide['top'] }}</span>
                                <span class="hero-editorial-badge hero-editorial-badge-bottom">{{ $slide['bottom'] }}</span>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="carousel-controls hero-editorial-controls">
            <button class="carousel-btn" type="button" data-carousel-prev aria-label="Imagen anterior"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"></path></svg></button>
            <div class="carousel-dots" data-carousel-dots>
                <button class="carousel-dot is-active" type="button" data-carousel-dot="0" aria-label="Ir a imagen 1"></button>
                <button class="carousel-dot" type="button" data-carousel-dot="1" aria-label="Ir a imagen 2"></button>
                <button class="carousel-dot" type="button" data-carousel-dot="2" aria-label="Ir a imagen 3"></button>
            </div>
            <button class="carousel-btn" type="button" data-carousel-next aria-label="Siguiente imagen"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"></path></svg></button>
        </div>
    </section>

    <section id="productos" class="products-showcase">
        <div class="section-head">
            <h3>Productos destacados</h3>
            <a href="/productos" class="link-clean">Ver catalogo</a>
        </div>
        <div class="product-grid">
            @foreach(($featuredProducts ?? []) as $product)
                <article class="product-card" data-product-card data-product-name="{{ $product['name'] }}" data-product-image="{{ $product['image'] }}" data-product-description="{{ $product['description'] }}" data-product-price="{{ $product['price_value'] ?? 0 }}" data-product-slug="{{ $product['slug'] ?? '' }}" data-product-color="{{ $product['color'] ?? '' }}" data-product-category="{{ $product['garment_type'] ?? '' }}">
                    @php($cardSizes = array_values(array_filter(array_unique([$product['size'] ?? 'M']))))
                    @php($cardSizes = empty($cardSizes) ? $defaultCardSizes : $cardSizes)
                    <a class="product-media" href="{{ route('store.product', $product['slug']) }}">
                        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy">
                    </a>
                    <h4>{{ $product['name'] }}</h4>
                    <p>{{ \Illuminate\Support\Str::limit($product['description'], 25, '...') }}</p>
                    <div class="product-purchase-controls">
                        <div class="size-selector" data-size-selector>
                            @foreach($cardSizes as $sizeOption)
                                <button type="button" class="size-pill {{ $loop->first ? 'is-active' : '' }}" data-size-option="{{ $sizeOption }}">{{ $sizeOption }}</button>
                            @endforeach
                        </div>
                        <div class="qty-selector" data-qty-selector>
                            <button type="button" data-qty-decrease>-</button>
                            <input type="number" min="1" value="1" data-qty-input>
                            <button type="button" data-qty-increase>+</button>
                        </div>
                    </div>
                    <div class="product-row">
                        <strong>{{ $product['price'] }}</strong>
                        <button type="button" class="btn ghost" data-add-to-cart>Agregar</button>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="products-showcase preorder-showcase">
        <div class="section-head">
            <h3>Preventas Exclusivas</h3>
            <a href="/productos" class="link-clean">Explorar catalogo</a>
        </div>
        <div class="product-grid">
            @forelse(($preorderProducts ?? []) as $product)
                <article class="product-card preorder-card" data-product-card data-product-name="{{ $product['name'] }}" data-product-image="{{ $product['image'] }}" data-product-description="{{ $product['description'] }}" data-product-price="{{ $product['price_value'] ?? 0 }}" data-product-slug="{{ $product['slug'] ?? '' }}" data-product-color="{{ $product['color'] ?? '' }}" data-product-category="{{ $product['garment_type'] ?? '' }}">
                    @php($cardSizes = array_values(array_filter(array_unique([$product['size'] ?? 'M']))))
                    @php($cardSizes = empty($cardSizes) ? $defaultCardSizes : $cardSizes)
                    <a class="product-media" href="{{ route('store.product', $product['slug']) }}">
                        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy">
                    </a>
                    <h4>{{ $product['name'] }}</h4>
                    <p>{{ \Illuminate\Support\Str::limit($product['description'], 25, '...') }}</p>
                    <div class="product-purchase-controls">
                        <div class="size-selector" data-size-selector>
                            @foreach($cardSizes as $sizeOption)
                                <button type="button" class="size-pill {{ $loop->first ? 'is-active' : '' }}" data-size-option="{{ $sizeOption }}">{{ $sizeOption }}</button>
                            @endforeach
                        </div>
                        <div class="qty-selector" data-qty-selector>
                            <button type="button" data-qty-decrease>-</button>
                            <input type="number" min="1" value="1" data-qty-input>
                            <button type="button" data-qty-increase>+</button>
                        </div>
                    </div>
                    <div class="product-row">
                        <strong>{{ $product['price'] }}</strong>
                        <button type="button" class="btn ghost" data-add-to-cart>Apartar</button>
                    </div>
                </article>
            @empty
                <article class="about-card">
                    <h3>Aun no hay preventas activas</h3>
                    <p>Cuando existan piezas en preventa, apareceran aqui con un limite de tres tarjetas.</p>
                </article>
            @endforelse
        </div>
    </section>

    <section class="products-showcase outlet-showcase">
        <div class="section-head">
            <h3>Outlet</h3>
            <a href="/productos?source=outlet" class="link-clean">Ver outlet</a>
        </div>
        <div class="product-grid">
            @forelse(($outletProducts ?? []) as $product)
                <article class="product-card outlet-card" data-product-card data-product-name="{{ $product['name'] }}" data-product-image="{{ $product['image'] }}" data-product-description="{{ $product['description'] }}" data-product-price="{{ $product['price_value'] ?? 0 }}" data-product-slug="{{ $product['slug'] ?? '' }}" data-product-color="{{ $product['color'] ?? '' }}" data-product-category="{{ $product['garment_type'] ?? '' }}">
                    @php($cardSizes = array_values(array_filter(array_unique([$product['size'] ?? 'M']))))
                    @php($cardSizes = empty($cardSizes) ? $defaultCardSizes : $cardSizes)
                    <a class="product-media" href="{{ route('store.product', $product['slug']) }}">
                        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy">
                    </a>
                    <h4>{{ $product['name'] }}</h4>
                    <p>{{ \Illuminate\Support\Str::limit($product['description'], 25, '...') }}</p>
                    <div class="product-purchase-controls">
                        <div class="size-selector" data-size-selector>
                            @foreach($cardSizes as $sizeOption)
                                <button type="button" class="size-pill {{ $loop->first ? 'is-active' : '' }}" data-size-option="{{ $sizeOption }}">{{ $sizeOption }}</button>
                            @endforeach
                        </div>
                        <div class="qty-selector" data-qty-selector>
                            <button type="button" data-qty-decrease>-</button>
                            <input type="number" min="1" value="1" data-qty-input>
                            <button type="button" data-qty-increase>+</button>
                        </div>
                    </div>
                    <div class="product-row">
                        <strong>{{ $product['price'] }}</strong>
                        <button type="button" class="btn ghost" data-add-to-cart>Ver oferta</button>
                    </div>
                </article>
            @empty
                <article class="about-card">
                    <h3>No hay outlet disponible</h3>
                    <p>Cuando existan piezas outlet, apareceran aqui entre preventas y colecciones exclusivas.</p>
                </article>
            @endforelse
        </div>
    </section>

    <section class="products-showcase scraping-showcase">
        <div class="section-head">
            <h3>Colecciones exclusivas</h3>
            <a href="/productos" class="link-clean">Explorar todo</a>
        </div>
        <div class="exclusive-banner">
            <p>Explora nuestro catálogo extendido. Estos artículos requieren una rápida validación de stock antes de tu pago.</p>
        </div>

        @if(!empty($scrapedProducts))
            <div class="scraped-grid">
                @foreach($scrapedProducts as $item)
                    <article class="scraped-card product-card" data-product-card data-product-name="{{ $item['title'] }}" data-product-image="{{ $item['image'] }}" data-product-description="{{ $item['category'] }}" data-product-price="{{ preg_replace('/[^\d\.]/', '', $item['price']) }}" data-product-slug="scraped-{{ $item['supplier_sku'] }}">
                        <div class="product-media">
                            <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" loading="lazy">
                        </div>
                        <h4>{{ $item['title'] }}</h4>
                        <p class="scraped-meta">{{ $item['supplier_name'] }} · SKU {{ $item['supplier_sku'] }}</p>
                        <p class="scraped-meta">{{ $item['category'] }} · {{ $item['availability'] }}</p>
                        <div class="product-purchase-controls">
                            <div class="size-selector" data-size-selector>
                                <button type="button" class="size-pill is-active" data-size-option="U">U</button>
                            </div>
                            <div class="qty-selector" data-qty-selector>
                                <button type="button" data-qty-decrease>-</button>
                                <input type="number" min="1" value="1" data-qty-input>
                                <button type="button" data-qty-increase>+</button>
                            </div>
                        </div>
                        <div class="product-row">
                            <strong>{{ $item['price'] }}</strong>
                            <button type="button" class="btn ghost" data-request-availability>Solicitar disponibilidad</button>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <article class="about-card">
                <h3>Sin resultados recientes</h3>
                <p>Aun no hay productos disponibles desde scraping en este momento.</p>
            </article>
        @endif
    </section>

    <section class="products-showcase brands-showcase">
        <div class="section-head">
            <h3>Marcas del catálogo extendido</h3>
        </div>
        <div class="brand-logos-grid">
            @forelse(($scrapedBrands ?? []) as $brand)
                <article class="brand-logo-card" title="{{ $brand['name'] }}">
                    <div class="brand-logo-mark">
                        @if(!empty($brand['logo']))
                            <img src="{{ $brand['logo'] }}" alt="Logo {{ $brand['name'] }}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">
                            <span class="brand-logo-fallback" style="display:none;">{{ $brand['initials'] }}</span>
                        @else
                            <span class="brand-logo-fallback">{{ $brand['initials'] }}</span>
                        @endif
                    </div>
                    <h4>{{ $brand['name'] }}</h4>
                </article>
            @empty
                <article class="about-card">
                    <h3>Sin marcas disponibles</h3>
                    <p>Aún no hay marcas del scraping para mostrar en esta sección.</p>
                </article>
            @endforelse
        </div>
    </section>

    <section id="beneficios" class="benefits-strip">
        <article>
            <div class="benefit-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 7H20V17H4V7Z" stroke="currentColor" stroke-width="1.7"/>
                    <path d="M4 10L12 14L20 10" stroke="currentColor" stroke-width="1.7"/>
                </svg>
            </div>
            <h4>Entrega nacional</h4>
            <p>Envíos rápidos y trazables para una experiencia de compra confiable.</p>
        </article>
        <article>
            <div class="benefit-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 11V8C7 5.8 8.8 4 11 4H13C15.2 4 17 5.8 17 8V11" stroke="currentColor" stroke-width="1.7"/>
                    <rect x="5" y="11" width="14" height="9" rx="2" stroke="currentColor" stroke-width="1.7"/>
                    <path d="M12 14V17" stroke="currentColor" stroke-width="1.7"/>
                </svg>
            </div>
            <h4>Pago seguro</h4>
            <p>Pasarela protegida y comprobantes automáticos para cliente y equipo.</p>
        </article>
        <article>
            <div class="benefit-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 5H20V15H12L8 19V15H4V5Z" stroke="currentColor" stroke-width="1.7"/>
                    <path d="M8 9H16" stroke="currentColor" stroke-width="1.7"/>
                    <path d="M8 12H13" stroke="currentColor" stroke-width="1.7"/>
                </svg>
            </div>
            <h4>Atención continua</h4>
            <p>Soporte comercial para compras mayoristas y asesoría personalizada.</p>
        </article>
    </section>

@endsection
