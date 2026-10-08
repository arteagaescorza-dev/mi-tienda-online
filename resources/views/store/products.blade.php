@extends('layouts.app')

@section('title','Productos')

@section('body_class', 'landing-page products-page')

@section('content')
    @php($defaultCardSizes = ['XS', 'S', 'M', 'L', 'XL'])
    @php($combinedProducts = collect($products)->merge($preorderProducts ?? [])->merge($outletProducts ?? [])->merge($extendedProducts ?? [])->values())
    @php($brands = $combinedProducts->pluck('brand')->filter(function ($value) { return !in_array($value, ['-', null, ''], true); })->unique()->sort()->values())
    @php($sizes = $combinedProducts->pluck('size')->filter(function ($value) { return !in_array($value, ['-', null, ''], true); })->unique()->sort()->values())
    @php($fits = $combinedProducts->pluck('fit')->filter(function ($value) { return !in_array($value, ['-', null, ''], true); })->unique()->sort()->values())
    @php($colors = $combinedProducts->pluck('color')->filter(function ($value) { return !in_array($value, ['-', null, ''], true); })->unique()->sort()->values())
    @php($garmentTypes = $combinedProducts->pluck('garment_type')->filter(function ($value) { return !in_array($value, ['-', null, ''], true); })->unique()->sort()->values())
    @php($categories = $combinedProducts->pluck('category')->filter(function ($value) { return !in_array($value, ['-', null, ''], true); })->unique()->sort()->values())
    @php($colorSwatches = [
        'blanco' => '#f3f6f8',
        'arena' => '#d9c6a3',
        'negro' => '#222b33',
        'camel' => '#b58b60',
        'azul' => '#3f72af',
        'rosa' => '#d980a6',
    ])

    <section class="products-banner" style="background-image:url('https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?auto=format&fit=crop&w=2200&q=80');">
        <div class="products-banner-overlay"></div>
        <div class="products-banner-content">
            <span class="carousel-chip">Catalogo de tienda</span>
            <h2>Explora la coleccion completa de ropa</h2>
            <p>Filtra por marca, talla, tamano, color y categoria para encontrar lo que necesitas.</p>
        </div>
    </section>

    <section class="catalog-layout store-section" data-catalog-filters>
        <aside class="catalog-sidebar">
            <div class="catalog-sidebar-head">
                <h3>Filtros</h3>
                <button type="button" class="btn ghost" data-filter-reset>Limpiar</button>
            </div>

            <!-- Filtro Faceteado en Árbol: Proveedores & Marcas Comerciales (Reglas 6.45 / Regla 2 y 3) -->
            <details class="filter-tree" open>
                <summary>Marcas & Proveedores (Árbol)</summary>
                <div class="tree-options space-y-2">
                    @if(isset($suppliersTree) && count($suppliersTree) > 0)
                        @foreach($suppliersTree as $supplierNode)
                            <div class="supplier-tree-node border-l-2 border-indigo-200 pl-2.5 py-1">
                                <label class="font-bold text-xs text-slate-800 flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" data-filter-group="supplier" value="{{ $supplierNode['name'] }}">
                                    <span>{{ $supplierNode['name'] }}</span>
                                    <span class="text-[10px] text-indigo-600 font-normal">({{ count($supplierNode['brands'] ?? []) }})</span>
                                </label>
                                @if(!empty($supplierNode['brands']))
                                    <div class="pl-4 mt-1 space-y-1">
                                        @foreach($supplierNode['brands'] as $childBrand)
                                            <label class="text-xs text-slate-600 flex items-center gap-1.5 cursor-pointer">
                                                <input type="checkbox" data-filter-group="brand" value="{{ $childBrand['name'] }}">
                                                <span>{{ $childBrand['name'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        @foreach($brands as $brand)
                            <label>
                                <input type="checkbox" data-filter-group="brand" value="{{ $brand }}">
                                <span>{{ $brand }}</span>
                            </label>
                        @endforeach
                    @endif
                </div>
            </details>

            <details class="filter-tree" open>
                <summary>Talla</summary>
                <div class="tree-options">
                    @foreach($sizes as $size)
                        <label>
                            <input type="checkbox" data-filter-group="size" value="{{ $size }}">
                            <span>{{ $size }}</span>
                        </label>
                    @endforeach
                </div>
            </details>

            <details class="filter-tree">
                <summary>Tamano</summary>
                <div class="tree-options">
                    @foreach($fits as $fit)
                        <label>
                            <input type="checkbox" data-filter-group="fit" value="{{ $fit }}">
                            <span>{{ $fit }}</span>
                        </label>
                    @endforeach
                </div>
            </details>

            <details class="filter-tree">
                <summary>Color</summary>
                <div class="tree-options">
                    @foreach($colors as $color)
                        @php($colorKey = strtolower($color))
                        @php($swatch = $colorSwatches[$colorKey] ?? '#9fb5c4')
                        <label>
                            <input type="checkbox" data-filter-group="color" value="{{ $color }}">
                            <span class="color-swatch" style="background-color: {{ $swatch }};"></span>
                            <span>{{ $color }}</span>
                        </label>
                    @endforeach
                </div>
            </details>

            <details class="filter-tree">
                <summary>Categoria</summary>
                <div class="tree-options">
                    @foreach($categories as $category)
                        <label>
                            <input type="checkbox" data-filter-group="category" value="{{ $category }}">
                            <span>{{ $category }}</span>
                        </label>
                    @endforeach
                </div>
            </details>

            <details class="filter-tree">
                <summary>Tipo de prenda</summary>
                <div class="tree-options">
                    @foreach($garmentTypes as $type)
                        <label>
                            <input type="checkbox" data-filter-group="garment_type" value="{{ $type }}">
                            <span>{{ $type }}</span>
                        </label>
                    @endforeach
                </div>
            </details>
        </aside>

        <div class="catalog-main">
            <div class="catalog-topbar">
                <div class="source-dropdown" data-filter-source-toggle>
                    <button type="button" class="source-dropdown-trigger" data-filter-source-trigger aria-haspopup="listbox" aria-expanded="false">
                        <span data-filter-source-label>Todo</span>
                    </button>
                    <div class="source-dropdown-menu" role="listbox" aria-label="Fuentes del catalogo">
                        <button type="button" class="is-active" data-filter-source="all">Todo</button>
                        <button type="button" data-filter-source="stock">En stock</button>
                        <button type="button" data-filter-source="extended">Catalogo extendido</button>
                        <button type="button" data-filter-source="preorder">Preventas Exclusivas</button>
                        <button type="button" data-filter-source="outlet">Outlet</button>
                    </div>
                </div>
                <input type="search" placeholder="Buscar por nombre o descripcion..." data-filter-search>
                <select data-filter-sort>
                    <option value="relevance">Ordenar: Relevancia</option>
                    <option value="precio_asc">Precio: menor a mayor</option>
                    <option value="precio_desc">Precio: mayor a menor</option>
                    <option value="nombre_asc">Nombre: A-Z</option>
                    <option value="nombre_desc">Nombre: Z-A</option>
                </select>

                <div class="per-page-toggle" data-filter-per-page>
                    <button type="button" class="is-active" data-per-page="25">25</button>
                    <button type="button" data-per-page="50">50</button>
                    <button type="button" data-per-page="100">100</button>
                </div>
                <span class="results-pill" data-results-count>{{ count($combinedProducts) }} resultados</span>
            </div>

            <section class="products-showcase catalog-results">
                <div class="section-head">
                    <h3>Catalogo de productos</h3>
                    <a href="/ofertas" class="link-clean">Ver ofertas</a>
                </div>
                <div class="product-grid" data-catalog-grid>
                    @foreach($products as $product)
                        <article
                            class="product-card"
                            data-product-card
                            data-source="stock"
                            data-availability="in_stock"
                            data-index="{{ $loop->index }}"
                            data-brand="{{ strtolower($product['brand'] ?? '') }}"
                            data-size="{{ strtolower($product['size'] ?? '') }}"
                            data-fit="{{ strtolower($product['fit'] ?? '') }}"
                            data-color="{{ strtolower($product['color'] ?? '') }}"
                            data-garment-type="{{ strtolower($product['garment_type'] ?? '') }}"
                            data-category="{{ strtolower($product['category'] ?? '') }}"
                            data-price="{{ $product['price_value'] ?? 0 }}"
                            data-name="{{ strtolower($product['name'] ?? '') }}"
                            data-search="{{ strtolower(($product['name'] ?? '') . ' ' . ($product['description'] ?? '')) }}"
                            data-product-name="{{ $product['name'] }}"
                            data-product-image="{{ $product['image'] }}"
                            data-product-description="{{ $product['description'] }}"
                            data-product-price="{{ $product['price_value'] ?? 0 }}"
                            data-product-slug="{{ $product['slug'] ?? '' }}"
                            data-product-variant-id="{{ $product['variant_id'] ?? '' }}"
                            data-variant-map="{{ json_encode($product['variants'] ?? [], JSON_HEX_APOS | JSON_HEX_QUOT) }}"
                        >
                            @php($cardSizes = array_values(array_filter(array_unique([$product['size'] ?? 'M']))))
                            @php($cardSizes = empty($cardSizes) ? $defaultCardSizes : $cardSizes)
                            <a class="product-media" href="{{ route('store.product', $product['slug']) }}">
                                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy">
                            </a>
                            <h4>{{ $product['name'] }}</h4>
                            <p>{{ \Illuminate\Support\Str::limit($product['description'], 25, '...') }}</p>
                            <p class="product-tags">{{ $product['brand'] }} · {{ $product['size'] }} · {{ $product['fit'] }} · {{ $product['color'] }} · {{ $product['category'] }}</p>
                            <div class="product-purchase-controls">
                                <div class="size-selector" data-size-selector>
                                    @foreach($cardSizes as $sizeOption)
                                        <button type="button" class="size-pill {{ $loop->first ? 'is-active' : '' }}" data-size-option="{{ $sizeOption }}">{{ $sizeOption }}</button>
                                    @endforeach
                                </div>
                                @if(!empty($product['variants']))
                                    @php($cardColors = collect($product['variants'])->pluck('color')->unique()->values())
                                    <div class="size-selector color-selector" data-color-selector>
                                        @foreach($cardColors as $colorOption)
                                            <button type="button" class="size-pill {{ $loop->first ? 'is-active' : '' }}" data-color-option="{{ $colorOption }}">{{ $colorOption }}</button>
                                        @endforeach
                                    </div>
                                @endif
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

                    @foreach(($preorderProducts ?? []) as $product)
                        <article
                            class="product-card"
                            data-product-card
                            data-source="preorder"
                            data-availability="preorder"
                            data-index="{{ count($products) + count($extendedProducts ?? []) + $loop->index }}"
                            data-brand="{{ strtolower($product['brand'] ?? '') }}"
                            data-size="{{ strtolower($product['size'] ?? '') }}"
                            data-fit="{{ strtolower($product['fit'] ?? '') }}"
                            data-color="{{ strtolower($product['color'] ?? '') }}"
                            data-garment-type="{{ strtolower($product['garment_type'] ?? '') }}"
                            data-category="{{ strtolower($product['category'] ?? '') }}"
                            data-price="{{ $product['price_value'] ?? 0 }}"
                            data-name="{{ strtolower($product['name'] ?? '') }}"
                            data-search="{{ strtolower(($product['name'] ?? '') . ' ' . ($product['description'] ?? '') . ' preventa') }}"
                            data-product-name="{{ $product['name'] }}"
                            data-product-image="{{ $product['image'] }}"
                            data-product-description="{{ $product['description'] }}"
                            data-product-price="{{ $product['price_value'] ?? 0 }}"
                            data-product-slug="{{ $product['slug'] ?? '' }}"
                            data-product-variant-id="{{ $product['variant_id'] ?? '' }}"
                        >
                            @php($cardSizes = array_values(array_filter(array_unique([$product['size'] ?? 'M']))))
                            @php($cardSizes = empty($cardSizes) ? $defaultCardSizes : $cardSizes)
                            <a class="product-media" href="{{ route('store.product', $product['slug']) }}">
                                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy">
                            </a>
                            <h4>{{ $product['name'] }}</h4>
                            <p>{{ \Illuminate\Support\Str::limit($product['description'], 25, '...') }}</p>
                            <p class="product-tags">{{ $product['brand'] }} · {{ $product['size'] }} · {{ $product['fit'] }} · {{ $product['color'] }} · Preventas</p>
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
                    @endforeach

                    @foreach(($outletProducts ?? []) as $product)
                        <article
                            class="product-card outlet-card"
                            data-product-card
                            data-source="outlet"
                            data-availability="outlet"
                            data-index="{{ count($products) + count($preorderProducts ?? []) + count($extendedProducts ?? []) + $loop->index }}"
                            data-brand="{{ strtolower($product['brand'] ?? '') }}"
                            data-size="{{ strtolower($product['size'] ?? '') }}"
                            data-fit="{{ strtolower($product['fit'] ?? '') }}"
                            data-color="{{ strtolower($product['color'] ?? '') }}"
                            data-garment-type="{{ strtolower($product['garment_type'] ?? '') }}"
                            data-category="{{ strtolower($product['category'] ?? '') }}"
                            data-price="{{ $product['price_value'] ?? 0 }}"
                            data-name="{{ strtolower($product['name'] ?? '') }}"
                            data-search="{{ strtolower(($product['name'] ?? '') . ' ' . ($product['description'] ?? '') . ' outlet') }}"
                            data-product-name="{{ $product['name'] }}"
                            data-product-image="{{ $product['image'] }}"
                            data-product-description="{{ $product['description'] }}"
                            data-product-price="{{ $product['price_value'] ?? 0 }}"
                            data-product-slug="{{ $product['slug'] ?? '' }}"
                            data-product-variant-id="{{ $product['variant_id'] ?? '' }}"
                        >
                            @php($cardSizes = array_values(array_filter(array_unique([$product['size'] ?? 'M']))))
                            @php($cardSizes = empty($cardSizes) ? $defaultCardSizes : $cardSizes)
                            <span class="product-badge outlet-badge">Outlet</span>
                            <a class="product-media" href="{{ route('store.product', $product['slug']) }}">
                                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy">
                            </a>
                            <h4>{{ $product['name'] }}</h4>
                            <p>{{ \Illuminate\Support\Str::limit($product['description'], 25, '...') }}</p>
                            <p class="product-tags">{{ $product['brand'] }} · {{ $product['size'] }} · {{ $product['fit'] }} · {{ $product['color'] }} · Outlet</p>
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
                                <button type="button" class="btn ghost" data-add-to-cart>Llevar</button>
                            </div>
                        </article>
                    @endforeach

                    @foreach(($extendedProducts ?? []) as $product)
                        <article
                            class="product-card"
                            data-product-card
                            data-source="extended"
                            data-availability="{{ strtolower($product['availability'] ?? 'unknown') }}"
                            data-index="{{ count($products) + count($preorderProducts ?? []) + $loop->index }}"
                            data-brand="{{ strtolower($product['brand'] ?? '') }}"
                            data-size="{{ strtolower($product['size'] ?? '') }}"
                            data-fit="{{ strtolower($product['fit'] ?? '') }}"
                            data-color="{{ strtolower($product['color'] ?? '') }}"
                            data-garment-type="{{ strtolower($product['garment_type'] ?? '') }}"
                            data-category="{{ strtolower($product['category'] ?? '') }}"
                            data-price="{{ $product['price_value'] ?? 0 }}"
                            data-name="{{ strtolower($product['name'] ?? '') }}"
                            data-search="{{ strtolower(($product['name'] ?? '') . ' ' . ($product['description'] ?? '') . ' ' . ($product['supplier_sku'] ?? '')) }}"
                            data-product-name="{{ $product['name'] }}"
                            data-product-image="{{ $product['image'] }}"
                            data-product-description="{{ $product['description'] }}"
                            data-product-price="{{ $product['price_value'] ?? 0 }}"
                            data-product-slug="{{ $product['slug'] ?? '' }}"
                        >
                            @php($cardSizes = $product['sizes'] ?? ['U'])
                            <div class="product-media">
                                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy">
                            </div>
                            <h4>{{ $product['name'] }}</h4>
                            <p>{{ \Illuminate\Support\Str::limit($product['description'], 25, '...') }}</p>
                            <p class="product-tags">{{ $product['brand'] }} · {{ $product['category'] }} · {{ $product['availability_label'] }} · SKU {{ $product['supplier_sku'] }}</p>
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
                                <button type="button" class="btn ghost" data-request-availability>Solicitar disponibilidad</button>
                            </div>
                        </article>
                    @endforeach
                </div>
                <article class="about-card hidden" data-results-empty>
                    <h3>Sin resultados</h3>
                    <p>No encontramos productos con esos filtros. Ajusta la busqueda para ver mas opciones.</p>
                </article>
                <div class="catalog-pagination hidden" data-catalog-pagination>
                    <button type="button" class="btn ghost" data-page-prev>Anterior</button>
                    <span class="page-pill" data-page-status>Pagina 1 de 1</span>
                    <button type="button" class="btn ghost" data-page-next>Siguiente</button>
                </div>
            </section>
        </div>
    </section>
@endsection
