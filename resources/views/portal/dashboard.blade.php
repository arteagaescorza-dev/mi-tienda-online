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
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700 font-semibold">4 Pendientes</span>
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
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-amber-100 text-amber-800 font-bold">3 Casos</span>
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
        <template x-if="activeTab === 'executive'">
            <template x-if="canViewFinancialCosts()">
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
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- COLUMNA IZQUIERDA: ACTION CENTER & EMBUDO (2 COLS) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- PANEL: BANDEJA DE ACCIÓN EJECUTIVA -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">Action Center: Tareas Prioritarias del Día</h3>
                                    <p class="text-xs text-slate-500">Acciones inmediatas con resolución en 1 clic</p>
                                </div>
                            </div>
                            <button class="section-help-btn" type="button" aria-label="Ayuda de Action Center" data-help-title="Action Center Operativo" data-help-desc="Cola de trabajo inteligente que prioriza envíos con vencimiento de SLA, pedidos mixtos desbloqueados y discrepancias de recepción.">?</button>
                        </div>

                        <div class="space-y-3">
                            <!-- Tarea 1: Empaque Pedido Mixto -->
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

                            <!-- Tarea 2: Alerta ROP Crítica -->
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

                            <!-- Tarea 3: Carrito Abandonado de Alto Valor -->
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
                                <button type="button" @click="switchTab('sales')" class="px-3 py-1.5 text-xs font-bold bg-white hover:bg-slate-50 border border-slate-300 rounded-lg text-slate-800 transition cursor-pointer shrink-0">
                                    Gestionar en Ventas
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- PANEL: EMBUDO COMERCIAL MULTIVERSO -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">Embudo Comercial Multiverso</h3>
                                    <p class="text-xs text-slate-500">Conversión en vivo a través de vitrinas Terra, Marte y Venus</p>
                                </div>
                            </div>
                            <button class="section-help-btn" type="button" aria-label="Ayuda del Embudo" data-help-title="Embudo Multiverso" data-help-desc="Telemetria en tiempo real del ciclo de vida del cliente: Sesiones -> Carritos -> Checkouts -> Compras D2C & Suscripciones.">?</button>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60 text-center">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Sesiones Hoy</span>
                                <span class="text-base font-black font-mono text-slate-800">12,450</span>
                            </div>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60 text-center">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Carritos Creados</span>
                                <span class="text-base font-black font-mono text-indigo-600">5,353</span>
                            </div>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60 text-center">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Checkouts</span>
                                <span class="text-base font-black font-mono text-slate-800">4,120</span>
                            </div>
                            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200/60 text-center">
                                <span class="text-[10px] uppercase font-bold text-emerald-800 block">Órdenes Pagadas</span>
                                <span class="text-base font-black font-mono text-emerald-700">3,486</span>
                            </div>
                        </div>

                        <!-- Barras visuales de conversión -->
                        <div class="space-y-3">
                            <div>
                                <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                                    <span>Visitas a Carrito</span>
                                    <span class="font-mono">43.0%</span>
                                </div>
                                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-500 rounded-full" style="width: 43%;"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                                    <span>Carrito a Checkout</span>
                                    <span class="font-mono">77.0%</span>
                                </div>
                                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-600 rounded-full" style="width: 77%;"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                                    <span>Checkout a Pago Exitoso</span>
                                    <span class="font-mono">84.6%</span>
                                </div>
                                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full" style="width: 84.6%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: TELEMETRÍA, ALERTAS Y COPILOTO (1 COL) -->
                <div class="space-y-6">
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

                    <!-- PANEL: TELEMETRÍA DE CONECTORES Y APIS -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Salud del Ecosistema</span>
                            </h3>
                            <button class="section-help-btn" type="button" aria-label="Ayuda de Salud del Ecosistema" data-help-title="Monitor de APIs" data-help-desc="Monitoreo de latencia y estado operacional de pasarelas (Stripe), scrapers (Torzo) y paqueterias (DHL / Skydropx).">?</button>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200/60">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="font-semibold text-slate-800">Stripe Payments API</span>
                                </div>
                                <span class="text-emerald-700 font-mono font-bold text-[11px]">99.98% OK (42ms)</span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200/60">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="font-semibold text-slate-800">Torzo Bot Scraper</span>
                                </div>
                                <span class="text-emerald-700 font-mono font-bold text-[11px]">Sincronizado</span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200/60">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="font-semibold text-slate-800">DHL Express & Sepomex</span>
                                </div>
                                <span class="text-emerald-700 font-mono font-bold text-[11px]">Operativo</span>
                            </div>
                        </div>
                    </div>

                    <!-- PANEL: GESTIÓN RÁPIDA CON COPILOTO IA -->
                    <div class="bg-gradient-to-br from-indigo-900 to-slate-900 text-white rounded-2xl p-6 shadow-md">
                        <div class="flex items-center gap-2.5 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-300 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-white">Copiloto Estratégico IA</h4>
                                <span class="text-[10px] text-indigo-300 font-medium">Asistente Operativo Contextual</span>
                            </div>
                        </div>
                        <p class="text-xs text-indigo-100 leading-relaxed mb-4">
                            Optimiza la asignación de pedidos, detecta quiebres ROP y redacta minutas de discrepancia comercial en segundos.
                        </p>
                        <button 
                            type="button" 
                            @click="showCopilotNotice()" 
                            class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer flex items-center justify-center gap-2"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            <span>Abrir Asistente Copiloto</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- ------------------------------------------------------------------------- --}}
    {{-- SUBMODULO 2: BANDEJA OPERATIVA (ALMACEN & EMPAQUE - CERO DINERO)          --}}
    {{-- ------------------------------------------------------------------------- --}}
    <div x-show="activeTab === 'operative'" class="space-y-6">
        <!-- Banner de Modo Almacen & Empaque -->
        <div class="p-3.5 bg-indigo-50/80 border border-indigo-200/80 rounded-xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-indigo-600 animate-pulse"></span>
                <span class="text-xs font-bold text-indigo-950 uppercase tracking-wider">Modo Almacén & Empaque Físico (Carlos Almacén)</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda de Modo Almacen" data-help-title="Bandeja Operativa" data-help-desc="Vista especializada para personal de bodega y empaque. Enfoque exclusivo en recoleccion DHL de las 14:00 hrs y recepcion en rampa. Cero visibilidad de finanzas.">?</button>
            </div>
            <span class="text-[11px] text-indigo-800 font-medium">Prioridad de surtido por pasillo y recepciones en rampa</span>
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
                        <span class="text-xs font-bold text-indigo-600 font-mono bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-100">4 Tareas</span>
                    </div>

                    <div class="space-y-3">
                        <div class="p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-rose-500 mt-1.5 shrink-0"></div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-900">Empaque: Pedido #MIX-1048</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Corte DHL Inminente</span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-0.5">3 prendas en Pasillo A-02 y Rack R-08. Requiere bolsa de lavado de cortesía.</p>
                                </div>
                            </div>
                            <button type="button" @click="showActionNotice('Mesa de Picking & Packing #MIX-1048')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer shrink-0">
                                Iniciar Empaque &rarr;
                            </button>
                        </div>

                        <div class="p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-900">Recepción Inbound: Torzo B2B #OC-2026-003</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Camión en Rampa</span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-0.5">Lote mayorista con 150 piezas textiles. Cotejo físico y verificación de precinto.</p>
                                </div>
                            </div>
                            <button type="button" @click="showActionNotice('Recepción en Rampa #OC-2026-003')" class="px-3 py-1.5 text-xs font-bold bg-white hover:bg-slate-50 border border-slate-300 rounded-lg text-slate-800 transition cursor-pointer shrink-0">
                                Recibir en Rampa
                            </button>
                        </div>

                        <div class="p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-900">Surtido Interno ROP: Anaquel A-01</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Reabastecer Rack</span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-0.5">Mover 15 piezas de Reserva Rack R-04 hacia Anaquel A-01 para surtido rápido.</p>
                                </div>
                            </div>
                            <button type="button" @click="showActionNotice('Surtido Interno Anaquel A-01')" class="px-3 py-1.5 text-xs font-bold bg-white hover:bg-slate-50 border border-slate-300 rounded-lg text-slate-800 transition cursor-pointer shrink-0">
                                Confirmar Surtido
                            </button>
                        </div>
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
                        <li class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Recepción física completada: <strong>#OC-2026-002</strong> (50 piezas)</span>
                            </div>
                            <span class="text-slate-400 font-mono">10:42 AM &bull; Carlos A.</span>
                        </li>
                        <li class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                <span>Manifiesto DHL generado con 12 guías impresas</span>
                            </div>
                            <span class="text-slate-400 font-mono">09:15 AM &bull; Carlos A.</span>
                        </li>
                        <li class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                <span>Apertura de turno de almacén y calibración de báscula</span>
                            </div>
                            <span class="text-slate-400 font-mono">08:00 AM &bull; Sistema</span>
                        </li>
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
                        <span class="text-[11px] font-bold text-rose-800 block mt-1">Horario límite de recolección</span>
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
                        <span class="text-xs font-bold text-blue-600 font-mono bg-blue-50 px-2.5 py-1 rounded-full border border-blue-100">3 Pendientes</span>
                    </div>

                    <div class="space-y-3">
                        <div class="p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-900">Carrito Abandonado: Andrea Ruiz</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Alta Urgencia</span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-0.5">3 prendas en carrito (MXN $1,450.00). Abandonado hace 25 min en paso de pago.</p>
                                </div>
                            </div>
                            <button type="button" @click="showActionNotice('Recuperación WhatsApp con Enlace Tokenizado')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer shrink-0 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>Recuperar WhatsApp</span>
                            </button>
                        </div>

                        <div class="p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-rose-500 mt-1.5 shrink-0"></div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-900">Dirección Incompleta: Orden #ORD-1092</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Falta Número Exterior</span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-0.5">Cliente: Marco Valdés (Guadalajara, CP 44100). Validar con Sepomex antes de expedir.</p>
                                </div>
                            </div>
                            <button type="button" @click="showActionNotice('Validador Predictivo Sepomex')" class="px-3 py-1.5 text-xs font-bold bg-white hover:bg-slate-50 border border-slate-300 rounded-lg text-slate-800 transition cursor-pointer shrink-0">
                                Corregir Dirección
                            </button>
                        </div>

                        <div class="p-4 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-indigo-500 mt-1.5 shrink-0"></div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-900">Lead Caliente: Carlos Mendoza</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">Scoring: 92 pts</span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-0.5">Preguntó por suscripción Caja VIP Nivel 3. Requiere confirmación de tallas.</p>
                                </div>
                            </div>
                            <a href="{{ route('leads.index') }}" class="px-3 py-1.5 text-xs font-bold bg-white hover:bg-slate-50 border border-slate-300 rounded-lg text-slate-800 transition shrink-0" style="text-decoration:none;">
                                Abrir Ficha CRM
                            </a>
                        </div>
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
</div>

<script>
function dashboardModule() {
    return {
        activeTab: 'executive',
        userRole: 'admin',
        isWarehouse: false,
        isRefreshing: false,
        kpis: {},
        alerts: [],

        init() {
            const data = window.serverDashboardData || {};
            this.userRole = data.userRole || 'admin';
            this.isWarehouse = Boolean(data.isWarehouseRole);
            this.activeTab = data.initialTab || (this.isWarehouse ? 'operative' : 'executive');
            this.kpis = data.kpis || {};
            this.alerts = data.alerts || [];

            // Si el rol es de almacen, forzar que la vista sea operativa
            if (this.isWarehouse && this.activeTab === 'executive') {
                this.activeTab = 'operative';
            }
        },

        switchTab(tab) {
            this.activeTab = tab;
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
            if (typeof showToast === 'function') {
                showToast('Abriendo Asistente Copiloto IA...');
            } else {
                alert('Abriendo Asistente Copiloto IA...');
            }
        }
    };
}
</script>
@endsection
