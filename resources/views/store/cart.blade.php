@extends('layouts.app')

@section('title','Carrito')

@section('body_class', 'landing-page')

@section('content')
    <section class="page-hero">
        <p class="page-kicker">Carrito</p>
        <h2>Mi carrito de compras</h2>
        <p>Revisa tus prendas seleccionadas, modifica cantidades y continua al envio.</p>
    </section>

    @include('partials.checkout-progress')

    <section class="store-section cart-layout checkout-cart-page" data-cart-page>
        <div class="checkout-cart-main">
            <div class="free-shipping-bar-box">
                <div class="free-shipping-text"><span data-free-shipping-message>Agrega mas para envio gratis</span><span>Mexico</span></div>
                <div class="free-shipping-progress-track"><div class="free-shipping-progress-fill" data-free-shipping-fill style="width: 0%;"></div></div>
            </div>
            <article class="cart-section-card checkout-items-panel">
                <div class="cart-section-header">
                    <h3 class="cart-section-title">Prendas en tu carrito</h3>
                    <span class="muted" data-cart-items-count>0 prendas</span>
                </div>
                <div class="cart-items" data-cart-items></div>
                <div class="cart-panel-footer">
                    <a class="btn-continue-shopping" href="/productos">Seguir explorando el catalogo</a>
                    <button class="btn ghost cart-clear-button" type="button" data-cart-clear>Vaciar carrito</button>
                </div>
            </article>
        </div>
        <article class="about-card hidden" data-cart-empty>
            <h3>Tu carrito esta vacio</h3>
            <p>Explora el catalogo y agrega productos para continuar con tu compra.</p>
            <a href="/productos" class="btn ghost">Ir al catalogo</a>
        </article>
        <aside class="about-card cart-sidebar-card checkout-cart-summary" data-cart-summary>
            <h3 class="cart-summary-title">Resumen de compra</h3>
            <p>Items: <span data-cart-total-items>0</span></p>
            <p>Subtotal: <span data-cart-subtotal>MXN $0.00</span></p>
            <p>Envio: MXN $0.00</p>
            <p><strong>Total: <span data-cart-total>MXN $0.00</span></strong></p>
            <a class="btn primary btn-checkout-link" href="/checkout">Continuar al envio</a>
        </aside>
    </section>
@endsection
