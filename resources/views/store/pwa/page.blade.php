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

  @if(($isFaqPage ?? false) && !empty($faqJsonLd))
    <!-- Schema.org JSON-LD FAQPage (Answer Engine Optimization - AEO para Perplexity, Gemini, Google SGE, ChatGPT) -->
    <script type="application/ld+json">
    {!! json_encode($faqJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
  @endif
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

  /* Estilos AEO Q&A */
  .aeo-hero-box {
    text-align: center;
    padding: 44px 20px;
    background-color: #09090b;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    border-radius: 20px;
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.35);
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
  }
  .aeo-hero-box.is-fullwidth {
    width: 100vw;
    position: relative;
    left: 50%;
    right: 50%;
    margin-left: -50vw;
    margin-right: -50vw;
    border-radius: 0;
    border-left: none;
    border-right: none;
    padding: 60px 24px;
    box-sizing: border-box;
  }
  .aeo-hero-overlay {
    position: absolute;
    inset: 0;
    z-index: 1;
    pointer-events: none;
  }
  .aeo-hero-inner {
    position: relative;
    z-index: 2;
    max-width: 860px;
    margin: 0 auto;
  }
  .aeo-hero-title,
  .aeo-hero-box h1 {
    color: var(--hero-title-color, #ffffff) !important;
    text-shadow: 0 3px 12px rgba(0, 0, 0, 0.9), 0 1px 3px rgba(0, 0, 0, 0.95) !important;
    font-family: var(--font-heading, 'Outfit', sans-serif);
    font-size: 2.1rem;
    font-weight: 900;
    margin: 0 0 12px;
    text-transform: uppercase;
    letter-spacing: 0.02em;
  }
  .aeo-hero-desc,
  .aeo-hero-box p {
    color: var(--hero-text-color, #f1f5f9) !important;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.85) !important;
  }
  .aeo-kicker-pill {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 9999px;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    background: rgba(99, 102, 241, 0.25);
    border: 1px solid rgba(99, 102, 241, 0.45);
    color: #a5b4fc;
    margin-bottom: 12px;
  }
  .aeo-search-bar {
    position: relative;
    max-width: 640px;
    margin: 0 auto 24px;
  }
  .aeo-search-input {
    width: 100%;
    padding: 14px 18px 14px 44px;
    background: #ffffff;
    border: 1px solid #d4d4d8;
    border-radius: 14px;
    font-size: 0.9rem;
    color: #09090b;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .aeo-search-input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
  }
  .aeo-search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #a1a1aa;
    pointer-events: none;
  }
  .aeo-categories-bar {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 12px;
    margin-bottom: 24px;
    scrollbar-width: none;
  }
  .aeo-category-btn {
    padding: 7px 15px;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    border: 1px solid #e4e4e7;
    background: #ffffff;
    color: #52525b;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
  }
  .aeo-category-btn.active, .aeo-category-btn:hover {
    background: #09090b;
    color: #ffffff;
    border-color: #09090b;
  }
  .aeo-details-item {
    background: #ffffff;
    border: 1px solid #e4e4e7;
    border-radius: 14px;
    margin-bottom: 12px;
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .aeo-details-item[open] {
    border-color: #c7d2fe;
    box-shadow: 0 4px 14px rgba(99, 102, 241, 0.08);
  }
  .aeo-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 700;
    color: #09090b;
    list-style: none;
    user-select: none;
  }
  .aeo-summary::-webkit-details-marker {
    display: none;
  }
  .aeo-toggle-chevron {
    width: 20px;
    height: 20px;
    color: #6366f1;
    transition: transform 0.2s;
    shrink: 0;
  }
  .aeo-details-item[open] .aeo-toggle-chevron {
    transform: rotate(180deg);
  }
  .aeo-answer-body {
    padding: 0 20px 20px;
    font-size: 0.9rem;
    line-height: 1.7;
    color: #3f3f46;
    border-top: 1px solid #f4f4f5;
    padding-top: 16px;
  }
  .aeo-keywords-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.68rem;
    font-family: monospace;
    background: #f4f4f5;
    color: #71717a;
    padding: 3px 8px;
    border-radius: 6px;
    margin-top: 12px;
  }
</style>
@endpush

@section('content')
<div class="cms-page-wrapper" style="{{ ($isAlliesPage ?? false) ? 'max-width: 1100px;' : '' }}">
  
  <nav class="cms-breadcrumb-nav" aria-label="Navegación institucional">
    <a href="{{ route('store.home', ['universo' => $activeUniverse]) }}">INICIO</a>
    <span>/</span>
    <span style="color: var(--text-primary); font-weight: 600;">{{ $page->title }}</span>
  </nav>

  @if($isAlliesPage ?? false)
    {{-- REPOSITORIO AUTOMÁTICO DE CONVENIOS & EVENTOS HUB 7 (SIN CONFIGURACIÓN MANUAL) --}}
    <div class="allies-repository-header" style="text-align: center; margin-bottom: 32px; padding: 24px; background: linear-gradient(135deg, #0f172a, #1e1b4b); border-radius: 20px; color: #ffffff; border: 1px solid rgba(255,255,255,0.12); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);">
      <span style="display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; background: rgba(99, 102, 241, 0.25); border: 1px solid rgba(99, 102, 241, 0.4); color: #a5b4fc; margin-bottom: 12px;">
        REPOSITORIO OFICIAL // CONVENIOS & ALIANZAS
      </span>
      <h1 style="font-family: var(--font-heading, 'Outfit', sans-serif); font-size: 1.85rem; font-weight: 900; margin: 0 0 10px; text-transform: uppercase; letter-spacing: 0.02em;">
        {{ $page->title }}
      </h1>
      <p style="font-size: 0.88rem; color: #cbd5e1; max-width: 680px; margin: 0 auto; line-height: 1.5;">
        Catálogo cronológico de eventos, festivales, spas y servicios con beneficios exclusivos pactados con nuestros aliados. Regístrate para generar tu código único de descuento de un solo uso.
      </p>
    </div>

    @if(isset($allyCampaigns) && count($allyCampaigns) > 0)
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; margin-bottom: 40px;">
        @foreach($allyCampaigns as $camp)
          @php
            $rawImg = $camp->banner_image;
            $imgSrc = null;
            if (!empty($rawImg)) {
              if (str_starts_with($rawImg, 'http') || str_starts_with($rawImg, '/')) {
                $imgSrc = $rawImg;
              } else {
                $imgSrc = asset($rawImg);
              }
            }
            $startDate = $camp->start_date ? \Carbon\Carbon::parse($camp->start_date)->format('d M Y') : null;
            $endDate = $camp->end_date ? \Carbon\Carbon::parse($camp->end_date)->format('d M Y') : null;
          @endphp

          <div class="ally-event-card" style="display: flex; flex-direction: column; background: #ffffff; border-radius: 18px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(0,0,0,0.06); transition: transform 0.2s, box-shadow 0.2s;">
            <!-- Poster del Evento -->
            <div style="position: relative; width: 100%; height: 220px; background: #0f172a; overflow: hidden;">
              @if($imgSrc)
                <img src="{{ $imgSrc }}" alt="{{ $camp->title }}" style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
              @else
                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #1e1b4b, #312e81); color: #818cf8; font-weight: 800; font-size: 1.5rem;">
                  {{ strtoupper(substr($camp->ally->name ?? 'ALIANZA', 0, 3)) }}
                </div>
              @endif

              <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(15,23,42,0.85) 0%, rgba(15,23,42,0.1) 60%);"></div>

              <!-- Badges Superiores -->
              <div style="position: absolute; top: 12px; left: 12px; right: 12px; display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                <span style="background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(6px); color: #ffffff; font-size: 0.65rem; font-weight: 800; padding: 4px 8px; border-radius: 6px; text-transform: uppercase; border: 1px solid rgba(255,255,255,0.2);">
                  {{ $camp->ally->category_label ?? ($camp->ally->category ?? 'ALIADO OFICIAL') }}
                </span>
                <span style="background: rgba(79, 70, 229, 0.9); backdrop-filter: blur(6px); color: #ffffff; font-size: 0.65rem; font-weight: 800; padding: 4px 8px; border-radius: 6px; text-transform: uppercase;">
                  {{ strtoupper($camp->universe ?? 'UNIVERSO') }}
                </span>
              </div>

              <!-- Fechas del Evento -->
              <div style="position: absolute; bottom: 10px; left: 14px; right: 14px; color: #cbd5e1; font-size: 0.72rem; font-weight: 600; display: flex; align-items: center; gap: 5px;">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>
                  @if($startDate && $endDate)
                    {{ $startDate }} &bull; {{ $endDate }}
                  @elseif($startDate)
                    A partir del {{ $startDate }}
                  @else
                    Vigente / Todo el año
                  @endif
                </span>
              </div>
            </div>

            <!-- Contenido del Convenio -->
            <div style="padding: 18px; display: flex; flex-direction: column; flex: 1; justify-content: space-between; gap: 14px;">
              <div>
                <span style="font-size: 0.72rem; font-weight: 700; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px;">
                  {{ $camp->ally->name ?? 'Aliado Estratégico' }}
                </span>
                <h3 style="font-family: var(--font-heading, sans-serif); font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0 0 8px; line-height: 1.3;">
                  {{ $camp->title }}
                </h3>
                
                <!-- Beneficio Pactado en Hub 7 -->
                <div style="padding: 10px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; color: #166534; font-size: 0.8rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                  <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                  <span>{{ $camp->benefit }}</span>
                </div>

                @if(!empty($camp->instructions))
                  <p style="font-size: 0.74rem; color: #64748b; margin: 8px 0 0; line-height: 1.4;">
                    {{ $camp->instructions }}
                  </p>
                @endif
              </div>

              <!-- Botón de Acción -->
              <div>
                <button type="button" 
                        onclick="window.openAllyDiscountModal({{ $camp->id }}, '{{ addslashes($camp->title) }}', '{{ addslashes($camp->benefit) }}', '{{ addslashes($camp->ally->name ?? 'Aliado') }}', '{{ $camp->universe ?? 'marte' }}', '{{ $imgSrc ?? '' }}')" 
                        style="width: 100%; padding: 10px 16px; background: #0f172a; color: #ffffff; border: none; border-radius: 10px; font-size: 0.82rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                  <span>Obtener Código Único</span>
                  <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div style="padding: 48px; text-align: center; background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 16px; margin-bottom: 32px;">
        <p style="font-size: 0.9rem; color: #64748b; margin: 0;">Actualmente no hay campañas o convenios activos registrados. Vuelve a consultar pronto.</p>
      </div>
    @endif

    <!-- Modal Interactivo para Suscripción y Generación de Código Único de Aliado -->
    <div id="allyDiscountModal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0,0,0,0.75); backdrop-filter: blur(6px); align-items: center; justify-content: center; padding: 16px;">
      <div style="position: relative; width: 100%; max-width: 440px; background: #0f172a; border-radius: 20px; overflow: hidden; border: 1px solid rgba(255,255,255,0.15); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.6); color: #ffffff;">
        <!-- Header con Poster o Fondo -->
        <div id="allyModalHeaderBg" style="position: relative; width: 100%; height: 130px; background: linear-gradient(135deg, #1e1b4b, #312e81); background-size: cover; background-position: center;">
          <div style="position: absolute; inset: 0; background: linear-gradient(to top, #0f172a 0%, rgba(15,23,42,0.3) 100%);"></div>
          <button type="button" onclick="window.closeAllyDiscountModal()" style="position: absolute; top: 12px; right: 12px; z-index: 10; background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.3); width: 32px; height: 32px; border-radius: 50%; color: #ffffff; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
          <div style="position: absolute; bottom: 10px; left: 16px; right: 16px;">
            <span id="allyModalAllyName" style="font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: #818cf8; display: block;"></span>
            <h3 id="allyModalTitle" style="font-family: var(--font-heading, sans-serif); font-size: 1.15rem; font-weight: 800; margin: 0; color: #ffffff; line-height: 1.2;"></h3>
          </div>
        </div>

        <div style="padding: 20px;">
          <!-- Beneficio -->
          <div id="allyModalBenefitBox" style="margin-bottom: 14px; padding: 10px 12px; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 10px; color: #86efac; font-size: 0.8rem; font-weight: 700;">
          </div>

          <!-- Formulario -->
          <div id="allyModalFormContainer">
            <form id="allyDiscountForm" onsubmit="window.handleAllyDiscountSubmit(event)" style="display: flex; flex-direction: column; gap: 10px;">
              <input type="hidden" name="ally_campaign_id" id="allyModalCampaignId">
              <input type="hidden" name="popup_action" value="ally_discount">
              <input type="hidden" name="universe" id="allyModalUniverse" value="marte">

              <div>
                <label style="display: block; font-size: 0.72rem; font-weight: 700; color: #cbd5e1; margin-bottom: 4px;">Tu Nombre Completo</label>
                <input type="text" name="name" required placeholder="Nombre y Apellido" style="width: 100%; padding: 9px 12px; border-radius: 8px; font-size: 0.82rem; background: rgba(255,255,255,0.92); border: 1px solid rgba(255,255,255,0.3); color: #0f172a; outline: none; box-sizing: border-box;">
              </div>

              <div>
                <label style="display: block; font-size: 0.72rem; font-weight: 700; color: #cbd5e1; margin-bottom: 4px;">WhatsApp (10 dígitos)</label>
                <div style="display: flex; gap: 6px;">
                  <span style="display: inline-flex; align-items: center; gap: 4px; padding: 0 10px; border-radius: 8px; background: rgba(16, 185, 129, 0.25); border: 1px solid rgba(16, 185, 129, 0.4); color: #6ee7b7; font-size: 0.75rem; font-weight: 800;">
                    +52
                  </span>
                  <input type="tel" name="whatsapp" required pattern="[0-9]{10}" maxlength="10" placeholder="Ej: 5512345678" style="flex: 1; min-width: 0; padding: 9px 12px; border-radius: 8px; font-size: 0.82rem; background: rgba(255,255,255,0.92); border: 1px solid rgba(255,255,255,0.3); color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
              </div>

              <div>
                <label style="display: block; font-size: 0.72rem; font-weight: 700; color: #cbd5e1; margin-bottom: 4px;">Correo Electrónico</label>
                <input type="email" name="email" required placeholder="tu-correo@ejemplo.com" style="width: 100%; padding: 9px 12px; border-radius: 8px; font-size: 0.82rem; background: rgba(255,255,255,0.92); border: 1px solid rgba(255,255,255,0.3); color: #0f172a; outline: none; box-sizing: border-box;">
              </div>

              <button type="submit" id="allyModalSubmitBtn" style="width: 100%; padding: 11px; margin-top: 6px; background: #6366f1; border: none; color: #ffffff; border-radius: 8px; font-size: 0.82rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);">
                Generar mi Cupón de Aliado
              </button>

              <div style="display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 0.68rem; color: #94a3b8; text-align: center; margin-top: 2px;">
                <svg width="11" height="11" fill="none" stroke="#34d399" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Código único de un solo uso &bull; Envío instantáneo a WhatsApp</span>
              </div>
            </form>
          </div>

          <!-- Contenedor de Éxito con Voucher -->
          <div id="allyModalSuccessContainer" style="display: none; flex-direction: column; align-items: center; gap: 12px; text-align: center; padding: 10px 0;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.4); color: #4ade80; display: flex; align-items: center; justify-content: center;">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h4 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #ffffff;">¡Voucher Generado con Éxito!</h4>
            <p id="allyModalSuccessMsg" style="margin: 0; font-size: 0.78rem; color: #cbd5e1;"></p>
            
            <div style="width: 100%; padding: 12px; background: rgba(255,255,255,0.08); border: 1px dashed rgba(255,255,255,0.3); border-radius: 12px; display: flex; flex-direction: column; gap: 4px;">
              <span style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: #94a3b8;">Tu Código Único de Descuento:</span>
              <span id="allyModalVoucherCode" style="font-family: monospace; font-size: 1.25rem; font-weight: 900; letter-spacing: 0.1em; color: #a5f3fc;"></span>
            </div>

            <div style="display: flex; gap: 8px; width: 100%; margin-top: 6px;">
              <button type="button" id="allyModalCopyBtn" style="flex: 1; padding: 9px; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); color: #ffffff; border-radius: 8px; font-size: 0.78rem; font-weight: 700; cursor: pointer;">Copiar</button>
              <button type="button" onclick="window.closeAllyDiscountModal()" style="flex: 1; padding: 9px; background: #059669; border: none; color: #ffffff; border-radius: 8px; font-size: 0.78rem; font-weight: 700; cursor: pointer;">Listo</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script>
      window.openAllyDiscountModal = function(campId, title, benefit, allyName, universe, bgUrl) {
        var m = document.getElementById('allyDiscountModal');
        if (!m) return;
        document.getElementById('allyModalCampaignId').value = campId;
        document.getElementById('allyModalUniverse').value = universe || 'marte';
        document.getElementById('allyModalTitle').textContent = title;
        document.getElementById('allyModalAllyName').textContent = allyName;
        document.getElementById('allyModalBenefitBox').textContent = 'Beneficio: ' + benefit;
        var headerBg = document.getElementById('allyModalHeaderBg');
        if (headerBg) {
          if (bgUrl) {
            headerBg.style.backgroundImage = "url('" + bgUrl + "')";
          } else {
            headerBg.style.backgroundImage = 'none';
          }
        }
        document.getElementById('allyModalFormContainer').style.display = 'block';
        document.getElementById('allyModalSuccessContainer').style.display = 'none';
        m.style.display = 'flex';
      };

      window.closeAllyDiscountModal = function() {
        var m = document.getElementById('allyDiscountModal');
        if (m) m.style.display = 'none';
      };

      window.handleAllyDiscountSubmit = async function(event) {
        event.preventDefault();
        var form = document.getElementById('allyDiscountForm');
        var btn = document.getElementById('allyModalSubmitBtn');
        if (!form) return;
        var formData = new FormData(form);
        var payload = Object.fromEntries(formData.entries());
        if (btn) {
          btn.disabled = true;
          btn.textContent = 'Generando Cupón...';
        }
        try {
          var res = await fetch('/api/v1/marketing/popup/subscribe', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
          });
          var data = await res.json();
          if (!res.ok) {
            alert(data.message || 'Error al procesar el cupón de descuento.');
            if (btn) {
              btn.disabled = false;
              btn.textContent = 'Reintentar';
            }
            return;
          }
          document.getElementById('allyModalFormContainer').style.display = 'none';
          document.getElementById('allyModalSuccessContainer').style.display = 'flex';
          document.getElementById('allyModalVoucherCode').textContent = data.voucher_code;
          document.getElementById('allyModalSuccessMsg').textContent = data.discount_message || 'Tu código único ha sido generado y vinculado a tu cuenta.';
          var copyBtn = document.getElementById('allyModalCopyBtn');
          if (copyBtn) {
            copyBtn.onclick = function() {
              navigator.clipboard.writeText(data.voucher_code);
              copyBtn.textContent = '¡Copiado!';
              setTimeout(function() { copyBtn.textContent = 'Copiar'; }, 2000);
            };
          }
        } catch (e) {
          alert('Error de conexión al generar el descuento.');
          if (btn) {
            btn.disabled = false;
            btn.textContent = 'Reintentar';
          }
        }
      };

      document.addEventListener('click', function(ev) {
        var m = document.getElementById('allyDiscountModal');
        if (m && ev.target === m) {
          window.closeAllyDiscountModal();
        }
      });
    </script>
  @elseif($isFaqPage ?? false)
    {{-- CENTRO Q&A OPTIMIZADO PARA AEO (ANSWER ENGINE OPTIMIZATION) --}}
    @php
      $isHeroFullwidth = $page->is_fullwidth ?? true;
      $overlayGradientCss = $page->getOverlayGradientCss();
      $heroBgImage = !empty($page->image_url) ? asset($page->image_url) : null;
      $heroTitleColor = $page->getTitleColor();
      $heroTextColor = $page->getTextColor();
    @endphp
    <div class="aeo-hero-box {{ $isHeroFullwidth ? 'is-fullwidth' : '' }}" style="{!! $heroBgImage ? "background-image: url('{$heroBgImage}');" : '' !!} --hero-title-color: {{ $heroTitleColor }}; --hero-text-color: {{ $heroTextColor }};">
      <div class="aeo-hero-overlay" style="background: {!! $overlayGradientCss !!};"></div>
      <div class="aeo-hero-inner">
        <span class="aeo-kicker-pill" style="border-color: rgba(255,255,255,0.4); background: rgba(0,0,0,0.45); color: {{ $heroTitleColor }} !important; backdrop-filter: blur(4px);">
          AEO // CENTRO OFICIAL DE RESPUESTAS DIRECTAS &amp; IA SEARCH
        </span>
        <h1 class="aeo-hero-title" style="color: {{ $heroTitleColor }} !important;">
          {{ $page->title ?: 'Q&A Preguntas Frecuentes' }}
        </h1>
        <p class="aeo-hero-desc" style="color: {{ $heroTextColor }} !important;">
          {{ $page->seo_description ?: 'Respuestas factuales estructuradas para compradores y motores de búsqueda generativa con IA (Perplexity, Google SGE, Gemini, ChatGPT). Información transparente y verificada.' }}
        </p>
      </div>
    </div>

    <!-- Barra de Búsqueda Interactiva AEO -->
    <div class="aeo-search-bar">
      <svg class="aeo-search-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      <input type="search" id="aeoSearchInput" class="aeo-search-input" placeholder="Buscar pregunta o palabra clave (ej. envíos, tallas, pagos, pedidos)..." autocomplete="off">
    </div>

    @php
      $faqCategories = $faqs->pluck('category')->unique()->filter()->values();
    @endphp

    @if(count($faqCategories) > 1)
      <!-- Píldoras de Categorías -->
      <div class="aeo-categories-bar" id="aeoCategoriesBar">
        <button type="button" class="aeo-category-btn active" data-category="all">
          Todas las preguntas ({{ $faqs->count() }})
        </button>
        @foreach($faqCategories as $cat)
          <button type="button" class="aeo-category-btn" data-category="{{ $cat }}">
            {{ $cat }} ({{ $faqs->where('category', $cat)->count() }})
          </button>
        @endforeach
      </div>
    @endif

    <!-- Lista de Preguntas Frecuentes con Microformato Schema.org -->
    <div id="aeoFaqList" class="aeo-faq-list" itemscope itemtype="https://schema.org/FAQPage">
      @forelse($faqs as $idx => $faq)
        <details class="aeo-details-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question" data-category="{{ $faq->category }}" data-search="{{ strtolower($faq->question . ' ' . $faq->answer . ' ' . $faq->category . ' ' . ($faq->ai_keywords ?? '')) }}" {{ $loop->first ? 'open' : '' }}>
          <summary class="aeo-summary" itemprop="name">
            <div style="display: flex; align-items: center; gap: 10px; flex: 1; padding-right: 12px;">
              <span style="display: inline-block; width: 6px; height: 6px; border-radius: 9999px; background: #6366f1; flex-shrink: 0;"></span>
              <span>{{ $faq->question }}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <span style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 2px 8px; border-radius: 9999px; background: #e0e7ff; color: #4338ca;">
                {{ $faq->category }}
              </span>
              <svg class="aeo-toggle-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
          </summary>
          <div class="aeo-answer-body" itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer">
            <div itemprop="text">
              {!! nl2br(e($faq->answer)) !!}
            </div>
            @if(!empty($faq->ai_keywords))
              <div class="aeo-keywords-badge">
                <span style="font-weight: 700;">AEO Intenciones:</span> {{ $faq->ai_keywords }}
              </div>
            @endif
          </div>
        </details>
      @empty
        <div style="padding: 48px 24px; text-align: center; background: #ffffff; border: 1px dashed #d4d4d8; border-radius: 16px;">
          <p style="font-size: 0.88rem; color: #71717a; margin: 0;">Aún no hay preguntas frecuentes publicadas. Se gestionan desde el Hub 2 en la sección de Páginas CMS.</p>
        </div>
      @endforelse

      <div id="aeoNoResultsMsg" style="display: none; padding: 36px 20px; text-align: center; background: #ffffff; border: 1px dashed #e4e4e7; border-radius: 14px; color: #71717a; font-size: 0.88rem;">
        No se encontraron respuestas para tu término de búsqueda. Prueba con otra palabra clave como "envío", "tallas" o "pagos".
      </div>
    </div>

    <!-- Concierge WhatsApp Help Box -->
    <div style="margin-top: 36px; padding: 24px; background: #ffffff; border: 1px solid #e4e4e7; border-radius: 16px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
      <div>
        <h4 style="font-family: var(--font-heading, 'Outfit', sans-serif); font-size: 1.05rem; font-weight: 800; color: #09090b; margin: 0 0 4px; text-transform: uppercase;">
          ¿No encontraste respuesta a tu consulta?
        </h4>
        <p style="font-size: 0.82rem; color: #71717a; margin: 0;">
          Nuestro equipo de concierge y atención personalizada está disponible en tiempo real para resolver dudas sobre pedidos, medidas o convenios.
        </p>
      </div>
      <a href="https://wa.me/525512345678?text=Hola,%20tengo%20una%20consulta%20sobre%20la%20tienda%20online" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 12px; background: #25d366; color: #ffffff; font-size: 0.82rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; text-decoration: none; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25);">
        <span>Chat Concierge</span>
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
      </a>
    </div>

    <script>
      (function() {
        var searchInput = document.getElementById('aeoSearchInput');
        var catButtons = document.querySelectorAll('.aeo-category-btn');
        var faqItems = document.querySelectorAll('.aeo-details-item');
        var noResults = document.getElementById('aeoNoResultsMsg');
        var activeCategory = 'all';

        function filterFaqs() {
          var query = (searchInput.value || '').trim().toLowerCase();
          var visibleCount = 0;

          faqItems.forEach(function(item) {
            var cat = item.getAttribute('data-category');
            var searchData = item.getAttribute('data-search') || '';

            var matchesCat = (activeCategory === 'all' || cat === activeCategory);
            var matchesQuery = (!query || searchData.indexOf(query) !== -1);

            if (matchesCat && matchesQuery) {
              item.style.display = '';
              visibleCount++;
            } else {
              item.style.display = 'none';
            }
          });

          if (noResults) {
            noResults.style.display = (visibleCount === 0 && faqItems.length > 0) ? 'block' : 'none';
          }
        }

        if (searchInput) {
          searchInput.addEventListener('input', filterFaqs);
        }

        catButtons.forEach(function(btn) {
          btn.addEventListener('click', function() {
            catButtons.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            activeCategory = this.getAttribute('data-category');
            filterFaqs();
          });
        });
      })();
    </script>
  @else
    {{-- PÁGINA EDITORIAL CMS ESTÁNDAR --}}
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
  @endif

</div>
@endsection
