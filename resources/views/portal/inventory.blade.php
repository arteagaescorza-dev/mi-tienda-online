@extends('layouts.portal-panel')

@section('title', 'Inventario & Cadena de Suministro')
@section('hub_kicker', 'Hub 4')
@section('hub_title', 'Inventario & Cadena de Suministro')
@section('hub_status', 'Operativo')

@section('content')
{{-- 
  Módulo de Inventario Híbrido y Catálogo - Plataforma RevOps E-Commerce B2C (Ropa Interior)
  Stack: Laravel Blade + Tailwind CSS + Alpine.js
  Ubicación sugerida en Laravel: resources/views/inventory/index.blade.php
--}}

<div 
    x-data="hybridInventoryModule()" 
    x-init="init()"
    @open-module-settings.window="moduleSettingsModalOpen = true"
    class="w-full text-slate-800 font-sans antialiased"
>
    <!-- TOAST NOTIFICATION FLOATING SYSTEM (z-[100] garantiza visibilidad sobre drawers y modales) -->
    <div 
        x-show="toast.show" 
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-6 right-6 z-[100] flex items-center gap-3 bg-slate-900 text-white px-4 py-3 rounded-xl shadow-2xl border border-slate-700 max-w-md"
        style="z-index: 100; display: none;"
    >
        <div :class="toast.type === 'success' ? 'text-emerald-400' : 'text-indigo-400'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="text-sm font-medium" x-text="toast.message"></div>
        <button @click="toast.show = false" class="text-slate-400 hover:text-white ml-auto cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    
    {{-- Inyección de Datos Reales de Inventario desde Laravel (259 SKUs, 1,283 variantes) --}}
    <script>
        window.serverInventoryProducts = @json($portalProducts);
        window.serverSuppliers = @json($portalProviders);
        window.serverPurchaseOrders = @json($portalPurchaseOrders);
        window.serverFuturePurchaseOrders = @json($portalFuturePurchaseOrders);
        window.serverPurchaseKardex = @json($portalPurchaseKardex);
        window.serverCreditAgreements = @json($portalCreditAgreements ?? []);
        window.serverProductBundles = @json($portalProductBundles ?? []);
        window.serverCycleCounts = @json($portalCycleCounts ?? []);
        window.serverSizeCurves = @json($portalSizeCurves ?? []);
        window.serverFragranceVolumes = @json($portalFragranceVolumes ?? []);
        window.serverCategories = @json($portalCategories ?? []);
    </script>

    <!-- ========================================================================= -->
    <!-- SECCIÓN 1: PESTAÑAS DE SUBMÓDULOS FIJAS (STICKY SUB-NAVBAR)               -->
    <!-- ========================================================================= -->
    <div class="sticky top-0 sm:top-[64px] z-20 bg-[#f8fafc]/95 backdrop-blur-md -mt-6 -mx-6 px-6 pt-3 pb-2 mb-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-2xs">
        <!-- PESTAÑAS DE NAVEGACIÓN (Alpine.js) -->
        <nav class="flex gap-2 -mb-px overflow-x-auto">

                <!-- TAB 1: CATÁLOGO OPERATIVO -->
                <button 
                    @click="activeTab = 'inventory'" 
                    :class="activeTab === 'inventory' 
                        ? 'border-indigo-600 text-indigo-600 font-semibold bg-white shadow-sm' 
                        : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'"
                    class="py-3 px-4 border-b-2 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>Catálogo Operativo</span>
                    <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700 font-semibold" x-text="products.length"></span>
                    <!-- Help Icon Tab 2 (Regla 8: Botón ? Universal) -->
                    <button class="section-help-btn" type="button" aria-label="Ayuda Catálogo Operativo" data-help-title="Pestaña: Catálogo Operativo" data-help-desc="Gestión diaria de existencias locales, ajustes manuales por merma/entrada, re-scraping de Torzo y panel de desglose por talla con calculadora de margen.">?</button>
                </button>

                <!-- TAB 2: PROVEEDORES & SCRAPING -->
                <button 
                    x-show="isTabAllowed('suppliers')"
                    @click="activeTab = 'suppliers'" 
                    :class="activeTab === 'suppliers' 
                        ? 'border-indigo-600 text-indigo-600 font-semibold bg-white shadow-sm' 
                        : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'"
                    class="py-3 px-4 border-b-2 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Proveedores & Scraping</span>
                    <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 font-bold">7 Activos</span>
                    <!-- Help Icon Tab 3 (Regla 8: Botón ? Universal) -->
                    <button class="section-help-btn" type="button" aria-label="Ayuda Proveedores y Scraping" data-help-title="Pestaña: Proveedores & Scraping" data-help-desc="Conectores de scrapers (Torzo) y APIs de mayoristas para dropshipping sincronizado y seguimiento de órdenes de compra pendientes.">?</button>
                </button>

                <!-- TAB 3: COMPRAS & REABASTECIMIENTO -->
                <button 
                    x-show="isTabAllowed('purchases')"
                    @click="activeTab = 'purchases'" 
                    :class="activeTab === 'purchases' 
                        ? 'border-indigo-600 text-indigo-600 font-semibold bg-white shadow-sm' 
                        : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'"
                    class="py-3 px-4 border-b-2 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span>Compras</span>
                    <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700 font-bold" x-text="futurePurchaseOrders.length + 2"></span>
                    <!-- Help Icon Tab 4 (Regla 8: Botón ? Universal) -->
                    <button class="section-help-btn" type="button" aria-label="Ayuda Compras y Reabastecimiento" data-help-title="Pestaña: Compras & Reabastecimiento" data-help-desc="Seguimiento de órdenes de compra pendientes, recepción de mercancía y consolidación de compras planificadas desde el catálogo.">?</button>
                </button>
        </nav>

        <!-- ACCIONES RÁPIDAS & CONEXIÓN (A LA MISMA ALTURA QUE LAS PESTAÑAS) -->
        <div class="flex items-center gap-2.5 pb-2 sm:pb-0 shrink-0">
            <div class="flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 px-2.5 py-1.5 rounded-lg text-xs font-medium text-emerald-700">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Torzo Scraper: Conectado</span>
            </div>
            <button 
                @click="triggerGlobalRefresh()" 
                :disabled="isRefreshing"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 hover:bg-slate-100 hover:border-slate-300 shadow-xs transition cursor-pointer disabled:opacity-50"
                style="min-height: unset;"
                title="Sincronizar inventario y catálogo"
            >
                <svg :class="isRefreshing ? 'animate-spin' : ''" class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span x-text="isRefreshing ? 'Actualizando...' : 'Actualizar'"></span>
            </button>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- PESTAÑA 1: CATÁLOGO OPERATIVO (Vista Principal)                   -->
    <!-- ================================================================= -->
    <div x-show="activeTab === 'inventory'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
        
        <!-- ============================================================= -->
        <!-- SECCIÓN 2: TARJETAS CON KPIS (CONTRAÍBLES Y EXPANDIBLES)      -->
        <!-- ============================================================= -->
        <div class="mb-5">
            <!-- Barra de control del toggle de KPIs -->
            <div class="flex items-center justify-between mb-2.5">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Métricas Clave de Inventario</span>
                    <template x-if="kpisCollapsed">
                        <span class="text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200/60 px-2 py-0.5 rounded-md" x-text="'3,674 piezas disponibles | 25 modelos activos | Último scrape: ' + lastScrapeTime"></span>
                    </template>
                </div>
                <button 
                    type="button" 
                    @click="toggleKpis()" 
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg shadow-2xs transition cursor-pointer"
                    :title="kpisCollapsed ? 'Expandir tarjetas de KPIs' : 'Contraer tarjetas de KPIs para ahorrar espacio'"
                >
                    <svg :class="kpisCollapsed ? 'rotate-180' : ''" class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                    </svg>
                    <span x-text="kpisCollapsed ? 'Expandir KPIs' : 'Contraer KPIs'"></span>
                </button>
            </div>

            <!-- Contenedor contraíble de las 4 Tarjetas de KPIs -->
            <div 
                x-show="!kpisCollapsed" 
                x-transition:enter="transition ease-out duration-200" 
                x-transition:enter-start="opacity-0 -translate-y-2" 
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"
            >
            <!-- KPI 1: Stock Local -->
            <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Stock Local</span>
                        <!-- Help Icon -->
                        <div x-data="{ tip: false }" class="relative inline-flex items-center">
                            <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                            <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Inventario Disponible</div>
                                <p class="text-[10px] text-slate-200 leading-snug">Cantidad total de prendas en estantería para despacho de pedidos el mismo día de la compra.</p>
                                <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                            </div>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900 mt-0.5"><span x-text="products.reduce((acc, p) => acc + (p.localStock || 0), 0).toLocaleString() + ' piezas'">{{ $summary['stock_local'] }}</span></div>
                    <div class="text-[11px] text-emerald-600 font-medium">Disponibles para despacho</div>
                </div>
            </div>

            <!-- KPI 2: Bajo Pedido -->
            <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Bajo Pedido</span>
                        <!-- Help Icon -->
                        <div x-data="{ tip: false }" class="relative inline-flex items-center">
                            <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                            <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Pedidos Dropshipping</div>
                                <p class="text-[10px] text-slate-200 leading-snug">Referencias vendidas que requieren gestión externa con el proveedor para envío directo al cliente.</p>
                                <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                            </div>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900 mt-0.5">0 <span class="text-xs font-normal text-slate-500">referencias</span></div>
                    <div class="text-[11px] text-slate-500">Sin rezagos acumulados</div>
                </div>
            </div>

            <!-- KPI 3: SKUs Activos -->
            <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">SKUs Activos</span>
                        <!-- Help Icon -->
                        <div x-data="{ tip: false }" class="relative inline-flex items-center">
                            <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                            <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Catálogo Habilitado</div>
                                <p class="text-[10px] text-slate-200 leading-snug">Modelos únicos comercializados. Cada uno se multiplica por su matriz de tallas CH, M, G y XL.</p>
                                <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                            </div>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900 mt-0.5">25 <span class="text-xs font-normal text-slate-500">modelos</span></div>
                    <div class="text-[11px] text-slate-500">4 tallas x modelo (CH a XL)</div>
                </div>
            </div>

            <!-- KPI 4: Último Scrape -->
            <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Último Scrape</span>
                        <!-- Help Icon (Alineado a la derecha) -->
                        <div x-data="{ tip: false }" class="relative inline-flex items-center">
                            <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                            <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full right-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Sincronización de Catálogo</div>
                                <p class="text-[10px] text-slate-200 leading-snug">Tiempo transcurrido desde que el bot headless verificó el inventario remoto en la plataforma de Torzo B2B.</p>
                                <span class="absolute bottom-full right-3 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                            </div>
                        </div>
                    </div>
                    <div class="text-lg font-bold text-slate-800 mt-0.5" x-text="lastScrapeTime">Sin registros</div>
                    <div class="text-[11px] text-emerald-600 font-medium">Torzo Feed Bot activo</div>
                </div>
            </div>
        </div>
        </div>

        <!-- ============================================================= -->
        <!-- SECCIÓN 3: FUNCIONALIDADES DEL MÓDULO (ACCIONES OPERATIVAS)   -->
        <!-- ============================================================= -->
        <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-xs mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-800 block leading-tight">Funcionalidades del Módulo</span>
                    <span class="text-[10px] text-slate-400 block">Acciones de auditoría, combos BOM y gestión de catálogo</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <!-- Botón Kardex / Auditoría (Permanente) -->
                <button 
                    type="button"
                    @click.stop="openKardexDrawer()" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 rounded-lg text-xs font-bold transition border border-slate-200 cursor-pointer"
                    style="min-height: unset;"
                    title="Historial de movimientos y kardex contable de auditoría"
                >
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span>Kardex / Auditoría</span>
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700" x-text="inventoryMovements.length"></span>
                </button>

                <!-- Botón Carritos Activos (Permanente: Telemetría de Stock Locks con TTL 5m) -->
                <button 
                    type="button"
                    @click.stop="openStockLocksModal()" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 hover:text-rose-800 rounded-lg text-xs font-bold transition border border-rose-200 cursor-pointer"
                    style="min-height: unset;"
                    title="Telemetría de Carritos Simultáneos y Reserva Temporal de Stock (TTL 5m)"
                >
                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <span>Carritos Activos</span>
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold" 
                          :class="activeStockLocksCount > 0 ? 'bg-rose-200 text-rose-900 animate-pulse' : 'bg-slate-200 text-slate-700'" 
                          x-text="activeStockLocksCount + ' en espera'"></span>
                </button>

                <!-- Menú Desplegable "Herramientas de Stock" (TASK-INV-10) -->
                <div class="relative inline-block text-left" @click.outside="toolsDropdownOpen = false">
                    <button 
                        type="button"
                        @click.stop="toolsDropdownOpen = !toolsDropdownOpen" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 rounded-lg text-xs font-bold transition border border-slate-200 cursor-pointer"
                        :class="toolsDropdownOpen ? 'bg-slate-200 ring-2 ring-indigo-500/20' : ''"
                        style="min-height: unset;"
                        title="Herramientas de auditoría, fórmulas BOM y balance de insumos"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Herramientas</span>
                        <!-- Alerta ámbar si la auditoría ciega está pendiente hoy -->
                        <span 
                            class="w-2 h-2 rounded-full" 
                            :class="cycleCountModal.isCompletedToday ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse'"
                            :title="cycleCountModal.isCompletedToday ? 'Auditoría diaria al día' : 'Auditoría diaria pendiente (3 SKUs)'"
                        ></span>
                        <svg class="w-3 h-3 text-slate-400 transition-transform duration-150" :class="toolsDropdownOpen ? 'rotate-180 text-slate-700' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Panel -->
                    <div 
                        x-show="toolsDropdownOpen" 
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute left-0 mt-1.5 w-72 rounded-xl bg-white shadow-xl border border-slate-200 py-1.5 z-50 focus:outline-none"
                        style="display: none;"
                    >
                        <div class="px-3 py-1.5 border-b border-slate-100">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Herramientas Operativas</span>
                        </div>

                        <!-- Opción 1: Recetas & Combos BOM -->
                        <button 
                            type="button" 
                            @click="toolsDropdownOpen = false; activeSettingsTab = 'bundles'; moduleSettingsModalOpen = true;" 
                            class="w-full text-left px-3 py-2 flex items-center justify-between hover:bg-slate-50 transition cursor-pointer group"
                            style="min-height: unset;"
                        >
                            <div class="flex items-center gap-2">
                                <span class="p-1 rounded-md bg-violet-50 text-violet-600 border border-violet-100 group-hover:bg-violet-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </span>
                                <div>
                                    <div class="text-xs font-bold text-slate-800">Recetas & Combos BOM</div>
                                    <div class="text-[10px] text-slate-400">Escandallos y despiece de kits</div>
                                </div>
                            </div>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-violet-100 text-violet-800 border border-violet-200" x-text="productBundles.length"></span>
                        </button>

                        <!-- Opción 2: Déficit BOM & OCs B2B -->
                        <button 
                            type="button" 
                            @click="toolsDropdownOpen = false; openBomDeficitModal('SUB-BOX-MARTE-LVL2', 200)" 
                            class="w-full text-left px-3 py-2 flex items-center justify-between hover:bg-slate-50 transition cursor-pointer group"
                            style="min-height: unset;"
                        >
                            <div class="flex items-center gap-2">
                                <span class="p-1 rounded-md bg-indigo-50 text-indigo-600 border border-indigo-100 group-hover:bg-indigo-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                </span>
                                <div>
                                    <div class="text-xs font-bold text-slate-800">Déficit BOM & OCs B2B</div>
                                    <div class="text-[10px] text-slate-400">Disparo automático a mayoristas</div>
                                </div>
                            </div>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">Auto OC</span>
                        </button>

                        <!-- Opción 3: Auditoría Cíclica a Ciegas -->
                        <button 
                            type="button" 
                            @click="toolsDropdownOpen = false; openCycleCountModal()" 
                            class="w-full text-left px-3 py-2 flex items-center justify-between hover:bg-slate-50 transition cursor-pointer group"
                            style="min-height: unset;"
                        >
                            <div class="flex items-center gap-2">
                                <span class="p-1 rounded-md bg-amber-50 text-amber-600 border border-amber-100 group-hover:bg-amber-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                </span>
                                <div>
                                    <div class="text-xs font-bold text-slate-800">Auditoría Diaria a Ciegas</div>
                                    <div class="text-[10px] text-slate-400">Conteo exactitud 3 SKUs</div>
                                </div>
                            </div>
                            <span 
                                class="px-1.5 py-0.5 rounded-full text-[10px] font-bold border"
                                :class="cycleCountModal.isCompletedToday ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-900 border-amber-200'"
                                x-text="cycleCountModal.isCompletedToday ? 'OK' : '3 SKUs'"
                            ></span>
                        </button>
                    </div>
                </div>

                <!-- Botón Agregar Producto -->
                <button 
                    type="button"
                    @click.stop="openCreateProductDrawer()" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer group shrink-0"
                    style="min-height: unset;"
                >
                    <svg class="w-3.5 h-3.5 text-indigo-200 group-hover:rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Agregar Producto</span>
                </button>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- SECCIÓN 4: FILTROS DE LAS TABLAS (BÚSQUEDA Y SEGMENTACIÓN)    -->
        <!-- ============================================================= -->
        <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-xs mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 flex-1 min-w-[320px]">
                <!-- Input Búsqueda -->
                <div class="relative w-full sm:w-60 min-w-[200px]">
                    <input 
                        type="text" 
                        x-model="searchFilter" 
                        placeholder="Buscar por SKU o modelo..." 
                        class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-slate-800 placeholder-slate-400"
                    >
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <!-- Filtro Origen -->
                <select 
                    x-model="typeFilter" 
                    class="text-xs bg-slate-50 border border-slate-200 rounded-lg py-1.5 px-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-slate-700 font-medium"
                >
                    <option value="all">Todos los orígenes</option>
                    <option value="propio">Stock Propio</option>
                    <option value="dropship">Dropshipping (Torzo)</option>
                </select>

                <!-- Filtro Clase ABC (Pareto) -->
                <select 
                    x-model="abcFilter" 
                    class="text-xs bg-slate-50 border border-slate-200 rounded-lg py-1.5 px-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-slate-700 font-semibold"
                >
                    <option value="all">Clase ABC: Todas</option>
                    <option value="A">Clase A (Alta Rotación 80%)</option>
                    <option value="B">Clase B (Complementarios 15%)</option>
                    <option value="C">Clase C (Cola Larga 5%)</option>
                </select>

                <!-- Filtro ROP (Punto de Reorden) -->
                <select 
                    x-model="ropFilter" 
                    class="text-xs bg-slate-50 border border-slate-200 rounded-lg py-1.5 px-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-slate-700 font-medium"
                >
                    <option value="all">Alerta ROP: Todos</option>
                    <option value="under_rop">Bajo ROP (Reabastecer Urgente)</option>
                </select>

                <!-- Filtro Sanitario (TASK-INV-06: Caducidad y Lotes en Cosmética/Fragancias) -->
                <select 
                    x-model="sanitaryFilter" 
                    class="text-xs bg-slate-50 border border-slate-200 rounded-lg py-1.5 px-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-slate-700 font-medium"
                >
                    <option value="all">Control Sanitario: Todos</option>
                    <option value="sanitary_only">Solo Cosmética / Cuidado Personal</option>
                    <option value="expiring_soon">Por Vencer (&le; 90 días)</option>
                    <option value="critical">Vencimiento Crítico (&le; 30 días)</option>
                </select>

                <!-- Help Icon Filtros -->
                <div x-data="{ tip: false }" class="relative inline-flex items-center">
                    <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                    <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-60 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                        <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Búsqueda & Filtros de Catálogo</div>
                        <p class="text-[11px] text-slate-200 leading-snug">Filtra instantáneamente por nombre, código SKU o separa las prendas físicas de bodega de las sincronizadas con proveedores.</p>
                        <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                    </div>
                </div>
            </div>

            <!-- Contador de Referencias a la derecha -->
            <div class="text-xs text-slate-500 font-medium shrink-0">
                Mostrando <span class="font-bold text-slate-800" x-text="filteredProducts.length"></span> de <span class="font-bold text-slate-800" x-text="products.length"></span> referencias
            </div>
        </div>

        <!-- TASK-INT-01: Banner de Salto Profundo Activo (Deep Link Active Banner) -->
        <div x-show="deepLinkActive" x-cloak class="deep-link-banner" style="display: none;">
            <div class="deep-link-banner-info">
                <div class="deep-link-banner-icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <div class="deep-link-banner-text">
                    <strong x-text="deepLinkBannerText">Filtro de Navegación Rápida Activo</strong>
                    <small>Mostrando registros coincidentes desde el Dashboard General.</small>
                </div>
            </div>
            <button type="button" @click="clearDeepLink()" class="deep-link-banner-btn">
                Restablecer Catálogo Completo
            </button>
        </div>

        <!-- Tabla de Datos (Data Grid Principal) -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm w-full min-w-full">
            <div class="overflow-x-auto rounded-b-xl">
                <table class="w-full min-w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[11px] uppercase tracking-wider font-semibold">
                            <th class="py-3 px-3 w-10 text-center">
                                <input 
                                    type="checkbox" 
                                    @change="toggleSelectAll($event.target.checked)" 
                                    :checked="isAllSelected()"
                                    class="rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                    title="Seleccionar todos los productos visibles"
                                >
                            </th>
                            <th class="py-3 px-3 w-12 text-center">Imagen</th>
                            <th class="py-3 px-3">SKU</th>
                            <th class="py-3 px-4">Producto & Categoría</th>
                            <th class="py-3 px-3 text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <span>Stock Local</span>
                                    <!-- Help Icon -->
                                    <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full right-0 mt-2 w-60 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                            <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Motor de Stock de 3 Estados</div>
                                            <p class="text-[10px] text-slate-200 leading-snug">Disponible para Venta = Físico en Rack - Comprometido (apartado en preparación o esperando arribo de mayorista en carritos mixtos).</p>
                                            <span class="absolute bottom-full right-3 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                        </div>
                                    </div>
                                </div>
                            </th>
                            <th class="py-3 px-3 text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <span>Stock Prov.</span>
                                    <!-- Help Icon -->
                                    <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full right-0 mt-2 w-52 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                            <p class="text-[10px] text-slate-200">Existencias reportadas en vivo desde la plataforma del proveedor.</p>
                                            <span class="absolute bottom-full right-3 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                        </div>
                                    </div>
                                </div>
                            </th>
                            <th class="py-3 px-3 text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <span>PVP ($)</span>
                                    <!-- Help Icon -->
                                    <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full right-0 mt-2 w-52 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                            <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Precio Venta Público</div>
                                            <p class="text-[10px] text-slate-200">Haz clic sobre el precio para editarlo en línea al instante sin abrir el panel lateral.</p>
                                            <span class="absolute bottom-full right-3 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                        </div>
                                    </div>
                                </div>
                            </th>
                            <th class="py-3 px-3 text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <span>Costo ($)</span>
                                    <!-- Help Icon -->
                                    <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full right-0 mt-2 w-64 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                            <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Valuación: Último Costo vs CPP Contable</div>
                                            <p class="text-[10px] text-slate-200 leading-snug">
                                                <strong>Último Costo:</strong> Precio de adquisición de la orden más reciente.<br>
                                                <strong>CPP (Costo Promedio Ponderado):</strong> Valuación contable ponderada por existencias en balance general.<br>
                                                <span class="text-indigo-300 font-mono text-[9px] mt-1 block">CPP = (Stock x CPP + Inbound x Costo) / Total</span>
                                            </p>
                                            <span class="absolute bottom-full right-3 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                        </div>
                                    </div>
                                </div>
                            </th>
                            <th class="py-3 px-3 text-center">Margen</th>
                            <th class="py-3 px-3">Ubicación</th>
                            <th class="py-3 px-3 min-w-[260px]">
                                <div class="inline-flex items-center gap-1">
                                    <span>Ajuste Rápido de Stock</span>
                                    <!-- Help Icon -->
                                    <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-64 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                            <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Formulario Inline de Ajuste</div>
                                            <p class="text-[10px] text-slate-200">Ingresa o deduce stock al instante por conteos físicos, mermas o devoluciones sin salir de la tabla.</p>
                                            <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                        </div>
                                    </div>
                                </div>
                            </th>
                            <th class="py-3 px-3 text-center">Vitrina Web</th>
                            <th class="py-3 px-3 text-center">Estado / Acciones</th>
                        </tr>

                        <!-- FILA DE FILTROS POR COLUMNA (REGLA 7) -->
                        <tr class="bg-slate-100/80 border-b border-slate-200 text-[10px]">
                            <!-- Col 1: Limpiar filtros si hay activos -->
                            <td class="py-2 px-1 text-center">
                                <template x-if="hasActiveColFilters()">
                                    <button 
                                        @click="clearColFilters()" 
                                        title="Limpiar filtros de columna"
                                        class="p-1 rounded bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 transition cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </template>
                            </td>
                            <!-- Col 2: Imagen -->
                            <td class="py-2 px-1 text-center text-slate-400">-</td>
                            <!-- Col 3: SKU -->
                            <td class="py-2 px-1">
                                <input 
                                    type="text" 
                                    x-model="colFilters.sku" 
                                    placeholder="SKU..." 
                                    class="w-full h-7 px-1.5 text-[11px] border border-slate-300 rounded bg-white font-mono focus:border-indigo-600 focus:outline-none"
                                >
                            </td>
                            <!-- Col 4: Producto -->
                            <td class="py-2 px-1">
                                <input 
                                    type="text" 
                                    x-model="colFilters.product" 
                                    placeholder="Nombre / Cat..." 
                                    class="w-full h-7 px-1.5 text-[11px] border border-slate-300 rounded bg-white focus:border-indigo-600 focus:outline-none"
                                >
                            </td>
                            <!-- Col 5: Stock Local -->
                            <td class="py-2 px-1 text-right">
                                <select 
                                    x-model="colFilters.stockLocal" 
                                    class="w-full h-7 px-1 text-[10px] border border-slate-300 rounded bg-white focus:border-indigo-600 focus:outline-none font-medium text-right"
                                >
                                    <option value="all">Todo</option>
                                    <option value="under_rop">Bajo ROP</option>
                                    <option value="in_stock">&gt; 0 uds</option>
                                    <option value="zero">Agotado (0)</option>
                                </select>
                            </td>
                            <!-- Col 6: Stock Prov. -->
                            <td class="py-2 px-1 text-right">
                                <select 
                                    x-model="colFilters.stockProv" 
                                    class="w-full h-7 px-1 text-[10px] border border-slate-300 rounded bg-white focus:border-indigo-600 focus:outline-none font-medium text-right"
                                >
                                    <option value="all">Todo</option>
                                    <option value="available">Con stock</option>
                                    <option value="none">Sin stock</option>
                                </select>
                            </td>
                            <!-- Col 7: PVP Mínimo -->
                            <td class="py-2 px-1 text-right">
                                <input 
                                    type="number" 
                                    x-model="colFilters.minPvp" 
                                    placeholder="Mín $" 
                                    class="w-full h-7 px-1 text-[10px] border border-slate-300 rounded bg-white focus:border-indigo-600 focus:outline-none font-mono text-right"
                                >
                            </td>
                            <!-- Col 8: Costo Máximo -->
                            <td class="py-2 px-1 text-right">
                                <input 
                                    type="number" 
                                    x-model="colFilters.maxCost" 
                                    placeholder="Máx $" 
                                    class="w-full h-7 px-1 text-[10px] border border-slate-300 rounded bg-white focus:border-indigo-600 focus:outline-none font-mono text-right"
                                >
                            </td>
                            <!-- Col 9: Margen -->
                            <td class="py-2 px-1 text-center">
                                <select 
                                    x-model="colFilters.margin" 
                                    class="w-full h-7 px-1 text-[10px] border border-slate-300 rounded bg-white focus:border-indigo-600 focus:outline-none font-medium"
                                >
                                    <option value="all">Todo</option>
                                    <option value="high">&ge; 60%</option>
                                    <option value="mid">40 - 59%</option>
                                    <option value="low">&lt; 40%</option>
                                </select>
                            </td>
                            <!-- Col 10: Ubicación -->
                            <td class="py-2 px-1">
                                <input 
                                    type="text" 
                                    x-model="colFilters.location" 
                                    placeholder="Rack..." 
                                    class="w-full h-7 px-1 text-[10px] border border-slate-300 rounded bg-white focus:border-indigo-600 focus:outline-none font-mono"
                                >
                            </td>
                            <!-- Col 11: Info -->
                            <td class="py-2 px-1 text-center text-slate-400 text-[10px]">
                                <template x-if="hasActiveColFilters()">
                                    <span class="text-indigo-600 font-bold">Filtro activo</span>
                                </template>
                            </td>
                            <!-- Col Vitrina Web -->
                            <td class="py-2 px-1 text-center text-slate-400 text-[10px]">-</td>
                            <!-- Col 12: Estado -->
                            <td class="py-2 px-1 text-center">
                                <select 
                                    x-model="colFilters.status" 
                                    class="w-full h-7 px-1 text-[10px] border border-slate-300 rounded bg-white focus:border-indigo-600 focus:outline-none font-medium"
                                >
                                    <option value="all">Todo</option>
                                    <option value="En Stock">En Stock</option>
                                    <option value="Bajo Stock">Bajo Stock</option>
                                    <option value="Agotado">Agotado</option>
                                </select>
                            </td>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        <template x-for="(item, index) in displayedProducts" :key="item.sku">
                            <tr :class="(selectedSkus.includes(item.sku) ? 'bg-indigo-50/50 hover:bg-indigo-50/70' : 'hover:bg-slate-50/70') + (highlightedSku === item.sku ? ' sku-row-highlight' : '')" class="transition group">
                                <!-- Columna: CHECKBOX SELECCIÓN MASIVA -->
                                <td class="py-3.5 px-3 text-center">
                                    <input 
                                        type="checkbox" 
                                        :value="item.sku" 
                                        x-model="selectedSkus"
                                        class="rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                    >
                                </td>

                                <!-- Columna: IMAGEN -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="relative w-11 h-11 mx-auto rounded-lg overflow-hidden border border-slate-200 bg-slate-100 flex items-center justify-center cursor-pointer shadow-xs group-hover:shadow transition"
                                         @click="openProductDrawer(item)">
                                        <svg class="w-6 h-6 text-slate-400 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border border-white"
                                              :class="item.type === 'propio' ? 'bg-indigo-600' : 'bg-emerald-500'"></span>
                                    </div>
                                </td>

                                <!-- Columna: SKU -->
                                <td class="py-3.5 px-4 font-mono font-bold text-indigo-700">
                                    <button 
                                        @click="openProductDrawer(item)" 
                                        class="hover:underline flex items-center gap-1 text-left cursor-pointer"
                                        title="Abrir ficha y calculadora de margen"
                                    >
                                        <span x-text="item.sku"></span>
                                        <svg class="w-3 h-3 text-slate-400 opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </button>
                                </td>

                                <!-- Columna: PRODUCTO -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900" x-text="item.name"></span>
                                        <span 
                                            :class="getAbcBadgeClass(getAbcClass(item))" 
                                            class="text-[9px] px-1.5 py-0.2 rounded border font-mono shrink-0" 
                                            :title="'Clase Pareto: ' + getAbcClass(item)"
                                            x-text="'[' + getAbcClass(item) + ']'"
                                        ></span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-slate-500" x-text="item.category"></span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span class="text-[11px] px-1.5 py-0.2 rounded font-semibold uppercase tracking-wider"
                                              :class="item.type === 'propio' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-100'"
                                              x-text="item.type === 'propio' ? 'Stock Propio' : 'Dropship Torzo'">
                                        </span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span class="text-[11px] font-semibold text-slate-600" x-text="item.brand || (item.type === 'dropship' ? 'Torzo B2B' : 'ModaIntima Textil MX')"></span>
                                    </div>

                                    <!-- Swatches Cromáticos de Cosmética (TASK-INV-09) -->
                                    <template x-if="item.variantDimension === 'shade'">
                                        <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                            <span class="text-[10px] font-bold text-pink-700 uppercase tracking-wider mr-0.5">Tonos:</span>
                                            <template x-for="(qty, shCode) in (item.shades || {})" :key="shCode">
                                                <div class="inline-flex items-center gap-1 bg-white hover:bg-pink-50/50 border border-slate-200 px-1.5 py-0.5 rounded-full shadow-2xs transition"
                                                     :title="'Tono ' + (getShadeInfo(shCode)?.code || shCode) + ': ' + (getShadeInfo(shCode)?.name || '') + ' (' + getUndertoneLabel(getShadeInfo(shCode)?.undertone) + ') - ' + qty + ' piezas'">
                                                    <span class="cosmetic-swatch-20" :style="'background: ' + (getShadeInfo(shCode)?.hex_color || '#E8BEA2')"></span>
                                                    <span class="text-[10px] font-bold text-slate-700" x-text="getShadeInfo(shCode)?.code || shCode"></span>
                                                    <span class="text-[9px] px-1 py-0.2 rounded font-semibold"
                                                          :class="getUndertoneBadgeClass(getShadeInfo(shCode)?.undertone)"
                                                          x-text="getUndertoneLabel(getShadeInfo(shCode)?.undertone)"></span>
                                                    <span class="text-[10px] font-mono font-bold text-slate-600 ml-0.5" x-text="qty"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Badges de Atributos Textiles: Tela, Color/Tornasol, Diseño y Sección -->
                                    <template x-if="item.variantDimension !== 'shade'">
                                        <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                            <!-- Swatch Color & Tornasol -->
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-50 text-slate-700 border border-slate-200" :title="'Color: ' + (item.colorName || 'Negro')">
                                                <span class="w-2.5 h-2.5 rounded-full border border-slate-300 shrink-0" :style="'background: ' + (item.colorHex || '#0f172a')"></span>
                                                <span x-text="item.colorName || 'Negro'"></span>
                                            </span>

                                            <!-- Badge Tipo de Tela -->
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200" :title="'Composición textil: ' + (item.fabricType || 'Modal')">
                                                <span x-text="item.fabricType || 'Modal'"></span>
                                            </span>

                                            <!-- Badge Variante Diseño -->
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium"
                                                  :class="item.designType === 'con_diseno' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-600 border border-slate-200'">
                                                <span x-text="item.designType === 'con_diseno' ? 'Estampado' : 'Liso'"></span>
                                            </span>

                                            <!-- Badge Sección Web -->
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200"
                                                  :title="'Sección de vitrina: ' + (item.websiteSection || 'General')">
                                                <span x-text="item.websiteSection === 'basicos' ? 'Básicos' : (item.websiteSection === 'disenador' ? 'Diseñador' : (item.websiteSection === 'temporada' ? 'Temporada' : 'General'))"></span>
                                            </span>
                                        </div>
                                    </template>

                                    <!-- Indicadores Sanitarios (TASK-INV-06: Lote, Caducidad y PAO) -->
                                    <template x-if="item.requiresSanitaryControl">
                                        <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-mono font-medium bg-slate-100 text-slate-700 border border-slate-200" :title="'Lote de fabricacion: ' + (item.batchNumber || 'N/A')">
                                                <span class="text-slate-500 mr-1 font-semibold">Lot:</span>
                                                <span x-text="item.batchNumber || 'S/L'"></span>
                                            </span>
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium border"
                                                  :class="getSanitaryBadgeClass(item)"
                                                  :title="'Caducidad: ' + (item.expirationDate || 'N/A')">
                                                <svg class="w-2.5 h-2.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span x-text="getSanitaryBadgeText(item)"></span>
                                            </span>
                                            <span x-show="item.paoMonths" class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200" :title="'Period After Opening: ' + item.paoMonths + ' meses'">
                                                <span class="text-purple-600 mr-0.5">PAO</span>
                                                <span x-text="item.paoMonths + 'M'"></span>
                                            </span>
                                        </div>
                                    </template>
                                </td>

                                <!-- Columna: STOCK LOCAL (3 ESTADOS: DISPONIBLE, FÍSICO, COMPROMETIDO) -->
                                <td class="py-3.5 px-3 text-right">
                                    <div class="flex flex-col items-end">
                                        <!-- Disponible para venta directa -->
                                        <div class="flex items-center gap-1">
                                            <span class="text-sm font-extrabold text-emerald-600" x-text="getAvailableStock(item)"></span>
                                            <span class="text-[9px] uppercase font-extrabold text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-200">Disp.</span>
                                        </div>
                                        <!-- Físico en rack vs Comprometido -->
                                        <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-1">
                                            <span x-text="item.localStock + ' físico'"></span>
                                            <template x-if="item.committedStock > 0">
                                                <span class="text-indigo-600 font-semibold bg-indigo-50 px-1 rounded border border-indigo-100" :title="'Apartado en pedidos/mixtos: ' + item.committedStock"><svg class="w-2.5 h-2.5 inline-block -mt-0.5 mr-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><span x-text="item.committedStock"></span></span>
                                            </template>
                                            <template x-if="getLockedStock(item) > 0">
                                                <span class="badge-stock-locked stock-lock-pulse text-rose-600 font-semibold bg-rose-50 px-1 rounded border border-rose-200" :title="'En Carrito de Compra (TTL 5m): ' + getLockedStock(item)"><svg class="w-2.5 h-2.5 inline-block -mt-0.5 mr-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><span x-text="getLockedStock(item) + ' carrito'"></span></span>
                                            </template>
                                        </div>
                                        <!-- DOH & Alerta ROP -->
                                        <div class="mt-1 flex items-center gap-1">
                                            <span 
                                                :class="getDOHBadgeClass(getDOH(item))"
                                                class="text-[9px] font-bold px-1.5 py-0.2 rounded border"
                                                :title="'Días de Inventario Restante (DOH): ' + getDOH(item) + ' días'"
                                                x-text="getDOH(item) + 'd DOH'"
                                            ></span>
                                            <template x-if="isUnderROP(item)">
                                                <button 
                                                    type="button" 
                                                    @click.stop="generateROPOrder(item)"
                                                    class="text-[9px] font-extrabold bg-rose-600 hover:bg-rose-700 text-white px-1.5 py-0.2 rounded shadow-2xs cursor-pointer animate-pulse"
                                                    :title="'Bajo Punto de Reorden (ROP=' + getROP(item) + '). Clic para generar orden de reabastecimiento.'"
                                                >
                                                    <svg class="w-3 h-3 inline-block -mt-0.5 mr-1" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>ROP
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </td>

                                <!-- Columna: STOCK PROVEEDOR -->
                                <td class="py-3.5 px-3 text-right">
                                    <template x-if="item.supplierStock !== null">
                                        <div>
                                            <span class="text-sm font-bold text-emerald-600" x-text="item.supplierStock"></span>
                                            <span class="text-xs text-slate-400 block">en Torzo</span>
                                        </div>
                                    </template>
                                    <template x-if="item.supplierStock === null">
                                        <span class="text-xs text-slate-400 italic font-normal">N/A (Local)</span>
                                    </template>
                                </td>

                                <!-- Columna: PVP ($) CON EDICIÓN EN LÍNEA -->
                                <td class="py-3.5 px-3 text-right">
                                    <template x-if="editingCell.sku === item.sku && editingCell.field === 'pvp'">
                                        <div class="inline-flex items-center gap-1 justify-end">
                                            <span class="text-xs text-slate-400 font-bold">$</span>
                                            <input 
                                                :id="'inline-input-' + item.sku + '-pvp'"
                                                type="number" 
                                                step="1"
                                                min="0"
                                                x-model.number="tempEditValue" 
                                                @keydown.enter="saveInlineEdit(item, 'pvp')" 
                                                @keydown.escape="cancelInlineEdit()" 
                                                @blur="saveInlineEdit(item, 'pvp')"
                                                class="w-20 text-right bg-white border-2 border-indigo-500 rounded px-1.5 py-0.5 text-xs font-bold text-slate-900 shadow-xs focus:outline-none"
                                            >
                                            <button 
                                                type="button" 
                                                @mousedown.prevent="saveInlineEdit(item, 'pvp')" 
                                                class="p-1 text-emerald-600 hover:text-emerald-700 rounded hover:bg-emerald-50 cursor-pointer"
                                                title="Guardar"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="!(editingCell.sku === item.sku && editingCell.field === 'pvp')">
                                        <div 
                                            class="group/edit inline-flex items-center gap-1 justify-end cursor-pointer hover:bg-slate-100/80 px-1.5 py-0.5 rounded transition"
                                            @click="startInlineEdit(item.sku, 'pvp', item.pvp)"
                                            title="Clic para editar PVP"
                                        >
                                            <div class="flex flex-col items-end">
                                                <template x-if="item.discountPct > 0">
                                                    <div class="flex items-center gap-1 leading-none mb-0.5">
                                                        <span class="line-through text-[10px] text-slate-400" x-text="'$' + (item.regularPrice ? item.regularPrice.toFixed(2) : item.pvp.toFixed(2))"></span>
                                                        <span class="text-[9px] font-extrabold bg-rose-100 text-rose-700 px-1 py-0.2 rounded" x-text="'-' + item.discountPct + '%'"></span>
                                                    </div>
                                                </template>
                                                <span class="font-bold" :class="item.discountPct > 0 ? 'text-emerald-700' : 'text-slate-900'" x-text="'$' + (item.discountPct > 0 ? (item.pvp * (1 - item.discountPct / 100)).toFixed(2) : (item.pvp ? item.pvp.toFixed(2) : '0.00'))"></span>
                                            </div>
                                            <template x-if="savedCellFeedback.sku === item.sku && savedCellFeedback.field === 'pvp'">
                                                <span class="text-emerald-600 animate-bounce">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                </span>
                                            </template>
                                            <template x-if="!(savedCellFeedback.sku === item.sku && savedCellFeedback.field === 'pvp')">
                                                <svg class="w-3 h-3 text-slate-400 opacity-0 group-hover/edit:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </template>
                                        </div>
                                    </template>
                                </td>

                                <!-- Columna: COSTO ($) CON EDICIÓN EN LÍNEA -->
                                <td class="py-3.5 px-3 text-right">
                                    <template x-if="canViewFinancialCosts()">
                                        <div>
                                            <template x-if="editingCell.sku === item.sku && editingCell.field === 'cost'">
                                                <div class="inline-flex items-center gap-1 justify-end">
                                                    <span class="text-xs text-slate-400 font-bold">$</span>
                                                    <input 
                                                        :id="'inline-input-' + item.sku + '-cost'"
                                                        type="number" 
                                                        step="1"
                                                        min="0"
                                                        x-model.number="tempEditValue" 
                                                        @keydown.enter="saveInlineEdit(item, 'cost')" 
                                                        @keydown.escape="cancelInlineEdit()" 
                                                        @blur="saveInlineEdit(item, 'cost')"
                                                        class="w-20 text-right bg-white border-2 border-indigo-500 rounded px-1.5 py-0.5 text-xs font-bold text-slate-900 shadow-xs focus:outline-none"
                                                    >
                                                    <button 
                                                        type="button" 
                                                        @mousedown.prevent="saveInlineEdit(item, 'cost')" 
                                                        class="p-1 text-emerald-600 hover:text-emerald-700 rounded hover:bg-emerald-50 cursor-pointer"
                                                        title="Guardar"
                                                    >
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="!(editingCell.sku === item.sku && editingCell.field === 'cost')">
                                                <div 
                                                    class="group/edit inline-flex flex-col items-end cursor-pointer hover:bg-slate-100/80 px-1.5 py-0.5 rounded transition"
                                                    @click="startInlineEdit(item.sku, 'cost', item.cost)"
                                                    title="Clic para editar Último Costo"
                                                >
                                                    <div class="inline-flex items-center gap-1 justify-end">
                                                        <span class="font-medium text-slate-600 text-xs" x-text="'$' + (item.cost ? item.cost.toFixed(2) : '0.00')"></span>
                                                        <template x-if="savedCellFeedback.sku === item.sku && savedCellFeedback.field === 'cost'">
                                                            <span class="text-emerald-600 animate-bounce">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                            </span>
                                                        </template>
                                                        <template x-if="!(savedCellFeedback.sku === item.sku && savedCellFeedback.field === 'cost')">
                                                            <svg class="w-3 h-3 text-slate-400 opacity-0 group-hover/edit:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                        </template>
                                                    </div>
                                                    <!-- Sub-badge contable CPP -->
                                                    <div class="text-[10px] text-slate-400 font-mono tracking-tight leading-none mt-0.5" title="Costo Promedio Ponderado (Contable)">
                                                        CPP: <span class="text-indigo-600 font-semibold" x-text="'$' + (item.weightedAverageCost ? item.weightedAverageCost.toFixed(2) : item.cost.toFixed(2))"></span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!canViewFinancialCosts()">
                                        <span class="inline-flex items-center gap-1 text-[11px] text-slate-400 font-mono italic bg-slate-100 px-2 py-0.5 rounded border border-slate-200" title="Información financiera reservada">
                                            <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <span>Protegido</span>
                                        </span>
                                    </template>
                                </td>

                                <!-- Columna: MARGEN OPERATIVO (%) (TASK-INV-04: CONTABLE REAL CPP) -->
                                <td class="py-3.5 px-3 text-center font-mono">
                                    <template x-if="canViewFinancialCosts()">
                                        <div class="inline-flex flex-col items-center">
                                            <span 
                                                :class="getMarginBadgeClass(calculateRealMarginPercent(item.pvp, item.weightedAverageCost || item.cost))"
                                                class="inline-block px-2 py-0.5 rounded text-[11px] font-bold border"
                                                :title="'Margen Contable Real sobre CPP: ' + calculateRealMarginPercent(item.pvp, item.weightedAverageCost || item.cost) + '% (Marginal s/ Costo: ' + calculateMarginPercent(item.pvp, item.cost) + '%)'"
                                                x-text="calculateRealMarginPercent(item.pvp, item.weightedAverageCost || item.cost) + '%'"
                                            ></span>
                                            <div class="text-[9px] text-slate-400 font-sans tracking-tight mt-0.5" x-show="Math.abs((item.weightedAverageCost || item.cost) - item.cost) > 0.05">
                                                Marginal: <span x-text="calculateMarginPercent(item.pvp, item.cost) + '%'"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!canViewFinancialCosts()">
                                        <span class="text-[11px] text-slate-400 font-mono italic bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">
                                            N/D
                                        </span>
                                    </template>
                                </td>

                                <!-- Columna: UBICACIÓN FÍSICA (SLOTTING: TASK-INV-01) -->
                                <td class="py-3.5 px-3">
                                    <template x-if="item.type === 'propio'">
                                        <button 
                                            @click="openSlotReassign(item)" 
                                            :class="getSlotBadgeClass(item.location)"
                                            class="group/slot inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-mono font-bold border transition cursor-pointer shadow-2xs"
                                            :title="'Ubicación en bodega: ' + item.location + '. Clic para reasignar slot.'"
                                        >
                                            <svg class="w-3.5 h-3.5 opacity-60 group-hover/slot:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span x-text="formatSlotCode(item.location)"></span>
                                            <svg class="w-2.5 h-2.5 opacity-40 group-hover/slot:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                    </template>
                                    <template x-if="item.type !== 'propio'">
                                        <span class="text-xs text-slate-400 italic font-normal">Dropship Remoto</span>
                                    </template>
                                </td>

                                <!-- Columna: AJUSTE RÁPIDO -->
                                <td class="py-3.5 px-4">
                                    <!-- FILA TIPO 1: STOCK PROPIO -->
                                    <template x-if="item.type === 'propio'">
                                        <div class="flex items-center gap-1.5 bg-slate-50 p-1.5 rounded-lg border border-slate-200">
                                            <select 
                                                x-model="item.adjustType" 
                                                class="text-xs py-1 px-1.5 bg-white border border-slate-200 rounded font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                            >
                                                <option value="add">+ Entr.</option>
                                                <option value="sub">- Sal.</option>
                                            </select>

                                            <input 
                                                type="number" 
                                                min="1" 
                                                x-model.number="item.adjustQty" 
                                                placeholder="Cant." 
                                                class="w-14 text-xs py-1 px-1.5 bg-white border border-slate-200 rounded text-center text-slate-800 font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                            >

                                            <input 
                                                type="text" 
                                                x-model="item.adjustReason" 
                                                placeholder="Motivo..." 
                                                class="flex-1 text-xs py-1 px-2 bg-white border border-slate-200 rounded text-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                            >

                                            <button 
                                                @click="applyQuickStockAdjustment(item)" 
                                                class="px-2.5 py-1 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded transition shadow-xs cursor-pointer"
                                                title="Aplicar ajuste"
                                            >
                                                Aplicar
                                            </button>
                                        </div>
                                    </template>

                                    <!-- FILA TIPO 2: DROPSHIPPING -->
                                    <template x-if="item.type === 'dropship'">
                                        <div class="flex items-center gap-2">
                                            <a 
                                                :href="item.providerUrl" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg border border-slate-200/80 transition"
                                            >
                                                <span>Ver en Proveedor</span>
                                                <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>

                                            <button 
                                                @click="rescrapeProduct(item)" 
                                                :disabled="item.isScraping"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg transition disabled:opacity-50 cursor-pointer"
                                            >
                                                <svg :class="item.isScraping ? 'animate-spin' : ''" class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                <span x-text="item.isScraping ? 'Scraping...' : 'Re-scrape'"></span>
                                            </button>
                                        </div>
                                    </template>
                                </td>

                                <!-- Columna: VITRINA WEB (SWITCH PUBLICACIÓN) -->
                                <td class="py-3.5 px-3 text-center">
                                    <button 
                                        type="button" 
                                        @click.stop="toggleProductPublication(item.sku)"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold transition border cursor-pointer shadow-3xs"
                                        :class="item.isPublished ? 'bg-emerald-50 text-emerald-700 border-emerald-300 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-300 hover:bg-slate-200'"
                                        :title="item.isPublished ? 'Publicado en sitio web (visible para clientes). Clic para despublicar.' : 'Fuera de catálogo (oculto). Clic para publicar en sitio web.'"
                                    >
                                        <span class="w-2 h-2 rounded-full" :class="item.isPublished ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                        <span x-text="item.isPublished ? 'En Sitio' : 'Oculto'"></span>
                                    </button>
                                </td>

                                <!-- Columna: ESTADO -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                              :class="{
                                                  'bg-emerald-50 text-emerald-700 border border-emerald-200': item.status === 'En Stock' || item.status === 'Sincronizado',
                                                  'bg-amber-50 text-amber-700 border border-amber-200': item.status === 'Bajo Stock',
                                                  'bg-rose-50 text-rose-700 border border-rose-200': item.status === 'Agotado'
                                              }"
                                              x-text="item.status">
                                        </span>
                                        <button 
                                            type="button"
                                            @click.stop="openThermalLabelModal(item)"
                                            title="Imprimir etiqueta térmica Code128 / QR"
                                            class="p-1.5 text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white rounded-lg transition-colors border border-indigo-200 cursor-pointer shadow-3xs flex items-center justify-center"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- BARRA DE PAGINACIÓN INCREMENTAL: BOTÓN "CARGAR MÁS" -->
            <div class="p-4 border-t border-slate-200 bg-slate-50/60 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-500 font-medium">
                    Mostrando <span class="font-bold text-slate-800" x-text="Math.min(visibleCount, filteredProducts.length)"></span> de <span class="font-bold text-slate-800" x-text="filteredProducts.length"></span> productos
                </div>
                <div class="flex items-center gap-2">
                    <template x-if="visibleCount < filteredProducts.length">
                        <button 
                            @click="loadMoreProducts()" 
                            :disabled="isLoadingMore"
                            class="px-4 py-2 bg-white border border-slate-300 hover:border-slate-800 rounded-lg text-xs font-bold text-slate-800 hover:bg-slate-50 shadow-sm transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50"
                        >
                            <template x-if="!isLoadingMore">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </template>
                            <template x-if="isLoadingMore">
                                <svg class="w-4 h-4 text-indigo-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            </template>
                            <span x-text="isLoadingMore ? 'Cargando más...' : 'Cargar más'"></span>
                        </button>
                    </template>
                    <template x-if="visibleCount >= filteredProducts.length && filteredProducts.length > 0">
                        <span class="text-xs text-slate-500 font-medium bg-slate-100 px-3 py-1.5 rounded-full border border-slate-200">
                            Todos los productos cargados
                        </span>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- PESTAÑA 2: PROVEEDORES & SCRAPING (7 PROVEEDORES + ÓRDENES PLANIFICADAS) -->
    <div x-show="activeTab === 'suppliers' && isTabAllowed('suppliers')" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
        
        <!-- 5 KPI Cards Proveedores con Tooltips & Modales de Detalle -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <!-- KPI 1: Proveedores Conectados (Clicable) -->
            <div 
                @click="providerKpiModalOpen = true"
                class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm hover:border-indigo-400 hover:shadow-md transition cursor-pointer group"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 group-hover:text-indigo-600 transition">Proveedores Conectados</span>
                    <div x-data="{ tip: false }" class="relative inline-flex items-center" @click.stop>
                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg>
                        </span>
                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                            <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Canales Mayoristas Activos</div>
                            <p class="text-[10px] text-slate-200 leading-snug">Empresas fabricantes y distribuidores vinculados mediante Scraper Puppeteer, Webhooks o REST API.</p>
                            <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xl font-bold text-slate-900" x-text="providers.length + ' Proveedores'">7 Proveedores</span>
                </div>
                <p class="text-xs text-slate-500 mt-1" x-text="connectedProvidersCount + ' activos • ' + maintenanceProvidersCount + ' en mant. • ' + pausedProvidersCount + ' pausados' + (archivedProvidersCount > 0 ? (' • ' + archivedProvidersCount + ' archivados') : '')">6 activos • 1 en mantenimiento</p>
                <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-indigo-600 font-semibold">
                    <span>Ver desglose y estados</span>
                    <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>

            <!-- KPI 2: Catálogo Externo Total (Informativo / NO Clicable) -->
            <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm cursor-default">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Catálogo Externo Total</span>
                    <div x-data="{ tip: false }" class="relative inline-flex items-center">
                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg>
                        </span>
                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                            <div class="font-bold text-indigo-300 text-[11px] mb-0.5">SKUs Sincronizados</div>
                            <p class="text-[10px] text-slate-200 leading-snug">Referencias activas en servidores de proveedores disponibles para venta sin stock físico en bodega.</p>
                            <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                        </div>
                    </div>
                </div>
                <div class="text-xl font-bold text-slate-900 mt-2" x-text="totalSyncedItemsCount + ' SKUs'">276 SKUs</div>
                <p class="text-xs text-emerald-600 mt-1 font-medium">Sincronización continua activa</p>
                <div class="mt-2 pt-2 border-t border-slate-100 text-[11px] text-slate-400">
                    Feed remoto B2B normalizado
                </div>
            </div>

            <!-- KPI 3: Tiempo de Respuesta / Latencia (Clicable) -->
            <div 
                @click="latencyKpiModalOpen = true"
                class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm hover:border-indigo-400 hover:shadow-md transition cursor-pointer group"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 group-hover:text-indigo-600 transition">Tiempo de Respuesta</span>
                    <div x-data="{ tip: false }" class="relative inline-flex items-center" @click.stop>
                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg>
                        </span>
                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                            <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Latencia de Conexión</div>
                            <p class="text-[10px] text-slate-200 leading-snug">Tiempo promedio en milisegundos que toma extraer o consultar las existencias de cada mayorista.</p>
                            <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                        </div>
                    </div>
                </div>
                <div class="text-xl font-bold text-slate-900 mt-2">1.18 seg</div>
                <p class="text-xs text-slate-500 mt-1">Monitoreo de red y Webhooks</p>
                <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-indigo-600 font-semibold">
                    <span>Ver telemetría y pings</span>
                    <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>

            <!-- KPI 4: Discrepancias Detectadas (Clicable) -->
            <div 
                @click="discrepancyKpiModalOpen = true"
                class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm hover:border-amber-400 hover:shadow-md transition cursor-pointer group"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 group-hover:text-amber-700 transition">Discrepancias Detectadas</span>
                    <div x-data="{ tip: false }" class="relative inline-flex items-center" @click.stop>
                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg>
                        </span>
                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                            <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Alertas de Stock/Costo/SLA</div>
                            <p class="text-[10px] text-slate-200 leading-snug">Divergencias en precios de mayoreo, variantes agotadas en fábrica o demoras de confección.</p>
                            <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-xl font-bold text-amber-600" x-text="(scrapingAlerts || []).filter(a => !a.resolved).length + ' Incidentes'">4 Incidentes</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-semibold">Auditoría B2B</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Costos, stock y demoras SLA</p>
                <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-amber-700 font-semibold">
                    <span>Ver historial de auditoría</span>
                    <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>

            <!-- KPI 5: SLA Promedio Proveedores (Clicable) -->
            <div 
                @click="slaKpiModalOpen = true"
                class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm hover:border-emerald-400 hover:shadow-md transition cursor-pointer group"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 group-hover:text-emerald-700 transition">SLA Promedio Proveedores</span>
                    <div x-data="{ tip: false }" class="relative inline-flex items-center" @click.stop>
                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-emerald-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg>
                        </span>
                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full right-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                            <div class="font-bold text-emerald-300 text-[11px] mb-0.5">Cumplimiento Acuerdos SLA</div>
                            <p class="text-[10px] text-slate-200 leading-snug">Promedio ponderado de entregas a tiempo (On-Time) y cumplimiento de piezas solicitadas (Fill Rate) en rampa de bodega.</p>
                            <span class="absolute bottom-full right-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-xl font-bold text-slate-900" x-text="averageSlaScore + '%'">96.4%</span>
                </div>
                <p class="text-xs text-emerald-600 mt-1 font-medium">On-Time & Fill Rate Global</p>
                <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-emerald-700 font-semibold">
                    <span>Scorecard & Ranking SLA</span>
                    <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>
        </div>

        <!-- CUERPO PRINCIPAL EN 2 COLUMNAS: CURADURÍA + AUDITORÍA DE DISCREPANCIAS B2B -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8 items-start">
            
            <!-- COLUMNA 1: BANDEJA DE CURADURÍA DE SCRAPING (STAGING AREA) -->
            <div class="bg-slate-900 text-white rounded-2xl shadow-md p-6 border border-slate-800 flex flex-col justify-between">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
                        <div>
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-amber-400 animate-ping"></span>
                                <h3 class="text-base font-bold text-white">Bandeja de Curaduría Previa de Scraping</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30" x-text="scrapedPendingItems.length + ' pendientes'">3 pendientes</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">Prendas extraídas automáticamente desde proveedores mayoristas pendientes de validación textil antes de publicarse en el catálogo</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-xs text-slate-400">Canal:</span>
                            <span class="text-xs font-semibold text-indigo-300 bg-indigo-950/60 border border-indigo-800 px-2.5 py-1 rounded-lg">Torzo Official B2B</span>
                        </div>
                    </div>

                    <!-- Listado Vertical Ergonómico de Prendas Scrapeadas -->
                    <div class="space-y-3 mt-4">
                        <template x-for="item in scrapedPendingItems" :key="item.id">
                            <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl p-4 flex flex-col justify-between hover:border-slate-600 transition space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-mono text-indigo-400 font-semibold" x-text="item.supplierSku || item.suggestedSku"></span>
                                            <span class="text-[10px] px-2 py-0.5 rounded bg-slate-700 text-slate-300" x-text="item.brand"></span>
                                        </div>
                                        <h4 class="text-sm font-bold text-white mt-1" x-text="item.name"></h4>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="text-[10px] text-slate-400 block uppercase">Costo Fábrica</span>
                                        <span class="text-sm font-bold text-emerald-400 font-mono" x-text="'$' + (item.scrapedCost || item.cost || 0).toFixed(2)"></span>
                                    </div>
                                </div>

                                <!-- Mini Badges de Atributos Detectados por Scraper -->
                                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                                    <span class="px-2 py-0.5 rounded bg-slate-900/80 text-slate-300 border border-slate-700 text-[11px]" x-text="'Cat: ' + item.suggestedCategory"></span>
                                    <span class="px-2 py-0.5 rounded bg-slate-900/80 text-slate-300 border border-slate-700 text-[11px]" x-text="'Tela: ' + item.suggestedFabric"></span>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-900/80 text-slate-300 border border-slate-700 text-[11px]">
                                        <span class="w-2.5 h-2.5 rounded-full border border-white/20" :style="'background-color: ' + item.detectedColorHex"></span>
                                        <span x-text="item.detectedColorName"></span>
                                    </span>
                                    <span class="px-2 py-0.5 rounded bg-indigo-950/60 text-indigo-300 border border-indigo-800 text-[11px]" x-text="'Tallas: ' + (Array.isArray(item.availableSizes) ? item.availableSizes.join(', ') : 'CH, M, G, XL')"></span>
                                </div>

                                <!-- Snippet de descripción scrapeada -->
                                <p class="text-xs text-slate-300 italic line-clamp-2 bg-slate-900/50 p-2.5 rounded-lg border border-slate-800" x-text="'“' + item.scrapedDescription + '”'"></p>

                                <!-- Botones de Acción de Curaduría -->
                                <div class="pt-2 border-t border-slate-700/60 flex items-center justify-between">
                                    <span class="text-[11px] text-slate-400" x-text="(item.supplierStock || 0) + ' uds en almacén proveedor'"></span>
                                    <button 
                                        type="button" 
                                        @click="openScrapingCuration(item)"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-bold transition shadow-sm cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        <span>Revisar, Curar & Publicar</span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="scrapedPendingItems.length === 0">
                            <div class="py-8 text-center text-slate-400 bg-slate-800/40 rounded-xl border border-slate-800">
                                <svg class="w-8 h-8 text-emerald-400 mx-auto mb-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <p class="text-sm font-semibold text-slate-300">Bandeja de curaduría al día</p>
                                <p class="text-xs text-slate-400 mt-0.5">Todas las prendas scrapeadas de proveedores han sido validadas y publicadas en el catálogo.</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- COLUMNA 2: CENTRO DE AUDITORÍA OPERATIVA & DISCREPANCIAS B2B (PROPUESTA 1 APROBADA) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 animate-pulse"></span>
                                <h3 class="text-base font-bold text-slate-900">Centro de Auditoría Operativa & Discrepancias B2B</h3>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Detección y resolución de variaciones de costo mayorista, quiebres en origen y retrasos de confección</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-xs text-slate-400">Último barrido: <strong class="text-slate-700" x-text="lastScrapeTime"></strong></span>
                            <button 
                                type="button" 
                                @click="triggerScraperQuickCheck()" 
                                :disabled="isScrapingNow"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer disabled:opacity-50"
                            >
                                <template x-if="!isScrapingNow">
                                    <svg class="w-3.5 h-3.5 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </template>
                                <template x-if="isScrapingNow">
                                    <svg class="w-3.5 h-3.5 text-indigo-200 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </template>
                                <span x-text="isScrapingNow ? 'Auditando...' : 'Quick-Check'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Filtros Rápidos por Categoría de Discrepancia B2B -->
                    <div class="flex items-center gap-1.5 mt-4 pb-3 border-b border-slate-100 flex-wrap text-xs">
                        <button 
                            type="button" 
                            @click="discrepancyCategoryFilter = 'all'"
                            :class="discrepancyCategoryFilter === 'all' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                            class="px-2.5 py-1 rounded-lg transition cursor-pointer"
                        >
                            Todas (<span x-text="(scrapingAlerts || []).length"></span>)
                        </button>
                        <button 
                            type="button" 
                            @click="discrepancyCategoryFilter = 'costs'"
                            :class="discrepancyCategoryFilter === 'costs' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                            class="px-2.5 py-1 rounded-lg transition cursor-pointer"
                        >
                            Costos & Precios (<span x-text="(scrapingAlerts || []).filter(a => a.category === 'costs').length"></span>)
                        </button>
                        <button 
                            type="button" 
                            @click="discrepancyCategoryFilter = 'stockouts'"
                            :class="discrepancyCategoryFilter === 'stockouts' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                            class="px-2.5 py-1 rounded-lg transition cursor-pointer"
                        >
                            Stock & Desabastos (<span x-text="(scrapingAlerts || []).filter(a => a.category === 'stockouts').length"></span>)
                        </button>
                        <button 
                            type="button" 
                            @click="discrepancyCategoryFilter = 'sla_delays'"
                            :class="discrepancyCategoryFilter === 'sla_delays' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                            class="px-2.5 py-1 rounded-lg transition cursor-pointer"
                        >
                            Demoras SLA (<span x-text="(scrapingAlerts || []).filter(a => a.category === 'sla_delays').length"></span>)
                        </button>
                    </div>

                    <!-- Feed de Discrepancias Operativas -->
                    <div class="space-y-3 mt-3">
                        <template x-for="alert in filteredDiscrepancies" :key="alert.id">
                            <div 
                                class="p-3.5 rounded-xl border transition flex flex-col justify-between gap-2.5"
                                :class="alert.resolved ? 'bg-slate-50/60 border-slate-200 opacity-60' : (alert.severity === 'danger' ? 'bg-rose-50/40 border-rose-200' : (alert.severity === 'warning' ? 'bg-amber-50/40 border-amber-200' : 'bg-emerald-50/40 border-emerald-200'))"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-2.5">
                                        <span 
                                            class="p-1.5 rounded-lg shrink-0 mt-0.5"
                                            :class="alert.severity === 'danger' ? 'bg-rose-100 text-rose-700' : (alert.severity === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700')"
                                        >
                                            <template x-if="alert.severity === 'danger'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            </template>
                                            <template x-if="alert.severity === 'warning'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </template>
                                            <template x-if="alert.severity === 'success' || alert.severity === 'info'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </template>
                                        </span>
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-mono font-bold text-xs text-indigo-700" x-text="alert.sku"></span>
                                                <span class="text-xs font-bold text-slate-800" x-text="alert.productName"></span>
                                                <span class="text-[10px] text-slate-500 font-semibold bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200" x-text="alert.supplier"></span>
                                                <template x-if="alert.resolved">
                                                    <span class="px-2 py-0.2 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">Resuelta</span>
                                                </template>
                                            </div>
                                            <p class="text-xs text-slate-600 mt-1 leading-snug" x-text="alert.message"></p>
                                        </div>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="alert.severity === 'danger' ? 'bg-rose-100 text-rose-800' : (alert.severity === 'warning' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800')" x-text="alert.impact || 'Alerta'"></span>
                                    </div>
                                </div>

                                <!-- Botones de Acción Resolutiva Contextual -->
                                <div class="pt-2 border-t border-slate-200/60 flex items-center justify-end gap-2 flex-wrap">
                                    <template x-if="!alert.resolved && alert.category === 'costs'">
                                        <div class="flex items-center gap-1.5">
                                            <button 
                                                type="button" 
                                                @click="resolveScraperAlert(alert.id, 'discard_variation')"
                                                class="px-2.5 py-1 text-slate-600 hover:text-slate-800 bg-white border border-slate-200 hover:bg-slate-100 rounded-lg text-xs font-medium transition cursor-pointer"
                                            >
                                                Descartar
                                            </button>
                                            <button 
                                                type="button" 
                                                @click="resolveScraperAlert(alert.id, 'adjust_pvp')"
                                                class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>Ajustar Costo y PVP</span>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="!alert.resolved && alert.category === 'stockouts'">
                                        <div class="flex items-center gap-1.5">
                                            <button 
                                                type="button" 
                                                x-show="alert.type === 'size_stockout'"
                                                @click="resolveScraperAlert(alert.id, 'pause_variant')"
                                                class="px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1"
                                            >
                                                <span>Pausar Variante</span>
                                            </button>
                                            <button 
                                                type="button" 
                                                x-show="alert.type === 'restock_available'"
                                                @click="resolveScraperAlert(alert.id, 'sync_stock')"
                                                class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1"
                                            >
                                                <span>Sincronizar Stock Remoto</span>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="!alert.resolved && alert.category === 'sla_delays'">
                                        <div class="flex items-center gap-1.5">
                                            <button 
                                                type="button" 
                                                @click="resolveScraperAlert(alert.id, 'reschedule_po')"
                                                class="px-2.5 py-1 text-slate-600 hover:text-slate-800 bg-white border border-slate-200 hover:bg-slate-100 rounded-lg text-xs font-medium transition cursor-pointer"
                                            >
                                                Reprogramar Rampa
                                            </button>
                                            <button 
                                                type="button" 
                                                @click="resolveScraperAlert(alert.id, 'contact_vendor')"
                                                class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                <span>Contactar Proveedor</span>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="filteredDiscrepancies.length === 0">
                            <div class="py-6 text-center text-slate-400 bg-slate-50 rounded-xl border border-slate-200">
                                <svg class="w-7 h-7 text-emerald-500 mx-auto mb-1.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <p class="text-xs font-bold text-slate-700">Sin discrepancias pendientes en esta categoría</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Los parámetros comerciales y de entrega de tus proveedores están en regla.</p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer de Acceso Directo a Ajustes -->
                <div class="pt-4 border-t border-slate-100 mt-5 flex items-center justify-between text-xs text-slate-500">
                    <span>Gobernanza y conectores en catálogo central</span>
                    <button 
                        type="button" 
                        @click="activeSettingsTab = 'scraping'; moduleSettingsModalOpen = true;" 
                        class="inline-flex items-center gap-1 font-bold text-indigo-600 hover:text-indigo-800 transition cursor-pointer"
                    >
                        <span>Abrir Proveedores en Ajustes &rarr;</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- ================================================================= -->
        <!-- MODAL ERGONÓMICO: DETALLES, CONDICIONES & SLA SCORECARD (TASK-INV-13) -->
        <!-- ================================================================= -->
        <div 
            x-show="providerModalOpen" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[90] overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            style="display: none;"
        >
            <div 
                @click.outside="providerModalOpen = false"
                class="bg-white rounded-2xl shadow-2xl border border-slate-200/80 w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]"
            >
                <!-- Cabecera del Modal -->
                <div class="px-6 py-4 border-b border-slate-200/80 bg-slate-50/60 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900" x-text="isCreatingProvider ? 'Conectar / Registrar Nuevo Proveedor' : (isEditingProvider ? 'Editar Condiciones: ' + (providerForm.tradeName || providerForm.name) : 'Ficha de Proveedor & Scorecard SLA')"></h3>
                            <p class="text-xs text-slate-500" x-text="isCreatingProvider ? 'Alta de taller de confección, fabricante cosmético o mayorista local' : (providerForm.vendorCode ? 'Código ' + providerForm.vendorCode + ' • ' + providerForm.name : 'Información técnica y comercial')"></p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        @click="providerModalOpen = false" 
                        class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                        aria-label="Cerrar modal"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Cuerpo del Modal (2 Columnas) -->
                <div class="p-6 overflow-y-auto grid grid-cols-1 md:grid-cols-12 gap-6">
                    
                    <!-- Columna Izquierda: Ficha Comercial & Contacto (7 Cols) -->
                    <div class="md:col-span-7 space-y-4">
                        <div class="border-b border-slate-100 pb-2 flex items-center justify-between">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Información General & Contacto</h4>
                            <span x-show="!isCreatingProvider && !isEditingProvider" class="text-[11px] text-slate-400 font-medium">Modo solo lectura</span>
                        </div>

                        <!-- Razón Social y Nombre Comercial -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Razón Social *</label>
                                <input 
                                    type="text" 
                                    x-model="providerForm.name" 
                                    :disabled="!isCreatingProvider && !isEditingProvider"
                                    placeholder="Ej. Confecciones Textiles Puebla SA de CV" 
                                    class="w-full px-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 disabled:bg-slate-50 disabled:text-slate-600 border-slate-200"
                                >
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Nombre Comercial / Marca *</label>
                                <input 
                                    type="text" 
                                    x-model="providerForm.tradeName" 
                                    :disabled="!isCreatingProvider && !isEditingProvider"
                                    placeholder="Ej. ModaIntima MX" 
                                    class="w-full px-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 disabled:bg-slate-50 disabled:text-slate-600 border-slate-200"
                                >
                            </div>
                        </div>

                        <!-- Tipo de Proveedor y Ciudad -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Tipo / Giro Comercial *</label>
                                <select 
                                    x-model="providerForm.type" 
                                    :disabled="!isCreatingProvider && !isEditingProvider"
                                    class="w-full px-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 disabled:bg-slate-50 disabled:text-slate-600 border-slate-200"
                                >
                                    <option value="workshop">Taller de Confección Textil</option>
                                    <option value="cosmetics">Fabricante Cosmética & Skincare</option>
                                    <option value="distributor">Distribuidor Mayorista</option>
                                    <option value="packaging">Insumos de Empaque & Cajas</option>
                                    <option value="commercial">Proveedor Comercial Directo</option>
                                    <option value="scraper">Integración Automatizada (Scraper/API)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Ciudad / Ubicación</label>
                                <input 
                                    type="text" 
                                    x-model="providerForm.city" 
                                    :disabled="!isCreatingProvider && !isEditingProvider"
                                    placeholder="Ej. CDMX, Puebla, Guadalajara" 
                                    class="w-full px-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 disabled:bg-slate-50 disabled:text-slate-600 border-slate-200"
                                >
                            </div>
                        </div>

                        <!-- Persona de Contacto, Teléfono y Correo -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Contacto Operativo *</label>
                                <input 
                                    type="text" 
                                    x-model="providerForm.contactName" 
                                    :disabled="!isCreatingProvider && !isEditingProvider"
                                    placeholder="Nombre completo" 
                                    class="w-full px-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 disabled:bg-slate-50 disabled:text-slate-600 border-slate-200"
                                >
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">WhatsApp / Teléfono *</label>
                                <input 
                                    type="tel" 
                                    x-model="providerForm.phone" 
                                    :disabled="!isCreatingProvider && !isEditingProvider"
                                    placeholder="+52 55..." 
                                    class="w-full px-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 disabled:bg-slate-50 disabled:text-slate-600 border-slate-200"
                                >
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Email Pedidos</label>
                                <input 
                                    type="email" 
                                    x-model="providerForm.email" 
                                    :disabled="!isCreatingProvider && !isEditingProvider"
                                    placeholder="pedidos@empresa.com" 
                                    class="w-full px-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 disabled:bg-slate-50 disabled:text-slate-600 border-slate-200"
                                >
                            </div>
                        </div>

                        <!-- Condiciones Comerciales: Lead Time y Plazos de Pago -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200/70">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Lead Time Prometido (Días)</label>
                                <div class="flex items-center gap-2">
                                    <input 
                                        type="number" 
                                        min="1" 
                                        max="90" 
                                        x-model="providerForm.leadTimeDays" 
                                        :disabled="!isCreatingProvider && !isEditingProvider"
                                        class="w-24 px-3 py-1.5 text-xs font-bold border rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 disabled:bg-white disabled:text-slate-800 border-slate-200 text-center"
                                    >
                                    <span class="text-xs text-slate-500">días hábiles a rampa</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Términos de Pago</label>
                                <select 
                                    x-model="providerForm.paymentTerms" 
                                    :disabled="!isCreatingProvider && !isEditingProvider"
                                    class="w-full px-3 py-1.5 text-xs border rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 disabled:bg-white disabled:text-slate-800 border-slate-200"
                                >
                                    <option value="contado">Contado / Anticipo 100%</option>
                                    <option value="credito_15">Crédito Comercial 15 días</option>
                                    <option value="credito_30">Crédito Comercial 30 días</option>
                                    <option value="credito_60">Crédito Comercial 60 días</option>
                                </select>
                            </div>
                        </div>

                        <!-- Notas Operativas -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Notas Internas & Acuerdos de Maquila</label>
                            <textarea 
                                x-model="providerForm.notes" 
                                :disabled="!isCreatingProvider && !isEditingProvider"
                                rows="2" 
                                placeholder="Detalles de maquila, acuerdos especiales de tela, MOQ o especificaciones..." 
                                class="w-full px-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 disabled:bg-slate-50 disabled:text-slate-600 border-slate-200 resize-none"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Columna Derecha: Scorecard SLA & Control de Estado (5 Cols) -->
                    <div class="md:col-span-5 space-y-4">
                        <div class="border-b border-slate-100 pb-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Desempeño SLA & Confiabilidad</h4>
                        </div>

                        <!-- Modo Proveedor Existente: Scorecard SLA -->
                        <div x-show="!isCreatingProvider && selectedProvider" class="space-y-4">
                            <!-- Tarjeta On-Time Delivery -->
                            <div class="p-4 bg-emerald-50/60 rounded-xl border border-emerald-100">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-bold text-emerald-900">Entregas a Tiempo (On-Time)</span>
                                    <span class="text-sm font-black text-emerald-700" x-text="(selectedProvider?.slaOnTimePct || 100) + '%'"></span>
                                </div>
                                <div class="w-full bg-emerald-200/60 rounded-full h-2 overflow-hidden">
                                    <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" :style="'width: ' + (selectedProvider?.slaOnTimePct || 100) + '%'"></div>
                                </div>
                                <p class="text-[10px] text-emerald-800 mt-1.5 font-medium">Histórico validado en rampa de recepción contra fecha compromiso de OC.</p>
                            </div>

                            <!-- Tarjeta Fill Rate (Sin Faltantes) -->
                            <div class="p-4 bg-indigo-50/60 rounded-xl border border-indigo-100">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-bold text-indigo-900">Cumplimiento de Cantidad (Fill Rate)</span>
                                    <span class="text-sm font-black text-indigo-700" x-text="(selectedProvider?.slaFillRatePct || 100) + '%'"></span>
                                </div>
                                <div class="w-full bg-indigo-200/60 rounded-full h-2 overflow-hidden">
                                    <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" :style="'width: ' + (selectedProvider?.slaFillRatePct || 100) + '%'"></div>
                                </div>
                                <p class="text-[10px] text-indigo-800 mt-1.5 font-medium">Porcentaje de piezas físicas recibidas completas sin remanentes pendientes.</p>
                            </div>

                            <!-- Comparativa Lead Time y Órdenes -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-center">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Demora Promedio</span>
                                    <span class="text-base font-black text-slate-800" x-text="'+' + (selectedProvider?.avgDelayDays || 0) + ' d'"></span>
                                    <span class="block text-[10px] text-slate-500">sobre fecha pactada</span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-center">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Órdenes OCs</span>
                                    <span class="text-base font-black text-slate-800" x-text="(selectedProvider?.completedOrders || 0) + ' / ' + (selectedProvider?.totalOrders || 0)"></span>
                                    <span class="block text-[10px] text-slate-500">completadas</span>
                                </div>
                            </div>

                            <!-- Selector de Estado Técnico / Operativo -->
                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                                <label class="block text-[11px] font-bold text-slate-700 mb-2">Estado Operativo en Plataforma</label>
                                <template x-if="selectedProvider?.status === 'archived'">
                                    <div class="p-3 bg-slate-100 rounded-lg border border-slate-300 text-center">
                                        <div class="flex items-center justify-center gap-1.5 text-xs font-bold text-slate-700 mb-1">
                                            <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                                            <span>Proveedor Archivado / Descontinuado</span>
                                        </div>
                                        <p class="text-[10px] text-slate-500 mb-2">No recibe pedidos automáticos ni órdenes de reabastecimiento.</p>
                                        <button 
                                            type="button" 
                                            @click="restoreProvider(selectedProvider)"
                                            class="w-full py-1 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition flex items-center justify-center gap-1 cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            <span>Restaurar a Estado Conectado</span>
                                        </button>
                                    </div>
                                </template>
                                <template x-if="selectedProvider?.status !== 'archived'">
                                    <div class="grid grid-cols-3 gap-2">
                                        <button 
                                            type="button" 
                                            @click="providerForm.status = 'connected'; if (!isEditingProvider) toggleProviderStatus(selectedProvider, 'connected')"
                                            :class="providerForm.status === 'connected' ? 'bg-emerald-600 text-white font-bold' : 'bg-white text-slate-700 hover:bg-slate-100 font-medium'"
                                            class="px-2 py-1.5 text-xs rounded-lg border border-slate-200 transition text-center cursor-pointer"
                                        >
                                            Conectado
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="providerForm.status = 'maintenance'; if (!isEditingProvider) toggleProviderStatus(selectedProvider, 'maintenance')"
                                            :class="providerForm.status === 'maintenance' ? 'bg-amber-500 text-white font-bold' : 'bg-white text-slate-700 hover:bg-slate-100 font-medium'"
                                            class="px-2 py-1.5 text-xs rounded-lg border border-slate-200 transition text-center cursor-pointer"
                                        >
                                            Mantenimiento
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="providerForm.status = 'paused'; if (!isEditingProvider) toggleProviderStatus(selectedProvider, 'paused')"
                                            :class="providerForm.status === 'paused' ? 'bg-slate-600 text-white font-bold' : 'bg-white text-slate-700 hover:bg-slate-100 font-medium'"
                                            class="px-2 py-1.5 text-xs rounded-lg border border-slate-200 transition text-center cursor-pointer"
                                        >
                                            Pausado
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Modo Alta: Tarjeta informativa de SLA -->
                        <div x-show="isCreatingProvider" class="p-4 bg-indigo-50/50 rounded-xl border border-indigo-100 text-slate-700 space-y-2">
                            <div class="flex items-center gap-2 font-bold text-xs text-indigo-900">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Monitoreo Automático de Desempeño</span>
                            </div>
                            <p class="text-xs leading-relaxed text-slate-600">
                                Al vincular este proveedor comercial o taller, el sistema comenzará a auditar automáticamente sus tiempos de entrega y cumplimiento de piezas en el Kardex de Compras (TASK-INV-16) cada vez que se reciba un lote en la rampa de almacén.
                            </p>
                            <div class="pt-2 text-[11px] font-semibold text-slate-500">
                                Estado inicial recomendado: <span class="text-emerald-700 font-bold">Conectado / Activo</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Pie del Modal: Acciones -->
                <div class="px-6 py-4 border-t border-slate-200/80 bg-slate-50/60 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2">
                        <span x-show="!isCreatingProvider && !isEditingProvider" class="text-xs text-slate-400 font-mono" x-text="'ID: ' + (selectedProvider?.id || '')"></span>
                        <template x-if="!isCreatingProvider && !isEditingProvider && selectedProvider">
                            <div class="inline-flex items-center gap-2 ml-3 pl-3 border-l border-slate-200">
                                <template x-if="selectedProvider.status !== 'archived'">
                                    <button 
                                        type="button" 
                                        @click="archiveProvider(selectedProvider)" 
                                        class="px-2.5 py-1 text-xs text-slate-500 hover:text-amber-700 hover:bg-amber-50 rounded-lg transition cursor-pointer flex items-center gap-1 border border-slate-200 hover:border-amber-200"
                                        title="Archivar proveedor"
                                    >
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                        <span>Archivar</span>
                                    </button>
                                </template>
                                <template x-if="selectedProvider.status === 'archived'">
                                    <button 
                                        type="button" 
                                        @click="restoreProvider(selectedProvider)" 
                                        class="px-2.5 py-1 text-xs text-emerald-700 hover:bg-emerald-50 rounded-lg transition cursor-pointer flex items-center gap-1 border border-emerald-300"
                                        title="Restaurar proveedor a activo"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <span>Restaurar</span>
                                    </button>
                                </template>
                                <button 
                                    type="button" 
                                    @click="deleteProvider(selectedProvider)" 
                                    class="px-2.5 py-1 text-xs text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition cursor-pointer flex items-center gap-1 border border-rose-200"
                                    title="Eliminar definitivamente de la base de datos"
                                >
                                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Eliminar</span>
                                </button>
                            </div>
                        </template>
                    </div>
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            @click="cancelProviderEdit()" 
                            class="px-4 py-2 text-slate-600 hover:text-slate-800 text-xs font-semibold rounded-xl hover:bg-slate-200/60 transition cursor-pointer"
                        >
                            <span x-text="isCreatingProvider ? 'Cancelar' : (isEditingProvider ? 'Descartar Cambios' : 'Cerrar')"></span>
                        </button>
                        <button 
                            type="button" 
                            x-show="!isCreatingProvider && !isEditingProvider"
                            @click="editProviderFromModal()" 
                            class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition border border-indigo-200/80 cursor-pointer flex items-center gap-1.5"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Editar Condiciones</span>
                        </button>
                        <button 
                            type="button" 
                            x-show="isCreatingProvider || isEditingProvider"
                            @click="saveProvider()" 
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="isCreatingProvider ? 'Guardar Proveedor' : 'Guardar Cambios'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MODAL KPI 1: RED Y ESTADO OPERATIVO DE PROVEEDORES                -->
        <!-- ================================================================= -->
        <div 
            x-show="providerKpiModalOpen" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            style="display: none;"
        >
            <div @click.outside="providerKpiModalOpen = false" class="bg-white rounded-2xl shadow-2xl border border-slate-200/80 w-full max-w-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200/80 bg-slate-50/60 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Red y Estado Operativo de Proveedores</h3>
                            <p class="text-xs text-slate-500">Desglose de fabricantes, talleres y mayoristas en plataforma</p>
                        </div>
                    </div>
                    <button type="button" @click="providerKpiModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3 bg-emerald-50/60 border border-emerald-100 rounded-xl text-center">
                            <span class="text-xs font-bold text-emerald-800 uppercase block">Conectados</span>
                            <span class="text-lg font-black text-emerald-600" x-text="connectedProvidersCount"></span>
                        </div>
                        <div class="p-3 bg-amber-50/60 border border-amber-100 rounded-xl text-center">
                            <span class="text-xs font-bold text-amber-800 uppercase block">Mantenimiento</span>
                            <span class="text-lg font-black text-amber-600" x-text="maintenanceProvidersCount"></span>
                        </div>
                        <div class="p-3 bg-slate-100 border border-slate-200 rounded-xl text-center">
                            <span class="text-xs font-bold text-slate-700 uppercase block">Pausados</span>
                            <span class="text-lg font-black text-slate-700" x-text="pausedProvidersCount"></span>
                        </div>
                        <div class="p-3 bg-slate-200/60 border border-slate-300 rounded-xl text-center">
                            <span class="text-xs font-bold text-slate-600 uppercase block">Archivados</span>
                            <span class="text-lg font-black text-slate-600" x-text="archivedProvidersCount"></span>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Proveedores Registrados</h4>
                        <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden">
                            <template x-for="p in providers" :key="p.id">
                                <div class="p-3 bg-white flex items-center justify-between gap-3 text-xs" :class="p.status === 'archived' ? 'opacity-60 bg-slate-50' : ''">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-2 h-2 rounded-full shrink-0" :class="{
                                            'bg-emerald-500': p.status === 'connected',
                                            'bg-amber-500': p.status === 'maintenance',
                                            'bg-slate-400': p.status === 'paused',
                                            'bg-slate-500': p.status === 'archived'
                                        }"></span>
                                        <div>
                                            <div class="font-bold text-slate-900" x-text="p.tradeName || p.name"></div>
                                            <div class="text-[11px] text-slate-500" x-text="(p.city || 'Nacional') + ' • ' + (p.typeLabel || 'Proveedor')"></div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-mono text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-semibold" x-text="p.status"></span>
                                        <div class="text-[10px] text-slate-400 mt-0.5" x-text="'Lead Time: ' + p.leadTimeDays + ' d'"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-3 border-t border-slate-200 bg-slate-50/60 flex items-center justify-between">
                    <button type="button" @click="providerKpiModalOpen = false" class="px-3 py-1.5 text-xs text-slate-600 hover:text-slate-800 rounded-lg transition cursor-pointer">
                        Cerrar
                    </button>
                    <button 
                        type="button" 
                        @click="providerKpiModalOpen = false; activeSettingsTab = 'scraping'; moduleSettingsModalOpen = true;"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5"
                    >
                        <span>Gestionar en Ajustes de Inventario &rarr;</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MODAL KPI 3: DIAGNÓSTICO DE TELEMETRÍA & SALUD DE RED             -->
        <!-- ================================================================= -->
        <div 
            x-show="latencyKpiModalOpen" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            style="display: none;"
        >
            <div @click.outside="latencyKpiModalOpen = false" class="bg-white rounded-2xl shadow-2xl border border-slate-200/80 w-full max-w-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200/80 bg-slate-50/60 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Diagnóstico de Telemetría & Salud de Red</h3>
                            <p class="text-xs text-slate-500">Métricas de latencia, ping de APIs y estado de webhooks</p>
                        </div>
                    </div>
                    <button type="button" @click="latencyKpiModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-center">
                            <span class="text-xs font-bold text-slate-500 uppercase block">Latencia Media</span>
                            <span class="text-xl font-black text-slate-900 mt-1 block">1.18 s</span>
                            <span class="text-[10px] text-emerald-600 font-semibold">Dentro del umbral optimo</span>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-center">
                            <span class="text-xs font-bold text-slate-500 uppercase block">Uptime Conectores</span>
                            <span class="text-xl font-black text-emerald-600 mt-1 block">99.8%</span>
                            <span class="text-[10px] text-slate-500">Ultimos 30 dias</span>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-center">
                            <span class="text-xs font-bold text-slate-500 uppercase block">Tasa de Error HTTP</span>
                            <span class="text-xl font-black text-indigo-600 mt-1 block">0.2%</span>
                            <span class="text-[10px] text-slate-500">Reintentos automaticos: 3</span>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Desglose de Conectividad por Proveedor</h4>
                        <div class="space-y-2 text-xs">
                            <div class="p-3 bg-white border border-slate-200 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="font-bold text-slate-900">Torzo Puppeteer Scraper</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">Headless v2.4</span>
                                </div>
                                <div class="font-mono text-xs font-semibold text-slate-700">1,420 ms &bull; HTTP 200 OK</div>
                            </div>
                            <div class="p-3 bg-white border border-slate-200 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="font-bold text-slate-900">Silk & Cotton REST API</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">JSON Bearer</span>
                                </div>
                                <div class="font-mono text-xs font-semibold text-emerald-600">310 ms &bull; HTTP 200 OK</div>
                            </div>
                            <div class="p-3 bg-white border border-slate-200 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="font-bold text-slate-900">Lingerie Drop Miami GraphQL</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">SSL v3</span>
                                </div>
                                <div class="font-mono text-xs font-semibold text-emerald-600">450 ms &bull; HTTP 200 OK</div>
                            </div>
                            <div class="p-3 bg-white border border-slate-200 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="font-bold text-slate-900">Seamless Tech Endpoint</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">API Sync</span>
                                </div>
                                <div class="font-mono text-xs font-semibold text-emerald-600">280 ms &bull; HTTP 200 OK</div>
                            </div>
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between opacity-70">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                    <span class="font-bold text-slate-800">ModaIntima MX & Talleres Locales</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-200 text-slate-600">Confeccion Local</span>
                                </div>
                                <div class="text-[11px] text-slate-500">Gestion mediante Kardex de Compras (OC)</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-3 border-t border-slate-200 bg-slate-50/60 flex justify-end">
                    <button type="button" @click="latencyKpiModalOpen = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition cursor-pointer">
                        Cerrar Diagnostico
                    </button>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MODAL KPI 4: REGISTRO DE AUDITORÍA DE DISCREPANCIAS B2B           -->
        <!-- ================================================================= -->
        <div 
            x-show="discrepancyKpiModalOpen" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            style="display: none;"
        >
            <div @click.outside="discrepancyKpiModalOpen = false" class="bg-white rounded-2xl shadow-2xl border border-slate-200/80 w-full max-w-3xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200/80 bg-slate-50/60 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="p-2 bg-amber-50 text-amber-600 rounded-xl border border-amber-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Registro de Auditoría de Discrepancias B2B</h3>
                            <p class="text-xs text-slate-500">Historial de alertas de costo, desabastos y demoras en entregas</p>
                        </div>
                    </div>
                    <button type="button" @click="discrepancyKpiModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <div class="flex items-center justify-between text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <div>
                            Total Registradas: <span class="font-bold text-slate-900" x-text="(scrapingAlerts || []).length"></span>
                        </div>
                        <div class="flex items-center gap-3 font-semibold">
                            <span class="text-amber-700">Pendientes: <span x-text="(scrapingAlerts || []).filter(a => !a.resolved).length"></span></span>
                            <span class="text-emerald-700">Resueltas: <span x-text="(scrapingAlerts || []).filter(a => a.resolved).length"></span></span>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden">
                        <template x-for="alert in scrapingAlerts" :key="alert.id">
                            <div class="p-3.5 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs" :class="alert.resolved ? 'bg-slate-50/50 opacity-70' : ''">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-indigo-700" x-text="alert.sku"></span>
                                        <span class="font-bold text-slate-800" x-text="alert.productName"></span>
                                        <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 border border-slate-200" x-text="alert.supplier"></span>
                                    </div>
                                    <p class="text-[11px] text-slate-600 mt-1" x-text="alert.message"></p>
                                </div>
                                <div class="text-right shrink-0 flex sm:flex-col items-center sm:items-end justify-between gap-1">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="alert.resolved ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'" x-text="alert.resolved ? 'Resuelta' : 'Pendiente'"></span>
                                    <span class="text-[10px] text-slate-500 font-semibold" x-text="alert.impact || 'Alerta'"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="px-6 py-3 border-t border-slate-200 bg-slate-50/60 flex justify-end">
                    <button type="button" @click="discrepancyKpiModalOpen = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition cursor-pointer">
                        Cerrar Historial
                    </button>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MODAL KPI 5: SCORECARD GLOBAL DE SLA & RANKING DE CONFIABILIDAD   -->
        <!-- ================================================================= -->
        <div 
            x-show="slaKpiModalOpen" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            style="display: none;"
        >
            <div @click.outside="slaKpiModalOpen = false" class="bg-white rounded-2xl shadow-2xl border border-slate-200/80 w-full max-w-4xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200/80 bg-slate-50/60 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Scorecard Global de SLA & Ranking de Confiabilidad</h3>
                            <p class="text-xs text-slate-500">Auditoría formal de cumplimiento en entregas a rampa y completitud de pedidos</p>
                        </div>
                    </div>
                    <button type="button" @click="slaKpiModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <!-- Top KPIs del Scorecard -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3 bg-emerald-50/60 border border-emerald-100 rounded-xl text-center">
                            <span class="text-xs font-bold text-emerald-800 uppercase block">SLA Ponderado</span>
                            <span class="text-xl font-black text-emerald-700 mt-1 block" x-text="averageSlaScore + '%'">96.4%</span>
                            <span class="text-[10px] text-emerald-600">Nivel excelente</span>
                        </div>
                        <div class="p-3 bg-indigo-50/60 border border-indigo-100 rounded-xl text-center">
                            <span class="text-xs font-bold text-indigo-800 uppercase block">On-Time Promedio</span>
                            <span class="text-xl font-black text-indigo-700 mt-1 block">95.1%</span>
                            <span class="text-[10px] text-indigo-600">A tiempo en rampa</span>
                        </div>
                        <div class="p-3 bg-blue-50/60 border border-blue-100 rounded-xl text-center">
                            <span class="text-xs font-bold text-blue-800 uppercase block">Fill Rate Promedio</span>
                            <span class="text-xl font-black text-blue-700 mt-1 block">97.4%</span>
                            <span class="text-[10px] text-blue-600">Sin piezas faltantes</span>
                        </div>
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-center">
                            <span class="text-xs font-bold text-slate-600 uppercase block">Órdenes Totales</span>
                            <span class="text-xl font-black text-slate-800 mt-1 block">96 OCs</span>
                            <span class="text-[10px] text-slate-500">Histórico auditado</span>
                        </div>
                    </div>

                    <!-- Tabla de Ranking de Proveedores -->
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[11px] uppercase tracking-wider font-semibold">
                                <tr>
                                    <th class="py-3 px-4">Proveedor & Giro</th>
                                    <th class="py-3 px-4 text-center">On-Time %</th>
                                    <th class="py-3 px-4 text-center">Fill Rate %</th>
                                    <th class="py-3 px-4 text-center">Demora Media</th>
                                    <th class="py-3 px-4 text-center">Órdenes</th>
                                    <th class="py-3 px-4 text-right">Confiabilidad</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <template x-for="p in providers.filter(prov => prov.status !== 'archived')" :key="p.id">
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-900" x-text="p.tradeName || p.name"></div>
                                            <div class="text-[10px] text-slate-400" x-text="(p.city || 'Nacional') + ' • ' + (p.typeLabel || 'Proveedor')"></div>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1.5 font-bold text-slate-800">
                                                <span x-text="(p.slaOnTimePct || 100) + '%'"></span>
                                            </div>
                                            <div class="w-16 bg-slate-200 h-1.5 rounded-full mx-auto mt-1 overflow-hidden">
                                                <div class="bg-emerald-500 h-1.5 rounded-full" :style="'width: ' + (p.slaOnTimePct || 100) + '%'"></div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1.5 font-bold text-slate-800">
                                                <span x-text="(p.slaFillRatePct || 100) + '%'"></span>
                                            </div>
                                            <div class="w-16 bg-slate-200 h-1.5 rounded-full mx-auto mt-1 overflow-hidden">
                                                <div class="bg-indigo-600 h-1.5 rounded-full" :style="'width: ' + (p.slaFillRatePct || 100) + '%'"></div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-center font-mono text-slate-700" x-text="'+' + (p.avgDelayDays || 0) + ' d'"></td>
                                        <td class="py-3 px-4 text-center text-slate-600" x-text="(p.completedOrders || 0) + ' / ' + (p.totalOrders || 0)"></td>
                                        <td class="py-3 px-4 text-right">
                                            <span 
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                :class="{
                                                    'bg-emerald-100 text-emerald-800': ((p.slaOnTimePct || 100) + (p.slaFillRatePct || 100)) / 2 >= 95,
                                                    'bg-amber-100 text-amber-800': ((p.slaOnTimePct || 100) + (p.slaFillRatePct || 100)) / 2 >= 90 && ((p.slaOnTimePct || 100) + (p.slaFillRatePct || 100)) / 2 < 95,
                                                    'bg-rose-100 text-rose-800': ((p.slaOnTimePct || 100) + (p.slaFillRatePct || 100)) / 2 < 90
                                                }"
                                                x-text="((p.slaOnTimePct || 100) + (p.slaFillRatePct || 100)) / 2 >= 95 ? 'Excelente' : (((p.slaOnTimePct || 100) + (p.slaFillRatePct || 100)) / 2 >= 90 ? 'Aceptable' : 'Riesgo')">
                                            </span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="px-6 py-3 border-t border-slate-200 bg-slate-50/60 flex justify-end">
                    <button type="button" @click="slaKpiModalOpen = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition cursor-pointer">
                        Cerrar Scorecard
                    </button>
                </div>
            </div>
        </div>

    
        @if(isset($syncRuns) && $syncRuns->isNotEmpty())
            <!-- SECCION: HISTORIAL DE SINCRONIZACIONES REALES (LARAVEL BACKEND) -->
            <div class="mt-8 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Historial de Sincronizaciones del Backend</span>
                    </h3>
                    <span class="text-xs text-slate-500 font-semibold">{{ $syncRuns->count() }} registros recientes</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 font-semibold uppercase tracking-wider">
                                <th class="py-2.5 px-3">Proveedor</th>
                                <th class="py-2.5 px-3">Estado</th>
                                <th class="py-2.5 px-3">Inicio</th>
                                <th class="py-2.5 px-3">Resultado / Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($syncRuns as $run)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-2.5 px-3 font-bold text-slate-800">{{ $run->supplier->name ?? 'Proveedor' }}</td>
                                    <td class="py-2.5 px-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $run->status === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            {{ ucfirst($run->status) }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-500">{{ $run->started_at?->format('d/m/Y H:i') }}</td>
                                    <td class="py-2.5 px-3 text-slate-600 font-mono">{{ $run->error ?? ($run->synced_count . ' productos sincronizados') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if(isset($variantMappings) && $variantMappings->isNotEmpty())
            <!-- SECCION: MAPEOS DE VARIANTES Y STOCK DROPSHIP (LARAVEL BACKEND) -->
            <div class="mt-6 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        <span>Mapeo Activo de Variantes con Mayoristas</span>
                    </h3>
                    <span class="text-xs text-slate-500 font-semibold">{{ $variantMappings->count() }} variantes vinculadas</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 font-semibold uppercase tracking-wider">
                                <th class="py-2.5 px-3">Producto Local</th>
                                <th class="py-2.5 px-3">Proveedor / SKU Externo</th>
                                <th class="py-2.5 px-3">Llave de Variante</th>
                                <th class="py-2.5 px-3">Stock Sincronizado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($variantMappings as $map)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-2.5 px-3 font-bold text-slate-800">{{ $map->productVariant?->product?->title ?? 'Producto' }}</td>
                                    <td class="py-2.5 px-3 text-slate-600">{{ $map->supplierProduct?->supplier?->name ?? 'Mayorista' }} ({{ $map->supplierProduct?->supplier_sku ?? 'N/A' }})</td>
                                    <td class="py-2.5 px-3 font-mono text-indigo-600 font-semibold">{{ $map->supplier_variant_key }}</td>
                                    <td class="py-2.5 px-3 font-bold text-slate-900">{{ $map->stock }} pzas</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>

    <!-- ================================================================= -->
    <!-- PESTAÑA 3: COMPRAS & REABASTECIMIENTO                             -->
    <!-- ================================================================= -->
    <div x-show="activeTab === 'purchases' && isTabAllowed('purchases')" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
        
        <!-- 4 KPI Cards Compras con Tooltips ? -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- KPI 1: Órdenes en Tránsito -->
            <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Órdenes en Tránsito</span>
                    <div x-data="{ tip: false }" class="relative inline-flex items-center">
                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg>
                        </span>
                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                            <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Órdenes Activas</div>
                            <p class="text-[10px] text-slate-200 leading-snug">Pedidos de compra ya emitidos a mayoristas o fabricantes que se encuentran en preparación o despacho con guía de paquetería.</p>
                            <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                        </div>
                    </div>
                </div>
                <div class="text-xl font-bold text-indigo-600 mt-2">2 Órdenes</div>
                <p class="text-xs text-slate-500 mt-1">1 en camino &bull; 1 por confirmar</p>
            </div>

            <!-- KPI 2: Lotes Planificados -->
            <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Lotes Planificados</span>
                    <div x-data="{ tip: false }" class="relative inline-flex items-center">
                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg>
                        </span>
                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                            <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Cola de Futuras Compras</div>
                            <p class="text-[10px] text-slate-200 leading-snug">Artículos agregados desde los Drawers de producto para consolidar pedidos masivos antes de quebrar existencias.</p>
                            <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                        </div>
                    </div>
                </div>
                <div class="text-xl font-bold text-indigo-600 mt-2" x-text="futurePurchaseOrders.length + ' lotes en cola'"></div>
                <p class="text-xs text-emerald-600 mt-1 font-medium">Acumulando para MOQ de proveedor</p>
            </div>

            <!-- KPI 3: Inversión Proyectada -->
            <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Inversión Estimada</span>
                    <div x-data="{ tip: false }" class="relative inline-flex items-center">
                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg>
                        </span>
                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                            <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Compromiso Financiero RevOps</div>
                            <p class="text-[10px] text-slate-200 leading-snug">Costo total proyectado de la reposición planificada en cola calculada a costo mayorista directo.</p>
                            <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                        </div>
                    </div>
                </div>
                <div class="text-xl font-bold text-emerald-600 mt-2" x-text="formatCurrency(futurePurchaseOrders.reduce((acc, o) => acc + (o.totalCost || 0), 0))"></div>
                <p class="text-xs text-slate-500 mt-1">Calculado a costo unitario neto</p>
            </div>

            <!-- KPI 4: Lead Time Promedio -->
            <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Lead Time Promedio</span>
                    <div x-data="{ tip: false }" class="relative inline-flex items-center">
                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg>
                        </span>
                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full right-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                            <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Tiempo de Reabastecimiento</div>
                            <p class="text-[10px] text-slate-200 leading-snug">Días promedio desde la confirmación de la orden hasta la recepción e ingreso físico a rack en almacén.</p>
                            <span class="absolute bottom-full right-3 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                        </div>
                    </div>
                </div>
                <div class="text-xl font-bold text-slate-900 mt-2">4.2 días</div>
                <p class="text-xs text-emerald-600 mt-1 font-medium">Logística rápida optimizada</p>
            </div>
        </div>

        <!-- Sub-navegación interna de Compras: Reabastecimiento vs Solicitudes On-Demand -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 bg-white p-2.5 rounded-xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center gap-2">
                <button 
                    type="button"
                    @click="purchasesSubTab = 'reorder'"
                    :class="purchasesSubTab === 'reorder' 
                        ? 'bg-indigo-600 text-white font-bold shadow-xs' 
                        : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                    class="px-3.5 py-2 rounded-lg text-xs transition-all flex items-center gap-2 cursor-pointer"
                    style="min-height: unset;"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Órdenes & Reabastecimiento Mayorista</span>
                    <span :class="purchasesSubTab === 'reorder' ? 'bg-indigo-700 text-white' : 'bg-slate-200 text-slate-700'" class="px-1.5 py-0.5 rounded-full text-[10px] font-bold" x-text="futurePurchaseOrders.length + 2"></span>
                </button>
                <button 
                    type="button"
                    @click="purchasesSubTab = 'on_demand'"
                    :class="purchasesSubTab === 'on_demand' 
                        ? 'bg-amber-600 text-white font-bold shadow-xs' 
                        : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                    class="px-3.5 py-2 rounded-lg text-xs transition-all flex items-center gap-2 cursor-pointer"
                    style="min-height: unset;"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span>Solicitudes On-Demand (Carritos de Clientes)</span>
                    <span :class="purchasesSubTab === 'on_demand' ? 'bg-amber-700 text-white' : 'bg-amber-100 text-amber-800'" class="px-1.5 py-0.5 rounded-full text-[10px] font-bold" x-text="customerOnDemandRequests.length"></span>
                </button>
            </div>
            <div class="text-xs text-slate-500 font-medium px-2">
                <template x-if="purchasesSubTab === 'reorder'">
                    <span>Órdenes a mayoristas y lotes acumulados</span>
                </template>
                <template x-if="purchasesSubTab === 'on_demand'">
                    <span>Backorders y líneas proveedor de carritos mixtos</span>
                </template>
            </div>
        </div>

        <!-- SUB-VISTA 1: ÓRDENES Y REABASTECIMIENTO MAYORISTA -->
        <div x-show="purchasesSubTab === 'reorder'" class="space-y-6">

        <!-- [P2.1] BANNER DE CARRITO MIXTO LIBERADO TRAS RECEPCIÓN DE MERCANCÍA -->
        <template x-if="mixedCartLiberated.show">
            <div class="p-4 bg-emerald-50 border border-emerald-300 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm animate-pulse">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-emerald-100 text-emerald-700 rounded-lg shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <div>
                        <h4 class="text-xs font-bold text-emerald-950 flex items-center gap-2">
                            <span>¡Carrito Mixto Liberado para Empaque Total!</span>
                            <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-200 text-emerald-900 font-mono font-bold" x-text="mixedCartLiberated.orderId"></span>
                        </h4>
                        <p class="text-[11px] text-emerald-800 mt-0.5">
                            Cliente: <strong x-text="mixedCartLiberated.customer"></strong> &bull; La prenda <strong x-text="mixedCartLiberated.item"></strong> arribó e ingresó a rack. El pedido ya cuenta con todas sus piezas completas.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button 
                        type="button" 
                        @click="showToast('Notificación enviada a la mesa de empaque en Logística (Hub 6)', 'success')" 
                        class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs cursor-pointer flex items-center gap-1.5"
                    >
                        <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>Enviar a Empaque</span>
                    </button>
                    <button 
                        type="button" 
                        @click="mixedCartLiberated.show = false" 
                        class="p-1.5 text-emerald-600 hover:text-emerald-800 rounded-lg hover:bg-emerald-100 transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </template>

        <!-- Tabla 1: Órdenes de Compra y Dropshipping Pendientes (Reactiva con Recepción Inbound) -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm mb-8">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-slate-900">Órdenes de Compra y Dropshipping Pendientes</h3>
                        <!-- Help Icon -->
                        <div x-data="{ tip: false }" class="relative inline-flex items-center">
                            <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg>
                            </span>
                            <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-64 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Seguimiento de Reabastecimiento</div>
                                <p class="text-[11px] text-slate-200 leading-snug">Monitorea órdenes enviadas a mayoristas para resurtir prendas en bodega o para envíos directos, con seguimiento de guía de transporte y recepción física.</p>
                                <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">Reabastecimiento y envíos directos solicitados a proveedor</p>
                </div>
                <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100">
                    <span x-text="activePurchaseOrders.filter(o => o.status !== 'Recibida en Almacén').length"></span> Órdenes Activas
                </span>
            </div>

            <div class="overflow-x-auto rounded-b-xl">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[11px] uppercase tracking-wider font-semibold">
                            <th class="py-3 px-4">ID Orden</th>
                            <th class="py-3 px-4">Proveedor & Guía</th>
                            <th class="py-3 px-4">Artículos Solicitados</th>
                            <th class="py-3 px-4 text-right">Total ($)</th>
                            <th class="py-3 px-4">Fecha & ETA</th>
                            <th class="py-3 px-4 text-center">Estado</th>
                            <th class="py-3 px-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        <template x-for="(order, index) in activePurchaseOrders" :key="order.id">
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-indigo-600" x-text="'#' + order.id"></td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-900 block" x-text="order.supplier"></span>
                                    <span class="text-slate-500 text-[11px] block font-mono" x-text="order.carrier"></span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-slate-900" x-text="order.qty + ' unidades'"></span>
                                        <template x-if="order.linkedCartId">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200" title="Contiene ítems de Carrito Mixto">
                                                Pedido Mixto
                                            </span>
                                        </template>
                                    </div>
                                    <span class="text-slate-600 text-xs block truncate max-w-xs" x-text="order.productName"></span>
                                    <div class="flex items-center gap-1 mt-0.5 text-[10px] text-slate-400">
                                        <span>Tallas:</span>
                                        <span class="font-mono text-slate-600" x-text="'CH:' + (order.sizes.ch||0) + ' M:' + (order.sizes.m||0) + ' G:' + (order.sizes.g||0) + ' XL:' + (order.sizes.xl||0) + (order.sizes['2xl'] ? ' 2XL:' + order.sizes['2xl'] : '')"></span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-slate-900" x-text="'$' + order.totalCost.toLocaleString('es-MX', {minimumFractionDigits: 2})"></td>
                                <td class="py-3.5 px-4">
                                    <span class="text-slate-700 font-medium block" x-text="order.date"></span>
                                    <span class="text-[11px] text-indigo-600 font-semibold block" x-text="'ETA: ' + order.eta"></span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <template x-if="order.status === 'En Camino'">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                            <span>En Camino</span>
                                        </span>
                                    </template>
                                    <template x-if="order.status === 'Recibida en Almacén'">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Recibida en Almacén</span>
                                        </span>
                                    </template>
                                    <template x-if="order.status === 'Pendiente Confirmación'">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span>⏳ Pendiente</span>
                                        </span>
                                    </template>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <template x-if="order.status === 'En Camino'">
                                        <button 
                                            type="button"
                                            @click="openReceiveOrderModal(order)"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow-xs transition cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                            <span>Recibir Mercancía</span>
                                        </button>
                                    </template>
                                    <template x-if="order.status === 'Recibida en Almacén'">
                                        <button 
                                            type="button"
                                            @click="openKardexForRef(order.id)"
                                            class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 hover:underline flex items-center justify-end gap-1 cursor-pointer"
                                        >
                                            <span>Ver en Kardex &rarr;</span>
                                        </button>
                                    </template>
                                    <template x-if="order.status === 'Pendiente Confirmación'">
                                        <button 
                                            type="button"
                                            @click="openOrderExportModal(order.supplier)"
                                            class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:underline cursor-pointer"
                                        >
                                            Enviar WhatsApp
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabla 3: Lista Reactiva de Futuras Compras (Reabastecimiento Planificado desde el Drawer) -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm">
            <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 bg-indigo-50 text-indigo-600 rounded-lg border border-indigo-200 inline-flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </span>
                        <h3 class="text-base font-bold text-slate-900">Lista de Futuras Compras & Reabastecimiento Planificado</h3>
                        <!-- Help Icon -->
                        <div x-data="{ tip: false }" class="relative inline-flex items-center">
                            <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg>
                            </span>
                            <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-64 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Consolidación de Compras</div>
                                <p class="text-[11px] text-slate-200 leading-snug">Artículos agregados individualmente desde el Drawer de producto para generar una orden consolidada por fabricante.</p>
                                <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">Lotes de reposición acumulados desde el panel lateral de producto</p>
                </div>
                <div class="flex items-center gap-2">
                    <button 
                        type="button"
                        @click="exportPurchaseOrdersToExcel()" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Exportar Orden Consolidada</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto rounded-b-xl">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[11px] uppercase tracking-wider font-semibold">
                            <th class="py-3 px-4">Código Lote</th>
                            <th class="py-3 px-4">Producto & SKU</th>
                            <th class="py-3 px-4">Proveedor Asignado</th>
                            <th class="py-3 px-4 text-center">Unidades</th>
                            <th class="py-3 px-4 text-right">Inversión Estimada</th>
                            <th class="py-3 px-4 text-center">Prioridad</th>
                            <th class="py-3 px-4 text-center">Estado</th>
                            <th class="py-3 px-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        <template x-for="(order, index) in futurePurchaseOrders" :key="order.id">
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-indigo-600" x-text="order.id"></td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-900 block" x-text="order.productName"></span>
                                    <span class="text-slate-400 font-mono text-[10px]" x-text="'SKU: ' + order.sku"></span>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800" x-text="order.supplier"></td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded font-bold text-xs bg-slate-100 text-slate-800" x-text="order.qty + ' uds'"></span>
                                    <template x-if="order.sizes">
                                        <div class="flex items-center justify-center gap-1 mt-1 text-[10px]">
                                            <span class="px-1 bg-slate-50 border border-slate-200 rounded text-slate-600 font-semibold" x-text="'CH:' + (order.sizes.ch || 0)"></span>
                                            <span class="px-1 bg-indigo-50 border border-indigo-200 rounded text-indigo-700 font-bold" x-text="'M:' + (order.sizes.m || 0)"></span>
                                            <span class="px-1 bg-indigo-50 border border-indigo-200 rounded text-indigo-700 font-bold" x-text="'G:' + (order.sizes.g || 0)"></span>
                                            <span class="px-1 bg-slate-50 border border-slate-200 rounded text-slate-600 font-semibold" x-text="'XL:' + (order.sizes.xl || 0)"></span>
                                            <template x-if="order.sizes['2xl'] > 0">
                                                <span class="px-1 bg-amber-50 border border-amber-200 rounded text-amber-800 font-bold" x-text="'2XL:' + order.sizes['2xl']"></span>
                                            </template>
                                            <template x-if="order.sizes['3xl'] > 0">
                                                <span class="px-1 bg-amber-50 border border-amber-200 rounded text-amber-800 font-bold" x-text="'3XL:' + order.sizes['3xl']"></span>
                                            </template>
                                        </div>
                                    </template>
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-slate-900" x-text="formatCurrency(order.totalCost)"></td>
                                <td class="py-3.5 px-4 text-center">
                                    <span 
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider"
                                        :class="order.priority === 'alta' ? 'bg-rose-50 text-rose-700 border border-rose-200' : (order.priority === 'media' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200')"
                                        x-text="order.priority"
                                    ></span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 text-xs text-indigo-700 font-semibold bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                        <span x-text="order.status"></span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <button 
                                        @click="futurePurchaseOrders.splice(index, 1); showToast('Ítem removido de la lista de futuras compras.', 'info')" 
                                        class="text-xs text-rose-500 hover:text-rose-700 hover:underline cursor-pointer"
                                    >
                                        Quitar
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        </div> <!-- Fin de SUB-VISTA 1 (purchasesSubTab === 'reorder') -->

        <!-- SUB-VISTA 2: BANDEJA DE SOLICITUDES ON-DEMAND DE CLIENTES -->
        <div x-show="purchasesSubTab === 'on_demand'" class="space-y-6" style="display: none;">
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm">
                <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 bg-amber-50 text-amber-600 rounded-lg border border-amber-200 inline-flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <h3 class="text-base font-bold text-slate-900">Bandeja de Solicitudes On-Demand (Carritos de Clientes)</h3>
                            <!-- Help Icon -->
                            <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-amber-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                                <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-64 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                    <div class="font-bold text-amber-300 text-[11px] mb-0.5">Atención Rápida On-Demand</div>
                                    <p class="text-[10px] text-slate-200 leading-snug">Prendas añadidas en 'Carritos para Solicitud de Pedido' o líneas externas de 'Carritos Mixtos'. Valida existencias con el proveedor antes de emitir el enlace de pago.</p>
                                    <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Atención y verificación de existencias remotas con temporizador SLA</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200 font-bold flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span>SLA Objetivo: &lt; 2 horas</span>
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-b-xl">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[11px] uppercase tracking-wider font-semibold">
                                <th class="py-3 px-4">Folio Solicitud</th>
                                <th class="py-3 px-4">Cliente & Contacto</th>
                                <th class="py-3 px-4">Origen del Carrito</th>
                                <th class="py-3 px-4">Prenda & Talla</th>
                                <th class="py-3 px-4">Proveedor / Torzo</th>
                                <th class="py-3 px-4 text-center">Temporizador SLA</th>
                                <th class="py-3 px-4 text-center">Estado Verificación</th>
                                <th class="py-3 px-4 text-right">Acciones Rápidas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            <template x-for="(req, index) in customerOnDemandRequests" :key="req.id">
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-amber-700" x-text="req.id"></td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900" x-text="req.customerName"></div>
                                        <div class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.699c.983.536 1.777.818 2.796.818 3.18 0 5.767-2.587 5.767-5.766.001-3.182-2.585-5.766-5.767-5.766zm3.385 8.169c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
                                            <span x-text="req.customerPhone"></span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span 
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold"
                                            :class="req.cartType === 'solicitud_pedido' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-indigo-50 text-indigo-800 border border-indigo-200'"
                                            x-text="req.cartLabel"
                                        ></span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900" x-text="req.productName"></div>
                                        <div class="flex items-center gap-1.5 mt-0.5 text-xs text-slate-500">
                                            <span class="font-mono text-[10px]" x-text="req.sku"></span>
                                            <span>&bull;</span>
                                            <span class="font-bold text-indigo-700 bg-indigo-50 px-1.5 rounded" x-text="'Talla ' + req.size"></span>
                                            <span>&bull;</span>
                                            <span class="font-semibold text-slate-700" x-text="req.qty + ' pza' + (req.qty > 1 ? 's' : '')"></span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-800" x-text="req.supplier"></td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700">
                                            <span>⏱</span>
                                            <span x-text="req.slaMinutes + ' min'"></span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <template x-if="req.verificationStatus === 'confirmado'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                <span>Stock Confirmado</span>
                                            </span>
                                        </template>
                                        <template x-if="req.verificationStatus === 'pendiente'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                <span>Por Verificar</span>
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Acción 1: Verificar con Proveedor -->
                                            <button 
                                                type="button" 
                                                @click="verifyRequestWithSupplier(req)" 
                                                class="px-2 py-1 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded text-[11px] font-bold transition cursor-pointer"
                                                style="min-height:unset;"
                                                title="Verificar disponibilidad en vivo con proveedor/Torzo"
                                            >
                                                Verificar
                                            </button>
                                            <!-- Acción 2: WhatsApp Link de Pago -->
                                            <button 
                                                type="button" 
                                                @click="sendPaymentLinkToCustomer(req)" 
                                                class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-bold transition cursor-pointer flex items-center gap-1"
                                                style="min-height:unset;"
                                                title="Enviar link de pago confirmado por WhatsApp"
                                            >
                                                <span>WhatsApp</span>
                                            </button>
                                            <!-- Acción 3: Pasar a Orden de Compra -->
                                            <button 
                                                type="button" 
                                                @click="convertRequestToPurchaseOrder(req)" 
                                                class="px-2 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-[11px] font-bold transition cursor-pointer"
                                                style="min-height:unset;"
                                                title="Sumar a la orden de compra del proveedor"
                                            >
                                                + A Compra
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div> <!-- Fin de SUB-VISTA 2 (purchasesSubTab === 'on_demand') -->
    </div>

    <!-- ================================================================= -->
    <!-- ================================================================= -->
    <!-- DRAWER LATERAL EXPANDIDO: TABLA COMPLETA TOP 10 PRODUCTOS         -->
    <!-- ================================================================= -->
    <div 
        x-show="topTenDrawerOpen" 
        @keydown.escape.window="closeTopTenDrawer()"
        class="fixed inset-0 z-50 overflow-hidden" 
        style="display: none;"
        aria-labelledby="top-ten-drawer-title" 
        role="dialog" 
        aria-modal="true"
    >
        <!-- Backdrop oscuro: Cierra al hacer clic fuera del panel -->
        <div 
            x-show="topTenDrawerOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="closeTopTenDrawer()"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity cursor-pointer"
        ></div>

        <!-- Contenedor flex con pointer-events-none para que el clic exterior traspase limpiamente -->
        <div 
            @click.self="closeTopTenDrawer()"
            class="fixed inset-y-0 right-0 w-screen max-w-full flex pointer-events-none"
        >
            <!-- Panel del Drawer: con pointer-events-auto y @click.outside para cierre garantizado -->
            <div 
                x-show="topTenDrawerOpen"
                @click.outside="if (!drawerOpen && !newProductDrawerOpen) closeTopTenDrawer()"
                x-transition:enter="transform transition ease-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="w-screen max-w-[96vw] lg:max-w-[calc(100vw-80px)] xl:max-w-[calc(100vw-100px)] bg-white shadow-2xl border-l border-slate-200 flex flex-col justify-between pointer-events-auto"
            >
                <!-- Header del Drawer Top 10 con Help ? -->
                <div class="p-6 border-b border-slate-200 bg-slate-50/90">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="p-2.5 bg-amber-50 text-amber-600 rounded-xl border border-amber-200/80 inline-flex items-center justify-center shadow-2xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-xl font-bold text-slate-900 leading-tight">Top 10 Productos con Mayor Rendimiento</h2>
                                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-amber-100 text-amber-800 border border-amber-200">Best Sellers RevOps</span>
                                    <!-- Help ? Icon del Drawer Header -->
                                    <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-72 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                            <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Matriz de Rendimiento Top 10</div>
                                            <p class="text-[11px] text-slate-200 leading-snug">Monitorea los 10 productos que concentran el 64.2% del flujo de caja. Permite supervisar la curva de tallas más demandadas, rentabilidad neta y nivel de existencias físicas o externas para evitar quiebres.</p>
                                            <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Data grid completo: facturación consolidada, curva de tallas más demandadas y stock disponible</p>
                            </div>
                        </div>
                        <button 
                            @click="closeTopTenDrawer()" 
                            class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200 transition cursor-pointer"
                            aria-label="Cerrar panel"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Métricas destacadas en cabecera del drawer con Tooltips ? -->
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-4 pt-4 border-t border-slate-200/80">
                        <!-- KPI 1: Facturación Top 10 -->
                        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 font-semibold block text-[11px] uppercase tracking-wider">Facturación Top 10</span>
                                <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                    <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg>
                                    </span>
                                    <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-64 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                        <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Facturación Concentrada Top 10</div>
                                        <p class="text-[10px] text-slate-200 leading-snug">Monto acumulado por los 10 productos estrella en los últimos 30 días. Representa el 64.2% del volumen total de ingresos del catálogo.</p>
                                        <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-xl font-extrabold text-slate-900 mt-1 block">$1,104,800 <span class="text-xs text-slate-400 font-normal">MXN</span></span>
                            <div class="text-[10px] text-emerald-600 font-medium mt-0.5">64.2% del volumen total de ventas</div>
                        </div>

                        <!-- KPI 2: Unidades Vendidas -->
                        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 font-semibold block text-[11px] uppercase tracking-wider">Unidades Vendidas</span>
                                <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                    <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg>
                                    </span>
                                    <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-64 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                        <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Prendas Despachadas Top 10</div>
                                        <p class="text-[10px] text-slate-200 leading-snug">Total de piezas vendidas entre los 10 modelos líderes, promediando 289 unidades por SKU con alta tasa de rotación en inventario.</p>
                                        <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-xl font-extrabold text-indigo-600 mt-1 block">2,890 <span class="text-xs text-slate-400 font-normal">piezas</span></span>
                            <div class="text-[10px] text-slate-500 mt-0.5">Promedio 289 uds por SKU</div>
                        </div>

                        <!-- KPI 3: Talla Central Más Vendida -->
                        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 font-semibold block text-[11px] uppercase tracking-wider">Talla Central Más Vendida</span>
                                <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                    <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg>
                                    </span>
                                    <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-64 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                        <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Curva Dominante de Tallas</div>
                                        <p class="text-[10px] text-slate-200 leading-snug">La talla M (Mediana) concentra el 44.5% de los pedidos totales. Priorizar su aprovisionamiento evita quiebres en el segmento core de clientes.</p>
                                        <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-xl font-extrabold text-indigo-900 mt-1 block">M &bull; Mediana</span>
                            <div class="text-[10px] text-indigo-600 font-medium mt-0.5">Concentra el 44.5% de compras</div>
                        </div>

                        <!-- KPI 4: Margen Bruto Promedio -->
                        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 font-semibold block text-[11px] uppercase tracking-wider">Margen Bruto Promedio</span>
                                <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                    <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg>
                                    </span>
                                    <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full right-0 mt-2 w-64 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                        <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Rentabilidad Comercial Ponderada</div>
                                        <p class="text-[10px] text-slate-200 leading-snug">Margen comercial ponderado combinando confección propia (65%-73%) y dropshipping (57%-63%). Se ubica en un rango óptimo y saludable (>50%).</p>
                                        <span class="absolute bottom-full right-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-xl font-extrabold text-emerald-600 mt-1 block">66.0% <span class="text-xs text-slate-400 font-normal">RevOps</span></span>
                            <div class="text-[10px] text-emerald-700 font-medium mt-0.5">Rango saludable (&gt;50%)</div>
                        </div>
                    </div>
                </div>

                <!-- Contenido con Scroll: TABLA COMPLETA CON TOOLTIPS ? EN ENCABEZADOS -->
                <div class="p-6 overflow-y-auto flex-1 text-sm bg-slate-50/30">
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <table class="w-full text-left border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-500 text-[11px] uppercase tracking-wider font-semibold">
                                    <th class="py-3.5 px-3 w-12 text-center">#</th>
                                    <th class="py-3.5 px-4 min-w-[200px]">Producto & SKU</th>
                                    <th class="py-3.5 px-3 text-center">Categoría</th>
                                    
                                    <!-- ENCABEZADO CON HELP ? : TALLAS MÁS VENDIDAS -->
                                    <th class="py-3.5 px-3 text-center">
                                        <div class="inline-flex items-center justify-center gap-1">
                                            <span>Tallas Más Vendidas</span>
                                            <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                                <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                                                <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-64 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                                    <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Curva de Demanda por Talla</div>
                                                    <p class="text-[10px] text-slate-200 leading-snug">Identifica las 2 tallas con mayor penetración (Top 1 y Top 2). En boxers y jocks dominan M y G; en tangas y bikinis dominan CH y M. Vital para no quebrar tallas centrales.</p>
                                                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </th>

                                    <th class="py-3.5 px-3 text-center">Origen</th>
                                    
                                    <!-- ENCABEZADO CON HELP ? : UDS VENDIDAS -->
                                    <th class="py-3.5 px-4 text-right">
                                        <div class="inline-flex items-center justify-end gap-1">
                                            <span>Uds Vendidas</span>
                                            <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                                <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                                                <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full right-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                                    <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Volumen Despachado</div>
                                                    <p class="text-[10px] text-slate-200 leading-snug">Prendas vendidas en los últimos 30 días. La micro-barra indica la proporción respecto al producto líder (#1).</p>
                                                    <span class="absolute bottom-full right-3 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </th>

                                    <!-- ENCABEZADO CON HELP ? : FACTURACIÓN (GMV) -->
                                    <th class="py-3.5 px-4 text-right">
                                        <div class="inline-flex items-center justify-end gap-1">
                                            <span>Facturación (GMV)</span>
                                            <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                                <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                                                <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full right-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                                    <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Gross Merchandise Value</div>
                                                    <p class="text-[10px] text-slate-200 leading-snug">Total bruto generado por la venta de este SKU (Precio PVP x Unidades vendidas en 30 días).</p>
                                                    <span class="absolute bottom-full right-3 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </th>

                                    <!-- ENCABEZADO CON HELP ? : MARGEN REVOPS -->
                                    <th class="py-3.5 px-3 text-center">
                                        <div class="inline-flex items-center justify-center gap-1">
                                            <span>Margen RevOps</span>
                                            <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                                <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                                                <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-60 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                                    <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Margen Bruto Unitario</div>
                                                    <p class="text-[10px] text-slate-200 leading-snug">Rentabilidad sobre el precio final: (PVP - Costo) / PVP. Todos los productos del Top 10 superan el 50% de margen saludable.</p>
                                                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </th>

                                    <!-- ENCABEZADO CON HELP ? : STOCK DISPONIBLE -->
                                    <th class="py-3.5 px-4 text-right">
                                        <div class="inline-flex items-center justify-end gap-1">
                                            <span>Stock Disponible</span>
                                            <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                                <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                                                <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full right-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                                    <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Nivel de Existencias</div>
                                                    <p class="text-[10px] text-slate-200 leading-snug">Piezas físicas en almacén o confirmadas en catálogo del proveedor. Permite detectar quiebres inminentes en productos estrella.</p>
                                                    <span class="absolute bottom-full right-3 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </th>

                                    <th class="py-3.5 px-3 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                
                                <!-- ITEM 1 (ORO) -->
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-800 border border-amber-300 font-extrabold text-xs inline-flex items-center justify-center shadow-xs">1</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900 text-sm">Boxer Trunks Modal Seamless</div>
                                        <div class="text-[11px] font-mono text-indigo-600 font-medium">SKU: BX-MOD-001</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/80">Boxers</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-extrabold text-xs bg-indigo-100 text-indigo-800 border border-indigo-200 shadow-2xs" title="Talla Top 1: 45% de pedidos">
                                                <span>M</span> <span class="text-[10px] text-indigo-600 font-normal">45%</span>
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md font-semibold text-xs bg-slate-100 text-slate-700 border border-slate-200" title="Talla Top 2: 35% de pedidos">
                                                <span>G</span> <span class="text-[10px] text-slate-500 font-normal">35%</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span> Stock Propio
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="font-bold text-slate-900">520 uds</span>
                                            <div class="w-14 h-1.5 bg-slate-100 rounded-full overflow-hidden hidden sm:block">
                                                <div class="h-full bg-indigo-600 rounded-full" style="width: 100%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="font-bold text-slate-900">$218,400</div>
                                        <div class="text-[10px] text-slate-400">PVP: $420 MXN</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            65.5% <span class="text-[9px] font-normal text-emerald-600 hidden xl:inline">&bull; Saludable</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                                        284 <span class="text-xs font-normal text-slate-500">piezas</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <button @click="openDrawerForSku('BX-MOD-001')" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Detalle</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- ITEM 2 (PLATA) -->
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 border border-slate-300 font-extrabold text-xs inline-flex items-center justify-center shadow-xs">2</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900 text-sm">Boxer Algodón Pima Orgánico</div>
                                        <div class="text-[11px] font-mono text-indigo-600 font-medium">SKU: BX-COT-007</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/80">Boxers</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-extrabold text-xs bg-indigo-100 text-indigo-800 border border-indigo-200 shadow-2xs" title="Talla Top 1: 42% de pedidos">
                                                <span>M</span> <span class="text-[10px] text-indigo-600 font-normal">42%</span>
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md font-semibold text-xs bg-slate-100 text-slate-700 border border-slate-200" title="Talla Top 2: 38% de pedidos">
                                                <span>G</span> <span class="text-[10px] text-slate-500 font-normal">38%</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span> Stock Propio
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="font-bold text-slate-900">460 uds</span>
                                            <div class="w-14 h-1.5 bg-slate-100 rounded-full overflow-hidden hidden sm:block">
                                                <div class="h-full bg-indigo-600 rounded-full" style="width: 88%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="font-bold text-slate-900">$179,400</div>
                                        <div class="text-[10px] text-slate-400">PVP: $390 MXN</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            69.2% <span class="text-[9px] font-normal text-emerald-600 hidden xl:inline">&bull; Saludable</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                                        480 <span class="text-xs font-normal text-slate-500">piezas</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <button @click="openDrawerForSku('BX-COT-007')" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Detalle</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- ITEM 3 (BRONCE) -->
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-800 border border-orange-300 font-extrabold text-xs inline-flex items-center justify-center shadow-xs">3</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900 text-sm">Jockstrap Athletic Carbon Edition</div>
                                        <div class="text-[11px] font-mono text-indigo-600 font-medium">SKU: JK-ATH-002</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">Jocks</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-extrabold text-xs bg-blue-100 text-blue-800 border border-blue-200 shadow-2xs" title="Talla Top 1: 50% de pedidos">
                                                <span>M</span> <span class="text-[10px] text-blue-600 font-normal">50%</span>
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md font-semibold text-xs bg-slate-100 text-slate-700 border border-slate-200" title="Talla Top 2: 30% de pedidos">
                                                <span>G</span> <span class="text-[10px] text-slate-500 font-normal">30%</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span> Stock Propio
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="font-bold text-slate-900">395 uds</span>
                                            <div class="w-14 h-1.5 bg-slate-100 rounded-full overflow-hidden hidden sm:block">
                                                <div class="h-full bg-blue-600 rounded-full" style="width: 76%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="font-bold text-slate-900">$150,100</div>
                                        <div class="text-[10px] text-slate-400">PVP: $380 MXN</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            69.7% <span class="text-[9px] font-normal text-emerald-600 hidden xl:inline">&bull; Saludable</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                                        198 <span class="text-xs font-normal text-slate-500">piezas</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <button @click="openDrawerForSku('JK-ATH-002')" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Detalle</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- ITEM 4 -->
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold text-xs inline-flex items-center justify-center">4</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900 text-sm">Torzo Mesh Breathable Trunk</div>
                                        <div class="text-[11px] font-mono text-indigo-600 font-medium">SKU: TOR-DRP-003</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/80">Boxers</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-extrabold text-xs bg-indigo-100 text-indigo-800 border border-indigo-200 shadow-2xs" title="Talla Top 1: 40% de pedidos">
                                                <span>M</span> <span class="text-[10px] text-indigo-600 font-normal">40%</span>
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md font-semibold text-xs bg-slate-100 text-slate-700 border border-slate-200" title="Talla Top 2: 35% de pedidos">
                                                <span>G</span> <span class="text-[10px] text-slate-500 font-normal">35%</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Dropship (Torzo)
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="font-bold text-slate-900">310 uds</span>
                                            <div class="w-14 h-1.5 bg-slate-100 rounded-full overflow-hidden hidden sm:block">
                                                <div class="h-full bg-indigo-600 rounded-full" style="width: 60%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="font-bold text-slate-900">$151,900</div>
                                        <div class="text-[10px] text-slate-400">PVP: $490 MXN</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            57.1% <span class="text-[9px] font-normal text-emerald-600 hidden xl:inline">&bull; Saludable</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <span class="font-bold text-emerald-700">48 <span class="text-xs font-normal text-emerald-600">sincronizadas</span></span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <button @click="openDrawerForSku('TOR-DRP-003')" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Detalle</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- ITEM 5 -->
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold text-xs inline-flex items-center justify-center">5</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900 text-sm">Tanga Microfibra Velvet Touch</div>
                                        <div class="text-[11px] font-mono text-indigo-600 font-medium">SKU: TG-SLK-004</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-fuchsia-50 text-fuchsia-700 border border-fuchsia-200/80">Tangas</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-extrabold text-xs bg-fuchsia-100 text-fuchsia-800 border border-fuchsia-200 shadow-2xs" title="Talla Top 1: 45% de pedidos">
                                                <span>CH</span> <span class="text-[10px] text-fuchsia-600 font-normal">45%</span>
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md font-semibold text-xs bg-slate-100 text-slate-700 border border-slate-200" title="Talla Top 2: 40% de pedidos">
                                                <span>M</span> <span class="text-[10px] text-slate-500 font-normal">40%</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span> Stock Propio
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="font-bold text-slate-900">285 uds</span>
                                            <div class="w-14 h-1.5 bg-slate-100 rounded-full overflow-hidden hidden sm:block">
                                                <div class="h-full bg-fuchsia-600 rounded-full" style="width: 55%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="font-bold text-slate-900">$82,650</div>
                                        <div class="text-[10px] text-slate-400">PVP: $290 MXN</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            73.1% <span class="text-[9px] font-normal text-emerald-600 hidden xl:inline">&bull; Saludable</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                                        154 <span class="text-xs font-normal text-slate-500">piezas</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <button @click="openDrawerForSku('TG-SLK-004')" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Detalle</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- ITEM 6 -->
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold text-xs inline-flex items-center justify-center">6</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900 text-sm">Jockstrap Pro-Gym Wide Band</div>
                                        <div class="text-[11px] font-mono text-indigo-600 font-medium">SKU: JK-PRO-010</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">Jocks</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-extrabold text-xs bg-blue-100 text-blue-800 border border-blue-200 shadow-2xs" title="Talla Top 1: 44% de pedidos">
                                                <span>G</span> <span class="text-[10px] text-blue-600 font-normal">44%</span>
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md font-semibold text-xs bg-slate-100 text-slate-700 border border-slate-200" title="Talla Top 2: 38% de pedidos">
                                                <span>M</span> <span class="text-[10px] text-slate-500 font-normal">38%</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span> Stock Propio
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="font-bold text-slate-900">240 uds</span>
                                            <div class="w-14 h-1.5 bg-slate-100 rounded-full overflow-hidden hidden sm:block">
                                                <div class="h-full bg-blue-600 rounded-full" style="width: 46%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="font-bold text-slate-900">$86,400</div>
                                        <div class="text-[10px] text-slate-400">PVP: $360 MXN</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            69.4% <span class="text-[9px] font-normal text-emerald-600 hidden xl:inline">&bull; Saludable</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                                        88 <span class="text-xs font-normal text-slate-500">piezas</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <button @click="openDrawerForSku('JK-PRO-010')" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Detalle</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- ITEM 7 -->
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold text-xs inline-flex items-center justify-center">7</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900 text-sm">Tanga Extreme Minimalist Straps</div>
                                        <div class="text-[11px] font-mono text-indigo-600 font-medium">SKU: TG-EXT-008</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-fuchsia-50 text-fuchsia-700 border border-fuchsia-200/80">Tangas</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-extrabold text-xs bg-fuchsia-100 text-fuchsia-800 border border-fuchsia-200 shadow-2xs" title="Talla Top 1: 48% de pedidos">
                                                <span>CH</span> <span class="text-[10px] text-fuchsia-600 font-normal">48%</span>
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md font-semibold text-xs bg-slate-100 text-slate-700 border border-slate-200" title="Talla Top 2: 38% de pedidos">
                                                <span>M</span> <span class="text-[10px] text-slate-500 font-normal">38%</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span> Stock Propio
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="font-bold text-slate-900">215 uds</span>
                                            <div class="w-14 h-1.5 bg-slate-100 rounded-full overflow-hidden hidden sm:block">
                                                <div class="h-full bg-fuchsia-600 rounded-full" style="width: 41%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="font-bold text-slate-900">$55,900</div>
                                        <div class="text-[10px] text-slate-400">PVP: $260 MXN</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            76.2% <span class="text-[9px] font-normal text-emerald-600 hidden xl:inline">&bull; Saludable</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                                        218 <span class="text-xs font-normal text-slate-500">piezas</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <button @click="openDrawerForSku('TG-EXT-008')" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Detalle</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- ITEM 8 -->
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold text-xs inline-flex items-center justify-center">8</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900 text-sm">Bikini Sport Performance Brief</div>
                                        <div class="text-[11px] font-mono text-indigo-600 font-medium">SKU: BK-FIT-005</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/80">Bikinis</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-extrabold text-xs bg-amber-100 text-amber-800 border border-amber-200 shadow-2xs" title="Talla Top 1: 46% de pedidos">
                                                <span>M</span> <span class="text-[10px] text-amber-600 font-normal">46%</span>
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md font-semibold text-xs bg-slate-100 text-slate-700 border border-slate-200" title="Talla Top 2: 32% de pedidos">
                                                <span>CH</span> <span class="text-[10px] text-slate-500 font-normal">32%</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span> Stock Propio
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="font-bold text-slate-900">190 uds</span>
                                            <div class="w-14 h-1.5 bg-slate-100 rounded-full overflow-hidden hidden sm:block">
                                                <div class="h-full bg-amber-600 rounded-full" style="width: 37%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="font-bold text-slate-900">$60,800</div>
                                        <div class="text-[10px] text-slate-400">PVP: $320 MXN</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            70.3% <span class="text-[9px] font-normal text-emerald-600 hidden xl:inline">&bull; Saludable</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                                        122 <span class="text-xs font-normal text-slate-500">piezas</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <button @click="openDrawerForSku('BK-FIT-005')" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Detalle</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- ITEM 9 -->
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold text-xs inline-flex items-center justify-center">9</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900 text-sm">Torzo Neon Glow Athletic Jock</div>
                                        <div class="text-[11px] font-mono text-indigo-600 font-medium">SKU: TOR-DRP-006</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">Jocks</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-extrabold text-xs bg-blue-100 text-blue-800 border border-blue-200 shadow-2xs" title="Talla Top 1: 48% de pedidos">
                                                <span>M</span> <span class="text-[10px] text-blue-600 font-normal">48%</span>
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md font-semibold text-xs bg-slate-100 text-slate-700 border border-slate-200" title="Talla Top 2: 34% de pedidos">
                                                <span>G</span> <span class="text-[10px] text-slate-500 font-normal">34%</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Dropship (Torzo)
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="font-bold text-slate-900">165 uds</span>
                                            <div class="w-14 h-1.5 bg-slate-100 rounded-full overflow-hidden hidden sm:block">
                                                <div class="h-full bg-blue-600 rounded-full" style="width: 32%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="font-bold text-slate-900">$74,250</div>
                                        <div class="text-[10px] text-slate-400">PVP: $450 MXN</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            56.7% <span class="text-[9px] font-normal text-emerald-600 hidden xl:inline">&bull; Saludable</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            14 piezas
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <button @click="openDrawerForSku('TOR-DRP-006')" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Detalle</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- ITEM 10 -->
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold text-xs inline-flex items-center justify-center">10</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900 text-sm">Torzo Signature Satin Bikini</div>
                                        <div class="text-[11px] font-mono text-indigo-600 font-medium">SKU: TOR-DRP-009</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/80">Bikinis</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-extrabold text-xs bg-amber-100 text-amber-800 border border-amber-200 shadow-2xs" title="Talla Top 1: 42% de pedidos">
                                                <span>CH</span> <span class="text-[10px] text-amber-600 font-normal">42%</span>
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md font-semibold text-xs bg-slate-100 text-slate-700 border border-slate-200" title="Talla Top 2: 40% de pedidos">
                                                <span>M</span> <span class="text-[10px] text-slate-500 font-normal">40%</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Dropship (Torzo)
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="font-bold text-slate-900">110 uds</span>
                                            <div class="w-14 h-1.5 bg-slate-100 rounded-full overflow-hidden hidden sm:block">
                                                <div class="h-full bg-amber-600 rounded-full" style="width: 21%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="font-bold text-slate-900">$45,100</div>
                                        <div class="text-[10px] text-slate-400">PVP: $410 MXN</div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            57.3% <span class="text-[9px] font-normal text-emerald-600 hidden xl:inline">&bull; Saludable</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Agotado
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <button @click="openDrawerForSku('TOR-DRP-009')" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Detalle</span>
                                        </button>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer del Drawer -->
                <div class="p-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2 text-slate-500">
                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Estrategia RevOps: Las tallas centrales <strong>M</strong> y <strong>G</strong> dominan en boxers y jocks, mientras que <strong>CH</strong> lidera en tangas y bikinis. Concentrar compras según esta curva.</span>
                    </div>
                    <button 
                        @click="closeTopTenDrawer()" 
                        class="px-5 py-2 text-sm font-semibold bg-slate-800 hover:bg-slate-900 text-white rounded-lg shadow-sm transition cursor-pointer shrink-0"
                    >
                        Cerrar Panel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- DRAWER LATERAL (Off-canvas animado con Alpine.js)                 -->
    <!-- ================================================================= -->
    <div 
        x-show="drawerOpen" 
        class="fixed inset-0 z-[60] overflow-hidden" 
        style="z-index: 60; display: none;"
        aria-labelledby="slide-over-title" 
        role="dialog" 
        aria-modal="true"
    >
        <!-- Backdrop oscuro con desenfoque suave -->
        <div 
            x-show="drawerOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="drawerOpen = false"
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"
        ></div>

        <div class="fixed inset-y-0 right-0 w-screen max-w-full flex pointer-events-none">
            <div 
                x-show="drawerOpen"
                x-transition:enter="transform transition ease-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="w-screen max-w-4xl bg-white shadow-2xl border-l border-slate-200 flex flex-col justify-between"
            >
                <!-- Header del Drawer -->
                <div class="p-6 border-b border-slate-200 bg-slate-50/70">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 text-xs font-mono font-bold rounded bg-indigo-100 text-indigo-700" x-text="selectedProduct?.sku"></span>
                            <span class="text-xs px-2 py-0.5 rounded font-semibold uppercase tracking-wider"
                                  :class="selectedProduct?.type === 'propio' ? 'bg-indigo-50 text-indigo-700' : 'bg-emerald-50 text-emerald-700'"
                                  x-text="selectedProduct?.type === 'propio' ? 'Stock Propio' : 'Dropshipping'">
                            </span>
                        </div>
                        <button @click="drawerOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="flex items-center justify-between mt-3">
                        <h2 class="text-xl font-bold text-slate-900" x-text="selectedProduct?.name"></h2>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-200 text-slate-700" x-text="drawerBrand"></span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="selectedProduct?.category"></p>
                </div>

                <!-- Contenido con Scroll del Drawer -->
                <div class="p-6 overflow-y-auto flex-1 text-sm">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- ========================================================= -->
                        <!-- COLUMNA IZQUIERDA: VITRINA WEB, ATRIBUTOS TEXTILES & CURA -->
                        <!-- ========================================================= -->
                        <div class="space-y-5">
                            <!-- 1. Toggle de Publicación en Tienda Online -->
                            <div class="p-4 rounded-xl border transition-colors shadow-2xs" :class="drawerIsPublished ? 'bg-emerald-50/80 border-emerald-300' : 'bg-slate-50 border-slate-200'">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-2.5 h-2.5 rounded-full" :class="drawerIsPublished ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                            <span class="font-bold text-xs uppercase tracking-wider" :class="drawerIsPublished ? 'text-emerald-900' : 'text-slate-700'">Estado en Vitrina Web</span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1" x-text="drawerIsPublished ? 'Prenda publicada y visible para clientes en la tienda online.' : 'Prenda fuera de catálogo (oculta temporalmente del público).'"></p>
                                    </div>
                                    <button 
                                        type="button" 
                                        @click="drawerIsPublished = !drawerIsPublished" 
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                        :class="drawerIsPublished ? 'bg-emerald-600' : 'bg-slate-300'"
                                        title="Alternar publicación"
                                    >
                                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                              :class="drawerIsPublished ? 'translate-x-5' : 'translate-x-0'"></span>
                                    </button>
                                </div>
                            </div>

                            <!-- 2. Marca o Proveedor & Sección del Sitio Web -->
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/90 space-y-3 shadow-2xs">
                                <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700">Catalogación & Proveedor</h4>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-600 block mb-1">Marca / Proveedor</label>
                                        <input 
                                            type="text" 
                                            x-model="drawerBrand" 
                                            class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                            placeholder="Ej: Torzo B2B, ModaIntima MX"
                                        >
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-600 block mb-1">Sección en Tienda Web</label>
                                        <select 
                                            x-model="drawerWebsiteSection" 
                                            class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                        >
                                            <option value="basicos">Línea Básicos Esenciales</option>
                                            <option value="disenador">Línea Diseñador & Autor</option>
                                            <option value="temporada">Colección Temporada / Drops</option>
                                            <option value="general">Catálogo General</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Variante de Diseño: Con diseño vs Sin diseño -->
                                <div>
                                    <label class="text-[11px] font-semibold text-slate-600 block mb-1">Variante de Diseño</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button 
                                            type="button" 
                                            @click="drawerDesignType = 'sin_diseno'"
                                            class="p-2 rounded-lg text-xs font-bold border text-center transition cursor-pointer"
                                            :class="drawerDesignType === 'sin_diseno' ? 'bg-white border-indigo-600 text-indigo-700 shadow-xs' : 'bg-slate-100 text-slate-600 border-slate-200'"
                                        >
                                            Sin Diseño (Liso / Sólido)
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="drawerDesignType = 'con_diseno'"
                                            class="p-2 rounded-lg text-xs font-bold border text-center transition cursor-pointer"
                                            :class="drawerDesignType === 'con_diseno' ? 'bg-white border-purple-600 text-purple-700 shadow-xs' : 'bg-slate-100 text-slate-600 border-slate-200'"
                                        >
                                            Con Diseño (Estampado / Pattern)
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Tipo de Tela & Fibras Textiles -->
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/90 space-y-2 shadow-2xs">
                                <div class="flex items-center justify-between">
                                    <label class="font-bold text-xs uppercase tracking-wider text-slate-700">Tipo de Tela & Fibras</label>
                                    <button 
                                        type="button" 
                                        @click="activeSettingsTab = 'fabrics'; moduleSettingsModalOpen = true;"
                                        class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 hover:underline cursor-pointer"
                                    >
                                        + Nueva Tela
                                    </button>
                                </div>
                                <select 
                                    x-model="drawerFabricType" 
                                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                >
                                    <template x-for="fab in apparelFabrics" :key="fab.id">
                                        <option :value="fab.name" x-text="fab.name + ' (' + fab.description + ')'"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- 4. Color & Tornasol Iridiscente -->
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/90 space-y-3 shadow-2xs">
                                <div class="flex items-center justify-between">
                                    <label class="font-bold text-xs uppercase tracking-wider text-slate-700">Color & Tornasol</label>
                                    <button 
                                        type="button" 
                                        @click="activeSettingsTab = 'colors'; moduleSettingsModalOpen = true;"
                                        class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 hover:underline cursor-pointer"
                                    >
                                        + Nuevo Color/Tornasol
                                    </button>
                                </div>
                                
                                <div class="flex items-center gap-3 p-2 bg-white rounded-lg border border-slate-200">
                                    <span class="w-8 h-8 rounded-lg border border-slate-300 shadow-xs shrink-0" :style="'background: ' + drawerColorHex"></span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-bold text-slate-900 truncate" x-text="drawerColorName"></div>
                                        <div class="text-[10px] text-slate-400 font-mono" x-text="drawerColorType === 'iridescent' ? 'Tornasol Holográfico' : 'Color Sólido'"></div>
                                    </div>
                                </div>

                                <!-- Selector Rápido de Paleta -->
                                <div class="grid grid-cols-4 sm:grid-cols-6 gap-1.5 pt-1">
                                    <template x-for="c in apparelColors" :key="c.id">
                                        <button 
                                            type="button" 
                                            @click="drawerColorName = c.name; drawerColorHex = c.hex; drawerColorType = c.type;"
                                            class="p-1 rounded-lg border flex flex-col items-center gap-1 transition cursor-pointer hover:scale-105"
                                            :class="drawerColorName === c.name ? 'border-indigo-600 ring-2 ring-indigo-500/30 bg-white' : 'border-slate-200 bg-slate-100'"
                                            :title="c.name"
                                        >
                                            <span class="w-5 h-5 rounded-md border border-slate-300 shadow-3xs" :style="'background: ' + c.hex"></span>
                                            <span class="text-[9px] font-semibold text-slate-700 truncate w-full text-center" x-text="c.name.split(' ')[0]"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- 5. Descripción del Producto -->
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/90 space-y-1.5 shadow-2xs">
                                <div class="flex items-center justify-between">
                                    <label class="font-bold text-xs uppercase tracking-wider text-slate-700">Descripción de la Prenda</label>
                                    <span class="text-[10px] text-slate-400">Para vitrina web y curaduría</span>
                                </div>
                                <textarea 
                                    x-model="drawerDescription" 
                                    rows="4" 
                                    placeholder="Describe la composición, tacto, silueta y detalles de diseño de la prenda..." 
                                    class="w-full p-2.5 bg-white border border-slate-200 rounded-lg text-xs text-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                ></textarea>
                            </div>
                        </div>
                        <!-- ========================================================= -->
                        <!-- COLUMNA DERECHA: EXISTENCIAS, SLOTTING, MARGEN & REORDEN   -->
                        <!-- ========================================================= -->
                        <div class="space-y-6">
                            <!-- Tarjeta Resumen 3 Estados: Físico vs Comprometido vs Disponible -->
                    <div class="bg-gradient-to-r from-slate-50 to-indigo-50/40 p-3.5 rounded-xl border border-slate-200/90 shadow-2xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span>Disponibilidad Real (RevOps)</span>
                            </span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">En Venta Activa</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="bg-white p-2 rounded-lg border border-slate-200 shadow-3xs">
                                <span class="text-[10px] text-slate-500 block uppercase font-medium">Físico en Rack</span>
                                <span class="text-base font-extrabold text-slate-900" x-text="selectedProduct ? selectedProduct.localStock : 0"></span>
                            </div>
                            <div class="bg-white p-2 rounded-lg border border-slate-200 shadow-3xs">
                                <span class="text-[10px] text-slate-500 block uppercase font-medium">Comprometido</span>
                                <span class="text-base font-extrabold text-amber-600 flex items-center justify-center gap-0.5">
                                    <svg class="w-3 h-3 inline-block text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    <span x-text="selectedProduct ? (selectedProduct.committedStock || 0) : 0"></span>
                                </span>
                            </div>
                            <div class="bg-white p-2 rounded-lg border border-emerald-200 bg-emerald-50/30 shadow-3xs">
                                <span class="text-[10px] text-emerald-700 block uppercase font-bold">Disponible Venta</span>
                                <span class="text-base font-extrabold text-emerald-600" x-text="selectedProduct ? getAvailableStock(selectedProduct) : 0"></span>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-2 text-center">
                            Apartados en empaque de carritos directos o esperando arribo de proveedor en carritos mixtos.
                        </p>
                        <div class="mt-2.5 pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
                            <span class="text-slate-500 text-[11px]">Trazabilidad de existencias:</span>
                            <button 
                                type="button" 
                                @click="openKardexForSku(selectedProduct.sku)" 
                                class="inline-flex items-center gap-1 font-bold text-indigo-600 hover:text-indigo-800 hover:underline cursor-pointer text-[11px]"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span>Ver Kardex de este SKU &rarr;</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- 1. Desglose de Stock por Talla o Tono Cosmético (TASK-INV-09) -->
                    <template x-if="selectedProduct && selectedProduct.variantDimension === 'shade'">
                        <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <label class="font-bold text-slate-900 text-xs uppercase tracking-wider">Existencias por Tono Cosmético</label>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-pink-100 text-pink-800 border border-pink-200">Swatches & Subtonos</span>
                                </div>
                                <span class="text-xs text-slate-500">Total: <strong class="text-pink-700 font-bold" x-text="totalProductStockCount()"></strong> piezas</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                <template x-for="shade in activeCosmeticShades" :key="shade.code">
                                    <div class="bg-white p-2.5 rounded-lg border border-slate-200 text-center flex flex-col items-center shadow-2xs hover:border-pink-300 transition">
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <span class="cosmetic-swatch-20" :style="'background: ' + shade.hex_color"></span>
                                            <span class="text-xs font-bold text-slate-800" x-text="'Tono ' + shade.code"></span>
                                        </div>
                                        <span class="text-[10px] text-slate-500 truncate w-full mb-1" x-text="shade.name"></span>
                                        <span class="text-[9px] mb-1.5" :class="getUndertoneBadgeClass(shade.undertone)" x-text="getUndertoneLabel(shade.undertone)"></span>
                                        <input 
                                            type="number" 
                                            min="0" 
                                            x-model.number="drawerShades[shade.code]" 
                                            class="w-full text-center bg-slate-50 border border-slate-200 rounded py-1 px-1 font-bold text-slate-800 text-xs focus:bg-white focus:ring-1 focus:ring-pink-500 focus:outline-none"
                                        >
                                    </div>
                                </template>
                            </div>
                            <div class="flex items-center justify-between mt-2.5 pt-2 border-t border-slate-200/80">
                                <span class="text-[10px] text-slate-400">Tonos configurables dinámicamente con código HEX y subtono.</span>
                                <button type="button" @click="openCosmeticShadesModal()" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 cursor-pointer">+ Gestionar Tonos</button>
                            </div>
                        </div>
                    </template>

                    <template x-if="!selectedProduct || selectedProduct.variantDimension !== 'shade'">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-1.5">
                                    <label class="font-bold text-slate-900 text-xs uppercase tracking-wider">Inventario por Talla</label>
                                    <!-- Help Icon -->
                                    <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                            <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Curva de Tallas</div>
                                            <p class="text-[10px] text-slate-200 leading-snug">Desglose de existencias por talla para evitar que quiebres en tallas centrales (M, G) bloqueen la conversión del modelo.</p>
                                            <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-xs text-slate-500">Total calculado: <strong class="text-indigo-600" x-text="totalSizesCount()"></strong> piezas</span>
                            </div>

                            <div class="grid grid-cols-4 gap-2.5">
                                <!-- Talla CH -->
                                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-center">
                                    <span class="text-xs font-bold text-slate-600 block mb-1">CH (S)</span>
                                    <input 
                                        type="number" 
                                        min="0" 
                                        x-model.number="drawerSizes.ch" 
                                        class="w-full text-center bg-white border border-slate-200 rounded py-1 px-1 font-bold text-slate-800 text-sm focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                    >
                                </div>

                                <!-- Talla M -->
                                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-center">
                                    <span class="text-xs font-bold text-slate-600 block mb-1">M</span>
                                    <input 
                                        type="number" 
                                        min="0" 
                                        x-model.number="drawerSizes.m" 
                                        class="w-full text-center bg-white border border-slate-200 rounded py-1 px-1 font-bold text-slate-800 text-sm focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                    >
                                </div>

                                <!-- Talla G -->
                                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-center">
                                    <span class="text-xs font-bold text-slate-600 block mb-1">G (L)</span>
                                    <input 
                                        type="number" 
                                        min="0" 
                                        x-model.number="drawerSizes.g" 
                                        class="w-full text-center bg-white border border-slate-200 rounded py-1 px-1 font-bold text-slate-800 text-sm focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                    >
                                </div>

                                <!-- Talla XL -->
                                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-center">
                                    <span class="text-xs font-bold text-slate-600 block mb-1">XL</span>
                                    <input 
                                        type="number" 
                                        min="0" 
                                        x-model.number="drawerSizes.xl" 
                                        class="w-full text-center bg-white border border-slate-200 rounded py-1 px-1 font-bold text-slate-800 text-sm focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                    >
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 2. Ubicación en Bodega -->
                    <div>
                        <div class="flex items-center gap-1.5 mb-2">
                            <label class="font-bold text-slate-900 text-xs uppercase tracking-wider">Ubicación Física en Almacén</label>
                            <!-- Help Icon -->
                            <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                                <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                    <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Dirección de Rack</div>
                                    <p class="text-[10px] text-slate-200 leading-snug">Identificador exacto de pasillo, rack y gaveta para guiar al equipo de picking y empaque de pedidos.</p>
                                    <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                </div>
                            </div>
                        </div>
                        <input 
                            type="text" 
                            x-model="drawerLocation" 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                        >
                    </div>

                    <!-- Control Sanitario & Trazabilidad (TASK-INV-06) -->
                    <template x-if="selectedProduct && selectedProduct.requiresSanitaryControl">
                        <div class="bg-gradient-to-r from-purple-50/60 to-indigo-50/50 p-4 rounded-xl border border-purple-200/90 shadow-2xs space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <span class="p-1 bg-purple-100 text-purple-700 rounded border border-purple-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </span>
                                    <label class="font-bold text-slate-900 text-xs uppercase tracking-wider">Control Sanitario & Trazabilidad</label>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border" :class="getSanitaryBadgeClass(selectedProduct)">
                                    <span x-text="getSanitaryBadgeText(selectedProduct)"></span>
                                </span>
                            </div>

                            <div class="grid grid-cols-3 gap-2.5">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Lote Activo</label>
                                    <input 
                                        type="text" 
                                        x-model="drawerBatchNumber" 
                                        class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-mono font-bold text-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    >
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Fecha Caducidad</label>
                                    <input 
                                        type="date" 
                                        x-model="drawerExpirationDate" 
                                        class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    >
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">PAO</label>
                                    <select 
                                        x-model.number="drawerPaoMonths" 
                                        class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    >
                                        <option value="6">6 Meses</option>
                                        <option value="12">12 Meses</option>
                                        <option value="18">18 Meses</option>
                                        <option value="24">24 Meses</option>
                                        <option value="36">36 Meses</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Control Sanitario & Trazabilidad (TASK-INV-06) -->
                    <template x-if="selectedProduct && selectedProduct.requiresSanitaryControl">
                        <div class="bg-gradient-to-r from-purple-50/60 to-indigo-50/50 p-4 rounded-xl border border-purple-200/90 shadow-2xs space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <span class="p-1 bg-purple-100 text-purple-700 rounded border border-purple-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </span>
                                    <label class="font-bold text-slate-900 text-xs uppercase tracking-wider">Control Sanitario & Trazabilidad</label>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border" :class="getSanitaryBadgeClass(selectedProduct)">
                                    <span x-text="getSanitaryBadgeText(selectedProduct)"></span>
                                </span>
                            </div>

                            <div class="grid grid-cols-3 gap-2.5">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Lote Activo</label>
                                    <input 
                                        type="text" 
                                        x-model="drawerBatchNumber" 
                                        class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-mono font-bold text-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    >
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Fecha Caducidad</label>
                                    <input 
                                        type="date" 
                                        x-model="drawerExpirationDate" 
                                        class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    >
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">PAO</label>
                                    <select 
                                        x-model.number="drawerPaoMonths" 
                                        class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    >
                                        <option value="6">6 Meses</option>
                                        <option value="12">12 Meses</option>
                                        <option value="18">18 Meses</option>
                                        <option value="24">24 Meses</option>
                                        <option value="36">36 Meses</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 3. Calculadora Reactiva de Margen (PVP - Costo = Margen) -->
                    <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white p-5 rounded-2xl shadow-lg border border-slate-800">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 bg-indigo-500/20 text-indigo-300 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </span>
                                <div class="flex items-center gap-1.5">
                                    <h4 class="font-bold text-sm text-white">Calculadora Reactiva de Margen</h4>
                                    <!-- Help Icon -->
                                    <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                        <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-indigo-300 hover:text-white transition-colors cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg></span>
                                        <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-64 p-2.5 bg-slate-950 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700" style="display: none;">
                                            <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Calculadora RevOps de Rentabilidad</div>
                                            <p class="text-[10px] text-slate-200 leading-snug">Calcula la ganancia bruta y el porcentaje de margen sobre PVP en tiempo real: Saludable (>50%), Aceptable (30-50%) o Riesgo (<30%).</p>
                                            <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-950"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">RevOps Unit</span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <!-- Input PVP Regular -->
                            <div>
                                <label class="text-[11px] font-semibold text-slate-300 block mb-1">PVP Regular (Lista)</label>
                                <div class="relative">
                                    <span class="absolute left-2.5 top-2 text-xs text-slate-400">$</span>
                                    <input 
                                        type="number" 
                                        step="0.5" 
                                        x-model.number="drawerPvp" 
                                        class="w-full bg-slate-800/80 border border-slate-700 rounded-lg py-1.5 pl-6 pr-2 text-sm font-bold text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                    >
                                </div>
                            </div>

                            <!-- Input Descuento (%) -->
                            <div>
                                <label class="text-[11px] font-semibold text-rose-300 block mb-1">Descuento Activo (%)</label>
                                <div class="relative">
                                    <input 
                                        type="number" 
                                        step="1" 
                                        min="0" 
                                        max="90" 
                                        x-model.number="drawerDiscountPct" 
                                        class="w-full bg-slate-800/80 border border-rose-500/40 rounded-lg py-1.5 px-2.5 text-sm font-bold text-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-400"
                                        placeholder="0%"
                                    >
                                    <span class="absolute right-2.5 top-2 text-xs text-rose-400 font-bold">%</span>
                                </div>
                            </div>

                            <!-- Input Último Costo -->
                            <div>
                                <label class="text-[11px] font-semibold text-slate-300 block mb-1">Último Costo Compra</label>
                                <div class="relative">
                                    <span class="absolute left-2.5 top-2 text-xs text-slate-400">$</span>
                                    <input 
                                        type="number" 
                                        step="0.5" 
                                        x-model.number="drawerCost" 
                                        class="w-full bg-slate-800/80 border border-slate-700 rounded-lg py-1.5 pl-6 pr-2 text-sm font-bold text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                    >
                                </div>
                            </div>

                            <!-- Input CPP Contable -->
                            <div>
                                <label class="text-[11px] font-semibold text-indigo-300 block mb-1">Costo Promedio (CPP)</label>
                                <div class="relative">
                                    <span class="absolute left-2.5 top-2 text-xs text-indigo-400">$</span>
                                    <input 
                                        type="number" 
                                        step="0.1" 
                                        x-model.number="drawerCpp" 
                                        class="w-full bg-slate-800/80 border border-indigo-700/80 rounded-lg py-1.5 pl-6 pr-2 text-sm font-bold text-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Resultados en Tiempo Real con Comparativa Contable -->
                        <div class="pt-3 border-t border-slate-800/80 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <span class="text-[11px] text-slate-400 block">Utilidad Real s/ CPP:</span>
                                <div class="text-base font-extrabold text-emerald-400 font-mono" x-text="formatCurrency(drawerPvp - drawerCpp)"></div>
                                <span class="text-[10px] text-slate-500 block font-mono">Marginal: <span x-text="formatCurrency(calculateProfit())"></span></span>
                            </div>

                            <div>
                                <span class="text-[11px] text-indigo-300 block font-semibold">Margen Real (CPP):</span>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-base font-extrabold font-mono" 
                                          :class="calculateRealMarginPercent() >= 50 ? 'text-emerald-400' : (calculateRealMarginPercent() >= 30 ? 'text-amber-400' : 'text-rose-400')" 
                                          x-text="calculateRealMarginPercent() + '%'">
                                    </span>
                                    <span class="text-[9px] px-1 py-0.5 rounded font-bold uppercase tracking-wider"
                                          :class="calculateRealMarginPercent() >= 50 ? 'bg-emerald-500/20 text-emerald-300' : (calculateRealMarginPercent() >= 30 ? 'bg-amber-500/20 text-amber-300' : 'bg-rose-500/20 text-rose-300')"
                                          x-text="calculateRealMarginPercent() >= 50 ? 'Saludable' : (calculateRealMarginPercent() >= 30 ? 'Aceptable' : 'Riesgo')">
                                    </span>
                                </div>
                            </div>

                            <div>
                                <span class="text-[11px] text-slate-400 block">Margen Marginal (Costo):</span>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-base font-bold text-slate-300 font-mono" x-text="calculateMarginPercent() + '%'"></span>
                                    <span class="text-[10px] text-slate-400 font-mono" x-text="'Dif: ' + (getMarginDifference() > 0 ? '+' : '') + getMarginDifference() + '%'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. PLAN DE REABASTECIMIENTO & FUTURAS COMPRAS CON PROVEEDOR -->
                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200/90 shadow-xs">
                        <div class="flex items-center justify-between mb-3 pb-2.5 border-b border-slate-200">
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 bg-indigo-50 text-indigo-700 rounded-lg border border-indigo-200/80 inline-flex items-center justify-center shadow-2xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                </span>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <h4 class="font-bold text-sm text-slate-900">Plan de Reabastecimiento con Proveedor</h4>
                                        <!-- Help Icon -->
                                        <div x-data="{ tip: false }" class="relative inline-flex items-center">
                                            <span @mouseenter="tip = true" @mouseleave="tip = false" @click.stop="tip = !tip" class="help-icon text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m0 3.5h.01"/></svg>
                                            </span>
                                            <div x-show="tip" @click.away="tip = false" x-transition class="absolute top-full left-0 mt-2 w-64 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-2xl z-50 pointer-events-none normal-case font-normal text-left border border-slate-700/80" style="display: none;">
                                                <div class="font-bold text-indigo-300 text-[11px] mb-0.5">Órdenes Planificadas RevOps</div>
                                                <p class="text-[10px] text-slate-200 leading-snug">Permite programar lotes de reabastecimiento antes de quebrar stock, acumulando volumen para alcanzar MOQ (pedido mínimo) o mejores tarifas mayoristas.</p>
                                                <span class="absolute bottom-full left-4 -mb-1 border-4 border-transparent border-b-slate-900"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-slate-500">Añadir a lista de compras para consolidar orden con fabricante</p>
                                </div>
                            </div>
                            
                            <!-- Badge indicador si el SKU ya está en la lista -->
                            <template x-if="selectedProduct && isProductInPurchaseList(selectedProduct.sku)">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                    <span x-text="getProductPurchaseCount(selectedProduct.sku) + ' uds en cola'"></span>
                                </span>
                            </template>
                        </div>

                        <div class="space-y-3.5">
                            <!-- Selector de Proveedor -->
                            <div>
                                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1">Proveedor Asignado / Fabricante</label>
                                <select 
                                    x-model="purchaseSupplier" 
                                    class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                                >
                                    <option value="Torzo B2B">Torzo B2B &bull; Headless Scraper (CDMX)</option>
                                    <option value="ModaIntima Textil MX">ModaIntima Textil MX &bull; Fabricante Nacional SAP (Puebla)</option>
                                    <option value="Silk & Cotton Iberia SL">Silk & Cotton Iberia SL &bull; Algodón/Modal Orgánico (Barcelona)</option>
                                    <option value="Lingerie Drop Global">Lingerie Drop Global &bull; Dropshipping Jocks/Bikinis (Miami)</option>
                                    <option value="Seamless Tech Apparel">Seamless Tech Apparel &bull; Sin Costuras (Medellín)</option>
                                    <option value="Aura Underwear Wholesale">Aura Underwear Wholesale &bull; Diseño Autor (Guadalajara)</option>
                                    <option value="Intimates Co. Logistics">Intimates Co. Logistics &bull; Distribuidor Multimarca (Monterrey)</option>
                                </select>
                            </div>

                            <!-- Matriz Reactiva de Tallas & Presets Rápidos -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">Matriz de Reposición por Talla</label>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="setPurchaseQty(30)" class="px-2 py-0.5 text-[10px] font-bold rounded bg-white hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 border border-slate-200 transition cursor-pointer" style="min-height:unset;padding:2px 8px;">+30</button>
                                        <button type="button" @click="setPurchaseQty(60)" class="px-2 py-0.5 text-[10px] font-bold rounded bg-white hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 border border-slate-200 transition cursor-pointer" style="min-height:unset;padding:2px 8px;">+60</button>
                                        <button type="button" @click="setPurchaseQty(120)" class="px-2 py-0.5 text-[10px] font-bold rounded bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 transition cursor-pointer font-extrabold" style="min-height:unset;padding:2px 8px;">+120 (MOQ)</button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-4 gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                    <div class="text-center">
                                        <span class="text-[10px] font-bold text-slate-500 block mb-1">CH</span>
                                        <input 
                                            type="number" 
                                            min="0" 
                                            x-model.number="purchaseSizes.ch" 
                                            @input="updatePurchaseTotalFromSizes()"
                                            class="w-full bg-white border border-slate-200 rounded-lg py-1 px-1 text-center text-xs font-bold text-slate-900 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                        >
                                    </div>
                                    <div class="text-center">
                                        <span class="text-[10px] font-bold text-indigo-700 inline-flex items-center justify-center gap-0.5 mb-1">M <svg class="w-2.5 h-2.5 text-amber-500 fill-amber-400" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>
                                        <input 
                                            type="number" 
                                            min="0" 
                                            x-model.number="purchaseSizes.m" 
                                            @input="updatePurchaseTotalFromSizes()"
                                            class="w-full bg-white border border-indigo-200 rounded-lg py-1 px-1 text-center text-xs font-extrabold text-indigo-700 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                        >
                                    </div>
                                    <div class="text-center">
                                        <span class="text-[10px] font-bold text-indigo-700 inline-flex items-center justify-center gap-0.5 mb-1">G <svg class="w-2.5 h-2.5 text-amber-500 fill-amber-400" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>
                                        <input 
                                            type="number" 
                                            min="0" 
                                            x-model.number="purchaseSizes.g" 
                                            @input="updatePurchaseTotalFromSizes()"
                                            class="w-full bg-white border border-indigo-200 rounded-lg py-1 px-1 text-center text-xs font-extrabold text-indigo-700 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                        >
                                    </div>
                                    <div class="text-center">
                                        <span class="text-[10px] font-bold text-slate-500 block mb-1">XL</span>
                                        <input 
                                            type="number" 
                                            min="0" 
                                            x-model.number="purchaseSizes.xl" 
                                            @input="updatePurchaseTotalFromSizes()"
                                            class="w-full bg-white border border-slate-200 rounded-lg py-1 px-1 text-center text-xs font-bold text-slate-900 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                        >
                                    </div>
                                </div>

                                <!-- Fila Plus Size para Reorden: 2XL y 3XL -->
                                <div class="mt-2 p-2 bg-slate-50/70 rounded-lg border border-slate-200/80">
                                    <div class="flex items-center justify-between mb-1 px-0.5">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Tallas Extendidas (Plus Size)</span>
                                        <span class="text-[10px] text-slate-400">Poco volumen</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="bg-white p-1 rounded-md border border-slate-200 flex items-center justify-between gap-2">
                                            <span class="text-[10px] font-bold text-slate-600 pl-1">2XL</span>
                                            <input 
                                                type="number" 
                                                min="0" 
                                                x-model.number="purchaseSizes['2xl']" 
                                                @input="updatePurchaseTotalFromSizes()"
                                                class="w-16 bg-slate-50 border border-slate-200 rounded py-0.5 px-1 text-center text-xs font-bold text-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                            >
                                        </div>
                                        <div class="bg-white p-1 rounded-md border border-slate-200 flex items-center justify-between gap-2">
                                            <span class="text-[10px] font-bold text-slate-600 pl-1">3XL</span>
                                            <input 
                                                type="number" 
                                                min="0" 
                                                x-model.number="purchaseSizes['3xl']" 
                                                @input="updatePurchaseTotalFromSizes()"
                                                class="w-16 bg-slate-50 border border-slate-200 rounded py-0.5 px-1 text-center text-xs font-bold text-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-[11px] text-slate-500 mt-1.5 px-0.5">
                                    <span>Total calculado: <strong class="text-indigo-600 font-bold" x-text="purchaseQty"></strong> piezas</span>
                                    <span class="text-slate-400 text-[10px] inline-flex items-center gap-1"><svg class="w-2.5 h-2.5 text-amber-500 fill-amber-400" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>Tallas de mayor rotación</span>
                                </div>
                            </div>

                            <!-- Prioridad e Inversión Estimada -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1">Prioridad de Reposición</label>
                                    <select 
                                        x-model="purchasePriority" 
                                        class="w-full bg-white border border-slate-200 rounded-lg py-1.5 px-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                                    >
                                        <option value="alta">Alta (Riesgo &lt; 7 días)</option>
                                        <option value="media">Media (Curva habitual)</option>
                                        <option value="baja">Baja (Lote programado)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1">Inversión Estimada</label>
                                    <div class="bg-white border border-slate-200 rounded-lg py-1.5 px-2.5 text-xs font-extrabold text-emerald-700 flex items-center justify-between">
                                        <span x-text="formatCurrency(calculatePurchaseTotal())"></span>
                                        <span class="text-[10px] font-normal text-slate-400">PVP Costo</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón Acción: Añadir a Lista de Compras -->
                            <button 
                                type="button" 
                                @click="addToFuturePurchaseList()" 
                                class="w-full mt-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer group"
                                style="min-height:unset;"
                            >
                                <svg class="w-4 h-4 text-indigo-200 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                <span>Añadir a Lista de Futuras Compras</span>
                            </button>

                            <!-- Mensaje de confirmación en línea inmediato dentro del drawer -->
                            <div 
                                x-show="purchaseAddedSuccess" 
                                x-transition:enter="transition ease-out duration-200 transform"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150 transform"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-1"
                                class="mt-2.5 p-2.5 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-2xs"
                                style="display: none;"
                            >
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span class="flex-1 leading-tight">¡Añadido exitosamente a la lista de futuras compras!</span>
                                <span class="text-[10px] bg-emerald-200/80 text-emerald-900 px-1.5 py-0.5 rounded font-bold shrink-0">En Proveedores</span>
                            </div>
                        </div>
                    </div>
                        </div>
                    </div>
                </div>


                <!-- Footer del Drawer -->
                <div class="p-4 border-t border-slate-200 bg-slate-50 flex items-center justify-end gap-3">
                    <button 
                        @click="drawerOpen = false" 
                        class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 rounded-lg transition cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button"
                        @click="handleSaveDrawerClick()" 
                        class="px-5 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm transition cursor-pointer"
                        style="min-height: unset;"
                    >
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
<!-- ================================================================= -->
    <!-- WORKSTATION LATERAL ERGONÓMICA: ALTA DE PRODUCTO & CATÁLOGO       -->
    <!-- (TASK-INV-15: Auto-SKU, Detección de Duplicados & Copiloto IA)     -->
    <!-- ================================================================= -->
    <div 
        x-show="newProductDrawerOpen" 
        class="fixed inset-0 z-[70] overflow-hidden" 
        style="z-index: 70; display: none;"
        aria-labelledby="new-product-title" 
        role="dialog" 
        aria-modal="true"
        @keydown.escape.window="closeCreateProductDrawer()"
    >
        <!-- Backdrop oscuro con desenfoque suave -->
        <div 
            x-show="newProductDrawerOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="closeCreateProductDrawer()"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity cursor-pointer"
        ></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-4 sm:pl-10">
            <div 
                x-show="newProductDrawerOpen"
                x-transition:enter="transform transition ease-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="w-screen max-w-6xl xl:max-w-7xl 2xl:max-w-[1550px] bg-slate-50 shadow-2xl border-l border-slate-200 flex flex-col justify-between h-full"
            >
                <!-- Header de la Workstation -->
                <div class="p-5 border-b border-slate-200 bg-white shadow-2xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-200/80 inline-flex items-center justify-center shadow-2xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-lg font-bold text-slate-900" id="new-product-title">Workstation de Alta de Producto</h2>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 tracking-wide uppercase">RevOps Catalogo</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">Codificacion inteligente SKU, matriz multidimensional y generacion con Copiloto IA</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <button 
                                type="button"
                                @click="generateAutoSku()" 
                                :disabled="newProduct.isGeneratingSku"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-bold transition cursor-pointer"
                                title="Generar codigo SKU automatico estructurado"
                            >
                                <svg x-show="!newProduct.isGeneratingSku" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <svg x-show="newProduct.isGeneratingSku" class="animate-spin w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Auto-SKU</span>
                            </button>

                            <button 
                                type="button"
                                @click="closeCreateProductDrawer()" 
                                class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                                aria-label="Cerrar workstation"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Contenedor de 3 Paneles Ergonómicos con Scroll -->
                <div class="p-6 overflow-y-auto flex-1">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                        <!-- ========================================================= -->
                        <!-- PANEL 1: IDENTIDAD, SKU Y CONTROL DE DUPLICADOS (4 COLS)  -->
                        <!-- ========================================================= -->
                        <div class="lg:col-span-4 space-y-5">
                            <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-4">
                                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-md bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">1</div>
                                        <h3 class="font-bold text-slate-800 text-sm">Identidad & Codificacion</h3>
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Unicidad</span>
                                </div>

                                <!-- Código SKU Oficial con Auto-Generador y Candado -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">Codigo SKU Oficial</label>
                                        <button 
                                            type="button" 
                                            @click="toggleSkuLock()" 
                                            class="text-[10px] font-bold inline-flex items-center gap-1 transition cursor-pointer"
                                            :class="newProduct.skuLocked ? 'text-slate-500 hover:text-slate-700' : 'text-amber-600 hover:text-amber-700'"
                                        >
                                            <svg x-show="newProduct.skuLocked" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            <svg x-show="!newProduct.skuLocked" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                            </svg>
                                            <span x-text="newProduct.skuLocked ? 'Bloqueado' : 'Edicion Manual'"></span>
                                        </button>
                                    </div>
                                    <div class="relative flex items-center">
                                        <input 
                                            type="text" 
                                            x-model="newProduct.sku" 
                                            :disabled="newProduct.skuLocked"
                                            placeholder="TXT-TRZ-0001" 
                                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono font-bold text-indigo-700 uppercase focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 disabled:opacity-75 disabled:cursor-not-allowed"
                                        >
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">Formato: [PREFIJO]-[MARCA]-[CORRELATIVO 4D]-[VARIANTE]</p>
                                </div>

                                <!-- Nombre del Producto con Análisis Reactivo de Duplicados -->
                                <div>
                                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1">Nombre Comercial del Modelo</label>
                                    <input 
                                        type="text" 
                                        x-model="newProduct.name" 
                                        @input.debounce.350ms="checkProductDuplicate()"
                                        placeholder="Ej: Boxer Trunk Modal Eco Air" 
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                                    >

                                    <!-- Indicador de Alerta de Duplicados Reactivo -->
                                    <div class="mt-2" x-cloak>
                                        <!-- Analizando en progreso -->
                                        <div x-show="newProduct.isCheckingDuplicate" class="flex items-center gap-1.5 text-[11px] text-slate-500 py-1">
                                            <svg class="animate-spin w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            <span>Verificando unicidad en catalogo...</span>
                                        </div>

                                        <!-- Duplicado crítico detectado -->
                                        <div 
                                            x-show="!newProduct.isCheckingDuplicate && newProduct.duplicateRisk === 'duplicate_match'" 
                                            class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1.5"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-1.5 font-bold">
                                                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                    </svg>
                                                    <span>Posible Duplicado Detectado</span>
                                                </div>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-rose-200 text-rose-900" x-text="newProduct.duplicateScore + '% match'"></span>
                                            </div>
                                            <p class="text-[11px] text-rose-700 leading-snug">
                                                Ya existe un producto con caracteristicas y nombre altamente similares.
                                            </p>
                                            <button 
                                                type="button" 
                                                @click="openDuplicateModal()" 
                                                class="w-full text-center py-1 px-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-[11px] transition cursor-pointer"
                                            >
                                                Resolver Conflicto Lado a Lado
                                            </button>
                                        </div>

                                        <!-- Advertencia moderada -->
                                        <div 
                                            x-show="!newProduct.isCheckingDuplicate && newProduct.duplicateRisk === 'warning'" 
                                            class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs space-y-1"
                                        >
                                            <div class="flex items-center justify-between font-bold">
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/>
                                                    </svg>
                                                    <span>Similitud Moderada</span>
                                                </span>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-200 text-amber-900" x-text="newProduct.duplicateScore + '%'"></span>
                                            </div>
                                            <button 
                                                type="button" 
                                                @click="openDuplicateModal()" 
                                                class="text-[10px] font-bold text-amber-800 hover:underline block"
                                            >
                                                Revisar coincidencias en catalogo
                                            </button>
                                        </div>

                                        <!-- Verificado limpio -->
                                        <div 
                                            x-show="!newProduct.isCheckingDuplicate && newProduct.duplicateChecked && newProduct.duplicateRisk === 'none' && newProduct.name.length >= 3" 
                                            class="flex items-center gap-1 text-[11px] font-semibold text-emerald-700 py-1"
                                        >
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Nombre verificado: sin duplicados en catalogo.</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Marca / Fabricante -->
                                <div>
                                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1">Marca / Proveedor</label>
                                    <input 
                                        type="text" 
                                        x-model="newProduct.brand" 
                                        @input.debounce.350ms="checkProductDuplicate()"
                                        placeholder="ModaIntima Textil MX / Torzo B2B" 
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                                    >
                                </div>

                                <!-- Categoría y Subcategoría -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1">Categoria</label>
                                        <select 
                                            x-model="newProduct.category" 
                                            @change="onNewProductCategoryChange()"
                                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                                        >
                                            <option value="Boxers">Boxers</option>
                                            <option value="Jocks">Jocks</option>
                                            <option value="Tangas">Tangas</option>
                                            <option value="Bikinis">Bikinis</option>
                                            <option value="Camisillas">Camisillas</option>
                                            <option value="Cosmetica / Skincare">Cosmetica / Skincare</option>
                                            <option value="Perfumeria / Fragancias">Perfumeria / Fragancias</option>
                                            <option value="Accesorios & Cuidado">Accesorios & Cuidado</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1">Subcategoria</label>
                                        <input 
                                            type="text" 
                                            x-model="newProduct.subcategory" 
                                            placeholder="Ej: Trunks / Briefs" 
                                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                                        >
                                    </div>
                                </div>

                                <!-- Modalidad Logística -->
                                <div>
                                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1.5">Modalidad Logistica</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <label 
                                            class="flex items-center gap-2 p-2.5 rounded-xl border text-xs font-bold cursor-pointer transition"
                                            :class="newProduct.type === 'propio' ? 'border-indigo-500 bg-indigo-50 text-indigo-900 shadow-2xs' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                                        >
                                            <input type="radio" value="propio" x-model="newProduct.type" class="sr-only">
                                            <span class="w-2 h-2 rounded-full" :class="newProduct.type === 'propio' ? 'bg-indigo-600' : 'bg-slate-300'"></span>
                                            <span>Propio (Bodega)</span>
                                        </label>
                                        <label 
                                            class="flex items-center gap-2 p-2.5 rounded-xl border text-xs font-bold cursor-pointer transition"
                                            :class="newProduct.type === 'dropship' ? 'border-emerald-500 bg-emerald-50 text-emerald-900 shadow-2xs' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                                        >
                                            <input type="radio" value="dropship" x-model="newProduct.type" class="sr-only">
                                            <span class="w-2 h-2 rounded-full" :class="newProduct.type === 'dropship' ? 'bg-emerald-600' : 'bg-slate-300'"></span>
                                            <span>Dropshipping</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Sección Vitrina y Publicación -->
                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                                    <div class="flex-1 mr-3">
                                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Seccion Vitrina</label>
                                        <select 
                                            x-model="newProduct.websiteSection"
                                            class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 focus:outline-none"
                                        >
                                            <option value="basicos">Coleccion Basicos</option>
                                            <option value="ofertas">Seccion Ofertas</option>
                                            <option value="temporada">Nueva Temporada</option>
                                            <option value="destacados">Vitrina Principal</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-2 pt-3">
                                        <input 
                                            type="checkbox" 
                                            id="new_prod_published" 
                                            x-model="newProduct.isPublished"
                                            class="rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                        >
                                        <label for="new_prod_published" class="text-xs font-bold text-slate-700 cursor-pointer">Publicar</label>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- ========================================================= -->
                        <!-- PANEL 2: MATRIZ DE TALLAS, CURVAS & ATRIBUTOS (4 COLS)    -->
                        <!-- ========================================================= -->
                        <div class="lg:col-span-4 space-y-5">
                            <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-4">
                                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-md bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">2</div>
                                        <h3 class="font-bold text-slate-800 text-sm">Matriz & Curva de Tallas</h3>
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Inventario</span>
                                </div>

                                <!-- Dimensión de Variante -->
                                <div>
                                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1.5">Dimension de Variante</label>
                                    <select 
                                        x-model="newProduct.variantDimension" 
                                        @change="onNewProductCategoryChange()"
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-800 focus:bg-white focus:outline-none"
                                    >
                                        <option value="size">Tallas Textiles (Curvas de Patronaje)</option>
                                        <option value="shade">Tonos / Shades (Cosmetica Facial & Corporal)</option>
                                        <option value="volume">Volumenes (Perfumeria & Fragancias)</option>
                                        <option value="none">Pieza Unica / Accesorio Fijo</option>
                                    </select>
                                </div>

                                <!-- Matriz Dinámica para Dimensión Tallas (Textil) -->
                                <template x-if="newProduct.variantDimension === 'size'">
                                    <div class="space-y-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                                        <!-- Selector de Curva de Tallas Configurable (TASK-INV-14) -->
                                        <div>
                                            <div class="flex items-center justify-between mb-1">
                                                <label class="text-[11px] font-bold text-indigo-900 uppercase tracking-wider">Curva de Tallas Operativa</label>
                                                <span class="text-[10px] text-slate-400">TASK-INV-14</span>
                                            </div>
                                            <select 
                                                x-model="newProduct.selectedSizeCurveId" 
                                                @change="onSizeCurveChange()"
                                                class="w-full px-3 py-1.5 bg-white border border-indigo-200 rounded-lg text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                            >
                                                <template x-for="curve in getAvailableSizeCurves()" :key="curve.id">
                                                    <option :value="curve.id" x-text="curve.name"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- Grid Reactivo de Tallas Dinámicas -->
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block mb-1.5">Stock Inicial por Talla (Piezas)</label>
                                            <div class="grid grid-cols-3 gap-2">
                                                <template x-for="sz in getCurrentNewProductSizes()" :key="sz.code">
                                                    <div class="bg-white p-2 rounded-lg border border-slate-200 text-center shadow-2xs">
                                                        <span class="text-xs font-bold text-indigo-700 block" x-text="sz.code"></span>
                                                        <input 
                                                            type="number" 
                                                            min="0" 
                                                            x-model.number="newProduct.sizes[(sz.code || '').toLowerCase()]" 
                                                            placeholder="0"
                                                            class="w-full text-center mt-1 py-0.5 text-xs font-bold border border-slate-200 rounded focus:border-indigo-600 focus:outline-none"
                                                        >
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-xs">
                                            <span class="text-slate-500 font-medium">Total en Curva:</span>
                                            <span class="font-extrabold text-indigo-700 text-sm" x-text="calculateNewProductTotalStock() + ' piezas'"></span>
                                        </div>
                                    </div>
                                </template>

                                <!-- Matriz para Dimensión Tonos (Cosmética) -->
                                <template x-if="newProduct.variantDimension === 'shade'">
                                    <div class="space-y-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                                        <div class="flex items-center justify-between">
                                            <label class="text-[11px] font-bold text-indigo-900 uppercase tracking-wider">Tonos Cosmeticos (Shades)</label>
                                            <span class="text-[10px] text-slate-400">6 Tonos</span>
                                        </div>
                                        <div class="grid grid-cols-3 gap-2">
                                            <div class="bg-white p-2 rounded-lg border border-slate-200 text-center">
                                                <span class="text-[11px] font-bold text-slate-700 block">T01 Claro</span>
                                                <input type="number" min="0" x-model.number="newProduct.shades.t01" class="w-full text-center mt-1 py-0.5 text-xs font-bold border border-slate-200 rounded">
                                            </div>
                                            <div class="bg-white p-2 rounded-lg border border-slate-200 text-center">
                                                <span class="text-[11px] font-bold text-slate-700 block">T02 Beige</span>
                                                <input type="number" min="0" x-model.number="newProduct.shades.t02" class="w-full text-center mt-1 py-0.5 text-xs font-bold border border-slate-200 rounded">
                                            </div>
                                            <div class="bg-white p-2 rounded-lg border border-slate-200 text-center">
                                                <span class="text-[11px] font-bold text-slate-700 block">T03 Medio</span>
                                                <input type="number" min="0" x-model.number="newProduct.shades.t03" class="w-full text-center mt-1 py-0.5 text-xs font-bold border border-slate-200 rounded">
                                            </div>
                                            <div class="bg-white p-2 rounded-lg border border-slate-200 text-center">
                                                <span class="text-[11px] font-bold text-slate-700 block">T04 Bronce</span>
                                                <input type="number" min="0" x-model.number="newProduct.shades.t04" class="w-full text-center mt-1 py-0.5 text-xs font-bold border border-slate-200 rounded">
                                            </div>
                                            <div class="bg-white p-2 rounded-lg border border-slate-200 text-center">
                                                <span class="text-[11px] font-bold text-slate-700 block">T05 Moreno</span>
                                                <input type="number" min="0" x-model.number="newProduct.shades.t05" class="w-full text-center mt-1 py-0.5 text-xs font-bold border border-slate-200 rounded">
                                            </div>
                                            <div class="bg-white p-2 rounded-lg border border-slate-200 text-center">
                                                <span class="text-[11px] font-bold text-slate-700 block">T06 Profundo</span>
                                                <input type="number" min="0" x-model.number="newProduct.shades.t06" class="w-full text-center mt-1 py-0.5 text-xs font-bold border border-slate-200 rounded">
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Matriz para Dimensión Volúmenes (Perfumería) -->
                                <template x-if="newProduct.variantDimension === 'volume'">
                                    <div class="space-y-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                                        <div class="flex items-center justify-between">
                                            <label class="text-[11px] font-bold text-indigo-900 uppercase tracking-wider">Volumenes de Presentacion</label>
                                            <span class="text-[10px] text-slate-400">Mililitros</span>
                                        </div>
                                        <div class="grid grid-cols-3 gap-2">
                                            <div class="bg-white p-2 rounded-lg border border-slate-200 text-center">
                                                <span class="text-[11px] font-bold text-slate-700 block">10 ml (Travel)</span>
                                                <input type="number" min="0" x-model.number="newProduct.volumes.v10" class="w-full text-center mt-1 py-0.5 text-xs font-bold border border-slate-200 rounded">
                                            </div>
                                            <div class="bg-white p-2 rounded-lg border border-slate-200 text-center">
                                                <span class="text-[11px] font-bold text-slate-700 block">50 ml (Regular)</span>
                                                <input type="number" min="0" x-model.number="newProduct.volumes.v50" class="w-full text-center mt-1 py-0.5 text-xs font-bold border border-slate-200 rounded">
                                            </div>
                                            <div class="bg-white p-2 rounded-lg border border-slate-200 text-center">
                                                <span class="text-[11px] font-bold text-slate-700 block">100 ml (Full)</span>
                                                <input type="number" min="0" x-model.number="newProduct.volumes.v100" class="w-full text-center mt-1 py-0.5 text-xs font-bold border border-slate-200 rounded">
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Pieza Única / Accesorio -->
                                <template x-if="newProduct.variantDimension === 'none'">
                                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                                        <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1">Stock Fisico Inicial</label>
                                        <input 
                                            type="number" 
                                            min="0" 
                                            x-model.number="newProduct.unitStock" 
                                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm font-bold text-slate-800"
                                        >
                                    </div>
                                </template>

                                <!-- Atributos Físicos: Tela & Color -->
                                <div class="grid grid-cols-2 gap-3 pt-2">
                                    <div>
                                        <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1">Tipo de Tela</label>
                                        <input 
                                            type="text" 
                                            x-model="newProduct.fabricType" 
                                            placeholder="Modal Seamless / Algodon" 
                                            class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none"
                                        >
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1">Color / Swatch</label>
                                        <div class="flex items-center gap-2">
                                            <input 
                                                type="color" 
                                                x-model="newProduct.colorHex" 
                                                class="w-7 h-7 p-0 border border-slate-300 rounded cursor-pointer shrink-0"
                                            >
                                            <input 
                                                type="text" 
                                                x-model="newProduct.colorName" 
                                                placeholder="Negro Profundo" 
                                                class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- Slotting de Almacén en Bodega -->
                                <div class="pt-2 border-t border-slate-100">
                                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1">Ubicacion en Bodega (Slotting)</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <span class="text-[10px] text-slate-400 block mb-0.5">Pasillo</span>
                                            <select x-model="newProduct.aisle" class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded text-xs font-bold">
                                                <option value="A">Pasillo A</option>
                                                <option value="B">Pasillo B</option>
                                                <option value="C">Pasillo C</option>
                                            </select>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-400 block mb-0.5">Rack</span>
                                            <input type="text" x-model="newProduct.rack" class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded text-xs font-bold text-center">
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-400 block mb-0.5">Gaveta</span>
                                            <input type="text" x-model="newProduct.bin" class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded text-xs font-bold text-center">
                                        </div>
                                    </div>
                                </div>

                                <!-- Galería de Imágenes -->
                                <div class="pt-2 border-t border-slate-100">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">Fotografias de Producto</label>
                                        <span class="text-[10px] font-semibold text-slate-400" x-text="(newProduct.images ? newProduct.images.length : 0) + ' / 10 fotos'"></span>
                                    </div>
                                    
                                    <!-- Dropzone -->
                                    <div 
                                        @dragover.prevent="isDraggingImages = true"
                                        @dragleave.prevent="isDraggingImages = false"
                                        @drop.prevent="handleImageDrop($event)"
                                        class="border-2 border-dashed rounded-xl p-3 text-center transition cursor-pointer"
                                        :class="isDraggingImages ? 'border-indigo-500 bg-indigo-50/60' : 'border-slate-200 bg-slate-50 hover:bg-slate-100/60'"
                                        @click="$refs.newProductFileInput.click()"
                                    >
                                        <input 
                                            type="file" 
                                            x-ref="newProductFileInput" 
                                            @change="handleImageUpload($event)"
                                            multiple 
                                            accept="image/jpeg,image/png,image/webp,image/avif" 
                                            class="hidden"
                                        >
                                        <div class="flex flex-col items-center justify-center gap-1 text-slate-500">
                                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-[11px] font-semibold text-slate-700">Arrastra fotos o haz clic para subir</span>
                                            <span class="text-[10px] text-slate-400">JPG, PNG, WEBP, AVIF (Max 5MB)</span>
                                        </div>
                                    </div>

                                    <!-- Thumbnails -->
                                    <template x-if="newProduct.images && newProduct.images.length > 0">
                                        <div class="grid grid-cols-4 gap-2 mt-2">
                                            <template x-for="(img, idx) in newProduct.images" :key="img.id || idx">
                                                <div class="relative group rounded-lg overflow-hidden border border-slate-200 aspect-square bg-slate-100">
                                                    <img :src="img.preview" class="w-full h-full object-cover">
                                                    <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-1">
                                                        <button type="button" @click.stop="setPrimaryImage(idx)" class="p-1 rounded bg-white/90 text-slate-800 text-[9px] font-bold" title="Hacer Portada">P</button>
                                                        <button type="button" @click.stop="removeImage(idx)" class="p-1 rounded bg-rose-600 text-white text-[9px] font-bold" title="Eliminar">X</button>
                                                    </div>
                                                    <span x-show="img.is_primary" class="absolute top-1 left-1 bg-indigo-600 text-white text-[8px] font-bold px-1 rounded">Portada</span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                            </div>
                        </div>

                        <!-- ========================================================= -->
                        <!-- PANEL 3: COPILOTO IA, SEO & PRICING COMERCIAL (4 COLS)    -->
                        <!-- ========================================================= -->
                        <div class="lg:col-span-4 space-y-5">
                            
                            <!-- Tarjeta Copiloto IA Comercial -->
                            <div class="bg-white p-5 rounded-2xl border border-indigo-200/90 shadow-2xs space-y-4 relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-28 h-28 bg-indigo-50 rounded-bl-full pointer-events-none -z-0 opacity-60"></div>
                                
                                <div class="flex items-center justify-between pb-3 border-b border-indigo-100 relative z-10">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-md bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-2xs">3</div>
                                        <h3 class="font-bold text-slate-800 text-sm">Copiloto Comercial IA</h3>
                                    </div>
                                    <button 
                                        type="button" 
                                        @click="generateAiDescription()" 
                                        :disabled="newProduct.isGeneratingAi || !newProduct.name || newProduct.name.trim().length === 0"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="Generar descripcion persuasiva y tags SEO con IA"
                                    >
                                        <svg x-show="!newProduct.isGeneratingAi" class="w-3.5 h-3.5 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        <svg x-show="newProduct.isGeneratingAi" class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        <span x-text="newProduct.isGeneratingAi ? 'Redactando...' : 'Generar con IA'"></span>
                                    </button>
                                </div>

                                <!-- Descripción Persuasiva Comercial -->
                                <div class="relative z-10">
                                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1">Descripcion Persuasiva (B2C)</label>
                                    <textarea 
                                        x-model="newProduct.description" 
                                        rows="4" 
                                        placeholder="Haz clic en 'Generar con IA' para redactar una descripcion comercial optimizada en base a la composicion textil y marca..."
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-800 leading-relaxed focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 resize-none"
                                    ></textarea>
                                </div>

                                <!-- Viñetas / Puntos Clave Técnicos -->
                                <template x-if="newProduct.bulletPoints && newProduct.bulletPoints.length > 0">
                                    <div class="relative z-10 p-3 bg-indigo-50/50 rounded-xl border border-indigo-100">
                                        <span class="text-[10px] font-bold text-indigo-900 uppercase tracking-wider block mb-1.5">Especificaciones Tecnicas</span>
                                        <ul class="space-y-1 text-[11px] text-slate-700">
                                            <template x-for="(bp, bIdx) in newProduct.bulletPoints" :key="bIdx">
                                                <li class="flex items-start gap-1.5">
                                                    <span class="text-indigo-600 font-bold shrink-0">&bull;</span>
                                                    <span x-text="bp"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>

                                <!-- Tags SEO & Vitrina Web -->
                                <div class="relative z-10">
                                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1.5">Tags SEO & Busqueda Storefront</label>
                                    <div class="flex flex-wrap gap-1.5 p-2 bg-slate-50 rounded-xl border border-slate-200 min-h-[44px]">
                                        <template x-for="(tag, tIdx) in newProduct.seoTags" :key="tIdx">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-white text-indigo-700 border border-indigo-200 shadow-2xs">
                                                <span x-text="tag"></span>
                                                <button type="button" @click="removeSeoTag(tIdx)" class="text-slate-400 hover:text-rose-600 font-extrabold cursor-pointer">&times;</button>
                                            </span>
                                        </template>
                                        <input 
                                            type="text" 
                                            x-model="newProduct.newTagInput" 
                                            @keydown.enter.prevent="addSeoTag()" 
                                            placeholder="+ tag..." 
                                            class="text-[11px] bg-transparent outline-none flex-1 min-w-[70px] text-slate-700"
                                        >
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">Escribe una palabra clave y presiona Enter para agregarla.</p>
                                </div>
                            </div>

                            <!-- Tarjeta Pricing & Margen en Tiempo Real -->
                            <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white p-5 rounded-2xl border border-slate-800 shadow-sm space-y-4">
                                <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                                    <h4 class="font-bold text-xs text-white uppercase tracking-wider">Margen Comercial & Pricing</h4>
                                    <span class="text-[10px] text-indigo-300 font-semibold">Calculo Reactivo</span>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-[10px] text-slate-300 block mb-1">Precio Venta (PVP)</label>
                                        <div class="relative">
                                            <span class="absolute left-2.5 top-2 text-xs text-slate-400 font-bold">$</span>
                                            <input 
                                                type="number" 
                                                step="0.5" 
                                                x-model.number="newProduct.pvp" 
                                                class="w-full bg-slate-800/90 border border-slate-700 rounded-lg py-1.5 pl-6 pr-2 text-xs font-bold text-white focus:outline-none focus:ring-1 focus:ring-indigo-400 font-mono"
                                            >
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-slate-300 block mb-1">Costo Unitario</label>
                                        <div class="relative">
                                            <span class="absolute left-2.5 top-2 text-xs text-slate-400 font-bold">$</span>
                                            <input 
                                                type="number" 
                                                step="0.5" 
                                                x-model.number="newProduct.cost" 
                                                class="w-full bg-slate-800/90 border border-slate-700 rounded-lg py-1.5 pl-6 pr-2 text-xs font-bold text-white focus:outline-none focus:ring-1 focus:ring-indigo-400 font-mono"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-xs">
                                    <div>
                                        <span class="text-[10px] text-slate-400 block">Utilidad por Pieza:</span>
                                        <strong class="text-emerald-400 text-sm font-mono" x-text="formatCurrency(calculateNewProductProfit())"></strong>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] text-slate-400 block">Margen Bruto:</span>
                                        <div class="flex items-center gap-1.5 justify-end">
                                            <span 
                                                class="text-sm font-extrabold font-mono" 
                                                :class="calculateNewProductMargin() >= 50 ? 'text-emerald-400' : (calculateNewProductMargin() >= 30 ? 'text-amber-400' : 'text-rose-400')" 
                                                x-text="calculateNewProductMargin() + '%'"
                                            ></span>
                                            <span 
                                                class="text-[9px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider"
                                                :class="calculateNewProductMargin() >= 50 ? 'bg-emerald-500/20 text-emerald-300' : (calculateNewProductMargin() >= 30 ? 'bg-amber-500/20 text-amber-300' : 'bg-rose-500/20 text-rose-300')"
                                                x-text="calculateNewProductMargin() >= 50 ? 'Saludable' : (calculateNewProductMargin() >= 30 ? 'Aceptable' : 'Riesgo')"
                                            ></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Proveedor URL si es dropshipping -->
                                <template x-if="newProduct.type === 'dropship'">
                                    <div class="pt-2 border-t border-slate-800">
                                        <label class="text-[10px] text-slate-400 block mb-1">Enlace / SKU Proveedor Mayorista</label>
                                        <input 
                                            type="text" 
                                            x-model="newProduct.providerUrl" 
                                            placeholder="https://torzo.com/items/..." 
                                            class="w-full px-2.5 py-1 bg-slate-800 border border-slate-700 rounded text-xs text-slate-200 focus:outline-none"
                                        >
                                    </div>
                                </template>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- Footer de la Workstation -->
                <div class="p-4 border-t border-slate-200 bg-white flex items-center justify-between">
                    <div class="text-xs text-slate-500">
                        <span class="font-bold text-slate-700" x-text="calculateNewProductTotalStock() + ' unidades'"></span> preparadas para ingresar al inventario.
                    </div>
                    <div class="flex items-center gap-3">
                        <button 
                            type="button"
                            @click="closeCreateProductDrawer()" 
                            class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-lg transition cursor-pointer"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="button"
                            @click="saveNewProduct()" 
                            class="px-5 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm transition cursor-pointer flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Guardar Producto en Catalogo</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL DE RESOLUCIÓN DE DUPLICADOS EN CATÁLOGO (TASK-INV-15)       -->
    <!-- Comparación lado a lado con 3 opciones de resolución operativa     -->
    <!-- ================================================================= -->
    <div 
        x-show="newProduct.duplicateAlertModalOpen" 
        class="fixed inset-0 z-[85] overflow-y-auto" 
        style="z-index: 85; display: none;"
        aria-labelledby="duplicate-modal-title" 
        role="dialog" 
        aria-modal="true"
        @keydown.escape.window="closeDuplicateModal()"
    >
        <!-- Backdrop oscuro -->
        <div 
            x-show="newProduct.duplicateAlertModalOpen"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            @click="closeDuplicateModal()"
        ></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div 
                x-show="newProduct.duplicateAlertModalOpen"
                x-transition:enter="ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl border border-slate-200 overflow-hidden"
                @click.stop
            >
                <!-- Encabezado del Modal -->
                <div class="flex items-start justify-between pb-4 border-b border-slate-200">
                    <div class="flex items-center gap-3">
                        <span class="p-2.5 bg-rose-100 text-rose-700 rounded-xl border border-rose-200 inline-flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-slate-900" id="duplicate-modal-title">Resolucion de Producto Duplicado</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-100 text-rose-800" x-text="newProduct.duplicateScore + '% Similitud'"></span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">El motor de unicidad detecto coincidencias criticas con un producto ya registrado.</p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        @click="closeDuplicateModal()" 
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Comparativa Lado a Lado: Borrador vs Producto Existente -->
                <div class="py-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <!-- Columna Izquierda: Nuevo Borrador -->
                    <div class="p-4 rounded-xl border border-indigo-200 bg-indigo-50/40 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-indigo-100">
                            <span class="text-xs font-bold text-indigo-900 uppercase tracking-wider">Tu Borrador Actual</span>
                            <span class="text-[10px] font-extrabold px-1.5 py-0.5 bg-indigo-200 text-indigo-800 rounded">Nuevo</span>
                        </div>
                        <div class="space-y-2 text-xs">
                            <div>
                                <span class="text-[10px] text-slate-400 block">SKU Propuesto:</span>
                                <span class="font-mono font-bold text-slate-800" x-text="newProduct.sku || 'Por generar'"></span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Nombre del Modelo:</span>
                                <span class="font-bold text-slate-800" x-text="newProduct.name"></span>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Marca:</span>
                                    <span class="font-semibold text-slate-700" x-text="newProduct.brand || '-'"></span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Categoria:</span>
                                    <span class="font-semibold text-slate-700" x-text="newProduct.category"></span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 pt-1 border-t border-indigo-100">
                                <div>
                                    <span class="text-[10px] text-slate-400 block">PVP:</span>
                                    <span class="font-mono font-bold text-slate-800" x-text="formatCurrency(newProduct.pvp || 0)"></span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Stock a Ingresar:</span>
                                    <span class="font-mono font-bold text-indigo-700" x-text="calculateNewProductTotalStock() + ' uds'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Producto Existente Detectado -->
                    <div class="p-4 rounded-xl border border-rose-200 bg-rose-50/40 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-rose-100">
                            <span class="text-xs font-bold text-rose-900 uppercase tracking-wider">Producto en Catalogo</span>
                            <span class="text-[10px] font-extrabold px-1.5 py-0.5 bg-rose-200 text-rose-800 rounded">Existente</span>
                        </div>
                        <template x-if="newProduct.duplicateSelectedMatch">
                            <div class="space-y-2 text-xs">
                                <div>
                                    <span class="text-[10px] text-slate-400 block">SKU Registrado:</span>
                                    <span class="font-mono font-bold text-rose-800" x-text="newProduct.duplicateSelectedMatch.sku"></span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Nombre Registrado:</span>
                                    <span class="font-bold text-slate-800" x-text="newProduct.duplicateSelectedMatch.title"></span>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <span class="text-[10px] text-slate-400 block">Marca:</span>
                                        <span class="font-semibold text-slate-700" x-text="newProduct.duplicateSelectedMatch.brand || '-'"></span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 block">Categoria:</span>
                                        <span class="font-semibold text-slate-700" x-text="newProduct.duplicateSelectedMatch.category || 'General'"></span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 pt-1 border-t border-rose-100">
                                    <div>
                                        <span class="text-[10px] text-slate-400 block">Precio Actual:</span>
                                        <span class="font-mono font-bold text-slate-800" x-text="formatCurrency(newProduct.duplicateSelectedMatch.price || 0)"></span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 block">Stock Disponible:</span>
                                        <span class="font-mono font-bold text-rose-700" x-text="(newProduct.duplicateSelectedMatch.stock || 0) + ' uds'"></span>
                                    </div>
                                </div>
                                <template x-if="newProduct.duplicateSelectedMatch.reasons && newProduct.duplicateSelectedMatch.reasons.length > 0">
                                    <div class="pt-1 text-[10px] text-rose-600">
                                        <span class="font-bold">Motivos: </span>
                                        <span x-text="newProduct.duplicateSelectedMatch.reasons.join(', ')"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="!newProduct.duplicateSelectedMatch">
                            <p class="text-xs text-slate-400 italic">No se encontro el producto en memoria.</p>
                        </template>
                    </div>

                </div>

                <!-- Opciones de Resolución Operativa -->
                <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <button 
                        type="button" 
                        @click="resolveDuplicateDismissDraft()" 
                        class="w-full sm:w-auto px-4 py-2 text-xs font-bold text-slate-600 hover:text-rose-700 hover:bg-slate-100 rounded-lg transition cursor-pointer"
                    >
                        Descartar Borrador
                    </button>

                    <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                        <button 
                            type="button" 
                            @click="resolveDuplicateContinueNew()" 
                            class="w-full sm:w-auto px-4 py-2 text-xs font-bold border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg transition cursor-pointer"
                            title="Guardar como un nuevo producto independiente omitiendo la advertencia"
                        >
                            Continuar como Producto Nuevo
                        </button>
                        <button 
                            type="button" 
                            @click="resolveDuplicateAddStock()" 
                            class="w-full sm:w-auto px-5 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition cursor-pointer flex items-center justify-center gap-1.5"
                            title="Sumar el stock ingresado directamente a la referencia existente"
                        >
                            <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span>Sumar Stock al Existente</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL DE CONFIRMACIÓN: AÑADIR A FUTURAS COMPRAS AL GUARDAR        -->
    <!-- ================================================================= -->
    <div 
        x-show="confirmPurchaseModalOpen" 
        class="fixed inset-0 z-[80] overflow-y-auto"
        style="z-index: 80; display: none;"
        aria-labelledby="confirm-purchase-modal-title" 
        role="dialog" 
        aria-modal="true"
        @keydown.escape.window="confirmPurchaseModalOpen = false"
    >
        <!-- Backdrop oscuro -->
        <div 
            x-show="confirmPurchaseModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="confirmPurchaseModalOpen = false"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity cursor-pointer"
        ></div>

        <div class="min-h-full flex items-center justify-center p-4 text-center sm:p-0">
            <div 
                x-show="confirmPurchaseModalOpen"
                x-transition:enter="ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative bg-white rounded-2xl p-6 text-left shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden my-8"
            >
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 shadow-2xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 leading-6" id="confirm-purchase-modal-title">
                            ¿Deseas añadir este producto a la lista de compras?
                        </h3>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                            Tienes configurada una orden de reposición para <strong class="text-indigo-700" x-text="selectedProduct?.sku"></strong> por <strong class="text-slate-900" x-text="(purchaseQty || 0) + ' piezas'"></strong> con el proveedor <strong class="text-slate-900" x-text="purchaseSupplier"></strong> (Inversión estimada: <strong class="text-emerald-700" x-text="formatCurrency(calculatePurchaseTotal())"></strong>).
                        </p>
                        <p class="text-xs text-slate-500 mt-1">
                            ¿Quieres incorporar esta orden a la pestaña <strong>Proveedores & Scraping</strong> al guardar los cambios del producto?
                        </p>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-end gap-2.5">
                    <button 
                        type="button" 
                        @click="confirmPurchaseModalOpen = false"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                        style="min-height: unset;"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        @click="confirmSaveWithPurchase(false)"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition cursor-pointer border border-slate-200"
                        style="min-height: unset;"
                    >
                        No, solo guardar producto
                    </button>
                    <button 
                        type="button" 
                        @click="confirmSaveWithPurchase(true)"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm transition cursor-pointer flex items-center justify-center gap-1.5"
                        style="min-height: unset;"
                    >
                        <svg class="w-4 h-4 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Sí, añadir a compras y guardar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================================================================= -->
    <!-- MODAL DE REASIGNACIÓN DE SLOT FÍSICO EN BODEGA (TASK-INV-01)      -->
    <!-- ================================================================= -->
    <div 
        x-show="slotReassignModal.isOpen" 
        class="fixed inset-0 z-[85] overflow-y-auto"
        style="z-index: 85; display: none;"
        aria-labelledby="slot-reassign-modal-title" 
        role="dialog" 
        aria-modal="true"
        @keydown.escape.window="slotReassignModal.isOpen = false"
    >
        <div 
            x-show="slotReassignModal.isOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="slotReassignModal.isOpen = false"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity cursor-pointer"
        ></div>

        <div class="min-h-full flex items-center justify-center p-4 text-center sm:p-0">
            <div 
                x-show="slotReassignModal.isOpen"
                x-transition:enter="ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200"
                @click.stop
            >
                <!-- Cabecera -->
                <div class="bg-slate-900 px-6 py-4 flex items-center justify-between text-white">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold tracking-tight text-white">Reasignar Ubicación Física (Slotting)</h3>
                            <p class="text-[11px] text-slate-400">Actualiza el pasillo, rack o gaveta del producto en bodega</p>
                        </div>
                    </div>
                    <button @click="slotReassignModal.isOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Cuerpo -->
                <div class="p-6 space-y-4">
                    <!-- Ficha del Producto -->
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="font-mono text-xs font-bold text-indigo-600" x-text="slotReassignModal.sku"></div>
                            <div class="text-xs font-semibold text-slate-900 mt-0.5" x-text="slotReassignModal.productName"></div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 block uppercase font-semibold">Ubicación Actual</span>
                            <span class="text-xs font-mono font-bold text-slate-700 bg-white px-2 py-0.5 rounded border border-slate-200 mt-0.5 inline-block" x-text="slotReassignModal.currentLocation || 'Sin Asignar'"></span>
                        </div>
                    </div>

                    <!-- Selector de Nuevo Slot -->
                    <div>
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-2">Nuevo Slot Destino</label>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="text-[10px] font-semibold text-slate-500 block mb-1">Pasillo</label>
                                <select 
                                    x-model="slotReassignModal.selectedAisle"
                                    class="w-full px-2.5 py-2 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                                >
                                    <option value="A">Pasillo A (Ropa Interior)</option>
                                    <option value="B">Pasillo B (Atlético & Jocks)</option>
                                    <option value="C">Pasillo C (Cuidado Personal)</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-semibold text-slate-500 block mb-1">Rack / Módulo</label>
                                <select 
                                    x-model="slotReassignModal.selectedRack"
                                    class="w-full px-2.5 py-2 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                                >
                                    <option value="01">Rack 01</option>
                                    <option value="02">Rack 02</option>
                                    <option value="03">Rack 03</option>
                                    <option value="04">Rack 04</option>
                                    <option value="05">Rack 05</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-semibold text-slate-500 block mb-1">Gaveta / Nivel</label>
                                <select 
                                    x-model="slotReassignModal.selectedBin"
                                    class="w-full px-2.5 py-2 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                                >
                                    <option value="A">Gaveta A (Superior)</option>
                                    <option value="B">Gaveta B (Media)</option>
                                    <option value="C">Gaveta C (Inferior)</option>
                                    <option value="D">Gaveta D (Piso)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Previsualización del nuevo código -->
                    <div class="bg-indigo-50/50 p-3 rounded-xl border border-indigo-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-indigo-900">Código de Slot Resultante:</span>
                            <span 
                                :class="getSlotBadgeClass(slotReassignModal.selectedAisle + '-' + slotReassignModal.selectedRack + '-' + slotReassignModal.selectedBin)"
                                class="text-xs font-mono font-extrabold px-2.5 py-0.5 rounded border"
                                x-text="slotReassignModal.selectedAisle + '-' + slotReassignModal.selectedRack + '-' + slotReassignModal.selectedBin"
                            ></span>
                        </div>
                        <span class="text-[10px] text-indigo-600 font-medium">Asiento inmutable en Kardex</span>
                    </div>

                    <!-- Motivo / Notas -->
                    <div>
                        <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1">Motivo del Movimiento Físico</label>
                        <input 
                            type="text" 
                            x-model="slotReassignModal.notes" 
                            placeholder="Ej: Reubicación por alta rotación (Clase A), consolidación de racks..."
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 font-medium"
                        >
                    </div>
                </div>

                <!-- Footer Acciones -->
                <div class="bg-slate-50 px-6 py-3.5 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button 
                        @click="slotReassignModal.isOpen = false"
                        type="button" 
                        class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button 
                        @click="saveSlotReassign()"
                        type="button" 
                        class="px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-xs transition cursor-pointer flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Confirmar Reubicación</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL DE RECETAS BOM Y ENSAMBLE DE COMBOS (TASK-INV-03)           -->
    <!-- ================================================================= -->
    <div 
        x-show="bundleRecipesModal.isOpen" 
        class="fixed inset-0 z-[85] overflow-y-auto"
        style="z-index: 85; display: none;"
        aria-labelledby="bundle-recipes-modal-title" 
        role="dialog" 
        aria-modal="true"
        @keydown.escape.window="bundleRecipesModal.isOpen = false"
    >
        <div 
            x-show="bundleRecipesModal.isOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="bundleRecipesModal.isOpen = false"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity cursor-pointer"
        ></div>

        <div class="min-h-full flex items-center justify-center p-4 text-center sm:p-0">
            <div 
                x-show="bundleRecipesModal.isOpen"
                x-transition:enter="ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-5xl border border-slate-200 flex flex-col max-h-[90vh]"
                @click.stop
            >
                <!-- Encabezado Modal -->
                <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 px-6 py-4 flex items-center justify-between border-b border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-violet-500/20 border border-violet-400/30 flex items-center justify-center text-violet-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 id="bundle-recipes-modal-title" class="text-base font-bold text-white leading-none">Recetas de Ensamble BOM & Combos Virtuales</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-500/30 text-violet-200 border border-violet-400/40" x-text="productBundles.length + ' Fórmulas'"></span>
                            </div>
                            <p class="text-xs text-slate-300 mt-1">Bill of Materials para Cajas de Suscripción Mensual Multiverso y Packs Promocionales</p>
                        </div>
                    </div>
                    <button @click="bundleRecipesModal.isOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Filtros por tipo de combo -->
                <div class="bg-slate-50 px-6 py-2.5 border-b border-slate-200 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            @click="bundleRecipesModal.activeFilter = 'all'"
                            class="px-3 py-1 text-xs font-bold rounded-lg transition cursor-pointer"
                            :class="bundleRecipesModal.activeFilter === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        >
                            Todos los Combos
                        </button>
                        <button 
                            type="button" 
                            @click="bundleRecipesModal.activeFilter = 'subscription'"
                            class="px-3 py-1 text-xs font-bold rounded-lg transition cursor-pointer"
                            :class="bundleRecipesModal.activeFilter === 'subscription' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        >
                            Cajas de Suscripción
                        </button>
                        <button 
                            type="button" 
                            @click="bundleRecipesModal.activeFilter = 'promo'"
                            class="px-3 py-1 text-xs font-bold rounded-lg transition cursor-pointer"
                            :class="bundleRecipesModal.activeFilter === 'promo' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        >
                            Packs Promocionales
                        </button>
                    </div>
                    <div class="text-[11px] text-slate-500 font-medium">
                        Liquidación atómica en Kardex (<code class="font-mono text-indigo-600 font-bold">SALIDA_COMBO_SUSCRIPCION</code>)
                    </div>
                </div>

                <!-- Contenido Principal Master-Detail -->
                <div class="p-6 overflow-y-auto flex-1 grid grid-cols-1 lg:grid-cols-12 gap-6 bg-slate-100/60">
                    
                    <!-- Columna Izquierda: Lista de Combos -->
                    <div class="lg:col-span-5 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 flex items-center justify-between">
                            <span>Catálogo de Fórmulas BOM</span>
                            <span class="text-indigo-600 font-mono" x-text="filteredBundles.length + ' activos'"></span>
                        </div>

                        <template x-for="bundle in filteredBundles" :key="bundle.id">
                            <div 
                                @click="bundleRecipesModal.selectedBundleId = bundle.id"
                                class="p-4 rounded-xl border transition cursor-pointer relative"
                                :class="bundleRecipesModal.selectedBundleId === bundle.id ? 'bg-white border-indigo-600 ring-2 ring-indigo-500/20 shadow-md' : 'bg-white border-slate-200/80 hover:border-indigo-300 hover:shadow-xs'"
                            >
                                <div class="flex items-start justify-between gap-2 mb-1.5">
                                    <span 
                                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                        :class="{
                                            'bg-blue-50 text-blue-700 border border-blue-200': bundle.badgeColor === 'blue',
                                            'bg-indigo-50 text-indigo-700 border border-indigo-200': bundle.badgeColor === 'indigo',
                                            'bg-purple-50 text-purple-700 border border-purple-200': bundle.badgeColor === 'purple',
                                            'bg-emerald-50 text-emerald-700 border border-emerald-200': bundle.badgeColor === 'emerald'
                                        }"
                                        x-text="bundle.tierName"
                                    ></span>
                                    <div class="text-right">
                                        <div class="text-xs font-bold text-slate-900" x-text="'$' + bundle.subscriptionPrice.toFixed(2) + ' MXN'"></div>
                                        <div class="text-[10px] text-slate-400 line-through" x-text="'$' + bundle.regularPrice.toFixed(2)"></div>
                                    </div>
                                </div>

                                <div class="font-bold text-sm text-slate-900 leading-snug" x-text="bundle.name"></div>
                                <div class="font-mono text-[11px] text-slate-500 mt-0.5" x-text="bundle.sku"></div>

                                <p class="text-xs text-slate-600 mt-2 leading-relaxed" x-text="bundle.description"></p>

                                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
                                    <span class="text-slate-500 font-medium">Insumos: <strong class="text-slate-700" x-text="bundle.components.length + ' items'"></strong></span>
                                    <span 
                                        class="px-2 py-0.5 rounded-full text-[11px] font-bold"
                                        :class="calculateBundleStock(bundle) > 5 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : (calculateBundleStock(bundle) > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200')"
                                        x-text="calculateBundleStock(bundle) + ' cajas armables'"
                                    ></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Columna Derecha: Detalle de Escandallo y Simulador -->
                    <div class="lg:col-span-7 space-y-4" x-show="selectedBundle">
                        <!-- Ficha de Combo Seleccionado -->
                        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-bold text-indigo-600" x-text="selectedBundle.sku"></span>
                                        <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.2 rounded border border-emerald-200" x-text="'-' + selectedBundle.discountPct + '% Ahorro'"></span>
                                    </div>
                                    <h4 class="text-base font-bold text-slate-900 mt-1" x-text="selectedBundle.name"></h4>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-indigo-600" x-text="'$' + selectedBundle.subscriptionPrice.toFixed(2) + ' MXN'"></div>
                                    <div class="text-[11px] text-slate-500 font-medium">Precio por entrega</div>
                                </div>
                            </div>

                            <!-- Barra de Stock Limitante -->
                            <div class="mt-4 p-3 bg-indigo-50/70 rounded-xl border border-indigo-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-800">Capacidad Máxima de Armado Inmediato</div>
                                        <div class="text-[11px] text-slate-600">Calculado sobre el insumo físico con menor existencia disponible</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-lg font-extrabold text-indigo-700" x-text="calculateBundleStock(selectedBundle)"></span>
                                    <span class="text-xs font-medium text-indigo-900 ml-1">cajas</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Escandallo (BOM) -->
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                                <h5 class="text-xs font-bold uppercase tracking-wider text-slate-700">Fórmula de Insumos Físicos (BOM)</h5>
                                <span class="text-[11px] text-slate-500">Componentes atómicos a deducir</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50/50 border-b border-slate-200 text-slate-500 text-[10px] uppercase font-bold tracking-wider">
                                            <th class="py-2.5 px-3">Insumo Físico</th>
                                            <th class="py-2.5 px-3">Slot Bodega</th>
                                            <th class="py-2.5 px-3 text-center">Cant / Caja</th>
                                            <th class="py-2.5 px-3 text-right">Stock Bodega</th>
                                            <th class="py-2.5 px-3 text-right">Requerido Lote</th>
                                            <th class="py-2.5 px-3 text-center">Estatus</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="comp in selectedBundle.components" :key="comp.sku">
                                            <tr class="hover:bg-slate-50/60 transition">
                                                <td class="py-2.5 px-3">
                                                    <div class="font-bold text-slate-800" x-text="comp.name"></div>
                                                    <div class="font-mono text-[10px] text-slate-400" x-text="comp.sku + (comp.size ? ' • ' + comp.size.toUpperCase() : '')"></div>
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    <span 
                                                        class="inline-block px-2 py-0.5 rounded font-mono text-[10px] font-bold"
                                                        :class="getSlotBadgeClass(comp.location)"
                                                        x-text="comp.location || 'A-01-A'"
                                                    ></span>
                                                </td>
                                                <td class="py-2.5 px-3 text-center font-bold text-slate-800" x-text="comp.qty + ' ud'"></td>
                                                <td class="py-2.5 px-3 text-right font-mono font-medium text-slate-700" x-text="getBundleComponentStock(comp.sku, comp.size) + ' uds'"></td>
                                                <td class="py-2.5 px-3 text-right font-mono font-bold text-indigo-600" x-text="(comp.qty * bundleRecipesModal.dispatchQty) + ' uds'"></td>
                                                <td class="py-2.5 px-3 text-center">
                                                    <span 
                                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                        :class="getBundleComponentStock(comp.sku, comp.size) >= (comp.qty * bundleRecipesModal.dispatchQty) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'"
                                                        x-text="getBundleComponentStock(comp.sku, comp.size) >= (comp.qty * bundleRecipesModal.dispatchQty) ? 'Suficiente' : 'Faltante'"
                                                    ></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Panel Simulador de Despacho Masivo & Liquidación Atómica -->
                        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <h5 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span>Simulador de Despacho Masivo & Liquidación Atómica</span>
                                </h5>
                                <span class="text-[10px] text-slate-400 font-mono">POST /api/v1/inventory/bundles/{id}/liquidate</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block mb-1">Cajas a Despachar</label>
                                    <input 
                                        type="number" 
                                        min="1" 
                                        :max="calculateBundleStock(selectedBundle)"
                                        x-model.number="bundleRecipesModal.dispatchQty"
                                        class="w-full px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                                    >
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block mb-1">Referencia de Lote</label>
                                    <input 
                                        type="text" 
                                        x-model="bundleRecipesModal.dispatchReference"
                                        placeholder="Ej: SUB-BATCH-2026-09A"
                                        class="w-full px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                                    >
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block mb-1">Notas de Auditoría</label>
                                    <input 
                                        type="text" 
                                        x-model="bundleRecipesModal.dispatchNotes"
                                        placeholder="Ej: Ciclo quincenal Marte Nivel 2"
                                        class="w-full px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                                    >
                                </div>
                            </div>

                            <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-100">
                                <div class="text-xs text-slate-600">
                                    Total insumos a liquidar: <strong class="text-slate-900" x-text="selectedBundle.components.reduce((acc, c) => acc + (c.qty * bundleRecipesModal.dispatchQty), 0) + ' unidades físicas'"></strong>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button 
                                        type="button" 
                                        @click="openBomDeficitModal(selectedBundle.id, 200)"
                                        class="px-3 py-1.5 text-xs font-bold text-indigo-700 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-lg transition cursor-pointer flex items-center gap-1.5"
                                        title="Simular deficit de lote grande y generar ordenes hacia Extranet B2B"
                                    >
                                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                        <span>Calcular Deficit Lote (B2B)</span>
                                    </button>
                                    <button 
                                        type="button"
                                        @click="bundleRecipesModal.isOpen = false; openKardexDrawer();"
                                        class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 rounded-lg hover:bg-slate-200 transition cursor-pointer"
                                    >
                                        Auditar Kardex
                                    </button>
                                    <button 
                                        type="button"
                                        @click="liquidateBundle(selectedBundle.id, bundleRecipesModal.dispatchQty, bundleRecipesModal.dispatchReference, bundleRecipesModal.dispatchNotes)"
                                        class="px-4 py-1.5 text-xs font-bold text-white bg-violet-600 hover:bg-violet-700 rounded-lg shadow-xs transition cursor-pointer flex items-center gap-1.5"
                                        :disabled="bundleRecipesModal.dispatchQty <= 0 || bundleRecipesModal.dispatchQty > calculateBundleStock(selectedBundle)"
                                        :class="bundleRecipesModal.dispatchQty <= 0 || bundleRecipesModal.dispatchQty > calculateBundleStock(selectedBundle) ? 'opacity-50 cursor-not-allowed' : ''"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Despachar y Liquidar en Kardex</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Calculo predictivo ROP y deduccion atomica inmutable segun especificacion backend Fase 3.5</span>
                    </div>
                    <button 
                        @click="bundleRecipesModal.isOpen = false"
                        type="button" 
                        class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                    >
                        Cerrar Ventana
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL DE AUDITORIA DE DEFICIT BOM & OCS B2B (TASK-INT-11)          -->
    <!-- ================================================================= -->
    <div 
        x-show="bomDeficitState.isOpen" 
        class="fixed inset-0 z-[90] overflow-y-auto"
        style="z-index: 90; display: none;"
        aria-labelledby="bom-deficit-modal-title" 
        role="dialog" 
        aria-modal="true"
        @keydown.escape.window="closeBomDeficitModal()"
    >
        <div 
            x-show="bomDeficitState.isOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="closeBomDeficitModal()"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity cursor-pointer"
        ></div>

        <div class="min-h-full flex items-center justify-center p-4 text-center sm:p-0">
            <div 
                x-show="bomDeficitState.isOpen"
                x-transition:enter="ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-5xl border border-slate-200 flex flex-col max-h-[92vh]"
                @click.stop
            >
                <!-- Encabezado Modal -->
                <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 px-6 py-4 flex items-center justify-between border-b border-indigo-900">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 id="bom-deficit-modal-title" class="text-base font-bold text-white leading-none">Auditoria de Deficit BOM & Disparo Automatico B2B</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/30 text-indigo-200 border border-indigo-400/40">TASK-INT-11</span>
                            </div>
                            <p class="text-xs text-slate-300 mt-1">Calculo reactivo de faltantes de insumos por lote y emision de Ordenes de Compra divididas por taller</p>
                        </div>
                    </div>
                    <button @click="closeBomDeficitModal()" class="text-slate-400 hover:text-white p-1 rounded-lg transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Barra de Parametros del Lote -->
                <div class="bg-slate-50 px-6 py-3 border-b border-slate-200 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                    <div class="sm:col-span-4">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Formula / Combo Seleccionado</label>
                        <select 
                            x-model="bomDeficitState.selectedBundleId" 
                            @change="calculateLotDeficit()"
                            class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                        >
                            <template x-for="b in productBundles" :key="b.id">
                                <option :value="b.id" x-text="b.sku + ' - ' + b.name"></option>
                            </template>
                        </select>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Cajas a Fabricar (Lote)</label>
                        <div class="relative">
                            <input 
                                type="number" 
                                min="1" 
                                max="1000"
                                x-model.number="bomDeficitState.targetQty" 
                                @input="calculateLotDeficit()"
                                class="w-full text-xs font-bold bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                            >
                            <span class="absolute right-2.5 top-1.5 text-[10px] text-slate-400 font-semibold">cajas</span>
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Referencia de Lote</label>
                        <input 
                            type="text" 
                            x-model="bomDeficitState.batchReference"
                            placeholder="SUB-BATCH-2026-09A"
                            class="w-full text-xs font-medium bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                        >
                    </div>

                    <div class="sm:col-span-2 flex items-end">
                        <button 
                            type="button" 
                            @click="calculateLotDeficit()"
                            class="w-full px-3 py-1.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg transition cursor-pointer flex items-center justify-center gap-1.5 shadow-xs"
                        >
                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Recalcular</span>
                        </button>
                    </div>
                </div>

                <!-- Resumen de KPIs de Balance -->
                <template x-if="bomDeficitState.result">
                    <div class="px-6 py-3 bg-indigo-50/50 border-b border-indigo-100 grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-white p-2.5 rounded-lg border border-slate-200/80 shadow-2xs">
                            <span class="text-[10px] font-bold uppercase text-slate-500 block">Objetivo de Ensamble</span>
                            <div class="text-base font-extrabold text-slate-900 mt-0.5" x-text="bomDeficitState.result.targetQty + ' Cajas'"></div>
                            <span class="text-[10px] text-slate-500 font-mono" x-text="bomDeficitState.result.bundleSku"></span>
                        </div>
                        <div class="bg-white p-2.5 rounded-lg border border-slate-200/80 shadow-2xs">
                            <span class="text-[10px] font-bold uppercase text-slate-500 block">Insumos Requeridos</span>
                            <div class="text-base font-extrabold text-slate-900 mt-0.5" x-text="bomDeficitState.result.totalRequiredPieces + ' uds'"></div>
                            <span class="text-[10px] text-indigo-600 font-medium" x-text="bomDeficitState.result.totalAvailablePieces + ' disponibles'"></span>
                        </div>
                        <div class="bg-white p-2.5 rounded-lg border border-slate-200/80 shadow-2xs">
                            <span class="text-[10px] font-bold uppercase text-slate-500 block">Deficit Fisico</span>
                            <div class="text-base font-extrabold mt-0.5" :class="bomDeficitState.result.totalDeficitPieces > 0 ? 'text-rose-600' : 'text-emerald-600'" x-text="bomDeficitState.result.totalDeficitPieces + ' piezas'"></div>
                            <span class="text-[10px] font-semibold" :class="bomDeficitState.result.totalDeficitPieces > 0 ? 'text-rose-500' : 'text-emerald-600'" x-text="bomDeficitState.result.totalDeficitPieces > 0 ? 'Insumos Faltantes' : 'Lote Cubierto al 100%'"></span>
                        </div>
                        <div class="bg-white p-2.5 rounded-lg border border-slate-200/80 shadow-2xs">
                            <span class="text-[10px] font-bold uppercase text-slate-500 block">Inversion Reposicion</span>
                            <div class="text-base font-extrabold text-indigo-700 mt-0.5" x-text="'$' + Object.values(bomDeficitState.result.supplierGroups).reduce((acc, g) => acc + g.totalEstimatedCost, 0).toLocaleString('es-MX', { minimumFractionDigits: 2 }) + ' MXN'"></div>
                            <span class="text-[10px] text-slate-500" x-text="Object.keys(bomDeficitState.result.supplierGroups).length + ' talleres receptores'"></span>
                        </div>
                    </div>
                </template>

                <!-- Contenido Principal: Analisis a 2 Columnas -->
                <div class="p-6 overflow-y-auto flex-1 grid grid-cols-1 lg:grid-cols-12 gap-6 bg-slate-100/60">
                    
                    <!-- Columna 1: Auditoria Insumo por Insumo (BOM Breakdown) -->
                    <div class="lg:col-span-6 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">1. Balance de Insumos Fisicos</span>
                            <span class="text-[11px] text-slate-500 font-mono">Existencia vs Requerimiento</span>
                        </div>

                        <template x-if="bomDeficitState.result">
                            <div class="space-y-2.5">
                                <template x-for="comp in bomDeficitState.result.components" :key="comp.sku">
                                    <div 
                                        class="bom-deficit-card p-3 rounded-xl border bg-white shadow-2xs transition"
                                        :class="comp.deficitQty > 0 ? 'deficit' : 'covered'"
                                    >
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <div class="font-bold text-xs text-slate-900" x-text="comp.name"></div>
                                                <div class="text-[11px] font-mono text-slate-500 flex items-center gap-2 mt-0.5">
                                                    <span x-text="comp.sku + (comp.size !== 'std' ? ' • ' + comp.size.toUpperCase() : '')"></span>
                                                    <span class="text-slate-400">|</span>
                                                    <span class="text-indigo-600 font-medium" x-text="comp.supplierName"></span>
                                                </div>
                                            </div>
                                            <span 
                                                class="badge-bom-deficit" 
                                                x-show="comp.deficitQty > 0"
                                                x-text="'-' + comp.deficitQty + ' Faltantes'"
                                            ></span>
                                            <span 
                                                class="badge-bom-covered" 
                                                x-show="comp.deficitQty === 0"
                                                x-text="'Stock Cubierto'"
                                            ></span>
                                        </div>

                                        <div class="mt-2 pt-2 border-t border-slate-100 grid grid-cols-4 gap-2 text-center text-xs">
                                            <div class="bg-slate-50 p-1.5 rounded">
                                                <span class="text-[9px] font-bold text-slate-400 uppercase block">Cant/Caja</span>
                                                <span class="font-bold text-slate-700 font-mono" x-text="comp.qtyPerBox + ' ud'"></span>
                                            </div>
                                            <div class="bg-slate-50 p-1.5 rounded">
                                                <span class="text-[9px] font-bold text-slate-400 uppercase block">Bodega</span>
                                                <span class="font-bold text-slate-700 font-mono" x-text="comp.availableStock + ' uds'"></span>
                                            </div>
                                            <div class="bg-slate-50 p-1.5 rounded">
                                                <span class="text-[9px] font-bold text-slate-400 uppercase block">Requerido</span>
                                                <span class="font-bold text-indigo-700 font-mono" x-text="comp.requiredQty + ' uds'"></span>
                                            </div>
                                            <div class="p-1.5 rounded" :class="comp.deficitQty > 0 ? 'bg-rose-100/70 text-rose-800' : 'bg-emerald-100/70 text-emerald-800'">
                                                <span class="text-[9px] font-bold uppercase block">Deficit</span>
                                                <span class="font-extrabold font-mono" x-text="comp.deficitQty + ' uds'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Columna 2: Generador de Ordenes de Compra a Extranet B2B -->
                    <div class="lg:col-span-6 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">2. Ordenes Planificadas por Taller</span>
                            <span class="text-[11px] text-slate-500 font-mono">Extranet B2B (Hub 7)</span>
                        </div>

                        <template x-if="bomDeficitState.result">
                            <div class="space-y-4">
                                <template x-for="(group, sId) in bomDeficitState.result.supplierGroups" :key="sId">
                                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs">
                                        <div class="supplier-group-header">
                                            <div class="flex items-center gap-2">
                                                <span x-text="group.supplierName"></span>
                                                <span class="text-[9px] px-1.5 py-0.2 bg-indigo-500/40 text-indigo-200 rounded font-mono" x-text="group.supplierId"></span>
                                            </div>
                                            <span class="font-mono text-xs" x-text="group.totalDeficitPieces + ' pzas por comprar'"></span>
                                        </div>

                                        <div class="p-3.5 space-y-2.5">
                                            <div class="flex items-center justify-between text-xs">
                                                <span class="text-slate-500">Categoria Maquila:</span>
                                                <span class="font-semibold text-slate-800" x-text="group.category"></span>
                                            </div>
                                            <div class="flex items-center justify-between text-xs">
                                                <span class="text-slate-500">Insumos incluidos en OC:</span>
                                                <span class="font-mono font-bold text-slate-800" x-text="group.items.filter(i => i.deficitQty > 0).length + ' SKUs deficitarios'"></span>
                                            </div>
                                            <div class="flex items-center justify-between text-xs">
                                                <span class="text-slate-500">Monto estimado de la orden:</span>
                                                <span class="font-mono font-bold text-indigo-700 text-sm" x-text="'$' + group.totalEstimatedCost.toLocaleString('es-MX', { minimumFractionDigits: 2 }) + ' MXN'"></span>
                                            </div>

                                            <div class="bg-slate-50 p-2 rounded-lg border border-slate-200/60 text-[11px] space-y-1">
                                                <div class="font-bold text-slate-600 uppercase text-[9px] tracking-wider">Desglose de Piezas Faltantes:</div>
                                                <template x-for="item in group.items" :key="item.sku">
                                                    <div class="flex items-center justify-between font-mono" x-show="item.deficitQty > 0">
                                                        <span class="text-slate-600 truncate max-w-[200px]" x-text="item.name + ' (' + item.sku + ')'"></span>
                                                        <span class="text-rose-600 font-bold" x-text="item.deficitQty + ' uds'"></span>
                                                    </div>
                                                </template>
                                                <div x-show="group.totalDeficitPieces === 0" class="text-emerald-700 font-semibold text-center py-1">
                                                    Sin deficit para este proveedor. Stock de bodega suficiente.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Estado de Ordenes Generadas Feedback -->
                                <template x-if="bomDeficitState.lastGeneratedOrders && bomDeficitState.lastGeneratedOrders.length > 0">
                                    <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl space-y-2">
                                        <div class="flex items-center gap-2 text-emerald-800 font-bold text-xs">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Ordenes de Compra Inyectadas en Extranet B2B</span>
                                        </div>
                                        <div class="text-[11px] text-emerald-700">
                                            Folio de auditoria: <strong class="font-mono text-slate-900" x-text="bomDeficitState.lastProcurementCode"></strong>
                                        </div>
                                        <div class="space-y-1.5 pt-1">
                                            <template x-for="genPo in bomDeficitState.lastGeneratedOrders" :key="genPo.poCode">
                                                <div class="bg-white p-2 rounded border border-emerald-200 flex items-center justify-between text-xs">
                                                    <div>
                                                        <span class="font-mono font-bold text-indigo-700" x-text="genPo.poCode"></span>
                                                        <span class="text-slate-500 text-[10px] ml-1.5" x-text="'(' + genPo.supplierName + ')'"></span>
                                                    </div>
                                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded text-[10px] font-bold" x-text="genPo.totalPieces + ' pzas • En Taller'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <!-- Boton de Generacion en 1 Clic -->
                                <div class="pt-2">
                                    <button 
                                        type="button"
                                        @click="generateB2BPurchaseOrders()"
                                        class="w-full py-2.5 px-4 text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 rounded-xl shadow-md transition cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="bomDeficitState.isProcessing || !bomDeficitState.result || !bomDeficitState.result.hasDeficit"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                        <span x-text="bomDeficitState.isProcessing ? 'Generando Ordenes...' : 'Generar OCs y Enviar a Portal de Proveedores en 1 Clic'"></span>
                                    </button>
                                    <p class="text-[10px] text-slate-500 text-center mt-1.5">
                                        Publica borradores en Extranet B2B y crea tareas de monitoreo de entrega en Action Center Hub 1.
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Integracion inter-hub con Extranet B2B (Fase 9) y Action Center Hub 1 segun especificacion Fase 3.5</span>
                    </div>
                    <button 
                        @click="closeBomDeficitModal()"
                        type="button" 
                        class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                    >
                        Cerrar Ventana
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL DE TONOS DE COSMETICA (TASK-INV-09)                         -->
    <!-- ================================================================= -->
    <div 
        x-show="cosmeticShadesModal.isOpen" 
        class="fixed inset-0 z-[75] overflow-y-auto" 
        style="display: none;"
        @keydown.escape.window="closeCosmeticShadesModal()"
    >
        <!-- Backdrop oscuro -->
        <div 
            x-show="cosmeticShadesModal.isOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            @click="closeCosmeticShadesModal()"
        ></div>

        <!-- Dialog modal -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div 
                x-show="cosmeticShadesModal.isOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-slate-200"
                @click.stop
            >
                <!-- Header -->
                <div class="bg-gradient-to-r from-pink-900 via-pink-800 to-slate-900 px-6 py-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-pink-500/20 border border-pink-400/30 flex items-center justify-center text-pink-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold tracking-tight">Catálogo Maestro de Tonos de Cosmética</h3>
                            <p class="text-xs text-pink-200/80">Gestión de muestras cromáticas HEX, subtonos dermatológicos y disponibilidad operativa</p>
                        </div>
                    </div>
                    <button @click="closeCosmeticShadesModal()" class="text-slate-400 hover:text-white p-1.5 rounded-lg transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Body en 2 Columnas -->
                <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 bg-slate-50/50">
                    <!-- Formulario de Registro / Edición (col-span-5) -->
                    <div class="lg:col-span-5 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs flex flex-col gap-4">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider" x-text="cosmeticShadesModal.isEditing ? 'Editar Tono ' + cosmeticShadesModal.form.code : 'Registrar Nuevo Tono'"></h4>
                            <span class="text-[10px] font-semibold text-pink-600 bg-pink-50 px-2 py-0.5 rounded-full border border-pink-100" x-text="cosmeticShadesModal.isEditing ? 'Modo Edición' : 'Nuevo Registro'"></span>
                        </div>

                        <!-- Código del Tono -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Código Identificador</label>
                            <input 
                                type="text" 
                                x-model="cosmeticShadesModal.form.code" 
                                placeholder="Ej. 07 o C-01" 
                                class="w-full text-xs font-bold px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 uppercase transition"
                            >
                            <p class="text-[10px] text-slate-400 mt-1">Identificador único alfanumérico para el desglose de inventario.</p>
                        </div>

                        <!-- Nombre Comercial -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre Comercial del Tono</label>
                            <input 
                                type="text" 
                                x-model="cosmeticShadesModal.form.name" 
                                placeholder="Ej. Beige Almendra, Bronce Cálido" 
                                class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition"
                            >
                        </div>

                        <!-- Selector Cromático HEX -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Muestra Cromática (Color HEX)</label>
                            <div class="flex items-center gap-3">
                                <input 
                                    type="color" 
                                    x-model="cosmeticShadesModal.form.hex_color" 
                                    class="w-10 h-10 rounded-lg cursor-pointer border border-slate-300 p-0.5 bg-white shadow-2xs flex-shrink-0"
                                >
                                <input 
                                    type="text" 
                                    x-model="cosmeticShadesModal.form.hex_color" 
                                    placeholder="#E8BEA2" 
                                    maxlength="7"
                                    class="flex-1 text-xs font-mono uppercase px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition"
                                >
                            </div>
                        </div>

                        <!-- Previsualización en Vivo -->
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center gap-3">
                            <span class="cosmetic-swatch-preview w-8 h-8 rounded-full border border-black/15 shadow-xs flex-shrink-0" :style="'background: ' + (cosmeticShadesModal.form.hex_color || '#E8BEA2')"></span>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-bold text-slate-800 truncate" x-text="cosmeticShadesModal.form.code ? ('Tono ' + cosmeticShadesModal.form.code + ' - ' + (cosmeticShadesModal.form.name || 'Sin nombre')) : 'Vista Previa'"></div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[10px] font-mono text-slate-500" x-text="cosmeticShadesModal.form.hex_color || '#E8BEA2'"></span>
                                    <span class="text-[9px] px-1.5 py-0.2 rounded font-semibold" :class="getUndertoneBadgeClass(cosmeticShadesModal.form.undertone)" x-text="getUndertoneLabel(cosmeticShadesModal.form.undertone)"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Subtono Dermatológico -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Subtono Dermatológico</label>
                            <select 
                                x-model="cosmeticShadesModal.form.undertone" 
                                class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 bg-white transition"
                            >
                                <option value="warm">Cálido (Warm / Dorado / Melocotón)</option>
                                <option value="cool">Frío (Cool / Rosado / Azulado)</option>
                                <option value="neutral">Neutro (Neutral / Equilibrado)</option>
                            </select>
                        </div>

                        <!-- Estado Activo -->
                        <div class="pt-1">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input 
                                    type="checkbox" 
                                    x-model="cosmeticShadesModal.form.is_active" 
                                    class="rounded border-slate-300 text-pink-600 focus:ring-pink-500 w-4 h-4 cursor-pointer"
                                >
                                <span class="text-xs font-medium text-slate-700">Tono activo en producción y catálogo</span>
                            </label>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex items-center gap-2 pt-2">
                            <button 
                                type="button" 
                                @click="saveCosmeticShade()" 
                                class="flex-1 py-2 px-4 bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs rounded-lg transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-text="cosmeticShadesModal.isEditing ? 'Actualizar Tono' : 'Guardar Tono'"></span>
                            </button>
                            <button 
                                x-show="cosmeticShadesModal.isEditing" 
                                type="button" 
                                @click="cosmeticShadesModal.isEditing = false; cosmeticShadesModal.editingId = null; cosmeticShadesModal.form = { code: '', name: '', hex_color: '#E8BEA2', undertone: 'neutral', is_active: true };" 
                                class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs rounded-lg transition cursor-pointer"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>

                    <!-- Lista / Cuadrícula de Tonos Existentes (col-span-7) -->
                    <div class="lg:col-span-7 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs flex flex-col">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                            <div class="flex items-center gap-2">
                                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Tonos Configurados</h4>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200" x-text="cosmeticShades.length + ' en catálogo'"></span>
                            </div>
                            <span class="text-[11px] text-slate-400" x-text="activeCosmeticShades.length + ' activos para venta'"></span>
                        </div>

                        <!-- Grid de Tonos con Scroll -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-[460px] overflow-y-auto pr-1">
                            <template x-for="shade in cosmeticShades" :key="shade.id">
                                <div 
                                    class="cosmetic-shade-card p-2.5 rounded-xl border transition flex items-center justify-between"
                                    :class="cosmeticShadesModal.editingId === shade.id ? 'border-pink-500 bg-pink-50/30 ring-1 ring-pink-500/20' : (shade.is_active ? 'border-slate-200 bg-white hover:border-pink-300' : 'border-slate-200 bg-slate-50 opacity-60 hover:opacity-100')"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="cosmetic-swatch-20 flex-shrink-0" :style="'background: ' + shade.hex_color"></span>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs font-bold text-slate-800" x-text="'Tono ' + shade.code"></span>
                                                <span class="text-[9px] px-1.5 py-0.2 rounded font-semibold" :class="getUndertoneBadgeClass(shade.undertone)" x-text="getUndertoneLabel(shade.undertone)"></span>
                                            </div>
                                            <div class="text-[11px] text-slate-500 truncate max-w-[130px]" x-text="shade.name"></div>
                                            <div class="text-[10px] font-mono text-slate-400" x-text="shade.hex_color"></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <!-- Botón Activar / Pausar -->
                                        <button 
                                            type="button" 
                                            @click="toggleCosmeticShadeStatus(shade)" 
                                            :class="shade.is_active ? 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border-emerald-200' : 'text-slate-500 bg-slate-100 hover:bg-slate-200 border-slate-200'" 
                                            class="px-1.5 py-0.5 rounded text-[10px] font-bold border transition cursor-pointer"
                                            :title="shade.is_active ? 'Desactivar de producción' : 'Activar en producción'"
                                        >
                                            <span x-text="shade.is_active ? 'Activo' : 'Pausa'"></span>
                                        </button>
                                        <!-- Botón Editar -->
                                        <button 
                                            type="button" 
                                            @click="editCosmeticShade(shade)" 
                                            class="p-1 text-slate-400 hover:text-indigo-600 rounded transition cursor-pointer"
                                            title="Editar información del tono"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <!-- Botón Eliminar -->
                                        <button 
                                            type="button" 
                                            @click="deleteCosmeticShade(shade.id)" 
                                            class="p-1 text-slate-400 hover:text-rose-600 rounded transition cursor-pointer"
                                            title="Eliminar tono del catálogo"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Sincronización atómica con matrices de inventario y selectores cromáticos del storefront</span>
                    </div>
                    <button 
                        @click="closeCosmeticShadesModal()" 
                        type="button" 
                        class="px-4 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                    >
                        Cerrar Ventana
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL DE CARANCELES / TELEMETRÍA DE CARRITOS SIMULTÁNEOS (TASK-INT-06) -->
    <!-- ================================================================= -->
    <div 
        x-show="stockLocksModalOpen" 
        class="fixed inset-0 z-[80] overflow-y-auto" 
        style="display: none;"
        @keydown.escape.window="closeStockLocksModal()"
    >
        <!-- Backdrop -->
        <div 
            x-show="stockLocksModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            @click="closeStockLocksModal()"
        ></div>

        <div class="min-h-screen px-4 text-center flex items-center justify-center p-4">
            <div 
                x-show="stockLocksModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="inline-block w-full max-w-4xl text-left align-middle transition-all transform bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-200"
            >
                <!-- Header -->
                <div class="bg-gradient-to-r from-rose-50 via-slate-50 to-white px-6 py-4 border-b border-rose-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 border border-rose-200 flex items-center justify-center text-rose-600 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Carritos Simultáneos y Reservas Activas de Stock</h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">Telemetría en tiempo real de prendas retenidas con TTL configurable de 5 minutos</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            @click="cleanExpiredLocks()" 
                            class="px-2.5 py-1 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition cursor-pointer flex items-center gap-1"
                            title="Purgar reservas expiradas"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Refrescar TTL</span>
                        </button>
                        <button 
                            type="button" 
                            @click="closeStockLocksModal()" 
                            class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Resumen KPI Bar -->
                <div class="grid grid-cols-3 gap-4 px-6 py-3 bg-slate-50 border-b border-slate-200 text-center">
                    <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Sesiones / Carritos Activos</div>
                        <div class="text-lg font-black text-rose-600 mt-0.5" x-text="activeStockLocksCount"></div>
                    </div>
                    <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Prendas Totales Bloqueadas</div>
                        <div class="text-lg font-black text-amber-600 mt-0.5" x-text="activeLockedUnitsTotal + ' uds'"></div>
                    </div>
                    <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tiempo Límite Estándar</div>
                        <div class="text-lg font-black text-slate-800 mt-0.5">5 Minutos (300s)</div>
                    </div>
                </div>

                <!-- Tabla de Bloqueos -->
                <div class="p-6 max-h-[50vh] overflow-y-auto">
                    <template x-if="activeStockLocksCount === 0">
                        <div class="text-center py-10 text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            <p class="text-sm font-bold text-slate-700">No hay carritos activos con stock bloqueado en este momento.</p>
                            <p class="text-xs text-slate-500 mt-1">El 100% de las unidades disponibles están libres para venta inmediata.</p>
                        </div>
                    </template>
                    <template x-if="activeStockLocksCount > 0">
                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="w-full text-xs text-left border-collapse stock-locks-table">
                                <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                                    <tr>
                                        <th class="p-2.5">Sesión / Origen</th>
                                        <th class="p-2.5">Prenda / SKU</th>
                                        <th class="p-2.5 text-center">Talla</th>
                                        <th class="p-2.5 text-center">Cantidad</th>
                                        <th class="p-2.5 text-center">TTL Restante</th>
                                        <th class="p-2.5 text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    <template x-for="lock in activeStockLocks" :key="lock.id">
                                        <tr class="hover:bg-rose-50/40 transition">
                                            <td class="p-2.5">
                                                <div class="font-mono font-bold text-slate-800" x-text="lock.sessionId"></div>
                                                <div class="text-[10px] text-slate-400" x-text="lock.ipAddress || 'Storefront'"></div>
                                            </td>
                                            <td class="p-2.5">
                                                <div class="font-bold text-slate-900" x-text="lock.name"></div>
                                                <div class="font-mono text-[10px] text-indigo-600" x-text="lock.sku"></div>
                                            </td>
                                            <td class="p-2.5 text-center">
                                                <span class="px-2 py-0.5 rounded bg-slate-100 font-bold text-slate-700" x-text="String(lock.size).toUpperCase()"></span>
                                            </td>
                                            <td class="p-2.5 text-center">
                                                <span class="font-extrabold text-slate-900" x-text="lock.quantity"></span>
                                            </td>
                                            <td class="p-2.5 text-center">
                                                <span class="badge-stock-locked stock-lock-pulse px-2 py-0.5 rounded font-mono font-bold" x-text="formatLockTimer(lock.expiresAt)"></span>
                                            </td>
                                            <td class="p-2.5 text-right">
                                                <button 
                                                    type="button" 
                                                    @click="forceReleaseLock(lock.id)" 
                                                    class="px-2 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded text-[10px] font-bold transition shadow-2xs cursor-pointer"
                                                    title="Liberar inmediatamente al stock disponible"
                                                >
                                                    Liberar
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span>El stock bloqueado en carrito se descuenta temporalmente del catálogo público para evitar sobreventas simultáneas.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <template x-if="activeStockLocksCount > 0">
                            <button 
                                type="button" 
                                @click="releaseAllStockLocks()" 
                                class="px-3 py-1 text-xs font-bold text-rose-700 hover:text-rose-900 hover:bg-rose-100 rounded-lg transition cursor-pointer"
                            >
                                Liberar Todos
                            </button>
                        </template>
                        <button 
                            @click="closeStockLocksModal()" 
                            type="button" 
                            class="px-4 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL DE RECEPCIÓN DE MERCANCÍA (INBOUND RECEIPT & COTEJO FÍSICO) -->
    <!-- ================================================================= -->
    <div 
        x-show="receiveOrderModalOpen" 
        class="fixed inset-0 z-[75] overflow-y-auto" 
        style="display: none;"
        @keydown.escape.window="receiveOrderModalOpen = false"
    >
        <!-- Backdrop oscuro -->
        <div 
            x-show="receiveOrderModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            @click="receiveOrderModalOpen = false"
        ></div>

        <div class="min-h-screen px-4 text-center flex items-center justify-center p-4">
            <div 
                x-show="receiveOrderModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="inline-block w-full max-w-2xl bg-white rounded-2xl text-left shadow-2xl transform transition-all border border-slate-200 overflow-hidden relative"
                @click.stop
            >
                <!-- Header del Modal -->
                <div class="px-6 py-4 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-600/30 border border-indigo-400/30 flex items-center justify-center text-indigo-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                <span>Recepción Inbound de Mercancía</span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-indigo-500/30 text-indigo-200 border border-indigo-400/40" x-text="receivingOrder ? '#' + receivingOrder.id : ''"></span>
                            </h3>
                            <p class="text-xs text-slate-300">Cotejo físico e ingreso inmediato a existencias de rack</p>
                        </div>
                    </div>
                    <button @click="receiveOrderModalOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Contenido del Modal -->
                <div class="p-6 space-y-5">
                    <!-- Tarjeta Info Orden -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Proveedor</span>
                            <span class="font-bold text-slate-800" x-text="receivingOrder?.supplier"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Transportista / Guía</span>
                            <span class="font-medium text-slate-700 truncate block" x-text="receivingOrder?.carrier"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Piezas Esperadas</span>
                            <span class="font-bold text-indigo-600 text-sm" x-text="receivingOrder ? receivingOrder.qty + ' uds' : '0'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Inversión Lote</span>
                            <span class="font-bold text-slate-800" x-text="receivingOrder ? '$' + receivingOrder.totalCost.toLocaleString('es-MX', {minimumFractionDigits: 2}) : '$0.00'"></span>
                        </div>
                    </div>

                    <!-- Producto a Recibir -->
                    <div class="p-3 bg-indigo-50/50 rounded-xl border border-indigo-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider block">Artículo que ingresa</span>
                            <h4 class="font-bold text-slate-900 text-sm" x-text="receivingOrder?.productName"></h4>
                            <span class="text-xs text-slate-500 font-mono" x-text="'SKU: ' + (receivingOrder?.sku || '')"></span>
                        </div>
                        <button 
                            type="button" 
                            @click="setReceivingSizesFull()"
                            class="px-3 py-1 bg-white hover:bg-slate-100 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-200 shadow-3xs transition cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5 inline-block mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>100% Recibido Conforme
                        </button>
                    </div>

                    <!-- Matriz de Tallas a Cotejar -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-700">Cotejo Físico por Talla (Piezas Recibidas en Mano)</label>
                            <span class="text-xs font-bold text-indigo-600">Total a Ingresar: <span x-text="calculateReceivingTotal()"></span> / <span x-text="receivingOrder?.qty || 0"></span> uds</span>
                        </div>
                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2.5">
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-center">
                                <span class="text-[11px] font-bold text-slate-600 block mb-1">CH</span>
                                <span class="text-[10px] text-slate-400 block mb-1">Esp: <span x-text="receivingOrder?.sizes?.ch || 0"></span></span>
                                <input type="number" min="0" x-model.number="receivingSizes.ch" class="w-full text-center bg-white border border-slate-200 rounded py-1 font-bold text-slate-800 text-sm focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            </div>
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-center">
                                <span class="text-[11px] font-bold text-indigo-600 inline-flex items-center justify-center gap-0.5 mb-1">M <svg class="w-2.5 h-2.5 text-amber-500 fill-amber-400" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>
                                <span class="text-[10px] text-slate-400 block mb-1">Esp: <span x-text="receivingOrder?.sizes?.m || 0"></span></span>
                                <input type="number" min="0" x-model.number="receivingSizes.m" class="w-full text-center bg-white border border-indigo-300 rounded py-1 font-bold text-slate-800 text-sm focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            </div>
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-center">
                                <span class="text-[11px] font-bold text-indigo-600 inline-flex items-center justify-center gap-0.5 mb-1">G <svg class="w-2.5 h-2.5 text-amber-500 fill-amber-400" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>
                                <span class="text-[10px] text-slate-400 block mb-1">Esp: <span x-text="receivingOrder?.sizes?.g || 0"></span></span>
                                <input type="number" min="0" x-model.number="receivingSizes.g" class="w-full text-center bg-white border border-indigo-300 rounded py-1 font-bold text-slate-800 text-sm focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            </div>
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-center">
                                <span class="text-[11px] font-bold text-slate-600 block mb-1">XL</span>
                                <span class="text-[10px] text-slate-400 block mb-1">Esp: <span x-text="receivingOrder?.sizes?.xl || 0"></span></span>
                                <input type="number" min="0" x-model.number="receivingSizes.xl" class="w-full text-center bg-white border border-slate-200 rounded py-1 font-bold text-slate-800 text-sm focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            </div>
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-center">
                                <span class="text-[11px] font-bold text-purple-700 block mb-1">2XL</span>
                                <span class="text-[10px] text-slate-400 block mb-1">Esp: <span x-text="receivingOrder?.sizes?.['2xl'] || 0"></span></span>
                                <input type="number" min="0" x-model.number="receivingSizes['2xl']" class="w-full text-center bg-white border border-slate-200 rounded py-1 font-bold text-slate-800 text-sm focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            </div>
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-center">
                                <span class="text-[11px] font-bold text-purple-700 block mb-1">3XL</span>
                                <span class="text-[10px] text-slate-400 block mb-1">Esp: <span x-text="receivingOrder?.sizes?.['3xl'] || 0"></span></span>
                                <input type="number" min="0" x-model.number="receivingSizes['3xl']" class="w-full text-center bg-white border border-slate-200 rounded py-1 font-bold text-slate-800 text-sm focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Control Sanitario en Recepcion Inbound (TASK-INV-06) -->
                    <template x-if="receivingRequiresSanitary">
                        <div class="p-3.5 bg-purple-50/70 rounded-xl border border-purple-200 text-xs space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="p-1 bg-purple-100 text-purple-700 rounded border border-purple-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </span>
                                    <div>
                                        <span class="font-bold text-purple-900 block">Trazabilidad Sanitaria Inbound</span>
                                        <span class="text-[10px] text-purple-700">Normativa Sanitaria DIGEMID / Farmacovigilancia y Cosmetica</span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-200/70 text-purple-800 font-mono">Lote Obligatorio</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="text-[10px] font-bold text-purple-800 uppercase tracking-wider block mb-1">Lote de Fabricacion</label>
                                    <input 
                                        type="text" 
                                        x-model="receivingBatchNumber" 
                                        placeholder="LOT-2026-XXXX"
                                        class="w-full text-xs font-mono font-bold px-2.5 py-1.5 bg-white border border-purple-200 rounded-lg focus:ring-1 focus:ring-purple-500 focus:outline-none text-purple-900"
                                    >
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-purple-800 uppercase tracking-wider block mb-1">Fecha de Caducidad</label>
                                    <input 
                                        type="date" 
                                        x-model="receivingExpirationDate" 
                                        class="w-full text-xs font-medium px-2.5 py-1.5 bg-white border border-purple-200 rounded-lg focus:ring-1 focus:ring-purple-500 focus:outline-none text-slate-800"
                                    >
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-purple-800 uppercase tracking-wider block mb-1">PAO (Meses tras Apertura)</label>
                                    <select 
                                        x-model.number="receivingPaoMonths" 
                                        class="w-full text-xs font-medium px-2.5 py-1.5 bg-white border border-purple-200 rounded-lg focus:ring-1 focus:ring-purple-500 focus:outline-none text-slate-800"
                                    >
                                        <option value="6">6 Meses (6M)</option>
                                        <option value="12">12 Meses (12M)</option>
                                        <option value="18">18 Meses (18M)</option>
                                        <option value="24">24 Meses (24M)</option>
                                        <option value="36">36 Meses (36M)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Control Sanitario en Recepcion Inbound (TASK-INV-06) -->
                    <template x-if="receivingRequiresSanitary">
                        <div class="p-3.5 bg-purple-50/70 rounded-xl border border-purple-200 text-xs space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="p-1 bg-purple-100 text-purple-700 rounded border border-purple-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </span>
                                    <div>
                                        <span class="font-bold text-purple-900 block">Trazabilidad Sanitaria Inbound</span>
                                        <span class="text-[10px] text-purple-700">Normativa Sanitaria DIGEMID / Farmacovigilancia y Cosmetica</span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-200/70 text-purple-800 font-mono">Lote Obligatorio</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="text-[10px] font-bold text-purple-800 uppercase tracking-wider block mb-1">Lote de Fabricacion</label>
                                    <input 
                                        type="text" 
                                        x-model="receivingBatchNumber" 
                                        placeholder="LOT-2026-XXXX"
                                        class="w-full text-xs font-mono font-bold px-2.5 py-1.5 bg-white border border-purple-200 rounded-lg focus:ring-1 focus:ring-purple-500 focus:outline-none text-purple-900"
                                    >
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-purple-800 uppercase tracking-wider block mb-1">Fecha de Caducidad</label>
                                    <input 
                                        type="date" 
                                        x-model="receivingExpirationDate" 
                                        class="w-full text-xs font-medium px-2.5 py-1.5 bg-white border border-purple-200 rounded-lg focus:ring-1 focus:ring-purple-500 focus:outline-none text-slate-800"
                                    >
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-purple-800 uppercase tracking-wider block mb-1">PAO (Meses tras Apertura)</label>
                                    <select 
                                        x-model.number="receivingPaoMonths" 
                                        class="w-full text-xs font-medium px-2.5 py-1.5 bg-white border border-purple-200 rounded-lg focus:ring-1 focus:ring-purple-500 focus:outline-none text-slate-800"
                                    >
                                        <option value="6">6 Meses (6M)</option>
                                        <option value="12">12 Meses (12M)</option>
                                        <option value="18">18 Meses (18M)</option>
                                        <option value="24">24 Meses (24M)</option>
                                        <option value="36">36 Meses (36M)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Impacto en Valuación Contable: CPP (TASK-INV-04) -->
                    <div class="p-3.5 bg-slate-900 text-white rounded-xl border border-slate-800 text-xs">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="p-1 bg-indigo-500/20 text-indigo-400 rounded border border-indigo-500/30">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </span>
                                <span class="font-bold text-slate-200">Impacto en Valuación Contable (CPP)</span>
                            </div>
                            <span class="text-[10px] text-indigo-300 font-mono">Fórmula Oficial CPP</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="bg-slate-800/80 p-2 rounded-lg border border-slate-700/60">
                                <span class="text-[10px] text-slate-400 block">CPP Anterior</span>
                                <span class="font-bold text-slate-200 text-xs font-mono" x-text="'$' + (getInboundCurrentCpp().toFixed(2)) + ' MXN'"></span>
                            </div>
                            <div class="bg-slate-800/80 p-2 rounded-lg border border-slate-700/60">
                                <span class="text-[10px] text-slate-400 block">Costo Lote Entrante</span>
                                <span class="font-bold text-amber-400 text-xs font-mono" x-text="'$' + (getInboundUnitCost().toFixed(2)) + ' MXN'"></span>
                            </div>
                            <div class="bg-indigo-950/80 p-2 rounded-lg border border-indigo-700/60">
                                <span class="text-[10px] text-indigo-300 block font-bold">Nuevo CPP Estimado</span>
                                <span class="font-extrabold text-emerald-400 text-xs font-mono" x-text="'$' + (getEstimatedInboundCPP().toFixed(2)) + ' MXN'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Aviso de Carrito Mixto si aplica -->
                    <template x-if="receivingOrder && receivingOrder.linkedCartId">
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2.5 text-xs">
                            <span class="text-amber-600 mt-0.5 flex items-center justify-center w-5 h-5 bg-amber-100 rounded-full"><svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></span>
                            <div>
                                <span class="font-bold text-amber-900 block">¡Atención de Liberación Inmediata!</span>
                                <p class="text-amber-800 leading-relaxed text-[11px]">
                                    Esta orden contiene la pieza solicitada por <strong><span x-text="receivingOrder.linkedCustomer"></span></strong> en el <strong><span x-text="receivingOrder.linkedCartId"></span></strong>. Al confirmar el ingreso, el carrito mixto se desbloqueará automáticamente y se notificará a Logística para empaque y despacho total.
                                </p>
                            </div>
                        </div>
                    </template>

                    <!-- Notas e Incidencias -->
                    <div>
                        <label class="text-[11px] font-bold text-slate-600 block mb-1">Notas de Recepción / Estado del Paquete</label>
                        <input type="text" x-model="receivingNotes" placeholder="Ej. Embalaje intacto, precintos de seguridad verificados" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:outline-none text-slate-700">
                    </div>
                </div>

                <!-- Footer del Modal -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                    <button 
                        type="button" 
                        @click="receiveOrderModalOpen = false" 
                        class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-lg hover:bg-slate-200 transition cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            @click="openInboundBatchLabels()" 
                            class="px-4 py-2 text-xs font-bold bg-slate-800 hover:bg-slate-900 text-white rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer"
                            title="Confirmar recepcion e imprimir lote de etiquetas termicas para todas las piezas recibidas"
                        >
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Confirmar e Imprimir Etiquetas</span>
                        </button>
                        <button 
                            type="button" 
                            @click="confirmInboundReceipt()" 
                            class="px-5 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer"
                        >
                            <svg class="w-4 h-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Confirmar e Ingresar a Almacén</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL GENERADOR DE ORDEN DE COMPRA FORMAL PARA WHATSAPP           -->
    <!-- ================================================================= -->
    <div 
        x-show="orderExportModalOpen" 
        class="fixed inset-0 z-[75] overflow-y-auto" 
        style="display: none;"
        @keydown.escape.window="orderExportModalOpen = false"
    >
        <!-- Backdrop oscuro -->
        <div 
            x-show="orderExportModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            @click="orderExportModalOpen = false"
        ></div>

        <div class="min-h-screen px-4 text-center flex items-center justify-center p-4">
            <div 
                x-show="orderExportModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="inline-block w-full max-w-3xl bg-white rounded-2xl text-left shadow-2xl transform transition-all border border-slate-200 overflow-hidden relative"
                @click.stop
            >
                <!-- Header WhatsApp -->
                <div class="px-6 py-4 bg-gradient-to-r from-emerald-800 to-teal-900 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/30 border border-emerald-400/30 flex items-center justify-center text-emerald-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Generador de Orden de Compra para WhatsApp</h3>
                            <p class="text-xs text-emerald-200">Formato estructurado con emojis y viñetas para envío rápido a mayoristas</p>
                        </div>
                    </div>
                    <button @click="orderExportModalOpen = false" class="text-emerald-300 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Contenido del Modal -->
                <div class="p-6 space-y-4">
                    <!-- Filtros y Opciones de Exportación -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-bold text-slate-700">Proveedor:</label>
                            <select x-model="exportSupplierFilter" class="text-xs bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 font-semibold text-slate-800 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                <option value="Torzo B2B">Torzo B2B (Mayorista)</option>
                                <option value="Silk & Cotton Iberia SL">Silk & Cotton Iberia SL</option>
                                <option value="ModaIntima Textil MX">ModaIntima Textil MX</option>
                                <option value="all">Consolidado Multimarca (Todos)</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-4 text-xs">
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium text-slate-700">
                                <input type="checkbox" x-model="exportIncludePlanned" class="rounded text-emerald-600 focus:ring-emerald-500">
                                <span>Planificadas (<span x-text="futurePurchaseOrders.filter(o => exportSupplierFilter === 'all' || o.supplier === exportSupplierFilter).length"></span>)</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium text-slate-700">
                                <input type="checkbox" x-model="exportIncludeOnDemand" class="rounded text-emerald-600 focus:ring-emerald-500">
                                <span>On-Demand (<span x-text="customerOnDemandRequests.filter(r => exportSupplierFilter === 'all' || r.supplier === exportSupplierFilter).length"></span>)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Caja de Previsualización Monospace -->
                    <div class="relative">
                        <div class="absolute top-3 right-3 z-10 flex items-center gap-2">
                            <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700">WhatsApp Markdown</span>
                        </div>
                        <textarea 
                            readonly 
                            rows="13"
                            :value="getExportOrderText()" 
                            class="w-full font-mono text-xs p-4 bg-slate-900 text-emerald-300 rounded-xl border border-slate-800 shadow-inner focus:outline-none select-all leading-relaxed"
                            style="resize: none;"
                        ></textarea>
                    </div>
                </div>

                <!-- Footer con Acciones -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <button 
                        type="button" 
                        @click="orderExportModalOpen = false" 
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-lg hover:bg-slate-200 transition cursor-pointer"
                    >
                        Cerrar
                    </button>
                    <div class="w-full sm:w-auto flex items-center justify-end gap-2.5">
                        <button 
                            type="button" 
                            @click="window.print()" 
                            class="px-3.5 py-2 text-xs font-semibold bg-white hover:bg-slate-100 text-slate-700 rounded-xl border border-slate-200 shadow-3xs transition cursor-pointer flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Imprimir / PDF</span>
                        </button>
                        <button 
                            type="button" 
                            @click="copyOrderTextToClipboard()" 
                            class="px-5 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer"
                        >
                            <template x-if="!exportCopiedToast">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                    <span>Copiar para WhatsApp</span>
                                </span>
                            </template>
                            <template x-if="exportCopiedToast">
                                <span class="flex items-center gap-1.5 text-white">
                                    <svg class="w-4 h-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>¡Copiado con Éxito!</span>
                                </span>
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- DRAWER LATERAL: KARDEX & AUDITORÍA DE MOVIMIENTOS MULTICANAL      -->
    <!-- ================================================================= -->
    <div 
        x-show="kardexDrawerOpen" 
        class="fixed inset-0 z-[70] overflow-hidden" 
        style="display: none;"
        @keydown.escape.window="kardexDrawerOpen = false"
    >
        <!-- Backdrop oscuro -->
        <div 
            x-show="kardexDrawerOpen"
            x-transition:enter="ease-in-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in-out duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            @click="kardexDrawerOpen = false"
        ></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div 
                x-show="kardexDrawerOpen"
                x-transition:enter="transform transition ease-in-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-300"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="w-screen max-w-full bg-white shadow-2xl flex flex-col pointer-events-auto"
                @click.stop
            >
                <!-- Header del Kardex -->
                <div class="p-6 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600/30 border border-indigo-400/30 flex items-center justify-center text-indigo-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-white">Kardex & Auditoría de Movimientos</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/30 text-indigo-200 border border-indigo-400/40" x-text="inventoryMovements.length + ' asientos'"></span>
                            </div>
                            <p class="text-xs text-slate-300">Trazabilidad inmutable de entradas, salidas, reservas y mermas (Espejo de <code class="text-indigo-300 font-mono text-[10px]">inventory_movements</code> en Laravel)</p>
                        </div>
                    </div>
                    <button @click="kardexDrawerOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- KPIs de Kardex -->
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-3xs">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Entradas x Compra</span>
                        <span class="text-base font-black text-emerald-600">
                            +<span x-text="inventoryMovements.filter(m => m.type === 'ENTRADA_COMPRA').reduce((acc, m) => acc + (m.delta || 0), 0)"></span> uds
                        </span>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-3xs">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Ventas / Despachos</span>
                        <span class="text-base font-black text-blue-600">
                            <span x-text="inventoryMovements.filter(m => m.type === 'VENTA_DIRECTA' || m.type === 'SALIDA_VENTA' || m.type === 'SALIDA_DESPACHO').reduce((acc, m) => acc + (m.delta || 0), 0)"></span> uds
                        </span>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-3xs">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Reservas Mixtas</span>
                        <span class="text-base font-black text-amber-600">
                            <span x-text="inventoryMovements.filter(m => m.type === 'RESERVA_MIXTA').length"></span> pedidos
                        </span>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-3xs">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Mermas / Ajustes</span>
                        <span class="text-base font-black text-rose-600">
                            <span x-text="inventoryMovements.filter(m => m.type === 'AJUSTE_MERMA').reduce((acc, m) => acc + (m.delta || 0), 0)"></span> uds
                        </span>
                    </div>
                </div>

                <!-- Filtros del Kardex -->
                <div class="p-4 bg-white border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2 flex-1 max-w-sm">
                        <div class="relative w-full">
                            <input 
                                type="text" 
                                x-model="kardexSearch" 
                                placeholder="Buscar por SKU, producto o documento..." 
                                class="w-full text-xs pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                            >
                            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <template x-if="kardexSearch">
                            <button @click="kardexSearch = ''" class="text-slate-400 hover:text-slate-600 cursor-pointer p-0.5" aria-label="Limpiar búsqueda"><svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                        </template>
                    </div>
                    <div class="flex items-center gap-1.5 overflow-x-auto text-xs pb-1 sm:pb-0">
                        <button 
                            @click="kardexFilterType = 'all'" 
                            :class="kardexFilterType === 'all' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg transition cursor-pointer shrink-0 text-[11px]"
                        >
                            Todos
                        </button>
                        <button 
                            @click="kardexFilterType = 'ENTRADA_COMPRA'" 
                            :class="kardexFilterType === 'ENTRADA_COMPRA' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg transition cursor-pointer shrink-0 text-[11px]"
                        >
                            Entradas
                        </button>
                        <button 
                            @click="kardexFilterType = 'VENTA_DIRECTA'" 
                            :class="kardexFilterType === 'VENTA_DIRECTA' ? 'bg-blue-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg transition cursor-pointer shrink-0 text-[11px]"
                        >
                            Ventas
                        </button>
                        <button 
                            @click="kardexFilterType = 'SALIDA_VENTA'" 
                            :class="kardexFilterType === 'SALIDA_VENTA' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg transition cursor-pointer shrink-0 text-[11px]"
                        >
                            Despachos
                        </button>
                        <button 
                            @click="kardexFilterType = 'SALIDA_COMBO_SUSCRIPCION'" 
                            :class="kardexFilterType === 'SALIDA_COMBO_SUSCRIPCION' ? 'bg-violet-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg transition cursor-pointer shrink-0 text-[11px]"
                        >
                            Suscripciones (BOM)
                        </button>
                        <button 
                            @click="kardexFilterType = 'RESERVA_MIXTA'" 
                            :class="kardexFilterType === 'RESERVA_MIXTA' ? 'bg-amber-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg transition cursor-pointer shrink-0 text-[11px]"
                        >
                            Reservas
                        </button>
                        <button 
                            @click="kardexFilterType = 'AJUSTE_MERMA'" 
                            :class="kardexFilterType === 'AJUSTE_MERMA' ? 'bg-rose-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg transition cursor-pointer shrink-0 text-[11px]"
                        >
                            Mermas
                        </button>
                    </div>
                </div>

                <!-- Tabla de Asientos del Kardex -->
                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50 sticky top-0 border-b border-slate-200 text-slate-500 text-[10px] uppercase tracking-wider font-semibold z-10">
                            <tr>
                                <th class="py-2.5 px-4">Fecha / ID</th>
                                <th class="py-2.5 px-4">Tipo Movimiento</th>
                                <th class="py-2.5 px-4">Producto & SKU</th>
                                <th class="py-2.5 px-4 text-center">Tallas</th>
                                <th class="py-2.5 px-4 text-center">Variación</th>
                                <th class="py-2.5 px-4 text-center">Saldo Físico</th>
                                <th class="py-2.5 px-4">Referencia / Usuario</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <template x-for="(mov, index) in displayedKardexMovements" :key="mov.id">
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4">
                                        <span class="font-mono font-bold text-slate-900 block" x-text="mov.id"></span>
                                        <span class="text-[10px] text-slate-400 block" x-text="mov.timestamp"></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span :class="getMovementBadgeClass(mov.type)" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border" x-text="getMovementLabel(mov.type)"></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-slate-900 block truncate max-w-[200px]" x-text="mov.productName"></span>
                                        <span class="font-mono text-[10px] text-slate-400 block" x-text="mov.sku"></span>
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono text-[10px]">
                                        <template x-if="mov.sizes">
                                            <div class="inline-flex flex-wrap gap-1 justify-center max-w-[120px]">
                                                <template x-for="(qty, sz) in mov.sizes" :key="sz">
                                                    <span x-show="qty > 0" class="px-1 py-0.2 bg-slate-100 rounded text-slate-600 font-semibold" x-text="sz.toUpperCase() + ':' + qty"></span>
                                                </template>
                                            </div>
                                        </template>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span 
                                            :class="mov.delta > 0 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : (mov.delta < 0 ? 'text-rose-700 bg-rose-50 border-rose-200' : 'text-amber-700 bg-amber-50 border-amber-200')" 
                                            class="inline-block px-2 py-0.5 rounded font-mono font-bold text-xs border"
                                            x-text="(mov.delta > 0 ? '+' : '') + mov.delta + ' uds'"
                                        ></span>
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono">
                                        <span class="text-slate-400 text-[10px]" x-text="mov.stockBefore"></span>
                                        <span class="text-slate-400 text-[10px]"> &rarr; </span>
                                        <span class="font-bold text-slate-900" x-text="mov.stockAfter"></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-semibold text-slate-800 text-[11px] block" x-text="mov.reference"></span>
                                        <span class="text-[10px] text-slate-500 block truncate max-w-[150px]" x-text="mov.user"></span>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="filteredMovements.length === 0">
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-slate-400">
                                        No se encontraron movimientos para el filtro seleccionado.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Footer del Kardex con Paginación Cargar más -->
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <div class="text-slate-500">
                        Mostrando <strong class="text-slate-800" x-text="Math.min(kardexVisibleCount, filteredMovements.length)"></strong> de <strong class="text-slate-800" x-text="filteredMovements.length"></strong> asientos de auditoría
                    </div>
                    <div class="flex items-center gap-2">
                        <template x-if="kardexVisibleCount < filteredMovements.length">
                            <button 
                                @click="loadMoreKardex()" 
                                :disabled="kardexIsLoadingMore"
                                class="px-3 py-1.5 bg-white border border-slate-300 hover:border-slate-800 rounded-md text-xs font-bold text-slate-800 transition cursor-pointer flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                <span x-text="kardexIsLoadingMore ? 'Cargando...' : 'Cargar más'"></span>
                            </button>
                        </template>
                        <button 
                            type="button" 
                            @click="kardexDrawerOpen = false" 
                            class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-md font-bold transition cursor-pointer"
                        >
                            Cerrar Kardex
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- BARRA FLOTANTE DE ACCIONES EN LOTE (FLOATING BULK ACTIONS TOOLBAR)-->
    <!-- ================================================================= -->
    <div 
        x-show="selectedSkus.length > 0" 
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-6 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-6 scale-95"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[60] flex flex-wrap items-center gap-2 sm:gap-3 bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-slate-700/80 max-w-4xl"
        style="display: none;"
    >
        <!-- Contador de Selección -->
        <div class="flex items-center gap-2 pr-3 border-r border-slate-700">
            <span class="w-6 h-6 rounded-full bg-indigo-600 text-white font-black text-xs inline-flex items-center justify-center shadow-xs" x-text="selectedSkus.length"></span>
            <span class="text-xs font-bold text-slate-200 hidden sm:inline">Seleccionados</span>
        </div>

        <!-- Botón 1: Exportar CSV -->
        <button 
            type="button" 
            @click="exportSelectedToCsv()"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-emerald-300 rounded-xl text-xs font-bold transition border border-slate-700 cursor-pointer shadow-3xs"
            title="Exportar selección a CSV plano"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Exportar CSV</span>
        </button>

        <!-- Botón 1B: Exportar Excel -->
        <button 
            type="button" 
            @click="exportSelectedToExcel()"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-green-400 rounded-xl text-xs font-bold transition border border-slate-700 cursor-pointer shadow-3xs"
            title="Exportar selección a hoja de cálculo Microsoft Excel (.xlsx / .xls)"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Exportar Excel</span>
        </button>

        <!-- Botón 2: Cambiar Proveedor -->
        <button 
            type="button" 
            @click="openBulkProviderModal()"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-indigo-300 rounded-xl text-xs font-bold transition border border-slate-700 cursor-pointer shadow-3xs"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>Proveedor</span>
        </button>

        <!-- Botón 3: Cambiar Categoría -->
        <button 
            type="button" 
            @click="openBulkCategoryModal()"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-amber-300 rounded-xl text-xs font-bold transition border border-slate-700 cursor-pointer shadow-3xs"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            <span>Categoría</span>
        </button>

        <!-- Botón 4: Añadir a Compras en Lote -->
        <button 
            type="button" 
            @click="addSelectedToPurchases()"
            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer"
        >
            <svg class="w-3.5 h-3.5 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <span>Añadir a Compras</span>
        </button>

        <!-- Botón 5: Etiquetas Térmicas Lote (TASK-INV-05) -->
        <button 
            type="button" 
            @click="openBulkLabelsModal()"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-sky-300 rounded-xl text-xs font-bold transition border border-slate-700 cursor-pointer shadow-3xs"
            title="Imprimir etiquetas termicas Code128 para los productos seleccionados"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Etiquetas Térmicas</span>
        </button>

        <!-- Botón 5: Limpiar Selección -->
        <button 
            type="button" 
            @click="clearSelection()"
            class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition cursor-pointer ml-1"
            title="Desmarcar todos"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL MASIVO: REASIGNAR PROVEEDOR EN LOTE                         -->
    <!-- ================================================================= -->
    <div 
        x-show="bulkProviderModalOpen" 
        class="fixed inset-0 z-[75] overflow-y-auto" 
        style="display: none;"
        @keydown.escape.window="bulkProviderModalOpen = false"
    >
        <div 
            x-show="bulkProviderModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            @click="bulkProviderModalOpen = false"
        ></div>

        <div class="min-h-screen px-4 text-center flex items-center justify-center p-4">
            <div 
                x-show="bulkProviderModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="inline-block w-full max-w-md bg-white rounded-2xl text-left shadow-2xl transform transition-all border border-slate-200 overflow-hidden relative"
                @click.stop
            >
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <span>Reasignar Proveedor en Lote</span>
                        <span class="px-2 py-0.5 rounded text-[10px] bg-indigo-600 text-white" x-text="selectedSkus.length + ' ítems'"></span>
                    </h3>
                    <button @click="bulkProviderModalOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4 text-xs">
                    <p class="text-slate-600">
                        Selecciona el nuevo proveedor mayorista para los <strong x-text="selectedSkus.length"></strong> productos seleccionados:
                    </p>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Nuevo Proveedor:</label>
                        <select x-model="bulkTargetProvider" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 font-semibold text-slate-800 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            <option value="Torzo B2B">Torzo B2B (Mayorista)</option>
                            <option value="Silk & Cotton Iberia SL">Silk & Cotton Iberia SL</option>
                            <option value="ModaIntima Textil MX">ModaIntima Textil MX</option>
                            <option value="Balam Textil">Balam Textil</option>
                        </select>
                    </div>
                </div>
                <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2 text-xs">
                    <button type="button" @click="bulkProviderModalOpen = false" class="px-3.5 py-1.5 text-slate-600 hover:text-slate-800 font-semibold cursor-pointer">Cancelar</button>
                    <button type="button" @click="applyBulkProvider()" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-xs cursor-pointer">Aplicar Cambio</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL MASIVO: REASIGNAR CATEGORÍA EN LOTE                         -->
    <!-- ================================================================= -->
    <div 
        x-show="bulkCategoryModalOpen" 
        class="fixed inset-0 z-[75] overflow-y-auto" 
        style="display: none;"
        @keydown.escape.window="bulkCategoryModalOpen = false"
    >
        <div 
            x-show="bulkCategoryModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            @click="bulkCategoryModalOpen = false"
        ></div>

        <div class="min-h-screen px-4 text-center flex items-center justify-center p-4">
            <div 
                x-show="bulkCategoryModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="inline-block w-full max-w-md bg-white rounded-2xl text-left shadow-2xl transform transition-all border border-slate-200 overflow-hidden relative"
                @click.stop
            >
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <span>Reasignar Categoría en Lote</span>
                        <span class="px-2 py-0.5 rounded text-[10px] bg-amber-600 text-white" x-text="selectedSkus.length + ' ítems'"></span>
                    </h3>
                    <button @click="bulkCategoryModalOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4 text-xs">
                    <p class="text-slate-600">
                        Selecciona la nueva categoría comercial para los <strong x-text="selectedSkus.length"></strong> productos seleccionados:
                    </p>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Nueva Categoría:</label>
                        <select x-model="bulkTargetCategory" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 font-semibold text-slate-800 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            <option value="Boxers">Boxers</option>
                            <option value="Jocks">Jocks</option>
                            <option value="Tangas">Tangas</option>
                            <option value="Bikinis">Bikinis</option>
                            <option value="Camisillas">Camisillas</option>
                        </select>
                    </div>
                </div>
                <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2 text-xs">
                    <button type="button" @click="bulkCategoryModalOpen = false" class="px-3.5 py-1.5 text-slate-600 hover:text-slate-800 font-semibold cursor-pointer">Cancelar</button>
                    <button type="button" @click="applyBulkCategory()" class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg shadow-xs cursor-pointer">Aplicar Cambio</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL 9: GENERADOR DE ETIQUETAS TÉRMICAS (CODE128 / QR) TASK-INV-05-->
    <!-- ================================================================= -->
    <div 
        x-show="thermalLabelModal.isOpen" 
        class="fixed inset-0 z-[80] overflow-y-auto" 
        style="display: none;"
        @keydown.escape.window="closeThermalLabelModal()"
    >
        <!-- Backdrop -->
        <div 
            x-show="thermalLabelModal.isOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs transition-opacity"
            @click="closeThermalLabelModal()"
        ></div>

        <div class="min-h-screen px-4 text-center flex items-center justify-center p-4">
            <div 
                x-show="thermalLabelModal.isOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="inline-block w-full max-w-2xl bg-white rounded-2xl text-left shadow-2xl transform transition-all border border-slate-200 overflow-hidden relative"
                @click.stop
            >
                <!-- Header del Modal -->
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600/30 text-indigo-400 flex items-center justify-center border border-indigo-500/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                <span>Etiquetas Térmicas de Bodega</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-500/30 text-indigo-300 border border-indigo-500/40">Code128 + QR</span>
                            </h3>
                            <p class="text-[11px] text-slate-400" x-text="thermalLabelModal.isBatch ? thermalLabelModal.batchTitle : (thermalLabelModal.sku + ' - ' + thermalLabelModal.productName)"></p>
                        </div>
                    </div>
                    <button @click="closeThermalLabelModal()" class="text-slate-400 hover:text-white p-1 rounded-lg transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Barra de Configuración de Impresión -->
                <div class="p-6 bg-slate-50 border-b border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Formato Térmico:</label>
                        <select x-model="thermalLabelModal.labelFormat" class="w-full bg-white border border-slate-300 rounded-lg p-2 font-medium text-slate-800 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            <option value="50x30">50 x 30 mm (Estándar Prenda/Rack)</option>
                            <option value="100x50">100 x 50 mm (Caja / Bulto / Pallet)</option>
                        </select>
                    </div>

                    <template x-if="!thermalLabelModal.isBatch">
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">
                                <span x-text="thermalLabelModal.variantType === 'shade' ? 'Tono Seleccionado:' : (thermalLabelModal.variantType === 'volume' ? 'Volumen Seleccionado:' : (thermalLabelModal.variantType === 'unit' ? 'Tipo:' : 'Talla Seleccionada:'))"></span>
                            </label>
                            <select x-model="thermalLabelModal.selectedVariant" class="w-full bg-white border border-slate-300 rounded-lg p-2 font-bold text-slate-800 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                                <template x-for="v in thermalLabelModal.availableVariants" :key="v">
                                    <option :value="v" x-text="v"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <div>
                        <label class="font-bold text-slate-700 block mb-1">
                            <span x-text="thermalLabelModal.isBatch ? 'Copias Totales en Lote:' : 'Número de Copias:'"></span>
                        </label>
                        <div class="flex items-center gap-1.5">
                            <template x-if="!thermalLabelModal.isBatch">
                                <button type="button" @click="thermalLabelModal.copies = Math.max(1, thermalLabelModal.copies - 1)" class="w-8 h-8 rounded-lg bg-white border border-slate-300 text-slate-700 font-bold hover:bg-slate-100 flex items-center justify-center cursor-pointer">-</button>
                            </template>
                            <input 
                                type="number" 
                                min="1" 
                                :disabled="thermalLabelModal.isBatch"
                                x-model.number="thermalLabelModal.copies" 
                                class="w-full bg-white border border-slate-300 rounded-lg p-2 text-center font-bold text-slate-900 focus:ring-1 focus:ring-indigo-500 focus:outline-none disabled:bg-slate-100 disabled:text-slate-500"
                            >
                            <template x-if="!thermalLabelModal.isBatch">
                                <button type="button" @click="thermalLabelModal.copies++" class="w-8 h-8 rounded-lg bg-white border border-slate-300 text-slate-700 font-bold hover:bg-slate-100 flex items-center justify-center cursor-pointer">+</button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Contenedor de Previsualización Térmica Física -->
                <div class="p-6 bg-slate-200/70 overflow-y-auto max-h-[380px] flex flex-col items-center justify-center">
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-3">Previsualización de Etiqueta Térmica</p>

                    <!-- ÁREA IMPRIMIBLE (TARGET DE IMPRESIÓN AISLADA) -->
                    <div id="thermal-print-area" class="w-full flex flex-col items-center gap-4">
                        <!-- Vista individual -->
                        <template x-if="!thermalLabelModal.isBatch">
                            <div 
                                class="bg-white border-2 border-dashed border-slate-400 p-4 rounded-xl shadow-md flex flex-col justify-between transition-all"
                                :class="thermalLabelModal.labelFormat === '100x50' ? 'w-[400px] min-h-[220px]' : 'w-[320px] min-h-[175px]'"
                            >
                                <div class="flex items-center justify-between border-b border-slate-800/20 pb-1.5 mb-1.5">
                                    <div class="text-[10px] font-black tracking-widest text-slate-900">MI TIENDA ONLINE</div>
                                    <div class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-900 text-white font-mono" x-text="'SLOT: ' + thermalLabelModal.location"></div>
                                </div>
                                <div class="mb-2">
                                    <div class="text-xs font-black text-slate-900 leading-snug line-clamp-1" x-text="thermalLabelModal.productName"></div>
                                    <div class="flex items-center justify-between mt-1">
                                        <div class="inline-block px-2 py-0.5 bg-slate-100 border border-slate-300 rounded text-[11px] font-black text-slate-900 font-mono">
                                            <span x-text="thermalLabelModal.variantType === 'shade' ? 'TONO: ' : (thermalLabelModal.variantType === 'volume' ? 'VOL: ' : (thermalLabelModal.variantType === 'unit' ? 'ITEM: ' : 'TALLA: '))"></span>
                                            <span x-text="thermalLabelModal.selectedVariant"></span>
                                        </div>
                                        <div class="text-[11px] font-bold text-slate-900 font-mono" x-text="'PVP: $' + Number(thermalLabelModal.pvp || 0).toFixed(2)"></div>
                                    </div>
                                    <!-- Informacion Sanitaria en Etiqueta (TASK-INV-06) -->
                                    <template x-if="thermalLabelModal.requiresSanitaryControl">
                                        <div class="flex items-center justify-between text-[9px] font-mono text-slate-700 bg-slate-50 border border-slate-200 rounded px-1.5 py-0.5 mt-1">
                                            <span>LOT: <strong class="text-slate-900" x-text="thermalLabelModal.batchNumber || 'S/L'"></strong></span>
                                            <span>EXP: <strong class="text-slate-900" x-text="thermalLabelModal.expirationDate || 'N/A'"></strong></span>
                                            <span x-show="thermalLabelModal.paoMonths">PAO: <strong class="text-slate-900" x-text="thermalLabelModal.paoMonths + 'M'"></strong></span>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex items-center justify-between gap-3 pt-1 border-t border-slate-800/20">
                                    <div class="flex-1 text-center">
                                        <div class="w-full flex justify-center overflow-hidden" x-html="generateBarcodeSvg(thermalLabelModal.sku)"></div>
                                        <div class="text-[10px] font-mono font-bold tracking-widest text-slate-800 mt-0.5" x-text="thermalLabelModal.sku"></div>
                                    </div>
                                    <template x-if="thermalLabelModal.includeQr">
                                        <div class="w-12 h-12 border border-slate-800 p-0.5 rounded bg-white flex items-center justify-center shrink-0">
                                            <svg class="w-10 h-10" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M3 3h6v6H3V3zm2 2v2h2V5H5zm8-2h6v6h-6V3zm2 2v2h2V5h-2zM3 13h6v6H3v-6zm2 2v2h2v-2H5zm13-2h3v2h-3v-2zm-5 0h2v3h-2v-3zm2 3h3v2h-3v-2zm2 2h2v2h-2v-2zm-4 0h2v2h-2v-2zm-2-2h2v2h-2v-2zm7 2h2v2h-2v-2zm0-5h3v2h-3v-2z"/>
                                            </svg>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Vista en Lote (Batch Items) -->
                        <template x-if="thermalLabelModal.isBatch">
                            <div class="w-full space-y-3">
                                <div class="p-3 bg-white rounded-xl border border-slate-300 text-xs">
                                    <div class="font-bold text-slate-800 mb-1 flex items-center justify-between">
                                        <span>Desglose de Etiquetas en este Lote:</span>
                                        <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 font-bold rounded" x-text="getBatchTotalPieces() + ' etiquetas totales'"></span>
                                    </div>
                                    <div class="max-h-36 overflow-y-auto divide-y divide-slate-100">
                                        <template x-for="(bItem, idx) in thermalLabelModal.batchItems" :key="idx">
                                            <div class="py-1.5 flex items-center justify-between text-[11px]">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-mono font-bold text-slate-900" x-text="bItem.sku"></span>
                                                    <span class="text-slate-500 truncate max-w-[200px]" x-text="bItem.name"></span>
                                                    <span class="px-1.5 py-0.2 bg-slate-100 font-mono font-bold text-slate-700 rounded text-[10px]" x-text="bItem.variant"></span>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <span class="text-[10px] text-slate-400 font-mono" x-text="'Slot: ' + bItem.location"></span>
                                                    <span class="font-bold text-indigo-600" x-text="bItem.qty + ' etiq.'"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Previsualización de muestra del lote -->
                                <div class="bg-white border-2 border-dashed border-slate-400 p-4 rounded-xl shadow-md w-[320px] mx-auto min-h-[175px] flex flex-col justify-between">
                                    <div class="flex items-center justify-between border-b border-slate-800/20 pb-1.5 mb-1.5">
                                        <div class="text-[10px] font-black tracking-widest text-slate-900">MI TIENDA ONLINE</div>
                                        <div class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-900 text-white font-mono" x-text="'SLOT: ' + (thermalLabelModal.batchItems[0] ? thermalLabelModal.batchItems[0].location : 'A-01-A')"></div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="text-xs font-black text-slate-900 leading-snug line-clamp-1" x-text="thermalLabelModal.batchItems[0] ? thermalLabelModal.batchItems[0].name : thermalLabelModal.productName"></div>
                                        <div class="flex items-center justify-between mt-1">
                                            <div class="inline-block px-2 py-0.5 bg-slate-100 border border-slate-300 rounded text-[11px] font-black text-slate-900 font-mono">
                                                <span>VAR: </span>
                                                <span x-text="thermalLabelModal.batchItems[0] ? thermalLabelModal.batchItems[0].variant : 'CORE'"></span>
                                            </div>
                                            <div class="text-[11px] font-bold text-slate-900 font-mono" x-text="'PVP: $' + Number((thermalLabelModal.batchItems[0] ? thermalLabelModal.batchItems[0].pvp : thermalLabelModal.pvp) || 0).toFixed(2)"></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 pt-1 border-t border-slate-800/20">
                                        <div class="flex-1 text-center">
                                            <div class="w-full flex justify-center overflow-hidden" x-html="generateBarcodeSvg(thermalLabelModal.batchItems[0] ? thermalLabelModal.batchItems[0].sku : thermalLabelModal.sku)"></div>
                                            <div class="text-[10px] font-mono font-bold tracking-widest text-slate-800 mt-0.5" x-text="thermalLabelModal.batchItems[0] ? thermalLabelModal.batchItems[0].sku : thermalLabelModal.sku"></div>
                                        </div>
                                        <div class="w-12 h-12 border border-slate-800 p-0.5 rounded bg-white flex items-center justify-center shrink-0">
                                            <svg class="w-10 h-10" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M3 3h6v6H3V3zm2 2v2h2V5H5zm8-2h6v6h-6V3zm2 2v2h2V5h-2zM3 13h6v6H3v-6zm2 2v2h2v-2H5zm13-2h3v2h-3v-2zm-5 0h2v3h-2v-3zm2 3h3v2h-3v-2zm2 2h2v2h-2v-2zm-4 0h2v2h-2v-2zm-2-2h2v2h-2v-2zm7 2h2v2h-2v-2zm0-5h3v2h-3v-2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer del Modal con Acción de Impresión Térmica -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-[11px] text-slate-500">
                        Compatible con Zebra ZD/GX, Rollo, Xprinter, Dymo o impresion laser/hoja adhesiva.
                    </div>
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            @click="closeThermalLabelModal()" 
                            class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-lg hover:bg-slate-200 transition cursor-pointer"
                        >
                            Cerrar
                        </button>
                        <button 
                            type="button" 
                            @click="printThermalLabels()" 
                            class="px-5 py-2 text-xs font-bold bg-slate-900 hover:bg-black text-white rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer"
                        >
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span x-text="'Imprimir ' + (thermalLabelModal.isBatch ? getBatchTotalPieces() : thermalLabelModal.copies) + ' Etiqueta(s)'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- ========================================================================= -->
    <!-- MODAL OPERATIVO: AUDITORIA CICLICA A CIEGAS (TASK-INV-07: 3 SKUS DIARIOS) -->
    <!-- ========================================================================= -->
    <div 
        x-show="cycleCountModal.isOpen" 
        class="fixed inset-0 z-[80] overflow-y-auto" 
        style="z-index: 80; display: none;"
        @keydown.escape.window="closeCycleCountModal()"
    >
        <!-- Backdrop -->
        <div 
            x-show="cycleCountModal.isOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs transition-opacity"
            @click="closeCycleCountModal()"
        ></div>

        <div class="min-h-screen px-4 text-center flex items-center justify-center p-4">
            <div 
                x-show="cycleCountModal.isOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="inline-block w-full max-w-2xl bg-white rounded-2xl text-left shadow-2xl transform transition-all border border-slate-200 overflow-hidden relative flex flex-col max-h-[90vh]"
                @click.stop
            >
            <!-- Header Modal -->
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-amber-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-white tracking-wide">Auditoría Cíclica a Ciegas</h3>
                            <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-amber-400/20 text-amber-300 border border-amber-400/30 font-mono">TASK-INV-07</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">Conteo ciego de 3 SKUs diarios para exactitud física (WMS Best Practice)</p>
                    </div>
                </div>
                <button 
                    type="button" 
                    @click="closeCycleCountModal()"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Paso 1: Conteo Ciego (step === 'count') -->
            <template x-if="cycleCountModal.step === 'count'">
                <div class="p-6 space-y-5 overflow-y-auto flex-1">
                    <!-- Selector de los 3 SKUs (Pestañas de SKU) -->
                    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
                        <template x-for="(sample, idx) in cycleCountModal.sampleItems" :key="sample.sku">
                            <button 
                                type="button" 
                                @click="cycleCountModal.activeSkuIndex = idx"
                                class="flex-1 py-2 px-3 rounded-xl border text-left transition cursor-pointer flex items-center justify-between"
                                :class="cycleCountModal.activeSkuIndex === idx ? 'bg-indigo-50 border-indigo-300 text-indigo-900 shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                            >
                                <div class="min-w-0 pr-2">
                                    <div class="text-[10px] uppercase tracking-wider font-bold" :class="cycleCountModal.activeSkuIndex === idx ? 'text-indigo-600' : 'text-slate-400'" x-text="'Muestra ' + (idx + 1) + ' / 3'"></div>
                                    <div class="font-mono font-black text-xs truncate" x-text="sample.sku"></div>
                                </div>
                                <div class="shrink-0">
                                    <template x-if="getCycleCountItemTotal(sample.sku) > 0 || (cycleCountModal.counts[sample.sku] && cycleCountModal.counts[sample.sku].isCounted)">
                                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-black">&#10003;</span>
                                    </template>
                                    <template x-if="!(getCycleCountItemTotal(sample.sku) > 0 || (cycleCountModal.counts[sample.sku] && cycleCountModal.counts[sample.sku].isCounted))">
                                        <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-[10px] font-bold" x-text="idx + 1"></span>
                                    </template>
                                </div>
                            </button>
                        </template>
                    </div>

                    <!-- Ficha del Producto Activo -->
                    <template x-if="cycleCountActiveItem">
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-black text-sm text-indigo-700" x-text="cycleCountActiveItem.sku"></span>
                                    <span class="text-[10px] px-2 py-0.5 rounded font-bold bg-slate-200 text-slate-700" x-text="cycleCountActiveItem.category"></span>
                                    <span class="text-[10px] px-2 py-0.5 rounded font-bold bg-indigo-100 text-indigo-800" x-text="'Clase [' + getAbcClass(cycleCountActiveItem) + ']'"></span>
                                </div>
                                <h4 class="font-bold text-slate-800 text-sm leading-tight" x-text="cycleCountActiveItem.name"></h4>
                                <p class="text-xs text-slate-500">Acude a la ubicación señalada y cuenta físicamente las unidades en anaquel.</p>
                            </div>
                            <!-- Tarjeta de Ubicación Física (Slotting: TASK-INV-01) -->
                            <div class="bg-white border border-slate-300 rounded-xl p-3 shadow-xs flex flex-col items-center justify-center min-w-[140px] text-center">
                                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Ubicación / Slot</span>
                                <div class="font-mono font-black text-base text-slate-900 mt-0.5" x-text="cycleCountActiveItem.location || 'A-01-A'"></div>
                                <span class="text-[9px] text-indigo-600 font-semibold mt-0.5">Pasillo - Rack - Nivel</span>
                            </div>
                        </div>
                    </template>

                    <!-- Formulario de Conteo Ciego (Stock Oculto) -->
                    <div class="border border-slate-200 rounded-xl p-4 bg-white shadow-xs">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                <span class="text-xs font-bold text-slate-800">Conteo Físico Ciego en Anaquel</span>
                            </div>
                            <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded">Stock del sistema oculto para evitar sesgo</span>
                        </div>

                        <!-- Matriz Textil (Curva CH a 3XL) -->
                        <template x-if="cycleCountActiveItem && (!cycleCountActiveItem.variantDimension || cycleCountActiveItem.variantDimension === 'size')">
                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2.5">
                                <template x-for="sz in ['ch', 'm', 'g', 'xl', '2xl', '3xl']" :key="sz">
                                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-2 text-center">
                                        <label class="text-[11px] font-black uppercase text-slate-700 block mb-1" x-text="sz"></label>
                                        <input 
                                            type="number" 
                                            min="0" 
                                            placeholder="0"
                                            x-model.number="cycleCountModal.counts[cycleCountActiveItem.sku].sizes[sz]"
                                            class="w-full h-8 px-1.5 text-center font-bold text-sm bg-white border border-slate-300 rounded focus:border-indigo-600 focus:outline-none"
                                        >
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Matriz Cosmética (Tonos 01 a 06) -->
                        <template x-if="cycleCountActiveItem && cycleCountActiveItem.variantDimension === 'shade'">
                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2.5">
                                <template x-for="sh in ['t01', 't02', 't03', 't04', 't05', 't06']" :key="sh">
                                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-2 text-center">
                                        <label class="text-[11px] font-black uppercase text-slate-700 block mb-1" x-text="sh.toUpperCase()"></label>
                                        <input 
                                            type="number" 
                                            min="0" 
                                            placeholder="0"
                                            x-model.number="cycleCountModal.counts[cycleCountActiveItem.sku].shades[sh]"
                                            class="w-full h-8 px-1.5 text-center font-bold text-sm bg-white border border-slate-300 rounded focus:border-indigo-600 focus:outline-none"
                                        >
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Matriz Perfumería (Volúmenes 10ml, 50ml, 100ml) -->
                        <template x-if="cycleCountActiveItem && cycleCountActiveItem.variantDimension === 'volume'">
                            <div class="grid grid-cols-3 gap-3">
                                <template x-for="vol in ['v10', 'v50', 'v100']" :key="vol">
                                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-2 text-center">
                                        <label class="text-[11px] font-black uppercase text-slate-700 block mb-1" x-text="vol === 'v10' ? '10ml' : (vol === 'v50' ? '50ml' : '100ml')"></label>
                                        <input 
                                            type="number" 
                                            min="0" 
                                            placeholder="0"
                                            x-model.number="cycleCountModal.counts[cycleCountActiveItem.sku].volumes[vol]"
                                            class="w-full h-8 px-1.5 text-center font-bold text-sm bg-white border border-slate-300 rounded focus:border-indigo-600 focus:outline-none"
                                        >
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Matriz Unitario / Accesorios -->
                        <template x-if="cycleCountActiveItem && cycleCountActiveItem.variantDimension === 'none'">
                            <div class="max-w-xs mx-auto text-center py-2">
                                <label class="text-xs font-bold text-slate-700 block mb-1">Piezas Físicas Totales</label>
                                <input 
                                    type="number" 
                                    min="0" 
                                    placeholder="0"
                                    x-model.number="cycleCountModal.counts[cycleCountActiveItem.sku].unitQty"
                                    class="w-32 h-10 mx-auto text-center font-black text-lg bg-white border border-slate-300 rounded-lg focus:border-indigo-600 focus:outline-none"
                                >
                            </div>
                        </template>

                        <!-- Subtotal Contado del SKU Actual -->
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-slate-500">Total físico registrado para este SKU:</span>
                            <span class="font-mono font-black text-sm text-indigo-700" x-text="getCycleCountItemTotal(cycleCountActiveItem ? cycleCountActiveItem.sku : '') + ' piezas'"></span>
                        </div>
                    </div>
                </div>

                <!-- Footer Paso 1 -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                    <button 
                        type="button" 
                        @click="closeCycleCountModal()" 
                        class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-lg hover:bg-slate-200 transition cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <div class="flex items-center gap-2">
                        <button 
                            type="button"
                            x-show="cycleCountModal.activeSkuIndex > 0"
                            @click="cycleCountModal.activeSkuIndex--"
                            class="px-3 py-2 text-xs font-bold bg-white hover:bg-slate-100 text-slate-700 rounded-lg border border-slate-300 transition cursor-pointer"
                        >
                            &larr; Anterior SKU
                        </button>
                        <button 
                            type="button"
                            x-show="cycleCountModal.activeSkuIndex < 2"
                            @click="cycleCountModal.activeSkuIndex++"
                            class="px-4 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition cursor-pointer"
                        >
                            Siguiente SKU &rarr;
                        </button>
                        <button 
                            type="button"
                            x-show="cycleCountModal.activeSkuIndex === 2"
                            @click="submitBlindCounts()"
                            class="px-5 py-2 text-xs font-bold bg-slate-900 hover:bg-black text-white rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer"
                        >
                            <span>Revelar y Comparar &rarr;</span>
                        </button>
                    </div>
                </div>
            </template>

            <!-- Paso 2: Reconciliación y Revelación de Discrepancias (step === 'reconcile') -->
            <template x-if="cycleCountModal.step === 'reconcile'">
                <div class="p-6 space-y-5 overflow-y-auto flex-1">
                    <!-- Resumen Semafórico Superior -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="p-3 rounded-xl border bg-slate-50 border-slate-200 text-center">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">SKUs Auditados</span>
                            <span class="text-lg font-black text-slate-800 font-mono">3 / 3</span>
                        </div>
                        <div class="p-3 rounded-xl border text-center" :class="cycleCountReport.totalDiscrepancy === 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'">
                            <span class="text-[10px] font-bold uppercase tracking-wider block">Discrepancia Neta</span>
                            <span class="text-lg font-black font-mono" x-text="(cycleCountReport.totalDiscrepancy > 0 ? '+' : '') + cycleCountReport.totalDiscrepancy + ' pzs'"></span>
                        </div>
                        <div class="p-3 rounded-xl border text-center" :class="cycleCountReport.accuracyRate >= 98 ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-amber-50 border-amber-200 text-amber-800'">
                            <span class="text-[10px] font-bold uppercase tracking-wider block">Exactitud (IRA)</span>
                            <span class="text-lg font-black font-mono" x-text="cycleCountReport.accuracyRate.toFixed(1) + '%'"></span>
                        </div>
                    </div>

                    <!-- Tabla de Discrepancias por SKU -->
                    <div class="border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-100 border-b border-slate-200 text-[11px] font-bold text-slate-700 uppercase">
                                <tr>
                                    <th class="py-2.5 px-3">SKU & Producto</th>
                                    <th class="py-2.5 px-2 text-center">Slot</th>
                                    <th class="py-2.5 px-2 text-right">Físico</th>
                                    <th class="py-2.5 px-2 text-right">Sistema</th>
                                    <th class="py-2.5 px-2 text-right">Diferencia</th>
                                    <th class="py-2.5 px-3">Motivo de Discrepancia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="row in cycleCountReport.rows" :key="row.sku">
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-3 px-3">
                                            <div class="font-mono font-bold text-slate-900" x-text="row.sku"></div>
                                            <div class="text-[11px] text-slate-500 truncate max-w-[180px]" x-text="row.name"></div>
                                        </td>
                                        <td class="py-3 px-2 text-center font-mono font-semibold text-slate-600 text-[11px]" x-text="row.location"></td>
                                        <td class="py-3 px-2 text-right font-mono font-black text-slate-900" x-text="row.physical"></td>
                                        <td class="py-3 px-2 text-right font-mono text-slate-500" x-text="row.system"></td>
                                        <td class="py-3 px-2 text-right font-mono font-bold" :class="row.diff === 0 ? 'text-emerald-600' : (row.diff < 0 ? 'text-rose-600' : 'text-amber-600')">
                                            <span x-text="(row.diff > 0 ? '+' : '') + row.diff"></span>
                                        </td>
                                        <td class="py-3 px-3">
                                            <template x-if="row.diff !== 0">
                                                <select 
                                                    x-model="cycleCountModal.discrepancyReasons[row.sku]"
                                                    class="w-full text-xs py-1 px-2 border border-slate-300 rounded bg-white font-medium focus:border-indigo-600 focus:outline-none"
                                                >
                                                    <option value="Merma operativa">Merma operativa</option>
                                                    <option value="Error de conteo previo">Error de conteo previo</option>
                                                    <option value="Producto dañado en rack">Producto dañado en rack</option>
                                                    <option value="Ubicación cruzada">Ubicación cruzada</option>
                                                </select>
                                            </template>
                                            <template x-if="row.diff === 0">
                                                <span class="inline-flex items-center gap-1 text-[11px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                                    <span>Conteo Exacto</span>
                                                </span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Nota de Advertencia sobre Asientos en Kardex -->
                    <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-xl text-xs text-indigo-900 flex items-start gap-2">
                        <svg class="w-4 h-4 text-indigo-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="leading-relaxed">
                            Al confirmar la reconciliación, el sistema ajustará automáticamente el stock físico local en el catálogo y generará asientos contables inmutables con tipo <strong>AJUSTE_AUDITORIA_CIEGA</strong> en el Kardex.
                        </p>
                    </div>
                </div>

                <!-- Footer Paso 2 -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                    <button 
                        type="button" 
                        @click="cycleCountModal.step = 'count'" 
                        class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-lg hover:bg-slate-200 transition cursor-pointer"
                    >
                        &larr; Re-contar
                    </button>
                    <button 
                        type="button"
                        @click="confirmCycleCountReconciliation()"
                        class="px-5 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Confirmar y Conciliar en Kardex</span>
                    </button>
                </div>
            </template>
            </div>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- AJUSTES Y CONFIGURACIÓN DEL HUB DE INVENTARIO (PANTALLA COMPLETA) -->
    <!-- ============================================================= -->
    <div 
        x-show="moduleSettingsModalOpen" 
        x-transition:enter="transition ease-out duration-200 transform"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150 transform"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[90] bg-[#f8fafc] flex flex-col w-screen h-screen overflow-hidden" 
        style="z-index: 90; display: none;"
        aria-labelledby="settings-fullscreen-title" 
        role="region"
        @keydown.escape.window="moduleSettingsModalOpen = false"
    >
        <!-- Full-Screen Workstation Topbar Header -->
        <header class="px-6 py-3.5 border-b border-slate-200 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shrink-0 shadow-xs">
            <div class="flex items-center gap-3">
                <button 
                    type="button" 
                    @click="moduleSettingsModalOpen = false"
                    class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-2 border border-slate-200 shadow-2xs"
                    title="Volver al Inventario"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Volver al Inventario</span>
                </button>
                <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>
                <div class="flex items-center gap-2.5">
                    <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-200 shadow-2xs">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-slate-900 m-0" id="settings-fullscreen-title">Ajustes &amp; Configuración: Hub de Inventario</h2>
                            <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-[10px] font-bold uppercase tracking-wider border border-indigo-200">Pantalla Completa</span>
                        </div>
                        <p class="text-xs text-slate-500 m-0">Gobernanza de telas, paleta de colores/tornasoles, tonos cosmética, slotting físico, reglas ROP, curaduría de scraping y curvas de tallas &amp; volúmenes</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    type="button" 
                    @click="moduleSettingsModalOpen = false" 
                    class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition cursor-pointer shadow-xs flex items-center gap-1.5"
                >
                    <span>Cerrar Ajustes</span>
                </button>
            </div>
        </header>

        <!-- Settings Tabs Bar -->
        <div class="px-6 border-b border-slate-200 bg-white flex gap-2 text-xs font-bold overflow-x-auto no-scrollbar shrink-0 shadow-2xs">
                    <button 
                        type="button" 
                        @click="activeSettingsTab = 'fabrics'" 
                        :class="activeSettingsTab === 'fabrics' ? 'border-indigo-600 text-indigo-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-800 py-3 border-b-2 border-transparent'"
                        class="cursor-pointer whitespace-nowrap px-2"
                    >
                        Telas & Fibras
                    </button>
                    <button 
                        type="button" 
                        @click="activeSettingsTab = 'colors'" 
                        :class="activeSettingsTab === 'colors' ? 'border-indigo-600 text-indigo-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-800 py-3 border-b-2 border-transparent'"
                        class="cursor-pointer whitespace-nowrap px-2"
                    >
                        Colores & Tornasoles
                    </button>
                    <button 
                        type="button" 
                        @click="activeSettingsTab = 'sizes'" 
                        :class="activeSettingsTab === 'sizes' ? 'border-indigo-600 text-indigo-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-800 py-3 border-b-2 border-transparent'"
                        class="cursor-pointer whitespace-nowrap px-2 flex items-center gap-1.5"
                    >
                        <span>Curvas & Tallas</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeSettingsTab === 'sizes' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-200 text-slate-700'" x-text="(sizeCurves || []).length"></span>
                    </button>
                    <button 
                        type="button" 
                        @click="activeSettingsTab = 'volumes'" 
                        :class="activeSettingsTab === 'volumes' ? 'border-indigo-600 text-indigo-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-800 py-3 border-b-2 border-transparent'"
                        class="cursor-pointer whitespace-nowrap px-2 flex items-center gap-1.5"
                    >
                        <span>Volúmenes Perfumería</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeSettingsTab === 'volumes' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-200 text-slate-700'" x-text="(fragranceVolumes || []).length"></span>
                    </button>
                    <button 
                        type="button" 
                        @click="activeSettingsTab = 'shades'" 
                        :class="activeSettingsTab === 'shades' ? 'border-indigo-600 text-indigo-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-800 py-3 border-b-2 border-transparent'"
                        class="cursor-pointer whitespace-nowrap px-2"
                    >
                        Tonos Cosmética
                    </button>
                    <button 
                        type="button" 
                        @click="activeSettingsTab = 'categories'" 
                        :class="activeSettingsTab === 'categories' ? 'border-indigo-600 text-indigo-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-800 py-3 border-b-2 border-transparent'"
                        class="cursor-pointer whitespace-nowrap px-2 flex items-center gap-1.5"
                    >
                        <span>Categorías & Familias</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeSettingsTab === 'categories' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-200 text-slate-700'" x-text="(categoriesTree || []).length"></span>
                    </button>
                    <button 
                        type="button" 
                        @click="activeSettingsTab = 'slotting'" 
                        :class="activeSettingsTab === 'slotting' ? 'border-indigo-600 text-indigo-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-800 py-3 border-b-2 border-transparent'"
                        class="cursor-pointer whitespace-nowrap px-2"
                    >
                        Slotting & Ubicaciones
                    </button>
                    <button 
                        type="button" 
                        @click="activeSettingsTab = 'rop'" 
                        :class="activeSettingsTab === 'rop' ? 'border-indigo-600 text-indigo-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-800 py-3 border-b-2 border-transparent'"
                        class="cursor-pointer whitespace-nowrap px-2"
                    >
                        Puntos de Reorden (ROP)
                    </button>
                    <button 
                        type="button" 
                        @click="activeSettingsTab = 'scraping'" 
                        :class="activeSettingsTab === 'scraping' ? 'border-indigo-600 text-indigo-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-800 py-3 border-b-2 border-transparent'"
                        class="cursor-pointer whitespace-nowrap px-2"
                    >
                        Proveedores & Scrapers
                    </button>
                    <button 
                        type="button" 
                        @click="activeSettingsTab = 'sections'" 
                        class="cursor-pointer whitespace-nowrap px-2"
                    >
                        Vitrinas Web
                    </button>
                    <button 
                        type="button" 
                        @click="activeSettingsTab = 'bundles'" 
                        :class="activeSettingsTab === 'bundles' ? 'border-indigo-600 text-indigo-600 border-b-2 py-3' : 'text-slate-500 hover:text-slate-800 py-3 border-b-2 border-transparent'"
                        class="cursor-pointer whitespace-nowrap px-2 flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Recetas BOM & Combos</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeSettingsTab === 'bundles' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-200 text-slate-700'" x-text="productBundles.filter(b => b.isActive !== false).length"></span>
                    </button>
                </div>

        <!-- Full-Screen Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8fafc]">
            <div class="max-w-7xl mx-auto space-y-6 text-sm">
                    
                    <!-- TAB 1: TELAS & FIBRAS (CRUD COMPLETO) -->
                    <template x-if="activeSettingsTab === 'fabrics'">
                        <div class="space-y-4">
                            <!-- Formulario de Alta / Edición de Tela -->
                            <div class="bg-indigo-50/60 p-4 rounded-xl border border-indigo-200">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-bold text-xs uppercase tracking-wider text-indigo-900" x-text="fabricEditState.isEditing ? 'Editar Tipo de Tela: ' + fabricEditState.form.name : 'Dar de Alta Nuevo Tipo de Tela'"></h4>
                                    <span x-show="fabricEditState.isEditing" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">Modo Edición</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Nombre de la Tela</label>
                                        <input type="text" x-model="fabricEditState.isEditing ? fabricEditState.form.name : newFabricForm.name" placeholder="Ej: Modal Peinado Premium" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Descripción / Propiedad Textil</label>
                                        <input type="text" x-model="fabricEditState.isEditing ? fabricEditState.form.description : newFabricForm.description" placeholder="Ej: 95% celulosa vegetal, secado ultra-rápido" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <template x-if="!fabricEditState.isEditing">
                                        <button type="button" @click="addNewFabric()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                            Guardar Tipo de Tela
                                        </button>
                                    </template>
                                    <template x-if="fabricEditState.isEditing">
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="saveFabric()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                                Actualizar Tela
                                            </button>
                                            <button type="button" @click="cancelEditFabric()" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-semibold transition cursor-pointer">
                                                Cancelar
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Tabla de Telas con Acciones CRUD -->
                            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                                        <tr>
                                            <th class="py-2.5 px-3">Tipo de Tela / Fibra</th>
                                            <th class="py-2.5 px-3">Propiedades Textiles</th>
                                            <th class="py-2.5 px-3 text-center">Estado</th>
                                            <th class="py-2.5 px-3 text-right">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="fab in apparelFabrics" :key="fab.id">
                                            <tr class="hover:bg-slate-50 transition" :class="fab.is_active === false ? 'opacity-60 bg-slate-50/50' : ''">
                                                <td class="py-2.5 px-3 font-bold text-slate-900" x-text="fab.name"></td>
                                                <td class="py-2.5 px-3 text-slate-600" x-text="fab.description"></td>
                                                <td class="py-2.5 px-3 text-center">
                                                    <span 
                                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                                        :class="fab.is_active !== false ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-slate-200 text-slate-600 border-slate-300'"
                                                        x-text="fab.is_active !== false ? 'Activo' : 'Archivado'"
                                                    ></span>
                                                </td>
                                                <td class="py-2.5 px-3 text-right">
                                                    <div class="inline-flex items-center gap-1.5">
                                                        <button 
                                                            type="button" 
                                                            @click="editFabric(fab)" 
                                                            class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-bold transition cursor-pointer"
                                                            title="Editar tela"
                                                        >
                                                            Editar
                                                        </button>
                                                        <button 
                                                            type="button" 
                                                            @click="toggleArchiveFabric(fab)" 
                                                            class="px-2 py-1 rounded text-[11px] font-bold transition cursor-pointer"
                                                            :class="fab.is_active !== false ? 'bg-amber-50 hover:bg-amber-100 text-amber-800' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800'"
                                                            x-text="fab.is_active !== false ? 'Archivar' : 'Restaurar'"
                                                        ></button>
                                                        <button 
                                                            type="button" 
                                                            @click="deleteFabric(fab.id)" 
                                                            class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded text-[11px] font-bold transition cursor-pointer"
                                                            title="Eliminar tela permanentemente"
                                                        >
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>

                    <!-- TAB 2: COLORES & TORNASOLES (CRUD COMPLETO) -->
                    <template x-if="activeSettingsTab === 'colors'">
                        <div class="space-y-4">
                            <!-- Formulario Alta / Edición Color -->
                            <div class="bg-indigo-50/60 p-4 rounded-xl border border-indigo-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-xs uppercase tracking-wider text-indigo-900" x-text="colorEditState.isEditing ? 'Editar Color: ' + colorEditState.form.name : 'Registrar Nuevo Color o Tornasol'"></h4>
                                    <span x-show="colorEditState.isEditing" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">Modo Edición</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Nombre del Color</label>
                                        <input type="text" x-model="colorEditState.isEditing ? colorEditState.form.name : newColorForm.name" placeholder="Ej: Esmeralda Aurora" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Tipo de Color</label>
                                        <select x-model="colorEditState.isEditing ? colorEditState.form.type : newColorForm.type" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            <option value="solid">Sólido (Un solo tono HEX)</option>
                                            <option value="iridescent">Tornasol Iridiscente (Degradado)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <template x-if="(colorEditState.isEditing ? colorEditState.form.type : newColorForm.type) === 'solid'">
                                            <div>
                                                <label class="text-[11px] font-semibold text-slate-700 block mb-1">Código HEX</label>
                                                <input type="color" x-model="colorEditState.isEditing ? colorEditState.form.hex : newColorForm.hex" class="w-full h-8 px-1 py-0.5 bg-white border border-slate-300 rounded-lg cursor-pointer">
                                            </div>
                                        </template>
                                        <template x-if="(colorEditState.isEditing ? colorEditState.form.type : newColorForm.type) === 'iridescent'">
                                            <div class="flex items-center gap-1">
                                                <div>
                                                    <label class="text-[10px] text-slate-600 block">Tono 1</label>
                                                    <input type="color" x-model="colorEditState.isEditing ? colorEditState.form.gradientColor1 : newColorForm.gradientColor1" class="w-10 h-8 border rounded cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="text-[10px] text-slate-600 block">Tono 2</label>
                                                    <input type="color" x-model="colorEditState.isEditing ? colorEditState.form.gradientColor2 : newColorForm.gradientColor2" class="w-10 h-8 border rounded cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="text-[10px] text-slate-600 block">Tono 3</label>
                                                    <input type="color" x-model="colorEditState.isEditing ? colorEditState.form.gradientColor3 : newColorForm.gradientColor3" class="w-10 h-8 border rounded cursor-pointer">
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <template x-if="!colorEditState.isEditing">
                                        <button type="button" @click="addNewColor()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                            Agregar a la Paleta
                                        </button>
                                    </template>
                                    <template x-if="colorEditState.isEditing">
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="saveColor()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                                Actualizar Color
                                            </button>
                                            <button type="button" @click="cancelEditColor()" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-semibold transition cursor-pointer">
                                                Cancelar
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Grilla de Colores Sólidos y Tornasoles con Acciones -->
                            <div>
                                <h4 class="font-bold text-xs uppercase tracking-wider text-slate-600 mb-2">Paleta de Colores en Plataforma</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <template x-for="col in apparelColors" :key="col.id">
                                        <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between gap-2.5" :class="col.is_active === false ? 'opacity-60' : ''">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <span class="w-8 h-8 rounded-lg border border-slate-300 shadow-xs shrink-0" :style="'background: ' + col.hex"></span>
                                                <div class="min-w-0">
                                                    <div class="text-xs font-bold text-slate-900 truncate" x-text="col.name"></div>
                                                    <div class="text-[10px] text-slate-400 capitalize" x-text="(col.type === 'iridescent' ? 'Tornasol' : 'Sólido') + (col.is_active === false ? ' (Archivado)' : '')"></div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1 shrink-0">
                                                <button type="button" @click="editColor(col)" class="p-1 text-slate-600 hover:text-slate-900 rounded hover:bg-slate-200 transition cursor-pointer" title="Editar">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                                </button>
                                                <button type="button" @click="toggleArchiveColor(col)" class="p-1 rounded transition cursor-pointer" :class="col.is_active !== false ? 'text-amber-700 hover:bg-amber-100' : 'text-emerald-700 hover:bg-emerald-100'" :title="col.is_active !== false ? 'Archivar' : 'Restaurar'">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8v13H3V8"/><path d="M1 3h22v5H1z"/><path d="M10 12h4"/></svg>
                                                </button>
                                                <button type="button" @click="deleteColor(col.id)" class="p-1 text-rose-600 hover:text-rose-800 rounded hover:bg-rose-100 transition cursor-pointer" title="Eliminar">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- TAB: CURVAS DE TALLAS & ESCALAS (CRUD COMPLETO - TASK-INV-14) -->
                    <template x-if="activeSettingsTab === 'sizes'">
                        <div class="space-y-6">
                            <!-- Top: Gestor de Curvas Maestras -->
                            <div class="bg-indigo-50/70 p-4 rounded-xl border border-indigo-200 space-y-4">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <div>
                                        <h4 class="font-bold text-xs uppercase tracking-wider text-indigo-900" x-text="curveEditState.isEditing ? 'Editar Curva de Tallas: ' + curveEditState.form.name : 'Crear Nueva Curva de Tallas'"></h4>
                                        <p class="text-[11px] text-slate-600">Configura escalas maestras de tallas (Americana, Pantalones numéricos, Lencería, Unitalla).</p>
                                    </div>
                                    <span x-show="curveEditState.isEditing" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">Modo Edición Curva</span>
                                </div>

                                <!-- Formulario Alta / Edición Curva -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Nombre de la Curva</label>
                                        <input type="text" x-model="curveEditState.isEditing ? curveEditState.form.name : newCurveForm.name" placeholder="Ej: Curva Americana Estándar" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Ámbito / Scope</label>
                                        <select x-model="curveEditState.isEditing ? curveEditState.form.category_scope : newCurveForm.category_scope" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold text-slate-700">
                                            <option value="textil">Textil (Ropa Interior, Boxers, Playeras)</option>
                                            <option value="calzado">Calzado & Sneakers</option>
                                            <option value="one_size">Unitalla / Accesorios</option>
                                            <option value="general">General</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Descripción / Propósito</label>
                                        <input type="text" x-model="curveEditState.isEditing ? curveEditState.form.description : newCurveForm.description" placeholder="Ej: Tallas comerciales XS a 4XL" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between flex-wrap gap-2 pt-2 border-t border-indigo-100">
                                    <div class="flex items-center gap-4 text-xs">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="checkbox" x-model="curveEditState.isEditing ? curveEditState.form.is_default : newCurveForm.is_default" class="rounded text-indigo-600 focus:ring-indigo-500">
                                            <span class="font-medium text-slate-700">Curva Predeterminada</span>
                                        </label>
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="checkbox" x-model="curveEditState.isEditing ? curveEditState.form.is_active : newCurveForm.is_active" class="rounded text-indigo-600 focus:ring-indigo-500">
                                            <span class="font-medium text-slate-700">Activa</span>
                                        </label>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <template x-if="!curveEditState.isEditing">
                                            <button type="button" @click="addNewCurve()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                                Crear Curva de Tallas
                                            </button>
                                        </template>
                                        <template x-if="curveEditState.isEditing">
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="saveCurve()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                                    Actualizar Curva
                                                </button>
                                                <button type="button" @click="cancelEditCurve()" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-semibold transition cursor-pointer">
                                                    Cancelar
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Selector Horizontal de Curvas Existentes -->
                            <div>
                                <h4 class="font-bold text-xs uppercase tracking-wider text-slate-600 mb-2">Curvas Configuradas en el Sistema</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <template x-for="curve in sizeCurves" :key="curve.id">
                                        <div 
                                            @click="selectCurve(curve.id)"
                                            class="p-3.5 rounded-xl border transition cursor-pointer flex flex-col justify-between gap-3"
                                            :class="selectedCurveId === curve.id ? 'bg-indigo-50/50 border-indigo-500 shadow-xs ring-1 ring-indigo-500' : 'bg-white border-slate-200 hover:border-slate-300'"
                                        >
                                            <div class="flex items-start justify-between gap-2">
                                                <div>
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="font-bold text-xs text-slate-900" x-text="curve.name"></span>
                                                        <template x-if="curve.is_default">
                                                            <span class="px-1.5 py-0.2 rounded-full text-[9px] font-extrabold bg-indigo-100 text-indigo-800 border border-indigo-200">Default</span>
                                                        </template>
                                                        <span 
                                                            class="px-1.5 py-0.2 rounded-full text-[9px] font-bold border"
                                                            :class="curve.deleted_at ? 'bg-rose-100 text-rose-800 border-rose-200' : (curve.is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-slate-200 text-slate-600 border-slate-300')"
                                                            x-text="curve.deleted_at ? 'Archivada' : (curve.is_active ? 'Activa' : 'Inactiva')"
                                                        ></span>
                                                    </div>
                                                    <p class="text-[11px] text-slate-500 mt-1" x-text="curve.description || 'Sin descripcion'"></p>
                                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                                        Ambito: <span class="font-semibold text-slate-600 uppercase" x-text="curve.category_scope"></span> | <span class="font-bold text-indigo-700" x-text="(curve.sizes || []).length"></span> tallas
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Acciones de la Curva (Universal Rule) -->
                                            <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-1.5" @click.stop>
                                                <button type="button" @click="editCurve(curve)" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[10px] font-bold transition cursor-pointer" title="Editar curva">
                                                    Editar
                                                </button>
                                                <button type="button" @click="toggleActiveCurve(curve)" class="px-2 py-1 rounded text-[10px] font-bold transition cursor-pointer" :class="curve.is_active ? 'bg-amber-50 hover:bg-amber-100 text-amber-800' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800'" :title="curve.is_active ? 'Desactivar' : 'Activar'" x-text="curve.is_active ? 'Desactivar' : 'Activar'"></button>
                                                <button type="button" @click="archiveCurve(curve)" class="px-2 py-1 rounded text-[10px] font-bold transition cursor-pointer" :class="curve.deleted_at ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800' : 'bg-amber-50 hover:bg-amber-100 text-amber-800'" :title="curve.deleted_at ? 'Restaurar' : 'Archivar'" x-text="curve.deleted_at ? 'Restaurar' : 'Archivar'"></button>
                                                <button type="button" @click="deleteCurve(curve.id)" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded text-[10px] font-bold transition cursor-pointer" title="Eliminar permanentemente">
                                                    Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Bottom: Detalle de Tallas de la Curva Seleccionada -->
                            <template x-if="selectedCurve">
                                <div class="space-y-4 pt-4 border-t border-slate-200">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-800">
                                                Tallas de la Curva: <span class="text-indigo-600" x-text="selectedCurve.name"></span>
                                            </h4>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800" x-text="(selectedCurve.sizes || []).length + ' Tallas'"></span>
                                        </div>
                                        <span x-show="sizeEditState.isEditing" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">Modo Edición Talla</span>
                                    </div>

                                    <!-- Formulario Alta / Edición Talla Individual -->
                                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                                        <div class="grid grid-cols-1 sm:grid-cols-6 gap-3">
                                            <div>
                                                <label class="text-[11px] font-semibold text-slate-700 block mb-1">Código (ej: XS, 32)</label>
                                                <input type="text" x-model="sizeEditState.isEditing ? sizeEditState.form.code : newSizeForm.code" placeholder="XS" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 font-mono font-bold uppercase">
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="text-[11px] font-semibold text-slate-700 block mb-1">Etiqueta Descriptiva</label>
                                                <input type="text" x-model="sizeEditState.isEditing ? sizeEditState.form.label : newSizeForm.label" placeholder="Extra Chica / XS" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            </div>
                                            <div>
                                                <label class="text-[11px] font-semibold text-slate-700 block mb-1">Orden (Sort)</label>
                                                <input type="number" x-model.number="sizeEditState.isEditing ? sizeEditState.form.sort_order : newSizeForm.sort_order" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 font-mono">
                                            </div>
                                            <div>
                                                <label class="text-[11px] font-semibold text-slate-700 block mb-1">Cintura Min (cm)</label>
                                                <input type="number" step="0.5" x-model.number="sizeEditState.isEditing ? sizeEditState.form.waist_min_cm : newSizeForm.waist_min_cm" placeholder="65" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 font-mono">
                                            </div>
                                            <div>
                                                <label class="text-[11px] font-semibold text-slate-700 block mb-1">Cintura Max (cm)</label>
                                                <input type="number" step="0.5" x-model.number="sizeEditState.isEditing ? sizeEditState.form.waist_max_cm : newSizeForm.waist_max_cm" placeholder="70" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 font-mono">
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between flex-wrap gap-2 pt-2 border-t border-slate-200">
                                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs">
                                                <input type="checkbox" x-model="sizeEditState.isEditing ? sizeEditState.form.is_active : newSizeForm.is_active" class="rounded text-indigo-600 focus:ring-indigo-500">
                                                <span class="font-medium text-slate-700">Talla Activa para Catálogo</span>
                                            </label>

                                            <div class="flex items-center gap-2">
                                                <template x-if="!sizeEditState.isEditing">
                                                    <button type="button" @click="addNewSize()" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                                        + Agregar Talla
                                                    </button>
                                                </template>
                                                <template x-if="sizeEditState.isEditing">
                                                    <div class="flex items-center gap-2">
                                                        <button type="button" @click="saveSize()" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                                            Actualizar Talla
                                                        </button>
                                                        <button type="button" @click="cancelEditSize()" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-semibold transition cursor-pointer">
                                                            Cancelar
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tabla de Tallas de la Curva -->
                                    <div class="border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                                        <table class="w-full text-left text-xs border-collapse">
                                            <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                                                <tr>
                                                    <th class="py-2.5 px-3 w-14 text-center">Orden</th>
                                                    <th class="py-2.5 px-3 w-24">Código</th>
                                                    <th class="py-2.5 px-3">Etiqueta Comercial</th>
                                                    <th class="py-2.5 px-3">Rango de Medida (Cintura)</th>
                                                    <th class="py-2.5 px-3 text-center">Estado</th>
                                                    <th class="py-2.5 px-3 text-right">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                <template x-for="sz in (selectedCurve.sizes || [])" :key="sz.id">
                                                    <tr class="hover:bg-slate-50 transition" :class="sz.deleted_at ? 'opacity-50 bg-rose-50/30' : (sz.is_active === false ? 'opacity-60 bg-slate-50/50' : '')">
                                                        <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-500" x-text="sz.sort_order"></td>
                                                        <td class="py-2.5 px-3">
                                                            <span class="font-mono font-bold text-xs px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-900" x-text="sz.code"></span>
                                                        </td>
                                                        <td class="py-2.5 px-3 font-semibold text-slate-800" x-text="sz.label"></td>
                                                        <td class="py-2.5 px-3 text-slate-600 font-mono text-[11px]">
                                                            <span x-text="sz.waist_min_cm ? (sz.waist_min_cm + ' - ' + (sz.waist_max_cm || '?') + ' cm') : 'No especificada'"></span>
                                                        </td>
                                                        <td class="py-2.5 px-3 text-center">
                                                            <span 
                                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                                                :class="sz.deleted_at ? 'bg-rose-100 text-rose-800 border-rose-200' : (sz.is_active !== false ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-slate-200 text-slate-600 border-slate-300')"
                                                                x-text="sz.deleted_at ? 'Archivada' : (sz.is_active !== false ? 'Activa' : 'Inactiva')"
                                                            ></span>
                                                        </td>
                                                        <td class="py-2.5 px-3 text-right">
                                                            <div class="inline-flex items-center gap-1.5">
                                                                <button type="button" @click="editSize(sz)" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-bold transition cursor-pointer" title="Editar talla">
                                                                    Editar
                                                                </button>
                                                                <button type="button" @click="toggleActiveSize(sz)" class="px-2 py-1 rounded text-[11px] font-bold transition cursor-pointer" :class="sz.is_active !== false ? 'bg-amber-50 hover:bg-amber-100 text-amber-800' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800'" :title="sz.is_active !== false ? 'Desactivar' : 'Activar'" x-text="sz.is_active !== false ? 'Desactivar' : 'Activar'"></button>
                                                                <button type="button" @click="archiveSize(sz)" class="px-2 py-1 rounded text-[11px] font-bold transition cursor-pointer" :class="sz.deleted_at ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800' : 'bg-amber-50 hover:bg-amber-100 text-amber-800'" :title="sz.deleted_at ? 'Restaurar' : 'Archivar'" x-text="sz.deleted_at ? 'Restaurar' : 'Archivar'"></button>
                                                                <button type="button" @click="deleteSize(sz.id)" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded text-[11px] font-bold transition cursor-pointer" title="Eliminar permanentemente">
                                                                    Eliminar
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- TAB: VOLUMENES DE PERFUMERIA (CRUD COMPLETO - TASK-INV-14) -->
                    <template x-if="activeSettingsTab === 'volumes'">
                        <div class="space-y-5">
                            <!-- Formulario Alta / Edición de Volumen -->
                            <div class="bg-indigo-50/70 p-4 rounded-xl border border-indigo-200 space-y-4">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <div>
                                        <h4 class="font-bold text-xs uppercase tracking-wider text-indigo-900" x-text="volumeEditState.isEditing ? 'Editar Volumen: ' + volumeEditState.form.label : 'Registrar Nuevo Volumen de Perfumería / Decant'"></h4>
                                        <p class="text-[11px] text-slate-600">Control de cubicaje, frascos comerciales, viales de muestra y decants de autor.</p>
                                    </div>
                                    <span x-show="volumeEditState.isEditing" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">Modo Edición Volumen</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Capacidad Numérica</label>
                                        <input type="number" step="0.1" x-model.number="volumeEditState.isEditing ? volumeEditState.form.value : newVolumeForm.value" placeholder="100" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 font-mono font-bold">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Unidad de Medida</label>
                                        <select x-model="volumeEditState.isEditing ? volumeEditState.form.unit : newVolumeForm.unit" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold text-slate-700">
                                            <option value="ml">Mililitros (ml)</option>
                                            <option value="oz">Onzas líquidas (fl oz)</option>
                                            <option value="gr">Gramos (gr)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Etiqueta Comercial</label>
                                        <input type="text" x-model="volumeEditState.isEditing ? volumeEditState.form.label : newVolumeForm.label" placeholder="Ej: 100ml Standard" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Orden de Visualización</label>
                                        <input type="number" x-model.number="volumeEditState.isEditing ? volumeEditState.form.sort_order : newVolumeForm.sort_order" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 font-mono">
                                    </div>
                                </div>

                                <div>
                                    <label class="text-[11px] font-semibold text-slate-700 block mb-1">Descripción / Propósito</label>
                                    <input type="text" x-model="volumeEditState.isEditing ? volumeEditState.form.description : newVolumeForm.description" placeholder="Ej: Frasco de cristal regular para venta al público" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </div>

                                <div class="flex items-center justify-between flex-wrap gap-2 pt-2 border-t border-indigo-100">
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs">
                                        <input type="checkbox" x-model="volumeEditState.isEditing ? volumeEditState.form.is_active : newVolumeForm.is_active" class="rounded text-indigo-600 focus:ring-indigo-500">
                                        <span class="font-medium text-slate-700">Volumen Activo en Catálogo</span>
                                    </label>

                                    <div class="flex items-center gap-2">
                                        <template x-if="!volumeEditState.isEditing">
                                            <button type="button" @click="addNewVolume()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                                Registrar Volumen
                                            </button>
                                        </template>
                                        <template x-if="volumeEditState.isEditing">
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="saveVolume()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                                    Actualizar Volumen
                                                </button>
                                                <button type="button" @click="cancelEditVolume()" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-semibold transition cursor-pointer">
                                                    Cancelar
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de Volúmenes de Perfumería -->
                            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                                        <tr>
                                            <th class="py-2.5 px-3 w-16 text-center">Orden</th>
                                            <th class="py-2.5 px-3">Cubicaje / Valor</th>
                                            <th class="py-2.5 px-3">Etiqueta Comercial</th>
                                            <th class="py-2.5 px-3">Descripción</th>
                                            <th class="py-2.5 px-3 text-center">Estado</th>
                                            <th class="py-2.5 px-3 text-right">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="vol in fragranceVolumes" :key="vol.id">
                                            <tr class="hover:bg-slate-50 transition" :class="vol.deleted_at ? 'opacity-50 bg-rose-50/30' : (vol.is_active === false ? 'opacity-60 bg-slate-50/50' : '')">
                                                <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-500" x-text="vol.sort_order"></td>
                                                <td class="py-2.5 px-3 font-mono font-bold text-slate-900" x-text="vol.value + ' ' + vol.unit"></td>
                                                <td class="py-2.5 px-3 font-semibold text-slate-800" x-text="vol.label"></td>
                                                <td class="py-2.5 px-3 text-slate-600" x-text="vol.description || '-'"></td>
                                                <td class="py-2.5 px-3 text-center">
                                                    <span 
                                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                                        :class="vol.deleted_at ? 'bg-rose-100 text-rose-800 border-rose-200' : (vol.is_active !== false ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-slate-200 text-slate-600 border-slate-300')"
                                                        x-text="vol.deleted_at ? 'Archivado' : (vol.is_active !== false ? 'Activo' : 'Inactivo')"
                                                    ></span>
                                                </td>
                                                <td class="py-2.5 px-3 text-right">
                                                    <div class="inline-flex items-center gap-1.5">
                                                        <button type="button" @click="editVolume(vol)" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-bold transition cursor-pointer" title="Editar volumen">
                                                            Editar
                                                        </button>
                                                        <button type="button" @click="toggleActiveVolume(vol)" class="px-2 py-1 rounded text-[11px] font-bold transition cursor-pointer" :class="vol.is_active !== false ? 'bg-amber-50 hover:bg-amber-100 text-amber-800' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800'" :title="vol.is_active !== false ? 'Desactivar' : 'Activar'" x-text="vol.is_active !== false ? 'Desactivar' : 'Activar'"></button>
                                                        <button type="button" @click="archiveVolume(vol)" class="px-2 py-1 rounded text-[11px] font-bold transition cursor-pointer" :class="vol.deleted_at ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800' : 'bg-amber-50 hover:bg-amber-100 text-amber-800'" :title="vol.deleted_at ? 'Restaurar' : 'Archivar'" x-text="vol.deleted_at ? 'Restaurar' : 'Archivar'"></button>
                                                        <button type="button" @click="deleteVolume(vol.id)" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded text-[11px] font-bold transition cursor-pointer" title="Eliminar permanentemente">
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>

                    <!-- TAB 3: TONOS DE COSMÉTICA (INTEGRADO EN AJUSTES - TASK-INV-09 / TASK-INV-11) -->
                    <template x-if="activeSettingsTab === 'shades'">
                        <div class="space-y-4">
                            <!-- Formulario Alta / Edición Tono Cosmética -->
                            <div class="bg-pink-50/60 p-4 rounded-xl border border-pink-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-xs uppercase tracking-wider text-pink-900" x-text="cosmeticShadesModal.isEditing ? 'Editar Tono: ' + cosmeticShadesModal.form.code : 'Registrar Nuevo Tono de Cosmética'"></h4>
                                    <span x-show="cosmeticShadesModal.isEditing" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-pink-100 text-pink-900 border border-pink-200">Modo Edición</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Código (ej. 01, 09)</label>
                                        <input type="text" x-model="cosmeticShadesModal.form.code" placeholder="09" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-pink-500 font-mono">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Nombre del Tono</label>
                                        <input type="text" x-model="cosmeticShadesModal.form.name" placeholder="Ej: Vainilla Cálido" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-pink-500">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Color HEX</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="color" x-model="cosmeticShadesModal.form.hex_color" class="w-8 h-8 rounded border border-slate-300 cursor-pointer">
                                            <input type="text" x-model="cosmeticShadesModal.form.hex_color" class="flex-1 px-2 py-1.5 text-xs bg-white border border-slate-300 rounded-lg font-mono">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Subtono</label>
                                        <select x-model="cosmeticShadesModal.form.undertone" class="w-full px-2.5 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-pink-500">
                                            <option value="warm">Cálido</option>
                                            <option value="cool">Frío</option>
                                            <option value="neutral">Neutro</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-1">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full border border-black/15 shadow-xs shrink-0" :style="'background: ' + (cosmeticShadesModal.form.hex_color || '#E8BEA2')"></span>
                                        <span class="text-xs text-slate-600">Vista Previa Swatch</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="saveCosmeticShade()" class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                            <span x-text="cosmeticShadesModal.isEditing ? 'Actualizar Tono' : 'Guardar Tono'"></span>
                                        </button>
                                        <button x-show="cosmeticShadesModal.isEditing" type="button" @click="cosmeticShadesModal.isEditing = false; cosmeticShadesModal.editingId = null;" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-semibold transition cursor-pointer">
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Listado de Tonos Cosmética -->
                            <div>
                                <h4 class="font-bold text-xs uppercase tracking-wider text-slate-600 mb-2">Tonos Cosmética Registrados (<span x-text="cosmeticShades.length"></span>)</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <template x-for="shade in cosmeticShades" :key="shade.id">
                                        <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between gap-2.5" :class="!shade.is_active ? 'opacity-60' : ''">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <span class="w-7 h-7 rounded-full border border-black/15 shadow-xs shrink-0" :style="'background: ' + shade.hex_color"></span>
                                                <div class="min-w-0">
                                                    <div class="text-xs font-bold text-slate-900 truncate">
                                                        <span class="font-mono text-slate-500" x-text="shade.code + ' - '"></span>
                                                        <span x-text="shade.name"></span>
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 font-mono" x-text="shade.hex_color + ' &bull; ' + (shade.undertone || 'neutro')"></div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1 shrink-0">
                                                <button type="button" @click="toggleCosmeticShadeStatus(shade)" class="px-2 py-0.5 rounded text-[10px] font-bold border transition cursor-pointer" :class="shade.is_active ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-300'" x-text="shade.is_active ? 'Activo' : 'Inactivo'"></button>
                                                <button type="button" @click="editCosmeticShade(shade)" class="p-1 text-slate-600 hover:text-slate-900 rounded hover:bg-slate-200 transition cursor-pointer" title="Editar">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                                </button>
                                                <button type="button" @click="deleteCosmeticShade(shade.id)" class="p-1 text-rose-600 hover:text-rose-800 rounded hover:bg-rose-100 transition cursor-pointer" title="Eliminar">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- TAB: CATEGORIAS & FAMILIAS (CRUD COMPLETO - TASK-INV-14) -->
                    <template x-if="activeSettingsTab === 'categories'">
                        <div class="space-y-6">
                            <!-- Formulario Alta / Edición de Categoría / Subcategoría -->
                            <div class="bg-indigo-50/70 p-4 rounded-xl border border-indigo-200 space-y-4">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <div>
                                        <h4 class="font-bold text-xs uppercase tracking-wider text-indigo-900" x-text="categoryEditState.isEditing ? 'Editar Categoría: ' + categoryEditState.form.name : 'Crear Nueva Categoría o Subcategoría'"></h4>
                                        <p class="text-[11px] text-slate-600">Jerarquía y familias de producto para catálogo, navegación web y taxonomía.</p>
                                    </div>
                                    <span x-show="categoryEditState.isEditing" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">Modo Edición Categoría</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Nombre de la Categoría</label>
                                        <input type="text" x-model="categoryEditState.isEditing ? categoryEditState.form.name : newCategoryForm.name" placeholder="Ej: Ropa Interior Deportiva" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Categoría Padre (Nivel Jerárquico)</label>
                                        <select x-model="categoryEditState.isEditing ? categoryEditState.form.parent_id : newCategoryForm.parent_id" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold text-slate-700">
                                            <option value="">-- Ninguna (Categoría Raíz / Familia Principal) --</option>
                                            <template x-for="parentCat in flatCategories" :key="parentCat.id">
                                                <option :value="parentCat.id" x-text="parentCat.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Descripción / Taxonomía</label>
                                        <input type="text" x-model="categoryEditState.isEditing ? categoryEditState.form.description : newCategoryForm.description" placeholder="Ej: Siluetas anatómicas masculinas de alto rendimiento" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between flex-wrap gap-2 pt-2 border-t border-indigo-100">
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs">
                                        <input type="checkbox" x-model="categoryEditState.isEditing ? categoryEditState.form.is_active : newCategoryForm.is_active" class="rounded text-indigo-600 focus:ring-indigo-500">
                                        <span class="font-medium text-slate-700">Categoría Activa en E-Commerce</span>
                                    </label>

                                    <div class="flex items-center gap-2">
                                        <template x-if="!categoryEditState.isEditing">
                                            <button type="button" @click="addNewCategory()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                                Guardar Categoría
                                            </button>
                                        </template>
                                        <template x-if="categoryEditState.isEditing">
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="saveCategory()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                                    Actualizar Categoría
                                                </button>
                                                <button type="button" @click="cancelEditCategory()" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-semibold transition cursor-pointer">
                                                    Cancelar
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Árbol Jerárquico de Categorías & Subcategorías -->
                            <div>
                                <h4 class="font-bold text-xs uppercase tracking-wider text-slate-600 mb-3">Familias Principales & Subcategorías Activas</h4>
                                <div class="space-y-3">
                                    <template x-for="rootCat in categoriesTree" :key="rootCat.id">
                                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-xs">
                                            <!-- Fila Padre (Categoría Raíz) -->
                                            <div class="p-3.5 bg-slate-50 flex items-center justify-between gap-3 border-b border-slate-200/80">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <span class="p-2 bg-indigo-100 text-indigo-700 rounded-lg shrink-0">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                                    </span>
                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <span class="font-bold text-xs text-slate-900" x-text="rootCat.name"></span>
                                                            <span class="font-mono text-[10px] text-slate-500 bg-slate-200 px-1.5 py-0.2 rounded" x-text="'/' + rootCat.slug"></span>
                                                            <span 
                                                                class="px-2 py-0.2 rounded-full text-[9px] font-bold border"
                                                                :class="rootCat.deleted_at ? 'bg-rose-100 text-rose-800 border-rose-200' : (rootCat.is_active !== false ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-slate-200 text-slate-600 border-slate-300')"
                                                                x-text="rootCat.deleted_at ? 'Archivada' : (rootCat.is_active !== false ? 'Activa' : 'Inactiva')"
                                                            ></span>
                                                        </div>
                                                        <p class="text-[11px] text-slate-500 mt-0.5 truncate" x-text="rootCat.description || 'Sin descripcion adicional'"></p>
                                                    </div>
                                                </div>

                                                <!-- Acciones Categoría Raíz -->
                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <button 
                                                        type="button" 
                                                        @click="newCategoryForm.parent_id = String(rootCat.id); newCategoryForm.name = '';" 
                                                        class="px-2 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded text-[10px] font-bold transition cursor-pointer"
                                                        title="Añadir subcategoría"
                                                    >
                                                        + Subcategoría
                                                    </button>
                                                    <button type="button" @click="editCategory(rootCat)" class="px-2 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded text-[10px] font-bold transition cursor-pointer" title="Editar">
                                                        Editar
                                                    </button>
                                                    <button type="button" @click="toggleActiveCategory(rootCat)" class="px-2 py-1 rounded text-[10px] font-bold transition cursor-pointer" :class="rootCat.is_active !== false ? 'bg-amber-50 hover:bg-amber-100 text-amber-800' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800'" :title="rootCat.is_active !== false ? 'Desactivar' : 'Activar'" x-text="rootCat.is_active !== false ? 'Desactivar' : 'Activar'"></button>
                                                    <button type="button" @click="archiveCategory(rootCat)" class="px-2 py-1 rounded text-[10px] font-bold transition cursor-pointer" :class="rootCat.deleted_at ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800' : 'bg-amber-50 hover:bg-amber-100 text-amber-800'" :title="rootCat.deleted_at ? 'Restaurar' : 'Archivar'" x-text="rootCat.deleted_at ? 'Restaurar' : 'Archivar'"></button>
                                                    <button type="button" @click="deleteCategory(rootCat.id)" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded text-[10px] font-bold transition cursor-pointer" title="Eliminar permanentemente">
                                                        Eliminar
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Subcategorías Hijas (Indented List) -->
                                            <div class="divide-y divide-slate-100 pl-8 pr-4 py-1 bg-white">
                                                <template x-if="!rootCat.children || rootCat.children.length === 0">
                                                    <div class="py-2.5 text-xs text-slate-400 italic">
                                                        No hay subcategorías hijas asociadas a esta familia principal.
                                                    </div>
                                                </template>

                                                <template x-for="child in (rootCat.children || [])" :key="child.id">
                                                    <div class="py-2.5 flex items-center justify-between gap-3 hover:bg-slate-50/80 transition -mx-4 px-4 rounded-lg" :class="child.deleted_at ? 'opacity-50' : (child.is_active === false ? 'opacity-60' : '')">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <span class="text-slate-300 font-mono text-xs select-none">&bull;</span>
                                                            <div class="min-w-0">
                                                                <div class="flex items-center gap-2 flex-wrap">
                                                                    <span class="font-bold text-xs text-slate-800" x-text="child.name"></span>
                                                                    <span class="font-mono text-[10px] text-slate-400" x-text="'/' + child.slug"></span>
                                                                    <span 
                                                                        class="px-1.5 py-0.2 rounded-full text-[9px] font-bold border"
                                                                        :class="child.deleted_at ? 'bg-rose-100 text-rose-800 border-rose-200' : (child.is_active !== false ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-slate-200 text-slate-600 border-slate-300')"
                                                                        x-text="child.deleted_at ? 'Archivada' : (child.is_active !== false ? 'Activa' : 'Inactiva')"
                                                                    ></span>
                                                                </div>
                                                                <p class="text-[11px] text-slate-500 truncate" x-text="child.description || 'Sin descripcion adicional'"></p>
                                                            </div>
                                                        </div>

                                                        <!-- Acciones Subcategoría -->
                                                        <div class="flex items-center gap-1.5 shrink-0">
                                                            <button type="button" @click="editCategory(child)" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[10px] font-bold transition cursor-pointer" title="Editar">
                                                                Editar
                                                            </button>
                                                            <button type="button" @click="toggleActiveCategory(child)" class="px-2 py-0.5 rounded text-[10px] font-bold transition cursor-pointer" :class="child.is_active !== false ? 'bg-amber-50 hover:bg-amber-100 text-amber-800' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800'" :title="child.is_active !== false ? 'Desactivar' : 'Activar'" x-text="child.is_active !== false ? 'Desactivar' : 'Activar'"></button>
                                                            <button type="button" @click="archiveCategory(child)" class="px-2 py-0.5 rounded text-[10px] font-bold transition cursor-pointer" :class="child.deleted_at ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800' : 'bg-amber-50 hover:bg-amber-100 text-amber-800'" :title="child.deleted_at ? 'Restaurar' : 'Archivar'" x-text="child.deleted_at ? 'Restaurar' : 'Archivar'"></button>
                                                            <button type="button" @click="deleteCategory(child.id)" class="px-2 py-0.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded text-[10px] font-bold transition cursor-pointer" title="Eliminar permanentemente">
                                                                Eliminar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- TAB 4: SLOTTING FÍSICO & UBICACIONES (NUEVA PESTAÑA CRUD - TASK-INV-11) -->
                    <template x-if="activeSettingsTab === 'slotting'">
                        <div class="space-y-4">
                            <!-- Formulario Alta / Edición de Ubicación -->
                            <div class="bg-indigo-50/60 p-4 rounded-xl border border-indigo-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-xs uppercase tracking-wider text-indigo-900" x-text="slotEditState.isEditing ? 'Editar Ubicación: ' + slotEditState.editingCode : 'Registrar Nueva Ubicación Física de Almacén'"></h4>
                                    <span x-show="slotEditState.isEditing" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">Modo Edición</span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Pasillo (A-Z)</label>
                                        <input type="text" maxlength="2" x-model="slotEditState.isEditing ? slotEditState.form.aisle : newSlotForm.aisle" :disabled="slotEditState.isEditing" placeholder="A" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg uppercase font-bold text-center">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Rack (01-99)</label>
                                        <input type="text" maxlength="2" x-model="slotEditState.isEditing ? slotEditState.form.rack : newSlotForm.rack" :disabled="slotEditState.isEditing" placeholder="01" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg font-mono text-center">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Gaveta / Nivel</label>
                                        <input type="text" maxlength="2" x-model="slotEditState.isEditing ? slotEditState.form.bin : newSlotForm.bin" :disabled="slotEditState.isEditing" placeholder="A" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg uppercase font-bold text-center">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Zona Operativa</label>
                                        <select x-model="slotEditState.isEditing ? slotEditState.form.zone : newSlotForm.zone" class="w-full px-2.5 py-1.5 text-xs bg-white border border-slate-300 rounded-lg">
                                            <option value="Ropa Interior">Ropa Interior</option>
                                            <option value="Atlético & Jocks">Atlético & Jocks</option>
                                            <option value="Skincare & Cuidado">Skincare & Cuidado</option>
                                            <option value="General">General</option>
                                            <option value="Cuarentena / RMA">Cuarentena / RMA</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-700 block mb-1">Capacidad Máx.</label>
                                        <input type="number" min="10" max="1000" x-model="slotEditState.isEditing ? slotEditState.form.capacity : newSlotForm.capacity" placeholder="100" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg text-center font-bold">
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <template x-if="!slotEditState.isEditing">
                                        <button type="button" @click="addNewSlot()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                            Guardar Ubicación
                                        </button>
                                    </template>
                                    <template x-if="slotEditState.isEditing">
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="saveSlot()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                                Actualizar Ubicación
                                            </button>
                                            <button type="button" @click="cancelEditSlot()" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-semibold transition cursor-pointer">
                                                Cancelar
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Tabla de Ubicaciones Físicas -->
                            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                                        <tr>
                                            <th class="py-2.5 px-3">Código Slot</th>
                                            <th class="py-2.5 px-3">Zona Operativa</th>
                                            <th class="py-2.5 px-3 text-center">Ocupación / Capacidad</th>
                                            <th class="py-2.5 px-3 text-center">SKUs</th>
                                            <th class="py-2.5 px-3 text-center">Estado</th>
                                            <th class="py-2.5 px-3 text-right">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="slot in warehouseSlots" :key="slot.code">
                                            <tr class="hover:bg-slate-50 transition" :class="slot.is_active === false ? 'opacity-60 bg-slate-50/50' : ''">
                                                <td class="py-2.5 px-3 font-mono font-black text-indigo-700" x-text="slot.code"></td>
                                                <td class="py-2.5 px-3 text-slate-700 font-semibold" x-text="slot.zone || 'General'"></td>
                                                <td class="py-2.5 px-3 text-center font-mono">
                                                    <span class="font-bold text-slate-900" x-text="getSlotOccupiedUnits(slot.code)"></span>
                                                    <span class="text-slate-400" x-text="' / ' + (slot.capacity || 100) + ' pzs'"></span>
                                                </td>
                                                <td class="py-2.5 px-3 text-center">
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200" x-text="getSlotProductCount(slot.code) + ' SKUs'"></span>
                                                </td>
                                                <td class="py-2.5 px-3 text-center">
                                                    <span 
                                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                                        :class="slot.is_active !== false ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-slate-200 text-slate-600 border-slate-300'"
                                                        x-text="slot.is_active !== false ? 'Activo' : 'Archivado'"
                                                    ></span>
                                                </td>
                                                <td class="py-2.5 px-3 text-right">
                                                    <div class="inline-flex items-center gap-1.5">
                                                        <button 
                                                            type="button" 
                                                            @click="editSlot(slot)" 
                                                            class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-bold transition cursor-pointer"
                                                            title="Editar ubicación"
                                                        >
                                                            Editar
                                                        </button>
                                                        <button 
                                                            type="button" 
                                                            @click="toggleArchiveSlot(slot)" 
                                                            class="px-2 py-1 rounded text-[11px] font-bold transition cursor-pointer"
                                                            :class="slot.is_active !== false ? 'bg-amber-50 hover:bg-amber-100 text-amber-800' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800'"
                                                            x-text="slot.is_active !== false ? 'Archivar' : 'Restaurar'"
                                                        ></button>
                                                        <button 
                                                            type="button" 
                                                            @click="deleteSlot(slot.code)" 
                                                            class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded text-[11px] font-bold transition cursor-pointer"
                                                            title="Eliminar ubicación"
                                                        >
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>

                    <!-- TAB 5: REGLAS DE ROP (NUMÉRICOS EDITABLES & RECÁLCULO EN VIVO - TASK-INV-11) -->
                    <template x-if="activeSettingsTab === 'rop'">
                        <div class="space-y-4">
                            <div class="p-5 bg-indigo-50/60 rounded-xl border border-indigo-200 space-y-4">
                                <div>
                                    <h4 class="font-bold text-xs uppercase tracking-wider text-indigo-900">Fórmula de Punto de Reorden (ROP RevOps)</h4>
                                    <p class="text-xs text-slate-600 mt-1">
                                        ROP = (Venta Diaria Promedio &times; Lead Time Días) + Días de Stock de Seguridad. Ajusta los parámetros operacionales y lanza el recálculo asíncrono en caliente sobre todo el catálogo.
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
                                        <label class="text-[10px] font-bold text-slate-500 block uppercase mb-1">Lead Time Local (Días)</label>
                                        <div class="flex items-center gap-2">
                                            <input type="number" min="1" max="60" x-model.number="ropSettings.leadTimeLocal" class="w-full px-3 py-1.5 text-sm font-extrabold text-slate-900 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            <span class="text-xs text-slate-400 font-semibold">días</span>
                                        </div>
                                    </div>
                                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
                                        <label class="text-[10px] font-bold text-slate-500 block uppercase mb-1">Lead Time Dropship (Días)</label>
                                        <div class="flex items-center gap-2">
                                            <input type="number" min="1" max="90" x-model.number="ropSettings.leadTimeDropship" class="w-full px-3 py-1.5 text-sm font-extrabold text-slate-900 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            <span class="text-xs text-slate-400 font-semibold">días</span>
                                        </div>
                                    </div>
                                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
                                        <label class="text-[10px] font-bold text-slate-500 block uppercase mb-1">Stock de Seguridad (Días)</label>
                                        <div class="flex items-center gap-2">
                                            <input type="number" min="1" max="60" x-model.number="ropSettings.safetyStockDays" class="w-full px-3 py-1.5 text-sm font-extrabold text-indigo-600 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            <span class="text-xs text-slate-400 font-semibold">días</span>
                                        </div>
                                    </div>
                                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
                                        <label class="text-[10px] font-bold text-slate-500 block uppercase mb-1">Venta Diaria Estándar</label>
                                        <div class="flex items-center gap-2">
                                            <input type="number" min="0.5" max="100" step="0.5" x-model.number="ropSettings.defaultDailySales" class="w-full px-3 py-1.5 text-sm font-extrabold text-slate-900 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            <span class="text-xs text-slate-400 font-semibold">pzs/d</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-2">
                                    <div class="text-xs text-slate-500">
                                        Al guardar, se recalcula inmediatamente el umbral de alerta en las tablas operativas de bodega y en el Kardex.
                                    </div>
                                    <button 
                                        type="button" 
                                        @click="saveRopSettings()" 
                                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-2"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <span>Guardar y Recalcular ROP Inmediatamente</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- TAB 6: PROVEEDORES & SCRAPERS CONFIGURADOS (CRUD COMPLETO Y PARÁMETROS) -->
                    <template x-if="activeSettingsTab === 'scraping'">
                        <div class="space-y-5">
                            <!-- Parámetros Torzo Bot Headless -->
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700">Parámetros de Extracción Torzo Bot</h4>
                                    <span class="text-[10px] text-indigo-700 font-semibold bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Torzo Bot v2.4 Headless</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-600 block mb-1">Frecuencia de Extracción Global</label>
                                        <select class="w-full px-2.5 py-1.5 text-xs bg-white border border-slate-300 rounded-lg">
                                            <option>Cada 15 minutos (Recomendado)</option>
                                            <option>Cada 30 minutos</option>
                                            <option>Cada 1 hora</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-semibold text-slate-600 block mb-1">Margen Mínimo Sugerido s/ Catálogo</label>
                                        <input type="text" value="50% s/ PVP" class="w-full px-2.5 py-1.5 text-xs bg-white border border-slate-300 rounded-lg">
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla Completa: Proveedores & Scrapers Configurados -->
                            <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
                                <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/50">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-bold text-slate-900">Proveedores & Scrapers Configurados</h4>
                                            <span class="text-[10px] px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 font-bold" x-text="providers.length + ' en catálogo'"></span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Gestión de conectores automatizados, talleres de confección y condiciones comerciales</p>
                                    </div>
                                    <button 
                                        type="button"
                                        @click="openCreateProviderModal()" 
                                        class="px-3.5 py-1.5 text-xs font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition cursor-pointer self-start sm:self-auto flex items-center gap-1.5 shadow-xs"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>+ Conectar Proveedor</span>
                                    </button>
                                </div>

                                <!-- Filtros y Buscador de Proveedores en Ajustes -->
                                <div class="px-4 py-2.5 bg-white border-b border-slate-200/80 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-2.5">
                                    <div class="relative flex-1 max-w-sm">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-slate-400">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </span>
                                        <input 
                                            type="text" 
                                            x-model="providerSearch" 
                                            placeholder="Buscar por nombre, código o ciudad..." 
                                            class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:bg-white focus:ring-1 focus:ring-indigo-500 text-slate-700"
                                        >
                                    </div>
                                    <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                                        <div class="flex items-center gap-1 text-xs text-slate-500">
                                            <span class="font-medium">Estado:</span>
                                            <select x-model="providerStatusFilter" class="bg-slate-50 border border-slate-200 text-xs rounded-lg px-2 py-1.5 text-slate-700 focus:outline-none focus:bg-white">
                                                <option value="all">Todos los estados</option>
                                                <option value="connected">Conectados / Activos</option>
                                                <option value="maintenance">En Mantenimiento</option>
                                                <option value="paused">Pausados / Inactivos</option>
                                                <option value="archived">Archivados / Descontinuados</option>
                                            </select>
                                        </div>
                                        <div class="flex items-center gap-1 text-xs text-slate-500">
                                            <span class="font-medium">Tipo:</span>
                                            <select x-model="providerTypeFilter" class="bg-slate-50 border border-slate-200 text-xs rounded-lg px-2 py-1.5 text-slate-700 focus:outline-none focus:bg-white">
                                                <option value="all">Todos los tipos</option>
                                                <option value="scraper">Scrapers & APIs</option>
                                                <option value="workshop">Talleres Confección</option>
                                                <option value="distributor">Distribuidores</option>
                                                <option value="commercial">Comerciales Directos</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabla con Registros Reactivos -->
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse text-xs">
                                        <thead>
                                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[11px] uppercase tracking-wider font-semibold">
                                                <th class="py-2.5 px-3">Proveedor</th>
                                                <th class="py-2.5 px-3">Método de Extracción</th>
                                                <th class="py-2.5 px-3">Frecuencia</th>
                                                <th class="py-2.5 px-3">Último Scrape</th>
                                                <th class="py-2.5 px-3 text-center">Items Sincronizados</th>
                                                <th class="py-2.5 px-3 text-center">Estado</th>
                                                <th class="py-2.5 px-3 text-right">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                            <template x-for="prov in filteredProviders" :key="prov.id">
                                                <tr class="hover:bg-slate-50 transition" :class="prov.status === 'archived' ? 'opacity-70 bg-slate-50/40' : ''">
                                                    <td class="py-2.5 px-3">
                                                        <div class="flex items-center gap-2">
                                                            <span 
                                                                class="w-2 h-2 rounded-full shrink-0"
                                                                :class="{
                                                                    'bg-emerald-500': prov.status === 'connected',
                                                                    'bg-amber-500': prov.status === 'maintenance',
                                                                    'bg-slate-300': prov.status === 'paused',
                                                                    'bg-slate-400': prov.status === 'archived'
                                                                }"
                                                            ></span>
                                                            <div>
                                                                <div class="flex items-center gap-1.5">
                                                                    <span class="font-bold text-slate-900" x-text="prov.tradeName || prov.name"></span>
                                                                    <span class="font-mono text-[10px] px-1 py-0.2 rounded bg-slate-100 text-slate-500 border border-slate-200" x-text="prov.vendorCode"></span>
                                                                    <span x-show="prov.status === 'archived'" class="text-[10px] px-1.5 py-0.2 rounded bg-slate-200 text-slate-600 font-semibold">Archivado</span>
                                                                </div>
                                                                <span class="text-[10px] text-slate-400 font-normal block" x-text="(prov.city || 'Nacional') + ' • ' + (prov.typeLabel || 'Proveedor')"></span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-2.5 px-3 font-mono text-[11px] text-slate-600" x-text="prov.extractionMethod"></td>
                                                    <td class="py-2.5 px-3 text-slate-600">
                                                        <span class="block text-xs" x-text="prov.frequency"></span>
                                                        <span class="text-[10px] text-slate-400" x-text="'Lead Time: ' + prov.leadTimeDays + ' d'"></span>
                                                    </td>
                                                    <td class="py-2.5 px-3 text-slate-500" x-text="prov.lastScrapeTime"></td>
                                                    <td class="py-2.5 px-3 text-center font-bold text-slate-800" x-text="(prov.syncedItemsCount || 0) + ' SKUs'"></td>
                                                    <td class="py-2.5 px-3 text-center">
                                                        <span 
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold border"
                                                            :class="{
                                                                'bg-emerald-50 text-emerald-700 border-emerald-200': prov.status === 'connected',
                                                                'bg-amber-50 text-amber-700 border-amber-200': prov.status === 'maintenance',
                                                                'bg-slate-100 text-slate-600 border-slate-200': prov.status === 'paused',
                                                                'bg-slate-200 text-slate-600 border-slate-300': prov.status === 'archived'
                                                            }"
                                                        >
                                                            <span 
                                                                class="w-1.5 h-1.5 rounded-full"
                                                                :class="{
                                                                    'bg-emerald-500': prov.status === 'connected',
                                                                    'bg-amber-500': prov.status === 'maintenance',
                                                                    'bg-slate-400': prov.status === 'paused',
                                                                    'bg-slate-500': prov.status === 'archived'
                                                                }"
                                                            ></span>
                                                            <span x-text="prov.status === 'connected' ? 'Conectado' : (prov.status === 'maintenance' ? 'Mantenimiento' : (prov.status === 'paused' ? 'Pausado' : 'Archivado'))"></span>
                                                        </span>
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right">
                                                        <div class="inline-flex items-center gap-1">
                                                            <button 
                                                                type="button"
                                                                @click="openProviderDetails(prov)" 
                                                                class="px-2 py-1 text-xs font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-lg transition cursor-pointer shadow-3xs"
                                                                title="Ver ficha técnica, condiciones comerciales y Scorecard de SLA"
                                                            >
                                                                Detalles / SLA
                                                            </button>
                                                            <template x-if="prov.status !== 'archived'">
                                                                <div class="inline-flex items-center gap-1">
                                                                    <button 
                                                                        type="button"
                                                                        x-show="prov.type === 'scraper' || prov.extractionMethod.includes('API') || prov.extractionMethod.includes('Webhook')"
                                                                        @click="triggerProviderSync(prov)" 
                                                                        :disabled="prov.status === 'paused' || prov.isSyncing"
                                                                        class="px-2 py-1 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1"
                                                                    >
                                                                        <svg x-show="prov.isSyncing" class="w-3 h-3 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                                        <span x-text="prov.isSyncing ? 'Sync...' : 'Forzar Sync'"></span>
                                                                    </button>
                                                                    <button 
                                                                        type="button"
                                                                        @click="archiveProvider(prov)"
                                                                        class="p-1 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition cursor-pointer"
                                                                        title="Archivar proveedor"
                                                                    >
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                                                    </button>
                                                                </div>
                                                            </template>
                                                            <template x-if="prov.status === 'archived'">
                                                                <div class="inline-flex items-center gap-1">
                                                                    <button 
                                                                        type="button"
                                                                        @click="restoreProvider(prov)"
                                                                        class="px-2 py-1 text-xs font-semibold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg transition cursor-pointer flex items-center gap-1"
                                                                        title="Restaurar proveedor a estado Conectado"
                                                                    >
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                                        <span>Restaurar</span>
                                                                    </button>
                                                                    <button 
                                                                        type="button"
                                                                        @click="deleteProvider(prov)"
                                                                        class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer"
                                                                        title="Eliminar definitivamente"
                                                                    >
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                    </button>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </template>
                                            <tr x-show="filteredProviders.length === 0">
                                                <td colspan="7" class="py-6 text-center text-slate-400 text-xs">
                                                    No se encontraron proveedores que coincidan con los filtros seleccionados.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- TAB 7: SECCIONES WEB -->
                    <template x-if="activeSettingsTab === 'sections'">
                        <div class="space-y-4">
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                                <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700">Secciones del Sitio Web E-Commerce</h4>
                                <ul class="space-y-2 text-xs">
                                    <li class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                                        <span class="font-bold text-slate-800">Línea Básicos Esenciales</span>
                                        <span class="text-slate-400">Prendas lisas de alta rotación</span>
                                    </li>
                                    <li class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                                        <span class="font-bold text-slate-800">Línea Diseñador & Autor</span>
                                        <span class="text-slate-400">Prendas con diseño, estampados y tornasoles</span>
                                    </li>
                                    <li class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                                        <span class="font-bold text-slate-800">Colección Temporada / Drops</span>
                                        <span class="text-slate-400">Lanzamientos de edición limitada</span>
                                    </li>
                                    <li class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                                        <span class="font-bold text-slate-800">Catálogo General</span>
                                        <span class="text-slate-400">Vitrina multiverso estándar</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </template>

                    <!-- TAB 8: RECETAS DE ENSAMBLE BOM & COMBOS VIRTUALES (CRUD COMPLETO) -->
                    <template x-if="activeSettingsTab === 'bundles'">
                        <div class="space-y-5">
                            <!-- Cabecera de la Sección -->
                            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 p-5 rounded-xl border border-slate-700 text-white flex items-center justify-between flex-wrap gap-4 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-300 shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-base font-bold text-white leading-none">Gestor de Recetas BOM & Combos Virtuales</h4>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30" x-text="productBundles.length + ' Fórmulas'"></span>
                                        </div>
                                        <p class="text-xs text-slate-300 mt-1">Configuración y despiece de cajas de suscripción y packs promocionales. El almacenista decide qué prendas componen cada fórmula y sus proporciones unitarias.</p>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    @click="openNewBundleModal()" 
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer shrink-0"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>+ Nueva Receta / Combo</span>
                                </button>
                            </div>

                            <!-- KPIs Resumen de Fórmulas -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Fórmulas BOM</span>
                                    <span class="text-xl font-black text-slate-900 mt-0.5 block" x-text="productBundles.length"></span>
                                    <span class="text-[10px] text-slate-500 font-medium">Catálogo completo</span>
                                </div>
                                <div class="bg-emerald-50/50 p-3.5 rounded-xl border border-emerald-200 shadow-2xs">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 block">Recetas Activas</span>
                                    <span class="text-xl font-black text-emerald-700 mt-0.5 block" x-text="productBundles.filter(b => b.isActive !== false).length"></span>
                                    <span class="text-[10px] text-emerald-600 font-medium">En comercialización</span>
                                </div>
                                <div class="bg-amber-50/50 p-3.5 rounded-xl border border-amber-200 shadow-2xs">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 block">Recetas Archivadas</span>
                                    <span class="text-xl font-black text-amber-700 mt-0.5 block" x-text="productBundles.filter(b => b.isActive === false).length"></span>
                                    <span class="text-[10px] text-amber-600 font-medium">Pausadas / histórico</span>
                                </div>
                                <div class="bg-indigo-50/50 p-3.5 rounded-xl border border-indigo-200 shadow-2xs">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-800 block">Insumos Físicos</span>
                                    <span class="text-xl font-black text-indigo-700 mt-0.5 block" x-text="productBundles.reduce((acc, b) => acc + (b.components ? b.components.length : 0), 0)"></span>
                                    <span class="text-[10px] text-indigo-600 font-medium">Prendas vinculadas</span>
                                </div>
                            </div>

                            <!-- Filtros y Barra de Búsqueda -->
                            <div class="flex items-center justify-between flex-wrap gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <!-- Filtro Estado -->
                                    <div class="flex items-center bg-white p-1 rounded-lg border border-slate-200 shadow-2xs">
                                        <button 
                                            type="button" 
                                            @click="bundleEditor.filterStatus = 'active'" 
                                            class="px-2.5 py-1 text-xs font-bold rounded-md transition cursor-pointer"
                                            :class="bundleEditor.filterStatus === 'active' ? 'bg-indigo-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                        >
                                            Activas (<span x-text="productBundles.filter(b => b.isActive !== false).length"></span>)
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="bundleEditor.filterStatus = 'archived'" 
                                            class="px-2.5 py-1 text-xs font-bold rounded-md transition cursor-pointer"
                                            :class="bundleEditor.filterStatus === 'archived' ? 'bg-indigo-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                        >
                                            Archivadas (<span x-text="productBundles.filter(b => b.isActive === false).length"></span>)
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="bundleEditor.filterStatus = 'all'" 
                                            class="px-2.5 py-1 text-xs font-bold rounded-md transition cursor-pointer"
                                            :class="bundleEditor.filterStatus === 'all' ? 'bg-indigo-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                        >
                                            Todas
                                        </button>
                                    </div>

                                    <!-- Filtro Tipo -->
                                    <div class="flex items-center bg-white p-1 rounded-lg border border-slate-200 shadow-2xs">
                                        <button 
                                            type="button" 
                                            @click="bundleEditor.filterType = 'all'" 
                                            class="px-2.5 py-1 text-xs font-bold rounded-md transition cursor-pointer"
                                            :class="bundleEditor.filterType === 'all' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                        >
                                            Todos los Tipos
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="bundleEditor.filterType = 'subscription'" 
                                            class="px-2.5 py-1 text-xs font-bold rounded-md transition cursor-pointer"
                                            :class="bundleEditor.filterType === 'subscription' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                        >
                                            Cajas Suscripción
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="bundleEditor.filterType = 'promo'" 
                                            class="px-2.5 py-1 text-xs font-bold rounded-md transition cursor-pointer"
                                            :class="bundleEditor.filterType === 'promo' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                        >
                                            Packs Promocionales
                                        </button>
                                    </div>
                                </div>

                                <!-- Buscador -->
                                <div class="relative min-w-[240px]">
                                    <input 
                                        type="text" 
                                        x-model="bundleEditor.searchQuery" 
                                        placeholder="Buscar por SKU, combo o prenda..." 
                                        class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 shadow-2xs"
                                    >
                                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                            </div>

                            <!-- Listado de Tarjetas de Recetas BOM -->
                            <div class="space-y-4">
                                <template x-for="bundle in getFilteredProductBundles()" :key="bundle.id">
                                    <div 
                                        class="p-4 bg-white rounded-xl border transition-all shadow-xs"
                                        :class="bundle.isActive === false ? 'border-dashed border-slate-300 bg-slate-50/60 opacity-80' : 'border-slate-200 hover:border-indigo-300 hover:shadow-sm'"
                                    >
                                        <div class="flex items-start justify-between flex-wrap gap-3 pb-3 border-b border-slate-100">
                                            <div class="flex items-start gap-3">
                                                <div 
                                                    class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                                                    :class="bundle.type === 'subscription' ? 'bg-indigo-50 text-indigo-600 border border-indigo-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200'"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                </div>
                                                <div>
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="text-xs font-mono font-bold text-slate-500" x-text="bundle.sku || bundle.id"></span>
                                                        <span 
                                                            class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider"
                                                            :class="bundle.type === 'subscription' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800'"
                                                            x-text="bundle.type === 'subscription' ? 'Caja Suscripción' : 'Pack Promocional'"
                                                        ></span>
                                                        <span 
                                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                            :class="bundle.isActive !== false ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-200 text-slate-600'"
                                                            x-text="bundle.isActive !== false ? 'Activa' : 'Archivada'"
                                                        ></span>
                                                        <template x-if="bundle.discountPct > 0">
                                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200" x-text="'-' + bundle.discountPct + '% Ahorro'"></span>
                                                        </template>
                                                    </div>
                                                    <h5 class="text-sm font-bold text-slate-900 mt-1 mb-0.5" x-text="bundle.name"></h5>
                                                    <p class="text-xs text-slate-500 m-0" x-text="bundle.description"></p>
                                                </div>
                                            </div>

                                            <!-- Precios & Capacidad -->
                                            <div class="flex items-center gap-4 text-right shrink-0">
                                                <div>
                                                    <div class="text-base font-extrabold text-indigo-700" x-text="'$' + (bundle.subscriptionPrice || 0).toFixed(2) + ' MXN'"></div>
                                                    <template x-if="bundle.regularPrice > bundle.subscriptionPrice">
                                                        <div class="text-[11px] text-slate-400 line-through" x-text="'$' + (bundle.regularPrice || 0).toFixed(2)"></div>
                                                    </template>
                                                </div>

                                                <!-- Capacidad de Armado Inmediato -->
                                                <div class="p-2.5 rounded-xl border bg-slate-50 min-w-[140px] text-center"
                                                    :class="calculateBundleStock(bundle) > 5 ? 'border-emerald-200 bg-emerald-50/40' : (calculateBundleStock(bundle) > 0 ? 'border-amber-200 bg-amber-50/40' : 'border-rose-200 bg-rose-50/40')"
                                                >
                                                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-500 block">Capacidad Inmediata</span>
                                                    <div class="flex items-center justify-center gap-1.5 mt-0.5">
                                                        <span 
                                                            class="w-2 h-2 rounded-full"
                                                            :class="calculateBundleStock(bundle) > 5 ? 'bg-emerald-500' : (calculateBundleStock(bundle) > 0 ? 'bg-amber-500' : 'bg-rose-500')"
                                                        ></span>
                                                        <strong 
                                                            class="text-sm font-black"
                                                            :class="calculateBundleStock(bundle) > 5 ? 'text-emerald-800' : (calculateBundleStock(bundle) > 0 ? 'text-amber-800' : 'text-rose-800')"
                                                            x-text="calculateBundleStock(bundle) + ' cajas'"
                                                        ></strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Desglose de Insumos Físicos de la Fórmula BOM (Decisión del Almacenista) -->
                                        <div class="mt-3">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                                                    <span>Prendas Físicas que Componen la Fórmula BOM</span>
                                                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600" x-text="(bundle.components ? bundle.components.length : 0) + ' items'"></span>
                                                </span>
                                                <span class="text-[10px] text-slate-400">Deducción atómica en Kardex por cada caja ensamblada</span>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                                <template x-for="(comp, cIdx) in (bundle.components || [])" :key="cIdx">
                                                    <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200 flex items-center justify-between gap-2 text-xs">
                                                        <div>
                                                            <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                                                <span class="font-mono text-indigo-600" x-text="comp.sku"></span>
                                                                <span class="text-slate-400">&bull;</span>
                                                                <span class="uppercase text-[10px] px-1 py-0.2 bg-white rounded border border-slate-200 font-semibold text-slate-700" x-text="'Talla: ' + (comp.size || 'M')"></span>
                                                            </div>
                                                            <div class="text-[11px] text-slate-600 truncate max-w-[200px]" x-text="comp.name"></div>
                                                            <div class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5">
                                                                <span>Rack:</span>
                                                                <span class="font-mono font-semibold text-slate-600" x-text="comp.location || 'A-01-A'"></span>
                                                                <span>&bull;</span>
                                                                <span>Stock físico:</span>
                                                                <strong 
                                                                    :class="getBundleComponentStock(comp.sku, comp.size) >= comp.qty ? 'text-emerald-700 font-bold' : 'text-rose-700 font-bold'"
                                                                    x-text="getBundleComponentStock(comp.sku, comp.size) + ' uds'"
                                                                ></strong>
                                                            </div>
                                                        </div>
                                                        <div class="text-right shrink-0">
                                                            <span class="px-2 py-1 rounded-md bg-white border border-slate-200 text-indigo-900 font-mono font-bold text-xs shadow-2xs block" x-text="comp.qty + ' ud/caja'"></span>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Barra de Acciones de la Receta -->
                                        <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between flex-wrap gap-2">
                                            <div class="text-[11px] text-slate-400 font-medium">
                                                ID Fórmula: <span class="font-mono" x-text="bundle.id"></span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <!-- Botón Editar -->
                                                <button 
                                                    type="button" 
                                                    @click="openEditBundleModal(bundle.id)" 
                                                    class="px-3 py-1.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg shadow-2xs transition flex items-center gap-1.5 cursor-pointer"
                                                >
                                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    <span>Editar Receta & Insumos</span>
                                                </button>

                                                <!-- Botón Archivar / Reactivar -->
                                                <button 
                                                    type="button" 
                                                    @click="archiveBundleRecipe(bundle.id)" 
                                                    class="px-3 py-1.5 text-xs font-bold rounded-lg border shadow-2xs transition flex items-center gap-1.5 cursor-pointer"
                                                    :class="bundle.isActive !== false ? 'text-amber-800 bg-amber-50 hover:bg-amber-100 border-amber-200' : 'text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border-emerald-200'"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                                    <span x-text="bundle.isActive !== false ? 'Archivar' : 'Reactivar'"></span>
                                                </button>

                                                <!-- Botón Eliminar -->
                                                <button 
                                                    type="button" 
                                                    @click="deleteBundleRecipe(bundle.id)" 
                                                    class="px-3 py-1.5 text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg shadow-2xs transition flex items-center gap-1.5 cursor-pointer"
                                                >
                                                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    <span>Eliminar</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Empty State -->
                                <template x-if="getFilteredProductBundles().length === 0">
                                    <div class="p-8 text-center border-2 border-dashed border-slate-200 rounded-xl bg-slate-50">
                                        <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </div>
                                        <h5 class="text-sm font-bold text-slate-800">No se encontraron recetas con los filtros seleccionados</h5>
                                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Puedes modificar los criterios de filtro o dar de alta una nueva fórmula BOM con el botón superior.</p>
                                        <button 
                                            type="button" 
                                            @click="openNewBundleModal()" 
                                            class="mt-3 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow-2xs transition cursor-pointer"
                                        >
                                            + Crear Nueva Receta
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <!-- ========================================================= -->
                            <!-- SUB-MODAL / EDITOR DE RECETA BOM (DECISIÓN DEL ALMACENISTA) -->
                            <!-- ========================================================= -->
                            <div 
                                x-show="bundleEditor.isOpen" 
                                class="fixed inset-0 z-[95] overflow-y-auto" 
                                style="z-index: 95; display: none;"
                                @keydown.escape.window="bundleEditor.isOpen = false"
                            >
                                <!-- Backdrop -->
                                <div 
                                    x-show="bundleEditor.isOpen"
                                    x-transition:enter="ease-out duration-300"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in duration-200"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    @click="bundleEditor.isOpen = false"
                                    class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs transition-opacity cursor-pointer"
                                ></div>

                                <div class="min-h-screen px-4 text-center flex items-center justify-center p-0">
                                    <div 
                                        x-show="bundleEditor.isOpen"
                                        x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="ease-in duration-200"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="inline-block w-full max-w-4xl bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-8 align-middle border border-slate-200 z-10"
                                        @click.stop
                                    >
                                        <!-- Header Editor -->
                                        <div class="px-6 py-4 border-b border-slate-700 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-300 shrink-0">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </div>
                                                <div>
                                                    <h3 class="text-base font-bold text-white leading-none" x-text="bundleEditor.isEditing ? 'Editar Receta BOM: ' + bundleEditor.form.name : 'Nueva Receta de Ensamble BOM & Combo'"></h3>
                                                    <p class="text-xs text-indigo-200 mt-1">El almacenista selecciona qué prendas físicas componen este combo y las proporciones a deducir en Kardex.</p>
                                                </div>
                                            </div>
                                            <button @click="bundleEditor.isOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-white transition cursor-pointer">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>

                                        <!-- Body Editor -->
                                        <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto text-sm">
                                            <!-- Sección 1: Datos Generales del Combo -->
                                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3 flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                                    <span>1. Identidad Comercial del Combo</span>
                                                </h4>
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 mb-1">SKU Padre del Combo *</label>
                                                        <input 
                                                            type="text" 
                                                            x-model="bundleEditor.form.sku" 
                                                            placeholder="ej. SUB-BOX-MARTE-LVL1" 
                                                            class="w-full px-3 py-1.5 text-xs font-mono font-bold bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 uppercase"
                                                            :disabled="bundleEditor.isEditing"
                                                        >
                                                        <span class="text-[10px] text-slate-400 mt-0.5 block">Identificador inmutable para pedidos y guías</span>
                                                    </div>
                                                    <div class="sm:col-span-2">
                                                        <label class="block text-xs font-bold text-slate-700 mb-1">Nombre Comercial de la Receta / Kit *</label>
                                                        <input 
                                                            type="text" 
                                                            x-model="bundleEditor.form.name" 
                                                            placeholder="ej. Caja Mensual Marte Nivel 2: Estilo & Cuidado" 
                                                            class="w-full px-3 py-1.5 text-xs font-bold bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                        >
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-3">
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipo de Combo</label>
                                                        <select 
                                                            x-model="bundleEditor.form.type" 
                                                            class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                        >
                                                            <option value="subscription">Caja de Suscripción</option>
                                                            <option value="promo">Pack Promocional</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 mb-1">Precio de Venta ($ MXN) *</label>
                                                        <input 
                                                            type="number" 
                                                            step="0.01" 
                                                            x-model.number="bundleEditor.form.subscriptionPrice" 
                                                            class="w-full px-3 py-1.5 text-xs font-bold text-indigo-700 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                        >
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 mb-1">Precio Regular de Referencia</label>
                                                        <input 
                                                            type="number" 
                                                            step="0.01" 
                                                            x-model.number="bundleEditor.form.regularPrice" 
                                                            class="w-full px-3 py-1.5 text-xs text-slate-600 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                        >
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 mb-1">Nivel / Sub-etiqueta</label>
                                                        <input 
                                                            type="text" 
                                                            x-model="bundleEditor.form.tierName" 
                                                            placeholder="ej. Nivel 2: Estilo" 
                                                            class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                        >
                                                    </div>
                                                </div>

                                                <div class="mt-3">
                                                    <label class="block text-xs font-bold text-slate-700 mb-1">Descripción para el Equipo de Empaque</label>
                                                    <textarea 
                                                        x-model="bundleEditor.form.description" 
                                                        rows="2" 
                                                        placeholder="Instrucciones de ensamble, presentación y características del kit..."
                                                        class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                    ></textarea>
                                                </div>
                                            </div>

                                            <!-- Sección 2: Selección de Prendas Físicas (Decisión del Almacenista) -->
                                            <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-200">
                                                <div class="flex items-center justify-between pb-2 mb-3 border-b border-indigo-200/60">
                                                    <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-950 flex items-center gap-2">
                                                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                                        <span>2. Selección de Prendas Insumo (Decisión del Almacenista)</span>
                                                    </h4>
                                                    <span class="text-[11px] text-indigo-800 font-semibold">Elige del catálogo operativo qué prendas integran la caja</span>
                                                </div>

                                                <!-- Selector de Prenda -->
                                                <div class="bg-white p-3 rounded-lg border border-indigo-200 shadow-2xs grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-end">
                                                    <div class="sm:col-span-6">
                                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Prenda del Catálogo Operativo</label>
                                                        <select 
                                                            x-model="bundleEditor.newComponent.sku" 
                                                            class="w-full px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                        >
                                                            <template x-for="prod in products" :key="prod.sku">
                                                                <option :value="prod.sku" x-text="'[' + prod.sku + '] ' + prod.name + ' • Stock Rack: ' + prod.localStock + ' uds (' + (prod.location || 'Rack') + ')'"></option>
                                                            </template>
                                                        </select>
                                                    </div>

                                                    <div class="sm:col-span-2">
                                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Talla / Variante</label>
                                                        <select 
                                                            x-model="bundleEditor.newComponent.size" 
                                                            class="w-full px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 uppercase font-mono"
                                                        >
                                                            <option value="ch">CH</option>
                                                            <option value="m">M</option>
                                                            <option value="g">G</option>
                                                            <option value="xl">XL</option>
                                                            <option value="unit">Estándar / Única</option>
                                                            <option value="v10">V10 (Cosmética)</option>
                                                        </select>
                                                    </div>

                                                    <div class="sm:col-span-2">
                                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Cant / Caja</label>
                                                        <input 
                                                            type="number" 
                                                            min="1" 
                                                            max="20" 
                                                            x-model.number="bundleEditor.newComponent.qty" 
                                                            class="w-full px-2.5 py-1.5 text-xs font-bold text-center bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                        >
                                                    </div>

                                                    <div class="sm:col-span-2">
                                                        <button 
                                                            type="button" 
                                                            @click="addBundleComponentToForm()" 
                                                            class="w-full py-1.5 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow-2xs transition flex items-center justify-center gap-1 cursor-pointer"
                                                        >
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                            <span>Asignar</span>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Tabla de Prendas Asignadas -->
                                                <div class="mt-3 overflow-hidden rounded-lg border border-slate-200 bg-white">
                                                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                                                        <thead class="bg-slate-50 font-bold text-slate-600">
                                                            <tr>
                                                                <th class="py-2 px-3 text-left">SKU Prenda</th>
                                                                <th class="py-2 px-3 text-left">Nombre del Producto</th>
                                                                <th class="py-2 px-3 text-center">Talla</th>
                                                                <th class="py-2 px-3 text-left">Ubicación Rack</th>
                                                                <th class="py-2 px-3 text-right">Stock Disponible</th>
                                                                <th class="py-2 px-3 text-center">Cant / Caja</th>
                                                                <th class="py-2 px-3 text-center">Acción</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-slate-100">
                                                            <template x-for="(comp, cIdx) in bundleEditor.form.components" :key="cIdx">
                                                                <tr class="hover:bg-slate-50/80">
                                                                    <td class="py-2 px-3 font-mono font-bold text-indigo-600" x-text="comp.sku"></td>
                                                                    <td class="py-2 px-3 text-slate-800 font-semibold" x-text="comp.name"></td>
                                                                    <td class="py-2 px-3 text-center uppercase font-mono font-bold text-slate-600" x-text="comp.size || 'M'"></td>
                                                                    <td class="py-2 px-3 text-slate-500 font-mono" x-text="comp.location || 'A-01-A'"></td>
                                                                    <td class="py-2 px-3 text-right font-mono font-bold"
                                                                        :class="getBundleComponentStock(comp.sku, comp.size) >= comp.qty ? 'text-emerald-700' : 'text-rose-700'"
                                                                        x-text="getBundleComponentStock(comp.sku, comp.size) + ' uds'"
                                                                    ></td>
                                                                    <td class="py-2 px-3 text-center">
                                                                        <input 
                                                                            type="number" 
                                                                            min="1" 
                                                                            max="20" 
                                                                            x-model.number="comp.qty" 
                                                                            class="w-14 py-0.5 px-1 text-center font-bold text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                                        >
                                                                    </td>
                                                                    <td class="py-2 px-3 text-center">
                                                                        <button 
                                                                            type="button" 
                                                                            @click="removeBundleComponentFromForm(cIdx)" 
                                                                            class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded transition cursor-pointer"
                                                                            title="Quitar prenda de la receta"
                                                                        >
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            </template>
                                                        </tbody>
                                                    </table>

                                                    <!-- Si no hay componentes -->
                                                    <template x-if="!bundleEditor.form.components || bundleEditor.form.components.length === 0">
                                                        <div class="p-6 text-center text-slate-400 bg-slate-50/50">
                                                            <svg class="w-6 h-6 mx-auto mb-1 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                            <span class="font-bold text-slate-600 block text-xs">Aún no has agregado prendas a esta receta</span>
                                                            <span class="text-[11px] text-slate-500">Selecciona una prenda del catálogo en el cuadro superior y presiona "Asignar".</span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer Editor -->
                                        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                                            <div class="text-xs text-slate-500">
                                                <span class="font-bold text-slate-700" x-text="bundleEditor.form.components.length + ' prendas asignadas'"></span>
                                                <span>a esta receta BOM</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button 
                                                    type="button" 
                                                    @click="bundleEditor.isOpen = false" 
                                                    class="px-4 py-2 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 rounded-xl text-xs font-bold transition cursor-pointer"
                                                >
                                                    Cancelar
                                                </button>
                                                <button 
                                                    type="button" 
                                                    @click="saveBundleRecipe()" 
                                                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5 cursor-pointer"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    <span x-text="bundleEditor.isEditing ? 'Guardar Cambios de Receta' : 'Crear Receta BOM'"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

            </div>
        </div>

        <!-- Full-Screen Workstation Footer -->
        <footer class="px-6 py-3 bg-white border-t border-slate-200 flex justify-between items-center shrink-0">
            <button 
                type="button" 
                @click="moduleSettingsModalOpen = false" 
                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-lg hover:bg-slate-100 transition cursor-pointer flex items-center gap-1.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver al Inventario</span>
            </button>
            <button 
                type="button" 
                @click="moduleSettingsModalOpen = false" 
                class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition cursor-pointer shadow-xs"
            >
                Cerrar Ajustes
            </button>
        </footer>
    </div>

    <!-- ============================================================= -->
    <!-- MODAL DE CURADURÍA PREVIA DE SCRAPING                          -->
    <!-- ============================================================= -->
    <div 
        x-show="scrapingCurationModalOpen" 
        class="fixed inset-0 z-[85] overflow-y-auto" 
        style="z-index: 85; display: none;"
        aria-labelledby="curation-modal-title" 
        role="dialog" 
        aria-modal="true"
        @keydown.escape.window="scrapingCurationModalOpen = false"
    >
        <div 
            x-show="scrapingCurationModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="scrapingCurationModalOpen = false"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity cursor-pointer"
        ></div>

        <div class="min-h-screen px-4 text-center flex items-center justify-center p-0">
            <div 
                x-show="scrapingCurationModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="inline-block w-full max-w-3xl bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-8 align-middle border border-slate-200 z-10"
                @click.stop
            >
                <!-- Curation Header -->
                <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="p-2 bg-indigo-500/20 text-indigo-300 rounded-xl border border-indigo-500/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-white" id="curation-modal-title">Validación & Curaduría de Prenda Scrapeada</h3>
                            <p class="text-xs text-indigo-200">Revisa la descripción y asigna color exacto, tela y sección antes de publicar</p>
                        </div>
                    </div>
                    <button @click="scrapingCurationModalOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-white transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Curation Body -->
                <template x-if="curatingItem">
                    <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto text-sm">
                        <!-- Top Banner de la prenda -->
                        <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <div>
                                <span class="text-xs font-mono font-bold text-indigo-600" x-text="curatingItem.suggestedSku"></span>
                                <h4 class="font-bold text-slate-900 text-base mt-0.5" x-text="curatingItem.name"></h4>
                                <span class="text-xs text-slate-500" x-text="'Proveedor: ' + curatingItem.brand + ' • ' + curatingItem.suggestedCategory"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-slate-400 block">Costo Mayorista</span>
                                <span class="text-lg font-extrabold text-emerald-600 font-mono" x-text="'$' + curatingItem.cost.toFixed(2)"></span>
                            </div>
                        </div>

                        <!-- 1. Validación / Edición de la Descripción Scrapeada -->
                        <div>
                            <label class="font-bold text-xs uppercase tracking-wider text-slate-700 block mb-1">Descripción del Producto (Validación Humana)</label>
                            <textarea 
                                x-model="curatingItem.scrapedDescription" 
                                rows="3" 
                                class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                placeholder="Verifica que la descripción técnica y comercial sea correcta..."
                            ></textarea>
                        </div>

                        <!-- 2. Selección de Color / Tornasol & Tipo de Tela -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Selector de Color / Tornasol -->
                            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2">
                                <label class="font-bold text-xs uppercase tracking-wider text-slate-700 block">Color / Tornasol Exacto</label>
                                <div class="flex items-center gap-2 p-2 bg-white rounded-lg border border-slate-200">
                                    <span class="w-6 h-6 rounded-md border border-slate-300 shrink-0" :style="'background: ' + curatingItem.detectedColorHex"></span>
                                    <span class="text-xs font-bold text-slate-900" x-text="curatingItem.detectedColorName"></span>
                                </div>
                                <select 
                                    @change="
                                        const c = apparelColors.find(x => x.name === $event.target.value);
                                        if (c) {
                                            curatingItem.detectedColorName = c.name;
                                            curatingItem.detectedColorHex = c.hex;
                                            curatingItem.colorType = c.type;
                                        }
                                    "
                                    class="w-full p-2 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-800"
                                >
                                    <template x-for="col in apparelColors" :key="col.id">
                                        <option :value="col.name" :selected="col.name === curatingItem.detectedColorName" x-text="col.name + ' (' + (col.type === 'iridescent' ? 'Tornasol' : 'Sólido') + ')'"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Selector de Tela -->
                            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2">
                                <label class="font-bold text-xs uppercase tracking-wider text-slate-700 block">Tipo de Tela</label>
                                <select 
                                    x-model="curatingItem.suggestedFabric" 
                                    class="w-full p-2 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-800"
                                >
                                    <template x-for="fab in apparelFabrics" :key="fab.id">
                                        <option :value="fab.name" x-text="fab.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- 3. Diseño & Sección Web -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Variante Diseño -->
                            <div>
                                <label class="font-bold text-xs uppercase tracking-wider text-slate-700 block mb-1">Variante de Diseño</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button 
                                        type="button" 
                                        @click="curatingItem.suggestedDesign = 'sin_diseno'"
                                        class="p-2 rounded-lg text-xs font-bold border text-center transition cursor-pointer"
                                        :class="curatingItem.suggestedDesign === 'sin_diseno' ? 'bg-indigo-50 border-indigo-600 text-indigo-700' : 'bg-slate-50 text-slate-600 border-slate-200'"
                                    >
                                        Sin Diseño (Liso)
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="curatingItem.suggestedDesign = 'con_diseno'"
                                        class="p-2 rounded-lg text-xs font-bold border text-center transition cursor-pointer"
                                        :class="curatingItem.suggestedDesign === 'con_diseno' ? 'bg-purple-50 border-purple-600 text-purple-700' : 'bg-slate-50 text-slate-600 border-slate-200'"
                                    >
                                        Con Diseño (Pattern)
                                    </button>
                                </div>
                            </div>

                            <!-- Sección Web -->
                            <div>
                                <label class="font-bold text-xs uppercase tracking-wider text-slate-700 block mb-1">Sección Web de Publicación</label>
                                <select 
                                    x-model="curatingItem.suggestedSection" 
                                    class="w-full p-2 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-800"
                                >
                                    <option value="basicos">Línea Básicos Esenciales</option>
                                    <option value="disenador">Línea Diseñador & Autor</option>
                                    <option value="temporada">Colección Temporada</option>
                                    <option value="general">Catálogo General</option>
                                </select>
                            </div>
                        </div>

                        <!-- 4. PVP Sugerido & Margen Calculado -->
                        <div class="p-3.5 bg-slate-900 text-white rounded-xl border border-slate-800 flex items-center justify-between">
                            <div>
                                <label class="text-[11px] text-slate-400 block">PVP Venta al Público</label>
                                <div class="flex items-center gap-1 mt-0.5">
                                    <span class="text-slate-400 text-sm">$</span>
                                    <input type="number" x-model.number="curatingItem.suggestedPvp" class="w-24 bg-slate-800 border border-slate-700 rounded px-2 py-1 text-sm font-bold text-white font-mono">
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-[11px] text-slate-400 block">Margen Comercial Estimado</span>
                                <span class="text-base font-extrabold text-emerald-400 font-mono" x-text="(((curatingItem.suggestedPvp - curatingItem.cost) / curatingItem.suggestedPvp) * 100).toFixed(1) + '%'"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Curation Footer -->
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                    <button 
                        type="button" 
                        @click="discardScrapedItem(curatingItem.id)" 
                        class="px-4 py-2 text-rose-600 hover:bg-rose-50 rounded-xl text-xs font-bold transition cursor-pointer"
                    >
                        Descartar Prenda
                    </button>
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            @click="scrapingCurationModalOpen = false" 
                            class="px-4 py-2 text-slate-600 hover:text-slate-800 text-xs font-semibold transition cursor-pointer"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="button" 
                            @click="approveScrapedItem()" 
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Aprobar y Publicar en Catálogo</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="{{ asset('js/revops-panel/inventory-module.js') }}?v=20260927"></script>
@endpush
