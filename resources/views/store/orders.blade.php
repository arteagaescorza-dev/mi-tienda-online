@extends('layouts.app')

@section('title','Mis pedidos')

@section('body_class', 'landing-page')

@section('content')
    <section class="page-hero">
        <p class="page-kicker">Cuenta</p>
        <h2>Seguimiento de pedidos</h2>
        <p>Estado actual de tus compras recientes.</p>
    </section>

    <section class="store-section cart-items">
        @if($orders->isEmpty())
            <article class="about-card">
                <h3>Aun no tienes pedidos</h3>
                <p>Cuando confirmes una compra, podras consultar aqui su estado.</p>
                <a class="btn primary" href="/productos">Explorar tienda</a>
            </article>
        @endif

        @foreach($orders as $order)
            <article class="about-card">
                <h3>{{ $order->order_number }}</h3>
                <p><strong>Estado:</strong> {{ ucfirst($order->status) }}</p>
                <p><strong>Total:</strong> MXN ${{ number_format((float) $order->total, 2) }}</p>
                <p class="muted">{{ $order->created_at->format('d/m/Y H:i') }}</p>

                @if($order->items->isNotEmpty())
                    <div class="stack" style="margin-top: 1rem;">
                        @foreach($order->items as $item)
                            @php($variant = $item->variant)
                            @php($product = $variant?->product)
                            <div class="cart-item" style="padding: 0.75rem 0; border-top: 1px solid #f2e1dc;">
                                <div class="cart-item-copy">
                                    <h4 style="margin: 0 0 0.25rem; font-size: 1rem;">{{ $product?->title ?? 'Producto' }}</h4>
                                    <p class="muted" style="margin: 0;">Variante: {{ data_get($variant?->attributes, 'size', 'U') }} / {{ data_get($variant?->attributes, 'color', 'Unica') }}</p>
                                    <p class="muted" style="margin: 0.2rem 0 0;">Cantidad: {{ $item->quantity }} · Unitario: MXN ${{ number_format((float) $item->unit_price, 2) }}</p>
                                    <p style="margin: 0.3rem 0 0;"><strong>Subtotal:</strong> MXN ${{ number_format((float) $item->line_total, 2) }}</strong></p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>
        @endforeach
    </section>
@endsection
