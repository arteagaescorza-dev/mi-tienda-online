@extends('layouts.app')

@section('title','Pedido confirmado')

@section('body_class', 'landing-page')

@section('content')
    <section class="page-hero">
        <p class="page-kicker">Pedido</p>
        <h2>Compra confirmada</h2>
        <p>Tu pedido fue registrado correctamente y ya queda en proceso.</p>
    </section>

    @include('partials.checkout-progress')

    <section class="store-section checkout-confirmation-layout">
        <article class="checkout-confirmation-card">
            <div class="checkout-confirmation-mark" aria-hidden="true"><svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"></path></svg></div>
            <p class="page-kicker">Pedido registrado</p>
            <h3>Gracias por tu compra</h3>
            <p class="checkout-confirmation-folio">{{ $orderNumber }}</p>
            <p class="muted">Tu pedido fue registrado correctamente. Consulta el detalle y el estado desde tu historial de pedidos.</p>

            <div class="checkout-confirmation-notice">
                <strong>Siguiente paso</strong>
                <span>El pedido queda pendiente de pago hasta integrar la pasarela correspondiente.</span>
            </div>

            <div class="confirmation-ticket">
                <div class="ticket-header"><span>Ticket digital</span><span>Pedido registrado</span></div>
                <div class="ticket-row"><span>Folio</span><strong>{{ $orderNumber }}</strong></div>
                <div class="ticket-row"><span>Estado</span><strong>Pendiente de pago</strong></div>
                <div class="ticket-row ticket-total-row"><span>Consulta el detalle</span><span>Mis pedidos</span></div>
            </div>

            <div class="form-actions">
                <a class="btn primary" href="/mis-pedidos">Ver mis pedidos</a>
                <a class="btn ghost" href="/productos">Seguir comprando</a>
            </div>
        </article>
    </section>
@endsection
