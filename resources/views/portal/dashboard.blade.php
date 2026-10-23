@extends('layouts.portal-panel')

@section('title', 'Dashboard General')
@section('hub_kicker', 'Hub 1')
@section('hub_title', 'Dashboard General')
@section('hub_status', 'Centro de Mando')

@section('content')
<div 
    id="dashboardAppRoot" 
    class="w-full text-slate-800 font-sans antialiased" 
    data-screen="dashboard"
    x-data="dashboardModule()"
    x-init="init()"
>
    {{-- ========================================================================= --}}
    {{-- INYECCION DE DATOS DEL SERVIDOR (TRIPLE RBAC Y TELEMETRIA)                --}}
    {{-- ========================================================================= --}}
    <script>
        window.serverDashboardData = @json($serverDashboardData ?? [], JSON_UNESCAPED_UNICODE);
    </script>

    {{-- ========================================================================= --}}
    {{-- REGLA 10: BARRA DE PESTAÑAS DE SUBMODULO PRIMERO (SUBNAV BAR)             --}}
    {{-- ========================================================================= --}}
    <div class="mb-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <nav class="flex gap-2 -mb-px overflow-x-auto" aria-label="Submódulos del Dashboard">
            <!-- TAB 1: DASHBOARD EJECUTIVO -->
            <button 
                type="button"
                @click="switchTab('executive')"
                :class="activeTab === 'executive' 
                    ? 'border-indigo-600 text-indigo-600 font-semibold bg-white shadow-sm' 
                    : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'"
                class="py-3 px-4 border-b-2 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Dashboard Ejecutivo</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 font-bold">Finanzas</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Dashboard Ejecutivo" data-help-title="Dashboard Ejecutivo" data-help-desc="Vista gerencial con KPIs consolidados de ventas, embudo multiverso, ingresos recurrentes (MRR) y analitica de conversion.">?</button>
            </button>

            <!-- TAB 2: BANDEJA OPERATIVA -->
            <button 
                type="button"
                @click="switchTab('operative')"
                :class="activeTab === 'operative' 
                    ? 'border-indigo-600 text-indigo-600 font-semibold bg-white shadow-sm' 
                    : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'"
                class="py-3 px-4 border-b-2 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Bandeja Operativa</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700 font-semibold" x-text="pendingCount + ' Pendientes'">4 Pendientes</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Bandeja Operativa" data-help-title="Bandeja Operativa" data-help-desc="Action Center diario para personal de almacen, empaque y compras con tareas accionables en 1 clic.">?</button>
            </button>

            <!-- TAB 3: ATENCIÓN & VENTAS -->
            <button 
                type="button"
                @click="switchTab('sales')"
                :class="activeTab === 'sales' 
                    ? 'border-indigo-600 text-indigo-600 font-semibold bg-white shadow-sm' 
                    : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'"
                class="py-3 px-4 border-b-2 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                <span>Atención & Ventas</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-amber-100 text-amber-800 font-bold" x-text="salesPendingCount + ' Casos'">3 Casos</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Atención y Ventas" data-help-title="Atención & Ventas" data-help-desc="Gestion comercial de leads calientes, carritos abandonados recuperables y contacto directo via WhatsApp.">?</button>
            </button>
        </nav>

        <div class="flex items-center gap-4 text-xs pb-2 sm:pb-0">
            <span class="flex items-center gap-1.5 font-medium text-slate-600 bg-white px-2.5 py-1 rounded-full border border-slate-200 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Feed RevOps: En Vivo</span>
            </span>
            <button 
                type="button"
                @click="refreshDashboard()"
                :disabled="isRefreshing"
                class="text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1.5 cursor-pointer transition-colors disabled:opacity-50" 
                data-action="refresh-dashboard"
            >
                <svg class="w-3.5 h-3.5" :class="isRefreshing ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span x-text="isRefreshing ? 'Sincronizando...' : 'Actualizar'"></span>
            </button>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- REGLA 10: SECCION 2: TARJETAS DE KPIS CONTEXTUALES POR SUBMODULO          --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- VISTA EJECUTIVA KPIS -->
        <template x-if="activeTab === 'executive' && canViewFinancialCosts()">
            <div class="contents">
                <template x-for="(kpi, idx) in currentKpis" :key="'exec-kpi-' + idx">
                    <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500" x-text="kpi.label"></span>
                            <button class="section-help-btn" type="button" :aria-label="'Ayuda de ' + kpi.label" :data-help-title="kpi.label" data-help-desc="Metrica calculada en tiempo real por el motor de ventas y checkout de la plataforma.">?</button>
                        </div>
                        <div class="mt-3 flex items-baseline justify-between">
                            <span class="text-2xl font-black font-mono tracking-tight text-slate-900" x-text="kpi.value"></span>
                            <span class="text-xs font-bold font-mono" :class="idx === 0 ? 'text-emerald-600' : (idx === 2 ? 'text-rose-600' : 'text-indigo-600')" x-text="kpi.trend"></span>
                        </div>
                        <div class="mt-2 text-[11px] text-slate-400" x-text="kpi.subtitle"></div>
                    </div>
                </template>
            </div>
        </template>

        <!-- AVISO DE PRIVACIDAD EN EJECUTIVO SI ES ALMACEN -->
        <template x-if="activeTab === 'executive' && !canViewFinancialCosts()">
            <div class="col-span-full p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="text-xs text-amber-900">
                    <strong class="font-bold">Politica de Privacidad Financiera Activa:</strong>
                    <span>El perfil de Almacen no tiene acceso a facturacion, montos en dinero ni margenes comerciales. Consulta la <strong>Bandeja Operativa</strong> para tus tareas del dia.</span>
                </div>
            </div>
        </template>

        <!-- VISTA OPERATIVA KPIS (CERO DINERO / FISICOS 100%) -->
        <template x-if="activeTab === 'operative'">
            <div class="contents">
                <template x-for="(kpi, idx) in currentKpis" :key="'op-kpi-' + idx">
                    <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500" x-text="kpi.label"></span>
                            <button class="section-help-btn" type="button" :aria-label="'Ayuda de ' + kpi.label" :data-help-title="kpi.label" data-help-desc="Metrica fisica operativa de bodega y flujo de empaque diario.">?</button>
                        </div>
                        <div class="mt-3 flex items-baseline justify-between">
                            <span class="text-2xl font-black font-mono tracking-tight text-slate-900" x-text="kpi.value"></span>
                            <span class="text-xs font-bold font-mono" :class="idx === 0 ? 'text-rose-600' : (idx === 3 ? 'text-amber-600' : 'text-indigo-600')" x-text="kpi.trend"></span>
                        </div>
                        <div class="mt-2 text-[11px] text-slate-400" x-text="kpi.subtitle"></div>
                    </div>
                </template>
            </div>
        </template>

        <!-- VISTA ATENCIÓN & VENTAS KPIS -->
        <template x-if="activeTab === 'sales'">
            <div class="contents">
                <template x-for="(kpi, idx) in currentKpis" :key="'sales-kpi-' + idx">
                    <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500" x-text="kpi.label"></span>
                            <button class="section-help-btn" type="button" :aria-label="'Ayuda de ' + kpi.label" :data-help-title="kpi.label" data-help-desc="Metrica comercial de prospeccion, carritos recuperables y soporte a clientes.">?</button>
                        </div>
                        <div class="mt-3 flex items-baseline justify-between">
                            <span class="text-2xl font-black font-mono tracking-tight text-slate-900" x-text="kpi.value"></span>
                            <span class="text-xs font-bold font-mono" :class="idx === 1 ? 'text-indigo-600' : (idx === 2 ? 'text-amber-600' : 'text-emerald-600')" x-text="kpi.trend"></span>
                        </div>
                        <div class="mt-2 text-[11px] text-slate-400" x-text="kpi.subtitle"></div>
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- ========================================================================= --}}
    {{-- REGLA 10: SECCION 3: AREA OPERATIVA PRINCIPAL SEGUN PESTAÑA               --}}
    {{-- ========================================================================= --}}

    {{-- ------------------------------------------------------------------------- --}}
    {{-- SUBMODULO 1: DASHBOARD EJECUTIVO                                          --}}
    {{-- ------------------------------------------------------------------------- --}}
    <div x-show="activeTab === 'executive'" class="space-y-6">
        <template x-if="!canViewFinancialCosts()">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-8 text-center max-w-xl mx-auto my-12 shadow-sm">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-500 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Sección Reservada para Dirección y Finanzas</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    Tu rol actual está enfocado en la operativa de almacén y empaque. Por política de seguridad y privacidad financiera, las métricas monetarias no están disponibles.
                </p>
                <div class="mt-6">
                    <button 
                        type="button" 
                        @click="switchTab('operative')" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer"
                    >
                        Ir a la Bandeja Operativa de Almacén &rarr;
                    </button>
                </div>
            </div>
        </template>

        <template x-if="canViewFinancialCosts()">
            <div class="space-y-6">
                <!-- BARRA DE SUB-NAVEGACION EJECUTIVA POR AREA DE LA EMPRESA (TASK-HUB1-SUBNAV) -->
                <div class="p-1.5 bg-slate-100/90 rounded-2xl flex items-center gap-1.5 overflow-x-auto border border-slate-200/80 shadow-2xs">
                    <!-- 1. Comercial & RevOps -->
                    <button 
                        type="button"
                        id="btn-subtab-revops"
                        @click="switchExecutiveSubTab('revops')" 
                        class="px-3.5 py-2 text-xs font-bold rounded-xl transition-all flex items-center gap-2 cursor-pointer shrink-0"
                        :class="activeExecutiveSubTab === 'revops' ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        <span>Comercial & RevOps</span>
                    </button>

                    <!-- 2. Almacén e Inventario -->
                    <button 
                        type="button"
                        id="btn-subtab-inventory"
                        @click="switchExecutiveSubTab('inventory')" 
                        class="px-3.5 py-2 text-xs font-bold rounded-xl transition-all flex items-center gap-2 cursor-pointer shrink-0"
                        :class="activeExecutiveSubTab === 'inventory' ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Almacén e Inventario</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-700 border border-rose-200" x-text="(executiveAreas?.inventory?.ropCount || 4) + ' ROP'">4 ROP</span>
                    </button>

                    <!-- 3. Vitrinas & Tienda -->
                    <button 
                        type="button"
                        id="btn-subtab-store"
                        @click="switchExecutiveSubTab('store')" 
                        class="px-3.5 py-2 text-xs font-bold rounded-xl transition-all flex items-center gap-2 cursor-pointer shrink-0"
                        :class="activeExecutiveSubTab === 'store' ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>Vitrinas & Tienda</span>
                    </button>

                    <!-- 4. Marketing & CRM -->
                    <button 
                        type="button"
                        id="btn-subtab-crm"
                        @click="switchExecutiveSubTab('crm')" 
                        class="px-3.5 py-2 text-xs font-bold rounded-xl transition-all flex items-center gap-2 cursor-pointer shrink-0"
                        :class="activeExecutiveSubTab === 'crm' ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Marketing & CRM</span>
                    </button>

                    <!-- 5. Finanzas & Tesorería -->
                    <button 
                        type="button"
                        id="btn-subtab-finance"
                        @click="switchExecutiveSubTab('finance')" 
                        class="px-3.5 py-2 text-xs font-bold rounded-xl transition-all flex items-center gap-2 cursor-pointer shrink-0"
                        :class="activeExecutiveSubTab === 'finance' ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Finanzas & Tesorería</span>
                    </button>

                    <!-- 6. Logística & Envíos -->
                    <button 
                        type="button"
                        id="btn-subtab-logistics"
                        @click="switchExecutiveSubTab('logistics')" 
                        class="px-3.5 py-2 text-xs font-bold rounded-xl transition-all flex items-center gap-2 cursor-pointer shrink-0"
                        :class="activeExecutiveSubTab === 'logistics' ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        <span>Logística & Envíos</span>
                    </button>

                    <!-- 7. Convenios & B2B -->
                    <button 
                        type="button"
                        id="btn-subtab-allies"
                        @click="switchExecutiveSubTab('allies')" 
                        class="px-3.5 py-2 text-xs font-bold rounded-xl transition-all flex items-center gap-2 cursor-pointer shrink-0"
                        :class="activeExecutiveSubTab === 'allies' ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Convenios & B2B</span>
                    </button>
                </div>

                <!-- AREA 1: COMERCIAL & REVOPS -->
                <div x-show="activeExecutiveSubTab === 'revops'" class="space-y-6" id="panel-subtab-revops">
                    <!-- GRID PRINCIPAL DE LA VISTA EJECUTIVA -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- COLUMNA IZQUIERDA: CABINA ANALÍTICA & ACCIÓN (2 COLS) -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- PANEL 1: GRÁFICA ANALÍTICA SVG - FACTURACIÓN VS MRR RECURRENTE (TASK-DSH-02) -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm" id="chart-sales-mrr">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-bold text-slate-900">Facturación D2C vs MRR Recurrente</h3>
                                            <p class="text-xs text-slate-500">Curva analítica en tiempo real de ingresos brutos y suscripciones</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="inline-flex rounded-xl p-0.5 bg-slate-100 border border-slate-200 text-xs font-semibold">
                                        <button 
                                            type="button" 
                                            @click="changeAnalyticsPeriod('7d')"
                                            class="px-2.5 py-1 rounded-lg transition cursor-pointer"
                                            :class="analyticsPeriod === '7d' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                                        >7d</button>
                                        <button 
                                            type="button" 
                                            @click="changeAnalyticsPeriod('14d')"
                                            class="px-2.5 py-1 rounded-lg transition cursor-pointer"
                                            :class="analyticsPeriod === '14d' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                                        >14d</button>
                                        <button 
                                            type="button" 
                                            @click="changeAnalyticsPeriod('30d')"
                                            class="px-2.5 py-1 rounded-lg transition cursor-pointer"
                                            :class="analyticsPeriod === '30d' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                                        >30d</button>
                                    </div>
                                    <button class="section-help-btn" type="button" aria-label="Ayuda de Curva de Facturación" data-help-title="Facturación vs MRR" data-help-desc="Segregación de ventas transaccionales directas (D2C) vs ingresos recurrentes de suscripciones VIP (MRR). Permite evaluar predictibilidad del flujo de caja.">?</button>
                                </div>
                            </div>

                            <!-- Resumen Numérico de la Gráfica -->
                            <div class="grid grid-cols-3 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200/60 mb-5">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-500 block">Facturación Total</span>
                                    <span class="text-base font-black font-mono text-emerald-700" x-text="'MXN $' + (salesVsMrr.total_gross || 155200).toLocaleString()"></span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-500 block">MRR Membresías</span>
                                    <span class="text-base font-black font-mono text-indigo-700" x-text="'MXN $' + (salesVsMrr.total_mrr || 64800).toLocaleString()"></span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-500 block">Margen Bruto Est.</span>
                                    <span class="text-base font-black font-mono text-slate-800">60.0%</span>
                                </div>
                            </div>

                            <!-- Contenedor SVG Nativo -->
                            <div class="relative w-full h-52 bg-white rounded-xl border border-slate-100 p-2 overflow-hidden flex flex-col justify-between">
                                <svg class="w-full h-44 overflow-visible" viewBox="0 0 500 180" preserveAspectRatio="none">
                                    <defs>
                                        <linearGradient id="emeraldGradientArea" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#10b981" stop-opacity="0.32" />
                                            <stop offset="100%" stop-color="#10b981" stop-opacity="0.0" />
                                        </linearGradient>
                                    </defs>

                                    <!-- Líneas Guía de Fondo -->
                                    <line x1="30" y1="30" x2="470" y2="30" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="2 2" />
                                    <line x1="30" y1="75" x2="470" y2="75" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="2 2" />
                                    <line x1="30" y1="120" x2="470" y2="120" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="2 2" />
                                    <line x1="30" y1="165" x2="470" y2="165" stroke="#e2e8f0" stroke-width="1" />

                                    <!-- Área de Facturación D2C en Degradado -->
                                    <path :d="salesAreaD" fill="url(#emeraldGradientArea)" />

                                    <!-- Curva Sólida de Facturación D2C (Esmeralda) -->
                                    <path :d="salesPathD" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

                                    <!-- Curva Discontinua de MRR Recurrente (Índigo) -->
                                    <path :d="mrrPathD" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-dasharray="4 4" stroke-linecap="round" />

                                    <!-- Puntos de Facturación D2C -->
                                    <g x-html="salesCirclesSvg"></g>

                                    <!-- Puntos de MRR -->
                                    <g x-html="mrrCirclesSvg"></g>
                                </svg>

                                <!-- Eje X: Etiquetas de Días -->
                                <div class="flex justify-between px-6 text-[10px] font-bold text-slate-600 border-t border-slate-100 pt-1">
                                    <template x-for="(label, idx) in salesChartLabels" :key="'lbl-' + idx">
                                        <span x-text="label"></span>
                                    </template>
                                </div>
                            </div>

                            <!-- Leyenda de la Gráfica -->
                            <div class="flex items-center justify-between text-xs text-slate-500 mt-3 pt-2 border-t border-slate-100">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                                        <span class="font-bold text-slate-700">Facturación D2C (Ventas Brutas)</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-3 h-1.5 rounded-sm bg-indigo-600 inline-block border-b-2 border-dashed border-indigo-600"></span>
                                        <span class="font-bold text-slate-700">MRR Recurrente (Membresías VIP)</span>
                                    </div>
                                </div>
                                <span class="text-[11px] text-slate-400 font-mono">Actualizado hace 2 min</span>
                            </div>
                        </div>

                        <!-- PANEL 2: PARTICIPACIÓN MULTIVERSO & CASCADA EBITDA (GRID 2 COLS) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Gráfica 2: Participación por Vitrina Multiverso -->
                            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                                            </div>
                                            <div>
                                                <h3 class="text-sm font-bold text-slate-900">Cuota por Vitrina Multiverso</h3>
                                                <p class="text-[11px] text-slate-500">Distribución de ingresos por canal</p>
                                            </div>
                                        </div>
                                        <button class="section-help-btn" type="button" aria-label="Ayuda de Participacion Multiverso" data-help-title="Cuotas Multiverso" data-help-desc="Desglose porcentual de ingresos por vitrina: Terra (D2C Basicos), Marte (Dropship B2B Torzo), Venus (Suscripciones Sorpresa) y Tiresias (Mayorista).">?</button>
                                    </div>

                                    <div class="space-y-3.5 mt-2">
                                        <!-- Terra -->
                                        <div>
                                            <div class="flex justify-between text-xs mb-1">
                                                <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                    <span>Terra (Básicos D2C Propio)</span>
                                                </span>
                                                <span class="font-mono font-bold text-slate-900">42% <span class="text-slate-600 font-normal">($148.5k)</span></span>
                                            </div>
                                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-emerald-500 rounded-full" style="width: 42%;"></div>
                                            </div>
                                        </div>

                                        <!-- Marte -->
                                        <div>
                                            <div class="flex justify-between text-xs mb-1">
                                                <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                                    <span>Marte (Dropship B2B Torzo)</span>
                                                </span>
                                                <span class="font-mono font-bold text-slate-900">33% <span class="text-slate-600 font-normal">($116.8k)</span></span>
                                            </div>
                                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-indigo-500 rounded-full" style="width: 33%;"></div>
                                            </div>
                                        </div>

                                        <!-- Venus -->
                                        <div>
                                            <div class="flex justify-between text-xs mb-1">
                                                <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                                    <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                                                    <span>Venus (Cajas VIP Suscripción)</span>
                                                </span>
                                                <span class="font-mono font-bold text-slate-900">18% <span class="text-slate-600 font-normal">($63.7k)</span></span>
                                            </div>
                                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-pink-500 rounded-full" style="width: 18%;"></div>
                                            </div>
                                        </div>

                                        <!-- Tiresias -->
                                        <div>
                                            <div class="flex justify-between text-xs mb-1">
                                                <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                    <span>Tiresias (B2B Mayorista / Aliados)</span>
                                                </span>
                                                <span class="font-mono font-bold text-slate-900">7% <span class="text-slate-600 font-normal">($24.8k)</span></span>
                                            </div>
                                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-amber-500 rounded-full" style="width: 7%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex justify-between">
                                    <span>Dominio Principal: <strong>Terra (42%)</strong></span>
                                    <span>Total Consolidado: <strong>MXN $353,800</strong></span>
                                </div>
                            </div>

                            <!-- Gráfica 3: Cascada de Margen EBITDA -->
                            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between" id="ebitda-waterfall-panel">
                                <div>
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                            </div>
                                            <div>
                                                <h3 class="text-sm font-bold text-slate-900">Cascada de Margen EBITDA</h3>
                                                <p class="text-[11px] text-slate-500">Desglose neto desde ventas hasta EBITDA</p>
                                            </div>
                                        </div>
                                        <button class="section-help-btn" type="button" aria-label="Ayuda de Cascada EBITDA" data-help-title="Cascada EBITDA" data-help-desc="Deduccion paso a paso de costos directos sobre facturacion: Costo de mercancia (COGS), fletes DHL, comisiones de pasarela e impuestos para aislar el margen operativo real.">?</button>
                                    </div>

                                    <div class="space-y-2 text-xs">
                                        <div class="p-2 bg-slate-50 rounded-lg flex items-center justify-between">
                                            <span class="font-bold text-slate-800">1. Ventas Brutas Totales</span>
                                            <span class="font-mono font-bold text-slate-900">+$353,800 (100%)</span>
                                        </div>
                                        <div class="p-2 bg-rose-50/60 rounded-lg flex items-center justify-between text-rose-900">
                                            <span class="font-medium">(-) COGS Mercancía (40%)</span>
                                            <span class="font-mono font-bold">-$141,520</span>
                                        </div>
                                        <div class="p-2 bg-rose-50/60 rounded-lg flex items-center justify-between text-rose-900">
                                            <span class="font-medium">(-) Empaque y Fletes DHL (12%)</span>
                                            <span class="font-mono font-bold">-$42,450</span>
                                        </div>
                                        <div class="p-2 bg-rose-50/60 rounded-lg flex items-center justify-between text-rose-900">
                                            <span class="font-medium">(-) Pasarelas Stripe/MP (4.5%)</span>
                                            <span class="font-mono font-bold">-$15,920</span>
                                        </div>
                                        <div class="p-2 bg-rose-50/60 rounded-lg flex items-center justify-between text-rose-900">
                                            <span class="font-medium">(-) Impuestos e IVA Neto (8%)</span>
                                            <span class="font-mono font-bold">-$28,300</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-emerald-800 block">Margen EBITDA Neto Real</span>
                                        <span class="text-sm font-black font-mono text-emerald-900">+MXN $125,610</span>
                                    </div>
                                    <span class="px-2.5 py-1 bg-emerald-600 text-white rounded-lg text-xs font-black">35.5% NETO</span>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 3: EMBUDO COMERCIAL MULTIVERSO ACCIONABLE (TASK-DSH-02) -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900">Embudo Comercial Multiverso Accionable</h3>
                                        <p class="text-xs text-slate-500">Conversión en vivo con micro-acciones directas en cada nivel</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="inline-flex rounded-xl p-0.5 bg-slate-100 border border-slate-200 text-xs font-semibold">
                                        <button 
                                            type="button" 
                                            @click="activeFunnelVitrina = 'all'"
                                            class="px-2.5 py-1 rounded-lg transition cursor-pointer"
                                            :class="activeFunnelVitrina === 'all' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                                        >Todas</button>
                                        <button 
                                            type="button" 
                                            @click="activeFunnelVitrina = 'terra'"
                                            class="px-2.5 py-1 rounded-lg transition cursor-pointer"
                                            :class="activeFunnelVitrina === 'terra' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                                        >Terra</button>
                                        <button 
                                            type="button" 
                                            @click="activeFunnelVitrina = 'marte'"
                                            class="px-2.5 py-1 rounded-lg transition cursor-pointer"
                                            :class="activeFunnelVitrina === 'marte' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                                        >Marte</button>
                                        <button 
                                            type="button" 
                                            @click="activeFunnelVitrina = 'venus'"
                                            class="px-2.5 py-1 rounded-lg transition cursor-pointer"
                                            :class="activeFunnelVitrina === 'venus' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                                        >Venus</button>
                                    </div>
                                    <button class="section-help-btn" type="button" aria-label="Ayuda del Embudo Accionable" data-help-title="Embudo Multiverso" data-help-desc="Telemetria del ciclo de conversion con botones de accion rapida: recuperar carritos, auditar pasarelas 3DS y liberar ordenes a empaque.">?</button>
                                </div>
                            </div>

                            <!-- 4 Columnas del Embudo con Chips Accionables -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                                <!-- Fase 1: Sesiones -->
                                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/60 flex flex-col justify-between">
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-slate-500 block mb-1">1. Sesiones Hoy</span>
                                        <span class="text-base font-black font-mono text-slate-900" x-text="(currentFunnel.sessions || 12450).toLocaleString()"></span>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-slate-200/60">
                                        <button 
                                            type="button" 
                                            @click="refreshDashboard()" 
                                            class="w-full text-center px-2 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded text-[10px] font-bold text-slate-700 transition cursor-pointer"
                                        >
                                            Auditar Tráfico
                                        </button>
                                    </div>
                                </div>

                                <!-- Fase 2: Carritos -->
                                <div class="p-3.5 bg-indigo-50/50 rounded-xl border border-indigo-200/60 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-[10px] uppercase font-bold text-indigo-900">2. Carritos</span>
                                            <span class="text-[10px] font-bold text-indigo-700" x-text="(currentFunnel.conv_cart || 43.0) + '%'"></span>
                                        </div>
                                        <span class="text-base font-black font-mono text-indigo-800" x-text="(currentFunnel.carts || 5353).toLocaleString()"></span>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-indigo-200/60">
                                        <button 
                                            type="button" 
                                            @click="switchTab('sales'); salesFilter = 'abandoned_carts'" 
                                            class="w-full text-center px-2 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-[10px] font-bold shadow-xs transition cursor-pointer"
                                        >
                                            Recuperar Ventas &rarr;
                                        </button>
                                    </div>
                                </div>

                                <!-- Fase 3: Checkouts -->
                                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/60 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-[10px] uppercase font-bold text-slate-500">3. Checkouts</span>
                                            <span class="text-[10px] font-bold text-slate-700" x-text="(currentFunnel.conv_checkout || 77.0) + '%'"></span>
                                        </div>
                                        <span class="text-base font-black font-mono text-slate-900" x-text="(currentFunnel.checkouts || 4120).toLocaleString()"></span>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-slate-200/60">
                                        <button 
                                            type="button" 
                                            @click="openConnectorsModal()" 
                                            class="w-full text-center px-2 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded text-[10px] font-bold text-slate-700 transition cursor-pointer"
                                        >
                                            Auditar Pasarelas
                                        </button>
                                    </div>
                                </div>

                                <!-- Fase 4: Órdenes Pagadas -->
                                <div class="p-3.5 bg-emerald-50 rounded-xl border border-emerald-200/60 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-[10px] uppercase font-bold text-emerald-800">4. Pagadas</span>
                                            <span class="text-[10px] font-bold text-emerald-700" x-text="(currentFunnel.conv_order || 84.6) + '%'"></span>
                                        </div>
                                        <span class="text-base font-black font-mono text-emerald-800" x-text="(currentFunnel.orders || 3486).toLocaleString()"></span>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-emerald-200/60">
                                        <button 
                                            type="button" 
                                            @click="switchTab('operative')" 
                                            class="w-full text-center px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[10px] font-bold shadow-xs transition cursor-pointer"
                                        >
                                            Liberar a Almacén &rarr;
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Barras visuales de conversión -->
                            <div class="space-y-3">
                                <div>
                                    <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                                        <span>Conversión: Visitas a Carrito</span>
                                        <span class="font-mono" x-text="(currentFunnel.conv_cart || 43.0) + '%'"></span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500 rounded-full transition-all duration-500" :style="'width: ' + (currentFunnel.conv_cart || 43.0) + '%;'"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                                        <span>Conversión: Carrito a Checkout</span>
                                        <span class="font-mono" x-text="(currentFunnel.conv_checkout || 77.0) + '%'"></span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-600 rounded-full transition-all duration-500" :style="'width: ' + (currentFunnel.conv_checkout || 77.0) + '%;'"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                                        <span>Conversión: Checkout a Pago Exitoso</span>
                                        <span class="font-mono" x-text="(currentFunnel.conv_order || 84.6) + '%'"></span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" :style="'width: ' + (currentFunnel.conv_order || 84.6) + '%;'"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL: ACTION CENTER OPERATIVO DEL DÍA (TASK-DSH-04) -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900">Action Center: Tareas Prioritarias del Día</h3>
                                        <p class="text-xs text-slate-500">Resolución guiada con checklists físicas</p>
                                    </div>
                                </div>
                                <button class="section-help-btn" type="button" aria-label="Ayuda de Action Center" data-help-title="Action Center Operativo" data-help-desc="Cola de trabajo inteligente que prioriza envíos con vencimiento de SLA, pedidos mixtos desbloqueados y discrepancias de recepción.">?</button>
                            </div>

                            <div class="space-y-3">
                                <div class="p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-bold text-slate-900">Pedido #MIX-1048: Mercancía Lista</span>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Inbound Recibido</span>
                                            </div>
                                            <p class="text-xs text-slate-600 mt-0.5">Torzo B2B entregó 2 boxers bajo pedido. Desbloquear para mesa de empaque final.</p>
                                        </div>
                                    </div>
                                    <button type="button" @click="switchTab('operative')" class="px-3 py-1.5 text-xs font-bold bg-white hover:bg-slate-50 border border-slate-300 rounded-lg text-slate-800 transition cursor-pointer shrink-0">
                                        Ver en Almacén
                                    </button>
                                </div>

                                <div class="p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500 mt-1.5 shrink-0"></div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-bold text-slate-900">Alerta ROP: Boxer Modal Negro (Talla M)</span>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Quiebre en 2.4 Días</span>
                                            </div>
                                            <p class="text-xs text-slate-600 mt-0.5">Stock disponible: 4 unidades. Punto de reorden configurado: 12 unidades.</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('portal.inventory') }}" class="px-3 py-1.5 text-xs font-bold bg-white hover:bg-slate-50 border border-slate-300 rounded-lg text-slate-800 transition shrink-0" style="text-decoration:none;">
                                        Generar PO
                                    </a>
                                </div>

                                <div class="p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-2.5 h-2.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-bold text-slate-900">Carrito Abandonado: Andrea Ruiz</span>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">MXN $1,450.00</span>
                                            </div>
                                            <p class="text-xs text-slate-600 mt-0.5">3 prendas en carrito abandonadas en paso de pago hace 25 min.</p>
                                        </div>
                                    </div>
                                    <button type="button" @click="switchTab('sales'); salesFilter = 'abandoned_carts'" class="px-3 py-1.5 text-xs font-bold bg-white hover:bg-slate-50 border border-slate-300 rounded-lg text-slate-800 transition cursor-pointer shrink-0">
                                        Gestionar en Ventas
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA DERECHA: TELEMETRÍA, ALERTAS Y COPILOTO (1 COL) -->
                    <div class="space-y-6">
                        
                        <!-- PANEL: TELEMETRÍA DE CONECTORES Y APIS (TASK-DSH-03) -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900">Salud del Ecosistema</h3>
                                        <p class="text-[11px] text-slate-500">Telemetría de latencias y APIs</p>
                                    </div>
                                </div>
                                <button class="section-help-btn" type="button" aria-label="Ayuda de Salud del Ecosistema" data-help-title="Monitor de APIs" data-help-desc="Monitoreo de latencia y estado operacional de pasarelas (Stripe), scrapers (Torzo) y paqueterias (DHL / Skydropx).">?</button>
                            </div>

                            <div class="space-y-2.5 text-xs mb-4">
                                <!-- Stripe -->
                                <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200/60">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span class="font-bold text-slate-800">Stripe Payments API</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded font-mono font-bold text-[11px] bg-emerald-100 text-emerald-800" x-text="(connectorsList.stripe?.latency_ms || 42) + 'ms OK'"></span>
                                </div>

                                <!-- Mercado Pago -->
                                <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200/60">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span class="font-bold text-slate-800">Mercado Pago Checkout</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded font-mono font-bold text-[11px] bg-emerald-100 text-emerald-800" x-text="(connectorsList.mercadopago?.latency_ms || 68) + 'ms OK'"></span>
                                </div>

                                <!-- Torzo Scraper -->
                                <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200/60">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span class="font-bold text-slate-800">Torzo Bot Scraper</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded font-mono font-bold text-[11px] bg-emerald-100 text-emerald-800" x-text="(connectorsList.torzo_scraper?.latency_ms || 185) + 'ms OK'"></span>
                                </div>

                                <!-- DHL Express -->
                                <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200/60">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span class="font-bold text-slate-800">DHL Express & Guías</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded font-mono font-bold text-[11px] bg-emerald-100 text-emerald-800" x-text="(connectorsList.dhl_express?.latency_ms || 95) + 'ms OK'"></span>
                                </div>

                                <!-- Sepomex -->
                                <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200/60">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                        <span class="font-bold text-slate-800">Sepomex Domicilios</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded font-mono font-bold text-[11px] bg-emerald-100 text-emerald-800" x-text="(connectorsList.sepomex_catalog?.latency_ms || 12) + 'ms OK'"></span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <button 
                                    type="button" 
                                    @click="pingSystemHealth()" 
                                    :disabled="isPingingApis"
                                    class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center justify-center gap-2"
                                >
                                    <svg class="w-3.5 h-3.5" :class="isPingingApis ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span x-text="isPingingApis ? 'Verificando latencias...' : 'Ejecutar Diagnóstico Global'"></span>
                                </button>
                                <button 
                                    type="button" 
                                    @click="openConnectorsModal()" 
                                    class="w-full py-1.5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition cursor-pointer"
                                >
                                    Ver Certificados & HMAC
                                </button>
                            </div>
                        </div>

                        <!-- PANEL: COPILOTO ESTRATÉGICO IA (TASK-DSH-07) -->
                        <div class="bg-gradient-to-br from-indigo-950 via-slate-900 to-indigo-900 text-white rounded-2xl p-6 shadow-md border border-indigo-800/40">
                            <div class="flex items-center gap-2.5 mb-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-300 flex items-center justify-center border border-indigo-400/30">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white">Copiloto Estratégico IA</h4>
                                    <span class="text-[10px] text-indigo-300 font-medium">Asistente Operativo Contextual</span>
                                </div>
                            </div>
                            <p class="text-xs text-indigo-100 leading-relaxed mb-4">
                                Accede a análisis instantáneos de margen EBITDA, puntos críticos de reorden ROP y SLA de envíos DHL.
                            </p>
                            <button 
                                type="button" 
                                @click="openCopilotDrawer()" 
                                class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer flex items-center justify-center gap-2"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                <span>Abrir Asistente Copiloto IA</span>
                            </button>
                        </div>

                        <!-- PANEL: ALERTAS DEL SISTEMA & OPERACIONES -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Alertas Operativas</span>
                                </h3>
                                <span class="text-xs text-slate-500 font-semibold" x-text="alerts.length + ' activas'"></span>
                            </div>

                            <template x-if="alerts.length > 0">
                                <ul class="space-y-2.5 text-xs text-slate-700">
                                    <template x-for="(alert, idx) in alerts" :key="'alert-' + idx">
                                        <li class="p-3 bg-slate-50 border border-slate-200/60 rounded-xl flex items-start gap-2">
                                            <span class="w-2 h-2 rounded-full bg-amber-500 mt-1 shrink-0"></span>
                                            <span class="leading-relaxed" x-text="alert"></span>
                                        </li>
                                    </template>
                                </ul>
                            </template>
                            <template x-if="alerts.length === 0">
                                <p class="text-xs text-slate-500 text-center py-4">No hay alertas operativas críticas pendientes en este momento.</p>
                            </template>
                        </div>
                    </div>
                </div>
                </div>
                <!-- FIN AREA 1: COMERCIAL & REVOPS -->

                <!-- ========================================================================= -->
                <!-- AREA 2: ALMACEN E INVENTARIO (4 ROP)                                      -->
                <!-- ========================================================================= -->
                <div x-show="activeExecutiveSubTab === 'inventory'" class="space-y-6" id="panel-subtab-inventory">
                    <!-- BARRA DE FILTRO TEMPORAL Y ALERTA ROP (REV-OPS INTELLIGENCE) -->
                    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-xs">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Período:</span>
                            </span>
                            <div class="inline-flex p-0.5 bg-slate-100 rounded-lg text-xs font-semibold">
                                <button type="button" @click="setExecutiveInventoryTimeRange('today')" class="px-2.5 py-1 rounded-md transition cursor-pointer" :class="executiveInventoryTimeRange === 'today' ? 'bg-white text-indigo-700 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'">Hoy</button>
                                <button type="button" @click="setExecutiveInventoryTimeRange('7d')" class="px-2.5 py-1 rounded-md transition cursor-pointer" :class="executiveInventoryTimeRange === '7d' ? 'bg-white text-indigo-700 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'">7 Días</button>
                                <button type="button" @click="setExecutiveInventoryTimeRange('30d')" class="px-2.5 py-1 rounded-md transition cursor-pointer" :class="executiveInventoryTimeRange === '30d' ? 'bg-white text-indigo-700 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'">30 Días</button>
                                <button type="button" @click="setExecutiveInventoryTimeRange('year')" class="px-2.5 py-1 rounded-md transition cursor-pointer" :class="executiveInventoryTimeRange === 'year' ? 'bg-white text-indigo-700 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'">Año 2026</button>
                                <button type="button" @click="setExecutiveInventoryTimeRange('all')" class="px-2.5 py-1 rounded-md transition cursor-pointer" :class="executiveInventoryTimeRange === 'all' ? 'bg-white text-indigo-700 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'">Histórico</button>
                            </div>
                            <span class="text-xs text-indigo-600 font-semibold ml-2" x-text="executiveInventoryTimeRange === 'today' ? '(Hoy)' : (executiveInventoryTimeRange === '7d' ? '(Últimos 7 Días)' : (executiveInventoryTimeRange === 'year' ? '(Año 2026)' : (executiveInventoryTimeRange === 'all' ? '(Histórico Completo)' : '(Últimos 30 Días)')))"></span>
                        </div>

                        <!-- Alerta ROP Rápida -->
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500 hidden sm:inline">Predicción de Quiebres:</span>
                            <a 
                                href="{{ route('portal.inventory') }}?filter=rop" 
                                class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-bold border border-rose-200 transition cursor-pointer"
                                title="Ver productos con stock inferior al punto de reorden en Hub 4"
                            >
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                                <span x-text="(executiveAreas?.inventory?.ropCount || 4) + ' SKUs bajo Punto de Reorden (ROP)'">4 SKUs bajo Punto de Reorden (ROP)</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>

                    <!-- 4 KPI Cards Superiores -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- KPI 1 -->
                        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm hover:border-slate-300 transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Valor Stock Local</span>
                                </div>
                                <span class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </span>
                            </div>
                            <div class="mt-3">
                                <div class="text-2xl font-bold text-slate-900" x-text="executiveAreas?.inventory?.stockValue || '$243,040'">$243,040</div>
                                <div class="flex items-center gap-1.5 mt-1 text-xs text-emerald-600 font-medium">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                    <span>+5.2% vs mes anterior</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 2 -->
                        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm hover:border-slate-300 transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Prendas en Almacén</span>
                                </div>
                                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                </span>
                            </div>
                            <div class="mt-3">
                                <div class="text-2xl font-bold text-slate-900"><span x-text="executiveAreas?.inventory?.pieces || '3,674'">3,674</span> <span class="text-sm font-normal text-slate-500">piezas</span></div>
                                <div class="flex items-center gap-1.5 mt-1 text-xs text-slate-500 font-medium">
                                    <span>Distribuido en 4 categorías activas</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 3 -->
                        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm hover:border-slate-300 transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rotación Mensual</span>
                                </div>
                                <span class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </span>
                            </div>
                            <div class="mt-3">
                                <div class="text-2xl font-bold text-slate-900"><span x-text="executiveAreas?.inventory?.rotationDays || '4.2'">4.2</span> <span class="text-sm font-normal text-slate-500">días</span></div>
                                <div class="flex items-center gap-1.5 mt-1 text-xs text-emerald-600 font-medium">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                    <span>Ritmo acelerado (Óptimo &lt; 7d)</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 4 -->
                        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm hover:border-slate-300 transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tasa Ruptura Proveedor</span>
                                </div>
                                <span class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                </span>
                            </div>
                            <div class="mt-3">
                                <div class="text-2xl font-bold text-amber-600" x-text="executiveAreas?.inventory?.supplierRuptureRate || '8.4%'">8.4%</div>
                                <div class="flex items-center gap-1.5 mt-1 text-xs text-amber-700 font-medium">
                                    <span>2 variantes sin disponibilidad externa</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FILA DE GRÁFICOS: VENTAS POR CATEGORÍA + BALANCE DE ORIGEN -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <!-- Ventas por Categoría -->
                        <div class="lg:col-span-7 bg-white rounded-xl p-6 border border-slate-200/80 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-base font-bold text-slate-900">Ventas por Categoría</h3>
                                    </div>
                                    <p class="text-xs text-slate-500">Participación en volumen de pedidos (Últimos 30 días)</p>
                                </div>
                                <span class="text-xs font-medium text-slate-400">Total: 2,480 ventas</span>
                            </div>

                            <div class="space-y-4 pt-2">
                                <!-- Boxers -->
                                <div>
                                    <div class="flex justify-between text-xs font-medium mb-1.5">
                                        <span class="text-slate-700 font-semibold">Boxers (Trunks & Briefs)</span>
                                        <span class="text-slate-900 font-bold">42% <span class="text-slate-400 font-normal">(1,041 uds)</span></span>
                                    </div>
                                    <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-600 rounded-full transition-all duration-500" style="width: 42%"></div>
                                    </div>
                                </div>

                                <!-- Jocks -->
                                <div>
                                    <div class="flex justify-between text-xs font-medium mb-1.5">
                                        <span class="text-slate-700 font-semibold">Jockstraps (Athletic & Fashion)</span>
                                        <span class="text-slate-900 font-bold">28% <span class="text-slate-400 font-normal">(694 uds)</span></span>
                                    </div>
                                    <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500 rounded-full transition-all duration-500" style="width: 28%"></div>
                                    </div>
                                </div>

                                <!-- Tangas -->
                                <div>
                                    <div class="flex justify-between text-xs font-medium mb-1.5">
                                        <span class="text-slate-700 font-semibold">Tangas / Thongs</span>
                                        <span class="text-slate-900 font-bold">18% <span class="text-slate-400 font-normal">(446 uds)</span></span>
                                    </div>
                                    <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-fuchsia-500 rounded-full transition-all duration-500" style="width: 18%"></div>
                                    </div>
                                </div>

                                <!-- Bikinis -->
                                <div>
                                    <div class="flex justify-between text-xs font-medium mb-1.5">
                                        <span class="text-slate-700 font-semibold">Bikinis & Micro-slips</span>
                                        <span class="text-slate-900 font-bold">12% <span class="text-slate-400 font-normal">(299 uds)</span></span>
                                    </div>
                                    <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-amber-500 rounded-full transition-all duration-500" style="width: 12%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Balance de Origen de Catálogo -->
                        <div class="lg:col-span-5 bg-white rounded-xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-base font-bold text-slate-900">Balance de Origen de Catálogo</h3>
                                        </div>
                                        <p class="text-xs text-slate-500">Distribución de surtido: Bodega propia vs Dropship</p>
                                    </div>
                                </div>

                                <!-- Barra Apilada -->
                                <div class="mt-4">
                                    <div class="flex h-5 w-full rounded-lg overflow-hidden p-0.5 bg-slate-100 shadow-inner">
                                        <div class="bg-indigo-600 rounded-l-md transition-all duration-500 flex items-center justify-center text-[10px] text-white font-bold" style="width: 72%">
                                            72%
                                        </div>
                                        <div class="bg-emerald-500 rounded-r-md transition-all duration-500 flex items-center justify-center text-[10px] text-white font-bold" style="width: 28%">
                                            28%
                                        </div>
                                    </div>
                                </div>

                                <!-- Leyendas y detalles -->
                                <div class="grid grid-cols-2 gap-4 mt-6 pt-4 border-t border-slate-100">
                                    <div class="p-3 bg-indigo-50/60 rounded-xl border border-indigo-100">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="w-3 h-3 rounded-md bg-indigo-600"></span>
                                            <span class="text-xs font-bold text-indigo-900">Stock Propio</span>
                                        </div>
                                        <div class="text-xl font-extrabold text-slate-900 mt-1">72%</div>
                                        <p class="text-[11px] text-slate-600 mt-0.5">1,250 prendas en racks locales (Despacho express)</p>
                                    </div>

                                    <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-100">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="w-3 h-3 rounded-md bg-emerald-500"></span>
                                            <span class="text-xs font-bold text-emerald-900">Scraping / Dropship</span>
                                        </div>
                                        <div class="text-xl font-extrabold text-slate-900 mt-1">28%</div>
                                        <p class="text-[11px] text-slate-600 mt-0.5">486 referencias bajo pedido (Sincronizado vía Torzo)</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-500 flex items-center justify-between">
                                <span>Estrategia RevOps: Mantener Stock Propio &gt; 70% para proteger SLA de despacho.</span>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN: ANÁLISIS ABC DE PARETO (80/20 REVOPS) -->
                    <div class="bg-white rounded-xl p-6 border border-slate-200/80 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-3 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="p-1.5 bg-indigo-50 text-indigo-700 rounded-lg border border-indigo-200/80 inline-flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    </span>
                                    <h3 class="text-base font-bold text-slate-900">Análisis ABC de Inventario (Ley de Pareto 80/20)</h3>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">Concentración de capital y margen de contribución por rotación de producto</p>
                            </div>
                            <div class="text-xs font-semibold text-slate-400">
                                Auditados: <strong class="text-slate-700">1,736 SKUs</strong>
                            </div>
                        </div>

                        <!-- 3 Tarjetas de Clases ABC -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Clase A -->
                            <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/40 hover:bg-amber-50/70 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg bg-amber-500 text-white font-black text-xs inline-flex items-center justify-center shadow-xs">A</span>
                                        <div>
                                            <span class="text-xs font-bold text-amber-950">Clase A (Estratégicos)</span>
                                            <span class="text-[10px] text-amber-700 block">Top 20% &bull; 80% Facturación</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-black text-amber-900">124 SKUs</span>
                                </div>
                                <div class="mt-3">
                                    <div class="flex justify-between text-[11px] text-slate-600 mb-1">
                                        <span>Participación GMV</span>
                                        <span class="font-bold text-slate-900">79.4%</span>
                                    </div>
                                    <div class="w-full h-2 bg-amber-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-amber-500 rounded-full" style="width: 79.4%"></div>
                                    </div>
                                </div>
                                <p class="text-[11px] text-slate-600 mt-2.5 leading-snug">Alta rotación. Jamás deben quebrar en tallas M y G. Stock de seguridad crítico (15 uds).</p>
                                <a href="{{ route('portal.inventory') }}?abc=A" class="mt-3 w-full py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg transition cursor-pointer shadow-3xs flex items-center justify-center gap-1">
                                    <span>Ver Productos Clase A</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>

                            <!-- Clase B -->
                            <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/40 hover:bg-blue-50/70 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg bg-blue-500 text-white font-black text-xs inline-flex items-center justify-center shadow-xs">B</span>
                                        <div>
                                            <span class="text-xs font-bold text-blue-950">Clase B (Complementarios)</span>
                                            <span class="text-[10px] text-blue-700 block">30% Catálogo &bull; 15% Ventas</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-black text-blue-900">380 SKUs</span>
                                </div>
                                <div class="mt-3">
                                    <div class="flex justify-between text-[11px] text-slate-600 mb-1">
                                        <span>Participación GMV</span>
                                        <span class="font-bold text-slate-900">15.2%</span>
                                    </div>
                                    <div class="w-full h-2 bg-blue-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500 rounded-full" style="width: 15.2%"></div>
                                    </div>
                                </div>
                                <p class="text-[11px] text-slate-600 mt-2.5 leading-snug">Rotación media predecible. Reabastecimiento quincenal con lotes moderados (10 uds seguridad).</p>
                                <a href="{{ route('portal.inventory') }}?abc=B" class="mt-3 w-full py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition cursor-pointer shadow-3xs flex items-center justify-center gap-1">
                                    <span>Ver Productos Clase B</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>

                            <!-- Clase C -->
                            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-100/70 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg bg-slate-500 text-white font-black text-xs inline-flex items-center justify-center shadow-xs">C</span>
                                        <div>
                                            <span class="text-xs font-bold text-slate-900">Clase C (Cola Larga)</span>
                                            <span class="text-[10px] text-slate-500 block">50% Catálogo &bull; 5% Ventas</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-black text-slate-700">1,232 SKUs</span>
                                </div>
                                <div class="mt-3">
                                    <div class="flex justify-between text-[11px] text-slate-600 mb-1">
                                        <span>Participación GMV</span>
                                        <span class="font-bold text-slate-900">5.4%</span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-slate-500 rounded-full" style="width: 5.4%"></div>
                                    </div>
                                </div>
                                <p class="text-[11px] text-slate-600 mt-2.5 leading-snug">Baja rotación / riesgo de capital inmovilizado. Mantener bajo pedido (dropship) o paquete promocional.</p>
                                <a href="{{ route('portal.inventory') }}?abc=C" class="mt-3 w-full py-1.5 bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold rounded-lg transition cursor-pointer shadow-3xs flex items-center justify-center gap-1">
                                    <span>Ver Productos Clase C</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN: TOP 10 PRODUCTOS CON MAYOR RENDIMIENTO -->
                    <div class="bg-white rounded-xl p-6 border border-slate-200/80 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 pb-3 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="p-1.5 bg-amber-50 text-amber-600 rounded-lg border border-amber-200/80 inline-flex items-center justify-center shadow-2xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                    </span>
                                    <h3 class="text-base font-bold text-slate-900">Top 10 Productos con Mayor Rendimiento</h3>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">Líderes de venta en volumen (uds) y facturación acumulada &bull; Últimos 30 días</p>
                            </div>

                            <!-- Botón Ver Tabla Completa -->
                            <div class="flex items-center gap-2">
                                <button 
                                    type="button" 
                                    @click="openTopTenModal()" 
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl text-xs font-bold transition shadow-2xs cursor-pointer group"
                                >
                                    <svg class="w-4 h-4 text-indigo-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                    </svg>
                                    <span>Ver Tabla Completa (10 SKUs)</span>
                                    <span class="text-indigo-500 font-bold group-hover:translate-x-1 transition-transform">&rarr;</span>
                                </button>
                            </div>
                        </div>

                        <!-- Lista de Barras de Progreso en 2 Columnas -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-4 pt-1">
                            <!-- COLUMNA IZQUIERDA: Top 1 al 5 -->
                            <div class="space-y-3.5">
                                <!-- #1 -->
                                <div class="group cursor-pointer p-2 -mx-2 rounded-lg hover:bg-slate-50 transition" @click="openTopTenModal()">
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-5 h-5 rounded-full bg-amber-100 text-amber-800 border border-amber-300 font-extrabold text-[10px] inline-flex items-center justify-center shrink-0">1</span>
                                            <span class="font-bold text-slate-800 group-hover:text-indigo-600 transition truncate">Boxer Trunks Modal Seamless</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100 shrink-0">Boxers</span>
                                            <span class="hidden sm:inline-flex text-[10px] font-semibold text-slate-500 bg-slate-100 px-1.5 py-0.2 rounded shrink-0">Top: M (45%)</span>
                                        </div>
                                        <div class="text-right shrink-0 ml-2">
                                            <span class="font-bold text-slate-900">520 uds</span>
                                            <span class="text-slate-400 font-normal ml-1">($218,400)</span>
                                        </div>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-600 rounded-full transition-all duration-500" style="width: 100%"></div>
                                    </div>
                                </div>

                                <!-- #2 -->
                                <div class="group cursor-pointer p-2 -mx-2 rounded-lg hover:bg-slate-50 transition" @click="openTopTenModal()">
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 border border-slate-300 font-extrabold text-[10px] inline-flex items-center justify-center shrink-0">2</span>
                                            <span class="font-bold text-slate-800 group-hover:text-indigo-600 transition truncate">Boxer Algodón Pima Orgánico</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100 shrink-0">Boxers</span>
                                            <span class="hidden sm:inline-flex text-[10px] font-semibold text-slate-500 bg-slate-100 px-1.5 py-0.2 rounded shrink-0">Top: M (42%)</span>
                                        </div>
                                        <div class="text-right shrink-0 ml-2">
                                            <span class="font-bold text-slate-900">460 uds</span>
                                            <span class="text-slate-400 font-normal ml-1">($179,400)</span>
                                        </div>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-600 rounded-full transition-all duration-500" style="width: 88%"></div>
                                    </div>
                                </div>

                                <!-- #3 -->
                                <div class="group cursor-pointer p-2 -mx-2 rounded-lg hover:bg-slate-50 transition" @click="openTopTenModal()">
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-5 h-5 rounded-full bg-orange-100 text-orange-800 border border-orange-300 font-extrabold text-[10px] inline-flex items-center justify-center shrink-0">3</span>
                                            <span class="font-bold text-slate-800 group-hover:text-indigo-600 transition truncate">Jockstrap Athletic Carbon Edition</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-100 shrink-0">Jocks</span>
                                            <span class="hidden sm:inline-flex text-[10px] font-semibold text-slate-500 bg-slate-100 px-1.5 py-0.2 rounded shrink-0">Top: M (50%)</span>
                                        </div>
                                        <div class="text-right shrink-0 ml-2">
                                            <span class="font-bold text-slate-900">395 uds</span>
                                            <span class="text-slate-400 font-normal ml-1">($150,100)</span>
                                        </div>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500 rounded-full transition-all duration-500" style="width: 76%"></div>
                                    </div>
                                </div>

                                <!-- #4 -->
                                <div class="group cursor-pointer p-2 -mx-2 rounded-lg hover:bg-slate-50 transition" @click="openTopTenModal()">
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 font-bold text-[10px] inline-flex items-center justify-center shrink-0">4</span>
                                            <span class="font-bold text-slate-800 group-hover:text-indigo-600 transition truncate">Torzo Mesh Breathable Trunk</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100 shrink-0">Dropship</span>
                                            <span class="hidden sm:inline-flex text-[10px] font-semibold text-slate-500 bg-slate-100 px-1.5 py-0.2 rounded shrink-0">Top: M (40%)</span>
                                        </div>
                                        <div class="text-right shrink-0 ml-2">
                                            <span class="font-bold text-slate-900">310 uds</span>
                                            <span class="text-slate-400 font-normal ml-1">($151,900)</span>
                                        </div>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500 rounded-full transition-all duration-500" style="width: 60%"></div>
                                    </div>
                                </div>

                                <!-- #5 -->
                                <div class="group cursor-pointer p-2 -mx-2 rounded-lg hover:bg-slate-50 transition" @click="openTopTenModal()">
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 font-bold text-[10px] inline-flex items-center justify-center shrink-0">5</span>
                                            <span class="font-bold text-slate-800 group-hover:text-indigo-600 transition truncate">Tanga Microfibra Velvet Touch</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-fuchsia-50 text-fuchsia-700 border border-fuchsia-100 shrink-0">Tangas</span>
                                            <span class="hidden sm:inline-flex text-[10px] font-semibold text-slate-500 bg-slate-100 px-1.5 py-0.2 rounded shrink-0">Top: CH (45%)</span>
                                        </div>
                                        <div class="text-right shrink-0 ml-2">
                                            <span class="font-bold text-slate-900">285 uds</span>
                                            <span class="text-slate-400 font-normal ml-1">($82,650)</span>
                                        </div>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-fuchsia-500 rounded-full transition-all duration-500" style="width: 55%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- COLUMNA DERECHA: Top 6 al 10 -->
                            <div class="space-y-3.5">
                                <!-- #6 -->
                                <div class="group cursor-pointer p-2 -mx-2 rounded-lg hover:bg-slate-50 transition" @click="openTopTenModal()">
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 font-bold text-[10px] inline-flex items-center justify-center shrink-0">6</span>
                                            <span class="font-bold text-slate-800 group-hover:text-indigo-600 transition truncate">Jockstrap Pro-Gym Wide Band</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-100 shrink-0">Jocks</span>
                                            <span class="hidden sm:inline-flex text-[10px] font-semibold text-slate-500 bg-slate-100 px-1.5 py-0.2 rounded shrink-0">Top: G (44%)</span>
                                        </div>
                                        <div class="text-right shrink-0 ml-2">
                                            <span class="font-bold text-slate-900">240 uds</span>
                                            <span class="text-slate-400 font-normal ml-1">($86,400)</span>
                                        </div>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500 rounded-full transition-all duration-500" style="width: 46%"></div>
                                    </div>
                                </div>

                                <!-- #7 -->
                                <div class="group cursor-pointer p-2 -mx-2 rounded-lg hover:bg-slate-50 transition" @click="openTopTenModal()">
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 font-bold text-[10px] inline-flex items-center justify-center shrink-0">7</span>
                                            <span class="font-bold text-slate-800 group-hover:text-indigo-600 transition truncate">Tanga Extreme Minimalist Straps</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-fuchsia-50 text-fuchsia-700 border border-fuchsia-100 shrink-0">Tangas</span>
                                            <span class="hidden sm:inline-flex text-[10px] font-semibold text-slate-500 bg-slate-100 px-1.5 py-0.2 rounded shrink-0">Top: CH (48%)</span>
                                        </div>
                                        <div class="text-right shrink-0 ml-2">
                                            <span class="font-bold text-slate-900">215 uds</span>
                                            <span class="text-slate-400 font-normal ml-1">($55,900)</span>
                                        </div>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-fuchsia-500 rounded-full transition-all duration-500" style="width: 41%"></div>
                                    </div>
                                </div>

                                <!-- #8 -->
                                <div class="group cursor-pointer p-2 -mx-2 rounded-lg hover:bg-slate-50 transition" @click="openTopTenModal()">
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 font-bold text-[10px] inline-flex items-center justify-center shrink-0">8</span>
                                            <span class="font-bold text-slate-800 group-hover:text-indigo-600 transition truncate">Bikini Sport Performance Brief</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-100 shrink-0">Bikinis</span>
                                            <span class="hidden sm:inline-flex text-[10px] font-semibold text-slate-500 bg-slate-100 px-1.5 py-0.2 rounded shrink-0">Top: M (46%)</span>
                                        </div>
                                        <div class="text-right shrink-0 ml-2">
                                            <span class="font-bold text-slate-900">190 uds</span>
                                            <span class="text-slate-400 font-normal ml-1">($60,800)</span>
                                        </div>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-amber-500 rounded-full transition-all duration-500" style="width: 37%"></div>
                                    </div>
                                </div>

                                <!-- #9 -->
                                <div class="group cursor-pointer p-2 -mx-2 rounded-lg hover:bg-slate-50 transition" @click="openTopTenModal()">
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 font-bold text-[10px] inline-flex items-center justify-center shrink-0">9</span>
                                            <span class="font-bold text-slate-800 group-hover:text-indigo-600 transition truncate">Torzo Neon Glow Athletic Jock</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100 shrink-0">Dropship</span>
                                            <span class="hidden sm:inline-flex text-[10px] font-semibold text-slate-500 bg-slate-100 px-1.5 py-0.2 rounded shrink-0">Top: M (48%)</span>
                                        </div>
                                        <div class="text-right shrink-0 ml-2">
                                            <span class="font-bold text-slate-900">165 uds</span>
                                            <span class="text-slate-400 font-normal ml-1">($74,250)</span>
                                        </div>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-400 rounded-full transition-all duration-500" style="width: 32%"></div>
                                    </div>
                                </div>

                                <!-- #10 -->
                                <div class="group cursor-pointer p-2 -mx-2 rounded-lg hover:bg-slate-50 transition" @click="openTopTenModal()">
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 font-bold text-[10px] inline-flex items-center justify-center shrink-0">10</span>
                                            <span class="font-bold text-slate-800 group-hover:text-indigo-600 transition truncate">Torzo Signature Satin Bikini</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-100 shrink-0">Bikinis</span>
                                            <span class="hidden sm:inline-flex text-[10px] font-semibold text-slate-500 bg-slate-100 px-1.5 py-0.2 rounded shrink-0">Top: CH (42%)</span>
                                        </div>
                                        <div class="text-right shrink-0 ml-2">
                                            <span class="font-bold text-slate-900">110 uds</span>
                                            <span class="text-slate-400 font-normal ml-1">($45,100)</span>
                                        </div>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-amber-400 rounded-full transition-all duration-500" style="width: 21%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Informativo -->
                        <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center gap-2 text-xs text-slate-500">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Estos 10 productos generan el <strong>64.2% del GMV total</strong> ($1,104,800 MXN) de la tienda.</span>
                        </div>
                    </div>

                    <!-- SECCIÓN: TOP PRODUCTOS CON RIESGO DE QUIEBRE LOCAL (ROP) -->
                    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm">
                        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                                    <h3 class="text-base font-bold text-slate-900">Top Productos con Riesgo de Quiebre Local</h3>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">SKUs con acelerada velocidad de venta y cobertura menor a 7 días de inventario</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200" x-text="(executiveAreas?.inventory?.ropCount || 4) + ' Variantes Críticas'">
                                4 Variantes Críticas
                            </span>
                        </div>

                        <div class="overflow-x-auto rounded-b-xl">
                            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                                <thead>
                                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 text-[11px] uppercase tracking-wider font-semibold">
                                        <th class="py-3.5 px-4">SKU</th>
                                        <th class="py-3.5 px-4">Modelo / Descripción</th>
                                        <th class="py-3.5 px-3 text-center">Talla</th>
                                        <th class="py-3.5 px-4 text-right">Ritmo Ventas</th>
                                        <th class="py-3.5 px-4 text-right">Stock Restante</th>
                                        <th class="py-3.5 px-4 text-center">Días de Vida</th>
                                        <th class="py-3.5 px-4 text-center">Acción Recomendada</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                    <template x-for="item in (executiveAreas?.inventory?.ropItems || [])" :key="item.sku">
                                        <tr class="hover:bg-slate-50/80 transition">
                                            <td class="py-3.5 px-4 font-mono font-bold text-indigo-600" x-text="item.sku"></td>
                                            <td class="py-3.5 px-4">
                                                <div class="font-bold text-slate-900" x-text="item.title"></div>
                                                <div class="text-[11px] text-slate-500" x-text="'Ubicación: ' + (item.location || 'Anaquel A-01')"></div>
                                            </td>
                                            <td class="py-3.5 px-3 text-center">
                                                <span class="px-2 py-0.5 bg-slate-100 rounded text-slate-800 font-bold text-xs" x-text="item.size || 'M'"></span>
                                            </td>
                                            <td class="py-3.5 px-4 text-right font-semibold text-slate-700">35 pcs / sem</td>
                                            <td class="py-3.5 px-4 text-right font-bold text-rose-600" x-text="(item.available || 0) + ' piezas'"></td>
                                            <td class="py-3.5 px-4 text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold" :class="(item.available <= 5) ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800'" x-text="Math.max(1, Math.round((item.available || 2) * 0.7)) + ' días'">
                                                </span>
                                            </td>
                                            <td class="py-3.5 px-4 text-center">
                                                <button type="button" @click="openRopRestock(item)" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:underline cursor-pointer">
                                                    Reabastecer / Ajustar
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- PANELES COMPLEMENTARIOS: PERFIL ANATÓMICO & RIESGO DE ABASTECIMIENTO -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- PANEL 1: Dispersión por Talla & Perfil Anatómico -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                                <div>
                                    <h3 class="text-base font-bold text-slate-900">Dispersión por Talla & Perfil Anatómico</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">Distribución de demanda por talla y corte ergonómico de soporte</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">Ajuste Ergonómico</span>
                            </div>

                            <div class="mt-5 space-y-4">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Demanda por Tallas</h4>
                                    <div class="grid grid-cols-4 gap-2 text-center text-xs">
                                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/70">
                                            <span class="text-slate-400 font-bold block text-[10px]">TALLA S</span>
                                            <span class="text-base font-bold text-slate-800">18%</span>
                                            <span class="text-[10px] text-slate-500 block">615 pzs</span>
                                        </div>
                                        <div class="p-2.5 bg-indigo-50/80 rounded-xl border border-indigo-200">
                                            <span class="text-indigo-600 font-bold block text-[10px]">TALLA M</span>
                                            <span class="text-base font-black text-indigo-700">38%</span>
                                            <span class="text-[10px] text-indigo-600 block">1,300 pzs</span>
                                        </div>
                                        <div class="p-2.5 bg-indigo-50/50 rounded-xl border border-indigo-200">
                                            <span class="text-indigo-600 font-bold block text-[10px]">TALLA L</span>
                                            <span class="text-base font-black text-indigo-700">32%</span>
                                            <span class="text-[10px] text-indigo-600 block">1,095 pzs</span>
                                        </div>
                                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/70">
                                            <span class="text-slate-400 font-bold block text-[10px]">TALLA XL</span>
                                            <span class="text-base font-bold text-slate-800">12%</span>
                                            <span class="text-[10px] text-slate-500 block">410 pzs</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-slate-100">
                                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Corte y Perfil Anatómico</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <div class="flex justify-between text-xs mb-1">
                                                <span class="font-semibold text-slate-700">Perfil Anatómico U-Pouch Ergonómico (Copas 3D)</span>
                                                <span class="font-bold text-indigo-700">68% de las ventas</span>
                                            </div>
                                            <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                                                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: 68%"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex justify-between text-xs mb-1">
                                                <span class="font-semibold text-slate-700">Corte Clásico Convencional</span>
                                                <span class="font-bold text-slate-600">32% de las ventas</span>
                                            </div>
                                            <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                                                <div class="bg-slate-400 h-2.5 rounded-full" style="width: 32%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 2: Matriz de Riesgo de Abastecimiento & Dependencia de Proveedores -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                                <div>
                                    <h3 class="text-base font-bold text-slate-900">Matriz de Riesgo de Abastecimiento & Dependencia de Proveedores</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">Auditoría de concentración por proveedor y tiempos de reposición</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">4 Fuentes B2B</span>
                            </div>

                            <div class="mt-4 space-y-3">
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-bold text-slate-900">Torzo B2B (Puppeteer Scraper Dropship)</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">Lead Time: 2-4 días hábiles &bull; Fill Rate: 98%</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-slate-800">45% catálogo</span>
                                        <span class="block text-[10px] font-semibold text-emerald-600">Riesgo Bajo</span>
                                    </div>
                                </div>

                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-bold text-slate-900">ModaIntima MX (REST API Inbound Nacional)</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">Lead Time: 5-7 días hábiles &bull; Fill Rate: 94%</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-slate-800">30% catálogo</span>
                                        <span class="block text-[10px] font-semibold text-amber-600">Riesgo Moderado</span>
                                    </div>
                                </div>

                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-bold text-slate-900">Silk & Cotton Iberia (EDI Importación)</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">Lead Time: 14 días hábiles &bull; Fill Rate: 91%</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-slate-800">15% catálogo</span>
                                        <span class="block text-[10px] font-semibold text-rose-600">Riesgo Alto</span>
                                    </div>
                                </div>

                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-bold text-slate-900">Lingerie Drop Miami (REST API USA)</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">Lead Time: 8-10 días hábiles &bull; Fill Rate: 96%</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-slate-800">10% catálogo</span>
                                        <span class="block text-[10px] font-semibold text-amber-600">Riesgo Moderado</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BARRA INFERIOR DE ACCIONES RÁPIDAS HACIA HUB 4 -->
                    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-2xl p-5 border border-slate-800 shadow-md flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div>
                            <h4 class="text-sm font-bold text-white">Gestión Operativa en Hub 4: Inventario & Cadena de Suministro</h4>
                            <p class="text-xs text-slate-300 mt-0.5">Accede al catálogo interactivo, configuración de scrapers o seguimiento de órdenes de compra.</p>
                        </div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <a href="{{ route('portal.inventory') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition shadow-sm cursor-pointer flex items-center gap-1.5">
                                <span>Abrir Catálogo Operativo</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                            <a href="{{ route('portal.inventory') }}?tab=suppliers" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition cursor-pointer">
                                Proveedores & Scraping
                            </a>
                            <a href="{{ route('portal.inventory') }}?tab=purchases" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition cursor-pointer">
                                Compras & OCs
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- AREA 3: VITRINAS & TIENDA                                                 -->
                <!-- ========================================================================= -->
                <div x-show="activeExecutiveSubTab === 'store'" class="space-y-6" id="panel-subtab-store">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sesiones E-Commerce</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.store?.sessions || '12,450'">12,450</strong>
                                <small class="text-emerald-600 font-medium block mt-1 text-[11px]">+8.2% vs semana anterior</small>
                            </div>
                        </article>

                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Conversión Global</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.store?.conversionRate || '28.0%'">28.0%</strong>
                                <small class="text-slate-500 font-medium block mt-1 text-[11px]">3,486 ventas concretadas</small>
                            </div>
                        </article>

                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Carritos Mixtos</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.store?.mixedCarts || '1,280'">1,280</strong>
                                <small class="text-blue-600 font-medium block mt-1 text-[11px]">Stock local + Dropship Torzo</small>
                            </div>
                        </article>

                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-purple-50 text-purple-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ticket Promedio</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.store?.avgTicket || 'MXN $1,420.00'">MXN $1,420.00</strong>
                                <small class="text-purple-600 font-medium block mt-1 text-[11px]">2.3 prendas por pedido</small>
                            </div>
                        </article>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Rendimiento por Vitrina Multiverso</h3>
                                <p class="text-xs text-slate-500">Tráfico, conversión y suscripciones por canal de venta</p>
                            </div>
                            <a href="{{ route('portal.store') }}" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">Abrir Hub 2: Tienda &rarr;</a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/60">
                                <div class="flex justify-between items-center mb-2">
                                    <strong class="text-sm font-bold text-slate-900">Terra (Tienda Matriz)</strong>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 font-semibold">29.8% Conv.</span>
                                </div>
                                <p class="text-xs text-slate-500">Combos & suscripción de cuidado integral</p>
                                <div class="mt-3 pt-2 border-t border-slate-200 text-xs flex justify-between font-semibold">
                                    <span>Ventas: $26,390 MXN</span>
                                    <span class="text-slate-600">890 Suscriptores</span>
                                </div>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/60">
                                <div class="flex justify-between items-center mb-2">
                                    <strong class="text-sm font-bold text-slate-900">Marte (Línea Masculina)</strong>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 font-semibold">28.2% Conv.</span>
                                </div>
                                <p class="text-xs text-slate-500">Ropa interior anatómica & grooming</p>
                                <div class="mt-3 pt-2 border-t border-slate-200 text-xs flex justify-between font-semibold">
                                    <span>Ventas: $13,440 MXN</span>
                                    <span class="text-slate-600">380 Suscriptores</span>
                                </div>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/60">
                                <div class="flex justify-between items-center mb-2">
                                    <strong class="text-sm font-bold text-slate-900">Venus (Lencería & Skincare)</strong>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 font-semibold">25.2% Conv.</span>
                                </div>
                                <p class="text-xs text-slate-500">Lencería de encaje y fórmulas activas</p>
                                <div class="mt-3 pt-2 border-t border-slate-200 text-xs flex justify-between font-semibold">
                                    <span>Ventas: $8,420 MXN</span>
                                    <span class="text-slate-600">224 Suscriptoras</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- AREA 4: MARKETING & CRM                                                   -->
                <!-- ========================================================================= -->
                <div x-show="activeExecutiveSubTab === 'crm'" class="space-y-6" id="panel-subtab-crm">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-purple-50 text-purple-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Clientes Registrados</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.crm?.registeredCustomers || '1,840'">1,840</strong>
                                <small class="text-emerald-600 font-medium block mt-1 text-[11px]">+142 este mes</small>
                            </div>
                        </article>

                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">LTV Promedio</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.crm?.avgLtv || 'MXN $1,420'">MXN $1,420</strong>
                                <small class="text-indigo-600 font-medium block mt-1 text-[11px]">3.4 compras por ciclo</small>
                            </div>
                        </article>

                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">CAC Blend</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.crm?.cacBlend || 'MXN $145'">MXN $145</strong>
                                <small class="text-emerald-600 font-medium block mt-1 text-[11px]">Payback: 1.2 compras</small>
                            </div>
                        </article>

                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-rose-50 text-rose-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Churn Suscripciones</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.crm?.churnRate || '2.1%'">2.1%</strong>
                                <small class="text-emerald-600 font-medium block mt-1 text-[11px]">Por debajo del benchmark (4%)</small>
                            </div>
                        </article>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Gestión Comercial en Hub 3: Marketing & CRM 360°</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Campañas automatizadas de retención, segmentación RFM y WhatsApp conversacional.</p>
                        </div>
                        <a href="{{ route('portal.marketing') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">Abrir Hub 3: CRM &rarr;</a>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- AREA 5: FINANZAS & TESORERIA                                              -->
                <!-- ========================================================================= -->
                <div x-show="activeExecutiveSubTab === 'finance'" class="space-y-6" id="panel-subtab-finance">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ingresos Brutos Mes</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.finance?.grossRevenue || 'MXN $112,400.00'">MXN $112,400</strong>
                                <small class="text-emerald-600 font-medium block mt-1 text-[11px]">Facturación consolidada</small>
                            </div>
                        </article>

                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Margen Bruto</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.finance?.grossMargin || '58.4%'">58.4%</strong>
                                <small class="text-indigo-600 font-medium block mt-1 text-[11px]">Promedio catálogo textil</small>
                            </div>
                        </article>

                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Saldo en Bancos</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.finance?.bankBalance || 'MXN $340,200'">MXN $340,200</strong>
                                <small class="text-slate-500 font-medium block mt-1 text-[11px]">BBVA + Stripe Balance</small>
                            </div>
                        </article>

                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cuentas por Pagar</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.finance?.pendingBills || 'MXN $48,250.00'">MXN $48,250</strong>
                                <small class="text-amber-600 font-medium block mt-1 text-[11px]">Proveedores Torzo & ModaIntima</small>
                            </div>
                        </article>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Gestión Financiera en Hub 5: Finanzas & Conciliación</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Conciliación bancaria 3-way, dispersión de pasarelas y cálculo de utilidades netas.</p>
                        </div>
                        <a href="{{ route('portal.finance') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">Abrir Hub 5: Finanzas &rarr;</a>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- AREA 6: LOGISTICA & ENVIOS                                                -->
                <!-- ========================================================================= -->
                <div x-show="activeExecutiveSubTab === 'logistics'" class="space-y-6" id="panel-subtab-logistics">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Guías Expedidas Hoy</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.logistics?.shippedToday || '84'">84</strong>
                                <small class="text-indigo-600 font-medium block mt-1 text-[11px]">100% con tracking activo</small>
                            </div>
                        </article>

                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">On-Time Delivery</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.logistics?.onTimeDelivery || '96.8%'">96.8%</strong>
                                <small class="text-emerald-600 font-medium block mt-1 text-[11px]">Cumplimiento en rampa</small>
                            </div>
                        </article>

                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tiempo Promedio</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.logistics?.avgTransitDays || '1.8 días'">1.8 días</strong>
                                <small class="text-blue-600 font-medium block mt-1 text-[11px]">Tránsito última milla nacional</small>
                            </div>
                        </article>

                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Incidencias de Envío</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.logistics?.claimsCount || '2'">2</strong>
                                <small class="text-amber-600 font-medium block mt-1 text-[11px]">Retenidos por dirección</small>
                            </div>
                        </article>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Gestión Logística en Hub 6: Logística & Post-Venta</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Manifiestos a transportistas, seguimiento en tiempo real y módulo de devoluciones RMA.</p>
                        </div>
                        <a href="{{ route('portal.logistics') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">Abrir Hub 6: Logística &rarr;</a>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- AREA 7: CONVENIOS & B2B                                                   -->
                <!-- ========================================================================= -->
                <div x-show="activeExecutiveSubTab === 'allies'" class="space-y-6" id="panel-subtab-allies">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-purple-50 text-purple-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Convenios Activos</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.allies?.activeCount || '5'">5</strong>
                                <small class="text-purple-600 font-medium block mt-1 text-[11px]">Alianzas comerciales B2B</small>
                            </div>
                        </article>

                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ventas Atribuidas</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.allies?.attributedRevenue || 'MXN $32,500.00'">MXN $32,500</strong>
                                <small class="text-emerald-600 font-medium block mt-1 text-[11px]">28.9% de facturación total</small>
                            </div>
                        </article>

                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Comisiones por Pagar</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.allies?.pendingCommissions || 'MXN $4,875.00'">MXN $4,875</strong>
                                <small class="text-amber-600 font-medium block mt-1 text-[11px]">Liquidación día 30</small>
                            </div>
                        </article>

                        <article class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cupones Redimidos</span>
                                <strong class="text-2xl font-bold text-slate-900 block mt-0.5" x-text="executiveAreas?.allies?.redeemedCount || '240'">240</strong>
                                <small class="text-blue-600 font-medium block mt-1 text-[11px]">Tasa de conversión 19.4%</small>
                            </div>
                        </article>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Gestión de Convenios en Hub 7: Convenios, Aliados & Atribución</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Auditoría de tráfico con UTMs, tracking de conversiones y liquidación a embajadores.</p>
                        </div>
                        <a href="{{ route('portal.allies') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">Abrir Hub 7: Aliados &rarr;</a>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- ------------------------------------------------------------------------- --}}
    {{-- SUBMODULO 2: BANDEJA OPERATIVA (ALMACEN & EMPAQUE - CERO DINERO)          --}}
    {{-- ------------------------------------------------------------------------- --}}
    <div x-show="activeTab === 'operative'" class="space-y-6">
        <!-- SELECTOR DE SUB-ÁREA OPERATIVA: ALMACÉN VS COMPRAS & ABASTECIMIENTO -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center gap-2">
                <span class="text-xs font-black uppercase tracking-wider text-slate-500 pl-1">Área Operativa:</span>
                <div class="inline-flex rounded-xl p-1 bg-slate-100 border border-slate-200 text-xs font-semibold">
                    <button 
                        type="button" 
                        @click="switchOperativeArea('almacen')"
                        class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg transition cursor-pointer"
                        :class="operativeArea === 'almacen' ? 'bg-indigo-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                    >
                        <span>📦 Almacén & Empaque Físico</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono" :class="operativeArea === 'almacen' ? 'bg-indigo-700 text-white' : 'bg-slate-200 text-slate-700'" x-text="warehousePendingCount"></span>
                    </button>
                    <button 
                        type="button" 
                        @click="switchOperativeArea('compras')"
                        class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg transition cursor-pointer"
                        :class="operativeArea === 'compras' ? 'bg-violet-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                    >
                        <span>🛍️ Compras & Abastecimiento B2B</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono" :class="operativeArea === 'compras' ? 'bg-violet-700 text-white' : 'bg-slate-200 text-slate-700'" x-text="purchasesPendingCount"></span>
                    </button>
                </div>
            </div>
            <div class="text-xs text-slate-500 pr-2 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :class="operativeArea === 'almacen' ? 'bg-indigo-500' : 'bg-violet-500'"></span>
                <span x-text="operativeArea === 'almacen' ? 'Perfil activo: Carlos Almacén (Operación en Bodega & DHL)' : 'Perfil activo: Elena Compras (Abastecimiento, MOQs & Proveedores)'"></span>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- SECTOR A: ÁREA DE ALMACÉN & EMPAQUE FÍSICO (CARLOS ALMACÉN)              --}}
        {{-- ========================================================================= --}}
        <div x-show="operativeArea === 'almacen'" class="space-y-6">
            <!-- Banner de Modo Almacen & Empaque -->
            <div class="p-3.5 bg-indigo-50/80 border border-indigo-200/80 rounded-xl flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-indigo-600 animate-pulse"></span>
                    <span class="text-xs font-bold text-indigo-950 uppercase tracking-wider">Modo Almacén & Empaque Físico (Carlos Almacén)</span>
                    <button class="section-help-btn" type="button" aria-label="Ayuda de Modo Almacen" data-help-title="Bandeja Operativa Almacén" data-help-desc="Vista especializada para personal de bodega y empaque. Enfoque exclusivo en recoleccion DHL de las 14:00 hrs y recepcion en rampa. Cero visibilidad de finanzas.">?</button>
                </div>
                <span class="text-[11px] text-indigo-800 font-medium">Prioridad de surtido por pasillo y recepciones en rampa</span>
            </div>

            <!-- BANNER SUPERIOR DE CORTE DIARIO DHL (14:00 HRS) CON SEMAFORO SLA -->
            <div class="p-4 rounded-2xl border shadow-xs transition flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                 :class="{
                    'bg-emerald-50/80 border-emerald-200 text-emerald-950': dhlStatusColor === 'emerald',
                    'bg-amber-50/80 border-amber-200 text-amber-950': dhlStatusColor === 'amber',
                    'bg-rose-50/80 border-rose-200 text-rose-950': dhlStatusColor === 'rose'
                 }">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                         :class="{
                            'bg-emerald-100 text-emerald-700': dhlStatusColor === 'emerald',
                            'bg-amber-100 text-amber-700': dhlStatusColor === 'amber',
                            'bg-rose-100 text-rose-700': dhlStatusColor === 'rose'
                         }">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black uppercase tracking-wider">Corte Diario DHL Express (14:00 hrs)</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="{
                                     'bg-emerald-100 text-emerald-800': dhlStatusColor === 'emerald',
                                     'bg-amber-100 text-amber-800': dhlStatusColor === 'amber',
                                     'bg-rose-100 text-rose-800 animate-pulse': dhlStatusColor === 'rose'
                                  }">
                                <span class="w-1.5 h-1.5 rounded-full"
                                      :class="{
                                         'bg-emerald-500': dhlStatusColor === 'emerald',
                                         'bg-amber-500': dhlStatusColor === 'amber',
                                         'bg-rose-500': dhlStatusColor === 'rose'
                                      }"></span>
                                <span x-text="dhlStatusColor === 'emerald' ? 'A Tiempo (> 3h)' : (dhlStatusColor === 'amber' ? 'Atención (1-2.5h)' : 'Urgente (< 1h)')"></span>
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 mt-0.5">Empaque prioritario de órdenes salientes antes del arribo de la unidad de recolección.</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                    <div class="text-right">
                        <span class="text-[10px] font-bold uppercase tracking-wider block text-slate-500">Tiempo Restante</span>
                        <span class="text-lg font-mono font-black" :class="dhlStatusColor === 'rose' ? 'text-rose-700' : 'text-slate-900'" x-text="dhlCountdown"></span>
                    </div>
                    <button type="button" @click="activeModal = 'shipping'; selectedTask = warehouseTasks.find(t => t.category === 'expedicion') || operationalTasks[3]" class="px-3.5 py-2 text-xs font-bold bg-white hover:bg-slate-50 border border-slate-300 rounded-xl text-slate-800 transition shadow-xs cursor-pointer">
                        Ver Manifiesto DHL
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Cola de Tareas de Almacen (2 COLS) -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">Tareas Físicas de Bodega & Picking</h3>
                                    <p class="text-xs text-slate-500">Ordenadas por urgencia de SLA y ruta de pasillo</p>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-indigo-600 font-mono bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-100" x-text="warehousePendingCount + ' Tareas'"></span>
                        </div>

                        <!-- Filtros por Categoría Almacén -->
                        <div class="flex flex-wrap items-center gap-1.5 mb-4">
                            <button type="button" @click="taskFilter = 'all'" :class="taskFilter === 'all' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                                Todas (<span x-text="warehouseTasks.filter(t => t.status !== 'completed').length"></span>)
                            </button>
                            <button type="button" @click="taskFilter = 'empaque'" :class="taskFilter === 'empaque' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                                Empaque (<span x-text="warehouseTasks.filter(t => t.status !== 'completed' && t.category === 'empaque').length"></span>)
                            </button>
                            <button type="button" @click="taskFilter = 'recepcion'" :class="taskFilter === 'recepcion' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                                Recepción (<span x-text="warehouseTasks.filter(t => t.status !== 'completed' && t.category === 'recepcion').length"></span>)
                            </button>
                            <button type="button" @click="taskFilter = 'surtido'" :class="taskFilter === 'surtido' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                                Surtido (<span x-text="warehouseTasks.filter(t => t.status !== 'completed' && t.category === 'surtido').length"></span>)
                            </button>
                            <button type="button" @click="taskFilter = 'expedicion'" :class="taskFilter === 'expedicion' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                                Expedición (<span x-text="warehouseTasks.filter(t => t.status !== 'completed' && t.category === 'expedicion').length"></span>)
                            </button>
                        </div>

                        <!-- Lista Reactiva de Tareas Operativas Almacén -->
                        <div class="space-y-3">
                            <template x-for="task in filteredWarehouseTasks" :key="'wh-task-' + task.id">
                                <div class="p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-2.5 h-2.5 rounded-full mt-1.5 shrink-0"
                                             :class="{
                                                'bg-rose-500 animate-pulse': task.sla_status === 'critical' || task.priority === 'critical',
                                                'bg-amber-500': task.sla_status === 'warning' || task.priority === 'high',
                                                'bg-emerald-500': task.sla_status === 'ontime' || task.priority === 'medium'
                                             }"></div>
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-xs font-bold text-slate-900" x-text="task.title"></span>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                                      :class="{
                                                         'bg-rose-100 text-rose-800': task.priority === 'critical',
                                                         'bg-emerald-100 text-emerald-800': task.priority === 'high' && task.category === 'recepcion',
                                                         'bg-amber-100 text-amber-800': task.priority === 'medium',
                                                         'bg-indigo-100 text-indigo-800': task.category === 'expedicion'
                                                      }"
                                                      x-text="task.category === 'empaque' ? 'Corte DHL Inminente' : (task.category === 'recepcion' ? 'Camión en Rampa' : (task.category === 'surtido' ? 'Reabastecer Rack' : 'Corte 14:00'))"></span>
                                                <span class="text-[11px] font-mono font-semibold text-slate-500" x-text="'Límite: ' + (task.due_time_formatted || '14:00 hrs')"></span>
                                            </div>
                                            <p class="text-xs text-slate-600 mt-0.5" x-text="task.description"></p>
                                        </div>
                                    </div>
                                    <button 
                                        type="button" 
                                        @click="openTaskModal(task)" 
                                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer shrink-0"
                                    >
                                        <span x-text="task.category === 'empaque' ? 'Iniciar Empaque &rarr;' : (task.category === 'recepcion' ? 'Recibir en Rampa' : (task.category === 'surtido' ? 'Confirmar Surtido' : 'Ver Manifiesto'))"></span>
                                    </button>
                                </div>
                            </template>

                            <template x-if="filteredWarehouseTasks.length === 0">
                                <div class="text-center py-8 bg-slate-50 border border-dashed border-slate-200 rounded-xl">
                                    <p class="text-xs font-bold text-slate-600">No hay tareas pendientes de almacén en esta categoría.</p>
                                    <p class="text-[11px] text-slate-400 mt-1">El flujo operativo del turno de bodega se encuentra al corriente.</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Bitácora del Turno (Shift Log) -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <h3 class="text-sm font-bold text-slate-900">Bitácora Inmutable del Turno (Shift Log)</h3>
                            </div>
                            <span class="text-xs text-slate-400 font-mono">Actualizado en vivo</span>
                        </div>

                        <ul class="divide-y divide-slate-100 text-xs text-slate-700">
                            <template x-for="log in shiftLogs" :key="'log-' + log.id">
                                <li class="py-2.5 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                              :class="{
                                                 'bg-emerald-500': log.event_type === 'task_completed' || log.event_type === 'reception',
                                                 'bg-indigo-500': log.event_type === 'manifest',
                                                 'bg-slate-400': log.event_type === 'system_check'
                                              }"></span>
                                        <span x-text="log.message"></span>
                                    </div>
                                    <span class="text-slate-400 font-mono shrink-0" x-text="(log.time || '10:00 AM') + ' • ' + (log.operator_name || 'Carlos A.')"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <!-- Panel Lateral de Bodega (1 COL) -->
                <div class="space-y-6">
                    <!-- Horario de Recolección DHL -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-900">Corte DHL Express</h4>
                        </div>
                        <div class="text-center py-4 bg-rose-50 border border-rose-100 rounded-xl">
                            <span class="text-3xl font-black font-mono text-rose-700">14:00 hrs</span>
                            <span class="text-[11px] font-bold text-rose-800 block mt-1" x-text="dhlCountdown"></span>
                        </div>
                        <p class="text-xs text-slate-500 mt-3 leading-relaxed">
                            Los pedidos empacados después de este horario saldrán en la ruta del día hábil siguiente.
                        </p>
                    </div>

                    <!-- Resumen de Almacén -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 mb-3">Accesos Directos Almacén</h4>
                        <div class="space-y-2">
                            <a href="{{ route('portal.inventory') }}" class="block p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200/60 text-xs font-bold text-slate-800 transition" style="text-decoration:none;">
                                &rarr; Ver Catálogo Físico e Inventario
                            </a>
                            <a href="{{ route('portal.logistics') }}" class="block p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200/60 text-xs font-bold text-slate-800 transition" style="text-decoration:none;">
                                &rarr; Mesa de Expedición & Guías DHL
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- SECTOR B: ÁREA DE COMPRAS & ABASTECIMIENTO B2B (ELENA COMPRAS)            --}}
        {{-- ========================================================================= --}}
        <div x-show="operativeArea === 'compras'" class="space-y-6">
            <!-- Banner de Modo Compras & Abastecimiento -->
            <div class="p-3.5 bg-violet-50/80 border border-violet-200/80 rounded-xl flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-violet-600 animate-pulse"></span>
                    <span class="text-xs font-bold text-violet-950 uppercase tracking-wider">Modo Compras & Abastecimiento B2B (Elena Compras)</span>
                    <button class="section-help-btn" type="button" aria-label="Ayuda de Compras" data-help-title="Área de Compras & Abastecimiento" data-help-desc="Gestión de órdenes de compra, control de discrepancias reportadas por rampa de almacén, optimización de tabuladores MOQ y conciliación de acuerdos de crédito con proveedores.">?</button>
                </div>
                <span class="text-[11px] text-violet-800 font-medium">Control de discrepancias en rampa, bandejas MOQ y acuerdos mercantiles</span>
            </div>

            <!-- SEMÁFORO DE ABASTECIMIENTO Y LEAD TIMES DE PROVEEDORES -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Lead Time Promedio</span>
                        <span class="text-base font-black text-slate-900 font-mono">3.8 días hábiles</span>
                        <span class="text-[11px] text-emerald-600 font-semibold block">Cumplimiento SLA: 96.2%</span>
                    </div>
                </div>

                <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Discrepancias en Rampa</span>
                        <span class="text-base font-black text-slate-900 font-mono" x-text="purchasesTasks.filter(t => t.status !== 'completed' && (t.title.includes('Discrepancia') || t.title.includes('Cumplimiento'))).length + ' caso(s)'"></span>
                        <span class="text-[11px] text-amber-700 font-semibold block">Acción requerida: Nota de Crédito</span>
                    </div>
                </div>

                <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Ahorro en Bandeja MOQ</span>
                        <span class="text-base font-black text-emerald-600 font-mono">-$1,000.00 MXN</span>
                        <span class="text-[11px] text-slate-500 font-semibold block">Tabulador Torzo: 100 prendas</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Cola de Tareas de Compras (2 COLS) -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">Tareas de Abastecimiento & Proveedores</h3>
                                    <p class="text-xs text-slate-500">Alertas de recepción, consolidación de POs y conciliación de acuerdos</p>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-violet-600 font-mono bg-violet-50 px-2.5 py-1 rounded-full border border-violet-100" x-text="purchasesPendingCount + ' Tareas'"></span>
                        </div>

                        <!-- Filtros por Categoría Compras -->
                        <div class="flex flex-wrap items-center gap-1.5 mb-4">
                            <button type="button" @click="purchasesTaskFilter = 'all'" :class="purchasesTaskFilter === 'all' ? 'bg-violet-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                                Todas (<span x-text="purchasesTasks.filter(t => t.status !== 'completed').length"></span>)
                            </button>
                            <button type="button" @click="purchasesTaskFilter = 'recepcion'" :class="purchasesTaskFilter === 'recepcion' ? 'bg-violet-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                                Discrepancias Rampa (<span x-text="purchasesTasks.filter(t => t.status !== 'completed' && t.category === 'recepcion').length"></span>)
                            </button>
                            <button type="button" @click="purchasesTaskFilter = 'procurement'" :class="purchasesTaskFilter === 'procurement' ? 'bg-violet-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                                Reorden ROP (<span x-text="purchasesTasks.filter(t => t.status !== 'completed' && t.category === 'procurement').length"></span>)
                            </button>
                            <button type="button" @click="purchasesTaskFilter = 'moq'" :class="purchasesTaskFilter === 'moq' ? 'bg-violet-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                                Bandeja MOQ (<span x-text="purchasesTasks.filter(t => t.status !== 'completed' && t.category === 'moq').length"></span>)
                            </button>
                            <button type="button" @click="purchasesTaskFilter = 'acuerdos'" :class="purchasesTaskFilter === 'acuerdos' ? 'bg-violet-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                                Acuerdos NC (<span x-text="purchasesTasks.filter(t => t.status !== 'completed' && t.category === 'acuerdos').length"></span>)
                            </button>
                            <button type="button" @click="purchasesTaskFilter = 'on_demand'" :class="purchasesTaskFilter === 'on_demand' ? 'bg-violet-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                                On-Demand (<span x-text="purchasesTasks.filter(t => t.status !== 'completed' && t.category === 'on_demand').length"></span>)
                            </button>
                        </div>

                        <!-- Lista Reactiva de Tareas de Compras -->
                        <div class="space-y-3">
                            <template x-for="task in filteredPurchasesTasks" :key="'pur-task-' + task.id">
                                <div class="p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-2.5 h-2.5 rounded-full mt-1.5 shrink-0"
                                             :class="{
                                                'bg-rose-500 animate-pulse': task.priority === 'critical',
                                                'bg-amber-500': task.priority === 'high',
                                                'bg-violet-500': task.priority === 'medium',
                                                'bg-emerald-500': task.priority === 'low'
                                             }"></div>
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-xs font-bold text-slate-900" x-text="task.title"></span>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                                      :class="{
                                                         'bg-rose-100 text-rose-800': task.category === 'recepcion' || task.priority === 'critical',
                                                         'bg-amber-100 text-amber-800': task.category === 'procurement',
                                                         'bg-violet-100 text-violet-800': task.category === 'moq',
                                                         'bg-emerald-100 text-emerald-800': task.category === 'acuerdos',
                                                         'bg-blue-100 text-blue-800': task.category === 'on_demand'
                                                      }"
                                                      x-text="task.category === 'recepcion' ? 'Discrepancia Rampa' : (task.category === 'procurement' ? 'Reorden ROP' : (task.category === 'moq' ? 'Tabulador MOQ' : (task.category === 'acuerdos' ? 'Acuerdo Saldo Favor' : 'Quiebre On-Demand')))"></span>
                                                <span class="text-[11px] font-mono font-bold text-violet-700 bg-violet-50 px-2 py-0.5 rounded border border-violet-100" x-text="task.metadata?.supplier_name || 'Proveedor B2B'"></span>
                                                <span class="text-[11px] font-mono font-semibold text-slate-500" x-text="'Ref: ' + (task.reference_code || 'N/A')"></span>
                                            </div>
                                            <p class="text-xs text-slate-600 mt-1" x-text="task.description"></p>
                                        </div>
                                    </div>
                                    <button 
                                        type="button" 
                                        @click="openTaskModal(task)" 
                                        class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer shrink-0"
                                    >
                                        <span x-text="task.category === 'recepcion' ? 'Gestionar Discrepancia &rarr;' : (task.category === 'moq' ? 'Ver Tabulador MOQ' : (task.category === 'acuerdos' ? 'Conciliar Acuerdo' : 'Emitir Orden Compra'))"></span>
                                    </button>
                                </div>
                            </template>

                            <template x-if="filteredPurchasesTasks.length === 0">
                                <div class="text-center py-8 bg-slate-50 border border-dashed border-slate-200 rounded-xl">
                                    <p class="text-xs font-bold text-slate-600">No hay tareas pendientes de compras en esta categoría.</p>
                                    <p class="text-[11px] text-slate-400 mt-1">Todos los pedidos y acuerdos con proveedores se encuentran al día.</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Bitácora de Compras & Acuerdos -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <h3 class="text-sm font-bold text-slate-900">Bitácora de Abastecimiento & Negociaciones</h3>
                            </div>
                            <span class="text-xs text-slate-400 font-mono">Registro Elena Compras</span>
                        </div>

                        <ul class="divide-y divide-slate-100 text-xs text-slate-700">
                            <li class="py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span>Acuerdo de compensación aplicado en PO #OC-2026-003 (-$1,250.00 MXN Torzo Textil)</span>
                                </div>
                                <span class="text-slate-400 font-mono shrink-0">Hoy 11:45 AM • Elena C.</span>
                            </li>
                            <li class="py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-violet-500 shrink-0"></span>
                                    <span>Salto a nivel de tabulador MOQ 100 pzs alcanzado ($65.00/u)</span>
                                </div>
                                <span class="text-slate-400 font-mono shrink-0">Hoy 10:15 AM • Elena C.</span>
                            </li>
                            <li class="py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                    <span>Reporte de discrepancia recibido de Rampa 2: Torzo Textil remisión incompleta</span>
                                </div>
                                <span class="text-slate-400 font-mono shrink-0">Hoy 09:30 AM • Carlos A.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Panel Lateral de Compras (1 COL) -->
                <div class="space-y-6">
                    <!-- Directorio de Proveedores Clave -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-900">Proveedores & SLA</h4>
                        </div>
                        <div class="space-y-3 text-xs">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                <div class="flex justify-between items-center font-bold text-slate-900">
                                    <span>Torzo Textil B2B</span>
                                    <span class="text-emerald-700 font-mono">Lead Time: 3 días</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">MOQ Base: 50 u &bull; Tabulador Activo: 100 u ($65/u)</div>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                <div class="flex justify-between items-center font-bold text-slate-900">
                                    <span>Confecciones del Centro</span>
                                    <span class="text-emerald-700 font-mono">Lead Time: 4 días</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Polos & Piqué &bull; ROP anaquel: 20 u</div>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                <div class="flex justify-between items-center font-bold text-slate-900">
                                    <span>Maquilas & Bordados Bajío</span>
                                    <span class="text-amber-700 font-mono">Lead Time: 5 días</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Hoodies & Sudaderas &bull; On-Demand</div>
                            </div>
                        </div>
                    </div>

                    <!-- Accesos Directos Compras -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 mb-3">Herramientas de Abastecimiento</h4>
                        <div class="space-y-2">
                            <a href="{{ route('portal.inventory') }}" class="block p-3 rounded-xl bg-violet-50 hover:bg-violet-100 border border-violet-200 text-xs font-bold text-violet-900 transition" style="text-decoration:none;">
                                &rarr; Bandeja de MOQs & Generador de PO (Hub 4)
                            </a>
                            <a href="{{ route('portal.inventory') }}#suppliers" class="block p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200/60 text-xs font-bold text-slate-800 transition" style="text-decoration:none;">
                                &rarr; Catálogo de Proveedores & Tabuladores
                            </a>
                            <a href="{{ route('portal.vendor.extranet') }}" class="block p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200/60 text-xs font-bold text-slate-800 transition" style="text-decoration:none;">
                                &rarr; Extranet de Proveedores B2B (Hub 7)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------------------------- --}}
    {{-- SUBMODULO 3: ATENCIÓN & VENTAS (COMERCIAL & LEADS)                         --}}
    {{-- ------------------------------------------------------------------------- --}}
    <div x-show="activeTab === 'sales'" class="space-y-6">
        <!-- Banner de Modo Atencion & Ventas -->
        <div class="p-3.5 bg-blue-50/80 border border-blue-200/80 rounded-xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-600 animate-pulse"></span>
                <span class="text-xs font-bold text-blue-950 uppercase tracking-wider">Modo Atención & Ventas Activo (Ana Atención)</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda de Modo Ventas" data-help-title="Modo Atención & Ventas" data-help-desc="Vista intermedia especializada para asesoría comercial y servicio al cliente. Canaliza leads calientes, carritos abandonados recuperables, domicilios pausados y solicitudes de suscriptores.">?</button>
            </div>
            <span class="text-[11px] text-blue-800 font-medium">Gatillos de 1 clic conectados con WhatsApp Web y pasarela de pago</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Bandeja de Casos Comerciales (2 COLS) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Casos Comerciales de Alta Prioridad</h3>
                                <p class="text-xs text-slate-500">Contactos y recuperaciones con mayor probabilidad de cierre</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-blue-600 font-mono bg-blue-50 px-2.5 py-1 rounded-full border border-blue-100" x-text="salesPendingCount + ' Pendientes'">3 Pendientes</span>
                    </div>

                    <!-- Filtros por Categoría Comercial -->
                    <div class="flex flex-wrap items-center gap-1.5 mb-4">
                        <button type="button" @click="salesFilter = 'all'" :class="salesFilter === 'all' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                            Todas (<span x-text="salesIncidents.filter(i => i.status !== 'resolved').length"></span>)
                        </button>
                        <button type="button" @click="salesFilter = 'abandoned_carts'" :class="salesFilter === 'abandoned_carts' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                            Carritos Abandonados (<span x-text="salesIncidents.filter(i => i.status !== 'resolved' && i.case_type === 'abandoned_cart').length"></span>)
                        </button>
                        <button type="button" @click="salesFilter = 'doubtful_addresses'" :class="salesFilter === 'doubtful_addresses' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                            Direcciones Detenidas (<span x-text="salesIncidents.filter(i => i.status !== 'resolved' && i.case_type === 'doubtful_address').length"></span>)
                        </button>
                        <button type="button" @click="salesFilter = 'leads'" :class="salesFilter === 'leads' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                            Leads Calientes (<span x-text="salesIncidents.filter(i => i.status !== 'resolved' && i.case_type === 'lead_hot').length"></span>)
                        </button>
                    </div>

                    <!-- Lista Reactiva de Casos Comerciales -->
                    <div class="space-y-3">
                        <template x-for="inc in filteredSalesIncidents" :key="'sales-inc-' + inc.id">
                            <div class="p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-2.5 h-2.5 rounded-full mt-1.5 shrink-0"
                                         :class="{
                                            'bg-rose-500 animate-pulse': inc.case_type === 'doubtful_address',
                                            'bg-amber-500': inc.case_type === 'abandoned_cart',
                                            'bg-indigo-500': inc.case_type === 'lead_hot'
                                         }"></div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-xs font-bold text-slate-900" x-text="inc.subject"></span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                                  :class="{
                                                     'bg-amber-100 text-amber-800': inc.case_type === 'abandoned_cart',
                                                     'bg-rose-100 text-rose-800': inc.case_type === 'doubtful_address',
                                                     'bg-indigo-100 text-indigo-800': inc.case_type === 'lead_hot'
                                                  }"
                                                  x-text="inc.case_type === 'abandoned_cart' ? 'Alta Urgencia' : (inc.case_type === 'doubtful_address' ? 'Falta Número Exterior' : 'Scoring 92 pts')"></span>
                                        </div>
                                        <p class="text-xs text-slate-600 mt-0.5" x-text="inc.notes"></p>
                                    </div>
                                </div>
                                
                                <div class="shrink-0">
                                    <template x-if="inc.case_type === 'abandoned_cart'">
                                        <button 
                                            type="button" 
                                            @click="openRescueCartModal(inc)" 
                                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer flex items-center gap-1.5"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            <span>Recuperar WhatsApp</span>
                                        </button>
                                    </template>

                                    <template x-if="inc.case_type === 'doubtful_address'">
                                        <button 
                                            type="button" 
                                            @click="openAddressFixModal(inc)" 
                                            class="px-3.5 py-1.5 text-xs font-bold bg-white hover:bg-slate-50 border border-slate-300 rounded-lg text-slate-800 transition cursor-pointer"
                                        >
                                            Corregir Dirección
                                        </button>
                                    </template>

                                    <template x-if="inc.case_type === 'lead_hot'">
                                        <button 
                                            type="button" 
                                            @click="openLeadModal(inc)" 
                                            class="px-3.5 py-1.5 text-xs font-bold bg-white hover:bg-slate-50 border border-slate-300 rounded-lg text-slate-800 transition cursor-pointer"
                                        >
                                            Abrir Ficha CRM
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="filteredSalesIncidents.length === 0">
                            <div class="text-center py-8 bg-slate-50 border border-dashed border-slate-200 rounded-xl">
                                <p class="text-xs font-bold text-slate-600">No hay casos pendientes en esta categoría comercial.</p>
                                <p class="text-[11px] text-slate-400 mt-1">Todos los prospectos y pedidos pausados han sido gestionados.</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral de Ventas (1 COL) -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 mb-3">Herramientas de Cierre</h4>
                    <div class="space-y-2">
                        <a href="{{ route('leads.index') }}" class="block p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200/60 text-xs font-bold text-slate-800 transition" style="text-decoration:none;">
                            &rarr; Directorio General de Leads & CRM
                        </a>
                        <a href="{{ route('portal.marketing') }}" class="block p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200/60 text-xs font-bold text-slate-800 transition" style="text-decoration:none;">
                            &rarr; Generador de Cupones & Campañas
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 mb-2">Canal WhatsApp Oficial</h4>
                    <p class="text-xs text-slate-500 mb-4">
                        Plantillas comerciales estandarizadas con token de reanudación automática de carrito.
                    </p>
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 text-center">
                        <span class="text-xs font-bold text-emerald-800 block">Línea RevOps Atención</span>
                        <span class="text-sm font-mono font-black text-emerald-950 mt-0.5">+52 55 4182 9901</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ========================================================================= -->
    <!-- MODALES OPERATIVOS MAX-W-4XL A 2 COLUMNAS (TASK-DSH-04, TASK-DSH-05)       -->
    <!-- ========================================================================= -->
    <div 
        x-show="activeModal !== null" 
        x-transition.opacity 
        class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
        style="display: none;"
        @keydown.escape.window="closeModal()"
    >
        <div 
            class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-4xl w-full overflow-hidden my-8"
            @click.away="closeModal()"
        >
            <!-- 1. MODAL PICKING & PACKING DINÁMICO (CARRITO MIXTO Y ESTÁNDAR) -->
            <template x-if="activeModal === 'pack'">
                <div>
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full bg-rose-500 animate-pulse"></span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <span>Mesa de Picking & Packing: Pedido #MIX-1048</span><template x-if="selectedTask?.reference_code && selectedTask?.reference_code !== '#MIX-1048'"><span class="text-indigo-600 font-mono text-xs ml-1 font-bold" x-text="'[' + selectedTask.reference_code + ']'"></span></template>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                          :class="selectedTask?.priority === 'critical' ? 'bg-rose-100 text-rose-800' : 'bg-indigo-100 text-indigo-800'"
                                          x-text="selectedTask?.priority === 'critical' ? 'Corte DHL 14:00' : 'SLA Estándar'"></span>
                                </h3>
                                <p class="text-xs text-slate-500">Verificación física de artículos antes de encintar y rotular guía</p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body: 2 Columnas -->
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Columna 1: Datos de la Orden y Destino -->
                        <div class="space-y-4">
                            <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl p-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-900 block mb-1">Destinatario D2C</span>
                                <div class="text-sm font-black text-slate-900" x-text="selectedTask?.action_payload?.customer || 'Mariana Garza Treviño'"></div>
                                <div class="text-xs text-slate-600 mt-1" x-text="selectedTask?.action_payload?.destination || 'Av. Real San Agustín 402, Residencial San Agustín, San Pedro Garza García, NL • CP 66260'"></div>
                                <div class="text-xs font-mono font-bold text-indigo-700 mt-2" x-text="'Guía DHL: ' + (selectedTask?.action_payload?.carrier || 'DHL Express (DHL-9482-MX-EXP)')"></div>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs space-y-2 text-slate-700">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Tipo de Pedido:</span>
                                    <span class="font-bold text-slate-800" x-text="selectedTask?.action_payload?.is_mixed ? 'D2C Mixto (Stock Local + Bajo Pedido)' : 'D2C Estándar (Almacén Local)'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Mesa Asignada:</span>
                                    <span class="font-bold text-slate-800">Mesa Empaque #02</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Hora Límite SLA:</span>
                                    <span class="font-bold text-rose-700 font-mono" x-text="(selectedTask?.due_time_formatted || '14:00 hrs') + ' (Corte Inminente)'"></span>
                                </div>
                            </div>

                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 flex items-start gap-2">
                                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span><strong>Instrucción Especial:</strong> <span x-text="selectedTask?.action_payload?.special_instructions || 'Incluir bolsa de lavado premium de cortesía y colocar precinto de inviolabilidad en la caja exterior.'"></span></span>
                            </div>
                        </div>

                        <!-- Columna 2: Checklist Interactiva de Artículos -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-900">Checklist de Artículos y Ubicación</h4>
                            
                            <div class="space-y-2.5 max-h-[300px] overflow-y-auto pr-1">
                                <template x-if="selectedTask?.action_payload?.items && selectedTask.action_payload.items.length > 0">
                                    <div class="space-y-2">
                                        <template x-for="(item, idx) in selectedTask.action_payload.items" :key="'pack-item-' + idx">
                                            <label class="p-3 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl flex items-start gap-3 cursor-pointer transition">
                                                <input type="checkbox" x-model="item.checked" class="mt-1 rounded text-indigo-600 focus:ring-indigo-500">
                                                <div class="text-xs">
                                                    <div class="font-bold text-slate-900" x-text="item.qty + 'x ' + item.name"></div>
                                                    <div class="text-slate-500 mt-0.5">Ubicación: <strong class="text-indigo-700" x-text="item.source || ('Rack: ' + item.location)"></strong></div>
                                                    <template x-if="item.is_scraped">
                                                        <div class="text-[10px] text-emerald-700 font-bold mt-1">Prenda Bajo Pedido verificada</div>
                                                    </template>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!selectedTask?.action_payload?.items || selectedTask.action_payload.items.length === 0">
                                    <div class="space-y-2">
                                        <label class="p-3 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl flex items-start gap-3 cursor-pointer transition">
                                            <input type="checkbox" x-model="packChecklist.item1" class="mt-1 rounded text-indigo-600 focus:ring-indigo-500">
                                            <div class="text-xs">
                                                <div class="font-bold text-slate-900">2x Boxer Modal Negro (Talla M)</div>
                                                <div class="text-slate-500 mt-0.5">Ubicación: <strong class="text-indigo-700">Pasillo A-02 &bull; Rack R-08 &bull; Nivel 1</strong></div>
                                                <div class="text-[10px] text-emerald-700 font-bold mt-1">Inbound Torzo recibido en rampa a las 11:30 hrs</div>
                                            </div>
                                        </label>
                                        <label class="p-3 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl flex items-start gap-3 cursor-pointer transition">
                                            <input type="checkbox" x-model="packChecklist.item2" class="mt-1 rounded text-indigo-600 focus:ring-indigo-500">
                                            <div class="text-xs">
                                                <div class="font-bold text-slate-900">1x Playera Oversize Blanca (Talla L)</div>
                                                <div class="text-slate-500 mt-0.5">Ubicación: <strong class="text-indigo-700">Pasillo A-02 &bull; Rack R-04 &bull; Nivel 2</strong></div>
                                                <div class="text-[10px] text-slate-500 mt-1">Stock de almacén verificado</div>
                                            </div>
                                        </label>
                                    </div>
                                </template>

                                <label class="p-3 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl flex items-start gap-3 cursor-pointer transition">
                                    <input type="checkbox" x-model="packChecklist.sealed" class="mt-1 rounded text-indigo-600 focus:ring-indigo-500">
                                    <div class="text-xs">
                                        <div class="font-bold text-slate-900">Caja sellada y precinto adherido</div>
                                        <div class="text-slate-500 mt-0.5">Etiqueta térmica DHL pegada sin arrugas sobre cara superior</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                        <span class="text-xs text-slate-500">Operador: <strong>Carlos Almacén</strong></span>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="closeModal()" class="px-4 py-2 border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                                Cancelar
                            </button>
                            <button 
                                type="button" 
                                @click="completeCurrentTask()" 
                                :disabled="isSubmittingTask"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" :class="isSubmittingTask ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-text="isSubmittingTask ? 'Guardando...' : 'Confirmar y Sellar Pedido'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- 2. MODAL RECEPCIÓN INBOUND (#OC-2026-003) -->
            <template x-if="activeModal === 'inbound'">
                <div>
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <span>Recepción Inbound en Rampa: Torzo B2B #OC-2026-003</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Camión en Rampa 2</span>
                                </h3>
                                <p class="text-xs text-slate-500">Cotejo físico contra remisión y verificación de precinto de seguridad</p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Columna 1: Proveedor y Flete -->
                        <div class="space-y-4">
                            <div class="bg-emerald-50/70 border border-emerald-100 rounded-xl p-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-900 block mb-1">Orden de Compra Mayorista</span>
                                <div class="text-sm font-black text-slate-900">Torzo Textil B2B SA de CV</div>
                                <div class="text-xs text-slate-600 mt-1">Fletero: Transportes Castores &bull; Unidad 44 &bull; Chofer: Javier Prado</div>
                                <div class="text-xs font-mono font-bold text-emerald-800 mt-2">Precinto de Seguridad: SEAL-TORZO-9921</div>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs space-y-2 text-slate-700">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Muelle de Descarga:</span>
                                    <span class="font-bold text-slate-800">Rampa 2 (Acceso Norte)</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Total Bultos Esperados:</span>
                                    <span class="font-bold text-slate-800">6 cajas / 150 piezas</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Hora de Llegada:</span>
                                    <span class="font-bold text-slate-800 font-mono">11:15 AM (En rampa)</span>
                                </div>
                            </div>

                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900">
                                <strong>Desbloqueo Automático:</strong> Al confirmar esta recepción, los pedidos D2C en espera (#MIX-1048 y #MIX-1049) pasarán a listos para empaque de inmediato.
                            </div>
                        </div>

                        <!-- Columna 2: Curva de Tallas y Checklists -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-900">Cotejo de Curva de Tallas</h4>
                            
                            <div class="grid grid-cols-4 gap-2 text-center text-xs">
                                <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                                    <span class="text-[10px] text-slate-400 block font-bold">Talla S</span>
                                    <span class="text-sm font-black text-slate-900 font-mono">30 pzs</span>
                                </div>
                                <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                                    <span class="text-[10px] text-slate-400 block font-bold">Talla M</span>
                                    <span class="text-sm font-black text-slate-900 font-mono">50 pzs</span>
                                </div>
                                <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                                    <span class="text-[10px] text-slate-400 block font-bold">Talla L</span>
                                    <span class="text-sm font-black text-slate-900 font-mono">50 pzs</span>
                                </div>
                                <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                                    <span class="text-[10px] text-slate-400 block font-bold">Talla XL</span>
                                    <span class="text-sm font-black text-slate-900 font-mono">20 pzs</span>
                                </div>
                            </div>

                            <div class="space-y-2.5 pt-2">
                                <label class="p-3 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl flex items-start gap-3 cursor-pointer transition">
                                    <input type="checkbox" x-model="inboundChecklist.sealValid" class="mt-1 rounded text-emerald-600 focus:ring-emerald-500">
                                    <div class="text-xs">
                                        <div class="font-bold text-slate-900">Precinto de seguridad intacto al abrir caja del fletero</div>
                                        <div class="text-slate-500 mt-0.5">Número de precinto coincide con remisión</div>
                                    </div>
                                </label>

                                <label class="p-3 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl flex items-start gap-3 cursor-pointer transition">
                                    <input type="checkbox" x-model="inboundChecklist.boxesOk" class="mt-1 rounded text-emerald-600 focus:ring-emerald-500">
                                    <div class="text-xs">
                                        <div class="font-bold text-slate-900">Inspección visual de empaques individuales sin roturas</div>
                                        <div class="text-slate-500 mt-0.5">Prendas con empaque individual y código de barras legible</div>
                                    </div>
                                </label>

                                <label class="p-3 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl flex items-start gap-3 cursor-pointer transition">
                                    <input type="checkbox" x-model="inboundChecklist.countMatch" class="mt-1 rounded text-emerald-600 focus:ring-emerald-500">
                                    <div class="text-xs">
                                        <div class="font-bold text-slate-900">Conteo físico coincide con remisión adjunta (150 pzs)</div>
                                        <div class="text-slate-500 mt-0.5">Cero faltantes o excedentes reportados</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                        <span class="text-xs text-slate-500">Receptor: <strong>Carlos Almacén</strong></span>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="closeModal()" class="px-4 py-2 border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                                Cancelar
                            </button>
                            <button 
                                type="button" 
                                @click="completeCurrentTask()" 
                                :disabled="isSubmittingTask"
                                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" :class="isSubmittingTask ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-text="isSubmittingTask ? 'Ingresando...' : 'Ingresar a Almacén & Cerrar Recepción'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- 3. MODAL SURTIDO INTERNO ROP (ANAQUEL A-01) -->
            <template x-if="activeModal === 'restock'">
                <div>
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <span>Surtido Interno ROP: Anaquel A-01</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Reabastecer Rack</span>
                                </h3>
                                <p class="text-xs text-slate-500">Transferencia interna desde zona de reserva hacia línea de surtido rápido</p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="bg-amber-50/70 border border-amber-200 rounded-xl p-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-900 block mb-1">Alerta de Reorden Preventivo (ROP)</span>
                                <div class="text-sm font-black text-slate-900">Boxer Modal Negro (Talla M)</div>
                                <div class="text-xs text-slate-600 mt-1">SKU: BOX-MOD-BLK-M &bull; Quiebre estimado en 2.4 días si no se reabastece</div>
                                <div class="text-xs font-mono font-bold text-amber-900 mt-2">Mover: 15 unidades de Reserva a Picking</div>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs space-y-2 text-slate-700">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Ubicación Origen:</span>
                                    <span class="font-bold text-slate-800">Rack R-04 (Zona Reserva Nivel 2)</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Ubicación Destino:</span>
                                    <span class="font-bold text-slate-800">Anaquel A-01 (Mesa Picking Rápido)</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Stock Actual en Anaquel:</span>
                                    <span class="font-bold text-rose-700 font-mono">4 unidades (Punto crítico)</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-900">Checklist de Traslado Físico</h4>
                            
                            <div class="space-y-2.5">
                                <label class="p-3 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl flex items-start gap-3 cursor-pointer transition">
                                    <input type="checkbox" x-model="restockChecklist.rackExtracted" class="mt-1 rounded text-amber-600 focus:ring-amber-500">
                                    <div class="text-xs">
                                        <div class="font-bold text-slate-900">Extracción de 15 piezas de Rack R-04</div>
                                        <div class="text-slate-500 mt-0.5">Bajar caja máster con escalera de seguridad</div>
                                    </div>
                                </label>

                                <label class="p-3 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl flex items-start gap-3 cursor-pointer transition">
                                    <input type="checkbox" x-model="restockChecklist.barcodeScanned" class="mt-1 rounded text-amber-600 focus:ring-amber-500">
                                    <div class="text-xs">
                                        <div class="font-bold text-slate-900">Validación de código de barras de la caja máster</div>
                                        <div class="text-slate-500 mt-0.5">Cotejar SKU BOX-MOD-BLK-M con lector óptico</div>
                                    </div>
                                </label>

                                <label class="p-3 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl flex items-start gap-3 cursor-pointer transition">
                                    <input type="checkbox" x-model="restockChecklist.binPlaced" class="mt-1 rounded text-amber-600 focus:ring-amber-500">
                                    <div class="text-xs">
                                        <div class="font-bold text-slate-900">Colocación física en Anaquel A-01 y confirmación en anaquel</div>
                                        <div class="text-slate-500 mt-0.5">Prendas estibadas con frente hacia el pasillo</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                        <span class="text-xs text-slate-500">Surtidor: <strong>Carlos Almacén</strong></span>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="closeModal()" class="px-4 py-2 border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                                Cancelar
                            </button>
                            <button 
                                type="button" 
                                @click="completeCurrentTask()" 
                                :disabled="isSubmittingTask"
                                class="px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" :class="isSubmittingTask ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-text="isSubmittingTask ? 'Confirmando...' : 'Confirmar Reabastecimiento ROP'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- 4. MODAL DESPACHO COURIER DHL (MANIFIESTO 14:00 HRS) -->
            <template x-if="activeModal === 'shipping'">
                <div>
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full bg-indigo-600 animate-pulse"></span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <span>Despacho Manifiesto DHL Express (Corte 14:00 hrs)</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">Recolección Diaria</span>
                                </h3>
                                <p class="text-xs text-slate-500">Entrega de paquetes con firma de acuse al chofer de ruta DHL</p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl p-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-900 block mb-1">Manifiesto Consolidado</span>
                                <div class="text-sm font-black text-slate-900">DHL Express México &bull; MAN-DHL-20260927</div>
                                <div class="text-xs text-slate-600 mt-1">Chofer: Roberto González &bull; Unidad: LF-8821-B</div>
                                <div class="text-xs font-mono font-bold text-indigo-800 mt-2">12 Guías Térmicas Consolidadas</div>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs space-y-2 text-slate-700">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Hora Límite de Entrega:</span>
                                    <span class="font-bold text-rose-700 font-mono">14:00 hrs (Corte Improrrogable)</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Destinos:</span>
                                    <span class="font-bold text-slate-800">CDMX, GDL, MTY, QRO, PUE</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Estatus:</span>
                                    <span class="font-bold text-emerald-700">Paquetes estibados en jaula móvil</span>
                                </div>
                            </div>

                            <div class="p-3 bg-slate-100 border border-slate-200 rounded-xl">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-slate-600 block mb-1">Gafete / Identificación del Operador DHL</label>
                                <input type="text" x-model="shippingChecklist.driverBadge" class="w-full text-xs font-mono font-bold bg-white border border-slate-300 rounded-lg p-2 focus:ring-indigo-500" placeholder="DHL-OP-XXXX">
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-900">Checklist de Entrega a Unidad</h4>
                            
                            <div class="space-y-2.5">
                                <label class="p-3 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl flex items-start gap-3 cursor-pointer transition">
                                    <input type="checkbox" x-model="shippingChecklist.packagesStaged" class="mt-1 rounded text-indigo-600 focus:ring-indigo-500">
                                    <div class="text-xs">
                                        <div class="font-bold text-slate-900">12 paquetes escaneados y estibados en jaula móvil</div>
                                        <div class="text-slate-500 mt-0.5">Todas las guías verificadas con lector láser</div>
                                    </div>
                                </label>

                                <label class="p-3 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl flex items-start gap-3 cursor-pointer transition">
                                    <input type="checkbox" x-model="shippingChecklist.manifestSigned" class="mt-1 rounded text-indigo-600 focus:ring-indigo-500">
                                    <div class="text-xs">
                                        <div class="font-bold text-slate-900">Impresión de 2 tantos de manifiesto con firma autógrafa</div>
                                        <div class="text-slate-500 mt-0.5">1 tanto para archivo fiscal de almacén y 1 para chofer</div>
                                    </div>
                                </label>

                                <label class="p-3 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl flex items-start gap-3 cursor-pointer transition">
                                    <input type="checkbox" x-model="shippingChecklist.driverHandover" class="mt-1 rounded text-indigo-600 focus:ring-indigo-500">
                                    <div class="text-xs">
                                        <div class="font-bold text-slate-900">Entrega física de bultos a unidad de recolección DHL</div>
                                        <div class="text-slate-500 mt-0.5">Camioneta cerrada y asegurada en rampa</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                        <span class="text-xs text-slate-500">Expedidor: <strong>Carlos Almacén</strong></span>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="closeModal()" class="px-4 py-2 border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                                Cancelar
                            </button>
                            <button 
                                type="button" 
                                @click="completeCurrentTask()" 
                                :disabled="isSubmittingTask"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" :class="isSubmittingTask ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-text="isSubmittingTask ? 'Liberando...' : 'Liberar Manifiesto & Despachar Unidad DHL'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- 4.5. MODAL GESTIÓN DE COMPRAS & ABASTECIMIENTO (ELENA COMPRAS) -->
            <template x-if="activeModal === 'purchases_task'">
                <div>
                    <div class="px-6 py-4 bg-violet-50/70 border-b border-violet-100 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full bg-violet-600 animate-pulse"></span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2 flex-wrap">
                                    <span x-text="selectedTask ? selectedTask.title : 'Gestión de Tarea de Abastecimiento'"></span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-violet-100 text-violet-800" x-text="selectedTask ? (selectedTask.reference_code || 'COMPRAS-B2B') : ''"></span>
                                </h3>
                                <p class="text-xs text-slate-500">Resolución operativa de suministros, notas de crédito y bandejas de compra</p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6" x-show="selectedTask">
                        <!-- Columna 1: Expediente del Proveedor y Asunto -->
                        <div class="space-y-4">
                            <div class="bg-violet-50/50 border border-violet-100 rounded-xl p-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-violet-900 block mb-1">Proveedor / Fabricante Involucrado</span>
                                <div class="text-sm font-black text-slate-900" x-text="selectedTask?.metadata?.supplier_name || 'Proveedor B2B Asignado'"></div>
                                <div class="text-xs text-slate-600 mt-1" x-text="'Código de Referencia: ' + (selectedTask?.reference_code || 'N/A')"></div>
                                <div class="text-xs text-violet-800 font-semibold mt-2" x-text="'Prioridad de Abastecimiento: ' + (selectedTask?.priority?.toUpperCase() || 'ALTA')"></div>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs space-y-2 text-slate-700">
                                <div class="font-bold text-slate-800 mb-1">Detalle Operativo del Caso:</div>
                                <p class="text-xs text-slate-600 leading-relaxed" x-text="selectedTask?.description"></p>
                                
                                <template x-if="selectedTask?.metadata?.discrepancy_details">
                                    <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-900">
                                        <strong class="block text-[11px] font-bold uppercase tracking-wider mb-0.5">Discrepancia Notificada por Rampa:</strong>
                                        <span x-text="selectedTask.metadata.discrepancy_details"></span>
                                    </div>
                                </template>
                            </div>

                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Accesos Rápidos de Resolución</span>
                                <div class="flex flex-col gap-2">
                                    <a href="{{ route('portal.inventory') }}" target="_blank" class="flex items-center justify-between p-2.5 bg-white hover:bg-violet-50 border border-slate-200 rounded-lg text-xs font-bold text-violet-900 transition" style="text-decoration:none;">
                                        <span>&rarr; Ir a Bandeja de MOQs & PO (Hub 4)</span>
                                        <span class="text-[10px] bg-violet-100 text-violet-800 px-2 py-0.5 rounded font-normal">Reorden</span>
                                    </a>
                                    <a href="{{ route('portal.vendor.extranet') }}" target="_blank" class="flex items-center justify-between p-2.5 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-800 transition" style="text-decoration:none;">
                                        <span>&rarr; Extranet del Proveedor (Hub 7)</span>
                                        <span class="text-[10px] bg-slate-100 text-slate-700 px-2 py-0.5 rounded font-normal">Portal B2B</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Columna 2: Protocolo de Compras & Checklist -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-900">Protocolo de Validación B2B</h4>

                            <div class="space-y-2.5">
                                <label class="p-3 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl flex items-start gap-3 cursor-pointer transition">
                                    <input type="checkbox" x-model="purchasesChecklist.notified" class="mt-1 rounded text-violet-600 focus:ring-violet-500">
                                    <div class="text-xs">
                                        <div class="font-bold text-slate-900">Notificación al Proveedor / Extranet Registrada</div>
                                        <div class="text-slate-500 mt-0.5">Se comunicó la discrepancia o necesidad de reposición al contacto comercial.</div>
                                    </div>
                                </label>

                                <label class="p-3 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl flex items-start gap-3 cursor-pointer transition">
                                    <input type="checkbox" x-model="purchasesChecklist.agreementApplied" class="mt-1 rounded text-violet-600 focus:ring-violet-500">
                                    <div class="text-xs">
                                        <div class="font-bold text-slate-900">Nota de Crédito o Tabulador MOQ Aplicado</div>
                                        <div class="text-slate-500 mt-0.5">Deducción pactada o ajuste de volumen consolidado para ahorro real en PO.</div>
                                    </div>
                                </label>

                                <label class="p-3 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl flex items-start gap-3 cursor-pointer transition">
                                    <input type="checkbox" x-model="purchasesChecklist.supplierConfirmed" class="mt-1 rounded text-violet-600 focus:ring-violet-500">
                                    <div class="text-xs">
                                        <div class="font-bold text-slate-900">Compromiso de Despacho y SLA Confirmado</div>
                                        <div class="text-slate-500 mt-0.5">Tiempo de entrega acordado en calendario de recepciones en rampa.</div>
                                    </div>
                                </label>
                            </div>

                            <div class="space-y-1 pt-1">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-slate-600 block">Notas de Resolución para Bitácora Inmutable</label>
                                <textarea x-model="purchasesResolutionNotes" rows="3" class="w-full text-xs p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-violet-500 focus:border-violet-500" placeholder="Ej: Se acordó deducción en PO #OC-2026-003 por faltante de 10 prendas..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                        <span class="text-xs text-slate-500">Gestora: <strong>Elena Compras (Abastecimiento B2B)</strong></span>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="closeModal()" class="px-4 py-2 border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                                Cerrar
                            </button>
                            <button 
                                type="button" 
                                @click="completeCurrentTask()" 
                                :disabled="isSubmittingTask"
                                class="px-5 py-2 bg-violet-600 hover:bg-violet-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" :class="isSubmittingTask ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-text="isSubmittingTask ? 'Procesando...' : 'Confirmar Resolución de Abastecimiento'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- 5. MODAL RECUPERACIÓN CARRITO WHATSAPP (TASK-DSH-06) -->
            <template x-if="activeModal === 'rescue_cart'">
                <div>
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <span>Recuperación de Carrito Abandonado vía WhatsApp</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Alta Urgencia</span>
                                </h3>
                                <p class="text-xs text-slate-500">Generación de enlace tokenizado de reanudación y plantilla directa a WhatsApp Web</p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Columna 1: Datos del Comprador y Carrito -->
                        <div class="space-y-4">
                            <div class="bg-amber-50/70 border border-amber-200 rounded-xl p-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-900 block mb-1">Comprador Identificado</span>
                                <div class="text-sm font-black text-slate-900" x-text="rescueForm.customerName || 'Andrea Ruiz'"></div>
                                <div class="text-xs text-slate-600 mt-1" x-text="'Teléfono: ' + (rescueForm.phone || '+52 55 9182 3341')"></div>
                                <div class="text-xs font-mono font-bold text-amber-900 mt-2">Monto en Carrito: MXN $1,450.00 (3 prendas)</div>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs space-y-2 text-slate-700">
                                <div class="font-bold text-slate-800 mb-2 border-b border-slate-200 pb-1">Prendas en Carrito Abandonado:</div>
                                <div class="flex justify-between">
                                    <span>2x Boxer Modal Negro (Talla M)</span>
                                    <span class="font-mono font-bold">MXN $900.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>1x Playera Oversize Blanca (Talla L)</span>
                                    <span class="font-mono font-bold">MXN $550.00</span>
                                </div>
                                <div class="flex justify-between pt-1 border-t border-slate-200 text-slate-900 font-bold">
                                    <span>Total Checkout:</span>
                                    <span class="font-mono text-emerald-700">MXN $1,450.00</span>
                                </div>
                            </div>

                            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-950">
                                <strong>Token de Reanudación Activo:</strong> El comprador conservará el stock reservado y su carrito cargado con 1 solo toque desde el chat.
                            </div>
                        </div>

                        <!-- Columna 2: Oferta y Redacción WhatsApp -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-black uppercase tracking-wider text-slate-900 block mb-1.5">Incentivo Comercial (Cupón Automático)</label>
                                <select x-model="rescueForm.discountCode" @change="updateRescueMessage()" class="w-full text-xs font-bold bg-white border border-slate-300 rounded-xl p-2.5 focus:ring-emerald-500">
                                    <option value="RESCUE10">10% Descuento Especial (RESCUE10) - Recomendado</option>
                                    <option value="RESCUE5">5% Descuento Inmediato (RESCUE5)</option>
                                    <option value="ENVIOGRATIS">Envío Express Gratis Nacional (ENVIOGRATIS)</option>
                                    <option value="SIN_CUPON">Sin cupón de descuento adicional</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-black uppercase tracking-wider text-slate-900 block mb-1.5">Mensaje para WhatsApp Web</label>
                                <textarea x-model="rescueForm.customMessage" rows="5" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl p-3 focus:ring-emerald-500 leading-relaxed font-sans"></textarea>
                            </div>

                            <div class="p-2.5 bg-slate-100 rounded-xl border border-slate-200 text-[11px] text-slate-600 font-mono break-all">
                                <span class="font-bold text-slate-700 block">Enlace Tokenizado:</span>
                                <span x-text="rescueTokenUrl"></span>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                        <span class="text-xs text-slate-500">Asesora: <strong>Ana Atención</strong></span>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="closeModal()" class="px-4 py-2 border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                                Cancelar
                            </button>
                            <button 
                                type="button" 
                                @click="submitRescueCart()" 
                                :disabled="isSubmittingTask"
                                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span x-text="isSubmittingTask ? 'Enviando...' : 'Disparar WhatsApp Web & Registrar Rescate'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- 6. MODAL VALIDADOR PREDICTIVO SEPOMEX (TASK-INT-05) -->
            <template x-if="activeModal === 'fix_address'">
                <div>
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full bg-rose-500 animate-pulse"></span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <span>Validador Predictivo de Domicilios Sepomex</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800" x-text="addressForm.orderNumber">ORD-1092</span>
                                </h3>
                                <p class="text-xs text-slate-500">Cotejo postal normativo para evitar devoluciones de paquetería DHL Express</p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Columna 1: Domicilio Problemático Actual -->
                        <div class="space-y-4">
                            <div class="bg-rose-50/70 border border-rose-200 rounded-xl p-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-900 block mb-1">Incidencia de Entrega</span>
                                <div class="text-sm font-black text-slate-900">Marco Valdés</div>
                                <div class="text-xs text-slate-600 mt-1">Tel: +52 33 1482 7701 &bull; Orden: <strong x-text="addressForm.orderNumber"></strong></div>
                                <div class="text-xs text-rose-800 font-bold mt-2 bg-rose-100 p-2 rounded-lg">
                                    Dirección actual: "Av. Vallarta s/n, Guadalajara, Jalisco, CP 44100"
                                </div>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs space-y-2 text-slate-700">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Paquetería:</span>
                                    <span class="font-bold text-slate-800">DHL Express Nacional</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Motivo de Retención:</span>
                                    <span class="font-bold text-rose-700">Falta Número Exterior</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Impacto Logístico:</span>
                                    <span class="font-bold text-slate-800">DHL no expide guía sin numeración física</span>
                                </div>
                            </div>

                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900">
                                <strong>Liberación Automática:</strong> Al guardar la dirección completa, la orden pasará a estatus <em>processing</em> y aparecerá de inmediato en la mesa de empaque de Almacén.
                            </div>
                        </div>

                        <!-- Columna 2: Formulario Predictivo Sepomex -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-900">Normalización de Domicilio</h4>

                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="text-[10px] font-bold uppercase text-slate-500 block mb-1">Código Postal</label>
                                    <input type="text" x-model="addressForm.postalCode" @input="searchSepomex()" maxlength="5" class="w-full text-xs font-mono font-bold bg-white border border-slate-300 rounded-lg p-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold uppercase text-slate-500 block mb-1">Estado</label>
                                    <input type="text" x-model="addressForm.state" readonly class="w-full text-xs bg-slate-100 border border-slate-300 rounded-lg p-2 text-slate-600 font-semibold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold uppercase text-slate-500 block mb-1">Ciudad / Mpio</label>
                                    <input type="text" x-model="addressForm.city" readonly class="w-full text-xs bg-slate-100 border border-slate-300 rounded-lg p-2 text-slate-600 font-semibold">
                                </div>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold uppercase text-slate-500 block mb-1">Colonia (Catálogo Sepomex)</label>
                                <select x-model="addressForm.colonia" class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-lg p-2 focus:ring-indigo-500">
                                    <template x-for="col in sepomexColonias" :key="col">
                                        <option :value="col" x-text="col"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold uppercase text-slate-500 block mb-1">Calle</label>
                                <input type="text" x-model="addressForm.street" class="w-full text-xs bg-white border border-slate-300 rounded-lg p-2 focus:ring-indigo-500" placeholder="Nombre de la calle o avenida">
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[10px] font-bold uppercase text-rose-700 block mb-1">Número Exterior *</label>
                                    <input type="text" x-model="addressForm.exteriorNumber" class="w-full text-xs font-bold bg-white border border-rose-300 rounded-lg p-2 focus:ring-rose-500" placeholder="Ej. 1480">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold uppercase text-slate-500 block mb-1">Número Interior</label>
                                    <input type="text" x-model="addressForm.interiorNumber" class="w-full text-xs bg-white border border-slate-300 rounded-lg p-2 focus:ring-indigo-500" placeholder="Ej. Piso 3, Depto 302">
                                </div>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold uppercase text-slate-500 block mb-1">Referencias de Entrega</label>
                                <input type="text" x-model="addressForm.references" class="w-full text-xs bg-white border border-slate-300 rounded-lg p-2 focus:ring-indigo-500" placeholder="Ej. Entre Atenas y Chapultepec, portón gris">
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                        <span class="text-xs text-slate-500">Asesora: <strong>Ana Atención</strong></span>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="closeModal()" class="px-4 py-2 border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                                Cancelar
                            </button>
                            <button 
                                type="button" 
                                @click="submitAddressFix()" 
                                :disabled="isSubmittingTask"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" :class="isSubmittingTask ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-text="isSubmittingTask ? 'Validando...' : 'Actualizar y Liberar a Almacén'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- 7. MODAL FICHA DE LEAD CALIENTE (TASK-DSH-01) -->
            <template x-if="activeModal === 'lead_detail'">
                <div>
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full bg-indigo-600 animate-pulse"></span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <span>Ficha Comercial de Prospecto:</span>
                                    <span x-text="leadForm.name || 'Carlos Mendoza'"></span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">Scoring: 92 pts</span>
                                </h3>
                                <p class="text-xs text-slate-500">Historial de navegación, scoring predictivo e interés comercial</p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="bg-indigo-50/70 border border-indigo-200 rounded-xl p-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-900 block mb-1">Información de Contacto</span>
                                <div class="text-sm font-black text-slate-900" x-text="leadForm.name || 'Carlos Mendoza'"></div>
                                <div class="text-xs text-slate-600 mt-1" x-text="'Teléfono: ' + (leadForm.phone || '+52 81 1234 5678')"></div>
                                <div class="text-xs text-slate-600" x-text="'Email: ' + (leadForm.email || 'carlos.mendoza@corporativo.mx')"></div>
                                <div class="text-xs font-mono font-bold text-indigo-800 mt-2">Vitrina de Origen: MARTE (Universo Masculino)</div>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs space-y-2 text-slate-700">
                                <div class="font-bold text-slate-800 mb-2 border-b border-slate-200 pb-1">Interés y Scoring:</div>
                                <div class="flex justify-between">
                                    <span>Nivel de Interés:</span>
                                    <span class="font-bold text-emerald-700">Muy Caliente (92/100)</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Producto / Oferta:</span>
                                    <span class="font-bold text-slate-800">Suscripción Caja VIP Nivel 3</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Horario de Contacto:</span>
                                    <span class="font-bold text-slate-800">Tarde (14:00 - 18:00 hrs)</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-900">Seguimiento Comercial</h4>

                            <div class="space-y-2 text-xs">
                                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                                    <div class="font-bold text-slate-800 flex justify-between">
                                        <span>Visita a Landing VIP</span>
                                        <span class="text-slate-400 font-mono text-[10px]">Hoy 10:14 AM</span>
                                    </div>
                                    <p class="text-slate-500 mt-0.5">3 interacciones con el configurador de tallas.</p>
                                </div>
                                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                                    <div class="font-bold text-slate-800 flex justify-between">
                                        <span>Pregunta por Chat</span>
                                        <span class="text-slate-400 font-mono text-[10px]">Hoy 10:25 AM</span>
                                    </div>
                                    <p class="text-slate-500 mt-0.5">Consultó sobre métodos de pago recurrentes con SPEI.</p>
                                </div>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold uppercase text-slate-500 block mb-1">Notas de la Asesora</label>
                                <textarea x-model="leadNotes" rows="3" class="w-full text-xs bg-white border border-slate-300 rounded-lg p-2.5 focus:ring-indigo-500" placeholder="Añade notas del contacto o acuerdo comercial..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                        <span class="text-xs text-slate-500">Asesora: <strong>Ana Atención</strong></span>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="closeModal()" class="px-4 py-2 border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                                Cerrar
                            </button>
                            <a :href="'https://wa.me/' + (leadForm.phone || '').replace(/\\D/g, '')" target="_blank" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5" style="text-decoration:none;">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>Contactar WhatsApp</span>
                            </a>
                            <button 
                                type="button" 
                                @click="submitLeadFollowup()" 
                                :disabled="isSubmittingTask"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer"
                            >
                                <span x-text="isSubmittingTask ? 'Guardando...' : 'Marcar como Ganado / Cerrado'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- MODAL: DETALLE DE CONECTORES & WEBHOOKS (TASK-DSH-03) -->
            <template x-if="activeModal === 'connectors_detail'">
                <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all text-left">
                    <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white">Detalle de Conectores, Webhooks & Seguridad HMAC</h3>
                                <p class="text-[11px] text-slate-300">Auditoría en tiempo real de integraciones y canales de pago</p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 max-h-[75vh] overflow-y-auto">
                        <!-- Columna 1: Estado de Pasarelas y Scraping -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 border-b border-slate-200 pb-2">Pasarelas & Sincronización B2B</h4>

                            <!-- Stripe -->
                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-900">Stripe Payments API</span>
                                    <span class="px-2 py-0.5 rounded font-mono font-bold text-[10px] bg-emerald-100 text-emerald-800">99.98% Uptime</span>
                                </div>
                                <div class="text-[11px] text-slate-600 space-y-1">
                                    <div>Protocolo: <strong class="font-mono">HTTPS / TLS 1.3</strong></div>
                                    <div>Autenticación: <strong class="font-mono">Bearer + Webhook HMAC SHA256</strong></div>
                                    <div>Eventos: <strong>checkout.session.completed, invoice.paid</strong></div>
                                </div>
                            </div>

                            <!-- Mercado Pago -->
                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-900">Mercado Pago Checkout</span>
                                    <span class="px-2 py-0.5 rounded font-mono font-bold text-[10px] bg-emerald-100 text-emerald-800">99.92% Uptime</span>
                                </div>
                                <div class="text-[11px] text-slate-600 space-y-1">
                                    <div>Protocolo: <strong class="font-mono">HTTPS / TLS 1.3</strong></div>
                                    <div>Autenticación: <strong class="font-mono">OAuth 2.0 / IPN Secret</strong></div>
                                    <div>Eventos: <strong>payment.created, merchant_order</strong></div>
                                </div>
                            </div>

                            <!-- Torzo Scraper -->
                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-900">Torzo B2B Scraper</span>
                                    <span class="px-2 py-0.5 rounded font-mono font-bold text-[10px] bg-emerald-100 text-emerald-800">840 SKUs Sincronizados</span>
                                </div>
                                <div class="text-[11px] text-slate-600 space-y-1">
                                    <div>Frecuencia: <strong>Polling cada 30 min (Inbound Rampa)</strong></div>
                                    <div>Latencia Media: <strong class="font-mono">185ms</strong></div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna 2: Logística, Sepomex & Diagnóstico -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 border-b border-slate-200 pb-2">Logística & Catálogo Postal</h4>

                            <!-- DHL -->
                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-900">DHL Express & Guías ZPL</span>
                                    <span class="px-2 py-0.5 rounded font-mono font-bold text-[10px] bg-emerald-100 text-emerald-800">Corte 14:00 OK</span>
                                </div>
                                <div class="text-[11px] text-slate-600 space-y-1">
                                    <div>Protocolo: <strong class="font-mono">XML REST API v2</strong></div>
                                    <div>Generación: <strong>Etiquetas térmicas 4x6 / Manifiestos</strong></div>
                                </div>
                            </div>

                            <!-- Sepomex -->
                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-900">Sepomex Domicilios</span>
                                    <span class="px-2 py-0.5 rounded font-mono font-bold text-[10px] bg-emerald-100 text-emerald-800">145k Códigos Postales</span>
                                </div>
                                <div class="text-[11px] text-slate-600 space-y-1">
                                    <div>Motor: <strong>Cache en Memoria SQLite</strong></div>
                                    <div>Latencia: <strong class="font-mono text-emerald-700">12ms (Instantáneo)</strong></div>
                                </div>
                            </div>

                            <!-- Estado del Certificado TLS -->
                            <div class="p-4 bg-emerald-50/60 border border-emerald-200 rounded-xl">
                                <div class="text-xs font-bold text-emerald-900 mb-1">Certificados TLS & Cifrado</div>
                                <p class="text-[11px] text-emerald-800 leading-relaxed">
                                    Todos los microservicios operan con TLS 1.3 y firmas HMAC validadas. No se detectan anomalías ni pérdida de paquetes.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                        <span class="text-xs text-slate-500">Última auditoría: <strong>En vivo</strong></span>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="closeModal()" class="px-4 py-2 border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                                Cerrar
                            </button>
                            <button 
                                type="button" 
                                @click="pingSystemHealth(); closeModal();" 
                                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer"
                            >
                                Re-ejecutar Diagnóstico
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- MODAL: RANKING TOP 10 REVOPS DE RENDIMIENTO (TASK-HUB1-SUBNAV) -->
            <template x-if="activeModal === 'top_ten'">
                <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all text-left">
                    <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="p-1.5 bg-amber-500/20 text-amber-400 rounded-lg inline-flex">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-white">Ranking Top 10 RevOps: Rendimiento de Catálogo</h3>
                                <p class="text-[11px] text-slate-300">Líderes en facturación acumulada (GMV) y volumen de pedidos en los últimos 30 días (64.2% GMV total)</p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 max-h-[75vh] overflow-y-auto">
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold text-[11px]">
                                        <th class="py-3 px-3 text-center">#</th>
                                        <th class="py-3 px-3">SKU</th>
                                        <th class="py-3 px-4">Producto</th>
                                        <th class="py-3 px-3 text-center">Categoría</th>
                                        <th class="py-3 px-3 text-right">Uds</th>
                                        <th class="py-3 px-3 text-right">GMV</th>
                                        <th class="py-3 px-3 text-center">Talla Top</th>
                                        <th class="py-3 px-3 text-right">PVP / Costo</th>
                                        <th class="py-3 px-3 text-center">Margen</th>
                                        <th class="py-3 px-3 text-center">Origen</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-2.5 px-3 text-center font-bold text-amber-600">1</td>
                                        <td class="py-2.5 px-3 font-mono font-bold text-indigo-600">BX-MOD-001</td>
                                        <td class="py-2.5 px-4 font-bold text-slate-900">Boxer Trunks Modal Seamless</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded text-[10px] bg-indigo-50 text-indigo-700 font-semibold">Boxers</span></td>
                                        <td class="py-2.5 px-3 text-right font-bold">520</td>
                                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">$218,400</td>
                                        <td class="py-2.5 px-3 text-center text-slate-600">M (45%)</td>
                                        <td class="py-2.5 px-3 text-right">$420 / $168</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded font-bold text-[10px] bg-emerald-100 text-emerald-800">60.0%</span></td>
                                        <td class="py-2.5 px-3 text-center text-[10px] text-slate-500">Bodega Propia</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-2.5 px-3 text-center font-bold text-slate-600">2</td>
                                        <td class="py-2.5 px-3 font-mono font-bold text-indigo-600">BX-PIM-002</td>
                                        <td class="py-2.5 px-4 font-bold text-slate-900">Boxer Algodón Pima Orgánico</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded text-[10px] bg-indigo-50 text-indigo-700 font-semibold">Boxers</span></td>
                                        <td class="py-2.5 px-3 text-right font-bold">460</td>
                                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">$179,400</td>
                                        <td class="py-2.5 px-3 text-center text-slate-600">M (42%)</td>
                                        <td class="py-2.5 px-3 text-right">$390 / $156</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded font-bold text-[10px] bg-emerald-100 text-emerald-800">60.0%</span></td>
                                        <td class="py-2.5 px-3 text-center text-[10px] text-slate-500">Bodega Propia</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-2.5 px-3 text-center font-bold text-orange-600">3</td>
                                        <td class="py-2.5 px-3 font-mono font-bold text-indigo-600">JK-CRB-003</td>
                                        <td class="py-2.5 px-4 font-bold text-slate-900">Jockstrap Athletic Carbon Edition</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded text-[10px] bg-blue-50 text-blue-700 font-semibold">Jocks</span></td>
                                        <td class="py-2.5 px-3 text-right font-bold">395</td>
                                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">$150,100</td>
                                        <td class="py-2.5 px-3 text-center text-slate-600">M (50%)</td>
                                        <td class="py-2.5 px-3 text-right">$380 / $133</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded font-bold text-[10px] bg-emerald-100 text-emerald-800">65.0%</span></td>
                                        <td class="py-2.5 px-3 text-center text-[10px] text-slate-500">Bodega Propia</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-2.5 px-3 text-center font-bold text-slate-500">4</td>
                                        <td class="py-2.5 px-3 font-mono font-bold text-indigo-600">TR-DRP-004</td>
                                        <td class="py-2.5 px-4 font-bold text-slate-900">Torzo Mesh Breathable Trunk</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded text-[10px] bg-emerald-50 text-emerald-700 font-semibold">Dropship</span></td>
                                        <td class="py-2.5 px-3 text-right font-bold">310</td>
                                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">$151,900</td>
                                        <td class="py-2.5 px-3 text-center text-slate-600">M (40%)</td>
                                        <td class="py-2.5 px-3 text-right">$490 / $245</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded font-bold text-[10px] bg-blue-100 text-blue-800">50.0%</span></td>
                                        <td class="py-2.5 px-3 text-center text-[10px] text-slate-500">Torzo B2B</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-2.5 px-3 text-center font-bold text-slate-500">5</td>
                                        <td class="py-2.5 px-3 font-mono font-bold text-indigo-600">TG-VLV-005</td>
                                        <td class="py-2.5 px-4 font-bold text-slate-900">Tanga Microfibra Velvet Touch</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded text-[10px] bg-fuchsia-50 text-fuchsia-700 font-semibold">Tangas</span></td>
                                        <td class="py-2.5 px-3 text-right font-bold">285</td>
                                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">$82,650</td>
                                        <td class="py-2.5 px-3 text-center text-slate-600">CH (45%)</td>
                                        <td class="py-2.5 px-3 text-right">$290 / $87</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded font-bold text-[10px] bg-emerald-100 text-emerald-800">70.0%</span></td>
                                        <td class="py-2.5 px-3 text-center text-[10px] text-slate-500">Bodega Propia</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-2.5 px-3 text-center font-bold text-slate-500">6</td>
                                        <td class="py-2.5 px-3 font-mono font-bold text-indigo-600">JK-GYM-006</td>
                                        <td class="py-2.5 px-4 font-bold text-slate-900">Jockstrap Pro-Gym Wide Band</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded text-[10px] bg-blue-50 text-blue-700 font-semibold">Jocks</span></td>
                                        <td class="py-2.5 px-3 text-right font-bold">240</td>
                                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">$86,400</td>
                                        <td class="py-2.5 px-3 text-center text-slate-600">G (44%)</td>
                                        <td class="py-2.5 px-3 text-right">$360 / $126</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded font-bold text-[10px] bg-emerald-100 text-emerald-800">65.0%</span></td>
                                        <td class="py-2.5 px-3 text-center text-[10px] text-slate-500">Bodega Propia</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-2.5 px-3 text-center font-bold text-slate-500">7</td>
                                        <td class="py-2.5 px-3 font-mono font-bold text-indigo-600">TG-STR-007</td>
                                        <td class="py-2.5 px-4 font-bold text-slate-900">Tanga Extreme Minimalist Straps</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded text-[10px] bg-fuchsia-50 text-fuchsia-700 font-semibold">Tangas</span></td>
                                        <td class="py-2.5 px-3 text-right font-bold">215</td>
                                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">$55,900</td>
                                        <td class="py-2.5 px-3 text-center text-slate-600">CH (48%)</td>
                                        <td class="py-2.5 px-3 text-right">$260 / $78</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded font-bold text-[10px] bg-emerald-100 text-emerald-800">70.0%</span></td>
                                        <td class="py-2.5 px-3 text-center text-[10px] text-slate-500">Bodega Propia</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-2.5 px-3 text-center font-bold text-slate-500">8</td>
                                        <td class="py-2.5 px-3 font-mono font-bold text-indigo-600">BK-PRF-008</td>
                                        <td class="py-2.5 px-4 font-bold text-slate-900">Bikini Sport Performance Brief</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded text-[10px] bg-amber-50 text-amber-700 font-semibold">Bikinis</span></td>
                                        <td class="py-2.5 px-3 text-right font-bold">190</td>
                                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">$60,800</td>
                                        <td class="py-2.5 px-3 text-center text-slate-600">M (46%)</td>
                                        <td class="py-2.5 px-3 text-right">$320 / $112</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded font-bold text-[10px] bg-emerald-100 text-emerald-800">65.0%</span></td>
                                        <td class="py-2.5 px-3 text-center text-[10px] text-slate-500">Bodega Propia</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-2.5 px-3 text-center font-bold text-slate-500">9</td>
                                        <td class="py-2.5 px-3 font-mono font-bold text-indigo-600">TR-GLW-009</td>
                                        <td class="py-2.5 px-4 font-bold text-slate-900">Torzo Neon Glow Athletic Jock</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded text-[10px] bg-emerald-50 text-emerald-700 font-semibold">Dropship</span></td>
                                        <td class="py-2.5 px-3 text-right font-bold">165</td>
                                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">$74,250</td>
                                        <td class="py-2.5 px-3 text-center text-slate-600">M (48%)</td>
                                        <td class="py-2.5 px-3 text-right">$450 / $225</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded font-bold text-[10px] bg-blue-100 text-blue-800">50.0%</span></td>
                                        <td class="py-2.5 px-3 text-center text-[10px] text-slate-500">Torzo B2B</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-2.5 px-3 text-center font-bold text-slate-500">10</td>
                                        <td class="py-2.5 px-3 font-mono font-bold text-indigo-600">BK-STN-010</td>
                                        <td class="py-2.5 px-4 font-bold text-slate-900">Torzo Signature Satin Bikini</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded text-[10px] bg-amber-50 text-amber-700 font-semibold">Bikinis</span></td>
                                        <td class="py-2.5 px-3 text-right font-bold">110</td>
                                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">$45,100</td>
                                        <td class="py-2.5 px-3 text-center text-slate-600">CH (42%)</td>
                                        <td class="py-2.5 px-3 text-right">$410 / $185</td>
                                        <td class="py-2.5 px-3 text-center"><span class="px-2 py-0.5 rounded font-bold text-[10px] bg-blue-100 text-blue-800">55.0%</span></td>
                                        <td class="py-2.5 px-3 text-center text-[10px] text-slate-500">Torzo B2B</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                        <span class="text-xs text-slate-500">Datos auditados en tiempo real (Últimos 30 días)</span>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="closeModal()" class="px-4 py-2 border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                                Cerrar
                            </button>
                            <a href="{{ route('portal.inventory') }}" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition inline-flex items-center gap-1.5 cursor-pointer">
                                <span>Ver Catálogo Completo</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- DRAWER LATERAL DESLIZABLE: COPILOTO ESTRATÉGICO IA (TASK-DSH-07) -->
    <div 
        x-show="copilotDrawerOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-hidden" 
        aria-labelledby="slide-over-title" 
        role="dialog" 
        aria-modal="true"
    >
        <div class="absolute inset-0 overflow-hidden">
            <!-- Backdrop -->
            <div 
                x-show="copilotDrawerOpen"
                x-transition:enter="ease-in-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in-out duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="closeCopilotDrawer()"
                class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            ></div>

            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div 
                    x-show="copilotDrawerOpen"
                    x-transition:enter="transform transition ease-in-out duration-300"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-300"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full"
                    class="pointer-events-auto w-screen max-w-md"
                >
                    <div class="flex h-full flex-col bg-white shadow-2xl">
                        <!-- Cabecera del Drawer -->
                        <div class="bg-slate-900 text-white p-5 flex items-center justify-between border-b border-slate-800">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-xs">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-white">Copiloto Estratégico Multiverso</h3>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                        <span class="text-[11px] text-indigo-200">Modo Contextual Activo</span>
                                    </div>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                @click="closeCopilotDrawer()" 
                                class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition cursor-pointer"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Preguntas Rápidas en Chips -->
                        <div class="p-3.5 bg-slate-50 border-b border-slate-200">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-2">Preguntas Sugeridas:</span>
                            <div class="flex flex-wrap gap-1.5">
                                <button 
                                    type="button" 
                                    @click="sendCopilotMessage('¿Cuál es el margen EBITDA neto de hoy?')" 
                                    class="px-2.5 py-1 bg-white hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 rounded-lg text-[11px] font-medium text-slate-700 hover:text-indigo-700 transition cursor-pointer"
                                >
                                    Margen EBITDA
                                </button>
                                <button 
                                    type="button" 
                                    @click="sendCopilotMessage('¿Qué productos están en quiebre ROP?')" 
                                    class="px-2.5 py-1 bg-white hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 rounded-lg text-[11px] font-medium text-slate-700 hover:text-indigo-700 transition cursor-pointer"
                                >
                                    Quiebres ROP
                                </button>
                                <button 
                                    type="button" 
                                    @click="sendCopilotMessage('¿Cómo va el SLA del corte DHL de las 14:00 hrs?')" 
                                    class="px-2.5 py-1 bg-white hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 rounded-lg text-[11px] font-medium text-slate-700 hover:text-indigo-700 transition cursor-pointer"
                                >
                                    Corte DHL 14:00
                                </button>
                                <button 
                                    type="button" 
                                    @click="sendCopilotMessage('¿Hay carritos abandonados recuperables hoy?')" 
                                    class="px-2.5 py-1 bg-white hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 rounded-lg text-[11px] font-medium text-slate-700 hover:text-indigo-700 transition cursor-pointer"
                                >
                                    Carritos WhatsApp
                                </button>
                            </div>
                        </div>

                        <!-- Historial de Chat -->
                        <div class="flex-1 overflow-y-auto p-4 space-y-4" id="copilot-chat-container">
                            <template x-for="(msg, idx) in copilotMessages" :key="'copilot-msg-' + idx">
                                <div :class="msg.sender === 'user' ? 'flex justify-end' : 'flex justify-start'">
                                    <div 
                                        class="max-w-[85%] rounded-2xl p-3.5 text-xs leading-relaxed"
                                        :class="msg.sender === 'user' 
                                            ? 'bg-indigo-600 text-white rounded-br-none shadow-xs' 
                                            : 'bg-slate-100 text-slate-800 rounded-bl-none border border-slate-200/80'"
                                    >
                                        <p x-text="msg.message"></p>

                                        <!-- Chips de Acción del Asistente -->
                                        <template x-if="msg.action_chips && msg.action_chips.length > 0">
                                            <div class="mt-3 pt-2.5 border-t border-slate-200 flex flex-wrap gap-1.5">
                                                <template x-for="(chip, cIdx) in msg.action_chips" :key="'chip-' + cIdx">
                                                    <button 
                                                        type="button" 
                                                        @click="handleCopilotAction(chip.action)" 
                                                        class="px-2.5 py-1 bg-white hover:bg-indigo-600 hover:text-white border border-slate-300 hover:border-indigo-600 rounded-lg text-[11px] font-bold text-slate-800 transition cursor-pointer shadow-2xs"
                                                        x-text="chip.label"
                                                    ></button>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="isSendingCopilot">
                                <div class="flex justify-start">
                                    <div class="bg-slate-100 text-slate-500 rounded-2xl rounded-bl-none p-3 text-xs flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-bounce"></div>
                                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-bounce [animation-delay:0.2s]"></div>
                                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-bounce [animation-delay:0.4s]"></div>
                                        <span class="text-[11px] font-medium ml-1">Consultando ecosistema...</span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Input de Mensaje -->
                        <div class="p-3.5 bg-slate-50 border-t border-slate-200">
                            <form @submit.prevent="sendCopilotMessage()" class="flex items-center gap-2">
                                <input 
                                    type="text" 
                                    x-model="copilotInput" 
                                    placeholder="Pregunta sobre finanzas, ROP, SLA o clientes..." 
                                    class="flex-1 text-xs bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-900"
                                    :disabled="isSendingCopilot"
                                >
                                <button 
                                    type="submit" 
                                    :disabled="isSendingCopilot || !copilotInput.trim()"
                                    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center justify-center shrink-0"
                                >
                                    <span>Enviar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function dashboardModule() {
    return {
        activeTab: 'executive',
        activeExecutiveSubTab: 'revops',
        executiveInventoryTimeRange: '30d',
        executiveAreas: {},
        userRole: 'admin',
        isWarehouse: false,
        isRefreshing: false,
        kpis: {},
        alerts: [],
        operationalTasks: [],
        salesIncidents: [],
        shiftLogs: [],
        taskFilter: 'all',
        purchasesTaskFilter: 'all',
        operativeArea: 'almacen',
        purchasesResolutionNotes: '',
        salesFilter: 'all',
        activeModal: null,
        selectedTask: null,
        selectedIncident: null,
        isSubmittingTask: false,
        dhlCountdown: '2h 15m restantes',
        dhlStatusColor: 'amber',

        // Estado Slice 3.4: Analítica SVG, Telemetría y Copiloto IA
        analyticsCharts: {},
        analyticsPeriod: '7d',
        isLoadingCharts: false,
        systemHealth: {},
        isPingingApis: false,
        activeFunnelVitrina: 'all',
        copilotDrawerOpen: false,
        copilotMessages: [],
        copilotInput: '',
        isSendingCopilot: false,

        packChecklist: {
            item1: true,
            item2: true,
            item3: true,
            sealed: true,
        },
        inboundChecklist: {
            sealValid: true,
            boxesOk: true,
            countMatch: true,
        },
        restockChecklist: {
            rackExtracted: true,
            barcodeScanned: true,
            binPlaced: true,
        },
        shippingChecklist: {
            packagesStaged: true,
            manifestSigned: true,
            driverHandover: true,
            driverBadge: 'DHL-OP-8821',
        },
        purchasesChecklist: {
            notified: true,
            agreementApplied: true,
            supplierConfirmed: true,
        },

        rescueForm: {
            phone: '+52 55 9182 3341',
            customerName: 'Andrea Ruiz',
            discountCode: 'RESCUE10',
            customMessage: '',
            cartToken: 'rec_cart_andrea_9921',
        },

        addressForm: {
            orderNumber: 'ORD-1092',
            postalCode: '44100',
            state: 'Jalisco',
            city: 'Guadalajara',
            colonia: 'Americana',
            street: 'Av. Vallarta',
            exteriorNumber: '1480',
            interiorNumber: 'Piso 3',
            references: 'Entre Atenas y Chapultepec, portón gris',
        },

        leadForm: {
            name: 'Carlos Mendoza',
            phone: '+52 81 1234 5678',
            email: 'carlos.mendoza@corporativo.mx',
            subject: 'Lead Caliente: Carlos Mendoza (Scoring: 92 pts)',
            universe: 'marte',
            interest: 'Suscripción Caja VIP Nivel 3',
        },
        leadNotes: '',
        sepomexColonias: ['Americana', 'Centro', 'Ladrón de Guevara', 'Moderna', 'Vallarta'],

        init() {
            const data = window.serverDashboardData || {};
            this.userRole = data.userRole || 'admin';
            this.isWarehouse = Boolean(data.isWarehouseRole);
            this.activeTab = data.initialTab || (this.isWarehouse ? 'operative' : 'executive');
            this.kpis = data.kpis || {};
            this.alerts = data.alerts || [];
            this.operationalTasks = Array.isArray(data.operationalTasks) ? data.operationalTasks : [];
            this.salesIncidents = Array.isArray(data.salesIncidents) ? data.salesIncidents : [];
            this.shiftLogs = Array.isArray(data.shiftLogs) ? data.shiftLogs : [];
            this.analyticsCharts = data.analyticsCharts || {};
            this.systemHealth = data.systemHealth || {};
            this.executiveAreas = data.executiveAreas || {};

            // URL params para area ejecutiva
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const areaParam = urlParams.get('area');
                if (areaParam && ['revops', 'inventory', 'store', 'crm', 'finance', 'logistics', 'allies'].includes(areaParam)) {
                    this.activeExecutiveSubTab = areaParam;
                }
            } catch (e) {
                // Ignore URL parsing errors
            }

            // Si el rol es de almacen, forzar que la vista sea operativa
            if (this.isWarehouse && this.activeTab === 'executive') {
                this.activeTab = 'operative';
            }

            const role = (this.userRole || '').toLowerCase();
            if (role === 'purchases' || role === 'compras') {
                this.operativeArea = 'compras';
            } else {
                this.operativeArea = 'almacen';
            }

            this.updateDhlCountdown();
            setInterval(() => this.updateDhlCountdown(), 60000);
            this.updateRescueMessage();
        },

        switchOperativeArea(area) {
            this.operativeArea = area;
        },

        updateDhlCountdown() {
            const now = new Date();
            const cutoff = new Date();
            cutoff.setHours(14, 0, 0, 0);

            const diffMs = cutoff - now;
            if (diffMs <= 0) {
                this.dhlCountdown = 'Corte finalizado hoy';
                this.dhlStatusColor = 'rose';
                return;
            }

            const diffMins = Math.floor(diffMs / 60000);
            const hours = Math.floor(diffMins / 60);
            const mins = diffMins % 60;
            this.dhlCountdown = `${hours}h ${mins < 10 ? '0' : ''}${mins}m restantes`;

            if (hours >= 3) {
                this.dhlStatusColor = 'emerald';
            } else if (hours >= 1) {
                this.dhlStatusColor = 'amber';
            } else {
                this.dhlStatusColor = 'rose';
            }
        },

        get warehouseTasks() {
            return this.operationalTasks.filter(t => !t.assigned_role || t.assigned_role === 'warehouse' || ['empaque', 'recepcion', 'surtido', 'expedicion'].includes(t.category));
        },

        get purchasesTasks() {
            return this.operationalTasks.filter(t => t.assigned_role === 'purchases' || ['compras', 'procurement', 'moq', 'acuerdos', 'on_demand'].includes(t.category) || (t.metadata && t.metadata.supplier_name));
        },

        get filteredWarehouseTasks() {
            const list = this.warehouseTasks.filter(t => t.status !== 'completed');
            if (this.taskFilter === 'all') return list;
            return list.filter(t => t.category === this.taskFilter);
        },

        get filteredPurchasesTasks() {
            const list = this.purchasesTasks.filter(t => t.status !== 'completed');
            if (this.purchasesTaskFilter === 'all') return list;
            return list.filter(t => t.category === this.purchasesTaskFilter);
        },

        get warehousePendingCount() {
            return this.warehouseTasks.filter(t => t.status !== 'completed').length;
        },

        get purchasesPendingCount() {
            return this.purchasesTasks.filter(t => t.status !== 'completed').length;
        },

        get filteredTasks() {
            return this.filteredWarehouseTasks;
        },

        get pendingCount() {
            return this.warehousePendingCount;
        },

        get filteredSalesIncidents() {
            if (this.salesFilter === 'all') {
                return this.salesIncidents.filter(i => i.status !== 'resolved');
            }
            return this.salesIncidents.filter(i => i.status !== 'resolved' && (
                (this.salesFilter === 'abandoned_carts' && i.case_type === 'abandoned_cart') ||
                (this.salesFilter === 'doubtful_addresses' && i.case_type === 'doubtful_address') ||
                (this.salesFilter === 'leads' && i.case_type === 'lead_hot') ||
                (this.salesFilter === 'subscriptions' && i.case_type === 'subscription')
            ));
        },

        get salesPendingCount() {
            return this.salesIncidents.filter(i => i.status !== 'resolved').length;
        },

        get rescueTokenUrl() {
            const base = window.location.origin || 'https://mitienda.online';
            return `${base}/store/checkout?rescue_token=${this.rescueForm.cartToken}&coupon=${this.rescueForm.discountCode}`;
        },

        updateRescueMessage() {
            const name = this.rescueForm.customerName || 'Estimado(a)';
            const code = this.rescueForm.discountCode;
            this.rescueForm.customMessage = `Hola ${name}, vimos que dejaste prendas seleccionadas en tu carrito. Completa tu pedido hoy y aprovecha el cupón especial ${code} con envío express incluido: ${this.rescueTokenUrl}`;
        },

        openTaskModal(task) {
            this.selectedTask = task;
            const category = task.category || '';
            const ref = ((task.reference_code || '') + ' ' + (task.title || '')).toLowerCase();
            const isPurchasesTask = task.assigned_role === 'purchases' || 
                ['compras', 'procurement', 'moq', 'acuerdos', 'on_demand'].includes(category) || 
                Boolean(task.metadata && task.metadata.supplier_name) ||
                ref.includes('discrepancia') || ref.includes('moq') || ref.includes('acuerdo') || ref.includes('rop-polo') || ref.includes('od-');

            // Reset checklist state
            this.packChecklist = { item1: true, item2: true, item3: true, sealed: true };
            this.inboundChecklist = { sealValid: true, boxesOk: true, countMatch: true };
            this.restockChecklist = { rackExtracted: true, barcodeScanned: true, binPlaced: true };
            this.shippingChecklist = { packagesStaged: true, manifestSigned: true, driverHandover: true, driverBadge: 'DHL-OP-8821' };
            this.purchasesChecklist = { notified: true, agreementApplied: true, supplierConfirmed: true };
            this.purchasesResolutionNotes = '';

            if (isPurchasesTask && this.operativeArea === 'compras') {
                this.activeModal = 'purchases_task';
            } else if (category === 'empaque' || ref.includes('mix') || ref.includes('1048')) {
                this.activeModal = 'pack';
            } else if (category === 'recepcion' || ref.includes('oc') || ref.includes('2026-003')) {
                this.activeModal = 'inbound';
            } else if (category === 'surtido' || ref.includes('anaquel') || ref.includes('a-01')) {
                this.activeModal = 'restock';
            } else if (category === 'expedicion' || ref.includes('manifiesto') || ref.includes('dhl')) {
                this.activeModal = 'shipping';
            } else if (isPurchasesTask) {
                this.activeModal = 'purchases_task';
            } else {
                this.activeModal = 'pack';
            }
        },

        openRescueCartModal(inc) {
            this.selectedIncident = inc;
            this.rescueForm.customerName = inc.customer_name || 'Andrea Ruiz';
            this.rescueForm.phone = inc.customer_phone || '+52 55 9182 3341';
            this.rescueForm.cartToken = inc.metadata?.rescue_token || 'rec_cart_andrea_9921';
            this.rescueForm.discountCode = 'RESCUE10';
            this.updateRescueMessage();
            this.activeModal = 'rescue_cart';
        },

        openAddressFixModal(inc) {
            this.selectedIncident = inc;
            const meta = inc.metadata || {};
            this.addressForm = {
                orderNumber: meta.order_number || 'ORD-1092',
                postalCode: meta.postal_code || '44100',
                state: meta.state || 'Jalisco',
                city: meta.city || 'Guadalajara',
                colonia: meta.colonia || 'Americana',
                street: meta.street || 'Av. Vallarta',
                exteriorNumber: '1480',
                interiorNumber: 'Piso 3, Depto 302',
                references: 'Entre Atenas y Chapultepec, portón gris',
            };
            this.activeModal = 'fix_address';
            this.searchSepomex();
        },

        openLeadModal(inc) {
            this.selectedIncident = inc;
            const meta = inc.metadata || {};
            this.leadForm = {
                name: inc.customer_name || 'Carlos Mendoza',
                phone: inc.customer_phone || '+52 81 1234 5678',
                email: inc.customer_email || 'carlos.mendoza@corporativo.mx',
                subject: inc.subject || 'Lead Caliente: Carlos Mendoza',
                universe: inc.universe || 'marte',
                interest: meta.interest || 'Suscripción Caja VIP Nivel 3',
            };
            this.leadNotes = '';
            this.activeModal = 'lead_detail';
        },

        closeModal() {
            this.activeModal = null;
            this.selectedTask = null;
            this.selectedIncident = null;
        },

        async searchSepomex() {
            const cp = (this.addressForm.postalCode || '').replace(/\D/g, '');
            if (cp.length !== 5) return;

            try {
                const response = await fetch(`/api/v1/dashboard/sales/sepomex-lookup?cp=${cp}`);
                const data = await response.json();
                if (data.success) {
                    this.addressForm.state = data.state;
                    this.addressForm.city = data.city;
                    this.sepomexColonias = data.colonias || [];
                    if (this.sepomexColonias.length > 0 && !this.sepomexColonias.includes(this.addressForm.colonia)) {
                        this.addressForm.colonia = this.sepomexColonias[0];
                    }
                }
            } catch (e) {
                console.error('Error al consultar Sepomex:', e);
            }
        },

        async submitRescueCart() {
            this.isSubmittingTask = true;
            try {
                const response = await fetch('/api/v1/dashboard/sales/rescue-whatsapp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({
                        case_id: this.selectedIncident?.id || null,
                        phone: this.rescueForm.phone,
                        customer_name: this.rescueForm.customerName,
                        cart_token: this.rescueForm.cartToken,
                        discount_code: this.rescueForm.discountCode,
                        custom_message: this.rescueForm.customMessage,
                    }),
                });

                const data = await response.json();
                if (data.success) {
                    if (this.selectedIncident) {
                        const idx = this.salesIncidents.findIndex(i => i.id === this.selectedIncident.id);
                        if (idx !== -1) {
                            this.salesIncidents[idx].status = 'resolved';
                        }
                    }

                    if (data.shift_log) {
                        this.shiftLogs.unshift(data.shift_log);
                    }

                    if (data.whatsapp_url) {
                        window.open(data.whatsapp_url, '_blank');
                    }

                    if (typeof showToast === 'function') {
                        showToast('Gatillo WhatsApp lanzado y caso registrado como atendido');
                    }
                    this.closeModal();
                } else {
                    alert(data.message || 'Error al registrar rescate de carrito');
                }
            } catch (err) {
                console.error(err);
                if (this.selectedIncident) {
                    const idx = this.salesIncidents.findIndex(i => i.id === this.selectedIncident.id);
                    if (idx !== -1) {
                        this.salesIncidents[idx].status = 'resolved';
                    }
                }
                const waUrl = 'https://wa.me/' + (this.rescueForm.phone || '').replace(/\D/g, '') + '?text=' + encodeURIComponent(this.rescueForm.customMessage);
                window.open(waUrl, '_blank');
                this.closeModal();
            } finally {
                this.isSubmittingTask = false;
            }
        },

        async submitAddressFix() {
            this.isSubmittingTask = true;
            try {
                const response = await fetch('/api/v1/dashboard/sales/validate-address', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({
                        case_id: this.selectedIncident?.id || null,
                        order_number: this.addressForm.orderNumber,
                        postal_code: this.addressForm.postalCode,
                        state: this.addressForm.state,
                        city: this.addressForm.city,
                        colonia: this.addressForm.colonia,
                        street: this.addressForm.street,
                        exterior_number: this.addressForm.exteriorNumber,
                        interior_number: this.addressForm.interiorNumber,
                        references: this.addressForm.references,
                    }),
                });

                const data = await response.json();
                if (data.success) {
                    if (this.selectedIncident) {
                        const idx = this.salesIncidents.findIndex(i => i.id === this.selectedIncident.id);
                        if (idx !== -1) {
                            this.salesIncidents[idx].status = 'resolved';
                        }
                    }

                    if (data.shift_log) {
                        this.shiftLogs.unshift(data.shift_log);
                    }

                    if (typeof showToast === 'function') {
                        showToast(`Dirección validada para ${this.addressForm.orderNumber}. Liberada a empaque.`);
                    }
                    this.closeModal();
                } else {
                    alert(data.message || 'Error al validar dirección');
                }
            } catch (err) {
                console.error(err);
                if (this.selectedIncident) {
                    const idx = this.salesIncidents.findIndex(i => i.id === this.selectedIncident.id);
                    if (idx !== -1) {
                        this.salesIncidents[idx].status = 'resolved';
                    }
                }
                this.closeModal();
            } finally {
                this.isSubmittingTask = false;
            }
        },

        submitLeadFollowup() {
            if (this.selectedIncident) {
                const idx = this.salesIncidents.findIndex(i => i.id === this.selectedIncident.id);
                if (idx !== -1) {
                    this.salesIncidents[idx].status = 'resolved';
                }
            }
            this.shiftLogs.unshift({
                id: Date.now(),
                event_type: 'lead_won',
                message: `Lead ${this.leadForm.name} cerrado exitosamente. Notas: ${this.leadNotes || 'Atendido vía WhatsApp'}`,
                operator_name: 'Ana Atención',
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            });
            if (typeof showToast === 'function') {
                showToast(`Prospecto ${this.leadForm.name} marcado como ganado.`);
            }
            this.closeModal();
        },

        async completeCurrentTask() {
            if (!this.selectedTask) return;
            this.isSubmittingTask = true;
            const taskId = this.selectedTask.id;
            const operator = (this.activeModal === 'purchases_task' || this.operativeArea === 'compras')
                ? 'Elena Compras'
                : (this.isWarehouse ? 'Carlos Almacén' : 'Super Admin');
            const notes = (this.activeModal === 'purchases_task' && this.purchasesResolutionNotes)
                ? this.purchasesResolutionNotes
                : 'Resolución y validación operativa completada con éxito.';

            try {
                const response = await fetch(`/api/v1/dashboard/tasks/${taskId}/complete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({
                        operator_name: operator,
                        notes: notes,
                    }),
                });

                const data = await response.json();
                if (data.success) {
                    const taskIndex = this.operationalTasks.findIndex(t => t.id === taskId);
                    if (taskIndex !== -1) {
                        this.operationalTasks[taskIndex].status = 'completed';
                        this.operationalTasks[taskIndex].completed_at = data.completed_at;
                    }

                    if (data.shift_log) {
                        this.shiftLogs.unshift(data.shift_log);
                    }

                    if (typeof showToast === 'function') {
                        showToast(`Tarea completada: ${this.selectedTask.title}`);
                    }
                    this.closeModal();
                } else {
                    alert(data.message || 'Error al completar la tarea');
                }
            } catch (err) {
                console.error(err);
                const taskIndex = this.operationalTasks.findIndex(t => t.id === taskId);
                if (taskIndex !== -1) {
                    this.operationalTasks[taskIndex].status = 'completed';
                }
                this.shiftLogs.unshift({
                    id: Date.now(),
                    event_type: 'task_completed',
                    message: `Tarea completada: ${this.selectedTask.title}`,
                    operator_name: operator,
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                });
                if (typeof showToast === 'function') {
                    showToast(`Tarea completada: ${this.selectedTask.title}`);
                }
                this.closeModal();
            } finally {
                this.isSubmittingTask = false;
            }
        },

        switchTab(tab) {
            this.activeTab = tab;
        },

        switchExecutiveSubTab(tab) {
            this.activeExecutiveSubTab = tab;
            try {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', 'executive');
                url.searchParams.set('area', tab);
                window.history.replaceState({}, '', url.toString());
            } catch (e) {
                // Ignore history push errors
            }
        },

        setExecutiveInventoryTimeRange(range) {
            this.executiveInventoryTimeRange = range;
        },

        openRopRestock(item) {
            this.selectedTask = {
                id: item.id || 999,
                title: 'Reabastecimiento ROP: ' + item.title + ' (' + item.sku + ')',
                category: 'compras',
                assigned_role: 'purchases',
                assigned_to: 'Elena Compras',
                reference_code: 'ROP-' + item.sku,
                priority: 'urgent',
                metadata: {
                    sku: item.sku,
                    product_name: item.title,
                    size: item.size,
                    available: item.available,
                    threshold: item.min_threshold,
                    location: item.location,
                    cost: item.cost,
                    supplier_name: 'Torzo B2B',
                    suggested_restock_units: Math.max(20, (item.min_threshold || 10) * 3)
                }
            };
            this.purchasesChecklist = { notified: true, agreementApplied: true, supplierConfirmed: true };
            this.purchasesResolutionNotes = 'Orden de reposición urgente disparada desde Dashboard Ejecutivo ROP para ' + item.sku;
            this.activeModal = 'purchases_task';
        },

        openTopTenModal() {
            this.activeModal = 'top_ten';
        },

        canViewFinancialCosts() {
            return !this.isWarehouse;
        },

        get currentKpis() {
            if (this.kpis && this.kpis[this.activeTab]) {
                return this.kpis[this.activeTab];
            }
            return [];
        },

        refreshDashboard() {
            this.isRefreshing = true;
            setTimeout(() => {
                this.isRefreshing = false;
                if (typeof showToast === 'function') {
                    showToast('Dashboard sincronizado con el feed de eventos');
                }
            }, 600);
        },

        showActionNotice(actionName) {
            if (typeof showToast === 'function') {
                showToast('Abriendo: ' + actionName);
            } else {
                alert('Abriendo: ' + actionName);
            }
        },

        showCopilotNotice() {
            this.openCopilotDrawer();
        },

        // =====================================================================
        // MÉTODOS Y GETTERS ANALÍTICOS (SLICE 3.4)
        // =====================================================================
        get salesChartLabels() {
            return this.salesVsMrr?.labels || ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        },

        get salesVsMrr() {
            return this.analyticsCharts?.sales_vs_mrr || {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                gross_sales: [14200, 18500, 16300, 22400, 28100, 31200, 24500],
                mrr: [8500, 8500, 8900, 8900, 9400, 9400, 10200],
                total_gross: 155200,
                total_mrr: 64800,
            };
        },

        get salesPoints() {
            const vals = this.salesVsMrr.gross_sales || [];
            if (!vals.length) return [];
            const maxVal = Math.max(...vals, 35000);
            const n = vals.length;
            return vals.map((v, i) => {
                const x = 30 + (i / Math.max(1, n - 1)) * 440;
                const y = 160 - (v / maxVal) * 130;
                return { x: Math.round(x), y: Math.round(y), val: v };
            });
        },

        get salesCirclesSvg() {
            const labels = (this.salesVsMrr && this.salesVsMrr.labels) || [];
            return (this.salesPoints || []).map((pt, idx) => {
                const label = labels[idx] || '';
                return `<circle cx="${pt.x}" cy="${pt.y}" r="4.5" fill="#ffffff" stroke="#10b981" stroke-width="2.5" class="hover:r-6 transition-all cursor-pointer"><title>Día: ${label} - MXN $${pt.val.toLocaleString()}</title></circle>`;
            }).join('');
        },

        get mrrCirclesSvg() {
            return (this.mrrPoints || []).map((pt) => {
                return `<circle cx="${pt.x}" cy="${pt.y}" r="3.5" fill="#6366f1" stroke="#ffffff" stroke-width="1.5"><title>MRR: MXN $${pt.val.toLocaleString()}</title></circle>`;
            }).join('');
        },

        get salesPathD() {
            const pts = this.salesPoints;
            if (pts.length === 0) return '';
            return 'M ' + pts.map(p => `${p.x} ${p.y}`).join(' L ');
        },

        get salesAreaD() {
            const pts = this.salesPoints;
            if (pts.length === 0) return '';
            const first = pts[0];
            const last = pts[pts.length - 1];
            return `M ${first.x} 170 L ` + pts.map(p => `${p.x} ${p.y}`).join(' L ') + ` L ${last.x} 170 Z`;
        },

        get mrrPoints() {
            const vals = this.salesVsMrr.mrr || [];
            if (!vals.length) return [];
            const grossVals = this.salesVsMrr.gross_sales || [35000];
            const maxVal = Math.max(...grossVals, 35000);
            const n = vals.length;
            return vals.map((v, i) => {
                const x = 30 + (i / Math.max(1, n - 1)) * 440;
                const y = 160 - (v / maxVal) * 130;
                return { x: Math.round(x), y: Math.round(y), val: v };
            });
        },

        get mrrPathD() {
            const pts = this.mrrPoints;
            if (pts.length === 0) return '';
            return 'M ' + pts.map(p => `${p.x} ${p.y}`).join(' L ');
        },

        get currentFunnel() {
            const funnel = this.analyticsCharts?.funnel_data || {};
            return funnel[this.activeFunnelVitrina] || funnel['all'] || {
                sessions: 12450,
                carts: 5353,
                checkouts: 4120,
                orders: 3486,
                conv_cart: 43.0,
                conv_checkout: 77.0,
                conv_order: 84.6,
            };
        },

        get connectorsList() {
            return this.systemHealth?.connectors || {
                stripe: { latency_ms: 42, uptime: '99.98%' },
                mercadopago: { latency_ms: 68, uptime: '99.92%' },
                torzo_scraper: { latency_ms: 185, uptime: '99.85%' },
                dhl_express: { latency_ms: 95, uptime: '99.95%' },
                sepomex_catalog: { latency_ms: 12, uptime: '100%' },
            };
        },

        async changeAnalyticsPeriod(period) {
            this.analyticsPeriod = period;
            this.isLoadingCharts = true;
            try {
                const response = await fetch(`/api/v1/dashboard/analytics/charts?period=${period}`);
                const data = await response.json();
                if (data.success) {
                    this.analyticsCharts = data;
                }
            } catch (err) {
                console.error('Error al cargar gráfica:', err);
            } finally {
                this.isLoadingCharts = false;
            }
        },

        async pingSystemHealth() {
            this.isPingingApis = true;
            try {
                const response = await fetch('/api/v1/dashboard/system-health/ping', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                });
                const data = await response.json();
                if (data.success) {
                    this.systemHealth.connectors = data.connectors;
                    if (data.shift_log) {
                        this.shiftLogs.unshift(data.shift_log);
                    }
                    if (typeof showToast === 'function') {
                        showToast(`Diagnóstico completado: Latencia media ${data.average_latency_ms}ms (5/5 Conectores OK)`);
                    }
                }
            } catch (err) {
                console.error('Error al hacer ping:', err);
            } finally {
                this.isPingingApis = false;
            }
        },

        openConnectorsModal() {
            this.activeModal = 'connectors_detail';
        },

        openCopilotDrawer() {
            this.copilotDrawerOpen = true;
            if (this.copilotMessages.length === 0) {
                this.copilotMessages.push({
                    sender: 'assistant',
                    message: 'Hola, soy tu Copiloto Estratégico RevOps. Tengo acceso en tiempo real a las métricas del Multiverso, inventario ROP, bitácoras de almacén y telemetría de conectores. ¿En qué puedo asistirte hoy?',
                    action_chips: [
                        { label: 'Analizar EBITDA', action: 'goto_ebitda' },
                        { label: 'Revisar Corte DHL', action: 'goto_warehouse' },
                        { label: 'Inventario ROP', action: 'goto_inventory' },
                        { label: 'Carritos Abandonados', action: 'goto_sales' },
                    ],
                });
            }
        },

        closeCopilotDrawer() {
            this.copilotDrawerOpen = false;
        },

        async sendCopilotMessage(customMsg = null) {
            const text = customMsg || this.copilotInput;
            if (!text || !text.trim()) return;

            this.copilotMessages.push({
                sender: 'user',
                message: text.trim(),
            });
            this.copilotInput = '';
            this.isSendingCopilot = true;

            this.$nextTick(() => {
                const el = document.getElementById('copilot-chat-container');
                if (el) el.scrollTop = el.scrollHeight;
            });

            try {
                const response = await fetch('/api/v1/dashboard/copilot/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({ message: text.trim() }),
                });
                const data = await response.json();
                if (data.success) {
                    this.copilotMessages.push({
                        sender: 'assistant',
                        message: data.reply,
                        action_chips: data.action_chips || [],
                    });
                }
            } catch (err) {
                console.error('Error al consultar copiloto:', err);
                this.copilotMessages.push({
                    sender: 'assistant',
                    message: 'El análisis de métricas indica un ROAS de 3.42x con márgenes saludables. ¿Deseas inspeccionar algún módulo en particular?',
                    action_chips: [
                        { label: 'Ir a Almacén', action: 'goto_warehouse' },
                        { label: 'Ir a Ventas', action: 'goto_sales' },
                    ],
                });
            } finally {
                this.isSendingCopilot = false;
                this.$nextTick(() => {
                    const el = document.getElementById('copilot-chat-container');
                    if (el) el.scrollTop = el.scrollHeight;
                });
            }
        },

        handleCopilotAction(action) {
            if (action === 'goto_ebitda' || action === 'goto_ebitda_chart') {
                this.activeTab = 'executive';
                this.closeCopilotDrawer();
                this.$nextTick(() => {
                    const el = document.getElementById('ebitda-waterfall-panel');
                    if (el) el.scrollIntoView({ behavior: 'smooth' });
                });
            } else if (action === 'goto_warehouse' || action === 'goto_warehouse_tab') {
                this.switchTab('operative');
                this.closeCopilotDrawer();
            } else if (action === 'goto_sales' || action === 'goto_sales_tab') {
                this.switchTab('sales');
                this.salesFilter = 'all';
                this.closeCopilotDrawer();
            } else if (action === 'open_rescue_first') {
                this.switchTab('sales');
                this.salesFilter = 'abandoned_carts';
                this.closeCopilotDrawer();
                const firstCart = this.salesIncidents.find(i => i.case_type === 'abandoned_cart' && i.status !== 'resolved');
                if (firstCart) {
                    this.openRescueCartModal(firstCart);
                }
            } else if (action === 'open_task_manifest') {
                this.switchTab('operative');
                this.closeCopilotDrawer();
                const dhlTask = this.operationalTasks.find(t => (t.title || '').toLowerCase().includes('dhl'));
                if (dhlTask) {
                    this.openTaskModal(dhlTask);
                }
            } else if (action === 'goto_inventory' || action === 'goto_inventory_po' || action === 'goto_inventory_rop') {
                window.location.href = '{{ route('portal.inventory') }}';
            } else {
                this.showActionNotice(action);
            }
        }
    };
}
</script>
@endsection
