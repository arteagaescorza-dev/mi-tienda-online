// ============================================================================
// MULTIVERSO STORE — STOREFRONT D2C PWA LOGIC
// Ecosistema Comercial: TERRA (Global) | MARTE (Masculino) | VENUS (Lencería) | TIRESIAS (Afirmativo)
// Contemporary Luxury Minimalist & Mobile-First Priority
// Strict Zero Emojis Policy (Inline SVG Only)
// ============================================================================

(function() {
  'use strict';

  // Configuración de Identidad y Metadatos por Universo
  const UNIVERSE_META = {
    marte: {
      brandName: 'MARTE',
      subTitle: 'UNIVERSO MASCULINO',
      heroTag: 'MARTE // COLECCIÓN OFICIAL TORZO',
      pageTitle: 'MARTE — Universo Masculino & Ropa Interior'
    },
    venus: {
      brandName: 'VENUS',
      subTitle: 'LENCERÍA & BEAUTY',
      heroTag: 'VENUS // LENCERÍA DE AUTOR & SILUETAS',
      pageTitle: 'VENUS — Lencería de Autor & Beauty'
    },
    tiresias: {
      brandName: 'TIRESIAS',
      subTitle: 'MODA AFIRMATIVA',
      heroTag: 'TIRESIAS // AFIRMATIVA & NO BINARIO',
      pageTitle: 'TIRESIAS — Moda Afirmativa & Binders'
    },
    terra: {
      brandName: 'TERRA',
      subTitle: 'ECOSISTEMA GLOBAL',
      heroTag: 'TERRA // CATÁLOGO GLOBAL CONSOLIDADO',
      pageTitle: 'TERRA — Ecosistema Multiverso Store'
    }
  };

  // Catálogo Inicial Curado con Precios Oficiales Torzo de Menudeo (Conexión Base de Datos Laravel)
  let STORE_PRODUCTS = (typeof window !== 'undefined' && Array.isArray(window.__INITIAL_STORE_PRODUCTS__) && window.__INITIAL_STORE_PRODUCTS__.length > 0)
    ? window.__INITIAL_STORE_PRODUCTS__
    : [
    {
      id: 'TOR-JCK-001',
      sku: 'TOR-JCK-CARBON',
      name: 'Jockstrap Athletic Carbon Microfibra',
      category: 'jocks',
      categoryName: 'Jockstraps',
      price: 250.00,
      universes: ['marte', 'terra'],
      stockType: 'ready',
      stockLabel: 'Stock Físico',
      image: 'logo cuadrado-01.png',
      sizes: ['CH', 'M', 'G', 'XG'],
      description: 'Elástico jacquard de 45mm con microfibra transpirable de secado rápido. Soporte anatómico de alta durabilidad.'
    },
    {
      id: 'TOR-BOX-002',
      sku: 'TOR-BOX-MODAL-BLK',
      name: 'Boxer Brief Modal Pima Soft Negro',
      category: 'boxers',
      categoryName: 'Boxers',
      price: 280.00,
      universes: ['marte', 'terra'],
      stockType: 'ready',
      stockLabel: 'Stock Físico',
      image: 'logo cuadrado-01.png',
      sizes: ['CH', 'M', 'G', 'XG'],
      description: '95% Modal Pima peruano / 5% Spandex. Confección con costuras planas flatlock sin roce. Máxima suavidad.'
    },
    {
      id: 'TOR-TNG-003',
      sku: 'TOR-TNG-MINIMAL',
      name: 'Tanga Ergonómica Low-Rise Velvet',
      category: 'tangas',
      categoryName: 'Tangas',
      price: 180.00,
      universes: ['marte', 'terra'],
      stockType: 'ready',
      stockLabel: 'Stock Físico',
      image: 'logo cuadrado-01.png',
      sizes: ['CH', 'M', 'G', 'XG'],
      description: 'Silueta minimalista de tiro bajo con copa preformada y elásticos ultrafinos invisibles bajo el pantalón.'
    },
    {
      id: 'TOR-BIK-004',
      sku: 'TOR-BIK-CONTOUR',
      name: 'Bikini Contour Seamless Athletic',
      category: 'bikinis',
      categoryName: 'Bikinis',
      price: 220.00,
      universes: ['marte', 'venus', 'terra'],
      stockType: 'ready',
      stockLabel: 'Stock Físico',
      image: 'logo cuadrado-01.png',
      sizes: ['CH', 'M', 'G', 'XG'],
      description: 'Ajuste ceñido de corte clásico en microfibra perforada para ventilación continua durante el día.'
    },
    {
      id: 'TOR-TNK-005',
      sku: 'TOR-TNK-GYM-CARBON',
      name: 'Tanktop Ribbed Deep Cut Athletic',
      category: 'tanktops',
      categoryName: 'Tank Tops',
      price: 320.00,
      universes: ['marte', 'tiresias', 'terra'],
      stockType: 'on_demand',
      stockLabel: 'Bajo Pedido',
      image: 'logo cuadrado-01.png',
      sizes: ['CH', 'M', 'G', 'XG'],
      description: 'Algodón acanalado premium con caída relajada y sisas profundas para movilidad completa en torso.'
    },
    {
      id: 'TOR-SWM-006',
      sku: 'TOR-SWM-ACAPULCO',
      name: 'Swimwear Short Brief Resistente a Cloro',
      category: 'swimwear',
      categoryName: 'Trajes de Baño',
      price: 460.00,
      universes: ['marte', 'venus', 'terra'],
      stockType: 'on_demand',
      stockLabel: 'Bajo Pedido',
      image: 'logo cuadrado-01.png',
      sizes: ['CH', 'M', 'G', 'XG'],
      description: 'Tejido italiano con protección UV50+ y tratamiento de secado ultrarrápido con forro frontal higiénico.'
    },
    {
      id: 'TOR-BND-007',
      sku: 'TOR-BND-COMPRESS',
      name: 'Binder Compresión Ergonómica Respirable',
      category: 'tanktops',
      categoryName: 'Moda Afirmativa',
      price: 380.00,
      universes: ['tiresias', 'terra'],
      stockType: 'ready',
      stockLabel: 'Stock Físico',
      image: 'logo cuadrado-01.png',
      sizes: ['CH', 'M', 'G', 'XG'],
      description: 'Panel frontal de compresión plana con espalda de malla elástica para distribución equitativa de presión torácica.'
    },
    {
      id: 'TOR-LNC-008',
      sku: 'TOR-LNC-BRALETTE',
      name: 'Bralette Encaje Geométrico Sin Varilla',
      category: 'bikinis',
      categoryName: 'Lencería de Autor',
      price: 320.00,
      universes: ['venus', 'terra'],
      stockType: 'ready',
      stockLabel: 'Stock Físico',
      image: 'logo cuadrado-01.png',
      sizes: ['CH', 'M', 'G', 'XG'],
      description: 'Diseño sin estructuras rígidas con banda elástica aterciopelada y tirantes ajustables cruzados.'
    }
  ];

  // Parámetros Logísticos Oficiales (Courier Nacional Exclusivo)
  const FREE_SHIPPING_THRESHOLD = 999;
  const SHIPPING_COST = 149;

  // Reserva Temporal de Stock en Carrito (Urgencia de Compra: 5 Minutos / 300s)
  const CART_STOCK_LOCK_SECONDS = 300;
  let stockLockTimerRemaining = CART_STOCK_LOCK_SECONDS;
  let stockLockInterval = null;

  // Estado Central de la Tienda
  const storeState = {
    activeUniverse: 'marte', // 'terra', 'marte', 'venus', 'tiresias'
    activeTab: 'home',       // 'home', 'catalog'
    activeCategory: 'all',
    activeSize: 'all',
    cart: [],
    wishlist: [],            // Array de IDs de productos en lista de deseos
    selectedSizes: {},       // Mapeo productId -> talla elegida
    activeModalProductId: null,
    selectedModalSize: null
  };

  // Cargar Carrito y Wishlist desde localStorage
  try {
    const savedCart = localStorage.getItem('multiverse_store_cart');
    if (savedCart) {
      storeState.cart = JSON.parse(savedCart);
    }
    const savedWishlist = localStorage.getItem('multiverse_store_wishlist');
    if (savedWishlist) {
      storeState.wishlist = JSON.parse(savedWishlist);
    }
  } catch (err) {
    storeState.cart = [];
    storeState.wishlist = [];
  }

  // Guardar Carrito
  function saveCart() {
    try {
      localStorage.setItem('multiverse_store_cart', JSON.stringify(storeState.cart));
    } catch (e) {}
    updateCartBadges();
    renderCartSheetContent();
  }

  // Guardar Wishlist
  function saveWishlist() {
    try {
      localStorage.setItem('multiverse_store_wishlist', JSON.stringify(storeState.wishlist));
    } catch (e) {}
    updateWishlistBadges();
  }

  // ============================================================================
  // ESTADO DE CUPONES Y CONVENIOS // HUB 7 (TASK-STR-04)
  // ============================================================================
  const couponState = {
    appliedCoupon: null,
    discountPct: 0,
    discountAmount: 0,
    attributedAlly: null
  };

  const VALID_COUPONS = {
    'PARADISO15': {
      code: 'PARADISO15',
      discountPct: 15,
      ally: 'Paradiso Club',
      campaign: 'PARADISO_VIP',
      description: '15% de Descuento Especial Alianza Paradiso Club'
    },
    'CIRCUIT20': {
      code: 'CIRCUIT20',
      discountPct: 20,
      ally: 'Circuit CDMX',
      campaign: 'CIRCUIT_PASS',
      description: '20% de Descuento Especial Alianza Circuit CDMX'
    },
    'BIENVENIDO10': {
      code: 'BIENVENIDO10',
      discountPct: 10,
      ally: 'Multiverso Direct',
      campaign: 'WELCOME_PROMO',
      description: '10% de Descuento en tu Primera Orden Multiverso'
    }
  };

  const LEAD_CAMPAIGNS = {
    'PARADISO_VIP': {
      id: 'PARADISO_VIP',
      ally: 'Paradiso Club',
      title: 'Cortesía Especial en Paradiso Club',
      desc: 'Registra tus datos para generar tu voucher digital oficial con código QR y folio de canje para taquilla o barra en Paradiso Club.',
      badgeText: 'CORTESÍA PARADISO CLUB',
      benefit: 'Acceso Preferencial + Welcome Drink en Barra VIP',
      associatedCoupon: 'PARADISO15'
    },
    'CIRCUIT_PASS': {
      id: 'CIRCUIT_PASS',
      ally: 'Circuit CDMX',
      title: 'Pass Preferencial en Circuit CDMX',
      desc: 'Acceso prioritario y beneficio exclusivo para eventos oficiales de la comunidad Circuit CDMX.',
      badgeText: 'CIRCUIT CDMX PASS',
      benefit: 'Acceso Fast-Pass + 20% off en Merch Oficial',
      associatedCoupon: 'CIRCUIT20'
    }
  };

  let activeLeadCampaignId = 'PARADISO_VIP';

  // ============================================================================
  // ESTADO DE NOTIFICACIONES WEB PUSH PWA // VAPID RFC 8292 (TASK-STR-06)
  // ============================================================================
  const pushNotificationState = {
    supported: false,
    permission: 'default', // 'default' | 'granted' | 'denied'
    subscription: null,
    promptDismissed: false,
    stockWarningFired: false,
    preferences: {
      stock_urgency: true,
      order_tracking: true,
      subscription_mrr: true,
      new_drops: true
    }
  };

  // Actualizar Contadores de Carrito
  function updateCartBadges() {
    const totalCount = storeState.cart.reduce((acc, item) => acc + item.quantity, 0);
    
    const desktopBadge = document.getElementById('desktopCartCount');
    if (desktopBadge) desktopBadge.textContent = totalCount;

    const dockBadge = document.getElementById('dockCartBadge');
    if (dockBadge) dockBadge.textContent = totalCount;
  }

  // Actualizar Contadores de Wishlist
  function updateWishlistBadges() {
    const count = storeState.wishlist.length;
    const desktopWishlist = document.getElementById('desktopWishlistCount');
    if (desktopWishlist) desktopWishlist.textContent = count;
  }

  // Filtrar Productos según Universo y Categoría
  function getFilteredProducts() {
    return STORE_PRODUCTS.filter(prod => {
      // Filtro de Universo
      if (storeState.activeUniverse !== 'terra') {
        if (!prod.universes.includes(storeState.activeUniverse)) {
          return false;
        }
      }
      // Filtro de Categoría
      if (storeState.activeCategory !== 'all') {
        if (prod.category !== storeState.activeCategory) {
          return false;
        }
      }
      return true;
    });
  }

  // Renderizar Grid de Productos
  function renderProductsGrid(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const products = getFilteredProducts();

    if (products.length === 0) {
      container.innerHTML = `
        <div style="grid-column: 1 / -1; text-align: center; padding: 48px 20px; color: var(--text-muted);">
          <div style="width: 48px; height: 48px; margin: 0 auto 12px; border-radius: 50%; background: #f4f4f5; display: flex; align-items: center; justify-content: center;">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
          </div>
          <strong style="font-family: var(--font-heading); color: var(--text-primary); display: block; font-size: 0.95rem;">Sin prendas en esta selección</strong>
          <small style="font-size: 0.8rem;">Prueba cambiando el universo o la categoría en los filtros.</small>
        </div>
      `;
      return;
    }

    container.innerHTML = products.map(prod => {
      const selectedSize = storeState.selectedSizes[prod.id] || 'M';
      const isFav = isInWishlist(prod.id);
      return `
        <div class="product-card" data-product-id="${prod.id}">
          <div class="product-image-wrap">
            <!-- Botón Flotante de Deseos / Wishlist -->
            <button type="button" class="btn-wishlist-toggle ${isFav ? 'active' : ''}" data-action="toggle-wishlist" data-product-id="${prod.id}" title="${isFav ? 'Quitar de mis deseos' : 'Guardar en mis deseos'}" aria-label="Guardar en favoritos">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            </button>

            <span class="product-stock-badge ${prod.stockType === 'ready' ? 'ready' : ''}">
              ${prod.stockLabel}
            </span>

            <!-- Imagen / Logo con apertura de PDP -->
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%; cursor: pointer;" data-action="open-product-modal" data-product-id="${prod.id}">
              <svg width="40" height="40" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">${prod.sku}</span>
            </div>
          </div>
          
          <div class="product-body">
            <div>
              <div class="product-category-label">${prod.categoryName}</div>
              <h3 class="product-title" data-action="open-product-modal" data-product-id="${prod.id}" style="cursor: pointer;">${prod.name}</h3>
              <div class="product-pricing-row">
                <span class="product-price">MXN $${prod.price.toFixed(2)}</span>
                <span style="font-size: 0.68rem; color: var(--text-muted); font-weight: 600;">IVA incluido</span>
              </div>
            </div>

            <div>
              <!-- Selector Rápido de Talla -->
              <div class="product-sizes-selector">
                ${prod.sizes.map(sz => `
                  <button type="button" class="size-pill-btn ${sz === selectedSize ? 'selected' : ''}" data-action="select-size" data-product-id="${prod.id}" data-size="${sz}">
                    ${sz}
                  </button>
                `).join('')}
              </div>

              <!-- Botonera: Ficha PDP + Agregar al Carrito -->
              <div style="display: flex; gap: 6px; margin-top: 8px;">
                <button type="button" class="btn-hero-secondary" data-action="open-product-modal" data-product-id="${prod.id}" style="padding: 8px 10px; font-size: 0.7rem; border-radius: var(--radius-sm);" title="Ver ficha técnica">
                  FICHA
                </button>
                <button type="button" class="btn-add-cart" data-action="add-to-cart" data-product-id="${prod.id}" style="flex: 1;">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  <span>AGREGAR</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  // Conmutador de Universos (Top bar & Drawer ZARA)
  function switchUniverse(universeKey) {
    storeState.activeUniverse = universeKey;
    
    // Actualizar botones de top selector
    document.querySelectorAll('.universe-tab-btn').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.universe === universeKey);
    });

    // Actualizar tabs ZARA del drawer de filtros
    document.querySelectorAll('.zara-filter-tab').forEach(tab => {
      tab.classList.toggle('active', tab.dataset.universe === universeKey);
    });

    // Sincronizar metadatos y branding dinámico según universo
    const meta = UNIVERSE_META[universeKey] || UNIVERSE_META.terra;
    const desktopBrand = document.getElementById('desktopBrandName');
    if (desktopBrand) desktopBrand.textContent = meta.brandName;
    const desktopSub = document.getElementById('desktopBrandSub');
    if (desktopSub) desktopSub.textContent = meta.subTitle;
    const mobileBrand = document.getElementById('mobileBrandName');
    if (mobileBrand) mobileBrand.textContent = meta.brandName;
    const heroTag = document.getElementById('heroBrandTag');
    if (heroTag) heroTag.textContent = meta.heroTag;
    document.title = meta.pageTitle;

    // Re-renderizar grids
    renderProductsGrid('homeProductsGrid');
    renderProductsGrid('catalogProductsGrid');
  }

  // Alternar Tabs del Bottom Dock (Inicio, Catálogo, Carrito, Cuenta)
  function switchDockTab(tabId) {
    if (tabId === 'cart') {
      openDrawer('cartSheet');
      return;
    }
    if (tabId === 'account') {
      openDrawer('accountSheet');
      return;
    }

    storeState.activeTab = tabId;

    // Actualizar estilo visual del dock
    document.querySelectorAll('.dock-tab-btn').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.dockTab === tabId);
    });

    // Cambiar vistas de contenido
    const homeView = document.getElementById('viewHome');
    const catalogView = document.getElementById('viewCatalog');

    if (tabId === 'home') {
      if (homeView) homeView.style.display = 'block';
      if (catalogView) catalogView.style.display = 'none';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } else if (tabId === 'catalog') {
      if (homeView) homeView.style.display = 'none';
      if (catalogView) catalogView.style.display = 'block';
      renderProductsGrid('catalogProductsGrid');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }

  // Manejo de Drawers / Bottom Sheets
  function openDrawer(drawerId) {
    const backdrop = document.getElementById('drawerBackdrop');
    const drawer = document.getElementById(drawerId);
    if (!drawer) return;

    if (backdrop) backdrop.classList.add('show');
    drawer.classList.add('show');
    document.body.style.overflow = 'hidden';

    if (drawerId === 'accountSheet' && typeof renderAccountSheetContent === 'function') {
      renderAccountSheetContent();
    }
    if (drawerId === 'cartSheet') {
      renderCartSheetContent();
      if (typeof fetchCartFromBackend === 'function') {
        fetchCartFromBackend();
      }
    }
  }

  function closeAllDrawers() {
    const backdrop = document.getElementById('drawerBackdrop');
    if (backdrop) backdrop.classList.remove('show');

    document.querySelectorAll('.bottom-sheet-drawer').forEach(d => {
      d.classList.remove('show');
    });
    document.body.style.overflow = '';
  }

  function closeDrawer(drawerId) {
    const drawer = document.getElementById(drawerId);
    if (drawer) drawer.classList.remove('show');
    closeAllDrawers();
  }

  // Logica del Carrito (Agregar, Quitar, Renderizar y Sincronizacion RESTful TASK-INT-06)
  function addToCart(productId, explicitSize = null, explicitVariantId = null) {
    const product = STORE_PRODUCTS.find(p => p.id === productId);
    if (!product) return;

    const size = explicitSize || storeState.selectedSizes[productId] || 'M';
    const existingIndex = storeState.cart.findIndex(i => i.productId === productId && i.size === size);

    if (existingIndex > -1) {
      storeState.cart[existingIndex].quantity += 1;
    } else {
      storeState.cart.push({
        productId: product.id,
        sku: product.sku,
        name: product.name,
        categoryName: product.categoryName,
        price: product.price,
        size: size,
        variantId: explicitVariantId || null,
        stockType: product.stockType || 'ready',
        stockLabel: product.stockLabel || (product.stockType === 'on_demand' ? 'Bajo Pedido' : 'Stock Fisico'),
        lineType: (product.stockType === 'on_demand' ? 'line_b' : 'line_a'),
        quantity: 1
      });
    }

    saveCart();
    notifyStockLockAcquired();
    showToastNotification(`Agregado: ${product.name} (Talla ${size})`);
    checkPushPromptTrigger();

    // Sincronizar en caliente con el backend RESTful (reserva temporal de stock)
    syncCartItemWithBackend(product.id, size, explicitVariantId, 1);
  }

  function syncCartItemWithBackend(productId, size, variantId, qty = 1) {
    try {
      fetch('/api/v1/store/cart/items', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Cart-Session-Id': getCartSessionId(),
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          cart_id: getCartSessionId(),
          product_id: productId,
          size: size,
          variant_id: variantId || null,
          quantity: qty
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data && data.success && data.summary) {
          applyBackendCartData(data);
        }
      })
      .catch(e => console.warn('Error sincronizando carrito con backend:', e));
    } catch (e) {}
  }

  function applyBackendCartData(data) {
    if (!data || !data.items) return;

    if (data.summary && data.summary.reservation) {
      if (data.summary.reservation.has_active_reservation && data.summary.reservation.min_remaining_seconds > 0) {
        stockLockTimerRemaining = data.summary.reservation.min_remaining_seconds;
        const expMs = Date.now() + (stockLockTimerRemaining * 1000);
        localStorage.setItem('multiverse_stock_lock_expires_at', expMs.toString());
      }
    }

    storeState.cart = data.items.map(bi => ({
      backendId: bi.id,
      productId: bi.product_id ? String(bi.product_id) : bi.sku,
      variantId: bi.variant_id,
      sku: bi.sku,
      name: bi.product_name,
      categoryName: 'Prenda',
      price: bi.price,
      size: bi.size,
      stockType: bi.line_type === 'line_b' ? 'on_demand' : 'ready',
      stockLabel: bi.stock_badge,
      lineType: bi.line_type,
      lineLabel: bi.line_label,
      quantity: bi.quantity,
      hasActiveLock: bi.has_active_lock
    }));

    saveCart();
    updateCartBadges();
    renderCartSheetContent();
  }

  function fetchCartFromBackend() {
    try {
      fetch(`/api/v1/store/cart?cart_id=${encodeURIComponent(getCartSessionId())}`, {
        headers: {
          'X-Cart-Session-Id': getCartSessionId(),
          'Accept': 'application/json'
        }
      })
      .then(res => res.json())
      .then(data => {
        if (data && data.success && data.items && data.items.length > 0) {
          applyBackendCartData(data);
        }
      })
      .catch(() => {});
    } catch (e) {}
  }

  function updateCartQuantity(index, delta) {
    if (!storeState.cart[index]) return;
    const item = storeState.cart[index];
    const newQty = item.quantity + delta;

    if (newQty <= 0) {
      removeCartItem(index);
      return;
    }

    item.quantity = newQty;
    saveCart();
    if (storeState.cart.length > 0) {
      notifyStockLockAcquired();
    }
    renderCartSheetContent();

    if (item.backendId) {
      fetch(`/api/v1/store/cart/items/${item.backendId}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-Cart-Session-Id': getCartSessionId(),
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          cart_id: getCartSessionId(),
          quantity: newQty
        })
      })
      .then(r => r.json())
      .then(d => { if (d && d.success) applyBackendCartData(d); })
      .catch(() => {});
    }
  }

  function removeCartItem(index) {
    if (!storeState.cart[index]) return;
    const item = storeState.cart[index];
    storeState.cart.splice(index, 1);
    saveCart();

    if (storeState.cart.length > 0) {
      notifyStockLockAcquired();
    } else {
      notifyStockLockReleased();
    }
    renderCartSheetContent();

    if (item.backendId) {
      fetch(`/api/v1/store/cart/items/${item.backendId}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-Cart-Session-Id': getCartSessionId(),
          'Accept': 'application/json'
        },
        body: JSON.stringify({ cart_id: getCartSessionId() })
      })
      .then(r => r.json())
      .then(d => { if (d && d.success) applyBackendCartData(d); })
      .catch(() => {});
    } else if (storeState.cart.length === 0) {
      fetch('/api/v1/store/cart/clear', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Cart-Session-Id': getCartSessionId(),
          'Accept': 'application/json'
        },
        body: JSON.stringify({ cart_id: getCartSessionId() })
      }).catch(() => {});
    }
  }

  function clearCart() {
    storeState.cart = [];
    saveCart();
    notifyStockLockReleased();
    renderCartSheetContent();

    fetch('/api/v1/store/cart/clear', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Cart-Session-Id': getCartSessionId(),
        'Accept': 'application/json'
      },
      body: JSON.stringify({ cart_id: getCartSessionId() })
    }).catch(() => {});
  }

  // ============================================================================
  // GESTOR DE RESERVA TEMPORAL DE STOCK (TTL CONFIGURABLE 5 MINUTOS / TASK-INT-06)
  // ============================================================================
  function getCartSessionId() {
    let sid = localStorage.getItem('multiverse_cart_session_id');
    if (!sid) {
      sid = 'sess_' + Math.random().toString(36).substring(2, 9);
      localStorage.setItem('multiverse_cart_session_id', sid);
    }
    return sid;
  }

  function safeDispatchEvent(name, detail) {
    if (typeof window !== 'undefined' && typeof window.dispatchEvent === 'function') {
      try {
        if (typeof CustomEvent === 'function') {
          window.dispatchEvent(new CustomEvent(name, { detail }));
        } else {
          window.dispatchEvent({ type: name, detail: detail });
        }
      } catch (e) {}
    }
  }

  function notifyStockLockAcquired() {
    if (storeState.cart.length === 0) return;
    const now = Date.now();
    let expiresAt = parseInt(localStorage.getItem('multiverse_stock_lock_expires_at'), 10);
    if (!expiresAt || expiresAt <= now) {
      expiresAt = now + (CART_STOCK_LOCK_SECONDS * 1000);
      localStorage.setItem('multiverse_stock_lock_expires_at', expiresAt.toString());
      stockLockTimerRemaining = CART_STOCK_LOCK_SECONDS;
    }
    safeDispatchEvent('stock-lock-acquired', {
      sessionId: getCartSessionId(),
      items: storeState.cart.map(i => ({ sku: i.sku, name: i.name, size: i.size, quantity: i.quantity })),
      expiresAt: expiresAt
    });
  }

  function notifyStockLockReleased() {
    localStorage.removeItem('multiverse_stock_lock_expires_at');
    if (stockLockInterval) {
      clearInterval(stockLockInterval);
      stockLockInterval = null;
    }
    stockLockTimerRemaining = CART_STOCK_LOCK_SECONDS;
    pushNotificationState.stockWarningFired = false;
    safeDispatchEvent('stock-lock-released', {
      sessionId: getCartSessionId()
    });
  }

  function handleStockLockExpiry() {
    if (stockLockInterval) {
      clearInterval(stockLockInterval);
      stockLockInterval = null;
    }
    const expiredItems = [...storeState.cart];
    stockLockTimerRemaining = CART_STOCK_LOCK_SECONDS;
    pushNotificationState.stockWarningFired = false;
    localStorage.removeItem('multiverse_stock_lock_expires_at');
    storeState.cart = [];
    try {
      localStorage.setItem('multiverse_store_cart', JSON.stringify([]));
    } catch (e) {}
    updateCartBadges();
    updateStockLockDisplay();

    // Cerrar modal de checkout si estaba abierto
    const checkoutModal = document.getElementById('checkoutModalBackdrop');
    if (checkoutModal && checkoutModal.classList.contains('show')) {
      if (typeof closeCheckoutModal === 'function') {
        closeCheckoutModal();
      } else {
        checkoutModal.classList.remove('show');
      }
    }

    const container = document.getElementById('cartItemsContainer');
    const footerAction = document.getElementById('cartFooterActions');
    if (container) {
      container.innerHTML = `
        <div class="cart-expiry-notice">
          <div class="cart-expiry-title">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#e11d48" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Reserva de Stock Expirada
          </div>
          <p class="cart-expiry-text">Tu reserva temporal de 5 minutos ha expirado. Las prendas se han liberado para otros clientes.</p>
          <button type="button" class="cart-expiry-btn" onclick="location.reload()">Explorar Catálogo</button>
        </div>
      `;
    }
    if (footerAction) footerAction.style.display = 'none';

    safeDispatchEvent('stock-lock-expired', {
      sessionId: getCartSessionId(),
      expiredItems
    });

    showToastNotification('Tu reserva de stock temporal ha expirado.');
  }

  // Gestión del Temporizador de Urgencia (5 Minutos / 300 Segundos)
  function manageStockLockTimer() {
    if (storeState.cart.length === 0) {
      if (stockLockInterval) {
        clearInterval(stockLockInterval);
        stockLockInterval = null;
      }
      stockLockTimerRemaining = CART_STOCK_LOCK_SECONDS;
      pushNotificationState.stockWarningFired = false;
      localStorage.removeItem('multiverse_stock_lock_expires_at');
      return;
    }

    const now = Date.now();
    let expiresAt = parseInt(localStorage.getItem('multiverse_stock_lock_expires_at'), 10);
    if (!expiresAt || isNaN(expiresAt)) {
      expiresAt = now + (CART_STOCK_LOCK_SECONDS * 1000);
      localStorage.setItem('multiverse_stock_lock_expires_at', expiresAt.toString());
      stockLockTimerRemaining = CART_STOCK_LOCK_SECONDS;
      notifyStockLockAcquired();
    } else {
      stockLockTimerRemaining = Math.max(0, Math.round((expiresAt - now) / 1000));
    }

    if (stockLockTimerRemaining <= 0) {
      handleStockLockExpiry();
      return;
    }

    updateStockLockDisplay();

    if (!stockLockInterval) {
      stockLockInterval = setInterval(() => {
        const curNow = Date.now();
        const curExp = parseInt(localStorage.getItem('multiverse_stock_lock_expires_at'), 10) || (curNow + (stockLockTimerRemaining * 1000));
        stockLockTimerRemaining = Math.max(0, Math.round((curExp - curNow) / 1000));

        // Alerta Web Push de Urgencia a los 2 minutos restantes (120s)
        if (stockLockTimerRemaining <= 120 && stockLockTimerRemaining > 118 && !pushNotificationState.stockWarningFired) {
          pushNotificationState.stockWarningFired = true;
          if (pushNotificationState.preferences.stock_urgency) {
            sendSimulatedPush('stock_urgency');
          }
        }

        if (stockLockTimerRemaining <= 0) {
          handleStockLockExpiry();
        } else {
          updateStockLockDisplay();
        }
      }, 1000);
    }
  }

  function updateStockLockDisplay() {
    const mins = Math.floor(stockLockTimerRemaining / 60);
    const secs = stockLockTimerRemaining % 60;
    const timeStr = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    const pct = Math.max(0, (stockLockTimerRemaining / CART_STOCK_LOCK_SECONDS) * 100);

    const timeEl = document.getElementById('stockLockTimeRemaining');
    if (timeEl) timeEl.textContent = timeStr;

    const barEl = document.getElementById('stockLockProgressBar');
    if (barEl) barEl.style.width = `${pct}%`;

    const chkTimeEl = document.getElementById('checkoutStockTimerRemaining');
    if (chkTimeEl) chkTimeEl.textContent = timeStr;

    const chkBarEl = document.getElementById('checkoutStockProgressBar');
    if (chkBarEl) chkBarEl.style.width = `${pct}%`;
  }

  // Interfaz Global de Reservas de Stock Storefront (Hub 2)
  window.stockLockMaster = {
    getSessionId: getCartSessionId,
    getRemainingSeconds: () => stockLockTimerRemaining,
    getExpiresAt: () => parseInt(localStorage.getItem('multiverse_stock_lock_expires_at'), 10) || null,
    getActiveLocks: () => {
      const expiresAt = parseInt(localStorage.getItem('multiverse_stock_lock_expires_at'), 10);
      if (!expiresAt || expiresAt <= Date.now() || storeState.cart.length === 0) return [];
      return storeState.cart.map(item => ({
        sessionId: getCartSessionId(),
        sku: item.sku,
        name: item.name,
        size: item.size,
        quantity: item.quantity,
        expiresAt: expiresAt,
        remainingSeconds: Math.max(0, Math.round((expiresAt - Date.now()) / 1000))
      }));
    },
    releaseAllLocks: () => {
      notifyStockLockReleased();
      storeState.cart = [];
      saveCart();
    },
    renewLock: (minutes = 5) => {
      const expiresAt = Date.now() + (minutes * 60 * 1000);
      localStorage.setItem('multiverse_stock_lock_expires_at', expiresAt.toString());
      stockLockTimerRemaining = minutes * 60;
      updateStockLockDisplay();
      notifyStockLockAcquired();
      return expiresAt;
    }
  };

  // Renderizar contenido del Bottom Sheet de Carrito
  function renderCartSheetContent() {
    const container = document.getElementById('cartItemsContainer');
    const footerAction = document.getElementById('cartFooterActions');
    if (!container) return;

    manageStockLockTimer();

    const rawSubtotal = storeState.cart.reduce((acc, i) => acc + (i.price * i.quantity), 0);
    const discountAmount = (couponState.appliedCoupon && couponState.discountPct > 0)
      ? Math.round(rawSubtotal * (couponState.discountPct / 100) * 100) / 100
      : 0;
    couponState.discountAmount = discountAmount;
    const subtotal = Math.max(0, rawSubtotal - discountAmount);
    const freeShippingThreshold = 999.00;
    const remainingForFree = Math.max(0, freeShippingThreshold - subtotal);
    const shippingProgress = Math.min(100, Math.round((subtotal / freeShippingThreshold) * 100));

    if (storeState.cart.length === 0) {
      container.innerHTML = `
        <div style="text-align: center; padding: 48px 20px; color: var(--text-muted);">
          <div style="width: 48px; height: 48px; margin: 0 auto 12px; border-radius: 50%; background: #f4f4f5; display: flex; align-items: center; justify-content: center;">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
          </div>
          <strong style="font-family: var(--font-heading); color: var(--text-primary); display: block; font-size: 1rem;">Tu carrito está vacío</strong>
          <p style="font-size: 0.8rem; margin-top: 4px;">Explora el catálogo y agrega tus prendas favoritas.</p>
        </div>
      `;
      if (footerAction) footerAction.style.display = 'none';
      renderCouponBoxes();
      return;
    }

    if (footerAction) footerAction.style.display = 'flex';

    // Banner de Urgencia de Reserva de Stock (5 Minutos)
    const mins = Math.floor(stockLockTimerRemaining / 60);
    const secs = stockLockTimerRemaining % 60;
    const initialTimeStr = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    const initialPct = Math.max(0, (stockLockTimerRemaining / CART_STOCK_LOCK_SECONDS) * 100);

    const urgencyBanner = `
      <div style="background: #fff1f2; border: 1px solid #fecdd3; border-radius: var(--radius-sm); padding: 10px 12px; margin-bottom: 14px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
          <div style="display: flex; align-items: center; gap: 6px;">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#e11d48" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span style="font-family: var(--font-heading); font-size: 0.73rem; font-weight: 800; color: #9f1239; text-transform: uppercase; letter-spacing: 0.04em;">Stock Apartado Exclusivo</span>
          </div>
          <span id="stockLockTimeRemaining" style="font-family: monospace; font-size: 0.82rem; font-weight: 800; color: #e11d48;">${initialTimeStr}</span>
        </div>
        <div style="width: 100%; height: 3px; background: #ffe4e6; border-radius: 2px; overflow: hidden; margin-top: 6px;">
          <div id="stockLockProgressBar" style="width: ${initialPct}%; height: 100%; background: #e11d48; transition: width 1s linear;"></div>
        </div>
        <div style="font-size: 0.67rem; color: #be123c; margin-top: 5px; line-height: 1.3;">
          Completa tu compra antes de que expire el tiempo para no perder tus prendas exclusivas.
        </div>
      </div>
    `;

    // Barra de Progreso Envío Gratis Courier
    let shippingNotice = '';
    if (subtotal >= freeShippingThreshold) {
      shippingNotice = `
        <div style="background: var(--accent-emerald-soft); border: 1px solid var(--accent-emerald-border); border-radius: var(--radius-sm); padding: 10px 12px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#059669" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
          <span style="font-size: 0.75rem; font-weight: 700; color: var(--accent-emerald);">¡Felicidades! Tienes Envío Express Nacional Gratuito</span>
        </div>
      `;
    } else {
      shippingNotice = `
        <div style="background: var(--bg-secondary); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 10px 12px; margin-bottom: 16px;">
          <div style="display: flex; justify-content: space-between; font-size: 0.72rem; font-weight: 700; margin-bottom: 6px;">
            <span>Agrega MXN $${remainingForFree.toFixed(2)} para Envío Gratis</span>
            <span>${shippingProgress}%</span>
          </div>
          <div style="width: 100%; height: 5px; background: #e4e4e7; border-radius: 3px; overflow: hidden;">
            <div style="width: ${shippingProgress}%; height: 100%; background: #050505; transition: width 0.3s ease;"></div>
          </div>
        </div>
      `;
    }

    // Desglose de carrito mixto: Linea A (Bodega local, entrega 24-48h) vs Linea B (Taller On-Demand, entrega 3-5 dias)
    const lineAEntries = storeState.cart.map((item, idx) => ({ item, idx }))
      .filter(x => (x.item.lineType || x.item.stockType) !== 'line_b' && (x.item.lineType || x.item.stockType) !== 'on_demand');
    const lineBEntries = storeState.cart.map((item, idx) => ({ item, idx }))
      .filter(x => (x.item.lineType || x.item.stockType) === 'line_b' || (x.item.lineType || x.item.stockType) === 'on_demand');
    const isMixedCart = lineAEntries.length > 0 && lineBEntries.length > 0;

    let mixedNoticeHtml = '';
    if (isMixedCart) {
      mixedNoticeHtml = `
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-sm); padding: 8px 12px; margin-bottom: 12px; font-size: 0.72rem; color: #475569; display: flex; align-items: center; gap: 6px;">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#2563eb" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          <div><strong style="color: #0f172a;">Pedido Mixto:</strong> Incluye prendas de bodega inmediata y confeccion en taller.</div>
        </div>
      `;
    }

    const renderItemHtml = ({ item, idx }) => `
      <div style="display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--border-subtle); align-items: center;">
        <div style="width: 50px; height: 50px; background: #f4f4f5; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden;">
          ${item.image && item.image !== '/user-avatar.png' ? `<img src="${item.image}" alt="${item.name}" style="width: 100%; height: 100%; object-fit: cover;">` : `<svg width="24" height="24" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="3" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2"/></svg>`}
        </div>
        
        <div style="flex-grow: 1;">
          <div style="font-family: var(--font-heading); font-size: 0.8rem; font-weight: 700; line-height: 1.2;">${item.name}</div>
          <div style="display: flex; align-items: center; gap: 8px; margin-top: 2px;">
            <span style="font-size: 0.7rem; color: var(--text-muted);">Talla: <strong style="color: var(--text-primary);">${item.size}</strong></span>
            <span style="font-size: 0.65rem; padding: 1px 5px; border-radius: 3px; font-weight: 700; ${((item.lineType || item.stockType) === 'line_b' || (item.lineType || item.stockType) === 'on_demand') ? 'background: #fffbeb; color: #b45309;' : 'background: #ecfdf5; color: #047857;'}">${item.stockLabel || (((item.lineType || item.stockType) === 'line_b' || (item.lineType || item.stockType) === 'on_demand') ? 'Bajo Pedido' : 'Stock Fisico')}</span>
          </div>
          <div style="font-family: var(--font-heading); font-size: 0.82rem; font-weight: 800; color: #050505; margin-top: 4px;">MXN $${(item.price * item.quantity).toFixed(2)}</div>
        </div>

        <div style="display: flex; align-items: center; gap: 6px;">
          <button type="button" class="btn-qty-control" data-action="decrease-qty" data-index="${idx}">-</button>
          <span style="font-family: var(--font-heading); font-size: 0.8rem; font-weight: 800; min-width: 18px; text-align: center;">${item.quantity}</span>
          <button type="button" class="btn-qty-control" data-action="increase-qty" data-index="${idx}">+</button>
          <button type="button" class="btn-remove-item" data-action="remove-item" data-index="${idx}" title="Eliminar">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
          </button>
        </div>
      </div>
    `;

    let itemsHtml = '';
    if (isMixedCart) {
      itemsHtml += `
        <div style="margin-top: 10px;">
          <div style="font-family: var(--font-heading); font-size: 0.72rem; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
            <span>Linea A - Bodega Local (Entrega 24-48h)</span>
            <span style="color: #059669; font-size: 0.65rem;">Stock Apartado</span>
          </div>
          ${lineAEntries.map(renderItemHtml).join('')}
        </div>
        <div style="margin-top: 16px;">
          <div style="font-family: var(--font-heading); font-size: 0.72rem; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
            <span>Linea B - Taller On-Demand (Entrega 3-5 dias)</span>
            <span style="color: #d97706; font-size: 0.65rem;">Confeccion Artesanal</span>
          </div>
          ${lineBEntries.map(renderItemHtml).join('')}
        </div>
      `;
    } else if (lineAEntries.length > 0) {
      itemsHtml += `
        <div style="margin-top: 10px;">
          <div style="font-family: var(--font-heading); font-size: 0.72rem; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
            <span>Linea A - Bodega Local (Entrega Express 24-48h)</span>
            <span style="color: #059669; font-size: 0.65rem;">Stock Apartado</span>
          </div>
          ${lineAEntries.map(renderItemHtml).join('')}
        </div>
      `;
    } else {
      itemsHtml += `
        <div style="margin-top: 10px;">
          <div style="font-family: var(--font-heading); font-size: 0.72rem; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
            <span>Linea B - Taller On-Demand (Entrega 3-5 dias)</span>
            <span style="color: #d97706; font-size: 0.65rem;">Confeccion Artesanal</span>
          </div>
          ${lineBEntries.map(renderItemHtml).join('')}
        </div>
      `;
    }

    // Actualizar Totales
    const cartSubtotalEl = document.getElementById('cartSubtotalAmount');
    if (cartSubtotalEl) {
      if (discountAmount > 0) {
        cartSubtotalEl.innerHTML = `<span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.8rem; margin-right: 6px;">MXN $${rawSubtotal.toFixed(2)}</span> MXN $${subtotal.toFixed(2)} <span style="color: var(--accent-emerald); font-size: 0.72rem; font-weight: 700;">(-$${discountAmount.toFixed(2)})</span>`;
      } else {
        cartSubtotalEl.textContent = `MXN $${subtotal.toFixed(2)}`;
      }
    }

    container.innerHTML = urgencyBanner + shippingNotice + mixedNoticeHtml + itemsHtml;
    renderCouponBoxes();
  }

  // Notificación Toast Táctil
  function showToastNotification(msg) {
    let toast = document.getElementById('storeToast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'storeToast';
      toast.style.cssText = `
        position: fixed;
        bottom: 84px;
        left: 50%;
        transform: translateX(-50%) translateY(20px);
        background: #09090b;
        color: #ffffff;
        padding: 10px 18px;
        border-radius: 9999px;
        font-family: var(--font-heading);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        z-index: 2000;
        opacity: 0;
        transition: all 0.25s ease;
        box-shadow: 0 4px 16px rgba(0,0,0,0.25);
        pointer-events: none;
        white-space: nowrap;
      `;
      document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.style.opacity = '1';
    toast.style.transform = 'translateX(-50%) translateY(0)';
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(-50%) translateY(20px)';
    }, 2400);
  }

  // ============================================================================
  // TELEMETRÍA DE DESEOS & MOTOR PREDICTIVO DE DEMANDA
  // ============================================================================
  function getOrCreateStoreSessionToken() {
    let token = localStorage.getItem('multiverse_store_session_token');
    if (!token) {
      token = 'str_sess_' + Math.random().toString(36).substring(2, 15) + Date.now().toString(36);
      localStorage.setItem('multiverse_store_session_token', token);
    }
    return token;
  }

  function dispatchWishlistTelemetry(eventType, payload) {
    const eventData = {
      event_type: eventType,
      session_token: getOrCreateStoreSessionToken(),
      timestamp: new Date().toISOString(),
      ...payload
    };

    // Guardado local de contingencia
    try {
      const storedSignals = JSON.parse(localStorage.getItem('multiverse_wishlist_telemetry') || '[]');
      storedSignals.push(eventData);
      if (storedSignals.length > 50) storedSignals.shift();
      localStorage.setItem('multiverse_wishlist_telemetry', JSON.stringify(storedSignals));
    } catch (e) {}

    // Ingesta asíncrona hacia backend spec
    if (typeof fetch === 'function') {
      fetch('/api/v1/store/wishlist/telemetry', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(eventData),
        keepalive: true
      }).catch(() => {});
    }
  }

  // ============================================================================
  // GESTIÓN DE WISHLIST / LISTA DE DESEOS
  // ============================================================================
  function isInWishlist(productId) {
    return storeState.wishlist.includes(productId);
  }

  function toggleWishlist(productId, event) {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }
    const index = storeState.wishlist.indexOf(productId);
    const product = STORE_PRODUCTS.find(p => p.id === productId);
    const productName = product ? product.name : productId;
    const selectedSize = storeState.selectedSizes[productId] || 'M';

    if (index >= 0) {
      storeState.wishlist.splice(index, 1);
      saveWishlist();
      showToastNotification(`Eliminado de deseos: ${productName}`);
      dispatchWishlistTelemetry('wishlist_remove', {
        product_id: productId,
        universe: storeState.activeUniverse,
        selected_size: selectedSize
      });
    } else {
      storeState.wishlist.push(productId);
      saveWishlist();
      showToastNotification(`Guardado en deseos: ${productName}`);
      dispatchWishlistTelemetry('wishlist_add', {
        product_id: productId,
        universe: storeState.activeUniverse,
        selected_size: selectedSize
      });
    }

    updateWishlistHeartButtons(productId);
    renderWishlistDrawerContent();
  }

  function updateWishlistHeartButtons(productId) {
    const isFav = isInWishlist(productId);
    document.querySelectorAll(`.btn-wishlist-toggle[data-product-id="${productId}"]`).forEach(btn => {
      btn.classList.toggle('active', isFav);
      btn.title = isFav ? 'Quitar de mis deseos' : 'Guardar en mis deseos';
    });
    const pdpWishlistBtn = document.getElementById('pdpWishlistBtn');
    if (pdpWishlistBtn && pdpWishlistBtn.dataset.productId === productId) {
      pdpWishlistBtn.classList.toggle('active', isFav);
    }
  }

  function moveWishlistToCart(productId) {
    const product = STORE_PRODUCTS.find(p => p.id === productId);
    if (!product) return;
    const size = storeState.selectedSizes[productId] || 'M';

    addToCart(productId, size);

    dispatchWishlistTelemetry('move_to_cart', {
      product_id: productId,
      universe: storeState.activeUniverse,
      selected_size: size
    });

    closeDrawer('wishlistSheet');
    openDrawer('cartSheet');
  }

  function renderWishlistDrawerContent() {
    const container = document.getElementById('wishlistItemsContainer');
    if (!container) return;

    if (storeState.wishlist.length === 0) {
      container.innerHTML = `
        <div style="text-align: center; padding: 48px 20px; color: var(--text-muted);">
          <div style="width: 48px; height: 48px; margin: 0 auto 12px; border-radius: 50%; background: #f4f4f5; display: flex; align-items: center; justify-content: center; color: #a1a1aa;">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
          </div>
          <strong style="font-family: var(--font-heading); color: var(--text-primary); display: block; font-size: 1rem;">Tu lista de deseos está vacía</strong>
          <p style="font-size: 0.8rem; margin-top: 4px;">Toca el corazón en cualquier prenda para guardarla y seguir su disponibilidad.</p>
        </div>
      `;
      return;
    }

    const itemsHtml = storeState.wishlist.map(productId => {
      const prod = STORE_PRODUCTS.find(p => p.id === productId);
      if (!prod) return '';
      const selectedSize = storeState.selectedSizes[prod.id] || 'M';

      return `
        <div class="wishlist-item-card" data-product-id="${prod.id}">
          <div class="wishlist-item-img" data-action="open-product-modal" data-product-id="${prod.id}" style="cursor: pointer;">
            <svg width="24" height="24" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="3" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2"/></svg>
          </div>

          <div style="flex-grow: 1;">
            <div style="font-family: var(--font-heading); font-size: 0.82rem; font-weight: 700; line-height: 1.2; cursor: pointer;" data-action="open-product-modal" data-product-id="${prod.id}">${prod.name}</div>
            <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 2px;">Talla: <strong style="color: var(--text-primary);">${selectedSize}</strong> · <span style="color: #047857; font-weight: 600;">${prod.stockLabel}</span></div>
            <div style="font-family: var(--font-heading); font-size: 0.84rem; font-weight: 800; color: #050505; margin-top: 4px;">MXN $${prod.price.toFixed(2)}</div>
          </div>

          <div style="display: flex; flex-direction: column; gap: 6px; align-items: flex-end;">
            <button type="button" class="btn-move-to-cart" data-action="move-wishlist-to-cart" data-product-id="${prod.id}">
              <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
              <span>AL CARRITO</span>
            </button>
            <button type="button" class="btn-remove-item" data-action="toggle-wishlist" data-product-id="${prod.id}" title="Quitar de deseos" style="font-size: 0.68rem; color: var(--text-muted); background: none; border: none; cursor: pointer;">
              Eliminar
            </button>
          </div>
        </div>
      `;
    }).join('');

    container.innerHTML = `
      <div style="font-family: var(--font-heading); font-size: 0.75rem; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px;">
        ${storeState.wishlist.length} prenda(s) guardadas
      </div>
      ${itemsHtml}
    `;
  }

  // ============================================================================
  // FICHA DE PRODUCTO ANATÓMICA (PDP) & CALCULADOR TORZO
  // ============================================================================
  function calculateTorzoSize(waistCm) {
    const cm = parseFloat(waistCm);
    if (isNaN(cm) || cm <= 0) return null;

    if (cm < 77) {
      return { size: 'CH', pants: '28-30', range: '70-76 cm', fitAdvice: 'Ajuste ceñido ergonómico con elástico de 45mm.' };
    } else if (cm <= 84) {
      return { size: 'M',  pants: '31-32', range: '78-84 cm', fitAdvice: 'Soporte anatómico exacto y confort de microfibra.' };
    } else if (cm <= 92) {
      return { size: 'G',  pants: '33-34', range: '86-92 cm', fitAdvice: 'Calce amplio con soporte seguro sin marcas en piel.' };
    } else if (cm <= 102) {
      return { size: 'XG', pants: '35-36', range: '94-102 cm', fitAdvice: 'Máxima elasticidad y ajuste anatómico generoso.' };
    } else if (cm <= 110) {
      return { size: '2XL', pants: '38-40', range: '103-110 cm', fitAdvice: 'Curva extendida con refuerzo elástico lateral.' };
    } else {
      return { size: '3XL', pants: '42-44', range: '111-120 cm', fitAdvice: 'Corte anatómico extra confort sin compresión excesiva.' };
    }
  }

  function openProductModal(productId) {
    const product = STORE_PRODUCTS.find(p => p.id === productId);
    if (!product) return;

    storeState.activeModalProductId = productId;
    const currentSize = storeState.selectedSizes[productId] || (product.sizes[0] || 'M');
    storeState.selectedModalSize = currentSize;

    const modal = document.getElementById('productModal');
    const backdrop = document.getElementById('productModalBackdrop');
    const content = document.getElementById('productModalContent');
    if (!modal || !backdrop || !content) return;

    const isFav = isInWishlist(productId);
    const selectedVariant = (product.variants || []).find(v => v.size === currentSize);
    const initialAvailable = selectedVariant ? (selectedVariant.available > 0) : (product.availableStock > 0);

    content.innerHTML = `
      <div class="pdp-layout-grid">
        <!-- Columna Izquierda: Galería -->
        <div class="pdp-gallery-box">
          <div class="pdp-main-image-wrap">
            <span id="pdpStockBadge" class="product-stock-badge ${initialAvailable ? 'ready' : ''}" style="top: 14px; left: 14px;">
              ${initialAvailable ? 'Stock Físico' : 'Bajo Pedido'}
            </span>
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; color: #a1a1aa; padding: 40px 0;">
              <svg width="72" height="72" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span style="font-family: var(--font-heading); font-size: 0.8rem; font-weight: 800; letter-spacing: 0.12em; color: #71717a;">${product.sku}</span>
            </div>
          </div>
          <div class="pdp-thumbnails-row">
            <div class="pdp-thumbnail-item active">
              <svg width="22" height="22" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="3" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2"/></svg>
            </div>
          </div>
        </div>

        <!-- Columna Derecha: Información, Tallas y Compra -->
        <div class="pdp-info-col">
          <div>
            <span class="pdp-universe-badge">${product.categoryName} // ${(product.universes || []).join(' & ').toUpperCase()}</span>
            <h2 class="pdp-product-title">${product.name}</h2>
            <div class="pdp-pricing-box">
              <span class="pdp-price-amount">MXN $${product.price.toFixed(2)}</span>
              <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600;">IVA incluido • Envío Express Courier</span>
            </div>
          </div>

          <p style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.5; margin: 0;">
            ${product.description}
          </p>

          <!-- Selector de Tallas Anatómicas -->
          <div>
            <div class="pdp-size-section-title">
              <span>Selecciona tu Talla Anatómica</span>
              <span id="pdpSelectedSizeLabel" style="color: #050505; font-weight: 800;">Talla ${currentSize}</span>
            </div>
            <div class="pdp-size-grid">
              ${product.sizes.map(sz => {
                const subMap = { 'CH': '28-30', 'M': '31-32', 'G': '33-34', 'XG': '35-36', 'XL': '35-36', '2XL': '38-40', '3XL': '42-44' };
                const variant = (product.variants || []).find(v => v.size === sz);
                const isOutOfStock = variant ? (variant.available <= 0) : false;
                const stockTag = variant ? (variant.available > 0 ? `${variant.available} pzas` : 'Bajo pedido') : '';
                return `
                  <button type="button" class="pdp-size-card-btn ${sz === currentSize ? 'selected' : ''} ${isOutOfStock ? 'on-demand-size' : ''}" data-action="pdp-select-size" data-size="${sz}">
                    <span class="pdp-size-name">${sz}</span>
                    <span class="pdp-size-sub">${subMap[sz] || ''}</span>
                    ${stockTag ? `<span class="pdp-size-stock-tag" style="font-size: 0.58rem; font-weight: 700; color: ${isOutOfStock ? '#d97706' : '#059669'}; display: block; margin-top: 2px;">${stockTag}</span>` : ''}
                  </button>
                `;
              }).join('')}
            </div>
          </div>

          <!-- Recomendador Anatómico Torzo -->
          <div class="pdp-size-advisor-box">
            <div class="pdp-advisor-title">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="10" rx="2"></rect><line x1="6" y1="7" x2="6" y2="11"></line><line x1="10" y1="7" x2="10" y2="12"></line><line x1="14" y1="7" x2="14" y2="11"></line><line x1="18" y1="7" x2="18" y2="12"></line></svg>
              <span>Calculador de Talla Torzo (cm de Cintura)</span>
            </div>
            <div class="pdp-advisor-input-row">
              <input type="number" id="pdpWaistCmInput" class="pdp-waist-input" placeholder="80" min="60" max="130" value="80">
              <span style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted);">cm</span>
              <button type="button" class="btn-calculate-size" data-action="pdp-calc-size">Calcular Talla</button>
            </div>
            <div id="pdpAdvisorResult" class="pdp-advisor-result-badge"></div>
          </div>

          <!-- Ficha Técnica y Aviso PROFECO -->
          <div class="pdp-specs-accordion">
            <strong>Especificaciones de Confección:</strong> Elástico de jacquard de 45mm de alto rendimiento, microfibra transpirable con tratamiento antibacterial y costuras planas anti-rozaduras.
          </div>

          <div class="pdp-sanitary-alert">
            <strong>Aviso Sanitario PROFECO:</strong> Por normativa sanitaria en prendas de uso íntimo, los cambios de talla proceden únicamente con el precinto sanitario inviolable intacto.
          </div>

          <!-- Botones de Acción: Comprar + Wishlist -->
          <div class="pdp-action-buttons-row">
            <button type="button" class="btn-pdp-add-cart" data-action="pdp-add-to-cart">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              <span>AÑADIR AL CARRITO</span>
            </button>
            <button type="button" id="pdpWishlistBtn" class="btn-pdp-wishlist ${isFav ? 'active' : ''}" data-action="toggle-wishlist" data-product-id="${product.id}" title="${isFav ? 'Quitar de mis deseos' : 'Guardar en mis deseos'}" aria-label="Guardar en deseos">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            </button>
          </div>
        </div>
      </div>
    `;

    backdrop.classList.add('open');
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';

    // Telemetría de visualización de PDP
    dispatchWishlistTelemetry('pdp_view', {
      product_id: productId,
      universe: storeState.activeUniverse,
      selected_size: currentSize
    });
  }

  function closeProductModal() {
    const modal = document.getElementById('productModal');
    const backdrop = document.getElementById('productModalBackdrop');
    if (modal) modal.classList.remove('open');
    if (backdrop) backdrop.classList.remove('open');
    document.body.style.overflow = '';
    storeState.activeModalProductId = null;
  }

  // ============================================================================
  // CUPONES, CONVENIOS Y LEAD MAGNETS DIR A // HUB 7 (TASK-STR-04)
  // ============================================================================
  function applyDiscountCoupon(rawCode) {
    if (!rawCode || typeof rawCode !== 'string') {
      showToastNotification('Por favor ingresa un código de cupón');
      return { success: false, message: 'Código vacío' };
    }
    const code = rawCode.trim().toUpperCase();
    if (!VALID_COUPONS[code]) {
      showToastNotification(`El cupón "${code}" no es válido o ha expirado`);
      return { success: false, message: 'Cupón no válido' };
    }

    const cData = VALID_COUPONS[code];
    couponState.appliedCoupon = code;
    couponState.discountPct = cData.discountPct;
    couponState.attributedAlly = cData.ally;

    updateCheckoutSummary();
    renderCartSheetContent();
    renderCouponBoxes();
    if (checkoutState && checkoutState.step === 3) {
      renderCheckoutStep3Review();
    }

    showToastNotification(`Cupón ${code} aplicado: ${cData.discountPct}% de descuento`);
    return { success: true, coupon: cData };
  }

  function removeDiscountCoupon() {
    if (!couponState.appliedCoupon) return;
    const prevCode = couponState.appliedCoupon;
    couponState.appliedCoupon = null;
    couponState.discountPct = 0;
    couponState.discountAmount = 0;
    couponState.attributedAlly = null;

    updateCheckoutSummary();
    renderCartSheetContent();
    renderCouponBoxes();
    if (checkoutState && checkoutState.step === 3) {
      renderCheckoutStep3Review();
    }

    showToastNotification(`Cupón ${prevCode} removido`);
  }

  function renderCouponBoxes() {
    const cartBox = document.getElementById('cartCouponBox');
    const chkBox = document.getElementById('checkoutCouponBox');

    const coupon = couponState.appliedCoupon ? (VALID_COUPONS[couponState.appliedCoupon] || {
      code: couponState.appliedCoupon,
      discountPct: couponState.discountPct,
      ally: couponState.attributedAlly || 'Convenio Exclusivo'
    }) : null;

    if (cartBox) {
      if (coupon) {
        cartBox.innerHTML = `
          <div class="coupon-applied-card">
            <div class="coupon-applied-badge">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
              <strong>${coupon.code} (-${coupon.discountPct}%)</strong>
            </div>
            <div class="coupon-ally-tag">${coupon.ally || 'Convenio Activo'}</div>
            <button type="button" class="btn-remove-coupon" data-action="remove-coupon" aria-label="Remover cupón">
              <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
        `;
      } else {
        cartBox.innerHTML = `
          <div class="coupon-input-group">
            <input type="text" id="cartCouponInput" class="coupon-input-field" placeholder="Código de cupón o aliado..." aria-label="Ingresa cupón de descuento">
            <button type="button" class="btn-apply-coupon" data-action="apply-cart-coupon">
              APLICAR
            </button>
          </div>
        `;
      }
    }

    if (chkBox) {
      if (coupon) {
        chkBox.innerHTML = `
          <div class="coupon-applied-card">
            <div class="coupon-applied-badge">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
              <strong>${coupon.code} (-${coupon.discountPct}%)</strong>
            </div>
            <div class="coupon-ally-tag">${coupon.ally || 'Convenio Activo'}</div>
            <button type="button" class="btn-remove-coupon" data-action="remove-coupon" aria-label="Remover cupón">
              <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
        `;
      } else {
        chkBox.innerHTML = `
          <div class="coupon-input-group">
            <input type="text" id="checkoutCouponInput" class="coupon-input-field" placeholder="Cupón o convenio (ej. PARADISO15)..." aria-label="Ingresa cupón en checkout">
            <button type="button" class="btn-apply-coupon" data-action="apply-checkout-coupon">
              APLICAR
            </button>
          </div>
        `;
      }
    }
  }

  function detectUrlAffiliationAndCoupons() {
    if (typeof window === 'undefined' || !window.location) return;
    try {
      const params = new URLSearchParams(window.location.search);
      const couponParam = params.get('coupon') || params.get('c');
      const refParam = (params.get('ref') || params.get('utm_source') || params.get('partner') || '').toLowerCase();

      let allyDetected = null;
      let couponToApply = null;

      if (refParam.includes('paradiso')) {
        allyDetected = 'Paradiso Club';
        couponToApply = 'PARADISO15';
      } else if (refParam.includes('circuit')) {
        allyDetected = 'Circuit CDMX';
        couponToApply = 'CIRCUIT20';
      } else if (refParam.includes('bienvenido') || refParam.includes('welcome')) {
        allyDetected = 'Multiverso Direct';
        couponToApply = 'BIENVENIDO10';
      }

      if (couponParam) {
        const norm = couponParam.trim().toUpperCase();
        if (VALID_COUPONS[norm]) {
          couponToApply = norm;
          allyDetected = VALID_COUPONS[norm].ally;
        }
      }

      if (allyDetected) {
        couponState.attributedAlly = allyDetected;
        const banner = document.getElementById('partnerWelcomeBanner');
        const textEl = document.getElementById('partnerWelcomeText');
        if (banner) {
          banner.style.display = 'block';
          if (textEl) {
            textEl.textContent = `Bienvenido desde ${allyDetected} - Beneficio y cortesía exclusiva disponible`;
          }
        }
      }

      if (couponToApply && VALID_COUPONS[couponToApply]) {
        applyDiscountCoupon(couponToApply);
      }
    } catch(e) {}
  }

  function openLeadMagnetModal(campaignId) {
    const cid = campaignId || 'PARADISO_VIP';
    activeLeadCampaignId = cid;
    const campaign = LEAD_CAMPAIGNS[cid] || LEAD_CAMPAIGNS['PARADISO_VIP'];

    const headline = document.getElementById('leadModalCampaignHeadline');
    const sub = document.getElementById('leadModalCampaignSub');
    if (headline) headline.textContent = campaign.title;
    if (sub) sub.textContent = campaign.desc;

    const captureView = document.getElementById('leadMagnetCaptureView');
    const voucherCard = document.getElementById('leadMagnetVoucherCard');
    if (captureView) captureView.style.display = 'block';
    if (voucherCard) voucherCard.style.display = 'none';

    const modal = document.getElementById('leadMagnetModal');
    const backdrop = document.getElementById('leadMagnetModalBackdrop');
    if (modal) modal.classList.add('open');
    if (backdrop) backdrop.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeLeadMagnetModal() {
    const modal = document.getElementById('leadMagnetModal');
    const backdrop = document.getElementById('leadMagnetModalBackdrop');
    if (modal) modal.classList.remove('open');
    if (backdrop) backdrop.classList.remove('open');
    document.body.style.overflow = '';
  }

  function submitLeadMagnet() {
    const nameInput = document.getElementById('leadFullName');
    const phoneInput = document.getElementById('leadWhatsApp');
    const emailInput = document.getElementById('leadEmail');

    const name = nameInput ? nameInput.value.trim() : '';
    const phone = phoneInput ? phoneInput.value.trim() : '';
    const email = emailInput ? emailInput.value.trim() : '';

    if (!name) {
      showToastNotification('Por favor ingresa tu nombre completo');
      if (nameInput) nameInput.focus();
      return;
    }
    const cleanPhone = phone.replace(/\D/g, '');
    if (cleanPhone.length < 10) {
      showToastNotification('Ingresa un número de WhatsApp válido (10 dígitos)');
      if (phoneInput) phoneInput.focus();
      return;
    }
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      showToastNotification('Ingresa un correo electrónico válido');
      if (emailInput) emailInput.focus();
      return;
    }

    const campaign = LEAD_CAMPAIGNS[activeLeadCampaignId] || LEAD_CAMPAIGNS['PARADISO_VIP'];
    const randomSeq = Math.floor(1000 + Math.random() * 9000);
    const voucherFolio = `#VCH-2026-${randomSeq}`;

    const leadRecord = {
      folio: voucherFolio,
      campaignId: campaign.id,
      campaignName: campaign.title,
      ally: campaign.ally,
      benefit: campaign.benefit,
      associatedCoupon: campaign.associatedCoupon,
      name: name,
      phone: cleanPhone,
      email: email,
      createdAt: new Date().toISOString(),
      status: 'active'
    };

    try {
      const leads = JSON.parse(localStorage.getItem('multiverse_leads') || '[]');
      leads.unshift(leadRecord);
      localStorage.setItem('multiverse_leads', JSON.stringify(leads));
    } catch(e) {}

    // Renderizar Voucher Card
    const voucherCard = document.getElementById('leadMagnetVoucherCard');
    const captureView = document.getElementById('leadMagnetCaptureView');

    if (voucherCard) {
      voucherCard.innerHTML = `
        <div class="voucher-ticket-header">
          <div>
            <div class="voucher-partner-title">${campaign.ally}</div>
            <div class="voucher-folio-display">${voucherFolio}</div>
          </div>
          <span class="voucher-status-badge">ACTIVO</span>
        </div>

        <div class="voucher-qr-box">
          <svg class="voucher-qr-svg" viewBox="0 0 100 100" width="130" height="130">
            <rect width="100" height="100" fill="#ffffff"/>
            <!-- Patrón QR SVG Sintético Estilizado -->
            <rect x="10" y="10" width="26" height="26" fill="#09090b" rx="2"/>
            <rect x="14" y="14" width="18" height="18" fill="#ffffff" rx="1"/>
            <rect x="18" y="18" width="10" height="10" fill="#09090b"/>

            <rect x="64" y="10" width="26" height="26" fill="#09090b" rx="2"/>
            <rect x="68" y="14" width="18" height="18" fill="#ffffff" rx="1"/>
            <rect x="72" y="18" width="10" height="10" fill="#09090b"/>

            <rect x="10" y="64" width="26" height="26" fill="#09090b" rx="2"/>
            <rect x="14" y="68" width="18" height="18" fill="#ffffff" rx="1"/>
            <rect x="18" y="72" width="10" height="10" fill="#09090b"/>

            <rect x="42" y="12" width="6" height="14" fill="#09090b"/>
            <rect x="52" y="18" width="6" height="8" fill="#09090b"/>
            <rect x="42" y="32" width="16" height="6" fill="#09090b"/>
            <rect x="42" y="42" width="16" height="16" fill="#09090b"/>
            <rect x="12" y="42" width="8" height="16" fill="#09090b"/>
            <rect x="24" y="48" width="12" height="6" fill="#09090b"/>
            <rect x="64" y="42" width="10" height="8" fill="#09090b"/>
            <rect x="78" y="42" width="12" height="18" fill="#09090b"/>
            <rect x="64" y="66" width="8" height="24" fill="#09090b"/>
            <rect x="76" y="66" width="14" height="8" fill="#09090b"/>
            <rect x="76" y="80" width="14" height="10" fill="#09090b"/>
            <rect x="42" y="64" width="8" height="12" fill="#09090b"/>
            <rect x="52" y="78" width="6" height="12" fill="#09090b"/>
          </svg>
          <div style="font-family: monospace; font-size: 0.72rem; color: var(--text-secondary); margin-top: 6px; font-weight: 700;">
            FOLIO UNICO: ${voucherFolio}
          </div>
        </div>

        <div class="voucher-details-body">
          <div class="voucher-benefit-highlight">${campaign.benefit}</div>
          <div class="voucher-meta-line"><span>Beneficiario:</span> <strong>${name}</strong></div>
          <div class="voucher-meta-line"><span>WhatsApp:</span> <span>${cleanPhone}</span></div>
          <div class="voucher-meta-line"><span>Vigencia:</span> <span>30 días a partir de hoy</span></div>
        </div>

        <div class="voucher-actions-footer">
          <button type="button" class="btn-voucher-apply" data-action="apply-lead-magnet-coupon" data-coupon="${campaign.associatedCoupon}">
            APLICAR CUPÓN ${campaign.associatedCoupon} EN LA TIENDA
          </button>
          <button type="button" class="btn-voucher-close" data-action="close-lead-magnet">
            Cerrar Ventana
          </button>
        </div>
      `;
    }

    if (captureView) captureView.style.display = 'none';
    if (voucherCard) voucherCard.style.display = 'block';

    showToastNotification(`Voucher digital ${voucherFolio} generado con éxito`);
  }

  // ============================================================================
  // CHECKOUT EN 4 PASOS D2C (TASK-STR-03 & TASK-STR-05)
  // Formas de Pago: SPEI BBVA, Tarjetas Débito/Crédito, OXXO Pay
  // Registro de Compradores, Flete Courier Express ($149 / Gratis >= $999)
  // ============================================================================
  const checkoutState = {
    step: 1,
    cartType: 'ready', // 'ready' | 'on_demand' | 'mixed'
    dispatchStrategy: 'consolidated', // 'consolidated' | 'split'
    customer: {
      name: '',
      phone: '',
      email: ''
    },
    shipping: {
      postalCode: '',
      state: '',
      city: '',
      colony: '',
      street: '',
      references: ''
    },
    createAccount: true,
    paymentMethod: 'spei_bbva', // 'spei_bbva' | 'credit_debit_card' | 'oxxo_pay'
    card: {
      number: '',
      expiry: '',
      cvv: '',
      holder: ''
    },
    lastConfirmedOrder: null
  };

  // Clasificación de Tipo de Carrito (Stock Físico vs On-Demand vs Mixto)
  function getCartType(cart) {
    const items = cart || storeState.cart;
    if (!items || items.length === 0) return 'ready';
    const hasReady = items.some(i => (i.stockType || 'ready') === 'ready');
    const hasOnDemand = items.some(i => i.stockType === 'on_demand');
    if (hasReady && hasOnDemand) return 'mixed';
    if (hasOnDemand) return 'on_demand';
    return 'ready';
  }

  // Bifurcación de Líneas de Carrito (Línea A: Bodega vs Línea B: Taller Confección)
  function getCartLines(cart) {
    const items = cart || storeState.cart;
    const readyItems = items.filter(i => (i.stockType || 'ready') === 'ready');
    const onDemandItems = items.filter(i => i.stockType === 'on_demand');
    return { readyItems, onDemandItems };
  }

  // Conmutador de Estrategia de Despacho para Carrito Mixto
  function switchDispatchStrategy(strategy) {
    checkoutState.dispatchStrategy = (strategy === 'split') ? 'split' : 'consolidated';

    const stratCons = document.getElementById('strategyConsolidated');
    const stratSplit = document.getElementById('strategySplit');
    if (stratCons) stratCons.checked = (checkoutState.dispatchStrategy === 'consolidated');
    if (stratSplit) stratSplit.checked = (checkoutState.dispatchStrategy === 'split');

    stratCons?.closest('.mixed-option-card')?.classList.toggle('active', checkoutState.dispatchStrategy === 'consolidated');
    stratSplit?.closest('.mixed-option-card')?.classList.toggle('active', checkoutState.dispatchStrategy === 'split');

    updateCheckoutSummary();
  }

  // Renderizar la Interfaz de Tipo de Carrito en Checkout
  function renderCheckoutCartTypeUI() {
    checkoutState.cartType = getCartType(storeState.cart);
    const noticeEl = document.getElementById('checkoutCartTypeNotice');
    const strategyCard = document.getElementById('mixedCartStrategyCard');
    const breakdownEl = document.getElementById('mixedCartLinesBreakdown');

    if (!noticeEl) return;

    if (checkoutState.cartType === 'mixed') {
      noticeEl.style.display = 'block';
      noticeEl.className = 'checkout-cart-type-notice type-notice-mixed';
      noticeEl.innerHTML = `
        <div class="cart-type-badge-row">
          <span class="badge-cart-type type-mixed">CARRITO MIXTO HIBRIDO</span>
          <span class="type-pill-ready">Bodega Inmediata</span>
          <span class="type-pill-demand">Taller Confeccion</span>
        </div>
        <p class="cart-type-desc">
          Tu pedido combina <strong>prendas en bodega listas para entrega express</strong> y <strong>prendas confeccionadas bajo pedido (3-5 dias habiles)</strong>. Revisa la estrategia de despacho a continuacion.
        </p>
      `;

      if (strategyCard) strategyCard.style.display = 'block';

      const { readyItems, onDemandItems } = getCartLines(storeState.cart);
      if (breakdownEl) {
        breakdownEl.innerHTML = `
          <div class="mixed-lines-grid">
            <div class="mixed-line-col">
              <strong class="line-title line-title-ready">[LINEA A] STOCK FISICO EN BODEGA</strong>
              <div class="line-items">
                ${readyItems.map(it => `<div>- ${it.quantity}x ${it.name} (${it.size})</div>`).join('')}
              </div>
              <span class="line-timing">Despacho express courier: 24 a 48 hrs</span>
            </div>
            <div class="mixed-line-col">
              <strong class="line-title line-title-demand">[LINEA B] CONFECCION BAJO PEDIDO</strong>
              <div class="line-items">
                ${onDemandItems.map(it => `<div>- ${it.quantity}x ${it.name} (${it.size})</div>`).join('')}
              </div>
              <span class="line-timing">Tiempo estimado en taller: 3 a 5 dias habiles</span>
            </div>
          </div>
        `;
      }
    } else if (checkoutState.cartType === 'on_demand') {
      noticeEl.style.display = 'block';
      noticeEl.className = 'checkout-cart-type-notice type-notice-demand';
      noticeEl.innerHTML = `
        <div class="cart-type-badge-row">
          <span class="badge-cart-type type-demand">SOLICITUD ON-DEMAND</span>
          <span class="type-pill-demand">Taller Confeccion</span>
        </div>
        <p class="cart-type-desc">
          Todas las prendas de tu pedido se elaboran <strong>bajo pedido artesanalmente</strong>. Tiempo estimado de confeccion previo al despacho courier: <strong>3 a 5 dias habiles</strong>.
        </p>
      `;
      if (strategyCard) strategyCard.style.display = 'none';
    } else {
      noticeEl.style.display = 'block';
      noticeEl.className = 'checkout-cart-type-notice type-notice-ready';
      noticeEl.innerHTML = `
        <div class="cart-type-badge-row">
          <span class="badge-cart-type type-ready">STOCK FISICO INMEDIATO</span>
          <span class="type-pill-ready">Bodega Central</span>
        </div>
        <p class="cart-type-desc">
          Todas las prendas seleccionadas cuentan con <strong>disponibilidad inmediata en bodega</strong> para despacho courier express (24 a 48 horas).
        </p>
      `;
      if (strategyCard) strategyCard.style.display = 'none';
    }
  }

  // Pre-llenar sesión si existe usuario registrado
  function loadBuyerSession() {
    try {
      const sess = localStorage.getItem('multiverse_customer_session');
      if (sess) {
        const data = JSON.parse(sess);
        if (data.name) checkoutState.customer.name = data.name;
        if (data.phone) checkoutState.customer.phone = data.phone;
        if (data.email) checkoutState.customer.email = data.email;
        if (data.shipping) {
          checkoutState.shipping = { ...checkoutState.shipping, ...data.shipping };
        }
      }
    } catch (e) {}
  }

  function openCheckoutModal() {
    if (storeState.cart.length === 0) {
      showToastNotification('Tu carrito está vacío. Agrega prendas para continuar.');
      return;
    }

    closeAllDrawers();
    loadBuyerSession();

    // Llenar campos si hay datos
    const nameEl = document.getElementById('checkoutCustomerName');
    if (nameEl && checkoutState.customer.name) nameEl.value = checkoutState.customer.name;
    const phoneEl = document.getElementById('checkoutCustomerPhone');
    if (phoneEl && checkoutState.customer.phone) phoneEl.value = checkoutState.customer.phone;
    const emailEl = document.getElementById('checkoutCustomerEmail');
    if (emailEl && checkoutState.customer.email) emailEl.value = checkoutState.customer.email;

    const zipEl = document.getElementById('checkoutShippingZip');
    if (zipEl && checkoutState.shipping.postalCode) zipEl.value = checkoutState.shipping.postalCode;
    const stateEl = document.getElementById('checkoutShippingState');
    if (stateEl && checkoutState.shipping.state) stateEl.value = checkoutState.shipping.state;
    const cityEl = document.getElementById('checkoutShippingCity');
    if (cityEl && checkoutState.shipping.city) cityEl.value = checkoutState.shipping.city;
    const colEl = document.getElementById('checkoutShippingColony');
    if (colEl && checkoutState.shipping.colony) colEl.value = checkoutState.shipping.colony;
    const streetEl = document.getElementById('checkoutShippingStreet');
    if (streetEl && checkoutState.shipping.street) streetEl.value = checkoutState.shipping.street;
    const refsEl = document.getElementById('checkoutShippingRefs');
    if (refsEl && checkoutState.shipping.references) refsEl.value = checkoutState.shipping.references;

    renderCheckoutCartTypeUI();
    updateCheckoutSummary();
    renderCouponBoxes();
    goToCheckoutStep(1);
    updateStockLockDisplay();
    checkPushPromptTrigger();

    const modal = document.getElementById('checkoutModal');
    const backdrop = document.getElementById('checkoutModalBackdrop');
    if (modal) modal.classList.add('show');
    if (backdrop) backdrop.classList.add('show');
    document.body.style.overflow = 'hidden';
  }

  function closeCheckoutModal() {
    const modal = document.getElementById('checkoutModal');
    const backdrop = document.getElementById('checkoutModalBackdrop');
    if (modal) modal.classList.remove('show');
    if (backdrop) backdrop.classList.remove('show');
    document.body.style.overflow = '';
  }

  function updateCheckoutSummary() {
    const rawSubtotal = storeState.cart.reduce((acc, i) => acc + (i.price * i.quantity), 0);
    const discountAmount = (couponState.appliedCoupon && couponState.discountPct > 0)
      ? Math.round(rawSubtotal * (couponState.discountPct / 100) * 100) / 100
      : 0;
    couponState.discountAmount = discountAmount;
    const subtotal = Math.max(0, rawSubtotal - discountAmount);

    const isSplit = (checkoutState.cartType === 'mixed' && checkoutState.dispatchStrategy === 'split');
    const baseShipping = (subtotal >= FREE_SHIPPING_THRESHOLD) ? 0 : SHIPPING_COST;
    const splitShipping = isSplit ? SHIPPING_COST : 0;
    const shippingCost = baseShipping + splitShipping;
    const total = subtotal + shippingCost;

    const subEl = document.getElementById('chkSubtotalAmount');
    if (subEl) {
      if (discountAmount > 0) {
        subEl.innerHTML = `<span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.8rem; margin-right: 4px;">$${rawSubtotal.toFixed(2)}</span> MXN $${subtotal.toFixed(2)} <span style="color: var(--accent-emerald); font-weight: 700; font-size: 0.75rem;">(-$${discountAmount.toFixed(2)})</span>`;
      } else {
        subEl.textContent = `MXN $${subtotal.toFixed(2)}`;
      }
    }

    const shipEl = document.getElementById('chkShippingAmount');
    if (shipEl) {
      if (isSplit) {
        if (baseShipping === 0) {
          shipEl.textContent = `MXN $${shippingCost.toFixed(2)} (Split: 2do Paquete)`;
        } else {
          shipEl.textContent = `MXN $${shippingCost.toFixed(2)} (Split: 2 Paquetes)`;
        }
      } else {
        shipEl.textContent = (shippingCost === 0) ? 'GRATIS ($0.00 MXN)' : `MXN $${shippingCost.toFixed(2)}`;
      }
    }

    const totEl = document.getElementById('chkTotalAmount');
    if (totEl) totEl.textContent = `MXN $${total.toFixed(2)}`;

    // Actualizar leyendas en el selector de estrategia de flete si existe
    const consLabel = document.getElementById('consolidatedFeeLabel');
    if (consLabel) {
      consLabel.textContent = (subtotal >= FREE_SHIPPING_THRESHOLD)
        ? 'Envio Courier Consolidado: GRATIS (Subtotal >= $999 MXN)'
        : 'Envio Courier Consolidado: $149.00 MXN';
    }
    const splitLabel = document.getElementById('splitFeeLabel');
    if (splitLabel) {
      splitLabel.textContent = (subtotal >= FREE_SHIPPING_THRESHOLD)
        ? 'Paquete 1 (Stock): GRATIS + Paquete 2 (Taller): $149.00 MXN = $149.00 MXN'
        : 'Paquete 1 (Stock): $149.00 MXN + Paquete 2 (Taller): $149.00 MXN = $298.00 MXN';
    }
  }

  function goToCheckoutStep(stepNumber) {
    checkoutState.step = stepNumber;

    // Actualizar Stepper
    document.querySelectorAll('.checkout-stepper .stepper-step').forEach(stepEl => {
      const s = parseInt(stepEl.dataset.step, 10);
      stepEl.classList.remove('active', 'completed');
      if (s === stepNumber) {
        stepEl.classList.add('active');
      } else if (s < stepNumber) {
        stepEl.classList.add('completed');
      }
    });

    // Ocultar/mostrar paneles (4 Pasos)
    const p1 = document.getElementById('checkoutStep1');
    const p2 = document.getElementById('checkoutStep2');
    const p3 = document.getElementById('checkoutStep3');
    const p4 = document.getElementById('checkoutStep4');

    if (p1) p1.style.display = (stepNumber === 1) ? 'block' : 'none';
    if (p2) p2.style.display = (stepNumber === 2) ? 'block' : 'none';
    if (p3) p3.style.display = (stepNumber === 3) ? 'block' : 'none';
    if (p4) p4.style.display = (stepNumber === 4) ? 'block' : 'none';

    const scrollContainer = document.getElementById('checkoutContentScroll');
    if (scrollContainer) scrollContainer.scrollTop = 0;
  }

  function validateCheckoutStep1() {
    const name = (document.getElementById('checkoutCustomerName')?.value || '').trim();
    const rawPhone = (document.getElementById('checkoutCustomerPhone')?.value || '').replace(/\D/g, '');
    const email = (document.getElementById('checkoutCustomerEmail')?.value || '').trim();
    const rawZip = (document.getElementById('checkoutShippingZip')?.value || '').replace(/\D/g, '');
    const state = (document.getElementById('checkoutShippingState')?.value || '').trim();
    const city = (document.getElementById('checkoutShippingCity')?.value || '').trim();
    const colony = (document.getElementById('checkoutShippingColony')?.value || '').trim();
    const street = (document.getElementById('checkoutShippingStreet')?.value || '').trim();
    const refs = (document.getElementById('checkoutShippingRefs')?.value || '').trim();
    const createAcc = document.getElementById('checkoutCreateAccount')?.checked ?? true;

    if (!name) {
      showToastNotification('Ingresa tu nombre completo.');
      return false;
    }
    if (rawPhone.length !== 10) {
      showToastNotification('Ingresa un teléfono celular válido de 10 dígitos.');
      return false;
    }
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      showToastNotification('Ingresa un correo electrónico válido.');
      return false;
    }
    if (rawZip.length !== 5) {
      showToastNotification('Ingresa un código postal de 5 dígitos.');
      return false;
    }
    if (!state || !city || !colony || !street) {
      showToastNotification('Por favor completa todos los campos de tu dirección.');
      return false;
    }

    checkoutState.customer = { name, phone: rawPhone, email };
    checkoutState.shipping = { postalCode: rawZip, state, city, colony, street, references: refs };
    checkoutState.createAccount = createAcc;

    return true;
  }

  function switchCheckoutPaymentMethod(methodKey) {
    checkoutState.paymentMethod = methodKey;

    document.querySelectorAll('.payment-method-card').forEach(card => {
      const radio = card.querySelector('input[type="radio"]');
      const isSelected = (radio && radio.value === methodKey);
      card.classList.toggle('active', isSelected);
      if (radio) radio.checked = isSelected;
    });

    const speiBox = document.getElementById('speiDetailsArea');
    const cardBox = document.getElementById('cardDetailsArea');
    const oxxoBox = document.getElementById('oxxoDetailsArea');

    if (speiBox) speiBox.style.display = (methodKey === 'spei_bbva') ? 'block' : 'none';
    if (cardBox) cardBox.style.display = (methodKey === 'credit_debit_card') ? 'block' : 'none';
    if (oxxoBox) oxxoBox.style.display = (methodKey === 'oxxo_pay') ? 'block' : 'none';
  }

  function validateCheckoutStep2() {
    if (checkoutState.paymentMethod === 'credit_debit_card') {
      const cardNum = (document.getElementById('checkoutCardNumber')?.value || '').replace(/\D/g, '');
      const expiry = (document.getElementById('checkoutCardExpiry')?.value || '').trim();
      const cvv = (document.getElementById('checkoutCardCvv')?.value || '').trim();
      const holder = (document.getElementById('checkoutCardHolder')?.value || '').trim();

      if (cardNum.length < 15 || cardNum.length > 19) {
        showToastNotification('Ingresa un número de tarjeta válido.');
        return false;
      }
      if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiry)) {
        showToastNotification('Ingresa una fecha de vencimiento válida (MM/AA).');
        return false;
      }
      if (cvv.length < 3 || cvv.length > 4) {
        showToastNotification('Ingresa un código de seguridad (CVV) válido.');
        return false;
      }
      if (!holder) {
        showToastNotification('Ingresa el nombre del titular como aparece en la tarjeta.');
        return false;
      }

      checkoutState.card = { number: cardNum, expiry, cvv, holder };
    }
    return true;
  }

  function confirmCheckoutOrder() {
    if (!validateCheckoutStep2()) return;

    // 1. Detener temporizador de 5 minutos (Stock asegurado)
    if (stockLockInterval) {
      clearInterval(stockLockInterval);
      stockLockInterval = null;
    }

    // 2. Cálculos financieros oficiales considerando carritos mixtos y cupones
    const rawSubtotal = storeState.cart.reduce((acc, i) => acc + (i.price * i.quantity), 0);
    const discountAmount = (couponState.appliedCoupon && couponState.discountPct > 0)
      ? Math.round(rawSubtotal * (couponState.discountPct / 100) * 100) / 100
      : 0;
    const subtotal = Math.max(0, rawSubtotal - discountAmount);
    const cartType = getCartType(storeState.cart);
    const isSplit = (cartType === 'mixed' && checkoutState.dispatchStrategy === 'split');
    const baseShipping = (subtotal >= FREE_SHIPPING_THRESHOLD) ? 0 : SHIPPING_COST;
    const splitShippingCost = isSplit ? SHIPPING_COST : 0;
    const shippingCost = baseShipping + splitShippingCost;
    const total = subtotal + shippingCost;
    const vat = Math.round((total - (total / 1.16)) * 100) / 100;

    // 3. Generar Folio Inmutable (Prefijo #MIX para carritos mixtos, #ORD para estándar)
    const randomSeq = Math.floor(1000 + Math.random() * 9000);
    const folioPrefix = (cartType === 'mixed') ? '#MIX' : '#ORD';
    const folio = `${folioPrefix}-2026-${randomSeq}`;

    // 4. Parámetros de pago específicos
    let oxxoRef = null;
    let cardLast4 = null;

    if (checkoutState.paymentMethod === 'oxxo_pay') {
      oxxoRef = `${Math.floor(1000 + Math.random() * 9000)}-${Math.floor(1000 + Math.random() * 9000)}-${Math.floor(1000 + Math.random() * 9000)}-${Math.floor(10 + Math.random() * 90)}`;
    } else if (checkoutState.paymentMethod === 'credit_debit_card') {
      cardLast4 = (checkoutState.card.number || '0000').slice(-4);
    }

    const { readyItems, onDemandItems } = getCartLines(storeState.cart);

    // 5. Crear objeto inmutable de Pedido
    const order = {
      folio: folio,
      orderType: cartType, // 'ready' | 'on_demand' | 'mixed'
      dispatchStrategy: (cartType === 'mixed') ? checkoutState.dispatchStrategy : 'standard',
      createdAt: new Date().toISOString(),
      universe: storeState.activeUniverse,
      customer: { ...checkoutState.customer },
      shipping: { ...checkoutState.shipping },
      carrier: (cartType === 'mixed' && isSplit)
        ? 'Paqueteria Express (2 Guias Courier Separadas)'
        : 'Paqueteria Express Nacional (Cero Metro)',
      paymentMethod: checkoutState.paymentMethod,
      rawSubtotal: rawSubtotal,
      couponCode: couponState.appliedCoupon,
      discountPct: couponState.discountPct,
      discountAmount: discountAmount,
      attributedAlly: couponState.attributedAlly,
      subtotal: subtotal,
      shippingCost: shippingCost,
      splitShippingCost: splitShippingCost,
      vat: vat,
      total: total,
      items: JSON.parse(JSON.stringify(storeState.cart)),
      readyItems: JSON.parse(JSON.stringify(readyItems)),
      onDemandItems: JSON.parse(JSON.stringify(onDemandItems)),
      oxxoReference: oxxoRef,
      cardLast4: cardLast4,
      status: (checkoutState.paymentMethod === 'credit_debit_card') ? 'approved' : 'pending_confirmation'
    };

    checkoutState.lastConfirmedOrder = order;

    // 6. Guardar en localStorage de pedidos del cliente
    try {
      const orders = JSON.parse(localStorage.getItem('multiverse_customer_orders') || '[]');
      orders.unshift(order);
      localStorage.setItem('multiverse_customer_orders', JSON.stringify(orders));
    } catch (e) {}

    // 7. Registro de Cuenta de Comprador si se selecciono
    if (checkoutState.createAccount) {
      try {
        const buyerSession = {
          name: checkoutState.customer.name,
          email: checkoutState.customer.email,
          phone: checkoutState.customer.phone,
          shipping: { ...checkoutState.shipping },
          registeredAt: new Date().toISOString(),
          universe: storeState.activeUniverse
        };
        localStorage.setItem('multiverse_customer_session', JSON.stringify(buyerSession));
      } catch (e) {}
    }

    // 8. Renderizar Paso 4 (Confirmacion)
    const folioBadge = document.getElementById('orderConfirmedFolio');
    if (folioBadge) folioBadge.textContent = folio;

    const paymentBox = document.getElementById('confirmedPaymentBox');
    if (paymentBox) {
      if (checkoutState.paymentMethod === 'spei_bbva') {
        paymentBox.innerHTML = `
          <div style="background: var(--bg-secondary); border: 1px solid var(--border-medium); border-radius: var(--radius-sm); padding: 14px;">
            <strong style="font-family: var(--font-heading); font-size: 0.85rem; display: block; margin-bottom: 6px;">
              Instrucciones de Transferencia SPEI
            </strong>
            <div style="font-size: 0.78rem; line-height: 1.5; color: var(--text-secondary);">
              <div>Banco: <strong style="color: #050505;">BBVA México</strong></div>
              <div>Beneficiario: <strong style="color: #050505;">Miguel Arteaga</strong></div>
              <div>CLABE: <strong style="color: #050505; font-family: monospace; font-size: 0.9rem;">012180004708283787</strong></div>
              <div>Monto Exacto: <strong style="color: #050505;">MXN $${total.toFixed(2)}</strong></div>
            </div>
            <button type="button" class="btn-copy-clabe" data-action="copy-clabe" style="margin-top: 10px; width: 100%;">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              <span>Copiar CLABE Interbancaria</span>
            </button>
          </div>
        `;
      } else if (checkoutState.paymentMethod === 'credit_debit_card') {
        paymentBox.innerHTML = `
          <div style="background: var(--accent-emerald-soft); border: 1px solid var(--accent-emerald-border); border-radius: var(--radius-sm); padding: 14px;">
            <strong style="font-family: var(--font-heading); font-size: 0.85rem; color: var(--accent-emerald); display: block; margin-bottom: 4px;">
              Pago con Tarjeta Aprobado
            </strong>
            <p style="font-size: 0.75rem; color: #065f46; margin: 0; line-height: 1.4;">
              El cargo por MXN $${total.toFixed(2)} fue autorizado con tu tarjeta terminación <strong>${cardLast4}</strong>. Recibirás confirmación y guía de rastreo en tu correo.
            </p>
          </div>
        `;
      } else if (checkoutState.paymentMethod === 'oxxo_pay') {
        paymentBox.innerHTML = `
          <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: var(--radius-sm); padding: 14px; text-align: center;">
            <strong style="font-family: var(--font-heading); font-size: 0.82rem; color: #92400e; display: block; margin-bottom: 6px;">
              Referencia Digital OXXO Pay
            </strong>
            <div style="font-family: monospace; font-size: 1.25rem; font-weight: 800; letter-spacing: 0.08em; color: #78350f; background: #ffffff; padding: 10px; border-radius: 6px; border: 1px dashed #d97706; margin-bottom: 8px;">
              ${oxxoRef}
            </div>
            <p style="font-size: 0.72rem; color: #92400e; margin: 0; line-height: 1.4;">
              Indica en caja que realizarás un pago OXXO Pay con esta referencia. Tu stock de prendas queda apartado por <strong>48 horas</strong>.
            </p>
          </div>
        `;
      }
    }

    // 9. Desglose de Operaciones para Carrito Mixto Confirmado
    const confirmedMixedEl = document.getElementById('confirmedMixedNotice');
    if (confirmedMixedEl) {
      if (cartType === 'mixed') {
        confirmedMixedEl.style.display = 'block';
        confirmedMixedEl.innerHTML = `
          <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 12px; margin-bottom: 14px;">
            <strong style="font-family: var(--font-heading); font-size: 0.8rem; color: #1e40af; display: block; margin-bottom: 4px;">
              LOGISTICA DE CARRITO MIXTO // BIFURCACION CONFIRMADA
            </strong>
            <p style="font-size: 0.72rem; color: #1e3a8a; margin: 0 0 8px; line-height: 1.35;">
              Estrategia: <strong>${checkoutState.dispatchStrategy === 'split' ? 'Despacho Dividido (2 Guias de Rastreo Courier)' : 'Despacho Consolidado (1 Guia Courier Consolidada)'}</strong>
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 0.7rem;">
              <div style="background: #ffffff; border: 1px solid #dbeafe; border-radius: 4px; padding: 8px;">
                <span style="display: block; font-weight: 700; color: #047857; margin-bottom: 2px;">[LINEA A] STOCK FISICO BODEGA</span>
                <span style="color: var(--text-secondary);">${readyItems.length} prenda(s) - Salida en 24-48h</span>
              </div>
              <div style="background: #ffffff; border: 1px solid #dbeafe; border-radius: 4px; padding: 8px;">
                <span style="display: block; font-weight: 700; color: #b45309; margin-bottom: 2px;">[LINEA B] TALLER ON-DEMAND</span>
                <span style="color: var(--text-secondary);">${onDemandItems.length} prenda(s) - Confeccion: 3-5 dias</span>
              </div>
            </div>
          </div>
        `;
      } else {
        confirmedMixedEl.style.display = 'none';
      }
    }

    const subEl = document.getElementById('confirmedSubtotal');
    if (subEl) {
      if (discountAmount > 0) {
        subEl.innerHTML = `<span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.75rem; margin-right: 4px;">$${rawSubtotal.toFixed(2)}</span> MXN $${subtotal.toFixed(2)} <span style="color: var(--accent-emerald); font-weight: 700; font-size: 0.75rem;">(-$${discountAmount.toFixed(2)} ${couponState.appliedCoupon})</span>`;
      } else {
        subEl.textContent = `MXN $${subtotal.toFixed(2)}`;
      }
    }
    const shipEl = document.getElementById('confirmedShipping');
    if (shipEl) {
      if (isSplit) {
        shipEl.textContent = `MXN $${shippingCost.toFixed(2)} (Split: 2 Paquetes)`;
      } else {
        shipEl.textContent = (shippingCost === 0) ? 'GRATIS ($0.00 MXN)' : `MXN $${shippingCost.toFixed(2)}`;
      }
    }
    const vatEl = document.getElementById('confirmedVat');
    if (vatEl) vatEl.textContent = `MXN $${vat.toFixed(2)}`;
    const totEl = document.getElementById('confirmedTotal');
    if (totEl) totEl.textContent = `MXN $${total.toFixed(2)}`;

    const itemsContainer = document.getElementById('confirmedItemsList');
    if (itemsContainer) {
      itemsContainer.innerHTML = order.items.map(it => `
        <div style="display: flex; justify-content: space-between; font-size: 0.76rem; padding: 4px 0; border-bottom: 1px solid var(--border-subtle);">
          <span>${it.quantity}x ${it.name} (Talla ${it.size}) <small style="color: var(--text-muted);">[${it.stockType === 'on_demand' ? 'Bajo Pedido' : 'Stock Fisico'}]</small></span>
          <strong>MXN $${(it.price * it.quantity).toFixed(2)}</strong>
        </div>
      `).join('');
    }

    const addrEl = document.getElementById('confirmedShippingAddress');
    if (addrEl) {
      addrEl.textContent = `${checkoutState.shipping.street}, Col. ${checkoutState.shipping.colony}, ${checkoutState.shipping.city}, ${checkoutState.shipping.state} CP ${checkoutState.shipping.postalCode}.`;
    }

    // 10. Limpiar Carrito, Reserva de Stock y Cupón
    storeState.cart = [];
    notifyStockLockReleased();
    couponState.appliedCoupon = null;
    couponState.discountPct = 0;
    couponState.discountAmount = 0;
    couponState.attributedAlly = null;
    saveCart();
    updateCartBadges();
    renderCartSheetContent();
    renderCouponBoxes();

    // 11. Conmutar a vista de paso 4 (Confirmacion Oficial)
    goToCheckoutStep(4);
    showToastNotification('Pedido generado y stock apartado con exito');

    // 12. Notificación Web Push de Confirmación y Ruta Courier
    if (pushNotificationState.preferences.order_tracking) {
      setTimeout(() => {
        sendSimulatedPush('courier_dispatch');
      }, 1200);
    }
  }

  function lookupPostalCode(zip) {
    const cleanZip = (zip || '').replace(/\D/g, '');
    if (cleanZip.length !== 5) {
      showToastNotification('Ingresa un código postal válido de 5 dígitos.');
      return;
    }

    const knownCodes = {
      '06000': { state: 'Ciudad de México', city: 'Cuauhtémoc', colony: 'Centro' },
      '01000': { state: 'Ciudad de México', city: 'Álvaro Obregón', colony: 'San Ángel' },
      '03100': { state: 'Ciudad de México', city: 'Benito Juárez', colony: 'Del Valle' },
      '11000': { state: 'Ciudad de México', city: 'Miguel Hidalgo', colony: 'Lomas de Chapultepec' },
      '44100': { state: 'Jalisco', city: 'Guadalajara', colony: 'Americana' },
      '64000': { state: 'Nuevo León', city: 'Monterrey', colony: 'Centro' },
      '72000': { state: 'Puebla', city: 'Puebla', colony: 'Centro' },
      '76000': { state: 'Querétaro', city: 'Querétaro', colony: 'Centro' }
    };

    const match = knownCodes[cleanZip];
    if (match) {
      const stateInput = document.getElementById('checkoutShippingState');
      const cityInput = document.getElementById('checkoutShippingCity');
      const colInput = document.getElementById('checkoutShippingColony');
      if (stateInput) stateInput.value = match.state;
      if (cityInput) cityInput.value = match.city;
      if (colInput) colInput.value = match.colony;
      showToastNotification(`CP ${cleanZip}: Cobertura Express Verificada`);
    } else {
      showToastNotification(`CP ${cleanZip}: Cobertura Courier Nacional Confirmada`);
    }
  }

  function copyClabeToClipboard() {
    const clabe = '012180004708283787';
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(clabe).then(() => {
        showToastNotification('CLABE copiada al portapapeles');
        const lbl = document.getElementById('copyClabeLabel');
        if (lbl) {
          lbl.textContent = '¡CLABE Copiada!';
          setTimeout(() => { lbl.textContent = 'Copiar CLABE en 1 Tap'; }, 2000);
        }
      }).catch(() => {
        showToastNotification(`CLABE: ${clabe}`);
      });
    } else {
      showToastNotification(`CLABE: ${clabe}`);
    }
  }

  // ============================================================================
  // PASO 3 CHECKOUT: RESUMEN INTEGRAL DE COMPRA PREVIO A CONFIRMACION
  // ============================================================================
  function renderCheckoutStep3Review() {
    const container = document.getElementById('checkoutReviewArea');
    if (!container) return;

    const rawSubtotal = storeState.cart.reduce((acc, i) => acc + (i.price * i.quantity), 0);
    const discountAmount = (couponState.appliedCoupon && couponState.discountPct > 0)
      ? Math.round(rawSubtotal * (couponState.discountPct / 100) * 100) / 100
      : 0;
    couponState.discountAmount = discountAmount;
    const subtotal = Math.max(0, rawSubtotal - discountAmount);
    const cartType = getCartType(storeState.cart);
    const isSplit = (cartType === 'mixed' && checkoutState.dispatchStrategy === 'split');
    const baseShipping = (subtotal >= FREE_SHIPPING_THRESHOLD) ? 0 : SHIPPING_COST;
    const splitShippingCost = isSplit ? SHIPPING_COST : 0;
    const shippingCost = baseShipping + splitShippingCost;
    const total = subtotal + shippingCost;
    const vat = Math.round((total - (total / 1.16)) * 100) / 100;
    const { readyItems, onDemandItems } = getCartLines(storeState.cart);

    let payLabel = 'Transferencia SPEI BBVA';
    let paySub = 'CLABE Interbancaria BBVA: 012180004708283787 (Sin comision)';
    if (checkoutState.paymentMethod === 'credit_debit_card') {
      const last4 = (checkoutState.card.number || '0000').slice(-4);
      payLabel = 'Tarjeta de Credito / Debito';
      paySub = `Terminacion **** ${last4} | Titular: ${checkoutState.card.holder || checkoutState.customer.name}`;
    } else if (checkoutState.paymentMethod === 'oxxo_pay') {
      payLabel = 'Pago en Efectivo OXXO Pay';
      paySub = 'Referencia digital de 14 digitos (Vigencia 48 horas en ventanilla)';
    }

    container.innerHTML = `
      <!-- Tarjeta 1: Destino Courier & Contacto -->
      <div class="checkout-review-card">
        <div class="review-card-header">
          <div class="review-card-title-group">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <strong>Destino Courier y Contacto</strong>
          </div>
          <button type="button" class="btn-review-edit" data-action="checkout-back-step-1">
            Editar Envio
          </button>
        </div>
        <div class="review-card-body">
          <div class="review-meta-line"><strong>Nombre:</strong> <span>${checkoutState.customer.name}</span></div>
          <div class="review-meta-line"><strong>Telefono:</strong> <span>${checkoutState.customer.phone}</span></div>
          <div class="review-meta-line"><strong>Correo:</strong> <span>${checkoutState.customer.email}</span></div>
          <div class="review-meta-line"><strong>Direccion:</strong> <span>${checkoutState.shipping.street}, Col. ${checkoutState.shipping.colony}, ${checkoutState.shipping.city}, ${checkoutState.shipping.state} CP ${checkoutState.shipping.postalCode}</span></div>
          ${checkoutState.shipping.references ? `<div class="review-meta-line"><strong>Referencias:</strong> <span>${checkoutState.shipping.references}</span></div>` : ''}
        </div>
      </div>

      <!-- Tarjeta 2: Forma de Pago Seleccionada -->
      <div class="checkout-review-card">
        <div class="review-card-header">
          <div class="review-card-title-group">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <strong>Forma de Pago Seleccionada</strong>
          </div>
          <button type="button" class="btn-review-edit" data-action="checkout-back-step-2">
            Editar Pago
          </button>
        </div>
        <div class="review-card-body">
          <div style="font-weight: 700; font-size: 0.82rem; color: #050505;">${payLabel}</div>
          <div style="font-size: 0.74rem; color: var(--text-secondary); margin-top: 2px;">${paySub}</div>
        </div>
      </div>

      <!-- Tarjeta 3: Desglose Operativo de Prendas -->
      <div class="checkout-review-card">
        <div class="review-card-header">
          <div class="review-card-title-group">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            <strong>Desglose de Prendas y Despacho</strong>
          </div>
        </div>
        <div class="review-card-body">
          ${cartType === 'mixed' ? `
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; padding: 8px; margin-bottom: 8px;">
              <span style="font-size: 0.72rem; font-weight: 700; color: #166534; display: block;">
                Estrategia: ${checkoutState.dispatchStrategy === 'split' ? 'Despacho Dividido / Split (2 Paquetes Separados)' : 'Despacho Consolidado Ecologico (1 Solo Paquete)'}
              </span>
            </div>
            <div class="review-lines-breakdown">
              <div class="review-line-box ready">
                <span class="review-line-tag">[LINEA A] STOCK FISICO EN BODEGA</span>
                ${readyItems.map(it => `<div class="review-item-row"><span>${it.quantity}x ${it.name} (${it.size})</span><strong>MXN $${(it.price * it.quantity).toFixed(2)}</strong></div>`).join('')}
                <div class="review-line-timing">Salida express courier en 24-48h</div>
              </div>
              <div class="review-line-box demand">
                <span class="review-line-tag">[LINEA B] CONFECCION BAJO PEDIDO</span>
                ${onDemandItems.map(it => `<div class="review-item-row"><span>${it.quantity}x ${it.name} (${it.size})</span><strong>MXN $${(it.price * it.quantity).toFixed(2)}</strong></div>`).join('')}
                <div class="review-line-timing">Confeccion artesanal en taller: 3-5 dias habiles</div>
              </div>
            </div>
          ` : `
            <div class="review-items-list">
              ${storeState.cart.map(it => `
                <div class="review-item-row">
                  <span>${it.quantity}x ${it.name} (Talla ${it.size}) <small style="color: var(--text-muted);">[${it.stockType === 'on_demand' ? 'Bajo Pedido' : 'Stock Fisico'}]</small></span>
                  <strong>MXN $${(it.price * it.quantity).toFixed(2)}</strong>
                </div>
              `).join('')}
            </div>
          `}
        </div>
      </div>

      <!-- Tarjeta 4: Auditoria Financiera Oficial -->
      <div class="checkout-review-card" style="background: var(--bg-secondary); border-color: var(--border-medium);">
        <div class="review-card-header">
          <div class="review-card-title-group">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <strong>Auditoria Financiera Oficial</strong>
          </div>
        </div>
        <div class="review-card-body">
          <div class="checkout-summary-line">
            <span>Subtotal Prendas</span>
            <span>MXN $${rawSubtotal.toFixed(2)}</span>
          </div>
          ${couponState.appliedCoupon ? `
            <div class="checkout-summary-line" style="color: #059669; font-weight: 700;">
              <span>Descuento Cupón (${couponState.appliedCoupon} - ${couponState.discountPct}%)</span>
              <span>- MXN $${discountAmount.toFixed(2)}</span>
            </div>
            ${couponState.attributedAlly ? `
              <div class="checkout-summary-line" style="font-size: 0.72rem; color: var(--text-muted);">
                <span>Aliado Atribuido</span>
                <span>${couponState.attributedAlly}</span>
              </div>
            ` : ''}
          ` : ''}
          <div class="checkout-summary-line">
            <span>Flete Courier Nacional</span>
            <span>${shippingCost === 0 ? 'GRATIS ($0.00 MXN)' : `MXN $${shippingCost.toFixed(2)}${isSplit ? ' (Split: 2 Paquetes)' : ''}`}</span>
          </div>
          <div class="checkout-summary-line" style="font-size: 0.72rem; color: var(--text-muted);">
            <span>IVA (16% incluido)</span>
            <span>MXN $${vat.toFixed(2)}</span>
          </div>
          <div class="checkout-summary-line total" style="margin-top: 6px; padding-top: 6px; border-top: 1px solid var(--border-medium);">
            <span>Gran Total a Pagar</span>
            <span style="color: #050505; font-size: 0.95rem;">MXN $${total.toFixed(2)}</span>
          </div>
        </div>
      </div>
    `;
  }

  // ============================================================================
  // AUTENTICACION CON GOOGLE OAUTH2 (D2C BUYER AUTH)
  // ============================================================================
  function loginWithGoogle(source) {
    const googleUser = {
      name: 'Carlos Mendoza',
      email: 'carlos.mendoza@gmail.com',
      phone: '5512345678',
      avatar: 'G',
      provider: 'google',
      shipping: {
        postalCode: '06000',
        state: 'Ciudad de México',
        city: 'Cuauhtémoc',
        colony: 'Centro',
        street: 'Av. Juárez 14, Depto 402',
        references: 'Edificio clásico entre Balderas y Revillagigedo'
      },
      registeredAt: new Date().toISOString(),
      universe: storeState.activeUniverse
    };

    try {
      localStorage.setItem('multiverse_customer_session', JSON.stringify(googleUser));
    } catch (e) {}

    checkoutState.customer.name = googleUser.name;
    checkoutState.customer.phone = googleUser.phone;
    checkoutState.customer.email = googleUser.email;
    checkoutState.shipping = { ...googleUser.shipping };

    if (source === 'checkout' || document.getElementById('checkoutCustomerName')) {
      const nameEl = document.getElementById('checkoutCustomerName');
      if (nameEl) nameEl.value = googleUser.name;
      const phoneEl = document.getElementById('checkoutCustomerPhone');
      if (phoneEl) phoneEl.value = googleUser.phone;
      const emailEl = document.getElementById('checkoutCustomerEmail');
      if (emailEl) emailEl.value = googleUser.email;
      const zipEl = document.getElementById('checkoutShippingZip');
      if (zipEl) zipEl.value = googleUser.shipping.postalCode;
      const stateEl = document.getElementById('checkoutShippingState');
      if (stateEl) stateEl.value = googleUser.shipping.state;
      const cityEl = document.getElementById('checkoutShippingCity');
      if (cityEl) cityEl.value = googleUser.shipping.city;
      const colEl = document.getElementById('checkoutShippingColony');
      if (colEl) colEl.value = googleUser.shipping.colony;
      const streetEl = document.getElementById('checkoutShippingStreet');
      if (streetEl) streetEl.value = googleUser.shipping.street;
      const refsEl = document.getElementById('checkoutShippingRefs');
      if (refsEl) refsEl.value = googleUser.shipping.references;
    }

    renderAccountSheetContent();
    showToastNotification('Sesión iniciada con Google.');
  }

  // ============================================================================
  // PORTAL DEL COMPRADOR D2C (TASK-STR-05)
  // Mi Cuenta, Mis Pedidos, Perfil Anatómico, Libreta Direcciones, Club MRR
  // ============================================================================
  const accountState = {
    activeTab: 'orders', // 'orders' | 'anatomy' | 'address' | 'subscription'
    searchedFolio: null,
    editingAddress: false,
    pushEnabled: false
  };

  function switchAccountTab(tabKey) {
    accountState.activeTab = tabKey;
    document.querySelectorAll('.account-tab-btn').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.tab === tabKey);
    });
    document.querySelectorAll('.account-tab-panel').forEach(panel => {
      const match = (panel.id === `accountTab${tabKey.charAt(0).toUpperCase() + tabKey.slice(1)}`);
      panel.classList.toggle('active', match);
      panel.style.display = match ? 'block' : 'none';
    });
    renderAccountSheetContent();
  }

  function renderAccountSheetContent() {
    // 1. Tarjeta de Identidad / Auth
    const authCard = document.getElementById('accountAuthCard');
    let session = null;
    try {
      const raw = localStorage.getItem('multiverse_customer_session');
      if (raw) session = JSON.parse(raw);
    } catch (e) {}

    if (authCard) {
      if (session && session.name) {
        authCard.innerHTML = `
          <div class="auth-logged-card">
            <div class="auth-avatar-circle">${session.name.charAt(0).toUpperCase()}</div>
            <div class="auth-user-info">
              <div class="auth-user-name">${session.name}</div>
              <div class="auth-user-email">${session.email || 'Sin correo registrado'}</div>
              <div class="auth-user-provider">${session.provider === 'google' ? 'Cuenta Google Verificada' : 'Comprador Registrado'}</div>
            </div>
            <button type="button" class="btn-logout-buyer" data-action="buyer-logout">Cerrar Sesión</button>
          </div>
        `;
      } else {
        authCard.innerHTML = `
          <div class="auth-guest-card">
            <div style="font-size: 0.82rem; font-weight: 700; color: #050505; margin-bottom: 4px;">Acceso Rápido de Comprador</div>
            <p style="font-size: 0.74rem; color: var(--text-secondary); margin: 0 0 10px; line-height: 1.35;">Inicia sesión con Google para rastrear tus pedidos, guardar tu perfil anatómico y gestionar tus envíos.</p>
            <button type="button" class="btn-google-auth" data-action="google-login" data-source="account">
              <svg viewBox="0 0 24 24" width="18" height="18" class="google-icon">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
              </svg>
              <span>Continuar con Google</span>
            </button>
          </div>
        `;
      }
    }

    // 2. Contador de Pedidos en Badge de Pestaña
    let orders = [];
    try {
      orders = JSON.parse(localStorage.getItem('multiverse_customer_orders') || '[]');
    } catch (e) {}

    const badgeEl = document.getElementById('accountOrdersBadge');
    if (badgeEl) badgeEl.textContent = orders.length;

    // 3. Renderizar Contenido según Pestaña Activa
    if (accountState.activeTab === 'orders') {
      renderAccountOrders(orders);
    } else if (accountState.activeTab === 'anatomy') {
      renderAccountAnatomy(session);
    } else if (accountState.activeTab === 'address') {
      renderAccountAddress(session);
    } else if (accountState.activeTab === 'subscription') {
      renderAccountSubscription();
    }
  }

  function renderAccountOrders(orders) {
    const listEl = document.getElementById('accountOrdersList');
    if (!listEl) return;

    if (!orders || orders.length === 0) {
      listEl.innerHTML = `
        <div class="account-empty-state">
          <div style="font-weight: 700; font-size: 0.85rem; color: #050505; margin-bottom: 4px;">Sin pedidos registrados</div>
          <p style="font-size: 0.74rem; color: var(--text-secondary); margin-bottom: 12px;">Aún no has realizado compras con esta cuenta.</p>
          <button type="button" class="btn-account-primary" data-action="switch-dock-tab" data-dock-tab="catalog">
            Explorar Catálogo
          </button>
        </div>
      `;
      return;
    }

    listEl.innerHTML = orders.map(order => {
      const isMixed = (order.orderType === 'mixed');
      const isSplit = (isMixed && order.dispatchStrategy === 'split');
      const dateFormatted = new Date(order.createdAt).toLocaleDateString('es-MX', { year: 'numeric', month: 'short', day: 'numeric' });

      return `
        <div class="order-history-card">
          <div class="order-history-header">
            <div>
              <div class="order-history-folio">${order.folio}</div>
              <div class="order-history-date">${dateFormatted}</div>
            </div>
            <div style="text-align: right;">
              <span class="order-status-badge ${order.status === 'approved' ? 'status-approved' : 'status-pending'}">
                ${order.status === 'approved' ? 'PAGO APROBADO' : 'PENDIENTE DE PAGO'}
              </span>
              <div class="order-history-total">MXN $${(order.total || 0).toFixed(2)}</div>
            </div>
          </div>

          ${isMixed ? `
            <div class="order-mixed-dual-badge">
              <span class="badge-branch-ready">[LINEA A] Bodega Salida 24-48h</span>
              <span class="badge-branch-demand">[LINEA B] Taller Confección 3-5d</span>
            </div>
            <div class="order-tracking-timeline">
              <div class="timeline-row">
                <span class="timeline-dot dot-ready"></span>
                <div class="timeline-info">
                  <strong>Línea A (Stock Físico):</strong> Apartado en bodega central. ${(order.readyItems || []).length} prenda(s).
                </div>
              </div>
              <div class="timeline-row">
                <span class="timeline-dot dot-demand"></span>
                <div class="timeline-info">
                  <strong>Línea B (On-Demand):</strong> En cola de patronaje y corte. ${(order.onDemandItems || []).length} prenda(s).
                </div>
              </div>
            </div>
          ` : `
            <div style="font-size: 0.72rem; color: var(--text-secondary); margin: 6px 0;">
              Tipo de despacho: <strong>${order.orderType === 'on_demand' ? 'Confección Bajo Pedido' : 'Stock Físico Inmediato'}</strong>
            </div>
          `}

          <div class="order-history-items">
            ${(order.items || []).map(it => `
              <div class="order-history-item-line">
                <span>${it.quantity}x ${it.name} (${it.size})</span>
                <span>MXN $${(it.price * it.quantity).toFixed(2)}</span>
              </div>
            `).join('')}
          </div>

          <div class="order-history-footer">
            <span class="order-carrier-tag">${order.carrier || 'Courier Express Nacional'}</span>
            <span style="font-size: 0.7rem; color: var(--text-muted);">${order.paymentMethod === 'spei_bbva' ? 'SPEI BBVA' : (order.paymentMethod === 'oxxo_pay' ? 'OXXO Pay' : 'Tarjeta')}</span>
          </div>
        </div>
      `;
    }).join('');
  }

  function lookupOrderByFolio(folioQuery) {
    const clean = (folioQuery || '').trim().toUpperCase();
    const resultBox = document.getElementById('accountLookupResult');
    if (!resultBox) return;

    if (!clean) {
      resultBox.style.display = 'none';
      return;
    }

    let orders = [];
    try {
      orders = JSON.parse(localStorage.getItem('multiverse_customer_orders') || '[]');
    } catch (e) {}

    const found = orders.find(o => o.folio && o.folio.toUpperCase().includes(clean));
    resultBox.style.display = 'block';

    if (!found) {
      resultBox.innerHTML = `
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 10px; font-size: 0.74rem; color: #991b1b;">
          No se encontró ningún pedido registrado con el folio "<strong>${clean}</strong>".
        </div>
      `;
    } else {
      const isMixed = (found.orderType === 'mixed');
      resultBox.innerHTML = `
        <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 6px; padding: 12px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <strong style="color: #166534; font-size: 0.82rem;">Folio: ${found.folio}</strong>
            <span class="order-status-badge ${found.status === 'approved' ? 'status-approved' : 'status-pending'}">
              ${found.status === 'approved' ? 'PAGO APROBADO' : 'PENDIENTE'}
            </span>
          </div>
          <div style="font-size: 0.74rem; color: #166534; margin-bottom: 6px;">
            Total: <strong>MXN $${(found.total || 0).toFixed(2)}</strong> | Método: ${found.paymentMethod}
          </div>
          ${isMixed ? `
            <div style="background: #ffffff; border: 1px solid #bbf7d0; border-radius: 4px; padding: 8px; font-size: 0.7rem; color: #166534;">
              <strong>Rastreo Dual de Carrito Mixto:</strong>
              <div>- Línea A (Bodega): Despacho express 24-48h</div>
              <div>- Línea B (Taller): Confección artesanal 3-5 días hábiles</div>
            </div>
          ` : ''}
        </div>
      `;
    }
  }

  function renderAccountAnatomy(session) {
    const slider = document.getElementById('anatomyWaistSlider');
    const valDisplay = document.getElementById('anatomyWaistValueDisplay');
    const calcCard = document.getElementById('anatomyCalculationCard');

    let currentWaist = 82;
    if (session && session.anatomicalProfile && session.anatomicalProfile.waistCm) {
      currentWaist = parseInt(session.anatomicalProfile.waistCm, 10);
    }

    if (slider) slider.value = currentWaist;
    if (valDisplay) valDisplay.textContent = `${currentWaist} cm`;

    const calc = calculateTorzoSize(currentWaist);
    if (calcCard && calc) {
      calcCard.innerHTML = `
        <div class="calc-top-row">
          <span class="calc-label">Talla Recomendada:</span>
          <strong class="calc-size-badge">${calc.size}</strong>
        </div>
        <div class="calc-pants-row">Equivalencia: <strong>Pantalón ${calc.pants}</strong> (${calc.range})</div>
        <p class="calc-advice">${calc.fitAdvice}</p>
      `;
    }

    if (session && session.anatomicalProfile && session.anatomicalProfile.silhouettes) {
      const s = session.anatomicalProfile.silhouettes;
      const jock = document.getElementById('prefSilJocks');
      if (jock) jock.checked = !!s.jocks;
      const boxer = document.getElementById('prefSilBoxers');
      if (boxer) boxer.checked = !!s.boxers;
      const tanga = document.getElementById('prefSilTangas');
      if (tanga) tanga.checked = !!s.tangas;
      const bikini = document.getElementById('prefSilBikinis');
      if (bikini) bikini.checked = !!s.bikinis;
      const tank = document.getElementById('prefSilTanktops');
      if (tank) tank.checked = !!s.tanktops;
    }
  }

  function saveAnatomicalProfile() {
    const slider = document.getElementById('anatomyWaistSlider');
    const waistCm = slider ? parseInt(slider.value, 10) : 82;
    const calc = calculateTorzoSize(waistCm);

    const silhouettes = {
      jocks: document.getElementById('prefSilJocks')?.checked ?? true,
      boxers: document.getElementById('prefSilBoxers')?.checked ?? true,
      tangas: document.getElementById('prefSilTangas')?.checked ?? false,
      bikinis: document.getElementById('prefSilBikinis')?.checked ?? false,
      tanktops: document.getElementById('prefSilTanktops')?.checked ?? true
    };

    let session = {};
    try {
      session = JSON.parse(localStorage.getItem('multiverse_customer_session') || '{}');
    } catch (e) {}

    session.anatomicalProfile = {
      waistCm: waistCm,
      size: calc ? calc.size : 'M',
      pants: calc ? calc.pants : '31-32',
      silhouettes: silhouettes,
      updatedAt: new Date().toISOString()
    };

    try {
      localStorage.setItem('multiverse_customer_session', JSON.stringify(session));
    } catch (e) {}

    showToastNotification('Perfil Anatómico guardado exitosamente.');
  }

  function renderAccountAddress(session) {
    const summaryEl = document.getElementById('accountAddressSummary');
    if (!summaryEl) return;

    const shipping = (session && session.shipping) ? session.shipping : checkoutState.shipping;
    const hasAddress = shipping && shipping.street && shipping.city;

    if (accountState.editingAddress || !hasAddress) {
      summaryEl.innerHTML = `
        <div class="address-edit-form">
          <div class="acc-form-group">
            <label class="acc-label">Calle y Número *</label>
            <input type="text" id="accInputStreet" class="account-input" value="${shipping?.street || ''}" placeholder="Ej: Av. Juárez 14, Depto 402">
          </div>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
            <div class="acc-form-group">
              <label class="acc-label">Código Postal *</label>
              <div style="display: flex; gap: 4px;">
                <input type="text" id="accInputZip" class="account-input" maxlength="5" value="${shipping?.postalCode || ''}" placeholder="06000">
                <button type="button" class="btn-account-action" data-action="acc-lookup-postal-code" style="padding: 4px 8px; font-size: 0.7rem;">CP</button>
              </div>
            </div>
            <div class="acc-form-group">
              <label class="acc-label">Colonia *</label>
              <input type="text" id="accInputColony" class="account-input" value="${shipping?.colony || ''}" placeholder="Centro">
            </div>
          </div>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
            <div class="acc-form-group">
              <label class="acc-label">Alcaldía / Municipio *</label>
              <input type="text" id="accInputCity" class="account-input" value="${shipping?.city || ''}" placeholder="Cuauhtémoc">
            </div>
            <div class="acc-form-group">
              <label class="acc-label">Estado *</label>
              <input type="text" id="accInputState" class="account-input" value="${shipping?.state || ''}" placeholder="Ciudad de México">
            </div>
          </div>
          <div class="acc-form-group">
            <label class="acc-label">Referencias del Domicilio</label>
            <input type="text" id="accInputRefs" class="account-input" value="${shipping?.references || ''}" placeholder="Entre calles, color de fachada...">
          </div>
          <div style="display: flex; gap: 8px; margin-top: 10px;">
            <button type="button" class="btn-account-primary" data-action="save-acc-address" style="flex: 1;">
              GUARDAR DIRECCIÓN
            </button>
            ${hasAddress ? `
              <button type="button" class="btn-account-secondary" data-action="cancel-acc-address" style="padding: 8px 12px; font-size: 0.72rem;">
                Cancelar
              </button>
            ` : ''}
          </div>
        </div>
      `;
    } else {
      summaryEl.innerHTML = `
        <div class="address-display-box">
          <div style="font-weight: 700; font-size: 0.82rem; color: #050505; margin-bottom: 4px;">Dirección Predeterminada de Entrega</div>
          <p style="font-size: 0.75rem; color: var(--text-secondary); line-height: 1.4; margin: 0 0 10px;">
            ${shipping.street}<br>
            Col. ${shipping.colony}, ${shipping.city}, ${shipping.state}<br>
            CP ${shipping.postalCode}
            ${shipping.references ? `<br><small style="color: var(--text-muted);">Ref: ${shipping.references}</small>` : ''}
          </p>
          <button type="button" class="btn-account-action" data-action="edit-acc-address">
            Editar Dirección
          </button>
        </div>
      `;
    }
  }

  function saveCustomerAddress() {
    const street = (document.getElementById('accInputStreet')?.value || '').trim();
    const postalCode = (document.getElementById('accInputZip')?.value || '').replace(/\D/g, '');
    const colony = (document.getElementById('accInputColony')?.value || '').trim();
    const city = (document.getElementById('accInputCity')?.value || '').trim();
    const state = (document.getElementById('accInputState')?.value || '').trim();
    const references = (document.getElementById('accInputRefs')?.value || '').trim();

    if (!street || postalCode.length !== 5 || !colony || !city || !state) {
      showToastNotification('Por favor completa todos los campos requeridos de la dirección.');
      return;
    }

    const shipping = { street, postalCode, colony, city, state, references };
    checkoutState.shipping = { ...shipping };

    let session = {};
    try {
      session = JSON.parse(localStorage.getItem('multiverse_customer_session') || '{}');
    } catch (e) {}

    session.shipping = shipping;
    try {
      localStorage.setItem('multiverse_customer_session', JSON.stringify(session));
    } catch (e) {}

    accountState.editingAddress = false;
    renderAccountAddress(session);
    showToastNotification('Dirección guardada correctamente.');
  }

  function renderAccountSubscription() {
    const panel = document.getElementById('accountTabSubscription');
    if (!panel) return;

    panel.innerHTML = `
      <div class="subscription-panel-content">
        <div class="sub-plan-card">
          <div class="sub-plan-header">
            <div>
              <span class="sub-badge-mrr">MEMBRESIA VIP D2C</span>
              <h4 class="sub-plan-title">MARTE Club MRR</h4>
            </div>
            <div class="sub-plan-price">
              <strong>$399</strong>
              <span>MXN / mes</span>
            </div>
          </div>
          <p class="sub-plan-desc">
            Recibe 1 prenda curada mensual adaptada a tu perfil anatómico, 15% de descuento permanente y envíos gratis sin mínimo de compra.
          </p>
          <ul class="sub-benefits-list">
            <li>1 Prenda mensual exclusiva seleccionada para ti</li>
            <li>15% OFF automático en todas las colecciones</li>
            <li>Envío express nacional gratis en todos tus pedidos</li>
            <li>Acceso prioritario a lanzamientos limitados</li>
          </ul>
          <button type="button" class="btn-account-primary" style="width: 100%;" onclick="alert('Membresía Club MARTE disponible próximamente en integración Stripe Billing.');">
            UNIRME AL CLUB MARTE ($399/mes)
          </button>
        </div>

        <div class="push-notifications-card" id="accountPushCenter">
          <!-- Renderizado dinámico desde renderAccountPushPreferences() -->
        </div>
      </div>
    `;

    renderAccountPushPreferences();
  }

  // ============================================================================
  // MOTOR DE NOTIFICACIONES WEB PUSH PWA (TASK-STR-06)
  // ============================================================================
  function initPushNotifications() {
    pushNotificationState.supported = typeof window !== 'undefined' && ('Notification' in window);

    // Cargar preferencias guardadas
    try {
      const savedPrefs = localStorage.getItem('multiverse_push_preferences');
      if (savedPrefs) {
        pushNotificationState.preferences = {
          ...pushNotificationState.preferences,
          ...JSON.parse(savedPrefs)
        };
      }
      const savedSub = localStorage.getItem('multiverse_push_subscription');
      if (savedSub) {
        pushNotificationState.subscription = JSON.parse(savedSub);
      }
      pushNotificationState.promptDismissed = localStorage.getItem('multiverse_push_prompt_dismissed') === 'true';
    } catch(e) {}

    // Leer permiso del navegador
    if (typeof window !== 'undefined' && 'Notification' in window) {
      pushNotificationState.permission = Notification.permission || 'default';
    } else {
      const isEnabled = localStorage.getItem('multiverse_push_enabled') === 'true';
      pushNotificationState.permission = isEnabled ? 'granted' : 'default';
    }

    // Escuchar mensajes del Service Worker ante clics en notificaciones
    if (typeof navigator !== 'undefined' && 'serviceWorker' in navigator && navigator.serviceWorker && typeof navigator.serviceWorker.addEventListener === 'function') {
      navigator.serviceWorker.addEventListener('message', function(event) {
        if (event.data && event.data.type === 'NOTIFICATION_CLICK') {
          handleNotificationRouting(event.data.data);
        }
      });
    }

    renderAccountPushPreferences();
  }

  function handleNotificationRouting(data) {
    if (!data) return;
    const action = data.action || '';
    if (action === 'open_cart') {
      openDrawer('cartSheet');
    } else if (action === 'open_orders') {
      openDrawer('accountSheet');
      switchAccountTab('orders');
      if (data.orderFolio) {
        lookupOrderByFolio(data.orderFolio);
      }
    } else if (action === 'open_subscription') {
      openDrawer('accountSheet');
      switchAccountTab('subscription');
    }
  }

  function requestPushPermission() {
    if (typeof window !== 'undefined' && 'Notification' in window) {
      try {
        Notification.requestPermission().then(function(permission) {
          handlePushPermissionResult(permission);
        }).catch(function() {
          handlePushPermissionResult('granted');
        });
      } catch (e) {
        handlePushPermissionResult('granted');
      }
    } else {
      handlePushPermissionResult('granted');
    }
  }

  function handlePushPermissionResult(perm) {
    pushNotificationState.permission = perm;
    const banner = document.getElementById('pushPromptBanner');
    if (banner) banner.style.display = 'none';
    pushNotificationState.promptDismissed = true;
    try {
      localStorage.setItem('multiverse_push_prompt_dismissed', 'true');
    } catch(e) {}

    if (perm === 'granted') {
      const syntheticSub = {
        endpoint: 'https://fcm.googleapis.com/fcm/send/pwa-multiverse-' + Math.random().toString(36).substr(2, 9),
        keys: {
          p256dh: 'BNcRdreALRFXTkOOUHK1EtK2wtaz5Ry4YfYCA_0QT9P045SD6-p145xV2s47bA',
          auth: 'tBH26G9fVp-XW2L8'
        },
        userVisibleOnly: true,
        subscribedAt: new Date().toISOString()
      };
      pushNotificationState.subscription = syntheticSub;
      try {
        localStorage.setItem('multiverse_push_subscription', JSON.stringify(syntheticSub));
        localStorage.setItem('multiverse_push_enabled', 'true');
      } catch(e) {}

      renderAccountPushPreferences();
      showToastNotification('Alertas Web Push activadas con éxito.');
      sendSimulatedPush('welcome');
    } else {
      try {
        localStorage.setItem('multiverse_push_enabled', 'false');
      } catch(e) {}
      renderAccountPushPreferences();
      showToastNotification('Permiso de notificaciones no activado.');
    }
  }

  function dismissPushPrompt() {
    pushNotificationState.promptDismissed = true;
    try {
      localStorage.setItem('multiverse_push_prompt_dismissed', 'true');
    } catch(e) {}
    const banner = document.getElementById('pushPromptBanner');
    if (banner) banner.style.display = 'none';
  }

  function checkPushPromptTrigger() {
    if (pushNotificationState.promptDismissed) return;
    if (pushNotificationState.permission === 'granted') return;
    const banner = document.getElementById('pushPromptBanner');
    if (banner) banner.style.display = 'block';
  }

  function togglePushTopicPreference(topic) {
    if (pushNotificationState.preferences.hasOwnProperty(topic)) {
      pushNotificationState.preferences[topic] = !pushNotificationState.preferences[topic];
      try {
        localStorage.setItem('multiverse_push_preferences', JSON.stringify(pushNotificationState.preferences));
      } catch(e) {}
      renderAccountPushPreferences();
      showToastNotification('Preferencia de alertas actualizada.');
    }
  }

  function togglePushNotifications() {
    const isEnabled = (pushNotificationState.permission === 'granted') && (localStorage.getItem('multiverse_push_enabled') !== 'false');
    if (isEnabled) {
      localStorage.setItem('multiverse_push_enabled', 'false');
      pushNotificationState.subscription = null;
      try {
        localStorage.removeItem('multiverse_push_subscription');
      } catch(e) {}
      renderAccountPushPreferences();
      showToastNotification('Alertas Web Push desactivadas.');
    } else {
      requestPushPermission();
    }
  }

  function renderAccountPushPreferences() {
    const center = document.getElementById('accountPushCenter');
    if (!center) return;

    const perm = pushNotificationState.permission;
    const isEnabled = (perm === 'granted') && (localStorage.getItem('multiverse_push_enabled') !== 'false');
    const prefs = pushNotificationState.preferences;

    let statusText = 'INACTIVO';
    let statusClass = 'inactive';
    if (isEnabled) {
      statusText = 'ACTIVO';
      statusClass = 'active';
    } else if (perm === 'denied') {
      statusText = 'BLOQUEADO';
      statusClass = 'blocked';
    }

    center.innerHTML = `
      <div class="push-center-header">
        <div class="push-center-title-box">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#2563eb" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
          <div>
            <strong style="font-size: 0.8rem; color: #050505; display: block;">Centro de Alertas Web Push</strong>
            <span style="font-size: 0.68rem; color: var(--text-muted);">Estándar VAPID RFC 8292</span>
          </div>
        </div>
        <span class="push-status-badge ${statusClass}">${statusText}</span>
      </div>

      <div class="push-topics-list">
        <div class="push-topic-item">
          <div class="push-topic-info">
            <span class="push-topic-title">Expiración de Stock (Urgencia 5 min)</span>
            <span class="push-topic-desc">Alerta preventiva al restar 2 minutos en tu carrito.</span>
          </div>
          <label class="push-switch" aria-label="Alerta de expiración de stock">
            <input type="checkbox" data-action="toggle-push-topic" data-topic="stock_urgency" ${prefs.stock_urgency ? 'checked' : ''}>
            <span class="push-slider"></span>
          </label>
        </div>

        <div class="push-topic-item">
          <div class="push-topic-info">
            <span class="push-topic-title">Rastreo Courier en Tiempo Real</span>
            <span class="push-topic-desc">Actualizaciones de despacho y salida a ruta de tu paquete.</span>
          </div>
          <label class="push-switch" aria-label="Alerta de rastreo courier">
            <input type="checkbox" data-action="toggle-push-topic" data-topic="order_tracking" ${prefs.order_tracking ? 'checked' : ''}>
            <span class="push-slider"></span>
          </label>
        </div>

        <div class="push-topic-item">
          <div class="push-topic-info">
            <span class="push-topic-title">Renovación Club MARTE (MRR)</span>
            <span class="push-topic-desc">Recordatorio de fecha de corte y despacho mensual.</span>
          </div>
          <label class="push-switch" aria-label="Alerta de suscripción Club MARTE">
            <input type="checkbox" data-action="toggle-push-topic" data-topic="subscription_mrr" ${prefs.subscription_mrr ? 'checked' : ''}>
            <span class="push-slider"></span>
          </label>
        </div>

        <div class="push-topic-item">
          <div class="push-topic-info">
            <span class="push-topic-title">Novedades & Drops de Colección</span>
            <span class="push-topic-desc">Avisos anticipados de nuevas colecciones de autor.</span>
          </div>
          <label class="push-switch" aria-label="Alerta de novedades y drops">
            <input type="checkbox" data-action="toggle-push-topic" data-topic="new_drops" ${prefs.new_drops ? 'checked' : ''}>
            <span class="push-slider"></span>
          </label>
        </div>
      </div>

      <div class="push-actions-row">
        ${isEnabled ? `
          <button type="button" class="btn-push-test" data-action="send-test-push">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <span>Probar Alerta Push</span>
          </button>
          <button type="button" class="btn-account-action" data-action="toggle-push-notifications" style="font-size: 0.7rem;">
            Desactivar
          </button>
        ` : `
          <button type="button" class="btn-account-primary" data-action="request-push-permission" style="width: 100%; font-size: 0.74rem;">
            ACTIVAR NOTIFICACIONES PWA
          </button>
        `}
      </div>
    `;
  }

  function sendSimulatedPush(type) {
    let title = 'Multiverso Store // Notificación';
    let body = 'Tienes una actualización importante de tu tienda.';
    let action = 'open_store';
    let tag = 'multiverse-general';

    if (type === 'stock_urgency') {
      title = 'Multiverso Store // Reserva de Stock';
      body = 'Tu apartado de stock expira en 2 minutos. Completa tu pedido antes de que tus prendas vuelvan al catálogo.';
      action = 'open_cart';
      tag = 'multiverse-stock';
    } else if (type === 'courier_dispatch') {
      title = 'Multiverso Store // Envio Express en Ruta';
      body = '¡Tu pedido ha sido despachado via Paqueteria Express Nacional con guia #EXP-2026-8819!';
      action = 'open_orders';
      tag = 'multiverse-courier';
    } else if (type === 'subscription_mrr') {
      title = 'MARTE Club MRR // Próxima Caja Mensual';
      body = 'Tu próxima prenda curada se procesará este viernes. Revisa tu Perfil Anatómico.';
      action = 'open_subscription';
      tag = 'multiverse-mrr';
    } else if (type === 'welcome') {
      title = 'Multiverso Store // Alertas Activadas';
      body = 'Recibirás avisos de tracking courier en vivo y alertas de apartado exclusivo.';
      action = 'open_store';
      tag = 'multiverse-welcome';
    }

    if (typeof window !== 'undefined' && 'Notification' in window && Notification.permission === 'granted') {
      try {
        new Notification(title, {
          body: body,
          icon: '/user-avatar.png',
          badge: '/user-avatar.png',
          tag: tag,
          data: { action: action, url: '/' }
        });
      } catch(e) {}
    }

    showToastNotification(`${title}: ${body}`);
  }

  function logoutBuyerSession() {
    try {
      localStorage.removeItem('multiverse_customer_session');
    } catch (e) {}
    checkoutState.customer = { name: '', phone: '', email: '' };
    renderAccountSheetContent();
    showToastNotification('Sesión cerrada correctamente.');
  }

  // Delegación de Eventos Click
  document.addEventListener('click', function(e) {
    // 1. Selector de Talla en Tarjeta de Producto
    const sizeBtn = e.target.closest('[data-action="select-size"]');
    if (sizeBtn) {
      e.preventDefault();
      const pId = sizeBtn.dataset.productId;
      const sz = sizeBtn.dataset.size;
      storeState.selectedSizes[pId] = sz;

      const card = sizeBtn.closest('.product-card');
      if (card) {
        card.querySelectorAll('[data-action="select-size"]').forEach(b => {
          b.classList.toggle('selected', b.dataset.size === sz);
        });
      }
      return;
    }

    // 2. Agregar al Carrito desde Vitrina
    const addBtn = e.target.closest('[data-action="add-to-cart"]');
    if (addBtn) {
      e.preventDefault();
      addToCart(addBtn.dataset.productId);
      return;
    }

    // 3. Abrir Ficha de Producto Modal (PDP)
    const openPdpBtn = e.target.closest('[data-action="open-product-modal"]');
    if (openPdpBtn) {
      e.preventDefault();
      openProductModal(openPdpBtn.dataset.productId);
      return;
    }

    // 4. Cerrar Ficha de Producto Modal (PDP)
    const closePdpBtn = e.target.closest('[data-action="close-product-modal"]');
    if (closePdpBtn) {
      e.preventDefault();
      closeProductModal();
      return;
    }

    // 5. Toggle Wishlist / Deseos
    const wishBtn = e.target.closest('[data-action="toggle-wishlist"]');
    if (wishBtn) {
      e.preventDefault();
      e.stopPropagation();
      toggleWishlist(wishBtn.dataset.productId, e);
      return;
    }

    // 6. Mover de Wishlist al Carrito
    const moveWishBtn = e.target.closest('[data-action="move-wishlist-to-cart"]');
    if (moveWishBtn) {
      e.preventDefault();
      moveWishlistToCart(moveWishBtn.dataset.productId);
      return;
    }

    // 7. Abrir Drawer de Deseos
    const openWishlistBtn = e.target.closest('[data-action="open-wishlist"]');
    if (openWishlistBtn) {
      e.preventDefault();
      renderWishlistDrawerContent();
      openDrawer('wishlistSheet');
      return;
    }

    // 8. Selección de Talla dentro del Modal PDP
    const pdpSizeBtn = e.target.closest('[data-action="pdp-select-size"]');
    if (pdpSizeBtn) {
      e.preventDefault();
      const sz = pdpSizeBtn.dataset.size;
      storeState.selectedModalSize = sz;
      if (storeState.activeModalProductId) {
        storeState.selectedSizes[storeState.activeModalProductId] = sz;
      }

      document.querySelectorAll('[data-action="pdp-select-size"]').forEach(b => {
        b.classList.toggle('selected', b.dataset.size === sz);
      });

      const lbl = document.getElementById('pdpSelectedSizeLabel');
      if (lbl) lbl.textContent = `Talla ${sz}`;

      const modalProd = STORE_PRODUCTS.find(p => p.id === storeState.activeModalProductId);
      if (modalProd) {
        const variant = (modalProd.variants || []).find(v => v.size === sz);
        const stockBadge = document.getElementById('pdpStockBadge');
        if (stockBadge) {
          if (variant && variant.available > 0) {
            stockBadge.className = 'product-stock-badge ready';
            stockBadge.textContent = 'Stock Físico';
          } else {
            stockBadge.className = 'product-stock-badge';
            stockBadge.textContent = 'Bajo Pedido';
          }
        }
      }

      // Telemetría de talla preferida
      dispatchWishlistTelemetry('size_select', {
        product_id: storeState.activeModalProductId,
        universe: storeState.activeUniverse,
        selected_size: sz
      });
      return;
    }

    // 9. Calcular Talla Torzo con Asesor
    const calcBtn = e.target.closest('[data-action="pdp-calc-size"]');
    if (calcBtn) {
      e.preventDefault();
      const input = document.getElementById('pdpWaistCmInput');
      const resultBox = document.getElementById('pdpAdvisorResult');
      if (input && resultBox) {
        const val = parseFloat(input.value);
        const res = calculateTorzoSize(val);
        if (res) {
          resultBox.style.display = 'block';
          resultBox.innerHTML = `
            <strong>Talla Sugerida: ${res.size}</strong> (Pantalón ${res.pants} / ${res.range}).<br>
            <span style="color: #047857; font-weight: 600;">${res.fitAdvice}</span>
          `;

          // Auto-seleccionar la talla sugerida
          storeState.selectedModalSize = res.size;
          if (storeState.activeModalProductId) {
            storeState.selectedSizes[storeState.activeModalProductId] = res.size;
          }
          document.querySelectorAll('[data-action="pdp-select-size"]').forEach(b => {
            b.classList.toggle('selected', b.dataset.size === res.size);
          });
          const lbl = document.getElementById('pdpSelectedSizeLabel');
          if (lbl) lbl.textContent = `Talla ${res.size} (Recomendada)`;

          // Telemetría con medida exacta de cintura
          dispatchWishlistTelemetry('size_advisor_calc', {
            product_id: storeState.activeModalProductId,
            universe: storeState.activeUniverse,
            selected_size: res.size,
            waist_cm: val
          });
        } else {
          resultBox.style.display = 'block';
          resultBox.textContent = 'Por favor ingresa una medida de cintura válida en cm (ej: 82).';
        }
      }
      return;
    }

    // 10. Añadir al Carrito desde Modal PDP
    const pdpAddBtn = e.target.closest('[data-action="pdp-add-to-cart"]');
    if (pdpAddBtn) {
      e.preventDefault();
      if (storeState.activeModalProductId) {
        addToCart(storeState.activeModalProductId, storeState.selectedModalSize);
        closeProductModal();
        openDrawer('cartSheet');
      }
      return;
    }

    // 11. Conmutador de Universos (Top selector)
    const univBtn = e.target.closest('[data-action="switch-universe"]');
    if (univBtn) {
      e.preventDefault();
      switchUniverse(univBtn.dataset.universe);
      return;
    }

    // 12. Conmutador de Universos ZARA dentro del Drawer
    const zaraTab = e.target.closest('[data-action="zara-switch-universe"]');
    if (zaraTab) {
      e.preventDefault();
      switchUniverse(zaraTab.dataset.universe);
      return;
    }

    // 13. Filtro de Categoría en Drawer
    const catPill = e.target.closest('[data-action="filter-category"]');
    if (catPill) {
      e.preventDefault();
      const cat = catPill.dataset.category;
      storeState.activeCategory = (storeState.activeCategory === cat) ? 'all' : cat;

      document.querySelectorAll('[data-action="filter-category"]').forEach(p => {
        p.classList.toggle('active', p.dataset.category === storeState.activeCategory);
      });
      return;
    }

    // 14. Aplicar Filtros del Drawer
    const applyBtn = e.target.closest('[data-action="apply-filters"]');
    if (applyBtn) {
      e.preventDefault();
      closeAllDrawers();
      renderProductsGrid('homeProductsGrid');
      renderProductsGrid('catalogProductsGrid');
      showToastNotification('Filtros aplicados');
      return;
    }

    // 15. Limpiar Filtros
    const clearBtn = e.target.closest('[data-action="clear-filters"]');
    if (clearBtn) {
      e.preventDefault();
      storeState.activeCategory = 'all';
      document.querySelectorAll('[data-action="filter-category"]').forEach(p => p.classList.remove('active'));
      return;
    }

    // 16. Botones del Bottom Navigation Dock
    const dockBtn = e.target.closest('[data-action="switch-dock-tab"]');
    if (dockBtn) {
      e.preventDefault();
      switchDockTab(dockBtn.dataset.dockTab);
      return;
    }

    // 17. Abrir Drawers
    const openFilterBtn = e.target.closest('[data-action="open-filter-drawer"]');
    if (openFilterBtn) {
      e.preventDefault();
      openDrawer('filterDrawer');
      return;
    }

    const openSizeGuideBtn = e.target.closest('[data-action="open-size-guide"]');
    if (openSizeGuideBtn) {
      e.preventDefault();
      openDrawer('sizeGuideSheet');
      return;
    }

    const openCartBtn = e.target.closest('[data-action="open-cart"]');
    if (openCartBtn) {
      e.preventDefault();
      openDrawer('cartSheet');
      return;
    }

    // 18. Cerrar Drawers
    const closeBtn = e.target.closest('[data-action="close-drawer"]');
    if (closeBtn) {
      e.preventDefault();
      closeAllDrawers();
      return;
    }

    // 19. Clic en Backdrop
    if (e.target.id === 'drawerBackdrop') {
      closeAllDrawers();
      return;
    }
    if (e.target.id === 'productModalBackdrop') {
      closeProductModal();
      return;
    }
    if (e.target.id === 'checkoutModalBackdrop') {
      closeCheckoutModal();
      return;
    }

    // 20. Controles de Cantidad en Carrito
    const incBtn = e.target.closest('[data-action="increase-qty"]');
    if (incBtn) {
      e.preventDefault();
      updateCartQuantity(parseInt(incBtn.dataset.index, 10), 1);
      return;
    }

    const decBtn = e.target.closest('[data-action="decrease-qty"]');
    if (decBtn) {
      e.preventDefault();
      updateCartQuantity(parseInt(decBtn.dataset.index, 10), -1);
      return;
    }

    const rmBtn = e.target.closest('[data-action="remove-item"]');
    if (rmBtn) {
      e.preventDefault();
      removeCartItem(parseInt(rmBtn.dataset.index, 10));
      return;
    }

    // 21. Apertura y Cierre de Checkout
    const openChkBtn = e.target.closest('[data-action="open-checkout"]');
    if (openChkBtn) {
      e.preventDefault();
      openCheckoutModal();
      return;
    }

    const closeChkBtn = e.target.closest('[data-action="close-checkout"]');
    if (closeChkBtn) {
      e.preventDefault();
      closeCheckoutModal();
      return;
    }

    // 22. Navegación de Pasos Checkout (4 Pasos)
    const toStep2Btn = e.target.closest('[data-action="checkout-to-step-2"]');
    if (toStep2Btn) {
      e.preventDefault();
      if (validateCheckoutStep1()) {
        goToCheckoutStep(2);
      }
      return;
    }

    const backStep1Btn = e.target.closest('[data-action="checkout-back-step-1"]');
    if (backStep1Btn) {
      e.preventDefault();
      goToCheckoutStep(1);
      return;
    }

    const toStep3Btn = e.target.closest('[data-action="checkout-to-step-3"]');
    if (toStep3Btn) {
      e.preventDefault();
      if (validateCheckoutStep2()) {
        renderCheckoutStep3Review();
        goToCheckoutStep(3);
      }
      return;
    }

    const backStep2Btn = e.target.closest('[data-action="checkout-back-step-2"]');
    if (backStep2Btn) {
      e.preventDefault();
      goToCheckoutStep(2);
      return;
    }

    const confirmOrderBtn = e.target.closest('[data-action="checkout-confirm-order"]');
    if (confirmOrderBtn) {
      e.preventDefault();
      confirmCheckoutOrder();
      return;
    }

    const toAccountBtn = e.target.closest('[data-action="checkout-to-account"]');
    if (toAccountBtn) {
      e.preventDefault();
      closeCheckoutModal();
      openDrawer('accountSheet');
      return;
    }

    // 23. Autenticación Google en Checkout y Mi Cuenta
    const googleLoginBtn = e.target.closest('[data-action="google-login"]');
    if (googleLoginBtn) {
      e.preventDefault();
      const source = googleLoginBtn.dataset.source || 'account';
      loginWithGoogle(source);
      return;
    }

    // 24. Pestañas y Acciones del Portal del Comprador D2C
    const accTabBtn = e.target.closest('[data-action="switch-account-tab"]');
    if (accTabBtn) {
      e.preventDefault();
      switchAccountTab(accTabBtn.dataset.tab);
      return;
    }

    const accLookupBtn = e.target.closest('[data-action="account-lookup-folio"]');
    if (accLookupBtn) {
      e.preventDefault();
      const q = document.getElementById('accountLookupFolioInput')?.value || '';
      lookupOrderByFolio(q);
      return;
    }

    const saveAnatBtn = e.target.closest('[data-action="save-anatomy-profile"]');
    if (saveAnatBtn) {
      e.preventDefault();
      saveAnatomicalProfile();
      return;
    }

    const editAddrBtn = e.target.closest('[data-action="edit-acc-address"]');
    if (editAddrBtn) {
      e.preventDefault();
      accountState.editingAddress = true;
      let sess = null;
      try { sess = JSON.parse(localStorage.getItem('multiverse_customer_session') || '{}'); } catch(e){}
      renderAccountAddress(sess);
      return;
    }

    const cancelAddrBtn = e.target.closest('[data-action="cancel-acc-address"]');
    if (cancelAddrBtn) {
      e.preventDefault();
      accountState.editingAddress = false;
      let sess = null;
      try { sess = JSON.parse(localStorage.getItem('multiverse_customer_session') || '{}'); } catch(e){}
      renderAccountAddress(sess);
      return;
    }

    const saveAddrBtn = e.target.closest('[data-action="save-acc-address"]');
    if (saveAddrBtn) {
      e.preventDefault();
      saveCustomerAddress();
      return;
    }

    const accCpBtn = e.target.closest('[data-action="acc-lookup-postal-code"]');
    if (accCpBtn) {
      e.preventDefault();
      const cp = document.getElementById('accInputZip')?.value || '';
      lookupPostalCode(cp);
      return;
    }

    const pushBtn = e.target.closest('[data-action="toggle-push-notifications"]');
    if (pushBtn) {
      e.preventDefault();
      togglePushNotifications();
      return;
    }

    const reqPushBtn = e.target.closest('[data-action="request-push-permission"]');
    if (reqPushBtn) {
      e.preventDefault();
      requestPushPermission();
      return;
    }

    const disPushBtn = e.target.closest('[data-action="dismiss-push-prompt"]');
    if (disPushBtn) {
      e.preventDefault();
      dismissPushPrompt();
      return;
    }

    const topicPushBtn = e.target.closest('[data-action="toggle-push-topic"]');
    if (topicPushBtn) {
      e.preventDefault();
      const topic = topicPushBtn.dataset.topic;
      if (topic) togglePushTopicPreference(topic);
      return;
    }

    const testPushBtn = e.target.closest('[data-action="send-test-push"]');
    if (testPushBtn) {
      e.preventDefault();
      sendSimulatedPush('courier_dispatch');
      return;
    }

    const logoutBtn = e.target.closest('[data-action="buyer-logout"]');
    if (logoutBtn) {
      e.preventDefault();
      logoutBuyerSession();
      return;
    }

    // 25. Búsqueda de Código Postal y Copiado de CLABE
    const lookupBtn = e.target.closest('[data-action="lookup-postal-code"]');
    if (lookupBtn) {
      e.preventDefault();
      const cp = document.getElementById('checkoutShippingZip')?.value || '';
      lookupPostalCode(cp);
      return;
    }

    const copyBtn = e.target.closest('[data-action="copy-clabe"]');
    if (copyBtn) {
      e.preventDefault();
      copyClabeToClipboard();
      return;
    }

    // 24. Selección de Método de Pago al hacer clic en tarjeta
    const payCard = e.target.closest('.payment-method-card');
    if (payCard && !e.target.closest('button') && !e.target.closest('input[type="text"]') && !e.target.closest('input[type="password"]')) {
      const radio = payCard.querySelector('input[name="checkoutPaymentMethod"]');
      if (radio) {
        radio.checked = true;
        switchCheckoutPaymentMethod(radio.value);
      }
      return;
    }

    // 25. Selección de Estrategia de Despacho en Carrito Mixto al hacer clic en tarjeta
    const stratCard = e.target.closest('.mixed-option-card');
    if (stratCard && !e.target.closest('input[type="radio"]')) {
      const radio = stratCard.querySelector('input[name="checkoutDispatchStrategy"]');
      if (radio) {
        radio.checked = true;
        switchDispatchStrategy(radio.value);
      }
      return;
    }

    // 26. Cupones de Descuento y Convenios (Hub 7 TASK-STR-04)
    const applyCartCpn = e.target.closest('[data-action="apply-cart-coupon"]');
    if (applyCartCpn) {
      e.preventDefault();
      const val = document.getElementById('cartCouponInput')?.value || '';
      applyDiscountCoupon(val);
      return;
    }

    const applyChkCpn = e.target.closest('[data-action="apply-checkout-coupon"]');
    if (applyChkCpn) {
      e.preventDefault();
      const val = document.getElementById('checkoutCouponInput')?.value || '';
      applyDiscountCoupon(val);
      return;
    }

    const removeCpn = e.target.closest('[data-action="remove-coupon"]');
    if (removeCpn) {
      e.preventDefault();
      removeDiscountCoupon();
      return;
    }

    const closePartnerBtn = e.target.closest('[data-action="close-partner-banner"]');
    if (closePartnerBtn) {
      e.preventDefault();
      const banner = document.getElementById('partnerWelcomeBanner');
      if (banner) banner.style.display = 'none';
      return;
    }

    // 27. Lead Magnet Dir A (Modal de Cortesía y Voucher Digital)
    const openLeadBtn = e.target.closest('[data-action="open-lead-magnet"]');
    if (openLeadBtn) {
      e.preventDefault();
      const camp = openLeadBtn.dataset.campaign || 'PARADISO_VIP';
      openLeadMagnetModal(camp);
      return;
    }

    const closeLeadBtn = e.target.closest('[data-action="close-lead-magnet"]');
    if (closeLeadBtn) {
      e.preventDefault();
      closeLeadMagnetModal();
      return;
    }

    const submitLeadBtn = e.target.closest('[data-action="submit-lead-magnet"]');
    if (submitLeadBtn) {
      e.preventDefault();
      submitLeadMagnet();
      return;
    }

    const applyLeadCpn = e.target.closest('[data-action="apply-lead-magnet-coupon"]');
    if (applyLeadCpn) {
      e.preventDefault();
      const c = applyLeadCpn.dataset.coupon || 'PARADISO15';
      closeLeadMagnetModal();
      applyDiscountCoupon(c);
      return;
    }

    if (e.target.id === 'leadMagnetModalBackdrop') {
      closeLeadMagnetModal();
      return;
    }
  });

  // Cambio de Radio Button en Pagos y Estrategia de Despacho
  document.addEventListener('change', function(e) {
    if (e.target.name === 'checkoutPaymentMethod') {
      switchCheckoutPaymentMethod(e.target.value);
    }
    if (e.target.name === 'checkoutDispatchStrategy') {
      switchDispatchStrategy(e.target.value);
    }
  });

  // Formateo Reactivo en Inputs de Checkout
  document.addEventListener('input', function(e) {
    if (e.target.id === 'checkoutCardNumber') {
      let v = e.target.value.replace(/\D/g, '').slice(0, 16);
      e.target.value = v.replace(/(\d{4})(?=\d)/g, '$1 ');
    }
    if (e.target.id === 'checkoutCardExpiry') {
      let v = e.target.value.replace(/\D/g, '').slice(0, 4);
      if (v.length >= 3) {
        e.target.value = v.slice(0, 2) + '/' + v.slice(2);
      } else {
        e.target.value = v;
      }
    }
    if (e.target.id === 'checkoutShippingZip') {
      let v = e.target.value.replace(/\D/g, '').slice(0, 5);
      e.target.value = v;
      if (v.length === 5) {
        lookupPostalCode(v);
      }
    }
    if (e.target.id === 'anatomyWaistSlider') {
      const val = parseInt(e.target.value, 10);
      const disp = document.getElementById('anatomyWaistValueDisplay');
      if (disp) disp.textContent = `${val} cm`;
      const calcCard = document.getElementById('anatomyCalculationCard');
      const calc = calculateTorzoSize(val);
      if (calcCard && calc) {
        calcCard.innerHTML = `
          <div class="calc-top-row">
            <span class="calc-label">Talla Recomendada:</span>
            <strong class="calc-size-badge">${calc.size}</strong>
          </div>
          <div class="calc-pants-row">Equivalencia: <strong>Pantalón ${calc.pants}</strong> (${calc.range})</div>
          <p class="calc-advice">${calc.fitAdvice}</p>
        `;
      }
    }
  });

  // Cierre con tecla Escape y Enter en Cupones
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeProductModal();
      closeCheckoutModal();
      closeAllDrawers();
      closeLeadMagnetModal();
    }
    if (e.key === 'Enter') {
      if (e.target && e.target.id === 'cartCouponInput') {
        e.preventDefault();
        applyDiscountCoupon(e.target.value);
      }
      if (e.target && e.target.id === 'checkoutCouponInput') {
        e.preventDefault();
        applyDiscountCoupon(e.target.value);
      }
    }
  });

  // Estilos Auxiliares para Controles de Carrito
  const styleEl = document.createElement('style');
  styleEl.textContent = `
    .btn-qty-control {
      width: 26px;
      height: 26px;
      border-radius: 4px;
      border: 1px solid var(--border-medium);
      background: #ffffff;
      color: #050505;
      font-weight: 800;
      font-size: 0.8rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .btn-remove-item {
      background: transparent;
      border: none;
      color: #a1a1aa;
      cursor: pointer;
      padding: 4px;
      margin-left: 4px;
    }
    .btn-remove-item:hover {
      color: #dc2626;
    }
  `;
  document.head.appendChild(styleEl);

  // Inicialización de la Aplicación
  function initStoreApp() {
    if (typeof window !== 'undefined' && Array.isArray(window.__INITIAL_STORE_PRODUCTS__) && window.__INITIAL_STORE_PRODUCTS__.length > 0) {
      STORE_PRODUCTS = window.__INITIAL_STORE_PRODUCTS__;
      window.STORE_PRODUCTS = STORE_PRODUCTS;
    }
    detectUrlAffiliationAndCoupons();
    updateCartBadges();
    updateWishlistBadges();
    renderProductsGrid('homeProductsGrid');
    renderProductsGrid('catalogProductsGrid');
    renderCartSheetContent();
    if (typeof fetchCartFromBackend === 'function') {
      fetchCartFromBackend();
    }
    renderWishlistDrawerContent();
    renderCouponBoxes();
    initPushNotifications();

    // Registro de Service Worker PWA
    if ('serviceWorker' in navigator && window.location.protocol.startsWith('http')) {
      navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
  }

  // Exponer a Window para pruebas automatizadas
  if (typeof window !== 'undefined') {
    window.storeState = storeState;
    window.STORE_PRODUCTS = STORE_PRODUCTS;
    window.UNIVERSE_META = UNIVERSE_META;
    window.couponState = couponState;
    window.VALID_COUPONS = VALID_COUPONS;
    window.LEAD_CAMPAIGNS = LEAD_CAMPAIGNS;
    window.applyDiscountCoupon = applyDiscountCoupon;
    window.removeDiscountCoupon = removeDiscountCoupon;
    window.renderCouponBoxes = renderCouponBoxes;
    window.detectUrlAffiliationAndCoupons = detectUrlAffiliationAndCoupons;
    window.openLeadMagnetModal = openLeadMagnetModal;
    window.closeLeadMagnetModal = closeLeadMagnetModal;
    window.submitLeadMagnet = submitLeadMagnet;
    window.switchUniverse = switchUniverse;
    window.switchDockTab = switchDockTab;
    window.openDrawer = openDrawer;
    window.closeAllDrawers = closeAllDrawers;
    window.addToCart = addToCart;
    window.clearCart = clearCart;
    window.fetchCartFromBackend = fetchCartFromBackend;
    window.syncCartItemWithBackend = syncCartItemWithBackend;
    window.renderProductsGrid = renderProductsGrid;
    window.isInWishlist = isInWishlist;
    window.toggleWishlist = toggleWishlist;
    window.moveWishlistToCart = moveWishlistToCart;
    window.renderWishlistDrawerContent = renderWishlistDrawerContent;
    window.openProductModal = openProductModal;
    window.closeProductModal = closeProductModal;
    window.calculateTorzoSize = calculateTorzoSize;
    window.calculateAnatomicSize = calculateTorzoSize;
    window.dispatchWishlistTelemetry = dispatchWishlistTelemetry;
    window.checkoutState = checkoutState;
    window.getCartType = getCartType;
    window.getCartLines = getCartLines;
    window.switchDispatchStrategy = switchDispatchStrategy;
    window.renderCheckoutCartTypeUI = renderCheckoutCartTypeUI;
    window.openCheckoutModal = openCheckoutModal;
    window.closeCheckoutModal = closeCheckoutModal;
    window.goToCheckoutStep = goToCheckoutStep;
    window.validateCheckoutStep1 = validateCheckoutStep1;
    window.validateCheckoutStep2 = validateCheckoutStep2;
    window.renderCheckoutStep3Review = renderCheckoutStep3Review;
    window.confirmCheckoutOrder = confirmCheckoutOrder;
    window.switchCheckoutPaymentMethod = switchCheckoutPaymentMethod;
    window.lookupPostalCode = lookupPostalCode;
    window.copyClabeToClipboard = copyClabeToClipboard;
    window.loginWithGoogle = loginWithGoogle;
    window.accountState = accountState;
    window.switchAccountTab = switchAccountTab;
    window.renderAccountSheetContent = renderAccountSheetContent;
    window.renderAccountOrders = renderAccountOrders;
    window.lookupOrderByFolio = lookupOrderByFolio;
    window.renderAccountAnatomy = renderAccountAnatomy;
    window.saveAnatomicalProfile = saveAnatomicalProfile;
    window.renderAccountAddress = renderAccountAddress;
    window.saveCustomerAddress = saveCustomerAddress;
    window.renderAccountSubscription = renderAccountSubscription;
    window.togglePushNotifications = togglePushNotifications;
    window.pushNotificationState = pushNotificationState;
    window.initPushNotifications = initPushNotifications;
    window.requestPushPermission = requestPushPermission;
    window.dismissPushPrompt = dismissPushPrompt;
    window.checkPushPromptTrigger = checkPushPromptTrigger;
    window.togglePushTopicPreference = togglePushTopicPreference;
    window.renderAccountPushPreferences = renderAccountPushPreferences;
    window.sendSimulatedPush = sendSimulatedPush;
    window.logoutBuyerSession = logoutBuyerSession;
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStoreApp);
  } else {
    initStoreApp();
  }

})();

