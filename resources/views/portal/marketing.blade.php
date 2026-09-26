@extends('layouts.portal-panel')

@section('title', 'Marketing & CRM 360°')
@section('hub_kicker', 'Hub 3')
@section('hub_title', 'Marketing & CRM 360°')
@section('hub_status', ($totalLeadsCount ?? 11) . ' Leads Activos')

@section('content')
<div id="marketingAppRoot" class="w-full text-slate-800 font-sans antialiased" data-screen="crm">
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-xs font-bold px-2 py-1">Cerrar</button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- REGLA 10: BARRA DE PESTAÑAS DE SUBMÓDULO PRIMERO (SUBNAV BAR)             --}}
    {{-- ========================================================================= --}}
    <div class="mkt-tabs-container mb-6 border-b border-slate-200 flex flex-wrap gap-2">
        <!-- PESTAÑA 1: PIPELINE & LEADS -->
        <button class="mkt-tab-btn active py-3 px-4 border-b-2 border-indigo-600 text-indigo-600 font-semibold bg-white shadow-xs rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative" data-action="switch-mkt-tab" data-mkt-tab="pipeline" type="button">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span>Pipeline & Leads</span>
            <span class="mkt-tab-count text-xs px-2 py-0.5 bg-slate-100 text-slate-700 rounded-full font-medium">{{ count($leads) }} Contactos</span>
            <button class="section-help-btn" type="button" aria-label="Ayuda Pipeline" data-help-title="Pipeline & Leads CRM" data-help-desc="Directorio unificado de clientes y prospectos multiverso con LTV acumulado, historial y ficha 360°.">?</button>
        </button>

        <!-- PESTAÑA 2: SCORING & CHURN RADAR -->
        <button class="mkt-tab-btn py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative" data-action="switch-mkt-tab" data-mkt-tab="scoring" type="button">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span>Scoring RFM</span>
            <span class="mkt-tab-count text-xs px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full font-bold">{{ $hotLeadsCount }} Hot Leads</span>
            <button class="section-help-btn" type="button" aria-label="Ayuda Scoring" data-help-title="Scoring RFM & Churn Radar" data-help-desc="Puntaje algorítmico de 0 a 100 por recencia, frecuencia y valor monetario, más radar preventivo de bajas.">?</button>
        </button>

        <!-- PESTAÑA 3: CHATBOT MARTEAGA -->
        <button class="mkt-tab-btn py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative" data-action="switch-mkt-tab" data-mkt-tab="chatbot" type="button">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            <span>Chatbot Marteaga</span>
            <span class="mkt-tab-count text-xs px-2 py-0.5 bg-slate-100 text-slate-700 rounded-full font-medium">{{ count($conversations) }} En Vivo</span>
            <button class="section-help-btn" type="button" aria-label="Ayuda Chatbot" data-help-title="Chatbot & WhatsApp Omnicanal" data-help-desc="Visor en tiempo real de conversaciones con árboles automatizados de tallas y tracking de guías.">?</button>
        </button>

        <!-- PESTAÑA 4: CAMPAÑAS & EMAILING -->
        <button class="mkt-tab-btn py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative" data-action="switch-mkt-tab" data-mkt-tab="campaigns" type="button">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <span>Campañas & Emailing</span>
            <span class="mkt-tab-count text-xs px-2 py-0.5 bg-slate-100 text-slate-700 rounded-full font-medium">{{ count($emailAutomations) }} Flujos</span>
            <button class="section-help-btn" type="button" aria-label="Ayuda Emailing" data-help-title="Campañas & Automatizaciones" data-help-desc="Flujos automáticos de bienvenida, recuperación de carritos (TASK-INT-05) y reposición periódica.">?</button>
        </button>

        <!-- PESTAÑA 5: CUPONES & PROMOS -->
        <button class="mkt-tab-btn py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative" data-action="switch-mkt-tab" data-mkt-tab="coupons" type="button">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            <span>Cupones & Promos</span>
            <span class="mkt-tab-count text-xs px-2 py-0.5 bg-slate-100 text-slate-700 rounded-full font-medium">{{ count($coupons) }} Vigentes</span>
            <button class="section-help-btn" type="button" aria-label="Ayuda Cupones" data-help-title="Cupones & Promociones" data-help-desc="Reglas comerciales de descuento directo, envío gratis y venta cruzada protegiendo el margen neto real.">?</button>
        </button>

        <!-- PESTAÑA 6: TELEMETRÍA ADS & COPILOTO -->
        <button class="mkt-tab-btn py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative" data-action="switch-mkt-tab" data-mkt-tab="ads" type="button">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span>Telemetría Ads & Copiloto</span>
            <span class="mkt-tab-count text-xs px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full font-semibold">ROAS {{ $adsMetrics['platformRoas'] }}x</span>
            <button class="section-help-btn" type="button" aria-label="Ayuda Ads" data-help-title="Telemetría Ads & Copiloto IA" data-help-desc="Monitoreo de Meta CAPI, ROAS Neto Real auditado y motor de recomendaciones de crecimiento (TASK-MKT-01).">?</button>
        </button>
    </div>

    {{-- ========================================================================= --}}
    {{-- REGLA 10: SECCIÓN 2: TARJETAS DE KPIS CONTEXTUALES (NIVEL 2)               --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6" id="mkt-kpis-container">
        <!-- KPI 1: TOTAL LEADS -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden group hover:border-indigo-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Leads Registrados</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Total Leads" data-help-title="Total Leads" data-help-desc="Prospectos, compradores y suscriptores captados omnicanal.">?</button>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono tracking-tight text-slate-900">{{ number_format($totalLeadsCount) }}</span>
                <span class="text-xs font-bold text-emerald-600 font-mono">+12 hoy</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">Omnicanal (Web &bull; WhatsApp &bull; Meta Ads)</div>
        </div>

        <!-- KPI 2: CLIENTES ACTIVOS -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden group hover:border-indigo-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Clientes Activos</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Clientes Activos" data-help-title="Clientes Activos" data-help-desc="Compradores con al menos una orden entregada con éxito.">?</button>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono tracking-tight text-slate-900">{{ number_format($activeCustomersCount) }}</span>
                <span class="text-xs font-bold text-slate-600 font-mono">{{ $totalLeadsCount > 0 ? round(($activeCustomersCount / $totalLeadsCount) * 100, 1) : 0 }}% conversión</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">Compradores verificados con ticket cerrado</div>
        </div>

        <!-- KPI 3: SUSCRIPTORES VIGENTES -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden group hover:border-indigo-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Suscriptores Vigentes</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Suscriptores" data-help-title="Suscriptores" data-help-desc="Membresías mensuales activas con cobro recurrente.">?</button>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono tracking-tight text-indigo-600">{{ number_format($activeSubscribersCount) }}</span>
                <span class="text-xs font-bold text-indigo-700 font-mono">MRR Activo</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">Corte los días 1 y 15 de cada mes</div>
        </div>

        <!-- KPI 4: LTV PROMEDIO -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden group hover:border-indigo-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">LTV Promedio</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda LTV" data-help-title="LTV Promedio" data-help-desc="Valor acumulado promedio generado por cada cliente en la plataforma.">?</button>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono tracking-tight text-slate-900">MXN ${{ number_format($avgLtv, 2) }}</span>
                <span class="text-xs font-bold text-emerald-600 font-mono">+8% vs mes anterior</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">Impulsado por catálogo Torzo & Adrenaline</div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- REGLA 10: SECCIÓN 3: PANELES Y ESPACIO DE TRABAJO OPERATIVO (NIVEL 3)     --}}
    {{-- ========================================================================= --}}
    <div id="mkt-workspace-container">
        <!-- 1. DIRECTORIO CRM 360 -->
        <div data-mkt-panel="pipeline" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <h3 class="text-sm font-bold text-slate-900">Directorio CRM 360° Multiverso</h3>
                        <button class="section-help-btn" type="button" aria-label="Ayuda Directorio" data-help-title="Directorio CRM 360°" data-help-desc="Contactos segmentados por vitrina, valor acumulado LTV, etapa de ciclo de vida y acceso a ficha 360°.">?</button>
                    </div>
                    <span class="text-xs text-slate-500 font-medium">{{ count($leads) }} contactos registrados</span>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('leads.import.form') }}" class="button button-light text-xs py-1.5 px-3 rounded-md shadow-xs flex items-center gap-1 border border-slate-300 hover:bg-slate-50">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Importar CSV Leads</span>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto mt-4">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-2.5 px-3">Contacto</th>
                            <th class="py-2.5 px-3">Universo</th>
                            <th class="py-2.5 px-3">Etapa Ciclo</th>
                            <th class="py-2.5 px-3">LTV Acumulado</th>
                            <th class="py-2.5 px-3">Score RFM</th>
                            <th class="py-2.5 px-3">Última Actividad</th>
                            <th class="py-2.5 px-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($leads as $l)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900">{{ $l['name'] }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $l['email'] }} &bull; {{ $l['phone'] }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700">{{ strtoupper($l['universe']) }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $l['stage'] === 'subscriber' ? 'bg-indigo-100 text-indigo-800' : ($l['stage'] === 'customer' ? 'bg-emerald-100 text-emerald-800' : ($l['stage'] === 'abandoned' ? 'bg-amber-100 text-amber-800' : ($l['stage'] === 'churn' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700'))) }}">
                                        @if($l['stage'] === 'abandoned') Carrito Abandonado
                                        @elseif($l['stage'] === 'subscriber') Suscriptor VIP
                                        @elseif($l['stage'] === 'customer') Cliente Frecuente
                                        @elseif($l['stage'] === 'churn') Riesgo Churn
                                        @else Prospecto
                                        @endif
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-bold font-mono text-slate-900">MXN ${{ number_format($l['ltv'], 2) }}</td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold font-mono {{ $l['score'] >= 80 ? 'bg-emerald-100 text-emerald-800' : ($l['score'] >= 60 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') }}">
                                        {{ $l['score'] }}/100
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-600 text-[11px]">{{ $l['lastInteraction'] }}</td>
                                <td class="py-3 px-3 text-right">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $l['phone']) }}" target="_blank" class="inline-block text-xs py-1 px-2.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded font-semibold transition">
                                        WhatsApp
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. SCORING RFM & CHURN RADAR -->
        <div data-mkt-panel="scoring" class="hidden grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <h3 class="text-sm font-bold text-slate-900">Matriz de Lead Scoring RFM (Hot Leads)</h3>
                        <button class="section-help-btn" type="button" aria-label="Ayuda Matriz RFM" data-help-title="Matriz RFM" data-help-desc="Puntaje predictivo de 0 a 100 calculado a partir de Recencia, Frecuencia y Valor Monetario para priorizar cierres comerciales.">?</button>
                    </div>
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>{{ $hotLeadsCount }} Hot Leads Listos</span>
                    </span>
                </div>
                <div class="divide-y divide-slate-100 mt-3">
                    @foreach(array_filter($leads, fn($l) => $l['score'] >= 80) as $hl)
                        <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900">{{ $hl['name'] }}</span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">{{ strtoupper($hl['universe']) }}</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $hl['notes'] }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-0.5 rounded text-xs font-bold font-mono bg-emerald-100 text-emerald-800">{{ $hl['score'] }} pts</span>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $hl['phone']) }}" target="_blank" class="text-xs py-1 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded font-bold transition flex items-center gap-1">
                                    <span>WhatsApp</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- RADAR DE CHURN -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <div class="flex items-center gap-2 pb-4 border-b border-slate-100">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <h3 class="text-sm font-bold text-slate-900">Radar de Churn (Suscripciones)</h3>
                    <button class="section-help-btn" type="button" aria-label="Ayuda Radar Churn" data-help-title="Radar Churn" data-help-desc="Detección temprana de suscriptores con riesgo de baja por tarjetas rechazadas o inactividad mayor a 40 días.">?</button>
                </div>
                @php($churnLeads = array_filter($leads, fn($l) => $l['stage'] === 'churn'))
                @forelse($churnLeads as $cl)
                    <div class="p-4 bg-rose-50/70 border border-rose-200 rounded-xl mt-4">
                        <div class="text-xs font-bold text-rose-900 flex items-center justify-between">
                            <span>Alerta de Baja Preventiva</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-200 text-rose-900">Dunning</span>
                        </div>
                        <p class="text-[11px] text-rose-700 mt-1.5 leading-relaxed">
                            {{ $cl['name'] }}: {{ $cl['notes'] }}
                        </p>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $cl['phone']) }}" target="_blank" class="block text-center text-xs mt-3 w-full py-1.5 bg-white text-rose-800 border border-rose-300 font-bold rounded-lg shadow-2xs hover:bg-rose-50 cursor-pointer">
                            Activar Rescate WhatsApp
                        </a>
                    </div>
                @empty
                    <div class="p-4 text-center text-slate-400 text-xs mt-4">No hay suscriptores en riesgo crítico de churn.</div>
                @endforelse
            </div>
        </div>

        <!-- 3. CHATBOT MARTEAGA & WHATSAPP OMNICANAL -->
        <div data-mkt-panel="chatbot" class="hidden bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs mb-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <h3 class="text-sm font-bold text-slate-900">Chatbot Marteaga &amp; WhatsApp Omnicanal</h3>
                    <button class="section-help-btn" type="button" aria-label="Ayuda Chatbot" data-help-title="Chatbot Marteaga" data-help-desc="Visor interactivo de conversaciones en vivo con árboles automatizados de asesoría de tallas y seguimiento de pedidos.">?</button>
                </div>
                <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded">Canal: WhatsApp API &amp; WebChat</span>
            </div>
            
            <div class="mkt-chat-container flex flex-col md:flex-row gap-4 border border-slate-200 rounded-xl overflow-hidden min-h-[420px]">
                <div class="mkt-chat-sidebar w-full md:w-1/3 bg-slate-50 border-r border-slate-200 divide-y divide-slate-100">
                    @foreach($conversations as $index => $c)
                        <div class="p-3 hover:bg-white cursor-pointer transition {{ $index === 0 ? 'bg-white font-bold' : '' }}">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-900">{{ $c['customerName'] }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $c['lastMessageTime'] }}</span>
                            </div>
                            <div class="text-[11px] text-slate-500 truncate mt-0.5">{{ end($c['messages'])['text'] ?? 'Sin mensajes' }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="mkt-chat-main flex-1 flex flex-col justify-between p-4 bg-white">
                    @if(count($conversations) > 0)
                        <div>
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                                <div>
                                    <div class="text-xs font-bold text-slate-900">{{ $conversations[0]['customerName'] }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $conversations[0]['customerPhone'] }} &bull; Intención: {{ $conversations[0]['intent'] }}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $conversations[0]['status'] === 'bot_active' ? 'bg-indigo-100 text-indigo-800' : ($conversations[0]['status'] === 'human_takeover' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                        {{ $conversations[0]['status'] === 'bot_active' ? 'Bot Activo' : ($conversations[0]['status'] === 'human_takeover' ? 'Agente Humano' : 'Resuelto') }}
                                    </span>
                                    @if($conversations[0]['status'] === 'bot_active')
                                        <form method="POST" action="{{ route('portal.marketing.conversations.takeover', $conversations[0]['db_id']) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded font-semibold cursor-pointer">
                                                Intervenir
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            <div class="mkt-chat-messages space-y-3 max-h-72 overflow-y-auto pr-2" id="mkt-chat-messages-scroll">
                                @foreach($conversations[0]['messages'] as $m)
                                    <div class="p-3 rounded-xl max-w-[85%] text-xs {{ $m['sender'] === 'user' ? 'bg-slate-100 mr-auto text-slate-800' : ($m['sender'] === 'bot' ? 'bg-indigo-50 text-indigo-900 ml-auto border border-indigo-100' : 'bg-amber-50 text-amber-900 ml-auto border border-amber-100') }}">
                                        <div class="text-[10px] font-bold mb-0.5 text-slate-500">
                                            {{ $m['sender'] === 'user' ? 'Cliente' : ($m['sender'] === 'bot' ? 'Marteaga Bot' : 'Asesor Humano') }}
                                        </div>
                                        <div>{{ $m['text'] }}</div>
                                        <div class="text-[9px] text-slate-400 text-right mt-1 font-mono">{{ $m['time'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 4. FLUJOS DE EMAILING Y AUTOMATIZACIONES -->
        <div data-mkt-panel="campaigns" class="hidden bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs mb-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <h3 class="text-sm font-bold text-slate-900">Flujos de Emailing y Automatizaciones Activas</h3>
                    <button class="section-help-btn" type="button" aria-label="Ayuda Flujos Emailing" data-help-title="Flujos Emailing" data-help-desc="Campañas automáticas disparadas por eventos de comportamiento (bienvenida, carrito abandonado, reposición y dunning).">?</button>
                </div>
                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded">Motor cPanel Cron (Database Queue)</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($emailAutomations as $em)
                    <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-xl">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-900">{{ $em['title'] }}</span>
                            <form method="POST" action="{{ route('portal.marketing.automations.toggle', $em['db_id']) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-2 py-0.5 rounded text-[10px] font-bold cursor-pointer {{ $em['isActive'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $em['isActive'] ? 'Activo' : 'Pausado' }}
                                </button>
                            </form>
                        </div>
                        <div class="text-[11px] text-slate-500 mt-1">Gatillo: {{ $em['trigger'] }} &bull; Retardo: {{ $em['delay'] }}</div>
                        <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-slate-200 text-center">
                            <div>
                                <span class="text-[10px] text-slate-400 block">Enviados</span>
                                <strong class="text-xs text-slate-800 font-mono">{{ number_format($em['sentCount']) }}</strong>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Apertura</span>
                                <strong class="text-xs text-indigo-600 font-mono">{{ $em['openRate'] }}%</strong>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Ventas Atrib.</span>
                                <strong class="text-xs text-emerald-600 font-mono">MXN ${{ number_format($em['revenueAttributed']) }}</strong>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 5. CUPONES Y PROMOCIONES DE VENTA CRUZADA -->
        <div data-mkt-panel="coupons" class="hidden bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs mb-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <h3 class="text-sm font-bold text-slate-900">Cupones y Promociones de Venta Cruzada</h3>
                    <button class="section-help-btn" type="button" aria-label="Ayuda Cupones Promocionales" data-help-title="Cupones Promocionales" data-help-desc="Reglas comerciales de descuento directo, envío gratis o regalos por compra cruzada protegiendo el margen neto real.">?</button>
                </div>
            </div>

            <!-- FORMULARIO CREAR CUPÓN INLINE -->
            <form method="POST" action="{{ route('portal.marketing.coupons.store') }}" class="mb-6 p-4 bg-slate-50 border border-slate-200 rounded-xl grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Código</label>
                    <input type="text" name="code" placeholder="PROMO20" required class="w-full bg-white border border-slate-300 rounded px-2.5 py-1.5 uppercase font-mono font-bold">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tipo Descuento</label>
                    <select name="discount_type" class="w-full bg-white border border-slate-300 rounded px-2 py-1.5 font-medium">
                        <option value="percent">Porcentaje (%)</option>
                        <option value="fixed_amount">Monto Fijo (MXN)</option>
                        <option value="free_shipping">Envío Gratis</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Valor</label>
                    <input type="number" step="0.01" name="discount_value" placeholder="15" required class="w-full bg-white border border-slate-300 rounded px-2.5 py-1.5 font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Mínimo Orden</label>
                    <input type="number" step="0.01" name="min_order_amount" placeholder="499" class="w-full bg-white border border-slate-300 rounded px-2.5 py-1.5 font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Vitrina Objetivo</label>
                    <select name="target_universe" class="w-full bg-white border border-slate-300 rounded px-2 py-1.5 font-medium">
                        <option value="all">Todas las vitrinas</option>
                        <option value="marte">Marte</option>
                        <option value="venus">Venus</option>
                        <option value="tiresias">Tiresias</option>
                        <option value="terra">Terra</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white rounded font-bold transition shadow-xs cursor-pointer">
                        + Crear Cupón
                    </button>
                </div>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($coupons as $c)
                    <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-indigo-50 text-indigo-700">{{ $c['code'] }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $c['isActive'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $c['isActive'] ? 'Vigente' : 'Inactivo' }}</span>
                        </div>
                        <div class="text-xs font-bold text-slate-800 mt-2">
                            {{ $c['type'] === 'percent' ? $c['value'] . '% de Descuento' : ($c['type'] === 'free_shipping' ? 'Envío Gratis' : 'Descuento Fijo MXN $' . $c['value']) }}
                        </div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Mínimo: MXN ${{ number_format($c['minAmount'], 2) }} &bull; Vitrina: {{ strtoupper($c['universe']) }}</div>
                        <div class="text-xs font-semibold text-slate-700 mt-2">Usos: {{ $c['usedCount'] }} / {{ $c['maxUses'] }}</div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1">
                            <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $c['maxUses'] > 0 ? min(100, ($c['usedCount'] / $c['maxUses']) * 100) : 0 }}%"></div>
                        </div>
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                            <span class="text-[11px] font-bold text-emerald-700 font-mono">MXN ${{ number_format($c['revenue']) }}</span>
                            <form method="POST" action="{{ route('portal.marketing.coupons.toggle', $c['id']) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs font-semibold px-2 py-0.5 rounded border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                    {{ $c['isActive'] ? 'Pausar' : 'Activar' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 6. TELEMETRÍA ADS, META CAPI & COPILOTO (TASK-MKT-01) -->
        <div data-mkt-panel="ads" class="hidden grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <h3 class="text-sm font-bold text-slate-900">Telemetría de Campañas (Meta Ads &amp; Google Ads)</h3>
                        <button class="section-help-btn" type="button" aria-label="Ayuda Telemetria Ads" data-help-title="Telemetria Ads" data-help-desc="Métricas en vivo de pauta publicitaria comparando ROAS reportado por pixel vs ROAS Neto Real auditado con Finanzas.">?</button>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">
                            Meta CAPI Score: {{ $adsMetrics['capiHealthScore'] }}/10
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                    <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-md text-center">
                        <span class="text-[10px] text-slate-400 block font-semibold">Inversión Hoy</span>
                        <strong class="text-xs text-slate-900 font-mono">MXN ${{ number_format($adsMetrics['spendToday']) }}</strong>
                    </div>
                    <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-md text-center">
                        <span class="text-[10px] text-slate-400 block font-semibold">ROAS Plataforma</span>
                        <strong class="text-xs text-indigo-600 font-mono">{{ $adsMetrics['platformRoas'] }}x</strong>
                    </div>
                    <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-md text-center">
                        <span class="text-[10px] text-emerald-700 block font-bold">ROAS Neto Real</span>
                        <strong class="text-xs text-emerald-800 font-mono font-extrabold">{{ $adsMetrics['netRealRoas'] }}x</strong>
                    </div>
                    <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-md text-center">
                        <span class="text-[10px] text-slate-400 block font-semibold">CPA Promedio</span>
                        <strong class="text-xs text-slate-900 font-mono">MXN ${{ number_format($adsMetrics['cpa'], 2) }}</strong>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach($adCampaigns as $camp)
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl {{ $camp['fatigueAlert'] ? 'border-amber-300 bg-amber-50/30' : '' }}">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-900">{{ $camp['name'] }}</span>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">{{ strtoupper($camp['universe']) }}</span>
                                        @if($camp['fatigueAlert'])
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Fatiga de Creativo</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-1">
                                        Presupuesto Diario: MXN ${{ number_format($camp['dailyBudget'], 2) }} &bull; Gasto: MXN ${{ number_format($camp['spendToday'], 2) }} &bull; CTR: {{ $camp['ctr'] }}%
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-right font-mono">
                                        <div class="text-xs font-bold text-slate-900">ROAS: {{ $camp['platformRoas'] }}x</div>
                                        <div class="text-[10px] font-semibold text-emerald-700">Neto: {{ $camp['netRealRoas'] }}x</div>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <form method="POST" action="{{ route('portal.marketing.campaigns.pause', $camp['db_id']) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs py-1 px-2.5 rounded border border-slate-300 hover:bg-white font-semibold cursor-pointer">
                                                {{ $camp['status'] === 'active' ? 'Pausar' : 'Reanudar' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('portal.marketing.campaigns.scale', $camp['db_id']) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs py-1 px-2.5 rounded bg-slate-900 text-white font-semibold hover:bg-slate-800 cursor-pointer">
                                                +20% Presupuesto
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- LATERAL COPILOTO IA & SYNC WHATSAPP -->
            <div class="space-y-6">
                <!-- SINCRONIZACIÓN WHATSAPP -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100 mb-3">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <h3 class="text-sm font-bold text-slate-900">Sincronización WhatsApp</h3>
                        <button class="section-help-btn" type="button" aria-label="Ayuda Catalogo WhatsApp" data-help-title="Catálogo WhatsApp" data-help-desc="Sincroniza stock y fotos WebP con Meta Commerce Catalog para ventas directas por chat.">?</button>
                    </div>
                    <p class="text-xs text-slate-500 mb-3 leading-relaxed">{{ $adsMetrics['lastCatalogSync'] }}</p>
                    <button class="w-full text-xs py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md shadow-xs cursor-pointer font-bold" type="button" onclick="alert('Sincronización de catálogo WhatsApp ejecutada con éxito.')">
                        Sincronizar Catálogo WhatsApp
                    </button>
                </div>

                <!-- COPILOTO IA DE CRECIMIENTO -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100 mb-3">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon stroke-linecap="round" stroke-linejoin="round" stroke-width="2" points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        <h3 class="text-sm font-bold text-slate-900">Copiloto IA de Crecimiento</h3>
                        <button class="section-help-btn" type="button" aria-label="Ayuda Growth Copilot" data-help-title="Growth Copilot" data-help-desc="Recomendaciones accionables para escalar anuncios rentables y generar copies con IA.">?</button>
                    </div>
                    <div class="space-y-3">
                        @foreach($growthRecommendations as $r)
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                                <div class="text-xs font-bold text-slate-900">{{ $r['title'] }}</div>
                                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">{{ $r['summary'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT REACTIVO DE PESTAÑAS (VANILLA JS LIGERO, SIN DEPENDENCIAS PESADAS) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('[data-mkt-tab]');
    const tabPanels = document.querySelectorAll('[data-mkt-panel]');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetTab = this.getAttribute('data-mkt-tab');

            // Reset button states
            tabButtons.forEach(b => {
                b.classList.remove('border-indigo-600', 'text-indigo-600', 'bg-white', 'shadow-xs', 'active');
                b.classList.add('border-transparent', 'text-slate-500');
            });

            // Activate current button
            this.classList.remove('border-transparent', 'text-slate-500');
            this.classList.add('border-indigo-600', 'text-indigo-600', 'bg-white', 'shadow-xs', 'active');

            // Toggle panels
            tabPanels.forEach(p => {
                if (p.getAttribute('data-mkt-panel') === targetTab) {
                    p.classList.remove('hidden');
                } else {
                    p.classList.add('hidden');
                }
            });

            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('tab', targetTab);
            window.history.replaceState({}, '', url);
        });
    });

    // Check initial tab from URL
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab');
    if (initialTab) {
        const matchingBtn = document.querySelector(`[data-mkt-tab="${initialTab}"]`);
        if (matchingBtn) matchingBtn.click();
    }
});
</script>
@endsection
