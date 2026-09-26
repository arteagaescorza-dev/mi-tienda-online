@extends('layouts.app')

@section('title','Nosotros')

@section('body_class', 'landing-page')

@section('content')
    <section class="page-hero">
        <p class="page-kicker">Nuestra marca</p>
        <h2>Historia, propuesta y estilo visual</h2>
        <p>Boceto de pagina corporativa para comunicar confianza, proceso y diferenciadores.</p>
    </section>

    <section class="store-section about-grid">
        <article class="about-card">
            <h3>Mision</h3>
            <p>Crear experiencias de compra claras, aspiracionales y eficientes para clientes y equipos internos.</p>
        </article>
        <article class="about-card">
            <h3>Vision</h3>
            <p>Ser referente en retail digital combinando diseño premium, velocidad operativa y analitica comercial.</p>
        </article>
        <article class="about-card">
            <h3>Valores</h3>
            <p>Calidad, transparencia, mejora continua y atencion humana en cada punto de contacto.</p>
        </article>
    </section>
@endsection
