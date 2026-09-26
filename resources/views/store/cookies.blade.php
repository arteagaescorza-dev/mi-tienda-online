@extends('layouts.app')

@section('title','Gestion de cookies')

@section('body_class', 'landing-page')

@section('content')
    <section class="page-hero">
        <p class="page-kicker">Legal</p>
        <h2>Gestion de cookies</h2>
        <p>Resumen sobre el uso de cookies y como puedes ajustar tus preferencias.</p>
    </section>

    <section class="store-section legal-card">
        <h3>1. Cookies esenciales</h3>
        <p>Permiten funciones basicas como inicio de sesion, seguridad y funcionamiento del carrito.</p>

        <h3>2. Cookies de analitica</h3>
        <p>Nos ayudan a entender el comportamiento de navegacion para optimizar contenidos y flujos.</p>

        <h3>3. Cookies de marketing</h3>
        <p>Se utilizan para mostrar campañas relevantes y medir el rendimiento de acciones comerciales.</p>

        <h3>4. Configuracion</h3>
        <p>Puedes bloquear o eliminar cookies desde tu navegador; algunas funciones podrian verse limitadas.</p>
    </section>
@endsection
