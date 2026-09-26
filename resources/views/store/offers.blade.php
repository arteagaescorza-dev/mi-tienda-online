@extends('layouts.app')

@section('title','Ofertas')

@section('body_class', 'landing-page')

@section('content')
    @php($defaultCardSizes = ['XS', 'S', 'M', 'L', 'XL'])
    <section class="page-hero">
        <p class="page-kicker">Ofertas</p>
        <h2>Promociones destacadas del mes</h2>
        <p>Boceto visual para campañas, descuentos y llamadas a la accion.</p>
    </section>

    <section class="products-showcase">
        <div class="section-head">
            <h3>Ofertas activas</h3>
            <a href="/productos" class="link-clean">Ver todo</a>
        </div>
        <div class="product-grid">
            @foreach($products as $product)
                <article class="product-card" data-product-card data-product-name="{{ $product['name'] }}" data-product-image="{{ $product['image'] }}" data-product-description="{{ $product['description'] }}" data-product-price="{{ $product['price_value'] ?? 0 }}" data-product-slug="{{ $product['slug'] ?? '' }}">
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
                        <button type="button" class="btn ghost" data-add-to-cart>Aplicar oferta</button>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
