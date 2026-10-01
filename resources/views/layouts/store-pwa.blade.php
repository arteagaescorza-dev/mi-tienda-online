<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="theme-color" content="#ffffff">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ $currentStorefront['pageTitle'] ?? 'MARTE — Universo Masculino & Multiverso Store' }}</title>
  <meta name="description" content="Colección exclusiva de moda contemporánea, lencería y prendas de confort anatómico. Envíos express a todo México.">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="{{ url()->current() }}">

  <!-- OpenGraph / Redes Sociales Oficiales -->
  <meta property="og:site_name" content="{{ $currentStorefront['brandName'] ?? 'Multiverso Store' }}">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="es_MX">
  <meta property="og:title" content="{{ $currentStorefront['pageTitle'] ?? 'Multiverso Store' }}">
  <meta property="og:description" content="Colección exclusiva de moda contemporánea, lencería y prendas de confort anatómico. Envíos express a todo México.">
  <meta property="og:url" content="{{ url()->current() }}">
  @if(!empty($currentStorefront['logoUrl']))
    <meta property="og:image" content="{{ $currentStorefront['logoUrl'] }}">
  @endif

  <!-- Twitter Cards Oficiales -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="{{ $currentStorefront['pageTitle'] ?? 'Multiverso Store' }}">
  <meta name="twitter:description" content="Colección exclusiva de moda contemporánea, lencería y prendas de confort anatómico. Envíos express a todo México.">
  @if(!empty($currentStorefront['logoUrl']))
    <meta name="twitter:image" content="{{ $currentStorefront['logoUrl'] }}">
  @endif

  <!-- JSON-LD Schema.org Structured Data (Organization & WebSite) -->
  <script type="application/ld+json">
  {!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => array_values(array_filter([
      [
        '@type' => 'Organization',
        '@id' => url('/') . '#organization',
        'name' => $currentStorefront['brandName'] ?? 'Multiverso Store',
        'url' => url('/'),
        'logo' => !empty($currentStorefront['logoUrl']) ? $currentStorefront['logoUrl'] : null,
        'sameAs' => [],
      ],
      [
        '@type' => 'WebSite',
        '@id' => url('/') . '#website',
        'url' => url('/'),
        'name' => $currentStorefront['brandName'] ?? 'Multiverso Store',
        'inLanguage' => 'es-MX',
        'potentialAction' => [
          '@type' => 'SearchAction',
          'target' => url('/search') . '?q={search_term_string}',
          'query-input' => 'required name=search_term_string',
        ],
      ],
    ])),
  ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
  </script>

  @yield('seo')

  @if(!empty($currentStorefront['faviconUrl']))
    <link rel="icon" type="image/x-icon" href="{{ $currentStorefront['faviconUrl'] }}">
    <link rel="shortcut icon" href="{{ $currentStorefront['faviconUrl'] }}">
  @else
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  @endif
  @if(!empty($currentStorefront['logoMobileUrl']) || !empty($currentStorefront['logoUrl']))
    <link rel="apple-touch-icon" href="{{ $currentStorefront['logoMobileUrl'] ?? $currentStorefront['logoUrl'] }}">
  @endif

  @if(isset($tracking) && $tracking->is_active)
    @if(!empty($tracking->gtm_id))
      <!-- Google Tag Manager -->
      <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
      new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
      j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
      'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
      })(window,document,'script','dataLayer','{{ $tracking->gtm_id }}');</script>
      <!-- End Google Tag Manager -->
    @endif
    @if(!empty($tracking->ga4_measurement_id))
      <!-- Google Analytics 4 -->
      <script async src="https://www.googletagmanager.com/gtag/js?id={{ $tracking->ga4_measurement_id }}"></script>
      <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $tracking->ga4_measurement_id }}');
      </script>
    @endif
    @if(!empty($tracking->facebook_pixel_id))
      <!-- Meta Pixel Code -->
      <script>
      !function(f,b,e,v,n,t,s)
      {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};
      if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
      n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t,s)}(window, document,'script',
      'https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '{{ $tracking->facebook_pixel_id }}');
      fbq('track', 'PageView');
      </script>
      <!-- End Meta Pixel Code -->
    @endif
  @endif

  <!-- Manifest PWA Oficial -->
  <link rel="manifest" href="{{ asset('manifest.json') }}">

  <!-- Google Fonts: Plus Jakarta Sans & Outfit (Minimalist Luxury) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Estilos Mobile-First & Desktop Adaptive Namespaced -->
  <link rel="stylesheet" href="{{ asset('css/store-pwa.css') }}?v={{ file_exists(public_path('css/store-pwa.css')) ? filemtime(public_path('css/store-pwa.css')) : time() }}">

  <!-- Variables CSS Semánticas de Marca y Colores de Hub 2 -->
  <style id="hub2DynamicTheme">
    :root {
      @if(!empty($currentStorefront['primaryColor']))
        --brand-primary: {{ $currentStorefront['primaryColor'] }};
        --accent-primary: {{ $currentStorefront['primaryColor'] }};
      @endif
      @if(!empty($currentStorefront['secondaryColor']))
        --brand-secondary: {{ $currentStorefront['secondaryColor'] }};
        --accent-emerald: {{ $currentStorefront['secondaryColor'] }};
        --accent-emerald-border: {{ $currentStorefront['secondaryColor'] }};
      @endif
      @if(!empty($currentStorefront['accentColor']))
        --brand-accent: {{ $currentStorefront['accentColor'] }};
        --accent-indigo: {{ $currentStorefront['accentColor'] }};
        --accent-cta: {{ $currentStorefront['accentColor'] }};
      @endif
      @if(!empty($currentStorefront['backgroundColor']))
        --brand-bg: {{ $currentStorefront['backgroundColor'] }};
        --bg-primary: {{ $currentStorefront['backgroundColor'] }};
        --bg-secondary: {{ $currentStorefront['backgroundColor'] }};
      @endif
      @if(!empty($currentStorefront['surfaceColor']))
        --brand-surface: {{ $currentStorefront['surfaceColor'] }};
        --bg-surface: {{ $currentStorefront['surfaceColor'] }};
      @endif
      @if(!empty($currentStorefront['textColor']))
        --brand-text: {{ $currentStorefront['textColor'] }};
        --text-primary: {{ $currentStorefront['textColor'] }};
        --accent-black: {{ $currentStorefront['textColor'] }};
      @endif
      @if(!empty($currentStorefront['mutedTextColor']))
        --brand-muted: {{ $currentStorefront['mutedTextColor'] }};
        --text-secondary: {{ $currentStorefront['mutedTextColor'] }};
        --text-muted: {{ $currentStorefront['mutedTextColor'] }};
      @endif
      @if(!empty($currentStorefront['borderColor']))
        --brand-border: {{ $currentStorefront['borderColor'] }};
        --border-color: {{ $currentStorefront['borderColor'] }};
        --border-subtle: {{ $currentStorefront['borderColor'] }};
        --border-medium: {{ $currentStorefront['borderColor'] }};
      @endif
    }

    /* Mapeo Concreto y Forzado de los 8 Tokens al Storefront */
    @if(!empty($currentStorefront['backgroundColor']))
      body.store-body {
        background-color: var(--brand-bg) !important;
      }
    @endif

    @if(!empty($currentStorefront['surfaceColor']))
      .product-card,
      .sticky-header-wrapper,
      .navbar-desktop,
      .navbar-mobile,
      .bottom-sheet-content,
      .modal-card,
      .dock-tab {
        background-color: var(--brand-surface) !important;
      }
    @endif

    @if(!empty($currentStorefront['textColor']))
      body.store-body,
      .product-title,
      .nav-link-item,
      .brand-name,
      .dock-tab-label,
      .store-heading,
      .filter-group-title,
      .universe-pill,
      h1, h2, h3, h4 {
        color: var(--brand-text) !important;
      }
    @endif

    @if(!empty($currentStorefront['mutedTextColor']))
      .product-category-label,
      .brand-sub,
      .marquee-sep,
      .text-muted,
      .product-desc,
      .product-sku-code {
        color: var(--brand-muted) !important;
      }
    @endif

    @if(!empty($currentStorefront['borderColor']))
      .product-card,
      .sticky-header-wrapper,
      .size-pill-btn,
      .btn-pill-action,
      .navbar-desktop,
      .universe-pill,
      .minimal-marquee-bar,
      .brand-border {
        border-color: var(--brand-border) !important;
      }
    @endif

    @if(!empty($currentStorefront['secondaryColor']))
      .product-stock-badge,
      .product-stock-badge.ready {
        color: var(--brand-secondary) !important;
        border-color: var(--brand-secondary) !important;
        background-color: rgba(255, 255, 255, 0.95) !important;
      }
    @endif

    @if(!empty($currentStorefront['accentColor']))
      .product-price,
      .price-highlight,
      .accent-price {
        color: var(--brand-accent) !important;
      }
      .btn-add-cart,
      .btn-primary,
      .btn-checkout,
      .hero-cta-btn,
      .size-pill-btn.selected,
      .universe-tab-btn.active {
        background-color: var(--brand-accent) !important;
        border-color: var(--brand-accent) !important;
        color: #ffffff !important;
      }
      .btn-add-cart:hover,
      .hero-cta-btn:hover {
        filter: brightness(0.9);
      }
      .nav-link-item.active::after {
        background-color: var(--brand-accent) !important;
      }
      .nav-link-item:hover,
      .nav-link-item.active {
        color: var(--brand-accent) !important;
      }
    @endif

    /* Corrección Ergonómica de Logo y Encabezado */
    .brand-logo-box {
      width: auto !important;
      height: auto !important;
      min-width: 32px !important;
      min-height: 32px !important;
      background: transparent !important;
      border: none !important;
      box-shadow: none !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
    }
    .brand-logo-box img {
      max-height: 38px !important;
      width: auto !important;
      max-width: 170px !important;
      object-fit: contain !important;
      display: block !important;
    }
    .nav-brand-group {
      display: flex !important;
      align-items: center !important;
      gap: 12px !important;
      text-decoration: none !important;
    }
    .brand-titles {
      display: flex !important;
      flex-direction: column !important;
      justify-content: center !important;
    }

    /* Hero Full Width & Edge-to-Edge Bleed (TASK-H2-HERO-FULLWIDTH) */
    html, body.store-body {
      overflow-x: hidden !important;
    }
    .hero-editorial-section.hero-full-width {
      width: 100vw !important;
      position: relative !important;
      left: 50% !important;
      right: 50% !important;
      margin-left: -50vw !important;
      margin-right: -50vw !important;
      max-width: 100vw !important;
      box-sizing: border-box !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
    }
    .hero-editorial-section.hero-full-width .hero-banner-card {
      border-radius: 0 !important;
      width: 100% !important;
      max-width: 100vw !important;
      box-sizing: border-box !important;
      padding-left: max(20px, calc((100vw - 1280px) / 2 + 24px)) !important;
      padding-right: max(20px, calc((100vw - 1280px) / 2 + 24px)) !important;
    }
    .hero-editorial-section.hero-full-width .hero-custom-container {
      width: 100% !important;
      max-width: 100vw !important;
      box-sizing: border-box !important;
    }
    .hero-banner-card .hero-title {
      color: #ffffff !important;
    }
    .hero-banner-card .hero-desc {
      color: #a1a1aa !important;
    }
    .hero-banner-card .hero-tag-pill {
      color: #ffffff !important;
    }

    /* Navbar Sticky Visible Todo el Tiempo (Regla 1) */
    .sticky-header-wrapper {
      position: sticky !important;
      top: 0 !important;
      z-index: 999 !important;
      background: rgba(255, 255, 255, 0.96) !important;
      backdrop-filter: blur(16px) !important;
      -webkit-backdrop-filter: blur(16px) !important;
    }

    /* Barra Móvil de Páginas Institucionales Sincronizadas */
    .mobile-institutional-bar {
      display: none;
      background: var(--brand-surface, #ffffff);
      border-bottom: 1px solid var(--border-subtle, #e4e4e7);
      padding: 6px 0;
    }
    @media (max-width: 768px) {
      .mobile-institutional-bar {
        display: block;
      }
    }
    .mobile-institutional-scroll {
      display: flex;
      align-items: center;
      gap: 8px;
      overflow-x: auto;
      white-space: nowrap;
      scrollbar-width: none;
      -ms-overflow-style: none;
      padding: 0 16px;
    }
    .mobile-institutional-scroll::-webkit-scrollbar {
      display: none;
    }
    .mobile-inst-link {
      font-family: var(--font-heading, sans-serif);
      font-size: 0.68rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      color: var(--text-muted, #71717a);
      text-decoration: none;
      padding: 4px 10px;
      border-radius: 9999px;
      background: rgba(0, 0, 0, 0.04);
      transition: all 0.2s ease;
      flex-shrink: 0;
    }
    .mobile-inst-link:hover, .mobile-inst-link:active {
      color: var(--brand-accent, #4f46e5);
      background: rgba(0, 0, 0, 0.08);
    }

    @if(!empty($currentStorefront['customCss']))
      {!! $currentStorefront['customCss'] !!}
    @endif
  </style>
  @stack('styles')
</head>
<body class="store-body">
  @if(isset($tracking) && $tracking->is_active && !empty($tracking->gtm_id))
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $tracking->gtm_id }}"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
  @endif

  <div class="sticky-header-wrapper">
    <!-- Banner de Bienvenida de Aliados / UTM Atribución (Hub 7 TASK-STR-04) -->
    <div id="partnerWelcomeBanner" class="partner-welcome-banner" style="display: none;">
      <div class="container" style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
        <div style="display: flex; align-items: center; gap: 8px;">
          <span class="partner-pulse-dot"></span>
          <span id="partnerWelcomeText" class="partner-welcome-text">
            Bienvenido desde Aliado Estratégico - Descuento preferencial activado
          </span>
        </div>
        <button type="button" class="btn-close-partner-banner" data-action="close-partner-banner" aria-label="Cerrar aviso de aliado">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
    </div>

    <!-- Marquesina Superior de Avisos Dinámica (Hub 2) -->
    @php
      $activeAnnouncements = collect($announcements ?? [])
        ->filter(fn($item) => is_array($item) && ($item['active'] ?? true) && !empty($item['text']))
        ->values();
    @endphp
    @if($activeAnnouncements->isNotEmpty())
      <div class="minimal-marquee-bar" aria-label="Avisos importantes de la tienda">
        <div class="marquee-track">
          @for($i = 0; $i < 2; $i++)
            @foreach($activeAnnouncements as $announcement)
              @if(!empty($announcement['url']))
                <a href="{{ $announcement['url'] }}" style="color: inherit; text-decoration: none;">
                  <span>{{ strtoupper($announcement['text']) }}</span>
                </a>
              @else
                <span>{{ strtoupper($announcement['text']) }}</span>
              @endif
              <span class="marquee-sep">/</span>
            @endforeach
          @endfor
        </div>
      </div>
    @endif

    <!-- 1. Header de Escritorio (Estilo DEV/pop) -->
    <header class="navbar-desktop">
      <div class="container" style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
        <!-- Logo de Marca Adaptativo por Universo -->
        <a href="{{ route('store.home') }}" class="nav-brand-group">
          <div class="brand-logo-box">
            @if(!empty($currentStorefront['logoUrl']))
              <img src="{{ $currentStorefront['logoUrl'] }}" alt="{{ $currentStorefront['brandName'] ?? 'Logo' }}">
            @else
              <svg width="24" height="24" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="var(--text-primary, #050505)"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            @endif
          </div>
          <div class="brand-titles">
            @if(empty($currentStorefront['logoUrl']))
              <span class="brand-name" id="desktopBrandName">{{ $currentStorefront['brandName'] ?? 'MARTE' }}</span>
            @endif
            <span class="brand-sub" id="desktopBrandSub">{{ $currentStorefront['kicker'] ?? $currentStorefront['subTitle'] ?? 'UNIVERSO MASCULINO' }}</span>
          </div>
        </a>

        <!-- Enlaces Oficiales Canónicos (Regla 4.13: Inicio, Explorar, Catálogo, Carrito, Cuenta) -->
        <nav class="nav-links-desktop">
          <a href="#" class="nav-link-item active" data-action="switch-dock-tab" data-dock-tab="home">INICIO</a>
          <a href="#" class="nav-link-item" data-action="switch-dock-tab" data-dock-tab="explore">EXPLORAR</a>
          <a href="#" class="nav-link-item" data-action="switch-dock-tab" data-dock-tab="catalog">CATÁLOGO</a>
          <a href="#" class="nav-link-item" data-action="open-cart">
            <span>CARRITO</span>
            <span id="desktopNavCartCount" class="cart-count-badge" style="display:inline-flex;">0</span>
          </a>
          <a href="#" class="nav-link-item" data-action="switch-dock-tab" data-dock-tab="account">CUENTA</a>
        </nav>
      </div>
    </header>

    <!-- 2. Header Móvil App (Compacto & Táctil) -->
    <header class="navbar-mobile">
      <button type="button" class="mobile-icon-btn" data-action="open-filter-drawer" aria-label="Abrir filtros">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
      </button>

      <a href="{{ route('store.home') }}" class="nav-brand-group">
        <div class="brand-logo-box">
          @if(!empty($currentStorefront['logoMobileUrl']) || !empty($currentStorefront['logoUrl']))
            <img src="{{ $currentStorefront['logoMobileUrl'] ?? $currentStorefront['logoUrl'] }}" alt="{{ $currentStorefront['brandName'] ?? 'Logo' }}">
          @else
            <svg width="20" height="20" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="var(--text-primary, #050505)"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          @endif
        </div>
        <div class="brand-titles">
          @if(empty($currentStorefront['logoMobileUrl']) && empty($currentStorefront['logoUrl']))
            <span class="brand-name" id="mobileBrandName" style="font-size: 0.95rem;">{{ $currentStorefront['brandName'] ?? 'MARTE' }}</span>
          @endif
          <span class="brand-sub" id="mobileBrandSub" style="font-size: 0.6rem;">{{ $currentStorefront['kicker'] ?? $currentStorefront['subTitle'] ?? 'UNIVERSO MASCULINO' }}</span>
        </div>
      </a>

      <div style="display: flex; align-items: center; gap: 4px;">
        <button type="button" class="mobile-icon-btn" data-action="open-wishlist" aria-label="Ver deseos">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
        </button>
        <button type="button" class="mobile-icon-btn" data-action="open-cart" aria-label="Ver carrito">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
        </button>
      </div>
    </header>

    <!-- Barra Móvil de Páginas Institucionales Sincronizadas -->
    @if(!empty($mobilePages) && $mobilePages->isNotEmpty())
      <div class="mobile-institutional-bar">
        <div class="container mobile-institutional-scroll">
          @foreach($mobilePages as $mp)
            <a href="{{ url('/pagina/' . $mp->slug) }}" class="mobile-inst-link">{{ strtoupper($mp->title) }}</a>
          @endforeach
        </div>
      </div>
    @endif

    <!-- 3. Selector de Universos Multiverso (Barra Superior Táctil) -->
    <div class="universe-selector-bar">
      <div class="container universe-scroll-container">
        <button type="button" class="universe-tab-btn {{ ($activeUniverse ?? 'marte') === 'marte' ? 'active' : '' }}" data-action="switch-universe" data-universe="marte">MARTE // MASCULINO</button>
        <button type="button" class="universe-tab-btn {{ ($activeUniverse ?? 'marte') === 'venus' ? 'active' : '' }}" data-action="switch-universe" data-universe="venus">VENUS // LENCERÍA & BEAUTY</button>
        <button type="button" class="universe-tab-btn {{ ($activeUniverse ?? 'marte') === 'tiresias' ? 'active' : '' }}" data-action="switch-universe" data-universe="tiresias">TIRESIAS // AFIRMATIVO</button>
        <button type="button" class="universe-tab-btn {{ ($activeUniverse ?? 'marte') === 'terra' ? 'active' : '' }}" data-action="switch-universe" data-universe="terra">TERRA // GLOBAL</button>
      </div>
    </div>
  </div>

  <!-- Contenedor Principal de la Tienda -->
  <main class="container">
    @yield('content')
  </main>

  <!-- Footer Corporativo Minimalista -->
  <footer class="store-footer">
    <div class="container">
      <div class="footer-top-grid">
        <div>
          <div class="footer-brand-name">{{ $currentStorefront['brandName'] ?? 'MARTE' }} // Contemporary fashion studio</div>
          <p style="line-height: 1.6; max-width: 420px; margin-bottom: 16px;">
            Plataforma contemporánea de moda íntima, streetwear y autocuidado. Envíos nacionales directos vía paquetería express con empaque 100% neutro y precinto higiénico.
          </p>
          <div style="font-size: 0.75rem; color: #71717a;">
            Escribenos por WhatsApp: <strong>+52 55 7510 2015</strong>
          </div>
        </div>

        <div>
          <strong style="display: block; color: #ffffff; margin-bottom: 12px; font-family: var(--font-heading); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.05em;">Pagos y envios</strong>
          <ul class="footer-link-list">
            <li><span>Paqueteria nacional express ($149 / Gratis +$999)</span></li>
            <li><span>Entrega personal discreta y segura</span></li>
            <li><span>Pagos 100% seguros con Tarjeta & OXXO Pay</span></li>
          </ul>
        </div>

        <div>
          <strong style="display: block; color: #ffffff; margin-bottom: 12px; font-family: var(--font-heading); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.05em;">Categorías Oficiales</strong>
          <ul class="footer-link-list">
            <li><a href="#" data-action="switch-dock-tab" data-dock-tab="catalog">Jockstraps ($250 MXN)</a></li>
            <li><a href="#" data-action="switch-dock-tab" data-dock-tab="catalog">Boxers Modal ($280 MXN)</a></li>
            <li><a href="#" data-action="switch-dock-tab" data-dock-tab="catalog">Tangas Velvet ($180 MXN)</a></li>
            <li><a href="#" data-action="switch-dock-tab" data-dock-tab="catalog">Bikinis Athletic ($220 MXN)</a></li>
            <li><a href="#" data-action="switch-dock-tab" data-dock-tab="catalog">Tank Tops ($320 MXN)</a></li>
          </ul>
        </div>

        <div>
          <strong style="display: block; color: #ffffff; margin-bottom: 12px; font-family: var(--font-heading); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.05em;">Información &amp; Legal</strong>
          <ul class="footer-link-list">
            @if(!empty($footerPages) && $footerPages->isNotEmpty())
              @foreach($footerPages as $fp)
                <li><a href="{{ url('/pagina/' . $fp->slug) }}" style="color: inherit; text-decoration: none;">{{ $fp->title }}</a></li>
              @endforeach
            @else
              <li><a href="/privacidad" style="color: inherit; text-decoration: none;">Aviso de Privacidad</a></li>
              <li><a href="/cookies" style="color: inherit; text-decoration: none;">Política de Cookies</a></li>
              <li><a href="#" data-action="open-size-guide" style="color: inherit; text-decoration: none;">Guía de Tallas Anatómicas</a></li>
            @endif
          </ul>

          <!-- Acceso al Panel RevOps -->
          <div class="footer-revops-card">
            <div>
              <span style="font-size: 0.7rem; color: #d4d4d8; display: block; font-weight: 700;">Consola RevOps</span>
              <span style="font-size: 0.65rem; color: #a1a1aa;">Administración & Hubs</span>
            </div>
            <a href="{{ route('portal.dashboard') }}" class="btn-goto-revops">
              <span>Panel RevOps</span>
              <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
          </div>
        </div>
      </div>

      <div style="border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: gap-2; font-size: 0.7rem; color: #71717a;">
        <span>(C) 2026 MULTIVERSO STORE. Todos los derechos reservados. <a href="/privacidad" style="color: inherit; text-decoration: underline; margin-left: 8px;">Aviso de Privacidad</a> <a href="/cookies" style="color: inherit; text-decoration: underline; margin-left: 8px;">Política de Cookies</a></span>
        <span>Política Estricta Cero Emojis // Vectorial Only</span>
      </div>
    </div>
  </footer>

  <!-- ============================================================================
       BOTTOM NAVIGATION DOCK (EXACTAMENTE 5 BOTONES - MÓVIL ONLY)
       1. Inicio | 2. Explorar | 3. Catálogo | 4. Carrito | 5. Cuenta
       ============================================================================ -->
  <nav class="bottom-app-dock" aria-label="Navegación principal de la aplicación">
    <button type="button" class="dock-tab-btn active" data-action="switch-dock-tab" data-dock-tab="home">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
      <span class="dock-tab-label">Inicio</span>
    </button>

    <button type="button" class="dock-tab-btn" data-action="switch-dock-tab" data-dock-tab="explore">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
      <span class="dock-tab-label">Explorar</span>
    </button>

    <button type="button" class="dock-tab-btn" data-action="switch-dock-tab" data-dock-tab="catalog">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
      <span class="dock-tab-label">Catálogo</span>
    </button>

    <button type="button" class="dock-tab-btn" data-action="switch-dock-tab" data-dock-tab="cart">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
      <span class="dock-tab-label">Carrito</span>
      <span id="dockCartBadge" class="dock-cart-badge">0</span>
    </button>

    <button type="button" class="dock-tab-btn" data-action="switch-dock-tab" data-dock-tab="account">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
      <span class="dock-tab-label">Cuenta</span>
    </button>
  </nav>

  <!-- ============================================================================
       DRAWERS / BOTTOM SHEETS DESLIZABLES
       ============================================================================ -->
  <!-- Backdrop Compartido -->
  <div id="drawerBackdrop" class="drawer-backdrop" data-mobile-nav-backdrop></div>

  <!-- A. Drawer de Filtros Lateral Izquierdo con Switcher Tipo ZARA (Regla 1) -->
  <aside id="filterDrawer" class="bottom-sheet-drawer drawer-left" data-mobile-nav-drawer aria-label="Filtros del catálogo">
    <div class="sheet-handle-bar"></div>
    <div class="sheet-header">
      <span class="sheet-title">Filtros & Universos</span>
      <button type="button" class="sheet-close-btn" data-action="close-drawer" data-mobile-nav-close aria-label="Cerrar filtros">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
      <div style="display: none;">
        <button type="button" data-open-login>Ingresar</button>
        <a href="/carrito">Mi Carrito</a>
        <a href="/contacto">Contacto</a>
      </div>
    </div>

    <div class="sheet-content-scroll">
      <!-- Switcher Multiverso Tipo ZARA -->
      <div style="margin-bottom: 6px;">
        <span style="font-family: var(--font-heading); font-size: 0.68rem; font-weight: 800; letter-spacing: 0.08em; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 6px;">Selecciona Universo (Estilo ZARA)</span>
        <div class="zara-multiverse-filter-bar">
          <button type="button" class="zara-filter-tab {{ ($activeUniverse ?? 'marte') === 'marte' ? 'active' : '' }}" data-action="zara-switch-universe" data-universe="marte">MARTE</button>
          <button type="button" class="zara-filter-tab {{ ($activeUniverse ?? 'marte') === 'venus' ? 'active' : '' }}" data-action="zara-switch-universe" data-universe="venus">VENUS</button>
          <button type="button" class="zara-filter-tab {{ ($activeUniverse ?? 'marte') === 'tiresias' ? 'active' : '' }}" data-action="zara-switch-universe" data-universe="tiresias">TIRESIAS</button>
          <button type="button" class="zara-filter-tab {{ ($activeUniverse ?? 'marte') === 'terra' ? 'active' : '' }}" data-action="zara-switch-universe" data-universe="terra">TERRA</button>
        </div>
      </div>

      <!-- Filtro por Categoría Oficial Torzo -->
      <div class="filter-group-block">
        <div class="filter-group-title">Categorías de Prenda</div>
        <div class="filter-category-pills">
          <button type="button" class="category-filter-pill" data-action="filter-category" data-category="jocks">Jockstraps ($250)</button>
          <button type="button" class="category-filter-pill" data-action="filter-category" data-category="boxers">Boxers ($280)</button>
          <button type="button" class="category-filter-pill" data-action="filter-category" data-category="tangas">Tangas ($180)</button>
          <button type="button" class="category-filter-pill" data-action="filter-category" data-category="bikinis">Bikinis ($220)</button>
          <button type="button" class="category-filter-pill" data-action="filter-category" data-category="tanktops">Tank Tops ($320)</button>
          <button type="button" class="category-filter-pill" data-action="filter-category" data-category="swimwear">Trajes de Baño ($460)</button>
        </div>
      </div>

      <!-- Guía de Tallas Rápida -->
      <div class="filter-group-block">
        <div class="filter-group-title">Curva de Tallas Anatómicas</div>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
          <div style="border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 8px; text-align: center;">
            <strong style="display: block; font-family: var(--font-heading); font-size: 0.8rem;">CH</strong>
            <span style="font-size: 0.65rem; color: var(--text-muted);">28-30</span>
          </div>
          <div style="border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 8px; text-align: center;">
            <strong style="display: block; font-family: var(--font-heading); font-size: 0.8rem;">M</strong>
            <span style="font-size: 0.65rem; color: var(--text-muted);">31-32</span>
          </div>
          <div style="border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 8px; text-align: center;">
            <strong style="display: block; font-family: var(--font-heading); font-size: 0.8rem;">G</strong>
            <span style="font-size: 0.65rem; color: var(--text-muted);">33-34</span>
          </div>
          <div style="border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 8px; text-align: center;">
            <strong style="display: block; font-family: var(--font-heading); font-size: 0.8rem;">XG</strong>
            <span style="font-size: 0.65rem; color: var(--text-muted);">35-36</span>
          </div>
        </div>
      </div>

      @if(!empty($mobilePages) && $mobilePages->isNotEmpty())
        <div class="filter-group-block">
          <div class="filter-group-title">Páginas Institucionales</div>
          <div style="display: flex; flex-direction: column; gap: 6px;">
            @foreach($mobilePages as $mp)
              <a href="{{ url('/pagina/' . $mp->slug) }}" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border-radius: var(--radius-sm, 8px); border: 1px solid var(--border-subtle, #e4e4e7); color: var(--text-primary, #09090b); text-decoration: none; font-size: 0.8rem; font-weight: 600;">
                <span>{{ $mp->title }}</span>
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
              </a>
            @endforeach
          </div>
        </div>
      @endif
    </div>

    <div class="sheet-footer-actions">
      <button type="button" class="btn-sheet-clear" data-action="clear-filters">Limpiar</button>
      <button type="button" class="btn-sheet-apply" data-action="apply-filters">Aplicar Selección</button>
    </div>
  </aside>

  <!-- B. Drawer de Carrito (Slide-over / Bottom Sheet) -->
  <aside id="cartSheet" class="bottom-sheet-drawer" aria-label="Tu carrito de compras">
    <div class="sheet-handle-bar"></div>
    <div class="sheet-header">
      <span class="sheet-title">Mi Carrito de Compras</span>
      <button type="button" class="sheet-close-btn" data-action="close-drawer" aria-label="Cerrar carrito">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
    </div>

    <div class="sheet-content-scroll" id="cartItemsContainer">
      <!-- Renderizado dinámico desde store-pwa.js -->
    </div>

    <div class="sheet-footer-actions" id="cartFooterActions" style="flex-direction: column; gap: 8px;">
      <!-- Caja de Cupón de Descuento (Hub 7 TASK-STR-04) -->
      <div class="cart-coupon-box" id="cartCouponBox">
        <!-- Inyectado dinámicamente desde store-pwa.js -->
      </div>

      <div style="display: flex; justify-content: space-between; width: 100%; font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800;">
        <span>Subtotal</span>
        <span id="cartSubtotalAmount">MXN $0.00</span>
      </div>
      <button type="button" class="btn-sheet-apply" id="btnCartCheckout" data-action="open-checkout" style="width: 100%;">
        CONTINUAR AL CHECKOUT (4 PASOS)
      </button>
    </div>
  </aside>

  <!-- C. Bottom Sheet Guía de Tallas -->
  <aside id="sizeGuideSheet" class="bottom-sheet-drawer" aria-label="Guía oficial de tallas">
    <div class="sheet-handle-bar"></div>
    <div class="sheet-header">
      <span class="sheet-title">Guía de Tallas Anatómicas</span>
      <button type="button" class="sheet-close-btn" data-action="close-drawer" aria-label="Cerrar guía">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
    </div>

    <div class="sheet-content-scroll">
      <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 16px;">
        Nuestras prendas están confeccionadas con elásticos jacquard de 45mm y textiles de alta recuperación. Consulta tu medida de cintura para un ajuste exacto.
      </p>

      <div style="border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); overflow: hidden; margin-bottom: 16px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
          <thead style="background: #050505; color: #ffffff; font-family: var(--font-heading);">
            <tr>
              <th style="padding: 10px; text-align: left;">Talla</th>
              <th style="padding: 10px; text-align: left;">Pantalón</th>
              <th style="padding: 10px; text-align: left;">Cintura (cm)</th>
            </tr>
          </thead>
          <tbody>
            <tr style="border-bottom: 1px solid var(--border-subtle);">
              <td style="padding: 10px; font-weight: 800;">CH (Chica)</td>
              <td style="padding: 10px;">28 - 30</td>
              <td style="padding: 10px; font-family: monospace;">70 - 76 cm</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-subtle); background: var(--bg-secondary);">
              <td style="padding: 10px; font-weight: 800;">M (Mediana)</td>
              <td style="padding: 10px;">31 - 32</td>
              <td style="padding: 10px; font-family: monospace;">78 - 84 cm</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-subtle);">
              <td style="padding: 10px; font-weight: 800;">G (Grande)</td>
              <td style="padding: 10px;">33 - 34</td>
              <td style="padding: 10px; font-family: monospace;">86 - 92 cm</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-subtle); background: var(--bg-secondary);">
              <td style="padding: 10px; font-weight: 800;">XG / XL (Extra Grande)</td>
              <td style="padding: 10px;">35 - 36</td>
              <td style="padding: 10px; font-family: monospace;">94 - 102 cm</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-subtle);">
              <td style="padding: 10px; font-weight: 800;">2XL (Doble Extra Grande)</td>
              <td style="padding: 10px;">38 - 40</td>
              <td style="padding: 10px; font-family: monospace;">104 - 110 cm</td>
            </tr>
            <tr style="background: var(--bg-secondary);">
              <td style="padding: 10px; font-weight: 800;">3XL (Triple Extra Grande)</td>
              <td style="padding: 10px;">42 - 44</td>
              <td style="padding: 10px; font-family: monospace;">112 - 120 cm</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div style="background: var(--bg-secondary); border-radius: var(--radius-sm); padding: 12px; font-size: 0.75rem; color: var(--text-secondary); line-height: 1.5;">
        <strong>Recomendación de calce:</strong> Si estás entre dos tallas, elige la mayor para comodidad relajada o la menor si prefieres soporte ceñido y definido.
      </div>
    </div>
  </aside>

  <!-- D. Bottom Sheet Cuenta & Portal del Comprador D2C -->
  <aside id="accountSheet" class="bottom-sheet-drawer" aria-label="Portal del Comprador">
    <div class="sheet-handle-bar"></div>
    <div class="sheet-header">
      <div style="display: flex; align-items: center; gap: 8px;">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        <span class="sheet-title">Mi Cuenta // Portal del Comprador</span>
      </div>
      <button type="button" class="sheet-close-btn" data-action="close-drawer" aria-label="Cerrar cuenta">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
    </div>

    <div class="sheet-content-scroll" id="accountSheetScroll">
      <!-- Tarjeta de Identidad y Sesión del Comprador -->
      <div id="accountAuthCard" class="account-auth-card">
        <!-- Renderizado dinámico desde store-pwa.js -->
      </div>

      <!-- Barra de Pestañas Táctiles del Portal -->
      <div class="account-tabs-nav" id="accountTabsNav">
        <button type="button" class="account-tab-btn active" data-action="switch-account-tab" data-tab="orders">
          <span>Mis Pedidos</span>
          <span id="accountOrdersBadge" class="account-tab-badge">0</span>
        </button>
        <button type="button" class="account-tab-btn" data-action="switch-account-tab" data-tab="anatomy">
          <span>Perfil Anatómico</span>
        </button>
        <button type="button" class="account-tab-btn" data-action="switch-account-tab" data-tab="address">
          <span>Dirección</span>
        </button>
        <button type="button" class="account-tab-btn" data-action="switch-account-tab" data-tab="subscription">
          <span>Club MRR</span>
        </button>
      </div>

      <!-- Paneles de Contenido por Pestaña -->
      <!-- Pestaña 1: Mis Pedidos & Rastreo -->
      <div id="accountTabOrders" class="account-tab-panel active">
        <div class="account-lookup-box">
          <span class="account-section-tag">Rastreo de Pedido por Folio</span>
          <div style="display: flex; gap: 8px;">
            <input type="text" id="accountLookupFolioInput" placeholder="Ej: #MIX-2026-1048 o #ORD-2026-..." class="account-input">
            <button type="button" class="btn-account-action" data-action="account-lookup-folio">Buscar</button>
          </div>
        </div>

        <div id="accountLookupResult" style="display: none; margin-bottom: 16px;"></div>

        <div id="accountOrdersList">
          <!-- Renderizado dinámico desde store-pwa.js -->
        </div>
      </div>

      <!-- Pestaña 2: Perfil Anatómico -->
      <div id="accountTabAnatomy" class="account-tab-panel" style="display: none;">
        <div class="anatomy-panel-content">
          <div class="anatomy-header">
            <h4 class="anatomy-title">Configuración de Perfil Anatómico</h4>
            <p class="anatomy-desc">Ajusta tu medida de cintura para que la tienda preseleccione tu talla recomendada en cada prenda.</p>
          </div>

          <div class="anatomy-slider-card">
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px;">
              <span style="font-size: 0.78rem; font-weight: 700; color: #050505;">Medida de Cintura</span>
              <strong id="anatomyWaistValueDisplay" style="font-family: monospace; font-size: 1.1rem; color: #2563eb;">82 cm</strong>
            </div>
            <input type="range" id="anatomyWaistSlider" min="70" max="102" value="82" step="1" class="anatomy-slider" data-action="anatomy-slider-change">
            <div style="display: flex; justify-content: space-between; font-size: 0.68rem; color: var(--text-muted); margin-top: 4px;">
              <span>70 cm (CH)</span>
              <span>80 cm (M)</span>
              <span>90 cm (G)</span>
              <span>102 cm (XG)</span>
            </div>
          </div>

          <div id="anatomyCalculationCard" class="anatomy-result-box">
            <!-- Renderizado dinámico: Talla sugerida -->
          </div>

          <div class="anatomy-silhouettes-card">
            <span class="anatomy-sub-tag">Siluetas de Preferencia</span>
            <div class="silhouettes-grid">
              <label class="silhouette-checkbox-label">
                <input type="checkbox" id="prefSilJocks" checked>
                <span>Jockstraps</span>
              </label>
              <label class="silhouette-checkbox-label">
                <input type="checkbox" id="prefSilBoxers" checked>
                <span>Boxers</span>
              </label>
              <label class="silhouette-checkbox-label">
                <input type="checkbox" id="prefSilTangas">
                <span>Tangas</span>
              </label>
              <label class="silhouette-checkbox-label">
                <input type="checkbox" id="prefSilBikinis">
                <span>Bikinis</span>
              </label>
              <label class="silhouette-checkbox-label">
                <input type="checkbox" id="prefSilTanktops" checked>
                <span>Tank Tops</span>
              </label>
            </div>
          </div>

          <button type="button" class="btn-account-primary" data-action="save-anatomy-profile" style="width: 100%;">
            <span>GUARDAR PERFIL ANATÓMICO</span>
          </button>
        </div>
      </div>

      <!-- Pestaña 3: Dirección de Entrega -->
      <div id="accountTabAddress" class="account-tab-panel" style="display: none;">
        <div class="address-panel-content">
          <div class="address-header">
            <h4 class="address-title">Libreta de Direcciones</h4>
            <p class="address-desc">Tu dirección guardada se utiliza para prellenar el checkout en 1 solo tap.</p>
          </div>

          <div id="accountAddressSummary" class="address-summary-card">
            <!-- Renderizado dinámico desde store-pwa.js -->
          </div>

          <div id="accountAddressEditForm" class="address-edit-form" style="display: none;">
            <div class="checkout-form-group">
              <label for="accShippingStreet" class="checkout-label">Calle y Número Exterior/Interior *</label>
              <input type="text" id="accShippingStreet" class="checkout-input" placeholder="Ej: Calle Génova 42 Depto 301">
            </div>
            <div class="checkout-form-row">
              <div class="checkout-form-group" style="flex: 1;">
                <label for="accShippingZip" class="checkout-label">Código Postal (5 dígitos) *</label>
                <div style="display: flex; gap: 6px;">
                  <input type="text" id="accShippingZip" class="checkout-input font-mono" placeholder="06600" maxlength="5">
                  <button type="button" class="btn-zip-lookup" data-action="acc-lookup-postal-code" title="Validar SEPOMEX">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                  </button>
                </div>
              </div>
              <div class="checkout-form-group" style="flex: 1;">
                <label for="accShippingState" class="checkout-label">Estado *</label>
                <input type="text" id="accShippingState" class="checkout-input" placeholder="Ciudad de México">
              </div>
            </div>
            <div class="checkout-form-row">
              <div class="checkout-form-group" style="flex: 1;">
                <label for="accShippingCity" class="checkout-label">Alcaldía / Municipio *</label>
                <input type="text" id="accShippingCity" class="checkout-input" placeholder="Cuauhtémoc">
              </div>
              <div class="checkout-form-group" style="flex: 1;">
                <label for="accShippingColony" class="checkout-label">Colonia *</label>
                <input type="text" id="accShippingColony" class="checkout-input" placeholder="Juárez">
              </div>
            </div>
            <div class="checkout-form-group">
              <label for="accShippingRefs" class="checkout-label">Referencias de Entrega</label>
              <input type="text" id="accShippingRefs" class="checkout-input" placeholder="Ej: Portón café, entre Reforma y Hamburgo">
            </div>
            <div style="display: flex; gap: 8px; margin-top: 10px;">
              <button type="button" class="btn-account-secondary" data-action="cancel-edit-address" style="flex: 1;">Cancelar</button>
              <button type="button" class="btn-account-primary" data-action="save-acc-address" style="flex: 1;">Guardar Dirección</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pestaña 4: Club Mensual MRR -->
      <div id="accountTabSubscription" class="account-tab-panel" style="display: none;">
        <div class="subscription-panel-content">
          <div class="sub-header">
            <h4 class="sub-title">Club de Reposición Recurrente (MRR)</h4>
            <p class="sub-desc">Recibe prendas esenciales mensualmente con flete courier bonificado y precio preferencial.</p>
          </div>

          <div id="accountSubscriptionCard" class="subscription-card">
            <!-- Renderizado dinámico desde store-pwa.js -->
          </div>
        </div>
      </div>

      <!-- Centro de Notificaciones Web Push PWA (TASK-STR-06) -->
      <div class="push-notifications-card" id="accountPushCenter">
        <!-- Renderizado dinámicamente desde store-pwa.js -->
      </div>

      <!-- Acceso a Consola RevOps para Operadores -->
      <div class="admin-console-shortcut">
        <div style="font-family: var(--font-heading); font-size: 0.78rem; font-weight: 800; margin-bottom: 4px;">Consola RevOps Interna</div>
        <p style="font-size: 0.7rem; color: var(--text-secondary); margin-bottom: 10px; line-height: 1.35;">
          Acceso reservado a operadores y administradores de los 7 Hubs operativos.
        </p>
        <a href="{{ route('portal.dashboard') }}" class="btn-revops-shortcut">
          <span>Ir a Consola RevOps</span>
          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>
    </div>
  </aside>

  <!-- E. Bottom Sheet / Drawer de Deseos & Favoritos -->
  <aside id="wishlistSheet" class="bottom-sheet-drawer" aria-label="Lista de deseos y favoritos">
    <div class="sheet-handle-bar"></div>
    <div class="sheet-header">
      <div style="display: flex; align-items: center; gap: 8px;">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
        <span class="sheet-title">Mis Deseos & Favoritos</span>
      </div>
      <button type="button" class="sheet-close-btn" data-action="close-drawer" aria-label="Cerrar deseos">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
    </div>

    <div class="sheet-content-scroll" id="wishlistItemsContainer">
      <!-- Renderizado dinámico desde store-pwa.js -->
    </div>
  </aside>

  <!-- F. Modal Ficha de Producto Anatómica (PDP) Dual: Bottom-Sheet Móvil & Centrado Desktop -->
  <div id="productModalBackdrop" class="pdp-modal-backdrop"></div>
  <aside id="productModal" class="pdp-modal-sheet" aria-label="Ficha de producto anatómica" role="dialog" aria-modal="true">
    <div class="sheet-handle-bar mobile-only-handle"></div>
    <button type="button" class="pdp-close-btn" data-action="close-product-modal" aria-label="Cerrar ficha">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
    <div id="productModalContent" class="pdp-modal-scroll">
      <!-- Renderizado dinámico desde store-pwa.js -->
    </div>
  </aside>

  <!-- G. Modal Checkout en 4 Pasos D2C (Envío, Pagos Múltiples, Confirmación & WhatsApp) -->
  <div id="checkoutModalBackdrop" class="checkout-modal-backdrop"></div>
  <aside id="checkoutModal" class="checkout-modal-sheet" aria-label="Proceso de compra seguro" role="dialog" aria-modal="true">
    <div class="sheet-handle-bar mobile-only-handle"></div>
    <button type="button" class="checkout-close-btn" data-action="close-checkout" aria-label="Cerrar checkout">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>

    <!-- Header del Checkout & Stepper de 4 Pasos -->
    <div class="checkout-header">
      <div class="checkout-brand-title">CHECKOUT SEGURO // D2C MULTIVERSO</div>
      <div class="checkout-stepper" id="checkoutStepper">
        <div class="stepper-step active" data-step="1">
          <span class="stepper-num">1</span>
          <span class="stepper-label">ENVÍO</span>
        </div>
        <div class="stepper-divider"></div>
        <div class="stepper-step" data-step="2">
          <span class="stepper-num">2</span>
          <span class="stepper-label">PAGO</span>
        </div>
        <div class="stepper-divider"></div>
        <div class="stepper-step" data-step="3">
          <span class="stepper-num">3</span>
          <span class="stepper-label">RESUMEN</span>
        </div>
        <div class="stepper-divider"></div>
        <div class="stepper-step" data-step="4">
          <span class="stepper-num">4</span>
          <span class="stepper-label">CONFIRMACIÓN</span>
        </div>
      </div>
    </div>

    <!-- Barra de Urgencia: Reserva de Stock Sincronizada (5 Minutos) -->
    <div class="checkout-timer-bar" id="checkoutTimerBanner">
      <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.72rem; font-weight: 700;">
        <div style="display: flex; align-items: center; gap: 6px;">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#e11d48" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <span style="color: #9f1239; text-transform: uppercase; letter-spacing: 0.04em;">Stock Apartado Exclusivo</span>
        </div>
        <span id="checkoutStockTimerRemaining" style="font-family: monospace; font-size: 0.8rem; font-weight: 800; color: #e11d48;">05:00</span>
      </div>
      <div style="width: 100%; height: 3px; background: #ffe4e6; border-radius: 2px; overflow: hidden; margin-top: 5px;">
        <div id="checkoutStockProgressBar" style="width: 100%; height: 100%; background: #e11d48; transition: width 1s linear;"></div>
      </div>
    </div>

    <!-- Indicador Reactivo de Modalidad de Carrito (Directo, Bajo Pedido o Carrito Mixto) -->
    <div id="checkoutCartTypeNotice" class="checkout-cart-type-notice" style="display: none;"></div>

    <div class="checkout-content-scroll" id="checkoutContentScroll">
      <!-- PASO 1: DATOS DE CONTACTO, ENVÍO COURIER Y REGISTRO OPCIONAL DE COMPRADOR -->
      <div id="checkoutStep1" class="checkout-step-panel active">
        <!-- Acceso Rápido con Google -->
        <div class="checkout-google-auth-box">
          <button type="button" class="btn-google-auth" data-action="google-login" data-source="checkout">
            <svg viewBox="0 0 24 24" width="18" height="18" class="google-icon">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span>Continuar con Google</span>
          </button>
          <div class="auth-divider-line">
            <span>o completa tus datos de entrega</span>
          </div>
        </div>

        <div class="checkout-section-header">
          <h3 class="checkout-section-title">1. Datos de Contacto y Destino</h3>
          <p class="checkout-section-desc">Entregas mediante Paquetería Courier Nacional Express. Cero entregas en estaciones de metro.</p>
        </div>

        <form id="checkoutShippingForm" onsubmit="return false;" class="checkout-form">
          <div class="checkout-form-group">
            <label for="checkoutCustomerName" class="checkout-label">Nombre Completo *</label>
            <input type="text" id="checkoutCustomerName" class="checkout-input" placeholder="Ej: Carlos Mendoza" required autocomplete="name">
          </div>

          <div class="checkout-form-row">
            <div class="checkout-form-group" style="flex: 1;">
              <label for="checkoutCustomerPhone" class="checkout-label">Teléfono Celular *</label>
              <input type="tel" id="checkoutCustomerPhone" class="checkout-input" placeholder="10 dígitos (ej: 5512345678)" maxlength="10" required autocomplete="tel">
            </div>
            <div class="checkout-form-group" style="flex: 1;">
              <label for="checkoutCustomerEmail" class="checkout-label">Correo Electrónico *</label>
              <input type="email" id="checkoutCustomerEmail" class="checkout-input" placeholder="correo@ejemplo.com" required autocomplete="email">
            </div>
          </div>

          <!-- Dirección Postal SEPOMEX -->
          <div class="checkout-form-row">
            <div class="checkout-form-group" style="flex: 1;">
              <label for="checkoutShippingZip" class="checkout-label">Código Postal (5 dígitos) *</label>
              <div style="display: flex; gap: 6px;">
                <input type="text" id="checkoutShippingZip" class="checkout-input font-mono" placeholder="Ej: 06000" maxlength="5" required>
                <button type="button" class="btn-zip-lookup" data-action="lookup-postal-code" title="Validar cobertura SEPOMEX">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </button>
              </div>
            </div>
            <div class="checkout-form-group" style="flex: 1;">
              <label for="checkoutShippingState" class="checkout-label">Estado *</label>
              <input type="text" id="checkoutShippingState" class="checkout-input" placeholder="Ej: Ciudad de México" required>
            </div>
          </div>

          <div class="checkout-form-row">
            <div class="checkout-form-group" style="flex: 1;">
              <label for="checkoutShippingCity" class="checkout-label">Municipio / Alcaldía *</label>
              <input type="text" id="checkoutShippingCity" class="checkout-input" placeholder="Ej: Cuauhtémoc" required>
            </div>
            <div class="checkout-form-group" style="flex: 1;">
              <label for="checkoutShippingColony" class="checkout-label">Colonia *</label>
              <input type="text" id="checkoutShippingColony" class="checkout-input" placeholder="Ej: Centro" required>
            </div>
          </div>

          <div class="checkout-form-group">
            <label for="checkoutShippingStreet" class="checkout-label">Calle, Número Exterior e Interior *</label>
            <input type="text" id="checkoutShippingStreet" class="checkout-input" placeholder="Ej: Av. Juárez 42, Depto 302" required autocomplete="street-address">
          </div>

          <div class="checkout-form-group">
            <label for="checkoutShippingRefs" class="checkout-label">Referencias de Entrega (Opcional)</label>
            <input type="text" id="checkoutShippingRefs" class="checkout-input" placeholder="Ej: Entre calle Balderas y Revillagigedo, portón café">
          </div>

          <!-- Selector de Estrategia para Carrito Mixto Híbrido -->
          <div id="mixedCartStrategyCard" class="mixed-shipping-strategy-card" style="display: none;">
            <div class="mixed-strategy-header">
              <div style="display: flex; align-items: center; gap: 6px;">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#2563eb" stroke-width="2"><path d="M16 16v1a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v1"/><rect x="18" y="8" width="5" height="11" rx="1"/><circle cx="5" cy="18" r="2"/><circle cx="12" cy="18" r="2"/><circle cx="20" cy="18" r="2"/></svg>
                <strong style="font-family: var(--font-heading); font-size: 0.78rem; text-transform: uppercase; color: #1e3a8a; letter-spacing: 0.04em;">Estrategia de Despacho // Carrito Mixto</strong>
              </div>
              <p style="font-size: 0.72rem; color: var(--text-secondary); margin: 4px 0 0; line-height: 1.35;">
                Tu orden combina prendas en bodega y prendas bajo pedido. Selecciona la modalidad de entrega:
              </p>
            </div>

            <div class="mixed-strategy-options">
              <label class="mixed-option-card active" for="strategyConsolidated">
                <input type="radio" name="checkoutDispatchStrategy" id="strategyConsolidated" value="consolidated" checked>
                <div class="mixed-option-body">
                  <div style="display: flex; justify-content: space-between; align-items: center;">
                    <strong style="font-size: 0.78rem; color: #050505;">Despacho Consolidado Ecológico (1 Solo Paquete)</strong>
                    <span class="badge-mixed-rec">RECOMENDADO</span>
                  </div>
                  <p style="font-size: 0.7rem; color: var(--text-secondary); margin: 3px 0 4px; line-height: 1.35;">
                    Apartamos tu stock físico en bodega hoy y despachamos todo junto cuando concluya la confección bajo pedido (3 a 5 días hábiles). Ahorras flete.
                  </p>
                  <span class="strategy-shipping-fee" id="consolidatedFeeLabel">Envío Courier: ${{ number_format((float)($shippingRule['flatRate'] ?? 149), 2) }} MXN (o GRATIS si total >= ${{ number_format((float)($shippingRule['freeShippingThreshold'] ?? 999), 0) }} MXN)</span>
                </div>
              </label>

              <label class="mixed-option-card" for="strategySplit">
                <input type="radio" name="checkoutDispatchStrategy" id="strategySplit" value="split">
                <div class="mixed-option-body">
                  <div style="display: flex; justify-content: space-between; align-items: center;">
                    <strong style="font-size: 0.78rem; color: #050505;">Despacho Dividido / Split (2 Paquetes Separados)</strong>
                    <span class="badge-mixed-fast">ENTREGA RÁPIDA</span>
                  </div>
                  <p style="font-size: 0.7rem; color: var(--text-secondary); margin: 3px 0 4px; line-height: 1.35;">
                    Paquete 1 (Stock físico) sale de inmediato en 24-48h. Paquete 2 (Bajo pedido) sale en cuanto termine su confección.
                  </p>
                  <span class="strategy-shipping-fee" id="splitFeeLabel">Aplica costo de 2do flete courier (${{ number_format((float)($shippingRule['flatRate'] ?? 149), 2) }} MXN adicional)</span>
                </div>
              </label>
            </div>

            <div id="mixedCartLinesBreakdown" class="mixed-cart-lines-breakdown"></div>
          </div>

          <!-- Casilla de Registro de Comprador Integrada -->
          <div class="checkout-account-optin">
            <label class="checkout-checkbox-card" for="checkoutCreateAccount">
              <input type="checkbox" id="checkoutCreateAccount" checked>
              <div class="optin-body">
                <strong class="optin-title">Crear mi Cuenta de Comprador</strong>
                <span class="optin-sub">Guarda tus direcciones y consulta tu historial de compras D2C en cualquier momento.</span>
              </div>
            </label>
          </div>

          <!-- Cupón de Descuento de Aliados / CMS en Checkout (Hub 7 TASK-STR-04) -->
          <div class="checkout-coupon-box" id="checkoutCouponBox">
            <!-- Inyectado dinámicamente desde store-pwa.js -->
          </div>

          <!-- Resumen Financiero Preliminar -->
          <div class="checkout-summary-card">
            <div class="checkout-summary-line">
              <span>Subtotal Prendas</span>
              <span id="chkSubtotalAmount">$0.00 MXN</span>
            </div>
            <div class="checkout-summary-line">
              <span>Envío Courier Express Nacional</span>
              <span id="chkShippingAmount">${{ number_format((float)($shippingRule['flatRate'] ?? 149), 2) }} MXN</span>
            </div>
            <div class="checkout-summary-line total">
              <span>Total Estimado</span>
              <span id="chkTotalAmount">$0.00 MXN</span>
            </div>
          </div>

          <button type="button" class="btn-checkout-primary" data-action="checkout-to-step-2">
            <span>CONTINUAR AL MÉTODO DE PAGO</span>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </form>
      </div>

      <!-- PASO 2: MÉTODOS DE PAGO (TARJETAS BANCARIAS & OXXO PAY - REGLA 3 CERO SPEI) -->
      <div id="checkoutStep2" class="checkout-step-panel" style="display: none;">
        <div class="checkout-section-header">
          <h3 class="checkout-section-title">2. Selecciona tu Forma de Pago</h3>
          <p class="checkout-section-desc">Selecciona la opción que prefieras. Cuentas y pasarelas verificadas en México.</p>
        </div>

        <div class="payment-methods-grid">
          <!-- Opción 1: Tarjetas de Débito y Crédito (Predeterminada) -->
          <label class="payment-method-card active" for="payMethodCard">
            <div class="payment-card-top">
              <div class="payment-radio-wrap">
                <input type="radio" name="checkoutPaymentMethod" id="payMethodCard" value="credit_debit_card" checked>
                <span class="payment-method-name">Tarjetas de Débito / Crédito</span>
              </div>
              <div class="card-brand-logos">
                <span class="card-tag">VISA</span>
                <span class="card-tag">MC</span>
                <span class="card-tag">AMEX</span>
              </div>
            </div>
            <div class="payment-card-body" id="cardDetailsArea" style="display: block;">
              <p style="font-size: 0.74rem; color: var(--text-secondary); margin-bottom: 10px; line-height: 1.4;">
                Procesamiento seguro mediante tokenización SSL de 256 bits.
              </p>
              <div class="card-inputs-grid">
                <div class="checkout-form-group">
                  <label for="checkoutCardNumber" class="checkout-label">Número de Tarjeta (16 dígitos) *</label>
                  <input type="text" id="checkoutCardNumber" class="checkout-input font-mono" placeholder="4000 1234 5678 9010" maxlength="19">
                </div>
                <div class="checkout-form-row">
                  <div class="checkout-form-group" style="flex: 1;">
                    <label for="checkoutCardExpiry" class="checkout-label">Expiración (MM/AA) *</label>
                    <input type="text" id="checkoutCardExpiry" class="checkout-input font-mono" placeholder="MM/AA" maxlength="5">
                  </div>
                  <div class="checkout-form-group" style="flex: 1;">
                    <label for="checkoutCardCvv" class="checkout-label">CVV / CVC *</label>
                    <input type="password" id="checkoutCardCvv" class="checkout-input font-mono" placeholder="123" maxlength="4">
                  </div>
                </div>
                <div class="checkout-form-group">
                  <label for="checkoutCardHolder" class="checkout-label">Nombre en la Tarjeta *</label>
                  <input type="text" id="checkoutCardHolder" class="checkout-input" placeholder="Como aparece en el plástico">
                </div>
              </div>
            </div>
          </label>

          <!-- Opción 3: OXXO Pay Efectivo -->
          <label class="payment-method-card" for="payMethodOxxo">
            <div class="payment-card-top">
              <div class="payment-radio-wrap">
                <input type="radio" name="checkoutPaymentMethod" id="payMethodOxxo" value="oxxo_pay">
                <span class="payment-method-name">Pago en Efectivo OXXO Pay</span>
              </div>
              <span class="payment-badge-oxxo">VIGENCIA 48H</span>
            </div>
            <div class="payment-card-body" id="oxxoDetailsArea" style="display: none;">
              <p style="font-size: 0.74rem; color: var(--text-secondary); line-height: 1.4;">
                Se generará una referencia digital de 14 dígitos para pagar en efectivo en cualquier ventanilla OXXO de la República Mexicana. Tu stock queda apartado durante 48 horas.
              </p>
            </div>
          </label>
        </div>

        <div class="checkout-nav-actions">
          <button type="button" class="btn-checkout-secondary" data-action="checkout-back-step-1">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            <span>VOLVER A ENVÍO</span>
          </button>
          <button type="button" class="btn-checkout-primary" data-action="checkout-to-step-3">
            <span>REVISAR RESUMEN DE COMPRA</span>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>
      </div>

      <!-- PASO 3: RESUMEN INTEGRAL DE LA COMPRA PREVIO A CONFIRMACIÓN -->
      <div id="checkoutStep3" class="checkout-step-panel" style="display: none;">
        <div class="checkout-section-header">
          <h3 class="checkout-section-title">3. Resumen de tu Compra</h3>
          <p class="checkout-section-desc">Verifica el destino, método de pago y desglose de prendas antes de generar tu pedido.</p>
        </div>

        <div id="checkoutReviewArea" class="checkout-review-area">
          <!-- Inyectado dinámicamente desde store-pwa.js -->
        </div>

        <div class="checkout-nav-actions">
          <button type="button" class="btn-checkout-secondary" data-action="checkout-back-step-2">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            <span>VOLVER A FORMA DE PAGO</span>
          </button>
          <button type="button" class="btn-checkout-primary" data-action="checkout-confirm-order">
            <span>CONFIRMAR Y FINALIZAR PEDIDO</span>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          </button>
        </div>
      </div>

      <!-- PASO 4: CONFIRMACIÓN, FOLIO INMUTABLE Y POST-VENTA -->
      <div id="checkoutStep4" class="checkout-step-panel" style="display: none;">
        <div class="order-success-hero">
          <div class="order-success-icon-box">
            <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="#059669" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <h3 class="order-success-title">¡Pedido Registrado con Éxito!</h3>
          <p class="order-success-sub">Tu reserva de stock ha sido asegurada. Comunícate al WhatsApp oficial para notificar tu pago y agilizar el despacho de tu paquete.</p>
          
          <div class="order-folio-badge">
            <span class="folio-label">FOLIO OFICIAL:</span>
            <strong class="folio-val" id="orderConfirmedFolio">#ORD-2026-0000</strong>
          </div>
        </div>

        <div class="confirmed-payment-box" id="confirmedPaymentBox">
          <!-- Inyectado dinámicamente desde store-pwa.js -->
        </div>

        <div class="checkout-summary-card">
          <div style="font-family: var(--font-heading); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
            Desglose Financiero Oficial
          </div>
          <div class="checkout-summary-line">
            <span>Subtotal Prendas</span>
            <span id="confirmedSubtotal">$0.00 MXN</span>
          </div>
          <div class="checkout-summary-line">
            <span>Envío Express Courier</span>
            <span id="confirmedShipping">$0.00 MXN</span>
          </div>
          <div class="checkout-summary-line" style="font-size: 0.72rem; color: var(--text-muted);">
            <span>IVA (16% incluido)</span>
            <span id="confirmedVat">$0.00 MXN</span>
          </div>
          <div class="checkout-summary-line total">
            <span>Gran Total</span>
            <span id="confirmedTotal">$0.00 MXN</span>
          </div>
        </div>

        <div class="confirmed-items-card">
          <div style="font-family: var(--font-heading); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
            Prendas Apartadas en esta Orden
          </div>
          <div id="confirmedItemsList"></div>
        </div>

        <div class="confirmed-shipping-card">
          <div style="font-family: var(--font-heading); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
            Destino Courier Registrado
          </div>
          <p id="confirmedShippingAddress" style="font-size: 0.78rem; color: var(--text-secondary); line-height: 1.4; margin: 0;"></p>
        </div>

        <div id="confirmedMixedNotice" class="confirmed-mixed-notice" style="display: none;"></div>

        <div class="confirmed-action-buttons">
          <button type="button" class="btn-checkout-primary" data-action="checkout-to-account" style="width: 100%;">
            <span>VER EN MI CUENTA & RASTREAR PEDIDO</span>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>

          <button type="button" class="btn-checkout-secondary" data-action="close-checkout" style="width: 100%;">
            <span>VOLVER AL CATÁLOGO DE LA TIENDA</span>
          </button>
        </div>
      </div>
    </div>
  </aside>

  <!-- H. Modal Lead Magnet Dir A: Vouchers de Aliados y Taquilla (Hub 7 TASK-STR-04) -->
  <div id="leadMagnetModalBackdrop" class="lead-magnet-backdrop" aria-hidden="true"></div>
  <aside id="leadMagnetModal" class="lead-magnet-modal-card" aria-label="Voucher Digital de Aliado">
    <div class="lead-magnet-modal-header">
      <div style="display: flex; align-items: center; gap: 8px;">
        <span class="badge-voucher-header">CONVENIO HUB 7</span>
        <strong style="font-family: var(--font-heading); font-size: 0.85rem; letter-spacing: 0.04em;">VOUCHER DIGITAL DIR A</strong>
      </div>
      <button type="button" class="btn-modal-close" data-action="close-lead-magnet" aria-label="Cerrar modal de cortesía">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
    </div>

    <div class="lead-magnet-modal-body" id="leadMagnetModalBody">
      <div id="leadMagnetCaptureView">
        <div class="lead-magnet-hero-info">
          <h3 id="leadModalCampaignHeadline" style="font-family: var(--font-heading); font-size: 1.05rem; font-weight: 800; margin: 0 0 6px; color: #050505;">
            Cortesía Especial en Paradiso Club
          </h3>
          <p id="leadModalCampaignSub" style="font-size: 0.76rem; color: var(--text-secondary); line-height: 1.4; margin: 0 0 16px;">
            Registra tus datos para generar tu voucher digital oficial con código QR y folio de canje para taquilla o barra.
          </p>
        </div>

        <form id="leadCaptureForm" class="lead-capture-form" onsubmit="event.preventDefault();">
          <div class="checkout-form-group">
            <label for="leadFullName" class="checkout-label">Nombre Completo *</label>
            <input type="text" id="leadFullName" class="checkout-input" placeholder="Tu nombre y apellido" required>
          </div>
          <div class="checkout-form-group">
            <label for="leadWhatsApp" class="checkout-label">WhatsApp / Celular (10 dígitos) *</label>
            <input type="tel" id="leadWhatsApp" class="checkout-input" placeholder="5512345678" maxlength="10" required>
          </div>
          <div class="checkout-form-group">
            <label for="leadEmail" class="checkout-label">Correo Electrónico *</label>
            <input type="email" id="leadEmail" class="checkout-input" placeholder="correo@ejemplo.com" required>
          </div>

          <button type="button" class="btn-checkout-primary" data-action="submit-lead-magnet" style="width: 100%; margin-top: 10px;">
            <span>GENERAR MI VOUCHER DIGITAL</span>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          </button>
        </form>
      </div>

      <div id="leadMagnetVoucherCard" class="voucher-ticket-box" style="display: none;">
        <!-- Inyectado dinámicamente desde store-pwa.js -->
      </div>
    </div>
  </aside>

  <!-- I. Banner Flotante de Permiso Web Push No Invasivo (TASK-STR-06) -->
  <aside id="pushPromptBanner" class="push-prompt-banner" aria-label="Solicitud de Notificaciones Web Push" style="display: none;">
    <div class="push-prompt-inner">
      <div class="push-prompt-icon-col">
        <div class="push-prompt-icon-badge">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
        </div>
      </div>
      <div class="push-prompt-text-col">
        <strong class="push-prompt-title">Activa Alertas de Rastreo & Stock</strong>
        <p class="push-prompt-desc">
          Recibe notificaciones en tu celular sobre el despacho courier en vivo de tu paquete y avisos cuando tu reserva de stock esté por expirar.
        </p>
        <div class="push-prompt-actions">
          <button type="button" class="btn-push-allow" data-action="request-push-permission">
            ACTIVAR ALERTAS
          </button>
          <button type="button" class="btn-push-dismiss" data-action="dismiss-push-prompt">
            Más tarde
          </button>
        </div>
      </div>
      <button type="button" class="btn-push-close" data-action="dismiss-push-prompt" aria-label="Cerrar aviso de alertas">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/>
          <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
  </aside>

  <!-- Inyección de Datos Reales de Base de Datos para Hidratación Reactiva PWA -->
  <script>
    window.__INITIAL_STORE_PRODUCTS__ = @json($initialProducts ?? []);
    window.__ACTIVE_UNIVERSE__ = @json($activeUniverse ?? 'marte');
    window.__UNIVERSE_META__ = @json($universeMeta ?? []);
    window.__STORE_CSRF_TOKEN__ = "{{ csrf_token() }}";
    window.__AUTO_OPEN_PRODUCT_ID__ = @json($autoOpenProductId ?? null);
  </script>
  <script src="{{ asset('js/storefront/store-pwa.js') }}"></script>
  @if(!empty($autoOpenProductId))
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        if (typeof openProductModal === 'function') {
          openProductModal(@json($autoOpenProductId));
        }
      });
    </script>
  @endif
  @stack('scripts')
</body>
</html>
