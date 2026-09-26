@extends('layouts.app')

@section('title','Categorias')

@section('body_class', 'landing-page')

@section('content')
    <section class="page-hero">
        <p class="page-kicker">Categorias</p>
        <h2>Boceto de navegacion por familia de producto</h2>
        <p>Seccion de muestra para visualizar como quedarían los bloques de categoria.</p>
    </section>

    <section class="store-section page-grid">
        @foreach($categories as $category)
            <article class="category-card">
                <div class="category-media">
                    <img src="{{ $category['image'] }}" alt="{{ $category['name'] }}" loading="lazy">
                </div>
                <h3>{{ $category['name'] }}</h3>
                <p>{{ $category['description'] }}</p>
                <a class="btn ghost" href="/productos">Explorar</a>
            </article>
        @endforeach
    </section>
@endsection
