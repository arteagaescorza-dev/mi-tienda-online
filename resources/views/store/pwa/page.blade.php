@extends('layouts.store-pwa')

@section('seo')
  <link rel="canonical" href="{{ url('/pagina/' . $page->slug) }}">
  <meta property="og:type" content="website">
  <meta property="og:title" content="{{ $page->seo_title ?: $page->title }} | {{ $currentStorefront['brandName'] ?? 'TORZO' }}">
  <meta property="og:description" content="{{ $page->seo_description ?: Str::limit(strip_tags($page->content_html), 160) }}">
  <meta property="og:url" content="{{ url('/pagina/' . $page->slug) }}">

  <!-- Schema.org JSON-LD WebPage -->
  <script type="application/ld+json">
  {!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => $page->seo_title ?: $page->title,
    'description' => $page->seo_description ?: Str::limit(strip_tags($page->content_html), 160),
    'url' => url('/pagina/' . $page->slug),
    'isPartOf' => [
      '@type' => 'WebSite',
      'name' => $currentStorefront['brandName'] ?? 'TORZO',
      'url' => url('/'),
    ],
  ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
  </script>
@endsection


@push('styles')
<style>
  .cms-page-wrapper {
    max-width: 900px;
    margin: 0 auto;
    padding: 24px 16px 80px;
  }
  .cms-breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.75rem;
    color: var(--text-muted, #71717a);
    margin-bottom: 24px;
  }
  .cms-breadcrumb-nav a {
    color: var(--text-muted, #71717a);
    text-decoration: none;
  }
  .cms-page-card {
    background: #ffffff;
    border: 1px solid #e4e4e7;
    border-radius: 16px;
    padding: 32px 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  }
  @media (min-width: 768px) {
    .cms-page-card {
      padding: 48px;
    }
  }
  .cms-page-header {
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f4f4f5;
  }
  .cms-page-title {
    font-family: var(--font-heading, 'Outfit', sans-serif);
    font-size: 1.85rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: var(--text-primary, #09090b);
    margin: 0;
  }
  .cms-page-body {
    font-size: 0.95rem;
    line-height: 1.8;
    color: #3f3f46;
  }
  .cms-page-body h2 {
    font-family: var(--font-heading, 'Outfit', sans-serif);
    font-size: 1.35rem;
    font-weight: 800;
    color: #09090b;
    margin: 32px 0 16px;
  }
  .cms-page-body p {
    margin-bottom: 18px;
  }
  .cms-page-body ul, .cms-page-body ol {
    margin-bottom: 20px;
    padding-left: 24px;
  }
  .cms-page-body li {
    margin-bottom: 8px;
  }
  .cms-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 32px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--brand-primary, #09090b);
    text-decoration: none;
  }
</style>
@endpush

@section('content')
<div class="cms-page-wrapper">
  
  <nav class="cms-breadcrumb-nav" aria-label="Navegación institucional">
    <a href="{{ route('store.home', ['universo' => $activeUniverse]) }}">INICIO</a>
    <span>/</span>
    <span style="color: var(--text-primary); font-weight: 600;">{{ $page->title }}</span>
  </nav>

  <article class="cms-page-card">
    <header class="cms-page-header">
      <h1 class="cms-page-title">{{ $page->title }}</h1>
    </header>

    <div class="cms-page-body">
      {!! $page->content_html !!}
    </div>

    <a href="{{ route('store.home', ['universo' => $activeUniverse]) }}" class="cms-back-btn">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
      <span>VOLVER A LA TIENDA</span>
    </a>
  </article>

</div>
@endsection
