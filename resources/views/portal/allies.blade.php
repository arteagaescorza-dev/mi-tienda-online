@extends('layouts.portal-panel')

@section('title', 'Convenios & Aliados')
@section('hub_kicker', 'Hub 7')
@section('hub_title', 'Convenios, Aliados & Atribución')
@section('hub_status', count($alliesList) . ' Aliados Activos')

@section('content')
<div id="alliesAppRoot" class="w-full text-slate-800 font-sans antialiased" data-screen="allies">
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-xs font-bold px-2 py-1 cursor-pointer">Cerrar</button>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span class="text-sm font-semibold">{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 text-xs font-bold px-2 py-1 cursor-pointer">Cerrar</button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- CONMUTADOR DE MODO: CONSOLA REVOPS vs EXTRANET SOCIO                     --}}
    {{-- ========================================================================= --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div class="allies-mode-switcher flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl border border-slate-200">
            <button class="allies-mode-btn {{ ($initialMode ?? 'admin') === 'admin' ? 'active' : '' }} px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer" data-action="switch-allies-mode" data-mode="admin" type="button">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <span>Vista Administrador RevOps</span>
            </button>
            <button class="allies-mode-btn {{ ($initialMode ?? 'admin') === 'partner' ? 'active' : '' }} px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer" data-action="switch-allies-mode" data-mode="partner" type="button">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m10 15 5-3-5-3v6Z"/></svg>
                <span>Pantalla Extranet del Aliado (Partner Portal)</span>
            </button>
            <a href="{{ route('portal.vendor.extranet') }}" class="allies-mode-btn px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer text-slate-700 hover:text-indigo-700 hover:bg-slate-200" title="Acceso al portal B2B de proveedores y confeccionistas">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                <span>Pantalla Extranet del Proveedor (B2B Supplier)</span>
            </a>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500 font-medium">Modo activo:</span>
            <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-slate-900 text-white font-mono" id="alliesActiveModeLabel">
                {{ ($initialMode ?? 'admin') === 'partner' ? 'Extranet Aliado' : 'Control RevOps' }}
            </span>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- REGLA 10: BARRA DE PESTAÑAS DE SUBMÓDULO PRIMERO (SUBNAV BAR)             --}}
    {{-- ========================================================================= --}}
    <div class="allies-subnav-bar mb-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <nav class="flex gap-2 -mb-px overflow-x-auto" aria-label="Submódulos de Convenios y Aliados">
            <!-- TAB 1: DIRECTORIO & ACUERDOS -->
            <button class="allies-subnav-btn active" data-action="switch-allies-tab" data-tab="directory" type="button">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>
                <span>1. Directorio &amp; Acuerdos</span>
                <span class="allies-subnav-count">{{ count($alliesList) }}</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Directorio" data-help-title="Directorio de Aliados" data-help-desc="Alianzas comunitarias, comisiones pactadas, tarifas por lead y balances individuales.">?</button>
            </button>

            <!-- TAB 2: CAMPAÑAS DIR A -->
            <button class="allies-subnav-btn" data-action="switch-allies-tab" data-tab="campaigns_a" type="button">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 11 4-7"/><path d="m19 11-4-7"/><path d="M2 11h20"/><path d="m3.5 11 1.6 7.4a2 2 0 0 0 2 1.6h9.8c.9 0 1.8-.7 2-1.6l1.6-7.4"/></svg>
                <span>2. Campañas Dir A (Lead Magnet CAPI)</span>
                <span class="allies-subnav-count">{{ count($campaignsDirA) }}</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Campañas Dir A" data-help-title="Campañas Dir A" data-help-desc="Promociones en web a cambio de registros de prospectos que nutren el CRM y elevan el EMQ de Meta CAPI.">?</button>
            </button>

            <!-- TAB 3: ATRIBUCIÓN DIR B -->
            <button class="allies-subnav-btn" data-action="switch-allies-tab" data-tab="attribution_b" type="button">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <span>3. Atribución Dir B (Cupones UTM)</span>
                <span class="allies-subnav-count">{{ count($couponsDirB) }}</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Atribución Dir B" data-help-title="Atribución Dir B" data-help-desc="Ventas referidas por aliados mediante cupones deterministas y enlaces tokenizados con comisión.">?</button>
            </button>

            <!-- TAB 4: CÁMARA DE COMPENSACIÓN -->
            <button class="allies-subnav-btn" data-action="switch-allies-tab" data-tab="clearing" type="button">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                <span>4. Cámara de Compensación</span>
                <span class="allies-subnav-count font-mono">${{ number_format(abs($totalNetBalance), 0) }} MXN</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Cámara de Compensación" data-help-title="Cámara de Compensación" data-help-desc="Cruce contable: (Leads Dir A x Tarifa) - Comisiones Dir B. Liquidación vía SPEI, Especie o Patrocinio.">?</button>
            </button>

            <!-- TAB 5: PREVISUALIZAR PARTNER PORTAL -->
            <button class="allies-subnav-btn" data-action="switch-allies-tab" data-tab="partner_portal" type="button">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12h20"/><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6"/></svg>
                <span>5. Previsualizar Partner Portal</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Partner Portal" data-help-title="Partner Portal" data-help-desc="Extranet segura tokenizada para aliados con validador de taquilla y exportación CSV.">?</button>
            </button>

            <!-- TAB 6: EXTRANET PROVEEDORES B2B -->
            <a href="{{ route('portal.vendor.extranet') }}" class="allies-subnav-btn flex items-center gap-2 text-indigo-700 hover:text-indigo-900 transition" title="Ir a la Extranet B2B de Proveedores & Fabricantes">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                <span>6. Extranet Proveedores B2B</span>
            </a>
        </nav>
    </div>

    {{-- ========================================================================= --}}
    {{-- REGLA 10: SECCIÓN 2: TARJETAS DE KPIS CONTEXTUALES (NIVEL 2)               --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- KPI 1: SOCIOS ALIADOS -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Socios Aliados</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Socios" data-help-title="Aliados Comerciales" data-help-desc="Número de acuerdos activos con festivales, creadores, saunas y colectivos.">?</button>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono tracking-tight text-slate-900">{{ count($alliesList) }} Activos</span>
                <span class="text-xs font-bold text-indigo-600 font-mono">CDMX &bull; Multiverso</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">Festivales &bull; Creadores OF &bull; Saunas &bull; Ballroom</div>
        </div>

        <!-- KPI 2: LEADS CAPTURADOS -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Leads Capturados (Dir A)</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Leads" data-help-title="Leads Dirección A" data-help-desc="Contactos registrados a cambio de beneficios en eventos de los aliados.">?</button>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono tracking-tight text-indigo-600">{{ array_sum(array_column(array_column($alliesList, 'stats'), 'leadsReceived')) }} Registros</span>
                <span class="text-xs font-bold text-emerald-600 font-mono">EMQ 9.8/10</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">Meta CAPI Server-Side con hashing SHA-256</div>
        </div>

        <!-- KPI 3: VENTAS REFERIDAS -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ventas Referidas (Dir B)</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Ventas" data-help-title="Ventas Dirección B" data-help-desc="Ingresos brutos generados en tienda mediante cupones y enlaces de los aliados.">?</button>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono tracking-tight text-slate-900">MXN ${{ number_format(array_sum(array_column(array_column($alliesList, 'stats'), 'gmvAttributed')), 2) }}</span>
                <span class="text-xs font-bold text-emerald-600 font-mono">{{ array_sum(array_column(array_column($alliesList, 'stats'), 'ordersAttributed')) }} pedidos</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">Atribución determinista por cupón y UTM</div>
        </div>

        <!-- KPI 4: SALDO CÁMARA DE COMPENSACIÓN -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Saldo Cámara Compensación</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Saldo" data-help-title="Cámara de Compensación" data-help-desc="Balance neto acumulado de comisiones de venta menos valor contable de leads captados.">?</button>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono tracking-tight text-amber-600">MXN ${{ number_format(abs($totalNetBalance), 2) }}</span>
                <span class="text-xs font-bold text-amber-700 font-mono">Por Liquidar</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">Liquidación vía SPEI &bull; Especie &bull; Patrocinio</div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- REGLA 10: SECCIÓN 3: PANELES Y ESPACIO DE TRABAJO OPERATIVO (NIVEL 3)     --}}
    {{-- ========================================================================= --}}
    <div id="alliesWorkspaceContainer">
        <!-- SUBMÓDULO 1: DIRECTORIO DE ALIADOS & ACUERDOS (TASK-ALY-01) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm mb-6" id="alliesDirectoryPanel">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 mb-5">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <h3 class="text-sm font-bold text-slate-900">Directorio de Aliados &amp; Acuerdos Comerciales</h3>
                    <button class="section-help-btn" type="button" aria-label="Ayuda Directorio" data-help-title="Directorio de Aliados" data-help-desc="Registro de socios comerciales de nicho con condiciones de comisión, costo de adquisición y saldos netos.">?</button>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button class="px-2.5 py-1 text-xs rounded font-semibold bg-slate-900 text-white cursor-pointer" data-action="filter-allies-universe" data-universe="all" type="button">Todos</button>
                    <button class="px-2.5 py-1 text-xs rounded font-semibold bg-white text-slate-600 border border-slate-200 cursor-pointer" data-action="filter-allies-universe" data-universe="marte" type="button">Marte</button>
                    <button class="px-2.5 py-1 text-xs rounded font-semibold bg-white text-slate-600 border border-slate-200 cursor-pointer" data-action="filter-allies-universe" data-universe="venus" type="button">Venus</button>
                    <button class="px-2.5 py-1 text-xs rounded font-semibold bg-white text-slate-600 border border-slate-200 cursor-pointer" data-action="filter-allies-universe" data-universe="tiresias" type="button">Tiresias</button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($alliesList as $ally)
                    <div class="ally-card bg-slate-50 border border-slate-200/80 rounded-2xl p-5 flex flex-col justify-between hover:border-indigo-300 transition-all" data-ally-universe="{{ $ally['universe'] }}">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="ally-category-badge px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ str_contains($ally['category'], 'circuit') ? 'bg-indigo-100 text-indigo-800' : (str_contains($ally['category'], 'onlyfans') ? 'bg-sky-100 text-sky-800' : (str_contains($ally['category'], 'sauna') ? 'bg-amber-100 text-amber-800' : 'bg-purple-100 text-purple-800')) }}">
                                    {{ $ally['categoryLabel'] }}
                                </span>
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-white text-slate-600 border border-slate-200 font-mono">{{ $ally['universe'] }}</span>
                            </div>

                            <h4 class="text-base font-bold text-slate-900 leading-snug">{{ $ally['name'] }}</h4>
                            <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $ally['code'] }} &bull; {{ $ally['socialHandle'] }}</p>
                            <p class="text-xs text-slate-600 mt-2 leading-relaxed line-clamp-2">{{ $ally['notes'] }}</p>

                            <div class="mt-3 pt-3 border-t border-slate-200/70 grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <span class="text-slate-400 text-[10px] uppercase font-bold block">Comisión Ventas:</span>
                                    <strong class="text-slate-800 font-mono">{{ $ally['commissionRate'] }}% Dir B</strong>
                                </div>
                                <div>
                                    <span class="text-slate-400 text-[10px] uppercase font-bold block">Crédito por Lead:</span>
                                    <strong class="text-slate-800 font-mono">${{ number_format($ally['leadAcquisitionCost'], 2) }} Dir A</strong>
                                </div>
                            </div>

                            <div class="mt-3 p-2.5 bg-white border border-slate-200/80 rounded-xl flex items-center justify-between text-xs">
                                <span class="text-slate-500 font-medium">Saldo Cámara:</span>
                                <span class="font-mono font-bold text-xs {{ $ally['clearingBalance'] >= 0 ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ $ally['clearingBalance'] >= 0 ? '+$' . number_format($ally['clearingBalance'], 2) . ' (A favor Tienda)' : '-$' . number_format(abs($ally['clearingBalance']), 2) . ' (Por Liquidar)' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-200/70 flex items-center justify-between gap-2">
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $ally['contactWhatsapp']) }}" target="_blank" class="px-2.5 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors flex items-center gap-1" style="text-decoration:none;">
                                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                <span>WhatsApp</span>
                            </a>
                            <button class="px-3 py-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors shadow-2xs cursor-pointer" data-action="open-partner-portal-for-ally" data-ally-id="{{ $ally['id'] }}" type="button">
                                Ver Extranet
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- SUBMÓDULO 2: CAMPAÑAS DIR A & LEAD MAGNET CAPI (TASK-ALY-02) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm mb-6" id="alliesCampaignsPanel">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 mb-4 gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 11 4-7m10 7-4-7M2 11h20m-18.5 0 1.6 7.4a2 2 0 0 0 2 1.6h9.8c.9 0 1.8-.7 2-1.6l1.6-7.4"/></svg>
                        <span>Campañas Dirección A: Web &rarr; Aliado (Lead Magnet &amp; CAPI)</span>
                    </h3>
                    <p class="text-xs text-slate-500">Promociones en vitrina a cambio de registro de prospectos. Disparo server-side CAPI con SHA-256.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Meta CAPI EMQ: 9.8/10
                    </span>
                </div>
            </div>

            <!-- Listado de Campañas Activas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                @foreach($campaignsDirA as $camp)
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 uppercase">{{ $camp['displayFormat'] }}</span>
                                <span class="text-xs font-bold text-slate-600 font-mono">{{ $camp['leadsCount'] }} leads</span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-900">{{ $camp['title'] }}</h4>
                            <p class="text-xs text-slate-500 mt-1">{{ $camp['allyName'] }}</p>
                            <p class="text-xs text-slate-600 mt-2 bg-white p-2 rounded border border-slate-200">{{ $camp['benefit'] }}</p>
                            @if(($camp['status'] ?? 'active') === 'pending_approval')
                                <div class="mt-2 p-2 bg-amber-50 border border-amber-200 rounded-lg">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-200 text-amber-900 uppercase">Solicitud Pendiente</span>
                                        <span class="text-[10px] text-slate-500">Póster adjunto</span>
                                    </div>
                                    <form action="{{ route('portal.allies.campaign.approve', $camp['id']) }}" method="POST" class="space-y-1.5">
                                        @csrf
                                        <label class="block text-[10px] font-bold text-slate-700">Descuento / Beneficio Pactado:</label>
                                        <input type="text" name="benefit" value="{{ $camp['benefit'] }}" class="w-full text-xs border border-slate-300 rounded px-2 py-1 bg-white" placeholder="Ej. 20% en coctelería o acceso preferente">
                                        <button type="submit" class="w-full py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold transition">Aprobar y Habilitar para Pop-up</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <div class="mt-3 pt-2 border-t border-slate-200/80 text-[11px] text-slate-400 font-mono">
                            Vigencia: {{ $camp['startDate'] }} al {{ $camp['endDate'] }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Tabla de Prospectos Capturados (Data Masking) -->
            <div class="border-t border-slate-100 pt-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span>Últimos Vouchers de Prospectos Capturados</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-700 rounded">Data Masking Activo</span>
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 uppercase tracking-wider bg-slate-50/50">
                                <th class="p-2.5 font-semibold">Folio Voucher</th>
                                <th class="p-2.5 font-semibold">Prospecto</th>
                                <th class="p-2.5 font-semibold">WhatsApp (Enmascarado)</th>
                                <th class="p-2.5 font-semibold">CAPI EMQ</th>
                                <th class="p-2.5 font-semibold">Fecha Captura</th>
                                <th class="p-2.5 font-semibold text-right">Estado Taquilla</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-mono">
                            @foreach($leadCaptures as $lead)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="p-2.5 font-bold text-indigo-700">{{ $lead['voucherCode'] }}</td>
                                    <td class="p-2.5 font-sans font-medium text-slate-900">{{ $lead['leadName'] }}</td>
                                    <td class="p-2.5 text-slate-600">{{ preg_replace('/(\+?52\s?55\s?)\d{4}(\s?\d{4})/', '$1 **** $2', $lead['leadWhatsapp']) }}</td>
                                    <td class="p-2.5 font-bold text-emerald-700">{{ $lead['capiScore'] }}/10.0</td>
                                    <td class="p-2.5 text-slate-500">{{ $lead['date'] }}</td>
                                    <td class="p-2.5 text-right font-sans">
                                        @if($lead['isRedeemed'])
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Canjeado ({{ $lead['redeemedAt'] }})</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Pendiente de Canje</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SUBMÓDULO 3: ATRIBUCIÓN DIR B & CUPONES UTM (TASK-ALY-03) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm mb-6" id="alliesAttributionPanel">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 mb-4 gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <span>Atribución Dirección B: Aliado &rarr; Web (Cupones Deterministas &amp; UTM)</span>
                    </h3>
                    <p class="text-xs text-slate-500">Ventas referidas por creadores, salones y colectivos. Comisiones devengadas por orden efectiva.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 uppercase tracking-wider bg-slate-50/50">
                            <th class="p-3 font-semibold">Cupón Determinista</th>
                            <th class="p-3 font-semibold">Aliado Referente</th>
                            <th class="p-3 font-semibold">Descuento</th>
                            <th class="p-3 font-semibold">Comisión (%)</th>
                            <th class="p-3 font-semibold">Clics / Tráfico</th>
                            <th class="p-3 font-semibold">Órdenes</th>
                            <th class="p-3 font-semibold">GMV Atribuido</th>
                            <th class="p-3 font-semibold text-right">Comisión Devengada</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono">
                        @foreach($couponsDirB as $c)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="p-3">
                                    <span class="px-2 py-1 bg-slate-900 text-white rounded text-xs font-bold">{{ $c['couponCode'] }}</span>
                                </td>
                                <td class="p-3 font-sans font-semibold text-slate-800">
                                    @php($allyMatch = collect($alliesList)->firstWhere('id', $c['allyId']))
                                    {{ $allyMatch['name'] ?? 'Aliado #' . $c['allyId'] }}
                                </td>
                                <td class="p-3 text-slate-700">{{ $c['discountPct'] }}% OFF</td>
                                <td class="p-3 font-bold text-indigo-700">{{ $c['commissionRate'] }}%</td>
                                <td class="p-3 text-slate-600">{{ $c['clicks'] }} clics</td>
                                <td class="p-3 font-bold text-slate-900">{{ $c['orders'] }} ventas</td>
                                <td class="p-3 font-bold text-emerald-700">MXN ${{ number_format($c['revenue'], 2) }}</td>
                                <td class="p-3 text-right font-black text-indigo-700">MXN ${{ number_format($c['commissionEarned'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SUBMÓDULO 4: CÁMARA DE COMPENSACIÓN & LIQUIDACIÓN (CLEARING HOUSE) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm mb-6" id="alliesClearingPanel">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 mb-4 gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Cámara de Compensación &amp; Auditoría de Liquidaciones (TASK-ALY-04)</span>
                    </h3>
                    <p class="text-xs text-slate-500">Cruce contable determinista entre créditos por prospectos y comisiones devengadas por órdenes.</p>
                </div>
                <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-700">
                    <span>Fórmula: <strong>(Leads Dir A x Tarifa) - Comisiones Dir B</strong></span>
                </div>
            </div>

            <!-- Matriz de Liquidación por Aliado -->
            <div class="overflow-x-auto mb-6">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 uppercase tracking-wider bg-slate-50/50">
                            <th class="p-3 font-semibold">Aliado</th>
                            <th class="p-3 font-semibold">Leads Dir A</th>
                            <th class="p-3 font-semibold">Crédito Leads</th>
                            <th class="p-3 font-semibold">Ventas Dir B</th>
                            <th class="p-3 font-semibold">Comisión Dir B</th>
                            <th class="p-3 font-semibold">Saldo Neto</th>
                            <th class="p-3 font-semibold text-right">Ejecutar Liquidación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($alliesList as $a)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="p-3">
                                    <strong class="font-bold text-slate-900 block">{{ $a['name'] }}</strong>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $a['code'] }} &bull; {{ $a['bankName'] }}</span>
                                </td>
                                <td class="p-3 font-mono text-slate-700">{{ $a['stats']['leadsReceived'] }} leads</td>
                                <td class="p-3 font-mono font-bold text-emerald-700">+MXN ${{ number_format($a['stats']['leadsReceived'] * $a['leadAcquisitionCost'], 2) }}</td>
                                <td class="p-3 font-mono text-slate-700">MXN ${{ number_format($a['stats']['gmvAttributed'], 2) }}</td>
                                <td class="p-3 font-mono font-bold text-rose-700">-MXN ${{ number_format($a['stats']['commissionEarned'], 2) }}</td>
                                <td class="p-3 font-mono font-black {{ $a['clearingBalance'] >= 0 ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ $a['clearingBalance'] >= 0 ? '+MXN $' . number_format($a['clearingBalance'], 2) : '-MXN $' . number_format(abs($a['clearingBalance']), 2) }}
                                </td>
                                <td class="p-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button class="button button-quiet text-[11px] py-1 px-2.5 bg-slate-900 text-white rounded shadow-2xs cursor-pointer" data-action="settle-spei-btn" data-ally-id="{{ $a['id'] }}" title="Dispersión SPEI programada para viernes (TASK-INT-19)" type="button">
                                            SPEI
                                        </button>
                                        <button class="button button-light text-[11px] py-1 px-2 rounded shadow-2xs cursor-pointer" data-action="settle-inkind-btn" data-ally-id="{{ $a['id'] }}" title="Canje de ropa interior con asiento inmutable en Kardex" type="button">
                                            Especie
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Historial de Liquidaciones -->
            <div class="border-t border-slate-100 pt-4">
                <h4 class="text-xs font-bold text-slate-900 mb-3 uppercase tracking-wider">Historial de Lotes de Liquidación Conciliados</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 font-semibold bg-slate-50">
                                <th class="p-2.5">Folio Lote</th>
                                <th class="p-2.5">Aliado</th>
                                <th class="p-2.5">Periodo</th>
                                <th class="p-2.5">Tipo Liquidación</th>
                                <th class="p-2.5">Monto Neto</th>
                                <th class="p-2.5">Referencia Contable</th>
                                <th class="p-2.5 text-right">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-mono">
                            @foreach($clearingSettlements as $s)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="p-2.5 font-bold text-slate-900">{{ $s['code'] }}</td>
                                    <td class="p-2.5 font-sans font-semibold text-slate-800">{{ $s['allyName'] }}</td>
                                    <td class="p-2.5 text-slate-600 font-sans">{{ $s['period'] }}</td>
                                    <td class="p-2.5 font-sans">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">{{ $s['typeLabel'] }}</span>
                                    </td>
                                    <td class="p-2.5 font-bold text-slate-900">MXN ${{ number_format(abs($s['netBalance']), 2) }}</td>
                                    <td class="p-2.5 text-slate-500 text-[11px]">{{ $s['reference'] }}</td>
                                    <td class="p-2.5 text-right font-sans">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Conciliado</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SUBMÓDULO 5: PANTALLA EXTRANET DEL SOCIO / PARTNER PORTAL (TASK-ALY-05) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm mb-6" id="alliesPartnerPortalPanel">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 mb-5 gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 text-[10px] font-bold bg-indigo-600 text-white rounded">Partner Portal</span>
                        <h3 class="text-base font-bold text-slate-900">{{ $selectedAlly['name'] ?? 'Aliado Comercial' }}</h3>
                    </div>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">Token Criptográfico: {{ $selectedAlly['portalToken'] ?? 'tok_demo' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="button button-light text-xs py-1.5 px-3 rounded-lg flex items-center gap-1.5 cursor-pointer bg-indigo-50 border border-indigo-200 text-indigo-700 font-bold" onclick="document.getElementById('modalNewAllyCampaign').classList.remove('hidden');" type="button">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>+ Registrar Evento / Beneficio</span>
                    </button>
                    <button class="button button-light text-xs py-1.5 px-3 rounded-lg flex items-center gap-1.5 cursor-pointer" data-action="export-door-list-csv" data-ally-id="{{ $selectedAlly['id'] ?? 1 }}" type="button">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span>Descargar CSV Taquilla</span>
                    </button>
                    <button class="button button-quiet text-xs py-1.5 px-3 bg-slate-900 text-white rounded-lg cursor-pointer" data-action="open-qr-modal" data-ally-id="{{ $selectedAlly['id'] ?? 1 }}" type="button">
                        Generar QR Flyer
                    </button>
                </div>
            </div>

            <!-- Validador de Taquilla -->
            <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Validador de Vouchers en Puerta / Taquilla</span>
                    </h4>
                    <span class="text-[11px] text-slate-500 font-mono">Anti-duplicados en tiempo real</span>
                </div>
                <form class="flex gap-2" data-action="validate-ticket-form" onsubmit="return false;">
                    <input type="text" placeholder="Ingresa folio de voucher (ej. PAR-7821)..." class="text-xs font-mono border border-slate-300 rounded-lg px-3 py-2 flex-1 bg-white focus:outline-none focus:border-slate-800" data-ticket-input id="alliesTicketVoucherInput">
                    <button type="button" class="button button-quiet text-xs px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold cursor-pointer" data-action="validate-ticket-btn">
                        Validar Canje
                    </button>
                </form>
                <div class="mt-3 p-3 bg-white rounded-xl border border-slate-200 text-xs hidden" id="alliesTicketValidationResult"></div>
            </div>

            <!-- Enlace de Difusión & UTM -->
            <div class="p-4 bg-indigo-50/50 border border-indigo-100 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div>
                    <span class="font-bold text-indigo-950 block">Enlace de Difusión con Atribución Automática:</span>
                    <code class="text-indigo-800 font-mono text-[11px]">https://mitienda.com/?utm_source={{ $selectedAlly['code'] ?? 'GENERAL' }}&amp;coupon={{ !empty($couponsDirB) && isset($couponsDirB[0]['couponCode']) ? $couponsDirB[0]['couponCode'] : 'PARADISO15' }}</code>
                </div>
                <button class="button button-light text-xs py-1.5 px-3 bg-white text-indigo-900 border-indigo-200 rounded-lg shrink-0 cursor-pointer" data-action="copy-ally-link" type="button">
                    Copiar Enlace
                </button>
            </div>
        </div>
    </div>

    {{-- Formulario oculto para ejecucion de liquidaciones --}}
    <form id="settlementActionForm" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="settlement_type" id="settlementTypeInput" value="spei_transfer">
    </form>

    <!-- Modal para que el Aliado Registre un Nuevo Evento / Beneficio con Póster Oficial -->
    <div id="modalNewAllyCampaign" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Nuevo Evento o Beneficio de Aliado</h3>
                <button type="button" class="text-slate-400 hover:text-slate-600 font-bold" onclick="document.getElementById('modalNewAllyCampaign').classList.add('hidden');">&times;</button>
            </div>
            <form action="{{ !empty($selectedAlly['portalToken']) ? route('portal.ally.extranet.campaign.submit', $selectedAlly['portalToken']) : '#' }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nombre del Evento, Beneficio o Servicio *</label>
                    <input type="text" name="title" required class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-slate-50 focus:bg-white" placeholder="Ej. Fiesta Paradiso / 20% Spa Masajes / Suscripción Contenido">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Universo Afín *</label>
                        <select name="universe" class="w-full text-xs border border-slate-300 rounded-lg px-2 py-2 bg-slate-50 font-semibold">
                            <option value="marte">Marte (Masculino)</option>
                            <option value="venus">Venus (Femenino / Lencería)</option>
                            <option value="tiresias">Tiresias (Afirmativo)</option>
                            <option value="terra">Terra (Global)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Póster Oficial (Imagen) *</label>
                        <input type="file" name="banner_image" accept="image/*" required class="w-full text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[11px] file:font-semibold file:bg-indigo-50 file:text-indigo-700 cursor-pointer">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Beneficio o Descuento Propuesto *</label>
                    <input type="text" name="benefit" required class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-slate-50 focus:bg-white" placeholder="Ej. 15% OFF en taquilla y cóctel de bienvenida">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Instrucciones de Canje</label>
                    <textarea name="instructions" rows="2" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-1.5 bg-slate-50" placeholder="Ej. Presentar el voucher digital al ingresar"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Fecha Inicio</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="w-full text-xs border border-slate-300 rounded-lg px-2 py-1.5 bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Fecha Fin</label>
                        <input type="date" name="end_date" value="{{ date('Y-m-d', strtotime('+2 months')) }}" class="w-full text-xs border border-slate-300 rounded-lg px-2 py-1.5 bg-slate-50">
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" class="px-3 py-1.5 text-xs text-slate-600 hover:text-slate-800" onclick="document.getElementById('modalNewAllyCampaign').classList.add('hidden');">Cancelar</button>
                    <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold">Enviar Solicitud con Póster</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SCRIPT REACTIVO OPERATIVO PARA CONVENIOS Y ALIADOS (VANILLA JS LIGERO) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const panels = {
        'directory': document.getElementById('alliesDirectoryPanel'),
        'campaigns_a': document.getElementById('alliesCampaignsPanel'),
        'attribution_b': document.getElementById('alliesAttributionPanel'),
        'clearing': document.getElementById('alliesClearingPanel'),
        'partner_portal': document.getElementById('alliesPartnerPortalPanel')
    };

    const tabButtons = document.querySelectorAll('[data-action="switch-allies-tab"]');
    const modeButtons = document.querySelectorAll('[data-action="switch-allies-mode"]');
    const modeLabel = document.getElementById('alliesActiveModeLabel');

    function switchTab(targetTab) {
        Object.keys(panels).forEach(tabKey => {
            const panel = panels[tabKey];
            if (!panel) return;
            if (tabKey === targetTab) {
                panel.classList.remove('hidden');
            } else {
                panel.classList.add('hidden');
            }
        });

        tabButtons.forEach(btn => {
            if (btn.getAttribute('data-tab') === targetTab) {
                btn.classList.add('active', 'border-indigo-600', 'text-indigo-600');
                btn.classList.remove('border-transparent', 'text-slate-500');
            } else {
                btn.classList.remove('active', 'border-indigo-600', 'text-indigo-600');
                btn.classList.add('border-transparent', 'text-slate-500');
            }
        });

        const url = new URL(window.location);
        url.searchParams.set('tab', targetTab);
        window.history.replaceState({}, '', url);
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (e.target.classList.contains('section-help-btn')) return;
            const tab = this.getAttribute('data-tab');
            if (tab) switchTab(tab);
        });
    });

    modeButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const mode = this.getAttribute('data-mode');
            modeButtons.forEach(b => b.classList.remove('active', 'bg-white', 'shadow-xs', 'text-slate-900'));
            this.classList.add('active', 'bg-white', 'shadow-xs', 'text-slate-900');

            if (mode === 'partner') {
                if (modeLabel) modeLabel.textContent = 'Extranet Aliado';
                switchTab('partner_portal');
            } else {
                if (modeLabel) modeLabel.textContent = 'Control RevOps';
                switchTab('directory');
            }
        });
    });

    // Check initial tab from URL or query
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab') || '{{ $initialMode === "partner" ? "partner_portal" : "directory" }}';
    if (initialTab && panels[initialTab]) {
        switchTab(initialTab);
    }

    // Direct open extranet button for specific ally
    document.querySelectorAll('[data-action="open-partner-portal-for-ally"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const allyId = this.getAttribute('data-ally-id');
            const url = new URL(window.location);
            url.searchParams.set('ally_id', allyId);
            url.searchParams.set('tab', 'partner_portal');
            window.location.href = url.toString();
        });
    });

    // Settlement Actions (SPEI & Especie)
    const settlementForm = document.getElementById('settlementActionForm');
    const settlementTypeInput = document.getElementById('settlementTypeInput');

    document.querySelectorAll('[data-action="settle-spei-btn"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const allyId = this.getAttribute('data-ally-id');
            if (confirm('Deseas registrar y conciliar la liquidacion SPEI para este socio comercial?')) {
                settlementForm.action = `/admin/convenios/aliados/${allyId}/liquidar`;
                settlementTypeInput.value = 'spei_transfer';
                settlementForm.submit();
            }
        });
    });

    document.querySelectorAll('[data-action="settle-inkind-btn"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const allyId = this.getAttribute('data-ally-id');
            if (confirm('Deseas registrar el canje de prendas en especie con asiento inmutable en Kardex?')) {
                settlementForm.action = `/admin/convenios/aliados/${allyId}/liquidar`;
                settlementTypeInput.value = 'in_kind_apparel';
                settlementForm.submit();
            }
        });
    });

    // Door List CSV Export
    document.querySelectorAll('[data-action="export-door-list-csv"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const allyId = this.getAttribute('data-ally-id');
            window.location.href = `/admin/convenios/aliados/${allyId}/export-csv`;
        });
    });

    // Copy Diffusion Link
    document.querySelectorAll('[data-action="copy-ally-link"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const codeEl = this.parentElement.querySelector('code');
            if (codeEl) {
                navigator.clipboard.writeText(codeEl.textContent.trim()).then(() => {
                    const originalText = this.textContent;
                    this.textContent = 'Copiado!';
                    setTimeout(() => { this.textContent = originalText; }, 2000);
                });
            }
        });
    });

    // Ticket Voucher Validation in Taquilla
    const validateBtn = document.querySelector('[data-action="validate-ticket-btn"]');
    const voucherInput = document.getElementById('alliesTicketVoucherInput');
    const validationResult = document.getElementById('alliesTicketValidationResult');

    function performTicketValidation() {
        if (!voucherInput || !validationResult) return;
        const code = voucherInput.value.trim();
        if (!code) {
            voucherInput.focus();
            return;
        }

        validationResult.classList.remove('hidden');
        validationResult.className = 'mt-3 p-3 bg-slate-100 border border-slate-200 text-slate-700 rounded-xl text-xs font-mono';
        validationResult.innerHTML = 'Consultando sistema de taquilla anti-duplicados...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        fetch('{{ route("portal.allies.ticket.validate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ voucher_code: code })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                validationResult.className = 'mt-3 p-3 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl text-xs';
                validationResult.innerHTML = `<strong>Acceso Autorizado:</strong> ${body.message}<br><span class="text-[11px] text-emerald-700 font-mono mt-1 block">Asistente: ${body.lead_name} &bull; Folio: ${body.voucher_code} &bull; Hora: ${body.redeemed_at} &bull; ${body.redeemed_by}</span>`;
                voucherInput.value = '';
            } else if (status === 422 || body.already_redeemed) {
                validationResult.className = 'mt-3 p-3 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl text-xs';
                validationResult.innerHTML = `<strong>Atencion - Voucher Ya Canjeado:</strong> ${body.message}`;
            } else {
                validationResult.className = 'mt-3 p-3 bg-rose-50 border border-rose-200 text-rose-900 rounded-xl text-xs';
                validationResult.innerHTML = `<strong>Error de Validacion:</strong> ${body.message || 'Folio invalido o no encontrado.'}`;
            }
        })
        .catch(() => {
            validationResult.className = 'mt-3 p-3 bg-rose-50 border border-rose-200 text-rose-900 rounded-xl text-xs';
            validationResult.innerHTML = '<strong>Error de Conexion:</strong> No se pudo conectar con el servicio de validacion.';
        });
    }

    if (validateBtn) {
        validateBtn.addEventListener('click', performTicketValidation);
    }
    if (voucherInput) {
        voucherInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performTicketValidation();
            }
        });
    }

    // Filter Allies by Universe in Directory Tab
    const universeFilterBtns = document.querySelectorAll('[data-action="filter-allies-universe"]');
    const allyCards = document.querySelectorAll('.ally-card');

    universeFilterBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const universe = this.getAttribute('data-universe');
            universeFilterBtns.forEach(b => {
                b.classList.remove('bg-slate-900', 'text-white');
                b.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-200');
            });
            this.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-200');
            this.classList.add('bg-slate-900', 'text-white');

            allyCards.forEach(card => {
                const cardUniverse = card.getAttribute('data-ally-universe');
                if (universe === 'all' || cardUniverse === universe) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        });
    });
});
</script>
@endsection
