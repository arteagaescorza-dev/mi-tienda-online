// MÃ³dulo de Inventario HÃ­brido y CatÃ¡logo para Plataforma RevOps (Ropa Interior B2C)
// Desarrollado con Tailwind CSS + Alpine.js

function hybridInventoryModule() {
    return {
        activeTab: 'inventory', // 'inventory' | 'suppliers' | 'purchases'
        purchasesSubTab: 'reorder', // 'reorder' | 'on_demand' | 'kardex'
        isRefreshing: false,
        lastScrapeTime: 'Sin registros',
        searchFilter: '',
        typeFilter: 'all',
        kpisCollapsed: false,
        publicationFilter: 'all', // 'all' | 'published' | 'hidden'
        supplierFilter: 'all',

        // Control de Visibilidad y Privacidad por Rol Operativo (TASK-RBAC-03)
        getCurrentRole() {
            if (typeof window !== 'undefined' && window.currentRole) {
                return window.currentRole;
            }
            if (typeof ROLES !== 'undefined' && ROLES.admin) {
                return ROLES.admin;
            }
            return null;
        },

        isTabAllowed(tabName) {
            const r = this.getCurrentRole();
            if (!r) return true;
            if (r.id === 'admin' || r.id === 'all_roles') return true;
            if (tabName === 'inventory') return true;
            if (tabName === 'suppliers' || tabName === 'purchases') {
                return r.id === 'purchases' || r.id === 'finance' || (Array.isArray(r.permissions) && r.permissions.includes('inventory.view_costs'));
            }
            if (tabName === 'inbound_tracking') {
                return r.id === 'purchases';
            }
            return true;
        },

        canViewFinancialCosts() {
            const r = this.getCurrentRole();
            if (!r) return true;
            if (r.id === 'admin' || r.id === 'all_roles') return true;
            if (r.id === 'warehouse' || r.id === 'packer' || r.id === 'sales') return false;
            return Boolean(r.isExecutive || (Array.isArray(r.permissions) && r.permissions.includes('inventory.view_costs')));
        },

        canReceiveAtWarehouse() {
            const r = this.getCurrentRole();
            if (!r) return true;
            if (r.id === 'purchases') return false;
            return true;
        },

        canAccessWarehouseTools() {
            const r = this.getCurrentRole();
            if (!r) return true;
            if (r.id === 'purchases') return false;
            return true;
        },

        isPurchasesRole() {
            const r = this.getCurrentRole();
            return Boolean(r && r.id === 'purchases');
        },

        canAccessInboundTracking() {
            const r = this.getCurrentRole();
            if (!r) return true;
            return r.id === 'purchases' || r.id === 'admin' || r.id === 'all_roles';
        },

        // TASK-INT-01: Estado Reactivo de Saltos Profundos (Deep Links) Inter-Hub
        deepLinkActive: false,
        deepLinkBannerText: '',
        highlightedSku: null,

        // TASK-INT-06: TelemetrÃ­a de Carritos SimultÃ¡neos y Reserva Temporal de Stock (TTL 5m)
        stockLocks: [
            {
                id: 'lock_demo_001',
                sessionId: 'sess_live_8a91',
                sku: 'BX-MOD-001',
                name: 'Boxer Trunks Modal Seamless',
                size: 'm',
                quantity: 2,
                expiresAt: Date.now() + (210 * 1000),
                ipAddress: '189.204.12.88',
                userAgent: 'Storefront Web Chrome Mobile',
                createdAt: Date.now() - (90 * 1000)
            },
            {
                id: 'lock_demo_002',
                sessionId: 'sess_live_4b72',
                sku: 'JK-ATH-002',
                name: 'Jockstrap Athletic Carbon Edition',
                size: 'g',
                quantity: 1,
                expiresAt: Date.now() + (140 * 1000),
                ipAddress: '201.141.55.19',
                userAgent: 'Storefront Web Safari Desktop',
                createdAt: Date.now() - (160 * 1000)
            }
        ],
        stockLocksModalOpen: false,

        // TASK-INV-08 / TASK-INV-11: CatÃ¡logo de Telas y Fibras Textiles
        apparelFabrics: [
            { id: 'algodon_pima', name: 'AlgodÃ³n Pima', description: 'Fibra natural extra suave de hilo largo de alta durabilidad', is_active: true },
            { id: 'algodon_peinado', name: 'AlgodÃ³n Peinado', description: '100% algodÃ³n transpirable sin impurezas para uso diario', is_active: true },
            { id: 'modal', name: 'Modal Seamless', description: 'Fibra celulÃ³sica ultra-sedosa con secado rÃ¡pido y mÃ¡xima elasticidad', is_active: true },
            { id: 'elastano', name: 'Elastano / Spandex Fit', description: 'Alta compresiÃ³n anatÃ³mica y memoria elÃ¡stica activa', is_active: true },
            { id: 'malla_mesh', name: 'Malla / Mesh Transpirable', description: 'Tejido tÃ©cnico micro-perforado para ventilaciÃ³n y control tÃ©rmico', is_active: true },
            { id: 'microfibra_velvet', name: 'Microfibra Velvet Touch', description: 'Tacto aterciopelado ultraligero que evita rozaduras', is_active: true },
            { id: 'bambu_eco', name: 'BambÃº Eco Air', description: 'Fibra ecolÃ³gica antibacteriana natural y termorreguladora', is_active: true },
            { id: 'seda_satin', name: 'Seda / SatÃ­n Premium', description: 'Acabado brillante de lujo para lencerÃ­a de autor', is_active: true }
        ],

        // TASK-INV-08 / TASK-INV-11: Paleta de Colores SÃ³lidos y Tornasoles Iridiscentes
        apparelColors: [
            { id: 'negro', name: 'Negro Profundo', hex: '#0f172a', type: 'solid', is_active: true },
            { id: 'blanco', name: 'Blanco Ã“palo', hex: '#f8fafc', type: 'solid', is_active: true },
            { id: 'gris_oxford', name: 'Gris Oxford', hex: '#475569', type: 'solid', is_active: true },
            { id: 'azul_marino', name: 'Azul Marino Navy', hex: '#1e3a8a', type: 'solid', is_active: true },
            { id: 'rojo_borgona', name: 'Rojo BorgoÃ±a', hex: '#881337', type: 'solid', is_active: true },
            { id: 'verde_militar', name: 'Verde Militar Oliva', hex: '#365314', type: 'solid', is_active: true },
            { id: 'nude', name: 'Nude / Canela Piel', hex: '#d97706', type: 'solid', is_active: true },
            { id: 'palo_rosa', name: 'Palo de Rosa Blush', hex: '#f43f5e', type: 'solid', is_active: true },
            { id: 'galaxia_holo', name: 'Galaxia Tornasol Holo', hex: 'linear-gradient(135deg, #6366f1 0%, #ec4899 50%, #f59e0b 100%)', type: 'iridescent', is_active: true },
            { id: 'esmeralda_aurora', name: 'Aurora Tornasol Boreal', hex: 'linear-gradient(135deg, #10b981 0%, #06b6d4 50%, #3b82f6 100%)', type: 'iridescent', is_active: true },
            { id: 'plata_iridiscente', name: 'Plata Iridiscente Prism', hex: 'linear-gradient(135deg, #e2e8f0 0%, #a855f7 50%, #06b6d4 100%)', type: 'iridescent', is_active: true },
            { id: 'sunset_oro', name: 'Sunset Oro Tornasol', hex: 'linear-gradient(135deg, #f59e0b 0%, #ef4444 50%, #ec4899 100%)', type: 'iridescent', is_active: true }
        ],

        // TASK-INV-08: Staging de CuradurÃ­a Previa de Scraping (ValidaciÃ³n humana antes de publicar)
        scrapedPendingItems: [
            {
                id: 'SCRP-PEND-001',
                suggestedSku: 'TOR-RAW-019',
                name: 'Torzo Holographic Prism Jockstrap',
                brand: 'Torzo B2B',
                suggestedCategory: 'Jocks',
                scrapedDescription: 'Prenda suspensorio atlÃ©tico con elÃ¡stico reflectante y tejido de malla tÃ©cnica transpirable con destellos tornasol. Ideal para performance y diseÃ±o contemporÃ¡neo.',
                originUrl: 'https://torzo.example.com/item/holo-prism-jock',
                cost: 145.00,
                suggestedPvp: 390.00,
                detectedColorName: 'Plata Iridiscente Prism',
                detectedColorHex: 'linear-gradient(135deg, #e2e8f0 0%, #a855f7 50%, #06b6d4 100%)',
                colorType: 'iridescent',
                suggestedFabric: 'Malla / Mesh Transpirable',
                suggestedDesign: 'con_diseno',
                suggestedSection: 'disenador',
                supplierStock: 65,
                sizes: { ch: 10, m: 25, g: 20, xl: 10 }
            },
            {
                id: 'SCRP-PEND-002',
                suggestedSku: 'TOR-RAW-020',
                name: 'Torzo Bamboo Comfort Trunks Seamless',
                brand: 'Torzo B2B',
                suggestedCategory: 'Boxers',
                scrapedDescription: 'Boxer corto liso elaborado en viscosa de bambÃº ecolÃ³gico, costuras planas anti-rozaduras y pretina elÃ¡stica afelpada para mÃ¡xima comodidad.',
                originUrl: 'https://torzo.example.com/item/bamboo-trunks',
                cost: 120.00,
                suggestedPvp: 340.00,
                detectedColorName: 'Verde Militar Oliva',
                detectedColorHex: '#365314',
                colorType: 'solid',
                suggestedFabric: 'BambÃº Eco Air',
                suggestedDesign: 'sin_diseno',
                suggestedSection: 'basicos',
                supplierStock: 90,
                sizes: { ch: 20, m: 35, g: 25, xl: 10 }
            }
        ],

        // TASK-INV-08 / TASK-INV-11 / TASK-INV-17: Modal de Ajustes y ConfiguraciÃ³n del MÃ³dulo
        moduleSettingsModalOpen: false,
        activeSettingsTab: 'fabrics', // 'fabrics' | 'colors' | 'shades' | 'slotting' | 'rop' | 'scraping' | 'sections' | 'bundles'
        bundleEditor: {
            isOpen: false,
            isEditing: false,
            bundleId: null,
            filterStatus: 'active', // 'active' | 'archived' | 'all'
            filterType: 'all', // 'all' | 'subscription' | 'promo'
            searchQuery: '',
            form: {
                sku: '',
                name: '',
                type: 'subscription',
                tierLevel: 'nivel_1_refill',
                tierName: 'Nivel 1: Refill Esencial',
                subscriptionPrice: 499.00,
                regularPrice: 600.00,
                discountPct: 15,
                isActive: true,
                badgeColor: 'indigo',
                description: '',
                components: []
            },
            newComponent: {
                sku: '',
                size: 'm',
                qty: 1
            }
        },
        newFabricForm: { name: '', description: '' },
        fabricEditState: { isEditing: false, editingId: null, form: { name: '', description: '', is_active: true } },
        newColorForm: { name: '', hex: '#6366f1', type: 'solid', gradientColor1: '#6366f1', gradientColor2: '#ec4899', gradientColor3: '#f59e0b' },
        colorEditState: { isEditing: false, editingId: null, form: { name: '', hex: '#6366f1', type: 'solid', gradientColor1: '#6366f1', gradientColor2: '#ec4899', gradientColor3: '#f59e0b', is_active: true } },
        newSlotForm: { aisle: 'A', rack: '01', bin: 'A', zone: 'Ropa Interior', name: '', capacity: 100, status: 'active', is_active: true },
        slotEditState: { isEditing: false, editingCode: null, form: { aisle: 'A', rack: '01', bin: 'A', zone: 'Ropa Interior', name: '', capacity: 100, status: 'active', is_active: true } },
        ropSettings: { leadTimeLocal: 3, leadTimeDropship: 5, safetyStockDays: 7, defaultDailySales: 2 },

        // TASK-INV-14: GestiÃ³n Configurable de Curvas de Tallas, VolÃºmenes y CategorÃ­as
        sizeCurves: [],
        selectedCurveId: null,
        newCurveForm: { name: '', category_scope: 'textil', description: '', is_default: false, is_active: true },
        curveEditState: { isEditing: false, editingId: null, form: { name: '', category_scope: 'textil', description: '', is_default: false, is_active: true } },
        newSizeForm: { code: '', label: '', sort_order: 1, waist_min_cm: null, waist_max_cm: null, is_active: true },
        sizeEditState: { isEditing: false, editingId: null, form: { code: '', label: '', sort_order: 1, waist_min_cm: null, waist_max_cm: null, is_active: true } },

        fragranceVolumes: [],
        newVolumeForm: { value: 100, unit: 'ml', label: '', description: '', sort_order: 1, is_active: true },
        volumeEditState: { isEditing: false, editingId: null, form: { value: 100, unit: 'ml', label: '', description: '', sort_order: 1, is_active: true } },

        categoriesTree: [],
        newCategoryForm: { name: '', description: '', parent_id: '', is_active: true },
        categoryEditState: { isEditing: false, editingId: null, form: { name: '', description: '', parent_id: '', is_active: true } },

        // TASK-INV-13: Gestor Integral de Proveedores, SLA y Control de Estados
        providers: (typeof window !== 'undefined' && Array.isArray(window.serverSuppliers) && window.serverSuppliers.length > 0)
            ? window.serverSuppliers
            : [
            {
                id: 'prov_torzo',
                vendorCode: 'VND-TORZO-001',
                name: 'Torzo Official B2B',
                tradeName: 'Torzo B2B',
                type: 'scraper',
                typeLabel: 'Fabricante Deportivo & Ropa Interior',
                city: 'CDMX',
                contactName: 'Carlos Mendoza',
                phone: '+52 55 4182 9901',
                email: 'b2b@torzo.com',
                extractionMethod: 'Headless Scraper (Puppeteer)',
                frequency: 'Automatico (Cada 6 hrs)',
                lastScrapeTime: 'Hace 18 min',
                syncedItemsCount: 42,
                status: 'connected',
                leadTimeDays: 5,
                paymentTerms: 'contado',
                slaOnTimePct: 96.8,
                slaFillRatePct: 98.2,
                totalOrders: 28,
                completedOrders: 27,
                avgDelayDays: 0.4,
                isSyncing: false,
                notes: 'Conexion automatizada via cluster Torzo Bot en Node.js/Puppeteer.'
            },
            {
                id: 'prov_modaintima',
                vendorCode: 'VND-MODA-002',
                name: 'ModaIntima Textil MX',
                tradeName: 'ModaIntima MX',
                type: 'workshop',
                typeLabel: 'Fabricante Nacional de Algodon y Boxers',
                city: 'Puebla',
                contactName: 'Lucia Morales',
                phone: '+52 222 389 1102',
                email: 'ventas@modaintima.mx',
                extractionMethod: 'REST API / SAP ERP (Webhook)',
                frequency: 'Diaria (06:00 AM)',
                lastScrapeTime: 'Hace 42 min',
                syncedItemsCount: 68,
                status: 'connected',
                leadTimeDays: 3,
                paymentTerms: 'credito_30',
                slaOnTimePct: 94.5,
                slaFillRatePct: 97.0,
                totalOrders: 35,
                completedOrders: 33,
                avgDelayDays: 0.8,
                isSyncing: false,
                notes: 'Taller principal de maquila de algodon pima y modal.'
            },
            {
                id: 'prov_silk_cotton',
                vendorCode: 'VND-SILK-003',
                name: 'Silk & Cotton Iberia SL',
                tradeName: 'Silk & Cotton Iberia',
                type: 'distributor',
                typeLabel: 'Hilados Premium Modal Organico',
                city: 'Barcelona',
                contactName: 'Jordi Puig',
                phone: '+34 932 108 440',
                email: 'pedidos@silkcotton.es',
                extractionMethod: 'SFTP / CSV Automatizado',
                frequency: 'Cada 12 hrs',
                lastScrapeTime: 'Hace 3 hrs',
                syncedItemsCount: 24,
                status: 'connected',
                leadTimeDays: 14,
                paymentTerms: 'credito_60',
                slaOnTimePct: 91.2,
                slaFillRatePct: 95.8,
                totalOrders: 12,
                completedOrders: 11,
                avgDelayDays: 1.5,
                isSyncing: false,
                notes: 'Insumos importados de Europa bajo embarque maritimo y aereo.'
            },
            {
                id: 'prov_lingerie_drop',
                vendorCode: 'VND-LING-004',
                name: 'Lingerie Drop Global',
                tradeName: 'Lingerie Drop Miami',
                type: 'scraper',
                typeLabel: 'Dropship Especializado Jocks & Bikinis',
                city: 'Miami',
                contactName: 'Steven Vance',
                phone: '+1 305 555 0199',
                email: 'ops@lingeriedrop.us',
                extractionMethod: 'Shopify App Private Webhook',
                frequency: 'Tiempo Real (Webhooks)',
                lastScrapeTime: 'Hace 5 min',
                syncedItemsCount: 95,
                status: 'connected',
                leadTimeDays: 7,
                paymentTerms: 'contado',
                slaOnTimePct: 98.4,
                slaFillRatePct: 99.1,
                totalOrders: 40,
                completedOrders: 40,
                avgDelayDays: 0.1,
                isSyncing: false,
                notes: 'Dropship cross-border directo con fulfillment en Florida.'
            },
            {
                id: 'prov_seamless_tech',
                vendorCode: 'VND-SEAM-005',
                name: 'Seamless Tech Apparel',
                tradeName: 'Seamless Tech',
                type: 'workshop',
                typeLabel: 'Prendas Sin Costuras y Corte Laser',
                city: 'Medellin',
                contactName: 'Valeria Restrepo',
                phone: '+57 4 444 8921',
                email: 'comercial@seamlesstech.co',
                extractionMethod: 'GraphQL API Endpoint',
                frequency: 'Cada 8 hrs',
                lastScrapeTime: 'Hace 1 hr',
                syncedItemsCount: 31,
                status: 'connected',
                leadTimeDays: 8,
                paymentTerms: 'credito_15',
                slaOnTimePct: 93.0,
                slaFillRatePct: 96.5,
                totalOrders: 18,
                completedOrders: 17,
                avgDelayDays: 1.1,
                isSyncing: false,
                notes: 'Maquila especializada en cortes termosellados sin rozaduras.'
            },
            {
                id: 'prov_aura_wholesale',
                vendorCode: 'VND-AURA-006',
                name: 'Aura Underwear Wholesale',
                tradeName: 'Aura Wholesale',
                type: 'distributor',
                typeLabel: 'Coleccion Diseno y Moda Intima',
                city: 'Guadalajara',
                contactName: 'Fernando Orozco',
                phone: '+52 33 3612 7780',
                email: 'distribucion@auraunderwear.com',
                extractionMethod: 'Headless Scraper (Cluster)',
                frequency: 'Cada 24 hrs',
                lastScrapeTime: 'Ayer 22:15',
                syncedItemsCount: 16,
                status: 'maintenance',
                leadTimeDays: 6,
                paymentTerms: 'credito_30',
                slaOnTimePct: 86.4,
                slaFillRatePct: 91.0,
                totalOrders: 15,
                completedOrders: 13,
                avgDelayDays: 2.8,
                isSyncing: false,
                notes: 'Cluster en mantenimiento por actualizacion de portal mayorista.'
            },
            {
                id: 'prov_intimates_co',
                vendorCode: 'VND-INTI-007',
                name: 'Intimates Co. Logistics',
                tradeName: 'Intimates Co.',
                type: 'commercial',
                typeLabel: 'Distribuidor Multimarca Tradicional',
                city: 'Monterrey',
                contactName: 'Guillermo Garza',
                phone: '+52 81 8345 6620',
                email: 'cuentas@intimatesco.mx',
                extractionMethod: 'REST API Webhook',
                frequency: 'Manual / On-demand',
                lastScrapeTime: 'Hace 4 dias',
                syncedItemsCount: 0,
                status: 'paused',
                leadTimeDays: 5,
                paymentTerms: 'contado',
                slaOnTimePct: 88.0,
                slaFillRatePct: 89.5,
                totalOrders: 8,
                completedOrders: 7,
                avgDelayDays: 3.0,
                isSyncing: false,
                notes: 'Canal pausado temporalmente por revision de acuerdos comerciales.'
            }
        ],
        providerSearch: '',
        providerStatusFilter: 'all',
        providerTypeFilter: 'all',
        providerModalOpen: false,
        providerKpiModalOpen: false,
        latencyKpiModalOpen: false,
        discrepancyKpiModalOpen: false,
        slaKpiModalOpen: false,
        discrepancyCategoryFilter: 'all',
        selectedProvider: null,
        isCreatingProvider: false,
        isEditingProvider: false,
        providerForm: {
            name: '',
            tradeName: '',
            type: 'workshop',
            typeLabel: 'Taller de Confeccion Textil',
            city: 'CDMX',
            contactName: '',
            phone: '',
            email: '',
            extractionMethod: 'Manual / Facturacion Directa',
            syncFrequency: 'Manual / On-demand',
            leadTimeDays: 5,
            paymentTerms: 'credito_30',
            status: 'connected',
            notes: ''
        },

        // TASK-INV-08: Modal de CuradurÃ­a de Scraping
        scrapingCurationModalOpen: false,
        curatingItem: null,

        // Drawer Properties Extendidas
        drawerFabricType: 'Modal Seamless',
        drawerColorName: 'Negro Profundo',
        drawerColorHex: '#0f172a',
        drawerColorType: 'solid',
        drawerDesignType: 'sin_diseno',
        drawerWebsiteSection: 'basicos',
        drawerIsPublished: true,
        drawerDescription: '',
        drawerBrand: 'ModaIntima Textil MX',
        drawerDiscountPct: 0,
        drawerRegularPrice: 0,

        // Regla 6: PaginaciÃ³n en tablas mediante "Cargar mÃ¡s"
        visibleCount: 8,
        pageSize: 8,
        isLoadingMore: false,
        kardexVisibleCount: 6,
        kardexPageSize: 6,
        kardexIsLoadingMore: false,
        _lastFilterQuery: '',

        // Regla 7: Filtros especÃ­ficos por columna
        colFilters: {
            sku: '',
            product: '',
            stockLocal: 'all', // 'all' | 'under_rop' | 'in_stock' | 'zero'
            stockProv: 'all',  // 'all' | 'available' | 'none'
            minPvp: '',
            maxCost: '',
            margin: 'all',     // 'all' | 'high' (>=60) | 'mid' (40-59) | 'low' (<40)
            location: '',
            status: 'all'      // 'all' | 'En Stock' | 'Bajo Stock' | 'Agotado'
        },

        toolsDropdownOpen: false,
        drawerOpen: false,
        topTenDrawerOpen: false,
        selectedProduct: null,
        drawerSizes: { ch: 0, m: 0, g: 0, xl: 0, '2xl': 0, '3xl': 0 },
        drawerShades: {},
        drawerLocation: '',
        drawerPvp: 0,
        drawerCost: 0,
        drawerCpp: 0,
        purchaseQty: 60,
        purchasePriority: 'alta',
        purchaseSupplier: 'Torzo B2B',
        purchaseSizes: { ch: 10, m: 25, g: 18, xl: 7, '2xl': 2, '3xl': 0 },
        newProductDrawerOpen: false,
        isDraggingImages: false,
        confirmPurchaseModalOpen: false,
        purchaseAddedSuccess: false,
        hasAddedPurchaseThisSession: false,
        // TASK-INV-01 / TASK-INV-11: CatÃ¡logo y Estado de Slotting FÃ­sico en Bodega
        warehouseSlots: [
            { code: 'A-01-A', aisle: 'A', rack: '01', bin: 'A', zone: 'Ropa Interior', name: 'Pasillo A (Ropa Interior) - Rack 01 Gaveta Superior', capacity: 100, status: 'active', is_active: true },
            { code: 'A-01-B', aisle: 'A', rack: '01', bin: 'B', zone: 'Ropa Interior', name: 'Pasillo A (Ropa Interior) - Rack 01 Gaveta Media', capacity: 120, status: 'active', is_active: true },
            { code: 'A-02-A', aisle: 'A', rack: '02', bin: 'A', zone: 'Ropa Interior', name: 'Pasillo A (Ropa Interior) - Rack 02 Gaveta Superior', capacity: 100, status: 'active', is_active: true },
            { code: 'A-02-B', aisle: 'A', rack: '02', bin: 'B', zone: 'Ropa Interior', name: 'Pasillo A (Ropa Interior) - Rack 02 Gaveta Media', capacity: 150, status: 'active', is_active: true },
            { code: 'A-03-A', aisle: 'A', rack: '03', bin: 'A', zone: 'Ropa Interior', name: 'Pasillo A (Ropa Interior) - Rack 03 Gaveta Superior', capacity: 100, status: 'active', is_active: true },
            { code: 'B-01-A', aisle: 'B', rack: '01', bin: 'A', zone: 'AtlÃ©tico & Jocks', name: 'Pasillo B (AtlÃ©tico & Jocks) - Rack 01 Gaveta Superior', capacity: 80, status: 'active', is_active: true },
            { code: 'B-02-B', aisle: 'B', rack: '02', bin: 'B', zone: 'AtlÃ©tico & Jocks', name: 'Pasillo B (AtlÃ©tico & Jocks) - Rack 02 Gaveta Media', capacity: 90, status: 'active', is_active: true },
            { code: 'B-03-A', aisle: 'B', rack: '03', bin: 'A', zone: 'AtlÃ©tico & Jocks', name: 'Pasillo B (AtlÃ©tico & Jocks) - Rack 03 Gaveta Superior', capacity: 80, status: 'active', is_active: true },
            { code: 'B-04-C', aisle: 'B', rack: '04', bin: 'C', zone: 'AtlÃ©tico & Jocks', name: 'Pasillo B (AtlÃ©tico & Jocks) - Rack 04 Gaveta Inferior', capacity: 110, status: 'active', is_active: true },
            { code: 'C-01-A', aisle: 'C', rack: '01', bin: 'A', zone: 'Skincare & Cuidado', name: 'Pasillo C (Skincare & Cuidado) - Rack 01 Gaveta Climatizada', capacity: 60, status: 'active', is_active: true },
            { code: 'C-02-B', aisle: 'C', rack: '02', bin: 'B', zone: 'Skincare & Cuidado', name: 'Pasillo C (Skincare & Cuidado) - Rack 02 Gaveta Media', capacity: 75, status: 'active', is_active: true },
            { code: 'C-03-A', aisle: 'C', rack: '03', bin: 'A', zone: 'Skincare & Cuidado', name: 'Pasillo C (Skincare & Cuidado) - Rack 03 Gaveta Superior', capacity: 50, status: 'active', is_active: true }
        ],
        slotReassignModal: {
            isOpen: false,
            sku: '',
            productName: '',
            currentLocation: '',
            selectedAisle: 'A',
            selectedRack: '01',
            selectedBin: 'A',
            notes: ''
        },
        // TASK-INV-03: Recetas BOM y Despacho de Combos Virtuales
        bundleRecipesModal: {
            isOpen: false,
            selectedBundleId: 'SUB-BOX-MARTE-LVL2',
            dispatchQty: 5,
            dispatchReference: 'SUB-BATCH-2026-09A',
            dispatchNotes: 'Despacho masivo ciclo quincenal Marte Nivel 2',
            activeFilter: 'all',
            isLiquidating: false
        },

        // TASK-INT-11: Disparo Automatico de OCs a Extranet B2B por Deficit de Ensamble BOM
        bomDeficitState: {
            isOpen: false,
            selectedBundleId: 'SUB-BOX-MARTE-LVL2',
            targetQty: 200,
            batchReference: 'SUB-BATCH-2026-09A',
            auditNotes: 'Reposicion preventiva de insumos para ensamble ciclo mensual',
            isProcessing: false,
            lastGeneratedOrders: [],
            lastProcurementCode: null,
            result: null
        },

        // Estado AuditorÃ­a CÃ­clica a Ciegas (TASK-INV-07: 3 SKUs Diarios)
        cycleCountHistory: [],
        cycleCountModal: {
            isOpen: false,
            step: 'count', // 'count' | 'reconcile' | 'summary'
            activeSkuIndex: 0,
            isCompletedToday: false,
            sampleSkus: [],
            sampleItems: [],
            counts: {}, // { [sku]: { sizes: {}, shades: {}, volumes: {}, unitQty: 0, isCounted: false } }
            discrepancyReasons: {}, // { [sku]: 'Merma operativa' }
            reconciliationReport: null
        },
        // TASK-INV-09: Gestor Configurable de Tonos de CosmÃ©tica con Swatches y Subtonos
        cosmeticShades: [
            { id: 1, code: '01', name: 'Porcelana', hex_color: '#F7D8BE', undertone: 'cool', sort_order: 1, is_active: true },
            { id: 2, code: '02', name: 'Neutro Claro', hex_color: '#EEC29A', undertone: 'neutral', sort_order: 2, is_active: true },
            { id: 3, code: '03', name: 'Beige CÃ¡lido', hex_color: '#D9A577', undertone: 'warm', sort_order: 3, is_active: true },
            { id: 4, code: '04', name: 'Canela Dorado', hex_color: '#BD8052', undertone: 'warm', sort_order: 4, is_active: true },
            { id: 5, code: '05', name: 'Miel CÃ¡lido', hex_color: '#9A5B32', undertone: 'warm', sort_order: 5, is_active: true },
            { id: 6, code: '06', name: 'Ã‰bano Profundo', hex_color: '#6D3C1A', undertone: 'cool', sort_order: 6, is_active: true },
            { id: 7, code: '07', name: 'Caramelo Neutro', hex_color: '#C68B59', undertone: 'neutral', sort_order: 7, is_active: true },
            { id: 8, code: '08', name: 'CafÃ© Intenso', hex_color: '#542D13', undertone: 'warm', sort_order: 8, is_active: true }
        ],
        cosmeticShadesModal: {
            isOpen: false,
            isEditing: false,
            editingId: null,
            form: {
                code: '',
                name: '',
                hex_color: '#E8BEA2',
                undertone: 'neutral',
                is_active: true
            }
        },
        productBundles: [
            {
                id: 'SUB-BOX-MARTE-LVL1',
                sku: 'SUB-BOX-MARTE-LVL1',
                name: 'Caja Mensual Marte Nivel 1: Refill Esencial',
                type: 'subscription',
                tierLevel: 'nivel_1_refill',
                tierName: 'Nivel 1: Refill Esencial',
                subscriptionPrice: 499.00,
                regularPrice: 600.00,
                discountPct: 17,
                isActive: true,
                badgeColor: 'blue',
                description: 'Suministro basico bimensual de higiene y reposicion de prenda interior de uso diario.',
                components: [
                    { sku: 'BX-MOD-001', name: 'Boxer Trunks Modal Seamless', size: 'm', qty: 1, location: 'A-01-B' },
                    { sku: 'JB-INT-016', name: 'Jabon Intimo pH Balance Masculino 250ml', size: 'unit', qty: 1, location: 'C-01-A' }
                ]
            },
            {
                id: 'SUB-BOX-MARTE-LVL2',
                sku: 'SUB-BOX-MARTE-LVL2',
                name: 'Caja Mensual Marte Nivel 2: Estilo & Cuidado',
                type: 'subscription',
                tierLevel: 'nivel_2_estilo',
                tierName: 'Nivel 2: Estilo & Cuidado',
                subscriptionPrice: 899.00,
                regularPrice: 1120.00,
                discountPct: 20,
                isActive: true,
                badgeColor: 'indigo',
                description: 'Seleccion premium bimensual: 2 prendas de alta rotacion combinadas con grooming y fragancia masculina.',
                components: [
                    { sku: 'BX-MOD-001', name: 'Boxer Trunks Modal Seamless', size: 'm', qty: 1, location: 'A-01-B' },
                    { sku: 'JK-ATH-002', name: 'Jockstrap Athletic Carbon Edition', size: 'm', qty: 1, location: 'B-03-A' },
                    { sku: 'PF-MAR-017', name: 'Perfume Solido Marte Noir 10ml', size: 'v10', qty: 1, location: 'C-02-B' }
                ]
            },
            {
                id: 'SUB-BOX-VIP-LVL3',
                sku: 'SUB-BOX-VIP-LVL3',
                name: 'Caja Marte VIP Nivel 3: Experiencia Total',
                type: 'subscription',
                tierLevel: 'nivel_3_vip',
                tierName: 'Nivel 3: VIP Total',
                subscriptionPrice: 1399.00,
                regularPrice: 1790.00,
                discountPct: 22,
                isActive: true,
                badgeColor: 'purple',
                description: 'Lujo sensorial y moda intima integral: lenceria de diseno, fragancia exclusiva y kit de preservacion textil.',
                components: [
                    { sku: 'BX-BAM-011', name: 'Boxer Brief Bambu Eco Air Flow', size: 'm', qty: 1, location: 'A-03-A' },
                    { sku: 'TG-SLK-004', name: 'Tanga Microfibra Velvet Touch', size: 'm', qty: 1, location: 'C-02-B' },
                    { sku: 'PF-MAR-017', name: 'Perfume Solido Marte Noir 10ml', size: 'v10', qty: 1, location: 'C-02-B' },
                    { sku: 'BL-LAV-018', name: 'Bolsa de Malla para Lavado Delicado', size: 'unit', qty: 1, location: 'A-01-A' }
                ]
            },
            {
                id: 'BND-TRIO-PROMO',
                sku: 'BND-TRIO-PROMO',
                name: 'Pack Trio Boxer Modal Seamless (Promo)',
                type: 'promo',
                tierLevel: 'promo_pack',
                tierName: 'Promo Bundle 3-Pack',
                subscriptionPrice: 999.00,
                regularPrice: 1260.00,
                discountPct: 21,
                isActive: true,
                badgeColor: 'emerald',
                description: 'Combo promocional directo de 3 piezas de boxer modal seamless con ahorro por volumen.',
                components: [
                    { sku: 'BX-MOD-001', name: 'Boxer Trunks Modal Seamless', size: 'm', qty: 3, location: 'A-01-B' }
                ]
            }
        ],
        newProduct: {
            sku: 'BX-ECO-011',
            skuLocked: true,
            isGeneratingSku: false,
            name: '',
            category: 'Boxers',
            subcategory: '',
            type: 'propio',
            aisle: 'A',
            rack: '02',
            bin: 'B',
            location: 'A-02-B',
            // TASK-INV-02 / TASK-INV-14: Matriz Multidimensional de Variantes y Curvas de Tallas
            variantDimension: 'size',
            selectedSizeCurveId: null,
            fabricType: 'Modal Seamless',
            colorName: 'Negro Profundo',
            colorHex: '#0f172a',
            colorType: 'solid',
            designType: 'sin_diseno',
            websiteSection: 'basicos',
            isPublished: true,
            description: '',
            bulletPoints: [],
            seoTags: ['boxer modal', 'ropa interior transpirable', 'sin costuras'],
            newTagInput: '',
            brand: 'ModaIntima Textil MX',
            discountPct: 0,
            regularPrice: 399.00,
            pvp: 399.00,
            cost: 135.00,
            weightedAverageCost: 135.00,
            sizes: { ch: 15, m: 35, g: 30, xl: 10, '2xl': 5, '3xl': 0 },
            shades: { t01: 10, t02: 25, t03: 20, t04: 15, t05: 5, t06: 0 },
            volumes: { v10: 15, v50: 30, v100: 10 },
            unitStock: 50,
            supplierStock: 40,
            provider: 'ModaIntima Textil MX',
            providerUrl: '',
            images: [],
            // TASK-INV-15: Auto-SKU, Deteccion de Duplicados & Copiloto IA
            duplicateRisk: 'none',
            duplicateMatches: [],
            duplicateScore: 0,
            duplicateChecked: false,
            isCheckingDuplicate: false,
            duplicateAlertModalOpen: false,
            duplicateSelectedMatch: null,
            forceCreateDespiteDuplicate: false,
            isGeneratingAi: false
        },

        toast: {
            show: false,
            message: '',
            type: 'success',
            timeout: null
        },

        products: (typeof window !== 'undefined' && Array.isArray(window.serverInventoryProducts) && window.serverInventoryProducts.length > 0)
            ? window.serverInventoryProducts
            : [
    {
        "sku": "BX-MOD-001",
        "weightedAverageCost": 138.50,
        "name": "Boxer Trunks Modal Seamless",
        "category": "Boxers",
        "type": "propio",
        "localStock": 284,
        "committedStock": 22,
        "committedSizes": { "ch": 3, "m": 9, "g": 8, "xl": 2 },
        "supplierStock": null,
        "location": "Rack A-01 / Nivel 2",
        "status": "En Stock",
        "pvp": 420,
        "cost": 145,
        "sizes": {
            "ch": 40,
            "m": 110,
            "g": 95,
            "xl": 39
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "JK-ATH-002",
        "weightedAverageCost": 110.00,
        "name": "Jockstrap Athletic Carbon Edition",
        "category": "Jocks",
        "type": "propio",
        "localStock": 198,
        "committedStock": 15,
        "committedSizes": { "ch": 2, "m": 6, "g": 5, "xl": 2 },
        "supplierStock": null,
        "location": "Rack B-03 / CajÃ³n 1",
        "status": "En Stock",
        "pvp": 380,
        "cost": 115,
        "sizes": {
            "ch": 30,
            "m": 85,
            "g": 60,
            "xl": 23
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "TOR-DRP-003",
        "weightedAverageCost": 202.00,
        "name": "Torzo Mesh Breathable Trunk",
        "category": "Boxers",
        "type": "dropship",
        "localStock": 0,
        "committedStock": 0,
        "committedSizes": { "ch": 0, "m": 0, "g": 0, "xl": 0 },
        "supplierStock": 48,
        "location": "AlmacÃ©n Proveedor (Torzo B2B)",
        "status": "Sincronizado",
        "providerUrl": "https://torzo.example.com/item/mesh-trunk",
        "isScraping": false,
        "pvp": 490,
        "cost": 210,
        "sizes": {
            "ch": 10,
            "m": 18,
            "g": 15,
            "xl": 5
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "TG-SLK-004",
        "weightedAverageCost": 74.50,
        "name": "Tanga Microfibra Velvet Touch",
        "category": "Tangas",
        "type": "propio",
        "localStock": 154,
        "committedStock": 12,
        "committedSizes": { "ch": 2, "m": 5, "g": 4, "xl": 1 },
        "supplierStock": null,
        "location": "Rack C-02 / Nivel 1",
        "status": "En Stock",
        "pvp": 290,
        "cost": 78,
        "sizes": {
            "ch": 35,
            "m": 60,
            "g": 45,
            "xl": 14
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "BK-FIT-005",
        "weightedAverageCost": 90.00,
        "name": "Bikini Sport Performance Brief",
        "category": "Bikinis",
        "type": "propio",
        "localStock": 122,
        "committedStock": 9,
        "committedSizes": { "ch": 1, "m": 4, "g": 3, "xl": 1 },
        "supplierStock": null,
        "location": "Rack B-01 / Nivel 3",
        "status": "En Stock",
        "pvp": 320,
        "cost": 95,
        "sizes": {
            "ch": 22,
            "m": 50,
            "g": 35,
            "xl": 15
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "TOR-DRP-006",
        "weightedAverageCost": 188.00,
        "name": "Torzo Neon Glow Athletic Jock",
        "category": "Jocks",
        "type": "dropship",
        "localStock": 0,
        "committedStock": 0,
        "committedSizes": { "ch": 0, "m": 0, "g": 0, "xl": 0 },
        "supplierStock": 14,
        "location": "AlmacÃ©n Proveedor (Torzo B2B)",
        "status": "Bajo Stock",
        "providerUrl": "https://torzo.example.com/item/neon-jock",
        "isScraping": false,
        "pvp": 450,
        "cost": 195,
        "sizes": {
            "ch": 2,
            "m": 6,
            "g": 4,
            "xl": 2
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "BX-COT-007",
        "weightedAverageCost": 114.00,
        "name": "Boxer AlgodÃ³n Pima OrgÃ¡nico",
        "category": "Boxers",
        "type": "propio",
        "localStock": 480,
        "committedStock": 38,
        "committedSizes": { "ch": 6, "m": 15, "g": 13, "xl": 4 },
        "supplierStock": null,
        "location": "Rack A-02 / Pallet 4",
        "status": "En Stock",
        "pvp": 390,
        "cost": 120,
        "sizes": {
            "ch": 100,
            "m": 180,
            "g": 140,
            "xl": 60
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "TG-EXT-008",
        "weightedAverageCost": 59.00,
        "name": "Tanga Extreme Minimalist Straps",
        "category": "Tangas",
        "type": "propio",
        "localStock": 218,
        "committedStock": 17,
        "committedSizes": { "ch": 3, "m": 7, "g": 6, "xl": 1 },
        "supplierStock": null,
        "location": "Rack C-01 / Nivel 2",
        "status": "En Stock",
        "pvp": 260,
        "cost": 62,
        "sizes": {
            "ch": 50,
            "m": 85,
            "g": 60,
            "xl": 23
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "TOR-DRP-009",
        "weightedAverageCost": 168.00,
        "name": "Torzo Signature Satin Bikini",
        "category": "Bikinis",
        "type": "dropship",
        "localStock": 0,
        "committedStock": 0,
        "committedSizes": { "ch": 0, "m": 0, "g": 0, "xl": 0 },
        "supplierStock": 0,
        "location": "AlmacÃ©n Proveedor (Torzo B2B)",
        "status": "Agotado",
        "providerUrl": "https://torzo.example.com/item/satin-bikini",
        "isScraping": false,
        "pvp": 410,
        "cost": 175,
        "sizes": {
            "ch": 0,
            "m": 0,
            "g": 0,
            "xl": 0
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "JK-PRO-010",
        "weightedAverageCost": 102.50,
        "name": "Jockstrap Pro-Gym Wide Band",
        "category": "Jocks",
        "type": "propio",
        "localStock": 270,
        "committedStock": 21,
        "committedSizes": { "ch": 3, "m": 8, "g": 7, "xl": 3 },
        "supplierStock": null,
        "location": "Rack B-02 / Nivel 2",
        "status": "En Stock",
        "pvp": 360,
        "cost": 108,
        "sizes": {
            "ch": 45,
            "m": 110,
            "g": 85,
            "xl": 30
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "BX-BAM-011",
        "weightedAverageCost": 132.00,
        "name": "Boxer Brief BambÃº Eco Air Flow",
        "category": "Boxers",
        "type": "propio",
        "localStock": 315,
        "committedStock": 25,
        "committedSizes": { "ch": 4, "m": 10, "g": 9, "xl": 2 },
        "supplierStock": null,
        "location": "Rack A-03 / Nivel 1",
        "status": "En Stock",
        "pvp": 440,
        "cost": 138,
        "sizes": {
            "ch": 55,
            "m": 130,
            "g": 95,
            "xl": 35
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "CM-RIB-012",
        "weightedAverageCost": 148.00,
        "name": "Camisilla Tank Ribbed Compression",
        "category": "Camisillas",
        "type": "propio",
        "localStock": 165,
        "committedStock": 13,
        "committedSizes": { "ch": 2, "m": 5, "g": 5, "xl": 1 },
        "supplierStock": null,
        "location": "Rack D-01 / Nivel 2",
        "status": "En Stock",
        "pvp": 480,
        "cost": 155,
        "sizes": {
            "ch": 25,
            "m": 70,
            "g": 50,
            "xl": 20
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "TOR-DRP-013",
        "weightedAverageCost": 172.00,
        "name": "Torzo Air Mesh Low-Rise Brief",
        "category": "Bikinis",
        "type": "dropship",
        "localStock": 0,
        "committedStock": 0,
        "committedSizes": { "ch": 0, "m": 0, "g": 0, "xl": 0 },
        "supplierStock": 62,
        "location": "AlmacÃ©n Proveedor (Torzo B2B)",
        "status": "Sincronizado",
        "providerUrl": "https://torzo.example.com/item/air-mesh-brief",
        "isScraping": false,
        "pvp": 430,
        "cost": 180,
        "sizes": {
            "ch": 12,
            "m": 25,
            "g": 18,
            "xl": 7
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "JK-LEAT-014",
        "weightedAverageCost": 154.00,
        "name": "Jockstrap Hard-Fit Vegan Leather",
        "category": "Jocks",
        "type": "propio",
        "localStock": 88,
        "committedStock": 7,
        "committedSizes": { "ch": 1, "m": 3, "g": 2, "xl": 1 },
        "supplierStock": null,
        "location": "Rack B-04 / CajÃ³n 2",
        "status": "En Stock",
        "pvp": 520,
        "cost": 160,
        "sizes": {
            "ch": 15,
            "m": 40,
            "g": 25,
            "xl": 8
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "TG-LACE-015",
        "weightedAverageCost": 71.00,
        "name": "Tanga Encaje ElÃ¡stico Black Lace",
        "category": "Tangas",
        "type": "propio",
        "localStock": 140,
        "committedStock": 11,
        "committedSizes": { "ch": 2, "m": 4, "g": 4, "xl": 1 },
        "supplierStock": null,
        "location": "Rack C-03 / Nivel 1",
        "status": "En Stock",
        "pvp": 310,
        "cost": 75,
        "sizes": {
            "ch": 30,
            "m": 55,
            "g": 40,
            "xl": 15
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "BX-LONG-016",
        "weightedAverageCost": 142.50,
        "name": "Boxer Largo Anti-Roce Deportivo",
        "category": "Boxers",
        "type": "propio",
        "localStock": 230,
        "committedStock": 18,
        "committedSizes": { "ch": 3, "m": 7, "g": 6, "xl": 2 },
        "supplierStock": null,
        "location": "Rack A-04 / Nivel 3",
        "status": "En Stock",
        "pvp": 460,
        "cost": 150,
        "sizes": {
            "ch": 35,
            "m": 95,
            "g": 75,
            "xl": 25
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "MOD-DRP-017",
        "weightedAverageCost": 158.00,
        "name": "ModaIntima Thermal Knit Boxer",
        "category": "Boxers",
        "type": "dropship",
        "localStock": 0,
        "committedStock": 0,
        "committedSizes": { "ch": 0, "m": 0, "g": 0, "xl": 0 },
        "supplierStock": 35,
        "location": "AlmacÃ©n Proveedor (ModaIntima Textil MX)",
        "status": "Sincronizado",
        "providerUrl": "https://modaintima.example.com/b2b/thermal-boxer",
        "isScraping": false,
        "pvp": 395,
        "cost": 165,
        "sizes": {
            "ch": 8,
            "m": 14,
            "g": 10,
            "xl": 3
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "BK-SEAM-018",
        "weightedAverageCost": 88.00,
        "name": "Bikini Micro-Mesh Ultra Ventilado",
        "category": "Bikinis",
        "type": "propio",
        "localStock": 175,
        "committedStock": 14,
        "committedSizes": { "ch": 2, "m": 6, "g": 5, "xl": 1 },
        "supplierStock": null,
        "location": "Rack B-01 / Nivel 1",
        "status": "En Stock",
        "pvp": 340,
        "cost": 92,
        "sizes": {
            "ch": 35,
            "m": 70,
            "g": 50,
            "xl": 20
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "CM-SEAM-019",
        "weightedAverageCost": 134.00,
        "name": "Camisilla Invisible Deep V-Neck",
        "category": "Camisillas",
        "type": "propio",
        "localStock": 190,
        "committedStock": 15,
        "committedSizes": { "ch": 2, "m": 6, "g": 5, "xl": 2 },
        "supplierStock": null,
        "location": "Rack D-02 / Nivel 1",
        "status": "En Stock",
        "pvp": 450,
        "cost": 140,
        "sizes": {
            "ch": 30,
            "m": 80,
            "g": 60,
            "xl": 20
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "TOR-DRP-020",
        "weightedAverageCost": 196.00,
        "name": "Torzo Cyber Stripe Active Trunk",
        "category": "Boxers",
        "type": "dropship",
        "localStock": 0,
        "committedStock": 0,
        "committedSizes": { "ch": 0, "m": 0, "g": 0, "xl": 0 },
        "supplierStock": 28,
        "location": "AlmacÃ©n Proveedor (Torzo B2B)",
        "status": "Sincronizado",
        "providerUrl": "https://torzo.example.com/item/cyber-stripe",
        "isScraping": false,
        "pvp": 475,
        "cost": 205,
        "sizes": {
            "ch": 5,
            "m": 12,
            "g": 8,
            "xl": 3
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "JK-NEO-021",
        "weightedAverageCost": 139.00,
        "name": "Jockstrap Tech Neoprene Support",
        "category": "Jocks",
        "type": "propio",
        "localStock": 110,
        "committedStock": 8,
        "committedSizes": { "ch": 1, "m": 3, "g": 3, "xl": 1 },
        "supplierStock": null,
        "location": "Rack B-03 / CajÃ³n 3",
        "status": "En Stock",
        "pvp": 490,
        "cost": 145,
        "sizes": {
            "ch": 20,
            "m": 45,
            "g": 35,
            "xl": 10
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "TG-COT-022",
        "weightedAverageCost": 52.00,
        "name": "Tanga Confort Diario AlgodÃ³n Stretch",
        "category": "Tangas",
        "type": "propio",
        "localStock": 260,
        "committedStock": 20,
        "committedSizes": { "ch": 3, "m": 8, "g": 7, "xl": 2 },
        "supplierStock": null,
        "location": "Rack C-01 / Nivel 3",
        "status": "En Stock",
        "pvp": 240,
        "cost": 55,
        "sizes": {
            "ch": 60,
            "m": 105,
            "g": 70,
            "xl": 25
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "SLK-DRP-023",
        "weightedAverageCost": 230.00,
        "name": "Silk & Cotton Royal Silk Brief",
        "category": "Bikinis",
        "type": "dropship",
        "localStock": 0,
        "committedStock": 0,
        "committedSizes": { "ch": 0, "m": 0, "g": 0, "xl": 0 },
        "supplierStock": 19,
        "location": "AlmacÃ©n Proveedor (Silk & Cotton Iberia SL)",
        "status": "Bajo Stock",
        "providerUrl": "https://silkcotton.example.com/items/royal-silk-brief",
        "isScraping": false,
        "pvp": 580,
        "cost": 240,
        "sizes": {
            "ch": 4,
            "m": 8,
            "g": 5,
            "xl": 2
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "BX-GEO-024",
        "weightedAverageCost": 119.00,
        "name": "Boxer GeomÃ©trico EdiciÃ³n Pride",
        "category": "Boxers",
        "type": "propio",
        "localStock": 145,
        "committedStock": 11,
        "committedSizes": { "ch": 2, "m": 4, "g": 4, "xl": 1 },
        "supplierStock": null,
        "location": "Rack A-01 / Nivel 3",
        "status": "En Stock",
        "pvp": 410,
        "cost": 125,
        "sizes": {
            "ch": 25,
            "m": 60,
            "g": 45,
            "xl": 15
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "CM-MUSC-025",
        "weightedAverageCost": 124.00,
        "name": "Camisilla Muscle Fit Gym Tank",
        "category": "Camisillas",
        "type": "propio",
        "localStock": 130,
        "committedStock": 10,
        "committedSizes": { "ch": 2, "m": 4, "g": 4, "xl": 0 },
        "supplierStock": null,
        "location": "Rack D-01 / Nivel 3",
        "status": "En Stock",
        "pvp": 420,
        "cost": 130,
        "sizes": {
            "ch": 20,
            "m": 55,
            "g": 40,
            "xl": 15
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "JB-INT-016",
        "weightedAverageCost": 42.00,
        "name": "Jabon Intimo pH Balance Masculino 250ml",
        "category": "Cosmetica / Skincare",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 140,
        "committedStock": 8,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-01-A",
        "status": "En Stock",
        "pvp": 180,
        "cost": 45,
        "sizes": {},
        "unitStock": 140,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "PF-MAR-017",
        "weightedAverageCost": 81.00,
        "name": "Perfume Solido Marte Noir 10ml",
        "category": "Perfumeria / Fragancias",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 185,
        "committedStock": 5,
        "committedSizes": { "v10": 5 },
        "supplierStock": null,
        "location": "C-02-B",
        "status": "En Stock",
        "pvp": 320,
        "cost": 85,
        "sizes": {},
        "volumes": { "v10": 115, "v50": 50, "v100": 20 },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "BL-LAV-018",
        "weightedAverageCost": 26.50,
        "name": "Bolsa de Malla para Lavado Delicado",
        "category": "Accesorios",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 210,
        "committedStock": 12,
        "committedSizes": {},
        "supplierStock": null,
        "location": "A-01-A",
        "status": "En Stock",
        "pvp": 120,
        "cost": 28,
        "sizes": {},
        "unitStock": 210,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": ""
    },
    {
        "sku": "CSM-BAS-001",
        "weightedAverageCost": 152.5,
        "name": "Base Fluida Mate Alta Cobertura Terra Venus",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 65,
        "committedStock": 5,
        "committedSizes": {
                "t02": 2,
                "t03": 3
        },
        "supplierStock": null,
        "location": "C-01-A",
        "status": "En Stock",
        "pvp": 480,
        "cost": 160,
        "sizes": {},
        "shades": {
                "t01": 8,
                "t02": 15,
                "t03": 20,
                "t04": 12,
                "t05": 6,
                "t06": 4
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS01",
        "expirationDate": "2027-11-15",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-COR-002",
        "weightedAverageCost": 98,
        "name": "Corrector Liquido Iluminador HD Anti-Ojeras",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 83,
        "committedStock": 6,
        "committedSizes": {
                "t02": 2,
                "t03": 4
        },
        "supplierStock": null,
        "location": "C-01-B",
        "status": "En Stock",
        "pvp": 320,
        "cost": 105,
        "sizes": {},
        "shades": {
                "t01": 11,
                "t02": 19,
                "t03": 25,
                "t04": 15,
                "t05": 8,
                "t06": 5
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS02",
        "expirationDate": "2027-08-20",
        "paoMonths": 6,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-PLV-003",
        "weightedAverageCost": 120,
        "name": "Polvo Compacto Mineral Matificante Ultrafino",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 101,
        "committedStock": 8,
        "committedSizes": {
                "t02": 2,
                "t03": 6
        },
        "supplierStock": null,
        "location": "C-01-C",
        "status": "En Stock",
        "pvp": 390,
        "cost": 125,
        "sizes": {},
        "shades": {
                "t01": 14,
                "t02": 23,
                "t03": 30,
                "t04": 18,
                "t05": 10,
                "t06": 6
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS03",
        "expirationDate": "2028-02-10",
        "paoMonths": 24,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-PRM-004",
        "weightedAverageCost": 135,
        "name": "Primer Siliconado Control Poros y Brillo",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 110,
        "committedStock": 8,
        "committedSizes": {
                "t02": 2,
                "t03": 6
        },
        "supplierStock": null,
        "location": "C-01-D",
        "status": "En Stock",
        "pvp": 420,
        "cost": 140,
        "sizes": {},
        "shades": {
                "t01": 17,
                "t02": 27,
                "t03": 35,
                "t04": 21,
                "t05": 6,
                "t06": 4
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS04",
        "expirationDate": "2026-11-15",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-TNT-005",
        "weightedAverageCost": 80.5,
        "name": "Tinte Labial y Mejillas Acabado Aterciopelado",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 116,
        "committedStock": 8,
        "committedSizes": {
                "t02": 2,
                "t03": 6
        },
        "supplierStock": null,
        "location": "C-02-A",
        "status": "En Stock",
        "pvp": 260,
        "cost": 85,
        "sizes": {},
        "shades": {
                "t01": 20,
                "t02": 31,
                "t03": 40,
                "t04": 12,
                "t05": 8,
                "t06": 5
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS05",
        "expirationDate": "2027-09-30",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-BLS-006",
        "weightedAverageCost": 57,
        "name": "Balsamo Labial Reparador con Color SPF 20",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 119,
        "committedStock": 8,
        "committedSizes": {
                "t02": 2,
                "t03": 6
        },
        "supplierStock": null,
        "location": "C-02-B",
        "status": "En Stock",
        "pvp": 190,
        "cost": 60,
        "sizes": {},
        "shades": {
                "t01": 8,
                "t02": 35,
                "t03": 45,
                "t04": 15,
                "t05": 10,
                "t06": 6
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS06",
        "expirationDate": "2027-12-05",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-GEL-007",
        "weightedAverageCost": 72,
        "name": "Gel Fijador de Cejas Transparente Esculpidor",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 104,
        "committedStock": 8,
        "committedSizes": {
                "t02": 2,
                "t03": 6
        },
        "supplierStock": null,
        "location": "C-02-C",
        "status": "En Stock",
        "pvp": 240,
        "cost": 75,
        "sizes": {},
        "shades": {
                "t01": 11,
                "t02": 15,
                "t03": 50,
                "t04": 18,
                "t05": 6,
                "t06": 4
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS07",
        "expirationDate": "2026-10-18",
        "paoMonths": 6,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-SRM-008",
        "weightedAverageCost": 172,
        "name": "Serum Iluminador Facial con Vitamina C 10%",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 87,
        "committedStock": 6,
        "committedSizes": {
                "t02": 2,
                "t03": 4
        },
        "supplierStock": null,
        "location": "C-02-D",
        "status": "En Stock",
        "pvp": 550,
        "cost": 180,
        "sizes": {},
        "shades": {
                "t01": 14,
                "t02": 19,
                "t03": 20,
                "t04": 21,
                "t05": 8,
                "t06": 5
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS08",
        "expirationDate": "2027-06-15",
        "paoMonths": 6,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-DLN-009",
        "weightedAverageCost": 86.5,
        "name": "Delineador Liquido Carbon Black Waterproof",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 93,
        "committedStock": 7,
        "committedSizes": {
                "t02": 2,
                "t03": 5
        },
        "supplierStock": null,
        "location": "C-03-A",
        "status": "En Stock",
        "pvp": 280,
        "cost": 90,
        "sizes": {},
        "shades": {
                "t01": 17,
                "t02": 23,
                "t03": 25,
                "t04": 12,
                "t05": 10,
                "t06": 6
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS09",
        "expirationDate": "2027-10-30",
        "paoMonths": 6,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-ILM-010",
        "weightedAverageCost": 110,
        "name": "Iluminador Liquido Perla Nacarada Multiuso",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 102,
        "committedStock": 8,
        "committedSizes": {
                "t02": 2,
                "t03": 6
        },
        "supplierStock": null,
        "location": "C-03-B",
        "status": "En Stock",
        "pvp": 360,
        "cost": 115,
        "sizes": {},
        "shades": {
                "t01": 20,
                "t02": 27,
                "t03": 30,
                "t04": 15,
                "t05": 6,
                "t06": 4
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS10",
        "expirationDate": "2028-01-20",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-BBC-011",
        "weightedAverageCost": 129,
        "name": "BB Cream Perfeccionadora Hidratante SPF 30",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 105,
        "committedStock": 8,
        "committedSizes": {
                "t02": 2,
                "t03": 6
        },
        "supplierStock": null,
        "location": "C-03-C",
        "status": "En Stock",
        "pvp": 410,
        "cost": 135,
        "sizes": {},
        "shades": {
                "t01": 8,
                "t02": 31,
                "t03": 35,
                "t04": 18,
                "t05": 8,
                "t06": 5
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS11",
        "expirationDate": "2027-05-18",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-BRZ-012",
        "weightedAverageCost": 104,
        "name": "Bronzer en Barra Moldeador Tono Canela",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 123,
        "committedStock": 8,
        "committedSizes": {
                "t02": 2,
                "t03": 6
        },
        "supplierStock": null,
        "location": "C-03-D",
        "status": "En Stock",
        "pvp": 340,
        "cost": 110,
        "sizes": {},
        "shades": {
                "t01": 11,
                "t02": 35,
                "t03": 40,
                "t04": 21,
                "t05": 10,
                "t06": 6
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS12",
        "expirationDate": "2027-08-14",
        "paoMonths": 18,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-SOM-013",
        "weightedAverageCost": 199,
        "name": "Paleta de Sombras Neutros Calidos Terra",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 96,
        "committedStock": 7,
        "committedSizes": {
                "t02": 2,
                "t03": 5
        },
        "supplierStock": null,
        "location": "C-04-A",
        "status": "En Stock",
        "pvp": 620,
        "cost": 210,
        "sizes": {},
        "shades": {
                "t01": 14,
                "t02": 15,
                "t03": 45,
                "t04": 12,
                "t05": 6,
                "t06": 4
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS13",
        "expirationDate": "2028-06-30",
        "paoMonths": 24,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-FJD-014",
        "weightedAverageCost": 114.5,
        "name": "Spray Fijador de Maquillaje Acabado Mate",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 114,
        "committedStock": 8,
        "committedSizes": {
                "t02": 2,
                "t03": 6
        },
        "supplierStock": null,
        "location": "C-04-B",
        "status": "En Stock",
        "pvp": 380,
        "cost": 120,
        "sizes": {},
        "shades": {
                "t01": 17,
                "t02": 19,
                "t03": 50,
                "t04": 15,
                "t05": 8,
                "t06": 5
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS14",
        "expirationDate": "2027-04-10",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-ACT-015",
        "weightedAverageCost": 138,
        "name": "Aceite Limpiador Desmaquillante de Camelia",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 97,
        "committedStock": 7,
        "committedSizes": {
                "t02": 2,
                "t03": 5
        },
        "supplierStock": null,
        "location": "C-04-C",
        "status": "En Stock",
        "pvp": 430,
        "cost": 145,
        "sizes": {},
        "shades": {
                "t01": 20,
                "t02": 23,
                "t03": 20,
                "t04": 18,
                "t05": 10,
                "t06": 6
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS15",
        "expirationDate": "2027-11-25",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-TON-016",
        "weightedAverageCost": 112,
        "name": "Tonico Exfoliante Diario Acido Glicolico 5%",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 91,
        "committedStock": 7,
        "committedSizes": {
                "t02": 2,
                "t03": 5
        },
        "supplierStock": null,
        "location": "C-04-D",
        "status": "En Stock",
        "pvp": 370,
        "cost": 118,
        "sizes": {},
        "shades": {
                "t01": 8,
                "t02": 27,
                "t03": 25,
                "t04": 21,
                "t05": 6,
                "t06": 4
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS16",
        "expirationDate": "2027-03-12",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-OJOS-017",
        "weightedAverageCost": 142,
        "name": "Crema Contorno de Ojos Descongestiva Cafeina",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 97,
        "committedStock": 7,
        "committedSizes": {
                "t02": 2,
                "t03": 5
        },
        "supplierStock": null,
        "location": "C-05-A",
        "status": "En Stock",
        "pvp": 460,
        "cost": 150,
        "sizes": {},
        "shades": {
                "t01": 11,
                "t02": 31,
                "t03": 30,
                "t04": 12,
                "t05": 8,
                "t06": 5
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS17",
        "expirationDate": "2027-07-22",
        "paoMonths": 6,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-PUR-018",
        "weightedAverageCost": 91,
        "name": "Mascarilla Facial Purificante de Arcilla Rosa",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 115,
        "committedStock": 8,
        "committedSizes": {
                "t02": 2,
                "t03": 6
        },
        "supplierStock": null,
        "location": "C-05-B",
        "status": "En Stock",
        "pvp": 310,
        "cost": 95,
        "sizes": {},
        "shades": {
                "t01": 14,
                "t02": 35,
                "t03": 35,
                "t04": 15,
                "t05": 10,
                "t06": 6
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS18",
        "expirationDate": "2026-09-28",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-EXF-019",
        "weightedAverageCost": 52,
        "name": "Exfoliante Labial Suave Azucar Moreno y Miel",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 100,
        "committedStock": 8,
        "committedSizes": {
                "t02": 2,
                "t03": 6
        },
        "supplierStock": null,
        "location": "C-05-C",
        "status": "En Stock",
        "pvp": 180,
        "cost": 55,
        "sizes": {},
        "shades": {
                "t01": 17,
                "t02": 15,
                "t03": 40,
                "t04": 18,
                "t05": 6,
                "t06": 4
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS19",
        "expirationDate": "2027-10-10",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CSM-BRM-020",
        "weightedAverageCost": 88,
        "name": "Bruma Hidratante Facial Agua de Rosas 120ml",
        "category": "CosmÃ©tica",
        "type": "propio",
        "variantDimension": "shade",
        "localStock": 118,
        "committedStock": 8,
        "committedSizes": {
                "t02": 2,
                "t03": 6
        },
        "supplierStock": null,
        "location": "C-05-D",
        "status": "En Stock",
        "pvp": 290,
        "cost": 92,
        "sizes": {},
        "shades": {
                "t01": 20,
                "t02": 19,
                "t03": 45,
                "t04": 21,
                "t05": 8,
                "t06": 5
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CS20",
        "expirationDate": "2028-03-01",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-MAR-001",
        "weightedAverageCost": 278,
        "name": "Eau de Parfum Marte Noir Cuero y Sandalo",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 65,
        "committedStock": 3,
        "committedSizes": {
                "v50": 3
        },
        "supplierStock": null,
        "location": "D-01-A",
        "status": "En Stock",
        "pvp": 890,
        "cost": 290,
        "sizes": {},
        "volumes": {
                "v10": 20,
                "v50": 35,
                "v100": 10
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF01",
        "expirationDate": "2028-12-31",
        "paoMonths": 36,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-VEN-002",
        "weightedAverageCost": 265,
        "name": "Eau de Parfum Venus Secret Jazmin y Vainilla",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 78,
        "committedStock": 4,
        "committedSizes": {
                "v50": 4
        },
        "supplierStock": null,
        "location": "D-01-B",
        "status": "En Stock",
        "pvp": 850,
        "cost": 280,
        "sizes": {},
        "volumes": {
                "v10": 24,
                "v50": 41,
                "v100": 13
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF02",
        "expirationDate": "2028-10-15",
        "paoMonths": 36,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-TIR-003",
        "weightedAverageCost": 295,
        "name": "Eau de Parfum Tiresias Ambar Gris y Cedro",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 91,
        "committedStock": 5,
        "committedSizes": {
                "v50": 5
        },
        "supplierStock": null,
        "location": "D-01-C",
        "status": "En Stock",
        "pvp": 950,
        "cost": 310,
        "sizes": {},
        "volumes": {
                "v10": 28,
                "v50": 47,
                "v100": 16
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF03",
        "expirationDate": "2029-01-20",
        "paoMonths": 36,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-CIT-004",
        "weightedAverageCost": 188,
        "name": "Agua de Colonia Bergamota de Calabria 100ml",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 104,
        "committedStock": 6,
        "committedSizes": {
                "v50": 6
        },
        "supplierStock": null,
        "location": "D-01-D",
        "status": "En Stock",
        "pvp": 590,
        "cost": 195,
        "sizes": {},
        "volumes": {
                "v10": 32,
                "v50": 53,
                "v100": 19
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF04",
        "expirationDate": "2028-05-10",
        "paoMonths": 24,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-OUD-005",
        "weightedAverageCost": 398,
        "name": "Extracto de Perfume Oud Real y Cardamomo",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 105,
        "committedStock": 6,
        "committedSizes": {
                "v50": 6
        },
        "supplierStock": null,
        "location": "D-02-A",
        "status": "En Stock",
        "pvp": 1250,
        "cost": 420,
        "sizes": {},
        "volumes": {
                "v10": 36,
                "v50": 59,
                "v100": 10
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF05",
        "expirationDate": "2029-04-12",
        "paoMonths": 36,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-BOS-006",
        "weightedAverageCost": 238,
        "name": "Eau de Parfum Bosque Humedo Musgo y Vetiver",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 98,
        "committedStock": 5,
        "committedSizes": {
                "v50": 5
        },
        "supplierStock": null,
        "location": "D-02-B",
        "status": "En Stock",
        "pvp": 780,
        "cost": 250,
        "sizes": {},
        "volumes": {
                "v10": 20,
                "v50": 65,
                "v100": 13
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF06",
        "expirationDate": "2028-08-18",
        "paoMonths": 36,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-TRA-007",
        "weightedAverageCost": 139,
        "name": "Decant Travel Pocket Atomizer Set x3 10ml",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 75,
        "committedStock": 4,
        "committedSizes": {
                "v50": 4
        },
        "supplierStock": null,
        "location": "D-02-C",
        "status": "En Stock",
        "pvp": 450,
        "cost": 145,
        "sizes": {},
        "volumes": {
                "v10": 24,
                "v50": 35,
                "v100": 16
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF07",
        "expirationDate": "2028-03-30",
        "paoMonths": 24,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-MIS-008",
        "weightedAverageCost": 105,
        "name": "Body Mist Refrescante Bambu y Te Blanco",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 88,
        "committedStock": 5,
        "committedSizes": {
                "v50": 5
        },
        "supplierStock": null,
        "location": "D-02-D",
        "status": "En Stock",
        "pvp": 340,
        "cost": 110,
        "sizes": {},
        "volumes": {
                "v10": 28,
                "v50": 41,
                "v100": 19
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF08",
        "expirationDate": "2027-11-12",
        "paoMonths": 24,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-IRI-009",
        "weightedAverageCost": 345,
        "name": "Perfume Esencia Pura Iris Florentino",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 89,
        "committedStock": 5,
        "committedSizes": {
                "v50": 5
        },
        "supplierStock": null,
        "location": "D-03-A",
        "status": "En Stock",
        "pvp": 1100,
        "cost": 360,
        "sizes": {},
        "volumes": {
                "v10": 32,
                "v50": 47,
                "v100": 10
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF09",
        "expirationDate": "2028-11-20",
        "paoMonths": 36,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-ROL-010",
        "weightedAverageCost": 115,
        "name": "Perfume en Aceite Roll-On Ambar Dorado 15ml",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 102,
        "committedStock": 6,
        "committedSizes": {
                "v50": 6
        },
        "supplierStock": null,
        "location": "D-03-B",
        "status": "En Stock",
        "pvp": 380,
        "cost": 120,
        "sizes": {},
        "volumes": {
                "v10": 36,
                "v50": 53,
                "v100": 13
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF10",
        "expirationDate": "2028-07-14",
        "paoMonths": 24,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-NER-011",
        "weightedAverageCost": 196,
        "name": "Agua Fresca Neroli Mediterraneo y Azahar",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 95,
        "committedStock": 5,
        "committedSizes": {
                "v50": 5
        },
        "supplierStock": null,
        "location": "D-03-C",
        "status": "En Stock",
        "pvp": 620,
        "cost": 205,
        "sizes": {},
        "volumes": {
                "v10": 20,
                "v50": 59,
                "v100": 16
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF11",
        "expirationDate": "2028-09-05",
        "paoMonths": 24,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-OCN-012",
        "weightedAverageCost": 228,
        "name": "Eau de Parfum Sal Marina y Algas Atlanticas",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 108,
        "committedStock": 6,
        "committedSizes": {
                "v50": 6
        },
        "supplierStock": null,
        "location": "D-03-D",
        "status": "En Stock",
        "pvp": 740,
        "cost": 240,
        "sizes": {},
        "volumes": {
                "v10": 24,
                "v50": 65,
                "v100": 19
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF12",
        "expirationDate": "2028-04-25",
        "paoMonths": 36,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-TAB-013",
        "weightedAverageCost": 256,
        "name": "Colonia Amaderada Hoja de Tabaco y Haba Tonka",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 73,
        "committedStock": 4,
        "committedSizes": {
                "v50": 4
        },
        "supplierStock": null,
        "location": "D-04-A",
        "status": "En Stock",
        "pvp": 820,
        "cost": 270,
        "sizes": {},
        "volumes": {
                "v10": 28,
                "v50": 35,
                "v100": 10
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF13",
        "expirationDate": "2028-12-10",
        "paoMonths": 36,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-SOL-014",
        "weightedAverageCost": 118,
        "name": "Perfume Solido Cuero y Cacao Balsamico 20g",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 86,
        "committedStock": 5,
        "committedSizes": {
                "v50": 5
        },
        "supplierStock": null,
        "location": "D-04-B",
        "status": "En Stock",
        "pvp": 390,
        "cost": 125,
        "sizes": {},
        "volumes": {
                "v10": 32,
                "v50": 41,
                "v100": 13
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF14",
        "expirationDate": "2027-08-30",
        "paoMonths": 18,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-ORQ-015",
        "weightedAverageCost": 110,
        "name": "Bruma Sensual Orquidea Negra y Pachuli",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 99,
        "committedStock": 5,
        "committedSizes": {
                "v50": 5
        },
        "supplierStock": null,
        "location": "D-04-C",
        "status": "En Stock",
        "pvp": 360,
        "cost": 115,
        "sizes": {},
        "volumes": {
                "v10": 36,
                "v50": 47,
                "v100": 16
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF15",
        "expirationDate": "2027-10-02",
        "paoMonths": 24,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-CIP-016",
        "weightedAverageCost": 215,
        "name": "Eau de Parfum Cipres de Toscana y Pino Silvestre",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 92,
        "committedStock": 5,
        "committedSizes": {
                "v50": 5
        },
        "supplierStock": null,
        "location": "D-04-D",
        "status": "En Stock",
        "pvp": 690,
        "cost": 225,
        "sizes": {},
        "volumes": {
                "v10": 20,
                "v50": 53,
                "v100": 19
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF16",
        "expirationDate": "2028-06-15",
        "paoMonths": 36,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-HIG-017",
        "weightedAverageCost": 206,
        "name": "Fragancia Fresca Higo Verde y Corteza de Cedro",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 93,
        "committedStock": 5,
        "committedSizes": {
                "v50": 5
        },
        "supplierStock": null,
        "location": "D-05-A",
        "status": "En Stock",
        "pvp": 670,
        "cost": 215,
        "sizes": {},
        "volumes": {
                "v10": 24,
                "v50": 59,
                "v100": 10
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF17",
        "expirationDate": "2028-02-28",
        "paoMonths": 36,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-VET-018",
        "weightedAverageCost": 305,
        "name": "Extracto Puro Vetiver de Haiti Ahumado",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 106,
        "committedStock": 6,
        "committedSizes": {
                "v50": 6
        },
        "supplierStock": null,
        "location": "D-05-B",
        "status": "En Stock",
        "pvp": 980,
        "cost": 320,
        "sizes": {},
        "volumes": {
                "v10": 28,
                "v50": 65,
                "v100": 13
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF18",
        "expirationDate": "2029-03-10",
        "paoMonths": 36,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-ROM-019",
        "weightedAverageCost": 162,
        "name": "Colonia Botanica Romero Silvestre y Toronja",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 83,
        "committedStock": 4,
        "committedSizes": {
                "v50": 4
        },
        "supplierStock": null,
        "location": "D-05-C",
        "status": "En Stock",
        "pvp": 520,
        "cost": 170,
        "sizes": {},
        "volumes": {
                "v10": 32,
                "v50": 35,
                "v100": 16
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF19",
        "expirationDate": "2026-11-20",
        "paoMonths": 24,
        "requiresSanitaryControl": true
},
    {
        "sku": "PRF-ECL-020",
        "weightedAverageCost": 362,
        "name": "Perfume Edicion Limitada Eclipse Resina Dorada",
        "category": "PerfumerÃ­a",
        "type": "propio",
        "variantDimension": "volume",
        "localStock": 96,
        "committedStock": 5,
        "committedSizes": {
                "v50": 5
        },
        "supplierStock": null,
        "location": "D-05-D",
        "status": "En Stock",
        "pvp": 1150,
        "cost": 380,
        "sizes": {},
        "volumes": {
                "v10": 36,
                "v50": 41,
                "v100": 19
        },
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-PF20",
        "expirationDate": "2029-05-01",
        "paoMonths": 36,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-JBN-001",
        "weightedAverageCost": 55,
        "name": "Jabon Intimo pH 5.5 Arbol de Te y Menta 250ml",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 45,
        "committedStock": 2,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-06-A",
        "status": "En Stock",
        "pvp": 195,
        "cost": 58,
        "sizes": {},
        "unitStock": 45,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP01",
        "expirationDate": "2027-09-15",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-JBF-002",
        "weightedAverageCost": 54.5,
        "name": "Jabon Intimo Femenino pH 4.2 Acido Lactico 250ml",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 57,
        "committedStock": 2,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-06-B",
        "status": "En Stock",
        "pvp": 195,
        "cost": 58,
        "sizes": {},
        "unitStock": 57,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP02",
        "expirationDate": "2027-10-20",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-GEL-003",
        "weightedAverageCost": 68,
        "name": "Gel Limpiador Corporal Antibacterial Sin Sulfatos 500ml",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 69,
        "committedStock": 3,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-06-C",
        "status": "En Stock",
        "pvp": 240,
        "cost": 72,
        "sizes": {},
        "unitStock": 69,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP03",
        "expirationDate": "2028-01-15",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-CRM-004",
        "weightedAverageCost": 81,
        "name": "Crema Corporal Hidratante Manteca de Karite 400ml",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 81,
        "committedStock": 4,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-06-D",
        "status": "En Stock",
        "pvp": 280,
        "cost": 85,
        "sizes": {},
        "unitStock": 81,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP04",
        "expirationDate": "2027-11-08",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-ACE-005",
        "weightedAverageCost": 74,
        "name": "Aceite Pre-Afeitado y Lubricacion Precisa 60ml",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 93,
        "committedStock": 4,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-07-A",
        "status": "En Stock",
        "pvp": 260,
        "cost": 78,
        "sizes": {},
        "unitStock": 93,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP05",
        "expirationDate": "2027-12-10",
        "paoMonths": 18,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-AFT-006",
        "weightedAverageCost": 86,
        "name": "Balsamo Aftershave Anti-Irritacion Centella 100ml",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 105,
        "committedStock": 5,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-07-B",
        "status": "En Stock",
        "pvp": 290,
        "cost": 90,
        "sizes": {},
        "unitStock": 105,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP06",
        "expirationDate": "2027-08-25",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-DSD-007",
        "weightedAverageCost": 61.5,
        "name": "Desodorante Natural Mineral Sin Aluminio Barra 75g",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 117,
        "committedStock": 5,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-07-C",
        "status": "En Stock",
        "pvp": 210,
        "cost": 65,
        "sizes": {},
        "unitStock": 117,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP07",
        "expirationDate": "2028-04-10",
        "paoMonths": 18,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-SHP-008",
        "weightedAverageCost": 78,
        "name": "Shampoo Anticaida Fortalecedor Biotina y Romero 350ml",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 129,
        "committedStock": 5,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-07-D",
        "status": "En Stock",
        "pvp": 270,
        "cost": 82,
        "sizes": {},
        "unitStock": 129,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP08",
        "expirationDate": "2027-10-14",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-EXF-009",
        "weightedAverageCost": 71,
        "name": "Exfoliante Corporal Exfoliante de Cafe Arabigo 250g",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 45,
        "committedStock": 2,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-08-A",
        "status": "En Stock",
        "pvp": 250,
        "cost": 75,
        "sizes": {},
        "unitStock": 45,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP09",
        "expirationDate": "2026-10-25",
        "paoMonths": 6,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-LOC-010",
        "weightedAverageCost": 64,
        "name": "Locion Astringente Antifriccion Zonas Intimas 150ml",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 57,
        "committedStock": 2,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-08-B",
        "status": "En Stock",
        "pvp": 220,
        "cost": 68,
        "sizes": {},
        "unitStock": 57,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP10",
        "expirationDate": "2027-09-02",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-CER-011",
        "weightedAverageCost": 66.5,
        "name": "Cera Moldeadora para Cabello Acabado Mate 80g",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 69,
        "committedStock": 3,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-08-C",
        "status": "En Stock",
        "pvp": 230,
        "cost": 70,
        "sizes": {},
        "unitStock": 69,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP11",
        "expirationDate": "2028-02-18",
        "paoMonths": 18,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-POM-012",
        "weightedAverageCost": 68,
        "name": "Pomada Fijadora Brillo Medio a Base de Agua 100g",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 81,
        "committedStock": 4,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-08-D",
        "status": "En Stock",
        "pvp": 240,
        "cost": 72,
        "sizes": {},
        "unitStock": 81,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP12",
        "expirationDate": "2028-03-22",
        "paoMonths": 18,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-BRB-013",
        "weightedAverageCost": 83,
        "name": "Aceite Hidratante y Suavizante para Barba 30ml",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 93,
        "committedStock": 4,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-09-A",
        "status": "En Stock",
        "pvp": 290,
        "cost": 88,
        "sizes": {},
        "unitStock": 93,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP13",
        "expirationDate": "2027-12-18",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-MAN-014",
        "weightedAverageCost": 45,
        "name": "Crema de Manos Reparadora con Ceramidas 75ml",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 105,
        "committedStock": 5,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-09-B",
        "status": "En Stock",
        "pvp": 160,
        "cost": 48,
        "sizes": {},
        "unitStock": 105,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP14",
        "expirationDate": "2027-11-30",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-BAR-015",
        "weightedAverageCost": 33,
        "name": "Jabon en Barra Artesanal de Avena y Aceite de Coco 120g",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 117,
        "committedStock": 5,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-09-C",
        "status": "En Stock",
        "pvp": 120,
        "cost": 35,
        "sizes": {},
        "unitStock": 117,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP15",
        "expirationDate": "2028-06-01",
        "paoMonths": 24,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-CAP-016",
        "weightedAverageCost": 90,
        "name": "Mascarilla Capilar Hidratacion Profunda Argan 200ml",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 129,
        "committedStock": 5,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-09-D",
        "status": "En Stock",
        "pvp": 310,
        "cost": 95,
        "sizes": {},
        "unitStock": 129,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP16",
        "expirationDate": "2027-07-15",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-PIE-017",
        "weightedAverageCost": 49,
        "name": "Spray Desodorante y Refrescante de Pies con Mentol 120ml",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 45,
        "committedStock": 2,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-10-A",
        "status": "En Stock",
        "pvp": 180,
        "cost": 52,
        "sizes": {},
        "unitStock": 45,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP17",
        "expirationDate": "2027-10-05",
        "paoMonths": 18,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-DUC-018",
        "weightedAverageCost": 58.5,
        "name": "Gel de Ducha Energizante Extracto de Citricos 300ml",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 57,
        "committedStock": 2,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-10-B",
        "status": "En Stock",
        "pvp": 210,
        "cost": 62,
        "sizes": {},
        "unitStock": 57,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP18",
        "expirationDate": "2028-01-28",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-LAB-019",
        "weightedAverageCost": 35.5,
        "name": "Balsamo Labial Unisex Hidratacion Extrema 15g",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 69,
        "committedStock": 3,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-10-C",
        "status": "En Stock",
        "pvp": 130,
        "cost": 38,
        "sizes": {},
        "unitStock": 69,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP19",
        "expirationDate": "2028-04-15",
        "paoMonths": 12,
        "requiresSanitaryControl": true
},
    {
        "sku": "CP-ROL-020",
        "weightedAverageCost": 41,
        "name": "Desodorante Roll-On Suave Piel Sensible 50ml",
        "category": "Cuidado Personal",
        "type": "propio",
        "variantDimension": "none",
        "localStock": 81,
        "committedStock": 4,
        "committedSizes": {},
        "supplierStock": null,
        "location": "C-10-D",
        "status": "En Stock",
        "pvp": 150,
        "cost": 44,
        "sizes": {},
        "unitStock": 81,
        "adjustType": "add",
        "adjustQty": "",
        "adjustReason": "",
        "batchNumber": "LOT-2026-CP20",
        "expirationDate": "2026-09-22",
        "paoMonths": 12,
        "requiresSanitaryControl": true
}
],

        

        // =========================================================================
        // MÃ‰TODOS DE PRIORIDAD 2: INBOUND, WHATSAPP & KARDEX
        // =========================================================================

        // [P2.1] RecepciÃ³n Inbound
        openReceiveOrderModal(order) {
            this.receivingOrder = order;
            this.receivingSizes = {
                ch: (order.sizes && order.sizes.ch) || 0,
                m: (order.sizes && order.sizes.m) || 0,
                g: (order.sizes && order.sizes.g) || 0,
                xl: (order.sizes && order.sizes.xl) || 0,
                '2xl': (order.sizes && order.sizes['2xl']) || 0,
                '3xl': (order.sizes && order.sizes['3xl']) || 0
            };
            this.receivingNotes = 'Cotejo fÃ­sico conforme. Empaque sellado de origen.';

            // Deteccion de Control Sanitario (TASK-INV-06)
            const prod = this.products.find(p => p.sku === order.sku);
            this.receivingRequiresSanitary = !!(prod && (prod.requiresSanitaryControl || prod.category === 'Cosmetica' || prod.category === 'Perfumeria' || prod.category === 'Cuidado Personal'));
            if (this.receivingRequiresSanitary) {
                this.receivingBatchNumber = (prod && prod.batchNumber) ? prod.batchNumber : ('LOT-' + new Date().getFullYear() + '-' + Math.floor(1000 + Math.random() * 9000));
                this.receivingExpirationDate = (prod && prod.expirationDate) ? prod.expirationDate : '';
                this.receivingPaoMonths = (prod && prod.paoMonths) ? prod.paoMonths : 12;
            } else {
                this.receivingBatchNumber = '';
                this.receivingExpirationDate = '';
                this.receivingPaoMonths = 12;
            }

            this.receiveOrderModalOpen = true;
        },

        setReceivingSizesFull() {
            if (!this.receivingOrder || !this.receivingOrder.sizes) return;
            this.receivingSizes = {
                ch: this.receivingOrder.sizes.ch || 0,
                m: this.receivingOrder.sizes.m || 0,
                g: this.receivingOrder.sizes.g || 0,
                xl: this.receivingOrder.sizes.xl || 0,
                '2xl': this.receivingOrder.sizes['2xl'] || 0,
                '3xl': this.receivingOrder.sizes['3xl'] || 0
            };
        },

        calculateReceivingTotal() {
            let sum = 0;
            if (this.receivingSizes) {
                Object.values(this.receivingSizes).forEach(v => {
                    sum += (parseInt(v) || 0);
                });
            }
            return sum;
        },

        confirmInboundReceipt() {
            if (!this.receivingOrder) return;
            const order = this.receivingOrder;
            const totalReceived = this.calculateReceivingTotal();

            if (totalReceived <= 0) {
                this.showToast('Debes ingresar al menos 1 unidad recibida en el cotejo fÃ­sico.', 'warning');
                return;
            }

            // 1. Actualizar estado de la orden
            order.status = 'Recibida en AlmacÃ©n';

            // 2. Incrementar stock del producto en catÃ¡logo y recalcular CPP Contable
            const product = this.products.find(p => p.sku === order.sku);
            let stockBefore = 0;
            let stockAfter = 0;
            let prevCpp = 0;
            let newCpp = 0;
            const unitCost = order.unitCost || (order.qty > 0 ? (order.totalCost / order.qty) : (order.costUnit || 0)) || (product ? product.cost : 0);

            if (product) {
                stockBefore = product.localStock || 0;
                prevCpp = product.weightedAverageCost || product.cost || 0;
                newCpp = this.calculateCPP(stockBefore, prevCpp, totalReceived, unitCost);
                product.weightedAverageCost = newCpp;
                product.cost = Math.round(unitCost * 100) / 100;

                product.localStock = (product.localStock || 0) + totalReceived;
                stockAfter = product.localStock;

                if (!product.sizes) {
                    product.sizes = { ch: 0, m: 0, g: 0, xl: 0, '2xl': 0, '3xl': 0 };
                }
                Object.keys(this.receivingSizes).forEach(sz => {
                    product.sizes[sz] = (product.sizes[sz] || 0) + (parseInt(this.receivingSizes[sz]) || 0);
                });

                // Si era dropship y ahora tiene stock fÃ­sico, se vuelve mixto o propio
                if (product.type === 'dropship' && product.localStock > 0) {
                    product.type = 'mixto';
                }

                // Actualizacion de trazabilidad sanitaria de lote (TASK-INV-06)
                if (this.receivingRequiresSanitary || product.requiresSanitaryControl) {
                    product.batchNumber = this.receivingBatchNumber || product.batchNumber || ('LOT-' + new Date().getFullYear() + '-001');
                    product.expirationDate = this.receivingExpirationDate || product.expirationDate;
                    product.paoMonths = parseInt(this.receivingPaoMonths) || product.paoMonths || 12;
                    product.requiresSanitaryControl = true;
                }
            }

            // 3. Liberar Carrito Mixto si aplica
            if (order.linkedCartId) {
                const req = this.customerOnDemandRequests.find(r => r.sku === order.sku && r.cartLabel.includes(order.linkedCartId));
                if (req) {
                    req.verificationStatus = 'confirmado';
                    req.statusNote = 'MercancÃ­a en AlmacÃ©n - Listo para Empaque Total';
                }
                this.mixedCartLiberated = {
                    show: true,
                    orderId: order.linkedCartId,
                    customer: order.linkedCustomer || (req ? req.customerName : 'Cliente'),
                    item: order.productName + ' (Talla M)',
                    date: 'Justo ahora'
                };
            }

            // 4. Registrar Asiento Inmutable en Kardex
            this.recordInventoryMovement({
                type: 'ENTRADA_COMPRA',
                sku: order.sku,
                productName: order.productName,
                sizes: { ...this.receivingSizes },
                delta: totalReceived,
                stockBefore: stockBefore,
                stockAfter: stockAfter,
                reference: 'Orden #' + order.id + ' (' + (order.carrier.split(' ')[0] || 'Inbound') + ')',
                user: 'AlmacÃ©n Central (RecepciÃ³n Inbound)',
                note: (order.linkedCartId ? ('RecepciÃ³n de proveedor que libera Pedido Mixto ' + order.linkedCartId + '. ') : 'RecepciÃ³n conforme de mercancÃ­a en trÃ¡nsito. ') + 'ValuaciÃ³n CPP: ' + prevCpp.toFixed(2) + ' -> ' + newCpp.toFixed(2) + ' (Lote @ ' + unitCost.toFixed(2) + ')' + ((product && product.requiresSanitaryControl) ? (' | Lote: ' + product.batchNumber + ' | Cad: ' + product.expirationDate + ' | PAO: ' + product.paoMonths + 'M') : '')
            });

            // Sincronizar recepciÃ³n Inbound con backend si la orden tiene persistencia DB
            if (order.dbId) {
                fetch('/api/v1/inventory/purchases/orders/' + order.dbId + '/receive', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        items: this.receivingSizes,
                        notes: this.receivingNotes,
                        batch_number: this.receivingRequiresSanitary ? (this.receivingBatchNumber || null) : null,
                        expiration_date: this.receivingRequiresSanitary ? (this.receivingExpirationDate || null) : null,
                        pao_months: this.receivingRequiresSanitary ? (parseInt(this.receivingPaoMonths, 10) || 12) : null
                    })
                }).catch(err => console.error('Error sincronizando recepcion Inbound:', err));
            }

            this.receiveOrderModalOpen = false;
            this.showToast('Â¡' + totalReceived + ' piezas de ' + order.sku + ' ingresadas a almacÃ©n exitosamente!', 'success');
        },

        // [P2.2] Generador WhatsApp
        openOrderExportModal(supplier = 'Torzo B2B') {
            this.exportSupplierFilter = supplier;
            this.exportCopiedToast = false;
            this.orderExportModalOpen = true;
        },

        formatCurrency(val) {
            return '$' + Number(val || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' MXN';
        },

        getExportOrderText() {
            const supplier = this.exportSupplierFilter;
            const incPlanned = this.exportIncludePlanned;
            const incOnDemand = this.exportIncludeOnDemand;

            const lines = [];
            lines.push('*ORDEN DE COMPRA MAYORISTA*');
            lines.push('*Proveedor:* ' + (supplier === 'all' ? 'Consolidado Multimarca' : supplier));
            lines.push('*Fecha de EmisiÃ³n:* ' + new Date().toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' }));
            lines.push('*Comprador:* Mi Tienda Online (RevOps Compras)');
            lines.push('*Destino:* AlmacÃ©n Central CDMX');
            lines.push('-------------------------------------------');

            const filteredOrders = incPlanned ? this.futurePurchaseOrders.filter(o => supplier === 'all' || o.supplier === supplier) : [];
            const filteredBackorders = incOnDemand ? this.customerOnDemandRequests.filter(r => supplier === 'all' || r.supplier === supplier) : [];

            let totalUnits = 0;
            let totalCost = 0;

            if (filteredOrders.length > 0) {
                lines.push('*REABASTECIMIENTO PLANIFICADO:*');
                filteredOrders.forEach((o, idx) => {
                    totalUnits += o.qty;
                    totalCost += o.totalCost;
                    lines.push('\n*' + (idx + 1) + '. ' + o.productName + '*');
                    lines.push('   â€¢ SKU: [' + o.sku + ']');
                    lines.push('   â€¢ Cantidad Total: *' + o.qty + ' piezas*');
                    if (o.sizes) {
                        const szStr = Object.entries(o.sizes)
                            .filter(([_, v]) => v > 0)
                            .map(([k, v]) => k.toUpperCase() + ': ' + v)
                            .join(' | ');
                        lines.push('   â€¢ Curva de Tallas: [ ' + szStr + ' ]');
                    }
                    lines.push('   â€¢ Costo Unitario: ' + this.formatCurrency(o.costUnit) + ' | Subtotal: *' + this.formatCurrency(o.totalCost) + '*');
                });
                lines.push('');
            }

            if (filteredBackorders.length > 0) {
                lines.push('-------------------------------------------');
                lines.push('*SOLICITUDES ON-DEMAND (PEDIDOS DE CLIENTES):*');
                filteredBackorders.forEach((r, idx) => {
                    totalUnits += r.qty;
                    totalCost += r.totalCost;
                    lines.push('\n*' + (idx + 1) + '. ' + r.productName + '* (Talla: *' + r.size + '*)');
                    lines.push('   â€¢ SKU: [' + r.sku + '] | Cantidad: *' + r.qty + ' ud(s)*');
                    lines.push('   â€¢ Referencia: ' + r.cartLabel + ' (' + r.customerName + ')');
                    lines.push('   â€¢ Subtotal: *' + this.formatCurrency(r.totalCost) + '*');
                });
                lines.push('');
            }

            lines.push('===========================================');
            lines.push('*RESUMEN TOTAL DE COMPRA:*');
            lines.push('*Total de Piezas:* ' + totalUnits + ' unidades');
            lines.push('*InversiÃ³n Total Estimada:* ' + this.formatCurrency(totalCost));
            lines.push('===========================================');
            lines.push('*Instrucciones:* Favor de confirmar existencias por talla y enviar cuenta bancaria para liquidaciÃ³n.');

            return lines.join('\n');
        },

        copyOrderTextToClipboard() {
            const text = this.getExportOrderText();
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    this.exportCopiedToast = true;
                    this.showToast('Â¡Texto copiado al portapapeles! Listo para pegar en WhatsApp.', 'success');
                    setTimeout(() => { this.exportCopiedToast = false; }, 3000);
                }).catch(() => {
                    this.fallbackCopyText(text);
                });
            } else {
                this.fallbackCopyText(text);
            }
        },

        fallbackCopyText(text) {
            const el = document.createElement('textarea');
            el.value = text;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            this.exportCopiedToast = true;
            this.showToast('Â¡Texto copiado al portapapeles! Listo para pegar en WhatsApp.', 'success');
            setTimeout(() => { this.exportCopiedToast = false; }, 3000);
        },

        // Toggle colapso de tarjetas de KPIs (Regla 2 de DiseÃ±o UI)
        toggleKpis() {
            this.kpisCollapsed = !this.kpisCollapsed;
        },

        // [P2.3] Kardex
        openKardexDrawer() {
            this.kardexSearch = '';
            this.kardexFilterType = 'all';
            this.kardexDrawerOpen = true;
        },

        openKardexForSku(sku) {
            this.kardexSearch = sku;
            this.kardexFilterType = 'all';
            this.kardexDrawerOpen = true;
        },

        openKardexForRef(refId) {
            this.kardexSearch = refId;
            this.kardexFilterType = 'all';
            this.kardexDrawerOpen = true;
        },

        recordInventoryMovement(movData) {
            const newMovement = {
                id: 'MOV-2026-' + String(this.inventoryMovements.length + 101).padStart(3, '0'),
                timestamp: 'Hoy, ' + new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }) + ' hrs',
                type: movData.type,
                sku: movData.sku,
                productName: movData.productName,
                sizes: movData.sizes || {},
                delta: movData.delta || 0,
                stockBefore: movData.stockBefore || 0,
                stockAfter: movData.stockAfter || 0,
                reference: movData.reference || 'Ajuste Manual',
                user: movData.user || 'Administrador RevOps',
                note: movData.note || ''
            };
            this.inventoryMovements.unshift(newMovement);
        },

        // TASK-INT-02: Trazabilidad de Stock: Comprometido a Despachado / Kardex
        recordOrderDispatch(payload) {
            if (!payload || !payload.items || !Array.isArray(payload.items)) return;
            const orderId = payload.orderId || '#ORD-DESP';
            const operator = payload.operator || 'Operador LogÃ­stico';
            const courier = payload.courier || 'DHL Express';

            if (typeof window !== 'undefined') {
                if (!window.__executedOrderDispatches) window.__executedOrderDispatches = [];
                if (!window.__executedOrderDispatches.some(d => d.orderId === orderId)) {
                    window.__executedOrderDispatches.push(payload);
                }
            }

            // Aliases entre SKUs de tareas Dashboard y catÃ¡logo maestro de inventario
            const skuAliases = {
                'BOX-BAM-001-M': 'BX-MOD-001',
                'TOR-DRP-002-M': 'JK-ATH-002',
                'ACC-BAG-001': 'BL-LAV-018',
                'BOX-MOD-001-M': 'BX-MOD-001'
            };

            const prefNote = (payload.subscriberPreferences && payload.subscriberPreferences.isSubscriber)
                ? ` [Pref. Suscriptor: Talla ${(payload.subscriberPreferences.sizePreference || 'M').toUpperCase()} - ${payload.subscriberPreferences.restrictions || 'Conforme'}]`
                : '';

            payload.items.forEach(item => {
                const targetSku = skuAliases[item.sku] || item.sku;
                let product = this.products.find(p => p.sku === targetSku || p.sku === item.sku);
                if (!product) {
                    product = this.products.find(p => item.sku.startsWith(p.sku) || p.sku.startsWith(item.sku));
                }

                const qty = parseInt(item.qty, 10) || 1;
                const sizeKey = (item.size || 'm').toLowerCase();

                if (product) {
                    const oldStock = product.localStock || 0;
                    product.committedStock = Math.max(0, (product.committedStock || 0) - qty);
                    product.localStock = Math.max(0, (product.localStock || 0) - qty);

                    if (product.sizes && product.sizes[sizeKey] !== undefined) {
                        product.sizes[sizeKey] = Math.max(0, product.sizes[sizeKey] - qty);
                    }
                    if (product.committedSizes && product.committedSizes[sizeKey] !== undefined) {
                        product.committedSizes[sizeKey] = Math.max(0, product.committedSizes[sizeKey] - qty);
                    }

                    this.recordInventoryMovement({
                        type: 'SALIDA_VENTA',
                        sku: product.sku,
                        productName: product.name,
                        sizes: { [sizeKey]: qty },
                        delta: -qty,
                        stockBefore: oldStock,
                        stockAfter: product.localStock,
                        reference: orderId,
                        user: operator,
                        note: `Despacho de pedido ${orderId} vÃ­a ${courier}.${prefNote} Stock comprometido liberado y fÃ­sico deducido.`
                    });
                } else {
                    this.recordInventoryMovement({
                        type: 'SALIDA_VENTA',
                        sku: item.sku,
                        productName: item.name || 'Prenda Despachada',
                        sizes: { [sizeKey]: qty },
                        delta: -qty,
                        stockBefore: qty,
                        stockAfter: 0,
                        reference: orderId,
                        user: operator,
                        note: `Despacho de pedido ${orderId} vÃ­a ${courier}.${prefNote} Salida registrada sin decremento local.`
                    });
                }
            });

            if (typeof this.recalculateInventoryKPIs === 'function') {
                this.recalculateInventoryKPIs();
            }
        },

        // TASK-INT-03: LiberaciÃ³n Masiva de Lotes de SuscripciÃ³n (Batch Fulfillment en Fecha de Corte)
        recordSubscriptionBatchFulfillment(payload) {
            if (!payload || !payload.batchCode || !payload.componentsDeduction || !Array.isArray(payload.componentsDeduction)) return;
            const batchCode = payload.batchCode;
            const operator = payload.operator || 'Coordinador Suscripciones';
            const totalBoxes = payload.totalBoxes || 150;
            const cutoffDate = payload.cutoffDate || '2026-09-15';

            if (typeof window !== 'undefined') {
                if (!window.__executedSubscriptionBatches) window.__executedSubscriptionBatches = [];
                if (!window.__executedSubscriptionBatches.some(b => b.batchCode === batchCode)) {
                    window.__executedSubscriptionBatches.push(payload);
                }
            }

            // Evitar duplicar descuentos si ya fue procesado en Kardex
            if (this.inventoryMovements.some(m => m.reference === batchCode && m.type === 'SALIDA_COMBO_SUSCRIPCION')) {
                return;
            }

            payload.componentsDeduction.forEach(comp => {
                let product = this.products.find(p => p.sku === comp.sku);
                const qty = parseInt(comp.qty, 10) || 1;
                const sizeKey = (comp.size || 'm').toLowerCase();

                if (product) {
                    const oldStock = product.localStock || 0;
                    product.localStock = Math.max(0, (product.localStock || 0) - qty);

                    if (comp.size && product.sizes && typeof product.sizes[comp.size] !== 'undefined') {
                        product.sizes[comp.size] = Math.max(0, product.sizes[comp.size] - qty);
                    } else if (comp.size && product.volumes && typeof product.volumes[comp.size] !== 'undefined') {
                        product.volumes[comp.size] = Math.max(0, product.volumes[comp.size] - qty);
                    } else if (comp.size && product.shades && typeof product.shades[comp.size] !== 'undefined') {
                        product.shades[comp.size] = Math.max(0, product.shades[comp.size] - qty);
                    }
                    if (typeof product.unitStock !== 'undefined') {
                        product.unitStock = Math.max(0, (product.unitStock || 0) - qty);
                    }

                    this.recordInventoryMovement({
                        type: 'SALIDA_COMBO_SUSCRIPCION',
                        sku: product.sku,
                        productName: product.name,
                        sizes: comp.size ? { [comp.size]: -qty } : {},
                        delta: -qty,
                        stockBefore: oldStock,
                        stockAfter: product.localStock,
                        reference: batchCode,
                        user: operator,
                        note: `Liquidacion masiva lote suscripciones (${totalBoxes} cajas). Corte quincenal ${cutoffDate} - Lote: ${batchCode}`
                    });
                } else {
                    this.recordInventoryMovement({
                        type: 'SALIDA_COMBO_SUSCRIPCION',
                        sku: comp.sku,
                        productName: comp.name || 'Componente BOM Suscripcion',
                        sizes: comp.size ? { [comp.size]: -qty } : {},
                        delta: -qty,
                        stockBefore: qty,
                        stockAfter: 0,
                        reference: batchCode,
                        user: operator,
                        note: `Liquidacion masiva lote suscripciones (${totalBoxes} cajas). Salida registrada sin decremento local.`
                    });
                }
            });

            if (typeof this.recalculateInventoryKPIs === 'function') {
                this.recalculateInventoryKPIs();
            }
        },

        get filteredMovements() {
            return this.inventoryMovements.filter(mov => {
                let matchesType = false;
                if (this.kardexFilterType === 'all') {
                    matchesType = true;
                } else if (this.kardexFilterType === 'SALIDA_VENTA') {
                    matchesType = mov.type === 'SALIDA_VENTA' || mov.type === 'SALIDA_DESPACHO';
                } else {
                    matchesType = mov.type === this.kardexFilterType;
                }
                const q = (this.kardexSearch || '').toLowerCase().trim();
                const matchesSearch = !q || 
                    (mov.sku && mov.sku.toLowerCase().includes(q)) || 
                    (mov.productName && mov.productName.toLowerCase().includes(q)) ||
                    (mov.reference && mov.reference.toLowerCase().includes(q)) ||
                    (mov.user && mov.user.toLowerCase().includes(q));
                return matchesType && matchesSearch;
            });
        },

        getMovementBadgeClass(type) {
            switch(type) {
                case 'ENTRADA_COMPRA':
                    return 'bg-emerald-50 text-emerald-700 border-emerald-200';
                case 'VENTA_DIRECTA':
                    return 'bg-blue-50 text-blue-700 border-blue-200';
                case 'RESERVA_MIXTA':
                    return 'bg-amber-50 text-amber-700 border-amber-200';
                case 'SALIDA_DESPACHO':
                case 'SALIDA_VENTA':
                    return 'bg-purple-50 text-purple-700 border-purple-200';
                case 'AJUSTE_MERMA':
                    return 'bg-rose-50 text-rose-700 border-rose-200';
                case 'SALIDA_COMBO_SUSCRIPCION':
                    return 'bg-violet-50 text-violet-700 border-violet-200';
                case 'AJUSTE_INVENTARIO':
                default:
                    return 'bg-slate-100 text-slate-700 border-slate-200';
            }
        },

        getMovementLabel(type) {
            switch(type) {
                case 'ENTRADA_COMPRA': return 'Entrada por Compra';
                case 'VENTA_DIRECTA': return 'Venta Directa';
                case 'RESERVA_MIXTA': return 'Reserva Carrito Mixto';
                case 'SALIDA_VENTA': return 'Salida por Venta / Despacho';
                case 'SALIDA_DESPACHO': return 'Salida Despacho';
                case 'AJUSTE_MERMA': return 'Merma / Defecto';
                case 'SALIDA_COMBO_SUSCRIPCION': return 'Combo Suscripcion (BOM)';
                case 'AJUSTE_INVENTARIO': return 'Ajuste de Conteo';
                default: return type;
            }
        },

        // =========================================================================
        // MÃ‰TODOS DE PRIORIDAD 3: EDICIÃ“N INLINE & ACCIONES EN LOTE
        // =========================================================================

        // [P3.1] EdiciÃ³n RÃ¡pida en LÃ­nea
        startInlineEdit(sku, field, initialValue) {
            this.editingCell = { sku: sku, field: field };
            this.tempEditValue = initialValue;
            if (this.$nextTick) {
                this.$nextTick(() => {
                    const input = document.getElementById('inline-input-' + sku + '-' + field);
                    if (input) {
                        input.focus();
                        input.select();
                    }
                });
            }
        },

        saveInlineEdit(product, field) {
            if (!this.editingCell.sku || this.editingCell.sku !== product.sku) return;
            const val = parseFloat(this.tempEditValue);
            if (!isNaN(val) && val >= 0) {
                product[field] = val;
                this.savedCellFeedback = { sku: product.sku, field: field };
                setTimeout(() => {
                    if (this.savedCellFeedback.sku === product.sku && this.savedCellFeedback.field === field) {
                        this.savedCellFeedback = { sku: null, field: null };
                    }
                }, 2500);
                this.showToast(field.toUpperCase() + ' de ' + product.sku + ' actualizado a $' + val.toFixed(2) + '.', 'success');
            }
            this.editingCell = { sku: null, field: null };
            this.tempEditValue = null;
        },

        cancelInlineEdit() {
            this.editingCell = { sku: null, field: null };
            this.tempEditValue = null;
        },

        getMarginBadgeClass(percent) {
            if (percent >= 60) {
                return 'bg-emerald-50 text-emerald-700 border-emerald-200';
            } else if (percent >= 40) {
                return 'bg-blue-50 text-blue-700 border-blue-200';
            } else {
                return 'bg-amber-50 text-amber-700 border-amber-200';
            }
        },

        // [P3.2] Acciones en Lote
        toggleSelectAll(checked) {
            if (checked) {
                this.selectedSkus = this.filteredProducts.map(p => p.sku);
            } else {
                this.selectedSkus = [];
            }
        },

        isAllSelected() {
            if (!this.filteredProducts || this.filteredProducts.length === 0) return false;
            return this.filteredProducts.every(p => this.selectedSkus.includes(p.sku));
        },

        clearSelection() {
            this.selectedSkus = [];
        },

        exportSelectedToCsv() {
            if (this.selectedSkus.length === 0) {
                this.showToast('No has seleccionado ningÃºn producto para exportar.', 'warning');
                return;
            }
            const selectedProds = this.products.filter(p => this.selectedSkus.includes(p.sku));
            const headers = ['SKU', 'Nombre', 'Categoria', 'Tipo', 'Stock_Fisico', 'Stock_Comprometido', 'Stock_Disponible', 'Costo_MXN', 'PVP_MXN', 'Margen_Pct', 'Proveedor', 'Ubicacion'];
            const rows = selectedProds.map(p => [
                '"' + p.sku + '"',
                '"' + (p.name || '').replace(/"/g, '""') + '"',
                '"' + (p.category || '') + '"',
                '"' + (p.type || '') + '"',
                p.localStock || 0,
                p.committedStock || 0,
                this.getAvailableStock(p),
                p.cost || 0,
                p.pvp || 0,
                this.calculateMarginPercent(p.pvp, p.cost) + '%',
                '"' + (p.provider || p.supplier || 'Torzo B2B') + '"',
                '"' + (p.location || 'Rack Principal') + '"'
            ]);

            const csvContent = '\uFEFF' + [headers.join(','), ...rows.map(r => r.join(','))].join('\r\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', 'catalogo_seleccion_' + new Date().toISOString().slice(0, 10) + '.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);

            this.showToast(selectedProds.length + ' productos exportados a CSV exitosamente.', 'success');
        },

        // =========================================================================
        // MÃ‰TODOS DE EXPORTACIÃ“N A MICROSOFT EXCEL (.xlsx / .xls)
        // =========================================================================

        exportSelectedToExcel() {
            if (this.selectedSkus.length === 0) {
                this.showToast('No has seleccionado ningÃºn producto para exportar.', 'warning');
                return;
            }
            const selectedProds = this.products.filter(p => this.selectedSkus.includes(p.sku));
            const fileName = 'catalogo_seleccion_' + new Date().toISOString().slice(0, 10);

            // Mapeo exhaustivo con matriz completa de 6 tallas y motor de 3 estados
            const excelRows = selectedProds.map(p => ({
                'SKU': p.sku,
                'Producto': p.name || '',
                'CategorÃ­a': p.category || '',
                'Tipo': p.type === 'propio' ? 'Stock Propio' : 'Dropship Torzo',
                'Stock FÃ­sico': p.localStock || 0,
                'Stock Comprometido': p.committedStock || 0,
                'Stock Disponible': this.getAvailableStock(p),
                'Talla CH': (p.sizes && p.sizes.ch) || 0,
                'Talla M': (p.sizes && p.sizes.m) || 0,
                'Talla G': (p.sizes && p.sizes.g) || 0,
                'Talla XL': (p.sizes && p.sizes.xl) || 0,
                'Talla 2XL': (p.sizes && p.sizes['2xl']) || 0,
                'Talla 3XL': (p.sizes && p.sizes['3xl']) || 0,
                'Costo Unitario (MXN)': p.cost || 0,
                'PVP (MXN)': p.pvp || 0,
                'Margen (%)': this.calculateMarginPercent(p.pvp, p.cost) + '%',
                'Proveedor': p.provider || p.supplier || 'Torzo B2B',
                'UbicaciÃ³n': p.location || 'Rack Principal'
            }));

            // Si SheetJS (XLSX) estÃ¡ disponible (CDN en index.html)
            if (typeof window !== 'undefined' && window.XLSX) {
                const ws = window.XLSX.utils.json_to_sheet(excelRows);
                ws['!cols'] = [
                    { wch: 15 }, { wch: 32 }, { wch: 14 }, { wch: 16 },
                    { wch: 12 }, { wch: 18 }, { wch: 15 },
                    { wch: 9 }, { wch: 9 }, { wch: 9 }, { wch: 9 }, { wch: 10 }, { wch: 10 },
                    { wch: 18 }, { wch: 12 }, { wch: 12 }, { wch: 22 }, { wch: 18 }
                ];
                const wb = window.XLSX.utils.book_new();
                window.XLSX.utils.book_append_sheet(wb, ws, 'CatÃ¡logo Operativo');
                window.XLSX.writeFile(wb, fileName + '.xlsx');
            } else {
                // Fallback XML Spreadsheet para Excel sin requerir librerÃ­a externa
                this.downloadExcelHtml(excelRows, 'CatÃ¡logo Operativo', fileName + '.xls');
            }

            this.showToast(selectedProds.length + ' productos exportados a Excel exitosamente.', 'success');
        },

        exportPurchaseOrdersToExcel(targetSupplier = null) {
            const supplierFilter = targetSupplier || this.exportSupplierFilter || 'all';
            const orders = this.futurePurchaseOrders.filter(o => supplierFilter === 'all' || o.supplier === supplierFilter);
            const requests = this.customerOnDemandRequests.filter(r => supplierFilter === 'all' || r.supplier === supplierFilter);

            if (orders.length === 0 && requests.length === 0) {
                this.showToast('No hay Ã³rdenes ni solicitudes para exportar.', 'warning');
                return;
            }

            const fileName = 'ordenes_compra_' + (supplierFilter !== 'all' ? supplierFilter.replace(/\s+/g, '_') + '_' : '') + new Date().toISOString().slice(0, 10);

            const ordersData = orders.map(o => ({
                'ID Orden': o.id,
                'SKU': o.sku,
                'Producto': o.productName,
                'Proveedor': o.supplier,
                'Cant. Total': o.qty,
                'CH': (o.sizes && o.sizes.ch) || 0,
                'M': (o.sizes && o.sizes.m) || 0,
                'G': (o.sizes && o.sizes.g) || 0,
                'XL': (o.sizes && o.sizes.xl) || 0,
                '2XL': (o.sizes && o.sizes['2xl']) || 0,
                '3XL': (o.sizes && o.sizes['3xl']) || 0,
                'Costo Unit (MXN)': o.costUnit || 0,
                'Total (MXN)': o.totalCost || (o.qty * (o.costUnit || 0)),
                'Prioridad': o.priority || 'media',
                'Estado': o.status || 'Planificada',
                'Fecha': o.date || ''
            }));

            const requestsData = requests.map(r => ({
                'ID Solicitud': r.id,
                'Cliente': r.customer,
                'TelÃ©fono': r.phone || '',
                'SKU': r.sku,
                'Prenda': r.item,
                'Talla Requerida': r.size,
                'Proveedor': r.supplier,
                'Tipo Pedido': r.mixedCart ? 'Carrito Mixto (#ORD-4491)' : 'Directo On-Demand',
                'Estado': r.status,
                'Fecha': r.date
            }));

            if (typeof window !== 'undefined' && window.XLSX) {
                const wb = window.XLSX.utils.book_new();
                if (ordersData.length > 0) {
                    const wsOrders = window.XLSX.utils.json_to_sheet(ordersData);
                    window.XLSX.utils.book_append_sheet(wb, wsOrders, 'Compras Planificadas');
                }
                if (requestsData.length > 0) {
                    const wsRequests = window.XLSX.utils.json_to_sheet(requestsData);
                    window.XLSX.utils.book_append_sheet(wb, wsRequests, 'Solicitudes Clientes');
                }
                window.XLSX.writeFile(wb, fileName + '.xlsx');
            } else {
                const combinedData = ordersData.length > 0 ? ordersData : requestsData;
                this.downloadExcelHtml(combinedData, 'Ordenes de Compra', fileName + '.xls');
            }

            this.showToast('Ã“rdenes consolidadas exportadas a Excel exitosamente.', 'success');
        },

        // =========================================================================
        // MÃ‰TODOS DE PRIORIDAD 4: ROP / DOH, SCRAPING ALERTS & PARETO ABC
        // =========================================================================

        // [P4.1] Motor Predictivo ROP / DOH
        getDailySales(product) {
            const monthly = product.monthlySales || 30;
            return Math.round((monthly / 30) * 10) / 10;
        },

        getDOH(product) {
            const avail = this.getAvailableStock(product);
            const vmd = (product.monthlySales || 30) / 30;
            if (vmd <= 0) return 999;
            return Math.round(avail / vmd);
        },

        getROP(product) {
            const vmd = (product.monthlySales || 30) / 30;
            const leadTime = product.leadTimeDays || 4;
            const safety = product.safetyStock || 10;
            return Math.round((vmd * leadTime) + safety);
        },

        isUnderROP(product) {
            return this.getAvailableStock(product) <= this.getROP(product);
        },

        getDOHBadgeClass(doh) {
            if (doh <= 7) return 'bg-rose-50 text-rose-700 border-rose-200';
            if (doh <= 15) return 'bg-amber-50 text-amber-700 border-amber-200';
            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        },

        generateROPOrder(product) {
            if (this.isProductInPurchaseList(product.sku)) {
                this.showToast('El producto ' + product.sku + ' ya se encuentra en la lista de compras planificadas.', 'info');
                return;
            }
            const vmd = (product.monthlySales || 30) / 30;
            const needed = Math.max(25, Math.ceil((vmd * 30) - this.getAvailableStock(product)));
            // Curva textil con matriz completa de 6 tallas
            const ch = Math.round(needed * 0.15);
            const m = Math.round(needed * 0.35);
            const g = Math.round(needed * 0.30);
            const xl = Math.round(needed * 0.15);
            const xxl = Math.max(0, Math.round(needed * 0.03));
            const xxxl = Math.max(0, needed - (ch + m + g + xl + xxl));
            const cost = product.cost || 120;
            // Regla de criticidad ROP para asignaciÃ³n de prioridad inicial (editable posteriormente)
            const currentStock = product.stock !== undefined ? product.stock : (product.unitStock || 0);
            const ropThreshold = product.rop || 20;
            let dynamicPriority = 'media';
            if (currentStock <= 0) {
                dynamicPriority = 'critica';
            } else if (currentStock <= Math.floor(ropThreshold / 2)) {
                dynamicPriority = 'alta';
            } else {
                dynamicPriority = 'media';
            }

            const newOrder = {
                id: 'FP-ROP-' + String(this.futurePurchaseOrders.length + 1).padStart(2, '0'),
                sku: product.sku,
                productName: product.name,
                supplier: product.provider || product.supplier || 'Torzo B2B',
                qty: needed,
                sizes: { ch: ch, m: m, g: g, xl: xl, '2xl': xxl, '3xl': xxxl },
                costUnit: cost,
                totalCost: needed * cost,
                priority: dynamicPriority,
                status: 'Planificada (ROP AutomÃ¡tico)',
                date: new Date().toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })
            };
            this.futurePurchaseOrders.unshift(newOrder);
            this.showToast('Sugerencia ROP agregada a Compras: ' + needed + ' uds de ' + product.sku + '.', 'success');
        },

        openCreatePOForProduct(product) {
            this.generateROPOrder(product);
            this.activeTab = 'purchases';
            this.showToast('Redirigiendo a Ã“rdenes de Compra para ' + product.sku, 'info');
        },

        // [P4.2] MÃ©todos del Centro de Scraping Torzo Bot
        triggerScraperQuickCheck() {
            this.isScrapingNow = true;
            this.showToast('Torzo Bot: Consultando stock y variaciones en catÃ¡logo remoto...', 'info');
            setTimeout(() => {
                this.isScrapingNow = false;
                this.lastScrapeTime = 'ReciÃ©n sincronizado';
                this.showToast('Scraping completado: 7 mayoristas auditados, 3 alertas vigentes.', 'success');
            }, 1200);
        },

        resolveScraperAlert(alertId, action) {
            const alert = (this.scrapingAlerts || []).find(a => a.id === alertId);
            if (!alert) return;

            if (action === 'adjust_pvp') {
                const prod = (this.products || []).find(p => p.sku === alert.sku);
                if (prod) {
                    prod.cost = alert.newCost;
                    if (alert.suggestedPvp) prod.pvp = alert.suggestedPvp;
                    this.showToast('Costo y PVP de ' + prod.sku + ' actualizados para preservar margen comercial.', 'success');
                } else {
                    this.showToast('Costo de referencia de ' + alert.sku + ' actualizado a $' + alert.newCost + '.', 'success');
                }
            } else if (action === 'discard_variation') {
                this.showToast('Variacion de costo de ' + alert.sku + ' descartada. Se preserva costo anterior.', 'info');
            } else if (action === 'pause_size' || action === 'pause_variant') {
                this.showToast('Variante ' + (alert.affectedSize || 'M') + ' de ' + alert.sku + ' pausada preventivamente en catalogo web.', 'warning');
            } else if (action === 'sync_stock') {
                const prod = (this.products || []).find(p => p.sku === alert.sku);
                if (prod) {
                    prod.supplierStock = (prod.supplierStock || 0) + (alert.restockQty || 100);
                    this.showToast((alert.restockQty || 100) + ' unidades sincronizadas al stock mayorista de ' + prod.sku + '.', 'success');
                } else {
                    this.showToast('Stock remoto sincronizado satisfactoriamente.', 'success');
                }
            } else if (action === 'contact_vendor') {
                this.showToast('Mensaje de seguimiento y reclamo de rampa emitido para ' + alert.supplier + '.', 'info');
            } else if (action === 'reschedule_po') {
                this.showToast('Fecha estimada de rampa reprogramada para la orden ' + (alert.poCode || 'OC') + '.', 'success');
            }
            alert.resolved = true;
        },

        // [P4.3] MÃ©todos de Filtro Temporal y AnÃ¡lisis ABC (Pareto)
        setDashboardTimeRange(range) {
            this.dashboardTimeRange = range;
            const labels = {
                'today': 'Hoy',
                '7d': 'Ãšltimos 7 DÃ­as',
                '30d': 'Ãšltimos 30 DÃ­as',
                'year': 'Este AÃ±o (2026)',
                'all': 'HistÃ³rico Completo'
            };
            this.showToast('Dashboard filtrado para: ' + (labels[range] || range), 'info');
        },

        getAbcClass(product) {
            if (product.abcClass) return product.abcClass;
            const sales = product.monthlySales || (product.localStock || 10);
            if (sales >= 120) return 'A';
            if (sales >= 50) return 'B';
            return 'C';
        },

        getAbcBadgeClass(abc) {
            switch(abc) {
                case 'A': return 'bg-amber-100 text-amber-900 border-amber-300 font-extrabold shadow-2xs';
                case 'B': return 'bg-blue-50 text-blue-800 border-blue-200 font-bold';
                case 'C': return 'bg-slate-100 text-slate-600 border-slate-200 font-medium';
                default: return 'bg-slate-50 text-slate-600 border-slate-200';
            }
        },

        filterByAbcFromDashboard(abc) {
            this.abcFilter = abc;
            this.activeTab = 'inventory';
            this.showToast('Filtrando catÃ¡logo por Clase ABC: ' + abc, 'info');
        },

        downloadExcelHtml(rows, sheetName, filename) {
            if (!rows || rows.length === 0) return;
            const headers = Object.keys(rows[0]);
            let html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            html += '<head><meta charset="utf-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
            html += '<x:Name>' + sheetName + '</x:Name>';
            html += '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
            html += '<body style="font-family: Arial, sans-serif;">';
            html += '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse; font-size:12px;">';
            html += '<thead style="background-color: #0f172a; color: #ffffff; font-weight: bold;"><tr>';
            headers.forEach(h => {
                html += '<th style="padding: 8px 12px; border: 1px solid #334155; text-align: left;">' + h + '</th>';
            });
            html += '</tr></thead><tbody>';
            rows.forEach((row, idx) => {
                const bg = idx % 2 === 0 ? '#ffffff' : '#f8fafc';
                html += '<tr style="background-color: ' + bg + ';">';
                headers.forEach(h => {
                    const val = row[h] !== undefined && row[h] !== null ? row[h] : '';
                    const isNum = typeof val === 'number';
                    const align = isNum ? 'right' : 'left';
                    html += '<td style="padding: 6px 12px; border: 1px solid #e2e8f0; text-align: ' + align + ';">' + val + '</td>';
                });
                html += '</tr>';
            });
            html += '</tbody></table></body></html>';

            const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        },

        openBulkProviderModal() {
            if (this.selectedSkus.length === 0) return;
            this.bulkProviderModalOpen = true;
        },

        applyBulkProvider() {
            const targetProv = this.bulkTargetProvider;
            let count = 0;
            this.products.forEach(p => {
                if (this.selectedSkus.includes(p.sku)) {
                    p.provider = targetProv;
                    p.supplier = targetProv;
                    count++;
                }
            });
            this.bulkProviderModalOpen = false;
            this.showToast(count + ' productos reasignados a ' + targetProv + ' exitosamente.', 'success');
        },

        openBulkCategoryModal() {
            if (this.selectedSkus.length === 0) return;
            this.bulkCategoryModalOpen = true;
        },

        applyBulkCategory() {
            const targetCat = this.bulkTargetCategory;
            let count = 0;
            this.products.forEach(p => {
                if (this.selectedSkus.includes(p.sku)) {
                    p.category = targetCat;
                    count++;
                }
            });
            this.bulkCategoryModalOpen = false;
            this.showToast(count + ' productos reasignados a la categorÃ­a ' + targetCat + '.', 'success');
        },

        addSelectedToPurchases() {
            if (this.selectedSkus.length === 0) return;
            const selectedProds = this.products.filter(p => this.selectedSkus.includes(p.sku));
            let addedCount = 0;
            selectedProds.forEach(p => {
                if (!this.isProductInPurchaseList(p.sku)) {
                    const qty = 30;
                    const cost = p.cost || 120.00;
                    const newOrder = {
                        id: 'FP-2026-' + String(this.futurePurchaseOrders.length + 1).padStart(2, '0'),
                        sku: p.sku,
                        productName: p.name,
                        supplier: p.provider || p.supplier || 'Torzo B2B',
                        qty: qty,
                        sizes: { ch: 5, m: 12, g: 10, xl: 3, '2xl': 0, '3xl': 0 },
                        costUnit: cost,
                        totalCost: qty * cost,
                        priority: 'media',
                        status: 'Planificada',
                        date: new Date().toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })
                    };
                    this.futurePurchaseOrders.unshift(newOrder);
                    addedCount++;
                }
            });
            this.selectedSkus = [];
            this.showToast(addedCount + ' lotes aÃ±adidos a la Lista de Compras Planificadas.', 'success');
        },

        init() {
            if (typeof window !== 'undefined' && Array.isArray(window.serverInventoryProducts) && window.serverInventoryProducts.length > 0) {
                this.products = window.serverInventoryProducts;
            }
            if (typeof window !== 'undefined' && Array.isArray(window.serverSuppliers) && window.serverSuppliers.length > 0) {
                this.providers = window.serverSuppliers;
            }
            if (typeof window !== 'undefined' && Array.isArray(window.serverPurchaseOrders) && window.serverPurchaseOrders.length > 0) {
                this.activePurchaseOrders = window.serverPurchaseOrders;
            }
            if (typeof window !== 'undefined' && Array.isArray(window.serverFuturePurchaseOrders) && window.serverFuturePurchaseOrders.length > 0) {
                this.futurePurchaseOrders = window.serverFuturePurchaseOrders;
            }
            if (typeof window !== 'undefined' && Array.isArray(window.serverPurchaseKardex) && window.serverPurchaseKardex.length > 0) {
                this.purchaseKardexLog = window.serverPurchaseKardex;
            }
            if (typeof window !== 'undefined' && Array.isArray(window.serverCreditAgreements) && window.serverCreditAgreements.length > 0) {
                this.pendingCreditAgreements = window.serverCreditAgreements;
            }
            if (typeof window !== 'undefined' && Array.isArray(window.serverProductBundles) && window.serverProductBundles.length > 0) {
                this.productBundles = window.serverProductBundles;
            }
            if (typeof window !== 'undefined' && Array.isArray(window.serverCycleCounts) && window.serverCycleCounts.length > 0) {
                this.cycleCountHistory = window.serverCycleCounts;
            }
            if (typeof window !== 'undefined' && Array.isArray(window.serverSizeCurves) && window.serverSizeCurves.length > 0) {
                this.sizeCurves = window.serverSizeCurves;
                const def = this.sizeCurves.find(c => c.is_default) || this.sizeCurves[0];
                if (def) this.selectedCurveId = def.id;
            }
            if (typeof window !== 'undefined' && Array.isArray(window.serverFragranceVolumes) && window.serverFragranceVolumes.length > 0) {
                this.fragranceVolumes = window.serverFragranceVolumes;
            }
            if (typeof window !== 'undefined' && Array.isArray(window.serverCategories) && window.serverCategories.length > 0) {
                this.categoriesTree = window.serverCategories;
            }
            if (!this.isTabAllowed(this.activeTab)) {
                this.activeTab = 'inventory';
            }
            if (this.lastScrapeTime === 'Sin registros') {
                setTimeout(() => {
                    this.lastScrapeTime = 'Hace 14 min';
                }, 600);
            }
            this.initRevOpsMetrics();
            if (typeof window !== 'undefined') {
                window.inventoryAppInstance = this;
                window.recordOrderDispatchedStock = (payload) => {
                    this.recordOrderDispatch(payload);
                };
                window.recordSubscriptionBatchStock = (payload) => {
                    this.recordSubscriptionBatchFulfillment(payload);
                };
                window.cosmeticShadesMaster = this.cosmeticShades;
                window.openCosmeticShadesModal = () => this.openCosmeticShadesModal();
                window.openStockLocksModal = () => this.openStockLocksModal();
                if (typeof window.addEventListener === 'function') {
                    window.addEventListener('role-changed', () => {
                        if (!this.isTabAllowed(this.activeTab)) {
                            this.activeTab = 'inventory';
                        }
                    });
                    window.addEventListener('open-module-settings', (e) => {
                        const tab = (e && e.detail && e.detail.tab) ? e.detail.tab : 'fabrics';
                        this.openModuleSettings(tab);
                    });
                    window.addEventListener('open-cosmetic-shades-modal', () => {
                        this.openCosmeticShadesModal();
                    });
                    // TASK-INT-06: Listener para modal de carritos activos
                    window.addEventListener('open-stock-locks-modal', () => {
                        this.openStockLocksModal();
                    });
                    // Soporte de impresiÃ³n formal de Orden de Compra (PO PDF)
                    window.addEventListener('beforeprint', () => {
                        const targetInstance = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
                        if (targetInstance && targetInstance.poGeneratorModalOpen) {
                            document.body.classList.add('printing-po-doc');
                        }
                    });
                    window.addEventListener('afterprint', () => {
                        document.body.classList.remove('printing-po-doc');
                    });
                    // TASK-INT-06: Listeners para telemetrÃ­a de carritos simultÃ¡neos y reservas con TTL
                    window.addEventListener('stock-lock-acquired', (e) => {
                        if (e && e.detail) {
                            this.handleStockLockAcquired(e.detail);
                        }
                    });
                    window.addEventListener('stock-lock-released', (e) => {
                        if (e && e.detail) {
                            this.handleStockLockReleased(e.detail);
                        }
                    });
                    window.addEventListener('stock-lock-expired', (e) => {
                        if (e && e.detail) {
                            this.handleStockLockExpired(e.detail);
                        }
                    });
                    // TASK-INT-01: Listener para despachos de deep link
                    window.addEventListener('inventory-deep-link', (e) => {
                        if (e && e.detail) {
                            this.handleDeepLink(e.detail);
                        }
                    });
                    // TASK-INT-02: Listener para empaque y despacho de pedidos
                    window.addEventListener('order-dispatched', (e) => {
                        if (e && e.detail) {
                            this.recordOrderDispatch(e.detail);
                        }
                    });
                    window.addEventListener('inventory-order-dispatched', (e) => {
                        if (e && e.detail) {
                            this.recordOrderDispatch(e.detail);
                        }
                    });
                    // TASK-INT-03: Listener para liberacion masiva de lotes de suscripcion
                    window.addEventListener('subscription-batch-released', (e) => {
                        if (e && e.detail) {
                            this.recordSubscriptionBatchFulfillment(e.detail);
                        }
                    });
                    window.addEventListener('inventory-subscription-batch-released', (e) => {
                        if (e && e.detail) {
                            this.recordSubscriptionBatchFulfillment(e.detail);
                        }
                    });
                }
                // TASK-INT-02: Consumir cola de despachos pendientes e histÃ³ricos si existen
                if (window.__pendingOrderDispatches && Array.isArray(window.__pendingOrderDispatches)) {
                    while (window.__pendingOrderDispatches.length > 0) {
                        const pendingPayload = window.__pendingOrderDispatches.shift();
                        this.recordOrderDispatch(pendingPayload);
                    }
                }
                if (window.__executedOrderDispatches && Array.isArray(window.__executedOrderDispatches)) {
                    window.__executedOrderDispatches.forEach(d => {
                        if (!this.inventoryMovements.some(m => m.reference === d.orderId && m.type === 'SALIDA_VENTA')) {
                            this.recordOrderDispatch(d);
                        }
                    });
                }
                // TASK-INT-03: Consumir cola de lotes de suscripcion pendientes e historicos
                if (window.__pendingSubscriptionBatches && Array.isArray(window.__pendingSubscriptionBatches)) {
                    while (window.__pendingSubscriptionBatches.length > 0) {
                        const pendingBatch = window.__pendingSubscriptionBatches.shift();
                        this.recordSubscriptionBatchFulfillment(pendingBatch);
                    }
                }
                if (window.__executedSubscriptionBatches && Array.isArray(window.__executedSubscriptionBatches)) {
                    window.__executedSubscriptionBatches.forEach(b => {
                        if (!this.inventoryMovements.some(m => m.reference === b.batchCode && m.type === 'SALIDA_COMBO_SUSCRIPCION')) {
                            this.recordSubscriptionBatchFulfillment(b);
                        }
                    });
                }
            }
            // TASK-INT-01: Consumir deep link inicial si existe en memoria global
            if (typeof window !== 'undefined' && window.__inventoryDeepLink) {
                this.handleDeepLink(window.__inventoryDeepLink);
            }
            this.initApparelDefaults();
        },

        initApparelDefaults() {
            const fabricList = ['Modal Seamless', 'AlgodÃ³n Peinado', 'Malla / Mesh Transpirable', 'Elastano / Spandex Fit', 'Microfibra Velvet Touch', 'BambÃº Eco Air'];
            const colorList = [
                { name: 'Negro Profundo', hex: '#0f172a', type: 'solid' },
                { name: 'Blanco Ã“palo', hex: '#f8fafc', type: 'solid' },
                { name: 'Azul Marino Navy', hex: '#1e3a8a', type: 'solid' },
                { name: 'Galaxia Tornasol Holo', hex: 'linear-gradient(135deg, #6366f1 0%, #ec4899 50%, #f59e0b 100%)', type: 'iridescent' },
                { name: 'Aurora Tornasol Boreal', hex: 'linear-gradient(135deg, #10b981 0%, #06b6d4 50%, #3b82f6 100%)', type: 'iridescent' },
                { name: 'Gris Oxford', hex: '#475569', type: 'solid' },
                { name: 'Rojo BorgoÃ±a', hex: '#881337', type: 'solid' }
            ];
            const sectionList = ['basicos', 'disenador', 'temporada', 'general'];

            this.products.forEach((p, idx) => {
                if (p.fabricType === undefined) p.fabricType = fabricList[idx % fabricList.length];
                if (p.colorName === undefined) {
                    const c = colorList[idx % colorList.length];
                    p.colorName = c.name;
                    p.colorHex = c.hex;
                    p.colorType = c.type;
                }
                if (p.designType === undefined) p.designType = (idx % 3 === 1) ? 'con_diseno' : 'sin_diseno';
                if (p.websiteSection === undefined) p.websiteSection = sectionList[idx % sectionList.length];
                if (p.isPublished === undefined) p.isPublished = idx !== 2; // SKU 3 fuera de catÃ¡logo para demostrar
                if (p.brand === undefined) p.brand = p.type === 'dropship' ? 'Torzo B2B' : (idx % 2 === 0 ? 'ModaIntima Textil MX' : 'Marte RevOps Intimates');
                if (p.discountPct === undefined) p.discountPct = (idx === 0 || idx === 3) ? 15 : 0;
                if (p.regularPrice === undefined) p.regularPrice = p.pvp;
                if (!p.description) {
                    p.description = `Prenda ergonÃ³mica de alta calidad confeccionada con ${p.fabricType}. DiseÃ±ada para soporte anatÃ³mico Ã³ptimo, transpirabilidad duradera y tacto ultrasuave para uso cotidiano.`;
                }
            });
        },

        initRevOpsMetrics() {
            this.products.forEach(p => {
                if (p.monthlySales === undefined) {
                    if (['BX-MOD-001', 'JK-ATH-002', 'BX-COT-007', 'JK-PRO-010', 'BX-BAM-011'].includes(p.sku)) {
                        p.monthlySales = 190;
                        p.abcClass = 'A';
                    } else if (p.type === 'dropship') {
                        p.monthlySales = 28;
                        p.abcClass = 'C';
                    } else if (p.localStock > 180) {
                        p.monthlySales = 120;
                        p.abcClass = 'A';
                    } else if (p.localStock > 90) {
                        p.monthlySales = 60;
                        p.abcClass = 'B';
                    } else {
                        p.monthlySales = 22;
                        p.abcClass = 'C';
                    }
                }
                if (p.leadTimeDays === undefined) {
                    if (p.provider === 'Torzo B2B' || p.supplier === 'Torzo B2B' || (p.sku && p.sku.startsWith('TOR-'))) {
                        p.leadTimeDays = 3;
                    } else if (p.provider === 'ModaIntima Textil MX' || p.supplier === 'ModaIntima Textil MX') {
                        p.leadTimeDays = 5;
                    } else if (p.provider === 'Silk & Cotton Iberia SL' || p.supplier === 'Silk & Cotton Iberia SL') {
                        p.leadTimeDays = 10;
                    } else {
                        p.leadTimeDays = 4;
                    }
                }
                if (p.safetyStock === undefined) {
                    p.safetyStock = p.abcClass === 'A' ? 15 : (p.abcClass === 'B' ? 10 : 6);
                }
            });
        },

        // TASK-INT-01: MÃ©todos de GestiÃ³n de Saltos Profundos (Deep Links) Inter-Hub
        handleDeepLink(options = {}) {
            if (!options) return;
            if (options.tab) {
                this.activeTab = options.tab;
            }
            if (options.subTab && this.activeTab === 'purchases') {
                this.purchasesSubTab = options.subTab;
            }
            if (options.ropFilter) {
                this.ropFilter = options.ropFilter;
                this.deepLinkActive = true;
                this.deepLinkBannerText = options.bannerMessage || 'Filtro activo de salto profundo: Mostrando SKUs bajo Punto de Reorden (ROP)';
            }
            if (options.searchFilter !== undefined && options.searchFilter !== null && options.searchFilter !== '') {
                this.searchFilter = options.searchFilter;
                this.deepLinkActive = true;
                this.deepLinkBannerText = options.bannerMessage || `Filtro activo por bÃºsqueda: "${options.searchFilter}"`;
            }
            if (options.supplierFilter && options.supplierFilter !== 'all') {
                this.supplierFilter = options.supplierFilter;
                this.deepLinkActive = true;
                this.deepLinkBannerText = options.bannerMessage || `Filtro activo por proveedor: ${options.supplierFilter}`;
            }
            if (options.highlightSku) {
                this.highlightedSku = options.highlightSku;
                setTimeout(() => {
                    this.highlightedSku = null;
                }, 5000);
            }
            if (options.highlightAlert && this.activeTab === 'suppliers') {
                const alertMatch = this.scrapingAlerts.find(a => a.id === options.highlightAlert);
                if (alertMatch) {
                    this.deepLinkActive = true;
                    this.deepLinkBannerText = options.bannerMessage || `Alerta enfocada: ${alertMatch.message}`;
                }
            }
            if (options.bannerMessage && !this.deepLinkActive) {
                this.deepLinkActive = true;
                this.deepLinkBannerText = options.bannerMessage;
            }
            this.visibleCount = this.pageSize || 8;
        },

        clearDeepLink() {
            this.ropFilter = 'all';
            this.searchFilter = '';
            this.supplierFilter = 'all';
            this.deepLinkActive = false;
            this.deepLinkBannerText = '';
            this.highlightedSku = null;
            if (typeof window !== 'undefined') {
                window.__inventoryDeepLink = null;
            }
            this.visibleCount = this.pageSize || 8;
            if (typeof showToast === 'function') {
                showToast('CatÃ¡logo completo restablecido sin filtros');
            }
        },

        get displayedProducts() {
            return this.filteredProducts.slice(0, this.visibleCount);
        },

        get displayedKardexMovements() {
            return this.filteredMovements.slice(0, this.kardexVisibleCount);
        },

        loadMoreProducts() {
            if (this.visibleCount < this.filteredProducts.length) {
                this.isLoadingMore = true;
                setTimeout(() => {
                    this.visibleCount += this.pageSize;
                    this.isLoadingMore = false;
                    this.showToast('Lote de productos cargado');
                }, 180);
            }
        },

        loadMoreKardex() {
            if (this.kardexVisibleCount < this.filteredMovements.length) {
                this.kardexIsLoadingMore = true;
                setTimeout(() => {
                    this.kardexVisibleCount += this.kardexPageSize;
                    this.kardexIsLoadingMore = false;
                    this.showToast('Asientos de Kardex cargados');
                }, 180);
            }
        },

        hasActiveColFilters() {
            return (this.colFilters.sku && this.colFilters.sku.trim() !== '') ||
                   (this.colFilters.product && this.colFilters.product.trim() !== '') ||
                   this.colFilters.stockLocal !== 'all' ||
                   this.colFilters.stockProv !== 'all' ||
                   (this.colFilters.minPvp !== '' && this.colFilters.minPvp !== null) ||
                   (this.colFilters.maxCost !== '' && this.colFilters.maxCost !== null) ||
                   this.colFilters.margin !== 'all' ||
                   (this.colFilters.location && this.colFilters.location.trim() !== '') ||
                   this.colFilters.status !== 'all';
        },

        clearColFilters() {
            this.colFilters = {
                sku: '',
                product: '',
                stockLocal: 'all',
                stockProv: 'all',
                minPvp: '',
                maxCost: '',
                margin: 'all',
                location: '',
                status: 'all'
            };
            this.visibleCount = this.pageSize || 8;
            this.showToast('Filtros de columna restablecidos');
        },

        get filteredProducts() {
            const cf = this.colFilters || {};
            const colQuery = `${cf.sku || ''}|${cf.product || ''}|${cf.stockLocal || 'all'}|${cf.stockProv || 'all'}|${cf.minPvp ?? ''}|${cf.maxCost ?? ''}|${cf.margin || 'all'}|${cf.location || ''}|${cf.status || 'all'}`;
            const currentQuery = `${this.searchFilter}|${this.typeFilter}|${this.abcFilter}|${this.ropFilter}|${this.sanitaryFilter}|${colQuery}`;
            if (this._lastFilterQuery !== currentQuery) {
                this._lastFilterQuery = currentQuery;
                this.visibleCount = this.pageSize || 8;
            }

            return this.products.filter(item => {
                const q = (this.searchFilter || '').toLowerCase().trim();
                const matchesSearch = !q || item.name.toLowerCase().includes(q) || item.sku.toLowerCase().includes(q);
                const matchesType = this.typeFilter === 'all' || item.type === this.typeFilter;
                const matchesAbc = !this.abcFilter || this.abcFilter === 'all' || this.getAbcClass(item) === this.abcFilter;
                const matchesRop = !this.ropFilter || this.ropFilter === 'all' || (this.ropFilter === 'under_rop' && this.isUnderROP(item));
                
                let matchesPublication = true;
                if (this.publicationFilter === 'published') {
                    matchesPublication = item.isPublished === true;
                } else if (this.publicationFilter === 'hidden') {
                    matchesPublication = item.isPublished === false;
                }
                
                let matchesSanitary = true;
                if (this.sanitaryFilter === 'sanitary_only') {
                    matchesSanitary = !!item.requiresSanitaryControl;
                } else if (this.sanitaryFilter === 'expiring_soon') {
                    matchesSanitary = !!item.requiresSanitaryControl && this.getDaysUntilExpiration(item.expirationDate) <= 90;
                } else if (this.sanitaryFilter === 'critical') {
                    matchesSanitary = !!item.requiresSanitaryControl && this.getDaysUntilExpiration(item.expirationDate) <= 30;
                }

                // Filtros especÃ­ficos por columna (Regla 7)
                let matchesCol = true;
                if (this.colFilters) {
                    if (this.colFilters.sku && this.colFilters.sku.trim() !== '') {
                        const targetSku = this.colFilters.sku.toLowerCase().trim();
                        if (!item.sku.toLowerCase().includes(targetSku)) matchesCol = false;
                    }
                    if (matchesCol && this.colFilters.product && this.colFilters.product.trim() !== '') {
                        const targetProd = this.colFilters.product.toLowerCase().trim();
                        const inName = item.name.toLowerCase().includes(targetProd);
                        const inCat = (item.category || '').toLowerCase().includes(targetProd);
                        if (!inName && !inCat) matchesCol = false;
                    }
                    if (matchesCol && this.colFilters.stockLocal && this.colFilters.stockLocal !== 'all') {
                        const avail = this.getAvailableStock(item);
                        if (this.colFilters.stockLocal === 'under_rop') {
                            if (!this.isUnderROP(item)) matchesCol = false;
                        } else if (this.colFilters.stockLocal === 'in_stock') {
                            if (avail <= 0) matchesCol = false;
                        } else if (this.colFilters.stockLocal === 'zero') {
                            if (avail !== 0) matchesCol = false;
                        }
                    }
                    if (matchesCol && this.colFilters.stockProv && this.colFilters.stockProv !== 'all') {
                        if (this.colFilters.stockProv === 'available') {
                            if (!item.supplierStock || item.supplierStock <= 0) matchesCol = false;
                        } else if (this.colFilters.stockProv === 'none') {
                            if (item.supplierStock !== null && item.supplierStock > 0) matchesCol = false;
                        }
                    }
                    if (matchesCol && this.colFilters.minPvp !== '' && this.colFilters.minPvp !== null && !isNaN(Number(this.colFilters.minPvp))) {
                        if (Number(item.pvp || 0) < Number(this.colFilters.minPvp)) matchesCol = false;
                    }
                    if (matchesCol && this.colFilters.maxCost !== '' && this.colFilters.maxCost !== null && !isNaN(Number(this.colFilters.maxCost))) {
                        const cost = Number(item.weightedAverageCost || item.cost || 0);
                        if (cost > Number(this.colFilters.maxCost)) matchesCol = false;
                    }
                    if (matchesCol && this.colFilters.margin && this.colFilters.margin !== 'all') {
                        const m = this.calculateRealMarginPercent(item.pvp, item.weightedAverageCost || item.cost);
                        if (this.colFilters.margin === 'high' && m < 60) matchesCol = false;
                        else if (this.colFilters.margin === 'mid' && (m < 40 || m >= 60)) matchesCol = false;
                        else if (this.colFilters.margin === 'low' && m >= 40) matchesCol = false;
                    }
                    if (matchesCol && this.colFilters.location && this.colFilters.location.trim() !== '') {
                        const loc = (item.location || '').toLowerCase();
                        if (!loc.includes(this.colFilters.location.toLowerCase().trim())) matchesCol = false;
                    }
                    if (matchesCol && this.colFilters.status && this.colFilters.status !== 'all') {
                        if (item.status !== this.colFilters.status) matchesCol = false;
                    }
                }

                // TASK-INT-01: Filtro por Proveedor / Marca
                let matchesSupplier = true;
                if (this.supplierFilter && this.supplierFilter !== 'all') {
                    const s = this.supplierFilter.toLowerCase().trim();
                    matchesSupplier = (item.brand && item.brand.toLowerCase().includes(s)) ||
                                      (item.supplier && item.supplier.toLowerCase().includes(s)) ||
                                      (item.provider && item.provider.toLowerCase().includes(s));
                }

                return matchesSearch && matchesType && matchesAbc && matchesRop && matchesSanitary && matchesCol && matchesPublication && matchesSupplier;
            });
        },

        // Helper methods para Control Sanitario (TASK-INV-06)
        getDaysUntilExpiration(dateStr) {
            if (!dateStr) return 9999;
            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            const parts = String(dateStr).split('-');
            if (parts.length !== 3) return 9999;
            const target = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
            const diffMs = target.getTime() - today.getTime();
            return Math.ceil(diffMs / (1000 * 60 * 60 * 24));
        },

        getSanitaryStatus(product) {
            if (!product || !product.requiresSanitaryControl || !product.expirationDate) {
                return 'exempt';
            }
            const days = this.getDaysUntilExpiration(product.expirationDate);
            if (days <= 30) return 'critical';
            if (days <= 90) return 'warning';
            return 'healthy';
        },

        getSanitaryBadgeClass(product) {
            const status = this.getSanitaryStatus(product);
            if (status === 'critical') {
                return 'bg-rose-50 text-rose-700 border-rose-200';
            } else if (status === 'warning') {
                return 'bg-amber-50 text-amber-800 border-amber-200';
            } else if (status === 'healthy') {
                return 'bg-emerald-50 text-emerald-700 border-emerald-200';
            }
            return 'bg-slate-50 text-slate-600 border-slate-200';
        },

        getSanitaryBadgeText(product) {
            if (!product || !product.expirationDate) return 'Sin Caducidad';
            const days = this.getDaysUntilExpiration(product.expirationDate);
            if (days < 0) {
                return 'Vencido hace ' + Math.abs(days) + 'd';
            } else if (days === 0) {
                return 'Vence Hoy';
            } else if (days <= 30) {
                return 'Vence en ' + days + 'd (Critico)';
            } else if (days <= 90) {
                return 'Expira en ' + days + 'd';
            } else {
                return 'Cad: ' + product.expirationDate;
            }
        },

        // TASK-INV-09: MÃ©todos de GestiÃ³n y Renderizado de Tonos de CosmÃ©tica
        get activeCosmeticShades() {
            return (this.cosmeticShades || []).filter(s => s.is_active);
        },

        getShadeInfo(code) {
            if (!code) return null;
            const strCode = String(code);
            const cleanCode = strCode.replace(/^t0?/i, '').padStart(2, '0');
            const found = (this.cosmeticShades || []).find(s => 
                s.code === cleanCode || 
                s.code === strCode || 
                ('t' + s.code) === strCode.toLowerCase() ||
                ('t' + s.code.replace(/^0/, '')) === strCode.toLowerCase()
            );
            if (found) return found;
            return {
                code: cleanCode,
                name: 'Tono ' + cleanCode,
                hex_color: '#E8BEA2',
                undertone: 'neutral'
            };
        },

        getUndertoneBadgeClass(undertone) {
            if (undertone === 'warm') return 'undertone-badge-warm';
            if (undertone === 'cool') return 'undertone-badge-cool';
            return 'undertone-badge-neutral';
        },

        getUndertoneLabel(undertone) {
            if (undertone === 'warm') return 'CÃ¡lido';
            if (undertone === 'cool') return 'FrÃ­o';
            return 'Neutro';
        },

        openCosmeticShadesModal() {
            this.cosmeticShadesModal.isEditing = false;
            this.cosmeticShadesModal.editingId = null;
            const nextCode = String(this.cosmeticShades.length + 1).padStart(2, '0');
            this.cosmeticShadesModal.form = {
                code: nextCode,
                name: '',
                hex_color: '#E8BEA2',
                undertone: 'neutral',
                is_active: true
            };
            this.cosmeticShadesModal.isOpen = true;
        },

        closeCosmeticShadesModal() {
            this.cosmeticShadesModal.isOpen = false;
        },

        editCosmeticShade(shade) {
            this.cosmeticShadesModal.isEditing = true;
            this.cosmeticShadesModal.editingId = shade.id;
            this.cosmeticShadesModal.form = {
                code: shade.code,
                name: shade.name,
                hex_color: shade.hex_color,
                undertone: shade.undertone,
                is_active: Boolean(shade.is_active)
            };
            this.cosmeticShadesModal.isOpen = true;
        },

        saveCosmeticShade() {
            const form = this.cosmeticShadesModal.form;
            if (!form.code || !form.code.trim() || !form.name || !form.name.trim() || !form.hex_color) {
                this.showToast('Por favor completa el cÃ³digo, nombre comercial y color hexadecimal.', 'warning');
                return;
            }
            const cleanCode = form.code.trim();
            if (this.cosmeticShadesModal.isEditing) {
                const idx = this.cosmeticShades.findIndex(s => s.id === this.cosmeticShadesModal.editingId);
                if (idx !== -1) {
                    this.cosmeticShades[idx] = {
                        ...this.cosmeticShades[idx],
                        code: cleanCode,
                        name: form.name.trim(),
                        hex_color: form.hex_color.toUpperCase(),
                        undertone: form.undertone,
                        is_active: form.is_active
                    };
                    this.showToast(`Tono ${cleanCode} (${form.name}) actualizado correctamente.`, 'success');
                }
            } else {
                const exists = this.cosmeticShades.some(s => s.code.toLowerCase() === cleanCode.toLowerCase());
                if (exists) {
                    this.showToast(`El cÃ³digo de tono ${cleanCode} ya existe. Usa un cÃ³digo Ãºnico.`, 'warning');
                    return;
                }
                const newId = Math.max(0, ...this.cosmeticShades.map(s => s.id)) + 1;
                const newShade = {
                    id: newId,
                    code: cleanCode,
                    name: form.name.trim(),
                    hex_color: form.hex_color.toUpperCase(),
                    undertone: form.undertone,
                    sort_order: this.cosmeticShades.length + 1,
                    is_active: form.is_active
                };
                this.cosmeticShades.push(newShade);
                this.showToast(`Nuevo tono cosmÃ©tico ${cleanCode} (${form.name}) registrado exitosamente.`, 'success');
            }
            if (typeof window !== 'undefined') {
                window.cosmeticShadesMaster = this.cosmeticShades;
            }
            this.cosmeticShadesModal.isOpen = false;
        },

        toggleCosmeticShadeStatus(shade) {
            shade.is_active = !shade.is_active;
            if (typeof window !== 'undefined') {
                window.cosmeticShadesMaster = this.cosmeticShades;
            }
            this.showToast(`Tono ${shade.code} ${shade.is_active ? 'activado' : 'desactivado'}.`, 'info');
        },

        deleteCosmeticShade(shadeId) {
            const shade = this.cosmeticShades.find(s => s.id === shadeId);
            if (!shade) return;
            if (this.cosmeticShades.length <= 1) {
                this.showToast('Debe existir al menos un tono en el catÃ¡logo maestro.', 'warning');
                return;
            }
            this.cosmeticShades = this.cosmeticShades.filter(s => s.id !== shadeId);
            if (typeof window !== 'undefined') {
                window.cosmeticShadesMaster = this.cosmeticShades;
            }
            this.showToast(`Tono ${shade.code} (${shade.name}) eliminado correctamente.`, 'success');
        },

        async applyQuickStockAdjustment(item) {
            if (!item.adjustQty || item.adjustQty <= 0) {
                this.showToast('Ingresa una cantidad valida mayor a 0.', 'info');
                return;
            }
            const qty = parseInt(item.adjustQty);
            const type = item.adjustType || 'add';
            const reason = item.adjustReason || (type === 'add' ? 'Entrada rapida de almacen' : 'Salida rapida de almacen');

            if (type === 'sub' && item.localStock - qty < 0) {
                this.showToast(`Stock insuficiente: no puedes deducir ${qty} uds de ${item.localStock}.`, 'warning');
                return;
            }

            item.isAdjusting = true;

            try {
                const response = await fetch('/api/v1/inventory/adjust', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        sku: item.sku,
                        type: type,
                        quantity: qty,
                        reason: reason
                    })
                });

                const resData = await response.json();

                if (response.ok && resData.status === 'success') {
                    if (resData.data && typeof resData.data.new_local_stock !== 'undefined') {
                        item.localStock = resData.data.new_local_stock;
                        if (resData.data.sizes) {
                            item.sizes = resData.data.sizes;
                        }
                    } else {
                        item.localStock = (type === 'add') ? item.localStock + qty : item.localStock - qty;
                    }

                    if (item.localStock === 0) item.status = 'Agotado';
                    else if (item.localStock < 25) item.status = 'Bajo Stock';
                    else item.status = 'Saludable';

                    item.adjustQty = '';
                    item.adjustReason = '';
                    this.showToast(resData.message || 'Stock actualizado correctamente.', 'success');
                } else {
                    const errorMsg = resData.message || 'Error al registrar el ajuste de stock en el servidor.';
                    this.showToast(errorMsg, 'error');
                }
            } catch (err) {
                console.error('Error al registrar ajuste de stock:', err);
                this.showToast('Error de red al intentar registrar el ajuste de stock.', 'error');
            } finally {
                item.isAdjusting = false;
            }
        },

        rescrapeProduct(item) {
            item.isScraping = true;
            this.showToast(`Sincronizando con catÃ¡logo de Torzo para ${item.sku}...`, 'info');
            setTimeout(() => {
                item.isScraping = false;
                const newStock = Math.floor(Math.random() * 40) + 10;
                item.supplierStock = newStock;
                item.status = 'Sincronizado';
                this.lastScrapeTime = 'Hace unos instantes';
                this.showToast(`Scraping exitoso: ${item.sku} tiene ${newStock} unidades en Torzo.`, 'success');
            }, 1400);
        },

        
        // --- GESTIÃ“N DE FUTURAS COMPRAS CON PROVEEDOR ---
        purchaseSupplier: 'Torzo B2B',
        purchaseQty: 50,
        purchasePriority: 'alta',
        purchaseSizes: { ch: 10, m: 20, g: 15, xl: 5 },
        futurePurchaseOrders: [
            {
                id: 'FP-2026-01',
                sku: 'BX-MOD-001',
                productName: 'Boxer Trunks Modal Seamless',
                supplier: 'Silk & Cotton Iberia SL',
                qty: 120,
                sizes: { ch: 18, m: 50, g: 40, xl: 12 },
                costUnit: 145.00,
                totalCost: 17400.00,
                priority: 'alta',
                status: 'Planificada',
                date: '05 Sept, 2026',
                _editingSizes: false
            },
            {
                id: 'FP-2026-02',
                sku: 'JK-ATH-002',
                productName: 'Jockstrap Athletic Carbon Edition',
                supplier: 'Torzo B2B',
                qty: 60,
                sizes: { ch: 10, m: 25, g: 18, xl: 7 },
                costUnit: 115.00,
                totalCost: 6900.00,
                priority: 'media',
                status: 'Planificada',
                date: '04 Sept, 2026',
                _editingSizes: false
            },
            {
                id: 'FP-2026-03',
                sku: 'TOR-DRP-003',
                productName: 'Torzo Mesh Breathable Trunk',
                supplier: 'Torzo B2B',
                qty: 48,
                sizes: { ch: 8, m: 20, g: 15, xl: 5 },
                costUnit: 210.00,
                totalCost: 10080.00,
                priority: 'alta',
                status: 'Planificada',
                date: '05 Sept, 2026',
                _editingSizes: false
            }
        ],

        // [TASK-INV-16] Variables reactivas de la bandeja de Compras, PO y Kardex
        inventoryCurrentScreen: 'main', // 'main' | 'settings' (REGLA: Todos los ajustes se muestran como pantalla completa)
        purchasesViewMode: 'tree', // 'tree' | 'flat'
        onDemandStatusFilter: 'todas', // 'todas' | 'pendientes' | 'en_compra' | 'agotadas'
        poGeneratorModalOpen: false,
        currentPoData: null,
        emailDispatchModalOpen: false,
        emailDispatchData: null,
        purchaseKardexFilter: 'all',
        purchaseKardexSearch: '',
        onDemandRejectModalOpen: false,
        selectedOnDemandReq: null,
        onDemandRejectReason: 'Agotado en taller sin fecha de resurtido',
        sizeEditModalOpen: false,
        selectedOrderForSizes: null,
        pendingCreditAgreements: (typeof window !== 'undefined' && Array.isArray(window.serverCreditAgreements) && window.serverCreditAgreements.length > 0)
            ? window.serverCreditAgreements
            : [
            {
                supplier: 'Silk & Cotton Iberia SL',
                ref: 'NC-SILK-2026-03',
                amount: 1160.00,
                reason: 'Merma textil por costuras defectuosas en 8 prendas del lote previo FP-2026-01',
                date: '18 Sept, 2026',
                status: 'Pendiente de Aplicar'
            },
            {
                supplier: 'Silk & Cotton Iberia SL',
                ref: 'NC-SILK-2026-01',
                amount: 540.00,
                reason: 'PenalizaciÃ³n acordada por demora de 4 dÃ­as hÃ¡biles en recolecciÃ³n de transporte',
                date: '02 Sept, 2026',
                status: 'Pendiente de Aplicar'
            },
            {
                supplier: 'Torzo B2B',
                ref: 'NC-TORZO-2026-012',
                amount: 850.00,
                reason: 'Faltante en muelle rampa Inbound 02 verificado contra remisiÃ³n #TRZ-889',
                date: '14 Sept, 2026',
                status: 'Pendiente de Aplicar'
            },
            {
                supplier: 'Torzo B2B',
                ref: 'NC-TORZO-2026-007',
                amount: 1420.00,
                reason: 'BonificaciÃ³n por volumen trimestral y compensaciÃ³n por merma de tintura en lote anterior',
                date: '28 Ago, 2026',
                status: 'Pendiente de Aplicar'
            },
            {
                supplier: 'ModaIntima Textil MX',
                ref: 'NC-MODA-2026-001',
                amount: 600.00,
                reason: 'BonificaciÃ³n comercial acordada por retraso de transporte fletero',
                date: '10 Sept, 2026',
                status: 'Pendiente de Aplicar'
            },
            {
                supplier: 'ModaIntima Textil MX',
                ref: 'NC-MODA-2026-002',
                amount: 380.00,
                reason: 'Discrepancia en curva de tallas (faltante de 3 piezas XL en entrega previa)',
                date: '05 Sept, 2026',
                status: 'Pendiente de Aplicar'
            }
        ],
        carrierTrackingModalOpen: false,
        selectedTrackingOrder: null,
        purchaseKardexLog: [
            {
                id: 'KDX-PUR-2026-001',
                timestamp: '05 Sept, 2026 12:45',
                type: 'PO_EMITIDA',
                typeLabel: 'EmisiÃ³n de PO',
                badgeClass: 'bg-indigo-50 text-indigo-700 border-indigo-200',
                poNumber: 'PO-2026-0041',
                supplier: 'Torzo B2B',
                sku: 'TOR-DRP-003',
                productName: 'Torzo Mesh Breathable Trunk',
                deltaQty: '+48 uds',
                totalAmount: 10080.00,
                user: 'Elena Compras',
                notes: 'GeneraciÃ³n formal de PO enviada a taller con entrega programada en Rampa Central CDMX.'
            },
            {
                id: 'KDX-PUR-2026-002',
                timestamp: '05 Sept, 2026 11:30',
                type: 'ON_DEMAND_CONVERTIDO',
                typeLabel: 'On-Demand a PO',
                badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                poNumber: 'REQ-2026-083',
                supplier: 'Silk & Cotton Iberia SL',
                sku: 'BX-MOD-001',
                productName: 'Boxer Trunks Modal Seamless',
                deltaQty: '+3 uds',
                totalAmount: 435.00,
                user: 'Elena Compras',
                notes: 'Solicitud On-Demand de Roberto Garza confirmada y sumada a compras planificadas.'
            },
            {
                id: 'KDX-PUR-2026-003',
                timestamp: '04 Sept, 2026 17:15',
                type: 'AJUSTE_CANTIDAD',
                typeLabel: 'Ajuste de Unidades',
                badgeClass: 'bg-amber-50 text-amber-700 border-amber-200',
                poNumber: 'FP-2026-02',
                supplier: 'Torzo B2B',
                sku: 'JK-ATH-002',
                productName: 'Jockstrap Athletic Carbon Edition',
                deltaQty: '40 -> 60 uds (+20)',
                totalAmount: 6900.00,
                user: 'Elena Compras',
                notes: 'Incremento de lote para cumplir MOQ de 60 piezas con flete bonificado.'
            },
            {
                id: 'KDX-PUR-2026-004',
                timestamp: '04 Sept, 2026 14:00',
                type: 'RECEPCION_RAMPA',
                typeLabel: 'RecepciÃ³n en Rampa',
                badgeClass: 'bg-blue-50 text-blue-700 border-blue-200',
                poNumber: 'PO-2026-0038',
                supplier: 'Torzo B2B',
                sku: 'JK-ATH-002',
                productName: 'Jockstrap Athletic Carbon Edition',
                deltaQty: '+28 uds ingresadas',
                totalAmount: 3245.00,
                user: 'Carlos AlmacÃ©n',
                notes: 'Ingreso fÃ­sico a rack A-02 conforme conteo ciego validado.'
            }
        ],

        // [P2.1] ColecciÃ³n reactiva de Ã“rdenes de Compra Activas (En TrÃ¡nsito y AlmacÃ©n)
        activePurchaseOrders: [
            {
                id: 'OC-2026-085',
                supplier: 'Torzo B2B',
                carrier: 'FedEx Express (GuÃ­a #FX-882910-MX)',
                eta: 'Hoy / En Reparto',
                sku: 'JK-ATH-002',
                productName: 'Jockstrap Athletic Carbon Edition',
                qty: 28,
                sizes: { ch: 4, m: 12, g: 10, xl: 2, '2xl': 0, '3xl': 0 },
                unitCost: 115.89,
                totalCost: 3245.00,
                date: '03 Sept, 2026',
                status: 'En Camino',
                linkedCartId: null,
                linkedCustomer: null
            },
            {
                id: 'OC-2026-088',
                supplier: 'Torzo B2B',
                carrier: 'DHL Express (GuÃ­a #DHL-994012-MX)',
                eta: 'Arribo en Sucursal',
                sku: 'TOR-DRP-003',
                productName: 'Torzo Mesh Breathable Trunk',
                qty: 15,
                sizes: { ch: 2, m: 6, g: 5, xl: 2, '2xl': 0, '3xl': 0 },
                unitCost: 210.00,
                totalCost: 3150.00,
                date: '04 Sept, 2026',
                status: 'En Camino',
                linkedCartId: '#ORD-4491',
                linkedCustomer: 'SofÃ­a AlbarrÃ¡n'
            },
            {
                id: 'OC-2026-089',
                supplier: 'Torzo B2B',
                carrier: 'Estafeta Terrestre (GuÃ­a #EST-440192-MX)',
                eta: '3 - 5 dÃ­as hÃ¡biles',
                sku: 'TOR-DRP-006',
                productName: 'Boxer Mesh Breathable UltraVent',
                qty: 45,
                sizes: { ch: 6, m: 18, g: 15, xl: 4, '2xl': 2, '3xl': 0 },
                unitCost: 115.00,
                totalCost: 5175.00,
                date: '05 Sept, 2026',
                status: 'Pendiente ConfirmaciÃ³n',
                linkedCartId: null,
                linkedCustomer: null
            }
        ],

        // [P2.1] Estado para Modal de RecepciÃ³n Inbound
        receiveOrderModalOpen: false,
        receivingOrder: null,
        receivingSizes: { ch: 0, m: 0, g: 0, xl: 0, '2xl': 0, '3xl': 0 },
        receivingNotes: 'Cotejo fÃ­sico conforme. Empaque sellado de origen.',
        mixedCartLiberated: { show: false, orderId: '', customer: '', item: '', date: '' },

        // [P2.2] Estado para Generador de Ã“rdenes WhatsApp
        orderExportModalOpen: false,
        exportSupplierFilter: 'Torzo B2B',
        exportIncludeOnDemand: true,
        exportIncludePlanned: true,
        exportCopiedToast: false,

        // [P2.3] Estado para Kardex & AuditorÃ­a Multicanal
        kardexDrawerOpen: false,
        kardexSearch: '',
        kardexFilterType: 'all',

        // [P3.1] Estado para EdiciÃ³n RÃ¡pida en LÃ­nea (Inline Quick Edit)
        editingCell: { sku: null, field: null },
        tempEditValue: null,
        savedCellFeedback: { sku: null, field: null },

        // [P3.2] Estado para Acciones en Lote (Bulk Actions)
        selectedSkus: [],
        bulkProviderModalOpen: false,
        bulkCategoryModalOpen: false,
        bulkTargetProvider: 'Torzo B2B',
        bulkTargetCategory: 'Boxers',

        // [TASK-INV-05] Generador de Etiquetas Termicas (Code128 / QR) en 1 Clic
        thermalLabelModal: {
            isOpen: false,
            sku: '',
            productName: '',
            category: '',
            variantType: 'size', // 'size' | 'shade' | 'volume' | 'unit'
            selectedVariant: 'M',
            availableVariants: ['CH', 'M', 'G', 'XL', '2XL', '3XL'],
            location: 'A-01-B',
            pvp: 0,
            copies: 1,
            labelFormat: '50x30', // '50x30' | '100x50'
            includeQr: true,
            isBatch: false,
            batchTitle: '',
            batchItems: [],
            batchNumber: '',
            expirationDate: '',
            paoMonths: 0,
            requiresSanitaryControl: false
        },

        // [P4.1 & P4.3] Estado RevOps Predictivo: ROP, DOH y Filtros de Dashboard
        dashboardTimeRange: '30d', // 'today' | '7d' | '30d' | 'year' | 'all'
        abcFilter: 'all',          // 'all' | 'A' | 'B' | 'C'
        ropFilter: 'all',          // 'all' | 'under_rop'
        sanitaryFilter: 'all',     // 'all' | 'sanitary_only' | 'expiring_soon' | 'critical'
        receivingRequiresSanitary: false,
        receivingBatchNumber: '',
        receivingExpirationDate: '',
        receivingPaoMonths: 12,
        drawerBatchNumber: '',
        drawerExpirationDate: '',
        drawerPaoMonths: 12,
        drawerRequiresSanitaryControl: false,

        // Centro de AuditorÃ­a Operativa & Discrepancias B2B (Propuesta 1)
        isScrapingNow: false,
        scrapingAlerts: [
            {
                id: 'ALT-SCR-001',
                category: 'costs',
                type: 'cost_increase',
                severity: 'warning',
                sku: 'TOR-DRP-006',
                productName: 'Torzo Neon Glow Athletic Jock',
                supplier: 'Torzo B2B',
                oldCost: 195.00,
                newCost: 235.00,
                suggestedPvp: 520.00,
                impact: '+$40.00 en costo (+20.5%)',
                message: 'Torzo B2B actualizo su costo mayorista de $195.00 a $235.00 (+20.5%). El margen comercial cae del 57% al 48%.',
                resolved: false
            },
            {
                id: 'ALT-SCR-002',
                category: 'stockouts',
                type: 'size_stockout',
                severity: 'danger',
                sku: 'TOR-DRP-003',
                productName: 'Torzo Mesh Breathable Trunk',
                supplier: 'Torzo B2B',
                affectedSize: 'M',
                impact: '-45 pzs en fabrica origen',
                message: 'Talla M agotada en fabrica mayorista. Afecta pedidos on-demand entrantes y despachos combinados.',
                resolved: false
            },
            {
                id: 'ALT-SCR-003',
                category: 'stockouts',
                type: 'restock_available',
                severity: 'success',
                sku: 'BX-MOD-001',
                productName: 'Boxer Trunks Modal Seamless',
                supplier: 'Torzo B2B',
                restockQty: 150,
                affectedSize: 'G',
                impact: '+150 pzs reabastecidas',
                message: 'Lote de 150 unidades de Talla G reingreso al inventario central de bodega externa.',
                resolved: false
            },
            {
                id: 'ALT-SCR-004',
                category: 'sla_delays',
                type: 'sla_delay',
                severity: 'danger',
                sku: 'BRA-MOD-002',
                productName: 'Bralette Encaje Valenciana',
                supplier: 'ModaIntima MX',
                poCode: 'OC-2026-088',
                delayDays: 3,
                impact: 'Demora +3 dias en rampa',
                message: 'Orden OC-2026-088 de 350 piezas de maquila textil presenta 3 dias de demora respecto a la fecha pactada.',
                resolved: false
            },
            {
                id: 'ALT-SCR-005',
                category: 'costs',
                type: 'cost_decrease',
                severity: 'info',
                sku: 'SLK-ROB-004',
                productName: 'Bata Satin Seda Iberia',
                supplier: 'Silk & Cotton Iberia',
                oldCost: 480.00,
                newCost: 425.00,
                suggestedPvp: 950.00,
                impact: '-$55.00 descuento maquila',
                message: 'Silk & Cotton aplico descuento comercial por volumen en lote de seda natural. Margen proyectado al 55.2%.',
                resolved: false
            }
        ],
        inventoryMovements: [
            {
                id: 'MOV-2026-101',
                timestamp: '06 Sept 2026, 09:15',
                type: 'VENTA_DIRECTA',
                sku: 'BX-MOD-001',
                productName: 'Boxer Trunks Modal Seamless',
                sizes: { m: 2 },
                delta: -2,
                stockBefore: 286,
                stockAfter: 284,
                reference: 'Pedido #ORD-4510',
                user: 'Tienda Online (Stripe)',
                note: 'Compra directa con stock local pagada'
            },
            {
                id: 'MOV-2026-100',
                timestamp: '06 Sept 2026, 08:30',
                type: 'RESERVA_MIXTA',
                sku: 'BX-MOD-001',
                productName: 'Boxer Trunks Modal Seamless',
                sizes: { m: 1 },
                delta: 0,
                stockBefore: 284,
                stockAfter: 284,
                reference: 'Pedido Mixto #ORD-4491',
                user: 'Checkout Web',
                note: 'Prenda fÃ­sica apartada en rack esperando mercancÃ­a de Torzo'
            },
            {
                id: 'MOV-2026-099',
                timestamp: '05 Sept 2026, 17:40',
                type: 'ENTRADA_COMPRA',
                sku: 'BX-COT-007',
                productName: 'Trunk AlgodÃ³n OrgÃ¡nico Pack x3',
                sizes: { ch: 5, m: 15, g: 15, xl: 5 },
                delta: 40,
                stockBefore: 95,
                stockAfter: 135,
                reference: 'Orden Compra #OC-2026-079 (DHL)',
                user: 'Carlos M. (AlmacÃ©n Central)',
                note: 'Ingreso por reabastecimiento programado de temporada'
            },
            {
                id: 'MOV-2026-098',
                timestamp: '05 Sept 2026, 14:10',
                type: 'AJUSTE_MERMA',
                sku: 'JK-ATH-002',
                productName: 'Jockstrap Athletic Carbon Edition',
                sizes: { g: 1 },
                delta: -1,
                stockBefore: 43,
                stockAfter: 42,
                reference: 'TKT-MER-014',
                user: 'Control de Calidad',
                note: 'Descostura en elÃ¡stico reportada en inspecciÃ³n previa a empaque'
            },
            {
                id: 'MOV-2026-097',
                timestamp: '05 Sept 2026, 11:25',
                type: 'VENTA_DIRECTA',
                sku: 'TG-SLK-004',
                productName: 'Tanga ClÃ¡sica Hilos de Seda',
                sizes: { ch: 1, m: 2 },
                delta: -3,
                stockBefore: 61,
                stockAfter: 58,
                reference: 'Pedido #ORD-4482',
                user: 'Tienda Online (Mercado Pago)',
                note: 'Venta directa liquidada'
            },
            {
                id: 'MOV-2026-096',
                timestamp: '04 Sept 2026, 16:50',
                type: 'AJUSTE_INVENTARIO',
                sku: 'BK-FIT-005',
                productName: 'Bikini Microfibra Fit Comfort',
                sizes: { m: 2 },
                delta: 2,
                stockBefore: 110,
                stockAfter: 112,
                reference: 'AUD-INV-SEMANAL',
                user: 'Supervisor de AlmacÃ©n',
                note: 'Ajuste positivo por conteo fÃ­sico en gaveta B-04'
            },
            {
                id: 'MOV-2026-095',
                timestamp: '04 Sept 2026, 12:00',
                type: 'SALIDA_DESPACHO',
                sku: 'JK-PRO-010',
                productName: 'Jockstrap Pro Performance Black',
                sizes: { g: 2, xl: 1 },
                delta: -3,
                stockBefore: 38,
                stockAfter: 35,
                reference: 'GuÃ­a DHL #981273-MX',
                user: 'LogÃ­stica Outbound',
                note: 'Despacho expedido hacia Guadalajara'
            },
            {
                id: 'MOV-2026-094',
                timestamp: '03 Sept 2026, 15:30',
                type: 'ENTRADA_COMPRA',
                sku: 'TG-EXT-008',
                productName: 'Tanga Deportiva AirVent Tech',
                sizes: { ch: 5, m: 10, g: 10 },
                delta: 25,
                stockBefore: 50,
                stockAfter: 75,
                reference: 'Orden Compra #OC-2026-074',
                user: 'Carlos M. (AlmacÃ©n Central)',
                note: 'RecepciÃ³n conforme de pedido a Balam Textil'
            }
        ],

        // BANDEJA ON-DEMAND: Solicitudes originadas en Carritos Bajo Pedido y Mixtos
        customerOnDemandRequests: [
            {
                id: 'REQ-2026-081',
                customerName: 'Carlos Mendoza',
                customerPhone: '+52 55 1234 5678',
                cartType: 'solicitud_pedido',
                cartLabel: 'Carrito Solicitud Directa',
                sku: 'BX-DEP-009',
                productName: 'BÃ³xer Deportivo Seamless Dry',
                size: 'G',
                qty: 2,
                supplier: 'Torzo B2B',
                costUnit: 145.00,
                totalCost: 290.00,
                slaMinutes: 35,
                verificationStatus: 'pendiente', // 'pendiente' | 'confirmado' | 'sin_stock'
                paymentSent: false,
                date: 'Hace 25 min'
            },
            {
                id: 'REQ-2026-082',
                customerName: 'SofÃ­a AlbarrÃ¡n',
                customerPhone: '+52 33 9876 5432',
                cartType: 'mixto_linea_proveedor',
                cartLabel: 'Carrito Mixto #ORD-4491 (LÃ­nea On-Demand)',
                sku: 'TOR-DRP-003',
                productName: 'Torzo Mesh Breathable Trunk',
                size: 'M',
                qty: 1,
                supplier: 'Torzo B2B',
                costUnit: 210.00,
                totalCost: 210.00,
                slaMinutes: 75,
                verificationStatus: 'pendiente',
                paymentSent: false,
                date: 'Hace 45 min'
            },
            {
                id: 'REQ-2026-083',
                customerName: 'Roberto Garza',
                customerPhone: '+52 81 2233 4455',
                cartType: 'solicitud_pedido',
                cartLabel: 'Carrito Solicitud Directa',
                sku: 'BX-MOD-001',
                productName: 'Boxer Trunks Modal Seamless',
                size: 'M',
                qty: 3,
                supplier: 'Silk & Cotton Iberia SL',
                costUnit: 145.00,
                totalCost: 435.00,
                slaMinutes: 105,
                verificationStatus: 'confirmado',
                paymentSent: true,
                date: 'Hace 1h 10m'
            },
            {
                id: 'REQ-2026-084',
                customerName: 'Brenda Soto',
                customerPhone: '+52 55 8877 6655',
                cartType: 'solicitud_pedido',
                cartLabel: 'Carrito Solicitud Directa',
                sku: 'BK-SEA-007',
                productName: 'Bikini Microfibra Seamless',
                size: 'CH',
                qty: 2,
                supplier: 'ModaIntima Textil MX',
                costUnit: 105.00,
                totalCost: 210.00,
                slaMinutes: 130,
                verificationStatus: 'pendiente',
                paymentSent: false,
                date: 'Hace 1h 35m'
            }
        ],

        calculatePurchaseTotal() {
            const qty = parseInt(this.purchaseQty) || 0;
            const cost = parseFloat(this.drawerCost) || 0;
            return qty * cost;
        },

        // ====================================================================
        // TASK-INT-06: GESTOR DE CARRITOS SIMULTÃNEOS Y RESERVA TEMPORAL (TTL)
        // ====================================================================
        get activeStockLocks() {
            const now = Date.now();
            return (this.stockLocks || []).filter(l => l.expiresAt > now);
        },

        get activeStockLocksCount() {
            return this.activeStockLocks.length;
        },

        get activeLockedUnitsTotal() {
            return this.activeStockLocks.reduce((sum, l) => sum + (l.quantity || 0), 0);
        },

        cleanExpiredLocks() {
            const now = Date.now();
            if (this.stockLocks && this.stockLocks.length > 0) {
                this.stockLocks = this.stockLocks.filter(l => l.expiresAt > now);
            }
        },

        openStockLocksModal() {
            this.cleanExpiredLocks();
            this.stockLocksModalOpen = true;
        },

        closeStockLocksModal() {
            this.stockLocksModalOpen = false;
        },

        forceReleaseLock(lockId) {
            const idx = (this.stockLocks || []).findIndex(l => l.id === lockId);
            if (idx !== -1) {
                const released = this.stockLocks.splice(idx, 1)[0];
                if (typeof this.showToast === 'function') {
                    this.showToast('Reserva #' + lockId + ' liberada forzadamente al inventario.', 'info');
                }
                window.dispatchEvent(new CustomEvent('stock-lock-force-released', {
                    detail: { lockId, sessionId: released.sessionId, sku: released.sku, quantity: released.quantity }
                }));
            }
        },

        releaseAllStockLocks() {
            const count = (this.stockLocks || []).length;
            this.stockLocks = [];
            if (typeof this.showToast === 'function') {
                this.showToast('Se liberaron ' + count + ' reservas de carrito al stock disponible.', 'success');
            }
            window.dispatchEvent(new CustomEvent('stock-locks-cleared-all', { detail: { count } }));
        },

        getRemainingLockSeconds(expiresAt) {
            return Math.max(0, Math.round((expiresAt - Date.now()) / 1000));
        },

        formatLockTimer(expiresAt) {
            const sec = this.getRemainingLockSeconds(expiresAt);
            const m = Math.floor(sec / 60);
            const s = sec % 60;
            return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        },

        handleStockLockAcquired(detail) {
            const { sessionId, items, expiresAt } = detail;
            if (!sessionId || !items || !Array.isArray(items)) return;
            this.stockLocks = (this.stockLocks || []).filter(l => l.sessionId !== sessionId);
            items.forEach((item, idx) => {
                this.stockLocks.push({
                    id: 'lock_' + sessionId + '_' + idx,
                    sessionId: sessionId,
                    sku: item.sku,
                    name: item.name || ('Prenda ' + item.sku),
                    size: item.size || 'M',
                    quantity: parseInt(item.quantity, 10) || 1,
                    expiresAt: expiresAt || (Date.now() + 300000),
                    ipAddress: 'Local Storefront',
                    userAgent: navigator.userAgent ? navigator.userAgent.substring(0, 40) : 'Storefront Client',
                    createdAt: Date.now()
                });
            });
            this.cleanExpiredLocks();
        },

        handleStockLockReleased(detail) {
            const { sessionId } = detail;
            if (!sessionId) return;
            this.stockLocks = (this.stockLocks || []).filter(l => l.sessionId !== sessionId);
        },

        handleStockLockExpired(detail) {
            const { sessionId } = detail;
            if (!sessionId) return;
            this.stockLocks = (this.stockLocks || []).filter(l => l.sessionId !== sessionId);
            if (typeof this.showToast === 'function') {
                this.showToast('Reserva de carrito ' + sessionId + ' expirada (TTL 5m). Stock liberado.', 'info');
            }
        },

        getLockedStock(product) {
            if (!product) return 0;
            this.cleanExpiredLocks();
            const sku = product.sku;
            return (this.stockLocks || [])
                .filter(lock => lock.sku === sku && lock.expiresAt > Date.now())
                .reduce((sum, lock) => sum + (lock.quantity || 0), 0);
        },

        getLockedSize(product, sizeKey) {
            if (!product) return 0;
            this.cleanExpiredLocks();
            const sku = product.sku;
            const sizeNorm = String(sizeKey).toLowerCase();
            return (this.stockLocks || [])
                .filter(lock => lock.sku === sku && String(lock.size).toLowerCase() === sizeNorm && lock.expiresAt > Date.now())
                .reduce((sum, lock) => sum + (lock.quantity || 0), 0);
        },

        // Motor de Stock de 3 Estados (FÃ³rmula Tripartita: FÃ­sico - Comprometido - Bloqueado en Carrito = Disponible)
        getAvailableStock(product) {
            if (!product) return 0;
            const physical = product.localStock || 0;
            const committed = product.committedStock || 0;
            const locked = this.getLockedStock(product);
            return Math.max(0, physical - committed - locked);
        },

        getAvailableSize(product, sizeKey) {
            if (!product || !product.sizes) return 0;
            const physical = product.sizes[sizeKey] || 0;
            const committed = (product.committedSizes && product.committedSizes[sizeKey]) || 0;
            const locked = this.getLockedSize(product, sizeKey);
            return Math.max(0, physical - committed - locked);
        },

        setPurchaseQty(qty) {
            this.purchaseQty = qty;
            const ch = Math.round(qty * 0.15);
            const m = Math.round(qty * 0.38);
            const g = Math.round(qty * 0.32);
            const xl = Math.round(qty * 0.10);
            const xxl = qty >= 60 ? Math.round(qty * 0.04) : 0;
            const xxxl = qty >= 120 ? Math.round(qty * 0.01) : 0;
            const remainder = Math.max(0, qty - (ch + m + g + xl + xxl + xxxl));
            this.purchaseSizes = { ch, m: m + remainder, g, xl, '2xl': xxl, '3xl': xxxl };
        },

        updatePurchaseTotalFromSizes() {
            const ch = parseInt(this.purchaseSizes.ch) || 0;
            const m = parseInt(this.purchaseSizes.m) || 0;
            const g = parseInt(this.purchaseSizes.g) || 0;
            const xl = parseInt(this.purchaseSizes.xl) || 0;
            const xxl = parseInt(this.purchaseSizes['2xl']) || 0;
            const xxxl = parseInt(this.purchaseSizes['3xl']) || 0;
            this.purchaseQty = ch + m + g + xl + xxl + xxxl;
        },

        addToFuturePurchaseList() {
            if (!this.selectedProduct) return;
            const qty = parseInt(this.purchaseQty) || 0;
            if (qty <= 0) {
                this.showToast('Ingresa al menos 1 unidad en las tallas para reordenar.', 'error');
                return;
            }
            const cost = parseFloat(this.drawerCost) || 0;
            const newOrder = {
                id: 'FP-2026-' + String(this.futurePurchaseOrders.length + 1).padStart(2, '0'),
                sku: this.selectedProduct.sku,
                productName: this.selectedProduct.name,
                supplier: this.purchaseSupplier,
                qty: qty,
                sizes: { ...this.purchaseSizes },
                costUnit: cost,
                totalCost: qty * cost,
                priority: this.purchasePriority,
                status: 'Planificada',
                date: 'Hoy'
            };
            this.futurePurchaseOrders.unshift(newOrder);
            this.hasAddedPurchaseThisSession = true;
            this.purchaseAddedSuccess = true;
            setTimeout(() => { this.purchaseAddedSuccess = false; }, 4000);
            this.showToast(qty + ' uds (' + this.selectedProduct.sku + ') aÃ±adidas con desglose por tallas a compras.', 'success');
        },

        // [TASK-INV-16] MÃ©todos reactivos de la bandeja de Compras, PO y Kardex
        updatePlannedOrderQty(orderId, newQty) {
            const order = this.futurePurchaseOrders.find(o => o.id === orderId);
            if (!order) return;
            const parsed = parseInt(newQty);
            const qty = isNaN(parsed) || parsed < 1 ? 1 : parsed;
            const oldQty = order.qty || 1;
            order.qty = qty;
            order.totalCost = Math.round(order.qty * (order.costUnit || 0) * 100) / 100;

            // Escala proporcional de curva de tallas si existe
            if (order.sizes && oldQty > 0) {
                const ratio = qty / oldQty;
                let runningSum = 0;
                const keys = Object.keys(order.sizes);
                keys.forEach((k, idx) => {
                    if (idx === keys.length - 1) {
                        order.sizes[k] = Math.max(0, qty - runningSum);
                    } else {
                        order.sizes[k] = Math.max(0, Math.round(order.sizes[k] * ratio));
                        runningSum += order.sizes[k];
                    }
                });
            }

            this.logPurchaseKardex({
                type: 'AJUSTE_CANTIDAD',
                typeLabel: 'Ajuste de Unidades',
                badgeClass: 'bg-amber-50 text-amber-700 border-amber-200',
                poNumber: order.id,
                supplier: order.supplier,
                sku: order.sku,
                productName: order.productName,
                deltaQty: oldQty + ' -> ' + qty + ' uds',
                totalAmount: order.totalCost,
                user: 'Elena Compras',
                notes: 'Cantidad modificada en compras planificadas. Subtotal recalculado a $' + order.totalCost.toFixed(2) + ' MXN.'
            });
            this.showToast('Lote ' + order.id + ' actualizado a ' + qty + ' uds. InversiÃ³n recalculada.', 'info');
        },

        updatePlannedOrderSize(orderId, sizeKey, val) {
            const targetInstance = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            const orders = (targetInstance.futurePurchaseOrders || this.futurePurchaseOrders || []);
            const order = orders.find(o => o.id === orderId);
            if (!order) return;
            if (!order.sizes) {
                order.sizes = { ch: 0, m: 0, g: 0, xl: 0, '2xl': 0, '3xl': 0 };
            }
            const parsed = parseInt(val);
            order.sizes[sizeKey] = isNaN(parsed) || parsed < 0 ? 0 : parsed;
            const total = Object.values(order.sizes).reduce((sum, v) => sum + (parseInt(v) || 0), 0);
            order.qty = total > 0 ? total : 1;
            order.totalCost = Math.round(order.qty * (order.costUnit || 0) * 100) / 100;
        },

        toggleOrderSizeEdit(order) {
            if (!order) return;
            order._editingSizes = !order._editingSizes;
            if (!order._editingSizes) {
                this.showToast('Curva de tallas actualizada para ' + order.productName + ' (' + order.qty + ' uds).', 'success');
            }
        },

        updatePlannedOrderCost(orderId, newCost) {
            const targetInstance = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            const orders = (targetInstance.futurePurchaseOrders || this.futurePurchaseOrders || []);
            const order = orders.find(o => o.id === orderId);
            if (!order) return;
            const parsed = parseFloat(newCost);
            const cost = isNaN(parsed) || parsed < 0 ? 0 : Math.round(parsed * 100) / 100;
            const oldCost = order.costUnit || 0;
            order.costUnit = cost;
            order.totalCost = Math.round((order.qty || 1) * cost * 100) / 100;

            if (this !== targetInstance && this.futurePurchaseOrders) {
                const localOrder = this.futurePurchaseOrders.find(o => o.id === orderId);
                if (localOrder) {
                    localOrder.costUnit = cost;
                    localOrder.totalCost = order.totalCost;
                }
            }

            this.logPurchaseKardex({
                type: 'AJUSTE_COSTO',
                typeLabel: 'Ajuste de Costo Unitario',
                badgeClass: 'bg-indigo-50 text-indigo-700 border-indigo-200',
                poNumber: order.id,
                supplier: order.supplier,
                sku: order.sku,
                productName: order.productName,
                deltaQty: '$' + oldCost.toFixed(2) + ' -> $' + cost.toFixed(2) + ' MXN',
                totalAmount: order.totalCost,
                user: 'Elena Compras',
                notes: 'Costo unitario pactado con fabricante actualizado a $' + cost.toFixed(2) + ' MXN. Subtotal recalculado a $' + order.totalCost.toFixed(2) + ' MXN.'
            });
            this.showToast('Costo de ' + order.id + ' actualizado a $' + cost.toFixed(2) + ' MXN. InversiÃ³n recalculada.', 'info');
        },

        updatePlannedOrderPriority(orderId, newPriority) {
            const targetInstance = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            const orders = (targetInstance.futurePurchaseOrders || this.futurePurchaseOrders || []);
            const order = orders.find(o => o.id === orderId);
            if (!order) return;
            const allowed = ['critica', 'alta', 'media', 'baja'];
            const prio = allowed.includes(String(newPriority).toLowerCase()) ? String(newPriority).toLowerCase() : 'media';
            order.priority = prio;

            if (this !== targetInstance && this.futurePurchaseOrders) {
                const localOrder = this.futurePurchaseOrders.find(o => o.id === orderId);
                if (localOrder) localOrder.priority = prio;
            }

            this.showToast('Prioridad de lote ' + order.id + ' asignada a ' + prio.toUpperCase() + '.', 'info');
        },

        printPurchaseOrderDoc() {
            const target = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            const po = target.currentPoData || this.currentPoData;
            const poNumber = po ? po.poNumber : 'PO-OFICIAL';
            const el = document.getElementById('printablePurchaseOrderDoc');

            // 1. AÃ±adir clase de respaldo en el body principal
            document.body.classList.add('printing-po-doc');

            // 2. MÃ©todo Principal: ImpresiÃ³n vÃ­a iFrame Aislado
            // Desacopla 100% el documento de la jerarquÃ­a de modales, flexbox, overflow y transforms GPU de Chromium
            if (typeof document !== 'undefined' && el) {
                try {
                    let printFrame = document.getElementById('poPrintFrame');
                    if (printFrame) {
                        try { document.body.removeChild(printFrame); } catch(e) {}
                    }
                    printFrame = document.createElement('iframe');
                    printFrame.id = 'poPrintFrame';
                    printFrame.name = 'poPrintFrame';
                    printFrame.style.position = 'fixed';
                    printFrame.style.top = '-9999px';
                    printFrame.style.left = '-9999px';
                    printFrame.style.width = '1000px';
                    printFrame.style.height = '1400px';
                    printFrame.style.border = '0';
                    printFrame.style.opacity = '0';
                    printFrame.style.zIndex = '-999';
                    document.body.appendChild(printFrame);

                    const doc = printFrame.contentWindow.document;
                    doc.open();
                    doc.write(`
                        <!DOCTYPE html>
                        <html lang="es">
                        <head>
                            <meta charset="utf-8">
                            <title>Orden de Compra ${poNumber}</title>
                            <script src="https://cdn.tailwindcss.com"></script>
                            <style>
                                @page {
                                    size: letter portrait;
                                    margin: 12mm 15mm;
                                }
                                * {
                                    box-sizing: border-box;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                html, body {
                                    background: #ffffff !important;
                                    color: #0f172a !important;
                                    margin: 0 !important;
                                    padding: 0 !important;
                                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                                }
                                #printablePurchaseOrderDoc {
                                    max-height: none !important;
                                    overflow: visible !important;
                                    padding: 0 !important;
                                    margin: 0 !important;
                                    width: 100% !important;
                                    display: block !important;
                                }
                                table {
                                    width: 100% !important;
                                    border-collapse: collapse !important;
                                }
                                tr {
                                    page-break-inside: avoid !important;
                                }
                                thead {
                                    display: table-header-group !important;
                                }
                                .no-print, .print\\:hidden {
                                    display: none !important;
                                }
                            </style>
                        </head>
                        <body class="bg-white text-slate-800 p-0 m-0">
                            ${el.outerHTML}
                        </body>
                        </html>
                    `);
                    doc.close();

                    setTimeout(() => {
                        try {
                            printFrame.contentWindow.focus();
                            printFrame.contentWindow.print();
                        } catch (err) {
                            console.warn('Fallo impresiÃ³n iframe, recurriendo a window.print()', err);
                            if (typeof window !== 'undefined' && window.print) {
                                window.print();
                            }
                        }
                        setTimeout(() => {
                            try {
                                if (printFrame && printFrame.parentNode) {
                                    printFrame.parentNode.removeChild(printFrame);
                                }
                            } catch(e) {}
                            document.body.classList.remove('printing-po-doc');
                        }, 2000);
                    }, 300);
                    return;
                } catch(e) {
                    console.error('Error creando iframe de impresion:', e);
                }
            }

            // Fallback a window.print() nativo
            setTimeout(() => {
                if (typeof window !== 'undefined' && window.print) {
                    window.print();
                }
            }, 100);
            window.addEventListener('afterprint', () => {
                document.body.classList.remove('printing-po-doc');
            }, { once: true });
            setTimeout(() => {
                document.body.classList.remove('printing-po-doc');
            }, 3000);
        },

        openSizeEditModal(order) {
            if (!order) return;
            const targetInstance = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            const sizes = order.sizes || {};
            const orderData = {
                id: order.id,
                sku: order.sku || '',
                productName: order.productName || '',
                supplier: order.supplier || '',
                costUnit: order.costUnit || 0,
                priority: order.priority || 'media',
                sizes: {
                    ch: parseInt(sizes.ch) || 0,
                    m: parseInt(sizes.m) || 0,
                    g: parseInt(sizes.g) || 0,
                    xl: parseInt(sizes.xl) || 0,
                    '2xl': parseInt(sizes['2xl']) || 0,
                    '3xl': parseInt(sizes['3xl']) || 0
                }
            };
            this.selectedOrderForSizes = orderData;
            this.sizeEditModalOpen = true;
            if (targetInstance && targetInstance !== this) {
                targetInstance.selectedOrderForSizes = orderData;
                targetInstance.sizeEditModalOpen = true;
            }
        },

        saveOrderSizesFromModal() {
            const currentSelected = this.selectedOrderForSizes || (typeof window !== 'undefined' && window.inventoryAppInstance ? window.inventoryAppInstance.selectedOrderForSizes : null);
            if (!currentSelected) {
                this.sizeEditModalOpen = false;
                if (typeof window !== 'undefined' && window.inventoryAppInstance) window.inventoryAppInstance.sizeEditModalOpen = false;
                return;
            }
            const orderId = currentSelected.id;
            const targetInstance = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            const orders = (targetInstance.futurePurchaseOrders || this.futurePurchaseOrders || []);
            const target = orders.find(o => o.id === orderId);
            if (target) {
                const s = currentSelected.sizes || {};
                target.sizes = {
                    ch: parseInt(s.ch) || 0,
                    m: parseInt(s.m) || 0,
                    g: parseInt(s.g) || 0,
                    xl: parseInt(s.xl) || 0,
                    '2xl': parseInt(s['2xl']) || 0,
                    '3xl': parseInt(s['3xl']) || 0
                };
                const totalQty = Object.values(target.sizes).reduce((sum, v) => sum + (parseInt(v) || 0), 0);
                target.qty = totalQty > 0 ? totalQty : 1;
                target.totalCost = Math.round(target.qty * (target.costUnit || 0) * 100) / 100;
                this.showToast('Curva de tallas guardada para ' + target.productName + ' (' + target.qty + ' uds).', 'success');
            }
            this.sizeEditModalOpen = false;
            this.selectedOrderForSizes = null;
            if (typeof window !== 'undefined' && window.inventoryAppInstance) {
                window.inventoryAppInstance.sizeEditModalOpen = false;
                window.inventoryAppInstance.selectedOrderForSizes = null;
            }
        },

        closeSizeEditModal() {
            this.sizeEditModalOpen = false;
            this.selectedOrderForSizes = null;
            if (typeof window !== 'undefined' && window.inventoryAppInstance) {
                window.inventoryAppInstance.sizeEditModalOpen = false;
                window.inventoryAppInstance.selectedOrderForSizes = null;
            }
        },

        getPlannedOrdersBySupplier() {
            const map = {};
            this.futurePurchaseOrders.forEach(order => {
                const sup = order.supplier || 'Proveedor General';
                if (!map[sup]) {
                    const prov = (this.providers || []).find(p => p.name === sup || p.tradeName === sup);
                    let moqTarget = 50;
                    if (sup === 'Torzo B2B') moqTarget = 100;
                    else if (sup === 'Silk & Cotton Iberia SL') moqTarget = 100;
                    else if (sup === 'ModaIntima Textil MX') moqTarget = 60;

                    map[sup] = {
                        supplier: sup,
                        providerData: prov || null,
                        leadTime: prov ? (prov.leadTimeDays + ' dÃ­as') : '4.2 dÃ­as',
                        contact: prov ? (prov.contactName + ' (' + prov.phone + ')') : 'VÃ­a Portal B2B',
                        paymentTerms: prov ? (prov.paymentTerms === 'credito_60' ? 'CrÃ©dito 60 dÃ­as' : (prov.paymentTerms === 'credito_30' ? 'CrÃ©dito 30 dÃ­as' : 'CrÃ©dito 15 dÃ­as')) : 'CrÃ©dito 30 dÃ­as',
                        moqTarget: moqTarget,
                        totalQty: 0,
                        totalCost: 0,
                        items: []
                    };
                }
                map[sup].items.push(order);
                map[sup].totalQty += order.qty;
                map[sup].totalCost += order.totalCost;
            });
            return Object.values(map);
        },

        getSupplierMoqProgress(supplierGroup) {
            const pct = Math.min(100, Math.round((supplierGroup.totalQty / supplierGroup.moqTarget) * 100));
            const met = supplierGroup.totalQty >= supplierGroup.moqTarget;
            return {
                percentage: pct,
                isMet: met,
                diff: supplierGroup.moqTarget - supplierGroup.totalQty,
                label: met ? (supplierGroup.totalQty + ' / ' + supplierGroup.moqTarget + ' uds (MOQ Cubierto)') : (supplierGroup.totalQty + ' / ' + supplierGroup.moqTarget + ' uds (Faltan ' + (supplierGroup.moqTarget - supplierGroup.totalQty) + ' pzas)')
            };
        },

        openModuleSettings(tab = 'fabrics') {
            const target = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            target.inventoryCurrentScreen = 'settings';
            target.moduleSettingsModalOpen = true;
            this.inventoryCurrentScreen = 'settings';
            this.moduleSettingsModalOpen = true;
            if (tab) {
                target.activeSettingsTab = tab;
                this.activeSettingsTab = tab;
            }
        },

        closeModuleSettings() {
            const target = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            target.inventoryCurrentScreen = 'main';
            target.moduleSettingsModalOpen = false;
            this.inventoryCurrentScreen = 'main';
            this.moduleSettingsModalOpen = false;
        },

        getPendingAgreementsForCurrentPo() {
            const target = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            const po = target.currentPoData || this.currentPoData;
            if (!po) return [];
            const list = target.pendingCreditAgreements || this.pendingCreditAgreements || [];
            return list.filter(a => (po.supplier === 'Consolidado Multimarca' || a.supplier === po.supplier) && a.status === 'Pendiente de Aplicar');
        },

        togglePoCreditDeduction() {
            const target = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            const po = target.currentPoData || this.currentPoData;
            if (!po) return;
            po.hasCreditDeduction = !po.hasCreditDeduction;
            if (po.hasCreditDeduction && !po.creditNoteRef) {
                const pending = this.getPendingAgreementsForCurrentPo();
                if (pending.length > 0) {
                    po.creditNoteRef = pending[0].ref;
                    po.creditNoteAmount = pending[0].amount;
                    po.creditNoteReason = pending[0].reason;
                } else {
                    po.creditNoteRef = 'NC-' + new Date().getFullYear() + '-001';
                    po.creditNoteAmount = 500.00;
                    po.creditNoteReason = 'Merma textil y rotura de costura en entrega previa';
                }
            }
            this.recalculatePoTotals();
            if (po.hasCreditDeduction) {
                this.showToast('CompensaciÃ³n por nota de crÃ©dito activada.', 'info');
            } else {
                this.showToast('CompensaciÃ³n desactivada.', 'info');
            }
        },

        applySuggestedCreditAgreement(agreement) {
            const target = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            const po = target.currentPoData || this.currentPoData;
            if (!po || !agreement) return;
            po.hasCreditDeduction = true;
            po.creditNoteRef = agreement.ref;
            po.creditNoteAmount = agreement.amount;
            po.creditNoteReason = agreement.reason;
            this.recalculatePoTotals();
            this.showToast('Acuerdo ' + agreement.ref + ' aplicado (' + this.formatCurrency(agreement.amount) + ').', 'success');
        },

        handleCreditAgreementSelect(selectedRef) {
            const target = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            const po = target.currentPoData || this.currentPoData;
            if (!po) return;

            if (!selectedRef || selectedRef === '') {
                // DeseleccionÃ³ compensaciÃ³n
                po.hasCreditDeduction = false;
                po.deductionAmount = 0;
                this.recalculatePoTotals();
                this.showToast('CompensaciÃ³n por acuerdo desactivada.', 'info');
                return;
            }

            if (selectedRef === 'manual') {
                po.hasCreditDeduction = true;
                if (!po.creditNoteRef || po.creditNoteRef === '') po.creditNoteRef = 'NC-' + new Date().getFullYear() + '-001';
                if (!po.creditNoteAmount || po.creditNoteAmount === 0) po.creditNoteAmount = 500.00;
                if (!po.creditNoteReason) po.creditNoteReason = 'Ajuste comercial por discrepancia previa en muelle';
                this.recalculatePoTotals();
                this.showToast('Modo de captura manual de acuerdo activado.', 'info');
                return;
            }

            const pendingList = this.getPendingAgreementsForCurrentPo();
            const found = pendingList.find(a => a.ref === selectedRef);
            if (found) {
                po.hasCreditDeduction = true;
                po.creditAgreementId = found.id || null;
                po.creditNoteRef = found.ref;
                po.creditNoteAmount = found.amount;
                po.creditNoteReason = found.reason;
                this.recalculatePoTotals();
                this.showToast('Acuerdo ' + found.ref + ' seleccionado (' + this.formatCurrency(found.amount) + ').', 'success');
            }
        },

        submitDiscrepancyMinute(minuteData) {
            const csrfToken = (typeof document !== 'undefined' && document.querySelector('meta[name="csrf-token"]'))
                ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                : '';

            return fetch('/api/v1/inventory/purchases/credit-agreements', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(minuteData)
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success && data.agreement) {
                    this.pendingCreditAgreements.unshift(data.agreement);
                    this.showToast('Minuta de discrepancia ' + data.agreement.ref + ' registrada exitosamente.', 'success');
                }
                return data;
            })
            .catch(err => {
                console.error('Error al registrar minuta de discrepancia:', err);
                this.showToast('Error al registrar la minuta de discrepancia.', 'error');
            });
        },

        recalculatePoTotals() {
            if (!this.currentPoData) return;
            const po = this.currentPoData;
            const gross = (po.items || []).reduce((sum, i) => sum + (Number(i.totalCost) || 0), 0);
            po.grossSubtotal = gross;
            let deduction = 0;
            if (po.hasCreditDeduction) {
                const parsedAmount = parseFloat(po.creditNoteAmount);
                deduction = isNaN(parsedAmount) || parsedAmount < 0 ? 0 : Math.min(gross, Math.round(parsedAmount * 100) / 100);
            }
            po.deductionAmount = deduction;
            const taxableSubtotal = Math.max(0, Math.round((gross - deduction) * 100) / 100);
            po.subtotal = taxableSubtotal;
            po.iva = Math.round(taxableSubtotal * 0.16 * 100) / 100;
            po.total = Math.round((taxableSubtotal + po.iva) * 100) / 100;

            const target = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            if (target && target !== this && target.currentPoData) {
                target.currentPoData.hasCreditDeduction = po.hasCreditDeduction;
                target.currentPoData.creditNoteRef = po.creditNoteRef;
                target.currentPoData.creditNoteAmount = po.creditNoteAmount;
                target.currentPoData.creditNoteReason = po.creditNoteReason;
                target.currentPoData.grossSubtotal = po.grossSubtotal;
                target.currentPoData.deductionAmount = po.deductionAmount;
                target.currentPoData.subtotal = po.subtotal;
                target.currentPoData.iva = po.iva;
                target.currentPoData.total = po.total;
            }
        },

        openPoGenerator(supplier) {
            const target = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            let items = [];
            let targetSupplier = supplier;
            const futureOrders = target.futurePurchaseOrders || this.futurePurchaseOrders || [];

            if (supplier === 'all') {
                items = [...futureOrders];
                targetSupplier = 'Consolidado Multimarca';
            } else {
                items = futureOrders.filter(o => o.supplier === supplier);
            }

            if (items.length === 0) {
                this.showToast('No hay artÃ­culos planificados para este proveedor.', 'warning');
                return;
            }

            const providersList = target.providers || this.providers || [];
            const prov = providersList.find(p => p.name === targetSupplier || p.tradeName === targetSupplier);
            const subtotal = items.reduce((sum, i) => sum + (Number(i.totalCost) || 0), 0);
            const iva = Math.round(subtotal * 0.16 * 100) / 100;
            const total = Math.round((subtotal + iva) * 100) / 100;
            const poNumber = 'PO-2026-' + String(Math.floor(1000 + Math.random() * 9000));

            // DetecciÃ³n automÃ¡tica de acuerdo de nota de crÃ©dito pendiente para el proveedor
            const pendingList = (target.pendingCreditAgreements || this.pendingCreditAgreements || []);
            const autoAgreement = pendingList.find(a => (targetSupplier === 'Consolidado Multimarca' || a.supplier === targetSupplier) && a.status === 'Pendiente de Aplicar');

            const poData = {
                poNumber: poNumber,
                date: new Date().toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' }),
                supplier: targetSupplier,
                vendorCode: prov ? prov.vendorCode : 'VND-B2B-01',
                contactName: prov ? prov.contactName : 'Ejecutivo de Cuenta B2B',
                contactPhone: prov ? prov.phone : '+52 55 5555 0100',
                contactEmail: prov ? prov.email : 'pedidos@proveedor.com',
                paymentTerms: prov ? (prov.paymentTerms === 'credito_60' ? 'CrÃ©dito 60 dÃ­as' : (prov.paymentTerms === 'credito_30' ? 'CrÃ©dito 30 dÃ­as' : 'CrÃ©dito Comercial 15 dÃ­as')) : 'CrÃ©dito Comercial 30 dÃ­as',
                deliveryDock: 'Rampa Central AlmacÃ©n CDMX - BahÃ­a Inbound 02 (Av. Insurgentes Sur #1898, CDMX)',
                deliveryCommitmentDate: '5 dÃ­as hÃ¡biles posteriores a recepciÃ³n de orden',
                items: items.map(it => ({
                    id: it.id,
                    sku: it.sku,
                    productName: it.productName,
                    supplier: it.supplier,
                    qty: Number(it.qty) || 0,
                    sizes: it.sizes ? { ...it.sizes } : { ch: 0, m: 0, g: 0, xl: 0 },
                    costUnit: Number(it.costUnit) || 0,
                    totalCost: Number(it.totalCost) || 0,
                    priority: it.priority || 'media'
                })),
                totalPieces: items.reduce((sum, i) => sum + (Number(i.qty) || 0), 0),
                hasCreditDeduction: false,
                creditNoteRef: autoAgreement ? autoAgreement.ref : '',
                creditNoteAmount: autoAgreement ? autoAgreement.amount : 0,
                creditNoteReason: autoAgreement ? autoAgreement.reason : 'Merma textil y rotura de costura en entrega previa',
                grossSubtotal: subtotal,
                deductionAmount: 0,
                subtotal: subtotal,
                iva: iva,
                total: total,
                buyerAgent: 'Elena Compras (CoordinaciÃ³n de Abastecimiento RevOps)',
                notes: 'Favor de confirmar recepciÃ³n, remitir factura comercial y nÃºmero de guÃ­a fletera a compras@mitienda.com.',
                isIssued: false
            };

            target.currentPoData = poData;
            target.poGeneratorModalOpen = true;
            this.currentPoData = poData;
            this.poGeneratorModalOpen = true;
        },

        closePoGenerator() {
            const target = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            target.poGeneratorModalOpen = false;
            this.poGeneratorModalOpen = false;
        },

        openEmailDispatchModal() {
            const target = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            const po = target.currentPoData || this.currentPoData;
            if (!po) return;

            const supplierName = po.supplier || 'Taller ConfecciÃ³n';
            const contactEmail = po.contactEmail || 'pedidos@proveedor.com';
            const subject = `[ORDEN DE COMPRA] ${po.poNumber} - Mi Tienda Online / ${supplierName}`;
            let deductionText = '';
            if (po.hasCreditDeduction && po.deductionAmount > 0) {
                deductionText = `\n\n* COMPENSACIÃ“N POR NOTA DE CRÃ‰DITO / DISCREPANCIA:\nSe aplicÃ³ deducciÃ³n de $${po.deductionAmount.toFixed(2)} MXN amparada bajo el acuerdo ${po.creditNoteRef} (${po.creditNoteReason}).\nImporte Bruto Partidas: $${(po.grossSubtotal || po.subtotal).toFixed(2)} MXN | Subtotal Neto Liquidable: $${po.subtotal.toFixed(2)} MXN + IVA.\n`;
            }
            const body = `Estimado equipo de ${supplierName},\n\nAdjunto a este correo encontrarÃ¡n la Orden de Compra formal con folio ${po.poNumber} por un total de ${po.totalPieces} piezas ($${(po.total || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} MXN).${deductionText}\nFavor de acusar recibo de este documento, confirmar la fecha estimada de entrega en nuestro muelle de recepciÃ³n e indicar el nÃºmero de guÃ­a de la fletera asignada una vez despachado el embarque.\n\nCondiciones de Pago: ${po.paymentTerms || 'CrÃ©dito Comercial 30 dÃ­as'}\nMuelle de RecepciÃ³n: Rampa Central AlmacÃ©n CDMX - BahÃ­a Inbound 02 (Av. Insurgentes Sur #1898, CDMX)\n\nQuedamos a su disposiciÃ³n para cualquier aclaraciÃ³n tÃ©cnica o logÃ­stica.\n\nAtentamente,\n${po.buyerAgent || 'Elena Compras'}\nCoordinaciÃ³n de Abastecimiento RevOps\nMi Tienda Online S.A. de C.V.`;

            const dispatchData = {
                poNumber: po.poNumber,
                supplier: supplierName,
                to: contactEmail,
                cc: 'compras@mitienda.com, logistica@mitienda.com',
                subject: subject,
                body: body,
                fileName: `Orden_de_Compra_${po.poNumber}.pdf`,
                pieces: po.totalPieces,
                total: po.total
            };

            target.emailDispatchData = dispatchData;
            this.emailDispatchData = dispatchData;
            target.emailDispatchModalOpen = true;
            this.emailDispatchModalOpen = true;
        },

        closeEmailDispatchModal() {
            const target = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            target.emailDispatchModalOpen = false;
            this.emailDispatchModalOpen = false;
        },

        sendPoEmailAndIssue() {
            const target = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            const po = target.currentPoData || this.currentPoData;
            const emailData = target.emailDispatchData || this.emailDispatchData;
            if (!po) return;

            const recipient = emailData ? emailData.to : po.contactEmail;

            // 1. Snapshot inmutable completo de la PO emitida para guardado y reimpresiÃ³n
            const poSnapshot = {
                ...po,
                isIssued: true,
                issuedAt: new Date().toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' }),
                dispatchedToEmail: recipient
            };

            // 2. Agregar a activePurchaseOrders con snapshot
            const newActivePo = {
                id: po.poNumber.replace('PO-', 'OC-'),
                poNumber: po.poNumber,
                supplier: po.supplier,
                carrier: 'Transportista B2B (AsignaciÃ³n en curso)',
                eta: po.deliveryCommitmentDate,
                sku: po.items && po.items.length > 0 ? po.items.map(i => i.sku).join(', ') : 'VARIOS',
                productName: po.items && po.items.length === 1 ? po.items[0].productName : ((po.items && po.items[0] ? po.items[0].productName : 'Lote ConfecciÃ³n') + ' (+' + (po.items.length - 1) + ' adicionales)'),
                qty: po.totalPieces,
                sizes: po.items && po.items[0] && po.items[0].sizes ? { ...po.items[0].sizes } : { ch: 0, m: 0, g: 0, xl: 0 },
                unitCost: po.items && po.items[0] ? po.items[0].costUnit : 0,
                totalCost: po.total,
                subtotal: po.subtotal,
                iva: po.iva,
                date: new Date().toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' }),
                status: 'Emitida (Correo Enviado)',
                emailSentTo: recipient,
                emailSentAt: new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }),
                linkedCartId: null,
                linkedCustomer: null,
                poSnapshot: poSnapshot
            };

            const targetActive = target.activePurchaseOrders || this.activePurchaseOrders || [];
            targetActive.unshift(newActivePo);

            // 2.5 Persistir en base de datos via API RESTful (Slice 2.2 / TASK-INV-16 / TASK-PUR-04)
            const csrfToken = (typeof document !== 'undefined' && document.querySelector('meta[name="csrf-token"]'))
                ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                : '';

            fetch('/api/v1/inventory/purchases/orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    po_number: po.poNumber,
                    supplier: po.supplier,
                    notes: po.notes,
                    payment_terms: po.paymentTerms,
                    delivery_commitment_date: po.deliveryCommitmentDate,
                    has_credit_deduction: po.hasCreditDeduction,
                    credit_agreement_id: po.creditAgreementId || null,
                    credit_note_ref: po.creditNoteRef || null,
                    credit_note_amount: po.deductionAmount || po.creditNoteAmount || 0,
                    credit_note_reason: po.creditNoteReason || null,
                    items: (po.items || []).map(it => ({
                        id: it.id,
                        sku: it.sku,
                        product_name: it.productName,
                        qty: it.qty,
                        unit_cost: it.costUnit,
                        sizes: it.sizes || null,
                        priority: it.priority || 'media'
                    })),
                    po_snapshot: poSnapshot
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success && data.order && data.order.dbId) {
                    newActivePo.dbId = data.order.dbId;
                    fetch('/api/v1/inventory/purchases/orders/' + data.order.dbId + '/dispatch', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            recipient_email: recipient,
                            carrier: po.carrier || 'Transportista B2B',
                            notes: 'Despacho formal notificado por correo institucional.'
                        })
                    }).catch(() => {});
                }
            })
            .catch(err => {
                console.warn('Nota: Persistencia de PO completada en modo offline/cache:', err);
            });

            // 3. Si se aplicÃ³ una nota de crÃ©dito, marcar el acuerdo como aplicado
            if (po.hasCreditDeduction && po.creditNoteRef) {
                const agreements = (target.pendingCreditAgreements || this.pendingCreditAgreements || []);
                const appliedAgr = agreements.find(a => a.ref === po.creditNoteRef);
                if (appliedAgr) {
                    appliedAgr.status = 'Aplicada en ' + po.poNumber;
                }
            }

            // 4. Descontar los Ã­tems planificados convertidos
            const itemIds = (po.items || []).map(i => i.id);
            if (target.futurePurchaseOrders) {
                target.futurePurchaseOrders = target.futurePurchaseOrders.filter(o => !itemIds.includes(o.id));
            }
            this.futurePurchaseOrders = this.futurePurchaseOrders.filter(o => !itemIds.includes(o.id));

            // 5. Registrar en Kardex de AuditorÃ­a de Compras
            target.logPurchaseKardex({
                type: 'PO_EMITIDA',
                typeLabel: 'EmisiÃ³n & EnvÃ­o PO',
                badgeClass: 'bg-indigo-50 text-indigo-700 border-indigo-200',
                poNumber: po.poNumber,
                supplier: po.supplier,
                sku: po.items && po.items[0] ? po.items[0].sku : 'LOTE-VARIOS',
                productName: 'Orden Oficial ' + po.poNumber + ' (' + po.items.length + ' partidas, ' + po.totalPieces + ' uds)',
                deltaQty: '+' + po.totalPieces + ' uds (TrÃ¡nsito)',
                totalAmount: po.total,
                user: 'Elena Compras',
                notes: 'PO emitida formalmente y enviada a ' + recipient + ' con snapshot inmutable guardado.' + (po.deductionAmount > 0 ? (' DeducciÃ³n de $' + po.deductionAmount.toFixed(2) + ' MXN amparada bajo acuerdo ' + po.creditNoteRef + '.') : '')
            });

            // 5. SincronizaciÃ³n con Finanzas (TASK-INT-23)
            if (typeof window.syncPoWithFinanceState === 'function') {
                window.syncPoWithFinanceState(po);
            }

            // 5.2 SincronizaciÃ³n y NotificaciÃ³n Reactiva al Extranet del Proveedor (TASK-HUB-VND / TASK-PUR-04)
            if (typeof window !== 'undefined' && typeof window.notifyVendorExtranetNewPo === 'function') {
                window.notifyVendorExtranetNewPo(po);
            }

            // 6. Cerrar modales y enfocar vista de Ã“rdenes Emitidas
            target.emailDispatchModalOpen = false;
            this.emailDispatchModalOpen = false;
            target.poGeneratorModalOpen = false;
            this.poGeneratorModalOpen = false;
            target.purchasesSubTab = 'inbound_tracking';
            this.purchasesSubTab = 'inbound_tracking';

            target.showToast('Orden ' + po.poNumber + ' emitida formalmente, enviada por correo a ' + recipient + ' y notificada a Extranet B2B.', 'success');
        },

        confirmAndIssuePo() {
            // Invoca directamente el modal de despacho por correo antes de emitir
            this.openEmailDispatchModal();
        },

        viewPoPdfFromActiveOrder(order) {
            const target = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            if (!order) return;

            // Si la orden ya cuenta con su snapshot original emitido, lo recuperamos Ã­ntegro
            if (order.poSnapshot) {
                const snapshot = {
                    ...order.poSnapshot,
                    isIssued: true
                };
                target.currentPoData = snapshot;
                this.currentPoData = snapshot;
                target.poGeneratorModalOpen = true;
                this.poGeneratorModalOpen = true;
                return;
            }

            const total = Number(order.totalCost) || 0;
            const sub = Math.round((total / 1.16) * 100) / 100;
            const iva = Math.round((total - sub) * 100) / 100;
            const poNum = order.poNumber || (order.id.startsWith('PO-') ? order.id : 'PO-' + order.id.replace('OC-', ''));
            const providersList = target.providers || this.providers || [];
            const prov = providersList.find(p => p.name === order.supplier);

            const poData = {
                poNumber: poNum,
                date: order.date || '03 Sept, 2026',
                supplier: order.supplier,
                vendorCode: prov ? prov.vendorCode : (order.supplier.includes('Torzo') ? 'VND-TRZ-01' : 'VND-GEN-01'),
                contactName: prov ? prov.contactName : 'Gerencia Comercial B2B',
                contactPhone: prov ? prov.phone : '+52 55 5555 0100',
                contactEmail: prov ? prov.email : 'pedidos@' + order.supplier.toLowerCase().replace(/[^a-z0-9]/g, '') + '.com',
                paymentTerms: 'CrÃ©dito Comercial 30 dÃ­as contra entrega',
                deliveryDock: 'Rampa Central AlmacÃ©n CDMX - BahÃ­a Inbound 02 (Av. Insurgentes Sur #1898, CDMX)',
                deliveryCommitmentDate: order.eta || '3 a 5 dÃ­as hÃ¡biles',
                items: [{
                    id: order.id + '-item',
                    sku: order.sku || 'SKU-INB',
                    productName: order.productName,
                    qty: order.qty,
                    costUnit: order.unitCost || (total / (order.qty || 1)),
                    totalCost: total,
                    sizes: order.sizes ? { ...order.sizes } : { ch: 0, m: 0, g: 0, xl: 0 }
                }],
                totalPieces: order.qty,
                subtotal: sub,
                iva: iva,
                total: total,
                buyerAgent: 'Elena Compras (CoordinaciÃ³n de Abastecimiento RevOps)',
                notes: 'Expediente oficial de orden emitida. Flete asignado: ' + (order.carrier || 'Flete Terrestre') + '.',
                isIssued: true
            };

            target.currentPoData = poData;
            this.currentPoData = poData;
            target.poGeneratorModalOpen = true;
            this.poGeneratorModalOpen = true;
        },

        viewPoPdfFromPoNumber(poNumber) {
            const target = (typeof window !== 'undefined' && window.inventoryAppInstance) ? window.inventoryAppInstance : this;
            const orders = target.activePurchaseOrders || this.activePurchaseOrders || [];
            const found = orders.find(o => o.poNumber === poNumber || o.id === poNumber || o.id === (poNumber || '').replace('PO-', 'OC-'));
            if (found) {
                this.viewPoPdfFromActiveOrder(found);
            } else {
                this.showToast('No se encontrÃ³ el expediente de la orden ' + poNumber, 'warning');
            }
        },

        openCarrierTrackingModal(order) {
            this.selectedTrackingOrder = order;
            this.carrierTrackingModalOpen = true;
        },

        closeCarrierTrackingModal() {
            this.carrierTrackingModalOpen = false;
            this.selectedTrackingOrder = null;
        },

        copyTrackingNumber(text) {
            if (navigator && navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
                this.showToast('NÃºmero de guÃ­a ' + text + ' copiado al portapapeles.', 'success');
            } else {
                this.showToast('GuÃ­a: ' + text, 'info');
            }
        },

        // Acciones para Bandeja On-Demand de Clientes (TASK-INV-16 & TASK-INT-22)
        get filteredOnDemandRequests() {
            if (this.onDemandStatusFilter === 'todas') {
                return this.customerOnDemandRequests;
            }
            if (this.onDemandStatusFilter === 'pendientes') {
                return this.customerOnDemandRequests.filter(r => r.verificationStatus === 'pendiente');
            }
            if (this.onDemandStatusFilter === 'en_compra') {
                return this.customerOnDemandRequests.filter(r => r.verificationStatus === 'en_compra' || r.verificationStatus === 'confirmado');
            }
            if (this.onDemandStatusFilter === 'agotadas') {
                return this.customerOnDemandRequests.filter(r => r.verificationStatus === 'agotado' || r.verificationStatus === 'sin_stock');
            }
            return this.customerOnDemandRequests;
        },

        resolveOnDemandToPlanned(req) {
            const newOrder = {
                id: 'FP-2026-' + String(this.futurePurchaseOrders.length + 1).padStart(2, '0'),
                sku: req.sku,
                productName: req.productName,
                supplier: req.supplier,
                qty: req.qty,
                sizes: {
                    ch: req.size === 'CH' ? req.qty : 0,
                    m: req.size === 'M' ? req.qty : 0,
                    g: req.size === 'G' ? req.qty : 0,
                    xl: req.size === 'XL' ? req.qty : 0,
                    '2xl': req.size === '2XL' ? req.qty : 0,
                    '3xl': req.size === '3XL' ? req.qty : 0
                },
                costUnit: req.costUnit,
                totalCost: req.totalCost,
                priority: 'alta',
                status: 'Planificada',
                date: 'Hoy (On-Demand)'
            };
            this.futurePurchaseOrders.unshift(newOrder);
            req.verificationStatus = 'en_compra';

            this.logPurchaseKardex({
                type: 'ON_DEMAND_CONVERTIDO',
                typeLabel: 'On-Demand a PO',
                badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                poNumber: req.id,
                supplier: req.supplier,
                sku: req.sku,
                productName: req.productName + ' (Talla ' + req.size + ')',
                deltaQty: '+' + req.qty + ' uds',
                totalAmount: req.totalCost,
                user: 'Elena Compras',
                notes: 'Solicitud On-Demand de ' + req.customerName + ' confirmada con ' + req.supplier + ' y transferida a compras planificadas.'
            });

            this.showToast('Solicitud ' + req.id + ' incorporada a compras planificadas de ' + req.supplier + '.', 'success');
        },

        openOnDemandRejectModal(req) {
            this.selectedOnDemandReq = req;
            this.onDemandRejectReason = 'Agotado en taller de confecciÃ³n sin fecha de resurtido';
            this.onDemandRejectModalOpen = true;
        },

        confirmRejectOnDemand() {
            if (!this.selectedOnDemandReq) return;
            const req = this.selectedOnDemandReq;
            req.verificationStatus = 'agotado';

            this.logPurchaseKardex({
                type: 'ON_DEMAND_AGOTADO',
                typeLabel: 'On-Demand Rechazado',
                badgeClass: 'bg-rose-50 text-rose-700 border-rose-200',
                poNumber: req.id,
                supplier: req.supplier,
                sku: req.sku,
                productName: req.productName + ' (Talla ' + req.size + ')',
                deltaQty: '0 uds',
                totalAmount: 0,
                user: 'Elena Compras',
                notes: 'MercancÃ­a agotada en fabricante. NotificaciÃ³n enviada a AtenciÃ³n a Clientes (Hub 3): "' + this.onDemandRejectReason + '".'
            });

            this.onDemandRejectModalOpen = false;
            this.showToast('Solicitud ' + req.id + ' marcada como Agotada. Ticket de sustituciÃ³n despachado a Hub 3.', 'warning');
        },

        verifyRequestWithSupplier(req) {
            req.verificationStatus = 'confirmado';
            this.showToast('Stock confirmado con ' + req.supplier + ' para ' + req.sku + ' (Talla ' + req.size + ').', 'success');
        },

        sendPaymentLinkToCustomer(req) {
            req.paymentSent = true;
            this.showToast('Enlace de pago y confirmaciÃ³n enviado a ' + req.customerName + ' (' + req.customerPhone + ') vÃ­a WhatsApp.', 'success');
        },

        convertRequestToPurchaseOrder(req) {
            this.resolveOnDemandToPlanned(req);
        },

        // Kardex de AuditorÃ­a de Compras (TASK-INV-16)
        logPurchaseKardex(event) {
            const newEntry = {
                id: 'KDX-PUR-2026-' + String(this.purchaseKardexLog.length + 1).padStart(3, '0'),
                timestamp: new Date().toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }),
                ...event
            };
            this.purchaseKardexLog.unshift(newEntry);
        },

        get filteredPurchaseKardex() {
            return (this.purchaseKardexLog || []).filter(item => {
                const matchesFilter = this.purchaseKardexFilter === 'all' || item.type === this.purchaseKardexFilter;
                const q = (this.purchaseKardexSearch || '').toLowerCase().trim();
                const matchesSearch = !q ||
                    (item.poNumber && item.poNumber.toLowerCase().includes(q)) ||
                    (item.supplier && item.supplier.toLowerCase().includes(q)) ||
                    (item.sku && item.sku.toLowerCase().includes(q)) ||
                    (item.productName && item.productName.toLowerCase().includes(q)) ||
                    (item.notes && item.notes.toLowerCase().includes(q));
                return matchesFilter && matchesSearch;
            });
        },

        handleSaveDrawerClick() {
            this.saveDrawerChanges();
        },

        confirmSaveWithPurchase(addPurchase) {
            if (addPurchase) {
                this.addToFuturePurchaseList();
            }
            this.confirmPurchaseModalOpen = false;
            this.saveDrawerChanges();
        },

        isProductInPurchaseList(sku) {
            return this.futurePurchaseOrders.some(o => o.sku === sku);
        },

        getProductPurchaseCount(sku) {
            const items = this.futurePurchaseOrders.filter(o => o.sku === sku);
            return items.reduce((sum, item) => sum + item.qty, 0);
        },

        toggleProductPublication(sku) {
            const prod = this.products.find(p => p.sku === sku);
            if (prod) {
                prod.isPublished = !prod.isPublished;
                const msg = prod.isPublished 
                    ? `SKU ${sku} publicado en vitrina web activa.`
                    : `SKU ${sku} retirado del catÃ¡logo pÃºblico (fuera de catÃ¡logo).`;
                this.showToast(msg, prod.isPublished ? 'success' : 'info');
            }
        },

        // =========================================================================
        // TASK-INV-11: GESTIÃ“N INTEGRAL DE TELAS, COLORES, SLOTTING Y REGLAS ROP
        // =========================================================================

        // --- TELAS & FIBRAS ---
        addNewFabric() {
            if (!this.newFabricForm.name || !this.newFabricForm.name.trim()) {
                this.showToast('Ingresa el nombre del tipo de tela.', 'info');
                return;
            }
            const id = this.newFabricForm.name.toLowerCase().replace(/[^a-z0-9]/g, '_');
            this.apparelFabrics.push({
                id: id,
                name: this.newFabricForm.name.trim(),
                description: this.newFabricForm.description.trim() || 'Fibra textil para indumentaria',
                is_active: true
            });
            this.showToast(`Tela "${this.newFabricForm.name.trim()}" registrada con Ã©xito.`, 'success');
            this.newFabricForm = { name: '', description: '' };
        },

        editFabric(fab) {
            this.fabricEditState.isEditing = true;
            this.fabricEditState.editingId = fab.id;
            this.fabricEditState.form = {
                name: fab.name,
                description: fab.description || '',
                is_active: fab.is_active !== false
            };
        },

        cancelEditFabric() {
            this.fabricEditState.isEditing = false;
            this.fabricEditState.editingId = null;
            this.fabricEditState.form = { name: '', description: '', is_active: true };
        },

        saveFabric() {
            if (!this.fabricEditState.form.name || !this.fabricEditState.form.name.trim()) {
                this.showToast('El nombre de la tela no puede estar vacÃ­o.', 'warning');
                return;
            }
            const idx = this.apparelFabrics.findIndex(f => f.id === this.fabricEditState.editingId);
            if (idx !== -1) {
                this.apparelFabrics[idx].name = this.fabricEditState.form.name.trim();
                this.apparelFabrics[idx].description = this.fabricEditState.form.description.trim();
                this.apparelFabrics[idx].is_active = this.fabricEditState.form.is_active;
                this.showToast(`Tela "${this.apparelFabrics[idx].name}" actualizada con Ã©xito.`, 'success');
            }
            this.cancelEditFabric();
        },

        toggleArchiveFabric(fab) {
            fab.is_active = !fab.is_active;
            const statusLabel = fab.is_active ? 'reactivada' : 'archivada';
            this.showToast(`Tela "${fab.name}" ${statusLabel} correctamente.`, 'info');
        },

        deleteFabric(fabId) {
            const fab = this.apparelFabrics.find(f => f.id === fabId);
            if (!fab) return;
            const inUseCount = (this.products || []).filter(p => (p.fabric === fab.name || p.drawerFabricType === fab.name)).length;
            if (inUseCount > 0) {
                this.showToast(`No es posible eliminar "${fab.name}" porque estÃ¡ en uso en ${inUseCount} productos. Se recomienda archivarla.`, 'warning');
                return;
            }
            this.apparelFabrics = this.apparelFabrics.filter(f => f.id !== fabId);
            this.showToast(`Tela "${fab.name}" eliminada de la base de datos.`, 'success');
        },

        // --- COLORES & TORNASOLES ---
        addNewColor() {
            if (!this.newColorForm.name || !this.newColorForm.name.trim()) {
                this.showToast('Ingresa el nombre del color o tornasol.', 'info');
                return;
            }
            const id = this.newColorForm.name.toLowerCase().replace(/[^a-z0-9]/g, '_');
            let hexVal = this.newColorForm.hex;
            if (this.newColorForm.type === 'iridescent') {
                hexVal = `linear-gradient(135deg, ${this.newColorForm.gradientColor1} 0%, ${this.newColorForm.gradientColor2} 50%, ${this.newColorForm.gradientColor3} 100%)`;
            }
            this.apparelColors.push({
                id: id,
                name: this.newColorForm.name.trim(),
                hex: hexVal,
                type: this.newColorForm.type,
                is_active: true
            });
            this.showToast(`Color "${this.newColorForm.name.trim()}" agregado a la paleta.`, 'success');
            this.newColorForm = { name: '', hex: '#6366f1', type: 'solid', gradientColor1: '#6366f1', gradientColor2: '#ec4899', gradientColor3: '#f59e0b' };
        },

        editColor(col) {
            this.colorEditState.isEditing = true;
            this.colorEditState.editingId = col.id;
            let grad1 = '#6366f1', grad2 = '#ec4899', grad3 = '#f59e0b';
            if (col.type === 'iridescent' && typeof col.hex === 'string' && col.hex.includes('linear-gradient')) {
                const hexMatches = col.hex.match(/#[0-9a-fA-F]{6}/g);
                if (hexMatches && hexMatches.length >= 3) {
                    grad1 = hexMatches[0];
                    grad2 = hexMatches[1];
                    grad3 = hexMatches[2];
                }
            }
            this.colorEditState.form = {
                name: col.name,
                hex: col.type === 'solid' ? col.hex : '#6366f1',
                type: col.type || 'solid',
                gradientColor1: grad1,
                gradientColor2: grad2,
                gradientColor3: grad3,
                is_active: col.is_active !== false
            };
        },

        cancelEditColor() {
            this.colorEditState.isEditing = false;
            this.colorEditState.editingId = null;
            this.colorEditState.form = { name: '', hex: '#6366f1', type: 'solid', gradientColor1: '#6366f1', gradientColor2: '#ec4899', gradientColor3: '#f59e0b', is_active: true };
        },

        saveColor() {
            if (!this.colorEditState.form.name || !this.colorEditState.form.name.trim()) {
                this.showToast('El nombre del color no puede estar vacÃ­o.', 'warning');
                return;
            }
            const idx = this.apparelColors.findIndex(c => c.id === this.colorEditState.editingId);
            if (idx !== -1) {
                let hexVal = this.colorEditState.form.hex;
                if (this.colorEditState.form.type === 'iridescent') {
                    hexVal = `linear-gradient(135deg, ${this.colorEditState.form.gradientColor1} 0%, ${this.colorEditState.form.gradientColor2} 50%, ${this.colorEditState.form.gradientColor3} 100%)`;
                }
                this.apparelColors[idx].name = this.colorEditState.form.name.trim();
                this.apparelColors[idx].hex = hexVal;
                this.apparelColors[idx].type = this.colorEditState.form.type;
                this.apparelColors[idx].is_active = this.colorEditState.form.is_active;
                this.showToast(`Color "${this.apparelColors[idx].name}" actualizado con Ã©xito.`, 'success');
            }
            this.cancelEditColor();
        },

        toggleArchiveColor(col) {
            col.is_active = !col.is_active;
            const statusLabel = col.is_active ? 'reactivado' : 'archivado';
            this.showToast(`Color "${col.name}" ${statusLabel} correctamente.`, 'info');
        },

        deleteColor(colId) {
            const col = this.apparelColors.find(c => c.id === colId);
            if (!col) return;
            const inUseCount = (this.products || []).filter(p => (p.colorName === col.name || p.drawerColorName === col.name)).length;
            if (inUseCount > 0) {
                this.showToast(`No es posible eliminar "${col.name}" porque estÃ¡ en uso en ${inUseCount} productos. Se recomienda archivarlo.`, 'warning');
                return;
            }
            this.apparelColors = this.apparelColors.filter(c => c.id !== colId);
            this.showToast(`Color "${col.name}" eliminado de la paleta.`, 'success');
        },

        // --- SLOTTING FÃSICO (UBICACIONES DE ALMACÃ‰N) ---
        getSlotProductCount(slotCode) {
            return (this.products || []).filter(p => (p.location === slotCode || p.location_code === slotCode)).length;
        },

        getSlotOccupiedUnits(slotCode) {
            return (this.products || [])
                .filter(p => (p.location === slotCode || p.location_code === slotCode))
                .reduce((sum, p) => sum + (p.stockLocal || 0), 0);
        },

        addNewSlot() {
            const aisle = (this.newSlotForm.aisle || 'A').toUpperCase().trim();
            const rack = String(this.newSlotForm.rack || '01').padStart(2, '0').trim();
            const bin = (this.newSlotForm.bin || 'A').toUpperCase().trim();
            const code = `${aisle}-${rack}-${bin}`;

            if (this.warehouseSlots.some(s => s.code === code)) {
                this.showToast(`La ubicaciÃ³n ${code} ya existe en bodega.`, 'warning');
                return;
            }

            const name = this.newSlotForm.name.trim() || `Pasillo ${aisle} (${this.newSlotForm.zone}) - Rack ${rack} Gaveta ${bin}`;
            const cap = parseInt(this.newSlotForm.capacity) || 100;

            this.warehouseSlots.push({
                code: code,
                aisle: aisle,
                rack: rack,
                bin: bin,
                zone: this.newSlotForm.zone || 'General',
                name: name,
                capacity: cap,
                status: this.newSlotForm.status || 'active',
                is_active: true
            });

            this.showToast(`UbicaciÃ³n fÃ­sica ${code} registrada con Ã©xito.`, 'success');
            this.newSlotForm = { aisle: 'A', rack: '01', bin: 'A', zone: 'Ropa Interior', name: '', capacity: 100, status: 'active', is_active: true };
        },

        editSlot(slot) {
            this.slotEditState.isEditing = true;
            this.slotEditState.editingCode = slot.code;
            this.slotEditState.form = {
                aisle: slot.aisle,
                rack: slot.rack,
                bin: slot.bin,
                zone: slot.zone || 'General',
                name: slot.name || '',
                capacity: slot.capacity || 100,
                status: slot.status || 'active',
                is_active: slot.is_active !== false
            };
        },

        cancelEditSlot() {
            this.slotEditState.isEditing = false;
            this.slotEditState.editingCode = null;
            this.slotEditState.form = { aisle: 'A', rack: '01', bin: 'A', zone: 'Ropa Interior', name: '', capacity: 100, status: 'active', is_active: true };
        },

        saveSlot() {
            const idx = this.warehouseSlots.findIndex(s => s.code === this.slotEditState.editingCode);
            if (idx !== -1) {
                const s = this.warehouseSlots[idx];
                s.zone = this.slotEditState.form.zone;
                s.name = this.slotEditState.form.name.trim() || `Pasillo ${s.aisle} (${s.zone}) - Rack ${s.rack} Gaveta ${s.bin}`;
                s.capacity = parseInt(this.slotEditState.form.capacity) || 100;
                s.status = this.slotEditState.form.status;
                s.is_active = this.slotEditState.form.is_active;
                this.showToast(`UbicaciÃ³n ${s.code} actualizada con Ã©xito.`, 'success');
            }
            this.cancelEditSlot();
        },

        toggleArchiveSlot(slot) {
            slot.is_active = !slot.is_active;
            slot.status = slot.is_active ? 'active' : 'archived';
            const statusLabel = slot.is_active ? 'reactivada' : 'archivada';
            this.showToast(`UbicaciÃ³n ${slot.code} ${statusLabel} correctamente.`, 'info');
        },

        deleteSlot(slotCode) {
            const prodCount = this.getSlotProductCount(slotCode);
            if (prodCount > 0) {
                this.showToast(`No es posible eliminar ${slotCode} porque aloja ${prodCount} producto(s). Reasigna los productos primero o archiva la ubicaciÃ³n.`, 'warning');
                return;
            }
            this.warehouseSlots = this.warehouseSlots.filter(s => s.code !== slotCode);
            this.showToast(`UbicaciÃ³n ${slotCode} eliminada del inventario fÃ­sico.`, 'success');
        },

        // --- PUNTOS DE REORDEN (ROP) ---
        saveRopSettings() {
            const ltLocal = parseInt(this.ropSettings.leadTimeLocal) || 3;
            const ltDropship = parseInt(this.ropSettings.leadTimeDropship) || 5;
            const ssDays = parseInt(this.ropSettings.safetyStockDays) || 7;
            const defSales = parseFloat(this.ropSettings.defaultDailySales) || 2;

            this.ropSettings.leadTimeLocal = ltLocal;
            this.ropSettings.leadTimeDropship = ltDropship;
            this.ropSettings.safetyStockDays = ssDays;
            this.ropSettings.defaultDailySales = defSales;

            let underCount = 0;
            (this.products || []).forEach(p => {
                const lt = (p.supplierType === 'dropship') ? ltDropship : ltLocal;
                const vel = p.dailyVelocity || defSales;
                p.ropUnits = Math.ceil((vel * lt) + ssDays);
                p.isUnderRop = (p.stockLocal <= p.ropUnits);
                if (p.isUnderRop) underCount++;
            });

            this.showToast(`ParÃ¡metros ROP guardados. ${this.products.length} productos recalculados en tiempo real (${underCount} en riesgo de quiebre).`, 'success');
        },

        // =========================================================================
        // TASK-INV-14: CURVAS DE TALLAS, VOLUMENES Y CATEGORIAS (UNIVERSAL CRUD)
        // =========================================================================

        get selectedCurve() {
            if (!this.selectedCurveId && this.sizeCurves && this.sizeCurves.length > 0) {
                const def = this.sizeCurves.find(c => c.is_default) || this.sizeCurves[0];
                this.selectedCurveId = def ? def.id : null;
            }
            return (this.sizeCurves || []).find(c => c.id === this.selectedCurveId) || null;
        },

        get flatCategories() {
            return (this.categoriesTree || []).map(cat => ({
                id: cat.id,
                name: cat.name,
                slug: cat.slug
            }));
        },

        selectCurve(curveId) {
            this.selectedCurveId = curveId;
            this.cancelEditCurve();
            this.cancelEditSize();
        },

        // --- CURVAS DE TALLAS CRUD ---
        async addNewCurve() {
            const name = (this.newCurveForm.name || '').trim();
            if (!name) {
                this.showToast('Ingresa un nombre valido para la curva de tallas.', 'warning');
                return;
            }
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('/api/v1/inventory/settings/size-curves', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(this.newCurveForm)
                });
                const data = await res.json();
                if (!res.ok) {
                    this.showToast(data.message || 'Error al crear la curva de tallas.', 'warning');
                    return;
                }
                this.sizeCurves.push(data.data);
                this.selectedCurveId = data.data.id;
                this.newCurveForm = { name: '', category_scope: 'textil', description: '', is_default: false, is_active: true };
                this.showToast(`Curva "${data.data.name}" creada con exito.`, 'success');
            } catch (err) {
                this.showToast('Error de comunicacion con el servidor.', 'warning');
            }
        },

        editCurve(curve) {
            this.curveEditState.isEditing = true;
            this.curveEditState.editingId = curve.id;
            this.curveEditState.form = {
                name: curve.name,
                category_scope: curve.category_scope || 'textil',
                description: curve.description || '',
                is_default: Boolean(curve.is_default),
                is_active: curve.is_active !== false
            };
        },

        cancelEditCurve() {
            this.curveEditState.isEditing = false;
            this.curveEditState.editingId = null;
            this.curveEditState.form = { name: '', category_scope: 'textil', description: '', is_default: false, is_active: true };
        },

        async saveCurve() {
            const name = (this.curveEditState.form.name || '').trim();
            if (!name) {
                this.showToast('El nombre de la curva no puede estar vacio.', 'warning');
                return;
            }
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/v1/inventory/settings/size-curves/${this.curveEditState.editingId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(this.curveEditState.form)
                });
                const data = await res.json();
                if (!res.ok) {
                    this.showToast(data.message || 'Error al actualizar la curva.', 'warning');
                    return;
                }
                const idx = this.sizeCurves.findIndex(c => c.id === this.curveEditState.editingId);
                if (idx !== -1) {
                    this.sizeCurves[idx] = data.data;
                }
                this.cancelEditCurve();
                this.showToast(`Curva "${data.data.name}" actualizada con exito.`, 'success');
            } catch (err) {
                this.showToast('Error al guardar la curva.', 'warning');
            }
        },

        async toggleActiveCurve(curve) {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/v1/inventory/settings/size-curves/${curve.id}/toggle-active`, {
                    method: 'PATCH',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                const data = await res.json();
                if (res.ok) {
                    curve.is_active = data.data.is_active;
                    this.showToast(`Curva "${curve.name}" ${curve.is_active ? 'activada' : 'desactivada'}.`, 'info');
                }
            } catch (err) {
                this.showToast('Error al alternar estado de la curva.', 'warning');
            }
        },

        async archiveCurve(curve) {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const isArchived = Boolean(curve.deleted_at);
                const url = isArchived 
                    ? `/api/v1/inventory/settings/size-curves/${curve.id}/restore`
                    : `/api/v1/inventory/settings/size-curves/${curve.id}/archive`;
                const method = isArchived ? 'POST' : 'DELETE';
                const res = await fetch(url, {
                    method: method,
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                if (res.ok) {
                    curve.deleted_at = isArchived ? null : new Date().toISOString();
                    this.showToast(`Curva "${curve.name}" ${isArchived ? 'restaurada' : 'archivada'}.`, 'info');
                }
            } catch (err) {
                this.showToast('Error al modificar archivo de la curva.', 'warning');
            }
        },

        async deleteCurve(curveId) {
            if (!confirm('Deseas eliminar permanentemente esta curva de tallas?')) return;
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/v1/inventory/settings/size-curves/${curveId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                if (res.ok) {
                    this.sizeCurves = this.sizeCurves.filter(c => c.id !== curveId);
                    if (this.selectedCurveId === curveId) {
                        this.selectedCurveId = this.sizeCurves.length > 0 ? this.sizeCurves[0].id : null;
                    }
                    this.showToast('Curva de tallas eliminada permanentemente.', 'success');
                }
            } catch (err) {
                this.showToast('Error al eliminar la curva.', 'warning');
            }
        },

        // --- TALLAS INDIVIDUALES CRUD ---
        async addNewSize() {
            if (!this.selectedCurveId) {
                this.showToast('Selecciona primero una curva de tallas.', 'warning');
                return;
            }
            const code = (this.newSizeForm.code || '').trim();
            if (!code) {
                this.showToast('Ingresa el codigo de talla (ej: XS, 32, XL).', 'warning');
                return;
            }
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const payload = {
                    ...this.newSizeForm,
                    label: (this.newSizeForm.label || '').trim() || code,
                    sort_order: parseInt(this.newSizeForm.sort_order) || 1
                };
                const res = await fetch(`/api/v1/inventory/settings/size-curves/${this.selectedCurveId}/sizes`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok) {
                    this.showToast(data.message || 'Error al agregar talla.', 'warning');
                    return;
                }
                const curve = this.selectedCurve;
                if (curve) {
                    if (!Array.isArray(curve.sizes)) curve.sizes = [];
                    curve.sizes.push(data.data);
                    curve.sizes.sort((a, b) => a.sort_order - b.sort_order);
                }
                this.newSizeForm = { code: '', label: '', sort_order: (curve?.sizes?.length || 0) + 1, waist_min_cm: null, waist_max_cm: null, is_active: true };
                this.showToast(`Talla "${data.data.code}" registrada con exito.`, 'success');
            } catch (err) {
                this.showToast('Error al agregar talla.', 'warning');
            }
        },

        editSize(size) {
            this.sizeEditState.isEditing = true;
            this.sizeEditState.editingId = size.id;
            this.sizeEditState.form = {
                code: size.code,
                label: size.label || size.code,
                sort_order: size.sort_order || 1,
                waist_min_cm: size.waist_min_cm,
                waist_max_cm: size.waist_max_cm,
                is_active: size.is_active !== false
            };
        },

        cancelEditSize() {
            this.sizeEditState.isEditing = false;
            this.sizeEditState.editingId = null;
            this.sizeEditState.form = { code: '', label: '', sort_order: 1, waist_min_cm: null, waist_max_cm: null, is_active: true };
        },

        async saveSize() {
            const code = (this.sizeEditState.form.code || '').trim();
            if (!code) {
                this.showToast('El codigo de talla no puede estar vacio.', 'warning');
                return;
            }
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/v1/inventory/settings/sizes/${this.sizeEditState.editingId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(this.sizeEditState.form)
                });
                const data = await res.json();
                if (!res.ok) {
                    this.showToast(data.message || 'Error al actualizar talla.', 'warning');
                    return;
                }
                const curve = this.selectedCurve;
                if (curve && Array.isArray(curve.sizes)) {
                    const idx = curve.sizes.findIndex(s => s.id === this.sizeEditState.editingId);
                    if (idx !== -1) {
                        curve.sizes[idx] = data.data;
                        curve.sizes.sort((a, b) => a.sort_order - b.sort_order);
                    }
                }
                this.cancelEditSize();
                this.showToast(`Talla "${data.data.code}" actualizada con exito.`, 'success');
            } catch (err) {
                this.showToast('Error al guardar talla.', 'warning');
            }
        },

        async toggleActiveSize(size) {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/v1/inventory/settings/sizes/${size.id}/toggle-active`, {
                    method: 'PATCH',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                const data = await res.json();
                if (res.ok) {
                    size.is_active = data.data.is_active;
                    this.showToast(`Talla "${size.code}" ${size.is_active ? 'activada' : 'desactivada'}.`, 'info');
                }
            } catch (err) {
                this.showToast('Error al alternar estado de talla.', 'warning');
            }
        },

        async archiveSize(size) {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const isArchived = Boolean(size.deleted_at);
                const url = isArchived 
                    ? `/api/v1/inventory/settings/sizes/${size.id}/restore`
                    : `/api/v1/inventory/settings/sizes/${size.id}/archive`;
                const method = isArchived ? 'POST' : 'DELETE';
                const res = await fetch(url, {
                    method: method,
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                if (res.ok) {
                    size.deleted_at = isArchived ? null : new Date().toISOString();
                    this.showToast(`Talla "${size.code}" ${isArchived ? 'restaurada' : 'archivada'}.`, 'info');
                }
            } catch (err) {
                this.showToast('Error al archivar talla.', 'warning');
            }
        },

        async deleteSize(sizeId) {
            if (!confirm('Deseas eliminar permanentemente esta talla?')) return;
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/v1/inventory/settings/sizes/${sizeId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                if (res.ok) {
                    const curve = this.selectedCurve;
                    if (curve && Array.isArray(curve.sizes)) {
                        curve.sizes = curve.sizes.filter(s => s.id !== sizeId);
                    }
                    this.showToast('Talla eliminada permanentemente.', 'success');
                }
            } catch (err) {
                this.showToast('Error al eliminar talla.', 'warning');
            }
        },

        // --- VOLUMENES DE PERFUMERIA CRUD ---
        async addNewVolume() {
            const val = parseFloat(this.newVolumeForm.value);
            if (!val || val <= 0) {
                this.showToast('Ingresa un valor numerico valido para el volumen (ej: 50, 100).', 'warning');
                return;
            }
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const payload = {
                    ...this.newVolumeForm,
                    value: val,
                    label: (this.newVolumeForm.label || '').trim() || `${val}${this.newVolumeForm.unit || 'ml'}`
                };
                const res = await fetch('/api/v1/inventory/settings/volumes', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok) {
                    this.showToast(data.message || 'Error al agregar volumen.', 'warning');
                    return;
                }
                this.fragranceVolumes.push(data.data);
                this.fragranceVolumes.sort((a, b) => a.sort_order - b.sort_order || a.value - b.value);
                this.newVolumeForm = { value: 100, unit: 'ml', label: '', description: '', sort_order: this.fragranceVolumes.length + 1, is_active: true };
                this.showToast(`Volumen "${data.data.label}" registrado con exito.`, 'success');
            } catch (err) {
                this.showToast('Error al crear volumen.', 'warning');
            }
        },

        editVolume(vol) {
            this.volumeEditState.isEditing = true;
            this.volumeEditState.editingId = vol.id;
            this.volumeEditState.form = {
                value: vol.value,
                unit: vol.unit || 'ml',
                label: vol.label || '',
                description: vol.description || '',
                sort_order: vol.sort_order || 1,
                is_active: vol.is_active !== false
            };
        },

        cancelEditVolume() {
            this.volumeEditState.isEditing = false;
            this.volumeEditState.editingId = null;
            this.volumeEditState.form = { value: 100, unit: 'ml', label: '', description: '', sort_order: 1, is_active: true };
        },

        async saveVolume() {
            const val = parseFloat(this.volumeEditState.form.value);
            if (!val || val <= 0) {
                this.showToast('El volumen debe ser un numero mayor a 0.', 'warning');
                return;
            }
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const payload = {
                    ...this.volumeEditState.form,
                    value: val,
                    label: (this.volumeEditState.form.label || '').trim() || `${val}${this.volumeEditState.form.unit || 'ml'}`
                };
                const res = await fetch(`/api/v1/inventory/settings/volumes/${this.volumeEditState.editingId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok) {
                    this.showToast(data.message || 'Error al actualizar volumen.', 'warning');
                    return;
                }
                const idx = this.fragranceVolumes.findIndex(v => v.id === this.volumeEditState.editingId);
                if (idx !== -1) {
                    this.fragranceVolumes[idx] = data.data;
                    this.fragranceVolumes.sort((a, b) => a.sort_order - b.sort_order || a.value - b.value);
                }
                this.cancelEditVolume();
                this.showToast(`Volumen "${data.data.label}" actualizado con exito.`, 'success');
            } catch (err) {
                this.showToast('Error al actualizar volumen.', 'warning');
            }
        },

        async toggleActiveVolume(vol) {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/v1/inventory/settings/volumes/${vol.id}/toggle-active`, {
                    method: 'PATCH',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                const data = await res.json();
                if (res.ok) {
                    vol.is_active = data.data.is_active;
                    this.showToast(`Volumen "${vol.label}" ${vol.is_active ? 'activado' : 'desactivado'}.`, 'info');
                }
            } catch (err) {
                this.showToast('Error al alternar estado del volumen.', 'warning');
            }
        },

        async archiveVolume(vol) {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const isArchived = Boolean(vol.deleted_at);
                const url = isArchived 
                    ? `/api/v1/inventory/settings/volumes/${vol.id}/restore`
                    : `/api/v1/inventory/settings/volumes/${vol.id}/archive`;
                const method = isArchived ? 'POST' : 'DELETE';
                const res = await fetch(url, {
                    method: method,
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                if (res.ok) {
                    vol.deleted_at = isArchived ? null : new Date().toISOString();
                    this.showToast(`Volumen "${vol.label}" ${isArchived ? 'restaurado' : 'archivado'}.`, 'info');
                }
            } catch (err) {
                this.showToast('Error al archivar volumen.', 'warning');
            }
        },

        async deleteVolume(volId) {
            if (!confirm('Deseas eliminar permanentemente este volumen de perfumeria?')) return;
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/v1/inventory/settings/volumes/${volId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                if (res.ok) {
                    this.fragranceVolumes = this.fragranceVolumes.filter(v => v.id !== volId);
                    this.showToast('Volumen eliminado permanentemente.', 'success');
                }
            } catch (err) {
                this.showToast('Error al eliminar volumen.', 'warning');
            }
        },

        // --- CATEGORIAS & SUBCATEGORIAS CRUD ---
        async addNewCategory() {
            const name = (this.newCategoryForm.name || '').trim();
            if (!name) {
                this.showToast('Ingresa un nombre para la categoria o subcategoria.', 'warning');
                return;
            }
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const payload = {
                    name: name,
                    description: (this.newCategoryForm.description || '').trim() || null,
                    parent_id: this.newCategoryForm.parent_id ? parseInt(this.newCategoryForm.parent_id) : null,
                    is_active: this.newCategoryForm.is_active !== false
                };
                const res = await fetch('/api/v1/inventory/settings/categories', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok) {
                    this.showToast(data.message || 'Error al crear categoria.', 'warning');
                    return;
                }
                if (payload.parent_id) {
                    const parent = this.categoriesTree.find(c => c.id === payload.parent_id);
                    if (parent) {
                        if (!Array.isArray(parent.children)) parent.children = [];
                        parent.children.push(data.data);
                    } else {
                        await this.reloadCategoriesTree();
                    }
                } else {
                    data.data.children = [];
                    this.categoriesTree.push(data.data);
                }
                this.newCategoryForm = { name: '', description: '', parent_id: '', is_active: true };
                this.showToast(`Categoria "${data.data.name}" registrada con exito.`, 'success');
            } catch (err) {
                this.showToast('Error al crear categoria.', 'warning');
            }
        },

        editCategory(cat) {
            this.categoryEditState.isEditing = true;
            this.categoryEditState.editingId = cat.id;
            this.categoryEditState.form = {
                name: cat.name,
                description: cat.description || '',
                parent_id: cat.parent_id ? String(cat.parent_id) : '',
                is_active: cat.is_active !== false
            };
        },

        cancelEditCategory() {
            this.categoryEditState.isEditing = false;
            this.categoryEditState.editingId = null;
            this.categoryEditState.form = { name: '', description: '', parent_id: '', is_active: true };
        },

        async saveCategory() {
            const name = (this.categoryEditState.form.name || '').trim();
            if (!name) {
                this.showToast('El nombre de la categoria no puede estar vacio.', 'warning');
                return;
            }
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const payload = {
                    name: name,
                    description: (this.categoryEditState.form.description || '').trim() || null,
                    parent_id: this.categoryEditState.form.parent_id ? parseInt(this.categoryEditState.form.parent_id) : null,
                    is_active: this.categoryEditState.form.is_active !== false
                };
                const res = await fetch(`/api/v1/inventory/settings/categories/${this.categoryEditState.editingId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok) {
                    this.showToast(data.message || 'Error al actualizar categoria.', 'warning');
                    return;
                }
                await this.reloadCategoriesTree();
                this.cancelEditCategory();
                this.showToast(`Categoria "${data.data.name}" actualizada con exito.`, 'success');
            } catch (err) {
                this.showToast('Error al guardar categoria.', 'warning');
            }
        },

        async toggleActiveCategory(cat) {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/v1/inventory/settings/categories/${cat.id}/toggle-active`, {
                    method: 'PATCH',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                const data = await res.json();
                if (res.ok) {
                    cat.is_active = data.data.is_active;
                    this.showToast(`Categoria "${cat.name}" ${cat.is_active ? 'activada' : 'desactivada'}.`, 'info');
                }
            } catch (err) {
                this.showToast('Error al alternar estado de categoria.', 'warning');
            }
        },

        async archiveCategory(cat) {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const isArchived = Boolean(cat.deleted_at);
                const url = isArchived 
                    ? `/api/v1/inventory/settings/categories/${cat.id}/restore`
                    : `/api/v1/inventory/settings/categories/${cat.id}/archive`;
                const method = isArchived ? 'POST' : 'DELETE';
                const res = await fetch(url, {
                    method: method,
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                if (res.ok) {
                    cat.deleted_at = isArchived ? null : new Date().toISOString();
                    this.showToast(`Categoria "${cat.name}" ${isArchived ? 'restaurada' : 'archivada'}.`, 'info');
                }
            } catch (err) {
                this.showToast('Error al archivar categoria.', 'warning');
            }
        },

        async deleteCategory(catId) {
            if (!confirm('Deseas eliminar permanentemente esta categoria? Sus subcategorias seran desacopladas a la raiz.')) return;
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/v1/inventory/settings/categories/${catId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                if (res.ok) {
                    await this.reloadCategoriesTree();
                    this.showToast('Categoria eliminada permanentemente.', 'success');
                }
            } catch (err) {
                this.showToast('Error al eliminar categoria.', 'warning');
            }
        },

        async reloadCategoriesTree() {
            try {
                const res = await fetch('/api/v1/inventory/settings/categories?include_archived=1', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.categoriesTree = data.data || [];
                }
            } catch (err) {
                console.error('Error al recargar arbol de categorias', err);
            }
        },

        // --- TASK-INV-13: GESTOR DE PROVEEDORES & CONTROL DE ESTADOS ---
        get filteredProviders() {
            return (this.providers || []).filter(p => {
                const search = (this.providerSearch || '').toLowerCase().trim();
                const matchesSearch = !search || 
                    (p.name && p.name.toLowerCase().includes(search)) ||
                    (p.tradeName && p.tradeName.toLowerCase().includes(search)) ||
                    (p.contactName && p.contactName.toLowerCase().includes(search)) ||
                    (p.city && p.city.toLowerCase().includes(search)) ||
                    (p.vendorCode && p.vendorCode.toLowerCase().includes(search));
                const matchesStatus = this.providerStatusFilter === 'all' 
                    ? p.status !== 'archived' 
                    : p.status === this.providerStatusFilter;
                const matchesType = this.providerTypeFilter === 'all' || p.type === this.providerTypeFilter;
                return matchesSearch && matchesStatus && matchesType;
            });
        },

        get connectedProvidersCount() {
            return (this.providers || []).filter(p => p.status === 'connected').length;
        },

        get maintenanceProvidersCount() {
            return (this.providers || []).filter(p => p.status === 'maintenance').length;
        },

        get pausedProvidersCount() {
            return (this.providers || []).filter(p => p.status === 'paused').length;
        },

        get archivedProvidersCount() {
            return (this.providers || []).filter(p => p.status === 'archived').length;
        },

        get totalSyncedItemsCount() {
            return (this.providers || []).filter(p => p.status !== 'archived').reduce((acc, p) => acc + (p.syncedItemsCount || 0), 0);
        },

        get averageSlaScore() {
            const active = (this.providers || []).filter(p => p.status !== 'archived');
            if (!active.length) return 0;
            const totalOnTime = active.reduce((sum, p) => sum + (p.slaOnTimePct || 100), 0);
            const totalFillRate = active.reduce((sum, p) => sum + (p.slaFillRatePct || 100), 0);
            return Math.round(((totalOnTime + totalFillRate) / (active.length * 2)) * 10) / 10;
        },

        get filteredDiscrepancies() {
            if (this.discrepancyCategoryFilter === 'all') {
                return (this.scrapingAlerts || []);
            }
            return (this.scrapingAlerts || []).filter(a => a.category === this.discrepancyCategoryFilter);
        },

        openCreateProviderModal() {
            this.isCreatingProvider = true;
            this.isEditingProvider = false;
            this.selectedProvider = null;
            this.providerForm = {
                name: '',
                tradeName: '',
                type: 'workshop',
                typeLabel: 'Taller de Confeccion Textil',
                city: 'CDMX',
                contactName: '',
                phone: '',
                email: '',
                extractionMethod: 'Manual / Facturacion Directa',
                syncFrequency: 'Manual / On-demand',
                leadTimeDays: 5,
                paymentTerms: 'credito_30',
                status: 'connected',
                notes: ''
            };
            this.providerModalOpen = true;
        },

        openProviderDetails(provider) {
            this.isCreatingProvider = false;
            this.isEditingProvider = false;
            this.selectedProvider = provider;
            this.providerForm = { ...provider };
            this.providerModalOpen = true;
        },

        editProviderFromModal() {
            this.isEditingProvider = true;
        },

        cancelProviderEdit() {
            if (this.isCreatingProvider) {
                this.providerModalOpen = false;
            } else {
                this.isEditingProvider = false;
                this.providerForm = { ...this.selectedProvider };
            }
        },

        saveProvider() {
            if (!this.providerForm.name || !this.providerForm.name.trim()) {
                this.showToast('El nombre de la empresa es obligatorio.', 'warning');
                return;
            }
            if (!this.providerForm.contactName || !this.providerForm.contactName.trim()) {
                this.showToast('La persona de contacto es obligatoria.', 'warning');
                return;
            }
            if (!this.providerForm.phone || !this.providerForm.phone.trim()) {
                this.showToast('El telefono de compras es obligatorio.', 'warning');
                return;
            }

            const typeLabels = {
                workshop: 'Taller de Confeccion Textil',
                cosmetics: 'Fabricante de Cosmetica & Skincare',
                distributor: 'Distribuidor Mayorista',
                packaging: 'Insumos & Empaque',
                commercial: 'Proveedor Comercial Directo',
                scraper: 'Integracion Automatizada Scraper/API'
            };

            const computedLabel = typeLabels[this.providerForm.type] || this.providerForm.typeLabel || 'Proveedor Comercial';

            if (this.isCreatingProvider) {
                const newId = 'prov_' + Date.now();
                const codeSuffix = Math.floor(100 + Math.random() * 900);
                const cleanCode = (this.providerForm.tradeName || this.providerForm.name).replace(/[^a-zA-Z0-9]/g, '').substring(0, 4).toUpperCase();
                const newProvider = {
                    id: newId,
                    vendorCode: 'VND-' + cleanCode + '-' + codeSuffix,
                    name: this.providerForm.name.trim(),
                    tradeName: (this.providerForm.tradeName || this.providerForm.name).trim(),
                    type: this.providerForm.type || 'workshop',
                    typeLabel: computedLabel,
                    city: this.providerForm.city ? this.providerForm.city.trim() : 'Nacional',
                    contactName: this.providerForm.contactName.trim(),
                    phone: this.providerForm.phone.trim(),
                    email: this.providerForm.email ? this.providerForm.email.trim() : '',
                    extractionMethod: this.providerForm.extractionMethod || 'Manual / Facturacion Directa',
                    frequency: this.providerForm.syncFrequency || 'Manual / On-demand',
                    lastScrapeTime: 'Recien registrado',
                    syncedItemsCount: 0,
                    status: this.providerForm.status || 'connected',
                    leadTimeDays: parseInt(this.providerForm.leadTimeDays, 10) || 5,
                    paymentTerms: this.providerForm.paymentTerms || 'credito_30',
                    slaOnTimePct: 100.0,
                    slaFillRatePct: 100.0,
                    totalOrders: 0,
                    completedOrders: 0,
                    avgDelayDays: 0.0,
                    isSyncing: false,
                    notes: this.providerForm.notes || ''
                };
                this.providers.unshift(newProvider);
                this.providerModalOpen = false;
                this.showToast('Proveedor ' + newProvider.tradeName + ' registrado exitosamente.', 'success');
            } else {
                const target = this.providers.find(p => p.id === this.selectedProvider.id);
                if (target) {
                    target.name = this.providerForm.name.trim();
                    target.tradeName = (this.providerForm.tradeName || this.providerForm.name).trim();
                    target.type = this.providerForm.type;
                    target.typeLabel = computedLabel;
                    target.city = this.providerForm.city ? this.providerForm.city.trim() : target.city;
                    target.contactName = this.providerForm.contactName.trim();
                    target.phone = this.providerForm.phone.trim();
                    target.email = this.providerForm.email ? this.providerForm.email.trim() : target.email;
                    target.leadTimeDays = parseInt(this.providerForm.leadTimeDays, 10) || target.leadTimeDays;
                    target.paymentTerms = this.providerForm.paymentTerms;
                    target.status = this.providerForm.status;
                    target.notes = this.providerForm.notes || '';
                    this.selectedProvider = { ...target };
                }
                this.isEditingProvider = false;
                this.showToast('Datos de ' + (target ? target.tradeName : 'proveedor') + ' actualizados.', 'success');
            }
        },

        toggleProviderStatus(provider, newStatus) {
            const valid = ['connected', 'paused', 'maintenance', 'archived'];
            if (!valid.includes(newStatus)) return;
            provider.status = newStatus;
            if (this.selectedProvider && this.selectedProvider.id === provider.id) {
                this.selectedProvider.status = newStatus;
                this.providerForm.status = newStatus;
            }
            const labels = { connected: 'Conectado / Activo', paused: 'Pausado', maintenance: 'En Mantenimiento', archived: 'Archivado' };
            this.showToast('Proveedor ' + provider.tradeName + ': Estado cambiado a ' + labels[newStatus] + '.', 'info');
        },

        archiveProvider(provider) {
            if (!provider) return;
            provider.status = 'archived';
            if (this.selectedProvider && this.selectedProvider.id === provider.id) {
                this.selectedProvider.status = 'archived';
                this.providerForm.status = 'archived';
            }
            const name = provider.tradeName || provider.name;
            this.showToast('Proveedor "' + name + '" archivado exitosamente. Ahora aparece bajo el filtro Archivados.', 'info');
        },

        restoreProvider(provider) {
            if (!provider) return;
            provider.status = 'connected';
            if (this.selectedProvider && this.selectedProvider.id === provider.id) {
                this.selectedProvider.status = 'connected';
                this.providerForm.status = 'connected';
            }
            const name = provider.tradeName || provider.name;
            this.showToast('Proveedor "' + name + '" restaurado a estado Conectado.', 'success');
        },

        deleteProvider(provider) {
            if (!provider) return;
            const hasOrders = (provider.totalOrders || 0) > 0 || (provider.completedOrders || 0) > 0;
            const inUseCount = (this.products || []).filter(p => (p.supplier === provider.tradeName || p.supplier === provider.name || p.brand === provider.tradeName)).length;
            if (hasOrders || inUseCount > 0) {
                const motive = hasOrders 
                    ? (provider.totalOrders + ' Ã³rdenes de compra registradas') 
                    : (inUseCount + ' productos vinculados en catÃ¡logo');
                this.showToast('No es posible eliminar "' + (provider.tradeName || provider.name) + '" porque cuenta con ' + motive + '. Se recomienda archivar el proveedor para conservar la trazabilidad y auditorÃ­a contable.', 'warning');
                return;
            }
            const name = provider.tradeName || provider.name;
            this.providers = this.providers.filter(p => p.id !== provider.id);
            if (this.selectedProvider && this.selectedProvider.id === provider.id) {
                this.providerModalOpen = false;
                this.selectedProvider = null;
            }
            this.showToast('Proveedor "' + name + '" eliminado definitivamente.', 'success');
        },

        triggerProviderSync(provider) {
            if (provider.status === 'paused' || provider.status === 'archived') {
                this.showToast('No se puede sincronizar un proveedor en estado ' + (provider.status === 'archived' ? 'archivado' : 'pausado') + '.', 'warning');
                return;
            }
            provider.isSyncing = true;
            setTimeout(() => {
                provider.isSyncing = false;
                provider.lastScrapeTime = 'Hace unos momentos';
                this.showToast('Sincronizacion completada para ' + provider.tradeName + ': ' + provider.syncedItemsCount + ' SKUs al dia.', 'success');
            }, 800);
        },

        openScrapingCuration(pendingItem) {
            this.curatingItem = JSON.parse(JSON.stringify(pendingItem));
            this.scrapingCurationModalOpen = true;
        },

        approveScrapedItem() {
            if (!this.curatingItem) return;
            const item = this.curatingItem;
            const nextNum = String(this.products.length + 1).padStart(3, '0');
            const finalSku = item.suggestedSku || ('TOR-CUR-' + nextNum);

            const newProductObj = {
                sku: finalSku,
                name: item.name,
                category: item.suggestedCategory || 'Boxers',
                type: 'dropship',
                brand: item.brand || 'Torzo B2B',
                fabricType: item.suggestedFabric || 'Malla / Mesh Transpirable',
                colorName: item.detectedColorName || 'Negro Profundo',
                colorHex: item.detectedColorHex || '#0f172a',
                colorType: item.colorType || 'solid',
                designType: item.suggestedDesign || 'sin_diseno',
                websiteSection: item.suggestedSection || 'general',
                description: item.scrapedDescription || '',
                isPublished: true,
                localStock: 0,
                committedStock: 0,
                committedSizes: { ch: 0, m: 0, g: 0, xl: 0 },
                supplierStock: item.supplierStock || 50,
                location: 'AlmacÃ©n Proveedor (Torzo B2B)',
                status: 'Sincronizado',
                providerUrl: item.originUrl || 'https://torzo.example.com',
                isScraping: false,
                pvp: parseFloat(item.suggestedPvp) || 390,
                regularPrice: parseFloat(item.suggestedPvp) || 390,
                cost: parseFloat(item.cost) || 150,
                weightedAverageCost: parseFloat(item.cost) || 150,
                discountPct: 0,
                sizes: item.sizes || { ch: 10, m: 20, g: 15, xl: 5 },
                adjustType: 'add',
                adjustQty: '',
                adjustReason: ''
            };

            this.products.unshift(newProductObj);
            this.scrapedPendingItems = this.scrapedPendingItems.filter(p => p.id !== item.id);
            this.scrapingCurationModalOpen = false;
            this.curatingItem = null;
            this.showToast(`Prenda ${finalSku} curada, validada y publicada en el catÃ¡logo.`, 'success');
        },

        discardScrapedItem(id) {
            this.scrapedPendingItems = this.scrapedPendingItems.filter(p => p.id !== id);
            this.scrapingCurationModalOpen = false;
            this.curatingItem = null;
            this.showToast('Prenda scrapeada descartada de la bandeja de entrada.', 'info');
        },

        openProductDrawer(product) {
            this.selectedProduct = product;
            this.purchaseAddedSuccess = false;
            this.hasAddedPurchaseThisSession = false;
            this.drawerSizes = {
                ch: product.sizes?.ch || 0,
                m: product.sizes?.m || 0,
                g: product.sizes?.g || 0,
                xl: product.sizes?.xl || 0,
                '2xl': product.sizes?.['2xl'] || 0,
                '3xl': product.sizes?.['3xl'] || 0
            };
            this.drawerShades = product.shades ? { ...product.shades } : {};
            this.drawerLocation = product.location || '';
            this.drawerRegularPrice = parseFloat(product.regularPrice || product.regular_price || product.pvp || 0);
            this.drawerDiscountPct = parseFloat(product.discountPct || product.discount_pct || 0);
            this.drawerPvp = parseFloat(product.pvp || this.drawerRegularPrice);
            this.drawerCost = parseFloat(product.cost || 0);
            this.drawerCpp = parseFloat(product.weightedAverageCost || product.cpp || product.cost || 0);
            this.drawerBatchNumber = product.batchNumber || product.batch_number || '';
            this.drawerExpirationDate = product.expirationDate || product.expiration_date || '';
            this.drawerPaoMonths = parseInt(product.paoMonths || product.pao_months) || 12;
            this.drawerRequiresSanitaryControl = !!(product.requiresSanitaryControl || product.requires_sanitary_control);
            this.drawerFabricType = product.fabricType || product.fabric_type || 'Modal Seamless';
            this.drawerColorName = product.colorName || product.color_name || 'Negro Profundo';
            this.drawerColorHex = product.colorHex || product.color_hex || '#0f172a';
            this.drawerColorType = product.colorType || product.color_type || 'solid';
            this.drawerDesignType = product.designType || product.design_type || 'sin_diseno';
            this.drawerWebsiteSection = product.websiteSection || product.website_section || 'basicos';
            this.drawerIsPublished = product.isPublished !== undefined ? product.isPublished : (product.is_published !== undefined ? product.is_published : true);
            this.drawerDescription = product.description || '';
            this.drawerBrand = product.brand || (product.type === 'dropship' ? 'Torzo B2B' : 'ModaIntima Textil MX');

            // Sugerir proveedor y cantidades para futura orden
            if (product.type === 'dropship') {
                this.purchaseSupplier = 'Torzo B2B';
                this.purchaseQty = 40;
            } else if (product.category === 'Boxers') {
                this.purchaseSupplier = 'ModaIntima Textil MX';
                this.purchaseQty = 60;
            } else if (product.category === 'Tangas') {
                this.purchaseSupplier = 'Seamless Tech Apparel';
                this.purchaseQty = 50;
            } else {
                this.purchaseSupplier = 'Silk & Cotton Iberia SL';
                this.purchaseQty = 50;
            }
            this.purchasePriority = (product.localStock && product.localStock < 50) ? 'alta' : 'media';
            this.purchaseSizes = {
                ch: Math.round(this.purchaseQty * 0.15),
                m: Math.round(this.purchaseQty * 0.38),
                g: Math.round(this.purchaseQty * 0.32),
                xl: Math.round(this.purchaseQty * 0.10),
                '2xl': (product.sizes && product.sizes['2xl']) ? Math.min(4, Math.round(this.purchaseQty * 0.04)) : 0,
                '3xl': (product.sizes && product.sizes['3xl']) ? Math.min(2, Math.round(this.purchaseQty * 0.01)) : 0
            };
            this.drawerOpen = true;

            // Enriquecer reactivamente con el backend si estÃ¡ disponible
            if (product.sku) {
                fetch(`/api/v1/inventory/products/${encodeURIComponent(product.sku)}/detail`, {
                    headers: { 'Accept': 'application/json' }
                }).then(res => res.ok ? res.json() : null).then(data => {
                    if (data && data.product && this.selectedProduct && this.selectedProduct.sku === product.sku) {
                        if (data.product.sizes) {
                            this.drawerSizes = { ...this.drawerSizes, ...data.product.sizes };
                        }
                        if (data.product.shades) {
                            this.drawerShades = { ...this.drawerShades, ...data.product.shades };
                        }
                        if (data.product.regular_price) {
                            this.drawerRegularPrice = parseFloat(data.product.regular_price);
                        }
                        if (data.product.discount_pct !== undefined) {
                            this.drawerDiscountPct = parseFloat(data.product.discount_pct);
                        }
                        if (data.product.cpp !== undefined) {
                            this.drawerCpp = parseFloat(data.product.cpp);
                        }
                        if (data.stock) {
                            this.selectedProduct.localStock = data.stock.physical;
                            this.selectedProduct.committedStock = data.stock.committed;
                            this.selectedProduct.lockedStock = data.stock.locked;
                        }
                    }
                }).catch(() => {});
            }
        },

        totalSizesCount() {
            const ch = parseInt(this.drawerSizes.ch) || 0;
            const m = parseInt(this.drawerSizes.m) || 0;
            const g = parseInt(this.drawerSizes.g) || 0;
            const xl = parseInt(this.drawerSizes.xl) || 0;
            const xxl = parseInt(this.drawerSizes['2xl']) || 0;
            const xxxl = parseInt(this.drawerSizes['3xl']) || 0;
            return ch + m + g + xl + xxl + xxxl;
        },

        totalProductStockCount() {
            if (this.selectedProduct && this.selectedProduct.variantDimension === 'shade') {
                return Object.values(this.drawerShades || {}).reduce((acc, qty) => acc + (parseInt(qty) || 0), 0);
            }
            return this.totalSizesCount();
        },

        openCreateProductDrawer() {
            const availableCurves = this.getAvailableSizeCurves();
            const defaultCurve = availableCurves.find(c => c.is_default) || availableCurves[0];
            const defaultCurveId = defaultCurve ? defaultCurve.id : null;

            this.newProduct = {
                sku: '',
                skuLocked: true,
                isGeneratingSku: false,
                name: '',
                category: 'Boxers',
                subcategory: '',
                type: 'propio',
                aisle: 'A',
                rack: '02',
                bin: 'B',
                location: 'A-02-B',
                // TASK-INV-02 / TASK-INV-14: Variantes y Curvas
                variantDimension: 'size',
                selectedSizeCurveId: defaultCurveId,
                fabricType: 'Modal Seamless',
                colorName: 'Negro Profundo',
                colorHex: '#0f172a',
                colorType: 'solid',
                designType: 'sin_diseno',
                websiteSection: 'basicos',
                isPublished: true,
                description: '',
                bulletPoints: [],
                seoTags: ['boxer modal', 'ropa interior transpirable', 'sin costuras'],
                newTagInput: '',
                brand: 'ModaIntima Textil MX',
                discountPct: 0,
                regularPrice: 399.00,
                pvp: 399.00,
                cost: 135.00,
                weightedAverageCost: 135.00,
                sizes: { ch: 0, m: 0, g: 0, xl: 0, '2xl': 0, '3xl': 0 },
                shades: { t01: 0, t02: 0, t03: 0, t04: 0, t05: 0, t06: 0 },
                volumes: { v10: 0, v50: 0, v100: 0 },
                unitStock: 0,
                supplierStock: 40,
                provider: 'ModaIntima Textil MX',
                providerUrl: '',
                images: [],
                // TASK-INV-15: Auto-SKU, Deteccion de Duplicados & Copiloto IA
                duplicateRisk: 'none',
                duplicateMatches: [],
                duplicateScore: 0,
                duplicateChecked: false,
                isCheckingDuplicate: false,
                duplicateAlertModalOpen: false,
                duplicateSelectedMatch: null,
                forceCreateDespiteDuplicate: false,
                isGeneratingAi: false
            };
            this.isDraggingImages = false;
            this.newProductDrawerOpen = true;
            this.generateAutoSku();
        },

        closeCreateProductDrawer() {
            this.newProductDrawerOpen = false;
            this.isDraggingImages = false;
        },

        async generateAutoSku() {
            this.newProduct.isGeneratingSku = true;
            try {
                const payload = {
                    category: this.newProduct.category || 'Boxers',
                    brand: this.newProduct.brand || 'ModaIntima Textil MX',
                    variant_code: (this.newProduct.variantDimension === 'size' && this.newProduct.sizes) ? Object.keys(this.newProduct.sizes)[0] : null
                };
                const res = await fetch('/api/v1/inventory/products/generate-sku', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });
                const json = await res.json();
                if (json && json.status === 'success' && json.data && json.data.sku) {
                    this.newProduct.sku = json.data.sku;
                    this.newProduct.skuLocked = true;
                    this.showToast('SKU estructurado generado: ' + json.data.sku, 'success');
                } else {
                    throw new Error(json.message || 'Error en generador de SKU');
                }
            } catch (e) {
                // Fallback local garantizado
                const catPfx = (this.newProduct.category || 'TXT').substring(0, 3).toUpperCase();
                const brCode = (this.newProduct.brand || 'GEN').substring(0, 2).toUpperCase();
                const seq = String(this.products.length + 1).padStart(4, '0');
                this.newProduct.sku = `${catPfx}-${brCode}-${seq}`;
                this.newProduct.skuLocked = true;
                this.showToast('SKU sintetizado localmente: ' + this.newProduct.sku, 'info');
            } finally {
                this.newProduct.isGeneratingSku = false;
            }
        },

        toggleSkuLock() {
            this.newProduct.skuLocked = !this.newProduct.skuLocked;
            if (!this.newProduct.skuLocked) {
                this.showToast('Edicion manual de SKU habilitada.', 'info');
            } else {
                this.showToast('SKU bloqueado contra modificaciones accidentales.', 'info');
            }
        },

        async checkProductDuplicate() {
            const name = (this.newProduct.name || '').trim();
            if (name.length < 3) {
                this.newProduct.duplicateRisk = 'none';
                this.newProduct.duplicateMatches = [];
                this.newProduct.duplicateScore = 0;
                this.newProduct.duplicateChecked = false;
                this.newProduct.duplicateSelectedMatch = null;
                return;
            }

            this.newProduct.isCheckingDuplicate = true;
            try {
                const payload = {
                    name: name,
                    brand: this.newProduct.brand || '',
                    category: this.newProduct.category || '',
                    pvp: parseFloat(this.newProduct.pvp) || 0,
                    sku: this.newProduct.sku || ''
                };
                const res = await fetch('/api/v1/inventory/products/check-duplicate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });
                const json = await res.json();
                if (json && json.status === 'success' && json.data) {
                    const data = json.data;
                    this.newProduct.duplicateRisk = data.risk_level || 'none';
                    this.newProduct.duplicateMatches = data.matches || [];
                    this.newProduct.duplicateScore = Math.round(data.highest_score || 0);
                    this.newProduct.duplicateChecked = true;
                    this.newProduct.duplicateSelectedMatch = (data.matches && data.matches.length > 0) ? data.matches[0] : null;
                }
            } catch (e) {
                // Fallback local en memoria
                const clean = name.toLowerCase();
                const found = this.products.find(p => (p.name || '').toLowerCase() === clean);
                if (found) {
                    this.newProduct.duplicateRisk = 'duplicate_match';
                    this.newProduct.duplicateScore = 95;
                    this.newProduct.duplicateMatches = [{
                        sku: found.sku,
                        title: found.name,
                        brand: found.brand || 'ModaIntima Textil MX',
                        score: 95,
                        reasons: ['Coincidencia exacta de nombre en catalogo']
                    }];
                    this.newProduct.duplicateSelectedMatch = this.newProduct.duplicateMatches[0];
                    this.newProduct.duplicateChecked = true;
                } else {
                    this.newProduct.duplicateRisk = 'none';
                    this.newProduct.duplicateMatches = [];
                    this.newProduct.duplicateScore = 0;
                    this.newProduct.duplicateSelectedMatch = null;
                    this.newProduct.duplicateChecked = true;
                }
            } finally {
                this.newProduct.isCheckingDuplicate = false;
            }
        },

        openDuplicateModal() {
            if (this.newProduct.duplicateMatches && this.newProduct.duplicateMatches.length > 0) {
                this.newProduct.duplicateAlertModalOpen = true;
            }
        },

        closeDuplicateModal() {
            this.newProduct.duplicateAlertModalOpen = false;
        },

        resolveDuplicateAddStock() {
            const match = this.newProduct.duplicateSelectedMatch;
            if (!match) return;
            const targetSku = match.sku || (match.product && match.product.sku);
            const targetProd = this.products.find(p => p.sku === targetSku);
            const incomingQty = this.calculateNewProductTotalStock();

            if (targetProd) {
                targetProd.localStock = (parseInt(targetProd.localStock) || 0) + incomingQty;
                if (this.newProduct.variantDimension === 'size' && this.newProduct.sizes && targetProd.sizes) {
                    Object.entries(this.newProduct.sizes).forEach(([sz, qty]) => {
                        const q = parseInt(qty) || 0;
                        if (q > 0) {
                            targetProd.sizes[sz] = (parseInt(targetProd.sizes[sz]) || 0) + q;
                        }
                    });
                }
                this.showToast(`Se sumaron ${incomingQty} unidades al producto existente ${targetSku} (${targetProd.name}).`, 'success');
            } else {
                this.showToast(`Stock transferido a referencia ${targetSku}.`, 'success');
            }

            this.newProduct.duplicateAlertModalOpen = false;
            this.closeCreateProductDrawer();
        },

        resolveDuplicateContinueNew() {
            this.newProduct.forceCreateDespiteDuplicate = true;
            this.newProduct.duplicateAlertModalOpen = false;
            this.showToast('Advertencia de duplicado omitida por decision operativa.', 'info');
            this.saveNewProduct();
        },

        resolveDuplicateDismissDraft() {
            this.newProduct.duplicateAlertModalOpen = false;
            this.closeCreateProductDrawer();
            this.showToast('Borrador de alta descartado.', 'info');
        },

        async generateAiDescription() {
            if (!this.newProduct.name || this.newProduct.name.trim().length === 0) {
                this.showToast('Ingresa el nombre del producto para activar el Copiloto IA.', 'warning');
                return;
            }

            this.newProduct.isGeneratingAi = true;
            try {
                const payload = {
                    name: this.newProduct.name,
                    brand: this.newProduct.brand || '',
                    category: this.newProduct.category || '',
                    fabric_type: this.newProduct.fabricType || '',
                    color_name: this.newProduct.colorName || '',
                    design_type: this.newProduct.designType || '',
                    variant_dimension: this.newProduct.variantDimension || 'size',
                    pvp: parseFloat(this.newProduct.pvp) || 0
                };
                const res = await fetch('/api/v1/inventory/products/generate-description', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });
                const json = await res.json();
                if (json && json.status === 'success' && json.data) {
                    const data = json.data;
                    if (data.description) {
                        this.newProduct.description = data.description;
                    }
                    if (Array.isArray(data.bullet_points) && data.bullet_points.length > 0) {
                        this.newProduct.bulletPoints = data.bullet_points;
                    }
                    if (Array.isArray(data.seo_tags)) {
                        const set = new Set([...(this.newProduct.seoTags || []), ...data.seo_tags]);
                        this.newProduct.seoTags = Array.from(set);
                    }
                    this.showToast('Descripcion comercial y tags SEO generados exitosamente con Copiloto IA.', 'success');
                } else {
                    throw new Error(json.message || 'Error en Copiloto IA');
                }
            } catch (e) {
                // Fallback local sintetizado
                const name = this.newProduct.name;
                const brand = this.newProduct.brand || 'Torzo';
                const fabric = this.newProduct.fabricType || 'Algodon & Modal';
                const color = this.newProduct.colorName || 'Negro Clasico';
                this.newProduct.description = `Experimenta el maximo confort y soporte anatomico con ${name} de ${brand}. Confeccionado minuciosamente con ${fabric} en tono ${color}, ofrece una sensacion ultrasuave de segunda piel y transpirabilidad que se adapta a tus movimientos cotidianos.\n\nDiseno de silueta limpia y patronaje ergonomico sin costuras visibles que garantiza discrecion y alta durabilidad para tu guardarropa intimo.`;
                this.newProduct.bulletPoints = [
                    `Composicion textil premium: ${fabric}`,
                    `Ajuste ergonomico de segunda piel`,
                    `Costuras planas antirozaduras`,
                    `Tejido transpirable de secado rapido`
                ];
                this.showToast('Contenido comercial generado localmente.', 'info');
            } finally {
                this.newProduct.isGeneratingAi = false;
            }
        },

        addSeoTag() {
            const tag = (this.newProduct.newTagInput || '').trim().toLowerCase();
            if (tag && !this.newProduct.seoTags.includes(tag)) {
                this.newProduct.seoTags.push(tag);
            }
            this.newProduct.newTagInput = '';
        },

        removeSeoTag(index) {
            this.newProduct.seoTags.splice(index, 1);
        },

        getAvailableSizeCurves() {
            if (Array.isArray(this.sizeCurves) && this.sizeCurves.length > 0) {
                const active = this.sizeCurves.filter(c => c.is_active !== false && !c.deleted_at);
                if (active.length > 0) return active;
            }
            return [
                {
                    id: 1,
                    name: 'Americana (CH - 3XL)',
                    is_default: true,
                    sizes: [
                        { code: 'CH', label: 'Chica (S)' },
                        { code: 'M', label: 'Mediana (M)' },
                        { code: 'G', label: 'Grande (L)' },
                        { code: 'XL', label: 'Extra Grande (XL)' },
                        { code: '2XL', label: 'Doble Extra Grande (2XL)' },
                        { code: '3XL', label: 'Triple Extra Grande (3XL)' }
                    ]
                },
                {
                    id: 2,
                    name: 'Numerica Pantalon (28 - 40)',
                    is_default: false,
                    sizes: [
                        { code: '28', label: 'Talla 28' },
                        { code: '30', label: 'Talla 30' },
                        { code: '32', label: 'Talla 32' },
                        { code: '34', label: 'Talla 34' },
                        { code: '36', label: 'Talla 36' },
                        { code: '38', label: 'Talla 38' },
                        { code: '40', label: 'Talla 40' }
                    ]
                },
                {
                    id: 3,
                    name: 'Copas Bra (32B - 38D)',
                    is_default: false,
                    sizes: [
                        { code: '32B', label: 'Copa 32B' },
                        { code: '34B', label: 'Copa 34B' },
                        { code: '34C', label: 'Copa 34C' },
                        { code: '36B', label: 'Copa 36B' },
                        { code: '36C', label: 'Copa 36C' },
                        { code: '38C', label: 'Copa 38C' },
                        { code: '38D', label: 'Copa 38D' }
                    ]
                },
                {
                    id: 4,
                    name: 'Unitalla / Estandar',
                    is_default: false,
                    sizes: [
                        { code: 'UNI', label: 'Unitalla (Standard)' }
                    ]
                }
            ];
        },

        getCurrentNewProductSizeCurve() {
            const curves = this.getAvailableSizeCurves();
            if (!this.newProduct.selectedSizeCurveId) {
                const def = curves.find(c => c.is_default) || curves[0];
                if (def) this.newProduct.selectedSizeCurveId = def.id;
                return def;
            }
            return curves.find(c => String(c.id) === String(this.newProduct.selectedSizeCurveId)) || curves[0];
        },

        getCurrentNewProductSizes() {
            const curve = this.getCurrentNewProductSizeCurve();
            if (curve && Array.isArray(curve.sizes) && curve.sizes.length > 0) {
                return curve.sizes;
            }
            return [
                { code: 'CH', label: 'Chica (S)' },
                { code: 'M', label: 'Mediana (M)' },
                { code: 'G', label: 'Grande (L)' },
                { code: 'XL', label: 'Extra Grande (XL)' },
                { code: '2XL', label: '2XL' },
                { code: '3XL', label: '3XL' }
            ];
        },

        onSizeCurveChange() {
            const sizes = this.getCurrentNewProductSizes();
            const newSizes = {};
            sizes.forEach(s => {
                const key = (s.code || '').toLowerCase();
                newSizes[key] = (this.newProduct.sizes && this.newProduct.sizes[key]) ? this.newProduct.sizes[key] : 0;
            });
            this.newProduct.sizes = newSizes;
        },

        handleImageUpload(event) {
            const files = event && event.target ? event.target.files : event;
            if (!files || !files.length) return;
            this.processImageFiles(Array.from(files));
            if (event && event.target) {
                event.target.value = '';
            }
        },

        handleImageDrop(event) {
            this.isDraggingImages = false;
            const files = event && event.dataTransfer ? event.dataTransfer.files : null;
            if (!files || !files.length) return;
            this.processImageFiles(Array.from(files));
        },

        processImageFiles(fileArray) {
            if (!Array.isArray(this.newProduct.images)) {
                this.newProduct.images = [];
            }
            if (this.newProduct.images.length >= 10) {
                this.showToast('Limite alcanzado: Ya tienes el maximo de 10 imagenes para este producto.', 'warning');
                return;
            }

            const allowedExtensions = ['.jpg', '.jpeg', '.png', '.webp', '.avif'];
            const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];
            const maxSizeBytes = 5 * 1024 * 1024; // 5 MB

            let addedCount = 0;
            let skippedOversize = 0;
            let skippedInvalid = 0;
            let reachedMax = false;

            for (const file of fileArray) {
                if (this.newProduct.images.length >= 10) {
                    reachedMax = true;
                    break;
                }

                const fileName = (file.name || '').toLowerCase();
                const fileType = (file.type || '').toLowerCase();
                const isValidExtension = allowedExtensions.some(ext => fileName.endsWith(ext));
                const isValidMime = allowedMimeTypes.includes(fileType);

                if (!isValidExtension && !isValidMime && fileType !== '') {
                    skippedInvalid++;
                    continue;
                }

                if (file.size && file.size > maxSizeBytes) {
                    skippedOversize++;
                    continue;
                }

                const imageId = 'img_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6);
                const isFirst = this.newProduct.images.length === 0;

                let previewUrl = '';
                if (typeof URL !== 'undefined' && URL.createObjectURL && typeof file === 'object' && typeof Blob !== 'undefined' && file instanceof Blob) {
                    try {
                        previewUrl = URL.createObjectURL(file);
                    } catch (e) {
                        previewUrl = '';
                    }
                }
                if (!previewUrl && file.preview) {
                    previewUrl = file.preview;
                }

                const imageItem = {
                    id: imageId,
                    name: file.name || ('Foto ' + (this.newProduct.images.length + 1)),
                    size: file.size || 0,
                    type: fileType || 'image/jpeg',
                    preview: previewUrl || 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="%23e2e8f0"><rect width="100" height="100"/></svg>',
                    is_primary: isFirst,
                    sort_order: this.newProduct.images.length,
                    file: file
                };

                this.newProduct.images.push(imageItem);
                addedCount++;
            }

            if (addedCount > 0) {
                this.showToast(addedCount === 1 ? 'Fotografia agregada a la galeria.' : (addedCount + ' fotografias agregadas a la galeria.'), 'success');
            }
            if (skippedOversize > 0) {
                this.showToast(skippedOversize + ' archivo(s) omitido(s) por superar 5 MB.', 'warning');
            }
            if (skippedInvalid > 0) {
                this.showToast(skippedInvalid + ' archivo(s) omitido(s): solo se admiten JPG, PNG, WEBP y AVIF.', 'warning');
            }
            if (reachedMax) {
                this.showToast('Se alcanzo el limite maximo de 10 imagenes por producto.', 'info');
            }
        },

        removeImage(index) {
            if (!this.newProduct.images || !this.newProduct.images[index]) return;
            const removed = this.newProduct.images.splice(index, 1)[0];

            this.newProduct.images.forEach((img, i) => {
                img.sort_order = i;
            });

            if (removed.is_primary && this.newProduct.images.length > 0) {
                this.newProduct.images[0].is_primary = true;
            }

            this.showToast('Fotografia eliminada de la galeria.', 'info');
        },

        setPrimaryImage(index) {
            if (!this.newProduct.images || !this.newProduct.images[index]) return;
            this.newProduct.images.forEach((img, i) => {
                img.is_primary = (i === index);
            });
            this.showToast('Fotografia ' + (index + 1) + ' asignada como Portada principal.', 'success');
        },

        calculateNewProductTotalStock() {
            if (!this.newProduct) return 0;
            const dim = this.newProduct.variantDimension || 'size';
            if (dim === 'size') {
                const s = this.newProduct.sizes || {};
                return Object.values(s).reduce((acc, qty) => acc + (parseInt(qty) || 0), 0);
            }
            if (dim === 'shade') {
                const sh = this.newProduct.shades || {};
                return Object.values(sh).reduce((acc, qty) => acc + (parseInt(qty) || 0), 0);
            }
            if (dim === 'volume') {
                const v = this.newProduct.volumes || {};
                return Object.values(v).reduce((acc, qty) => acc + (parseInt(qty) || 0), 0);
            }
            if (dim === 'none') {
                return parseInt(this.newProduct.unitStock) || 0;
            }
            return 0;
        },

        onNewProductCategoryChange() {
            const cat = (this.newProduct.category || '').toLowerCase();
            if (cat.includes('cosm') || cat.includes('skin') || cat.includes('maquillaje') || cat.includes('cuidado')) {
                this.newProduct.variantDimension = 'shade';
                this.newProduct.aisle = 'C';
                this.updateNewProductLocation();
            } else if (cat.includes('perfum') || cat.includes('fraganc') || cat.includes('locion')) {
                this.newProduct.variantDimension = 'volume';
                this.newProduct.aisle = 'C';
                this.updateNewProductLocation();
            } else if (cat.includes('accesorio') || cat.includes('fijo')) {
                this.newProduct.variantDimension = 'none';
                this.updateNewProductLocation();
            } else {
                this.newProduct.variantDimension = 'size';
                if (this.newProduct.category === 'Jocks') {
                    this.newProduct.aisle = 'B';
                } else {
                    this.newProduct.aisle = 'A';
                }
                this.updateNewProductLocation();
            }
            // Auto regenerate SKU on category change
            this.generateAutoSku();
        },

        calculateNewProductProfit() {
            const pvp = parseFloat(this.newProduct.pvp) || 0;
            const cost = parseFloat(this.newProduct.cost) || 0;
            return Math.max(0, pvp - cost);
        },

        calculateNewProductMargin() {
            const pvp = parseFloat(this.newProduct.pvp) || 0;
            const cost = parseFloat(this.newProduct.cost) || 0;
            if (pvp <= 0) return '0.0';
            const m = ((pvp - cost) / pvp) * 100;
            return Math.max(0, m).toFixed(1);
        },

        saveNewProduct() {
            if (!this.newProduct.sku || !this.newProduct.sku.trim() || !this.newProduct.name || !this.newProduct.name.trim()) {
                this.showToast('Por favor completa el codigo SKU y el nombre del producto.', 'warning');
                return;
            }

            // Deteccion de duplicados: bloquear si hay match critico no confirmado
            if (!this.newProduct.forceCreateDespiteDuplicate && this.newProduct.duplicateRisk === 'duplicate_match' && this.newProduct.duplicateMatches && this.newProduct.duplicateMatches.length > 0) {
                this.newProduct.duplicateAlertModalOpen = true;
                this.showToast('Atencion: Se detecto un posible producto duplicado. Resuelve el conflicto para continuar.', 'warning');
                return;
            }

            const cleanSku = this.newProduct.sku.trim().toUpperCase();
            const exists = this.products.some(p => p.sku.toUpperCase() === cleanSku);
            if (exists) {
                this.showToast('El SKU ' + cleanSku + ' ya existe en el catalogo. Usa un codigo unico.', 'warning');
                return;
            }

            const totalStock = this.calculateNewProductTotalStock();
            const pvp = parseFloat(this.newProduct.pvp) || 0;
            const cost = parseFloat(this.newProduct.cost) || 0;
            const weightedAverageCost = parseFloat(this.newProduct.weightedAverageCost) || cost;

            const createdItem = {
                sku: cleanSku,
                name: this.newProduct.name.trim(),
                category: this.newProduct.category,
                subcategory: this.newProduct.subcategory || '',
                type: this.newProduct.type,
                variant_dimension: this.newProduct.variantDimension || 'size',
                images: Array.isArray(this.newProduct.images) ? [...this.newProduct.images] : [],
                localStock: this.newProduct.type === 'propio' ? totalStock : 0,
                supplierStock: this.newProduct.type === 'dropship' ? (parseInt(this.newProduct.supplierStock) || 0) : null,
                location: this.newProduct.type === 'propio' ? (this.newProduct.location || 'Bodega Principal') : ('Almacen Proveedor (' + this.newProduct.provider + ')'),
                status: (this.newProduct.type === 'propio' && totalStock > 0) ? 'En Stock' : (this.newProduct.type === 'dropship' ? 'Sincronizado' : 'Agotado'),
                pvp: pvp,
                cost: cost,
                weightedAverageCost: weightedAverageCost,
                description: this.newProduct.description || '',
                bulletPoints: Array.isArray(this.newProduct.bulletPoints) ? [...this.newProduct.bulletPoints] : [],
                seoTags: Array.isArray(this.newProduct.seoTags) ? [...this.newProduct.seoTags] : [],
                sizes: (this.newProduct.variantDimension === 'size') ? { ...this.newProduct.sizes } : undefined,
                shades: (this.newProduct.variantDimension === 'shade') ? { ...this.newProduct.shades } : undefined,
                volumes: (this.newProduct.variantDimension === 'volume') ? { ...this.newProduct.volumes } : undefined,
                adjustType: 'add',
                adjustQty: '',
                adjustReason: '',
                provider: this.newProduct.provider,
                providerUrl: this.newProduct.providerUrl,
                isScraping: false
            };

            this.products.unshift(createdItem);
            this.newProductDrawerOpen = false;
            this.showToast('Producto ' + createdItem.sku + ' agregado exitosamente al catalogo.', 'success');
        },

        sidebarWasExpanded: false,

        openTopTenDrawer() {
            this.topTenDrawerOpen = true;
            if (document.body && !document.body.classList.contains('collapsed')) {
                document.body.classList.add('collapsed');
                this.sidebarWasExpanded = true;
            }
        },

        closeTopTenDrawer() {
            this.topTenDrawerOpen = false;
            if (this.sidebarWasExpanded && document.body) {
                document.body.classList.remove('collapsed');
                this.sidebarWasExpanded = false;
            }
        },

        getTopTenProducts() {
            if (!this.products || this.products.length === 0) return [];
            return [...this.products]
                .sort((a, b) => {
                    const scoreA = ((a.localStock || 0) + (a.committedStock || 0)) * (a.pvp || 1);
                    const scoreB = ((b.localStock || 0) + (b.committedStock || 0)) * (b.pvp || 1);
                    return scoreB - scoreA;
                })
                .slice(0, 10);
        },

        getTopTenMetrics() {
            const list = this.getTopTenProducts();
            if (list.length === 0) {
                return {
                    totalRevenue: 0,
                    totalUnits: 0,
                    topSize: 'M',
                    topSizePct: 0,
                    avgMargin: 65.0
                };
            }
            let totalRevenue = 0;
            let totalUnits = 0;
            let totalMarginSum = 0;
            const sizeFrequency = {};

            list.forEach(p => {
                const stock = (p.localStock || 0) + (p.committedStock || 0);
                const pvp = p.pvp || 0;
                const cost = p.cost || p.weightedAverageCost || (pvp * 0.4);
                totalRevenue += (stock * pvp);
                totalUnits += stock;
                const margin = pvp > 0 ? ((pvp - cost) / pvp) * 100 : 60;
                totalMarginSum += margin;

                if (p.sizes) {
                    Object.keys(p.sizes).forEach(sz => {
                        const count = parseInt(p.sizes[sz], 10) || 0;
                        sizeFrequency[sz] = (sizeFrequency[sz] || 0) + count;
                    });
                }
            });

            let topSize = 'M';
            let maxCount = 0;
            Object.keys(sizeFrequency).forEach(sz => {
                if (sizeFrequency[sz] > maxCount) {
                    maxCount = sizeFrequency[sz];
                    topSize = sz.toUpperCase();
                }
            });

            const topSizePct = totalUnits > 0 ? Math.round((maxCount / totalUnits) * 100) : 42;
            const avgMargin = list.length > 0 ? Math.round((totalMarginSum / list.length) * 10) / 10 : 65.0;

            return {
                totalRevenue: totalRevenue > 0 ? totalRevenue : 450000,
                totalUnits: totalUnits > 0 ? totalUnits : 1200,
                topSize: topSize,
                topSizePct: topSizePct > 0 ? topSizePct : 40,
                avgMargin: avgMargin > 0 ? avgMargin : 65.0
            };
        },

        getTopSizesForProduct(item) {
            if (!item || !item.sizes) return [{ size: 'M', pct: 50 }, { size: 'G', pct: 50 }];
            const entries = Object.entries(item.sizes)
                .map(([size, qty]) => ({ size: size.toUpperCase(), qty: parseInt(qty, 10) || 0 }))
                .filter(e => e.qty > 0)
                .sort((a, b) => b.qty - a.qty);
            
            const total = entries.reduce((acc, e) => acc + e.qty, 0);
            if (total === 0 || entries.length === 0) return [{ size: 'M', pct: 50 }, { size: 'G', pct: 50 }];
            
            return entries.slice(0, 2).map(e => ({
                size: e.size,
                pct: Math.round((e.qty / total) * 100)
            }));
        },

        getProductMargin(item) {
            const pvp = parseFloat(item.pvp) || 0;
            const cost = parseFloat(item.cost || item.weightedAverageCost) || 0;
            if (pvp <= 0) return 60.0;
            return Math.round(((pvp - cost) / pvp) * 1000) / 10;
        },

        openDrawerForSku(sku) {
            const prod = this.products.find(p => p.sku === sku);
            if (prod) {
                this.openProductDrawer(prod);
            } else {
                this.showToast('Producto ' + sku + ' no encontrado en el catalogo.', 'info');
            }
        },

        openQuickAdjust(sku) {
            this.activeTab = 'inventory';
            this.searchFilter = sku;
            this.showToast('Filtrando catÃ¡logo por SKU: ' + sku + ' para ajuste rÃ¡pido.', 'info');
        },

        calculateProfit() {
            return (this.drawerPvp - this.drawerCost);
        },

        
        // =========================================================================
        // TASK-INV-04: MÃ‰TODOS DE VALUACIÃ“N CONTABLE (CPP VS ÃšLTIMO COSTO)
        // =========================================================================
        calculateCPP(currentStock, currentCPP, receivedQty, newCost) {
            const s = Math.max(0, parseInt(currentStock) || 0);
            const q = Math.max(0, parseInt(receivedQty) || 0);
            const prevCpp = parseFloat(currentCPP) || 0;
            const unitCost = parseFloat(newCost) || 0;

            if (s + q === 0) return Math.round(unitCost * 100) / 100;
            if (s === 0) return Math.round(unitCost * 100) / 100;
            if (q === 0) return Math.round(prevCpp * 100) / 100;

            const totalValuation = (s * prevCpp) + (q * unitCost);
            const totalQty = s + q;
            return Math.round((totalValuation / totalQty) * 100) / 100;
        },

        calculateRealMarginPercent(pvp = null, cpp = null) {
            const p = (pvp !== null && pvp !== undefined) ? parseFloat(pvp) : parseFloat(this.drawerPvp);
            const c = (cpp !== null && cpp !== undefined) ? parseFloat(cpp) : parseFloat(this.drawerCpp);
            if (!p || p <= 0) return 0;
            const m = ((p - c) / p) * 100;
            if (pvp !== null && pvp !== undefined) {
                return Math.round(m);
            }
            return Math.max(0, m).toFixed(1);
        },

        getMarginDifference(pvp = null, cost = null, cpp = null) {
            const p = (pvp !== null && pvp !== undefined) ? parseFloat(pvp) : parseFloat(this.drawerPvp);
            const lastCost = (cost !== null && cost !== undefined) ? parseFloat(cost) : parseFloat(this.drawerCost);
            const weightedCost = (cpp !== null && cpp !== undefined) ? parseFloat(cpp) : parseFloat(this.drawerCpp);
            const realMargin = parseFloat(this.calculateRealMarginPercent(p, weightedCost));
            const marginalMargin = parseFloat(this.calculateMarginPercent(p, lastCost));
            return Math.round((realMargin - marginalMargin) * 10) / 10;
        },

        getInboundCurrentCpp() {
            if (!this.receivingOrder) return 0;
            const product = this.products.find(p => p.sku === this.receivingOrder.sku);
            return product ? (product.weightedAverageCost || product.cost || 0) : 0;
        },

        getInboundUnitCost() {
            if (!this.receivingOrder) return 0;
            return this.receivingOrder.unitCost || (this.receivingOrder.qty > 0 ? (this.receivingOrder.totalCost / this.receivingOrder.qty) : (this.receivingOrder.costUnit || 0));
        },

        getEstimatedInboundCPP() {
            if (!this.receivingOrder) return 0;
            const product = this.products.find(p => p.sku === this.receivingOrder.sku);
            if (!product) return 0;
            const currentStock = product.localStock || 0;
            const currentCpp = product.weightedAverageCost || product.cost || 0;
            const receivedQty = this.calculateReceivingTotal();
            const unitCost = this.getInboundUnitCost() || product.cost || 0;
            return this.calculateCPP(currentStock, currentCpp, receivedQty, unitCost);
        },

        calculateMarginPercent(pvp = null, cost = null) {
            const p = (pvp !== null && pvp !== undefined) ? parseFloat(pvp) : parseFloat(this.drawerPvp);
            const c = (cost !== null && cost !== undefined) ? parseFloat(cost) : parseFloat(this.drawerCost);
            if (!p || p <= 0) return 0;
            const m = ((p - c) / p) * 100;
            if (pvp !== null && pvp !== undefined) {
                return Math.round(m);
            }
            return Math.max(0, m).toFixed(1);
        },

        drawerEffectivePvp() {
            const base = parseFloat(this.drawerRegularPrice) || parseFloat(this.drawerPvp) || 0;
            const disc = Math.min(100, Math.max(0, parseFloat(this.drawerDiscountPct) || 0));
            const eff = base * (1 - (disc / 100));
            return Math.round(eff * 100) / 100;
        },

        drawerUnitProfit() {
            const eff = this.drawerEffectivePvp();
            const cpp = parseFloat(this.drawerCpp) || parseFloat(this.drawerCost) || 0;
            return Math.round((eff - cpp) * 100) / 100;
        },

        drawerRealMarginPct() {
            const eff = this.drawerEffectivePvp();
            const cpp = parseFloat(this.drawerCpp) || 0;
            if (eff <= 0) return 0;
            const m = ((eff - cpp) / eff) * 100;
            return Math.max(0, m).toFixed(1);
        },

        drawerMarginalMarginPct() {
            const eff = this.drawerEffectivePvp();
            const cost = parseFloat(this.drawerCost) || 0;
            if (eff <= 0) return 0;
            const m = ((eff - cost) / eff) * 100;
            return Math.max(0, m).toFixed(1);
        },

        livePhysicalStock() {
            if (this.selectedProduct && this.selectedProduct.variantDimension === 'shade') {
                return Object.values(this.drawerShades || {}).reduce((acc, qty) => acc + (parseInt(qty) || 0), 0);
            }
            return this.totalSizesCount();
        },

        liveAvailableStock() {
            const phys = this.livePhysicalStock();
            const comm = this.selectedProduct ? (parseInt(this.selectedProduct.committedStock) || 0) : 0;
            const lock = this.selectedProduct ? (parseInt(this.selectedProduct.lockedStock) || 0) : 0;
            return Math.max(0, phys - comm - lock);
        },

        async saveDrawerChanges() {
            if (!this.selectedProduct) return;
            const sku = this.selectedProduct.sku;

            this.selectedProduct.sizes = { ...this.drawerSizes };
            if (this.selectedProduct.variantDimension === 'shade') {
                this.selectedProduct.shades = { ...this.drawerShades };
            }
            this.selectedProduct.location = this.drawerLocation;
            this.selectedProduct.regularPrice = parseFloat(this.drawerRegularPrice) || parseFloat(this.drawerPvp) || 0;
            this.selectedProduct.discountPct = parseFloat(this.drawerDiscountPct) || 0;
            this.selectedProduct.pvp = this.drawerEffectivePvp();
            this.selectedProduct.cost = parseFloat(this.drawerCost) || 0;
            this.selectedProduct.weightedAverageCost = parseFloat(this.drawerCpp) || this.selectedProduct.weightedAverageCost || this.selectedProduct.cost;
            this.selectedProduct.fabricType = this.drawerFabricType;
            this.selectedProduct.colorName = this.drawerColorName;
            this.selectedProduct.colorHex = this.drawerColorHex;
            this.selectedProduct.colorType = this.drawerColorType;
            this.selectedProduct.designType = this.drawerDesignType;
            this.selectedProduct.websiteSection = this.drawerWebsiteSection;
            this.selectedProduct.isPublished = this.drawerIsPublished;
            this.selectedProduct.description = this.drawerDescription;
            this.selectedProduct.brand = this.drawerBrand;
            if (this.drawerRequiresSanitaryControl || this.selectedProduct.requiresSanitaryControl) {
                this.selectedProduct.batchNumber = this.drawerBatchNumber;
                this.selectedProduct.expirationDate = this.drawerExpirationDate;
                this.selectedProduct.paoMonths = parseInt(this.drawerPaoMonths) || 12;
                this.selectedProduct.requiresSanitaryControl = true;
            }
            if (this.selectedProduct.type === 'propio') {
                this.selectedProduct.localStock = this.livePhysicalStock();
                if (this.selectedProduct.localStock === 0) this.selectedProduct.status = 'Agotado';
                else if (this.selectedProduct.localStock < 25) this.selectedProduct.status = 'Bajo Stock';
                else this.selectedProduct.status = 'En Stock';
            }
            this.showToast(`Cambios de ${sku} guardados correctamente.`, 'success');
            this.drawerOpen = false;

            try {
                const payload = {
                    name: this.selectedProduct.name,
                    brand: this.drawerBrand,
                    description: this.drawerDescription,
                    is_published: !!this.drawerIsPublished,
                    fabric_type: this.drawerFabricType,
                    color_name: this.drawerColorName,
                    color_hex: this.drawerColorHex,
                    color_type: this.drawerColorType,
                    design_type: this.drawerDesignType,
                    website_section: this.drawerWebsiteSection,
                    location: this.drawerLocation,
                    regular_price: parseFloat(this.drawerRegularPrice) || parseFloat(this.drawerPvp) || 0,
                    discount_pct: parseFloat(this.drawerDiscountPct) || 0,
                    pvp: this.drawerEffectivePvp(),
                    cost: parseFloat(this.drawerCost) || 0,
                    cpp: parseFloat(this.drawerCpp) || 0,
                    sizes: { ...this.drawerSizes },
                    shades: { ...this.drawerShades },
                    physical_stock: this.livePhysicalStock(),
                    batch_number: this.drawerBatchNumber,
                    expiration_date: this.drawerExpirationDate,
                    pao_months: parseInt(this.drawerPaoMonths) || 12,
                    requires_sanitary_control: !!this.drawerRequiresSanitaryControl
                };

                const res = await fetch(`/api/v1/inventory/products/${encodeURIComponent(sku)}/update-complete`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data && data.financials) {
                        this.selectedProduct.pvp = data.financials.effective_price;
                        this.selectedProduct.weightedAverageCost = data.financials.cpp;
                    }
                }
            } catch (err) {
                console.warn('Persistencia remota en segundo plano no disponible (fallback local aplicado):', err);
            }
        },

        formatCurrency(val) {
            return '$' + (Number(val) || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' MXN';
        },

        showToast(msg, type = 'success') {
            if (this.toast.timeout) clearTimeout(this.toast.timeout);
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            this.toast.timeout = setTimeout(() => {
                this.toast.show = false;
            }, 3500);
        },

        triggerGlobalRefresh() {
            this.isRefreshing = true;
            this.showToast('Actualizando mÃ©tricas de inventario y conexiones...', 'info');
            setTimeout(() => {
                this.isRefreshing = false;
                this.showToast('Inventario y mÃ©tricas sincronizados con Ã©xito.', 'success');
            }, 900);
        },

        triggerScraperSync() {
            this.showToast('Disparando scraper headless en Torzo B2B...', 'info');
            setTimeout(() => {
                this.lastScrapeTime = 'Hace unos instantes';
                this.showToast('Scraping completado: 42 variantes verificadas.', 'success');
            }, 1600);
        },

        // TASK-INV-01: MÃ©todos de Slotting y UbicaciÃ³n FÃ­sica
        getSlotBadgeClass(location) {
            if (!location || location === 'N/A' || location.includes('Dropship')) {
                return 'bg-slate-100 text-slate-600 border-slate-200';
            }
            const clean = location.toUpperCase();
            if (clean.startsWith('A') || clean.includes('RACK A')) {
                return 'bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100';
            }
            if (clean.startsWith('B') || clean.includes('RACK B')) {
                return 'bg-purple-50 text-purple-700 border-purple-200 hover:bg-purple-100';
            }
            if (clean.startsWith('C') || clean.includes('RACK C')) {
                return 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100';
            }
            return 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100';
        },

        formatSlotCode(location) {
            if (!location) return 'N/A';
            if (location.includes('Rack A-01')) return 'A-01-B';
            if (location.includes('Rack A-02')) return 'A-02-A';
            if (location.includes('Rack B-03')) return 'B-03-A';
            if (location.includes('Rack B-01')) return 'B-01-B';
            if (location.includes('Rack C-02')) return 'C-02-B';
            if (location.includes('Rack C-01')) return 'C-01-A';
            return location;
        },

        updateNewProductLocation() {
            this.newProduct.location = `${this.newProduct.aisle}-${this.newProduct.rack}-${this.newProduct.bin}`;
        },

        openSlotReassign(item) {
            this.slotReassignModal.isOpen = true;
            this.slotReassignModal.sku = item.sku;
            this.slotReassignModal.productName = item.name;
            this.slotReassignModal.currentLocation = item.location;
            const currentCode = this.formatSlotCode(item.location);
            const parts = currentCode.split('-');
            if (parts.length === 3) {
                this.slotReassignModal.selectedAisle = parts[0];
                this.slotReassignModal.selectedRack = parts[1];
                this.slotReassignModal.selectedBin = parts[2];
            } else {
                this.slotReassignModal.selectedAisle = 'A';
                this.slotReassignModal.selectedRack = '01';
                this.slotReassignModal.selectedBin = 'A';
            }
            this.slotReassignModal.notes = '';
        },

        saveSlotReassign() {
            const newCode = `${this.slotReassignModal.selectedAisle}-${this.slotReassignModal.selectedRack}-${this.slotReassignModal.selectedBin}`;
            const target = this.products.find(p => p.sku === this.slotReassignModal.sku);
            if (target) {
                const prev = target.location;
                target.location = newCode;
                this.inventoryMovements.unshift({
                    id: Date.now(),
                    date: new Date().toISOString().slice(0, 10),
                    sku: target.sku,
                    product: target.name,
                    size: 'TODAS',
                    type: 'REUBICACION_SLOT',
                    qty: 0,
                    reference: `SLOT: ${prev} -> ${newCode}`,
                    user: 'Admin Bodega',
                    notes: this.slotReassignModal.notes || 'Reasignacion de slot fisico de almacen'
                });
                this.showToast(`Ubicacion actualizada para ${target.sku} a ${newCode}`, 'success');
            }
            this.slotReassignModal.isOpen = false;
        },

        // =====================================================================
        // TASK-INV-03: METODOS DE RECETAS BOM Y LIQUIDACION DE COMBOS
        // =====================================================================
        openBundleRecipesModal(bundleId) {
            if (bundleId) {
                this.bundleRecipesModal.selectedBundleId = bundleId;
            }
            this.bundleRecipesModal.isOpen = true;
        },

        closeBundleRecipesModal() {
            this.bundleRecipesModal.isOpen = false;
        },

        // =========================================================================
        // METODOS DE AUDITORIA CICLICA A CIEGAS (TASK-INV-07: 3 SKUS DIARIOS)
        // =========================================================================
        openCycleCountModal() {
            // Seleccionar 3 SKUs fÃ­sicos de bodega (exclusivamente stock propio con existencias en rack):
            // 1) 1 SKU Clase A de alta rotaciÃ³n en bodega
            // 2) 1 SKU de prendas textiles bÃ¡sicas
            // 3) 1 SKU de categorÃ­a sensible (cosmÃ©tica/cuidado personal)
            const propios = this.products.filter(p => p.type === 'propio' && (p.localStock || 0) > 0);
            const skuClaseA = propios.find(p => this.getAbcClass(p) === 'A') || propios[0] || this.products[0];
            const skuTextile = propios.find(p => p.sku !== skuClaseA.sku && !p.requiresSanitaryControl) || propios[1] || this.products[1];
            const skuCosmetic = propios.find(p => p.requiresSanitaryControl && p.sku !== skuClaseA.sku && p.sku !== skuTextile.sku) ||
                                propios.find(p => p.sku !== skuClaseA.sku && p.sku !== skuTextile.sku) ||
                                propios[2] || this.products[2];

            const sample = [skuClaseA, skuTextile, skuCosmetic].filter(Boolean);
            this.cycleCountModal.sampleItems = sample;
            this.cycleCountModal.sampleSkus = sample.map(s => s.sku);
            this.cycleCountModal.activeSkuIndex = 0;
            this.cycleCountModal.step = 'count';

            // Inicializar inputs de conteo ciego a cero
            sample.forEach(item => {
                if (!this.cycleCountModal.counts[item.sku]) {
                    this.cycleCountModal.counts[item.sku] = {
                        sizes: { ch: '', m: '', g: '', xl: '', '2xl': '', '3xl': '' },
                        shades: { t01: '', t02: '', t03: '', t04: '', t05: '', t06: '' },
                        volumes: { v10: '', v50: '', v100: '' },
                        unitQty: '',
                        isCounted: false
                    };
                }
                if (!this.cycleCountModal.discrepancyReasons[item.sku]) {
                    this.cycleCountModal.discrepancyReasons[item.sku] = 'Merma operativa';
                }
            });

            this.cycleCountModal.isOpen = true;
        },

        closeCycleCountModal() {
            this.cycleCountModal.isOpen = false;
        },

        get cycleCountActiveItem() {
            if (!this.cycleCountModal.sampleItems || !this.cycleCountModal.sampleItems.length) return null;
            return this.cycleCountModal.sampleItems[this.cycleCountModal.activeSkuIndex] || null;
        },

        getCycleCountItemTotal(sku) {
            if (!sku || !this.cycleCountModal.counts[sku]) return 0;
            const c = this.cycleCountModal.counts[sku];
            let total = 0;
            if (c.unitQty !== '' && c.unitQty !== null && !isNaN(Number(c.unitQty))) {
                total += Number(c.unitQty);
            }
            if (c.sizes) {
                Object.values(c.sizes).forEach(val => {
                    if (val !== '' && val !== null && !isNaN(Number(val))) total += Number(val);
                });
            }
            if (c.shades) {
                Object.values(c.shades).forEach(val => {
                    if (val !== '' && val !== null && !isNaN(Number(val))) total += Number(val);
                });
            }
            if (c.volumes) {
                Object.values(c.volumes).forEach(val => {
                    if (val !== '' && val !== null && !isNaN(Number(val))) total += Number(val);
                });
            }
            return total;
        },

        submitBlindCounts() {
            // Marcar todos como contados
            this.cycleCountModal.sampleItems.forEach(item => {
                if (this.cycleCountModal.counts[item.sku]) {
                    this.cycleCountModal.counts[item.sku].isCounted = true;
                }
            });

            // Generar reporte de discrepancias
            this.cycleCountModal.step = 'reconcile';
        },

        get cycleCountReport() {
            if (!this.cycleCountModal.sampleItems || !this.cycleCountModal.sampleItems.length) {
                return { rows: [], totalDiscrepancy: 0, accuracyRate: 100 };
            }

            let totalPhysical = 0;
            let totalSystem = 0;
            let totalDiffAbs = 0;

            const rows = this.cycleCountModal.sampleItems.map(item => {
                const physical = this.getCycleCountItemTotal(item.sku);
                const system = Number(item.localStock || 0);
                const diff = physical - system;

                totalPhysical += physical;
                totalSystem += system;
                totalDiffAbs += Math.abs(diff);

                return {
                    sku: item.sku,
                    name: item.name,
                    category: item.category,
                    location: item.location || 'A-01-A',
                    physical: physical,
                    system: system,
                    diff: diff,
                    reason: this.cycleCountModal.discrepancyReasons[item.sku] || 'Merma operativa'
                };
            });

            const netDiscrepancy = totalPhysical - totalSystem;
            const accuracyRate = totalSystem > 0 
                ? Math.max(0, Math.min(100, 100 - ((totalDiffAbs / totalSystem) * 100)))
                : 100;

            return {
                rows: rows,
                totalPhysical: totalPhysical,
                totalSystem: totalSystem,
                totalDiscrepancy: netDiscrepancy,
                accuracyRate: accuracyRate
            };
        },

        confirmCycleCountReconciliation() {
            const report = this.cycleCountReport;
            let adjustedCount = 0;
            const countsPayload = [];

            report.rows.forEach(row => {
                const reason = this.cycleCountModal.discrepancyReasons[row.sku] || 'Merma operativa';
                countsPayload.push({
                    sku: row.sku,
                    physical_counted: row.physical,
                    reason: reason
                });

                if (row.diff !== 0) {
                    const product = this.products.find(p => p.sku === row.sku);
                    if (product) {
                        const stockBefore = product.localStock || 0;
                        const stockAfter = row.physical;
                        product.localStock = stockAfter;

                        // Actualizar variantes si aplica
                        const counts = this.cycleCountModal.counts[row.sku];
                        if (counts) {
                            if (product.sizes && counts.sizes) {
                                Object.keys(counts.sizes).forEach(sz => {
                                    if (counts.sizes[sz] !== '') product.sizes[sz] = Number(counts.sizes[sz]);
                                });
                            }
                            if (product.shades && counts.shades) {
                                Object.keys(counts.shades).forEach(sh => {
                                    if (counts.shades[sh] !== '') product.shades[sh] = Number(counts.shades[sh]);
                                });
                            }
                            if (product.volumes && counts.volumes) {
                                Object.keys(counts.volumes).forEach(vol => {
                                    if (counts.volumes[vol] !== '') product.volumes[vol] = Number(counts.volumes[vol]);
                                });
                            }
                        }

                        // Asiento inmutable en Kardex (Regla Transversal de Auditoria)
                        this.recordInventoryMovement({
                            type: 'AJUSTE_AUDITORIA_CIEGA',
                            sku: product.sku,
                            productName: product.name,
                            delta: row.diff,
                            stockBefore: stockBefore,
                            stockAfter: stockAfter,
                            reference: 'AuditorÃ­a Diaria (Ciega)',
                            user: 'Auditor de AlmacÃ©n (Conteo CÃ­clico)',
                            note: 'Motivo: ' + reason + ' | Discrepancia: ' + (row.diff > 0 ? '+' : '') + row.diff + ' uds'
                        });

                        adjustedCount++;
                    }
                }
            });

            // Enviar auditoria a ciegas con conciliacion Kardex al backend
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch('/api/v1/inventory/cycle-counts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    counts: countsPayload,
                    reconcile_kardex: true
                })
            }).then(r => r.json()).then(data => {
                if (data && data.success) {
                    if (this.cycleCountHistory) {
                        this.cycleCountHistory.unshift({
                            id: data.cycle_count_id,
                            date: new Date().toLocaleDateString('es-MX'),
                            skuCount: countsPayload.length,
                            totalCounted: data.total_units_counted || 0,
                            totalVariance: data.total_variance_units || 0,
                            iraScore: data.ira_accuracy_score || 100,
                            auditedBy: 'Operador AlmacÃ©n',
                            status: data.status || 'reconciled',
                            details: data.details || []
                        });
                    }
                }
            }).catch(err => {
                console.error('Error al persistir auditoria ciclica:', err);
            });

            this.cycleCountModal.isCompletedToday = true;
            this.cycleCountModal.isOpen = false;

            if (adjustedCount > 0) {
                this.showToast('AuditorÃ­a completada: ' + adjustedCount + ' SKU(s) reconciliados en Kardex');
            } else {
                this.showToast('AuditorÃ­a completada al 100% de exactitud: Sin discrepancias');
            }
        },

        get selectedBundle() {
            return this.productBundles.find(b => b.id === this.bundleRecipesModal.selectedBundleId) || this.productBundles[0];
        },

        get filteredBundles() {
            if (this.bundleRecipesModal.activeFilter === 'all') return this.productBundles;
            return this.productBundles.filter(b => b.type === this.bundleRecipesModal.activeFilter);
        },

        getBundleComponentStock(sku, size) {
            const product = this.products.find(p => p.sku === sku);
            if (!product) return 0;
            if (size && product.sizes && typeof product.sizes[size] !== 'undefined') {
                return product.sizes[size] || 0;
            }
            if (size && product.volumes && typeof product.volumes[size] !== 'undefined') {
                return product.volumes[size] || 0;
            }
            if (size && product.shades && typeof product.shades[size] !== 'undefined') {
                return product.shades[size] || 0;
            }
            return product.localStock || product.unitStock || 0;
        },

        calculateBundleStock(bundle) {
            if (!bundle || !bundle.components || bundle.components.length === 0) return 0;
            let maxBoxes = Infinity;
            for (const comp of bundle.components) {
                const available = this.getBundleComponentStock(comp.sku, comp.size);
                const boxesForThisComp = Math.floor(available / comp.qty);
                if (boxesForThisComp < maxBoxes) {
                    maxBoxes = boxesForThisComp;
                }
            }
            return maxBoxes === Infinity ? 0 : maxBoxes;
        },

        liquidateBundle(bundleId, qty, reference, notes) {
            const bundle = this.productBundles.find(b => b.id === bundleId);
            if (!bundle) {
                this.showToast('Receta no encontrada en catalogo de combos', 'error');
                return false;
            }
            const requestedQty = parseInt(qty, 10);
            if (isNaN(requestedQty) || requestedQty <= 0) {
                this.showToast('Indica una cantidad valida de cajas a despachar', 'error');
                return false;
            }

            // 1. Validar disponibilidad de stock para cada componente
            for (const comp of bundle.components) {
                const needed = comp.qty * requestedQty;
                const available = this.getBundleComponentStock(comp.sku, comp.size);
                if (available < needed) {
                    this.showToast(`Stock insuficiente para ${comp.sku} (${comp.size || 'std'}). Se requieren ${needed} uds y hay ${available} disponibles.`, 'error');
                    return false;
                }
            }

            // 2. Descontar stock de forma atomica en cada producto y registrar en Kardex
            const ref = reference || `SUB-BATCH-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}`;
            let totalDeductedPieces = 0;

            bundle.components.forEach(comp => {
                const unitsToDeduct = comp.qty * requestedQty;
                totalDeductedPieces += unitsToDeduct;
                const product = this.products.find(p => p.sku === comp.sku);
                if (product) {
                    const stockBefore = product.localStock;
                    product.localStock = Math.max(0, product.localStock - unitsToDeduct);
                    if (comp.size && product.sizes && typeof product.sizes[comp.size] !== 'undefined') {
                        product.sizes[comp.size] = Math.max(0, product.sizes[comp.size] - unitsToDeduct);
                    } else if (comp.size && product.volumes && typeof product.volumes[comp.size] !== 'undefined') {
                        product.volumes[comp.size] = Math.max(0, product.volumes[comp.size] - unitsToDeduct);
                    } else if (comp.size && product.shades && typeof product.shades[comp.size] !== 'undefined') {
                        product.shades[comp.size] = Math.max(0, product.shades[comp.size] - unitsToDeduct);
                    }
                    if (typeof product.unitStock !== 'undefined') {
                        product.unitStock = Math.max(0, product.unitStock - unitsToDeduct);
                    }

                    // Registrar asiento inmutable en Kardex
                    this.inventoryMovements.unshift({
                        id: 'MOV-2026-' + String(this.inventoryMovements.length + 101).padStart(3, '0'),
                        timestamp: 'Hoy, ' + new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }) + ' hrs',
                        type: 'SALIDA_COMBO_SUSCRIPCION',
                        sku: comp.sku,
                        productName: product.name,
                        sizes: comp.size ? { [comp.size]: -unitsToDeduct } : {},
                        delta: -unitsToDeduct,
                        stockBefore: stockBefore,
                        stockAfter: product.localStock,
                        reference: ref,
                        user: 'Despacho BOM Masivo',
                        note: `Liquidacion BOM lote ${requestedQty}x ${bundle.name} (${bundle.sku}). ${notes || ''}`.trim()
                    });
                }
            });

            this.showToast(`Despacho exitoso: Se liquidaron ${requestedQty} cajas (${totalDeductedPieces} unidades de insumos) con referencia ${ref}`, 'success');
            this.bundleRecipesModal.isOpen = false;
            this.openKardexForRef(ref);
            return true;
        },

        // =====================================================================
        // TASK-INV-17: GESTOR CRUD DE RECETAS BOM & COMBOS EN AJUSTES
        // =====================================================================
        openNewBundleModal() {
            this.bundleEditor.isEditing = false;
            this.bundleEditor.bundleId = null;
            this.bundleEditor.form = {
                sku: 'BND-' + Math.random().toString(36).substring(2, 7).toUpperCase(),
                name: '',
                type: 'subscription',
                tierLevel: 'nivel_1_refill',
                tierName: 'Nivel 1: Refill Esencial',
                subscriptionPrice: 499.00,
                regularPrice: 600.00,
                discountPct: 15,
                isActive: true,
                badgeColor: 'indigo',
                description: '',
                components: []
            };
            this.bundleEditor.newComponent = {
                sku: this.products && this.products[0] ? this.products[0].sku : 'BX-MOD-001',
                size: 'm',
                qty: 1
            };
            this.bundleEditor.isOpen = true;
        },

        openEditBundleModal(bundleId) {
            const bundle = this.productBundles.find(b => b.id === bundleId);
            if (!bundle) {
                this.showToast('Receta no encontrada', 'error');
                return;
            }
            this.bundleEditor.isEditing = true;
            this.bundleEditor.bundleId = bundle.id;
            this.bundleEditor.form = {
                sku: bundle.sku || bundle.id,
                name: bundle.name,
                type: bundle.type || 'subscription',
                tierLevel: bundle.tierLevel || 'custom_pack',
                tierName: bundle.tierName || bundle.name,
                subscriptionPrice: typeof bundle.subscriptionPrice !== 'undefined' ? bundle.subscriptionPrice : 499.00,
                regularPrice: typeof bundle.regularPrice !== 'undefined' ? bundle.regularPrice : 600.00,
                discountPct: bundle.discountPct || 0,
                isActive: typeof bundle.isActive !== 'undefined' ? bundle.isActive : true,
                badgeColor: bundle.badgeColor || 'indigo',
                description: bundle.description || '',
                components: JSON.parse(JSON.stringify(bundle.components || []))
            };
            this.bundleEditor.newComponent = {
                sku: this.products && this.products[0] ? this.products[0].sku : 'BX-MOD-001',
                size: 'm',
                qty: 1
            };
            this.bundleEditor.isOpen = true;
        },

        addBundleComponentToForm() {
            const sku = this.bundleEditor.newComponent.sku;
            const size = this.bundleEditor.newComponent.size || 'm';
            const qty = parseInt(this.bundleEditor.newComponent.qty, 10) || 1;
            if (!sku) {
                this.showToast('Selecciona una prenda del catalogo operativo', 'error');
                return;
            }
            const product = this.products.find(p => p.sku === sku);
            if (!product) {
                this.showToast('Producto no encontrado en el catalogo', 'error');
                return;
            }

            // Verificar si ya existe el SKU y variante en componentes
            const existingIndex = this.bundleEditor.form.components.findIndex(c => c.sku === sku && c.size === size);
            if (existingIndex >= 0) {
                this.bundleEditor.form.components[existingIndex].qty += qty;
                this.showToast(`Cantidad incrementada a ${this.bundleEditor.form.components[existingIndex].qty} uds para ${product.name}`, 'info');
            } else {
                this.bundleEditor.form.components.push({
                    sku: product.sku,
                    name: product.name,
                    size: size,
                    qty: qty,
                    location: product.location || 'A-01-A'
                });
                this.showToast(`Prenda asignada a la receta: ${product.name}`, 'success');
            }
        },

        removeBundleComponentFromForm(index) {
            this.bundleEditor.form.components.splice(index, 1);
            this.showToast('Prenda retirada de la receta', 'info');
        },

        saveBundleRecipe() {
            if (!this.bundleEditor.form.name || !this.bundleEditor.form.name.trim()) {
                this.showToast('El nombre comercial de la receta o kit es obligatorio', 'error');
                return;
            }
            if (!this.bundleEditor.form.sku || !this.bundleEditor.form.sku.trim()) {
                this.showToast('El SKU padre del combo es obligatorio', 'error');
                return;
            }
            if (!this.bundleEditor.form.components || this.bundleEditor.form.components.length === 0) {
                this.showToast('Debes seleccionar al menos una prenda que forme parte de esta receta', 'error');
                return;
            }

            const cleanSku = this.bundleEditor.form.sku.trim().toUpperCase();
            const cleanPrice = parseFloat(this.bundleEditor.form.subscriptionPrice) || 0;
            const cleanRegPrice = parseFloat(this.bundleEditor.form.regularPrice) || cleanPrice;
            let disc = 0;
            if (cleanRegPrice > cleanPrice) {
                disc = Math.round(((cleanRegPrice - cleanPrice) / cleanRegPrice) * 100);
            }

            if (this.bundleEditor.isEditing) {
                const index = this.productBundles.findIndex(b => b.id === this.bundleEditor.bundleId);
                if (index >= 0) {
                    this.productBundles[index] = {
                        ...this.productBundles[index],
                        sku: cleanSku,
                        name: this.bundleEditor.form.name.trim(),
                        type: this.bundleEditor.form.type,
                        tierLevel: this.bundleEditor.form.tierLevel,
                        tierName: this.bundleEditor.form.tierName || this.bundleEditor.form.name.trim(),
                        subscriptionPrice: cleanPrice,
                        regularPrice: cleanRegPrice,
                        discountPct: disc,
                        isActive: this.bundleEditor.form.isActive,
                        badgeColor: this.bundleEditor.form.type === 'subscription' ? 'indigo' : 'emerald',
                        description: this.bundleEditor.form.description.trim() || 'Receta de ensamble configurada por Almacen.',
                        components: JSON.parse(JSON.stringify(this.bundleEditor.form.components))
                    };
                    this.showToast(`Receta BOM actualizada: ${this.bundleEditor.form.name}`, 'success');
                }
            } else {
                // Comprobar si existe SKU duplicado
                if (this.productBundles.some(b => b.id === cleanSku || b.sku === cleanSku)) {
                    this.showToast('Ya existe una receta registrada con este SKU', 'error');
                    return;
                }
                const newBundle = {
                    id: cleanSku,
                    sku: cleanSku,
                    name: this.bundleEditor.form.name.trim(),
                    type: this.bundleEditor.form.type,
                    tierLevel: this.bundleEditor.form.tierLevel || 'custom_pack',
                    tierName: this.bundleEditor.form.tierName || this.bundleEditor.form.name.trim(),
                    subscriptionPrice: cleanPrice,
                    regularPrice: cleanRegPrice,
                    discountPct: disc,
                    isActive: true,
                    badgeColor: this.bundleEditor.form.type === 'subscription' ? 'indigo' : 'emerald',
                    description: this.bundleEditor.form.description.trim() || 'Receta de ensamble creada por Almacen.',
                    components: JSON.parse(JSON.stringify(this.bundleEditor.form.components))
                };
                this.productBundles.unshift(newBundle);
                this.showToast(`Nueva receta BOM creada exitosamente: ${newBundle.name}`, 'success');
            }

            // Persistir de forma asincrona en base de datos
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const payload = {
                id: this.bundleEditor.isEditing ? this.bundleEditor.bundleId : null,
                sku: cleanSku,
                name: this.bundleEditor.form.name.trim(),
                type: this.bundleEditor.form.type,
                subscriptionPrice: cleanPrice,
                regularPrice: cleanRegPrice,
                isActive: this.bundleEditor.form.isActive,
                components: this.bundleEditor.form.components
            };
            fetch('/api/v1/inventory/bundles', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            }).then(r => r.json()).then(data => {
                if (data && data.success && data.bundle && data.bundle.id) {
                    const item = this.productBundles.find(b => b.id === cleanSku || b.sku === cleanSku || b.id === this.bundleEditor.bundleId);
                    if (item) {
                        item.dbId = data.bundle.id;
                    }
                }
            }).catch(err => {
                console.error('Error al persistir receta BOM en backend:', err);
            });

            this.bundleEditor.isOpen = false;
        },

        archiveBundleRecipe(bundleId) {
            const bundle = this.productBundles.find(b => b.id === bundleId);
            if (!bundle) return;
            bundle.isActive = !bundle.isActive;
            this.showToast(bundle.isActive ? `Receta BOM reactivada: ${bundle.name}` : `Receta BOM archivada: ${bundle.name}`, 'info');
        },

        deleteBundleRecipe(bundleId) {
            const index = this.productBundles.findIndex(b => b.id === bundleId);
            if (index === -1) return;
            const name = this.productBundles[index].name;
            if (confirm(`Â¿Estas seguro de eliminar la receta BOM "${name}"? Esta accion no se puede deshacer.`)) {
                this.productBundles.splice(index, 1);
                this.showToast(`Receta BOM eliminada: ${name}`, 'info');
            }
        },

        getFilteredProductBundles() {
            return this.productBundles.filter(b => {
                if (this.bundleEditor.filterStatus === 'active' && b.isActive === false) return false;
                if (this.bundleEditor.filterStatus === 'archived' && b.isActive !== false) return false;
                if (this.bundleEditor.filterType !== 'all' && b.type !== this.bundleEditor.filterType) return false;
                if (this.bundleEditor.searchQuery && this.bundleEditor.searchQuery.trim()) {
                    const q = this.bundleEditor.searchQuery.toLowerCase();
                    const matchName = (b.name || '').toLowerCase().includes(q);
                    const matchSku = (b.sku || b.id || '').toLowerCase().includes(q);
                    const matchComp = (b.components || []).some(c => (c.name || '').toLowerCase().includes(q) || (c.sku || '').toLowerCase().includes(q));
                    if (!matchName && !matchSku && !matchComp) return false;
                }
                return true;
            });
        },

        // =====================================================================
        // TASK-INT-11: CALCULO DE DEFICIT BOM Y DISPARO AUTOMATICO A B2B
        // =====================================================================
        getComponentSupplier(sku) {
            const cosmeticSkus = ['PF-MAR-017', 'JB-INT-016', 'COS-SRM-50ML', 'COS-CLNS-150', 'COS-BALM-15'];
            if (cosmeticSkus.includes(sku)) {
                return {
                    supplierId: 'VND-TERRA',
                    supplierName: 'Terra Labs Cosmetics',
                    category: 'Cosmetica & Cuidado Personal'
                };
            }
            return {
                supplierId: 'VND-TORZO',
                supplierName: 'Torzo Labs & Textile Co.',
                category: 'Textiles & Confeccion de Ropa Interior'
            };
        },

        calculateLotDeficit(bundleId, targetQty) {
            const bId = bundleId || this.bomDeficitState.selectedBundleId;
            const bundle = this.productBundles.find(b => b.id === bId);
            if (!bundle) return null;

            const qty = parseInt(targetQty !== undefined ? targetQty : this.bomDeficitState.targetQty, 10) || 1;
            const componentsAnalysis = [];
            const supplierGroups = {};
            let totalRequired = 0;
            let totalAvailable = 0;
            let totalDeficit = 0;

            bundle.components.forEach(comp => {
                const required = comp.qty * qty;
                const available = this.getBundleComponentStock(comp.sku, comp.size);
                const deficit = Math.max(0, required - available);
                const isCovered = deficit === 0;
                const product = this.products.find(p => p.sku === comp.sku);
                const unitCost = (product && product.cost) ? product.cost : 135.00;
                const supplier = this.getComponentSupplier(comp.sku);

                totalRequired += required;
                totalAvailable += Math.min(available, required);
                totalDeficit += deficit;

                const item = {
                    sku: comp.sku,
                    name: comp.name,
                    size: comp.size || 'std',
                    location: comp.location || 'A-01-A',
                    qtyPerBox: comp.qty,
                    requiredQty: required,
                    availableStock: available,
                    deficitQty: deficit,
                    isCovered: isCovered,
                    unitCost: unitCost,
                    estimatedDeficitCost: deficit * unitCost,
                    supplierId: supplier.supplierId,
                    supplierName: supplier.supplierName,
                    category: supplier.category
                };

                componentsAnalysis.push(item);

                if (!supplierGroups[supplier.supplierId]) {
                    supplierGroups[supplier.supplierId] = {
                        supplierId: supplier.supplierId,
                        supplierName: supplier.supplierName,
                        category: supplier.category,
                        items: [],
                        totalRequiredPieces: 0,
                        totalDeficitPieces: 0,
                        totalEstimatedCost: 0
                    };
                }
                supplierGroups[supplier.supplierId].items.push(item);
                supplierGroups[supplier.supplierId].totalRequiredPieces += required;
                supplierGroups[supplier.supplierId].totalDeficitPieces += deficit;
                supplierGroups[supplier.supplierId].totalEstimatedCost += (deficit * unitCost);
            });

            const result = {
                bundleId: bundle.id,
                bundleSku: bundle.sku,
                bundleName: bundle.name,
                targetQty: qty,
                totalRequiredPieces: totalRequired,
                totalAvailablePieces: totalAvailable,
                totalDeficitPieces: totalDeficit,
                hasDeficit: totalDeficit > 0,
                components: componentsAnalysis,
                supplierGroups: supplierGroups
            };

            this.bomDeficitState.result = result;
            return result;
        },

        openBomDeficitModal(bundleId, defaultQty) {
            if (bundleId) {
                this.bomDeficitState.selectedBundleId = bundleId;
            }
            if (defaultQty) {
                this.bomDeficitState.targetQty = defaultQty;
            }
            this.calculateLotDeficit(this.bomDeficitState.selectedBundleId, this.bomDeficitState.targetQty);
            this.bomDeficitState.isOpen = true;
        },

        closeBomDeficitModal() {
            this.bomDeficitState.isOpen = false;
        },

        generateB2BPurchaseOrders() {
            if (!this.bomDeficitState.result) {
                this.calculateLotDeficit();
            }
            const res = this.bomDeficitState.result;
            if (!res || !res.hasDeficit) {
                this.showToast('No existe deficit de insumos para el lote simulado. El stock cubre la demanda al 100%.', 'info');
                return false;
            }

            this.bomDeficitState.isProcessing = true;

            const batchRef = this.bomDeficitState.batchReference || `SUB-BATCH-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}`;
            const procurementCode = `BOM-PROC-${new Date().toISOString().slice(0, 7).replace('-', '')}-${String(Math.floor(100 + Math.random() * 900))}`;
            this.bomDeficitState.lastProcurementCode = procurementCode;

            const ordersToCreate = [];

            Object.values(res.supplierGroups).forEach((group) => {
                if (group.totalDeficitPieces <= 0) return;

                const poCode = `OC-2026-BOM-${String(Math.floor(100 + Math.random() * 900))}`;
                const sizesBreakdown = {};
                group.items.forEach(it => {
                    if (it.deficitQty > 0) {
                        const key = `${it.sku}${it.size !== 'std' ? '-' + it.size.toUpperCase() : ''}`;
                        sizesBreakdown[key] = it.deficitQty;
                    }
                });

                const firstDeficitItem = group.items.find(it => it.deficitQty > 0) || group.items[0];

                const orderData = {
                    poCode: poCode,
                    bomProcurementCode: procurementCode,
                    bomBatchReference: batchRef,
                    issueDate: new Date().toISOString().slice(0, 10),
                    expectedDockDate: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10),
                    targetDockDeadline: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10),
                    supplierId: group.supplierId,
                    supplierName: group.supplierName,
                    sku: firstDeficitItem ? firstDeficitItem.sku : res.bundleSku,
                    productName: `Insumos BOM Deficit Lote ${res.targetQty}x ${res.bundleSku} (${group.category})`,
                    sizes: sizesBreakdown,
                    totalPieces: group.totalDeficitPieces,
                    unitCostMxn: group.items.length > 0 ? (group.totalEstimatedCost / group.totalDeficitPieces) : 135.00,
                    totalAmountMxn: group.totalEstimatedCost,
                    productionStatus: 'in_production',
                    productionStatusLabel: 'Lote BOM En Taller de Fabricacion',
                    fabricStatus: 'Material Programado y Corte Asignado',
                    notes: `Orden generada automaticamente por auditoria de deficit BOM (${procurementCode}) para lote de ${res.targetQty} cajas de ${res.bundleName}. Fecha fatal en rampa.`
                };

                ordersToCreate.push(orderData);
            });

            if (ordersToCreate.length === 0) {
                this.bomDeficitState.isProcessing = false;
                this.showToast('No se generaron ordenes de compra: todos los grupos de insumos estan cubiertos.', 'info');
                return false;
            }

            // Inyectar en app.js / window
            if (typeof window !== 'undefined') {
                if (typeof window.injectBomProcurementOrders === 'function') {
                    window.injectBomProcurementOrders(ordersToCreate, {
                        batchReference: batchRef,
                        bundleSku: res.bundleSku,
                        targetBoxes: res.targetQty
                    });
                } else if (window.vendorPortalState && Array.isArray(window.vendorPortalState.orders)) {
                    ordersToCreate.forEach(ord => {
                        window.vendorPortalState.orders.unshift(ord);
                    });
                }
            }

            this.bomDeficitState.lastGeneratedOrders = ordersToCreate;
            this.bomDeficitState.isProcessing = false;

            // Persistir deficit en base de datos y crear OCs borrador en backend
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch('/api/v1/inventory/bundles/audit-deficit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    bundle_sku: res.bundleSku,
                    bundle_id: res.bundleId,
                    target_units: res.targetQty,
                    batch_reference: batchRef
                })
            }).then(r => r.json()).then(data => {
                if (data && data.success && Array.isArray(data.purchase_orders) && data.purchase_orders.length > 0) {
                    data.purchase_orders.forEach(po => {
                        const exists = this.activePurchaseOrders.some(p => p.id === po.id || p.poCode === po.id);
                        if (!exists) {
                            this.activePurchaseOrders.unshift({
                                id: po.id,
                                dbId: po.dbId,
                                supplier: po.supplierName,
                                supplier_id: po.supplierId,
                                carrier: 'Paqueteria Express (BOM Guia Pendiente)',
                                eta: 'En 7 dias',
                                sku: res.bundleSku,
                                productName: `Insumos BOM Deficit Lote ${res.targetQty}x ${res.bundleSku}`,
                                qty: po.totalPieces,
                                sizes: {},
                                unitCost: po.totalPieces > 0 ? (po.subtotal / po.totalPieces) : 135.00,
                                totalCost: po.totalAmount,
                                date: new Date().toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' }),
                                status: 'Borrador',
                                notes: po.notes
                            });
                        }
                    });
                }
            }).catch(err => {
                console.error('Error al sincronizar auditoria BOM con backend:', err);
            });

            const generatedPoCodes = ordersToCreate.map(o => o.poCode).join(', ');
            this.showToast(`Exito: Se generaron ${ordersToCreate.length} ordenes de compra (${generatedPoCodes}) enviadas a Extranet B2B.`, 'success');
            return ordersToCreate;
        },

        // [TASK-INV-05] Metodos de Generacion e Impresion de Etiquetas Termicas (Code128 / QR)
        openThermalLabelModal(product, variant = null, copies = 1) {
            if (!product) return;
            this.thermalLabelModal.isOpen = true;
            this.thermalLabelModal.isBatch = false;
            this.thermalLabelModal.batchTitle = '';
            this.thermalLabelModal.batchItems = [];
            this.thermalLabelModal.sku = product.sku || '';
            this.thermalLabelModal.productName = product.name || '';
            this.thermalLabelModal.category = product.category || 'General';
            this.thermalLabelModal.location = product.location || 'A-01-A';
            this.thermalLabelModal.pvp = product.pvp || 0;
            this.thermalLabelModal.copies = Math.max(1, parseInt(copies) || 1);
            this.thermalLabelModal.labelFormat = '50x30';
            this.thermalLabelModal.includeQr = true;
            this.thermalLabelModal.batchNumber = product.batchNumber || '';
            this.thermalLabelModal.expirationDate = product.expirationDate || '';
            this.thermalLabelModal.paoMonths = product.paoMonths || 0;
            this.thermalLabelModal.requiresSanitaryControl = !!product.requiresSanitaryControl;

            // Deteccion automatica de dimensiones de variante (TASK-INV-02)
            if (product.category === 'Cosmetica' || (product.shades && Object.keys(product.shades).length > 0)) {
                this.thermalLabelModal.variantType = 'shade';
                this.thermalLabelModal.availableVariants = product.shades ? Object.keys(product.shades).map(k => k.toUpperCase()) : ['TONO 01', 'TONO 02', 'TONO 03', 'TONO 04', 'TONO 05', 'TONO 06'];
                this.thermalLabelModal.selectedVariant = variant ? variant.toUpperCase() : (this.thermalLabelModal.availableVariants[0] || 'TONO 01');
            } else if (product.category === 'Perfumeria' || (product.volumes && Object.keys(product.volumes).length > 0)) {
                this.thermalLabelModal.variantType = 'volume';
                this.thermalLabelModal.availableVariants = product.volumes ? Object.keys(product.volumes).map(k => k.toUpperCase()) : ['10ML', '50ML', '100ML'];
                this.thermalLabelModal.selectedVariant = variant ? variant.toUpperCase() : (this.thermalLabelModal.availableVariants[0] || '50ML');
            } else if (product.category === 'Cuidado Personal' || product.category === 'Accesorios' || product.unitStock !== undefined) {
                this.thermalLabelModal.variantType = 'unit';
                this.thermalLabelModal.availableVariants = ['UNIDAD'];
                this.thermalLabelModal.selectedVariant = 'UNIDAD';
            } else {
                this.thermalLabelModal.variantType = 'size';
                this.thermalLabelModal.availableVariants = product.sizes ? Object.keys(product.sizes).map(k => k.toUpperCase()) : ['CH', 'M', 'G', 'XL', '2XL', '3XL'];
                this.thermalLabelModal.selectedVariant = variant ? variant.toUpperCase() : (this.thermalLabelModal.availableVariants.includes('M') ? 'M' : this.thermalLabelModal.availableVariants[0] || 'M');
            }
        },

        closeThermalLabelModal() {
            this.thermalLabelModal.isOpen = false;
        },

        openInboundBatchLabels() {
            if (!this.receivingOrder) {
                this.showToast('No hay una orden de recepcion activa', 'warning');
                return;
            }

            const targetProduct = this.products.find(p => p.sku === this.receivingOrder.sku) || {
                sku: this.receivingOrder.sku,
                name: this.receivingOrder.productName || 'Producto Inbound',
                location: 'A-01-A',
                pvp: 0,
                category: 'General'
            };

            const batch = [];
            if (this.receivingSizes) {
                for (const [sizeKey, qty] of Object.entries(this.receivingSizes)) {
                    const numQty = parseInt(qty) || 0;
                    if (numQty > 0) {
                        batch.push({
                            sku: targetProduct.sku,
                            name: targetProduct.name,
                            variant: sizeKey.toUpperCase(),
                            variantType: 'size',
                            location: targetProduct.location || 'A-01-A',
                            pvp: targetProduct.pvp || 0,
                            qty: numQty
                        });
                    }
                }
            }

            if (batch.length === 0) {
                const totalPieces = parseInt(this.receivingOrder.qty) || 1;
                batch.push({
                    sku: targetProduct.sku,
                    name: targetProduct.name,
                    variant: 'ESTANDAR',
                    variantType: 'unit',
                    location: targetProduct.location || 'A-01-A',
                    pvp: targetProduct.pvp || 0,
                    qty: totalPieces
                });
            }

            const totalPieces = batch.reduce((sum, item) => sum + item.qty, 0);

            this.thermalLabelModal.isOpen = true;
            this.thermalLabelModal.isBatch = true;
            this.thermalLabelModal.batchTitle = `Lote Inbound ${this.receivingOrder.orderId || 'ORD-INBOUND'} (${totalPieces} etiquetas)`;
            this.thermalLabelModal.sku = targetProduct.sku;
            this.thermalLabelModal.productName = targetProduct.name;
            this.thermalLabelModal.category = targetProduct.category || 'General';
            this.thermalLabelModal.location = targetProduct.location || 'A-01-A';
            this.thermalLabelModal.pvp = targetProduct.pvp || 0;
            this.thermalLabelModal.batchItems = batch;
            this.thermalLabelModal.copies = totalPieces;
            this.thermalLabelModal.labelFormat = '50x30';
            this.thermalLabelModal.includeQr = true;

            this.receiveOrderModalOpen = false;
            this.showToast(`Generado lote de impresion de ${totalPieces} etiquetas termicas para ${targetProduct.sku}`, 'info');
        },

        openBulkLabelsModal() {
            if (!this.selectedSkus || this.selectedSkus.length === 0) {
                this.showToast('Selecciona al menos un producto en el catalogo', 'warning');
                return;
            }

            const batch = [];
            this.selectedSkus.forEach(sku => {
                const p = this.products.find(item => item.sku === sku);
                if (p) {
                    batch.push({
                        sku: p.sku,
                        name: p.name,
                        variant: 'M',
                        variantType: 'size',
                        location: p.location || 'A-01-A',
                        pvp: p.pvp || 0,
                        qty: 1
                    });
                }
            });

            this.thermalLabelModal.isOpen = true;
            this.thermalLabelModal.isBatch = true;
            this.thermalLabelModal.batchTitle = `Lote Seleccion (${batch.length} productos / ${batch.length} etiquetas)`;
            this.thermalLabelModal.sku = batch[0].sku;
            this.thermalLabelModal.productName = batch[0].name;
            this.thermalLabelModal.category = 'Seleccion Masiva';
            this.thermalLabelModal.location = batch[0].location;
            this.thermalLabelModal.pvp = batch[0].pvp;
            this.thermalLabelModal.batchItems = batch;
            this.thermalLabelModal.copies = batch.length;
            this.thermalLabelModal.labelFormat = '50x30';
            this.thermalLabelModal.includeQr = true;

            this.showToast(`Preparadas ${batch.length} etiquetas termicas para impresion en lote`, 'info');
        },

        getBatchTotalPieces() {
            if (!this.thermalLabelModal.batchItems || this.thermalLabelModal.batchItems.length === 0) return 0;
            return this.thermalLabelModal.batchItems.reduce((acc, item) => acc + (parseInt(item.qty) || 0), 0);
        },

        generateBarcodeSvg(sku) {
            if (!sku) sku = 'PROD-000';
            const clean = sku.replace(/[^A-Za-z0-9_-]/g, '').toUpperCase();
            let rects = [];
            let x = 8;
            const barHeight = 36;

            // Guard bar inicial
            rects.push(`<rect x="${x}" y="0" width="2" height="${barHeight}" fill="#000000"/>`);
            x += 3;
            rects.push(`<rect x="${x}" y="0" width="1" height="${barHeight}" fill="#000000"/>`);
            x += 3;

            for (let i = 0; i < clean.length; i++) {
                const code = clean.charCodeAt(i);
                const w1 = (code % 3) + 1;
                const space1 = ((code >> 1) % 2) + 1;
                const w2 = ((code >> 2) % 3) + 1;
                const space2 = ((code >> 3) % 2) + 1;

                rects.push(`<rect x="${x}" y="0" width="${w1}" height="${barHeight}" fill="#000000"/>`);
                x += w1 + space1;
                rects.push(`<rect x="${x}" y="0" width="${w2}" height="${barHeight}" fill="#000000"/>`);
                x += w2 + space2;
            }

            // Guard bar final
            rects.push(`<rect x="${x}" y="0" width="2" height="${barHeight}" fill="#000000"/>`);
            x += 3;
            rects.push(`<rect x="${x}" y="0" width="3" height="${barHeight}" fill="#000000"/>`);
            x += 8;

            return `<svg viewBox="0 0 ${Math.max(x, 140)} 40" class="w-full h-9 block" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">${rects.join('')}</svg>`;
        },

        printThermalLabels() {
            const count = this.thermalLabelModal.isBatch ? this.getBatchTotalPieces() : this.thermalLabelModal.copies;
            this.showToast(`Enviando ${count} etiqueta(s) a impresion termica...`, 'success');
            setTimeout(() => {
                if (typeof window !== 'undefined' && window.print) {
                    window.print();
                }
            }, 350);
        }
    };
}
function hybridInventoryBody() {
    // La vista canonica del Hub 4 reside en resources/views/portal/inventory.blade.php
    // y es renderizada en el servidor por Laravel en la ruta /admin/inventario-catalogo.
    if (typeof window !== 'undefined' && window.location.pathname !== '/admin/inventario-catalogo') {
        window.location.href = '/admin/inventario-catalogo';
    }
    return `
<div class="p-8 text-center text-slate-500 font-sans">
    <div class="inline-block animate-spin w-6 h-6 border-2 border-indigo-600 border-t-transparent rounded-full mb-3"></div>
    <div class="text-sm font-semibold text-slate-700">Cargando modulo de inventario y compras...</div>
</div>
    `;
}

if (typeof window !== 'undefined') {
    window.hybridInventoryBody = hybridInventoryBody;
    window.hybridInventoryModule = hybridInventoryModule;
    if (window.Alpine) {
        window.Alpine.data('hybridInventoryModule', hybridInventoryModule);
    } else {
        document.addEventListener('alpine:init', () => {
            Alpine.data('hybridInventoryModule', hybridInventoryModule);
        });
    }
}
