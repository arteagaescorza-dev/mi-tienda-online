@extends('layouts.store-pwa')

@section('content')
  <!-- VISTA 1: INICIO (HOME FEED) -->
  <div id="viewHome">
    
    @if(isset($sections) && $sections->isNotEmpty())
      @foreach($sections as $sec)
        @if($sec->type === 'hero_carousel')
          <!-- Hero Carousel / Editorial Banner -->
          <section class="hero-editorial-section" data-section-id="{{ $sec->id }}">
            <div class="hero-banner-card">
              <div class="hero-tag-pill">
                <span class="hero-pulse-dot"></span>
                <span id="heroBrandTag">{{ $sec->badge ?? $currentStorefront['heroTag'] ?? 'MARTE // COLECCIÓN OFICIAL TORZO' }}</span>
              </div>
              <h1 class="hero-title">{{ $sec->title ?? $currentStorefront['heroTitle'] ?? 'MODA CONTEMPORÁNEA & ROPA INTERIOR ERGONÓMICA' }}</h1>
              <p class="hero-desc">
                {{ $sec->subtitle ?? $currentStorefront['heroSubtitle'] ?? 'Diseño textil anatómico con modal pima, costuras planas y microfibras de alta transpirabilidad. Envíos express a todo México.' }}
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
        @elseif($sec->type === 'brand_pillars')
          <!-- Pilares de Marca & Beneficios (Courier Exclusivo) -->
          <section class="brand-pillars-bar" data-section-id="{{ $sec->id }}">
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
        @elseif($sec->type === 'editorial_banner')
          <!-- Banner Promocional de Campañas Dir A (Convenios & Aliados Hub 7 TASK-STR-04) -->
          <section class="lead-magnet-banner-card" id="leadMagnetHomeCard" data-section-id="{{ $sec->id }}">
            <div class="lead-magnet-content">
              <div class="lead-magnet-tag-row">
                <span class="lead-magnet-tag">{{ $sec->badge ?? 'CONVENIOS MULTIVERSO // HUB 7' }}</span>
                <span class="badge-lead-magnet">CORTESÍA EXCLUSIVA</span>
              </div>
              <h3 class="lead-magnet-title" id="leadMagnetTitle">{{ $sec->title ?? 'Cortesía Especial en Paradiso Club & Circuit CDMX' }}</h3>
              <p class="lead-magnet-desc" id="leadMagnetDesc">
                {{ $sec->subtitle ?? 'Obtén tu voucher digital con acceso preferencial y trago de cortesía en los mejores venues aliados de la comunidad.' }}
              </p>
              <button type="button" class="btn-lead-magnet-action" data-action="open-lead-magnet" data-campaign="{{ $sec->settings['campaign'] ?? 'PARADISO_VIP' }}">
                <span>{{ $sec->settings['cta_text'] ?? 'OBTENER MI VOUCHER DIGITAL' }}</span>
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              </button>
            </div>
          </section>
        @elseif($sec->type === 'curated_products')
          <!-- Feed de Prendas Destacadas -->
          <section style="margin-bottom: 32px;" data-section-id="{{ $sec->id }}">
            <div class="catalog-section-header">
              <div>
                <h2 class="section-headline">{{ $sec->title ?? 'PRENDAS DESTACADAS' }}</h2>
                <span style="font-size: 0.72rem; color: var(--text-muted);">{{ $sec->subtitle ?? 'Curaduría activa para tu universo' }}</span>
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
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%; cursor: pointer;" data-action="open-product-modal" data-product-id="{{ $prod['id'] }}">
                      <svg width="40" height="40" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                      <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">{{ $prod['sku'] }}</span>
                    </div>
                  </div>
                  <div class="product-body">
                    <div>
                      <div class="product-category-label">{{ $prod['categoryName'] }}</div>
                      <h3 class="product-title" data-action="open-product-modal" data-product-id="{{ $prod['id'] }}" style="cursor: pointer;">{{ $prod['name'] }}</h3>
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
        @elseif($sec->type === 'custom_html_css_js')
          <!-- Bloque Custom Operativo (HTML/CSS/JS Scoped) -->
          <section class="custom-cms-section" data-section-id="{{ $sec->id }}" style="margin-bottom: 32px;">
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
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%; cursor: pointer;" data-action="open-product-modal" data-product-id="{{ $prod['id'] }}">
                  <svg width="40" height="40" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">{{ $prod['sku'] }}</span>
                </div>
              </div>
              <div class="product-body">
                <div>
                  <div class="product-category-label">{{ $prod['categoryName'] }}</div>
                  <h3 class="product-title" data-action="open-product-modal" data-product-id="{{ $prod['id'] }}" style="cursor: pointer;">{{ $prod['name'] }}</h3>
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

  <!-- VISTA 2: CATÁLOGO COMPLETO -->
  <div id="viewCatalog" style="display: none; padding-top: 16px;">
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
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%; cursor: pointer;" data-action="open-product-modal" data-product-id="{{ $prod['id'] }}">
              <svg width="40" height="40" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">{{ $prod['sku'] }}</span>
            </div>
          </div>
          <div class="product-body">
            <div>
              <div class="product-category-label">{{ $prod['categoryName'] }}</div>
              <h3 class="product-title" data-action="open-product-modal" data-product-id="{{ $prod['id'] }}" style="cursor: pointer;">{{ $prod['name'] }}</h3>
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
                <button type="button" class="btn-hero-secondary" data-action="open-product-modal" data-product-id="{{ $prod['id'] }}" style="padding: 8px 10px; font-size: 0.7rem; border-radius: var(--radius-sm);" title="Ver ficha técnica">
                  FICHA
                </button>
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
