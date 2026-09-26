@extends('layouts.app')

@section('title','Checkout')

@section('body_class', 'landing-page')

@section('content')
    <section class="page-hero">
        <p class="page-kicker">Checkout</p>
        <h2>Finaliza tu compra</h2>
        <p>Tu pedido se completa en 3 pasos: carrito, datos y pago.</p>
    </section>

    <section class="store-section contact-layout">
        <article class="about-card">
            <h3>Resumen del flujo</h3>
            <ol>
                <li>Revisa tu carrito</li>
                <li>Completa los datos de envio</li>
                <li>Confirma el pago</li>
            </ol>

            <div class="form-actions" style="margin-top: 1.5rem;">
                <a class="btn primary" href="/checkout">Ir al paso 1</a>
                <a class="btn ghost" href="/carrito">Volver al carrito</a>
            </div>
        </article>

        <article class="about-card">
            <h3>Estado del pedido</h3>
            <p>Actualmente el pedido queda registrado con validacion de stock y datos de envio.</p>
            <p>La pasarela de pago se habilitara en una siguiente fase del proyecto.</p>
        </article>
    </section>
@endsection
