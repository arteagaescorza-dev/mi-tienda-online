@extends('layouts.app')

@section('title','Contacto')

@section('body_class', 'landing-page')

@section('content')
    <section class="page-hero">
        <p class="page-kicker">Contacto</p>
        <h2>Hablemos de tu pedido o colaboracion</h2>
        <p>Boceto del formulario de contacto con enfoque comercial.</p>
    </section>

    <section class="store-section contact-layout">
        <article class="about-card">
            <h3>Canales directos</h3>
            <p>Correo: ventas@mitienda.com</p>
            <p>WhatsApp: +52 55 0000 0000</p>
            <p>Horario: Lun a Vie, 9:00 a 18:00</p>
        </article>
        <article class="about-card">
            <h3>Formulario de muestra</h3>
            <form class="stack" action="#" method="POST">
                <div>
                    <label for="contact-name">Nombre</label>
                    <input id="contact-name" type="text" placeholder="Tu nombre completo">
                </div>
                <div>
                    <label for="contact-email">Correo</label>
                    <input id="contact-email" type="email" placeholder="correo@dominio.com">
                </div>
                <div>
                    <label for="contact-message">Mensaje</label>
                    <textarea id="contact-message" rows="4" placeholder="Escribe tu mensaje"></textarea>
                </div>
                <button type="button" class="btn primary">Enviar</button>
            </form>
        </article>
    </section>
@endsection
