@extends('layouts.store-pwa')

@section('title', 'Checkout')

@section('content')
<div class="checkout-page-pwa-container" style="max-width: 960px; margin: 0 auto; padding: 20px 16px 90px;">
    <section class="page-hero" style="text-align: center; margin-bottom: 24px;">
        <span class="page-kicker" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--brand-primary, #6366f1); font-weight: 800;">Checkout Seguro</span>
        <h1 style="font-family: var(--font-heading); font-size: 1.65rem; font-weight: 800; margin: 6px 0 8px; color: var(--text-primary, #0f172a);">Mi Carrito de Compras</h1>
        <p style="color: var(--text-secondary, #64748b); font-size: 0.86rem; margin: 0;">Revisa tus prendas seleccionadas, modifica cantidades y continúa al envío.</p>
    </section>

    @include('partials.checkout-progress')

    <section class="store-section cart-layout checkout-cart-page" data-cart-page style="margin-top: 24px;">
        <div class="checkout-cart-main">
            <div class="free-shipping-bar-box" style="background: var(--bg-surface, #f8fafc); border: 1px solid var(--border-subtle, #e2e8f0); border-radius: 12px; padding: 14px 16px; margin-bottom: 16px;">
                <div class="free-shipping-text" style="display: flex; justify-content: space-between; font-size: 0.78rem; font-weight: 700; color: var(--text-primary, #0f172a); margin-bottom: 8px;">
                    <span data-free-shipping-message>Agrega más para envío gratis</span>
                    <span>México</span>
                </div>
                <div class="free-shipping-progress-track" style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                    <div class="free-shipping-progress-fill" data-free-shipping-fill style="width: 0%; height: 100%; background: #10b981; transition: width 0.3s ease;"></div>
                </div>
            </div>
            <article class="cart-section-card checkout-items-panel" style="background: var(--bg-surface, #ffffff); border: 1px solid var(--border-subtle, #e2e8f0); border-radius: 14px; padding: 20px;">
                <div class="cart-section-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-subtle, #e2e8f0); padding-bottom: 12px; margin-bottom: 16px;">
                    <h3 class="cart-section-title" style="font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; margin: 0;">Prendas en tu carrito</h3>
                    <span class="muted" data-cart-items-count style="font-size: 0.8rem; color: var(--text-secondary, #64748b);">0 prendas</span>
                </div>
                <div class="cart-items" data-cart-items></div>
                <div class="cart-panel-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; flex-wrap: wrap; gap: 10px;">
                    <a class="btn-continue-shopping" href="{{ route('store.catalog') }}" style="font-size: 0.8rem; color: var(--brand-primary, #4f46e5); text-decoration: underline; font-weight: 600;">Seguir explorando el catálogo</a>
                    <button class="btn ghost cart-clear-button" type="button" data-cart-clear style="font-size: 0.75rem; padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; background: transparent; cursor: pointer;">Vaciar carrito</button>
                </div>
            </article>
        </div>
        <article class="about-card hidden" data-cart-empty style="text-align: center; padding: 48px 20px; background: var(--bg-surface, #ffffff); border: 1px solid var(--border-subtle, #e2e8f0); border-radius: 14px; margin-top: 20px;">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; margin-bottom: 8px;">Tu carrito está vacío</h3>
            <p style="color: var(--text-secondary, #64748b); font-size: 0.85rem; margin-bottom: 16px;">Agrega productos para continuar con el pedido.</p>
            <a href="{{ route('store.catalog') }}" class="btn ghost" style="display: inline-block; padding: 10px 20px; border-radius: 8px; border: 1.5px solid var(--brand-primary, #4f46e5); color: var(--brand-primary, #4f46e5); text-decoration: none; font-weight: 700; font-size: 0.82rem;">Ir al catálogo</a>
        </article>
        <aside class="about-card cart-sidebar-card checkout-cart-summary" data-cart-summary style="background: var(--bg-surface, #ffffff); border: 1px solid var(--border-subtle, #e2e8f0); border-radius: 14px; padding: 20px; margin-top: 20px;">
            <h3 class="cart-summary-title" style="font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; margin-top: 0; margin-bottom: 14px;">Resumen de compra</h3>
            <p style="display: flex; justify-content: space-between; font-size: 0.84rem; margin: 8px 0; color: var(--text-secondary, #475569);">Items: <span data-cart-total-items style="font-weight: 700; color: var(--text-primary, #0f172a);">0</span></p>
            <p style="display: flex; justify-content: space-between; font-size: 0.84rem; margin: 8px 0; color: var(--text-secondary, #475569);">Subtotal: <span data-cart-subtotal style="font-weight: 700; color: var(--text-primary, #0f172a);">MXN $0.00</span></p>
            <p style="display: flex; justify-content: space-between; font-size: 0.84rem; margin: 8px 0; color: var(--text-secondary, #475569);">Envío: <span style="font-weight: 700; color: var(--text-primary, #0f172a);">MXN $0.00</span></p>
            <p style="display: flex; justify-content: space-between; font-size: 0.95rem; margin: 12px 0 16px; border-top: 1px solid var(--border-subtle, #e2e8f0); padding-top: 10px;"><strong>Total:</strong> <strong data-cart-total style="color: var(--brand-primary, #4f46e5);">MXN $0.00</strong></p>
            <a class="btn primary btn-checkout-link" href="/checkout/datos" data-checkout-continue style="display: block; width: 100%; text-align: center; padding: 12px; background: var(--brand-primary, #0f172a); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 800; font-size: 0.82rem; letter-spacing: 0.05em; text-transform: uppercase;">Continuar al envío</a>
        </aside>
    </section>
</div>
@endsection
