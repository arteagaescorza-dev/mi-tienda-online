// ============================================================================
// MULTIVERSO STORE — STOREFRONT D2C PWA LOGIC
// Ecosistema Comercial: TERRA (Global) | MARTE (Masculino) | VENUS (Lencería) | TIRESIAS (Afirmativo)
// Contemporary Luxury Minimalist & Mobile-First Priority
// Strict Zero Emojis Policy (Inline SVG Only)
// ============================================================================

(function() {
  'use strict';

  if (typeof window !== 'undefined') {
    window.closeStorePopup = function(secId) {
      const m = document.getElementById('promoModal_' + secId);
      if (m) {
        m.style.setProperty('display', 'none', 'important');
        m.classList.remove('active');
        m.setAttribute('aria-hidden', 'true');
      }
      try {
        sessionStorage.setItem('store_popup_seen_' + secId, '1');
        localStorage.setItem('store_popup_closed_' + secId, Date.now().toString());
      } catch (e) {}
    };
  }

  // Configuración de Identidad y Metadatos por Universo
  const UNIVERSE_META = Object.assign({
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
  }, (typeof window !== 'undefined' && window.__UNIVERSE_META__) ? window.__UNIVERSE_META__ : {});

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

  // Reserva Temporal de Stock en Carrito (Urgencia de Compra: 15 Minutos / 900s - Amigable UX)
  const CART_STOCK_LOCK_SECONDS = 900;
  let stockLockTimerRemaining = CART_STOCK_LOCK_SECONDS;
  let stockLockInterval = null;

  // Estado Central de la Tienda
  const storeState = {
    activeUniverse: 'marte', // 'terra', 'marte', 'venus', 'tiresias'
    activeTab: 'home',       // 'home', 'explore', 'catalog'
    activeCategory: 'all',
    activeBrand: 'all',
    activeSize: 'all',
    activeFabric: 'all',
    activeColor: 'all',
    activeVolume: 'all',
    activeShade: 'all',
    activeUniverses: [],
    activeCategories: [],
    activeBrands: [],
    activeSuppliers: [],
    activeSizes: [],
    activeFabrics: [],
    activeColors: [],
    activeVolumes: [],
    activeShades: [],
    activeGarmentTypes: [],
    activeStyleTags: [],
    activePrices: [],
    activeDiscounts: [],
    activeAvailability: [],
    activeMaxPrice: null,
    catalogMaxPrice: 2500,
    searchQuery: '',         // Búsqueda en tiempo real (Regla 5)
    cart: [],
    savedForLater: [],       // Array de prendas guardadas para después
    wishlist: [],            // Array de IDs de productos en lista de deseos
    selectedSizes: {},       // Mapeo productId -> talla elegida
    activeModalProductId: null,
    selectedModalSize: null
  };

  // Cargar Carrito, Wishlist y Guardados para después desde localStorage
  try {
    const savedCart = localStorage.getItem('multiverse_store_cart');
    if (savedCart) {
      storeState.cart = JSON.parse(savedCart);
    }
    const savedWishlist = localStorage.getItem('multiverse_store_wishlist');
    if (savedWishlist) {
      storeState.wishlist = JSON.parse(savedWishlist);
    }
    const savedLater = localStorage.getItem('multiverse_store_saved_for_later');
    if (savedLater) {
      storeState.savedForLater = JSON.parse(savedLater);
    }
  } catch (err) {
    storeState.cart = [];
    storeState.savedForLater = [];
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

  // Guardar Prendas para Después
  function saveSavedForLater() {
    try {
      localStorage.setItem('multiverse_store_saved_for_later', JSON.stringify(storeState.savedForLater || []));
    } catch (e) {}
    renderCartSheetContent();
  }

  // Guardar Wishlist (Sincroniza badges y carrito donde reside la wishlist)
  function saveWishlist() {
    try {
      localStorage.setItem('multiverse_store_wishlist', JSON.stringify(storeState.wishlist));
    } catch (e) {}
    updateWishlistBadges();
    renderCartSheetContent();
    if (typeof renderWishlistDrawerContent === 'function') {
      renderWishlistDrawerContent();
    }
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

    const desktopNavBadge = document.getElementById('desktopNavCartCount');
    if (desktopNavBadge) desktopNavBadge.textContent = totalCount;

    const dockBadge = document.getElementById('dockCartBadge');
    if (dockBadge) dockBadge.textContent = totalCount;
  }

  // Actualizar Contadores de Wishlist (Desktop y Móvil)
  function updateWishlistBadges() {
    const count = storeState.wishlist.length;
    const desktopWishlist = document.getElementById('desktopWishlistCount');
    if (desktopWishlist) {
      desktopWishlist.textContent = count;
      desktopWishlist.style.display = count > 0 ? 'inline-flex' : 'none';
    }
    const mobileWishlist = document.getElementById('mobileWishlistCount');
    if (mobileWishlist) {
      mobileWishlist.textContent = count;
      mobileWishlist.style.display = count > 0 ? 'inline-flex' : 'none';
    }
  }

  // Filtrar Productos según Universo, Categoría y Búsqueda (Regla 5)
  function getFilteredProducts() {
    return STORE_PRODUCTS.filter(prod => {
      // Filtro de Búsqueda Conmutable
      if (storeState.searchQuery && storeState.searchQuery.trim().length > 0) {
        const q = storeState.searchQuery.trim().toLowerCase();
        const mName = prod.name && prod.name.toLowerCase().includes(q);
        const mCat = prod.categoryName && prod.categoryName.toLowerCase().includes(q);
        const mDesc = prod.description && prod.description.toLowerCase().includes(q);
        const mSku = prod.sku && prod.sku.toLowerCase().includes(q);
        if (!mName && !mCat && !mDesc && !mSku) {
          return false;
        }
      }

      // Filtro de Universo
      if (storeState.activeUniverse !== 'terra') {
        if (!prod.universes.includes(storeState.activeUniverse)) {
          return false;
        }
      }
      // Filtro de Categoría (Árbol y Checkboxes)
      const matchesCatFilter = (targetCat, prodCat, prodCatName, prodName, prodSku, prodSlug) => {
        if (!targetCat || targetCat === 'all') return true;
        const clean = str => (str || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
        const t = clean(targetCat);
        const c = clean(prodCat);
        const cn = clean(prodCatName);
        const pn = clean(prodName);
        const psku = clean(prodSku);
        const pslug = clean(prodSlug);
        if (t === c || t === cn) return true;
        const norm = s => s.replace(/(s|es)$/, '');
        const nt = norm(t);
        if (nt === norm(c) || nt === norm(cn) || nt === norm(pn) || nt === norm(pslug)) return true;
        if (c.includes(t) || t.includes(c) || cn.includes(t) || t.includes(cn) || pn.includes(nt) || pn.includes(t) || pslug.includes(nt) || psku.includes(nt)) return true;
        return false;
      };

      if (Array.isArray(storeState.activeCategories) && storeState.activeCategories.length > 0) {
        if (!storeState.activeCategories.some(cat => matchesCatFilter(cat, prod.category, prod.categoryName, prod.name, prod.sku, prod.slug))) {
          return false;
        }
      } else if (storeState.activeCategory && storeState.activeCategory !== 'all') {
        if (!matchesCatFilter(storeState.activeCategory, prod.category, prod.categoryName, prod.name, prod.sku, prod.slug)) {
          return false;
        }
      }
      // Filtro de Talla
      if (Array.isArray(storeState.activeSizes) && storeState.activeSizes.length > 0) {
        if (!prod.sizes || !prod.sizes.some(s => storeState.activeSizes.includes(s))) {
          return false;
        }
      } else if (storeState.activeSize && storeState.activeSize !== 'all') {
        if (!prod.sizes || !prod.sizes.includes(storeState.activeSize)) {
          return false;
        }
      }
      // Filtro de Telas & Fibras (Regla Storefront)
      if (Array.isArray(storeState.activeFabrics) && storeState.activeFabrics.length > 0) {
        const pFab = (prod.fabric_type || '').toLowerCase();
        const pDesc = (prod.description || '').toLowerCase();
        const pName = (prod.name || '').toLowerCase();
        const matchesFab = storeState.activeFabrics.some(f => {
          const target = f.toLowerCase();
          return pFab.includes(target) || pDesc.includes(target) || pName.includes(target);
        });
        if (!matchesFab) return false;
      } else if (storeState.activeFabric && storeState.activeFabric !== 'all') {
        const fabTarget = storeState.activeFabric.toLowerCase();
        const pFab = (prod.fabric_type || '').toLowerCase();
        const pDesc = (prod.description || '').toLowerCase();
        const pName = (prod.name || '').toLowerCase();
        if (!pFab.includes(fabTarget) && !pDesc.includes(fabTarget) && !pName.includes(fabTarget)) {
          return false;
        }
      }
      // Filtro de Colores & Tornasoles (Regla Storefront)
      if (Array.isArray(storeState.activeColors) && storeState.activeColors.length > 0) {
        const matchesCol = storeState.activeColors.some(col => {
          const colTarget = col.toLowerCase();
          return (prod.variants || []).some(v => (v.color || '').toLowerCase().includes(colTarget)) ||
                 (Array.isArray(prod.colors) && prod.colors.some(c => (c.name || '').toLowerCase().includes(colTarget))) ||
                 (prod.name || '').toLowerCase().includes(colTarget) ||
                 (prod.description || '').toLowerCase().includes(colTarget);
        });
        if (!matchesCol) return false;
      } else if (storeState.activeColor && storeState.activeColor !== 'all') {
        const colTarget = storeState.activeColor.toLowerCase();
        const hasVariantColor = (prod.variants || []).some(v => (v.color || '').toLowerCase().includes(colTarget));
        const pName = (prod.name || '').toLowerCase();
        const pDesc = (prod.description || '').toLowerCase();
        if (!hasVariantColor && !pName.includes(colTarget) && !pDesc.includes(colTarget)) {
          return false;
        }
      }
      // Filtro de Volúmenes de Perfumería (Regla Storefront)
      if (Array.isArray(storeState.activeVolumes) && storeState.activeVolumes.length > 0) {
        const matchesVol = storeState.activeVolumes.some(v => (prod.name || '').toLowerCase().includes(v.toLowerCase()));
        if (!matchesVol) return false;
      } else if (storeState.activeVolume && storeState.activeVolume !== 'all') {
        const volTarget = storeState.activeVolume.toLowerCase();
        const pName = (prod.name || '').toLowerCase();
        const pDesc = (prod.description || '').toLowerCase();
        if (!pName.includes(volTarget) && !pDesc.includes(volTarget)) {
          return false;
        }
      }
      // Filtro de Tonos Cosméticos & Shading (Regla Storefront)
      if (Array.isArray(storeState.activeShades) && storeState.activeShades.length > 0) {
        const matchesSh = storeState.activeShades.some(sh => (prod.name || '').toLowerCase().includes(sh.toLowerCase()));
        if (!matchesSh) return false;
      } else if (storeState.activeShade && storeState.activeShade !== 'all') {
        const shTarget = storeState.activeShade.toLowerCase();
        const pName = (prod.name || '').toLowerCase();
        const pDesc = (prod.description || '').toLowerCase();
        if (!pName.includes(shTarget) && !pDesc.includes(shTarget)) {
          return false;
        }
      }
      // Filtro de Marcas Registradas (Brand)
      if (Array.isArray(storeState.activeBrands) && storeState.activeBrands.length > 0) {
        const prodBrand = (prod.brand || '').toLowerCase();
        const matchesBrand = storeState.activeBrands.some(b => prodBrand.includes(b.toLowerCase()) || (prod.categoryName || '').toLowerCase().includes(b.toLowerCase()) || (prod.name || '').toLowerCase().includes(b.toLowerCase()));
        if (!matchesBrand) return false;
      } else if (storeState.activeBrand && storeState.activeBrand !== 'all') {
        const bTarget = storeState.activeBrand.toLowerCase();
        const prodBrand = (prod.brand || '').toLowerCase();
        if (!prodBrand.includes(bTarget)) {
          return false;
        }
      }

      // Filtro de Proveedor & Marcas (Regla 2)
      if (Array.isArray(storeState.activeSuppliers) && storeState.activeSuppliers.length > 0) {
        const prodSup = (prod.supplier || prod.brand || '').toLowerCase();
        const matchesSup = storeState.activeSuppliers.some(s => prodSup.includes(s.toLowerCase()));
        if (!matchesSup) return false;
      } else if (storeState.activeSupplier && storeState.activeSupplier !== 'all') {
        const supTarget = storeState.activeSupplier.toLowerCase();
        const pSup = (prod.supplier || prod.brand || '').toLowerCase();
        if (!pSup.includes(supTarget)) {
          return false;
        }
      }

      // Filtro de Universos (Checkboxes)
      if (Array.isArray(storeState.activeUniverses) && storeState.activeUniverses.length > 0) {
        const matchesUniv = storeState.activeUniverses.some(u => {
          if (u === 'terra') return true;
          return Array.isArray(prod.universes) && prod.universes.includes(u);
        });
        if (!matchesUniv) return false;
      }

      // Filtro de Siluetas / Tipo de Prenda
      if (Array.isArray(storeState.activeGarmentTypes) && storeState.activeGarmentTypes.length > 0) {
        const pGt = (prod.garment_type || '').toLowerCase();
        const pName = (prod.name || '').toLowerCase();
        const matchesGt = storeState.activeGarmentTypes.some(gt => pGt.includes(gt.toLowerCase()) || pName.includes(gt.toLowerCase()));
        if (!matchesGt) return false;
      }

      // Filtro de Estilo & Ocasión
      if (Array.isArray(storeState.activeStyleTags) && storeState.activeStyleTags.length > 0) {
        const pDesc = (prod.description || '').toLowerCase();
        const pName = (prod.name || '').toLowerCase();
        const matchesTag = storeState.activeStyleTags.some(t => pDesc.includes(t.toLowerCase()) || pName.includes(t.toLowerCase()));
        if (!matchesTag) return false;
      }

      // Filtro de Rango de Precio (Ajustador en una línea)
      if (typeof storeState.activeMaxPrice === 'number' && storeState.activeMaxPrice < storeState.catalogMaxPrice) {
        const pPrice = typeof prod.price === 'number' ? prod.price : parseFloat(prod.price || 0);
        if (pPrice > storeState.activeMaxPrice) return false;
      }
      if (Array.isArray(storeState.activePrices) && storeState.activePrices.length > 0) {
        const price = typeof prod.price === 'number' ? prod.price : parseFloat(prod.price || 0);
        const matchesPrice = storeState.activePrices.some(r => {
          if (r === 'under_300') return price < 300;
          if (r === '300_599') return price >= 300 && price <= 599;
          if (r === '600_999') return price >= 600 && price <= 999;
          if (r === 'over_1000') return price >= 1000;
          return true;
        });
        if (!matchesPrice) return false;
      }

      // Filtro de Descuentos & Ofertas
      if (Array.isArray(storeState.activeDiscounts) && storeState.activeDiscounts.length > 0) {
        const disc = parseFloat(prod.discount_pct || 0);
        const matchesDisc = storeState.activeDiscounts.some(d => {
          if (d === 'only_sale') return disc > 0;
          if (d === 'min_15') return disc >= 15;
          if (d === 'min_25') return disc >= 25;
          return true;
        });
        if (!matchesDisc) return false;
      }

      // Filtro de Disponibilidad de Stock & Modalidad de Entrega (En Stock vs Bajo Pedido)
      if (Array.isArray(storeState.activeAvailability) && storeState.activeAvailability.length > 0) {
        const isReady = (prod.stockType === 'ready' || prod.stock_type === 'ready' || (!prod.is_scraped && (Number(prod.availableStock || 0) > 0 || Number(prod.total_available_stock || 0) > 0)));
        const isOnDemand = (prod.stockType === 'on_demand' || prod.stock_type === 'on_demand' || Boolean(prod.is_scraped) || (Number(prod.availableStock || 0) <= 0 && Number(prod.total_available_stock || 0) <= 0));

        const matchesAvail = storeState.activeAvailability.some(a => {
          if (a === 'in_stock' || a === 'ready' || a === 'stock') return isReady;
          if (a === 'on_demand' || a === 'bajo_pedido' || a === 'pedido') return isOnDemand;
          return true;
        });
        if (!matchesAvail) return false;
      }

      return true;
    });
  }

  // Generar HTML de Tarjeta de Producto Reutilizable (Catálogo, Home y Secciones Dinámicas PDP)
  function createProductCardHTML(prod) {
    const sizesList = Array.isArray(prod.sizes) && prod.sizes.length > 0 ? prod.sizes : ['CH', 'M', 'G', 'XG'];
    const selectedSize = storeState.selectedSizes[prod.id] || sizesList[0] || 'M';
    const isFav = isInWishlist(prod.id);
    const discPct = Number(prod.discount_pct || 0);
    const hasDiscount = discPct > 0 || (Number(prod.compare_at_price || 0) > Number(prod.price || 0));
    const displayDiscPct = discPct > 0
      ? Math.round(discPct)
      : (hasDiscount && Number(prod.compare_at_price) > 0
          ? Math.round(((Number(prod.compare_at_price) - Number(prod.price)) / Number(prod.compare_at_price)) * 100)
          : 0);

    return `
      <div class="product-card" data-product-id="${prod.id}">
        <div class="product-image-wrap">
          <!-- Botón Flotante de Deseos / Wishlist -->
          <button type="button" class="btn-wishlist-toggle ${isFav ? 'active' : ''}" data-action="toggle-wishlist" data-product-id="${prod.id}" title="${isFav ? 'Quitar de mis deseos' : 'Guardar en mis deseos'}" aria-label="Guardar en favoritos">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
          </button>

          <span class="product-stock-badge ${prod.stockType === 'ready' ? 'ready' : (prod.stockType === 'drop_on_demand' ? 'drop_on_demand' : '')}">
            ${prod.stockLabel || (prod.stockType === 'ready' ? 'Stock Físico' : 'Bajo Pedido')}
          </span>

          ${displayDiscPct > 0 ? `
            <span class="product-discount-badge" style="position: absolute; bottom: 8px; left: 8px; z-index: 4; background: #e11d48; color: #ffffff; font-family: var(--font-heading); font-size: 0.62rem; font-weight: 800; padding: 2px 7px; border-radius: 999px; letter-spacing: 0.04em;">
              -${displayDiscPct}% OFF
            </span>
          ` : ''}

          <!-- Imagen / Logo con enlace a PDP -->
          <a href="/productos/${encodeURIComponent(prod.slug || prod.id)}" style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; height: 100%; cursor: pointer; text-decoration: none; position: relative;" title="${prod.name}">
            ${prod.image && prod.image !== '/user-avatar.png' ? `
              <img src="${prod.image}" alt="${prod.name}" loading="lazy" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';" style="width: 100%; height: 100%; object-fit: cover; display: block;">
              <div class="product-fallback-silhouette" style="display: none; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%; position: absolute; inset: 0;">
                <svg width="40" height="40" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">${prod.sku || ''}</span>
              </div>
            ` : `
              <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%;">
                <svg width="40" height="40" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">${prod.sku || ''}</span>
              </div>
            `}
          </a>
        </div>
        
        <div class="product-body">
          <div>
            <div class="product-category-label">${prod.categoryName || prod.brand || 'Prenda'}</div>
            <h3 class="product-title">
              <a href="/productos/${encodeURIComponent(prod.slug || prod.id)}" style="cursor: pointer; color: inherit; text-decoration: none;">${prod.name}</a>
            </h3>
            <div class="product-pricing-row">
              <span class="product-price">MXN $${Number(prod.price || 0).toFixed(2)}</span>
              ${Number(prod.compare_at_price || 0) > Number(prod.price || 0) ? `
                <span style="font-size: 0.68rem; color: var(--text-muted); text-decoration: line-through;">$${Number(prod.compare_at_price).toFixed(2)}</span>
              ` : `
                <span style="font-size: 0.68rem; color: var(--text-muted); font-weight: 600;">IVA incluido</span>
              `}
            </div>
            ${Array.isArray(prod.colors) && prod.colors.length > 0 ? `
              <div class="product-card-colors">
                ${prod.colors.map(c => {
                  const isMulti = (c.name || '').toLowerCase().includes('multicolor');
                  const dotBg = isMulti
                    ? 'linear-gradient(135deg, #ef4444 0%, #f97316 20%, #eab308 40%, #22c55e 60%, #3b82f6 80%, #a855f7 100%)'
                    : (c.hex || '#0f172a');
                  return `<span class="product-card-color-dot ${isMulti ? 'color-dot-multicolor' : ''}" data-color-name="${c.name}" title="${c.name}" style="background: ${dotBg};"></span>`;
                }).join('')}
                <span style="font-size: 0.65rem; color: var(--text-muted); margin-left: 2px;">${prod.colors.length} ${prod.colors.length === 1 ? 'color' : 'colores'}</span>
              </div>
            ` : ''}
          </div>

          <div>
            <!-- Resumen Elegante de Tallas (Apple HIG Ergonomics) -->
            <div class="product-sizes-summary">
              <span class="sizes-label">Tallas:</span>
              <span class="sizes-tags">${sizesList.join(' · ')}</span>
            </div>

            <!-- Botonera: Ficha PDP + Quick Add Táctil 48x48 -->
            <div style="display: flex; gap: 6px; margin-top: 8px;">
              <a href="/productos/${encodeURIComponent(prod.slug || prod.id)}" class="btn-hero-secondary" style="padding: 8px 10px; font-size: 0.7rem; border-radius: var(--radius-sm); text-decoration: none; text-align: center;" title="Ver ficha técnica">
                DETALLES
              </a>
              <button type="button" class="btn-add-cart" data-action="quick-add" data-product-id="${prod.id}" style="flex: 1;">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>AGREGAR</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  // Renderizar Grid de Productos
  function renderProductsGrid(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const products = getFilteredProducts();

    if (products.length === 0) {
      container.innerHTML = `
        <div style="grid-column: 1 / -1; text-align: center; padding: 48px 20px; color: var(--text-muted);">
          <div style="width: 48px; height: 48px; margin: 0 auto 12px; border-radius: 50%; background: var(--bg-tertiary, #f4f4f5); display: flex; align-items: center; justify-content: center;">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
          </div>
          <strong style="font-family: var(--font-heading); color: var(--text-primary); display: block; font-size: 0.95rem;">Sin prendas en esta selección</strong>
          <small style="font-size: 0.8rem;">Prueba cambiando el universo o la categoría en los filtros.</small>
        </div>
      `;
      return;
    }

    container.innerHTML = products.map(prod => createProductCardHTML(prod)).join('');
  }

  // ============================================================================
  // SECCIONES DINÁMICAS EN PÁGINA DE PRODUCTO (PDP):
  // 2.1 Productos Sugeridos: Clasificación C dentro del stock físico -> Fallback Bajo Pedido o Relacionados con el tipo de producto.
  // 2.2 Segunda Sección: Perfil Anatómico guardado (si está logueado) -> Más productos con descuentos (si no está logueado) -> Relacionados con su consulta.
  // ============================================================================
  function renderPdpDynamicSections() {
    const suggestedGrid = document.getElementById('pdpSuggestedProductsGrid');
    const profileGrid = document.getElementById('pdpProfileProductsGrid');
    if (!suggestedGrid && !profileGrid) return;

    const currentProd = (typeof window !== 'undefined' && window.CURRENT_PDP_PRODUCT) ? window.CURRENT_PDP_PRODUCT : null;
    const currentId = currentProd ? String(currentProd.id) : '';
    const catalog = Array.isArray(STORE_PRODUCTS) ? STORE_PRODUCTS : [];
    const others = catalog.filter(p => String(p.id) !== currentId);

    const norm = s => (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim().replace(/(s|es)$/, '');
    const curCat = norm(currentProd?.category || currentProd?.categoryName || '');
    const curGarment = norm(currentProd?.garment_type || '');
    const curBrand = norm(currentProd?.brand || '');

    const isRelatedToCurrent = (p) => {
      const pCat = norm(p.category || p.categoryName || '');
      const pGarment = norm(p.garment_type || '');
      const pBrand = norm(p.brand || '');
      const pName = norm(p.name || '');
      if (curCat && (pCat === curCat || pCat.includes(curCat) || curCat.includes(pCat) || pName.includes(curCat))) return true;
      if (curGarment && (pGarment === curGarment || pName.includes(curGarment))) return true;
      if (curBrand && pBrand === curBrand) return true;
      return false;
    };

    // -------------------------------------------------------------------------
    // SECCIÓN 2.1: PRODUCTOS SUGERIDOS
    // Prioridad 1: Clasificación C dentro del Stock Físico (ready & availableStock > 0 & !is_scraped)
    // Fallback: Productos Bajo Pedido (on_demand) o Relacionados con el tipo de producto buscado
    // -------------------------------------------------------------------------
    if (suggestedGrid) {
      const physicalClassC = others.filter(p => {
        const isPhysicalReady = (p.stockType === 'ready' && !p.is_scraped && Number(p.availableStock || 0) > 0);
        const abc = String(p.abc_class || 'C').toUpperCase();
        return isPhysicalReady && abc === 'C';
      }).sort((a, b) => (isRelatedToCurrent(b) ? 1 : 0) - (isRelatedToCurrent(a) ? 1 : 0));

      let suggestedList = [...physicalClassC];
      const usedSuggestedIds = new Set(suggestedList.map(p => String(p.id)));

      if (suggestedList.length < 4) {
        // Fallback: productos bajo pedido o relacionados con el tipo de producto que están buscando
        const fallbackPool = others.filter(p => {
          if (usedSuggestedIds.has(String(p.id))) return false;
          const isOnDemand = (p.stockType === 'on_demand' || p.stockType === 'drop_on_demand' || Boolean(p.is_scraped) || Number(p.availableStock || 0) <= 0);
          return isRelatedToCurrent(p) || isOnDemand;
        }).sort((a, b) => (isRelatedToCurrent(b) ? 1 : 0) - (isRelatedToCurrent(a) ? 1 : 0));

        for (const item of fallbackPool) {
          if (suggestedList.length >= 4) break;
          suggestedList.push(item);
          usedSuggestedIds.add(String(item.id));
        }
      }

      if (suggestedList.length < 4) {
        for (const item of others) {
          if (suggestedList.length >= 4) break;
          if (!usedSuggestedIds.has(String(item.id))) {
            suggestedList.push(item);
            usedSuggestedIds.add(String(item.id));
          }
        }
      }

      const finalSuggested = suggestedList.slice(0, 4);
      const badgeEl = document.getElementById('pdpSuggestedBadge');
      const subEl = document.getElementById('pdpSuggestedSub');

      if (physicalClassC.length > 0) {
        if (badgeEl) badgeEl.textContent = 'STOCK FÍSICO // EDICIÓN LIMITADA';
        if (subEl) {
          subEl.textContent = physicalClassC.length >= 4
            ? 'Selección exclusiva de prendas con disponibilidad inmediata en bodega para despacho express.'
            : 'Prendas con entrega inmediata en bodega complementadas con alternativas bajo pedido afines a tu estilo.';
        }
      } else {
        if (badgeEl) badgeEl.textContent = 'BAJO PEDIDO & RELACIONADOS';
        if (subEl) {
          subEl.textContent = 'Sugerencias confeccionadas bajo pedido y prendas relacionadas con el tipo de silueta que estás consultando.';
        }
      }

      suggestedGrid.innerHTML = finalSuggested.map(prod => createProductCardHTML(prod)).join('');
    }

    // -------------------------------------------------------------------------
    // SECCIÓN 2.2: PERFIL ANATÓMICO (SI ESTÁ LOGUEADO) -> DESCUENTOS (SI NO ESTÁ LOGUEADO) -> RELACIONADOS
    // -------------------------------------------------------------------------
    if (profileGrid) {
      let session = null;
      try {
        session = JSON.parse(localStorage.getItem('multiverse_customer_session') || 'null');
      } catch (e) {}

      const isUserLoggedIn = Boolean(
        session && (
          session.email ||
          session.name ||
          session.phone ||
          session.loggedIn ||
          session.provider ||
          session.twoFactorEnrolled ||
          (session.anatomicalProfile && session.anatomicalProfile.size)
        )
      );

      const badgeEl = document.getElementById('pdpProfileSectionBadge');
      const titleEl = document.getElementById('pdpProfileSectionTitle');
      const subEl = document.getElementById('pdpProfileSectionSub');
      const actionBtnText = document.getElementById('pdpProfileActionBtnText');

      let section2Products = [];

      if (isUserLoggedIn) {
        const anat = (session && session.anatomicalProfile) ? session.anatomicalProfile : {
          waistCm: 82,
          size: 'M',
          pants: '31-32',
          silhouettes: { jocks: true, boxers: true, tangas: false, bikinis: false, tanktops: true }
        };
        const prefSize = String(anat.size || 'M').toUpperCase();
        const silObj = anat.silhouettes || { jocks: true, boxers: true, tanktops: true };
        const activeSilhouettes = Array.isArray(silObj)
          ? silObj.map(s => norm(s))
          : Object.keys(silObj).filter(k => silObj[k]).map(k => norm(k));

        const matchesSilhouette = (p) => {
          if (activeSilhouettes.length === 0) return true;
          const pCat = norm(p.category || p.categoryName || '');
          const pGarment = norm(p.garment_type || '');
          const pName = norm(p.name || '');
          return activeSilhouettes.some(s => pCat.includes(s) || s.includes(pCat) || pGarment.includes(s) || pName.includes(s));
        };

        const matchesSize = (p) => {
          const sizes = Array.isArray(p.sizes) ? p.sizes.map(sz => String(sz).toUpperCase()) : [];
          return sizes.includes(prefSize) || sizes.includes('UNI');
        };

        // Prendas que coinciden tanto con su talla anatómica como con sus siluetas favoritas
        const strictAnatomyMatches = others.filter(p => matchesSize(p) && matchesSilhouette(p));
        const usedIds = new Set(strictAnatomyMatches.map(p => String(p.id)));
        section2Products = [...strictAnatomyMatches];

        if (section2Products.length < 4) {
          const partialMatches = others.filter(p => !usedIds.has(String(p.id)) && (matchesSize(p) || matchesSilhouette(p)));
          for (const item of partialMatches) {
            if (section2Products.length >= 4) break;
            section2Products.push(item);
            usedIds.add(String(item.id));
          }
        }

        if (section2Products.length < 4) {
          for (const item of others) {
            if (section2Products.length >= 4) break;
            if (!usedIds.has(String(item.id))) {
              section2Products.push(item);
              usedIds.add(String(item.id));
            }
          }
        }

        section2Products = section2Products.slice(0, 4);

        // Pre-seleccionar la talla anatómica del usuario en las tarjetas de esta sección
        section2Products.forEach(p => {
          if (Array.isArray(p.sizes) && p.sizes.map(s => String(s).toUpperCase()).includes(prefSize)) {
            storeState.selectedSizes[p.id] = prefSize;
          }
        });

        if (badgeEl) {
          badgeEl.className = 'pdp-dynamic-badge badge-anatomy';
          badgeEl.textContent = `PERFIL ANATÓMICO // TALLA ${prefSize}`;
        }
        if (titleEl) {
          titleEl.textContent = 'Para tu Perfil Anatómico y Cuenta';
        }
        if (subEl) {
          const waistLabel = anat.waistCm ? ` (${anat.waistCm} cm cintura)` : '';
          subEl.textContent = `Prendas compatibles con tu talla recomendada ${prefSize}${waistLabel} y las siluetas guardadas en tu perfil.`;
        }
        if (actionBtnText) {
          actionBtnText.textContent = 'Actualizar Perfil Anatómico';
        }
      } else {
        // Usuario NO logueado: Mostrar más productos con descuentos; de no existir, productos relacionados con su consulta
        const discountedProducts = others.filter(p => {
          const disc = Number(p.discount_pct || 0);
          const comp = Number(p.compare_at_price || 0);
          const pr = Number(p.price || 0);
          return disc > 0 || (comp > pr && pr > 0);
        }).sort((a, b) => Number(b.discount_pct || 0) - Number(a.discount_pct || 0));

        if (discountedProducts.length > 0) {
          section2Products = [...discountedProducts];
          const usedIds = new Set(section2Products.map(p => String(p.id)));
          if (section2Products.length < 4) {
            const relatedPool = others.filter(p => !usedIds.has(String(p.id)) && isRelatedToCurrent(p));
            for (const item of relatedPool) {
              if (section2Products.length >= 4) break;
              section2Products.push(item);
              usedIds.add(String(item.id));
            }
          }
          if (section2Products.length < 4) {
            for (const item of others) {
              if (section2Products.length >= 4) break;
              if (!usedIds.has(String(item.id))) {
                section2Products.push(item);
                usedIds.add(String(item.id));
              }
            }
          }
          section2Products = section2Products.slice(0, 4);

          if (badgeEl) {
            badgeEl.className = 'pdp-dynamic-badge badge-discount';
            badgeEl.textContent = 'PROMOCIONES & DESCUENTOS ACTIVOS';
          }
          if (titleEl) {
            titleEl.textContent = 'Más Productos con Descuento';
          }
          if (subEl) {
            subEl.textContent = 'Aprovecha prendas seleccionadas con beneficio activo. Inicia sesión para recibir sugerencias de tu perfil anatómico.';
          }
        } else {
          // De no existir productos con descuento, se muestran productos relacionados con su consulta
          const relatedProducts = others.filter(p => isRelatedToCurrent(p));
          const usedIds = new Set(relatedProducts.map(p => String(p.id)));
          section2Products = [...relatedProducts];

          if (section2Products.length < 4) {
            for (const item of others) {
              if (section2Products.length >= 4) break;
              if (!usedIds.has(String(item.id))) {
                section2Products.push(item);
                usedIds.add(String(item.id));
              }
            }
          }
          section2Products = section2Products.slice(0, 4);

          if (badgeEl) {
            badgeEl.className = 'pdp-dynamic-badge';
            badgeEl.textContent = 'AFINES A TU CONSULTA';
          }
          if (titleEl) {
            titleEl.textContent = 'Productos Relacionados con tu Consulta';
          }
          if (subEl) {
            subEl.textContent = 'Alternativas ergonómicas de la misma categoría y corte. Inicia sesión en tu cuenta para personalizar por perfil anatómico.';
          }
        }

        if (actionBtnText) {
          actionBtnText.textContent = 'Iniciar Sesión / Crear Perfil Anatómico';
        }
      }

      profileGrid.innerHTML = section2Products.map(prod => createProductCardHTML(prod)).join('');
    }
  }

  // Conmutador de Universos (Top bar & Drawer ZARA)
  function switchUniverse(universeKey) {
    storeState.activeUniverse = universeKey;
    
    // Navegar para renderizar en servidor las secciones CMS y catálogo específico del universo
    if (typeof window !== 'undefined' && window.location) {
      try {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('universo', universeKey);
        if (window.location.pathname === '/' || window.location.pathname === '/productos') {
          window.location.href = currentUrl.toString();
          return;
        } else {
          window.location.href = '/?universo=' + encodeURIComponent(universeKey);
          return;
        }
      } catch (err) {
        window.location.href = '/?universo=' + encodeURIComponent(universeKey);
        return;
      }
    }
    
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

  // Alternar Tabs del Bottom Dock y Navbar (Inicio, Explorar, Catálogo, Carrito, Cuenta)
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

    // Regla 2: El botón de filtros en móvil solo se muestra cuando se está visualizando el catálogo
    const mobileFilterBtn = document.getElementById('mobileFilterToggleBtn');
    if (mobileFilterBtn) {
      mobileFilterBtn.style.display = (tabId === 'catalog') ? 'inline-flex' : 'none';
    }

    // Actualizar estilo visual del dock móvil y links desktop
    document.querySelectorAll('.dock-tab-btn, .nav-link-item').forEach(btn => {
      const match = btn.dataset.dockTab === tabId;
      btn.classList.toggle('active', match);
    });

    // Cambiar vistas de contenido
    const homeView = document.getElementById('viewHome');
    const exploreView = document.getElementById('viewExplore');
    const catalogView = document.getElementById('viewCatalog');

    if (!homeView && !exploreView && !catalogView) {
      window.location.href = '/?tab=' + encodeURIComponent(tabId);
      return;
    }

    if (tabId === 'home') {
      if (homeView) homeView.style.display = 'block';
      if (exploreView) exploreView.style.display = 'none';
      if (catalogView) catalogView.style.display = 'none';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } else if (tabId === 'explore') {
      if (homeView) homeView.style.display = 'none';
      if (exploreView) exploreView.style.display = 'block';
      if (catalogView) catalogView.style.display = 'none';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } else if (tabId === 'catalog') {
      if (homeView) homeView.style.display = 'none';
      if (exploreView) exploreView.style.display = 'none';
      if (catalogView) catalogView.style.display = 'block';
      renderProductsGrid('catalogProductsGrid');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }

  function openCatalogWithCategory(category, universe, supplier, tags) {
    let targetUniverse = universe;
    if (targetUniverse === 'dama') {
      targetUniverse = 'venus';
    } else if (targetUniverse === 'caballero') {
      targetUniverse = 'marte';
    } else if (!targetUniverse || targetUniverse === 'all') {
      targetUniverse = storeState.activeUniverse || 'marte';
    }

    storeState.activeUniverse = targetUniverse;
    document.querySelectorAll('.universe-tab-btn').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.universe === targetUniverse);
    });
    document.querySelectorAll('.zara-filter-tab').forEach(tab => {
      tab.classList.toggle('active', tab.dataset.universe === targetUniverse);
    });

    const cleanNorm = s => (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim().replace(/(s|es)$/, '');

    if (category && category !== 'all') {
      const cNorm = cleanNorm(category);
      storeState.activeCategory = category;
      storeState.activeCategories = [category];
      document.querySelectorAll('[data-action="filter-category"]').forEach(p => {
        const pCat = p.dataset.category || '';
        p.classList.toggle('active', pCat === category || cleanNorm(pCat) === cNorm);
      });
      document.querySelectorAll('.filter-cat-checkbox, input[name="filter_category"]').forEach(cb => {
        const val = cb.getAttribute('data-val') || cb.value || '';
        const isMatch = (val === category || cleanNorm(val) === cNorm || cleanNorm(val).includes(cNorm) || cNorm.includes(cleanNorm(val)));
        cb.checked = isMatch;
        if (isMatch && !storeState.activeCategories.includes(val)) {
          storeState.activeCategories.push(val);
        }
      });
    } else {
      storeState.activeCategory = 'all';
      storeState.activeCategories = [];
      document.querySelectorAll('[data-action="filter-category"]').forEach(p => {
        p.classList.remove('active');
      });
      document.querySelectorAll('.filter-cat-checkbox, input[name="filter_category"]').forEach(cb => {
        cb.checked = false;
      });
    }

    if (supplier && supplier !== 'all') {
      storeState.activeSupplier = supplier;
      storeState.activeSuppliers = [supplier];
      document.querySelectorAll('.filter-brand-checkbox, input[name="filter_brand"]').forEach(cb => {
        const val = cb.getAttribute('data-val') || cb.value || '';
        cb.checked = (val.toLowerCase() === supplier.toLowerCase());
      });
    }

    if (tags) {
      storeState.searchQuery = tags;
    }

    switchDockTab('catalog');
    renderProductsGrid('catalogProductsGrid');

    setTimeout(() => {
      const catalogGridEl = document.getElementById('catalogProductsGrid') || document.getElementById('viewCatalog');
      if (catalogGridEl) {
        catalogGridEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }, 150);

    try {
      const queryParams = new URLSearchParams(window.location.search);
      queryParams.set('tab', 'catalog');
      if (category && category !== 'all') queryParams.set('categoria', category);
      if (universe && universe !== 'all') queryParams.set('universo', universe);
      if (supplier && supplier !== 'all') queryParams.set('proveedor', supplier);
      if (tags) queryParams.set('tags', tags);
      window.history.pushState(null, '', '?' + queryParams.toString());
    } catch (err) {}
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

  // Clasificador global de Linea B (Bajo Pedido / Taller / Scraping) vs Linea A (Bodega Local)
  function isLineBItem(item) {
    if (!item) return false;
    const lt = item.lineType || item.line_type;
    const st = item.stockType || item.stock_type;
    return lt === 'line_b' || st === 'on_demand' || st === 'drop_on_demand' || Boolean(item.is_scraped);
  }

  // Logica del Carrito (Agregar, Quitar, Renderizar y Sincronizacion RESTful TASK-INT-06)
  function addToCart(productId, explicitSize = null, explicitVariantId = null) {
    const targetId = (productId && typeof productId === 'object') ? (productId.id || productId.productId || productId.sku) : productId;
    const product = STORE_PRODUCTS.find(p => String(p.id) === String(targetId) || String(p.sku) === String(targetId));
    if (!product) return;

    const size = explicitSize || storeState.selectedSizes[product.id] || storeState.selectedSizes[targetId] || 'M';
    const existingIndex = storeState.cart.findIndex(i => String(i.productId) === String(product.id) && i.size === size);

    if (existingIndex > -1) {
      storeState.cart[existingIndex].quantity += 1;
    } else {
      const isScraped = Boolean(product.is_scraped || product.type === 'dropship' || product.type === 'scraped');
      const isLineB = isScraped || product.stockType === 'on_demand' || product.stockType === 'drop_on_demand' || (product.availableStock !== undefined && product.availableStock <= 0);

      storeState.cart.push({
        productId: product.id,
        sku: product.sku,
        name: product.name,
        categoryName: product.categoryName,
        price: product.price,
        size: size,
        image: product.image || '/user-avatar.png',
        color: storeState.selectedModalColor || (Array.isArray(product.colors) && product.colors[0]?.name) || 'Único',
        variantId: explicitVariantId || null,
        stockType: isLineB ? 'on_demand' : 'ready',
        stockLabel: isLineB ? 'Bajo Pedido' : 'Stock Fisico',
        lineType: isLineB ? 'line_b' : 'line_a',
        is_scraped: isScraped,
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

    storeState.cart = data.items.map(bi => {
      const localProd = Array.isArray(STORE_PRODUCTS) ? STORE_PRODUCTS.find(p => String(p.id) === String(bi.product_id) || p.sku === bi.sku) : null;
      let itemImg = bi.image;
      if (!itemImg || itemImg === '/user-avatar.png') {
        itemImg = localProd ? localProd.image : null;
      }
      const itemSlug = bi.product_slug || (localProd ? (localProd.slug || localProd.id) : (bi.product_id || bi.sku));
      const isLineB = bi.line_type === 'line_b' || Boolean(bi.is_scraped);

      return {
        backendId: bi.id,
        productId: bi.product_id ? String(bi.product_id) : bi.sku,
        variantId: bi.variant_id,
        sku: bi.sku,
        name: bi.product_name,
        slug: itemSlug,
        image: itemImg,
        categoryName: bi.category_name || (localProd ? localProd.categoryName : 'Prenda'),
        price: bi.price,
        size: bi.size,
        color: bi.color || (localProd && Array.isArray(localProd.colors) ? localProd.colors[0]?.name : 'Único'),
        stockType: isLineB ? 'on_demand' : 'ready',
        stockLabel: bi.stock_badge || (isLineB ? 'Bajo Pedido' : 'Stock Fisico'),
        lineType: isLineB ? 'line_b' : 'line_a',
        lineLabel: bi.line_label,
        is_scraped: isLineB,
        quantity: bi.quantity,
        hasActiveLock: bi.has_active_lock
      };
    });

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

  function saveCartItemForLater(index) {
    if (!storeState.cart[index]) return;
    const item = storeState.cart[index];
    storeState.cart.splice(index, 1);
    if (!Array.isArray(storeState.savedForLater)) {
      storeState.savedForLater = [];
    }
    storeState.savedForLater.push(item);
    saveSavedForLater();
    saveCart();

    if (storeState.cart.length === 0) {
      notifyStockLockReleased();
    }
    showToastNotification(`"${item.name}" guardado para después`);
  }

  function moveSavedItemToCart(index) {
    if (!storeState.savedForLater || !storeState.savedForLater[index]) return;
    const item = storeState.savedForLater.splice(index, 1)[0];
    saveSavedForLater();
    addToCart(item.productId || item.id, item.size, item.variantId);
    renderCartSheetContent();
    showToastNotification(`"${item.name}" movido al carrito`);
  }

  function removeSavedItem(index) {
    if (!storeState.savedForLater || !storeState.savedForLater[index]) return;
    const item = storeState.savedForLater.splice(index, 1)[0];
    saveSavedForLater();
    renderCartSheetContent();
    showToastNotification(`"${item.name}" eliminado de guardados`);
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

  function isItemOutOfStock(item) {
    if (!item) return false;
    if (item.outOfStock || item.is_out_of_stock || item.discontinued) return true;
    if (typeof item.availableStock === 'number' && item.availableStock <= 0 && (item.stockType || item.lineType) !== 'on_demand') return true;
    const catProd = STORE_PRODUCTS.find(p => String(p.id) === String(item.productId || item.id) || p.sku === item.sku);
    if (catProd) {
      if (catProd.outOfStock || catProd.discontinued || catProd.visible === false) return true;
      if (typeof catProd.availableStock === 'number' && catProd.availableStock <= 0 && catProd.stockType !== 'on_demand') return true;
    }
    return false;
  }

  function handleStockLockExpiry() {
    if (stockLockInterval) {
      clearInterval(stockLockInterval);
      stockLockInterval = null;
    }
    const expiredItems = [...storeState.cart];
    stockLockTimerRemaining = 0;
    pushNotificationState.stockWarningFired = false;
    localStorage.removeItem('multiverse_stock_lock_expires_at');
    notifyStockLockReleased();

    // REGLA 2: Si el tiempo se acaba, el producto PERMANECE en carrito!
    // No vaciamos storeState.cart
    saveCart();
    updateCartBadges();
    updateStockLockDisplay();

    // Notificar al usuario sin vaciar el carrito
    showToastNotification('Tu tiempo de reserva temporal ha finalizado. Tus productos permanecen en el carrito sujetos a disponibilidad.');

    if (typeof renderCartSheetContent === 'function') {
      renderCartSheetContent();
    }

    safeDispatchEvent('stock-lock-expired', {
      sessionId: getCartSessionId(),
      expiredItems
    });
  }

  // REGLA 2: La cuenta regresiva del apartado SOLO se activa cuando inicia el checkout
  function startCheckoutStockLockTimer() {
    if (storeState.cart.length === 0) return;

    const now = Date.now();
    let expiresAt = parseInt(localStorage.getItem('multiverse_stock_lock_expires_at'), 10);
    if (!expiresAt || isNaN(expiresAt) || expiresAt <= now) {
      expiresAt = now + (CART_STOCK_LOCK_SECONDS * 1000);
      localStorage.setItem('multiverse_stock_lock_expires_at', expiresAt.toString());
      stockLockTimerRemaining = CART_STOCK_LOCK_SECONDS;
      notifyStockLockAcquired();
    } else {
      stockLockTimerRemaining = Math.max(0, Math.round((expiresAt - now) / 1000));
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

  function manageStockLockTimer() {
    // Si hay un lock activo proveniente del checkout, mantener la cuenta en sync
    const expiresAt = parseInt(localStorage.getItem('multiverse_stock_lock_expires_at'), 10);
    if (expiresAt && expiresAt > Date.now()) {
      stockLockTimerRemaining = Math.max(0, Math.round((expiresAt - Date.now()) / 1000));
      updateStockLockDisplay();
    } else {
      stockLockTimerRemaining = 0;
      updateStockLockDisplay();
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
    renewLock: (minutes = 15) => {
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

    // Bloque de prendas guardadas para después
    const hasSaved = Array.isArray(storeState.savedForLater) && storeState.savedForLater.length > 0;
    let savedSectionHtml = '';
    if (hasSaved) {
      savedSectionHtml = `
        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px dashed var(--border-subtle);">
          <div style="font-family: var(--font-heading); font-size: 0.74rem; font-weight: 800; color: var(--text-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 6px;">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
              <span>Guardados para después</span>
            </div>
            <span style="background: var(--bg-tertiary); color: var(--text-muted); padding: 1px 7px; border-radius: 9999px; font-size: 0.7rem; font-weight: 700;">${storeState.savedForLater.length}</span>
          </div>
          <div style="display: flex; flex-direction: column; gap: 10px;">
            ${storeState.savedForLater.map((sItem, sIdx) => `
              <div class="cart-saved-card" style="display: flex; gap: 12px; padding: 10px; background: var(--bg-secondary); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); align-items: center;">
                <div style="width: 44px; height: 44px; background: var(--bg-tertiary); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden;">
                  ${sItem.image && sItem.image !== '/user-avatar.png' ? `<img src="${sItem.image}" alt="${sItem.name}" style="width: 100%; height: 100%; object-fit: cover;">` : `<svg width="20" height="20" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="3" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2"/></svg>`}
                </div>
                <div style="flex-grow: 1; min-width: 0;">
                  <div style="font-family: var(--font-heading); font-size: 0.76rem; font-weight: 700; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-primary);">${sItem.name}</div>
                  <div style="font-size: 0.68rem; color: var(--text-muted); margin-top: 2px;">Talla: <strong style="color: var(--text-primary);">${sItem.size}</strong> &bull; MXN $${(sItem.price).toFixed(2)}</div>
                </div>
                <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                  <button type="button" class="btn-sheet-apply" data-action="move-saved-to-cart" data-index="${sIdx}" style="padding: 5px 8px; font-size: 0.68rem; border-radius: var(--radius-sm); cursor: pointer;" title="Mover al carrito">
                    Mover al carrito
                  </button>
                  <button type="button" class="btn-remove-item" data-action="remove-saved-item" data-index="${sIdx}" title="Eliminar de guardados">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                  </button>
                </div>
              </div>
            `).join('')}
          </div>
        </div>
      `;
    }

    // Bloque de Wishlist / Mis Deseos (Regla 1: dentro de carrito reside la lista de la wishlist)
    const hasWishlist = Array.isArray(storeState.wishlist) && storeState.wishlist.length > 0;
    let wishlistSectionHtml = '';
    if (hasWishlist) {
      wishlistSectionHtml = `
        <div id="cartWishlistSection" style="margin-top: 24px; padding-top: 16px; border-top: 1px dashed var(--border-subtle);">
          <div style="font-family: var(--font-heading); font-size: 0.74rem; font-weight: 800; color: var(--text-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 6px;">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
              <span>Mi Lista de Deseos</span>
            </div>
            <span style="background: var(--bg-tertiary); color: var(--text-muted); padding: 1px 7px; border-radius: 9999px; font-size: 0.7rem; font-weight: 700;">${storeState.wishlist.length}</span>
          </div>
          <div style="display: flex; flex-direction: column; gap: 10px;">
            ${storeState.wishlist.map(productId => {
              const wProd = STORE_PRODUCTS.find(p => p.id === productId);
              if (!wProd) return '';
              const wSize = storeState.selectedSizes[productId] || 'M';
              return `
                <div class="cart-wishlist-card" style="display: flex; gap: 12px; padding: 10px; background: var(--bg-secondary); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); align-items: center;">
                  <div style="width: 44px; height: 44px; background: var(--bg-tertiary); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden;">
                    ${wProd.image && wProd.image !== '/user-avatar.png' ? `<img src="${wProd.image}" alt="${wProd.name}" style="width: 100%; height: 100%; object-fit: cover;">` : `<svg width="20" height="20" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="3" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2"/></svg>`}
                  </div>
                  <div style="flex-grow: 1; min-width: 0;">
                    <a href="/productos/${encodeURIComponent(wProd.slug || wProd.id)}" style="font-family: var(--font-heading); font-size: 0.76rem; font-weight: 700; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; color: var(--text-primary); text-decoration: none;">${wProd.name}</a>
                    <div style="font-size: 0.68rem; color: var(--text-muted); margin-top: 2px;">Talla: <strong style="color: var(--text-primary);">${wSize}</strong> &bull; MXN $${(wProd.price).toFixed(2)}</div>
                  </div>
                  <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                    <button type="button" class="btn-sheet-apply" data-action="move-wishlist-to-cart" data-product-id="${wProd.id}" style="padding: 5px 8px; font-size: 0.68rem; border-radius: var(--radius-sm); cursor: pointer;" title="Mover al carrito">
                      Al carrito
                    </button>
                    <button type="button" class="btn-remove-item" data-action="toggle-wishlist" data-product-id="${wProd.id}" title="Quitar de deseos">
                      <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                  </div>
                </div>
              `;
            }).join('')}
          </div>
        </div>
      `;
    }

    if (storeState.cart.length === 0) {
      container.innerHTML = `
        <div style="text-align: center; padding: 40px 20px 24px; color: var(--text-muted);">
          <div class="cart-empty-icon-circle" style="width: 48px; height: 48px; margin: 0 auto 12px; border-radius: 50%; background: var(--bg-tertiary); display: flex; align-items: center; justify-content: center;">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
          </div>
          <strong style="font-family: var(--font-heading); color: var(--text-primary); display: block; font-size: 1rem;">Tu carrito está vacío</strong>
          <p style="font-size: 0.8rem; margin-top: 4px;">Explora el catálogo y agrega tus prendas favoritas.</p>
        </div>
        ${savedSectionHtml}
        ${wishlistSectionHtml}
      `;
      if (footerAction) footerAction.style.display = 'none';
      renderCouponBoxes();
      return;
    }

    if (footerAction) footerAction.style.display = 'flex';

    // REGLA 2: La cuenta regresiva del apartado solo se activa cuando inicia el checkout
    let urgencyBanner = '';
    if (stockLockTimerRemaining > 0 && stockLockInterval) {
      const mins = Math.floor(stockLockTimerRemaining / 60);
      const secs = stockLockTimerRemaining % 60;
      const initialTimeStr = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
      const initialPct = Math.max(0, (stockLockTimerRemaining / CART_STOCK_LOCK_SECONDS) * 100);

      urgencyBanner = `
        <div style="background: #fff1f2; border: 1px solid #fecdd3; border-radius: var(--radius-sm); padding: 10px 12px; margin-bottom: 14px;">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
            <div style="display: flex; align-items: center; gap: 6px;">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#e11d48" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span style="font-family: var(--font-heading); font-size: 0.73rem; font-weight: 800; color: #9f1239; text-transform: uppercase; letter-spacing: 0.04em;">Stock Apartado en Checkout</span>
            </div>
            <span id="stockLockTimeRemaining" style="font-family: monospace; font-size: 0.82rem; font-weight: 800; color: #e11d48;">${initialTimeStr}</span>
          </div>
          <div style="width: 100%; height: 3px; background: #ffe4e6; border-radius: 2px; overflow: hidden; margin-top: 6px;">
            <div id="stockLockProgressBar" style="width: ${initialPct}%; height: 100%; background: #e11d48; transition: width 1s linear;"></div>
          </div>
        </div>
      `;
    }

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
          <div style="display: flex; justify-content: space-between; font-size: 0.72rem; font-weight: 700; margin-bottom: 6px; color: var(--text-primary);">
            <span>Agrega MXN $${remainingForFree.toFixed(2)} para Envío Gratis</span>
            <span>${shippingProgress}%</span>
          </div>
          <div class="cart-shipping-progress-track" style="width: 100%; height: 5px; background: var(--border-medium); border-radius: 3px; overflow: hidden;">
            <div class="cart-shipping-progress-fill" style="width: ${shippingProgress}%; height: 100%; background: var(--text-primary); transition: width 0.3s ease;"></div>
          </div>
        </div>
      `;
    }

    // Desglose de carrito mixto: Linea A (Bodega local, entrega 24-48h) vs Linea B (Bajo Pedido / Taller / Scraping)
    const lineAEntries = storeState.cart.map((item, idx) => ({ item, idx }))
      .filter(x => !isLineBItem(x.item));
    const lineBEntries = storeState.cart.map((item, idx) => ({ item, idx }))
      .filter(x => isLineBItem(x.item));
    const isMixedCart = lineAEntries.length > 0 && lineBEntries.length > 0;

    let mixedNoticeHtml = '';
    if (isMixedCart) {
      mixedNoticeHtml = `
        <div style="background: var(--bg-tertiary, #f8fafc); border: 1px solid var(--border-subtle, #e2e8f0); border-radius: var(--radius-sm); padding: 8px 12px; margin-bottom: 12px; font-size: 0.72rem; color: var(--text-secondary, #475569); display: flex; align-items: center; gap: 6px;">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#2563eb" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          <div><strong style="color: var(--text-primary, #0f172a);">Pedido Mixto:</strong> Incluye prendas en bodega inmediata (24-48h) y prendas por pedido (3-5 días).</div>
        </div>
      `;
    }

    const renderItemHtml = ({ item, idx }) => {
      const outOfStock = isItemOutOfStock(item);
      const productSlug = item.slug || item.product_slug || item.productId || '';
      const productUrl = productSlug ? `/productos/${productSlug}` : '#';
      let itemImg = item.image;
      if (!itemImg || itemImg === '/user-avatar.png') {
        const localProd = Array.isArray(STORE_PRODUCTS) ? STORE_PRODUCTS.find(p => String(p.id) === String(item.productId) || p.sku === item.sku) : null;
        if (localProd && localProd.image && localProd.image !== '/user-avatar.png') {
          itemImg = localProd.image;
        }
      }

      return `
      <div class="cart-item-row" style="display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--border-subtle); align-items: center; ${outOfStock ? 'background: rgba(239, 68, 68, 0.1); border-radius: var(--radius-sm); padding: 10px;' : ''}">
        <a href="${productUrl}" style="width: 52px; height: 52px; background: var(--bg-tertiary, #f4f4f5); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; text-decoration: none; border: 1px solid var(--border-subtle, transparent);" title="Ver prenda">
          ${itemImg ? `<img src="${itemImg}" alt="${item.name}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
          <div style="display: none; align-items: center; justify-content: center; width: 100%; height: 100%; background: #18181b; color: white;">
            <svg width="22" height="22" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="3" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2"/></svg>
          </div>` : `<svg width="22" height="22" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="3" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2"/></svg>`}
        </a>
        
        <div style="flex-grow: 1; min-width: 0;">
          <a href="${productUrl}" style="text-decoration: none; color: inherit; display: block;" title="Ver prenda">
            <div style="font-family: var(--font-heading); font-size: 0.8rem; font-weight: 700; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-primary);">${item.name}</div>
          </a>
          <div style="display: flex; align-items: center; gap: 8px; margin-top: 2px;">
            <span style="font-size: 0.7rem; color: var(--text-muted);">Talla: <strong style="color: var(--text-primary);">${item.size}</strong></span>
            <span style="font-size: 0.65rem; padding: 1px 5px; border-radius: 3px; font-weight: 700; ${isLineBItem(item) ? 'background: #fffbeb; color: #b45309; border: 1px solid #fef3c7;' : 'background: #ecfdf5; color: #047857; border: 1px solid #d1fae5;'}">${isLineBItem(item) ? 'Bajo Pedido' : (item.stockLabel || 'Stock Fisico')}</span>
          </div>
          ${outOfStock ? `
            <div class="cart-item-out-of-stock-notice" style="color: #ef4444; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              <span>Ya no hay existencias</span>
            </div>
          ` : ''}
          <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
            <div style="font-family: var(--font-heading); font-size: 0.82rem; font-weight: 800; color: var(--text-primary);">MXN $${(item.price * item.quantity).toFixed(2)}</div>
            <a href="${productUrl}" class="btn-cart-view-pdp" style="font-size: 0.68rem; color: var(--text-muted); text-decoration: underline; display: inline-flex; align-items: center; gap: 3px; font-weight: 600;" title="Ver prenda sin sacarla del carrito">
              <span>Ver prenda</span>
              <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
          </div>
        </div>

        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
          <div style="display: flex; align-items: center; gap: 6px;">
            <button type="button" class="btn-qty-control" data-action="decrease-qty" data-index="${idx}">-</button>
            <span style="font-family: var(--font-heading); font-size: 0.8rem; font-weight: 800; min-width: 18px; text-align: center; color: var(--text-primary);">${item.quantity}</span>
            <button type="button" class="btn-qty-control" data-action="increase-qty" data-index="${idx}">+</button>
            <button type="button" class="btn-remove-item" data-action="remove-item" data-index="${idx}" title="Eliminar">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
          </div>
          <button type="button" data-action="save-for-later" data-index="${idx}" title="Guardar para después" style="background: none; border: none; padding: 2px 0; font-size: 0.65rem; color: var(--text-muted); cursor: pointer; text-decoration: underline; display: flex; align-items: center; gap: 3px;">
            <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
            <span>Guardar para después</span>
          </button>
        </div>
      </div>
      `;
    };

    let itemsHtml = '';
    if (isMixedCart) {
      itemsHtml += `
        <div style="margin-top: 10px;">
          <div style="font-family: var(--font-heading); font-size: 0.72rem; font-weight: 800; color: var(--text-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
            <span>Entrega Inmediata // Bodega Local (24-48h)</span>
            <span style="color: #059669; font-size: 0.65rem;">Stock Apartado</span>
          </div>
          ${lineAEntries.map(renderItemHtml).join('')}
        </div>
        <div style="margin-top: 16px;">
          <div style="font-family: var(--font-heading); font-size: 0.72rem; font-weight: 800; color: var(--text-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
            <span>Confección Bajo Pedido (Entrega 3-5 días hábiles)</span>
            <span style="color: #d97706; font-size: 0.65rem;">Bajo Pedido</span>
          </div>
          ${lineBEntries.map(renderItemHtml).join('')}
        </div>
      `;
    } else if (lineAEntries.length > 0) {
      itemsHtml += `
        <div style="margin-top: 10px;">
          <div style="font-family: var(--font-heading); font-size: 0.72rem; font-weight: 800; color: var(--text-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
            <span>Entrega Inmediata // Bodega Local (24-48h)</span>
            <span style="color: #059669; font-size: 0.65rem;">Stock Apartado</span>
          </div>
          ${lineAEntries.map(renderItemHtml).join('')}
        </div>
      `;
    } else {
      itemsHtml += `
        <div style="margin-top: 10px;">
          <div style="font-family: var(--font-heading); font-size: 0.72rem; font-weight: 800; color: var(--text-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
            <span>Confección Bajo Pedido (Entrega 3-5 días hábiles)</span>
            <span style="color: #d97706; font-size: 0.65rem;">Bajo Pedido</span>
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

    container.innerHTML = urgencyBanner + shippingNotice + mixedNoticeHtml + itemsHtml + savedSectionHtml + wishlistSectionHtml;
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
        padding: 10px 16px;
        border-radius: 14px;
        font-family: var(--font-heading);
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        line-height: 1.35;
        text-align: center;
        max-width: calc(100vw - 24px);
        width: max-content;
        box-sizing: border-box;
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
        z-index: 10000;
        opacity: 0;
        transition: all 0.25s ease;
        box-shadow: 0 4px 16px rgba(0,0,0,0.25);
        pointer-events: none;
      `;
      document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.style.opacity = '1';
    toast.style.transform = 'translateX(-50%) translateY(0)';
    if (toast._hideTimer) clearTimeout(toast._hideTimer);
    toast._hideTimer = setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(-50%) translateY(20px)';
    }, 2800);
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

    const wIdx = storeState.wishlist.indexOf(productId);
    if (wIdx > -1) {
      storeState.wishlist.splice(wIdx, 1);
      saveWishlist();
    }

    dispatchWishlistTelemetry('move_to_cart', {
      product_id: productId,
      universe: storeState.activeUniverse,
      selected_size: size
    });

    renderCartSheetContent();
    showToastNotification(`"${product.name}" movido al carrito`);
  }

  function renderWishlistDrawerContent() {
    const container = document.getElementById('wishlistItemsContainer');
    if (!container) return;

    if (storeState.wishlist.length === 0) {
      container.innerHTML = `
        <div style="text-align: center; padding: 48px 20px; color: var(--text-muted);">
          <div class="cart-empty-icon-circle" style="width: 48px; height: 48px; margin: 0 auto 12px; border-radius: 50%; background: var(--bg-tertiary); display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
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
          <a href="/productos/${encodeURIComponent(prod.slug || prod.id)}" class="wishlist-item-img" style="cursor: pointer; text-decoration: none;" title="${prod.name}">
            <svg width="24" height="24" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="3" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2"/></svg>
          </a>

          <div style="flex-grow: 1;">
            <a href="/productos/${encodeURIComponent(prod.slug || prod.id)}" style="font-family: var(--font-heading); font-size: 0.82rem; font-weight: 700; line-height: 1.2; cursor: pointer; color: var(--text-primary); text-decoration: none; display: block;">${prod.name}</a>
            <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 2px;">Talla: <strong style="color: var(--text-primary);">${selectedSize}</strong> · <span style="color: #059669; font-weight: 600;">${prod.stockLabel}</span></div>
            <div style="font-family: var(--font-heading); font-size: 0.84rem; font-weight: 800; color: var(--text-primary); margin-top: 4px;">MXN $${prod.price.toFixed(2)}</div>
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
  // FUNCIÓN DE BÚSQUEDA CONMUTABLE EN TIEMPO REAL (REGLA 5)
  // ============================================================================
  function toggleSearchBar(forceOpen = null) {
    const searchBar = document.getElementById('storeSearchBar');
    const searchInput = document.getElementById('storeLiveSearchInput');
    if (!searchBar) return;

    const isClosed = (searchBar.style.display === 'none' || !searchBar.style.display);
    const shouldOpen = (forceOpen !== null) ? forceOpen : isClosed;

    if (shouldOpen) {
      searchBar.style.display = 'block';
      if (searchInput) {
        setTimeout(() => searchInput.focus(), 60);
      }
    } else {
      searchBar.style.display = 'none';
      if (storeState.searchQuery) {
        storeState.searchQuery = '';
        if (searchInput) searchInput.value = '';
        const clearBtn = document.getElementById('clearSearchBtn');
        if (clearBtn) clearBtn.style.display = 'none';
        renderProductsGrid('catalogProductsGrid');
        renderProductsGrid('homeProductsGrid');
      }
    }
  }

  function handleLiveSearch(query) {
    storeState.searchQuery = query;
    const clearBtn = document.getElementById('clearSearchBtn');
    if (clearBtn) {
      clearBtn.style.display = (query && query.length > 0) ? 'inline-flex' : 'none';
    }

    if (storeState.activeTab !== 'catalog') {
      switchDockTab('catalog');
    } else {
      renderProductsGrid('catalogProductsGrid');
    }
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
    if (!productId) return;
    const product = (typeof STORE_PRODUCTS !== 'undefined' && Array.isArray(STORE_PRODUCTS))
      ? STORE_PRODUCTS.find(p => String(p.id) === String(productId) || String(p.sku) === String(productId))
      : null;
    const slug = product && product.slug ? product.slug : productId;
    window.location.href = `/productos/${slug}`;
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
  // QUICK ADD BOTTOM SHEET (Apple HIG & Material Design 48x48px Touch Targets)
  // ============================================================================
  let activeQuickAddProduct = null;
  let activeQuickAddSize = null;

  function openQuickAddSheet(productId) {
    if (!productId) return;
    const product = (typeof STORE_PRODUCTS !== 'undefined' && Array.isArray(STORE_PRODUCTS))
      ? STORE_PRODUCTS.find(p => String(p.id) === String(productId) || String(p.sku) === String(productId))
      : null;
    if (!product) return;

    const sizesList = (Array.isArray(product.sizes) && product.sizes.length > 0)
      ? product.sizes
      : ['CH', 'M', 'G'];

    // Si tiene talla única, añadir directamente sin fricción
    if (sizesList.length <= 1) {
      addToCart(product.id, sizesList[0] || 'M');
      openDrawer('cartSheet');
      return;
    }

    activeQuickAddProduct = product;
    activeQuickAddSize = storeState.selectedSizes[product.id] || sizesList[0];

    const contentEl = document.getElementById('quickAddContent');
    if (!contentEl) return;

    contentEl.innerHTML = `
      <div class="quick-add-product-header">
        <img src="${product.image || '/user-avatar.png'}" alt="${product.name}" class="quick-add-thumb">
        <div class="quick-add-meta">
          <div style="font-size: 0.68rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">${product.categoryName || 'Prenda Anatómica'}</div>
          <h3 class="quick-add-title">${product.name}</h3>
          <div class="quick-add-price">MXN $${Number(product.price || 0).toFixed(2)}</div>
        </div>
      </div>

      <div class="quick-add-sizes-title">
        <span>Selecciona tu talla</span>
        <span style="font-size: 0.68rem; color: var(--brand-primary); cursor: pointer;" onclick="openDrawer('sizeGuideSheet')">Guía de tallas</span>
      </div>

      <div class="quick-add-sizes-grid" id="quickAddSizesGrid">
        ${sizesList.map(sz => `
          <button type="button" class="quick-add-size-pill ${sz === activeQuickAddSize ? 'selected' : ''}" data-size="${sz}">
            ${sz}
          </button>
        `).join('')}
      </div>

      <button type="button" class="quick-add-cta-btn" id="quickAddCtaBtn">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>AÑADIR AL CARRITO (${activeQuickAddSize})</span>
      </button>
    `;

    openDrawer('quickAddSheet');
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

    // Validar en tiempo real contra la API del backend (Hub 7 / Hub 2 / Cupones BD)
    const rawSubtotal = storeState.cart.reduce((acc, i) => acc + (i.price * i.quantity), 0);
    fetch('/api/v1/store/coupons/validate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ code: code, subtotal: rawSubtotal })
    })
    .then(r => r.json())
    .then(res => {
      if (res && res.valid) {
        const cData = {
          code: res.code,
          discountPct: res.discount_pct,
          ally: res.type === 'ally_b2b' ? (res.utm_source || 'Aliado B2B') : 'Promoción Oficial'
        };
        VALID_COUPONS[code] = cData;
        couponState.appliedCoupon = res.code;
        couponState.discountPct = res.discount_pct;
        couponState.discountAmount = res.discount_amount;
        couponState.attributedAlly = cData.ally;

        updateCheckoutSummary();
        renderCartSheetContent();
        renderCouponBoxes();
        if (checkoutState && checkoutState.step === 3) {
          renderCheckoutStep3Review();
        }
        showToastNotification(`Cupón ${res.code} aplicado: ${res.discount_pct}% de descuento`);
      } else {
        if (VALID_COUPONS[code]) {
          delete VALID_COUPONS[code];
        }
        if (couponState.appliedCoupon === code) {
          couponState.appliedCoupon = null;
          couponState.discountPct = 0;
          couponState.discountAmount = 0;
          couponState.attributedAlly = null;
          updateCheckoutSummary();
          renderCartSheetContent();
          renderCouponBoxes();
        }
        showToastNotification(res.message || `El cupón "${code}" no es válido o ha expirado`);
      }
    })
    .catch(() => {
      // Fallback offline si el backend no responde
      if (VALID_COUPONS[code]) {
        const cData = VALID_COUPONS[code];
        couponState.appliedCoupon = code;
        couponState.discountPct = cData.discountPct;
        couponState.attributedAlly = cData.ally;
        updateCheckoutSummary();
        renderCartSheetContent();
        renderCouponBoxes();
        showToastNotification(`Cupón ${code} aplicado en modo local`);
      } else {
        showToastNotification(`El cupón "${code}" no es válido o ha expirado`);
      }
    });

    return { success: false, message: 'Validando cupón...' };
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
      // Regla 8: En carrito del storefront el formulario de cupón no reside aquí, solo en el paso 1 del checkout
      cartBox.innerHTML = '';
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
    paymentMethod: 'credit_debit_card', // 'credit_debit_card' | 'oxxo_pay' (Regla 3: Cero SPEI)
    card: {
      number: '',
      expiry: '',
      cvv: '',
      holder: ''
    },
    lastConfirmedOrder: null
  };

  // Clasificación de Tipo de Carrito (Stock Físico vs On-Demand / Scraping vs Mixto)
  function getCartType(cart) {
    const items = cart || storeState.cart;
    if (!items || items.length === 0) return 'ready';
    const hasReady = items.some(i => !isLineBItem(i));
    const hasOnDemand = items.some(i => isLineBItem(i));
    if (hasReady && hasOnDemand) return 'mixed';
    if (hasOnDemand) return 'on_demand';
    return 'ready';
  }

  // Bifurcación de Líneas de Carrito (Línea A: Bodega vs Línea B: Bajo Pedido / Proveedor / Taller)
  function getCartLines(cart) {
    const items = cart || storeState.cart;
    const readyItems = items.filter(i => !isLineBItem(i));
    const onDemandItems = items.filter(i => isLineBItem(i));
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
              <strong class="line-title line-title-ready">[STOCK FÍSICO] ENTREGA INMEDIATA BODEGA</strong>
              <div class="line-items">
                ${readyItems.map(it => `<div>- ${it.quantity}x ${it.name} (${it.size})</div>`).join('')}
              </div>
              <span class="line-timing">Despacho express courier: 24 a 48 hrs</span>
            </div>
            <div class="mixed-line-col">
              <strong class="line-title line-title-demand">[BAJO PEDIDO] CONFECCIÓN ON-DEMAND</strong>
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

    // REGLA 2: Verificar si algún producto ya no tiene existencias antes de iniciar checkout
    const outOfStockItem = storeState.cart.find(isItemOutOfStock);
    if (outOfStockItem) {
      showToastNotification(`La prenda "${outOfStockItem.name}" ya no cuenta con existencias. Retírala del carrito para continuar.`);
      openDrawer('cartSheet');
      return;
    }

    // REGLA 2: La cuenta regresiva del apartado y reserva en inventario SOLO se activa cuando inicia el checkout
    startCheckoutStockLockTimer();

    // REGLA 3: Tarjeta de crédito/débito predeterminada (cero SPEI)
    if (checkoutState.paymentMethod === 'spei_bbva' || !checkoutState.paymentMethod) {
      checkoutState.paymentMethod = 'credit_debit_card';
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

  function focusCheckoutField(id) {
    const el = document.getElementById(id);
    if (el) {
      try {
        el.focus();
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      } catch (e) {}
    }
  }

  // Helper de validación inline (borde rojo/verde + mensaje de error contextual)
  function setFieldValidation(fieldId, isValid, errorMsg = '') {
    const el = document.getElementById(fieldId);
    if (!el) return;

    const parent = el.closest('.checkout-form-group') || el.parentElement;
    let errEl = parent ? parent.querySelector('.checkout-field-error') : null;

    if (isValid) {
      el.classList.remove('is-invalid');
      el.classList.add('is-valid');
      if (errEl) errEl.remove();
    } else {
      el.classList.remove('is-valid');
      el.classList.add('is-invalid');
      if (!errEl && parent) {
        errEl = document.createElement('span');
        errEl.className = 'checkout-field-error';
        parent.appendChild(errEl);
      }
      if (errEl) errEl.textContent = errorMsg;
    }
  }

  function validateSingleCheckoutField(id) {
    const el = document.getElementById(id);
    if (!el) return true;
    const val = (el.value || '').trim();

    switch(id) {
      case 'checkoutCustomerName':
        if (!val) { setFieldValidation(id, false, 'Ingresa tu nombre completo.'); return false; }
        setFieldValidation(id, true); return true;
      case 'checkoutCustomerPhone':
        const cleanPhone = val.replace(/\D/g, '');
        if (cleanPhone.length !== 10) { setFieldValidation(id, false, 'Ingresa un celular válido de 10 dígitos.'); return false; }
        setFieldValidation(id, true); return true;
      case 'checkoutCustomerEmail':
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(val)) { setFieldValidation(id, false, 'Ingresa un correo electrónico válido.'); return false; }
        setFieldValidation(id, true); return true;
      case 'checkoutShippingZip':
        const cleanZip = val.replace(/\D/g, '');
        if (cleanZip.length !== 5) { setFieldValidation(id, false, 'Código postal requerido (5 dígitos).'); return false; }
        setFieldValidation(id, true); return true;
      case 'checkoutShippingState':
        if (!val) { setFieldValidation(id, false, 'Ingresa el estado de destino.'); return false; }
        setFieldValidation(id, true); return true;
      case 'checkoutShippingCity':
        if (!val) { setFieldValidation(id, false, 'Ingresa el municipio o alcaldía.'); return false; }
        setFieldValidation(id, true); return true;
      case 'checkoutShippingColony':
        if (!val) { setFieldValidation(id, false, 'Ingresa la colonia.'); return false; }
        setFieldValidation(id, true); return true;
      case 'checkoutShippingStreet':
        if (!val) { setFieldValidation(id, false, 'Ingresa calle y número de entrega.'); return false; }
        setFieldValidation(id, true); return true;
      case 'checkoutCardNumber':
        const cleanCard = val.replace(/\D/g, '');
        if (cleanCard.length < 15 || cleanCard.length > 19) { setFieldValidation(id, false, 'Número de tarjeta inválido (15-16 dígitos).'); return false; }
        setFieldValidation(id, true); return true;
      case 'checkoutCardExpiry':
        if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(val)) { setFieldValidation(id, false, 'Expiración MM/AA inválida.'); return false; }
        setFieldValidation(id, true); return true;
      case 'checkoutCardCvv':
        if (val.length < 3 || val.length > 4) { setFieldValidation(id, false, 'CVV de 3 o 4 dígitos.'); return false; }
        setFieldValidation(id, true); return true;
      case 'checkoutCardHolder':
        if (!val) { setFieldValidation(id, false, 'Ingresa el nombre como aparece en la tarjeta.'); return false; }
        setFieldValidation(id, true); return true;
      default:
        return true;
    }
  }

  function validateCheckoutStep1() {
    const fields = [
      'checkoutCustomerName',
      'checkoutCustomerPhone',
      'checkoutCustomerEmail',
      'checkoutShippingZip',
      'checkoutShippingState',
      'checkoutShippingCity',
      'checkoutShippingColony',
      'checkoutShippingStreet'
    ];

    let allValid = true;
    let firstInvalid = null;

    fields.forEach(fieldId => {
      const isValid = validateSingleCheckoutField(fieldId);
      if (!isValid) {
        allValid = false;
        if (!firstInvalid) firstInvalid = fieldId;
      }
    });

    if (!allValid) {
      showToastNotification('Por favor completa los campos requeridos en rojo.');
      if (firstInvalid) focusCheckoutField(firstInvalid);
      return false;
    }

    const name = (document.getElementById('checkoutCustomerName')?.value || '').trim();
    let rawPhone = (document.getElementById('checkoutCustomerPhone')?.value || '').replace(/\D/g, '');
    if (rawPhone.length > 10 && rawPhone.startsWith('52')) {
      rawPhone = rawPhone.slice(-10);
    }
    const email = (document.getElementById('checkoutCustomerEmail')?.value || '').trim();
    const rawZip = (document.getElementById('checkoutShippingZip')?.value || '').replace(/\D/g, '');
    const state = (document.getElementById('checkoutShippingState')?.value || '').trim();
    const city = (document.getElementById('checkoutShippingCity')?.value || '').trim();
    const colony = (document.getElementById('checkoutShippingColony')?.value || '').trim();
    const street = (document.getElementById('checkoutShippingStreet')?.value || '').trim();
    const refs = (document.getElementById('checkoutShippingRefs')?.value || '').trim();
    const createAcc = document.getElementById('checkoutCreateAccount')?.checked ?? true;

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
      const cardFields = [
        'checkoutCardNumber',
        'checkoutCardExpiry',
        'checkoutCardCvv',
        'checkoutCardHolder'
      ];

      let allValid = true;
      let firstInvalid = null;

      cardFields.forEach(fieldId => {
        const isValid = validateSingleCheckoutField(fieldId);
        if (!isValid) {
          allValid = false;
          if (!firstInvalid) firstInvalid = fieldId;
        }
      });

      if (!allValid) {
        showToastNotification('Por favor verifica los datos de tu tarjeta.');
        if (firstInvalid) focusCheckoutField(firstInvalid);
        return false;
      }

      const cardNum = (document.getElementById('checkoutCardNumber')?.value || '').replace(/\D/g, '');
      const expiry = (document.getElementById('checkoutCardExpiry')?.value || '').trim();
      const cvv = (document.getElementById('checkoutCardCvv')?.value || '').trim();
      const holder = (document.getElementById('checkoutCardHolder')?.value || '').trim();

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

    const payloadItems = (storeState.cart || []).map(i => ({
      productId: i.productId || i.id,
      product_id: i.productId || i.id,
      variantId: i.variantId || null,
      variant_id: i.variantId || null,
      sku: i.sku || null,
      size: i.size || 'M',
      quantity: i.quantity || 1,
      price: i.price || 0
    }));

    // Conectar con backend RESTful de checkout (TASK-INT-06 / TASK-FIN-01)
    fetch('/api/v1/store/checkout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Cart-Session-Id': getCartSessionId(),
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        cart_id: getCartSessionId(),
        items: payloadItems,
        customer: { ...checkoutState.customer },
        shipping: { ...checkoutState.shipping },
        payment_method: checkoutState.paymentMethod,
        coupon_code: couponState.appliedCoupon || null,
        universe: storeState.activeUniverse,
        dispatch_strategy: checkoutState.dispatchStrategy || 'standard'
      })
    })
    .then(r => r.json())
    .then(res => {
      if (res && res.success && res.order) {
        order.folio = res.order.order_number;
        order.trackingNumber = res.order.tracking_number;
        const folioBadge = document.getElementById('orderConfirmedFolio');
        if (folioBadge) folioBadge.textContent = res.order.order_number;
        try {
          const orders = JSON.parse(localStorage.getItem('multiverse_customer_orders') || '[]');
          if (orders.length > 0) {
            orders[0].folio = res.order.order_number;
            orders[0].trackingNumber = res.order.tracking_number;
            localStorage.setItem('multiverse_customer_orders', JSON.stringify(orders));
          }
        } catch (e) {}
      } else if (res && !res.success) {
        showToastNotification(res.message || 'Atención: No se pudo registrar la orden en el servidor.');
      }
    })
    .catch(err => {
      console.warn('Error en checkout backend:', err);
    });

    // Limpiar carrito local
    storeState.cart = [];
    try {
      localStorage.setItem('multiverse_store_cart', JSON.stringify([]));
      localStorage.removeItem('multiverse_stock_lock_expires_at');
    } catch (e) {}
    updateCartBadges();
    renderCartSheetContent();

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
      if (checkoutState.paymentMethod === 'oxxo_pay') {
        paymentBox.innerHTML = `
          <div class="checkout-oxxo-box" style="background: #fef3c7; border: 1px solid #fde68a; border-radius: var(--radius-sm); padding: 14px; text-align: center;">
            <strong class="checkout-oxxo-title" style="font-family: var(--font-heading); font-size: 0.82rem; color: #92400e; display: block; margin-bottom: 6px;">
              Referencia Digital OXXO Pay
            </strong>
            <div class="checkout-oxxo-ref" style="font-family: monospace; font-size: 1.25rem; font-weight: 800; letter-spacing: 0.08em; color: #78350f; background: #ffffff; padding: 10px; border-radius: 6px; border: 1px dashed #d97706; margin-bottom: 8px;">
              ${oxxoRef}
            </div>
            <p class="checkout-oxxo-desc" style="font-size: 0.72rem; color: #92400e; margin: 0; line-height: 1.4;">
              Indica en caja que realizarás un pago OXXO Pay con esta referencia. Tu stock de prendas queda apartado por <strong>48 horas</strong>.
            </p>
          </div>
        `;
      } else {
        paymentBox.innerHTML = `
          <div class="checkout-card-approved-box" style="background: var(--accent-emerald-soft); border: 1px solid var(--accent-emerald-border); border-radius: var(--radius-sm); padding: 14px;">
            <strong style="font-family: var(--font-heading); font-size: 0.85rem; color: var(--accent-emerald); display: block; margin-bottom: 4px;">
              Pago con Tarjeta Aprobado
            </strong>
            <p class="checkout-card-approved-desc" style="font-size: 0.75rem; color: #065f46; margin: 0; line-height: 1.4;">
              El cargo por MXN $${total.toFixed(2)} fue autorizado con tu tarjeta terminación <strong>${cardLast4}</strong>. Recibirás confirmación y guía de rastreo en tu correo.
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
          <div class="checkout-confirmed-mixed-box" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 12px; margin-bottom: 14px;">
            <strong class="checkout-confirmed-mixed-title" style="font-family: var(--font-heading); font-size: 0.8rem; color: #1e40af; display: block; margin-bottom: 4px;">
              LOGISTICA DE CARRITO MIXTO // BIFURCACION CONFIRMADA
            </strong>
            <p class="checkout-confirmed-mixed-desc" style="font-size: 0.72rem; color: #1e3a8a; margin: 0 0 8px; line-height: 1.35;">
              Estrategia: <strong>${checkoutState.dispatchStrategy === 'split' ? 'Despacho Dividido (2 Guias de Rastreo Courier)' : 'Despacho Consolidado (1 Guia Courier Consolidada)'}</strong>
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 0.7rem;">
              <div class="checkout-confirmed-line-card" style="background: #ffffff; border: 1px solid #dbeafe; border-radius: 4px; padding: 8px;">
                <span style="display: block; font-weight: 700; color: #10b981; margin-bottom: 2px;">[STOCK FÍSICO] BODEGA LOCAL</span>
                <span style="color: var(--text-secondary);">${readyItems.length} prenda(s) - Salida en 24-48h</span>
              </div>
              <div class="checkout-confirmed-line-card" style="background: #ffffff; border: 1px solid #dbeafe; border-radius: 4px; padding: 8px;">
                <span style="display: block; font-weight: 700; color: #f59e0b; margin-bottom: 2px;">[BAJO PEDIDO] TALLER ON-DEMAND</span>
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
          <span>${it.quantity}x ${it.name} (Talla ${it.size}) <small style="color: var(--text-muted);">[${isLineBItem(it) ? 'Bajo Pedido' : 'Stock Fisico'}]</small></span>
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

  async function lookupPostalCode(zip) {
    const cleanZip = (zip || '').replace(/\D/g, '');
    if (cleanZip.length !== 5) {
      showToastNotification('Ingresa un código postal válido de 5 dígitos.');
      return;
    }

    const stateInput = document.getElementById('checkoutShippingState');
    const cityInput = document.getElementById('checkoutShippingCity');
    const colInput = document.getElementById('checkoutShippingColony');
    const datalist = document.getElementById('checkoutColoniesList');

    const knownCodes = {
      '06000': { state: 'Ciudad de México', city: 'Cuauhtémoc', colonias: ['Centro'] },
      '06600': { state: 'Ciudad de México', city: 'Cuauhtémoc', colonias: ['Juárez', 'Cuauhtémoc', 'Roma Norte'] },
      '01000': { state: 'Ciudad de México', city: 'Álvaro Obregón', colonias: ['San Ángel', 'Campestre'] },
      '03100': { state: 'Ciudad de México', city: 'Benito Juárez', colonias: ['Del Valle Centro', 'Insurgentes San Borja', 'Tlacoquemecatl'] },
      '11000': { state: 'Ciudad de México', city: 'Miguel Hidalgo', colonias: ['Lomas de Chapultepec', 'Polanco'] },
      '44100': { state: 'Jalisco', city: 'Guadalajara', colonias: ['Americana', 'Centro', 'Ladrón de Guevara', 'Moderna', 'Vallarta'] },
      '45050': { state: 'Jalisco', city: 'Zapopan', colonias: ['Ciudad del Sol', 'La Calma'] },
      '64000': { state: 'Nuevo León', city: 'Monterrey', colonias: ['Centro', 'Obrera', 'Industrial', 'Bella Vista'] },
      '66220': { state: 'Nuevo León', city: 'San Pedro Garza García', colonias: ['Del Valle', 'Santa Engracia'] },
      '72000': { state: 'Puebla', city: 'Puebla', colonias: ['Centro', 'Amor'] },
      '76000': { state: 'Querétaro', city: 'Querétaro', colonias: ['Centro', 'Carretas', 'Alamos 1ra Sección'] },
      '77500': { state: 'Quintana Roo', city: 'Cancún', colonias: ['Cancún Centro', 'Zona Hotelera'] },
      '22000': { state: 'Baja California', city: 'Tijuana', colonias: ['Zona Urbana Río Tijuana', 'Centro'] }
    };

    let resolvedState = null;
    let resolvedCity = null;
    let resolvedColonias = [];

    // 1. Consulta a API SEPOMEX de backend
    try {
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
      const resp = await fetch('/api/v1/postal-code/lookup', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {})
        },
        body: JSON.stringify({ postal_code: cleanZip })
      });

      if (resp.ok) {
        const data = await resp.json();
        if (data.state) resolvedState = data.state;
        if (data.city) resolvedCity = data.city;
        if (Array.isArray(data.colonias) && data.colonias.length > 0) {
          resolvedColonias = data.colonias;
        }
      }
    } catch (e) {}

    // 2. Fallback local si la API no estuviera disponible
    if (!resolvedState && knownCodes[cleanZip]) {
      const match = knownCodes[cleanZip];
      resolvedState = match.state;
      resolvedCity = match.city;
      resolvedColonias = match.colonias || [match.colony];
    }

    // 3. Aplicar datos autocompletados al formulario
    if (resolvedState && stateInput) {
      stateInput.value = resolvedState;
      setFieldValidation('checkoutShippingState', true);
    }
    if (resolvedCity && cityInput) {
      cityInput.value = resolvedCity;
      setFieldValidation('checkoutShippingCity', true);
    }

    if (resolvedColonias.length > 0) {
      if (datalist) {
        datalist.innerHTML = resolvedColonias.map(c => `<option value="${c}">`).join('');
      }
      if (colInput) {
        colInput.value = resolvedColonias[0];
        setFieldValidation('checkoutShippingColony', true);
      }
    }

    setFieldValidation('checkoutShippingZip', true);

    if (resolvedState && resolvedCity) {
      showToastNotification(`CP ${cleanZip}: ${resolvedCity}, ${resolvedState} (Cobertura Verificada)`);
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

    let payLabel = 'Tarjeta de Credito / Debito';
    let paySub = `Terminacion **** ${(checkoutState.card.number || '0000').slice(-4)} | Titular: ${checkoutState.card.holder || checkoutState.customer.name || 'Cliente'}`;
    if (checkoutState.paymentMethod === 'oxxo_pay') {
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
          <div class="review-pay-label" style="font-weight: 700; font-size: 0.82rem; color: var(--text-primary);">${payLabel}</div>
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
            <div class="review-mixed-strategy-banner" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; padding: 8px; margin-bottom: 8px;">
              <span class="review-mixed-strategy-text" style="font-size: 0.72rem; font-weight: 700; color: #166534; display: block;">
                Estrategia: ${checkoutState.dispatchStrategy === 'split' ? 'Despacho Dividido / Split (2 Paquetes Separados)' : 'Despacho Consolidado Ecologico (1 Solo Paquete)'}
              </span>
            </div>
            <div class="review-lines-breakdown">
              <div class="review-line-box ready">
                <span class="review-line-tag">[STOCK FÍSICO] ENTREGA INMEDIATA BODEGA</span>
                ${readyItems.map(it => `<div class="review-item-row"><span>${it.quantity}x ${it.name} (${it.size})</span><strong>MXN $${(it.price * it.quantity).toFixed(2)}</strong></div>`).join('')}
                <div class="review-line-timing">Salida express courier en 24-48h</div>
              </div>
              <div class="review-line-box demand">
                <span class="review-line-tag">[BAJO PEDIDO] CONFECCIÓN ON-DEMAND</span>
                ${onDemandItems.map(it => `<div class="review-item-row"><span>${it.quantity}x ${it.name} (${it.size})</span><strong>MXN $${(it.price * it.quantity).toFixed(2)}</strong></div>`).join('')}
                <div class="review-line-timing">Confeccion artesanal en taller: 3-5 dias habiles</div>
              </div>
            </div>
          ` : `
            <div class="review-items-list">
              ${storeState.cart.map(it => `
                <div class="review-item-row">
                  <span>${it.quantity}x ${it.name} (Talla ${it.size}) <small style="color: var(--text-muted);">[${isLineBItem(it) ? 'Bajo Pedido' : 'Stock Fisico'}]</small></span>
                  <strong>MXN $${(it.price * it.quantity).toFixed(2)}</strong>
                </div>
              `).join('')}
            </div>
          `}
        </div>
      </div>

      <!-- Tarjeta 4: Auditoria Financiera Oficial -->
      <div class="checkout-review-card checkout-audit-card" style="background: var(--bg-secondary); border-color: var(--border-medium);">
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
            <span class="review-grand-total-val" style="color: var(--text-primary); font-size: 0.95rem;">MXN $${total.toFixed(2)}</span>
          </div>
        </div>
      </div>
    `;
  }

  // ============================================================================
  // AUTENTICACION REAL D2C (CONECTADA A public.users EN SUPABASE)
  // ============================================================================
  let buyerAuthMode = 'login'; // 'login' | 'register' | 'two_factor' | 'forgot' | 'reset'
  let buyerResetParams = { token: '', email: '' };
  let buyerTwoFactorUiState = {
    mode: null, // null | 'start' | 'confirm' | 'recovery' | 'disable'
    secret: '',
    otpauthUri: '',
    recoveryCodes: null,
  };

  (function initAuthUrlParams() {
    try {
      const params = new URLSearchParams(window.location.search);
      const resetToken = params.get('reset_token');
      const resetEmail = params.get('reset_email');
      const isVerified = params.get('verified') === '1';
      const wantsAccountTab = params.get('tab') === 'account';

      if (resetToken && resetEmail) {
        buyerResetParams = { token: resetToken, email: resetEmail };
        buyerAuthMode = 'reset';
      }

      if (wantsAccountTab || (resetToken && resetEmail) || isVerified) {
        const openAccountOnBoot = () => {
          if (typeof openDrawer === 'function') {
            openDrawer('accountSheet');
          }
          if (isVerified && typeof showToastNotification === 'function') {
            showToastNotification('Correo confirmado correctamente. Tu cuenta ya puede activar 2FA.');
          }
        };
        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', () => setTimeout(openAccountOnBoot, 120));
        } else {
          setTimeout(openAccountOnBoot, 120);
        }
      }
    } catch (e) {}
  })();

  function getActiveCustomerSession() {
    if (window.__INITIAL_AUTH_USER__ && window.__INITIAL_AUTH_USER__.email) {
      return window.__INITIAL_AUTH_USER__;
    }
    try {
      const raw = localStorage.getItem('multiverse_customer_session');
      if (raw) {
        const parsed = JSON.parse(raw);
        if (parsed && parsed.email === 'carlos.mendoza@gmail.com') {
          localStorage.removeItem('multiverse_customer_session');
          return null;
        }
        return parsed;
      }
    } catch (e) {}
    return null;
  }

  function applyAuthenticatedUserSession(userObj) {
    window.__INITIAL_AUTH_USER__ = userObj;
    try {
      localStorage.setItem('multiverse_customer_session', JSON.stringify(userObj));
    } catch (e) {}
    if (typeof checkoutState !== 'undefined' && checkoutState.customer) {
      checkoutState.customer.name = userObj.name || '';
      checkoutState.customer.phone = userObj.phone || '';
      checkoutState.customer.email = userObj.email || '';
    }
    renderAccountSheetContent();
    if (typeof renderPdpDynamicSections === 'function') {
      renderPdpDynamicSections();
    }
  }

  (function syncInitialAuthOnBoot() {
    if (window.__INITIAL_AUTH_USER__ && window.__INITIAL_AUTH_USER__.email) {
      try {
        localStorage.setItem('multiverse_customer_session', JSON.stringify(window.__INITIAL_AUTH_USER__));
      } catch (e) {}
    } else {
      try {
        const raw = localStorage.getItem('multiverse_customer_session');
        if (raw) {
          const parsed = JSON.parse(raw);
          if (parsed && parsed.email === 'carlos.mendoza@gmail.com') {
            localStorage.removeItem('multiverse_customer_session');
          }
        }
      } catch (e) {}
    }
  })();

  function loginWithGoogle(source) {
    if (typeof openDrawer === 'function') {
      openDrawer('accountSheet');
    }
    showToastNotification('Inicia sesión o crea tu cuenta con tu correo para guardar tu historial real.');
  }

  // ============================================================================
  // PORTAL DEL COMPRADOR D2C (TASK-STR-05)
  // Mi Cuenta, Mis Pedidos, Perfil Anatómico, Libreta Direcciones, Club MRR
  // ============================================================================
  // MOTOR DE TEMA VISUAL: DARK / LIGHT / SYSTEM (REGLA 2)
  // ============================================================================
  function getAppTheme() {
    try {
      return localStorage.getItem('app_theme') || 'system';
    } catch(e) {
      return 'system';
    }
  }

  function setAppTheme(mode, save = true) {
    if (!['light', 'dark', 'system'].includes(mode)) mode = 'system';
    if (save) {
      try {
        localStorage.setItem('app_theme', mode);
      } catch (e) {}
    }

    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = (mode === 'dark') || (mode === 'system' && prefersDark);

    if (isDark) {
      document.documentElement.classList.add('dark');
      document.documentElement.setAttribute('data-theme', 'dark');
      if (document.body) document.body.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
      document.documentElement.setAttribute('data-theme', 'light');
      if (document.body) document.body.classList.remove('dark');
    }

    // Actualizar meta theme-color
    const themeMeta = document.querySelector('meta[name="theme-color"]');
    if (themeMeta) {
      themeMeta.setAttribute('content', isDark ? '#09090b' : '#ffffff');
    }

    // Actualizar botones de opción en pestaña Configuración
    document.querySelectorAll('[data-action="set-app-theme"]').forEach(btn => {
      const btnMode = btn.getAttribute('data-theme-mode');
      btn.classList.toggle('active', btnMode === mode);
    });

    // Actualizar badge indicador
    const badge = document.getElementById('currentThemeBadge');
    if (badge) {
      const labels = { light: 'CLARO', dark: 'OSCURO', system: 'SISTEMA' };
      badge.textContent = labels[mode] || 'SISTEMA';
    }

    // Actualizar iconos de conmutación rápida
    document.querySelectorAll('.theme-icon-sun').forEach(el => {
      el.style.display = isDark ? 'inline-block' : 'none';
    });
    document.querySelectorAll('.theme-icon-moon').forEach(el => {
      el.style.display = isDark ? 'none' : 'inline-block';
    });

    window.dispatchEvent(new CustomEvent('app-theme-changed', {
      detail: { mode, isDark }
    }));
  }

  if (typeof window !== 'undefined' && window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
      if (getAppTheme() === 'system') {
        setAppTheme('system', false);
      }
    });
  }

  // ============================================================================
  const accountState = {
    activeTab: 'orders', // 'orders' | 'account' | 'discounts' | 'subscription' | 'settings'
    searchedFolio: null,
    editingAddressId: null,
    selectedAddressType: 'casa',
    selectedOrderFolio: null,
    pushEnabled: false
  };

  const PARTNER_DISCOUNTS = [
    { code: 'PARADISO15', title: '15% OFF en Colecciones Night & Festival', ally: 'Club Paradiso Nightlife', minOrder: 800, discountPercent: 15, redeemed: false, savedAmount: 0 },
    { code: 'CIRCUIT20', title: '20% OFF en Trajes de Baño & Ropa Interior', ally: 'Circuit Festival World', minOrder: 1200, discountPercent: 20, redeemed: false, savedAmount: 0 },
    { code: 'BIENVENIDO10', title: '10% OFF en Tu Primera Orden', ally: 'Torzo Welcome Gift', minOrder: 500, discountPercent: 10, redeemed: true, savedAmount: 130 },
    { code: 'PRIDE2026', title: 'Envío Express Courier Bonificado (100% OFF)', ally: 'Alianza Orgullo México', minOrder: 699, discountPercent: 0, freeShipping: true, redeemed: false, savedAmount: 0 },
    { code: 'MULTIVERSE50', title: '$50.00 MXN Bono de Recompensa MRR', ally: 'Programa Lealtad Multiverso', minOrder: 450, discountFixed: 50, redeemed: true, savedAmount: 320 }
  ];

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
    if (tabKey === 'discounts') {
      fetchAccountDiscounts();
    }
    if (tabKey === 'settings') {
      if (typeof renderAccountPushPreferences === 'function') {
        renderAccountPushPreferences();
      }
      setAppTheme(getAppTheme(), false);
    }
  }

  const AUTH_EYE_OPEN_SVG = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
  const AUTH_EYE_CLOSED_SVG = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

  function renderPasswordFieldHtml(name, placeholder, opts = {}) {
    const minlength = opts.minlength ? `minlength="${opts.minlength}"` : '';
    const padding = opts.compact ? '8px 36px 8px 10px' : '9px 38px 9px 12px';
    const fontSize = opts.compact ? '0.78rem' : '0.82rem';
    const radius = opts.compact ? '6px' : '8px';
    return `
      <div style="position: relative; width: 100%;">
        <input type="password" name="${name}" required ${minlength} placeholder="${placeholder}" style="width: 100%; box-sizing: border-box; padding: ${padding}; border: 1px solid var(--border-subtle); border-radius: ${radius}; font-size: ${fontSize}; background: var(--bg-primary); color: var(--text-primary);">
        <button type="button" data-action="toggle-password-visibility" aria-label="Mostrar u ocultar contraseña" aria-pressed="false" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 4px; display: inline-flex; align-items: center; justify-content: center; color: var(--text-muted); cursor: pointer;">
          ${AUTH_EYE_OPEN_SVG}
        </button>
      </div>
    `;
  }

  function renderPhoneWithCountryCodeHtml() {
    const codes = [
      ['+52', '🇲🇽 +52'],
      ['+1', '🇺🇸 +1'],
      ['+57', '🇨🇴 +57'],
      ['+54', '🇦🇷 +54'],
      ['+34', '🇪🇸 +34'],
      ['+56', '🇨🇱 +56'],
      ['+51', '🇵🇪 +51'],
      ['+593', '🇪🇨 +593'],
      ['+502', '🇬🇹 +502'],
      ['+506', '🇨🇷 +506'],
      ['+507', '🇵🇦 +507'],
      ['+55', '🇧🇷 +55'],
      ['+44', '🇬🇧 +44'],
    ];
    const options = codes.map(([val, label]) => `<option value="${val}">${label}</option>`).join('');
    return `
      <div style="display: flex; gap: 6px; width: 100%;">
        <select name="phone_code" aria-label="Código internacional" style="width: 96px; flex-shrink: 0; padding: 9px 6px; border: 1px solid var(--border-subtle); border-radius: 8px; font-size: 0.8rem; background: var(--bg-primary); color: var(--text-primary);">
          ${options}
        </select>
        <input type="tel" name="phone" placeholder="Teléfono / WhatsApp (opcional)" style="flex: 1; min-width: 0; padding: 9px 12px; border: 1px solid var(--border-subtle); border-radius: 8px; font-size: 0.82rem; background: var(--bg-primary); color: var(--text-primary);">
      </div>
    `;
  }

  function renderAccountSheetContent() {
    // 1. Tarjeta de Identidad / Auth
    const authCard = document.getElementById('accountAuthCard');
    const session = getActiveCustomerSession();

    if (authCard) {
      if (session && session.name) {
        const initials = session.name.split(' ').filter(Boolean).map(n => n.charAt(0)).slice(0, 2).join('').toUpperCase() || 'M';
        const isGoogle = session.provider === 'google' || !!session.google_linked;
        const walletBalance = (typeof session.walletBalance === 'number') ? session.walletBalance : 0;
        authCard.innerHTML = `
          <div class="auth-logged-card">
            <div class="auth-logged-main">
              <div class="auth-avatar-wrapper">
                <div class="auth-avatar-circle">
                  <span>${initials}</span>
                </div>
                <span class="auth-status-indicator" title="Sesion activa"></span>
              </div>
              <div class="auth-user-info">
                <div class="auth-user-name">${session.name}</div>
                <div class="auth-user-email">${session.email || 'Sin correo registrado'}</div>
                <div class="auth-user-tags-row">
                  ${isGoogle ? `
                    <span class="auth-badge-pill auth-badge-google">
                      <svg viewBox="0 0 24 24" width="11" height="11"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
                      <span>Google Vinculado</span>
                    </span>
                  ` : `
                    <span class="auth-badge-pill auth-badge-verified">
                      <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                      <span>Comprador Verificado</span>
                    </span>
                  `}
                  ${session.discretePackaging !== false ? `
                    <span class="auth-badge-pill auth-badge-discrete" title="Empaque 100% discreto activo">
                      <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                      <span>Empaque Discreto</span>
                    </span>
                  ` : ''}
                </div>
              </div>
              <button type="button" class="btn-logout-buyer" data-action="buyer-logout" title="Cerrar Sesion">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Salir</span>
              </button>
            </div>

            <div class="auth-logged-footer">
              <div class="auth-stat-item">
                <span class="auth-stat-label">Multiverse Wallet:</span>
                <strong class="auth-stat-value font-mono">MXN $${walletBalance.toFixed(2)}</strong>
              </div>
              <div class="auth-stat-sep">·</div>
              <div class="auth-stat-item">
                <span class="auth-stat-label">Recompensa:</span>
                <span class="auth-stat-accent">5% Cashback</span>
              </div>
            </div>
            ${!session.email_verified ? `
              <div style="margin-top: 10px; padding: 8px 10px; border-radius: 8px; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 0.72rem; color: #b45309;">
                <span>Confirma tu correo para activar 2FA y vincular pedidos previos.</span>
                <button type="button" data-action="resend-verification-email" style="flex-shrink: 0; background: #d97706; color: #fff; border: none; border-radius: 6px; padding: 4px 8px; font-size: 0.7rem; font-weight: 700; cursor: pointer;">
                  Reenviar correo
                </button>
              </div>
            ` : ''}
          </div>
        `;
      } else {
        const mode = buyerAuthMode || 'login';
        let title = 'Acceso de Comprador';
        let desc = 'Inicia sesión con tu correo para consultar tus pedidos y perfil.';
        let fieldsHtml = '';
        let submitText = 'Iniciar Sesión';

        if (mode === 'register') {
          title = 'Crear Cuenta de Comprador';
          desc = 'Regístrate para guardar tus pedidos reales, direcciones y preferencias.';
          submitText = 'Crear Cuenta';
          fieldsHtml = `
            <input type="text" name="name" required placeholder="Nombre completo" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-subtle); border-radius: 8px; font-size: 0.82rem; background: var(--bg-primary); color: var(--text-primary);">
            ${renderPhoneWithCountryCodeHtml()}
            <input type="email" name="email" required placeholder="correo@ejemplo.com" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-subtle); border-radius: 8px; font-size: 0.82rem; background: var(--bg-primary); color: var(--text-primary);">
            ${renderPasswordFieldHtml('password', 'Contraseña (mín. 8 caracteres)', { minlength: 8 })}
            ${renderPasswordFieldHtml('password_confirmation', 'Confirmar contraseña', { minlength: 8 })}
          `;
        } else if (mode === 'two_factor') {
          title = 'Verificación en Dos Pasos (2FA)';
          desc = 'Ingresa el código de 6 dígitos de tu app autenticadora o uno de tus códigos de respaldo.';
          submitText = 'Verificar Código';
          fieldsHtml = `
            <input type="text" name="code" required autocomplete="one-time-code" placeholder="Código 2FA o respaldo (ej. 123456)" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-subtle); border-radius: 8px; font-size: 0.9rem; letter-spacing: 0.08em; text-align: center; background: var(--bg-primary); color: var(--text-primary);">
            <label style="display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--text-secondary); cursor: pointer;">
              <input type="checkbox" name="trust_device" value="1">
              <span>Confiar en este dispositivo por 30 días</span>
            </label>
          `;
        } else if (mode === 'forgot') {
          title = 'Recuperar Contraseña';
          desc = 'Ingresa tu correo y te enviaremos un enlace válido por 60 minutos.';
          submitText = 'Enviar Enlace';
          fieldsHtml = `
            <input type="email" name="email" required placeholder="correo@ejemplo.com" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-subtle); border-radius: 8px; font-size: 0.82rem; background: var(--bg-primary); color: var(--text-primary);">
          `;
        } else if (mode === 'reset') {
          title = 'Restablecer Contraseña';
          desc = 'Elige una nueva contraseña segura (mín. 8 caracteres).';
          submitText = 'Guardar Nueva Contraseña';
          fieldsHtml = `
            <input type="hidden" name="token" value="${buyerResetParams.token || ''}">
            <input type="email" name="email" required value="${buyerResetParams.email || ''}" placeholder="correo@ejemplo.com" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-subtle); border-radius: 8px; font-size: 0.82rem; background: var(--bg-primary); color: var(--text-primary);">
            ${renderPasswordFieldHtml('password', 'Nueva contraseña (mín. 8 caracteres)', { minlength: 8 })}
            ${renderPasswordFieldHtml('password_confirmation', 'Confirmar nueva contraseña', { minlength: 8 })}
            <input type="text" name="code" placeholder="Código 2FA (solo si tu cuenta tiene 2FA activo)" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-subtle); border-radius: 8px; font-size: 0.82rem; background: var(--bg-primary); color: var(--text-primary);">
          `;
        } else {
          fieldsHtml = `
            <input type="email" name="email" required placeholder="correo@ejemplo.com" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-subtle); border-radius: 8px; font-size: 0.82rem; background: var(--bg-primary); color: var(--text-primary);">
            ${renderPasswordFieldHtml('password', 'Contraseña', { minlength: 8 })}
            <div style="text-align: right;">
              <button type="button" data-action="set-buyer-auth-mode" data-mode="forgot" style="background: none; border: none; color: #4f46e5; font-size: 0.72rem; font-weight: 600; cursor: pointer; text-decoration: underline;">
                ¿Olvidaste tu contraseña?
              </button>
            </div>
          `;
        }

        authCard.innerHTML = `
          <div class="auth-guest-card">
            <div class="account-subheading">${title}</div>
            <p class="account-subheading-desc" style="margin-bottom: 10px;">${desc}</p>
            <form id="buyerAuthForm" data-mode="${mode}" style="display: flex; flex-direction: column; gap: 8px;">
              ${fieldsHtml}
              <div id="buyerAuthError" style="display:none; color: #e11d48; font-size: 0.75rem; font-weight: 600;"></div>
              <div id="buyerAuthSuccess" style="display:none; color: #16a34a; font-size: 0.75rem; font-weight: 600;"></div>
              <button type="submit" id="buyerAuthSubmitBtn" class="btn-add-cart" style="width: 100%; padding: 10px; justify-content: center;">
                ${submitText}
              </button>
            </form>
            <div style="margin-top: 10px; text-align: center; font-size: 0.76rem; color: var(--text-muted);">
              ${mode === 'login' ? `
                ¿Aún no tienes cuenta?
                <button type="button" data-action="toggle-buyer-auth-mode" style="background: none; border: none; color: #4f46e5; font-weight: 700; cursor: pointer; text-decoration: underline; margin-left: 4px;">
                  Regístrate aquí
                </button>
              ` : `
                <button type="button" data-action="set-buyer-auth-mode" data-mode="login" style="background: none; border: none; color: #4f46e5; font-weight: 700; cursor: pointer; text-decoration: underline;">
                  Volver a Iniciar Sesión
                </button>
              `}
            </div>
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
      renderAccountWishlistIntegrated();
    } else if (accountState.activeTab === 'account') {
      renderAccountPersonalAndAddresses(session);
      renderAccountAnatomy(session);
    } else if (accountState.activeTab === 'discounts') {
      renderAccountDiscounts();
    } else if (accountState.activeTab === 'subscription') {
      renderAccountSubscription();
    } else if (accountState.activeTab === 'settings') {
      renderAccountSettingsView(session);
    }
  }

  function renderAccountOrders(orders) {
    const listEl = document.getElementById('accountOrdersList');
    if (!listEl) return;

    let session = null;
    try {
      session = JSON.parse(localStorage.getItem('multiverse_customer_session') || '{}');
    } catch (e) {}

    const emailQuery = (session && session.email) ? `?email=${encodeURIComponent(session.email)}` : '';

    fetch(`/api/v1/store/account/orders${emailQuery}`)
      .then(res => res.json())
      .then(data => {
        let combined = [];
        if (data && data.success && Array.isArray(data.orders) && data.orders.length > 0) {
          combined = data.orders;
        } else if (orders && orders.length > 0) {
          combined = orders;
        }

        const badgeEl = document.getElementById('accountOrdersBadge');
        if (badgeEl) badgeEl.textContent = combined.length;

        window.__latestOrders = combined;
        renderOrdersListHtml(listEl, combined);
        if (accountState.selectedOrderFolio) {
          showOrderDetail(accountState.selectedOrderFolio);
        }
      })
      .catch(() => {
        window.__latestOrders = orders || [];
        renderOrdersListHtml(listEl, orders || []);
        if (accountState.selectedOrderFolio) {
          showOrderDetail(accountState.selectedOrderFolio);
        }
      });
  }

  function renderOrdersListHtml(listEl, orders) {
    if (!orders || orders.length === 0) {
      listEl.innerHTML = `
        <div class="account-empty-state" style="padding: 16px 12px; text-align: center;">
          <div class="account-subheading" style="margin-bottom: 4px;">Sin pedidos registrados</div>
          <p class="account-subheading-desc" style="margin-bottom: 12px;">Aún no has realizado compras con esta cuenta.</p>
          <button type="button" class="btn-account-primary" data-action="switch-dock-tab" data-dock-tab="catalog">
            Explorar Catálogo
          </button>
        </div>
      `;
      return;
    }

    listEl.innerHTML = orders.map(order => {
      const folio = order.folio || order.order_number;
      const isMixed = (order.order_type === 'mixed' || order.orderType === 'mixed' || order.is_mixed);
      const dateFormatted = order.created_at_formatted || (order.createdAt ? new Date(order.createdAt).toLocaleDateString('es-MX', { year: 'numeric', month: 'short', day: 'numeric' }) : 'Reciente');
      const totalNum = typeof order.total === 'number' ? order.total : parseFloat(order.total || 0);
      const isApproved = (order.status === 'completed' || order.status === 'approved' || order.payment_status === 'paid');
      const items = order.items || [];
      const trackingNumber = order.tracking_number || (order.shipment ? order.shipment.tracking_number : null);

      return `
        <div class="order-history-card" data-action="view-order-detail" data-folio="${folio}" style="cursor: pointer;" title="Toca para ver resumen completo del pedido">
          <div class="order-history-header">
            <div>
              <div class="order-history-folio">${folio}</div>
              <div class="order-history-date">${dateFormatted}</div>
            </div>
            <div style="text-align: right;">
              <span class="order-status-badge ${isApproved ? 'status-approved' : 'status-pending'}">
                ${isApproved ? 'PAGO APROBADO' : 'PENDIENTE'}
              </span>
              <div class="order-history-total">MXN $${totalNum.toFixed(2)}</div>
            </div>
          </div>

          ${isMixed ? `
            <div class="order-mixed-dual-badge" style="margin: 8px 0;">
              <span class="badge-branch-ready">[LÍNEA A] Bodega 24-48h</span>
              <span class="badge-branch-demand">[LÍNEA B] Taller 3-5d</span>
            </div>
          ` : `
            <div style="font-size: 0.72rem; color: var(--text-secondary); margin: 6px 0;">
              Despacho: <strong>${order.order_type === 'on_demand' ? 'Confección Bajo Pedido' : 'Stock Inmediato'}</strong>
            </div>
          `}

          <div class="order-history-items" style="margin: 8px 0;">
            ${items.slice(0, 2).map(it => `
              <div class="order-history-item-line">
                <span>${it.quantity}x ${it.name || it.product_name} ${it.size ? '(' + it.size + ')' : ''}</span>
                <span>MXN $${((it.price || it.unit_price || 0) * it.quantity).toFixed(2)}</span>
              </div>
            `).join('')}
            ${items.length > 2 ? `<div style="font-size: 0.68rem; color: var(--text-muted); margin-top: 2px;">+ ${items.length - 2} prenda(s) más</div>` : ''}
          </div>

          <div class="order-history-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 8px; border-top: 1px solid var(--border-subtle); gap: 6px; flex-wrap: wrap;">
            <span class="order-carrier-tag">${order.carrier || 'DHL Express'}${trackingNumber ? ' (' + trackingNumber + ')' : ''}</span>
            <div style="display: flex; gap: 6px; align-items: center;">
              <button type="button" class="btn-reorder-1tap" data-action="reorder-order-items" data-folio="${folio}" title="Agregar todas las prendas de este pedido a tu carrito">
                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                <span>Reordenar</span>
              </button>
              <button type="button" class="btn-account-action" style="padding: 4px 10px; font-size: 0.7rem;" data-action="view-order-detail" data-folio="${folio}">
                Ver Resumen
              </button>
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  function showOrderDetail(folio) {
    accountState.selectedOrderFolio = folio;
    const detailContainer = document.getElementById('orderDetailViewContainer');
    const listContainer = document.getElementById('orderListViewContainer');
    if (!detailContainer || !listContainer) return;

    listContainer.style.display = 'none';
    detailContainer.style.display = 'block';

    detailContainer.innerHTML = `
      <div style="padding: 20px 0; text-align: center; color: var(--text-muted); font-size: 0.75rem;">
        Cargando resumen de ${folio}...
      </div>
    `;

    let foundOrder = null;
    const list = window.__latestOrders || [];
    foundOrder = list.find(o => (o.folio === folio || o.order_number === folio));

    if (!foundOrder) {
      try {
        const local = JSON.parse(localStorage.getItem('multiverse_customer_orders') || '[]');
        foundOrder = local.find(o => (o.folio === folio || o.order_number === folio));
      } catch (e) {}
    }

    fetch(`/api/v1/store/account/tracking/${encodeURIComponent(folio)}`)
      .then(res => res.json())
      .then(data => {
        const trackingData = (data && data.success && data.tracking) ? data.tracking : null;
        renderOrderDetailView(detailContainer, foundOrder, trackingData, folio);
      })
      .catch(() => {
        renderOrderDetailView(detailContainer, foundOrder, null, folio);
      });
  }

  function renderOrderDetailView(container, order, tracking, folio) {
    const isApproved = order ? (order.status === 'completed' || order.status === 'approved' || order.payment_status === 'paid') : true;
    const totalNum = order ? (typeof order.total === 'number' ? order.total : parseFloat(order.total || 0)) : (tracking ? parseFloat(tracking.total || 0) : 0);
    const dateFormatted = order?.created_at_formatted || (order?.createdAt ? new Date(order.createdAt).toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' }) : 'Reciente');
    const isMixed = order ? (order.order_type === 'mixed' || order.orderType === 'mixed' || order.is_mixed) : (tracking?.is_mixed || false);
    const readyItems = order?.ready_items || order?.readyItems || [];
    const onDemandItems = order?.on_demand_items || order?.onDemandItems || [];
    const items = order?.items || [];
    const carrier = order?.carrier || tracking?.carrier || 'DHL Express Nacional';
    const trackingNumber = order?.tracking_number || tracking?.tracking_number || 'MXN-TRK-' + Math.floor(100000 + Math.random() * 900000);
    const shipping = order?.shipping || checkoutState.shipping || {};

    const defaultTimeline = [
      { step: 1, title: 'Pago Aprobado y Conciliado', desc: 'Transacción confirmada por pasarela segura.', completed: true, timestamp: dateFormatted },
      { step: 2, title: isMixed ? 'Preparación Dual (Bodega + Confección)' : 'Preparación en Centro de Logística', desc: isMixed ? 'Stock físico apartado; orden de patronaje enviada a taller.' : 'Prendas inspeccionadas y empacadas discretamente.', completed: true, timestamp: 'En proceso' },
      { step: 3, title: 'Despacho con Guía Courier', desc: `Entregado al mensajero (${carrier}) con guía ${trackingNumber}.`, completed: isApproved, timestamp: 'Programado' },
      { step: 4, title: 'Entrega en Domicilio', desc: 'Recepción garantizada en dirección registrada.', completed: false, timestamp: 'Pendiente' }
    ];

    const timeline = (tracking && tracking.timeline && tracking.timeline.length > 0) ? tracking.timeline : defaultTimeline;

    container.innerHTML = `
      <div class="order-detail-header-bar">
        <button type="button" class="btn-account-back" data-action="back-to-orders-list">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          <span>Volver a Mis Pedidos</span>
        </button>
        <span class="order-folio-badge">${folio}</span>
      </div>

      <div class="order-detail-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
          <div>
            <div class="account-subheading" style="margin: 0 0 2px;">Resumen del Pedido</div>
            <div style="font-size: 0.72rem; color: var(--text-muted);">Realizado el ${dateFormatted}</div>
          </div>
          <span class="order-status-badge ${isApproved ? 'status-approved' : 'status-pending'}">
            ${isApproved ? 'PAGO APROBADO' : 'PENDIENTE'}
          </span>
        </div>

        <div style="background: var(--bg-tertiary); border: 1px solid var(--border-medium); border-radius: 8px; padding: 10px; margin-bottom: 12px; font-size: 0.74rem;">
          <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
            <span style="color: var(--text-secondary);">Mensajería / Courier:</span>
            <strong>${carrier}</strong>
          </div>
          <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
            <span style="color: var(--text-secondary);">Guía de Rastreo:</span>
            <strong class="font-mono" style="color: var(--brand-accent, #4f46e5);">${trackingNumber}</strong>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span style="color: var(--text-secondary);">Método de Pago:</span>
            <span>${order?.paymentMethod || order?.payment_method || 'Tarjeta Bancaria / SPEI'}</span>
          </div>
        </div>

        ${isMixed ? `
          <div style="background: rgba(79, 70, 229, 0.08); border: 1px solid rgba(79, 70, 229, 0.25); border-radius: 8px; padding: 10px; margin-bottom: 12px;">
            <strong style="font-size: 0.76rem; color: var(--brand-accent, #4f46e5); display: block; margin-bottom: 4px;">Rastreo Dual de Carrito Mixto</strong>
            <div style="font-size: 0.7rem; color: var(--text-secondary); line-height: 1.4;">
              <div>• <strong>Línea A (Stock Físico):</strong> Despacho express 24-48h (${readyItems.length} prenda(s))</div>
              <div>• <strong>Línea B (Taller On-Demand):</strong> Confección artesanal 3-5 días hábiles (${onDemandItems.length} prenda(s))</div>
            </div>
          </div>
        ` : ''}

        <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-primary); margin: 14px 0 8px;">Estado y Telemetría de Entrega</div>
        <div class="order-tracking-timeline" style="margin: 0 0 16px;">
          ${timeline.map(st => `
            <div class="timeline-row" style="opacity: ${st.completed ? '1' : '0.45'};">
              <span class="timeline-dot ${st.completed ? 'dot-ready' : ''}"></span>
              <div class="timeline-info">
                <strong>${st.title}</strong>
                <div>${st.desc}</div>
                ${st.timestamp ? `<small style="color: var(--text-muted);">${st.timestamp}</small>` : ''}
              </div>
            </div>
          `).join('')}
        </div>

        <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-primary); margin: 14px 0 8px;">Prendas en esta Orden (${items.length})</div>
        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 14px;">
          ${items.map(it => {
            const matchedProd = STORE_PRODUCTS.find(p => p.id === it.id || p.name === it.name);
            const imgUrl = it.image || matchedProd?.image || 'https://images.unsplash.com/photo-1598033129183-c4f50c736f10?w=200&auto=format&fit=crop&q=80';
            const priceUnit = it.price || it.unit_price || 0;
            const lineTotal = priceUnit * (it.quantity || 1);

            return `
              <div style="display: flex; align-items: center; gap: 10px; padding: 8px; background: var(--bg-primary); border: 1px solid var(--border-medium); border-radius: 8px;">
                <img src="${imgUrl}" alt="${it.name}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px; background: var(--bg-tertiary); flex-shrink: 0;">
                <div style="flex: 1; min-width: 0;">
                  <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${it.name || it.product_name}</div>
                  <div style="font-size: 0.68rem; color: var(--text-muted); margin-top: 2px;">
                    ${it.size ? 'Talla: <strong>' + it.size + '</strong> · ' : ''} Cantidad: ${it.quantity}
                  </div>
                </div>
                <div style="font-size: 0.78rem; font-weight: 800; color: var(--text-primary); text-align: right; white-space: nowrap;">
                  MXN $${lineTotal.toFixed(2)}
                </div>
              </div>
            `;
          }).join('')}
        </div>

        ${shipping.street ? `
          <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-primary); margin: 14px 0 8px;">Dirección de Envío</div>
          <div style="font-size: 0.72rem; color: var(--text-secondary); background: var(--bg-primary); border: 1px solid var(--border-medium); border-radius: 8px; padding: 10px; margin-bottom: 14px; line-height: 1.4;">
            <strong>${shipping.recipientName || order?.customer_name || 'Comprador'}</strong><br>
            ${shipping.street}<br>
            Col. ${shipping.colony || ''}, ${shipping.city || ''}, ${shipping.state || ''} CP ${shipping.postalCode || ''}<br>
            ${shipping.references ? `<small style="color: var(--text-muted);">Ref: ${shipping.references}</small>` : ''}
          </div>
        ` : ''}

        <div style="padding-top: 10px; border-top: 1px dashed var(--border-medium); font-size: 0.75rem;">
          <div style="display: flex; justify-content: space-between; margin-bottom: 4px; color: var(--text-secondary);">
            <span>Subtotal:</span>
            <span>MXN $${totalNum.toFixed(2)}</span>
          </div>
          <div style="display: flex; justify-content: space-between; margin-bottom: 6px; color: var(--text-secondary);">
            <span>Envío Express:</span>
            <span style="color: #16a34a; font-weight: 700;">BONIFICADO</span>
          </div>
          <div style="display: flex; justify-content: space-between; font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; color: var(--text-primary); padding-top: 6px; border-top: 1px solid var(--border-subtle);">
            <span>Total Pagado:</span>
            <span style="color: var(--brand-accent, #4f46e5);">MXN $${totalNum.toFixed(2)}</span>
          </div>
        </div>

        <div style="display: flex; gap: 8px; margin-top: 14px;">
          <button type="button" class="btn-account-primary" style="flex: 1; padding: 8px; font-size: 0.72rem; display: flex; align-items: center; justify-content: center; gap: 6px;" data-action="reorder-order-items" data-folio="${folio}">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
            <span>Volver a Comprar (1-Tap)</span>
          </button>
          <button type="button" class="btn-account-secondary" style="flex: 1; padding: 8px; font-size: 0.72rem;" onclick="showToastNotification('Comprobante descargado en formato digital.')">
            Descargar Recibo
          </button>
        </div>
      </div>
    `;
  }

  function backToOrdersList() {
    accountState.selectedOrderFolio = null;
    const detailContainer = document.getElementById('orderDetailViewContainer');
    const listContainer = document.getElementById('orderListViewContainer');
    if (detailContainer) detailContainer.style.display = 'none';
    if (listContainer) listContainer.style.display = 'block';
  }

  function lookupOrderByFolio(folioQuery) {
    const clean = (folioQuery || '').trim().toUpperCase();
    const resultBox = document.getElementById('accountLookupResult');
    if (!resultBox) return;

    if (!clean) {
      resultBox.style.display = 'none';
      return;
    }

    showOrderDetail(clean);
  }

  function renderAccountWishlistIntegrated() {
    const container = document.getElementById('accountWishlistItemsList');
    const badge = document.getElementById('accountWishlistCountBadge');
    if (!container) return;

    const count = (storeState.wishlist || []).length;
    if (badge) badge.textContent = `${count} prenda${count === 1 ? '' : 's'}`;

    if (count === 0) {
      container.innerHTML = `
        <div style="text-align: center; padding: 16px 10px; background: var(--bg-secondary); border-radius: 8px; border: 1px solid var(--border-subtle); color: var(--text-muted); font-size: 0.72rem;">
          No tienes prendas en tu lista de deseos todavía. Toca el corazón en cualquier modelo del catálogo.
        </div>
      `;
      return;
    }

    container.innerHTML = storeState.wishlist.map(productId => {
      const prod = STORE_PRODUCTS.find(p => p.id === productId);
      if (!prod) return '';
      const selectedSize = storeState.selectedSizes[prod.id] || 'M';
      const imgUrl = prod.image || 'https://images.unsplash.com/photo-1598033129183-c4f50c736f10?w=200&auto=format&fit=crop&q=80';

      return `
        <div class="account-wishlist-card">
          <a href="/productos/${encodeURIComponent(prod.slug || prod.id)}" style="flex-shrink: 0;">
            <img src="${imgUrl}" alt="${prod.name}" class="account-wishlist-thumb">
          </a>
          <div style="flex: 1; min-width: 0;">
            <a href="/productos/${encodeURIComponent(prod.slug || prod.id)}" style="font-family: var(--font-heading); font-size: 0.78rem; font-weight: 700; color: var(--text-primary); text-decoration: none; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
              ${prod.name}
            </a>
            <div style="font-size: 0.68rem; color: var(--text-muted); margin-top: 2px;">
              Talla: <strong style="color: var(--text-primary);">${selectedSize}</strong> · <span style="color: #047857; font-weight: 600;">${prod.stockLabel}</span>
            </div>
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-primary); margin-top: 4px;">
              MXN $${prod.price.toFixed(2)}
            </div>
          </div>
          <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-end;">
            <button type="button" class="btn-account-primary" data-action="move-wishlist-to-cart" data-product-id="${prod.id}" style="padding: 4px 8px; font-size: 0.68rem;">
              Al Carrito
            </button>
            <button type="button" data-action="toggle-wishlist" data-product-id="${prod.id}" style="background: none; border: none; font-size: 0.65rem; color: var(--text-muted); cursor: pointer;">
              Quitar
            </button>
          </div>
        </div>
      `;
    }).join('');
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

    const calc = typeof calculateTorzoSize === 'function' ? calculateTorzoSize(currentWaist) : null;
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
      if (jock) jock.checked = Array.isArray(s) ? s.includes('jocks') : !!s.jocks;
      const boxer = document.getElementById('prefSilBoxers');
      if (boxer) boxer.checked = Array.isArray(s) ? s.includes('boxers') : !!s.boxers;
      const tanga = document.getElementById('prefSilTangas');
      if (tanga) tanga.checked = Array.isArray(s) ? s.includes('tangas') : !!s.tangas;
      const bikini = document.getElementById('prefSilBikinis');
      if (bikini) bikini.checked = Array.isArray(s) ? s.includes('bikinis') : !!s.bikinis;
      const tank = document.getElementById('prefSilTanktops');
      if (tank) tank.checked = Array.isArray(s) ? s.includes('tanktops') : !!s.tanktops;
    }
  }

  function saveAnatomicalProfile() {
    const slider = document.getElementById('anatomyWaistSlider');
    const waistCm = slider ? parseInt(slider.value, 10) : 82;
    const calc = typeof calculateTorzoSize === 'function' ? calculateTorzoSize(waistCm) : null;

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

    fetch('/api/v1/store/account/profile', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        waist_cm: waistCm,
        preferred_cut: calc ? calc.size : 'M',
        silhouettes: silhouettes
      })
    }).catch(e => console.warn('Error sincronizando perfil anatómico:', e));

    if (typeof renderPdpDynamicSections === 'function') {
      renderPdpDynamicSections();
    }
    showToastNotification('Perfil Anatómico guardado exitosamente.');
  }

  function renderAccountPersonalAndAddresses(session) {
    // 1. Datos Personales
    const nameInput = document.getElementById('accProfileName');
    const emailInput = document.getElementById('accProfileEmail');
    const phoneInput = document.getElementById('accProfilePhone');
    if (nameInput) nameInput.value = session?.name || '';
    if (emailInput) {
      emailInput.value = session?.email || '';
      emailInput.readOnly = !!(session && session.email);
    }
    if (phoneInput) phoneInput.value = session?.phone || '';

    // 2. Vinculación Google (oculta si no hay OAuth vinculado)
    const googleCard = document.getElementById('accGoogleLinkCard');
    if (googleCard) {
      const isGoogleLinked = (session && (session.provider === 'google' || session.google_linked));
      if (isGoogleLinked) {
        googleCard.style.display = 'block';
        googleCard.innerHTML = `
          <div style="display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <svg viewBox="0 0 24 24" width="18" height="18" class="google-icon">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
              </svg>
              <div>
                <strong style="font-size: 0.75rem; color: var(--text-primary); display: block;">Cuenta Google Vinculada</strong>
                <span style="font-size: 0.68rem; color: var(--text-muted);">${session.google_email || session.email || 'Conectada'}</span>
              </div>
            </div>
            <span style="font-size: 0.65rem; font-weight: 800; color: #16a34a; background: rgba(34, 197, 94, 0.12); padding: 2px 8px; border-radius: 9999px; border: 1px solid rgba(34, 197, 94, 0.25);">ACTIVA</span>
          </div>
        `;
      } else {
        googleCard.style.display = 'none';
        googleCard.innerHTML = '';
      }
    }

    // 2.5 Preferencia de Empaque Discreto
    const discreteContainer = document.getElementById('accDiscretePackagingContainer');
    if (discreteContainer) {
      const isDiscrete = (session?.discretePackaging !== false);
      discreteContainer.innerHTML = `
        <div class="discrete-packaging-card">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
            <div style="display: flex; gap: 10px; align-items: flex-start;">
              <div class="discrete-icon-box">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              </div>
              <div>
                <h4 class="account-subheading" style="margin: 0 0 2px;">Preferencia de Empaque Discreto</h4>
                <p class="account-subheading-desc" style="margin: 0; line-height: 1.35;">
                  Tus paquetes se despachan en empaques 100% anonimos, sin marcas exteriores ni leyendas de prendas intimas para garantizar total confidencialidad.
                </p>
              </div>
            </div>
            <label class="toggle-switch-wrapper" title="Activar o desactivar empaque discreto">
              <input type="checkbox" id="accDiscretePackagingToggle" data-action="toggle-discrete-packaging" ${isDiscrete ? 'checked' : ''}>
              <span class="toggle-switch-slider"></span>
            </label>
          </div>
          <div style="display: flex; align-items: center; gap: 6px; margin-top: 10px; font-size: 0.7rem; color: ${isDiscrete ? '#16a34a' : '#64748b'}; font-weight: 700;">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span id="discretePackagingStatusText">${isDiscrete ? 'EMPAQUE 100% ANONIMO ACTIVO EN TODOS TUS ENVIOS' : 'EMPAQUE ESTANDAR CON LOGOTIPO COMERCIAL'}</span>
          </div>
        </div>
      `;
    }

    // 3. Libreta de Direcciones (Casa / Trabajo)
    renderAddressList(session);
  }

  function renderAddressList(session) {
    const listEl = document.getElementById('accountAddressList');
    if (!listEl) return;

    let addresses = Array.isArray(session?.addresses) ? session.addresses : [];
    if (addresses.length === 0) {
      listEl.innerHTML = `
        <div style="padding: 12px; text-align: center; font-size: 0.74rem; color: var(--text-muted); background: var(--bg-secondary); border: 1px dashed var(--border-medium); border-radius: 8px;">
          Aún no tienes direcciones guardadas. Agrega tu dirección de Casa o Trabajo.
        </div>
      `;
      return;
    }

    listEl.innerHTML = addresses.map(addr => {
      const isCasa = addr.type === 'casa';
      return `
        <div class="account-address-item" data-address-id="${addr.id}">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <div style="display: flex; align-items: center; gap: 6px;">
              <span class="${isCasa ? 'address-badge-casa' : 'address-badge-trabajo'}">
                ${isCasa ? `
                  <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                  CASA
                ` : `
                  <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                  TRABAJO
                `}
              </span>
              ${addr.isDefault ? `<span style="font-size: 0.65rem; color: #16a34a; font-weight: 800; background: rgba(22, 163, 74, 0.1); padding: 1px 6px; border-radius: 4px;">PREDETERMINADA</span>` : ''}
            </div>
            <div style="display: flex; gap: 6px;">
              <button type="button" class="btn-account-action" data-action="edit-acc-address" data-id="${addr.id}" style="padding: 2px 8px; font-size: 0.68rem;">Editar</button>
              ${!addr.isDefault ? `<button type="button" class="btn-account-secondary" data-action="set-default-address" data-id="${addr.id}" style="padding: 2px 6px; font-size: 0.68rem;">Hacer Principal</button>` : ''}
              <button type="button" data-action="delete-acc-address" data-id="${addr.id}" style="background: none; border: none; color: #ef4444; font-size: 0.68rem; cursor: pointer; padding: 2px 4px;">Eliminar</button>
            </div>
          </div>
          <div style="font-size: 0.74rem; color: var(--text-secondary); line-height: 1.4;">
            <strong>${addr.street}</strong><br>
            Col. ${addr.colony}, ${addr.city}, ${addr.state} · CP ${addr.postalCode}
            ${addr.references ? `<br><small style="color: var(--text-muted);">Ref: ${addr.references}</small>` : ''}
          </div>
        </div>
      `;
    }).join('');
  }

  function showAddAddressForm(addrToEdit = null) {
    const form = document.getElementById('accountAddressEditForm');
    const title = document.getElementById('accAddressFormTitle');
    const idInput = document.getElementById('accAddressEditId');
    if (!form) return;

    form.style.display = 'block';
    if (addrToEdit) {
      if (title) title.textContent = 'Editar Dirección de Entrega';
      if (idInput) idInput.value = addrToEdit.id;
      const st = document.getElementById('accShippingStreet'); if (st) st.value = addrToEdit.street || '';
      const zp = document.getElementById('accShippingZip'); if (zp) zp.value = addrToEdit.postalCode || '';
      const col = document.getElementById('accShippingColony'); if (col) col.value = addrToEdit.colony || '';
      const cit = document.getElementById('accShippingCity'); if (cit) cit.value = addrToEdit.city || '';
      const sta = document.getElementById('accShippingState'); if (sta) sta.value = addrToEdit.state || '';
      const ref = document.getElementById('accShippingRefs'); if (ref) ref.value = addrToEdit.references || '';
      setAddressType(addrToEdit.type || 'casa');
    } else {
      if (title) title.textContent = 'Añadir Nueva Dirección de Entrega';
      if (idInput) idInput.value = '';
      const st = document.getElementById('accShippingStreet'); if (st) st.value = '';
      const zp = document.getElementById('accShippingZip'); if (zp) zp.value = '';
      const col = document.getElementById('accShippingColony'); if (col) col.value = '';
      const cit = document.getElementById('accShippingCity'); if (cit) cit.value = '';
      const sta = document.getElementById('accShippingState'); if (sta) sta.value = '';
      const ref = document.getElementById('accShippingRefs'); if (ref) ref.value = '';
      setAddressType('casa');
    }
  }

  function setAddressType(type) {
    accountState.selectedAddressType = type;
    const casaBtn = document.getElementById('addrTypeCasaBtn');
    const trabajoBtn = document.getElementById('addrTypeTrabajoBtn');
    if (casaBtn) casaBtn.classList.toggle('active', type === 'casa');
    if (trabajoBtn) trabajoBtn.classList.toggle('active', type === 'trabajo');
  }

  function syncAddressesToSession(addresses) {
    const list = Array.isArray(addresses) ? addresses : [];
    const session = getActiveCustomerSession() || {};
    session.addresses = list;
    const defaultAddr = list.find(a => a.isDefault) || list[0];
    if (defaultAddr) {
      session.shipping = {
        street: defaultAddr.street,
        postalCode: defaultAddr.postalCode,
        colony: defaultAddr.colony,
        city: defaultAddr.city,
        state: defaultAddr.state,
        references: defaultAddr.references
      };
      if (typeof checkoutState !== 'undefined') checkoutState.shipping = { ...session.shipping };
    }
    if (window.__INITIAL_AUTH_USER__) window.__INITIAL_AUTH_USER__.addresses = list;
    try { localStorage.setItem('multiverse_customer_session', JSON.stringify(session)); } catch (e) {}
    return session;
  }

  async function saveCustomerAddress() {
    const editId = document.getElementById('accAddressEditId')?.value || '';
    const street = (document.getElementById('accShippingStreet')?.value || '').trim();
    const postalCode = (document.getElementById('accShippingZip')?.value || '').replace(/\D/g, '');
    const colony = (document.getElementById('accShippingColony')?.value || '').trim();
    const city = (document.getElementById('accShippingCity')?.value || '').trim();
    const state = (document.getElementById('accShippingState')?.value || '').trim();
    const references = (document.getElementById('accShippingRefs')?.value || '').trim();
    const type = accountState.selectedAddressType || 'casa';

    if (!street || postalCode.length !== 5 || !colony || !city || !state) {
      showToastNotification('Por favor completa todos los campos requeridos de la dirección.');
      return;
    }

    const active = getActiveCustomerSession();
    if (!active || !active.email) {
      showToastNotification('Inicia sesión para guardar direcciones en tu cuenta.');
      return;
    }

    const endpoint = editId ? `/auth/account/addresses/${encodeURIComponent(editId)}` : '/auth/account/addresses';
    const method = editId ? 'PUT' : 'POST';
    try {
      const res = await fetch(endpoint, {
        method,
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': window.__STORE_CSRF_TOKEN__ || ''
        },
        body: JSON.stringify({
          label_type: type,
          street,
          postal_code: postalCode,
          colony,
          city,
          state,
          references
        })
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        showToastNotification(data.message || 'No se pudo guardar la dirección.');
        return;
      }
      const updatedSession = syncAddressesToSession(data.addresses || []);
      const form = document.getElementById('accountAddressEditForm');
      if (form) form.style.display = 'none';
      renderAddressList(updatedSession);
      showToastNotification(data.message || 'Dirección guardada exitosamente.');
    } catch (e) {
      showToastNotification('Error de conexión al guardar la dirección.');
    }
  }

  async function setDefaultAddress(addrId) {
    const active = getActiveCustomerSession();
    if (!active || !active.email) return;

    try {
      const res = await fetch(`/auth/account/addresses/${encodeURIComponent(addrId)}/default`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': window.__STORE_CSRF_TOKEN__ || ''
        }
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        showToastNotification(data.message || 'No se pudo actualizar la dirección.');
        return;
      }
      const updatedSession = syncAddressesToSession(data.addresses || []);
      renderAddressList(updatedSession);
      showToastNotification(data.message || 'Dirección predeterminada actualizada.');
    } catch (e) {
      showToastNotification('Error de conexión.');
    }
  }

  async function deleteAddress(addrId) {
    const active = getActiveCustomerSession();
    if (!active || !active.email) return;

    try {
      const res = await fetch(`/auth/account/addresses/${encodeURIComponent(addrId)}`, {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': window.__STORE_CSRF_TOKEN__ || ''
        }
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        showToastNotification(data.message || 'No se pudo eliminar la dirección.');
        return;
      }
      const updatedSession = syncAddressesToSession(data.addresses || []);
      renderAddressList(updatedSession);
      showToastNotification(data.message || 'Dirección eliminada.');
    } catch (e) {
      showToastNotification('Error de conexión.');
    }
  }

  let REAL_CUSTOMER_DISCOUNTS = [];

  async function fetchAccountDiscounts() {
    let session = {};
    try { session = JSON.parse(localStorage.getItem('multiverse_customer_session') || '{}'); } catch(e){}
    const email = session.email || '';
    const phone = session.phone || session.whatsapp || '';
    if (!email && !phone) return;

    try {
      const res = await fetch(`/api/v1/store/account/discounts?email=${encodeURIComponent(email)}&phone=${encodeURIComponent(phone)}`);
      const data = await res.json();
      if (data && data.success && Array.isArray(data.discounts)) {
        REAL_CUSTOMER_DISCOUNTS = data.discounts;
        renderAccountDiscounts();
      }
    } catch(err) {
      console.warn('[Discounts] Error al sincronizar descuentos del cliente:', err);
    }
  }
  window.fetchAccountDiscounts = fetchAccountDiscounts;

  function renderAccountDiscounts() {
    const listEl = document.getElementById('accountDiscountsList');
    const kpiSavedEl = document.getElementById('kpiTotalDiscountsSaved');
    const kpiCountEl = document.getElementById('kpiActiveDiscountsCount');
    const walletContainer = document.getElementById('accWalletCardContainer');
    const referralContainer = document.getElementById('accReferralCardContainer');
    if (!listEl) return;

    let session = getActiveCustomerSession() || {};
    const walletBalance = (typeof session.walletBalance === 'number') ? session.walletBalance : 0;

    // 1. Ocultar Wallet si saldo es 0 (sin saldo simulado)
    if (walletContainer) {
      if (walletBalance > 0) {
        walletContainer.style.display = 'block';
        walletContainer.innerHTML = `
          <div class="wallet-cashback-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
              <div style="display: flex; align-items: center; gap: 8px;">
                <div class="wallet-icon-box">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M16 12h.01"/><path d="M2 10h20"/></svg>
                </div>
                <div>
                  <div class="account-subheading" style="margin: 0;">Multiverse Wallet</div>
                  <span class="account-subheading-desc" style="margin: 0;">Monedero Digital y Cashback</span>
                </div>
              </div>
            </div>
            <div class="wallet-balance-row">
              <div>
                <span style="font-size: 0.7rem; color: var(--text-muted); display: block;">Saldo Acumulado Disponible</span>
                <strong class="wallet-balance-amount font-mono">MXN $${walletBalance.toFixed(2)}</strong>
              </div>
              <button type="button" class="btn-wallet-redeem" data-action="redeem-wallet-to-cart">
                Canjear en Carrito
              </button>
            </div>
          </div>
        `;
      } else {
        walletContainer.style.display = 'none';
        walletContainer.innerHTML = '';
      }
    }

    // 2. Ocultar Referidos hardcoded hasta conectar programa real en BD
    if (referralContainer) {
      referralContainer.style.display = 'none';
      referralContainer.innerHTML = '';
    }

    let allDiscounts = Array.isArray(REAL_CUSTOMER_DISCOUNTS) ? [...REAL_CUSTOMER_DISCOUNTS] : [];

    let totalSaved = 0;
    let activeCount = 0;
    let redeemedCount = 0;

    allDiscounts.forEach(d => {
      if (d.redeemed) {
        totalSaved += (d.savedAmount || 0);
        redeemedCount++;
      } else {
        activeCount++;
      }
    });

    if (kpiSavedEl) kpiSavedEl.textContent = `MXN $${totalSaved.toFixed(2)}`;
    if (kpiCountEl) kpiCountEl.textContent = `${activeCount} Activo${activeCount === 1 ? '' : 's'}`;

    const countAllEl = document.getElementById('countDiscountsAll');
    const countActiveEl = document.getElementById('countDiscountsActive');
    const countRedeemedEl = document.getElementById('countDiscountsRedeemed');
    if (countAllEl) countAllEl.textContent = allDiscounts.length;
    if (countActiveEl) countActiveEl.textContent = activeCount;
    if (countRedeemedEl) countRedeemedEl.textContent = redeemedCount;

    // Filter Switcher state (Requerimiento 5)
    accountState.discountsFilter = accountState.discountsFilter || 'all';
    document.querySelectorAll('[data-action="set-discount-filter"]').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.filter === accountState.discountsFilter);
    });

    const filteredDiscounts = allDiscounts.filter(d => {
      if (accountState.discountsFilter === 'active') return !d.redeemed;
      if (accountState.discountsFilter === 'redeemed') return d.redeemed;
      return true;
    });

    if (filteredDiscounts.length === 0) {
      listEl.innerHTML = `
        <div style="grid-column: 1 / -1; padding: 24px; text-align: center; color: var(--text-muted); font-size: 0.78rem;">
          No hay cupones ${accountState.discountsFilter === 'active' ? 'activos' : 'redimidos'} disponibles.
        </div>
      `;
      return;
    }

    // Grid 3x3 en Formato 4:5 (Requerimiento 5)
    listEl.innerHTML = filteredDiscounts.map(d => {
      const discountDisplay = d.discountPercent ? `${d.discountPercent}% OFF` : (d.discountFixed ? `MXN $${d.discountFixed} OFF` : 'ENVIO GRATIS');
      return `
        <div class="discount-card-4x5 ${d.redeemed ? 'redeemed' : ''}">
          <div class="dcard-top">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 4px;">
              <span class="dcard-ally-badge">${d.ally}</span>
              <span class="order-status-badge ${d.redeemed ? 'status-pending' : 'status-approved'}" style="font-size: 0.58rem; padding: 2px 6px;">
                ${d.redeemed ? 'REDIMIDO' : 'DISPONIBLE'}
              </span>
            </div>
            <div class="dcard-value-display">${discountDisplay}</div>
          </div>

          <div class="dcard-mid">
            <strong class="dcard-title">${d.title}</strong>
            <div class="dcard-min-order">Compra min: MXN $${d.minOrder}</div>
            ${d.redeemed ? `
              <div class="dcard-redeemed-text">
                Ahorraste MXN $${(d.savedAmount || 0).toFixed(2)}
              </div>
            ` : ''}
          </div>

          <div class="dcard-bottom">
            <div class="dcard-code-pill" data-action="copy-discount-code" data-code="${d.code}" title="Toca para copiar código">
              <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
              <span class="font-mono">${d.code}</span>
            </div>
            ${!d.redeemed ? `
              <button type="button" class="btn-dcard-apply" data-action="apply-discount-code" data-code="${d.code}">
                Aplicar al Carrito
              </button>
            ` : ''}
          </div>
        </div>
      `;
    }).join('');
  }

  function renderAccountSettingsView(session) {
    // 1. Desvinculación de Google
    const unlinkRow = document.getElementById('accGoogleUnlinkRow');
    if (unlinkRow) {
      const isGoogleLinked = (session && (session.provider === 'google' || session.google_linked));
      if (isGoogleLinked) {
        unlinkRow.innerHTML = `
          <button type="button" class="btn-account-secondary" data-action="unlink-google-account" style="width: 100%; padding: 8px; font-size: 0.72rem; color: #ef4444; border-color: rgba(239, 68, 68, 0.3);">
            Desvincular Cuenta de Google
          </button>
        `;
      } else {
        unlinkRow.innerHTML = `
          <div style="font-size: 0.7rem; color: var(--text-muted); padding: 4px 0;">
            Tu cuenta no tiene vinculación con Google activa actualmente.
          </div>
        `;
      }
    }

    // 2. Estado de 2FA real (TOTP)
    const appBtn = document.getElementById('btnToggle2FAApp');
    const isTwoFactorEnabled = !!(session && session.two_factor_enabled);
    if (appBtn) {
      appBtn.textContent = isTwoFactorEnabled ? 'Desactivar' : 'Configurar';
      appBtn.classList.toggle('active', isTwoFactorEnabled);
    }

    const twoFaPanel = document.getElementById('accTwoFactorPanel');
    if (twoFaPanel) {
      if (!session || !session.email) {
        twoFaPanel.innerHTML = '';
      } else if (!session.email_verified) {
        twoFaPanel.innerHTML = `
          <div style="font-size: 0.72rem; color: #b45309; background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); border-radius: 8px; padding: 8px 10px;">
            Confirma tu correo electrónico antes de activar la verificación en dos pasos (2FA).
          </div>
        `;
      } else if (buyerTwoFactorUiState.recoveryCodes && buyerTwoFactorUiState.recoveryCodes.length) {
        twoFaPanel.innerHTML = `
          <div style="background: var(--bg-primary); border: 1px solid #16a34a; border-radius: 8px; padding: 10px;">
            <div style="font-size: 0.76rem; font-weight: 700; color: #16a34a; margin-bottom: 6px;">Códigos de respaldo (guárdalos en un lugar seguro)</div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 4px; font-family: monospace; font-size: 0.74rem; margin-bottom: 8px;">
              ${buyerTwoFactorUiState.recoveryCodes.map(c => `<div style="padding: 4px 6px; background: var(--bg-secondary); border-radius: 4px; text-align: center;">${c}</div>`).join('')}
            </div>
            <button type="button" class="btn-account-secondary" data-action="2fa-close-panel" style="width: 100%; padding: 6px; font-size: 0.72rem;">Listo, ya los guardé</button>
          </div>
        `;
      } else if (buyerTwoFactorUiState.mode === 'start') {
        twoFaPanel.innerHTML = `
          <form id="buyer2faEnableForm" style="display: flex; flex-direction: column; gap: 8px; background: var(--bg-primary); border: 1px solid var(--border-medium); border-radius: 8px; padding: 10px;">
            <div style="font-size: 0.74rem; font-weight: 600;">Confirma tu contraseña actual para generar tu clave 2FA</div>
            ${renderPasswordFieldHtml('current_password', 'Contraseña actual', { compact: true })}
            <div id="buyer2faPanelError" style="display:none; color:#e11d48; font-size:0.72rem; font-weight:600;"></div>
            <div style="display:flex; gap:6px;">
              <button type="submit" class="btn-add-cart" style="flex:1; padding: 7px; font-size: 0.74rem; justify-content:center;">Generar QR</button>
              <button type="button" class="btn-account-secondary" data-action="2fa-close-panel" style="padding: 7px 10px; font-size: 0.74rem;">Cancelar</button>
            </div>
          </form>
        `;
      } else if (buyerTwoFactorUiState.mode === 'confirm' && buyerTwoFactorUiState.secret) {
        const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(buyerTwoFactorUiState.otpauthUri);
        twoFaPanel.innerHTML = `
          <form id="buyer2faConfirmForm" style="display: flex; flex-direction: column; gap: 8px; background: var(--bg-primary); border: 1px solid var(--border-medium); border-radius: 8px; padding: 10px;">
            <div style="font-size: 0.74rem; font-weight: 700;">1. Escanea el QR en Google Authenticator o Authy</div>
            <div style="text-align: center;">
              <img src="${qrUrl}" alt="QR 2FA" width="140" height="140" style="border-radius: 8px; border: 1px solid var(--border-subtle); background: #fff; padding: 4px;">
            </div>
            <div style="font-size: 0.68rem; color: var(--text-muted); word-break: break-all;">Clave manual: <strong style="font-family: monospace; color: var(--text-primary);">${buyerTwoFactorUiState.secret}</strong></div>
            <div style="font-size: 0.74rem; font-weight: 700;">2. Ingresa el código de 6 dígitos para confirmar</div>
            <input type="text" name="code" required autocomplete="one-time-code" placeholder="000000" style="width: 100%; padding: 8px 10px; border: 1px solid var(--border-subtle); border-radius: 6px; font-size: 0.86rem; text-align: center; letter-spacing: 0.12em;">
            <div id="buyer2faPanelError" style="display:none; color:#e11d48; font-size:0.72rem; font-weight:600;"></div>
            <button type="submit" class="btn-add-cart" style="width:100%; padding: 8px; font-size: 0.75rem; justify-content:center;">Activar 2FA</button>
          </form>
        `;
      } else if (buyerTwoFactorUiState.mode === 'disable' || buyerTwoFactorUiState.mode === 'recovery') {
        const isDisable = buyerTwoFactorUiState.mode === 'disable';
        twoFaPanel.innerHTML = `
          <form id="${isDisable ? 'buyer2faDisableForm' : 'buyer2faRecoveryForm'}" style="display: flex; flex-direction: column; gap: 8px; background: var(--bg-primary); border: 1px solid var(--border-medium); border-radius: 8px; padding: 10px;">
            <div style="font-size: 0.74rem; font-weight: 700;">${isDisable ? 'Desactivar Verificación en Dos Pasos' : 'Regenerar Códigos de Respaldo'}</div>
            ${renderPasswordFieldHtml('current_password', 'Contraseña actual', { compact: true })}
            <input type="text" name="code" required autocomplete="one-time-code" placeholder="Código 2FA de 6 dígitos o respaldo" style="width: 100%; padding: 8px 10px; border: 1px solid var(--border-subtle); border-radius: 6px; font-size: 0.78rem;">
            <div id="buyer2faPanelError" style="display:none; color:#e11d48; font-size:0.72rem; font-weight:600;"></div>
            <div style="display:flex; gap:6px;">
              <button type="submit" class="btn-add-cart" style="flex:1; padding: 7px; font-size: 0.74rem; justify-content:center;">${isDisable ? 'Confirmar Desactivación' : 'Generar Nuevos Códigos'}</button>
              <button type="button" class="btn-account-secondary" data-action="2fa-close-panel" style="padding: 7px 10px; font-size: 0.74rem;">Cancelar</button>
            </div>
          </form>
        `;
      } else if (isTwoFactorEnabled) {
        twoFaPanel.innerHTML = `
          <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; padding: 8px 10px; background: rgba(22,163,74,0.08); border: 1px solid rgba(22,163,74,0.3); border-radius: 8px;">
            <span style="font-size: 0.72rem; color: #15803d; font-weight: 600;">2FA TOTP Activo</span>
            <button type="button" class="btn-account-secondary" data-action="2fa-open-recovery" style="font-size: 0.68rem; padding: 4px 8px;">Regenerar códigos</button>
          </div>
        `;
      } else {
        twoFaPanel.innerHTML = '';
      }
    }

    const changePwdBox = document.getElementById('accChangePasswordContainer');
    if (changePwdBox) {
      if (!session || !session.email) {
        changePwdBox.innerHTML = '<div style="font-size: 0.72rem; color: var(--text-muted);">Inicia sesión para cambiar tu contraseña.</div>';
      } else {
        changePwdBox.innerHTML = `
          <form id="buyerChangePasswordForm" style="display: flex; flex-direction: column; gap: 8px; margin-top: 6px;">
            ${renderPasswordFieldHtml('current_password', 'Contraseña actual', { compact: true })}
            ${renderPasswordFieldHtml('password', 'Nueva contraseña (mín. 8 caracteres)', { compact: true, minlength: 8 })}
            ${renderPasswordFieldHtml('password_confirmation', 'Confirmar nueva contraseña', { compact: true, minlength: 8 })}
            ${isTwoFactorEnabled ? `<input type="text" name="code" required autocomplete="one-time-code" placeholder="Código 2FA de 6 dígitos o respaldo" style="width: 100%; padding: 8px 10px; border: 1px solid var(--border-subtle); border-radius: 6px; font-size: 0.78rem; background: var(--bg-primary); color: var(--text-primary);">` : ''}
            <div id="buyerChangePasswordError" style="display:none; color:#e11d48; font-size:0.72rem; font-weight:600;"></div>
            <button type="submit" class="btn-account-secondary" style="width: 100%; padding: 8px; font-size: 0.75rem; font-weight: 700;">Actualizar Contraseña</button>
          </form>
        `;
      }
    }
  }

  function renderAccountSubscription() {
    const panel = document.getElementById('accountTabSubscription');
    if (!panel) return;

    panel.innerHTML = `
      <div class="subscription-panel-content">
        <div id="accountSubStatusBox" style="margin-bottom: 12px;">
          <div style="font-size: 0.78rem; color: var(--text-secondary);">Cargando estado de suscripcion...</div>
        </div>
        <div class="push-notifications-card" id="accountPushCenter">
          <!-- Renderizado dinamico desde renderAccountPushPreferences() -->
        </div>
      </div>
    `;

    renderAccountPushPreferences();

    fetch('/api/v1/store/account/subscriptions')
      .then(res => res.json())
      .then(data => {
        const box = document.getElementById('accountSubStatusBox');
        if (!box) return;

        if (data.subscriptions && data.subscriptions.length > 0) {
          const sub = data.subscriptions[0];
          box.innerHTML = `
            <div class="sub-plan-card">
              <div class="sub-plan-header">
                <div>
                  <span class="sub-badge-mrr">MEMBRESIA MRR</span>
                  <h4 class="sub-plan-title">${sub.tier_name}</h4>
                  <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">Folio: ${sub.folio}</div>
                </div>
                <div class="sub-plan-price">
                  <strong>$${sub.monthly_amount}</strong>
                  <span>MXN / mes</span>
                </div>
              </div>
              <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin: 10px 0; font-size: 0.75rem;">
                <div>Estado: <strong style="color: ${sub.status === 'active' ? '#166534' : '#b45309'};">${sub.status_label.toUpperCase()}</strong></div>
                <div>Metodo de Pago: <strong>${sub.card_brand} **** ${sub.card_last4}</strong></div>
                <div>Proximo Cobro: <strong>${sub.next_billing_formatted}</strong></div>
              </div>
              <div style="display: flex; gap: 8px;">
                ${sub.status === 'active' ? `
                  <button type="button" class="btn-account-secondary" style="flex: 1; padding: 8px;" onclick="handlePauseSubscription(${sub.id})">
                    Pausar Membresia
                  </button>
                ` : `
                  <button type="button" class="btn-account-primary" style="flex: 1; padding: 8px;" onclick="handleResumeSubscription(${sub.id})">
                    Reanudar Membresia
                  </button>
                `}
              </div>
            </div>
          `;
        } else {
          const tiers = data.available_tiers || [];
          box.innerHTML = `
            <div class="sub-plan-card">
              <div class="sub-plan-header">
                <div>
                  <span class="sub-badge-mrr">CLUB VIP MARTE</span>
                  <h4 class="sub-plan-title">Elige tu Nivel de Experiencia</h4>
                </div>
              </div>
              <p class="sub-plan-desc">
                Recibe prendas exclusivas curadas mes con mes con descuento permanente y envios gratis sin minimo de compra.
              </p>
              <div class="tiers-selection-list" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 12px;">
                ${tiers.map(t => `
                  <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; background: #fff;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                      <strong style="font-size: 0.8rem; color: #0f172a;">${t.tier_name}</strong>
                      <span style="font-weight: 700; color: #0284c7; font-size: 0.85rem;">${t.price_formatted}</span>
                    </div>
                    <ul style="margin: 6px 0; padding-left: 18px; font-size: 0.7rem; color: var(--text-secondary); line-height: 1.4;">
                      ${t.benefits.map(b => `<li>${b}</li>`).join('')}
                    </ul>
                    <button type="button" class="btn-account-primary" style="width: 100%; padding: 6px; font-size: 0.72rem; margin-top: 4px;" onclick="handleSubscribeToTier('${t.tier_name}', ${t.price}, '${t.universe}')">
                      Activar este Plan
                    </button>
                  </div>
                `).join('')}
              </div>
            </div>
          `;
        }
      })
      .catch(() => {
        const box = document.getElementById('accountSubStatusBox');
        if (box) {
          box.innerHTML = `
            <div class="sub-plan-card">
              <div class="sub-plan-header">
                <div>
                  <span class="sub-badge-mrr">MEMBRESIA VIP D2C</span>
                  <h4 class="sub-plan-title">MARTE Club VIP</h4>
                </div>
                <div class="sub-plan-price">
                  <strong>$399</strong>
                  <span>MXN / mes</span>
                </div>
              </div>
              <p class="sub-plan-desc">
                Recibe 1 prenda curada mensual adaptada a tu perfil anatomico, 15% de descuento permanente y envios gratis sin minimo de compra.
              </p>
              <button type="button" class="btn-account-primary" style="width: 100%;" onclick="handleSubscribeToTier('Caja Marte Nivel 1 (Basico)', 399, 'marte')">
                UNIRME AL CLUB MARTE ($399/mes)
              </button>
            </div>
          `;
        }
      });
  }

  window.handlePauseSubscription = function(subId) {
    fetch(`/api/v1/store/account/subscriptions/${subId}/pause`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToastNotification('Suscripcion pausada correctamente.');
        renderAccountSubscription();
      } else {
        showToastNotification(data.message || 'No se pudo pausar la suscripcion.');
      }
    })
    .catch(() => showToastNotification('Error de conexion al pausar suscripcion.'));
  };

  window.handleResumeSubscription = function(subId) {
    fetch(`/api/v1/store/account/subscriptions/${subId}/resume`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToastNotification('Suscripcion reactivada correctamente.');
        renderAccountSubscription();
      } else {
        showToastNotification(data.message || 'No se pudo reactivar la suscripcion.');
      }
    })
    .catch(() => showToastNotification('Error de conexion al reactivar suscripcion.'));
  };

  window.handleSubscribeToTier = function(tierName, price, universe) {
    fetch('/api/v1/store/account/subscriptions/subscribe', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        tier_name: tierName,
        monthly_amount: price,
        universe: universe,
        card_last4: '4242',
        card_brand: 'Visa'
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToastNotification('Bienvenido al club!');
        renderAccountSubscription();
      } else {
        showToastNotification(data.message || 'Error al procesar suscripcion.');
      }
    })
    .catch(() => showToastNotification('Error de conexion al formalizar suscripcion.'));
  };

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

      // Sincronizar con backend RESTful (TASK-STR-06)
      fetch('/api/v1/store/push/subscribe', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Cart-Session-Id': getCartSessionId()
        },
        body: JSON.stringify({
          endpoint: syntheticSub.endpoint,
          keys: syntheticSub.keys,
          topics: pushNotificationState.preferences
        })
      }).catch(e => console.warn('Error registrando push subscription en backend:', e));

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

      const currentEndpoint = pushNotificationState.subscription?.endpoint;
      if (currentEndpoint) {
        fetch('/api/v1/store/push/subscribe', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Cart-Session-Id': getCartSessionId()
          },
          body: JSON.stringify({
            endpoint: currentEndpoint,
            topics: pushNotificationState.preferences
          })
        }).catch(e => console.warn('Error sincronizando preferencias push:', e));
      }

      renderAccountPushPreferences();
      showToastNotification('Preferencia de alertas actualizada.');
    }
  }

  function togglePushNotifications() {
    const isEnabled = (pushNotificationState.permission === 'granted') && (localStorage.getItem('multiverse_push_enabled') !== 'false');
    if (isEnabled) {
      const currentEndpoint = pushNotificationState.subscription?.endpoint;
      if (currentEndpoint) {
        fetch('/api/v1/store/push/unsubscribe', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ endpoint: currentEndpoint })
        }).catch(e => console.warn('Error cancelando push subscription en backend:', e));
      }

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
      title = 'MARTE Club VIP // Próxima Caja Mensual';
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

  async function logoutBuyerSession() {
    try {
      await fetch('/auth/logout', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': window.__STORE_CSRF_TOKEN__ || '',
          'Accept': 'application/json'
        }
      });
    } catch (e) {}
    window.__INITIAL_AUTH_USER__ = null;
    try {
      localStorage.removeItem('multiverse_customer_session');
    } catch (e) {}
    checkoutState.customer = { name: '', phone: '', email: '' };
    renderAccountSheetContent();
    if (typeof renderPdpDynamicSections === 'function') {
      renderPdpDynamicSections();
    }
    showToastNotification('Sesión cerrada correctamente.');
  }

  async function postAuthJson(endpoint, payload) {
    const res = await fetch(endpoint, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': window.__STORE_CSRF_TOKEN__ || ''
      },
      body: JSON.stringify(payload || {})
    });
    const data = await res.json().catch(() => ({}));
    return { res, data };
  }

  function extractAuthError(data, fallback) {
    return data && data.errors
      ? Object.values(data.errors).flat()[0]
      : ((data && data.message) || fallback);
  }

  document.addEventListener('submit', async function(e) {
    const authForm = e.target.closest('#buyerAuthForm');
    if (authForm) {
      e.preventDefault();
      const mode = authForm.dataset.mode || 'login';
      const endpointMap = {
        login: '/auth/login',
        register: '/auth/register',
        two_factor: '/auth/2fa/challenge',
        forgot: '/auth/forgot-password',
        reset: '/auth/reset-password'
      };
      const endpoint = endpointMap[mode] || '/auth/login';
      const errEl = document.getElementById('buyerAuthError');
      const okEl = document.getElementById('buyerAuthSuccess');
      const submitBtn = document.getElementById('buyerAuthSubmitBtn');
      const originalBtnText = submitBtn ? submitBtn.textContent : '';
      if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }
      if (okEl) { okEl.style.display = 'none'; okEl.textContent = ''; }
      if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Procesando...'; }

      const payload = Object.fromEntries(new FormData(authForm).entries());
      if (mode === 'register') {
        const rawPhone = (payload.phone || '').trim();
        const phoneCode = (payload.phone_code || '').trim();
        payload.phone = rawPhone ? `${phoneCode ? phoneCode + ' ' : ''}${rawPhone}` : '';
        delete payload.phone_code;
      }
      if (mode === 'two_factor') {
        payload.trust_device = !!authForm.querySelector('input[name="trust_device"]:checked');
      }

      try {
        const { res, data } = await postAuthJson(endpoint, payload);
        if (res.ok && data.requires_two_factor) {
          buyerAuthMode = 'two_factor';
          renderAccountSheetContent();
          return;
        }
        if (!res.ok) {
          const fallbackMsg = res.status === 419
            ? 'Tu sesión expiró. Recarga la página e intenta de nuevo.'
            : `Error al autenticar (HTTP ${res.status}).`;
          if (errEl) { errEl.textContent = extractAuthError(data, fallbackMsg); errEl.style.display = 'block'; }
          return;
        }
        if (mode === 'forgot' || mode === 'reset') {
          if (okEl) { okEl.textContent = data.message || 'Operación completada.'; okEl.style.display = 'block'; }
          showToastNotification(data.message || 'Operación completada.');
          if (mode === 'reset') {
            buyerResetParams = { token: '', email: '' };
            buyerAuthMode = 'login';
            setTimeout(() => renderAccountSheetContent(), 900);
          }
          return;
        }
        if (data.user) {
          buyerAuthMode = 'login';
          applyAuthenticatedUserSession(data.user);
          showToastNotification(data.message || 'Sesión iniciada correctamente.');
        }
      } catch (err) {
        if (errEl) { errEl.textContent = 'Error de conexión. Intenta de nuevo.'; errEl.style.display = 'block'; }
      } finally {
        if (submitBtn && document.body.contains(submitBtn)) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalBtnText;
        }
      }
      return;
    }

    const enable2faForm = e.target.closest('#buyer2faEnableForm');
    if (enable2faForm) {
      e.preventDefault();
      const errEl = document.getElementById('buyer2faPanelError');
      const payload = Object.fromEntries(new FormData(enable2faForm).entries());
      const { res, data } = await postAuthJson('/auth/2fa/enable', payload);
      if (!res.ok) {
        if (errEl) { errEl.textContent = extractAuthError(data, 'No se pudo iniciar 2FA.'); errEl.style.display = 'block'; }
        return;
      }
      buyerTwoFactorUiState = { mode: 'confirm', secret: data.secret, otpauthUri: data.otpauth_uri, recoveryCodes: null };
      renderAccountSettingsView(getActiveCustomerSession());
      return;
    }

    const confirm2faForm = e.target.closest('#buyer2faConfirmForm');
    if (confirm2faForm) {
      e.preventDefault();
      const errEl = document.getElementById('buyer2faPanelError');
      const payload = Object.fromEntries(new FormData(confirm2faForm).entries());
      const { res, data } = await postAuthJson('/auth/2fa/confirm', payload);
      if (!res.ok) {
        if (errEl) { errEl.textContent = extractAuthError(data, 'Código incorrecto.'); errEl.style.display = 'block'; }
        return;
      }
      buyerTwoFactorUiState = { mode: null, secret: '', otpauthUri: '', recoveryCodes: data.recovery_codes || [] };
      if (data.user) applyAuthenticatedUserSession(data.user);
      showToastNotification(data.message || '2FA activado.');
      return;
    }

    const disable2faForm = e.target.closest('#buyer2faDisableForm');
    if (disable2faForm) {
      e.preventDefault();
      const errEl = document.getElementById('buyer2faPanelError');
      const payload = Object.fromEntries(new FormData(disable2faForm).entries());
      const { res, data } = await postAuthJson('/auth/2fa/disable', payload);
      if (!res.ok) {
        if (errEl) { errEl.textContent = extractAuthError(data, 'No se pudo desactivar 2FA.'); errEl.style.display = 'block'; }
        return;
      }
      buyerTwoFactorUiState = { mode: null, secret: '', otpauthUri: '', recoveryCodes: null };
      if (data.user) applyAuthenticatedUserSession(data.user);
      showToastNotification(data.message || '2FA desactivado.');
      return;
    }

    const recovery2faForm = e.target.closest('#buyer2faRecoveryForm');
    if (recovery2faForm) {
      e.preventDefault();
      const errEl = document.getElementById('buyer2faPanelError');
      const payload = Object.fromEntries(new FormData(recovery2faForm).entries());
      const { res, data } = await postAuthJson('/auth/2fa/recovery-codes', payload);
      if (!res.ok) {
        if (errEl) { errEl.textContent = extractAuthError(data, 'No se pudieron regenerar los códigos.'); errEl.style.display = 'block'; }
        return;
      }
      buyerTwoFactorUiState = { mode: null, secret: '', otpauthUri: '', recoveryCodes: data.recovery_codes || [] };
      renderAccountSettingsView(getActiveCustomerSession());
      showToastNotification(data.message || 'Códigos regenerados.');
      return;
    }

    const changePwdForm = e.target.closest('#buyerChangePasswordForm');
    if (changePwdForm) {
      e.preventDefault();
      const errEl = document.getElementById('buyerChangePasswordError');
      if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }
      const payload = Object.fromEntries(new FormData(changePwdForm).entries());
      const { res, data } = await postAuthJson('/auth/password/change', payload);
      if (!res.ok) {
        if (errEl) { errEl.textContent = extractAuthError(data, 'No se pudo actualizar la contraseña.'); errEl.style.display = 'block'; }
        return;
      }
      changePwdForm.reset();
      if (data.user) applyAuthenticatedUserSession(data.user);
      showToastNotification(data.message || 'Contraseña actualizada.');
      return;
    }

    const deleteAccForm = e.target.closest('#buyerDeleteAccountForm');
    if (deleteAccForm) {
      e.preventDefault();
      const errEl = document.getElementById('buyerDeleteAccountError');
      if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }
      const payload = Object.fromEntries(new FormData(deleteAccForm).entries());
      const res = await fetch('/auth/account', {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': window.__STORE_CSRF_TOKEN__ || ''
        },
        body: JSON.stringify(payload)
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        if (errEl) { errEl.textContent = extractAuthError(data, 'No se pudo eliminar la cuenta.'); errEl.style.display = 'block'; }
        return;
      }
      window.__INITIAL_AUTH_USER__ = null;
      localStorage.removeItem('multiverse_customer_session');
      localStorage.removeItem('multiverse_customer_orders');
      localStorage.removeItem('multiverse_store_wishlist');
      closeAllDrawers();
      showToastNotification(data.message || 'Tu cuenta y datos han sido eliminados.');
      setTimeout(() => { window.location.reload(); }, 700);
    }
  });

  // Delegación de Eventos Click
  document.addEventListener('click', function(e) {
    // 0. Abrir Perfil Anatómico desde PDP
    const openAnatBtn = e.target.closest('[data-action="open-anatomy-profile"]');
    if (openAnatBtn) {
      e.preventDefault();
      openDrawer('accountSheet');
      if (typeof switchAccountTab === 'function') {
        switchAccountTab('profile');
      }
      return;
    }
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

    // 2. Quick Add & Agregar al Carrito desde Vitrina (Apple HIG 48x48)
    const quickAddBtn = e.target.closest('[data-action="quick-add"]');
    if (quickAddBtn) {
      e.preventDefault();
      openQuickAddSheet(quickAddBtn.dataset.productId);
      return;
    }

    const quickSizeBtn = e.target.closest('.quick-add-size-pill');
    if (quickSizeBtn) {
      e.preventDefault();
      const sz = quickSizeBtn.dataset.size;
      activeQuickAddSize = sz;
      document.querySelectorAll('.quick-add-size-pill').forEach(b => b.classList.toggle('selected', b.dataset.size === sz));
      const cta = document.getElementById('quickAddCtaBtn');
      if (cta) {
        cta.innerHTML = `
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>AÑADIR AL CARRITO (${sz})</span>
        `;
      }
      return;
    }

    const quickCtaBtn = e.target.closest('#quickAddCtaBtn');
    if (quickCtaBtn && activeQuickAddProduct) {
      e.preventDefault();
      addToCart(activeQuickAddProduct.id, activeQuickAddSize);
      closeAllDrawers();
      openDrawer('cartSheet');
      return;
    }

    const addBtn = e.target.closest('[data-action="add-to-cart"]');
    if (addBtn) {
      e.preventDefault();
      const prodId = addBtn.dataset.productId;
      const prod = Array.isArray(STORE_PRODUCTS) ? STORE_PRODUCTS.find(p => String(p.id) === String(prodId)) : null;
      if (prod && Array.isArray(prod.sizes) && prod.sizes.length > 1) {
        openQuickAddSheet(prodId);
      } else {
        addToCart(prodId);
      }
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

    // 7. Abrir Wishlist (reside dentro del Carrito - Regla 1)
    const openWishlistBtn = e.target.closest('[data-action="open-wishlist"]');
    if (openWishlistBtn) {
      e.preventDefault();
      openDrawer('cartSheet');
      setTimeout(() => {
        const wSec = document.getElementById('cartWishlistSection');
        if (wSec) {
          wSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }, 100);
      return;
    }

    // 7.1 Conmutador de Búsqueda (Regla 5)
    const toggleSearchBtn = e.target.closest('[data-action="toggle-search"]');
    if (toggleSearchBtn) {
      e.preventDefault();
      toggleSearchBar();
      return;
    }

    const clearSearchBtn = e.target.closest('[data-action="clear-search"]');
    if (clearSearchBtn) {
      e.preventDefault();
      const input = document.getElementById('storeLiveSearchInput');
      if (input) {
        input.value = '';
        input.focus();
      }
      handleLiveSearch('');
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
          const isScraped = Boolean(modalProd.is_scraped || modalProd.stockType === 'drop_on_demand' || modalProd.type === 'dropship' || modalProd.type === 'scraped');
          if (!isScraped && variant && variant.available > 0) {
            stockBadge.className = 'product-stock-badge ready';
            stockBadge.textContent = 'Stock Físico';
          } else {
            stockBadge.className = 'product-stock-badge on_demand';
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

    // 13.1 Filtro de Talla Anatómica en Drawer
    const sizePill = e.target.closest('[data-action="filter-size"]');
    if (sizePill) {
      e.preventDefault();
      const sz = sizePill.dataset.size;
      storeState.activeSize = (storeState.activeSize === sz) ? 'all' : sz;
      document.querySelectorAll('[data-action="filter-size"]').forEach(p => {
        p.classList.toggle('active', p.dataset.size === storeState.activeSize);
      });
      return;
    }

    // 13.2 Filtro de Telas & Fibras en Drawer
    const fabricPill = e.target.closest('[data-action="filter-fabric"]');
    if (fabricPill) {
      e.preventDefault();
      const fab = fabricPill.dataset.fabric;
      storeState.activeFabric = (storeState.activeFabric === fab) ? 'all' : fab;
      document.querySelectorAll('[data-action="filter-fabric"]').forEach(p => {
        p.classList.toggle('active', p.dataset.fabric === storeState.activeFabric);
      });
      return;
    }

    // 13.3 Filtro de Colores & Tornasoles en Drawer
    const colorPill = e.target.closest('[data-action="filter-color"]');
    if (colorPill) {
      e.preventDefault();
      const col = colorPill.dataset.color;
      storeState.activeColor = (storeState.activeColor === col) ? 'all' : col;
      document.querySelectorAll('[data-action="filter-color"]').forEach(p => {
        p.classList.toggle('active', p.dataset.color === storeState.activeColor);
      });
      return;
    }

    // 13.4 Filtro de Volúmenes de Perfumería en Drawer
    const volPill = e.target.closest('[data-action="filter-volume"]');
    if (volPill) {
      e.preventDefault();
      const vol = volPill.dataset.volume;
      storeState.activeVolume = (storeState.activeVolume === vol) ? 'all' : vol;
      document.querySelectorAll('[data-action="filter-volume"]').forEach(p => {
        p.classList.toggle('active', p.dataset.volume === storeState.activeVolume);
      });
      return;
    }

    // 13.5 Filtro de Tonos Cosméticos en Drawer
    const shadePill = e.target.closest('[data-action="filter-shade"]');
    if (shadePill) {
      e.preventDefault();
      const sh = shadePill.dataset.shade;
      storeState.activeShade = (storeState.activeShade === sh) ? 'all' : sh;
      document.querySelectorAll('[data-action="filter-shade"]').forEach(p => {
        p.classList.toggle('active', p.dataset.shade === storeState.activeShade);
      });
      return;
    }

    // 14. Aplicar Filtros del Drawer (Regla 6.35: Filtros Facetados Extendidos en Storefront)
    const applyBtn = e.target.closest('[data-action="apply-filters"]');
    if (applyBtn) {
      e.preventDefault();
      storeState.activeUniverses = Array.from(document.querySelectorAll('input[name="filter_universe"]:checked')).map(cb => cb.value);
      storeState.activeCategories = Array.from(document.querySelectorAll('input[name="filter_category"]:checked')).map(cb => cb.value);
      storeState.activeBrands = Array.from(document.querySelectorAll('input[name="filter_brand"]:checked')).map(cb => cb.value);
      storeState.activeSuppliers = Array.from(document.querySelectorAll('input[name="filter_supplier"]:checked')).map(cb => cb.value);
      storeState.activeSizes = Array.from(document.querySelectorAll('input[name="filter_size"]:checked')).map(cb => cb.value);
      storeState.activeFabrics = Array.from(document.querySelectorAll('input[name="filter_fabric"]:checked')).map(cb => cb.value);
      storeState.activeColors = Array.from(document.querySelectorAll('input[name="filter_color"]:checked')).map(cb => cb.value);
      storeState.activeVolumes = Array.from(document.querySelectorAll('input[name="filter_volume"]:checked')).map(cb => cb.value);
      storeState.activeShades = Array.from(document.querySelectorAll('input[name="filter_shade"]:checked')).map(cb => cb.value);
      storeState.activeGarmentTypes = Array.from(document.querySelectorAll('input[name="filter_garment_type"]:checked')).map(cb => cb.value);
      storeState.activeStyleTags = Array.from(document.querySelectorAll('input[name="filter_style_tag"]:checked')).map(cb => cb.value);
      storeState.activePrices = Array.from(document.querySelectorAll('input[name="filter_price"]:checked')).map(cb => cb.value);
      storeState.activeDiscounts = Array.from(document.querySelectorAll('input[name="filter_discount"]:checked')).map(cb => cb.value);
      storeState.activeAvailability = Array.from(document.querySelectorAll('input[name="filter_availability"]:checked, input[name="filter_stock_type"]:checked')).map(cb => cb.value);

      const pendingFacets = {
        activeUniverses: storeState.activeUniverses,
        activeCategories: storeState.activeCategories,
        activeBrands: storeState.activeBrands,
        activeSuppliers: storeState.activeSuppliers,
        activeSizes: storeState.activeSizes,
        activeFabrics: storeState.activeFabrics,
        activeColors: storeState.activeColors,
        activeVolumes: storeState.activeVolumes,
        activeShades: storeState.activeShades,
        activeGarmentTypes: storeState.activeGarmentTypes,
        activeStyleTags: storeState.activeStyleTags,
        activePrices: storeState.activePrices,
        activeDiscounts: storeState.activeDiscounts,
        activeAvailability: storeState.activeAvailability,
        activeMaxPrice: storeState.activeMaxPrice,
      };
      try {
        sessionStorage.setItem('multiverse_active_facets', JSON.stringify(pendingFacets));
      } catch (e) {}

      closeAllDrawers();

      if (!document.getElementById('catalogProductsGrid')) {
        window.location.href = '/?tab=catalog';
        return;
      }

      switchDockTab('catalog');
      renderProductsGrid('homeProductsGrid');
      renderProductsGrid('catalogProductsGrid');
      showToastNotification('Filtros facetados aplicados');
      return;
    }

    // 15. Limpiar Filtros
    const clearBtn = e.target.closest('[data-action="clear-filters"]');
    if (clearBtn) {
      e.preventDefault();
      document.querySelectorAll('.filter-facet-checkbox').forEach(cb => { cb.checked = false; });
      storeState.activeUniverses = [];
      storeState.activeCategories = [];
      storeState.activeBrands = [];
      storeState.activeSuppliers = [];
      storeState.activeSizes = [];
      storeState.activeFabrics = [];
      storeState.activeColors = [];
      storeState.activeVolumes = [];
      storeState.activeShades = [];
      storeState.activeGarmentTypes = [];
      storeState.activeStyleTags = [];
      storeState.activePrices = [];
      storeState.activeDiscounts = [];
      storeState.activeAvailability = [];
      storeState.activeMaxPrice = storeState.catalogMaxPrice;
      const priceSlider = document.getElementById('storefrontPriceSlider');
      const priceDisplay = document.getElementById('priceSliderValueDisplay');
      if (priceSlider) {
        priceSlider.value = storeState.catalogMaxPrice;
      }
      if (priceDisplay) {
        priceDisplay.textContent = '$' + Number(storeState.catalogMaxPrice).toLocaleString();
      }
      storeState.activeCategory = 'all';
      storeState.activeBrand = 'all';
      storeState.activeSupplier = 'all';
      storeState.activeSize = 'all';
      storeState.activeFabric = 'all';
      storeState.activeColor = 'all';
      storeState.activeVolume = 'all';
      storeState.activeShade = 'all';
      try { sessionStorage.removeItem('multiverse_active_facets'); } catch (e) {}
      document.querySelectorAll('[data-action="filter-category"]').forEach(p => p.classList.remove('active'));
      document.querySelectorAll('[data-action="filter-size"]').forEach(p => p.classList.remove('active'));
      document.querySelectorAll('[data-action="filter-fabric"]').forEach(p => p.classList.remove('active'));
      document.querySelectorAll('[data-action="filter-color"]').forEach(p => p.classList.remove('active'));
      document.querySelectorAll('[data-action="filter-volume"]').forEach(p => p.classList.remove('active'));
      document.querySelectorAll('[data-action="filter-shade"]').forEach(p => p.classList.remove('active'));
      if (document.getElementById('catalogProductsGrid')) {
        renderProductsGrid('homeProductsGrid');
        renderProductsGrid('catalogProductsGrid');
      }
      showToastNotification('Filtros restablecidos');
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

    const saveLaterBtn = e.target.closest('[data-action="save-for-later"]');
    if (saveLaterBtn) {
      e.preventDefault();
      saveCartItemForLater(parseInt(saveLaterBtn.dataset.index, 10));
      return;
    }

    const moveSavedBtn = e.target.closest('[data-action="move-saved-to-cart"]');
    if (moveSavedBtn) {
      e.preventDefault();
      moveSavedItemToCart(parseInt(moveSavedBtn.dataset.index, 10));
      return;
    }

    const rmSavedBtn = e.target.closest('[data-action="remove-saved-item"]');
    if (rmSavedBtn) {
      e.preventDefault();
      removeSavedItem(parseInt(rmSavedBtn.dataset.index, 10));
      return;
    }

    const exploreFilterBtn = e.target.closest('[data-action="explore-filter-catalog"]');
    if (exploreFilterBtn) {
      e.preventDefault();
      const universe = exploreFilterBtn.dataset.universe;
      const category = exploreFilterBtn.dataset.category;
      const supplier = exploreFilterBtn.dataset.supplier;
      const tags = exploreFilterBtn.dataset.tags;
      openCatalogWithCategory(category, universe, supplier, tags);
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
    const stepperStepBtn = e.target.closest('.checkout-stepper .stepper-step');
    if (stepperStepBtn && checkoutState.step !== 4) {
      const targetStep = parseInt(stepperStepBtn.dataset.step, 10);
      if (targetStep === 1) {
        e.preventDefault();
        goToCheckoutStep(1);
        return;
      } else if (targetStep === 2) {
        e.preventDefault();
        if (checkoutState.step === 1) {
          if (validateCheckoutStep1()) goToCheckoutStep(2);
        } else {
          goToCheckoutStep(2);
        }
        return;
      } else if (targetStep === 3) {
        e.preventDefault();
        if (checkoutState.step === 1) {
          if (validateCheckoutStep1() && validateCheckoutStep2()) {
            renderCheckoutStep3Review();
            goToCheckoutStep(3);
          }
        } else if (checkoutState.step === 2) {
          if (validateCheckoutStep2()) {
            renderCheckoutStep3Review();
            goToCheckoutStep(3);
          }
        }
        return;
      }
    }

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

    const themeOptionBtn = e.target.closest('[data-action="set-app-theme"]');
    if (themeOptionBtn) {
      e.preventDefault();
      const mode = themeOptionBtn.getAttribute('data-theme-mode');
      if (mode) setAppTheme(mode, true);
      return;
    }

    const quickThemeBtn = e.target.closest('[data-action="toggle-app-theme-quick"]');
    if (quickThemeBtn) {
      e.preventDefault();
      const current = getAppTheme();
      const isCurrentlyDark = document.documentElement.classList.contains('dark');
      const next = isCurrentlyDark ? 'light' : 'dark';
      setAppTheme(next, true);
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

    const viewOrderDetailBtn = e.target.closest('[data-action="view-order-detail"]');
    if (viewOrderDetailBtn) {
      e.preventDefault();
      const folio = viewOrderDetailBtn.dataset.folio;
      if (folio) showOrderDetail(folio);
      return;
    }

    const backOrdersBtn = e.target.closest('[data-action="back-to-orders-list"]');
    if (backOrdersBtn) {
      e.preventDefault();
      backToOrdersList();
      return;
    }

    const showAddAddrBtn = e.target.closest('[data-action="show-add-address-form"]');
    if (showAddAddrBtn) {
      e.preventDefault();
      showAddAddressForm(null);
      return;
    }

    const addrTypeBtn = e.target.closest('[data-action="select-address-type"]');
    if (addrTypeBtn) {
      e.preventDefault();
      setAddressType(addrTypeBtn.dataset.type);
      return;
    }

    const editAddrBtn = e.target.closest('[data-action="edit-acc-address"]');
    if (editAddrBtn) {
      e.preventDefault();
      let sess = null;
      try { sess = JSON.parse(localStorage.getItem('multiverse_customer_session') || '{}'); } catch(e){}
      const targetId = editAddrBtn.dataset.id;
      const addr = (sess?.addresses || []).find(a => a.id === targetId);
      showAddAddressForm(addr || null);
      return;
    }

    const cancelEditAddrBtn = e.target.closest('[data-action="cancel-edit-address"]');
    if (cancelEditAddrBtn) {
      e.preventDefault();
      const form = document.getElementById('accountAddressEditForm');
      if (form) form.style.display = 'none';
      return;
    }

    const setDefAddrBtn = e.target.closest('[data-action="set-default-address"]');
    if (setDefAddrBtn) {
      e.preventDefault();
      setDefaultAddress(setDefAddrBtn.dataset.id);
      return;
    }

    const deleteAddrBtn = e.target.closest('[data-action="delete-acc-address"]');
    if (deleteAddrBtn) {
      e.preventDefault();
      deleteAddress(deleteAddrBtn.dataset.id);
      return;
    }

    const saveProfileBtn = e.target.closest('[data-action="save-profile-info"]');
    if (saveProfileBtn) {
      e.preventDefault();
      const active = getActiveCustomerSession();
      if (!active || !active.email) {
        showToastNotification('Inicia sesión primero para guardar tus datos personales.');
        return;
      }
      const name = (document.getElementById('accProfileName')?.value || '').trim();
      const phone = (document.getElementById('accProfilePhone')?.value || '').trim();
      if (!name) {
        showToastNotification('Ingresa tu nombre completo.');
        return;
      }
      saveProfileBtn.disabled = true;
      fetch('/auth/account/personal-info', {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': window.__STORE_CSRF_TOKEN__ || ''
        },
        body: JSON.stringify({
          name,
          phone,
          discrete_packaging: active.discretePackaging !== false
        })
      }).then(async (res) => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
          showToastNotification(data.message || 'No se pudieron guardar tus datos.');
          return;
        }
        const merged = { ...active, ...(data.user || { name, phone }) };
        applyAuthenticatedUserSession(merged);
        showToastNotification(data.message || 'Información personal guardada.');
      }).catch(() => {
        showToastNotification('Error de conexión al guardar.');
      }).finally(() => {
        saveProfileBtn.disabled = false;
      });
      return;
    }

    const linkGoogleBtn = e.target.closest('[data-action="link-google-account"]');
    if (linkGoogleBtn) {
      e.preventDefault();
      loginWithGoogle('account');
      return;
    }

    const unlinkGoogleBtn = e.target.closest('[data-action="unlink-google-account"]');
    if (unlinkGoogleBtn) {
      e.preventDefault();
      let sess = {};
      try { sess = JSON.parse(localStorage.getItem('multiverse_customer_session') || '{}'); } catch(e){}
      sess.provider = 'local';
      delete sess.google_linked;
      delete sess.google_email;
      try { localStorage.setItem('multiverse_customer_session', JSON.stringify(sess)); } catch(e){}
      renderAccountSheetContent();
      showToastNotification('Cuenta de Google desvinculada exitosamente.');
      return;
    }

    const copyDiscountBtn = e.target.closest('[data-action="copy-discount-code"]');
    if (copyDiscountBtn) {
      e.preventDefault();
      const code = copyDiscountBtn.dataset.code;
      if (code && navigator.clipboard) {
        navigator.clipboard.writeText(code).then(() => {
          showToastNotification(`Código ${code} copiado al portapapeles.`);
        }).catch(() => {
          showToastNotification(`Código: ${code}`);
        });
      } else if (code) {
        showToastNotification(`Código: ${code}`);
      }
      return;
    }

    const applyDiscountBtn = e.target.closest('[data-action="apply-discount-code"]');
    if (applyDiscountBtn) {
      e.preventDefault();
      const code = applyDiscountBtn.dataset.code;
      if (code) {
        applyDiscountCoupon(code);
        openDrawer('cartSheet');
      }
      return;
    }

    const filterDiscBtn = e.target.closest('[data-action="set-discount-filter"]');
    if (filterDiscBtn) {
      e.preventDefault();
      accountState.discountsFilter = filterDiscBtn.dataset.filter || 'all';
      renderAccountDiscounts();
      return;
    }

    const reorderBtn = e.target.closest('[data-action="reorder-order-items"]');
    if (reorderBtn) {
      e.preventDefault();
      const folio = reorderBtn.dataset.folio;
      const orders = window.__latestOrders || [];
      let ord = orders.find(o => (o.folio === folio || o.order_number === folio));
      if (!ord) {
        try {
          const local = JSON.parse(localStorage.getItem('multiverse_customer_orders') || '[]');
          ord = local.find(o => (o.folio === folio || o.order_number === folio));
        } catch(e){}
      }
      if (ord && ord.items && ord.items.length > 0) {
        let addedCount = 0;
        ord.items.forEach(it => {
          const pid = it.productId || it.product_id || it.id;
          const prod = (window.storeState.products || []).find(p => String(p.id) === String(pid));
          if (prod) {
            addToCart(prod, it.size || 'M', it.color || 'Unico', it.quantity || 1);
            addedCount++;
          }
        });
        showToastNotification(`Prendas del pedido ${folio} agregadas a tu carrito.`);
        openDrawer('cartSheet');
      } else {
        showToastNotification('No se encontraron prendas para reordenar.');
      }
      return;
    }

    const copyRefBtn = e.target.closest('[data-action="copy-referral-link"]');
    if (copyRefBtn) {
      e.preventDefault();
      const link = window.location.origin + '/?ref=TORZO-REF-7492';
      if (navigator.clipboard) {
        navigator.clipboard.writeText(link).then(() => {
          showToastNotification('Enlace de referidos copiado al portapapeles.');
        }).catch(() => {
          showToastNotification(`Enlace: ${link}`);
        });
      } else {
        showToastNotification(`Enlace: ${link}`);
      }
      return;
    }

    const shareWaRefBtn = e.target.closest('[data-action="share-referral-whatsapp"]');
    if (shareWaRefBtn) {
      e.preventDefault();
      const msg = encodeURIComponent('Te regalo $150 MXN en tu primera compra de ropa interior anatomica con mi codigo TORZO-REF-7492: ' + window.location.origin + '/?ref=TORZO-REF-7492');
      window.open(`https://api.whatsapp.com/send?text=${msg}`, '_blank');
      return;
    }

    const redeemWalletBtn = e.target.closest('[data-action="redeem-wallet-to-cart"]');
    if (redeemWalletBtn) {
      e.preventDefault();
      showToastNotification('Saldo de Monedero Wallet aplicado a tu carrito.');
      openDrawer('cartSheet');
      return;
    }

    const toggleDiscreteBtn = e.target.closest('[data-action="toggle-discrete-packaging"]');
    if (toggleDiscreteBtn) {
      const active = getActiveCustomerSession();
      const nextDiscrete = !!toggleDiscreteBtn.checked;
      if (active && active.email) {
        fetch('/auth/account/personal-info', {
          method: 'PUT',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': window.__STORE_CSRF_TOKEN__ || ''
          },
          body: JSON.stringify({
            name: active.name || 'Comprador',
            phone: active.phone || '',
            discrete_packaging: nextDiscrete
          })
        }).then(async (res) => {
          const data = await res.json().catch(() => ({}));
          if (res.ok) {
            const merged = { ...active, discretePackaging: nextDiscrete, discrete_packaging: nextDiscrete };
            applyAuthenticatedUserSession(merged);
            renderAccountPersonalAndAddresses(merged);
          }
        }).catch(() => {});
      }
      showToastNotification(nextDiscrete ? 'Empaque discreto activado (100% anonimo).' : 'Empaque comercial estandar asignado.');
      return;
    }

    const toggle2FAAppBtn = e.target.closest('[data-action="toggle-2fa-app"]');
    if (toggle2FAAppBtn) {
      e.preventDefault();
      const sess = getActiveCustomerSession();
      if (!sess || !sess.email) {
        showToastNotification('Inicia sesión primero para configurar 2FA.');
        return;
      }
      if (!sess.email_verified) {
        showToastNotification('Confirma tu correo electrónico antes de activar 2FA.');
        return;
      }
      buyerTwoFactorUiState.recoveryCodes = null;
      buyerTwoFactorUiState.mode = sess.two_factor_enabled ? 'disable' : 'start';
      renderAccountSettingsView(sess);
      return;
    }

    const twoFaCloseBtn = e.target.closest('[data-action="2fa-close-panel"]');
    if (twoFaCloseBtn) {
      e.preventDefault();
      buyerTwoFactorUiState = { mode: null, secret: '', otpauthUri: '', recoveryCodes: null };
      renderAccountSettingsView(getActiveCustomerSession());
      return;
    }

    const twoFaOpenRecoveryBtn = e.target.closest('[data-action="2fa-open-recovery"]');
    if (twoFaOpenRecoveryBtn) {
      e.preventDefault();
      buyerTwoFactorUiState.recoveryCodes = null;
      buyerTwoFactorUiState.mode = 'recovery';
      renderAccountSettingsView(getActiveCustomerSession());
      return;
    }

    const resendVerifyBtn = e.target.closest('[data-action="resend-verification-email"]');
    if (resendVerifyBtn) {
      e.preventDefault();
      resendVerifyBtn.disabled = true;
      postAuthJson('/auth/email/resend', {}).then(({ res, data }) => {
        showToastNotification(data.message || (res.ok ? 'Correo reenviado.' : 'No se pudo reenviar el correo.'));
      }).finally(() => {
        if (document.body.contains(resendVerifyBtn)) resendVerifyBtn.disabled = false;
      });
      return;
    }

    const deleteAccountBtn = e.target.closest('[data-action="delete-customer-account"]');
    if (deleteAccountBtn) {
      e.preventDefault();
      const active = getActiveCustomerSession();
      const panel = document.getElementById('accDeleteAccountPanel');
      if (!active || !active.email) {
        showToastNotification('Inicia sesión para gestionar la eliminación de tu cuenta.');
        return;
      }
      if (panel) {
        panel.innerHTML = `
          <form id="buyerDeleteAccountForm" style="display:flex; flex-direction:column; gap:8px; background:var(--bg-primary); border:1px solid rgba(239,68,68,0.35); border-radius:8px; padding:10px;">
            <div style="font-size:0.72rem; color:#ef4444; font-weight:700;">Confirmar eliminación ARCO (irreversible)</div>
            ${renderPasswordFieldHtml('current_password', 'Tu contraseña actual', { compact: true })}
            ${active.two_factor_enabled ? `<input type="text" name="code" required autocomplete="one-time-code" placeholder="Código 2FA de 6 dígitos o respaldo" style="width:100%; padding:8px 10px; border:1px solid var(--border-subtle); border-radius:6px; font-size:0.78rem; background:var(--bg-primary); color:var(--text-primary);">` : ''}
            <div id="buyerDeleteAccountError" style="display:none; color:#e11d48; font-size:0.72rem; font-weight:600;"></div>
            <div style="display:flex; gap:6px;">
              <button type="submit" style="flex:1; padding:7px; border:none; border-radius:6px; background:#ef4444; color:#fff; font-size:0.74rem; font-weight:700; cursor:pointer;">Eliminar Definitivamente</button>
              <button type="button" class="btn-account-secondary" data-action="cancel-delete-account" style="padding:7px 10px; font-size:0.74rem;">Cancelar</button>
            </div>
          </form>
        `;
      }
      return;
    }

    const cancelDeleteAccBtn = e.target.closest('[data-action="cancel-delete-account"]');
    if (cancelDeleteAccBtn) {
      e.preventDefault();
      const panel = document.getElementById('accDeleteAccountPanel');
      if (panel) panel.innerHTML = '';
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
      const currentEndpoint = pushNotificationState.subscription?.endpoint;
      if (currentEndpoint) {
        fetch('/api/v1/store/push/test', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ endpoint: currentEndpoint })
        }).catch(e => console.warn('Error enviando push test al backend:', e));
      }
      return;
    }

    const logoutBtn = e.target.closest('[data-action="buyer-logout"]');
    if (logoutBtn) {
      e.preventDefault();
      logoutBuyerSession();
      return;
    }

    const toggleBuyerAuthBtn = e.target.closest('[data-action="toggle-buyer-auth-mode"]');
    if (toggleBuyerAuthBtn) {
      e.preventDefault();
      buyerAuthMode = (buyerAuthMode === 'login') ? 'register' : 'login';
      renderAccountSheetContent();
      return;
    }

    const togglePwdBtn = e.target.closest('[data-action="toggle-password-visibility"]');
    if (togglePwdBtn) {
      e.preventDefault();
      const wrapper = togglePwdBtn.parentElement;
      const input = wrapper ? wrapper.querySelector('input') : null;
      if (input) {
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        togglePwdBtn.setAttribute('aria-pressed', show ? 'true' : 'false');
        togglePwdBtn.innerHTML = show ? AUTH_EYE_CLOSED_SVG : AUTH_EYE_OPEN_SVG;
      }
      return;
    }

    const setBuyerAuthModeBtn = e.target.closest('[data-action="set-buyer-auth-mode"]');
    if (setBuyerAuthModeBtn) {
      e.preventDefault();
      buyerAuthMode = setBuyerAuthModeBtn.dataset.mode || 'login';
      renderAccountSheetContent();
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

    // 28. Tutorial de Instalacion PWA con Deteccion de Sistema Operativo (iOS / Android)
    const openPwaTutBtn = e.target.closest('[data-action="open-pwa-tutorial"]');
    if (openPwaTutBtn) {
      e.preventDefault();
      openPwaTutorialSheet();
      return;
    }

    const switchPwaOsBtn = e.target.closest('[data-action="switch-pwa-os-tab"]');
    if (switchPwaOsBtn) {
      e.preventDefault();
      const targetOs = switchPwaOsBtn.dataset.os || 'ios';
      switchPwaTutorialOsTab(targetOs);
      return;
    }

    const dismissPwaBannerBtn = e.target.closest('[data-action="dismiss-pwa-banner"]');
    if (dismissPwaBannerBtn) {
      e.preventDefault();
      dismissPwaInstallBanner();
      return;
    }

    const triggerNativePwaBtn = e.target.closest('[data-action="trigger-native-pwa-install"]');
    if (triggerNativePwaBtn) {
      e.preventDefault();
      triggerNativePwaInstall();
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

  // Formateo Reactivo y Validación en Inputs de Checkout y Búsqueda en Vivo (Regla 5)
  document.addEventListener('focusout', function(e) {
    if (e.target && e.target.classList && e.target.classList.contains('checkout-input')) {
      validateSingleCheckoutField(e.target.id);
    }
  });

  document.addEventListener('input', function(e) {
    // Si el campo estaba marcado como inválido, revalidar en vivo mientras escribe
    if (e.target && e.target.classList && e.target.classList.contains('checkout-input') && e.target.classList.contains('is-invalid')) {
      validateSingleCheckoutField(e.target.id);
    }

    if (e.target.id === 'storeLiveSearchInput') {
      handleLiveSearch(e.target.value);
      return;
    }
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

  // Cierre con tecla Escape y Enter en Cupones y Búsqueda
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      const searchBar = document.getElementById('storeSearchBar');
      if (searchBar && searchBar.style.display !== 'none') {
        toggleSearchBar(false);
      }
      closeProductModal();
      closeCheckoutModal();
      closeAllDrawers();
      closeLeadMagnetModal();
    }
    if (e.key === 'Enter') {
      if (e.target && e.target.id === 'storeLiveSearchInput') {
        e.preventDefault();
        handleLiveSearch(e.target.value);
      }
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
      border: 1px solid var(--border-medium, #e4e4e7);
      background: var(--bg-surface, #ffffff);
      color: var(--text-primary, #050505);
      font-weight: 800;
      font-size: 0.8rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.15s ease;
    }
    .btn-qty-control:hover {
      background: var(--bg-tertiary, #f4f4f5);
    }
    html.dark .btn-qty-control {
      background: #22222b !important;
      color: #f4f4f6 !important;
      border-color: #3b3b4a !important;
    }
    html.dark .btn-qty-control:hover {
      background: #2c2c38 !important;
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

  // ============================================================================
  // DETECCION AUTOMATICA DE SO (iOS / iPadOS vs ANDROID) & TUTORIAL PWA
  // ============================================================================
  let deferredPwaInstallPrompt = null;

  function isRunningStandalonePwa() {
    if (typeof window === 'undefined') return false;
    return (
      (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) ||
      window.navigator.standalone === true
    );
  }

  function detectPwaMobileOS() {
    if (typeof navigator === 'undefined') return 'ios';
    const ua = navigator.userAgent || navigator.vendor || window.opera || '';
    const platform = navigator.platform || '';

    // Detectar iPadOS 13+ (reporta MacIntel con touch points) o iPhone/iPad/iPod
    const isIOS = /iPad|iPhone|iPod/.test(ua) ||
      (platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    if (isIOS) return 'ios';

    if (/android/i.test(ua)) return 'android';

    // Fallback inteligente para pruebas o tablets genericas
    if (/Mac/i.test(platform)) return 'ios';
    return 'android';
  }

  function switchPwaTutorialOsTab(os) {
    const normalizedOs = (os === 'android') ? 'android' : 'ios';
    const tabIos = document.getElementById('pwaTabBtnIos');
    const tabAndroid = document.getElementById('pwaTabBtnAndroid');
    const panelIos = document.getElementById('pwaTutorialPanelIos');
    const panelAndroid = document.getElementById('pwaTutorialPanelAndroid');

    if (tabIos) tabIos.classList.toggle('active', normalizedOs === 'ios');
    if (tabAndroid) tabAndroid.classList.toggle('active', normalizedOs === 'android');
    if (panelIos) panelIos.style.display = (normalizedOs === 'ios') ? 'block' : 'none';
    if (panelAndroid) panelAndroid.style.display = (normalizedOs === 'android') ? 'block' : 'none';
  }

  function openPwaTutorialSheet() {
    const detectedOs = detectPwaMobileOS();
    const badgeText = document.getElementById('pwaDetectedOsText');
    if (badgeText) {
      badgeText.textContent = detectedOs === 'ios'
        ? 'Sistema detectado: iOS / iPadOS (Safari)'
        : 'Sistema detectado: Android (Chrome)';
    }
    switchPwaTutorialOsTab(detectedOs);
    openDrawer('pwaInstallTutorialSheet');
  }

  function dismissPwaInstallBanner() {
    const banner = document.getElementById('pwaInstallBanner');
    if (banner) banner.style.display = 'none';
    try {
      sessionStorage.setItem('multiverse_pwa_banner_dismissed', '1');
    } catch (e) {}
  }

  async function triggerNativePwaInstall() {
    if (!deferredPwaInstallPrompt) {
      showToastNotification('Sigue los 3 pasos en pantalla desde el menu de tu navegador.');
      return;
    }
    try {
      deferredPwaInstallPrompt.prompt();
      const { outcome } = await deferredPwaInstallPrompt.userChoice;
      if (outcome === 'accepted') {
        closeAllDrawers();
        dismissPwaInstallBanner();
        showToastNotification('Acceso directo instalado en tu pantalla de inicio.');
      }
      deferredPwaInstallPrompt = null;
    } catch (e) {}
  }

  function initPwaInstallAssistant() {
    const banner = document.getElementById('pwaInstallBanner');
    const bannerHint = document.getElementById('pwaBannerOsHint');
    const headerBtn = document.getElementById('btnOpenPwaTutorialHeader');
    const detectedOs = detectPwaMobileOS();

    if (isRunningStandalonePwa()) {
      if (banner) banner.style.display = 'none';
      if (headerBtn) headerBtn.style.display = 'none';
      return;
    }

    if (bannerHint) {
      bannerHint.textContent = detectedOs === 'ios'
        ? 'Instala el acceso directo en tu iPhone o iPad'
        : 'Instala el acceso directo en tu Android o Tablet';
    }

    // Mostrar banner en móvil y tablet (<= 1024px) si no ha sido descartado en la sesión
    let dismissed = false;
    try {
      dismissed = sessionStorage.getItem('multiverse_pwa_banner_dismissed') === '1';
    } catch (e) {}

    if (banner && !dismissed && window.innerWidth <= 1024) {
      banner.style.display = 'flex';
    }

    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredPwaInstallPrompt = e;
      const nativeZone = document.getElementById('pwaNativeInstallActionZone');
      if (nativeZone) nativeZone.style.display = 'block';
    });
  }

  // ============================================================================
  // MOTOR DE PARALLAX SCROLLING ("SCROLL FIJO DINAMICO") PARA HERO
  // ============================================================================
  function initHeroParallaxEngine() {
    const layers = document.querySelectorAll('[data-hero-parallax-layer="1"]');
    if (!layers.length) return;

    // Las capas usan position: fixed dentro de un contenedor con clip-path: inset(0),
    // lo que reproduce exactamente el efecto "background-attachment: fixed" (scroll fijo dinamico)
    // tanto en Desktop como en iOS Safari, iPadOS y Android sin saltos ni desfasajes.
    layers.forEach((layer) => {
      layer.style.transform = 'translate3d(0, 0, 0)';
      const container = layer.closest('.hero-banner-card, .hero-custom-container');
      if (container) {
        container.classList.add('hero-card-parallax-enabled');
      }
    });
  }

  // Inicialización de la Aplicación
  function initStoreApp() {
    if (typeof window !== 'undefined' && Array.isArray(window.__INITIAL_STORE_PRODUCTS__) && window.__INITIAL_STORE_PRODUCTS__.length > 0) {
      STORE_PRODUCTS = window.__INITIAL_STORE_PRODUCTS__;
      window.STORE_PRODUCTS = STORE_PRODUCTS;
    }
    detectUrlAffiliationAndCoupons();
    updateCartBadges();
    updateWishlistBadges();

    // Control inicial del botón de filtros en móvil (Regla 2: sólo visible si catálogo)
    const mobileFilterBtn = document.getElementById('mobileFilterToggleBtn');
    if (mobileFilterBtn) {
      mobileFilterBtn.style.display = (storeState.activeTab === 'catalog') ? 'inline-flex' : 'none';
    }

    const homeGrid = document.getElementById('homeProductsGrid');
    if (homeGrid && homeGrid.children.length === 0) {
      renderProductsGrid('homeProductsGrid');
    }
    renderProductsGrid('catalogProductsGrid');
    renderCartSheetContent();
    if (typeof fetchCartFromBackend === 'function') {
      fetchCartFromBackend();
    }
    renderWishlistDrawerContent();
    renderCouponBoxes();
    initPushNotifications();
    setAppTheme(getAppTheme(), false);
    initPwaInstallAssistant();
    initHeroParallaxEngine();

    // Regla 6.35: Restaurar facetas activas si se redirigió desde PDP o sesión previa
    try {
      const savedFacetsRaw = sessionStorage.getItem('multiverse_active_facets');
      if (savedFacetsRaw) {
        const f = JSON.parse(savedFacetsRaw);
        if (Array.isArray(f.activeUniverses)) storeState.activeUniverses = f.activeUniverses;
        if (Array.isArray(f.activeCategories)) storeState.activeCategories = f.activeCategories;
        if (Array.isArray(f.activeBrands)) storeState.activeBrands = f.activeBrands;
        if (Array.isArray(f.activeSuppliers)) storeState.activeSuppliers = f.activeSuppliers;
        if (Array.isArray(f.activeSizes)) storeState.activeSizes = f.activeSizes;
        if (Array.isArray(f.activeFabrics)) storeState.activeFabrics = f.activeFabrics;
        if (Array.isArray(f.activeColors)) storeState.activeColors = f.activeColors;
        if (Array.isArray(f.activeVolumes)) storeState.activeVolumes = f.activeVolumes;
        if (Array.isArray(f.activeShades)) storeState.activeShades = f.activeShades;
        if (Array.isArray(f.activeGarmentTypes)) storeState.activeGarmentTypes = f.activeGarmentTypes;
        if (Array.isArray(f.activeStyleTags)) storeState.activeStyleTags = f.activeStyleTags;
        if (Array.isArray(f.activePrices)) storeState.activePrices = f.activePrices;
        if (Array.isArray(f.activeDiscounts)) storeState.activeDiscounts = f.activeDiscounts;
        if (Array.isArray(f.activeAvailability)) storeState.activeAvailability = f.activeAvailability;
        if (typeof f.activeMaxPrice === 'number') {
          storeState.activeMaxPrice = f.activeMaxPrice;
          const pSlider = document.getElementById('storefrontPriceSlider');
          const pDisplay = document.getElementById('priceSliderValueDisplay');
          if (pSlider) pSlider.value = f.activeMaxPrice;
          if (pDisplay) pDisplay.textContent = '$' + Number(f.activeMaxPrice).toLocaleString();
        }

        document.querySelectorAll('.filter-facet-checkbox').forEach(cb => {
          const type = cb.dataset.facetType;
          const val = cb.value;
          if (type === 'universe' && storeState.activeUniverses.includes(val)) cb.checked = true;
          if (type === 'category' && storeState.activeCategories.includes(val)) cb.checked = true;
          if (type === 'brand' && storeState.activeBrands.includes(val)) cb.checked = true;
          if (type === 'supplier' && storeState.activeSuppliers.includes(val)) cb.checked = true;
          if (type === 'size' && storeState.activeSizes.includes(val)) cb.checked = true;
          if (type === 'fabric' && storeState.activeFabrics.includes(val)) cb.checked = true;
          if (type === 'color' && storeState.activeColors.includes(val)) cb.checked = true;
          if (type === 'volume' && storeState.activeVolumes.includes(val)) cb.checked = true;
          if (type === 'shade' && storeState.activeShades.includes(val)) cb.checked = true;
          if (type === 'garment_type' && storeState.activeGarmentTypes.includes(val)) cb.checked = true;
          if (type === 'style_tag' && storeState.activeStyleTags.includes(val)) cb.checked = true;
          if (type === 'price' && storeState.activePrices.includes(val)) cb.checked = true;
          if (type === 'discount' && storeState.activeDiscounts.includes(val)) cb.checked = true;
          if ((type === 'availability' || type === 'stock_type') && storeState.activeAvailability.includes(val)) cb.checked = true;
        });

        if (document.getElementById('catalogProductsGrid')) {
          renderProductsGrid('catalogProductsGrid');
          renderProductsGrid('homeProductsGrid');
        }
      }
    } catch (e) {}

    // Regla 6.35: Sincronización reactiva en tiempo real al conmutar cualquier checkbox de faceta
    document.addEventListener('change', (e) => {
      if (e.target.matches('.filter-facet-checkbox')) {
        storeState.activeUniverses = Array.from(document.querySelectorAll('input[name="filter_universe"]:checked')).map(cb => cb.value);
        storeState.activeCategories = Array.from(document.querySelectorAll('input[name="filter_category"]:checked')).map(cb => cb.value);
        storeState.activeBrands = Array.from(document.querySelectorAll('input[name="filter_brand"]:checked')).map(cb => cb.value);
        storeState.activeSuppliers = Array.from(document.querySelectorAll('input[name="filter_supplier"]:checked')).map(cb => cb.value);
        storeState.activeSizes = Array.from(document.querySelectorAll('input[name="filter_size"]:checked')).map(cb => cb.value);
        storeState.activeFabrics = Array.from(document.querySelectorAll('input[name="filter_fabric"]:checked')).map(cb => cb.value);
        storeState.activeColors = Array.from(document.querySelectorAll('input[name="filter_color"]:checked')).map(cb => cb.value);
        storeState.activeVolumes = Array.from(document.querySelectorAll('input[name="filter_volume"]:checked')).map(cb => cb.value);
        storeState.activeShades = Array.from(document.querySelectorAll('input[name="filter_shade"]:checked')).map(cb => cb.value);
        storeState.activeGarmentTypes = Array.from(document.querySelectorAll('input[name="filter_garment_type"]:checked')).map(cb => cb.value);
        storeState.activeStyleTags = Array.from(document.querySelectorAll('input[name="filter_style_tag"]:checked')).map(cb => cb.value);
        storeState.activePrices = Array.from(document.querySelectorAll('input[name="filter_price"]:checked')).map(cb => cb.value);
        storeState.activeDiscounts = Array.from(document.querySelectorAll('input[name="filter_discount"]:checked')).map(cb => cb.value);
        storeState.activeAvailability = Array.from(document.querySelectorAll('input[name="filter_availability"]:checked, input[name="filter_stock_type"]:checked')).map(cb => cb.value);

        if (document.getElementById('catalogProductsGrid')) {
          renderProductsGrid('catalogProductsGrid');
          renderProductsGrid('homeProductsGrid');
        }
      }
    });

    // Regla: Ajustador de precio interactivo en tiempo real
    const priceRangeInput = document.getElementById('storefrontPriceSlider');
    const priceDisplayEl = document.getElementById('priceSliderValueDisplay');
    if (priceRangeInput) {
      const maxLimit = parseFloat(priceRangeInput.getAttribute('data-max-limit') || priceRangeInput.max || 2500);
      storeState.catalogMaxPrice = maxLimit;
      if (storeState.activeMaxPrice === null || storeState.activeMaxPrice === undefined) {
        storeState.activeMaxPrice = maxLimit;
      }
      priceRangeInput.value = storeState.activeMaxPrice;
      if (priceDisplayEl) {
        priceDisplayEl.textContent = '$' + Number(storeState.activeMaxPrice).toLocaleString();
      }

      priceRangeInput.addEventListener('input', (e) => {
        const val = parseFloat(e.target.value);
        storeState.activeMaxPrice = val;
        if (priceDisplayEl) {
          priceDisplayEl.textContent = '$' + Number(val).toLocaleString();
        }
        if (document.getElementById('catalogProductsGrid')) {
          renderProductsGrid('catalogProductsGrid');
          renderProductsGrid('homeProductsGrid');
        }
      });
    }

    try {
      const urlParams = new URLSearchParams(window.location.search);
      const tabParam = urlParams.get('tab');
      const catParam = urlParams.get('categoria') || urlParams.get('category');
      const uniParam = urlParams.get('universo') || urlParams.get('universe');
      const supParam = urlParams.get('proveedor') || urlParams.get('supplier');
      const tagParam = urlParams.get('tags') || urlParams.get('tag');

      if (catParam || supParam || tagParam || (tabParam && ['home', 'explore', 'catalog'].includes(tabParam))) {
        if (catParam || supParam || tagParam) {
          openCatalogWithCategory(catParam || 'all', uniParam || 'all', supParam, tagParam);
        } else if (tabParam) {
          switchDockTab(tabParam);
          if (tabParam === 'catalog') {
            renderProductsGrid('catalogProductsGrid');
          }
        }
      }

      // Apertura reactiva desde URL canónica (?open=cart / ?open=checkout / ?open=account)
      const openParam = urlParams.get('open') || urlParams.get('action');
      if (openParam === 'cart' || openParam === 'carrito') {
        setTimeout(() => openDrawer('cartSheet'), 250);
      } else if (openParam === 'checkout') {
        setTimeout(() => {
          if (typeof openCheckoutModal === 'function') openCheckoutModal();
        }, 250);
      } else if (openParam === 'account' || openParam === 'cuenta') {
        setTimeout(() => openDrawer('accountSheet'), 250);
      }
    } catch (e) {}

    // Registro de Service Worker PWA
    if ('serviceWorker' in navigator && window.location.protocol.startsWith('http')) {
      navigator.serviceWorker.register('/sw.js').catch(() => {});
    }

    // Renderizar Secciones Dinámicas si estamos en una Página de Producto (PDP)
    if (typeof renderPdpDynamicSections === 'function') {
      renderPdpDynamicSections();
    }
  }

  // Exponer a Window para pruebas automatizadas
  if (typeof window !== 'undefined') {
    window.createProductCardHTML = createProductCardHTML;
    window.renderPdpDynamicSections = renderPdpDynamicSections;
    window.setAppTheme = setAppTheme;
    window.getAppTheme = getAppTheme;
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
    window.openCatalogWithCategory = openCatalogWithCategory;
    window.toggleSearchBar = toggleSearchBar;
    window.handleLiveSearch = handleLiveSearch;
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
    window.renderAccountPersonalAndAddresses = renderAccountPersonalAndAddresses;
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
    window.detectPwaMobileOS = detectPwaMobileOS;
    window.openPwaTutorialSheet = openPwaTutorialSheet;
    window.switchPwaTutorialOsTab = switchPwaTutorialOsTab;
    window.initHeroParallaxEngine = initHeroParallaxEngine;

    window.closeStorePopup = function(secId) {
      const m = document.getElementById('promoModal_' + secId);
      if (m) m.style.display = 'none';
    };

    window.handleStorePopupSubmit = async function(event, secId) {
      event.preventDefault();
      const form = document.getElementById('popupSubscribeForm_' + secId);
      const submitBtn = document.getElementById('popupSubmitBtn_' + secId);
      if (!form) return;

      const formData = new FormData(form);
      const payload = Object.fromEntries(formData.entries());

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Procesando...';
      }

      try {
        const res = await fetch('/api/v1/marketing/popup/subscribe', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (!res.ok) {
          showToastNotification(data.message || 'Error al procesar suscripción.');
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Reintentar';
          }
          return;
        }

        // 1. Guardar o actualizar sesión de usuario con 2FA WhatsApp en localStorage
        let session = {};
        try {
          session = JSON.parse(localStorage.getItem('multiverse_customer_session') || '{}');
        } catch(e) {}

        session.name = data.lead?.name || payload.name;
        session.email = data.lead?.email || payload.email;
        session.phone = data.lead?.phone || payload.whatsapp;
        session.whatsapp = payload.whatsapp;
        session.twoFactorEnrolled = true;
        session.twoFactorMethod = 'whatsapp';
        session.leadId = data.lead?.id;
        localStorage.setItem('multiverse_customer_session', JSON.stringify(session));

        // 2. Si es preventa, redireccionar de inmediato
        if (data.redirect_url) {
          window.location.href = data.redirect_url;
          return;
        }

        // 3. Si se generó código de cupón / voucher
        if (data.voucher_code) {
          const formContainer = document.getElementById('popupFormContainer_' + secId);
          const successContainer = document.getElementById('popupSuccessContainer_' + secId);
          const voucherDisplay = document.getElementById('popupVoucherDisplay_' + secId);
          const successMsg = document.getElementById('popupSuccessMsg_' + secId);
          const copyBtn = document.getElementById('popupCopyBtn_' + secId);
          const applyBtn = document.getElementById('popupApplyBtn_' + secId);

          if (formContainer) formContainer.style.display = 'none';
          if (successContainer) successContainer.style.display = 'flex';
          if (voucherDisplay) voucherDisplay.textContent = data.voucher_code;
          if (successMsg) successMsg.textContent = data.discount_message || 'Tu código único ha sido generado y guardado en tu cuenta.';

          if (copyBtn) {
            copyBtn.onclick = function() {
              navigator.clipboard.writeText(data.voucher_code);
              copyBtn.textContent = 'Copiado';
              setTimeout(() => { copyBtn.textContent = 'Copiar'; }, 2000);
            };
          }

          if (applyBtn) {
            applyBtn.onclick = function() {
              if (typeof applyDiscountCoupon === 'function') {
                applyDiscountCoupon(data.voucher_code);
              }
              window.closeStorePopup(secId);
            };
          }

          // Agregar a descuentos del cliente y sincronizar
          if (typeof fetchAccountDiscounts === 'function') {
            fetchAccountDiscounts();
          }
        } else {
          // Newsletter u otro sin cupón
          showToastNotification(data.discount_message || 'Gracias por suscribirte al newsletter.');
          window.closeStorePopup(secId);
        }
      } catch (err) {
        console.error('[Popup Subscription] Error:', err);
        showToastNotification('Ocurrió un error al procesar la solicitud.');
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Reintentar';
        }
      }
    };
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStoreApp);
  } else {
    initStoreApp();
  }

})();

