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

            <!-- TAB 2: BANDEJA DE SOLICITUDES DE EVENTOS -->
            <button class="allies-subnav-btn" data-action="switch-allies-tab" data-tab="requests" type="button">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <span>2. Solicitudes de Eventos &amp; Convenios</span>
                <span class="allies-subnav-count {{ count($pendingCampaigns) > 0 ? 'bg-amber-500 text-white font-bold' : '' }}">{{ count($pendingCampaigns) }}</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Solicitudes" data-help-title="Bandeja de Solicitudes de Eventos" data-help-desc="Propuestas de eventos y convenios enviadas por aliados desde su extranet para revisión y aprobación por Superadministrador.">?</button>
            </button>

            <!-- TAB 3: CAMPAÑAS DIR A -->
            <button class="allies-subnav-btn" data-action="switch-allies-tab" data-tab="campaigns_a" type="button">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 11 4-7"/><path d="m19 11-4-7"/><path d="M2 11h20"/><path d="m3.5 11 1.6 7.4a2 2 0 0 0 2 1.6h9.8c.9 0 1.8-.7 2-1.6l1.6-7.4"/></svg>
                <span>3. Campañas Dir A (Lead Magnet CAPI)</span>
                <span class="allies-subnav-count">{{ count($campaignsDirA) }}</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Campañas Dir A" data-help-title="Campañas Dir A" data-help-desc="Promociones en web a cambio de registros de prospectos que nutren el CRM y elevan el EMQ de Meta CAPI.">?</button>
            </button>

            <!-- TAB 4: ATRIBUCIÓN DIR B -->
            <button class="allies-subnav-btn" data-action="switch-allies-tab" data-tab="attribution_b" type="button">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <span>4. Atribución Dir B (Cupones UTM)</span>
                <span class="allies-subnav-count">{{ count($couponsDirB) }}</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Atribución Dir B" data-help-title="Atribución Dir B" data-help-desc="Ventas referidas por aliados mediante cupones deterministas y enlaces tokenizados con comisión.">?</button>
            </button>

            <!-- TAB 5: CÁMARA DE COMPENSACIÓN -->
            <button class="allies-subnav-btn" data-action="switch-allies-tab" data-tab="clearing" type="button">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                <span>5. Cámara de Compensación</span>
                <span class="allies-subnav-count font-mono">${{ number_format(abs($totalNetBalance), 0) }} MXN</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Cámara de Compensación" data-help-title="Cámara de Compensación" data-help-desc="Cruce contable: (Leads Dir A x Tarifa) - Comisiones Dir B. Liquidación vía SPEI, Especie o Patrocinio.">?</button>
            </button>

            <!-- TAB 6: PREVISUALIZAR PARTNER PORTAL -->
            <button class="allies-subnav-btn" data-action="switch-allies-tab" data-tab="partner_portal" type="button">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12h20"/><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6"/></svg>
                <span>6. Previsualizar Partner Portal</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Partner Portal" data-help-title="Partner Portal" data-help-desc="Extranet segura tokenizada para aliados con validador de taquilla y exportación CSV.">?</button>
            </button>

            <!-- TAB 7: EXTRANET PROVEEDORES B2B -->
            <a href="{{ route('portal.vendor.extranet') }}" class="allies-subnav-btn flex items-center gap-2 text-indigo-700 hover:text-indigo-900 transition" title="Ir a la Extranet B2B de Proveedores & Fabricantes">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                <span>7. Extranet Proveedores B2B</span>
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
                <span class="text-2xl font-black font-mono tracking-tight text-slate-900">{{ $activeAlliesCount ?? count($alliesList) }} Activos</span>
                @if(($archivedAlliesCount ?? 0) > 0)
                    <span class="text-xs font-bold text-amber-600 font-mono">{{ $archivedAlliesCount }} Archivados</span>
                @else
                    <span class="text-xs font-bold text-indigo-600 font-mono">CDMX &bull; Multiverso</span>
                @endif
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

                    <div class="h-4 w-px bg-slate-200 mx-1"></div>

                    <button class="px-2.5 py-1 text-xs rounded font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 cursor-pointer" data-action="filter-allies-status" data-status="all" type="button">Todos</button>
                    <button class="px-2.5 py-1 text-xs rounded font-semibold bg-white text-slate-600 border border-slate-200 cursor-pointer" data-action="filter-allies-status" data-status="active" type="button">Activos ({{ $activeAlliesCount ?? count($alliesList) }})</button>
                    <button class="px-2.5 py-1 text-xs rounded font-semibold bg-white text-slate-600 border border-slate-200 cursor-pointer" data-action="filter-allies-status" data-status="archived" type="button">Archivados ({{ $archivedAlliesCount ?? 0 }})</button>

                    <button type="button" class="px-3 py-1 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-2xs flex items-center gap-1.5 cursor-pointer ml-auto sm:ml-2" onclick="window.dispatchEvent(new CustomEvent('open-module-settings'))" title="Dar de alta nuevo aliado y configurar acuerdos comerciales (Engranaje Navbar)">
                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        <span>+ Alta de Aliado</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="alliesDirectoryGrid">
                @forelse($alliesList as $ally)
                    <div class="ally-card bg-slate-50 border border-slate-200/80 rounded-2xl p-5 flex flex-col justify-between hover:border-indigo-300 transition-all {{ ($ally['isArchived'] ?? false) ? 'opacity-80 bg-slate-100/90' : '' }}" data-ally-universe="{{ $ally['universe'] }}" data-ally-status="{{ ($ally['isArchived'] ?? false) ? 'archived' : 'active' }}">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="ally-category-badge px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ str_contains($ally['category'], 'circuit') ? 'bg-indigo-100 text-indigo-800' : (str_contains($ally['category'], 'onlyfans') ? 'bg-sky-100 text-sky-800' : (str_contains($ally['category'], 'sauna') ? 'bg-amber-100 text-amber-800' : 'bg-purple-100 text-purple-800')) }}">
                                        {{ $ally['categoryLabel'] }}
                                    </span>
                                    @if($ally['isArchived'] ?? false)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300 font-mono">
                                            Archivado
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 font-mono">
                                            Activo
                                        </span>
                                    @endif
                                </div>
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

                            <div class="mt-2.5 pt-2 border-t border-slate-200/70 text-[11px]">
                                <span class="text-slate-400 font-bold uppercase block text-[9px] mb-1">Descuentos Autorizados Portal:</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($ally['allowedDiscountTypes'] ?? ['cover', 'cover_2x1', 'free_drink', 'store_discount', 'vip_access'] as $dt)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-white text-slate-700 border border-slate-200 shadow-2xs">
                                            {{ match($dt) {
                                                'cover' => 'Cover Libre',
                                                'cover_2x1' => 'Cover 2x1',
                                                'free_drink' => 'Trago Cortesía',
                                                'store_discount' => 'Tienda ' . ($ally['maxDiscountPct'] ?? 20) . '%',
                                                'vip_access' => 'VIP',
                                                default => ucfirst(str_replace('_', ' ', $dt))
                                            } }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-3 p-2.5 bg-white border border-slate-200/80 rounded-xl flex items-center justify-between text-xs">
                                <span class="text-slate-500 font-medium">Saldo Cámara:</span>
                                <span class="font-mono font-bold text-xs {{ $ally['clearingBalance'] >= 0 ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ $ally['clearingBalance'] >= 0 ? '+$' . number_format($ally['clearingBalance'], 2) . ' (A favor Tienda)' : '-$' . number_format(abs($ally['clearingBalance']), 2) . ' (Por Liquidar)' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-200/70 flex flex-wrap items-center justify-between gap-1.5">
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $ally['contactWhatsapp']) }}" target="_blank" class="px-2 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors flex items-center gap-1" style="text-decoration:none;">
                                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                <span>WhatsApp</span>
                            </a>
                            <div class="flex items-center gap-1">
                                <button class="px-2.5 py-1.5 text-xs font-semibold text-slate-700 bg-slate-200 hover:bg-slate-300 rounded-lg transition-colors cursor-pointer" data-action="edit-ally-btn" data-ally="{{ json_encode($ally) }}" type="button" title="Editar acuerdos comerciales de este aliado">
                                    Editar
                                </button>

                                <form action="{{ route('portal.allies.archive', $ally['id']) }}" method="POST" class="inline" onsubmit="return confirm('{{ ($ally['isArchived'] ?? false) ? '¿Deseas reactivar a este socio aliado?' : '¿Deseas archivar a este socio aliado? Sus campañas y cupones activos serán pausados.' }}');">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1.5 text-xs font-semibold {{ ($ally['isArchived'] ?? false) ? 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200' : 'text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200' }} rounded-lg transition-colors cursor-pointer" title="{{ ($ally['isArchived'] ?? false) ? 'Reactivar aliado' : 'Archivar aliado' }}">
                                        {{ ($ally['isArchived'] ?? false) ? 'Reactivar' : 'Archivar' }}
                                    </button>
                                </form>

                                <form action="{{ route('portal.allies.destroy', $ally['id']) }}" method="POST" class="inline" onsubmit="return confirm('¿Confirmas que deseas eliminar al aliado \'{{ $ally['name'] }}\'? Se preservarán sus registros de forma segura en histórico.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition-colors cursor-pointer" title="Eliminar aliado">
                                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" class="inline-block"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </form>

                                <button class="px-3 py-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors shadow-2xs cursor-pointer" data-action="open-partner-portal-for-ally" data-ally-id="{{ $ally['id'] }}" type="button">
                                    Ver Extranet
                                </button>
                            </div>
                        </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 px-6 bg-white border border-dashed border-slate-300 rounded-2xl text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800">Directorio de Aliados Vacío</h4>
                        <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 leading-relaxed">
                            No hay aliados comerciales registrados actualmente. Comienza dando de alta a tu primer socio comercial desde el botón "+ Alta de Aliado" o el engranaje del navbar para configurar sus acuerdos comerciales (descuentos, comisiones y compensaciones).
                        </p>
                        <button type="button" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-xs cursor-pointer transition" onclick="window.dispatchEvent(new CustomEvent('open-module-settings'))">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                            <span>+ Alta de Aliado</span>
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- SUBMÓDULO 2: BANDEJA DE SOLICITUDES DE EVENTOS & CONVENIOS -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm mb-6 hidden" id="alliesRequestsPanel">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 mb-4 gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        <span>Bandeja de Solicitudes de Eventos &amp; Convenios</span>
                    </h3>
                    <p class="text-xs text-slate-500">Recepción y dictamen de eventos y promociones creados por los aliados comerciales para convenio.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ count($pendingCampaigns) > 0 ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                        {{ count($pendingCampaigns) }} Pendientes de Autorización
                    </span>
                </div>
            </div>

            <!-- Caja de Política Oficial -->
            <div class="p-3.5 bg-amber-50/80 border border-amber-200 rounded-xl mb-5 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="text-xs text-amber-900 leading-relaxed">
                    <strong class="font-bold block text-amber-950">POLÍTICA DE ALIADOS &amp; GOBERNANZA COMERCIAL:</strong>
                    Los aliados comerciales <strong>únicamente tienen facultades para solicitar</strong> la dada de alta de su evento o promoción. 
                    Queda estrictamente prohibida la publicación directa no supervisada. Toda solicitud enviada permanece en estado <span class="font-semibold underline">Pendiente de Autorización</span> hasta que un <strong>Superadministrador</strong> la valide y apruebe en esta bandeja.
                    Al aprobarse, se sincroniza <strong>inmediata y automáticamente</strong> con la página de convenios de explorar del Storefront y con el generador de Pop-ups en Hub 2.
                </div>
            </div>

            @if(empty($pendingCampaigns))
                <div class="text-center py-10 bg-slate-50 border border-dashed border-slate-300 rounded-2xl mb-6">
                    <svg class="w-10 h-10 text-slate-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h4 class="text-sm font-bold text-slate-700">Bandeja al día: No hay solicitudes pendientes</h4>
                    <p class="text-xs text-slate-500 mt-1">Todas las solicitudes de eventos y convenios de los aliados han sido dictaminadas.</p>
                </div>
            @else
                <div class="space-y-4 mb-6">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>Solicitudes Pendientes de Aprobación ({{ count($pendingCampaigns) }})</span>
                    </h4>

                    @foreach($pendingCampaigns as $req)
                        <div class="p-5 bg-amber-50/40 border border-amber-200 rounded-2xl flex flex-col lg:flex-row gap-5 hover:border-amber-400 transition-all shadow-xs">
                            <!-- Flyer / Poster Oficial -->
                            <div class="w-full lg:w-44 shrink-0 flex flex-col items-center justify-center bg-slate-900 rounded-xl overflow-hidden relative group min-h-[160px]">
                                @if(!empty($req['bannerImage']) && $req['bannerImage'] !== 'default_campaign.webp')
                                    <img src="{{ $req['bannerImage'] }}" alt="{{ $req['title'] }}" class="w-full h-44 object-cover object-center group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="p-4 text-center">
                                        <svg class="w-8 h-8 text-slate-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-[10px] text-slate-400 block font-mono">Póster Oficial</span>
                                    </div>
                                @endif
                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-black/70 text-amber-300 backdrop-blur-xs font-mono">
                                    {{ $req['universe'] }}
                                </span>
                            </div>

                            <!-- Detalles de la solicitud -->
                            <div class="flex-1 flex flex-col justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-800 font-mono">
                                                {{ $req['allyCategoryLabel'] }}
                                            </span>
                                            <span class="text-xs font-bold text-slate-700">{{ $req['allyName'] }}</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-200 text-amber-900 uppercase">
                                            Solicitud Pendiente &bull; {{ $req['createdAt'] }}
                                        </span>
                                    </div>

                                    <h4 class="text-base font-bold text-slate-900">{{ $req['title'] }}</h4>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 text-xs">
                                        <div class="p-2.5 bg-white border border-slate-200 rounded-xl">
                                            <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Beneficio / Descuento Propuesto:</span>
                                            <p class="font-semibold text-slate-800">{{ $req['benefit'] }}</p>
                                            <span class="inline-block mt-1 px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-mono">
                                                Tipo: {{ match($req['discountType']) {
                                                    'cover' => 'Cover Libre',
                                                    'cover_2x1' => 'Cover 2x1',
                                                    'free_drink' => 'Trago Cortesía',
                                                    'store_discount' => '% Descuento Tienda',
                                                    'vip_access' => 'Acceso VIP',
                                                    default => $req['discountType']
                                                } }}
                                            </span>
                                        </div>
                                        <div class="p-2.5 bg-white border border-slate-200 rounded-xl">
                                            <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Instrucciones de Canje en Puerta:</span>
                                            <p class="text-slate-600 text-xs">{{ $req['instructions'] ?: 'Presentar voucher digital en taquilla' }}</p>
                                            <span class="text-[10px] text-slate-400 font-mono block mt-1">Vigencia: {{ $req['startDate'] }} &rarr; {{ $req['endDate'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Formulario de dictamen -->
                                <div class="mt-4 pt-3 border-t border-amber-200/80 flex flex-col md:flex-row items-center justify-between gap-3">
                                    <form action="{{ route('portal.allies.campaign.approve', $req['id']) }}" method="POST" class="w-full md:flex-1 flex flex-wrap items-center gap-2">
                                        @csrf
                                        <div class="flex-1 min-w-[200px]">
                                            <input type="text" name="benefit" value="{{ $req['benefit'] }}" class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-1.5 bg-white focus:outline-none focus:border-emerald-600" title="Ajustar beneficio final si es necesario">
                                        </div>
                                        <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-2xs transition flex items-center gap-1 cursor-pointer">
                                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                            <span>Aprobar &amp; Sincronizar Tienda</span>
                                        </button>
                                    </form>

                                    <form action="{{ route('portal.allies.campaign.reject', $req['id']) }}" method="POST" class="shrink-0" onsubmit="return confirm('¿Confirmas el rechazo de esta solicitud de evento?');">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs font-semibold transition cursor-pointer">
                                            Rechazar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Historial de Todas las Campañas de Aliados (Aprobadas / Finalizadas / Rechazadas) -->
            <div class="border-t border-slate-100 pt-5 mt-6">
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3">Historial General de Eventos &amp; Acuerdos Comercializados</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 uppercase tracking-wider bg-slate-50/50">
                                <th class="p-2.5 font-semibold">Evento / Campaña</th>
                                <th class="p-2.5 font-semibold">Aliado</th>
                                <th class="p-2.5 font-semibold">Universo</th>
                                <th class="p-2.5 font-semibold">Beneficio Pactado</th>
                                <th class="p-2.5 font-semibold">Leads CAPI</th>
                                <th class="p-2.5 font-semibold">Vigencia</th>
                                <th class="p-2.5 font-semibold text-right">Estatus Tienda</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($campaignsDirA as $camp)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="p-2.5 font-bold text-slate-900">
                                        {{ $camp['title'] }}
                                        @if(!empty($camp['rejectionReason']))
                                            <span class="text-[10px] text-rose-600 block font-normal">Motivo rechazo: {{ $camp['rejectionReason'] }}</span>
                                        @endif
                                    </td>
                                    <td class="p-2.5 text-slate-700 font-medium">{{ $camp['allyName'] }}</td>
                                    <td class="p-2.5 uppercase font-mono text-[10px] text-slate-500">{{ $camp['universe'] }}</td>
                                    <td class="p-2.5 text-slate-700">{{ $camp['benefit'] }}</td>
                                    <td class="p-2.5 font-mono font-bold text-indigo-700">{{ $camp['leadsCount'] }} leads</td>
                                    <td class="p-2.5 text-slate-500 font-mono text-[11px]">{{ $camp['startDate'] }} &rarr; {{ $camp['endDate'] }}</td>
                                    <td class="p-2.5 text-right font-sans">
                                        @if($camp['status'] === 'active')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Aprobada &bull; Sincronizada</span>
                                        @elseif($camp['status'] === 'pending_approval')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Pendiente Aprobación</span>
                                        @elseif($camp['status'] === 'rejected')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Rechazada</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">{{ ucfirst($camp['status']) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SUBMÓDULO 3: CAMPAÑAS DIR A & LEAD MAGNET CAPI (TASK-ALY-02) -->
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
                                    @php $allyMatch = collect($alliesList)->firstWhere('id', $c['allyId']); @endphp
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
                                        @php
                                            $cleanClabe = preg_replace('/\D/', '', (string) ($a['bankClabe'] ?? ''));
                                            $hasValidClabe = strlen($cleanClabe) === 18;
                                        @endphp
                                        @if($hasValidClabe)
                                            <button class="button button-quiet text-[11px] py-1 px-2.5 bg-slate-900 text-white rounded shadow-2xs cursor-pointer" data-action="settle-spei-btn" data-ally-id="{{ $a['id'] }}" title="Dispersión SPEI programada para viernes (TASK-INT-19)" type="button">
                                                SPEI
                                            </button>
                                        @else
                                            <button class="button text-[11px] py-1 px-2.5 bg-slate-200 text-slate-400 rounded cursor-not-allowed opacity-60" disabled title="Bloqueado: El socio comercial no tiene una cuenta CLABE de 18 dígitos registrada para dispersión SPEI." type="button">
                                                SPEI
                                            </button>
                                        @endif
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
            @if(empty($selectedAlly['id']))
                <div class="mb-5 p-4 bg-amber-50 border border-amber-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-amber-900">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>No hay aliados comerciales activos registrados en el directorio. Registra tu primer socio comercial para habilitar el Partner Portal y autorizar eventos o beneficios.</span>
                    </div>
                    <button type="button" class="px-3 py-1.5 font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg cursor-pointer shrink-0" onclick="window.dispatchEvent(new CustomEvent('open-module-settings'))">
                        + Alta de Aliado
                    </button>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 mb-5 gap-3">
                <div class="flex items-center gap-3">
                    <button type="button" class="px-2.5 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors cursor-pointer flex items-center gap-1" data-action="switch-allies-tab" data-tab="directory">
                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                        <span>Volver al Directorio</span>
                    </button>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-indigo-600 text-white rounded">Partner Portal</span>
                            <h3 class="text-base font-bold text-slate-900">{{ $selectedAlly['name'] ?? 'Aliado Comercial' }}</h3>
                        </div>
                        <p class="text-xs text-slate-500 font-mono mt-0.5">Token Criptográfico: {{ !empty($selectedAlly['portalToken']) ? $selectedAlly['portalToken'] : 'Sin token activo' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button class="button button-light text-xs py-1.5 px-3 rounded-lg flex items-center gap-1.5 cursor-pointer bg-indigo-50 border border-indigo-200 text-indigo-700 font-bold" onclick="{{ !empty($selectedAlly['id']) ? "document.getElementById('modalNewAllyCampaign').classList.remove('hidden');" : "alert('Primero debes registrar un socio aliado en el Directorio.');" }}" type="button">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>+ Registrar Evento / Beneficio</span>
                    </button>
                    <button class="button button-light text-xs py-1.5 px-3 rounded-lg flex items-center gap-1.5 cursor-pointer" data-action="export-door-list-csv" data-ally-id="{{ $selectedAlly['id'] ?? '' }}" type="button">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span>Descargar CSV Taquilla</span>
                    </button>
                    <button class="button button-quiet text-xs py-1.5 px-3 bg-slate-900 text-white rounded-lg cursor-pointer" data-action="open-qr-modal" data-ally-id="{{ $selectedAlly['id'] ?? '' }}" type="button">
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
            <div class="p-4 bg-indigo-50/50 border border-indigo-100 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs mb-5">
                <div>
                    <span class="font-bold text-indigo-950 block">Enlace de Difusión con Atribución Automática:</span>
                    <code class="text-indigo-800 font-mono text-[11px]">https://mitienda.com/?utm_source={{ $selectedAlly['code'] ?? 'GENERAL' }}&amp;coupon={{ !empty($couponsDirB) && isset($couponsDirB[0]['couponCode']) ? $couponsDirB[0]['couponCode'] : 'PARADISO15' }}</code>
                </div>
                <button class="button button-light text-xs py-1.5 px-3 bg-white text-indigo-900 border-indigo-200 rounded-lg shrink-0 cursor-pointer" data-action="copy-ally-link" type="button">
                    Copiar Enlace
                </button>
            </div>

            <!-- Acuerdos y Beneficios Autorizados para este Aliado -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl text-xs mb-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-bold text-slate-900 block">Acuerdos Comerciales &amp; Tipos de Descuentos Autorizados:</span>
                    <span class="text-[10px] text-slate-500 font-mono">ID Aliado: #{{ $selectedAlly['id'] ?? 'N/A' }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-1.5 mb-2">
                    @foreach($selectedAlly['allowedDiscountTypes'] ?? ['cover', 'cover_2x1', 'free_drink', 'store_discount', 'vip_access'] as $dt)
                        <span class="px-2 py-1 rounded-md text-[11px] font-bold bg-white text-indigo-900 border border-indigo-200 shadow-2xs">
                            {{ match($dt) {
                                'cover' => 'Cover Libre / Entrada Gratuita',
                                'cover_2x1' => 'Cover 2x1 en Taquilla',
                                'free_drink' => 'Trago o Cóctel Cortesía',
                                'store_discount' => 'Descuento en Tienda (Hasta ' . ($selectedAlly['maxDiscountPct'] ?? 20) . '%)',
                                'vip_access' => 'Acceso Preferente / VIP',
                                default => ucfirst(str_replace('_', ' ', $dt))
                            } }}
                        </span>
                    @endforeach
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Solo podrás solicitar convenios para los tipos de beneficios configurados en tu acuerdo comercial.
                    Comisión en ventas referidas (Dir B): <strong class="text-slate-800 font-mono">{{ $selectedAlly['commissionRate'] }}%</strong> &bull;
                    Crédito por Lead (Dir A): <strong class="text-slate-800 font-mono">${{ number_format($selectedAlly['leadAcquisitionCost'], 2) }} MXN</strong>.
                </p>
            </div>

            <!-- Mis Solicitudes de Eventos & Estado de Aprobación -->
            <div class="pt-5 border-t border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Mis Solicitudes de Eventos &amp; Estado de Aprobación</h4>
                        <p class="text-[11px] text-slate-500">Historial de solicitudes creadas desde este portal para autorización comercial.</p>
                    </div>
                    <button class="button button-light text-xs py-1.5 px-3 rounded-lg flex items-center gap-1.5 cursor-pointer bg-indigo-50 border border-indigo-200 text-indigo-700 font-bold" onclick="document.getElementById('modalNewAllyCampaign').classList.remove('hidden');" type="button">
                        + Solicitar Evento
                    </button>
                </div>

                @php
                $myCampaigns = collect($campaignsDirA)->where('allyId', $selectedAlly['id'])->values();
                @endphp

                @if($myCampaigns->isEmpty())
                    <div class="text-center py-8 bg-slate-50 border border-dashed border-slate-200 rounded-xl">
                        <p class="text-xs text-slate-500">Aún no has enviado solicitudes de eventos o beneficios para este aliado.</p>
                        <button class="mt-2 text-xs font-bold text-indigo-600 hover:text-indigo-800 cursor-pointer" onclick="document.getElementById('modalNewAllyCampaign').classList.remove('hidden');" type="button">Crear Primera Solicitud &rarr;</button>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($myCampaigns as $c)
                            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-3">
                                    @if(!empty($c['bannerImage']) && $c['bannerImage'] !== 'default_campaign.webp')
                                        <img src="{{ $c['bannerImage'] }}" alt="{{ $c['title'] }}" class="w-12 h-14 object-cover rounded-lg shrink-0 border border-slate-200">
                                    @else
                                        <div class="w-12 h-14 bg-slate-200 rounded-lg shrink-0 flex items-center justify-center text-slate-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    @endif
                                    <div>
                                        <h5 class="font-bold text-slate-900">{{ $c['title'] }}</h5>
                                        <p class="text-[11px] text-slate-600 font-medium">{{ $c['benefit'] }}</p>
                                        <span class="text-[10px] text-slate-400 font-mono">Vigencia: {{ $c['startDate'] }} al {{ $c['endDate'] }}</span>
                                    </div>
                                </div>
                                <div class="sm:text-right shrink-0">
                                    @if($c['status'] === 'active')
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 block">
                                            Aprobado &amp; Publicado en Tienda
                                        </span>
                                    @elseif($c['status'] === 'pending_approval')
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-300 block">
                                            En Revisión por Equipo Comercial
                                        </span>
                                    @elseif($c['status'] === 'rejected')
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200 block">
                                            Rechazado
                                        </span>
                                        @if(!empty($c['rejectionReason']))
                                            <span class="text-[10px] text-rose-600 block mt-0.5">{{ $c['rejectionReason'] }}</span>
                                        @endif
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 block">
                                            {{ ucfirst($c['status']) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tipo de Descuento / Beneficio Autorizado *</label>
                    <select name="discount_type" required class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-slate-50 font-semibold focus:bg-white">
                        @php
                            $allowed = $selectedAlly['allowedDiscountTypes'] ?? ['cover', 'cover_2x1', 'free_drink', 'store_discount', 'vip_access'];
                        @endphp
                        @foreach($allowed as $dtKey)
                            @php
                                $matchedDt = isset($discountTypes) ? $discountTypes->firstWhere('code', $dtKey) : null;
                                $dtLabel = $matchedDt ? $matchedDt->name : match($dtKey) {
                                    'cover' => 'Cover / Acceso Libre (100% cortesía)',
                                    'cover_2x1' => 'Cover 2x1 en Puerta',
                                    'free_drink' => 'Trago o Cóctel de Cortesía',
                                    'store_discount' => '% Descuento en Tienda Online (Tope ' . ($selectedAlly['maxDiscountPct'] ?? 20) . '%)',
                                    'vip_access' => 'Acceso VIP / Pulsera Exclusiva',
                                    'service_promo' => 'Promoción Especial en Servicio',
                                    default => ucfirst(str_replace('_', ' ', $dtKey))
                                };
                            @endphp
                            <option value="{{ $dtKey }}">{{ $dtLabel }}</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-500 mt-1">Solo puedes solicitar convenios dentro de los tipos de beneficios autorizados en tu acuerdo comercial.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Descripción del Beneficio o Descuento Propuesto *</label>
                    <input type="text" name="benefit" required class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-slate-50 focus:bg-white" placeholder="Ej. 15% OFF en taquilla y cóctel de bienvenida">
                </div>
                <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-lg text-[11px] text-amber-900 leading-relaxed">
                    <strong>POLÍTICA DE ALIADOS:</strong> Los socios únicamente son capaces de solicitar la dada de alta de su evento o promoción. Al enviar, la solicitud quedará en estado <span class="font-bold underline">Pendiente de Autorización</span> hasta ser validada por el Superadministrador en Hub 7.
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

{{-- MODAL DE CONFIGURACIÓN DEL MÓDULO (ENGRANAJE NAVBAR) - GESTIÓN DE ACUERDOS COMERCIALES Y DIRECTORIO DE ALIADOS --}}
@php
    $currentUser = auth()->user();
    $isSuperadmin = $currentUser && in_array($currentUser->role, ['superadmin', 'admin', 'portal', 'all_roles'], true);
@endphp

<div id="alliesModuleSettingsModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6 hidden">
    <div class="bg-white rounded-3xl max-w-5xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden">
        <!-- Modal Header -->
        <div class="p-5 sm:p-6 bg-slate-900 text-white flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-white tracking-wide">Configuración de Acuerdos Comerciales &amp; Directorio de Aliados (Hub 7)</h3>
                        @if($isSuperadmin)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-400 text-slate-950 uppercase font-mono">Superadministrador</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500 text-white uppercase font-mono">Restringido</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Gestión de alta manual de socios, condiciones de compensación Dir A &amp; B y tipos de descuentos autorizados.</p>
                </div>
            </div>
            <button type="button" class="text-slate-400 hover:text-white p-2 rounded-lg hover:bg-slate-800 transition cursor-pointer" onclick="document.getElementById('alliesModuleSettingsModal').classList.add('hidden');">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        @if(!$isSuperadmin)
            <!-- Vista restringida para usuarios sin rol superadministrador -->
            <div class="p-8 text-center bg-rose-50 border-t border-rose-100 flex-1 flex flex-col items-center justify-center">
                <div class="w-16 h-16 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m4-8a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <h4 class="text-base font-bold text-rose-950 mb-1">Acceso Exclusivo para Rol Superadministrador</h4>
                <p class="text-xs text-rose-700 max-w-md mx-auto leading-relaxed">
                    El directorio de aliados comerciales, asignación de tarifas de compensación y políticas de descuentos solo están disponibles desde el engranaje del navbar para usuarios con rango de <strong>Superadministrador</strong>.
                </p>
                <button type="button" class="mt-5 px-5 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition cursor-pointer" onclick="document.getElementById('alliesModuleSettingsModal').classList.add('hidden');">
                    Entendido, Cerrar
                </button>
            </div>
        @else
            <!-- Subtabs de Configuración -->
            <div class="bg-slate-100 border-b border-slate-200 px-6 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <button type="button" id="tabBtnAllyForm" class="py-3 px-4 text-xs font-bold border-b-2 border-indigo-600 text-indigo-700 cursor-pointer flex items-center gap-1.5" onclick="window.switchAllySettingsTab('form')">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span id="tabLabelAllyForm">1. Alta / Edición de Aliado Comercial</span>
                    </button>
                    <button type="button" id="tabBtnAllyList" class="py-3 px-4 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 cursor-pointer flex items-center gap-1.5" onclick="window.switchAllySettingsTab('list')">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        <span>2. Directorio &amp; Acuerdos Vigentes ({{ count($alliesList) }})</span>
                    </button>
                </div>
                <span class="text-[11px] font-mono text-slate-500 hidden sm:inline">Políticas Inmutables B2B</span>
            </div>

            <!-- Contenido con scroll -->
            <div class="p-6 overflow-y-auto flex-1 bg-white">
                <!-- PANEL 1: Formulario Alta / Edición (Funciona igual que alta manual de proveedores de Hub 4) -->
                <div id="panelAllySettingsForm">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-5">
                        <div class="flex items-center gap-2">
                            <span id="allyFormModeBadge" class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                Modo: Alta de Nuevo Aliado
                            </span>
                            <span class="text-xs text-slate-400">Igual que dar de alta a proveedores manualmente en Hub 4</span>
                        </div>
                        <button type="button" id="btnResetAllyForm" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold cursor-pointer underline" onclick="window.resetAllyForm()">
                            + Dar de alta otro socio
                        </button>
                    </div>

                    <form id="allyMasterForm" action="{{ route('portal.allies.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="_method" id="allyFormMethod" value="POST">
                        <input type="hidden" id="allyEditId" value="">

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Columna Izquierda: Datos del Aliado -->
                            <div class="space-y-4 bg-slate-50/60 p-4 rounded-2xl border border-slate-200/80">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span>Datos Generales de la Entidad</span>
                                </h4>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Nombre Comercial del Aliado *</label>
                                    <input type="text" name="name" id="allyFormName" required class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:border-indigo-600" placeholder="Ej. Club Sauna Paraíso / DJ Velvet / Circuit Festival">
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Código de Aliado</label>
                                        <input type="text" name="code" id="allyFormCode" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white font-mono uppercase" placeholder="Ej. AL-PARADISO (Opcional)">
                                        <span class="text-[10px] text-slate-400">Se autogenera si se deja vacío</span>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Universo Afín *</label>
                                        <select name="universe" id="allyFormUniverse" required class="w-full text-xs border border-slate-300 rounded-lg px-2 py-2 bg-white font-semibold">
                                            <option value="marte">Marte (Masculino)</option>
                                            <option value="venus">Venus (Femenino / Lencería)</option>
                                            <option value="tiresias">Tiresias (Afirmativo)</option>
                                            <option value="terra">Terra (Global)</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Tipo de Aliado / Categoría *</label>
                                    <select name="category" id="allyFormCategory" required class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white font-semibold focus:outline-none focus:border-indigo-600">
                                        @forelse($activeAllyTypes as $at)
                                            <option value="{{ $at->code }}">{{ $at->name }}</option>
                                        @empty
                                            <option value="sauna">Sauna &amp; Baños de Vapor</option>
                                            <option value="spa">Spa &amp; Masajes Wellness</option>
                                            <option value="content_creator">Creador de Contenido / OnlyFans / Influencer</option>
                                            <option value="party_producer">Productor de Fiestas / Festival Circuit</option>
                                            <option value="drag_ballroom">Colectivo Drag &amp; Ballroom Culture</option>
                                            <option value="gym_fitness">Fitness &amp; Gimnasio Especializado</option>
                                            <option value="bar_nightclub">Bar / Nightclub / Discoteca</option>
                                            <option value="barbershop">Barbería &amp; Grooming</option>
                                            <option value="other">Otro Establecimiento o Alianza</option>
                                        @endforelse
                                    </select>
                                    <span class="text-[10px] text-slate-500 mt-0.5 block">Define el giro del socio para segmentación de acuerdos y vitrinas. Administrable en pestaña "3. Tipos de Aliado".</span>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Nombre de Contacto</label>
                                        <input type="text" name="contact_name" id="allyFormContactName" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white" placeholder="Ej. Roberto Sánchez">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">WhatsApp de Contacto</label>
                                        <input type="text" name="contact_whatsapp" id="allyFormContactWhatsapp" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white font-mono" placeholder="+52 55 1234 5678">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Notificaciones</label>
                                        <input type="email" name="contact_email" id="allyFormContactEmail" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white" placeholder="contacto@aliado.com">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Redes / Handle Social</label>
                                        <input type="text" name="social_handle" id="allyFormSocialHandle" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white font-mono" placeholder="@usuario_oficial">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Notas Comerciales / Acuerdo de Alianza</label>
                                    <textarea name="notes" id="allyFormNotes" rows="2" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white" placeholder="Detalles particulares del convenio, acuerdos verbales o requerimientos especiales."></textarea>
                                </div>
                            </div>

                            <!-- Columna Derecha: Reglas de Compensación & Descuentos -->
                            <div class="space-y-4 bg-slate-50/60 p-4 rounded-2xl border border-slate-200/80">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Reglas de Compensación &amp; Descuentos Autorizados</span>
                                </h4>

                                <!-- Regla Dirección A -->
                                <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-xs font-bold text-indigo-900">Tarifa Crédito por Lead CAPI (Dirección A):</label>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700">Web &rarr; Aliado</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-500 font-mono">MXN $</span>
                                        <input type="number" step="0.01" min="0" name="lead_acquisition_cost" id="allyFormLeadAcquisitionCost" value="20.00" required class="w-full text-xs border border-slate-300 rounded-lg px-3 py-1.5 bg-slate-50 font-mono font-bold focus:bg-white">
                                    </div>
                                    <p class="text-[10px] text-slate-500 leading-tight">Monto que el aliado abona en créditos al recibir prospectos calificados con voucher emitido desde el pop-up o vitrina de convenios.</p>
                                </div>

                                <!-- Regla Dirección B -->
                                <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-xs font-bold text-indigo-900">Comisión por Ventas Referidas (Dirección B):</label>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700">Aliado &rarr; Web</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <select name="commission_type" id="allyFormCommissionType" class="w-full text-xs border border-slate-300 rounded-lg px-2 py-1.5 bg-slate-50 font-semibold">
                                                <option value="percentage">Porcentaje (%)</option>
                                                <option value="fixed">Monto Fijo ($ MXN)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <input type="number" step="0.01" min="0" name="commission_rate" id="allyFormCommissionRate" value="10.00" required class="w-full text-xs border border-slate-300 rounded-lg px-3 py-1.5 bg-slate-50 font-mono font-bold focus:bg-white" placeholder="10.00">
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-slate-500 leading-tight">Comisión devengada a favor del aliado por cada orden efectiva pagada en tienda con su cupón determinista UTM.</p>
                                </div>

                                <!-- Tipos de Descuentos que el Aliado Puede Configurar desde su Portal -->
                                <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-xs font-bold text-slate-900">Tipos de Descuentos Autorizados en su Portal:</label>
                                        <span class="text-[10px] text-slate-400">Administrable en pestaña 4</span>
                                    </div>

                                    <!-- Grupo Dirección A: Beneficios Aliado -> Cliente -->
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700">Dirección A (Eventos / Aliado a Cliente)</span>
                                            <span class="text-[9px] text-slate-400">Canjeables en taquilla/local</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-1.5 text-xs">
                                            @forelse($activeDiscountTypesDirA as $dt)
                                                <label class="flex items-center gap-2 p-1.5 rounded hover:bg-indigo-50/50 cursor-pointer border border-slate-100">
                                                    <input type="checkbox" name="allowed_discount_types[]" value="{{ $dt->code }}" class="rounded text-indigo-600 focus:ring-0 ally-dt-checkbox" checked>
                                                    <span class="text-slate-700 font-medium text-[11px] truncate" title="{{ $dt->name }}">{{ $dt->name }}</span>
                                                </label>
                                            @empty
                                                <label class="flex items-center gap-2 p-1.5 rounded hover:bg-slate-50 cursor-pointer">
                                                    <input type="checkbox" name="allowed_discount_types[]" value="cover" class="rounded text-indigo-600 focus:ring-0 ally-dt-checkbox" checked>
                                                    <span class="text-slate-700 font-medium text-[11px]">Cover Libre</span>
                                                </label>
                                                <label class="flex items-center gap-2 p-1.5 rounded hover:bg-slate-50 cursor-pointer">
                                                    <input type="checkbox" name="allowed_discount_types[]" value="cover_2x1" class="rounded text-indigo-600 focus:ring-0 ally-dt-checkbox" checked>
                                                    <span class="text-slate-700 font-medium text-[11px]">Cover 2x1</span>
                                                </label>
                                                <label class="flex items-center gap-2 p-1.5 rounded hover:bg-slate-50 cursor-pointer">
                                                    <input type="checkbox" name="allowed_discount_types[]" value="free_drink" class="rounded text-indigo-600 focus:ring-0 ally-dt-checkbox" checked>
                                                    <span class="text-slate-700 font-medium text-[11px]">Trago Cortesía</span>
                                                </label>
                                                <label class="flex items-center gap-2 p-1.5 rounded hover:bg-slate-50 cursor-pointer">
                                                    <input type="checkbox" name="allowed_discount_types[]" value="vip_access" class="rounded text-indigo-600 focus:ring-0 ally-dt-checkbox" checked>
                                                    <span class="text-slate-700 font-medium text-[11px]">Acceso VIP</span>
                                                </label>
                                            @endforelse
                                        </div>
                                    </div>

                                    <!-- Grupo Dirección B: Descuentos Tienda -> Audiencia Aliado -->
                                    <div class="pt-2 border-t border-slate-100">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Dirección B (Tienda Online a Audiencia)</span>
                                            <span class="text-[9px] text-slate-400">Cupones de ecommerce</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-1.5 text-xs">
                                            @forelse($activeDiscountTypesDirB as $dt)
                                                <label class="flex items-center gap-2 p-1.5 rounded hover:bg-emerald-50/50 cursor-pointer border border-slate-100">
                                                    <input type="checkbox" name="allowed_discount_types[]" value="{{ $dt->code }}" class="rounded text-emerald-600 focus:ring-0 ally-dt-checkbox" checked>
                                                    <span class="text-slate-700 font-medium text-[11px] truncate" title="{{ $dt->name }}">{{ $dt->name }}</span>
                                                </label>
                                            @empty
                                                <label class="flex items-center gap-2 p-1.5 rounded hover:bg-slate-50 cursor-pointer">
                                                    <input type="checkbox" name="allowed_discount_types[]" value="store_discount" class="rounded text-emerald-600 focus:ring-0 ally-dt-checkbox" checked>
                                                    <span class="text-slate-700 font-medium text-[11px]">% Descuento Tienda</span>
                                                </label>
                                            @endforelse
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-3">
                                        <label class="text-[11px] font-bold text-slate-700 shrink-0">Tope Máx. Descuento Tienda (%):</label>
                                        <input type="number" step="0.5" min="0" max="100" name="max_discount_pct" id="allyFormMaxDiscountPct" value="20.00" class="w-24 text-xs border border-slate-300 rounded px-2 py-1 bg-slate-50 font-mono font-bold text-right">
                                    </div>
                                </div>

                                <!-- Datos Bancarios & Estatus -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Banco Receptor (SPEI)</label>
                                        <input type="text" name="bank_name" id="allyFormBankName" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-1.5 bg-white" placeholder="Ej. BBVA">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Estatus</label>
                                        <select name="status" id="allyFormStatus" class="w-full text-xs border border-slate-300 rounded-lg px-2 py-1.5 bg-white font-semibold">
                                            <option value="active">Activo (Habilitado)</option>
                                            <option value="archived">Archivado</option>
                                            <option value="paused">Pausado</option>
                                            <option value="inactive">Inactivo</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Cuenta CLABE Interbancaria (18 dígitos)</label>
                                    <input type="text" maxlength="18" name="bank_clabe" id="allyFormBankClabe" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-1.5 bg-white font-mono" placeholder="012180000000000000">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                            <button type="button" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 cursor-pointer" onclick="document.getElementById('alliesModuleSettingsModal').classList.add('hidden');">
                                Cancelar
                            </button>
                            <button type="submit" id="allyFormSubmitBtn" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span id="allyFormSubmitBtnLabel">Guardar Acuerdo Comercial &amp; Alta de Aliado</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- PANEL 2: Directorio & Acuerdos Activos (Resumen y Edición Rápida) -->
                <div id="panelAllySettingsList" class="hidden space-y-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Directorio de Acuerdos Comerciales Registrados</h4>
                            <p class="text-xs text-slate-500">Selecciona cualquier aliado para editar sus condiciones, porcentajes o tipos de descuentos.</p>
                        </div>
                        <button type="button" class="px-3 py-1.5 text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg cursor-pointer" onclick="window.resetAllyForm(); window.switchAllySettingsTab('form');">
                            + Nuevo Aliado
                        </button>
                    </div>

                    <div class="overflow-x-auto border border-slate-200 rounded-xl">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-400 uppercase tracking-wider bg-slate-50/70">
                                    <th class="p-3 font-semibold">Aliado &amp; Código</th>
                                    <th class="p-3 font-semibold">Giro / Universo</th>
                                    <th class="p-3 font-semibold">Reglas Compensación</th>
                                    <th class="p-3 font-semibold">Descuentos Autorizados</th>
                                    <th class="p-3 font-semibold">Saldo Cámara</th>
                                    <th class="p-3 font-semibold text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($alliesList as $al)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="p-3">
                                            <strong class="text-slate-900 block">{{ $al['name'] }}</strong>
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $al['code'] }} &bull; {{ $al['socialHandle'] }}</span>
                                        </td>
                                        <td class="p-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">{{ $al['categoryLabel'] }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono uppercase block mt-0.5">{{ $al['universe'] }}</span>
                                        </td>
                                        <td class="p-3 font-mono text-[11px]">
                                            <span class="text-indigo-700 font-bold block">Dir A: ${{ number_format($al['leadAcquisitionCost'], 2) }} /lead</span>
                                            <span class="text-slate-700 font-medium block">Dir B: {{ $al['commissionRate'] }}% ventas</span>
                                        </td>
                                        <td class="p-3">
                                            <div class="flex flex-wrap gap-1 max-w-[200px]">
                                                @foreach($al['allowedDiscountTypes'] ?? [] as $dt)
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                        {{ match($dt) {
                                                            'cover' => 'Cover',
                                                            'cover_2x1' => '2x1',
                                                            'free_drink' => 'Trago',
                                                            'store_discount' => 'Tienda',
                                                            'vip_access' => 'VIP',
                                                            default => $dt
                                                        } }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="p-3 font-mono font-bold text-xs {{ $al['clearingBalance'] >= 0 ? 'text-emerald-700' : 'text-amber-700' }}">
                                            ${{ number_format($al['clearingBalance'], 2) }}
                                        </td>
                                        <td class="p-3 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="button" class="px-2.5 py-1 text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition cursor-pointer" data-action="edit-ally-btn" data-ally="{{ json_encode($al) }}">
                                                    Editar Acuerdos
                                                </button>
                                                <form action="{{ route('portal.allies.archive', $al['id']) }}" method="POST" class="inline" onsubmit="return confirm('{{ ($al['isArchived'] ?? false) ? '¿Reactivar socio?' : '¿Archivar socio? Sus campañas y cupones serán pausados.' }}');">
                                                    @csrf
                                                    <button type="submit" class="px-2 py-1 text-xs font-semibold {{ ($al['isArchived'] ?? false) ? 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200' : 'text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200' }} rounded-lg transition cursor-pointer">
                                                        {{ ($al['isArchived'] ?? false) ? 'Reactivar' : 'Archivar' }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('portal.allies.destroy', $al['id']) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar socio {{ $al['name'] }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-2 py-1 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition cursor-pointer" title="Eliminar">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-8 text-center text-slate-500 text-xs">
                                            No hay aliados comerciales registrados en el directorio. Utiliza la pestaña "1. Alta / Edición de Aliado Comercial" para registrar a tu primer socio comercial.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PANEL 3: Catálogo Maestro de Tipos de Aliado Comercial -->
                <div id="panelAllySettingsTypes" class="hidden space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                <span>Tipos de Aliado Comercial &amp; Categorías</span>
                            </h4>
                            <p class="text-xs text-slate-500 mt-0.5">Define las clasificaciones de socios comerciales (ej. Sauna, Spa, Creador de Contenido, Festival, etc.) para segmentar convenios y portales.</p>
                        </div>
                        <button type="button" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold cursor-pointer underline" onclick="window.resetAllyTypeForm()">
                            + Nuevo Tipo de Aliado
                        </button>
                    </div>

                    <!-- Formulario de Alta / Edición de Tipo de Aliado -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200">
                        <div class="flex items-center justify-between mb-3">
                            <span id="allyTypeFormBadge" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800">
                                Modo: Crear Nuevo Tipo
                            </span>
                            <button type="button" class="text-[11px] text-slate-500 hover:text-slate-700" onclick="window.resetAllyTypeForm()">Limpiar</button>
                        </div>
                        <form id="allyTypeMasterForm" action="{{ route('portal.allies.types.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="_method" id="allyTypeFormMethod" value="POST">
                            <input type="hidden" id="allyTypeEditId" value="">

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Nombre del Tipo / Giro *</label>
                                    <input type="text" name="name" id="allyTypeName" required class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white" placeholder="Ej. Sauna &amp; Vapor, Spa Wellness">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Código Identificador (Slug)</label>
                                    <input type="text" name="code" id="allyTypeCode" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white font-mono lowercase" placeholder="Ej. sauna (Opcional, autogenerado)">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Universo Afín Sugerido *</label>
                                    <select name="default_universe" id="allyTypeUniverse" class="w-full text-xs border border-slate-300 rounded-lg px-2 py-2 bg-white font-semibold">
                                        <option value="marte">Marte (Masculino)</option>
                                        <option value="venus">Venus (Femenino / Lencería)</option>
                                        <option value="tiresias">Tiresias (Afirmativo)</option>
                                        <option value="terra">Terra (Global)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-center">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Descripción del Giro o Perfil</label>
                                    <input type="text" name="description" id="allyTypeDescription" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white" placeholder="Breve descripción del perfil del socio y beneficios mutuos">
                                </div>
                                <div class="flex items-center gap-4 pt-4 md:pt-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="is_active" id="allyTypeIsActive" value="1" class="rounded text-indigo-600 focus:ring-0" checked>
                                        <span class="text-xs font-bold text-slate-700">Habilitado</span>
                                    </label>
                                    <button type="submit" id="allyTypeSubmitBtn" class="ml-auto px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition cursor-pointer">
                                        <span id="allyTypeSubmitBtnLabel">Guardar Tipo de Aliado</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Tabla de Tipos Registrados -->
                    <div class="overflow-x-auto border border-slate-200 rounded-xl">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-400 uppercase tracking-wider bg-slate-50/70">
                                    <th class="p-3 font-semibold">Tipo &amp; Código</th>
                                    <th class="p-3 font-semibold">Universo</th>
                                    <th class="p-3 font-semibold">Descripción</th>
                                    <th class="p-3 font-semibold">Estatus</th>
                                    <th class="p-3 font-semibold text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($allyTypes as $at)
                                    <tr class="hover:bg-slate-50/60 transition-colors {{ $at->trashed() ? 'opacity-60 bg-slate-50' : '' }}">
                                        <td class="p-3">
                                            <strong class="text-slate-900 block font-semibold">{{ $at->name }}</strong>
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $at->code }}</span>
                                        </td>
                                        <td class="p-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase font-mono">{{ $at->default_universe ?? 'marte' }}</span>
                                        </td>
                                        <td class="p-3 text-slate-600 max-w-xs truncate" title="{{ $at->description }}">
                                            {{ $at->description ?: 'Sin descripción adicional' }}
                                        </td>
                                        <td class="p-3">
                                            <div class="flex flex-col gap-1 items-start">
                                                @if($at->trashed())
                                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-rose-100 text-rose-800">Eliminado (Papelera)</span>
                                                @elseif($at->status === 'archived')
                                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800">Archivado</span>
                                                @elseif($at->is_active)
                                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">Activo</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-slate-200 text-slate-700">Inactivo</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-3 text-right">
                                            <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                                @if($at->trashed())
                                                    <form action="{{ route('portal.allies.types.restore', $at->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-2 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg cursor-pointer">
                                                            Restaurar
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="px-2 py-1 text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-lg cursor-pointer" data-action="edit-ally-type-btn" data-type="{{ json_encode($at) }}">
                                                        Editar
                                                    </button>
                                                    <form action="{{ route('portal.allies.types.toggle', $at->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-2 py-1 text-xs font-semibold {{ $at->is_active ? 'text-slate-600 bg-slate-100 hover:bg-slate-200' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200' }} rounded-lg cursor-pointer">
                                                            {{ $at->is_active ? 'Desactivar' : 'Activar' }}
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('portal.allies.types.archive', $at->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-2 py-1 text-xs font-semibold {{ $at->status === 'archived' ? 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' : 'text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200' }} rounded-lg cursor-pointer">
                                                            {{ $at->status === 'archived' ? 'Reactivar' : 'Archivar' }}
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('portal.allies.types.destroy', $at->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar tipo de aliado {{ $at->name }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="px-2 py-1 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg cursor-pointer">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-6 text-center text-slate-500 text-xs">
                                            No hay tipos de aliado registrados. Utiliza el formulario superior para registrar categorías.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PANEL 4: Catálogo de Tipos de Descuento (Dirección A & B) -->
                <div id="panelAllySettingsDiscounts" class="hidden space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                <span>Tipos de Descuento &amp; Beneficios Comerciales (Dirección A &amp; B)</span>
                            </h4>
                            <p class="text-xs text-slate-500 mt-0.5">Control de beneficios que los aliados ofrecen a clientes de la tienda (Dirección A: cover, 2x1, tragos) y cupones de descuento en tienda online (Dirección B).</p>
                        </div>
                        <button type="button" class="text-xs text-emerald-600 hover:text-emerald-800 font-bold cursor-pointer underline" onclick="window.resetDiscountTypeForm()">
                            + Nuevo Tipo de Descuento
                        </button>
                    </div>

                    <!-- Formulario de Alta / Edición de Tipo de Descuento -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200">
                        <div class="flex items-center justify-between mb-3">
                            <span id="discountTypeFormBadge" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                Modo: Crear Nuevo Descuento
                            </span>
                            <button type="button" class="text-[11px] text-slate-500 hover:text-slate-700" onclick="window.resetDiscountTypeForm()">Limpiar</button>
                        </div>
                        <form id="discountTypeMasterForm" action="{{ route('portal.allies.discounts.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="_method" id="discountTypeFormMethod" value="POST">
                            <input type="hidden" id="discountTypeEditId" value="">

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Nombre del Descuento / Beneficio *</label>
                                    <input type="text" name="name" id="discountTypeName" required class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white" placeholder="Ej. Cover 2x1, 15% Descuento Tienda">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Código Identificador (Slug)</label>
                                    <input type="text" name="code" id="discountTypeCode" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white font-mono lowercase" placeholder="Ej. cover_2x1 (Opcional)">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Dirección del Convenio *</label>
                                    <select name="direction" id="discountTypeDirection" required class="w-full text-xs border border-slate-300 rounded-lg px-2 py-2 bg-white font-semibold">
                                        <option value="dir_a">Dirección A (Aliado &rarr; Clientes Tienda)</option>
                                        <option value="dir_b">Dirección B (Tienda Online &rarr; Audiencia Aliado)</option>
                                        <option value="both">Ambas Direcciones (A &amp; B)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Naturaleza del Valor *</label>
                                    <select name="value_type" id="discountTypeValueType" required class="w-full text-xs border border-slate-300 rounded-lg px-2 py-2 bg-white font-semibold">
                                        <option value="percentage">Porcentaje (%)</option>
                                        <option value="fixed_amount">Monto Fijo ($ MXN)</option>
                                        <option value="free_item">Cortesía / Obsequio</option>
                                        <option value="access">Acceso VIP / Pulsera</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-center">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Tope Máx. / Valor Defecto</label>
                                    <input type="number" step="0.5" min="0" max="1000" name="max_value_default" id="discountTypeMaxValue" value="20.00" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white font-mono font-bold" placeholder="20.00">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Descripción Operativa</label>
                                    <input type="text" name="description" id="discountTypeDescription" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white" placeholder="Condiciones del beneficio o cupón">
                                </div>
                                <div class="flex items-center gap-4 pt-4 md:pt-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="is_active" id="discountTypeIsActive" value="1" class="rounded text-emerald-600 focus:ring-0" checked>
                                        <span class="text-xs font-bold text-slate-700">Activo</span>
                                    </label>
                                    <button type="submit" id="discountTypeSubmitBtn" class="ml-auto px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition cursor-pointer">
                                        <span id="discountTypeSubmitBtnLabel">Guardar Tipo de Descuento</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Tabla de Descuentos Registrados -->
                    <div class="overflow-x-auto border border-slate-200 rounded-xl">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-400 uppercase tracking-wider bg-slate-50/70">
                                    <th class="p-3 font-semibold">Descuento &amp; Código</th>
                                    <th class="p-3 font-semibold">Dirección</th>
                                    <th class="p-3 font-semibold">Tipo Valor &amp; Tope</th>
                                    <th class="p-3 font-semibold">Descripción</th>
                                    <th class="p-3 font-semibold">Estatus</th>
                                    <th class="p-3 font-semibold text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($discountTypes as $dt)
                                    <tr class="hover:bg-slate-50/60 transition-colors {{ $dt->trashed() ? 'opacity-60 bg-slate-50' : '' }}">
                                        <td class="p-3">
                                            <strong class="text-slate-900 block font-semibold">{{ $dt->name }}</strong>
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $dt->code }}</span>
                                        </td>
                                        <td class="p-3">
                                            @if($dt->direction === 'dir_a')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">Dirección A (Aliado)</span>
                                            @elseif($dt->direction === 'dir_b')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Dirección B (Tienda)</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">Ambas (A &amp; B)</span>
                                            @endif
                                        </td>
                                        <td class="p-3 font-mono text-[11px]">
                                            <span class="font-bold text-slate-800 block">{{ $dt->value_type_label }}</span>
                                            @if($dt->max_value_default > 0)
                                                <span class="text-slate-500 text-[10px]">Tope: {{ $dt->max_value_default }}{{ $dt->value_type === 'percentage' ? '%' : ' MXN' }}</span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-slate-600 max-w-xs truncate" title="{{ $dt->description }}">
                                            {{ $dt->description ?: 'Sin notas' }}
                                        </td>
                                        <td class="p-3">
                                            <div class="flex flex-col gap-1 items-start">
                                                @if($dt->trashed())
                                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-rose-100 text-rose-800">Eliminado (Papelera)</span>
                                                @elseif($dt->status === 'archived')
                                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800">Archivado</span>
                                                @elseif($dt->is_active)
                                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">Activo</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-slate-200 text-slate-700">Inactivo</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-3 text-right">
                                            <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                                @if($dt->trashed())
                                                    <form action="{{ route('portal.allies.discounts.restore', $dt->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-2 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg cursor-pointer">
                                                            Restaurar
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="px-2 py-1 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg cursor-pointer" data-action="edit-discount-type-btn" data-discount="{{ json_encode($dt) }}">
                                                        Editar
                                                    </button>
                                                    <form action="{{ route('portal.allies.discounts.toggle', $dt->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-2 py-1 text-xs font-semibold {{ $dt->is_active ? 'text-slate-600 bg-slate-100 hover:bg-slate-200' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200' }} rounded-lg cursor-pointer">
                                                            {{ $dt->is_active ? 'Desactivar' : 'Activar' }}
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('portal.allies.discounts.archive', $dt->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-2 py-1 text-xs font-semibold {{ $dt->status === 'archived' ? 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' : 'text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200' }} rounded-lg cursor-pointer">
                                                            {{ $dt->status === 'archived' ? 'Reactivar' : 'Archivar' }}
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('portal.allies.discounts.destroy', $dt->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar tipo de descuento {{ $dt->name }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="px-2 py-1 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg cursor-pointer">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-6 text-center text-slate-500 text-xs">
                                            No hay tipos de descuento registrados. Utiliza el formulario superior para registrar promociones y beneficios.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- SCRIPT REACTIVO OPERATIVO PARA CONVENIOS Y ALIADOS (VANILLA JS LIGERO) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const panels = {
        'directory': document.getElementById('alliesDirectoryPanel'),
        'requests': document.getElementById('alliesRequestsPanel'),
        'campaigns_a': document.getElementById('alliesCampaignsPanel'),
        'attribution_b': document.getElementById('alliesAttributionPanel'),
        'clearing': document.getElementById('alliesClearingPanel'),
        'partner_portal': document.getElementById('alliesPartnerPortalPanel')
    };

    // Subtabs de configuración de módulo (Engranaje)
    window.switchAllySettingsTab = function(target) {
        const panels = {
            'form': document.getElementById('panelAllySettingsForm'),
            'list': document.getElementById('panelAllySettingsList'),
            'types': document.getElementById('panelAllySettingsTypes'),
            'discounts': document.getElementById('panelAllySettingsDiscounts')
        };
        const buttons = {
            'form': document.getElementById('tabBtnAllyForm'),
            'list': document.getElementById('tabBtnAllyList'),
            'types': document.getElementById('tabBtnAllyTypes'),
            'discounts': document.getElementById('tabBtnDiscountTypes')
        };

        Object.keys(panels).forEach(key => {
            if (!panels[key]) return;
            if (key === target) {
                panels[key].classList.remove('hidden');
            } else {
                panels[key].classList.add('hidden');
            }
        });

        Object.keys(buttons).forEach(key => {
            if (!buttons[key]) return;
            if (key === target) {
                buttons[key].classList.add('border-indigo-600', 'text-indigo-700');
                buttons[key].classList.remove('border-transparent', 'text-slate-500');
            } else {
                buttons[key].classList.remove('border-indigo-600', 'text-indigo-700');
                buttons[key].classList.add('border-transparent', 'text-slate-500');
            }
        });
    };

    window.populateAllyFormForEdit = function(ally) {
        const form = document.getElementById('allyMasterForm');
        if (!form) return;

        form.action = `/admin/convenios/aliados/${ally.id}`;
        const methodInput = document.getElementById('allyFormMethod');
        if (methodInput) methodInput.value = 'PUT';
        
        const editIdInput = document.getElementById('allyEditId');
        if (editIdInput) editIdInput.value = ally.id;

        const modeBadge = document.getElementById('allyFormModeBadge');
        if (modeBadge) modeBadge.textContent = 'Modo: Editando a ' + (ally.name || 'Aliado');

        const tabLabel = document.getElementById('tabLabelAllyForm');
        if (tabLabel) tabLabel.textContent = '1. Editando Acuerdos: ' + (ally.name || 'Aliado');

        const submitBtnLabel = document.getElementById('allyFormSubmitBtnLabel');
        if (submitBtnLabel) submitBtnLabel.textContent = 'Actualizar Acuerdos Comerciales';

        if (document.getElementById('allyFormName')) document.getElementById('allyFormName').value = ally.name || '';
        if (document.getElementById('allyFormCode')) document.getElementById('allyFormCode').value = ally.code || '';
        if (document.getElementById('allyFormUniverse')) document.getElementById('allyFormUniverse').value = ally.universe || 'marte';
        if (document.getElementById('allyFormCategory')) document.getElementById('allyFormCategory').value = ally.category || 'other';
        if (document.getElementById('allyFormContactName')) document.getElementById('allyFormContactName').value = ally.contactName || '';
        if (document.getElementById('allyFormContactWhatsapp')) document.getElementById('allyFormContactWhatsapp').value = ally.contactWhatsapp || '';
        if (document.getElementById('allyFormContactEmail')) document.getElementById('allyFormContactEmail').value = ally.contactEmail || '';
        if (document.getElementById('allyFormSocialHandle')) document.getElementById('allyFormSocialHandle').value = ally.socialHandle || '';
        if (document.getElementById('allyFormNotes')) document.getElementById('allyFormNotes').value = ally.notes || '';

        if (document.getElementById('allyFormLeadAcquisitionCost')) document.getElementById('allyFormLeadAcquisitionCost').value = ally.leadAcquisitionCost ?? 20.00;
        if (document.getElementById('allyFormCommissionType')) document.getElementById('allyFormCommissionType').value = ally.commissionType || 'percentage';
        if (document.getElementById('allyFormCommissionRate')) document.getElementById('allyFormCommissionRate').value = ally.commissionRate ?? 10.00;
        if (document.getElementById('allyFormMaxDiscountPct')) document.getElementById('allyFormMaxDiscountPct').value = ally.maxDiscountPct ?? 20.00;
        if (document.getElementById('allyFormBankName')) document.getElementById('allyFormBankName').value = ally.bankName || '';
        if (document.getElementById('allyFormBankClabe')) document.getElementById('allyFormBankClabe').value = ally.bankClabe || '';
        if (document.getElementById('allyFormStatus')) document.getElementById('allyFormStatus').value = ally.status || 'active';

        const allowed = Array.isArray(ally.allowedDiscountTypes) ? ally.allowedDiscountTypes : [];
        document.querySelectorAll('.ally-dt-checkbox').forEach(cb => {
            cb.checked = allowed.includes(cb.value);
        });
    };

    window.resetAllyForm = function() {
        const form = document.getElementById('allyMasterForm');
        if (!form) return;

        form.action = '{{ route("portal.allies.store") }}';
        const methodInput = document.getElementById('allyFormMethod');
        if (methodInput) methodInput.value = 'POST';

        const editIdInput = document.getElementById('allyEditId');
        if (editIdInput) editIdInput.value = '';

        const modeBadge = document.getElementById('allyFormModeBadge');
        if (modeBadge) modeBadge.textContent = 'Modo: Alta de Nuevo Aliado';

        const tabLabel = document.getElementById('tabLabelAllyForm');
        if (tabLabel) tabLabel.textContent = '1. Alta / Edición de Aliado Comercial';

        const submitBtnLabel = document.getElementById('allyFormSubmitBtnLabel');
        if (submitBtnLabel) submitBtnLabel.textContent = 'Guardar Acuerdo Comercial & Alta de Aliado';

        form.reset();
        document.querySelectorAll('.ally-dt-checkbox').forEach(cb => {
            cb.checked = true;
        });
    };

    window.populateAllyTypeForEdit = function(type) {
        const form = document.getElementById('allyTypeMasterForm');
        if (!form) return;

        form.action = `/admin/convenios/tipos-aliado/${type.id}`;
        const methodInput = document.getElementById('allyTypeFormMethod');
        if (methodInput) methodInput.value = 'PUT';

        const editIdInput = document.getElementById('allyTypeEditId');
        if (editIdInput) editIdInput.value = type.id;

        const badge = document.getElementById('allyTypeFormBadge');
        if (badge) badge.textContent = 'Modo: Editando Tipo ' + (type.name || '#' + type.id);

        const btnLabel = document.getElementById('allyTypeSubmitBtnLabel');
        if (btnLabel) btnLabel.textContent = 'Actualizar Tipo de Aliado';

        if (document.getElementById('allyTypeName')) document.getElementById('allyTypeName').value = type.name || '';
        if (document.getElementById('allyTypeCode')) document.getElementById('allyTypeCode').value = type.code || '';
        if (document.getElementById('allyTypeUniverse')) document.getElementById('allyTypeUniverse').value = type.default_universe || 'marte';
        if (document.getElementById('allyTypeDescription')) document.getElementById('allyTypeDescription').value = type.description || '';
        if (document.getElementById('allyTypeIsActive')) document.getElementById('allyTypeIsActive').checked = (type.is_active !== false && type.is_active !== 0 && type.is_active !== '0');

        window.switchAllySettingsTab('types');
        form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    window.resetAllyTypeForm = function() {
        const form = document.getElementById('allyTypeMasterForm');
        if (!form) return;

        form.action = '{{ route("portal.allies.types.store") }}';
        const methodInput = document.getElementById('allyTypeFormMethod');
        if (methodInput) methodInput.value = 'POST';

        const editIdInput = document.getElementById('allyTypeEditId');
        if (editIdInput) editIdInput.value = '';

        const badge = document.getElementById('allyTypeFormBadge');
        if (badge) badge.textContent = 'Modo: Crear Nuevo Tipo';

        const btnLabel = document.getElementById('allyTypeSubmitBtnLabel');
        if (btnLabel) btnLabel.textContent = 'Guardar Tipo de Aliado';

        form.reset();
        if (document.getElementById('allyTypeIsActive')) document.getElementById('allyTypeIsActive').checked = true;
    };

    window.populateDiscountTypeForEdit = function(disc) {
        const form = document.getElementById('discountTypeMasterForm');
        if (!form) return;

        form.action = `/admin/convenios/tipos-descuento/${disc.id}`;
        const methodInput = document.getElementById('discountTypeFormMethod');
        if (methodInput) methodInput.value = 'PUT';

        const editIdInput = document.getElementById('discountTypeEditId');
        if (editIdInput) editIdInput.value = disc.id;

        const badge = document.getElementById('discountTypeFormBadge');
        if (badge) badge.textContent = 'Modo: Editando Descuento ' + (disc.name || '#' + disc.id);

        const btnLabel = document.getElementById('discountTypeSubmitBtnLabel');
        if (btnLabel) btnLabel.textContent = 'Actualizar Tipo de Descuento';

        if (document.getElementById('discountTypeName')) document.getElementById('discountTypeName').value = disc.name || '';
        if (document.getElementById('discountTypeCode')) document.getElementById('discountTypeCode').value = disc.code || '';
        if (document.getElementById('discountTypeDirection')) document.getElementById('discountTypeDirection').value = disc.direction || 'dir_a';
        if (document.getElementById('discountTypeValueType')) document.getElementById('discountTypeValueType').value = disc.value_type || 'percentage';
        if (document.getElementById('discountTypeMaxValue')) document.getElementById('discountTypeMaxValue').value = disc.max_value_default ?? 20.00;
        if (document.getElementById('discountTypeDescription')) document.getElementById('discountTypeDescription').value = disc.description || '';
        if (document.getElementById('discountTypeIsActive')) document.getElementById('discountTypeIsActive').checked = (disc.is_active !== false && disc.is_active !== 0 && disc.is_active !== '0');

        window.switchAllySettingsTab('discounts');
        form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    window.resetDiscountTypeForm = function() {
        const form = document.getElementById('discountTypeMasterForm');
        if (!form) return;

        form.action = '{{ route("portal.allies.discounts.store") }}';
        const methodInput = document.getElementById('discountTypeFormMethod');
        if (methodInput) methodInput.value = 'POST';

        const editIdInput = document.getElementById('discountTypeEditId');
        if (editIdInput) editIdInput.value = '';

        const badge = document.getElementById('discountTypeFormBadge');
        if (badge) badge.textContent = 'Modo: Crear Nuevo Descuento';

        const btnLabel = document.getElementById('discountTypeSubmitBtnLabel');
        if (btnLabel) btnLabel.textContent = 'Guardar Tipo de Descuento';

        form.reset();
        if (document.getElementById('discountTypeIsActive')) document.getElementById('discountTypeIsActive').checked = true;
    };

    function openAlliesSettingsModal() {
        const modal = document.getElementById('alliesModuleSettingsModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
    function closeAlliesSettingsModal() {
        const modal = document.getElementById('alliesModuleSettingsModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }
    window.openAlliesSettingsModal = openAlliesSettingsModal;
    window.closeAlliesSettingsModal = closeAlliesSettingsModal;

    window.addEventListener('open-module-settings', openAlliesSettingsModal);
    window.addEventListener('open-allies-settings-modal', openAlliesSettingsModal);

    document.querySelectorAll('[data-action="edit-ally-btn"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const raw = this.getAttribute('data-ally');
            if (!raw) return;
            try {
                const ally = JSON.parse(raw);
                window.populateAllyFormForEdit(ally);
                window.switchAllySettingsTab('form');
                openAlliesSettingsModal();
            } catch(e) {
                console.error('Error parsing ally json', e);
            }
        });
    });

    document.querySelectorAll('[data-action="edit-ally-type-btn"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const raw = this.getAttribute('data-type');
            if (!raw) return;
            try {
                const type = JSON.parse(raw);
                window.populateAllyTypeForEdit(type);
                openAlliesSettingsModal();
            } catch(e) {
                console.error('Error parsing ally type json', e);
            }
        });
    });

    document.querySelectorAll('[data-action="edit-discount-type-btn"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const raw = this.getAttribute('data-discount');
            if (!raw) return;
            try {
                const disc = JSON.parse(raw);
                window.populateDiscountTypeForEdit(disc);
                openAlliesSettingsModal();
            } catch(e) {
                console.error('Error parsing discount type json', e);
            }
        });
    });

    const tabButtons = document.querySelectorAll('[data-action="switch-allies-tab"]');

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
    window.switchTab = switchTab;

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (e.target.classList.contains('section-help-btn')) return;
            const tab = this.getAttribute('data-tab');
            if (tab) switchTab(tab);
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
            if (!allyId) {
                alert('No hay un socio aliado activo seleccionado para exportar la lista de taquilla.');
                return;
            }
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

    // Filter Allies by Universe & Status in Directory Tab
    const universeFilterBtns = document.querySelectorAll('[data-action="filter-allies-universe"]');
    const statusFilterBtns = document.querySelectorAll('[data-action="filter-allies-status"]');
    const allyCards = document.querySelectorAll('.ally-card');

    let currentUniverse = 'all';
    let currentStatus = 'all';

    function applyAllyFilters() {
        allyCards.forEach(card => {
            const cardUniverse = card.getAttribute('data-ally-universe');
            const cardStatus = card.getAttribute('data-ally-status');
            const matchUniverse = (currentUniverse === 'all' || cardUniverse === currentUniverse);
            const matchStatus = (currentStatus === 'all' || cardStatus === currentStatus);

            if (matchUniverse && matchStatus) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    universeFilterBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            currentUniverse = this.getAttribute('data-universe');
            universeFilterBtns.forEach(b => {
                b.classList.remove('bg-slate-900', 'text-white');
                b.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-200');
            });
            this.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-200');
            this.classList.add('bg-slate-900', 'text-white');
            applyAllyFilters();
        });
    });

    statusFilterBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            currentStatus = this.getAttribute('data-status');
            statusFilterBtns.forEach(b => {
                b.classList.remove('bg-emerald-50', 'text-emerald-800', 'border-emerald-200', 'bg-slate-900', 'text-white');
                b.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-200');
            });
            this.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-200');
            this.classList.add('bg-slate-900', 'text-white');
            applyAllyFilters();
        });
    });
});
</script>
@endsection
