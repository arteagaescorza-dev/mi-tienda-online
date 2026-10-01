@extends('layouts.store-pwa')

@section('content')
  <!-- VISTA 1: INICIO (HOME FEED // NUESTRA REVISTA DIGITAL OFICIAL) -->
  <div id="viewHome" style="{{ ($initialTab ?? 'home') === 'home' ? 'display: block;' : 'display: none;' }}">
    
    @if(isset($sections) && $sections->isNotEmpty())
      @foreach($sections as $sec)
        @if($sec->type === 'hero_carousel')
          @php
            $settings = is_array($sec->settings) ? $sec->settings : (json_decode($sec->settings ?? '{}', true) ?: []);
            $heroMode = $settings['hero_mode'] ?? (!empty($sec->custom_html) && empty($sec->title) ? 'custom' : 'structured');
            $isFullWidth = !empty($settings['full_width']);
            $bgImage = $settings['background_image'] ?? null;
            $enableGradient = !empty($settings['enable_gradient']);
            $gradStart = $settings['gradient_color_start'] ?? '#0f172a';
            $gradEnd = $settings['gradient_color_end'] ?? '#1e1b4b';
            $gradDir = $settings['gradient_direction'] ?? 'to bottom';
            $gradOpacity = isset($settings['gradient_opacity']) ? ((int)$settings['gradient_opacity'] / 100) : 0.85;

            $bgLayers = [];
            if ($enableGradient) {
                $bgLayers[] = "linear-gradient({$gradDir}, {$gradStart}, {$gradEnd})";
            }
            if (!empty($bgImage)) {
                $bgLayers[] = "url('{$bgImage}') center/cover no-repeat";
            }
            $heroBgStyle = !empty($bgLayers) ? 'background: ' . implode(', ', $bgLayers) . ' !important;' : '';

            $ctaPrimaryText = $settings['cta_primary_text'] ?? $settings['cta_text'] ?? $sec->query_rules['cta_text'] ?? 'EXPLORAR CATÁLOGO';
            $ctaPrimaryUrl = $settings['cta_primary_url'] ?? $settings['cta_url'] ?? $sec->query_rules['cta_url'] ?? null;
            $ctaSecondaryText = $settings['cta_secondary_text'] ?? 'GUÍA DE TALLAS TORZO';
            $ctaSecondaryUrl = $settings['cta_secondary_url'] ?? null;
          @endphp

          <!-- Hero Carousel / Editorial Banner -->
          <section id="section-{{ $sec->id }}" class="hero-editorial-section {{ $isFullWidth ? 'hero-full-width' : '' }}" data-section-id="{{ $sec->id }}">
            @if($heroMode === 'custom')
              {{-- MODO CÓDIGO PERSONALIZADO (AISLADO, NO DUPLICA EL HERO) --}}
              <div class="hero-custom-container" style="{{ $heroBgStyle }}">
                @if(!empty($sec->custom_css))
                  <style>{!! $sec->custom_css !!}</style>
                @endif
                @if(!empty($sec->custom_html))
                  {!! $sec->custom_html !!}
                @endif
                @if(!empty($sec->custom_js))
                  <script>{!! $sec->custom_js !!}</script>
                @endif
              </div>
            @else
              {{-- MODO VISUAL ESTRUCTURADO (FORMULARIO DINÁMICO) --}}
              <div class="hero-banner-card" style="{{ $heroBgStyle }}">
                @if(!empty($sec->badge) || !empty($currentStorefront['heroTag']))
                  <div class="hero-tag-pill">
                    <span class="hero-pulse-dot"></span>
                    <span id="heroBrandTag">{{ $sec->badge ?? $currentStorefront['heroTag'] ?? 'MARTE // COLECCIÓN OFICIAL TORZO' }}</span>
                  </div>
                @endif

                <h1 class="hero-title">{{ $sec->title ?? $currentStorefront['heroTitle'] ?? 'MODA CONTEMPORÁNEA & ROPA INTERIOR ERGONÓMICA' }}</h1>

                @if(!empty($sec->subtitle) || !empty($currentStorefront['heroSubtitle']))
                  <p class="hero-desc">
                    {{ $sec->subtitle ?? $currentStorefront['heroSubtitle'] ?? 'Diseño textil anatómico con modal pima, costuras planas y microfibras de alta transpirabilidad. Envíos express a todo México.' }}
                  </p>
                @endif

                <div class="hero-actions-row">
                  @if(!empty($ctaPrimaryUrl))
                    <a href="{{ $ctaPrimaryUrl }}" class="btn-hero-primary" style="text-decoration: none;">
                      <span>{{ $ctaPrimaryText }}</span>
                      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                  @else
                    <button type="button" class="btn-hero-primary" data-action="switch-dock-tab" data-dock-tab="catalog">
                      <span>{{ $ctaPrimaryText }}</span>
                      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                  @endif

                  @if(!empty($ctaSecondaryUrl))
                    <a href="{{ $ctaSecondaryUrl }}" class="btn-hero-secondary" style="text-decoration: none;">
                      <span>{{ $ctaSecondaryText }}</span>
                    </a>
                  @else
                    <button type="button" class="btn-hero-secondary" data-action="open-size-guide">
                      <span>{{ $ctaSecondaryText }}</span>
                    </button>
                  @endif
                </div>
              </div>
            @endif
          </section>
        @elseif($sec->type === 'brand_pillars')
          <!-- Pilares de Marca & Beneficios (Courier Exclusivo) -->
          <section id="section-{{ $sec->id }}" class="brand-pillars-bar" data-section-id="{{ $sec->id }}">
            <div class="pillar-item-card">
              <div class="pillar-icon-box">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
              </div>
              <div>
                <div class="pillar-title">ENVÍOS EXPRESS NACIONALES</div>
                <div class="pillar-sub">Paquetería Express (${{ number_format((float)($shippingRule['flatRate'] ?? 149), 0) }} MXN / Gratis desde ${{ number_format((float)($shippingRule['freeShippingThreshold'] ?? 999), 0) }})</div>
              </div>
            </div>

            <div class="pillar-item-card">
              <div class="pillar-icon-box">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M7 15h3M15 15h2"/></svg>
              </div>
              <div>
                <div class="pillar-title">TRANSFERENCIAS SPEI BBVA</div>
                <div class="pillar-sub">Sin comisión bancaria directa</div>
              </div>
            </div>

            <div class="pillar-item-card">
              <div class="pillar-icon-box">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              </div>
              <div>
                <div class="pillar-title">EMPAQUE 100% DISCRETO</div>
                <div class="pillar-sub">Precinto higiénico termosellado</div>
              </div>
            </div>
            @if(!empty($sec->custom_css))
              <style>{!! $sec->custom_css !!}</style>
            @endif
            @if(!empty($sec->custom_html))
              {!! $sec->custom_html !!}
            @endif
            @if(!empty($sec->custom_js))
              <script>{!! $sec->custom_js !!}</script>
            @endif
          </section>
        @elseif($sec->type === 'editorial_banner')
          <!-- Banner Promocional de Campañas Dir A (Convenios & Aliados Hub 7 TASK-STR-04) -->
          <section id="section-{{ $sec->id }}" class="lead-magnet-banner-card" data-section-id="{{ $sec->id }}" data-card-id="leadMagnetHomeCard_{{ $sec->id }}">
            <div class="lead-magnet-content">
              <div class="lead-magnet-tag-row">
                <span class="lead-magnet-tag">{{ $sec->badge ?? 'CONVENIOS MULTIVERSO // HUB 7' }}</span>
                <span class="badge-lead-magnet">CORTESÍA EXCLUSIVA</span>
              </div>
              <h3 class="lead-magnet-title">{{ $sec->title ?? 'Cortesía Especial en Paradiso Club & Circuit CDMX' }}</h3>
              <p class="lead-magnet-desc">
                {{ $sec->subtitle ?? 'Obtén tu voucher digital con acceso preferencial y trago de cortesía en los mejores venues aliados de la comunidad.' }}
              </p>
              @php
                $edCtaText = $sec->settings['cta_text'] ?? $sec->query_rules['cta_text'] ?? 'OBTENER MI VOUCHER DIGITAL';
                $edCtaUrl = $sec->settings['cta_url'] ?? $sec->query_rules['cta_url'] ?? null;
              @endphp
              @if(!empty($edCtaUrl))
                <a href="{{ $edCtaUrl }}" class="btn-lead-magnet-action" style="text-decoration: none;">
                  <span>{{ $edCtaText }}</span>
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
              @else
                <button type="button" class="btn-lead-magnet-action" data-action="open-lead-magnet" data-campaign="{{ $sec->settings['campaign'] ?? 'PARADISO_VIP' }}">
                  <span>{{ $edCtaText }}</span>
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>
              @endif
            </div>
            @if(!empty($sec->custom_css))
              <style>{!! $sec->custom_css !!}</style>
            @endif
            @if(!empty($sec->custom_html))
              {!! $sec->custom_html !!}
            @endif
            @if(!empty($sec->custom_js))
              <script>{!! $sec->custom_js !!}</script>
            @endif
          </section>
        @elseif(in_array($sec->type, ['curated_products', 'categories_grid', 'family_showcase', 'tags_cloud']))
          <!-- Feed de Prendas Destacadas / Colección Curada ({{ $sec->type }}) -->
          @php
            $sectionProducts = $sec->resolved_products ?? $products;
          @endphp
          <section id="section-{{ $sec->id }}" style="margin-bottom: 32px;" data-section-id="{{ $sec->id }}" data-section-type="{{ $sec->type }}">
            <div class="catalog-section-header">
              <div>
                <h2 class="section-headline">{{ $sec->title ?? 'PRENDAS DESTACADAS' }}</h2>
                <span style="font-size: 0.72rem; color: var(--text-muted);">{{ $sec->subtitle ?? 'Curaduría activa para tu universo' }}</span>
              </div>
              <div style="display: flex; align-items: center; gap: 8px;">
                @if(!empty($sec->badge))
                  <span class="badge-lead-magnet" style="font-size: 0.65rem;">{{ $sec->badge }}</span>
                @endif
                <button type="button" class="section-action-btn" data-action="open-filter-drawer">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
                  <span>Filtros</span>
                </button>
              </div>
            </div>

            <div id="{{ $sec->type === 'curated_products' ? 'homeProductsGrid' : 'sectionProductsGrid_' . $sec->id }}" class="product-feed-grid">
              @forelse($sectionProducts as $prod)
                <div class="product-card" data-product-id="{{ $prod['id'] }}">
                  <div class="product-image-wrap">
                    <button type="button" class="btn-wishlist-toggle" data-action="toggle-wishlist" data-product-id="{{ $prod['id'] }}" title="Guardar en mis deseos" aria-label="Guardar en favoritos">
                      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    </button>
                    <span class="product-stock-badge {{ ($prod['stockType'] ?? '') === 'ready' ? 'ready' : '' }}">
                      {{ $prod['stockLabel'] ?? 'ENVÍO EXPRESS' }}
                    </span>
                    <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%; cursor: pointer; text-decoration: none;" title="{{ $prod['name'] }}">
                      <svg width="40" height="40" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                      <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">{{ $prod['sku'] ?? 'TORZO' }}</span>
                    </a>
                  </div>
                  <div class="product-body">
                    <div>
                      <div class="product-category-label">{{ $prod['categoryName'] ?? 'General' }}</div>
                      <h3 class="product-title">
                        <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" style="cursor: pointer; color: inherit; text-decoration: none;">{{ $prod['name'] }}</a>
                      </h3>
                      <div class="product-pricing-row">
                        <span class="product-price">{{ $prod['price_formatted'] ?? ('MXN $' . number_format((float)($prod['price'] ?? 0), 2)) }}</span>
                        <span style="font-size: 0.68rem; color: var(--text-muted); font-weight: 600;">IVA incluido</span>
                      </div>
                    </div>
                    <div>
                      <div class="product-sizes-selector">
                        @foreach(($prod['sizes'] ?? ['CH', 'M', 'G']) as $sz)
                          <button type="button" class="size-pill-btn {{ $loop->first ? 'selected' : '' }}" data-action="select-size" data-product-id="{{ $prod['id'] }}" data-size="{{ $sz }}">{{ $sz }}</button>
                        @endforeach
                      </div>
                      <div style="display: flex; gap: 6px; margin-top: 8px;">
                        <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" class="btn-hero-secondary" style="padding: 8px 10px; font-size: 0.7rem; border-radius: var(--radius-sm); text-decoration: none; text-align: center;" title="Ver ficha técnica completa">
                          FICHA
                        </a>
                        <button type="button" class="btn-add-cart" data-action="add-to-cart" data-product-id="{{ $prod['id'] }}" style="flex: 1;">
                          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                          <span>AGREGAR</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 48px 20px; color: var(--text-muted);">
                  <strong style="font-family: var(--font-heading); color: var(--text-primary); display: block; font-size: 0.95rem;">Sin prendas en esta selección</strong>
                  <small style="font-size: 0.8rem;">Prueba cambiando el universo o la categoría en los filtros.</small>
                </div>
              @endforelse
            </div>
            @if(!empty($sec->custom_css))
              <style>{!! $sec->custom_css !!}</style>
            @endif
            @if(!empty($sec->custom_html))
              {!! $sec->custom_html !!}
            @endif
            @if(!empty($sec->custom_js))
              <script>{!! $sec->custom_js !!}</script>
            @endif
          </section>
        @elseif($sec->type === 'subcategories_slider')
          <!-- Slider Horizontal de Subcategorías CMS -->
          @php
            $sliderProducts = $sec->resolved_products ?? $products;
          @endphp
          <section id="section-{{ $sec->id }}" style="margin-bottom: 32px;" data-section-id="{{ $sec->id }}" data-section-type="subcategories_slider">
            <div class="catalog-section-header">
              <div>
                <h2 class="section-headline">{{ $sec->title ?? 'EXPLORAR COLECCIÓN' }}</h2>
                <span style="font-size: 0.72rem; color: var(--text-muted);">{{ $sec->subtitle ?? 'Desliza para ver más prendas' }}</span>
              </div>
              @if(!empty($sec->badge))
                <span class="badge-lead-magnet" style="font-size: 0.65rem;">{{ $sec->badge }}</span>
              @endif
            </div>

            <div style="display: flex; gap: 14px; overflow-x: auto; padding-bottom: 12px; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scrollbar-width: thin;">
              @forelse($sliderProducts as $prod)
                <div class="product-card" data-product-id="{{ $prod['id'] }}" style="min-width: 220px; max-width: 240px; flex-shrink: 0; scroll-snap-align: start;">
                  <div class="product-image-wrap" style="height: 180px;">
                    <button type="button" class="btn-wishlist-toggle" data-action="toggle-wishlist" data-product-id="{{ $prod['id'] }}" title="Guardar en mis deseos" aria-label="Guardar en favoritos">
                      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    </button>
                    <span class="product-stock-badge {{ ($prod['stockType'] ?? '') === 'ready' ? 'ready' : '' }}">
                      {{ $prod['stockLabel'] ?? 'EXPRESS' }}
                    </span>
                    <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%; cursor: pointer; text-decoration: none;" title="{{ $prod['name'] }}">
                      <svg width="36" height="36" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                      <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">{{ $prod['sku'] ?? 'TORZO' }}</span>
                    </a>
                  </div>
                  <div class="product-body" style="padding: 10px;">
                    <div>
                      <div class="product-category-label">{{ $prod['categoryName'] ?? 'General' }}</div>
                      <h3 class="product-title" style="font-size: 0.78rem;">
                        <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" style="cursor: pointer; color: inherit; text-decoration: none;">{{ $prod['name'] }}</a>
                      </h3>
                      <div class="product-pricing-row">
                        <span class="product-price" style="font-size: 0.85rem;">{{ $prod['price_formatted'] ?? ('MXN $' . number_format((float)($prod['price'] ?? 0), 2)) }}</span>
                      </div>
                    </div>
                    <div style="margin-top: 8px;">
                      <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" class="btn-hero-secondary" style="display: block; padding: 6px 10px; font-size: 0.68rem; border-radius: var(--radius-sm); text-decoration: none; text-align: center;" title="Ver ficha técnica">
                        VER PRENDA
                      </a>
                    </div>
                  </div>
                </div>
              @empty
                <div style="text-align: center; padding: 24px; color: var(--text-muted); width: 100%;">
                  <small>No hay prendas disponibles para esta subcategoría.</small>
                </div>
              @endforelse
            </div>
            @if(!empty($sec->custom_css))
              <style>{!! $sec->custom_css !!}</style>
            @endif
            @if(!empty($sec->custom_html))
              {!! $sec->custom_html !!}
            @endif
            @if(!empty($sec->custom_js))
              <script>{!! $sec->custom_js !!}</script>
            @endif
          </section>
        @elseif($sec->type === 'promo_modal')
          <!-- Modal Promocional CMS (TASK-H2-12) -->
          <div id="promoModal_{{ $sec->id }}" class="promo-modal-backdrop" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 16px;">
            <div style="background: #ffffff; border-radius: 12px; max-width: 480px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); position: relative; border: 1px solid #e4e4e7;">
              <button type="button" onclick="document.getElementById('promoModal_{{ $sec->id }}').style.display='none'" style="position: absolute; top: 12px; right: 12px; background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #71717a; line-height: 1;">&times;</button>
              @if(!empty($sec->badge))
                <div style="font-size: 0.7rem; font-weight: 700; color: #4f46e5; text-transform: uppercase; margin-bottom: 6px;">{{ $sec->badge }}</div>
              @endif
              <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; color: #09090b; margin: 0 0 8px;">{{ $sec->title }}</h3>
              @if(!empty($sec->subtitle))
                <p style="font-size: 0.85rem; color: #71717a; margin-bottom: 16px;">{{ $sec->subtitle }}</p>
              @endif
              @if(!empty($sec->custom_html))
                <div style="margin-bottom: 16px;">{!! $sec->custom_html !!}</div>
              @endif
              <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('promoModal_{{ $sec->id }}').style.display='none'" class="btn-hero-secondary" style="padding: 8px 16px; font-size: 0.8rem;">CERRAR</button>
                @if(!empty($sec->settings['cta_url']))
                  <a href="{{ $sec->settings['cta_url'] }}" class="btn-hero-primary" style="padding: 8px 16px; font-size: 0.8rem; text-decoration: none;">{{ $sec->settings['cta_text'] ?? 'VER PROMOCIÓN' }}</a>
                @endif
              </div>
            </div>
            @if(!empty($sec->custom_css))
              <style>{!! $sec->custom_css !!}</style>
            @endif
            @if(!empty($sec->custom_js))
              <script>{!! $sec->custom_js !!}</script>
            @endif
          </div>
          <script>
            document.addEventListener('DOMContentLoaded', function() {
              var modalKey = 'promo_seen_{{ $sec->id }}';
              if (!sessionStorage.getItem(modalKey)) {
                setTimeout(function() {
                  var m = document.getElementById('promoModal_{{ $sec->id }}');
                  if (m) m.style.display = 'flex';
                }, 1200);
                sessionStorage.setItem(modalKey, '1');
              }
            });
          </script>
        @elseif($sec->type === 'custom_html_css_js')
          <!-- Bloque Custom Operativo (HTML/CSS/JS Scoped) -->
          <section id="section-{{ $sec->id }}" class="custom-cms-section" data-section-id="{{ $sec->id }}" style="margin-bottom: 32px;">
            @if(!empty($sec->custom_css))
              <style>{!! $sec->custom_css !!}</style>
            @endif
            @if(!empty($sec->custom_html))
              {!! $sec->custom_html !!}
            @endif
            @if(!empty($sec->custom_js))
              <script>{!! $sec->custom_js !!}</script>
            @endif
          </section>
        @endif
      @endforeach
    @else
      <!-- Hero Carousel / Editorial Banner (Fallback) -->
      <section class="hero-editorial-section">
        <div class="hero-banner-card">
          <div class="hero-tag-pill">
            <span class="hero-pulse-dot"></span>
            <span id="heroBrandTag">{{ $currentStorefront['heroTag'] ?? 'MARTE // COLECCIÓN OFICIAL TORZO' }}</span>
          </div>
          <h1 class="hero-title">{{ $currentStorefront['heroTitle'] ?? 'MODA CONTEMPORÁNEA & ROPA INTERIOR ERGONÓMICA' }}</h1>
          <p class="hero-desc">
            {{ $currentStorefront['heroSubtitle'] ?? 'Diseño textil anatómico con modal pima, costuras planas y microfibras de alta transpirabilidad. Envíos express a todo México.' }}
          </p>
          <div class="hero-actions-row">
            <button type="button" class="btn-hero-primary" data-action="switch-dock-tab" data-dock-tab="catalog">
              <span>EXPLORAR CATÁLOGO</span>
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>
            <button type="button" class="btn-hero-secondary" data-action="open-size-guide">
              <span>GUÍA DE TALLAS TORZO</span>
            </button>
          </div>
        </div>
      </section>

      <!-- Pilares de Marca & Beneficios (Fallback) -->
      <section class="brand-pillars-bar">
        <div class="pillar-item-card">
          <div class="pillar-icon-box">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
          </div>
          <div>
            <div class="pillar-title">ENVÍOS EXPRESS NACIONALES</div>
            <div class="pillar-sub">Paquetería Express (${{ number_format((float)($shippingRule['flatRate'] ?? 149), 0) }} MXN / Gratis desde ${{ number_format((float)($shippingRule['freeShippingThreshold'] ?? 999), 0) }})</div>
          </div>
        </div>

        <div class="pillar-item-card">
          <div class="pillar-icon-box">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M7 15h3M15 15h2"/></svg>
          </div>
          <div>
            <div class="pillar-title">TRANSFERENCIAS SPEI BBVA</div>
            <div class="pillar-sub">Sin comisión bancaria directa</div>
          </div>
        </div>

        <div class="pillar-item-card">
          <div class="pillar-icon-box">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
          </div>
          <div>
            <div class="pillar-title">EMPAQUE 100% DISCRETO</div>
            <div class="pillar-sub">Precinto higiénico termosellado</div>
          </div>
        </div>
      </section>

      <!-- Banner Promocional de Campañas Dir A (Fallback) -->
      <section class="lead-magnet-banner-card" id="leadMagnetHomeCard">
        <div class="lead-magnet-content">
          <div class="lead-magnet-tag-row">
            <span class="lead-magnet-tag">CONVENIOS MULTIVERSO // HUB 7</span>
            <span class="badge-lead-magnet">CORTESÍA EXCLUSIVA</span>
          </div>
          <h3 class="lead-magnet-title" id="leadMagnetTitle">Cortesía Especial en Paradiso Club & Circuit CDMX</h3>
          <p class="lead-magnet-desc" id="leadMagnetDesc">
            Obtén tu voucher digital con acceso preferencial y trago de cortesía en los mejores venues aliados de la comunidad.
          </p>
          <button type="button" class="btn-lead-magnet-action" data-action="open-lead-magnet" data-campaign="PARADISO_VIP">
            <span>OBTENER MI VOUCHER DIGITAL</span>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
        </div>
      </section>

      <!-- Feed de Prendas Destacadas (Fallback) -->
      <section style="margin-bottom: 32px;">
        <div class="catalog-section-header">
          <div>
            <h2 class="section-headline">PRENDAS DESTACADAS</h2>
            <span style="font-size: 0.72rem; color: var(--text-muted);">Curaduría activa para tu universo</span>
          </div>
          <button type="button" class="section-action-btn" data-action="open-filter-drawer">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
            <span>Filtros</span>
          </button>
        </div>

        <div id="homeProductsGrid" class="product-feed-grid">
          @forelse($products as $prod)
            <div class="product-card" data-product-id="{{ $prod['id'] }}">
              <div class="product-image-wrap">
                <button type="button" class="btn-wishlist-toggle" data-action="toggle-wishlist" data-product-id="{{ $prod['id'] }}" title="Guardar en mis deseos" aria-label="Guardar en favoritos">
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                </button>
                <span class="product-stock-badge {{ $prod['stockType'] === 'ready' ? 'ready' : '' }}">
                  {{ $prod['stockLabel'] }}
                </span>
                <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%; cursor: pointer; text-decoration: none;" title="{{ $prod['name'] }}">
                  <svg width="40" height="40" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">{{ $prod['sku'] }}</span>
                </a>
              </div>
              <div class="product-body">
                <div>
                  <div class="product-category-label">{{ $prod['categoryName'] }}</div>
                  <h3 class="product-title">
                    <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" style="cursor: pointer; color: inherit; text-decoration: none;">{{ $prod['name'] }}</a>
                  </h3>
                  <div class="product-pricing-row">
                    <span class="product-price">{{ $prod['price_formatted'] }}</span>
                    <span style="font-size: 0.68rem; color: var(--text-muted); font-weight: 600;">IVA incluido</span>
                  </div>
                </div>
                <div>
                  <div class="product-sizes-selector">
                    @foreach($prod['sizes'] as $sz)
                      <button type="button" class="size-pill-btn {{ $loop->first ? 'selected' : '' }}" data-action="select-size" data-product-id="{{ $prod['id'] }}" data-size="{{ $sz }}">{{ $sz }}</button>
                    @endforeach
                  </div>
                  <div style="display: flex; gap: 6px; margin-top: 8px;">
                    <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" class="btn-hero-secondary" style="padding: 8px 10px; font-size: 0.7rem; border-radius: var(--radius-sm); text-decoration: none; text-align: center;" title="Ver ficha técnica completa">
                      FICHA
                    </a>
                    <button type="button" class="btn-add-cart" data-action="add-to-cart" data-product-id="{{ $prod['id'] }}" style="flex: 1;">
                      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                      <span>AGREGAR</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 48px 20px; color: var(--text-muted);">
              <strong style="font-family: var(--font-heading); color: var(--text-primary); display: block; font-size: 0.95rem;">Sin prendas en esta selección</strong>
              <small style="font-size: 0.8rem;">Prueba cambiando el universo o la categoría en los filtros.</small>
            </div>
          @endforelse
        </div>
      </section>
    @endif

  </div>

  <!-- VISTA: EXPLORAR (BOTONES CON IMAGEN 4:5, OVERLAY CON DEGRADADO & ACCIÓN DIRECTA) -->
  <div id="viewExplore" style="{{ ($initialTab ?? '') === 'explore' ? 'display: block;' : 'display: none;' }} padding-top: 16px;">
    <div class="catalog-section-header">
      <div>
        <h2 class="section-headline">EXPLORAR UNIVERSOS &amp; COLECCIONES</h2>
        <span style="font-size: 0.72rem; color: var(--text-muted);">Páginas temáticas, curadurías de estilo y guías de selección</span>
      </div>
    </div>

    <style>
      .explore-buttons-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 16px;
        margin-top: 16px;
        margin-bottom: 40px;
      }
      @media (max-width: 640px) {
        .explore-buttons-grid {
          grid-template-columns: repeat(2, 1fr);
          gap: 12px;
        }
      }
      .explore-button-card {
        aspect-ratio: 4 / 5;
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 16px;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.12);
        transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.28s ease;
        text-align: left;
        width: 100%;
        background: #09090b;
        user-select: none;
        -webkit-tap-highlight-color: transparent;
      }
      .explore-button-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.25);
      }
      .explore-button-card:active {
        transform: scale(0.98);
      }
      .explore-button-card .explore-card-bg-img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 1;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
      }
      .explore-button-card:hover .explore-card-bg-img {
        transform: scale(1.05);
      }
      .explore-button-card .explore-card-overlay {
        position: absolute;
        inset: 0;
        z-index: 2;
        pointer-events: none;
      }
      .explore-button-card .explore-card-top {
        position: relative;
        z-index: 3;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        pointer-events: none;
      }
      .explore-button-card .explore-card-content {
        position: relative;
        z-index: 3;
        display: flex;
        flex-direction: column;
        pointer-events: none;
      }
    </style>

    <div class="explore-buttons-grid">
      @forelse($explorePages ?? [] as $ep)
        @php
          $hasFilters = !empty($ep->filter_universe) || !empty($ep->filter_category);
          $overlayCss = $ep->getOverlayGradientCss();
        @endphp

        @if($hasFilters)
          <button type="button" 
                  class="explore-button-card"
                  data-action="explore-filter-catalog"
                  data-universe="{{ $ep->filter_universe }}"
                  data-category="{{ $ep->filter_category }}"
                  title="{{ $ep->title }}">
        @else
          <a href="{{ url('/pagina/' . $ep->slug) }}" 
             class="explore-button-card"
             role="button"
             title="{{ $ep->title }}">
        @endif

          {{-- 1. IMAGEN DE FONDO --}}
          @if(!empty($ep->image_url))
            <img src="{{ $ep->image_url }}" alt="{{ $ep->title }}" class="explore-card-bg-img" loading="lazy">
          @else
            <div class="explore-card-bg-img" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #18181b, #09090b); color: #71717a;">
              <svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
            </div>
          @endif

          {{-- 2. OVERLAY CON DEGRADADO --}}
          <div class="explore-card-overlay" style="background: {!! $overlayCss !!};"></div>

          {{-- 3. CABECERA / INSIGNIA SUPERIOR --}}
          <div class="explore-card-top">
            @if($hasFilters)
              <span style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); color: #ffffff; font-size: 0.62rem; font-weight: 800; padding: 3px 9px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid rgba(255,255,255,0.25);">
                {{ strtoupper($ep->filter_universe ?? 'CATÁLOGO') }} {{ !empty($ep->filter_category) ? '// ' . strtoupper($ep->filter_category) : '' }}
              </span>
            @else
              <span style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); color: #ffffff; font-size: 0.62rem; font-weight: 800; padding: 3px 9px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid rgba(255,255,255,0.25);">
                EDITORIAL
              </span>
            @endif

            @if($hasFilters)
              <a href="{{ url('/pagina/' . $ep->slug) }}" 
                 onclick="event.stopPropagation();" 
                 style="pointer-events: auto; background: rgba(255, 255, 255, 0.18); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); color: #ffffff; border-radius: 9999px; padding: 4px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.3); transition: background 0.2s;"
                 title="Leer artículo editorial"
                 aria-label="Leer artículo editorial">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
              </a>
            @endif
          </div>

          {{-- 4. CONTENIDO INFERIOR INTEGRADO AL BOTÓN --}}
          <div class="explore-card-content">
            <h3 style="font-family: var(--font-heading, sans-serif); font-size: 1.05rem; font-weight: 800; color: #ffffff; margin: 0 0 4px; line-height: 1.25; text-shadow: 0 2px 6px rgba(0,0,0,0.6);">
              {{ $ep->title }}
            </h3>
            @if(!empty($ep->seo_description))
              <p style="font-size: 0.72rem; color: rgba(255, 255, 255, 0.85); margin: 0 0 10px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-shadow: 0 1px 3px rgba(0,0,0,0.6);">
                {{ \Illuminate\Support\Str::limit($ep->seo_description, 80) }}
              </p>
            @endif

            <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
              <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #ffffff; background: rgba(255,255,255,0.22); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); padding: 5px 12px; border-radius: 9999px; border: 1px solid rgba(255,255,255,0.35);">
                @if($hasFilters)
                  <span>Ver Catálogo</span>
                  <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                @else
                  <span>Ver Página</span>
                  <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                @endif
              </span>
            </div>
          </div>

        @if($hasFilters)
          </button>
        @else
          </a>
        @endif
      @empty
        <div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: var(--bg-secondary); border-radius: var(--radius-md); border: 1px dashed var(--border-subtle);">
          <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">No hay páginas de exploración configuradas aún. Créalas desde el Hub 2 en la sección "Páginas de Explorar".</p>
        </div>
      @endforelse
    </div>
  </div>

  <!-- VISTA 2: CATÁLOGO COMPLETO -->
  <div id="viewCatalog" style="{{ ($initialTab ?? '') === 'catalog' ? 'display: block;' : 'display: none;' }} padding-top: 16px;">
    <div class="catalog-section-header">
      <div>
        <h2 class="section-headline">CATÁLOGO COMPLETO</h2>
        <span style="font-size: 0.72rem; color: var(--text-muted);">Prendas de diseño, ropa interior y cuidado</span>
      </div>
      <button type="button" class="section-action-btn" data-action="open-filter-drawer">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
        <span>Filtros & Universos</span>
      </button>
    </div>

    <div id="catalogProductsGrid" class="product-feed-grid">
      @forelse($products as $prod)
        <div class="product-card" data-product-id="{{ $prod['id'] }}">
          <div class="product-image-wrap">
            <button type="button" class="btn-wishlist-toggle" data-action="toggle-wishlist" data-product-id="{{ $prod['id'] }}" title="Guardar en mis deseos" aria-label="Guardar en favoritos">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            </button>
            <span class="product-stock-badge {{ $prod['stockType'] === 'ready' ? 'ready' : '' }}">
              {{ $prod['stockLabel'] }}
            </span>
            <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%; cursor: pointer; text-decoration: none;" title="{{ $prod['name'] }}">
              <svg width="40" height="40" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">{{ $prod['sku'] }}</span>
            </a>
          </div>
          <div class="product-body">
            <div>
              <div class="product-category-label">{{ $prod['categoryName'] }}</div>
              <h3 class="product-title">
                <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" style="cursor: pointer; color: inherit; text-decoration: none;">{{ $prod['name'] }}</a>
              </h3>
              <div class="product-pricing-row">
                <span class="product-price">{{ $prod['price_formatted'] }}</span>
                <span style="font-size: 0.68rem; color: var(--text-muted); font-weight: 600;">IVA incluido</span>
              </div>
            </div>
            <div>
              <div class="product-sizes-selector">
                @foreach($prod['sizes'] as $sz)
                  <button type="button" class="size-pill-btn {{ $loop->first ? 'selected' : '' }}" data-action="select-size" data-product-id="{{ $prod['id'] }}" data-size="{{ $sz }}">{{ $sz }}</button>
                @endforeach
              </div>
              <div style="display: flex; gap: 6px; margin-top: 8px;">
                <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" class="btn-hero-secondary" style="padding: 8px 10px; font-size: 0.7rem; border-radius: var(--radius-sm); text-decoration: none; text-align: center;" title="Ver ficha técnica completa">
                  FICHA
                </a>
                <button type="button" class="btn-add-cart" data-action="add-to-cart" data-product-id="{{ $prod['id'] }}" style="flex: 1;">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  <span>AGREGAR</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 48px 20px; color: var(--text-muted);">
          <strong style="font-family: var(--font-heading); color: var(--text-primary); display: block; font-size: 0.95rem;">Sin prendas en esta selección</strong>
          <small style="font-size: 0.8rem;">Prueba cambiando el universo o la categoría en los filtros.</small>
        </div>
      @endforelse
    </div>
  </div>
@endsection
