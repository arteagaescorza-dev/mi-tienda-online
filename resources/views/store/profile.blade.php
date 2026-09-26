@extends('layouts.app')

@section('title','Mi perfil')

@section('body_class', 'landing-page')

@section('content')
    <section class="page-hero">
        <p class="page-kicker">Cuenta</p>
        <h2>Perfil del cliente</h2>
        <p>Informacion basica para gestionar tus datos de compra.</p>
    </section>

    <section class="store-section contact-layout">
        <article class="about-card">
            <h3>Datos principales</h3>
            <p><strong>Nombre:</strong> {{ $user->name }}</p>
            <p><strong>Correo:</strong> {{ $user->email }}</p>
            <p><strong>Tipo de cuenta:</strong> {{ $user->role === 'portal' ? 'Portal' : 'Cliente tienda' }}</p>
        </article>
        <article class="about-card">
            <h3>Acciones</h3>
            <p>Actualiza tu informacion personal y preferencias de contacto.</p>
            <a class="btn ghost" href="/mis-pedidos">Ver pedidos</a>
        </article>
    </section>
@endsection
