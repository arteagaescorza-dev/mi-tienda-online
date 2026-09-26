@extends('layouts.portal-panel')

@section('title', 'Dashboard General')
@section('hub_kicker', 'Hub 1')
@section('hub_title', 'Dashboard General')
@section('hub_status', 'Vista Ejecutiva')

@section('content')
<div id="dashboardAppRoot" class="w-full text-slate-800 font-sans antialiased" data-screen="dashboard">
    {{-- ========================================================================= --}}
    {{-- REGLA 10: BARRA DE PESTAÑAS DE SUBMÓDULO PRIMERO (SUBNAV BAR)             --}}
    {{-- ========================================================================= --}}
    <div class="mb-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <nav class="flex gap-2 -mb-px overflow-x-auto" aria-label="Submódulos del Dashboard">
            <!-- TAB 1: DASHBOARD EJECUTIVO -->
            <button 
                type="button"
                data-dashboard-tab="executive"
                class="py-3 px-4 border-b-2 border-indigo-600 text-indigo-600 font-semibold bg-white shadow-sm rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Dashboard Ejecutivo</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 font-bold">Finanzas</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Dashboard Ejecutivo" data-help-title="Dashboard Ejecutivo" data-help-desc="Vista gerencial con KPIs consolidados de ventas, embudo multiverso, ingresos recurrentes (MRR) y analítica de conversión.">?</button>
            </button>

            <!-- TAB 2: BANDEJA OPERATIVA -->
            <button 
                type="button"
                data-dashboard-tab="operative"
                class="py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Bandeja Operativa</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700 font-semibold">4 Pendientes</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Bandeja Operativa" data-help-title="Bandeja Operativa" data-help-desc="Action Center diario para personal de almacén, empaque y compras con tareas accionables en 1 clic.">?</button>
            </button>

            <!-- TAB 3: ATENCIÓN & VENTAS -->
            <button 
                type="button"
                data-dashboard-tab="sales"
                class="py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                <span>Atención & Ventas</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-amber-100 text-amber-800 font-bold">3 Casos</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Atención y Ventas" data-help-title="Atención & Ventas" data-help-desc="Gestión comercial de leads calientes, carritos abandonados recuperables y contacto directo vía WhatsApp.">?</button>
            </button>
        </nav>

        <div class="flex items-center gap-4 text-xs pb-2 sm:pb-0">
            <span class="flex items-center gap-1.5 font-medium text-slate-600 bg-white px-2.5 py-1 rounded-full border border-slate-200 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Feed RevOps: En Vivo</span>
            </span>
            <button class="text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1.5 cursor-pointer transition-colors" data-action="refresh-dashboard">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Actualizar</span>
            </button>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- REGLA 10: SECCIÓN 2: TARJETAS DE KPIS CONTEXTUALES                        --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @if(isset($kpis))
            @foreach($kpis as $index => $kpi)
                <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $kpi['label'] }}</span>
                        <button class="section-help-btn" type="button" aria-label="Ayuda de {{ $kpi['label'] }}" data-help-title="{{ $kpi['label'] }}" data-help-desc="Métrica calculada en tiempo real por el motor de ventas y checkout de la plataforma.">?</button>
                    </div>
                    <div class="mt-3 flex items-baseline justify-between">
                        <span class="text-2xl font-black font-mono tracking-tight text-slate-900">{{ $kpi['value'] }}</span>
                        @if($index === 0)
                            <span class="text-xs font-bold text-emerald-600 font-mono">+18.4%</span>
                        @elseif($index === 2)
                            <span class="text-xs font-bold text-rose-600 font-mono">Alta Urgencia</span>
                        @else
                            <span class="text-xs font-bold text-indigo-600 font-mono">Hoy</span>
                        @endif
                    </div>
                    <div class="mt-2 text-[11px] text-slate-400">
                        @if($index === 0)
                            Consolidado de vitrinas D2C
                        @elseif($index === 1)
                            Prospectos en embudo comercial
                        @elseif($index === 2)
                            Recuperables vía WhatsApp
                        @else
                            Promedio por orden finalizada
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- REGLA 10: SECCIÓN 3: ÁREA OPERATIVA PRINCIPAL & EMBUDOS                  --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- COLUMNA IZQUIERDA: ACTION CENTER & TAREAS OPERATIVAS (2 COLS) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- PANEL: BANDEJA DE ACCIÓN OPERATIVA -->
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
                        <a href="{{ route('portal.inventory') }}" class="button button-light text-xs font-bold px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-white shrink-0 text-center" style="text-decoration:none;">
                            Empacar Ahora
                        </a>
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
                        <a href="{{ route('portal.inventory') }}" class="button button-light text-xs font-bold px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-white shrink-0 text-center" style="text-decoration:none;">
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
                        <a href="{{ route('leads.index') }}" class="button button-light text-xs font-bold px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-white shrink-0 text-center" style="text-decoration:none;">
                            Recuperar WhatsApp
                        </a>
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
                    <button class="section-help-btn" type="button" aria-label="Ayuda del Embudo" data-help-title="Embudo Multiverso" data-help-desc="Telemetría en tiempo real del ciclo de vida del cliente: Sesiones $\rightarrow$ Carritos $\rightarrow$ Checkouts $\rightarrow$ Compras D2C & Suscripciones.">?</button>
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
                    <span class="text-xs text-slate-500 font-semibold">{{ count($alerts) }} activas</span>
                </div>

                @if(count($alerts) > 0)
                    <ul class="space-y-2.5 text-xs text-slate-700">
                        @foreach($alerts as $alert)
                            <li class="p-3 bg-slate-50 border border-slate-200/60 rounded-xl flex items-start gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500 mt-1 shrink-0"></span>
                                <span class="leading-relaxed">{{ $alert }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-xs text-slate-500 text-center py-4">No hay alertas operativas críticas pendientes en este momento.</p>
                @endif
            </div>

            <!-- PANEL: TELEMETRÍA DE CONECTORES Y APIS -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Salud del Ecosistema</span>
                    </h3>
                    <button class="section-help-btn" type="button" aria-label="Ayuda de Salud del Ecosistema" data-help-title="Monitor de APIs" data-help-desc="Monitoreo de latencia y estado operacional de pasarelas (Stripe), scrapers (Torzo) y paqueterías (DHL / Skydropx).">?</button>
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
                    onclick="window.dispatchEvent(new CustomEvent('toggle-copilot-chat'))" 
                    class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer flex items-center justify-center gap-2"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <span>Abrir Chat con Copiloto</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
