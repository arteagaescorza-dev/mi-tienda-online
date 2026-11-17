<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="theme-color" content="#ffffff">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Anti-FOUC Theme Switcher Engine (Regla 2: Dark / Light / System) -->
  <script>
    (function() {
      try {
        var t = localStorage.getItem('app_theme') || 'system';
        var d = t === 'dark' || (t === 'system' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
        if (d) {
          document.documentElement.classList.add('dark');
          document.documentElement.setAttribute('data-theme', 'dark');
        } else {
          document.documentElement.classList.remove('dark');
          document.documentElement.setAttribute('data-theme', 'light');
        }
      } catch(e) {}
    })();
  </script>

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

  <!-- Google Fonts Dinámicas con Soporte Multitipografía para Hub 2 y Vitrina -->
  @php
    $googleFontsToLoad = [
      $currentStorefront['typography_heading'] ?? 'Outfit',
      $currentStorefront['typography_subtitle'] ?? 'Outfit',
      $currentStorefront['typography_body'] ?? 'Plus Jakarta Sans',
      $currentStorefront['typography_accent'] ?? 'Plus Jakarta Sans',
      'Outfit',
      'Plus Jakarta Sans'
    ];
    if (isset($sections)) {
      foreach($sections as $s) {
        $st = is_array($s->settings) ? $s->settings : (json_decode($s->settings ?? '{}', true) ?: []);
        if (!empty($st['title_font'])) $googleFontsToLoad[] = $st['title_font'];
        if (!empty($st['subtitle_font'])) $googleFontsToLoad[] = $st['subtitle_font'];
        if (!empty($st['font_family'])) $googleFontsToLoad[] = $st['font_family'];
      }
    }
    $cleanGoogleFonts = array_unique(array_filter(array_map('trim', $googleFontsToLoad), function($f) {
      return !empty($f) && !in_array(strtolower($f), ['font-sans', 'font-serif', 'font-mono', 'sans-serif', 'inherit', 'inherit / vitrina']);
    }));
    $fontFamiliesQuery = collect($cleanGoogleFonts)->map(function($f) {
      return 'family=' . str_replace(' ', '+', $f) . ':wght@300;400;500;600;700;800;900';
    })->implode('&');
  @endphp
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  @if(!empty($fontFamiliesQuery))
    <link href="https://fonts.googleapis.com/css2?{!! $fontFamiliesQuery !!}&display=swap" rel="stylesheet">
  @else
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  @endif

  <!-- Estilos Mobile-First & Desktop Adaptive Namespaced -->
  <link rel="stylesheet" href="{{ asset('css/store-pwa.css') }}?v={{ file_exists(public_path('css/store-pwa.css')) ? filemtime(public_path('css/store-pwa.css')) : time() }}">

  <!-- Variables CSS Semánticas de Marca, Colores y Tipografías de Hub 2 -->
  <style id="hub2DynamicTheme">
    :root {
      --font-heading: '{{ $currentStorefront['typography_heading'] ?? 'Outfit' }}', sans-serif;
      --font-subtitle: '{{ $currentStorefront['typography_subtitle'] ?? 'Outfit' }}', sans-serif;
      --font-body: '{{ $currentStorefront['typography_body'] ?? 'Plus Jakarta Sans' }}', sans-serif;
      --font-accent: '{{ $currentStorefront['typography_accent'] ?? 'Plus Jakarta Sans' }}', sans-serif;

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

    /* Mapeo de Tipografías Semánticas */
    body.store-body {
      font-family: var(--font-body) !important;
    }
    h1, h2, h3, .store-heading, .brand-name, .hero-title {
      font-family: var(--font-heading) !important;
    }
    .hero-desc, .product-desc, .brand-sub, .section-subtitle {
      font-family: var(--font-subtitle) !important;
    }
    .btn-primary, .btn-hero-primary, .dock-tab-label, .hero-tag-pill, .product-stock-badge, .badge, .btn-pill-action, .accent-font {
      font-family: var(--font-accent) !important;
    }

    /* Override Semántico Automático para Modo Oscuro en Tokens de Marca */
    html.dark,
    html[data-theme="dark"],
    body.dark {
      --brand-bg: #0c0d12 !important;
      --brand-surface: #151720 !important;
      --brand-text: #f8fafc !important;
      --brand-muted: #94a3b8 !important;
      --brand-border: #272b3a !important;
      --bg-primary: #0c0d12 !important;
      --bg-surface: #151720 !important;
      --bg-secondary: #1d202c !important;
      --bg-tertiary: #262a3a !important;
      --text-primary: #f8fafc !important;
      --text-secondary: #cbd5e1 !important;
      --text-muted: #94a3b8 !important;
      --border-subtle: #252938 !important;
      --border-medium: #373d52 !important;
    }

    /* Mapeo Concreto y Forzado de los 8 Tokens al Storefront (Modo Claro) */
    @if(!empty($currentStorefront['backgroundColor']))
      html:not(.dark) body.store-body {
        background-color: var(--brand-bg) !important;
      }
    @endif

    @if(!empty($currentStorefront['surfaceColor']))
      html:not(.dark) .product-card,
      html:not(.dark) .sticky-header-wrapper,
      html:not(.dark) .navbar-desktop,
      html:not(.dark) .navbar-mobile,
      html:not(.dark) .bottom-sheet-content,
      html:not(.dark) .modal-card,
      html:not(.dark) .dock-tab {
        background-color: var(--brand-surface) !important;
      }
    @endif

    @if(!empty($currentStorefront['textColor']))
      html:not(.dark) body.store-body,
      html:not(.dark) .product-title,
      html:not(.dark) .nav-link-item,
      html:not(.dark) .brand-name,
      html:not(.dark) .store-heading,
      html:not(.dark) .filter-group-title,
      html:not(.dark) .universe-pill,
      html:not(.dark) h1:not(.aeo-hero-title):not(.explore-card-title):not(.hero-title),
      html:not(.dark) h2:not(.aeo-hero-title):not(.explore-card-title):not(.hero-title),
      html:not(.dark) h3:not(.aeo-hero-title):not(.explore-card-title):not(.hero-title),
      html:not(.dark) h4 {
        color: var(--brand-text) !important;
      }
    @endif

    @if(!empty($currentStorefront['mutedTextColor']))
      html:not(.dark) .product-category-label,
      html:not(.dark) .brand-sub,
      html:not(.dark) .marquee-sep,
      html:not(.dark) .text-muted,
      html:not(.dark) .product-desc,
      html:not(.dark) .product-sku-code {
        color: var(--brand-muted) !important;
      }
    @endif

    @if(!empty($currentStorefront['borderColor']))
      html:not(.dark) .product-card,
      html:not(.dark) .sticky-header-wrapper,
      html:not(.dark) .size-pill-btn,
      html:not(.dark) .btn-pill-action,
      html:not(.dark) .navbar-desktop,
      html:not(.dark) .universe-pill,
      html:not(.dark) .minimal-marquee-bar,
      html:not(.dark) .brand-border {
        border-color: var(--brand-border) !important;
      }
    @endif

    @if(!empty($currentStorefront['secondaryColor']))
      html:not(.dark) .product-stock-badge,
      html:not(.dark) .product-stock-badge.ready {
        color: var(--brand-secondary) !important;
        border-color: var(--brand-secondary) !important;
        background-color: rgba(255, 255, 255, 0.95) !important;
      }
    @endif

    @if(!empty($currentStorefront['accentColor']))
      html:not(.dark) .product-price,
      html:not(.dark) .price-highlight,
      html:not(.dark) .accent-price {
        color: var(--brand-accent) !important;
      }
      .btn-add-cart,
      .btn-primary,
      .btn-checkout,
      .hero-cta-btn,
      html:not(.dark) .size-pill-btn.selected,
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
      html:not(.dark) .nav-link-item:hover,
      html:not(.dark) .nav-link-item.active {
        color: var(--brand-accent) !important;
      }
    @endif

    /* Alto Contraste y Visibilidad para Dock Inferior e Iconos de la App */
    .bottom-app-dock {
      background: rgba(10, 11, 16, 0.96) !important;
      backdrop-filter: blur(20px) !important;
      -webkit-backdrop-filter: blur(20px) !important;
      border-top: 1px solid rgba(255, 255, 255, 0.12) !important;
      box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.5) !important;
    }
    .dock-tab-btn {
      color: rgba(255, 255, 255, 0.65) !important;
      transition: color 0.2s ease, transform 0.2s ease !important;
    }
    .dock-tab-btn svg {
      stroke: currentColor !important;
      stroke-width: 1.85 !important;
    }
    .dock-tab-btn .dock-tab-label {
      color: rgba(255, 255, 255, 0.65) !important;
      font-size: 0.65rem !important;
      font-weight: 600 !important;
    }
    .dock-tab-btn:hover {
      color: #ffffff !important;
    }
    .dock-tab-btn:hover .dock-tab-label {
      color: #ffffff !important;
    }
    .dock-tab-btn.active {
      color: #ffffff !important;
    }
    .dock-tab-btn.active svg {
      stroke: #ffffff !important;
      stroke-width: 2.3 !important;
      filter: drop-shadow(0 0 6px rgba(255, 255, 255, 0.45)) !important;
    }
    .dock-tab-btn.active .dock-tab-label {
      color: #ffffff !important;
      font-weight: 800 !important;
    }
    .btn-nav-action {
      color: var(--text-primary, #ffffff) !important;
    }

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
    .main-page-wrapper {
      overflow-x: hidden !important;
      width: 100%;
    }
    .hero-editorial-section {
      padding-top: 0 !important;
      padding-bottom: 0 !important;
      margin-top: 0;
      margin-bottom: 0;
    }
    /* Soporte para Ancho Completo Horizontal (100% solo horizontal) y Full Page */
    .hero-editorial-section.hero-full-width,
    .hero-editorial-section.hero-full-width-h,
    .hero-editorial-section.hero-fullpage-v {
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
      padding-top: 0 !important;
      padding-bottom: 0 !important;
      margin-top: 0 !important;
      margin-bottom: 0 !important;
    }
    .hero-editorial-section.hero-full-width .hero-banner-card,
    .hero-editorial-section.hero-full-width-h .hero-banner-card,
    .hero-editorial-section.hero-fullpage-v .hero-banner-card {
      border-radius: 0 !important;
      width: 100% !important;
      max-width: 100vw !important;
      box-sizing: border-box !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
      margin-left: 0 !important;
      margin-right: 0 !important;
    }
    .hero-editorial-section.hero-full-width .hero-custom-container,
    .hero-editorial-section.hero-full-width-h .hero-custom-container,
    .hero-editorial-section.hero-fullpage-v .hero-custom-container {
      width: 100% !important;
      max-width: 100vw !important;
      box-sizing: border-box !important;
      border-radius: 0 !important;
    }

    /* Soporte para Hero y Carrusel Fullpage (100% de alto de pantalla) */
    .hero-editorial-section.hero-fullpage-v .hero-banner-card,
    .hero-editorial-section.hero-fullpage-v .hero-custom-container {
      min-height: calc(100vh - 56px) !important;
      height: calc(100vh - 56px) !important;
      display: flex !important;
      flex-direction: column !important;
      justify-content: center !important;
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

    /* REGLAS 2 & 3: Acordeones, Árbol Taxonómico y Checkboxes Facetados */
    .filter-accordion-block {
      border-bottom: 1px solid var(--border-subtle, #e4e4e7);
      padding: 10px 0;
    }
    .filter-accordion-block:last-child {
      border-bottom: none;
    }
    .filter-accordion-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      background: none;
      border: none;
      padding: 6px 0;
      cursor: pointer;
      font-family: var(--font-heading);
      font-size: 0.74rem;
      font-weight: 800;
      letter-spacing: 0.06em;
      color: var(--text-primary, #09090b);
      text-transform: uppercase;
    }
    .filter-accordion-header .accordion-chevron {
      transition: transform 0.25s ease;
      stroke: var(--text-muted, #71717a);
    }
    .filter-accordion-block.is-collapsed .filter-accordion-content {
      display: none;
    }
    .filter-accordion-block.is-collapsed .accordion-chevron {
      transform: rotate(-90deg);
    }
    .filter-accordion-content {
      padding-top: 8px;
      padding-bottom: 4px;
    }
    .filter-tree-container {
      display: flex;
      flex-direction: column;
      gap: 3px;
    }
    .filter-tree-node {
      display: flex;
      flex-direction: column;
    }
    .filter-tree-children {
      padding-left: 18px;
      margin-top: 3px;
      margin-bottom: 5px;
      border-left: 2px solid var(--border-subtle, #e4e4e7);
      display: flex;
      flex-direction: column;
      gap: 3px;
    }
    .filter-checkbox-label {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.78rem;
      color: var(--text-primary, #18181b);
      cursor: pointer;
      padding: 4px 6px;
      border-radius: 6px;
      transition: background 0.15s ease;
      user-select: none;
    }
    .filter-checkbox-label:hover {
      background: rgba(0, 0, 0, 0.04);
    }
    .filter-facet-checkbox {
      width: 15px;
      height: 15px;
      accent-color: var(--brand-primary, #09090b);
      cursor: pointer;
      flex-shrink: 0;
      border-radius: 4px;
    }
    .filter-checkbox-grid {
      display: flex;
      flex-direction: column;
      gap: 3px;
      max-height: 200px;
      overflow-y: auto;
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
      $isMarqueeActive = isset($announcementActive)
        ? filter_var($announcementActive, FILTER_VALIDATE_BOOLEAN)
        : filter_var(\Illuminate\Support\Facades\Cache::get('cms_announcement_active', true), FILTER_VALIDATE_BOOLEAN);
      $activeAnnouncements = collect($announcements ?? [])
        ->filter(fn($item) => is_array($item) && filter_var($item['active'] ?? true, FILTER_VALIDATE_BOOLEAN) && !empty(trim((string)($item['text'] ?? ''))))
        ->values();
    @endphp
    @if($isMarqueeActive && $activeAnnouncements->isNotEmpty())
      <div class="minimal-marquee-bar" aria-label="Avisos importantes de la tienda">
        <div class="marquee-track">
          @for($i = 0; $i < 2; $i++)
            @foreach($activeAnnouncements as $announcement)
              @php
                $anUrl = trim((string)($announcement['link'] ?? ($announcement['url'] ?? '')));
              @endphp
              @if(!empty($anUrl) && $anUrl !== '#')
                <a href="{{ $anUrl }}" style="color: inherit; text-decoration: none;">
                  <span>{{ mb_strtoupper($announcement['text']) }}</span>
                </a>
              @else
                <span>{{ mb_strtoupper($announcement['text']) }}</span>
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
          <a href="{{ route('store.home', ['tab' => 'home']) }}" class="nav-link-item active" data-action="switch-dock-tab" data-dock-tab="home">INICIO</a>
          <a href="{{ route('store.home', ['tab' => 'explore']) }}" class="nav-link-item" data-action="switch-dock-tab" data-dock-tab="explore">EXPLORAR</a>
          <a href="{{ route('store.home', ['tab' => 'catalog']) }}" class="nav-link-item" data-action="switch-dock-tab" data-dock-tab="catalog">CATÁLOGO</a>
          <a href="#" class="nav-link-item" data-action="open-cart">
            <span>CARRITO</span>
            <span id="desktopNavCartCount" class="cart-count-badge" style="display:inline-flex;">0</span>
          </a>
          <a href="#" class="nav-link-item" data-action="switch-dock-tab" data-dock-tab="account">CUENTA</a>
        </nav>

        <!-- Botones de Acción Desktop: Búsqueda, Wishlist y Tema (Reglas 1, 2 y 5) -->
        <div class="nav-actions-desktop">
          <button type="button" class="btn-pill-action" data-action="toggle-search" title="Buscar prendas" aria-label="Buscar prendas">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <span>BUSCAR</span>
          </button>
          <button type="button" class="btn-pill-action" data-action="open-wishlist" title="Mis deseos" aria-label="Ver deseos">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            <span>DESEOS</span>
            <span id="desktopWishlistCount" class="cart-count-badge" style="display:none;">0</span>
          </button>
          <button type="button" class="btn-pill-action" data-action="toggle-app-theme-quick" title="Alternar Modo Claro / Oscuro" aria-label="Cambiar tema">
            <svg class="theme-icon-sun" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            <svg class="theme-icon-moon" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            <span>TEMA</span>
          </button>
        </div>
      </div>
    </header>

    <!-- 2. Header Móvil & Tablet App (Compacto, Táctil y Sin Desbordamiento) -->
    <header class="navbar-mobile">
      <div style="display: flex; align-items: center; gap: 6px; min-width: 0;">
        <button type="button" class="mobile-icon-btn" id="mobileFilterToggleBtn" data-action="open-filter-drawer" aria-label="Abrir filtros" style="display: none;">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
        </button>

        <!-- Logo de Marca Adaptativo para Formato App (Sin texto para evitar desbordamiento) -->
        <a href="{{ route('store.home') }}" class="nav-brand-group" aria-label="{{ $currentStorefront['brandName'] ?? 'MARTE' }}">
          <div class="brand-logo-box">
            @if(!empty($currentStorefront['logoMobileUrl']) || !empty($currentStorefront['logoUrl']))
              <img src="{{ $currentStorefront['logoMobileUrl'] ?? $currentStorefront['logoUrl'] }}" alt="{{ $currentStorefront['brandName'] ?? 'Logo' }}">
            @else
              <svg width="22" height="22" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="var(--text-primary, #050505)"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            @endif
          </div>
        </a>
      </div>

      <!-- Iconos de Acción App (Móvil & Tablet): Acceso Directo PWA, Tema, Búsqueda y Wishlist -->
      <div style="display: flex; align-items: center; gap: 4px;">
        <button type="button" class="btn-pwa-app-header" data-action="open-pwa-tutorial" aria-label="Instalar Acceso Directo App" title="Crear acceso directo PWA en inicio">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/><path d="M12 7v6"/><path d="M9.5 10.5L12 13l2.5-2.5"/></svg>
          <span class="pwa-btn-text">INSTALAR APP</span>
        </button>
        <button type="button" class="mobile-icon-btn" data-action="toggle-app-theme-quick" aria-label="Cambiar tema" title="Modo Claro / Oscuro">
          <svg class="theme-icon-sun" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
          <svg class="theme-icon-moon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </button>
        <button type="button" class="mobile-icon-btn" data-action="toggle-search" aria-label="Buscar prendas">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </button>
        <button type="button" class="mobile-icon-btn" data-action="open-wishlist" aria-label="Ver deseos" style="position: relative;">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
          <span id="mobileWishlistCount" class="cart-count-badge" style="position: absolute; top: 6px; right: 4px; font-size: 0.6rem; min-width: 14px; padding: 1px 4px; display: none;">0</span>
        </button>
      </div>
    </header>

    <!-- Banner Inteligente de Instalación PWA / Acceso Directo para Móvil y Tablet (Auto-detecta iOS vs Android) -->
    <div id="pwaInstallBanner" class="pwa-smart-install-banner" style="display: none;">
      <div class="pwa-banner-left">
        <div class="pwa-banner-icon">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/><path d="M12 7v6"/><path d="M9.5 10.5L12 13l2.5-2.5"/></svg>
        </div>
        <div class="pwa-banner-copy">
          <strong class="pwa-banner-title">Convierte la tienda en App de Inicio</strong>
          <span id="pwaBannerOsSubtitle" class="pwa-banner-sub">Tutorial rápido para crear acceso directo en tu dispositivo</span>
        </div>
      </div>
      <div class="pwa-banner-actions">
        <button type="button" class="btn-pwa-banner-open" data-action="open-pwa-tutorial">
          VER TUTORIAL
        </button>
        <button type="button" class="btn-pwa-banner-close" data-action="dismiss-pwa-banner" aria-label="Cerrar aviso de instalación PWA">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </div>

    <!-- Barra Desplegable de Búsqueda Conmutable (Regla 5) -->
    <div id="storeSearchBar" class="store-search-bar" style="display: none; padding: 10px 16px; background: var(--bg-primary, #ffffff); border-bottom: 1px solid var(--border-subtle, #e4e4e7); transition: all 0.2s ease;">
      <div class="container" style="display: flex; align-items: center; gap: 8px;">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; color: var(--text-muted, #71717a);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="search" id="storeLiveSearchInput" placeholder="Buscar prendas, estilos, categorías..." style="flex: 1; border: none; outline: none; background: transparent; font-family: var(--font-heading, sans-serif); font-size: 0.85rem; padding: 8px 4px; color: var(--text-primary, #09090b);" autocomplete="off">
        <button type="button" id="clearSearchBtn" data-action="clear-search" style="background: none; border: none; cursor: pointer; color: var(--text-muted, #71717a); padding: 4px; display: none;" aria-label="Limpiar búsqueda">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
        <button type="button" data-action="toggle-search" style="background: none; border: none; cursor: pointer; color: var(--text-primary, #09090b); padding: 4px; font-size: 0.75rem; font-weight: 700; font-family: var(--font-heading, sans-serif);" aria-label="Cerrar búsqueda">
          CERRAR
        </button>
      </div>
    </div>
  </div>

  <!-- Contenedor Principal de la Tienda -->
  <div class="main-page-wrapper">
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
            {{ $footerConfig['brand_summary'] ?? 'Plataforma contemporánea de moda íntima, streetwear y autocuidado. Envíos nacionales directos vía paquetería express con empaque 100% neutro y precinto higiénico.' }}
          </p>
          <div style="font-size: 0.75rem; color: #71717a;">
            Escribenos por WhatsApp: <strong>{{ $footerConfig['whatsapp_contact'] ?? '+52 55 7510 2015' }}</strong>
            @if(!empty($footerConfig['support_email']))
              <div style="margin-top: 4px;">Email: <strong>{{ $footerConfig['support_email'] }}</strong></div>
            @endif
          </div>
        </div>

        <div>
          <strong style="display: block; color: #ffffff; margin-bottom: 12px; font-family: var(--font-heading); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.05em;">Pagos y envios</strong>
          <ul class="footer-link-list">
            <li><span>{{ $footerConfig['shipping_badge_text'] ?? 'Paqueteria nacional express ($149 / Gratis +$999)' }}</span></li>
            <li><span>{{ $footerConfig['packaging_badge_text'] ?? 'Entrega personal discreta y segura' }}</span></li>
            <li><span>{{ $footerConfig['payments_badge_text'] ?? 'Pagos 100% seguros con Tarjeta & OXXO Pay' }}</span></li>
          </ul>
        </div>

        <div>
          <strong style="display: block; color: #ffffff; margin-bottom: 12px; font-family: var(--font-heading); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.05em;">Categorías Oficiales</strong>
          <ul class="footer-link-list">
            @forelse($footerCategories as $fCat)
              <li>
                <a href="{{ route('store.home', ['categoria' => $fCat['slug'], 'universo' => $activeUniverse ?? 'marte']) }}" 
                   data-action="explore-filter-catalog" 
                   data-category="{{ $fCat['slug'] }}" 
                   data-universe="{{ $activeUniverse ?? 'marte' }}" 
                   style="color: inherit; text-decoration: none;">
                  {{ $fCat['name'] }}
                </a>
              </li>
            @empty
              <li><a href="#" data-action="switch-dock-tab" data-dock-tab="catalog">Catálogo Completo</a></li>
            @endforelse
          </ul>
        </div>

        <div>
          <strong style="display: block; color: #ffffff; margin-bottom: 12px; font-family: var(--font-heading); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.05em;">Información &amp; Legal</strong>
          <ul class="footer-link-list">
            @forelse($footerLegalPages as $fPage)
              <li>
                <a href="{{ url('/pagina/' . $fPage['slug']) }}" style="color: inherit; text-decoration: none;">
                  {{ $fPage['title'] }}
                </a>
              </li>
            @empty
              <li><a href="/privacidad" style="color: inherit; text-decoration: none;">Aviso de Privacidad</a></li>
              <li><a href="/cookies" style="color: inherit; text-decoration: none;">Política de Cookies</a></li>
              <li><a href="#" data-action="open-size-guide" style="color: inherit; text-decoration: none;">Guía de Tallas Anatómicas</a></li>
            @endforelse
          </ul>

          <!-- Acceso al Panel RevOps -->
          @if(filter_var($footerConfig['show_revops_link'] ?? true, FILTER_VALIDATE_BOOLEAN))
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
          @endif
        </div>
      </div>

      <div style="border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: gap-2; font-size: 0.7rem; color: #71717a;">
        <span>{{ $footerConfig['copyright_text'] ?? '(C) 2026 MULTIVERSO STORE. Todos los derechos reservados.' }} <a href="/privacidad" style="color: inherit; text-decoration: underline; margin-left: 8px;">Aviso de Privacidad</a> <a href="/cookies" style="color: inherit; text-decoration: underline; margin-left: 8px;">Política de Cookies</a></span>
      </div>
    </div>
  </footer>
  </div>

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
  <aside id="filterDrawer" class="bottom-sheet-drawer drawer-left zara-multiverse-filter-bar" data-mobile-nav-drawer aria-label="Filtros del catálogo">
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
      @php
        $cmsFiltersList = $catalogFilters ?? \App\Models\CmsCatalogFilter::orderBy('sort_order')->get();
        $defaultFilters = [
          'universe' => ['label' => 'Universos del Catálogo', 'sort_order' => 1],
          'category' => ['label' => 'Categorías & Familias', 'sort_order' => 2],
          'subcategory' => ['label' => 'Subcategorías Específicas', 'sort_order' => 3],
          'brand' => ['label' => 'Colección / Marca', 'sort_order' => 4],
          'supplier' => ['label' => 'Proveedores & Distribuidores', 'sort_order' => 5],
          'size' => ['label' => 'Talla Anatómica', 'sort_order' => 6],
          'color' => ['label' => 'Color & Tornasoles', 'sort_order' => 7],
          'textile_family' => ['label' => 'Familia Textil / Fibras', 'sort_order' => 8],
          'garment_type' => ['label' => 'Silueta / Tipo de Prenda', 'sort_order' => 9],
          'style_tag' => ['label' => 'Estilo & Ocasión', 'sort_order' => 10],
          'price' => ['label' => 'Rango de Precio', 'sort_order' => 11],
          'discount' => ['label' => 'Descuentos & Ofertas', 'sort_order' => 12],
          'volume' => ['label' => 'Volúmenes de Perfumería', 'sort_order' => 13],
          'shade' => ['label' => 'Tonos Cosméticos & Shading', 'sort_order' => 14],
          'availability' => ['label' => 'Disponibilidad (Stock / Bajo Pedido)', 'sort_order' => 15],
        ];

        $existingKeys = $cmsFiltersList->pluck('filter_key')->all();
        foreach ($defaultFilters as $k => $def) {
          if (!in_array($k, $existingKeys)) {
            $cmsFiltersList->push((object)[
              'filter_key' => $k,
              'label' => $def['label'],
              'display_type' => 'checkbox',
              'sort_order' => $def['sort_order'],
              'is_visible' => true,
            ]);
          }
        }
        $cmsFiltersList = $cmsFiltersList->sortBy('sort_order');
        if (!isset($catalogColors)) {
          $dbColors = \Illuminate\Support\Facades\DB::table('apparel_colors')->where('is_active', true)->get();
          $catalogColors = $dbColors->map(function ($c) {
            $isGrad = in_array($c->color_type ?? '', ['gradient', 'iridescent']) || !empty($c->gradient_css);
            return [
              'key' => strtolower($c->name),
              'name' => $c->name,
              'hex' => $c->hex_code ?: '#0f172a',
              'grad' => $isGrad ? $c->gradient_css : null,
              'type' => $c->color_type ?: 'solid',
              'border' => in_array(strtolower($c->name), ['blanco', 'white', 'hueso', 'perla']) || in_array(strtolower($c->hex_code ?? ''), ['#ffffff', '#fff', '#f8fafc', '#fafafa']),
            ];
          })->values()->all();
        }
        if (!isset($catalogBrands)) {
          $catalogBrands = \App\Models\Product::where('visible', true)->whereNotNull('brand')->pluck('brand')->unique()->sort()->values()->all();
        }
        if (!isset($catalogFabrics)) {
          $catalogFabrics = \App\Models\Product::where('visible', true)->whereNotNull('fabric')->pluck('fabric')->unique()->sort()->values()->all();
        }
        if (!isset($catalogSizes)) {
          $catalogSizes = \App\Models\ProductVariant::where('visible', true)->get()->map(fn($v) => data_get($v->attributes, 'size'))->filter()->unique()->values()->all();
          if (empty($catalogSizes)) {
            $catalogSizes = ['CH', 'M', 'G', 'XL', '2XL', '3XL', 'UNI'];
          }
          $sizeOrder = ['CH' => 1, 'S' => 2, 'M' => 3, 'G' => 4, 'L' => 5, 'XL' => 6, 'XG' => 7, '2XL' => 8, 'XXL' => 9, '3XL' => 10, 'UNI' => 99];
          usort($catalogSizes, fn($a, $b) => ($sizeOrder[$a] ?? 50) <=> ($sizeOrder[$b] ?? 50));
        }
        if (!isset($catalogGarmentTypes)) {
          $catalogGarmentTypes = \App\Models\Category::whereNotNull('parent_id')->get()->map(fn($c) => ['key' => $c->slug, 'name' => $c->name])->all();
        }
      @endphp

      @php
        $visibleFilterCount = 0;
      @endphp
      @foreach($cmsFiltersList as $cmsFilter)
        @continue(!$cmsFilter->is_visible)
        @php
          $isFirstVisibleAccordion = ($visibleFilterCount === 0);
          $visibleFilterCount++;
        @endphp

        @if($cmsFilter->filter_key === 'universe')
          <!-- Universos del Catálogo (Checkbox) -->
          <div class="filter-accordion-block {{ $isFirstVisibleAccordion ? '' : 'is-collapsed' }}">
            <button type="button" class="filter-accordion-toggle filter-accordion-header" onclick="this.closest('.filter-accordion-block').classList.toggle('is-collapsed')" aria-label="Alternar {{ $cmsFilter->label }}">
              <span>{{ $cmsFilter->label }}</span>
              <svg class="accordion-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="filter-accordion-content">
              <div class="filter-checkbox-grid">
                @foreach([
                  ['key' => 'marte', 'name' => 'Marte (Masculino)'],
                  ['key' => 'venus', 'name' => 'Venus (Femenino)'],
                  ['key' => 'tiresias', 'name' => 'Tiresias (Afirmativo)'],
                  ['key' => 'terra', 'name' => 'Terra (Ecosistema Global)'],
                ] as $uDim)
                  <label class="filter-checkbox-label">
                    <input type="checkbox" name="filter_universe" value="{{ $uDim['key'] }}" class="filter-facet-checkbox filter-universe-checkbox" data-action="filter-facet" data-facet-type="universe" data-universe="{{ $uDim['key'] }}" data-val="{{ $uDim['key'] }}">
                    <span>{{ $uDim['name'] }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          </div>

        @elseif($cmsFilter->filter_key === 'category')
          <!-- Categorías & Familias en Formato Árbol Jerárquico -->
          <div class="filter-accordion-block {{ $isFirstVisibleAccordion ? '' : 'is-collapsed' }}">
            <button type="button" class="filter-accordion-toggle filter-accordion-header" onclick="this.closest('.filter-accordion-block').classList.toggle('is-collapsed')" aria-label="Alternar {{ $cmsFilter->label }}">
              <span>{{ $cmsFilter->label }}</span>
              <svg class="accordion-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="filter-accordion-content">
              <div class="filter-tree-container">
                @if(!empty($categoriesTree) && $categoriesTree->isNotEmpty())
                  @foreach($categoriesTree as $pCat)
                    <div class="filter-tree-node filter-tree-parent">
                      <label class="filter-checkbox-label">
                        <input type="checkbox" name="filter_category" value="{{ $pCat->slug }}" class="filter-facet-checkbox filter-cat-checkbox" data-action="filter-facet" data-facet-type="category" data-val="{{ $pCat->slug }}">
                        <strong style="font-size: 0.8rem;">{{ $pCat->name }}</strong>
                      </label>
                      @if($pCat->children && $pCat->children->isNotEmpty())
                        <div class="filter-tree-children">
                          @foreach($pCat->children as $cCat)
                            <div class="filter-tree-node filter-tree-child">
                              <label class="filter-checkbox-label">
                                <input type="checkbox" name="filter_category" value="{{ $cCat->slug }}" class="filter-facet-checkbox filter-cat-checkbox" data-action="filter-facet" data-facet-type="category" data-val="{{ $cCat->slug }}">
                                <span>{{ $cCat->name }}</span>
                              </label>
                            </div>
                          @endforeach
                        </div>
                      @endif
                    </div>
                  @endforeach
                @elseif(!empty($categories) && (is_object($categories) && method_exists($categories, 'isNotEmpty') ? $categories->isNotEmpty() : !empty($categories)))
                  @foreach($categories as $cat)
                    @php
                      $cSlug = is_object($cat) ? ($cat->slug ?? '') : (is_array($cat) ? ($cat['slug'] ?? '') : (string)$cat);
                      $cName = is_object($cat) ? ($cat->name ?? $cSlug) : (is_array($cat) ? ($cat['name'] ?? $cSlug) : (string)$cat);
                    @endphp
                    @if(!empty($cSlug))
                      <label class="filter-checkbox-label">
                        <input type="checkbox" name="filter_category" value="{{ $cSlug }}" class="filter-facet-checkbox filter-cat-checkbox" data-action="filter-facet" data-facet-type="category" data-val="{{ $cSlug }}">
                        <span>{{ $cName }}</span>
                      </label>
                    @endif
                  @endforeach
                @else
                  @foreach(['jocks' => 'Jockstraps', 'boxers' => 'Boxers', 'tangas' => 'Tangas', 'bikinis' => 'Bikinis', 'tanktops' => 'Tank Tops', 'swimwear' => 'Trajes de Baño'] as $cSlug => $cName)
                    <label class="filter-checkbox-label">
                      <input type="checkbox" name="filter_category" value="{{ $cSlug }}" class="filter-facet-checkbox filter-cat-checkbox" data-action="filter-facet" data-facet-type="category" data-val="{{ $cSlug }}">
                      <span>{{ $cName }}</span>
                    </label>
                  @endforeach
                @endif
              </div>
            </div>
          </div>

        @elseif($cmsFilter->filter_key === 'subcategory')
          <!-- Subcategorías Específicas Registradas -->
          <div class="filter-accordion-block {{ $isFirstVisibleAccordion ? '' : 'is-collapsed' }}">
            <button type="button" class="filter-accordion-toggle filter-accordion-header" onclick="this.closest('.filter-accordion-block').classList.toggle('is-collapsed')" aria-label="Alternar {{ $cmsFilter->label }}">
              <span>{{ $cmsFilter->label }}</span>
              <svg class="accordion-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="filter-accordion-content">
              <div class="filter-checkbox-grid">
                @php
                  $subcats = !empty($categories) ? $categories->whereNotNull('parent_id') : collect();
                @endphp
                @if($subcats->isNotEmpty())
                  @foreach($subcats as $subCat)
                    <label class="filter-checkbox-label">
                      <input type="checkbox" name="filter_category" value="{{ $subCat->slug }}" class="filter-facet-checkbox filter-cat-checkbox" data-action="filter-facet" data-facet-type="category" data-val="{{ $subCat->slug }}">
                      <span>{{ $subCat->name }}</span>
                    </label>
                  @endforeach
                @else
                  @foreach(['boxer' => 'Boxer', 'bikini' => 'Bikini', 'tanga' => 'Tanga', 'jockstrap' => 'Jockstrap', 'tank-top' => 'Tank Top', 'swimwear' => 'Swimwear'] as $subSlug => $subName)
                    <label class="filter-checkbox-label">
                      <input type="checkbox" name="filter_category" value="{{ $subSlug }}" class="filter-facet-checkbox filter-cat-checkbox" data-action="filter-facet" data-facet-type="category" data-val="{{ $subSlug }}">
                      <span>{{ $subName }}</span>
                    </label>
                  @endforeach
                @endif
              </div>
            </div>
          </div>

        @elseif($cmsFilter->filter_key === 'brand')
          <!-- Marcas de Diseñador & Fabricantes Registradas -->
          <div class="filter-accordion-block {{ $isFirstVisibleAccordion ? '' : 'is-collapsed' }}">
            <button type="button" class="filter-accordion-toggle filter-accordion-header" onclick="this.closest('.filter-accordion-block').classList.toggle('is-collapsed')" aria-label="Alternar {{ $cmsFilter->label }}">
              <span>{{ $cmsFilter->label }}</span>
              <svg class="accordion-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="filter-accordion-content">
              <div class="filter-checkbox-grid">
                @foreach((!empty($catalogBrands) ? $catalogBrands : ['Torzo Sexywear', 'Dajo Underwear', 'Mazho']) as $bName)
                  <label class="filter-checkbox-label">
                    <input type="checkbox" name="filter_brand" value="{{ $bName }}" class="filter-facet-checkbox filter-brand-checkbox" data-action="filter-facet" data-facet-type="brand" data-val="{{ $bName }}">
                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $bName }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          </div>

        @elseif($cmsFilter->filter_key === 'supplier')
          <!-- Proveedores Registrados -->
          <div class="filter-accordion-block {{ $isFirstVisibleAccordion ? '' : 'is-collapsed' }}">
            <button type="button" class="filter-accordion-toggle filter-accordion-header" onclick="this.closest('.filter-accordion-block').classList.toggle('is-collapsed')" aria-label="Alternar {{ $cmsFilter->label }}">
              <span>{{ $cmsFilter->label }}</span>
              <svg class="accordion-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="filter-accordion-content">
              <div class="filter-checkbox-grid">
                @foreach((!empty($suppliers) ? $suppliers : ['Torzo B2B', 'ModaIntima Textil MX', 'Silk & Cotton Iberia SL']) as $sup)
                  <label class="filter-checkbox-label">
                    <input type="checkbox" name="filter_supplier" value="{{ $sup }}" class="filter-facet-checkbox filter-supplier-checkbox" data-action="filter-facet" data-facet-type="supplier" data-val="{{ $sup }}">
                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $sup }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          </div>

        @elseif($cmsFilter->filter_key === 'size')
          <!-- Curva de Tallas Anatómicas Registradas -->
          <div class="filter-accordion-block {{ $isFirstVisibleAccordion ? '' : 'is-collapsed' }}">
            <button type="button" class="filter-accordion-toggle filter-accordion-header" onclick="this.closest('.filter-accordion-block').classList.toggle('is-collapsed')" aria-label="Alternar {{ $cmsFilter->label }}">
              <span>{{ $cmsFilter->label }}</span>
              <svg class="accordion-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="filter-accordion-content">
              <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 4px;">
                @php
                  $medidas = ['CH' => '28-30', 'S' => '28-30', 'M' => '31-32', 'G' => '33-34', 'L' => '33-34', 'XL' => '35-36', 'XG' => '35-36', '2XL' => '38-40', '3XL' => '42-44', 'UNI' => 'Unitalla'];
                @endphp
                @foreach((!empty($catalogSizes) ? $catalogSizes : ['CH', 'M', 'G', 'XL', '2XL', '3XL', 'UNI']) as $talla)
                  <label class="filter-checkbox-label" style="border: 1px solid var(--border-subtle, #e4e4e7); padding: 5px 8px; border-radius: 6px;">
                    <input type="checkbox" name="filter_size" value="{{ $talla }}" class="filter-facet-checkbox filter-size-checkbox" data-action="filter-facet" data-facet-type="size" data-val="{{ $talla }}">
                    <span style="display: flex; justify-content: space-between; width: 100%;">
                      <strong>{{ $talla }}</strong>
                      <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $medidas[$talla] ?? '' }}</span>
                    </span>
                  </label>
                @endforeach
              </div>
            </div>
          </div>

        @elseif($cmsFilter->filter_key === 'color')
          <!-- Colores & Tornasoles Iridiscentes Registrados en Catálogo -->
          <div class="filter-accordion-block {{ $isFirstVisibleAccordion ? '' : 'is-collapsed' }}">
            <button type="button" class="filter-accordion-toggle filter-accordion-header" onclick="this.closest('.filter-accordion-block').classList.toggle('is-collapsed')" aria-label="Alternar {{ $cmsFilter->label }}">
              <span>{{ $cmsFilter->label }}</span>
              <svg class="accordion-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="filter-accordion-content">
              <div class="filter-checkbox-grid">
                @foreach((!empty($catalogColors) ? $catalogColors : [
                  ['key' => 'negro', 'name' => 'Negro', 'hex' => '#09090b', 'grad' => null, 'border' => false],
                  ['key' => 'blanco', 'name' => 'Blanco', 'hex' => '#ffffff', 'grad' => null, 'border' => true],
                  ['key' => 'rojo', 'name' => 'Rojo', 'hex' => '#ef4444', 'grad' => null, 'border' => false],
                  ['key' => 'azul', 'name' => 'Azul Marino', 'hex' => '#1e3a8a', 'grad' => null, 'border' => false],
                ]) as $cItem)
                  <label class="filter-checkbox-label">
                    <input type="checkbox" name="filter_color" value="{{ $cItem['key'] }}" class="filter-facet-checkbox filter-color-checkbox" data-action="filter-facet" data-facet-type="color" data-val="{{ $cItem['key'] }}">
                    <span style="width: 14px; height: 14px; border-radius: 50%; background: {{ $cItem['grad'] ?? $cItem['hex'] }}; border: 1px solid {{ !empty($cItem['border']) ? '#cbd5e1' : 'rgba(0,0,0,0.18)' }}; display: inline-block; flex-shrink: 0;"></span>
                    <span>{{ $cItem['name'] }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          </div>

        @elseif($cmsFilter->filter_key === 'textile_family')
          <!-- Telas & Fibras Textiles Registradas -->
          <div class="filter-accordion-block {{ $isFirstVisibleAccordion ? '' : 'is-collapsed' }}">
            <button type="button" class="filter-accordion-toggle filter-accordion-header" onclick="this.closest('.filter-accordion-block').classList.toggle('is-collapsed')" aria-label="Alternar {{ $cmsFilter->label }}">
              <span>{{ $cmsFilter->label }}</span>
              <svg class="accordion-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="filter-accordion-content">
              <div class="filter-checkbox-grid">
                @foreach((!empty($catalogFabrics) ? $catalogFabrics : ['Licra', 'Poliester 95%, Elastano 5%', 'Modal Pima']) as $fItem)
                  <label class="filter-checkbox-label">
                    <input type="checkbox" name="filter_fabric" value="{{ $fItem }}" class="filter-facet-checkbox filter-fabric-checkbox" data-action="filter-facet" data-facet-type="fabric" data-val="{{ $fItem }}">
                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $fItem }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          </div>

        @elseif($cmsFilter->filter_key === 'garment_type')
          <!-- Siluetas & Tipos de Prenda -->
          <div class="filter-accordion-block {{ $isFirstVisibleAccordion ? '' : 'is-collapsed' }}">
            <button type="button" class="filter-accordion-toggle filter-accordion-header" onclick="this.closest('.filter-accordion-block').classList.toggle('is-collapsed')" aria-label="Alternar {{ $cmsFilter->label }}">
              <span>{{ $cmsFilter->label }}</span>
              <svg class="accordion-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="filter-accordion-content">
              <div class="filter-checkbox-grid">
                @foreach(($catalogGarmentTypes ?? [
                  ['key' => 'boxer', 'name' => 'Boxer Trunk'],
                  ['key' => 'jockstrap', 'name' => 'Jockstrap'],
                  ['key' => 'bikini', 'name' => 'Bikini'],
                  ['key' => 'tanga', 'name' => 'Tanga'],
                  ['key' => 'tank-top', 'name' => 'Tank Top'],
                  ['key' => 'swimwear', 'name' => 'Swimwear'],
                ]) as $gtItem)
                  @php
                    $gtKey = is_array($gtItem) ? ($gtItem['key'] ?? $gtItem['name']) : (string)$gtItem;
                    $gtName = is_array($gtItem) ? ($gtItem['name'] ?? $gtItem['key']) : (string)$gtItem;
                  @endphp
                  <label class="filter-checkbox-label">
                    <input type="checkbox" name="filter_garment_type" value="{{ $gtKey }}" class="filter-facet-checkbox filter-garment-checkbox" data-action="filter-facet" data-facet-type="garment_type" data-val="{{ $gtKey }}">
                    <span>{{ $gtName }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          </div>

        @elseif($cmsFilter->filter_key === 'style_tag')
          <!-- Estilo & Ocasión -->
          <div class="filter-accordion-block {{ $isFirstVisibleAccordion ? '' : 'is-collapsed' }}">
            <button type="button" class="filter-accordion-toggle filter-accordion-header" onclick="this.closest('.filter-accordion-block').classList.toggle('is-collapsed')" aria-label="Alternar {{ $cmsFilter->label }}">
              <span>{{ $cmsFilter->label }}</span>
              <svg class="accordion-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="filter-accordion-content">
              <div class="filter-checkbox-grid">
                @foreach([
                  'sensual' => 'Sensual & Provocativo',
                  'gym' => 'Deportivo / Gym',
                  'afirmativo' => 'Afirmativo / Compresión',
                  'minimalista' => 'Minimalista & Diario',
                  'edicion' => 'Edición Limitada'
                ] as $stKey => $stLabel)
                  <label class="filter-checkbox-label">
                    <input type="checkbox" name="filter_style_tag" value="{{ $stKey }}" class="filter-facet-checkbox filter-tag-checkbox" data-action="filter-facet" data-facet-type="style_tag" data-val="{{ $stKey }}">
                    <span>{{ $stLabel }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          </div>

        @elseif($cmsFilter->filter_key === 'price')
          <!-- Rango de Precio (Ajustador en una línea) -->
          <div class="filter-accordion-block {{ $isFirstVisibleAccordion ? '' : 'is-collapsed' }}">
            <button type="button" class="filter-accordion-toggle filter-accordion-header" onclick="this.closest('.filter-accordion-block').classList.toggle('is-collapsed')" aria-label="Alternar {{ $cmsFilter->label }}">
              <span>{{ $cmsFilter->label }}</span>
              <svg class="accordion-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="filter-accordion-content">
              @php
                $maxPrice = $maxCatalogPrice ?? 2500;
              @endphp
              <div class="filter-price-slider-line" style="display: flex; align-items: center; gap: 8px; padding: 6px 0;">
                <span style="font-size: 0.7rem; font-weight: 700; color: var(--text-muted);">$0</span>
                <input type="range" 
                       id="storefrontPriceSlider" 
                       min="0" 
                       max="{{ $maxPrice }}" 
                       step="25" 
                       value="{{ $maxPrice }}" 
                       class="filter-price-slider-input" 
                       data-action="filter-price-slider"
                       data-max-limit="{{ $maxPrice }}"
                       style="flex: 1; height: 6px; accent-color: var(--brand-accent, #6366f1); cursor: pointer; border-radius: 9999px;">
                <span id="priceSliderValueDisplay" style="font-size: 0.74rem; font-weight: 800; color: var(--brand-accent, #6366f1); white-space: nowrap; font-family: var(--font-heading); min-width: 65px; text-align: right;">
                  ${{ number_format($maxPrice, 0) }}
                </span>
              </div>
            </div>
          </div>

        @elseif($cmsFilter->filter_key === 'discount')
          <!-- Descuentos & Ofertas (Checkbox) -->
          <div class="filter-accordion-block {{ $isFirstVisibleAccordion ? '' : 'is-collapsed' }}">
            <button type="button" class="filter-accordion-toggle filter-accordion-header" onclick="this.closest('.filter-accordion-block').classList.toggle('is-collapsed')" aria-label="Alternar {{ $cmsFilter->label }}">
              <span>{{ $cmsFilter->label }}</span>
              <svg class="accordion-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="filter-accordion-content">
              <div class="filter-checkbox-grid">
                @foreach([
                  'only_sale' => 'Solo con Descuento / Liquidación',
                  'min_15' => 'Descuento mín. 15%',
                  'min_25' => 'Descuento mín. 25%'
                ] as $dsKey => $dsLabel)
                  <label class="filter-checkbox-label">
                    <input type="checkbox" name="filter_discount" value="{{ $dsKey }}" class="filter-facet-checkbox filter-discount-checkbox" data-action="filter-facet" data-facet-type="discount" data-val="{{ $dsKey }}">
                    <span>{{ $dsLabel }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          </div>

        @elseif($cmsFilter->filter_key === 'volume')
          <!-- Volúmenes de Perfumería -->
          <div class="filter-accordion-block {{ $isFirstVisibleAccordion ? '' : 'is-collapsed' }}">
            <button type="button" class="filter-accordion-toggle filter-accordion-header" onclick="this.closest('.filter-accordion-block').classList.toggle('is-collapsed')" aria-label="Alternar {{ $cmsFilter->label }}">
              <span>{{ $cmsFilter->label }}</span>
              <svg class="accordion-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="filter-accordion-content">
              <div class="filter-checkbox-grid">
                @foreach(['30ml' => '30ml Travel', '50ml' => '50ml EdP', '100ml' => '100ml Standard', '200ml' => '200ml Refill'] as $vKey => $vLabel)
                  <label class="filter-checkbox-label">
                    <input type="checkbox" name="filter_volume" value="{{ $vKey }}" class="filter-facet-checkbox filter-volume-checkbox" data-action="filter-facet" data-facet-type="volume" data-val="{{ $vKey }}">
                    <span>{{ $vLabel }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          </div>

        @elseif($cmsFilter->filter_key === 'shade')
          <!-- Tonos Cosméticos & Shading -->
          <div class="filter-accordion-block {{ $isFirstVisibleAccordion ? '' : 'is-collapsed' }}">
            <button type="button" class="filter-accordion-toggle filter-accordion-header" onclick="this.closest('.filter-accordion-block').classList.toggle('is-collapsed')" aria-label="Alternar {{ $cmsFilter->label }}">
              <span>{{ $cmsFilter->label }}</span>
              <svg class="accordion-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="filter-accordion-content">
              <div class="filter-checkbox-grid">
                @foreach(['claro' => 'Tono 01 Claro', 'medio' => 'Tono 02 Medio', 'bronce' => 'Tono 03 Bronce', 'profundo' => 'Tono 04 Profundo'] as $tKey => $tLabel)
                  <label class="filter-checkbox-label">
                    <input type="checkbox" name="filter_shade" value="{{ $tKey }}" class="filter-facet-checkbox filter-shade-checkbox" data-action="filter-facet" data-facet-type="shade" data-val="{{ $tKey }}">
                    <span>{{ $tLabel }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          </div>

        @elseif($cmsFilter->filter_key === 'availability' || $cmsFilter->filter_key === 'stock_type')
          <!-- Disponibilidad de Stock & Modalidad de Entrega (En Stock / Bajo Pedido) -->
          <div class="filter-accordion-block {{ $isFirstVisibleAccordion ? '' : 'is-collapsed' }}">
            <button type="button" class="filter-accordion-toggle filter-accordion-header" onclick="this.closest('.filter-accordion-block').classList.toggle('is-collapsed')" aria-label="Alternar {{ $cmsFilter->label }}">
              <span>{{ $cmsFilter->label }}</span>
              <svg class="accordion-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="filter-accordion-content">
              <div class="filter-checkbox-grid">
                @foreach([
                  'in_stock' => 'En Stock',
                  'on_demand' => 'Bajo Pedido',
                ] as $avKey => $avLabel)
                  <label class="filter-checkbox-label">
                    <input type="checkbox" name="filter_availability" value="{{ $avKey }}" class="filter-facet-checkbox filter-availability-checkbox" data-action="filter-facet" data-facet-type="availability" data-val="{{ $avKey }}">
                    <span>{{ $avLabel }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          </div>
        @endif
      @endforeach
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
      <!-- Regla 8: El formulario de cupón reside únicamente en el Paso 1 de Checkout -->

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
      <div style="display: flex; align-items: center; gap: 8px;">
        <button type="button" class="quick-theme-toggle-btn" data-action="toggle-app-theme-quick" title="Alternar Modo Oscuro / Claro" aria-label="Cambiar tema">
          <svg class="theme-icon-sun" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
          <svg class="theme-icon-moon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </button>
        <button type="button" class="sheet-close-btn" data-action="close-drawer" aria-label="Cerrar cuenta">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
    </div>

    <div class="sheet-content-scroll" id="accountSheetScroll">
      <!-- Tarjeta de Identidad y Sesión del Comprador -->
      <div id="accountAuthCard" class="account-auth-card">
        <!-- Renderizado dinámico desde store-pwa.js -->
      </div>

      <!-- Barra de Pestañas Táctiles del Portal (5 Pestañas Oficiales en Grid sin Scroll) -->
      <div class="account-tabs-nav" id="accountTabsNav">
        <button type="button" class="account-tab-btn active" data-action="switch-account-tab" data-tab="orders">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
            <line x1="12" y1="22.08" x2="12" y2="12"></line>
          </svg>
          <span class="account-tab-text">Pedidos</span>
          <span id="accountOrdersBadge" class="account-tab-badge">0</span>
        </button>
        <button type="button" class="account-tab-btn" data-action="switch-account-tab" data-tab="account">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          <span class="account-tab-text">Cuenta</span>
        </button>
        <button type="button" class="account-tab-btn" data-action="switch-account-tab" data-tab="discounts">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
            <line x1="2" y1="10" x2="22" y2="10"></line>
            <path d="M7 15h.01M17 15h.01"></path>
          </svg>
          <span class="account-tab-text">Descuentos</span>
        </button>
        <button type="button" class="account-tab-btn" data-action="switch-account-tab" data-tab="subscription">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
          </svg>
          <span class="account-tab-text">Club VIP</span>
        </button>
        <button type="button" class="account-tab-btn" data-action="switch-account-tab" data-tab="settings">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
          </svg>
          <span class="account-tab-text">Ajustes</span>
        </button>
      </div>

      <!-- Paneles de Contenido por Pestaña -->

      <!-- Pestaña 1: Mis Pedidos & Rastreo + Wishlist -->
      <div id="accountTabOrders" class="account-tab-panel active">
        <!-- Vista de Detalle de Pedido (oculta inicialmente, se abre al pulsar un pedido) -->
        <div id="orderDetailViewContainer" style="display: none; margin-bottom: 20px;">
          <!-- Renderizado dinámico desde store-pwa.js -->
        </div>

        <div id="orderListViewContainer">
          <div id="accountOrdersList">
            <!-- Renderizado dinámico desde store-pwa.js -->
          </div>

          <!-- Sección Integrada de Wishlist en Mis Pedidos -->
          <div class="account-wishlist-integrated" style="margin-top: 24px; padding-top: 20px; border-top: 1px dashed var(--border-medium);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
              <div style="display: flex; align-items: center; gap: 8px;">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" stroke="none" style="color: #e11d48;"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                <h4 class="account-subheading" style="margin: 0;">Mis Deseos &amp; Favoritos</h4>
              </div>
              <span id="accountWishlistCountBadge" style="font-size: 0.68rem; font-weight: 700; color: var(--text-muted);">0 prendas</span>
            </div>
            <div id="accountWishlistItemsList" class="account-wishlist-grid">
              <!-- Renderizado dinámico desde store-pwa.js -->
            </div>
          </div>
        </div>
      </div>

      <!-- Pestaña 2: Cuenta (Datos Personales, Google, Direcciones Casa/Trabajo, Perfil Anatómico) -->
      <div id="accountTabAccount" class="account-tab-panel" style="display: none;">
        <!-- Sección 1: Datos Personales & Google Link -->
        <div class="account-section-card" style="margin-bottom: 18px;">
          <div class="account-section-header" style="margin-bottom: 12px;">
            <h4 class="account-subheading">Datos del Perfil</h4>
            <p class="account-subheading-desc">Administra tu información de contacto y métodos de acceso.</p>
          </div>

          <div class="acc-form-group">
            <label class="checkout-label">Nombre Completo</label>
            <input type="text" id="accProfileName" class="checkout-input" placeholder="Tu nombre">
          </div>
          <div class="checkout-form-row">
            <div class="checkout-form-group" style="flex: 1;">
              <label class="checkout-label">Correo Electrónico</label>
              <input type="email" id="accProfileEmail" class="checkout-input" placeholder="correo@ejemplo.com">
            </div>
            <div class="checkout-form-group" style="flex: 1;">
              <label class="checkout-label">Teléfono Móvil</label>
              <input type="tel" id="accProfilePhone" class="checkout-input font-mono" placeholder="55 1234 5678">
            </div>
          </div>

          <!-- Vinculación con Cuenta de Google -->
          <div id="accGoogleLinkCard" style="background: var(--bg-secondary); border: 1px solid var(--border-medium); border-radius: 8px; padding: 12px; margin: 12px 0;">
            <!-- Renderizado dinámico desde store-pwa.js -->
          </div>

          <!-- Preferencia de Empaque Discreto 100% Anónimo (Recomendación 1) -->
          <div id="accDiscretePackagingContainer" style="margin: 12px 0;">
            <!-- Renderizado dinámico desde store-pwa.js -->
          </div>

          <button type="button" class="btn-account-primary" data-action="save-profile-info" style="width: 100%; margin-top: 6px;">
            <span>GUARDAR INFORMACIÓN</span>
          </button>
        </div>

        <!-- Libreta de Direcciones (Casa / Trabajo) -->
        <div class="account-section-card" style="margin-bottom: 18px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div>
              <h4 class="account-subheading">Libreta de Direcciones</h4>
              <p class="account-subheading-desc">Guarda direcciones para casa y trabajo con prellenado rápido.</p>
            </div>
            <button type="button" class="btn-account-action" data-action="show-add-address-form" style="font-size: 0.72rem; padding: 4px 10px; white-space: nowrap;">
              + Nueva
            </button>
          </div>

          <!-- Lista de Direcciones Guardadas -->
          <div id="accountAddressList">
            <!-- Renderizado dinámico desde store-pwa.js -->
          </div>

          <!-- Formulario Añadir / Editar Dirección -->
          <div id="accountAddressEditForm" class="address-edit-form" style="display: none; margin-top: 12px; padding: 12px; background: var(--bg-secondary); border: 1px solid var(--border-medium); border-radius: 8px;">
            <div style="font-size: 0.76rem; font-weight: 800; color: var(--text-primary); margin-bottom: 10px;" id="accAddressFormTitle">
              Añadir Dirección de Entrega
            </div>
            <input type="hidden" id="accAddressEditId" value="">

            <!-- Selector de Tipo de Dirección: Casa o Trabajo -->
            <div class="checkout-form-group">
              <label class="checkout-label">Tipo de Ubicación *</label>
              <div style="display: flex; gap: 8px;">
                <button type="button" class="address-type-pill active" id="addrTypeCasaBtn" data-action="select-address-type" data-type="casa">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                  <span>Casa</span>
                </button>
                <button type="button" class="address-type-pill" id="addrTypeTrabajoBtn" data-action="select-address-type" data-type="trabajo">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                  <span>Trabajo</span>
                </button>
              </div>
            </div>

            <div class="checkout-form-group">
              <label for="accShippingStreet" class="checkout-label">Calle y Número Exterior / Interior *</label>
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
              <input type="text" id="accShippingRefs" class="checkout-input" placeholder="Ej: Portón café, timbre 301">
            </div>
            <div style="display: flex; gap: 8px; margin-top: 10px;">
              <button type="button" class="btn-account-secondary" data-action="cancel-edit-address" style="flex: 1;">Cancelar</button>
              <button type="button" class="btn-account-primary" data-action="save-acc-address" style="flex: 1;">Guardar Dirección</button>
            </div>
          </div>
        </div>

        <!-- Sección 2: Perfil Anatómico & Preferencias -->
        <div class="account-section-card">
          <div class="anatomy-header">
            <h4 class="anatomy-title">Perfil Anatómico y Preferencias</h4>
            <p class="anatomy-desc">Ajusta tu medida de cintura para que la tienda preseleccione tu talla recomendada en cada prenda.</p>
          </div>

          <div class="anatomy-slider-card">
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px;">
              <span class="acc-slider-label">Medida de Cintura</span>
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

      <!-- Pestaña 3: Mis Descuentos (Códigos Aliados, Estado Redimido/Disponible, Dinero Ahorrado, Wallet & Referidos) -->
      <div id="accountTabDiscounts" class="account-tab-panel" style="display: none;">
        <div class="discounts-panel-content">
          <!-- Monedero Wallet Cashback (Recomendación 8) -->
          <div id="accWalletCardContainer" style="margin-bottom: 14px;">
            <!-- Renderizado dinámico desde store-pwa.js -->
          </div>

          <!-- Programa de Referidos Invita a un Amigo (Recomendación 7) -->
          <div id="accReferralCardContainer" style="margin-bottom: 14px;">
            <!-- Renderizado dinámico desde store-pwa.js -->
          </div>

          <!-- KPI Banner de Dinero Ahorrado -->
          <div class="discounts-kpi-banner">
            <div class="kpi-banner-item">
              <span class="kpi-banner-label">Total Ahorrado con Aliados</span>
              <strong class="kpi-banner-value" id="kpiTotalDiscountsSaved">MXN $450.00</strong>
            </div>
            <div class="kpi-banner-divider"></div>
            <div class="kpi-banner-item">
              <span class="kpi-banner-label">Cupones Disponibles</span>
              <strong class="kpi-banner-value" id="kpiActiveDiscountsCount">3 Activos</strong>
            </div>
          </div>

          <div class="discounts-header" style="margin: 16px 0 10px;">
            <h4 class="account-subheading">Códigos de Descuento &amp; Aliados</h4>
            <p class="account-subheading-desc">Beneficios exclusivos con marcas y eventos aliados en formato 4:5.</p>
          </div>

          <!-- Filtro Conmutador de Cupones Activos y Redimidos (Requerimiento 5) -->
          <div class="discounts-filter-bar" id="accDiscountsFilterBar">
            <button type="button" class="discount-filter-btn active" data-action="set-discount-filter" data-filter="all">Todos (<span id="countDiscountsAll">5</span>)</button>
            <button type="button" class="discount-filter-btn" data-action="set-discount-filter" data-filter="active">Disponibles (<span id="countDiscountsActive">3</span>)</button>
            <button type="button" class="discount-filter-btn" data-action="set-discount-filter" data-filter="redeemed">Redimidos (<span id="countDiscountsRedeemed">2</span>)</button>
          </div>

          <!-- Grid de Cupones 3x3 en formato 4:5 -->
          <div id="accountDiscountsList" class="discounts-grid-3x3">
            <!-- Renderizado dinámico desde store-pwa.js -->
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

      <!-- Pestaña 5: Configuración & Preferencias (Tema, Web Push, 2FA WhatsApp/Authenticator, Gestión de Cuenta) -->
      <div id="accountTabSettings" class="account-tab-panel" style="display: none;">
        <div class="settings-panel-content" style="padding: 4px 0;">
          <div class="settings-header" style="margin-bottom: 16px;">
            <h4 class="account-subheading">Configuración de la Cuenta</h4>
            <p class="account-subheading-desc">Personaliza la apariencia visual, seguridad 2FA y canales de comunicación directa.</p>
          </div>

          <!-- Selector de Modo Oscuro / Claro / Sistema -->
          <div class="theme-selector-card" style="background: var(--bg-secondary); border: 1px solid var(--border-subtle); border-radius: 12px; padding: 14px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
              <div style="display: flex; align-items: center; gap: 8px;">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--brand-accent, #4f46e5);"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                <div>
                  <strong class="settings-card-title">Apariencia Visual</strong>
                  <span style="font-size: 0.68rem; color: var(--text-muted);">Soporte Dark Mode &amp; Light Mode</span>
                </div>
              </div>
              <span id="currentThemeBadge" class="theme-active-badge" style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; padding: 2px 8px; border-radius: 9999px; background: rgba(79, 70, 229, 0.1); color: var(--brand-accent, #4f46e5); border: 1px solid rgba(79, 70, 229, 0.2);">SISTEMA</span>
            </div>

            <div class="theme-options-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
              <button type="button" class="theme-option-btn" data-action="set-app-theme" data-theme-mode="light" style="display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 10px 6px; border-radius: 8px; border: 1px solid var(--border-medium); background: var(--bg-primary); color: var(--text-primary); cursor: pointer; transition: all 0.2s;">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                <span style="font-size: 0.72rem; font-weight: 700;">Claro</span>
              </button>

              <button type="button" class="theme-option-btn" data-action="set-app-theme" data-theme-mode="dark" style="display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 10px 6px; border-radius: 8px; border: 1px solid var(--border-medium); background: var(--bg-primary); color: var(--text-primary); cursor: pointer; transition: all 0.2s;">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                <span style="font-size: 0.72rem; font-weight: 700;">Oscuro</span>
              </button>

              <button type="button" class="theme-option-btn active" data-action="set-app-theme" data-theme-mode="system" style="display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 10px 6px; border-radius: 8px; border: 1px solid var(--brand-accent, #4f46e5); background: var(--bg-primary); color: var(--brand-accent, #4f46e5); cursor: pointer; transition: all 0.2s;">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <span style="font-size: 0.72rem; font-weight: 700;">Sistema</span>
              </button>
            </div>
            <p style="font-size: 0.68rem; color: var(--text-muted); margin: 8px 0 0; text-align: center;">
              El modo "Sistema" sincroniza automáticamente la tienda con la configuración de apariencia de tu dispositivo.
            </p>
          </div>

          <!-- Centro de Notificaciones Web Push PWA (REGLA 2: RESIDE EN CONFIGURACIÓN) -->
          <div class="push-notifications-card" id="accountPushCenter">
            <!-- Renderizado dinámicamente desde store-pwa.js -->
          </div>

          <!-- Seguridad y Autenticación de Dos Factores (2FA) -->
          <div class="security-2fa-card" style="background: var(--bg-secondary); border: 1px solid var(--border-subtle); border-radius: 12px; padding: 14px; margin-top: 16px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--brand-accent, #4f46e5);"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              <strong class="settings-card-title">Seguridad en Dos Pasos (2FA)</strong>
            </div>
            <p style="font-size: 0.72rem; color: var(--text-secondary); margin: 0 0 12px; line-height: 1.35;">Protege tus compras y datos de cuenta mediante códigos temporales de un solo uso.</p>

            <!-- 2FA Authenticator App -->
            <div class="twofa-option-row" style="display: flex; align-items: center; justify-content: space-between; padding: 10px; background: var(--bg-primary); border: 1px solid var(--border-medium); border-radius: 8px;">
              <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(79, 70, 229, 0.12); color: #4f46e5; display: flex; align-items: center; justify-content: center;">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="1" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="23"/><line x1="20" y1="12" x2="23" y2="12"/><line x1="1" y1="12" x2="4" y2="12"/></svg>
                </div>
                <div>
                  <div class="twofa-title" style="font-size: 0.78rem; font-weight: 700; color: var(--text-primary);">Google Authenticator / Authy</div>
                  <div class="twofa-desc" style="font-size: 0.68rem; color: var(--text-muted);">Clave temporal TOTP de 6 dígitos sin depender de señal celular.</div>
                </div>
              </div>
              <button type="button" class="btn-account-secondary" id="btnToggle2FAApp" data-action="toggle-2fa-app" style="font-size: 0.7rem; padding: 4px 10px;">
                Configurar
              </button>
            </div>
            <div id="accTwoFactorPanel" style="margin-top: 10px;"></div>
          </div>

          <!-- Cambiar Contraseña -->
          <div class="account-management-card" id="accChangePasswordCard" style="background: var(--bg-secondary); border: 1px solid var(--border-subtle); border-radius: 12px; padding: 14px; margin-top: 16px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--brand-accent, #4f46e5);"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
              <strong class="settings-card-title">Cambiar Contraseña</strong>
            </div>
            <div id="accChangePasswordContainer"></div>
          </div>

          <!-- Gestión y Privacidad de la Cuenta -->
          <div class="account-management-card" style="background: var(--bg-secondary); border: 1px solid var(--border-subtle); border-radius: 12px; padding: 14px; margin-top: 16px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="color: #ef4444;"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <strong class="settings-card-title">Gestión de la Cuenta &amp; Privacidad</strong>
            </div>
            <p style="font-size: 0.72rem; color: var(--text-secondary); margin: 0 0 12px; line-height: 1.35;">Controla la vinculación con servicios de terceros y tus derechos ARCO de cancelación de datos.</p>

            <div id="accGoogleUnlinkRow" style="margin-bottom: 10px;">
              <!-- Renderizado dinámicamente: botón de desvincular Google si aplica -->
            </div>

            <button type="button" class="btn-danger-account" data-action="delete-customer-account" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 9px 12px; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; color: #ef4444; font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: all 0.2s;">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
              <span>Eliminar Mi Cuenta y Datos Personales</span>
            </button>
            <div id="accDeleteAccountPanel" style="margin-top: 10px;"></div>
          </div>
        </div>
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

  <!-- K. Bottom Sheet Quick Add para Selección Táctil de Tallas (Apple HIG & Material Design 48x48px) -->
  <aside id="quickAddSheet" class="bottom-sheet-drawer quick-add-sheet" aria-label="Selección rápida de talla" role="dialog" aria-modal="true">
    <div class="sheet-handle-bar"></div>
    <div class="sheet-header">
      <span class="sheet-title">Seleccionar Talla</span>
      <button type="button" class="sheet-close-btn" data-action="close-drawer" aria-label="Cerrar selector">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
    </div>
    <div class="quick-add-content" id="quickAddContent">
      <!-- Inyectado dinámicamente con imagen, título, precio, botones de tallas táctiles 48x48 y CTA -->
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

    <!-- Barra de Urgencia: Reserva de Stock Sincronizada (15 Minutos) -->
    <div class="checkout-timer-bar" id="checkoutTimerBanner">
      <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.72rem; font-weight: 700;">
        <div style="display: flex; align-items: center; gap: 6px;">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#e11d48" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <span class="checkout-timer-label" style="color: #9f1239; text-transform: uppercase; letter-spacing: 0.04em;">Stock Apartado Activo</span>
        </div>
        <span id="checkoutStockTimerRemaining" style="font-family: monospace; font-size: 0.8rem; font-weight: 800; color: #e11d48;">15:00</span>
      </div>
      <div class="checkout-timer-progress-track" style="width: 100%; height: 3px; background: #ffe4e6; border-radius: 2px; overflow: hidden; margin-top: 5px;">
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
          <p class="checkout-section-desc">Entregas seguras y discretas a domicilio mediante Paquetería Courier Nacional Express certificada.</p>
        </div>

        <form id="checkoutShippingForm" onsubmit="return false;" class="checkout-form">
          <div class="checkout-form-group">
            <label for="checkoutCustomerName" class="checkout-label">Nombre Completo *</label>
            <input type="text" id="checkoutCustomerName" class="checkout-input" placeholder="Ej: Carlos Mendoza" required autocomplete="name">
          </div>

          <div class="checkout-form-row">
            <div class="checkout-form-group" style="flex: 1;">
              <label for="checkoutCustomerPhone" class="checkout-label">Teléfono Celular *</label>
              <input type="tel" id="checkoutCustomerPhone" class="checkout-input" placeholder="10 dígitos (ej: 5512345678)" maxlength="18" required autocomplete="tel">
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
              <input type="text" id="checkoutShippingColony" class="checkout-input" placeholder="Ej: Centro" list="checkoutColoniesList" required autocomplete="address-level3">
              <datalist id="checkoutColoniesList"></datalist>
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
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#3b82f6" stroke-width="2"><path d="M16 16v1a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v1"/><rect x="18" y="8" width="5" height="11" rx="1"/><circle cx="5" cy="18" r="2"/><circle cx="12" cy="18" r="2"/><circle cx="20" cy="18" r="2"/></svg>
                <strong class="mixed-strategy-title" style="font-family: var(--font-heading); font-size: 0.78rem; text-transform: uppercase; color: var(--text-primary); letter-spacing: 0.04em;">Estrategia de Despacho // Carrito Mixto</strong>
              </div>
              <p style="font-size: 0.72rem; color: var(--text-secondary); margin: 4px 0 0; line-height: 1.35;">
                Tu orden combina prendas en bodega y prendas bajo pedido. Selecciona la modalidad de entrega:
              </p>
            </div>

            <div class="mixed-strategy-options">
              <label class="mixed-option-card active" for="strategyConsolidated">
                <input type="radio" name="checkoutDispatchStrategy" id="strategyConsolidated" value="consolidated" checked>
                <div class="mixed-option-body">
                  <div style="display: flex; justify-content: space-between; align-items: center; gap: 6px;">
                    <strong class="mixed-option-heading" style="font-size: 0.78rem; color: var(--text-primary);">Despacho Consolidado Ecológico (1 Solo Paquete)</strong>
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
                  <div style="display: flex; justify-content: space-between; align-items: center; gap: 6px;">
                    <strong class="mixed-option-heading" style="font-size: 0.78rem; color: var(--text-primary);">Despacho Dividido / Split (2 Paquetes Separados)</strong>
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

      <!-- PASO 2: MÉTODOS DE PAGO (TARJETAS BANCARIAS & OXXO PAY - REGLA 3 CERO Tarjetas) -->
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

  <!-- J. Bottom Sheet / Modal Tutorial de Instalación PWA (Acceso Directo iOS / iPadOS y Android) -->
  <aside id="pwaInstallTutorialSheet" class="bottom-sheet-drawer pwa-tutorial-sheet" aria-label="Tutorial para instalar acceso directo PWA">
    <div class="sheet-handle-bar"></div>
    <div class="sheet-header">
      <div style="display: flex; align-items: center; gap: 10px;">
        <div class="pwa-sheet-icon-badge">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/><path d="M12 7v6"/><path d="M9.5 10.5L12 13l2.5-2.5"/></svg>
        </div>
        <div>
          <span class="sheet-title" style="display: block; line-height: 1.15;">Acceso Directo App (PWA)</span>
          <span id="pwaDetectedOsBadge" class="pwa-detected-os-pill">Sistema detectado automáticamente</span>
        </div>
      </div>
      <button type="button" class="sheet-close-btn" data-action="close-drawer" aria-label="Cerrar tutorial PWA">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
    </div>

    <div class="sheet-content-scroll">
      <p class="pwa-tutorial-intro">
        Agrega <strong>{{ $currentStorefront['brandName'] ?? 'MARTE' }}</strong> a la pantalla de inicio de tu teléfono o tablet para abrir la tienda a pantalla completa como aplicación nativa, con carga instantánea y seguimiento de pedidos.
      </p>

      <!-- Selector de Sistema Operativo (Auto-seleccionado según detección de dispositivo) -->
      <div class="pwa-os-tabs" role="tablist">
        <button type="button" id="pwaTabBtnIos" class="pwa-os-tab-btn active" data-action="switch-pwa-os-tab" data-os="ios" role="tab">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
          <span>iOS / iPadOS (iPhone &amp; iPad)</span>
        </button>
        <button type="button" id="pwaTabBtnAndroid" class="pwa-os-tab-btn" data-action="switch-pwa-os-tab" data-os="android" role="tab">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="10" y1="18" x2="14" y2="18"/></svg>
          <span>Android (Móvil &amp; Tablet)</span>
        </button>
      </div>

      <!-- Panel 1: Tutorial iOS / iPadOS (Safari / Chrome en iPhone e iPad) -->
      <div id="pwaTutorialPanelIos" class="pwa-tutorial-panel" style="display: block;">
        <div class="pwa-steps-list">
          <div class="pwa-step-card">
            <div class="pwa-step-num">1</div>
            <div class="pwa-step-body">
              <strong class="pwa-step-title">Toca el botón "Compartir" en Safari</strong>
              <p class="pwa-step-desc">
                Ubica el icono de compartir en la barra inferior (iPhone) o superior derecha (iPad) de tu navegador Safari.
              </p>
              <div class="pwa-step-visual-pill">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                <span>Icono Compartir de Safari</span>
              </div>
            </div>
          </div>

          <div class="pwa-step-card">
            <div class="pwa-step-num">2</div>
            <div class="pwa-step-body">
              <strong class="pwa-step-title">Selecciona "Agregar a inicio"</strong>
              <p class="pwa-step-desc">
                Desliza hacia abajo la lista de acciones del menú y toca la opción <strong>"Agregar a inicio"</strong> (<em>Add to Home Screen</em>).
              </p>
              <div class="pwa-step-visual-pill">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="18" height="18" rx="3" ry="3"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                <span>Agregar a inicio</span>
              </div>
            </div>
          </div>

          <div class="pwa-step-card">
            <div class="pwa-step-num">3</div>
            <div class="pwa-step-body">
              <strong class="pwa-step-title">Confirma tocando "Agregar"</strong>
              <p class="pwa-step-desc">
                Pulsa <strong>"Agregar"</strong> en la esquina superior derecha. Verás el icono oficial de la tienda directamente entre tus aplicaciones.
              </p>
              <div class="pwa-step-visual-pill success">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Acceso directo listo en tu inicio</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Panel 2: Tutorial Android (Chrome / Samsung Internet / Edge en Smartphone y Tablet) -->
      <div id="pwaTutorialPanelAndroid" class="pwa-tutorial-panel" style="display: none;">
        <!-- Botón de instalación directa en 1 toque si el navegador expone beforeinstallprompt -->
        <div id="pwaNativeInstallBox" class="pwa-native-install-box" style="display: none;">
          <button type="button" id="btnTriggerNativePwaInstall" class="btn-pwa-native-install" data-action="trigger-native-pwa-install">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>INSTALAR APLICACIÓN EN 1 TOQUE</span>
          </button>
          <span class="pwa-native-hint">Tu navegador permite instalar la App directamente sin pasos manuales.</span>
        </div>

        <div class="pwa-steps-list">
          <div class="pwa-step-card">
            <div class="pwa-step-num">1</div>
            <div class="pwa-step-body">
              <strong class="pwa-step-title">Abre el menú del navegador (3 puntos)</strong>
              <p class="pwa-step-desc">
                Toca el icono de <strong>tres puntos verticales</strong> ubicado en la esquina superior derecha de Chrome o de tu navegador en Android.
              </p>
              <div class="pwa-step-visual-pill">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                <span>Menú de opciones del navegador</span>
              </div>
            </div>
          </div>

          <div class="pwa-step-card">
            <div class="pwa-step-num">2</div>
            <div class="pwa-step-body">
              <strong class="pwa-step-title">Toca "Instalar aplicación" o "Agregar a la pantalla principal"</strong>
              <p class="pwa-step-desc">
                En el menú desplegable, selecciona <strong>"Instalar aplicación"</strong> o <strong>"Agregar a la pantalla principal"</strong>.
              </p>
              <div class="pwa-step-visual-pill">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><path d="M12 8v6"/><path d="M9.5 11.5L12 14l2.5-2.5"/></svg>
                <span>Instalar aplicación / Pantalla principal</span>
              </div>
            </div>
          </div>

          <div class="pwa-step-card">
            <div class="pwa-step-num">3</div>
            <div class="pwa-step-body">
              <strong class="pwa-step-title">Confirma en "Instalar"</strong>
              <p class="pwa-step-desc">
                Acepta la ventana de confirmación tocando <strong>"Instalar"</strong>. El icono de la tienda se añadirá a tu escritorio Android en formato App nativa.
              </p>
              <div class="pwa-step-visual-pill success">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>App instalada en tu pantalla principal</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="sheet-footer-actions">
      <button type="button" class="btn-sheet-apply" data-action="close-drawer" style="width: 100%;">
        ENTENDIDO
      </button>
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
    window.__UNIVERSE_META__ = @json($universeMeta ?? [], JSON_UNESCAPED_UNICODE);
    window.__STORE_CSRF_TOKEN__ = "{{ csrf_token() }}";
    window.__AUTO_OPEN_PRODUCT_ID__ = @json($autoOpenProductId ?? null);
    @php
      $authUser = auth()->user();
      $initialAddresses = [];
      if ($authUser && \Illuminate\Support\Facades\Schema::hasTable('customer_addresses')) {
        $initialAddresses = $authUser->addresses()->orderByDesc('is_default')->orderByDesc('id')->get()->map(fn ($a) => [
          'id' => $a->id,
          'type' => $a->label_type,
          'street' => $a->street,
          'postalCode' => $a->postal_code,
          'colony' => $a->colony,
          'city' => $a->city,
          'state' => $a->state,
          'references' => $a->references ?? '',
          'isDefault' => (bool) $a->is_default,
        ])->values()->all();
      }
      $initialAuthPayload = $authUser ? [
        'id' => $authUser->id,
        'name' => $authUser->name,
        'email' => $authUser->email,
        'phone' => $authUser->phone,
        'role' => $authUser->role,
        'email_verified' => $authUser->hasVerifiedEmail(),
        'two_factor_enabled' => $authUser->hasTwoFactorEnabled(),
        'discrete_packaging' => (bool) ($authUser->discrete_packaging ?? true),
        'discretePackaging' => (bool) ($authUser->discrete_packaging ?? true),
        'addresses' => $initialAddresses,
        'walletBalance' => 0,
      ] : null;
    @endphp
    window.__INITIAL_AUTH_USER__ = @json($initialAuthPayload);
  </script>
  <script src="{{ asset('js/storefront/store-pwa.js') }}?v={{ file_exists(public_path('js/storefront/store-pwa.js')) ? filemtime(public_path('js/storefront/store-pwa.js')) : time() }}"></script>
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



