@extends('layouts.portal-panel')

@section('title', 'Finanzas & Conciliación')
@section('hub_kicker', 'Hub 5')
@section('hub_title', 'Finanzas & Conciliación')
@section('hub_status', 'MXN $' . number_format($subscriptionKpis['mrr_active'] ?? 0, 2) . ' MRR Activo')

@section('content')
<div id="financeAppRoot" class="w-full text-slate-800 font-sans antialiased" data-screen="finance">
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
    <div class="mb-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <nav class="flex gap-2 -mb-px overflow-x-auto" aria-label="Submódulos de Finanzas">
            <!-- TAB 1: FLUJO DE CAJA & P&L -->
            <button 
                type="button"
                data-finance-tab="cashflow"
                class="finance-nav-btn py-3 px-4 border-b-2 font-semibold text-sm transition-all flex items-center gap-2 cursor-pointer group relative {{ ($activeTab ?? 'cashflow') === 'cashflow' ? 'border-indigo-600 text-indigo-600 bg-white shadow-xs rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Flujo de Caja & P&L</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 font-bold">EBITDA {{ $waterfall['net_margin_percent'] ?? 0 }}%</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Flujo de Caja" data-help-title="Flujo de Caja & P&L" data-help-desc="Estado de resultados en vivo con cascada de absorción de costos directos (COGS, pasarelas, empaque y fletes).">?</button>
            </button>

            <!-- TAB 2: CONCILIACIÓN PASARELAS & SPEI -->
            <button 
                type="button"
                data-finance-tab="reconciliation"
                class="finance-nav-btn py-3 px-4 border-b-2 font-semibold text-sm transition-all flex items-center gap-2 cursor-pointer group relative {{ ($activeTab ?? '') === 'reconciliation' ? 'border-indigo-600 text-indigo-600 bg-white shadow-xs rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Conciliación Pasarelas</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-medium">{{ count($payoutBatches ?? []) }} Lotes</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Conciliación" data-help-title="Conciliación de Pasarelas" data-help-desc="Cruce automatizado de lotes de liquidación bancaria (payouts) contra pedidos cobrados y tasa efectiva de comisiones.">?</button>
            </button>

            <!-- TAB 3: MRR & SUSCRIPCIONES -->
            <button 
                type="button"
                data-finance-tab="subscriptions"
                class="finance-nav-btn py-3 px-4 border-b-2 font-semibold text-sm transition-all flex items-center gap-2 cursor-pointer group relative {{ ($activeTab ?? '') === 'subscriptions' ? 'border-indigo-600 text-indigo-600 bg-white shadow-xs rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>MRR & Suscripciones</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700 font-semibold">{{ $subscriptionKpis['active_subscribers'] ?? 0 }} Activas</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Suscripciones" data-help-title="Suscripciones & Dunning" data-help-desc="Monitoreo de ingresos recurrentes (MRR), cohortes de retención y motor de rescate de cobros fallidos (TASK-SUB-DUN).">?</button>
            </button>

            <!-- TAB 4: MARGEN NETO REAL -->
            <button 
                type="button"
                data-finance-tab="margins"
                class="finance-nav-btn py-3 px-4 border-b-2 font-semibold text-sm transition-all flex items-center gap-2 cursor-pointer group relative {{ ($activeTab ?? '') === 'margins' ? 'border-indigo-600 text-indigo-600 bg-white shadow-xs rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Margen Neto Real</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 font-bold">{{ $marginKpis['avg_margin'] ?? 0 }}% Prom</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Margen Neto" data-help-title="Unit Economics por Orden" data-help-desc="Auditoría ticket por ticket deduciendo costo de prenda, pasarela, caja de empaque y flete para asegurar cero ventas a pérdida (TASK-FIN-01).">?</button>
            </button>

            <!-- TAB 5: CUENTAS POR PAGAR -->
            <button 
                type="button"
                data-finance-tab="expenses"
                class="finance-nav-btn py-3 px-4 border-b-2 font-semibold text-sm transition-all flex items-center gap-2 cursor-pointer group relative {{ ($activeTab ?? '') === 'expenses' ? 'border-indigo-600 text-indigo-600 bg-white shadow-xs rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Cuentas por Pagar</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-medium">{{ count($vendorBills ?? []) }} Facturas</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Cuentas por Pagar" data-help-title="Cuentas por Pagar" data-help-desc="Programación de pagos a talleres de confección, proveedores dropship (Torzo), laboratorios cosmética y fleteras.">?</button>
            </button>
        </nav>

        <div class="flex items-center gap-3 text-xs pb-2 sm:pb-0">
            <span class="flex items-center gap-1.5 font-medium text-slate-600 bg-white px-2.5 py-1 rounded-full border border-slate-200 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Cierre Fiscal Mensual: En Curso</span>
            </span>
            <button class="text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 px-3 py-1.5 rounded-lg shadow-xs flex items-center gap-1.5 cursor-pointer transition-colors" data-action="export-pnl-report" onclick="window.print()">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Exportar Reporte P&L</span>
            </button>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- REGLA 10: SECCIÓN 2: TARJETAS DE KPIS CONTEXTUALES                        --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @if(isset($cards))
            @foreach($cards as $index => $card)
                <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden group hover:border-indigo-300 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $card['label'] }}</span>
                        <button class="section-help-btn" type="button" aria-label="Ayuda de {{ $card['label'] }}" data-help-title="{{ $card['label'] }}" data-help-desc="Métrica financiera calculada a partir de las transacciones y libros mayores contables de la plataforma.">?</button>
                    </div>
                    <div class="mt-3 flex items-baseline justify-between">
                        <span class="text-2xl font-black font-mono tracking-tight text-slate-900">{{ $card['value'] }}</span>
                        @if($index === 0)
                            <span class="text-xs font-bold text-emerald-600 font-mono">{{ $card['change'] ?? '+14.2%' }}</span>
                        @elseif($index === 1)
                            <span class="text-xs font-bold text-slate-600 font-mono">{{ $card['change'] ?? 'Deducciones' }}</span>
                        @elseif($index === 2)
                            <span class="text-xs font-bold text-indigo-600 font-mono">{{ $card['change'] ?? 'EBITDA' }}</span>
                        @else
                            <span class="text-xs font-bold text-emerald-700 font-mono">{{ $card['change'] ?? 'MRR' }}</span>
                        @endif
                    </div>
                    <div class="mt-2 text-[11px] text-slate-400">
                        {{ $card['note'] ?? '' }}
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- SECCIÓN 3: PESTAÑA 1 — CASCADA FINANCIERA P&L & FLUJO DE CAJA              --}}
    {{-- ========================================================================= --}}
    <div data-finance-panel="cashflow" class="{{ ($activeTab ?? 'cashflow') === 'cashflow' ? '' : 'hidden' }}">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- CASCADA P&L WATERFALL (2 COLS) -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            <span>Cascada de Absorción de Costos (P&L Waterfall)</span>
                        </h3>
                        <p class="text-xs text-slate-500">Desglose secuencial de ingresos brutos auditados contra costos directos de la tienda</p>
                    </div>
                    <button class="section-help-btn" type="button" aria-label="Ayuda Cascada P&L" data-help-title="Cascada P&L" data-help-desc="Resta paso a paso los costos variables por cada peso vendido para garantizar márgenes saludables.">?</button>
                </div>

                <div class="space-y-3 text-xs">
                    <!-- 1. Ingreso Bruto -->
                    <div class="p-3 bg-slate-50 rounded-xl flex items-center justify-between">
                        <span class="font-bold text-slate-900">1. Facturación Bruta Total (Ventas D2C + MRR)</span>
                        <span class="font-mono font-black text-slate-900 text-sm">MXN ${{ number_format($waterfall['gross_revenue'] ?? 0, 2) }}</span>
                    </div>

                    <!-- 2. Costo de Mercancía COGS -->
                    <div class="p-3 bg-rose-50/50 rounded-xl flex items-center justify-between text-rose-900">
                        <span class="font-medium">(-) Costo Directo de Mercancía (COGS Catálogo Torzo & Adrenaline)</span>
                        <span class="font-mono font-bold">-MXN ${{ number_format($waterfall['cogs'] ?? 0, 2) }} ({{ $waterfall['cogs_percent'] ?? 0 }}%)</span>
                    </div>

                    <!-- 3. Comisiones Pasarelas -->
                    <div class="p-3 bg-rose-50/50 rounded-xl flex items-center justify-between text-rose-900">
                        <span class="font-medium">(-) Comisiones Pasarelas (Stripe, Mercado Pago, OXXO Pay)</span>
                        <span class="font-mono font-bold">-MXN ${{ number_format($waterfall['gateway_fees'] ?? 0, 2) }} ({{ $waterfall['gateway_percent'] ?? 0 }}%)</span>
                    </div>

                    <!-- 4. Cajas e Insumos Empaque -->
                    <div class="p-3 bg-rose-50/50 rounded-xl flex items-center justify-between text-rose-900">
                        <span class="font-medium">(-) Insumos de Empaque & Material de Embalaje (Cajas BOX-M02 / Insumos)</span>
                        <span class="font-mono font-bold">-MXN ${{ number_format($waterfall['packaging'] ?? 0, 2) }} ({{ $waterfall['packaging_percent'] ?? 0 }}%)</span>
                    </div>

                    <!-- 5. Fletes Courier -->
                    <div class="p-3 bg-rose-50/50 rounded-xl flex items-center justify-between text-rose-900">
                        <span class="font-medium">(-) Fletes y Guías Courier Pagadas a Paqueterías (DHL / Skydropx)</span>
                        <span class="font-mono font-bold">-MXN ${{ number_format($waterfall['shipping'] ?? 0, 2) }} ({{ $waterfall['shipping_percent'] ?? 0 }}%)</span>
                    </div>

                    <!-- 6. Pauta Publicitaria -->
                    <div class="p-3 bg-rose-50/50 rounded-xl flex items-center justify-between text-rose-900">
                        <span class="font-medium">(-) Pauta Publicitaria Ads (Meta CAPI & Google Ads)</span>
                        <span class="font-mono font-bold">-MXN ${{ number_format($waterfall['ads_spend'] ?? 0, 2) }} ({{ $waterfall['ads_percent'] ?? 0 }}%)</span>
                    </div>

                    <!-- 7. Utilidad Neta Real (EBITDA) -->
                    <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200/80 flex items-center justify-between text-emerald-950">
                        <div>
                            <span class="font-black text-sm block">(=) Utilidad Neta Real Líquida (EBITDA)</span>
                            <span class="text-[11px] text-emerald-700 font-medium">Margen neto final libre de absorción</span>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-black text-lg text-emerald-700 block">MXN ${{ number_format($waterfall['net_profit'] ?? 0, 2) }}</span>
                            <span class="text-xs font-bold text-emerald-800 font-mono">{{ $waterfall['net_margin_percent'] ?? 0 }}% Margen Neto</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MOVIMIENTOS RECIENTES & LOTES DE DISPERSIÓN (1 COL) -->
            <div class="space-y-6">
                <!-- PANEL: LOTES DE CONCILIACIÓN BANCARIA -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            <span>Lotes Bancarios Recientes</span>
                        </h3>
                        <button type="button" onclick="document.querySelector('[data-finance-tab=reconciliation]').click()" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold cursor-pointer">Ver todos</button>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        @foreach($payoutBatches->take(3) as $pb)
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono font-bold text-slate-800">{{ $pb->payout_id_external }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $pb->status === 'reconciled' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $pb->status === 'reconciled' ? 'Conciliado' : 'En Tránsito' }}
                                    </span>
                                </div>
                                <div class="mt-1 flex items-center justify-between text-slate-500 text-[11px]">
                                    <span>{{ strtoupper($pb->gateway) }} &bull; {{ $pb->deposit_date ? $pb->deposit_date->format('d M, H:i') : 'Programado' }}</span>
                                    <span class="font-mono font-bold text-slate-900">MXN ${{ number_format((float) $pb->net_deposited, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- PANEL: MOVIMIENTOS RECIENTES DEL SISTEMA -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Movimientos Reales</span>
                        </h3>
                        <span class="text-xs text-slate-500 font-semibold">{{ count($movements) }} registros</span>
                    </div>

                    <div class="space-y-2 text-xs">
                        @foreach($movements as $m)
                            <div class="p-2.5 bg-slate-50 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $m['type'] === 'Ingreso' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    <div>
                                        <span class="font-semibold text-slate-800 block truncate max-w-[190px]">{{ $m['concept'] }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $m['type'] }} &bull; {{ $m['date'] }}</span>
                                    </div>
                                </div>
                                <span class="font-mono font-bold {{ $m['type'] === 'Ingreso' ? 'text-emerald-700' : 'text-rose-700' }}">{{ $m['amount'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SECCIÓN 4: PESTAÑA 2 — CONCILIACIÓN DE PASARELAS & LOTES BANCARIOS        --}}
    {{-- ========================================================================= --}}
    <div data-finance-panel="reconciliation" class="{{ ($activeTab ?? '') === 'reconciliation' ? '' : 'hidden' }}">
        <!-- KPI METRICS SUBMÓDULO 2 -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Cobrado en Pasarelas</span>
                <div class="mt-2 text-xl font-black font-mono text-slate-900">MXN ${{ number_format($payoutKpis['gross_captured'] ?? 0, 2) }}</div>
                <span class="text-[11px] text-slate-400">Total capturado en Stripe, MP, OXXO, SPEI</span>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Comisiones & Retenciones</span>
                <div class="mt-2 text-xl font-black font-mono text-rose-600">-MXN ${{ number_format($payoutKpis['fees_retained'] ?? 0, 2) }}</div>
                <span class="text-[11px] text-slate-400">Tasa efectiva consolidada de comisiones</span>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Depósitos Conciliados</span>
                <div class="mt-2 text-xl font-black font-mono text-emerald-600">MXN ${{ number_format($payoutKpis['reconciled_amount'] ?? 0, 2) }}</div>
                <span class="text-[11px] text-slate-400">Fondos confirmados en cuenta BBVA</span>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Discrepancias / En Tránsito</span>
                <div class="mt-2 text-xl font-black font-mono text-amber-600">MXN ${{ number_format($payoutKpis['pending_amount'] ?? 0, 2) }}</div>
                <span class="text-[11px] text-slate-400">Lotes programados para dispersión</span>
            </div>
        </div>

        <!-- TABLA DE LOTES BANCARIOS -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-6">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Lotes de Liquidación Bancaria (Payouts)</span>
                    </h3>
                    <p class="text-xs text-slate-500">Cruce automatizado de dispersiones de pasarelas contra depósitos BBVA</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Lote ID</th>
                            <th class="py-3 px-4">Pasarela</th>
                            <th class="py-3 px-4">Monto Bruto</th>
                            <th class="py-3 px-4">Comisiones</th>
                            <th class="py-3 px-4">Neto Depositado</th>
                            <th class="py-3 px-4">Cuenta Destino</th>
                            <th class="py-3 px-4">Fecha Depósito</th>
                            <th class="py-3 px-4">Estado</th>
                            <th class="py-3 px-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-800 font-sans">
                        @foreach($payoutBatches as $pb)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $pb->payout_id_external }}</td>
                                <td class="py-3 px-4 font-semibold uppercase text-slate-700">{{ $pb->gateway }}</td>
                                <td class="py-3 px-4 font-mono font-semibold">MXN ${{ number_format((float) $pb->gross_amount, 2) }}</td>
                                <td class="py-3 px-4 font-mono text-rose-600">-MXN ${{ number_format((float) ($pb->fee_amount + $pb->tax_withheld), 2) }}</td>
                                <td class="py-3 px-4 font-mono font-black text-emerald-700">MXN ${{ number_format((float) $pb->net_deposited, 2) }}</td>
                                <td class="py-3 px-4 text-slate-500 text-[11px]">{{ $pb->bank_account_target }}</td>
                                <td class="py-3 px-4 text-slate-500 text-[11px]">{{ $pb->deposit_date ? $pb->deposit_date->format('d M, Y H:i') : 'Pendiente' }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $pb->status === 'reconciled' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $pb->status === 'reconciled' ? 'Conciliado' : 'Pendiente Dispersión' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($pb->status === 'pending')
                                        <form method="POST" action="{{ route('portal.finance.payouts.reconcile', $pb->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2.5 py-1 rounded-lg cursor-pointer transition">
                                                Conciliar Lote
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[11px] text-slate-400 font-semibold">Auditado</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SECCIÓN 5: PESTAÑA 3 — MRR & SUSCRIPCIONES (TASK-SUB-DUN)                  --}}
    {{-- ========================================================================= --}}
    <div data-finance-panel="subscriptions" class="{{ ($activeTab ?? '') === 'subscriptions' ? '' : 'hidden' }}">
        <!-- KPI METRICS SUBMÓDULO 3 -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">MRR Recurrente Activo</span>
                <div class="mt-2 text-xl font-black font-mono text-indigo-700">MXN ${{ number_format($subscriptionKpis['mrr_active'] ?? 0, 2) }}</div>
                <span class="text-[11px] text-slate-400">Ingreso predecible mensual contratado</span>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Suscriptores Vigentes</span>
                <div class="mt-2 text-xl font-black font-mono text-slate-900">{{ $subscriptionKpis['active_subscribers'] ?? 0 }} Miembros</div>
                <span class="text-[11px] text-slate-400">Caja Marte Nivel 1, 2 y 3</span>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Tasa Churn Mensual</span>
                <div class="mt-2 text-xl font-black font-mono text-emerald-600">{{ $subscriptionKpis['churn_rate'] ?? '2.1%' }}</div>
                <span class="text-[11px] text-slate-400">Nivel saludable &lt; 3.5%</span>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Cobros Rescatados (Dunning)</span>
                <div class="mt-2 text-xl font-black font-mono text-emerald-700">MXN ${{ number_format($subscriptionKpis['recovered_dunning'] ?? 0, 2) }}</div>
                <span class="text-[11px] text-slate-400">Recuperados por motor TASK-SUB-DUN</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- TABLA DE SUSCRIPCIONES (2 COLS) -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Membresías MRR (Caja Marte)</span>
                        </h3>
                        <p class="text-xs text-slate-500">Suscripciones recurrentes con renovación mensual automática</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold text-[11px]">
                            <tr>
                                <th class="py-3 px-4">Folio</th>
                                <th class="py-3 px-4">Nivel / Tier</th>
                                <th class="py-3 px-4">Monto Mes</th>
                                <th class="py-3 px-4">Método</th>
                                <th class="py-3 px-4">Próximo Cobro</th>
                                <th class="py-3 px-4">Estado</th>
                                <th class="py-3 px-4 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-slate-800">
                            @foreach($subscriptions as $sub)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $sub->subscription_folio }}</td>
                                    <td class="py-3 px-4 font-semibold text-slate-700">{{ $sub->tier_name }}</td>
                                    <td class="py-3 px-4 font-mono font-black text-indigo-700">MXN ${{ number_format((float) $sub->monthly_amount, 2) }}</td>
                                    <td class="py-3 px-4 uppercase text-[11px] font-mono text-slate-600">{{ $sub->card_brand }} *{{ $sub->card_last4 }}</td>
                                    <td class="py-3 px-4 text-slate-500 text-[11px]">{{ $sub->next_billing_at ? $sub->next_billing_at->format('d M, Y') : ($sub->cancelled_at ? 'Cancelada' : 'Pausada') }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $sub->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($sub->status === 'past_due' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-600') }}">
                                            {{ $sub->status === 'active' ? 'Activa' : ($sub->status === 'past_due' ? 'Fallo Cobro' : 'Cancelada') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        @if($sub->status === 'past_due')
                                            <form method="POST" action="{{ route('portal.finance.subscriptions.retry', $sub->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded-lg cursor-pointer transition">
                                                    Reintentar
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-[11px] text-slate-400 font-semibold">Al día</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- COLA DE DUNNING REACTIVA (1 COL) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Motor Dunning (TASK-SUB-DUN)</span>
                    </h3>
                </div>
                <p class="text-xs text-slate-500 mb-4">Historial de reintentos escalonados y rescates automáticos de cobro</p>

                <div class="space-y-3 text-xs">
                    @forelse($dunningLogs as $log)
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-slate-800">{{ $log->subscription?->subscription_folio ?? 'SUB-GEN' }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $log->was_recovered ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $log->was_recovered ? 'Rescatado' : 'Reintento #' . $log->attempt_number }}
                                </span>
                            </div>
                            <div class="mt-1 flex items-center justify-between text-slate-500 text-[11px]">
                                <span>Causa: {{ $log->decline_code ?? 'insufficient_funds' }}</span>
                                <span class="font-medium text-slate-700">{{ $log->action_taken }}</span>
                            </div>
                            @if($log->was_recovered)
                                <div class="mt-1 font-mono font-bold text-emerald-700 text-[11px]">
                                    +MXN ${{ number_format((float) $log->amount_recovered, 2) }} recuperado
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-4 text-center text-slate-400 text-xs">No hay eventos de cobro declinado pendientes.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SECCIÓN 6: PESTAÑA 4 — MARGEN NETO REAL POR PEDIDO (TASK-FIN-01)          --}}
    {{-- ========================================================================= --}}
    <div data-finance-panel="margins" class="{{ ($activeTab ?? '') === 'margins' ? '' : 'hidden' }}">
        <!-- KPI METRICS SUBMÓDULO 4 -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Ticket Promedio Bruto</span>
                <div class="mt-2 text-xl font-black font-mono text-slate-900">MXN ${{ number_format($marginKpis['avg_ticket'] ?? 0, 2) }}</div>
                <span class="text-[11px] text-slate-400">Valor medio por pedido facturado</span>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Margen Neto Promedio</span>
                <div class="mt-2 text-xl font-black font-mono text-emerald-700">{{ $marginKpis['avg_margin'] ?? 0 }}%</div>
                <span class="text-[11px] text-slate-400">Rentabilidad líquida tras toda absorción</span>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Insumo Empaque Medio</span>
                <div class="mt-2 text-xl font-black font-mono text-slate-800">MXN ${{ number_format($marginKpis['avg_packaging'] ?? 0, 2) }}</div>
                <span class="text-[11px] text-slate-400">Caja de cartón + bolsa segura</span>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Flete Courier Medio</span>
                <div class="mt-2 text-xl font-black font-mono text-slate-800">MXN ${{ number_format($marginKpis['avg_shipping'] ?? 0, 2) }}</div>
                <span class="text-[11px] text-slate-400">Guía DHL / Estafeta por envío</span>
            </div>
        </div>

        <!-- TABLA DE AUDITORÍA TICKET POR TICKET -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-6">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>Auditoría de Margen Neto Real Ticket por Ticket (TASK-FIN-01)</span>
                    </h3>
                    <p class="text-xs text-slate-500">Cálculo exacto: PVP Pagado - COGS - Pasarela - Empaque - Courier</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Orden</th>
                            <th class="py-3 px-4">Universo</th>
                            <th class="py-3 px-4">PVP Pagado</th>
                            <th class="py-3 px-4">COGS Prenda</th>
                            <th class="py-3 px-4">Pasarela</th>
                            <th class="py-3 px-4">Empaque</th>
                            <th class="py-3 px-4">Courier</th>
                            <th class="py-3 px-4">Utilidad Neta</th>
                            <th class="py-3 px-4">Margen %</th>
                            <th class="py-3 px-4 text-right">Salud</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-800">
                        @foreach($orderMargins as $om)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $om->order_number }}</td>
                                <td class="py-3 px-4 uppercase text-[11px] font-semibold text-slate-600">{{ $om->universe }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">MXN ${{ number_format((float) $om->gross_revenue, 2) }}</td>
                                <td class="py-3 px-4 font-mono text-rose-600">-MXN ${{ number_format((float) $om->cogs_total, 2) }}</td>
                                <td class="py-3 px-4 font-mono text-rose-600">-MXN ${{ number_format((float) $om->gateway_fee, 2) }}</td>
                                <td class="py-3 px-4 font-mono text-rose-600">-MXN ${{ number_format((float) $om->packaging_cost, 2) }}</td>
                                <td class="py-3 px-4 font-mono text-rose-600">-MXN ${{ number_format((float) $om->shipping_courier_cost, 2) }}</td>
                                <td class="py-3 px-4 font-mono font-black text-emerald-700">MXN ${{ number_format((float) $om->net_profit, 2) }}</td>
                                <td class="py-3 px-4 font-mono font-bold {{ (float) $om->net_margin_percent >= 50 ? 'text-emerald-700' : ((float) $om->net_margin_percent >= 30 ? 'text-amber-700' : 'text-rose-700') }}">
                                    {{ $om->net_margin_percent }}%
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $om->margin_health_level === 'healthy' ? 'bg-emerald-100 text-emerald-800' : ($om->margin_health_level === 'standard' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $om->margin_health_level === 'healthy' ? 'Saludable (>50%)' : ($om->margin_health_level === 'standard' ? 'Estándar' : 'Crítico') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SECCIÓN 7: PESTAÑA 5 — CUENTAS POR PAGAR & EGRESOS (TASK-INT-19)           --}}
    {{-- ========================================================================= --}}
    <div data-finance-panel="expenses" class="{{ ($activeTab ?? '') === 'expenses' ? '' : 'hidden' }}">
        <!-- KPI METRICS SUBMÓDULO 5 -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Cuentas por Pagar Esta Semana</span>
                <div class="mt-2 text-xl font-black font-mono text-rose-600">MXN ${{ number_format($vendorBillKpis['pending_week'] ?? 0, 2) }}</div>
                <span class="text-[11px] text-slate-400">Compromisos con vencimiento inmediato</span>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Talleres Textiles & Maquila</span>
                <div class="mt-2 text-xl font-black font-mono text-slate-900">MXN ${{ number_format($vendorBillKpis['workshops_pending'] ?? 0, 2) }}</div>
                <span class="text-[11px] text-slate-400">Torzo B2B y Talleres CDMX</span>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Pauta Publicitaria Meta/Google</span>
                <div class="mt-2 text-xl font-black font-mono text-slate-900">MXN ${{ number_format($vendorBillKpis['ads_invoiced'] ?? 0, 2) }}</div>
                <span class="text-[11px] text-slate-400">Facturación publicitaria mensual</span>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Días Promedio de Pago</span>
                <div class="mt-2 text-xl font-black font-mono text-indigo-700">{{ $vendorBillKpis['avg_payment_days'] ?? '14 días' }}</div>
                <span class="text-[11px] text-slate-400">Plazo medio de liquidación comercial</span>
            </div>
        </div>

        <!-- TABLA DE CUENTAS POR PAGAR -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-6">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Programación de Cuentas por Pagar (Vendor Bills)</span>
                    </h3>
                    <p class="text-xs text-slate-500">Control y liquidación de facturas a talleres, laboratorios y fleteras</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Factura ID</th>
                            <th class="py-3 px-4">Proveedor / Beneficiario</th>
                            <th class="py-3 px-4">Tipo</th>
                            <th class="py-3 px-4">Monto</th>
                            <th class="py-3 px-4">Banco CLABE</th>
                            <th class="py-3 px-4">Vencimiento</th>
                            <th class="py-3 px-4">Estado</th>
                            <th class="py-3 px-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-800">
                        @foreach($vendorBills as $bill)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $bill->bill_number }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $bill->vendor_name }}</td>
                                <td class="py-3 px-4 text-[11px] text-slate-500 uppercase">{{ $bill->vendor_type }}</td>
                                <td class="py-3 px-4 font-mono font-black text-rose-700">MXN ${{ number_format((float) $bill->amount, 2) }}</td>
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-500">{{ $bill->bank_clabe }} ({{ $bill->bank_name }})</td>
                                <td class="py-3 px-4 text-slate-500 text-[11px]">{{ $bill->due_date ? $bill->due_date->format('d M, Y') : '-' }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $bill->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($bill->status === 'scheduled' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $bill->status === 'paid' ? 'Pagado SPEI' : ($bill->status === 'scheduled' ? 'Programado' : 'Pendiente') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($bill->status !== 'paid')
                                        <form method="POST" action="{{ route('portal.finance.vendor_bills.pay', $bill->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 px-2.5 py-1 rounded-lg cursor-pointer transition">
                                                Pagar SPEI
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[11px] font-mono text-emerald-700 font-semibold">{{ $bill->payment_reference ?? 'PAGADO' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT REACTIVO DE PESTAÑAS (VANILLA JS LIGERO, SIN DEPENDENCIAS PESADAS) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('[data-finance-tab]');
    const tabPanels = document.querySelectorAll('[data-finance-panel]');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetTab = this.getAttribute('data-finance-tab');

            // Reset tab button states
            tabButtons.forEach(b => {
                b.classList.remove('border-indigo-600', 'text-indigo-600', 'bg-white', 'shadow-xs');
                b.classList.add('border-transparent', 'text-slate-500');
            });

            // Activate current button
            this.classList.remove('border-transparent', 'text-slate-500');
            this.classList.add('border-indigo-600', 'text-indigo-600', 'bg-white', 'shadow-xs');

            // Toggle panels
            tabPanels.forEach(p => {
                if (p.getAttribute('data-finance-panel') === targetTab) {
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
});
</script>
@endsection
