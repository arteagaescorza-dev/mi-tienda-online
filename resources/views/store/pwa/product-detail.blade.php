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
  .pdp-image-stock-badge.drop_on_demand,
  .pdp-image-stock-badge.dropship {
    background: #d97706;
    color: #ffffff;
    border: 1px solid rgba(245, 158, 11, 0.5);
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
  .pdp-stock-dot.drop_on_demand,
  .pdp-stock-dot.dropship {
    background: #d97706;
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

  /* PDP Link Buttons & Info Modals */
  .pdp-meta-link-btn {
    background: none;
    border: none;
    color: var(--text-muted, #71717a);
    font-size: 0.74rem;
    font-weight: 600;
    text-decoration: underline;
    cursor: pointer;
    transition: color 0.15s ease;
    display: inline-flex;
    align-items: center;
    padding: 0;
  }
  .pdp-meta-link-btn:hover {
    color: var(--text-primary, #09090b);
  }
  .pdp-composition-care-btn {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    color: #1e293b;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 6px 12px;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
  }
  .pdp-composition-care-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
  }
  .pdp-info-modal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
  }
  .pdp-info-modal.active {
    display: flex !important;
  }
  .pdp-info-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
  }
  .pdp-info-dialog {
    position: relative;
    background: #ffffff;
    border-radius: 16px;
    width: 100%;
    max-width: 580px;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border: 1px solid #e2e8f0;
    z-index: 10;
    animation: pdpModalFadeIn 0.2s ease-out;
  }
  @keyframes pdpModalFadeIn {
    from { opacity: 0; transform: scale(0.96) translateY(8px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
  }
  .pdp-info-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 22px;
    border-bottom: 1px solid #f1f5f9;
  }
  .pdp-info-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .pdp-info-subtitle {
    font-size: 0.76rem;
    color: #64748b;
    margin-top: 3px;
  }
  .pdp-info-close {
    background: transparent;
    border: none;
    cursor: pointer;
    color: #94a3b8;
    padding: 6px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
  }
  .pdp-info-close:hover {
    color: #0f172a;
    background: #f1f5f9;
  }
  .pdp-info-body {
    padding: 22px;
  }
  .pdp-symbol-card {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
  }
  .pdp-symbol-icon {
    width: 22px;
    height: 22px;
    color: #4f46e5;
    flex-shrink: 0;
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

  /* Botones de acción superiores PDP */
  .pdp-action-nav-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    background: var(--bg-surface, #ffffff);
    border: 1.5px solid var(--border-medium, #e4e4e7);
    border-radius: 8px;
    font-size: 0.76rem;
    font-weight: 700;
    color: var(--text-primary, #18181b);
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
  }
  .pdp-action-nav-btn:hover {
    background: var(--bg-tertiary, #f4f4f5);
  }

  /* Caja de composición textil oficial */
  .pdp-composition-box {
    background: var(--bg-surface, #fafafa);
    padding: 18px;
    border-radius: 12px;
    border: 1px solid var(--border-subtle, #f4f4f5);
    font-size: 0.82rem;
    line-height: 1.5;
  }

  /* Botones de swatch de color PDP */
  .pdp-color-swatch-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 12px;
    border-radius: 8px;
    border: 1.5px solid var(--border-medium, #d4d4d8);
    background: var(--bg-surface, #ffffff);
    cursor: pointer;
    transition: all 0.15s ease;
  }
  .pdp-color-swatch-btn:hover {
    background: var(--bg-tertiary, #f4f4f5);
  }
  .pdp-color-swatch-btn.selected {
    border-color: var(--brand-primary, #09090b);
    background: var(--bg-tertiary, #fafafa);
  }
  .pdp-color-swatch-btn .pdp-color-name-text {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--text-primary, #18181b);
  }

  /* ESTILOS DARK MODE PARA PDP (Multitonos oscuros con profundidad zinc) */
  html.dark .pdp-page-container {
    color: #f4f4f6;
  }
  html.dark .pdp-action-nav-btn {
    background: #18181f !important;
    border-color: #2e2e3c !important;
    color: #f4f4f6 !important;
  }
  html.dark .pdp-action-nav-btn:hover {
    background: #252532 !important;
    border-color: #49495c !important;
  }
  html.dark .pdp-main-image-card {
    background: #121216 !important;
    border-color: #272732 !important;
  }
  html.dark .pdp-pillars-grid {
    background: #18181f !important;
    border-color: #272732 !important;
  }
  html.dark .pdp-pillar-item {
    color: #cbd5e1 !important;
  }
  html.dark .pdp-pillar-item strong {
    color: #f4f4f6 !important;
  }
  html.dark .pdp-composition-box {
    background: #18181f !important;
    border-color: #272732 !important;
  }
  html.dark .pdp-composition-box p {
    color: #f4f4f6 !important;
  }
  html.dark .pdp-accordion-card {
    background: #121216 !important;
    border-color: #272732 !important;
  }
  html.dark .pdp-accordion-header {
    background: #18181f !important;
    color: #f4f4f6 !important;
  }
  html.dark .pdp-accordion-header:hover {
    background: #22222b !important;
  }
  html.dark .pdp-accordion-body {
    background: #121216 !important;
    color: #cbd5e1 !important;
    border-top: 1px solid #272732 !important;
  }
  html.dark .torzo-size-table th {
    background: #22222b !important;
    color: #f4f4f6 !important;
    border-color: #3b3b4a !important;
  }
  html.dark .torzo-size-table td {
    background: #18181f !important;
    color: #cbd5e1 !important;
    border-bottom: 1px solid #272732 !important;
  }
  html.dark .pdp-sticky-bar {
    background: #121216 !important;
    border-top-color: #272732 !important;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.5) !important;
  }
  html.dark .pdp-sticky-title {
    color: #f4f4f6 !important;
  }
  html.dark .pdp-sticky-price {
    color: #ffffff !important;
  }
  html.dark .pdp-qty-stepper {
    background: #18181f !important;
    border-color: #2e2e3c !important;
  }
  html.dark .pdp-qty-stepper button,
  html.dark .pdp-qty-stepper span {
    color: #f4f4f6 !important;
  }
  html.dark .pdp-color-swatch-btn {
    background: #18181f !important;
    border-color: #3b3b4a !important;
  }
  html.dark .pdp-color-swatch-btn:hover {
    background: #22222b !important;
  }
  html.dark .pdp-color-swatch-btn.selected {
    background: #2b2b37 !important;
    border-color: #f4f4f6 !important;
  }
  html.dark .pdp-color-swatch-btn .pdp-color-name-text {
    color: #f4f4f6 !important;
  }
  html.dark .pdp-breadcrumb-nav a {
    color: #9da0ae !important;
  }
  html.dark .pdp-breadcrumb-current {
    color: #f4f4f6 !important;
  }
  html.dark .pdp-title {
    color: #f4f4f6 !important;
  }
  html.dark .pdp-price-amount,
  html.dark #pdpDisplayPrice {
    color: #ffffff !important;
  }
  html.dark .pdp-meta-bar {
    border-bottom-color: #252938 !important;
    color: #94a3b8 !important;
  }
  html.dark .pdp-sku-badge {
    color: #cbd5e1 !important;
  }
  html.dark .pdp-section-heading {
    color: #f4f4f6 !important;
  }
  html.dark .pdp-size-pill {
    background: #18181f !important;
    border-color: #3b3b4a !important;
    color: #f4f4f6 !important;
  }
  html.dark .pdp-size-pill:hover {
    background: #22222b !important;
    border-color: #6366f1 !important;
  }
  html.dark .pdp-size-pill.selected {
    background: #f4f4f6 !important;
    color: #0c0d12 !important;
    border-color: #ffffff !important;
  }
  html.dark .pdp-stock-notice-box {
    background: #18181f !important;
    border: 1px solid #252938 !important;
    color: #cbd5e1 !important;
  }
  html.dark #pdpStockNoticeText,
  html.dark #pdpStockNoticeText strong {
    color: #f4f4f6 !important;
  }
</style>
@endpush

@section('content')
<div class="pdp-page-container">
  
  <!-- Regla 4: Botón Volver Atrás y Botón que Conmuta a Filtros -->
  <div class="pdp-top-actions-bar" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; gap: 8px;">
    <button type="button" class="pdp-action-nav-btn" onclick="if(window.history.length > 1) { window.history.back(); } else { window.location.href='{{ route('store.home', ['universo' => $activeUniverse]) }}'; }" aria-label="Volver atrás">
      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
      <span>Volver Atrás</span>
    </button>

    <button type="button" class="pdp-action-nav-btn" data-action="open-filters" onclick="window.openDrawer ? window.openDrawer('filterDrawer') : (document.getElementById('filterDrawer')?.classList.add('show'))" aria-label="Abrir filtros">
      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
      <span>Filtros</span>
    </button>
  </div>

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

      <!-- Reglas 5 & 6: Exhibidor de Color & Variabilidad de Color con Conmutación -->
      @if(!empty($product['colors']))
        <div style="margin-bottom: 6px;">
          <div class="pdp-section-heading">
            <span>Color: <strong id="pdpSelectedColorName">{{ $product['colors'][0]['name'] ?? 'Único' }}</strong></span>
            <span style="font-size: 0.72rem; color: var(--text-muted);">{{ count($product['colors']) }} {{ count($product['colors']) === 1 ? 'color indexado' : 'colores indexados' }}</span>
          </div>
          <div class="pdp-color-swatches-group" style="display: flex; gap: 8px; flex-wrap: wrap;">
            @foreach($product['colors'] as $cIndex => $cData)
              @php
                $isMulti = str_contains(strtolower($cData['name'] ?? ''), 'multicolor');
                $swatchBg = $isMulti
                  ? 'linear-gradient(135deg, #ef4444 0%, #f97316 20%, #eab308 40%, #22c55e 60%, #3b82f6 80%, #a855f7 100%)'
                  : ($cData['hex'] ?? '#0f172a');
              @endphp
              <button type="button"
                      class="pdp-color-swatch-btn {{ $loop->first ? 'selected' : '' }}"
                      data-color-name="{{ $cData['name'] }}"
                      data-color-hex="{{ $swatchBg }}"
                      data-color-image="{{ asset($cData['image'] ?? $product['image']) }}"
                      onclick="selectPdpColor(this)"
                      title="{{ $cData['name'] }}">
                <span class="pdp-swatch-circle {{ $isMulti ? 'color-dot-multicolor' : '' }}" style="width: 14px; height: 14px; border-radius: 50%; background: {{ $swatchBg }}; border: 1px solid rgba(0,0,0,0.2); display: inline-block; flex-shrink: 0;"></span>
                <span class="pdp-color-name-text">{{ $cData['name'] }}</span>
              </button>
            @endforeach
          </div>
        </div>
      @endif

      <!-- Selector de Tallas con Conexión a Stock Real de Hub 4 -->
      <div>
        <div class="pdp-section-heading">
          <span>Selecciona tu Talla</span>
          <div style="display: flex; align-items: center; gap: 12px;">
            @if(!empty($brandSizeCurve) && $brandSizeCurve->activeSizes->count() > 0)
              <button type="button" class="pdp-meta-link-btn" onclick="openPdpInfoModal('pdpSizeGuideModal')">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:3px; vertical-align:-1px;"><path d="M21 6H3"/><path d="M7 6v4"/><path d="M12 6v2"/><path d="M17 6v4"/></svg>
                Guía de tallas
              </button>
            @endif
            @if(!empty($careProtocol))
              <button type="button" class="pdp-meta-link-btn" onclick="openPdpInfoModal('pdpCareGuideModal')">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:3px; vertical-align:-1px;"><path d="M3 8h18l-2 10a3 3 0 01-3 3H8a3 3 0 01-3-3L3 8z"/><path d="M3 11c3 1 6-1 9 0s6-1 9 0"/></svg>
                Cuidados de la prenda
              </button>
            @endif
          </div>
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
          @if(($product['stockType'] ?? '') === 'drop_on_demand')
            <strong>Drop Bajo Pedido:</strong> Despacho directo desde proveedor internacional/aliado en 5-8 días hábiles.
          @elseif(($product['availableStock'] ?? 0) > 0)
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
          <div><strong>Pago Seguro</strong><br>Tarjetas bancarias & OXXO Pay</div>
        </div>
      </div>

      <!-- Ficha Técnica de Composición Textil -->
      <div class="pdp-composition-box" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
          <strong style="text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.08em; display: block; margin-bottom: 4px; color: var(--text-muted, #52525b);">Composición Textil Oficial</strong>
          <p style="margin: 0; color: var(--text-primary, #27272a);">{{ $product['fabricComposition'] }}</p>
        </div>
        @if(!empty($careProtocol))
          <button type="button" class="pdp-composition-care-btn" onclick="openPdpInfoModal('pdpCareGuideModal')">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h18l-2 10a3 3 0 01-3 3H8a3 3 0 01-3-3L3 8z"/><path d="M3 11c3 1 6-1 9 0s6-1 9 0"/></svg>
            <span>Cuidados de la prenda</span>
          </button>
        @endif
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

    <!-- 2. Aviso Sanitario y Normativo PROFECO -->
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

<!-- MODAL 1: GUÍA DE TALLAS DESCENTRALIZADA DE MARCA / PROVEEDOR (HUB 4) -->
@if(!empty($brandSizeCurve) && $brandSizeCurve->activeSizes->count() > 0)
<div id="pdpSizeGuideModal" class="pdp-info-modal" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="pdp-info-backdrop" onclick="closePdpInfoModal('pdpSizeGuideModal')"></div>
  <div class="pdp-info-dialog">
    <div class="pdp-info-header">
      <div>
        <h3 class="pdp-info-title">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 6H3"/><path d="M7 6v4"/><path d="M12 6v2"/><path d="M17 6v4"/></svg>
          Guía de tallas
        </h3>
        <div class="pdp-info-subtitle">
          Curva oficial: <strong>{{ $brandSizeCurve->name }}</strong> · Marca: <strong>{{ $brandSizeCurve->brand?->name ?? $brandSizeCurve->supplier?->name ?? $product['brand'] }}</strong>
        </div>
      </div>
      <button type="button" class="pdp-info-close" onclick="closePdpInfoModal('pdpSizeGuideModal')" aria-label="Cerrar guía de tallas">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="pdp-info-body">
      @if(!empty($brandSizeCurve->description))
        <p style="font-size: 0.8rem; color: #64748b; margin-top: 0; margin-bottom: 14px;">
          {{ $brandSizeCurve->description }}
        </p>
      @endif

      <div style="overflow-x: auto;">
        <table class="torzo-size-table">
          <thead>
            <tr>
              <th>Talla</th>
              <th>Descripción / Etiqueta</th>
              <th>Cintura Mín</th>
              <th>Cintura Máx</th>
            </tr>
          </thead>
          <tbody>
            @foreach($brandSizeCurve->activeSizes as $sz)
              <tr>
                <td><strong style="color: #0f172a; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-family: monospace;">{{ $sz->code }}</strong></td>
                <td>{{ $sz->label }}</td>
                <td>{{ $sz->waist_min_cm ? $sz->waist_min_cm . ' cm' : '—' }}</td>
                <td>{{ $sz->waist_max_cm ? $sz->waist_max_cm . ' cm' : '—' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div style="margin-top: 16px; padding: 12px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 0.76rem; color: #475569; line-height: 1.45;">
        💡 <strong>Recomendación de calce:</strong> Si tus medidas se encuentran en el límite superior de una talla, o prefieres un calce más relajado, te sugerimos elegir la talla inmediata siguiente.
      </div>
    </div>
  </div>
</div>
@endif

<!-- MODAL 2: PROTOCOLO DE CUIDADOS DE LA PRENDA & TELAS/FIBRAS DE HUB 4 -->
@if(!empty($careProtocol))
<div id="pdpCareGuideModal" class="pdp-info-modal" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="pdp-info-backdrop" onclick="closePdpInfoModal('pdpCareGuideModal')"></div>
  <div class="pdp-info-dialog">
    <div class="pdp-info-header">
      <div>
        <h3 class="pdp-info-title">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h18l-2 10a3 3 0 01-3 3H8a3 3 0 01-3-3L3 8z"/><path d="M3 11c3 1 6-1 9 0s6-1 9 0"/></svg>
          Cuidados de la prenda
        </h3>
        <div class="pdp-info-subtitle">
          Protocolo oficial de cuidado para: <strong>{{ $careProtocol['fiber'] ?? $product['fabricComposition'] }}</strong>
        </div>
      </div>
      <button type="button" class="pdp-info-close" onclick="closePdpInfoModal('pdpCareGuideModal')" aria-label="Cerrar cuidados de la prenda">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="pdp-info-body">
      @if(!empty($careProtocol['rule']))
        <div style="font-size: 0.85rem; color: #1e293b; font-weight: 600; margin-bottom: 12px;">
          {{ $careProtocol['rule'] }}
        </div>
      @endif

      @if(!empty($careProtocol['warning']))
        <div style="margin-bottom: 16px; padding: 12px 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; font-size: 0.78rem; color: #92400e; line-height: 1.45;">
          <strong>Advertencia especial:</strong> {{ $careProtocol['warning'] }}
        </div>
      @endif

      @php
        $activeSymKeys = is_array($careProtocol['symbols'] ?? null) ? $careProtocol['symbols'] : [];
        $symLookup = $standardCareSymbols ?? \App\Models\CmsOfficialDocument::getStandardCareSymbols();
      @endphp

      @if(!empty($activeSymKeys))
        <strong style="text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.08em; display: block; margin-bottom: 8px; color: #64748b;">
          Simbología Oficial de Lavado y Cuidado
        </strong>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px; margin-bottom: 16px;">
          @foreach($activeSymKeys as $sKey)
            @if(isset($symLookup[$sKey]))
              @php $symInfo = $symLookup[$sKey]; @endphp
              <div class="pdp-symbol-card">
                <span class="pdp-symbol-icon">{!! $symInfo['svg'] !!}</span>
                <div>
                  <div style="font-size: 0.78rem; font-weight: 700; color: #0f172a;">{{ $symInfo['name'] }}</div>
                  <div style="font-size: 0.7rem; color: #64748b;">{{ $symInfo['category'] }}</div>
                </div>
              </div>
            @endif
          @endforeach
        </div>
      @endif

      <div style="padding: 12px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 0.75rem; color: #475569; line-height: 1.45;">
        <strong>Preservación de elastanos:</strong> Los textiles con fibras elásticas y jacquards anatómicos mantienen su elasticidad original al evitar el uso de secadoras mecánicas y fuentes de calor directo.
      </div>
    </div>
  </div>
</div>
@endif

@endsection

@push('scripts')
<script>
  let currentSelectedSize = @json($product['sizes'][0] ?? 'M');
  let currentSelectedColor = @json($product['colors'][0]['name'] ?? 'Único');
  const pdpImagesByColor = @json($product['images_by_color'] ?? []);
  const productId = @json($product['id']);
  const productName = @json($product['name']);

  function selectPdpColor(btn) {
    document.querySelectorAll('.pdp-color-swatch-btn').forEach(el => {
      el.classList.remove('selected');
    });
    btn.classList.add('selected');

    currentSelectedColor = btn.dataset.colorName;
    const nameEl = document.getElementById('pdpSelectedColorName');
    if (nameEl) nameEl.textContent = currentSelectedColor;

    // Regla 6: Las imágenes del producto conmutan al color indexado acordado
    const newImgSrc = btn.dataset.colorImage;
    const mainImg = document.getElementById('pdpMainImg');

    let colorImgs = (pdpImagesByColor && pdpImagesByColor[currentSelectedColor] && pdpImagesByColor[currentSelectedColor].length > 0)
      ? pdpImagesByColor[currentSelectedColor]
      : (newImgSrc ? [newImgSrc] : []);

    if (mainImg && colorImgs.length > 0) {
      mainImg.src = colorImgs[0];
      if (typeof currentPdpImageIndex !== 'undefined') {
        currentPdpImageIndex = 0;
      }
    }

    // Actualizar miniatura de galería si existen
    const thumbsStrip = document.querySelector('.pdp-thumbs-strip');
    if (thumbsStrip && colorImgs.length > 1) {
      thumbsStrip.innerHTML = colorImgs.map((img, i) => `
        <button type="button" class="pdp-thumb-btn ${i === 0 ? 'active' : ''}" onclick="switchPdpImage('${img}', this, ${i})" aria-label="Ver miniatura ${i+1}">
          <img src="${img}" alt="Miniatura ${i+1}">
        </button>
      `).join('');
    }
  }

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
      if (window.storeState) {
        window.storeState.selectedModalColor = currentSelectedColor;
      }
      for (let i = 0; i < qty; i++) {
        window.addToCart(productId, currentSelectedSize);
      }
      if (typeof openDrawer === 'function') {
        openDrawer('cartSheet');
      }
    } else {
      // Fallback nativo
      alert('Prenda agregada a tu carrito: ' + productName + ' (Color: ' + currentSelectedColor + ' | Talla: ' + currentSelectedSize + ' x' + qty + ')');
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

  window.openPdpInfoModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  };
  function openPdpInfoModal(modalId) {
    window.openPdpInfoModal(modalId);
  }

  window.closePdpInfoModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  };
  function closePdpInfoModal(modalId) {
    window.closePdpInfoModal(modalId);
  }

  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      ['pdpSizeGuideModal', 'pdpCareGuideModal'].forEach(id => {
        const m = document.getElementById(id);
        if (m && m.classList.contains('active')) {
          closePdpInfoModal(id);
        }
      });
    }
  });
</script>
@endpush
