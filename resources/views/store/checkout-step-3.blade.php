@extends('layouts.app')

@section('title','Pago')

@section('body_class', 'landing-page')

@section('content')
    <section class="page-hero">
        <p class="page-kicker">Checkout</p>
        <h2>Pago</h2>
        <p>Selecciona tu metodo de pago para completar la orden.</p>
    </section>

    @include('partials.checkout-progress')

    <section class="store-section contact-layout checkout-page-layout">
        <div class="checkout-form-shell">
            <section class="checkout-form-section checkout-payment-address">
                <div class="checkout-section-heading">
                    <h3>Enviando a</h3>
                    <a href="/checkout/datos">Editar datos</a>
                </div>
                <p class="muted">La direccion confirmada en el paso anterior se aplicara al pedido.</p>
            </section>

            <section class="checkout-form-section">
                <h3>Selecciona tu metodo de pago</h3>
                <div class="checkout-payment-notice">
                    El pago se realiza despues de registrar el pedido. Recibiras las instrucciones con tu folio.
                </div>
                <div class="checkout-payment-options" role="radiogroup" aria-label="Metodo de pago">
                    <label class="checkout-payment-option">
                        <input type="radio" name="payment_method" value="spei" checked>
                        <span><strong>Transferencia SPEI</strong><small>Confirmacion mediante comprobante de pago.</small></span>
                        <b>Recomendado</b>
                    </label>
                    <label class="checkout-payment-option">
                        <input type="radio" name="payment_method" value="card">
                        <span><strong>Tarjeta de credito o debito</strong><small>Link seguro de pago. Disponible proximamente.</small></span>
                    </label>
                    <label class="checkout-payment-option">
                        <input type="radio" name="payment_method" value="cash">
                        <span><strong>Pago contra entrega</strong><small>Disponible para entregas personales autorizadas.</small></span>
                    </label>
                </div>
            </section>

            <div class="form-actions">
                <a class="btn ghost" href="/checkout/datos">Volver a datos</a>
            </div>
        </div>

        <aside class="about-card checkout-order-summary checkout-payment-summary">
            <h3>Total definitivo</h3>
            <div class="checkout-summary-placeholder" data-checkout-summary>
                <p class="muted">Tus productos, cantidades y total apareceran aqui.</p>
                <p class="checkout-summary-total">Total pendiente de confirmar</p>
            </div>
            <p class="muted">El costo de envio se actualizara al conectar la cotizacion seleccionada.</p>
        </aside>
    </section>
@endsection
