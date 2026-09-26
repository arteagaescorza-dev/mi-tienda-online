@extends('layouts.app')

@section('title', $product['name'])

@section('body_class', 'landing-page')

@section('content')
    @php($defaultCardSizes = ['XS', 'S', 'M', 'L', 'XL'])
    @php($detailSizes = array_values(array_filter(array_unique([$product['size'] ?? 'M']))))
    @php($detailSizes = empty($detailSizes) ? $defaultCardSizes : $detailSizes)
    <section class="page-hero">
        <p class="page-kicker">Detalle de producto</p>
        <h2>{{ $product['name'] }}</h2>
        <p>{{ $product['description'] }}</p>
    </section>

    <section class="store-section product-detail-layout">
        <article class="product-detail-media">
            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}">
        </article>
        <article class="product-detail-copy" data-product-card data-product-name="{{ $product['name'] }}" data-product-image="{{ $product['image'] }}" data-product-description="{{ $product['description'] }}" data-product-price="{{ $product['price_value'] ?? 0 }}" data-product-slug="{{ $product['slug'] ?? '' }}" data-product-variant-id="{{ $product['variant_id'] ?? '' }}" data-variant-map="{{ json_encode($product['variants'] ?? [], JSON_HEX_APOS | JSON_HEX_QUOT) }}">
            <h3>{{ $product['name'] }}</h3>
            <p>{{ \Illuminate\Support\Str::limit($product['description'], 25, '...') }}</p>
            <p class="price">{{ $product['price'] }}</p>
            <div class="product-purchase-controls">
                <div class="size-selector" data-size-selector>
                    @foreach($detailSizes as $sizeOption)
                        <button type="button" class="size-pill {{ $loop->first ? 'is-active' : '' }}" data-size-option="{{ $sizeOption }}">{{ $sizeOption }}</button>
                    @endforeach
                </div>
                @if(!empty($product['variants']))
                    @php($detailColors = collect($product['variants'])->pluck('color')->unique()->values())
                    <div class="size-selector color-selector" data-color-selector>
                        @foreach($detailColors as $colorOption)
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
            <div class="actions">
                <button type="button" class="btn primary" data-add-to-cart>Agregar al carrito</button>
                <a href="/productos" class="btn ghost">Volver al catalogo</a>
            </div>
        </article>
    </section>

    <section class="products-showcase">
        <div class="section-head">
            <h3>Tambien te puede interesar</h3>
        </div>
        <div class="product-grid">
            @foreach($products as $item)
                <article class="product-card" data-product-card data-product-name="{{ $item['name'] }}" data-product-image="{{ $item['image'] }}" data-product-description="{{ $item['description'] }}" data-product-price="{{ $item['price_value'] ?? 0 }}" data-product-slug="{{ $item['slug'] ?? '' }}" data-product-variant-id="{{ $item['variant_id'] ?? '' }}">
                    @php($cardSizes = array_values(array_filter(array_unique([$item['size'] ?? 'M']))))
                    @php($cardSizes = empty($cardSizes) ? $defaultCardSizes : $cardSizes)
                    <a class="product-media" href="{{ route('store.product', $item['slug']) }}">
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy">
                    </a>
                    <h4>{{ $item['name'] }}</h4>
                    <p>{{ \Illuminate\Support\Str::limit($item['description'], 25, '...') }}</p>
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
                        <strong>{{ $item['price'] }}</strong>
                        <button type="button" class="btn ghost" data-add-to-cart>Agregar</button>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
