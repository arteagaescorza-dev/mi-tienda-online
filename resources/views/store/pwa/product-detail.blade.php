@extends('layouts.store-pwa')

@section('seo')
  <link rel="canonical" href="{{ url('/productos/' . ($product['slug'] ?? $product['id'])) }}">
  <meta property="og:type" content="product">
  <meta property="og:title" content="{{ $product['name'] }} | {{ $currentStorefront['brandName'] ?? 'TORZO' }}">
  <meta property="og:description" content="{{ Str::limit(strip_tags($product['description']), 160) }}">
  <meta property="og:image" content="{{ asset($product['image']) }}">
  <meta property="product:price:amount" content="{{ $product['price'] }}">
  <meta property="product:price:currency" content="MXN">

  <!-- Schema.org JSON-LD para SEO Oficial -->
  <script type="application/ld+json">
  {!! json_encode([
    '@context' => 'https://schema.org/',
    '@type' => 'Product',
    'name' => $product['name'],
    'image' => [asset($product['image'])],
    'description' => strip_tags($product['description']),
    'sku' => $product['sku'],
    'brand' => [
      '@type' => 'Brand',
      'name' => $product['brand'] ?? 'TORZO',
    ],
    'offers' => [
      '@type' => 'Offer',
      'url' => url('/productos/' . ($product['slug'] ?? $product['id'])),
      'priceCurrency' => 'MXN',
      'price' => $product['price'],
      'priceValidUntil' => '2026-12-31',
      'availability' => ($product['availableStock'] ?? 0) > 0 ? 'https://schema.org/InStock' : 'https://schema.org/PreOrder',
      'itemCondition' => 'https://schema.org/NewCondition',
    ],
  ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
  </script>
@endsection

@push('styles')
<style>
  /* ==========================================================================
     DEDICATED FULL-SCREEN PDP (STANDALONE SIN DRAWERS)
     Mobile-First Responsive Layout & Strict Zero Emojis Policy
     ========================================================================== */
  .pdp-page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 16px 16px 100px;
  }

  .pdp-breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.75rem;
    color: var(--text-muted, #71717a);
    margin-bottom: 20px;
    flex-wrap: wrap;
  }
  .pdp-breadcrumb-nav a {
    color: var(--text-muted, #71717a);
    text-decoration: none;
    transition: color 0.2s ease;
  }
  .pdp-breadcrumb-nav a:hover {
    color: var(--brand-primary, #09090b);
  }
  .pdp-breadcrumb-sep {
    font-size: 0.65rem;
    opacity: 0.5;
  }
  .pdp-breadcrumb-current {
    color: var(--text-primary, #18181b);
    font-weight: 600;
  }

  /* Two Column Grid for Desktop / Single Column for Mobile */
  .pdp-main-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
  }
  @media (min-width: 900px) {
    .pdp-main-grid {
      grid-template-columns: 1.1fr 1fr;
      gap: 48px;
      align-items: start;
    }
  }

  /* Media Gallery */
  .pdp-media-wrapper {
    position: relative;
    width: 100%;
  }
  .pdp-main-image-card {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    background-color: var(--brand-surface, #f4f4f5);
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(0, 0, 0, 0.06);
    cursor: zoom-in;
    user-select: none;
  }
  .pdp-image-zoom-trigger {
    position: absolute;
    bottom: 14px;
    right: 14px;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(9, 9, 11, 0.75);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    backdrop-filter: blur(8px);
    transition: all 0.2s ease;
    z-index: 5;
  }
  .pdp-image-zoom-trigger:hover {
    background: rgba(9, 9, 11, 0.95);
    transform: scale(1.06);
  }
  .pdp-main-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .pdp-image-stock-badge {
    position: absolute;
    top: 14px;
    left: 14px;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 5px 12px;
    border-radius: 999px;
    background: rgba(9, 9, 11, 0.85);
    color: #ffffff;
    backdrop-filter: blur(8px);
  }
  .pdp-image-stock-badge.ready {
    background: #059669;
    color: #ffffff;
  }
  .pdp-image-stock-badge.on_demand {
    background: #d97706;
    color: #ffffff;
  }

  /* Thumbnails strip */
  .pdp-thumbs-strip {
    display: flex;
    gap: 10px;
    margin-top: 12px;
    overflow-x: auto;
    padding-bottom: 4px;
  }
  .pdp-thumb-btn {
    width: 70px;
    height: 70px;
    border-radius: 8px;
    border: 2px solid transparent;
    padding: 0;
    overflow: hidden;
    background: #f4f4f5;
    cursor: pointer;
    flex-shrink: 0;
  }
  .pdp-thumb-btn.active {
    border-color: var(--brand-primary, #09090b);
  }
  .pdp-thumb-btn img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  /* Product Info Column */
  .pdp-info-col {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }
  .pdp-tag-row {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .pdp-universe-pill {
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    background: var(--brand-primary, #09090b);
    color: #ffffff;
    padding: 3px 10px;
    border-radius: 4px;
  }
  .pdp-category-pill {
    font-size: 0.68rem;
    font-weight: 700;
    color: var(--text-muted, #71717a);
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .pdp-title {
    font-family: var(--font-heading, 'Outfit', sans-serif);
    font-size: 1.65rem;
    font-weight: 800;
    line-height: 1.2;
    color: var(--text-primary, #09090b);
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.02em;
  }
  @media (min-width: 768px) {
    .pdp-title {
      font-size: 2.1rem;
    }
  }

  .pdp-meta-bar {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 0.78rem;
    color: var(--text-muted, #71717a);
    padding-bottom: 14px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.07);
  }
  .pdp-sku-badge {
    font-family: monospace;
    font-weight: 600;
    color: #52525b;
  }

  .pdp-pricing-box {
    display: flex;
    align-items: baseline;
    gap: 12px;
    flex-wrap: wrap;
  }
  .pdp-price-amount {
    font-family: var(--font-heading, 'Outfit', sans-serif);
    font-size: 1.85rem;
    font-weight: 800;
    color: var(--brand-primary, #09090b);
  }
  .pdp-tax-badge {
    font-size: 0.72rem;
    color: #059669;
    font-weight: 700;
    background: rgba(5, 150, 105, 0.1);
    padding: 3px 8px;
    border-radius: 4px;
  }

  /* Size Selector */
  .pdp-section-heading {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-primary, #18181b);
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .pdp-size-btn-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }
  .pdp-size-pill {
    min-width: 52px;
    height: 44px;
    padding: 0 14px;
    border: 1.5px solid #d4d4d8;
    background: #ffffff;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 700;
    color: #18181b;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .pdp-size-pill:hover {
    border-color: #09090b;
  }
  .pdp-size-pill.selected {
    background: #09090b;
    color: #ffffff;
    border-color: #09090b;
  }

  /* Live Stock Info Box */
  .pdp-stock-notice-box {
    padding: 12px 14px;
    border-radius: 10px;
    background: #f4f4f5;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .pdp-stock-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #059669;
    flex-shrink: 0;
  }
  .pdp-stock-dot.on_demand {
    background: #d97706;
  }

  /* Quantity & Add to Cart Controls */
  .pdp-actions-container {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-top: 8px;
  }
  .pdp-qty-stepper {
    display: inline-flex;
    align-items: center;
    border: 1.5px solid #d4d4d8;
    border-radius: 8px;
    height: 48px;
    background: #ffffff;
  }
  .pdp-qty-btn {
    width: 40px;
    height: 100%;
    border: none;
    background: transparent;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #18181b;
  }
  .pdp-qty-input {
    width: 40px;
    border: none;
    text-align: center;
    font-weight: 700;
    font-size: 0.95rem;
    background: transparent;
  }

  .pdp-btn-add-main {
    flex: 1;
    height: 48px;
    background: var(--brand-primary, #09090b);
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: background 0.2s ease;
  }
  .pdp-btn-add-main:hover {
    background: #27272a;
  }

  /* ==========================================================================
     PDP IMAGE GALLERY MODAL & SWIPE (PROJECT_RULES PDP RULE 2)
     ========================================================================== */
  .pdp-gallery-modal {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    margin: 0;
    width: 100vw;
    height: 100vh;
    touch-action: pan-y;
  }
  .pdp-gallery-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(9, 9, 11, 0.94);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
  }
  .pdp-gallery-content {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    z-index: 2;
    overflow: hidden;
  }
  .pdp-gallery-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    z-index: 10;
  }
  .pdp-gallery-counter {
    font-family: var(--font-heading, sans-serif);
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: #f4f4f5;
    background: rgba(255, 255, 255, 0.1);
    padding: 4px 12px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.15);
  }
  .pdp-gallery-close-btn {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .pdp-gallery-close-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: scale(1.05);
  }
  .pdp-gallery-viewport {
    position: relative;
    flex: 1;
    width: 100%;
    height: 100%;
    overflow: hidden;
    display: flex;
    align-items: center;
    cursor: grab;
    user-select: none;
    -webkit-user-select: none;
    touch-action: none;
  }
  .pdp-gallery-viewport:active {
    cursor: grabbing;
  }
  .pdp-gallery-track {
    display: flex;
    width: 100%;
    height: 100%;
    transition: transform 0.3s cubic-bezier(0.25, 1, 0.5, 1);
    will-change: transform;
  }
  .pdp-gallery-slide {
    min-width: 100%;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    box-sizing: border-box;
  }
  .pdp-gallery-img {
    max-width: 92vw;
    max-height: 75vh;
    object-fit: contain;
    border-radius: 12px;
    pointer-events: none;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
  }
  .pdp-gallery-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 10;
  }
  .pdp-gallery-nav:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-50%) scale(1.08);
  }
  .pdp-gallery-prev {
    left: 20px;
  }
  .pdp-gallery-next {
    right: 20px;
  }
  @media (max-width: 640px) {
    .pdp-gallery-nav {
      display: none;
    }
  }
  .pdp-gallery-dots {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 16px 20px 24px;
    z-index: 10;
  }
  .pdp-gallery-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.3);
    border: none;
    padding: 0;
    cursor: pointer;
    transition: all 0.25s ease;
  }
  .pdp-gallery-dot.active {
    width: 24px;
    background: #ffffff;
  }

  /* Pillars Grid */
  .pdp-pillars-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
    margin-top: 10px;
    padding: 16px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
  }
  @media (min-width: 600px) {
    .pdp-pillars-grid {
      grid-template-columns: repeat(3, 1fr);
    }
  }
  .pdp-pillar-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.74rem;
    color: #334155;
    line-height: 1.3;
  }

  /* Accordions Section */
  .pdp-accordions-group {
    margin-top: 32px;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  .pdp-accordion-card {
    border: 1px solid #e4e4e7;
    border-radius: 12px;
    overflow: hidden;
    background: #ffffff;
  }
  .pdp-accordion-header {
    width: 100%;
    padding: 16px 20px;
    background: #fafafa;
    border: none;
    text-align: left;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    font-family: var(--font-heading, 'Outfit', sans-serif);
    font-size: 0.85rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #18181b;
  }
  .pdp-accordion-header:hover {
    background: #f4f4f5;
  }
  .pdp-accordion-icon {
    transition: transform 0.2s ease;
  }
  .pdp-accordion-card.open .pdp-accordion-icon {
    transform: rotate(180deg);
  }
  .pdp-accordion-body {
    display: none;
    padding: 20px;
    font-size: 0.85rem;
    line-height: 1.6;
    color: #3f3f46;
    border-top: 1px solid #f4f4f5;
  }
  .pdp-accordion-card.open .pdp-accordion-body {
    display: block;
  }

  /* Guia de tallas table styling */
  .torzo-size-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8rem;
    margin-top: 10px;
  }
  .torzo-size-table th, .torzo-size-table td {
    padding: 10px 12px;
    text-align: left;
    border-bottom: 1px solid #e4e4e7;
  }
  .torzo-size-table th {
    background: #f4f4f5;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.72rem;
    letter-spacing: 0.05em;
  }

  /* Sticky Mobile Bottom Add Bar */
  .pdp-sticky-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #ffffff;
    border-top: 1px solid #e4e4e7;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    z-index: 90;
    box-shadow: 0 -4px 16px rgba(0,0,0,0.06);
  }
  @media (min-width: 900px) {
    .pdp-sticky-bar {
      display: none;
    }
  }
  .pdp-sticky-info {
    display: flex;
    flex-direction: column;
  }
  .pdp-sticky-title {
    font-size: 0.78rem;
    font-weight: 700;
    color: #18181b;
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .pdp-sticky-price {
    font-size: 0.88rem;
    font-weight: 800;
    color: var(--brand-primary, #09090b);
  }
</style>
@endpush

@section('content')
<div class="pdp-page-container">
  
  <!-- Breadcrumbs de Navegación -->
  <nav class="pdp-breadcrumb-nav" aria-label="Ruta de navegación">
    <a href="{{ route('store.home', ['universo' => $activeUniverse]) }}">INICIO</a>
    <span class="pdp-breadcrumb-sep">/</span>
    <a href="{{ route('store.home', ['universo' => $activeUniverse]) }}#catalogo">{{ strtoupper($product['categoryName'] ?? 'CATÁLOGO') }}</a>
    <span class="pdp-breadcrumb-sep">/</span>
    <span class="pdp-breadcrumb-current">{{ $product['name'] }}</span>
  </nav>

  <div class="pdp-main-grid">

    <!-- COLUMNA 1: GALERÍA DE IMÁGENES -->
    <div class="pdp-media-wrapper">
      <div class="pdp-main-image-card" onclick="openPdpGalleryModal(currentPdpImageIndex)" role="button" tabindex="0" title="Click para ampliar imagen" aria-label="Ampliar galería de imágenes">
        <span id="pdpStockBadge" class="pdp-image-stock-badge {{ $product['stockType'] }}">
          {{ $product['stockLabel'] }}
        </span>

        @if(!empty($product['image']) && $product['image'] !== '/user-avatar.png')
          <img id="pdpMainImg" src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}" class="pdp-main-img" onerror="this.onerror=null; this.src='{{ asset('user-avatar.png') }}';">
        @else
          <!-- Silhouette SVG oficial cuando la prenda aún no tiene foto de estudio -->
          <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; color: #a1a1aa; width: 100%; height: 100%; padding: 40px;">
            <svg width="64" height="64" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="6" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span style="font-family: var(--font-heading); font-size: 0.8rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">TORZO // COLECCIÓN OFICIAL</span>
            <small style="font-size: 0.72rem; color: #a1a1aa;">{{ $product['sku'] }}</small>
          </div>
        @endif

        <button type="button" class="pdp-image-zoom-trigger" onclick="event.stopPropagation(); openPdpGalleryModal(currentPdpImageIndex);" aria-label="Ampliar imagen de producto">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
        </button>
      </div>

      @if(count($product['images']) > 1)
        <div class="pdp-thumbs-strip">
          @foreach($product['images'] as $thumb)
            <button type="button" class="pdp-thumb-btn {{ $loop->first ? 'active' : '' }}" onclick="switchPdpImage('{{ asset($thumb) }}', this, {{ $loop->index }})" aria-label="Ver miniatura {{ $loop->iteration }}">
              <img src="{{ asset($thumb) }}" alt="Miniatura {{ $loop->iteration }}">
            </button>
          @endforeach
        </div>
      @endif
    </div>

    <!-- COLUMNA 2: DETALLES, PRECIO Y SELECTOR DE TALLAS -->
    <div class="pdp-info-col">
      <div class="pdp-tag-row">
        <span class="pdp-universe-pill">{{ strtoupper($activeUniverse) }}</span>
        <span class="pdp-category-pill">{{ $product['categoryName'] }}</span>
      </div>

      <h1 class="pdp-title">{{ $product['name'] }}</h1>

      <div class="pdp-meta-bar">
        <span>SKU: <span id="pdpCurrentSku" class="pdp-sku-badge">{{ $product['sku'] }}</span></span>
        <span>•</span>
        <span>Marca: <strong>{{ $product['brand'] }}</strong></span>
      </div>

      <div class="pdp-pricing-box">
        <span class="pdp-price-amount" id="pdpDisplayPrice">{{ $product['price_formatted'] }}</span>
        <span class="pdp-tax-badge">IVA 16% INCLUIDO</span>
      </div>

      <!-- Selector de Tallas con Conexión a Stock Real de Hub 4 -->
      <div>
        <div class="pdp-section-heading">
          <span>Selecciona tu Talla</span>
          <button type="button" style="background:none; border:none; color:var(--text-muted); font-size:0.75rem; text-decoration:underline; cursor:pointer;" onclick="toggleAccordion('accSizeGuide')">
            Ver Medidas Torzo
          </button>
        </div>

        <div class="pdp-size-btn-group" id="pdpSizeGroup">
          @foreach($product['sizes'] as $sz)
            @php
              $vData = collect($product['variants'])->firstWhere('size', $sz);
              $isAvailable = $vData ? ($vData['available'] > 0) : true;
            @endphp
            <button type="button"
                    class="pdp-size-pill {{ $loop->first ? 'selected' : '' }}"
                    data-size="{{ $sz }}"
                    data-sku="{{ $vData['sku'] ?? $product['sku'] }}"
                    data-available="{{ $vData['available'] ?? $product['availableStock'] }}"
                    data-stock-label="{{ $isAvailable ? 'Stock Físico' : 'Bajo Pedido' }}"
                    data-stock-type="{{ $isAvailable ? 'ready' : 'on_demand' }}"
                    onclick="selectPdpSize(this)">
              {{ $sz }}
            </button>
          @endforeach
        </div>
      </div>

      <!-- Estado en Vivo del Inventario Seleccionado -->
      <div class="pdp-stock-notice-box" id="pdpStockNoticeBox">
        <span class="pdp-stock-dot {{ $product['stockType'] }}" id="pdpStockDot"></span>
        <div id="pdpStockNoticeText">
          @if(($product['availableStock'] ?? 0) > 0)
            <strong>En existencia física:</strong> {{ $product['availableStock'] }} piezas listas para despacho express inmediato.
          @else
            <strong>Confección bajo pedido:</strong> Se produce y despacha en 3-5 días hábiles.
          @endif
        </div>
      </div>

      <!-- Selector de Cantidad y Botón de Agregar -->
      <div class="pdp-actions-container">
        <div class="pdp-qty-stepper">
          <button type="button" class="pdp-qty-btn" onclick="adjustPdpQty(-1)" aria-label="Disminuir cantidad">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <input type="number" id="pdpQtyInput" class="pdp-qty-input" value="1" min="1" max="10" readonly>
          <button type="button" class="pdp-qty-btn" onclick="adjustPdpQty(1)" aria-label="Aumentar cantidad">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
        </div>

        <button type="button" class="pdp-btn-add-main" id="pdpAddBtn" onclick="submitPdpToCart()">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>AGREGAR AL CARRITO</span>
        </button>
      </div>



      <!-- Pilares de Confianza Courier & Sanitario -->
      <div class="pdp-pillars-grid">
        <div class="pdp-pillar-item">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
          <div><strong>Envío Express</strong><br>Tarifa plana $149 (Gratis +$999)</div>
        </div>
        <div class="pdp-pillar-item">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
          <div><strong>Empaque Discreto</strong><br>Caja neutra termosellada</div>
        </div>
        <div class="pdp-pillar-item">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M7 15h3M15 15h2"/></svg>
          <div><strong>Pago Seguro</strong><br>SPEI BBVA directo sin comisión</div>
        </div>
      </div>

      <!-- Ficha Técnica de Composición Textil -->
      <div style="background: #fafafa; padding: 18px; border-radius: 12px; border: 1px solid #f4f4f5; font-size: 0.82rem; line-height: 1.5;">
        <strong style="text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.08em; display: block; margin-bottom: 6px; color: #52525b;">Composición Textil Oficial</strong>
        <p style="margin: 0; color: #27272a;">{{ $product['fabricComposition'] }}</p>
      </div>

    </div>
  </div>

  <!-- SECCIÓN DE DOCUMENTOS OFICIALES & GUÍAS CMS (Acordeones Desplegables) -->
  <div class="pdp-accordions-group">
    
    <!-- 1. Descripción Completa de la Prenda -->
    <div class="pdp-accordion-card open" id="accDesc">
      <button type="button" class="pdp-accordion-header" onclick="toggleAccordion('accDesc')">
        <span>Descripción & Silueta Anatómica</span>
        <svg class="pdp-accordion-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="pdp-accordion-body">
        {!! nl2br(e($product['description'])) !!}
      </div>
    </div>

    <!-- 2. Guía Oficial de Tallas Torzo -->
    <div class="pdp-accordion-card" id="accSizeGuide">
      <button type="button" class="pdp-accordion-header" onclick="toggleAccordion('accSizeGuide')">
        <span>{{ $sizeGuideDoc->title ?? 'Guía Oficial de Tallas Torzo' }}</span>
        <svg class="pdp-accordion-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="pdp-accordion-body">
        @if(!empty($sizeGuideDoc?->body_html))
          {!! $sizeGuideDoc->body_html !!}
        @else
          <p>Consulta las equivalencias anatómicas en centímetros para un calce ergonómico sin opresión:</p>
          <div style="overflow-x: auto;">
            <table class="torzo-size-table">
              <thead>
                <tr>
                  <th>Talla</th>
                  <th>Cintura (cm)</th>
                  <th>Cadera (cm)</th>
                  <th>Equivalencia Pantalón</th>
                </tr>
              </thead>
              <tbody>
                <tr><td><strong>CH (Chica)</strong></td><td>70 - 76 cm</td><td>84 - 90 cm</td><td>28 - 30</td></tr>
                <tr><td><strong>M (Mediana)</strong></td><td>78 - 84 cm</td><td>92 - 98 cm</td><td>31 - 33</td></tr>
                <tr><td><strong>G (Grande)</strong></td><td>86 - 92 cm</td><td>100 - 106 cm</td><td>34 - 36</td></tr>
                <tr><td><strong>XG / XL</strong></td><td>94 - 102 cm</td><td>108 - 116 cm</td><td>38 - 40</td></tr>
                <tr><td><strong>2XG / 2XL</strong></td><td>104 - 112 cm</td><td>118 - 126 cm</td><td>42 - 44</td></tr>
                <tr><td><strong>3XG / 3XL</strong></td><td>114 - 124 cm</td><td>128 - 138 cm</td><td>46 - 48</td></tr>
              </tbody>
            </table>
          </div>
          <small style="color: #71717a; display: block; margin-top: 10px;">Si estás entre dos tallas, te recomendamos elegir la talla mayor para mayor confort diario.</small>
        @endif
      </div>
    </div>

    <!-- 3. Protocolo de Cuidado de Ropa Delicada -->
    <div class="pdp-accordion-card" id="accCare">
      <button type="button" class="pdp-accordion-header" onclick="toggleAccordion('accCare')">
        <span>{{ $careProtocolDoc->title ?? 'Protocolo de Lavado & Cuidado de Prendas' }}</span>
        <svg class="pdp-accordion-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="pdp-accordion-body">
        @if(!empty($careProtocolDoc?->body_html))
          {!! $careProtocolDoc->body_html !!}
        @else
          <ul>
            <li><strong>Temperatura de lavado:</strong> Lavar a mano o ciclo suave a máquina con agua fría (máximo 30°C).</li>
            <li><strong>Detergente:</strong> Usar jabón líquido neutro sin blanqueadores de cloro.</li>
            <li><strong>Elásticos y jacquards:</strong> No exprimir ni retorcer bruscamente para preservar la tensión anatómica.</li>
            <li><strong>Secado:</strong> Secar extendido en plano horizontal a la sombra. Prohibido secadora térmica.</li>
            <li><strong>Planchado:</strong> No planchar los elásticos ni apliques de goma.</li>
          </ul>
        @endif
      </div>
    </div>

    <!-- 4. Aviso Sanitario y Normativo PROFECO -->
    <div class="pdp-accordion-card" id="accProfeco">
      <button type="button" class="pdp-accordion-header" onclick="toggleAccordion('accProfeco')">
        <span>{{ $profecoDoc->title ?? 'Aviso Normativo y Sanitario PROFECO' }}</span>
        <svg class="pdp-accordion-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="pdp-accordion-body">
        @if(!empty($profecoDoc?->body_html))
          {!! $profecoDoc->body_html !!}
        @else
          <p>
            Por estrictas disposiciones de salubridad y la regulación de la Procuraduría Federal del Consumidor (PROFECO) para artículos de uso íntimo y personal:
          </p>
          <p>
            Todas las prendas cuentan con un <strong>precinto higiénico termosellado de seguridad</strong>. No se aceptan cambios ni devoluciones una vez retirado o violado dicho precinto, salvaguardando la máxima higiene para toda nuestra comunidad. En caso de defecto de fabricación con precinto intacto, cuentas con 7 días naturales tras la entrega para reposición sin costo.
          </p>
        @endif
      </div>
    </div>

  </div>

</div>

<!-- Barra Fija Inferior para Móviles (Sticky Mobile Bar) -->
<div class="pdp-sticky-bar" id="pdpStickyBar">
  <div class="pdp-sticky-info">
    <span class="pdp-sticky-title">{{ $product['name'] }}</span>
    <span class="pdp-sticky-price" id="pdpStickyPrice">{{ $product['price_formatted'] }}</span>
  </div>
  <button type="button" class="pdp-btn-add-main" style="flex: initial; padding: 0 20px;" onclick="submitPdpToCart()">
    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    <span>AGREGAR</span>
  </button>
</div>

<!-- MODAL INTERACTIVO DE GALERÍA DE IMÁGENES CON SWIPE (REGLA PDP 2) -->
<div id="pdpGalleryModal" class="pdp-gallery-modal" aria-hidden="true" role="dialog" aria-modal="true" style="display: none;">
  <div class="pdp-gallery-backdrop" onclick="closePdpGalleryModal()"></div>
  <div class="pdp-gallery-content">
    <div class="pdp-gallery-header">
      <span class="pdp-gallery-counter" id="pdpGalleryCounter">1 / {{ max(1, count($product['images'] ?? [])) }}</span>
      <button type="button" class="pdp-gallery-close-btn" onclick="closePdpGalleryModal()" aria-label="Cerrar modal de imágenes">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <div class="pdp-gallery-viewport" id="pdpGalleryViewport">
      <div class="pdp-gallery-track" id="pdpGalleryTrack">
        @forelse($product['images'] as $idx => $imgUrl)
          <div class="pdp-gallery-slide" data-index="{{ $idx }}">
            <img src="{{ asset($imgUrl) }}" alt="{{ $product['name'] }} - Vista {{ $idx + 1 }}" loading="lazy" class="pdp-gallery-img" onerror="this.onerror=null; this.src='{{ asset('user-avatar.png') }}';">
          </div>
        @empty
          <div class="pdp-gallery-slide" data-index="0">
            <img src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}" class="pdp-gallery-img" onerror="this.onerror=null; this.src='{{ asset('user-avatar.png') }}';">
          </div>
        @endforelse
      </div>
    </div>

    @if(count($product['images'] ?? []) > 1)
      <button type="button" class="pdp-gallery-nav pdp-gallery-prev" onclick="navigatePdpGallery(-1)" aria-label="Imagen anterior">
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <button type="button" class="pdp-gallery-nav pdp-gallery-next" onclick="navigatePdpGallery(1)" aria-label="Imagen siguiente">
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </button>

      <div class="pdp-gallery-dots" id="pdpGalleryDots">
        @foreach($product['images'] as $idx => $imgUrl)
          <button type="button" class="pdp-gallery-dot {{ $loop->first ? 'active' : '' }}" onclick="goToPdpGallerySlide({{ $idx }})" aria-label="Ir a imagen {{ $idx + 1 }}"></button>
        @endforeach
      </div>
    @endif
  </div>
</div>
@endsection

@push('scripts')
<script>
  let currentSelectedSize = @json($product['sizes'][0] ?? 'M');
  const productId = @json($product['id']);
  const productName = @json($product['name']);

  function selectPdpSize(btn) {
    document.querySelectorAll('.pdp-size-pill').forEach(el => el.classList.remove('selected'));
    btn.classList.add('selected');
    currentSelectedSize = btn.dataset.size;

    // Actualizar SKU dinámico
    if (btn.dataset.sku) {
      document.getElementById('pdpCurrentSku').textContent = btn.dataset.sku;
    }

    // Actualizar Estado de Stock
    const available = parseInt(btn.dataset.available || '0', 10);
    const stockType = btn.dataset.stockType || 'ready';
    const stockLabel = btn.dataset.stockLabel || 'Stock Físico';

    const dot = document.getElementById('pdpStockDot');
    const badge = document.getElementById('pdpStockBadge');
    const noticeText = document.getElementById('pdpStockNoticeText');

    dot.className = 'pdp-stock-dot ' + stockType;
    if (badge) {
      badge.className = 'pdp-image-stock-badge ' + stockType;
      badge.textContent = stockLabel;
    }

    if (available > 0) {
      noticeText.innerHTML = '<strong>En existencia física:</strong> ' + available + ' piezas listas para despacho express inmediato.';
    } else {
      noticeText.innerHTML = '<strong>Confección bajo pedido:</strong> Se produce y despacha en 3-5 días hábiles.';
    }

    // Actualizar link de WhatsApp con la talla seleccionada
    const waLink = document.getElementById('pdpWhatsappLink');
    if (waLink) {
      const msg = 'Hola, me interesa adquirir la prenda ' + productName + ' en talla ' + currentSelectedSize + ' (SKU: ' + (btn.dataset.sku || '') + ')';
      waLink.href = 'https://wa.me/5215500000000?text=' + encodeURIComponent(msg);
    }
  }

  function adjustPdpQty(delta) {
    const input = document.getElementById('pdpQtyInput');
    let val = parseInt(input.value, 10) || 1;
    val = Math.max(1, Math.min(10, val + delta));
    input.value = val;
  }

  function submitPdpToCart() {
    const qty = parseInt(document.getElementById('pdpQtyInput').value, 10) || 1;
    
    // Si la función global addToCart de store-pwa.js está disponible
    if (typeof window.addToCart === 'function') {
      for (let i = 0; i < qty; i++) {
        window.addToCart(productId, currentSelectedSize);
      }
      if (typeof openDrawer === 'function') {
        openDrawer('cartSheet');
      }
    } else {
      // Fallback nativo
      alert('Prenda agregada a tu carrito: ' + productName + ' (Talla: ' + currentSelectedSize + ' x' + qty + ')');
    }
  }

  let currentPdpImageIndex = 0;
  const pdpGalleryTotal = {{ max(1, count($product['images'] ?? [])) }};

  function switchPdpImage(src, btn, index = 0) {
    const mainImg = document.getElementById('pdpMainImg');
    if (mainImg) {
      mainImg.src = src;
    }
    document.querySelectorAll('.pdp-thumb-btn').forEach(b => b.classList.remove('active'));
    if (btn) {
      btn.classList.add('active');
    }
    currentPdpImageIndex = index;
  }

  function openPdpGalleryModal(index = 0) {
    currentPdpImageIndex = (index >= 0 && index < pdpGalleryTotal) ? index : 0;
    const modal = document.getElementById('pdpGalleryModal');
    if (!modal) return;
    
    updatePdpGalleryTrack(false);
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closePdpGalleryModal() {
    const modal = document.getElementById('pdpGalleryModal');
    if (!modal) return;
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  function goToPdpGallerySlide(index, animate = true) {
    if (pdpGalleryTotal <= 0) return;
    if (index < 0) {
      currentPdpImageIndex = 0;
    } else if (index >= pdpGalleryTotal) {
      currentPdpImageIndex = pdpGalleryTotal - 1;
    } else {
      currentPdpImageIndex = index;
    }
    updatePdpGalleryTrack(animate);
  }

  function navigatePdpGallery(step) {
    if (pdpGalleryTotal <= 1) return;
    let next = currentPdpImageIndex + step;
    if (next < 0) next = pdpGalleryTotal - 1;
    else if (next >= pdpGalleryTotal) next = 0;
    goToPdpGallerySlide(next, true);
  }

  function updatePdpGalleryTrack(animate = true) {
    const track = document.getElementById('pdpGalleryTrack');
    const counter = document.getElementById('pdpGalleryCounter');
    const dots = document.querySelectorAll('.pdp-gallery-dot');

    if (track) {
      track.style.transition = animate ? 'transform 0.3s cubic-bezier(0.25, 1, 0.5, 1)' : 'none';
      track.style.transform = `translateX(-${currentPdpImageIndex * 100}%)`;
    }

    if (counter) {
      counter.textContent = `${currentPdpImageIndex + 1} / ${pdpGalleryTotal}`;
    }

    dots.forEach((dot, idx) => {
      dot.classList.toggle('active', idx === currentPdpImageIndex);
    });
  }

  // Soporte de Swipe Táctil y Arrastre en Galería Modal (PROJECT_RULES PDP Regla 2)
  (function initPdpGallerySwipe() {
    const viewport = document.getElementById('pdpGalleryViewport');
    const track = document.getElementById('pdpGalleryTrack');
    if (!viewport || !track) return;

    let startX = 0;
    let startY = 0;
    let currentX = 0;
    let isTouching = false;
    let isHorizontalSwipe = false;
    let startTime = 0;

    // EVENTOS TOUCH
    viewport.addEventListener('touchstart', (e) => {
      if (e.touches.length !== 1) return;
      isTouching = true;
      isHorizontalSwipe = false;
      startX = e.touches[0].clientX;
      startY = e.touches[0].clientY;
      currentX = startX;
      startTime = Date.now();
      track.style.transition = 'none';
    }, { passive: true });

    viewport.addEventListener('touchmove', (e) => {
      if (!isTouching || e.touches.length !== 1) return;
      currentX = e.touches[0].clientX;
      const currentY = e.touches[0].clientY;
      const diffX = currentX - startX;
      const diffY = currentY - startY;

      if (!isHorizontalSwipe) {
        if (Math.abs(diffX) > 10 && Math.abs(diffX) > Math.abs(diffY)) {
          isHorizontalSwipe = true;
        }
      }

      if (isHorizontalSwipe) {
        if (e.cancelable) e.preventDefault();
        const viewportWidth = viewport.clientWidth || 360;
        const dragPercent = (diffX / viewportWidth) * 100;
        const baseTranslate = -(currentPdpImageIndex * 100);
        track.style.transform = `translateX(${baseTranslate + dragPercent}%)`;
      }
    }, { passive: false });

    viewport.addEventListener('touchend', (e) => {
      if (!isTouching) return;
      isTouching = false;
      const diffX = (e.changedTouches ? e.changedTouches[0].clientX : currentX) - startX;
      const elapsed = Date.now() - startTime;
      const threshold = 45;
      const isQuickFlick = elapsed < 300 && Math.abs(diffX) > 20;

      if (isHorizontalSwipe && (Math.abs(diffX) > threshold || isQuickFlick)) {
        if (diffX < 0) {
          navigatePdpGallery(1); // Deslizar izquierda -> Siguiente
        } else {
          navigatePdpGallery(-1); // Deslizar derecha -> Anterior
        }
      } else {
        updatePdpGalleryTrack(true);
      }
      isHorizontalSwipe = false;
    }, { passive: true });

    // ARRASTRE CON RATÓN (MOUSE DRAG)
    let isMouseDown = false;
    viewport.addEventListener('mousedown', (e) => {
      isMouseDown = true;
      startX = e.clientX;
      currentX = startX;
      startTime = Date.now();
      track.style.transition = 'none';
    });

    window.addEventListener('mousemove', (e) => {
      if (!isMouseDown) return;
      currentX = e.clientX;
      const diffX = currentX - startX;
      const viewportWidth = viewport.clientWidth || 360;
      const dragPercent = (diffX / viewportWidth) * 100;
      const baseTranslate = -(currentPdpImageIndex * 100);
      track.style.transform = `translateX(${baseTranslate + dragPercent}%)`;
    });

    window.addEventListener('mouseup', (e) => {
      if (!isMouseDown) return;
      isMouseDown = false;
      const diffX = currentX - startX;
      const elapsed = Date.now() - startTime;
      if (Math.abs(diffX) > 40 || (elapsed < 300 && Math.abs(diffX) > 20)) {
        if (diffX < 0) {
          navigatePdpGallery(1);
        } else {
          navigatePdpGallery(-1);
        }
      } else {
        updatePdpGalleryTrack(true);
      }
    });

    // ATAJOS DE TECLADO
    window.addEventListener('keydown', (e) => {
      const modal = document.getElementById('pdpGalleryModal');
      if (!modal || modal.style.display === 'none') return;
      if (e.key === 'ArrowLeft') {
        navigatePdpGallery(-1);
      } else if (e.key === 'ArrowRight') {
        navigatePdpGallery(1);
      } else if (e.key === 'Escape') {
        closePdpGalleryModal();
      }
    });
  })();

  function toggleAccordion(id) {
    const card = document.getElementById(id);
    if (card) {
      card.classList.toggle('open');
    }
  }
</script>
@endpush
