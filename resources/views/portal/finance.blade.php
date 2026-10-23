@push('styles')
<style>
textarea { resize: vertical !important; }
</style>
@endpush
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

            <!-- TAB 5: CUENTAS POR PAGAR & COTEJO 3 VÍAS -->
            <button 
                type="button"
                data-finance-tab="expenses"
                class="finance-nav-btn py-3 px-4 border-b-2 font-semibold text-sm transition-all flex items-center gap-2 cursor-pointer group relative {{ ($activeTab ?? '') === 'expenses' ? 'border-indigo-600 text-indigo-600 bg-white shadow-xs rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Cuentas por Pagar & Cotejo 3 Vías</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full {{ ($matchingKpis['discrepancy_held_count'] ?? 0) > 0 ? 'bg-rose-100 text-rose-800 font-bold animate-pulse' : 'bg-slate-100 text-slate-600 font-medium' }}">
                    {{ ($matchingKpis['discrepancy_held_count'] ?? 0) > 0 ? ($matchingKpis['discrepancy_held_count'] . ' Retenciones') : count($vendorBills ?? []) . ' Facturas' }}
                </span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Cuentas por Pagar" data-help-title="Cuentas por Pagar & Cotejo 3 Vías" data-help-desc="Cotejo de 3 vías (OC vs Rampa vs Factura Proveedor), retención preventiva SPEI por mermas y motor fiscal SAT (TASK-INT-07).">?</button>
            </button>

            <!-- TAB 6: AUDITORÍA DE PAQUETERÍAS (TASK-INT-08) -->
            <button 
                type="button"
                data-finance-tab="shipping_audit"
                class="finance-nav-btn py-3 px-4 border-b-2 font-semibold text-sm transition-all flex items-center gap-2 cursor-pointer group relative {{ ($activeTab ?? '') === 'shipping_audit' ? 'border-indigo-600 text-indigo-600 bg-white shadow-xs rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Auditoría de Paqueterías</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full {{ ($carrierAuditKpis['total_discrepant_tracks'] ?? 0) > 0 ? 'bg-rose-100 text-rose-800 font-bold animate-pulse' : 'bg-slate-100 text-slate-600 font-medium' }}">
                    {{ ($carrierAuditKpis['total_discrepant_tracks'] ?? 0) > 0 ? ($carrierAuditKpis['total_discrepant_tracks'] . ' Discrepancias') : 'Al Día' }}
                </span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Auditoría Envíos" data-help-title="Auditoría de Paqueterías" data-help-desc="Detección algorítmica de sobreprecios volumétricos y cubicaje inflado en facturas de transportistas contra manifiestos de almacén (TASK-INT-08).">?</button>
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
    {{-- SECCIÓN 7: PESTAÑA 5 — CUENTAS POR PAGAR, COTEJO 3 VÍAS & MOTOR FISCAL SAT --}}
    {{-- ========================================================================= --}}
    <div data-finance-panel="expenses" class="{{ ($activeTab ?? '') === 'expenses' ? '' : 'hidden' }} space-y-6">
        
        <!-- 1. MOTOR FISCAL MEXICANO SAT (TASK-FIN-02) -->
        <div class="bg-gradient-to-r from-slate-900 to-indigo-950 rounded-2xl p-5 text-white shadow-md">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-white/10 pb-4 mb-4">
                <div>
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Consola Fiscal SAT // Base PVP / 1.16 & Retenciones (Periodo {{ $taxSummary['period'] ?? date('Y-m') }})</span>
                    </h3>
                    <p class="text-[11px] text-slate-300 mt-0.5">IVA Trasladado 16% vs Retenciones Pasarelas (Art. 113-A ISR 1.0% / Art. 18-J IVA 8.0%)</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 font-mono text-xs font-bold border border-emerald-500/30">
                        IVA Neto a Pagar: MXN ${{ number_format($taxSummary['kpis']['net_sat_iva_payable'] ?? 0, 2) }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                <div class="bg-white/5 rounded-xl p-3 border border-white/10">
                    <span class="text-[10px] uppercase font-semibold text-slate-400 block">Facturación Bruta</span>
                    <span class="text-sm font-mono font-bold text-white mt-1 block">MXN ${{ number_format($taxSummary['kpis']['gross_revenue'] ?? 0, 2) }}</span>
                    <span class="text-[10px] text-slate-400">PVP Cobrado</span>
                </div>
                <div class="bg-white/5 rounded-xl p-3 border border-white/10">
                    <span class="text-[10px] uppercase font-semibold text-slate-400 block">Base Gravable</span>
                    <span class="text-sm font-mono font-bold text-slate-200 mt-1 block">MXN ${{ number_format($taxSummary['kpis']['taxable_base'] ?? 0, 2) }}</span>
                    <span class="text-[10px] text-slate-400">PVP / 1.16</span>
                </div>
                <div class="bg-white/5 rounded-xl p-3 border border-white/10">
                    <span class="text-[10px] uppercase font-semibold text-slate-400 block">IVA Trasladado</span>
                    <span class="text-sm font-mono font-bold text-indigo-300 mt-1 block">MXN ${{ number_format($taxSummary['kpis']['iva_transferred_16'] ?? 0, 2) }}</span>
                    <span class="text-[10px] text-slate-400">Tasa 16% SAT</span>
                </div>
                <div class="bg-white/5 rounded-xl p-3 border border-white/10">
                    <span class="text-[10px] uppercase font-semibold text-slate-400 block">Retención ISR</span>
                    <span class="text-sm font-mono font-bold text-amber-300 mt-1 block">-MXN ${{ number_format($taxSummary['kpis']['platform_retentions_isr_1'] ?? 0, 2) }}</span>
                    <span class="text-[10px] text-slate-400">1.0% Art. 113-A</span>
                </div>
                <div class="bg-white/5 rounded-xl p-3 border border-white/10">
                    <span class="text-[10px] uppercase font-semibold text-slate-400 block">Retención IVA</span>
                    <span class="text-sm font-mono font-bold text-amber-300 mt-1 block">-MXN ${{ number_format($taxSummary['kpis']['platform_retentions_iva_8'] ?? 0, 2) }}</span>
                    <span class="text-[10px] text-slate-400">8.0% Art. 18-J</span>
                </div>
                <div class="bg-emerald-500/10 rounded-xl p-3 border border-emerald-500/30">
                    <span class="text-[10px] uppercase font-semibold text-emerald-300 block">Líquido al SAT</span>
                    <span class="text-sm font-mono font-black text-emerald-400 mt-1 block">MXN ${{ number_format($taxSummary['kpis']['net_sat_iva_payable'] ?? 0, 2) }}</span>
                    <span class="text-[10px] text-emerald-300">Impuesto Final</span>
                </div>
            </div>
        </div>

        <!-- 2. KPIS COTEJO DE 3 VÍAS INBOUND (TASK-INT-07) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Inbound Auditados (3 Vías)</span>
                <div class="mt-2 text-xl font-black font-mono text-slate-900">{{ $matchingKpis['total_inbound_audited'] ?? 0 }}</div>
                <span class="text-[11px] text-slate-400">OC vs Rampa vs Factura Proveedor</span>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Cotejos Perfectos (OK)</span>
                <div class="mt-2 text-xl font-black font-mono text-emerald-700">{{ $matchingKpis['matched_count'] ?? 0 }}</div>
                <span class="text-[11px] text-slate-400">Sin merma ni variación de precio</span>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Retenciones Preventivas SPEI</span>
                <div class="mt-2 text-xl font-black font-mono text-rose-600">{{ $matchingKpis['discrepancy_held_count'] ?? 0 }}</div>
                <span class="text-[11px] text-slate-400">Bloqueo de dispersión activo</span>
            </div>
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold uppercase text-slate-500">Importe Retenido en CxP</span>
                <div class="mt-2 text-xl font-black font-mono text-rose-700">MXN ${{ number_format($matchingKpis['total_held_amount'] ?? 0, 2) }}</div>
                <span class="text-[11px] text-slate-400">Protección preventiva de tesorería</span>
            </div>
        </div>

        <!-- 3. EVALUADOR MANUAL / INGESTA DE COTEJO 3 VÍAS -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Cotejo de 3 Vías Inbound (OC vs Rampa vs Factura Proveedor)</span>
                    </h3>
                    <p class="text-xs text-slate-500">Cruce automatizado para bloqueo preventivo SPEI y emisión de Notas de Débito por mermas</p>
                </div>
                <button type="button" onclick="loadSampleThreeWayMatching()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition cursor-pointer">
                    Cargar Ejemplo Inbound
                </button>
            </div>

            <form method="POST" action="{{ route('portal.finance.three_way_matching.verify') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200/70">
                    <!-- COLUMNA 1: OC ORIGINAL -->
                    <div class="space-y-2 bg-white p-3 rounded-lg border border-slate-200">
                        <span class="block text-[11px] uppercase font-bold text-indigo-700">1. Orden de Compra (OC)</span>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-0.5">Folio OC</label>
                            <input type="text" name="po_code" id="twmPoCode" required placeholder="OC-2026-082" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-0.5">Proveedor</label>
                            <input type="text" name="supplier_name" id="twmSupplierName" required placeholder="Torzo Labs & Textile Co." class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg">
                            <input type="hidden" name="supplier_id" id="twmSupplierId" value="VND-TORZO">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-0.5">Piezas OC</label>
                                <input type="number" name="po_qty" id="twmPoQty" required min="1" placeholder="150" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-0.5">Total OC ($)</label>
                                <input type="number" step="0.01" name="po_total_amount" id="twmPoTotal" required min="0" placeholder="22500.00" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg font-mono">
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA 2: MUELLE / RAMPA -->
                    <div class="space-y-2 bg-white p-3 rounded-lg border border-slate-200">
                        <span class="block text-[11px] uppercase font-bold text-amber-700">2. Recepción en Rampa</span>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-0.5">Auditor de Andén</label>
                            <input type="text" name="inspected_by" id="twmInspectedBy" value="Rampa Control Central" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-0.5">Recibidas Físicas</label>
                                <input type="number" name="warehouse_received_qty" id="twmReceivedQty" required min="0" placeholder="142" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-0.5">Mermas / Dañadas</label>
                                <input type="number" name="warehouse_damaged_qty" id="twmDamagedQty" required min="0" placeholder="8" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg font-mono">
                            </div>
                        </div>
                        <div class="p-2 bg-amber-50 rounded text-[10px] text-amber-800">
                            Piezas Aptas = Recibidas - Mermas. Si difiere de la OC, se activa bloqueo preventivo SPEI.
                        </div>
                    </div>

                    <!-- COLUMNA 3: FACTURA PROVEEDOR -->
                    <div class="space-y-2 bg-white p-3 rounded-lg border border-slate-200">
                        <span class="block text-[11px] uppercase font-bold text-emerald-700">3. Factura del Proveedor</span>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-0.5">Folio Factura</label>
                            <input type="text" name="invoice_number" id="twmInvoiceNumber" required placeholder="FAC-TORZO-9912" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg font-mono">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-0.5">Piezas Facturadas</label>
                                <input type="number" name="invoice_qty" id="twmInvoiceQty" required min="1" placeholder="150" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-0.5">Importe Bruto ($)</label>
                                <input type="number" step="0.01" name="invoice_gross_amount" id="twmInvoiceGross" required min="0" placeholder="22500.00" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg font-mono">
                            </div>
                        </div>
                        <div class="p-2 bg-emerald-50 rounded text-[10px] text-emerald-800">
                            Se cotejará el precio unitario y cantidad contra la recepción real en muelle.
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-xs transition cursor-pointer">
                        Ejecutar Cotejo de 3 Vías
                    </button>
                </div>
            </form>
        </div>

        <!-- 4. TABLA VISUALIZADORA DE COTEJOS 3 VÍAS -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-6">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Historial de Cotejos Inbound & Control de Pagos SPEI</span>
                    </h3>
                    <p class="text-xs text-slate-500">Auditoría cruzada en tiempo real de órdenes de compra, muelle y facturación</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold text-[11px]">
                        <tr>
                            <th class="py-3 px-4">OC & Proveedor</th>
                            <th class="py-3 px-4">OC Cant / $</th>
                            <th class="py-3 px-4">Rampa (Recibido vs Merma)</th>
                            <th class="py-3 px-4">Factura Proveedor</th>
                            <th class="py-3 px-4">Nota de Débito / Neto</th>
                            <th class="py-3 px-4">Estado & Semáforo</th>
                            <th class="py-3 px-4 text-right">Dispersión SPEI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-800">
                        @forelse($threeWayMatchings as $twm)
                            <tr class="hover:bg-slate-50/70 transition {{ $twm->match_status === 'discrepancy_held' ? 'bg-rose-50/30' : '' }}">
                                <td class="py-3 px-4">
                                    <span class="font-mono font-bold text-slate-900 block">{{ $twm->po_code }}</span>
                                    <span class="text-[11px] text-slate-500 block">{{ $twm->supplier_name }}</span>
                                </td>
                                <td class="py-3 px-4 font-mono">
                                    <span class="font-bold text-slate-800 block">{{ $twm->po_qty }} pzas</span>
                                    <span class="text-[11px] text-slate-500">MXN ${{ number_format((float) $twm->po_total_amount, 2) }}</span>
                                </td>
                                <td class="py-3 px-4 font-mono">
                                    <span class="text-slate-800 block">{{ $twm->warehouse_received_qty }} recibidas</span>
                                    @if($twm->warehouse_damaged_qty > 0)
                                        <span class="text-[11px] font-bold text-rose-600 block">-{{ $twm->warehouse_damaged_qty }} mermas / dañadas</span>
                                    @else
                                        <span class="text-[11px] text-emerald-600 block">0 mermas</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-mono font-bold text-slate-900 block">{{ $twm->invoice_number }}</span>
                                    <span class="text-[11px] font-mono text-slate-600">{{ $twm->invoice_qty }} pzas (MXN ${{ number_format((float) $twm->invoice_gross_amount, 2) }})</span>
                                </td>
                                <td class="py-3 px-4 font-mono">
                                    @if($twm->debit_note_number)
                                        <span class="text-indigo-700 font-bold block">{{ $twm->debit_note_number }}</span>
                                        <span class="text-[10px] text-indigo-600 block">-MXN ${{ number_format((float) $twm->debit_note_amount, 2) }}</span>
                                    @elseif($twm->debit_note_amount > 0)
                                        <span class="text-rose-600 font-bold block">Sugerida: ${{ number_format((float) $twm->debit_note_amount, 2) }}</span>
                                    @else
                                        <span class="text-slate-400 block">$0.00</span>
                                    @endif
                                    <span class="font-bold text-slate-900 block mt-0.5">Neto: ${{ number_format((float) $twm->payable_net_amount, 2) }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="space-y-1">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $twm->match_status === 'matched' ? 'bg-emerald-100 text-emerald-800' : ($twm->match_status === 'approved_for_payment' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($twm->match_status === 'debit_note_issued' ? 'bg-indigo-100 text-indigo-800' : 'bg-rose-100 text-rose-800 animate-pulse')) }}">
                                            {{ match($twm->match_status) {
                                                'matched' => 'COTEJO PERFECTO (3 VÍAS OK)',
                                                'debit_note_issued' => 'NOTA DE DÉBITO COMPENSADA',
                                                'approved_for_payment' => 'PAGO SPEI AUTORIZADO',
                                                default => 'RETENCIÓN PREVENTIVA SPEI'
                                            } }}
                                        </span>
                                        @if($twm->discrepancy_reason)
                                            <span class="text-[10px] text-slate-500 block leading-tight">{{ $twm->discrepancy_reason }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($twm->match_status === 'discrepancy_held' || $twm->spei_payout_blocked)
                                            <button 
                                                type="button" 
                                                onclick="openDebitNoteModal({{ $twm->id }}, '{{ $twm->po_code }}', '{{ $twm->invoice_number }}', {{ $twm->debit_note_amount }})" 
                                                class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white text-[11px] font-bold rounded-lg shadow-2xs transition cursor-pointer"
                                            >
                                                Emitir Nota Débito
                                            </button>
                                            <span class="px-2 py-1 bg-rose-50 border border-rose-200 text-rose-700 text-[10px] font-bold rounded-lg cursor-not-allowed" title="Dispersión retenida preventivamente por faltantes o discrepancias">
                                                SPEI Retenido
                                            </span>
                                        @elseif($twm->match_status === 'approved_for_payment')
                                            <span class="text-emerald-700 font-mono font-bold text-[11px]">DISPERSADO</span>
                                        @elseif(in_array($twm->match_status, ['matched', 'debit_note_issued']))
                                            <button 
                                                type="button" 
                                                onclick="openSpeiModal({{ $twm->id }}, '{{ $twm->po_code }}', {{ $twm->payable_net_amount }})" 
                                                class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded-lg shadow-2xs transition cursor-pointer"
                                            >
                                                Autorizar SPEI
                                            </button>
                                        @else
                                            <span class="px-2 py-1 bg-slate-100 text-slate-400 text-[10px] font-semibold rounded-lg">Pendiente</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-slate-400">
                                    No hay cotejos registrados. Use el formulario superior o "Cargar Ejemplo Inbound" para iniciar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. PROGRAMACIÓN DE CUENTAS POR PAGAR (VENDOR BILLS) -->
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
                                        @if($bill->is_spei_blocked || ($bill->threeWayMatching && $bill->threeWayMatching->spei_payout_blocked))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-200 rounded-lg cursor-not-allowed" title="Dispersión bloqueada: Retención preventiva por discrepancia en cotejo de 3 vías">
                                                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                SPEI Retenido
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('portal.finance.vendor_bills.pay', $bill->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 px-2.5 py-1 rounded-lg cursor-pointer transition">
                                                    Pagar SPEI
                                                </button>
                                            </form>
                                        @endif
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

    {{-- ========================================================================= --}}
    {{-- PANEL 6: AUDITORÍA DE FACTURAS DE PAQUETERÍA (TASK-INT-08)               --}}
    {{-- ========================================================================= --}}
    <div data-finance-panel="shipping_audit" class="{{ ($activeTab ?? '') === 'shipping_audit' ? '' : 'hidden' }} space-y-6">
        <!-- 1. KPIS DE AUDITORÍA LOGÍSTICA -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Guías Auditadas</span>
                    <span class="text-2xl font-bold font-mono text-slate-900 mt-1 block">{{ number_format($carrierAuditKpis['total_audited_tracks'] ?? 0) }}</span>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Cotejadas vs Manifiestos</span>
                    <span class="font-bold text-slate-700">Hub 6 Rampa</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Discrepancias Detectadas</span>
                    <span class="text-2xl font-bold font-mono {{ ($carrierAuditKpis['total_discrepant_tracks'] ?? 0) > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-1 block">
                        {{ number_format($carrierAuditKpis['total_discrepant_tracks'] ?? 0) }}
                    </span>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Sobreprecio > 10%</span>
                    <span class="font-bold text-rose-600">Alerta Cobro</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Sobreprecio Reclamable</span>
                    <span class="text-2xl font-bold font-mono text-rose-600 mt-1 block">
                        MXN ${{ number_format($carrierAuditKpis['total_overcharge_amount'] ?? 0, 2) }}
                    </span>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Cubicaje inflado / Tarifas</span>
                    <span class="font-bold text-slate-700">Recuperable</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Expedientes de Reclamación</span>
                    <span class="text-2xl font-bold font-mono text-indigo-600 mt-1 block">
                        {{ $carrierAuditKpis['total_claims_count'] ?? 0 }}
                    </span>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Retenido en CxP:</span>
                    <span class="font-bold text-indigo-700 font-mono">MXN ${{ number_format($carrierAuditKpis['total_claimed_amount'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- 2. INGESTA Y CRUCE ALGORÍTMICO DE FACTURA COURIER -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <span>Ingesta y Auditoría de Factura de Transportista</span>
                    </h3>
                    <p class="text-xs text-slate-500">Cruce automatizado de peso volumétrico IATA (L x W x H / 5000) y tarifa pactada contra el cobro de la paquetería</p>
                </div>
                <button type="button" onclick="loadSampleShippingAudit()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition cursor-pointer">
                    Cargar Ejemplo de Prueba
                </button>
            </div>

            <form method="POST" action="{{ route('portal.finance.shipping_audit.upload') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Paquetería / Fletera</label>
                        <select name="carrier_id" id="auditCarrierSelect" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-white font-semibold text-slate-800 focus:outline-indigo-600">
                            <option value="dhl">DHL Express México</option>
                            <option value="estafeta">Estafeta Mexicana</option>
                            <option value="fedex">FedEx Express</option>
                            <option value="skydropx">Skydropx API</option>
                            <option value="99minutos">99 Minutos</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Folio de Factura del Proveedor</label>
                        <input type="text" name="invoice_folio" id="auditInvoiceFolio" required placeholder="F-DHL-99201948" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-slate-800 font-mono bg-white focus:outline-indigo-600">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Archivo CSV de la Paquetería</label>
                        <input type="file" name="report_file" accept=".csv,.txt" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">O pegar datos en formato CSV directo:</label>
                    <textarea name="raw_csv" id="auditRawCsv" rows="3" placeholder="guia,peso_kg,costo_facturado,pedido" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-slate-800 font-mono text-[11px] focus:outline-indigo-600 bg-white"></textarea>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Encabezados esperados: <code>guia, peso_kg, costo_facturado, pedido</code>. El sistema cruzará con el tipo de caja y manifiesto de rampa.</span>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-xs transition cursor-pointer">
                        Ejecutar Auditoría Algorítmica contra Manifiestos
                    </button>
                </div>
            </form>
        </div>

        <!-- 3. HISTORIAL DE RECONCILIACIONES Y SEMÁFORO DE PARTIDAS -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Lotes de Conciliación de Fletes & Semáforo de Discrepancias</span>
                    </h3>
                    <p class="text-xs text-slate-500">Detección de cobros indebidos por sobrepeso o tarifas fuera de contrato</p>
                </div>

                <button 
                    type="button" 
                    onclick="openClaimModal()" 
                    class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer flex items-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Generar Expediente de Reclamación</span>
                </button>
            </div>

            @if(count($shippingReconciliations ?? []) > 0)
                <div class="space-y-6">
                    @foreach($shippingReconciliations as $recon)
                        <div class="border border-slate-200/90 rounded-xl p-4 bg-slate-50/50 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-200/70 pb-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-slate-900 text-xs">{{ $recon->folio }}</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">{{ $recon->carrier_label }}</span>
                                        <span class="text-xs text-slate-500 font-mono">Factura: {{ $recon->invoice_folio ?? $recon->invoice_number }}</span>
                                    </div>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">Auditado por {{ $recon->audited_by ?? 'Sistema' }} • {{ $recon->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-xs">
                                    <div class="text-right">
                                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Facturado / Esperado</span>
                                        <span class="font-mono font-bold text-slate-800">MXN ${{ number_format($recon->total_invoiced_amount, 2) }}</span>
                                        <span class="text-slate-400 text-[11px] font-mono">/ ${{ number_format($recon->total_expected_amount, 2) }}</span>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold font-mono {{ $recon->total_overcharge_amount > 0 ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $recon->total_overcharge_amount > 0 ? '+MXN $' . number_format($recon->total_overcharge_amount, 2) . ' Sobrepeso' : 'Conciliado OK' }}
                                    </span>
                                </div>
                            </div>

                            <!-- TABLA DE PARTIDAS DE ESTE LOTE -->
                            <div class="overflow-x-auto bg-white rounded-lg border border-slate-200/80">
                                <table class="w-full text-xs text-left">
                                    <thead>
                                        <tr class="border-b border-slate-100 text-slate-400 font-semibold uppercase tracking-wider text-[10px]">
                                            <th class="py-2 px-3 w-8 text-center">
                                                <input type="checkbox" onclick="toggleReconCheckboxes(this, {{ $recon->id }})" class="rounded text-indigo-600 focus:ring-indigo-500">
                                            </th>
                                            <th class="py-2 px-3">Guía & Pedido</th>
                                            <th class="py-2 px-3">Caja / Dimensión IATA</th>
                                            <th class="py-2 px-3">Peso Almacén vs Báscula</th>
                                            <th class="py-2 px-3">Tarifa Pactada vs Cobrada</th>
                                            <th class="py-2 px-3">Diferencia</th>
                                            <th class="py-2 px-3">Semáforo & Motivo</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                                        @forelse($recon->items as $item)
                                            <tr class="hover:bg-slate-50 transition {{ $item->audit_status === 'discrepancy' ? 'bg-rose-50/40' : '' }}">
                                                <td class="py-2.5 px-3 text-center">
                                                    @if($item->audit_status === 'discrepancy')
                                                        <input type="checkbox" name="claim_items[]" value="{{ $item->id }}" data-recon-id="{{ $recon->id }}" class="recon-claim-cb rounded text-rose-600 focus:ring-rose-500 cursor-pointer">
                                                    @else
                                                        <span class="text-slate-300 text-[10px]">-</span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    <span class="font-bold text-slate-900 block">{{ $item->tracking_number }}</span>
                                                    <span class="text-[10px] text-slate-500 font-sans">{{ $item->order_number ?? 'Expedición directa' }}</span>
                                                </td>
                                                <td class="py-2.5 px-3 font-sans">
                                                    <span class="font-medium text-slate-800 block">{{ $item->box_code ?? 'BOX-STD' }}</span>
                                                    <span class="text-[10px] text-slate-400 font-mono">{{ $item->box_length_cm }}x{{ $item->box_width_cm }}x{{ $item->box_height_cm }} cm</span>
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    <span class="text-slate-700 block">Rampa: {{ number_format($item->warehouse_weight_kg, 2) }} kg (Vol: {{ number_format($item->warehouse_volumetric_kg, 2) }})</span>
                                                    <span class="font-bold {{ $item->carrier_billed_weight_kg > max($item->warehouse_weight_kg, $item->warehouse_volumetric_kg) * 1.15 ? 'text-rose-600' : 'text-slate-900' }}">
                                                        Báscula: {{ number_format($item->carrier_billed_weight_kg, 2) }} kg
                                                    </span>
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    <span class="text-slate-600 block">Pactada: ${{ number_format($item->expected_rate_mxn, 2) }}</span>
                                                    <span class="font-bold text-slate-900">Cobrada: ${{ number_format($item->carrier_invoiced_rate_mxn, 2) }}</span>
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    <span class="font-bold block {{ $item->overcharge_amount_mxn > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                                        {{ $item->overcharge_amount_mxn > 0 ? '+MXN $' . number_format($item->overcharge_amount_mxn, 2) : '$0.00' }}
                                                    </span>
                                                    @if($item->overcharge_pct > 0)
                                                        <span class="text-[10px] text-rose-500 block">(+{{ number_format($item->overcharge_pct, 1) }}%)</span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 font-sans">
                                                    <div class="space-y-0.5">
                                                        <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold {{ $item->audit_status === 'discrepancy' ? 'bg-rose-100 text-rose-800' : ($item->audit_status === 'claim_filed' ? 'bg-indigo-100 text-indigo-800' : ($item->audit_status === 'marginal_variance' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800')) }}">
                                                            {{ match($item->audit_status) {
                                                                'discrepancy' => 'SOBREPESO INDEBIDO',
                                                                'claim_filed' => 'RECLAMACIÓN RADICADA',
                                                                'marginal_variance' => 'MARGEN TOLERANCIA',
                                                                default => 'CONCILIADO OK'
                                                            } }}
                                                        </span>
                                                        @if($item->discrepancy_reason)
                                                            <span class="text-[10px] text-slate-500 block leading-tight">{{ $item->discrepancy_reason }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-slate-400 font-sans">Sin partidas registradas en este lote.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                    <p class="text-slate-500 text-xs font-medium">No se han registrado auditorías de paquetería aún.</p>
                    <p class="text-slate-400 text-[11px] mt-1">Cargue un archivo CSV o use el botón "Cargar Ejemplo de Prueba" superior para conciliar facturas contra manifiestos.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL DE RECLAMACIÓN FORMAL DE FLETES (TASK-INT-08) --}}
    <div id="claimModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4 text-xs">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Emitir Expediente Formal de Reclamación</span>
                    </h3>
                    <p class="text-[11px] text-slate-500">Radica retención preventiva en Cuentas por Pagar ante el transportista</p>
                </div>
                <button type="button" onclick="closeClaimModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer font-bold text-lg">&times;</button>
            </div>

            <form id="shippingClaimForm" method="POST" action="" class="space-y-4">
                @csrf
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl space-y-1">
                    <span class="block text-rose-900 font-bold">Partidas Seleccionadas con Discrepancia:</span>
                    <span id="selectedClaimCount" class="text-rose-700 block font-mono">0 partidas seleccionadas</span>
                    <span class="text-[10px] text-rose-600 block">El sistema bloqueará el pago correspondiente en Cuentas por Pagar hasta resolución de la fletera.</span>
                </div>

                <div id="hiddenClaimItemsContainer"></div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Notas Técnicas para el Expediente:</label>
                    <textarea name="claim_notes" rows="3" required placeholder="Fundamentación de cubicaje inflado según dimensiones estandarizadas de empaque y manifiesto de rampa..." class="w-full px-3 py-2 border border-slate-300 rounded-xl text-slate-800 focus:outline-indigo-600 bg-white">Discrepancia volumétrica > 10% provocada por calibración errónea de báscula de andén del transportista. Las dimensiones corresponden a cajas microcorrugadas estandarizadas.</textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeClaimModal()" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50 transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold shadow-xs transition cursor-pointer">
                        Emitir Expediente & Radicar Retención
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DE NOTA DE DÉBITO COMPENSATORIA (TASK-INT-07) --}}
    <div id="debitNoteModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4 text-xs">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Emitir Nota de Débito Compensatoria</span>
                    </h3>
                    <p class="text-[11px] text-slate-500">Compensa mermas/faltantes y libera retención preventiva SPEI</p>
                </div>
                <button type="button" onclick="closeDebitNoteModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer font-bold text-lg">&times;</button>
            </div>

            <form id="debitNoteForm" method="POST" action="" class="space-y-4">
                @csrf
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl space-y-1">
                    <span class="block text-amber-900 font-bold">Orden de Compra: <span id="dnPoCode" class="font-mono"></span></span>
                    <span class="block text-amber-800 text-[11px]">Factura Proveedor: <span id="dnInvoiceNumber" class="font-mono font-semibold"></span></span>
                    <span class="text-[10px] text-amber-700 block">La emisión de esta nota ajustará el saldo por pagar del proveedor y habilitará la dispersión SPEI del monto neto.</span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Folio de Nota de Débito:</label>
                    <input type="text" name="debit_note_number" id="dnNumber" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-slate-800 font-mono bg-white focus:outline-indigo-600">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Monto de Compensación (MXN $):</label>
                    <input type="number" step="0.01" name="debit_amount" id="dnAmount" required min="0" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-slate-800 font-mono bg-white focus:outline-indigo-600">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeDebitNoteModal()" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50 transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold shadow-xs transition cursor-pointer">
                        Emitir Nota & Liberar SPEI
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DE AUTORIZACIÓN DE DISPERSIÓN SPEI (TASK-INT-07) --}}
    <div id="speiModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4 text-xs">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Autorizar Dispersión SPEI</span>
                    </h3>
                    <p class="text-[11px] text-slate-500">Cotejo solventado. Liberación del pago interbancario</p>
                </div>
                <button type="button" onclick="closeSpeiModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer font-bold text-lg">&times;</button>
            </div>

            <form id="speiForm" method="POST" action="" class="space-y-4">
                @csrf
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl space-y-1">
                    <span class="block text-emerald-900 font-bold">Orden: <span id="speiPoCode" class="font-mono"></span></span>
                    <span class="block text-emerald-800 text-sm font-black font-mono">Monto Neto a Dispersar: <span id="speiAmount"></span> MXN</span>
                    <span class="text-[10px] text-emerald-700 block">Este pago se registrará como liquidado y se actualizará el estado de la cuenta por pagar a 'Pagado SPEI'.</span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Referencia Bancaria SPEI / Clave de Rastreo:</label>
                    <input type="text" name="payment_reference" id="speiRef" required placeholder="SPEI-BBVA-990184" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-slate-800 font-mono bg-white focus:outline-indigo-600">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeSpeiModal()" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50 transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-xs transition cursor-pointer">
                        Confirmar Dispersión SPEI
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SCRIPT REACTIVO DE PESTAÑAS Y AUDITORÍA DE TRANSPORTISTAS --}}
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

function toggleReconCheckboxes(masterCb, reconId) {
    const cbs = document.querySelectorAll(`.recon-claim-cb[data-recon-id="${reconId}"]`);
    cbs.forEach(cb => {
        cb.checked = masterCb.checked;
    });
}

function openClaimModal() {
    const checked = Array.from(document.querySelectorAll('.recon-claim-cb:checked'));
    if (checked.length === 0) {
        alert('Por favor seleccione al menos una partida con sobrepeso indebido para generar el expediente de reclamacion.');
        return;
    }

    const firstReconId = checked[0].getAttribute('data-recon-id');
    const form = document.getElementById('shippingClaimForm');
    form.action = `/portal/finance/shipping-audit/${firstReconId}/claim`;

    const container = document.getElementById('hiddenClaimItemsContainer');
    container.innerHTML = '';
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'claim_items[]';
        input.value = cb.value;
        container.appendChild(input);
    });

    document.getElementById('selectedClaimCount').textContent = `${checked.length} partida(s) seleccionada(s) del lote #${firstReconId}`;
    document.getElementById('claimModal').classList.remove('hidden');
}

function closeClaimModal() {
    document.getElementById('claimModal').classList.add('hidden');
}

function loadSampleShippingAudit() {
    const carrier = document.getElementById('auditCarrierSelect');
    const folio = document.getElementById('auditInvoiceFolio');
    const rawCsv = document.getElementById('auditRawCsv');

    if (carrier) carrier.value = 'dhl';
    if (folio) folio.value = 'FAC-DHL-' + Math.floor(100000 + Math.random() * 900000);
    if (rawCsv) {
        rawCsv.value = "guia,peso_kg,costo_facturado,pedido\nDHL-TEST-001,5.20,380.00,ORD-8001\nDHL-TEST-002,1.30,175.00,ORD-8002\nDHL-TEST-003,8.90,560.00,ORD-8003";
    }
}

function openDebitNoteModal(matchingId, poCode, invoiceNumber, suggestedAmount) {
    const form = document.getElementById('debitNoteForm');
    form.action = `/admin/finanzas/cotejo-3-vias/${matchingId}/nota-debito`;
    document.getElementById('dnPoCode').textContent = poCode;
    document.getElementById('dnInvoiceNumber').textContent = invoiceNumber;
    document.getElementById('dnNumber').value = 'NC-2026-' + Math.floor(1000 + Math.random() * 9000);
    document.getElementById('dnAmount').value = Number(suggestedAmount).toFixed(2);
    document.getElementById('debitNoteModal').classList.remove('hidden');
}

function closeDebitNoteModal() {
    document.getElementById('debitNoteModal').classList.add('hidden');
}

function openSpeiModal(matchingId, poCode, payableAmount) {
    const form = document.getElementById('speiForm');
    form.action = `/admin/finanzas/cotejo-3-vias/${matchingId}/autorizar-spei`;
    document.getElementById('speiPoCode').textContent = poCode;
    document.getElementById('speiAmount').textContent = Number(payableAmount).toLocaleString('es-MX', {minimumFractionDigits: 2});
    document.getElementById('speiRef').value = 'SPEI-BBVA-' + Math.floor(100000 + Math.random() * 900000);
    document.getElementById('speiModal').classList.remove('hidden');
}

function closeSpeiModal() {
    document.getElementById('speiModal').classList.add('hidden');
}

function loadSampleThreeWayMatching() {
    const poCode = document.getElementById('twmPoCode');
    const supplier = document.getElementById('twmSupplierName');
    const supplierId = document.getElementById('twmSupplierId');
    const poQty = document.getElementById('twmPoQty');
    const poTotal = document.getElementById('twmPoTotal');
    const recQty = document.getElementById('twmReceivedQty');
    const damQty = document.getElementById('twmDamagedQty');
    const invNum = document.getElementById('twmInvoiceNumber');
    const invQty = document.getElementById('twmInvoiceQty');
    const invGross = document.getElementById('twmInvoiceGross');

    if (poCode) poCode.value = 'OC-2026-' + Math.floor(100 + Math.random() * 900);
    if (supplier) supplier.value = 'Torzo Labs & Textile Co.';
    if (supplierId) supplierId.value = 'VND-TORZO';
    if (poQty) poQty.value = '150';
    if (poTotal) poTotal.value = '22500.00';
    if (recQty) recQty.value = '142';
    if (damQty) damQty.value = '8';
    if (invNum) invNum.value = 'FAC-TORZO-' + Math.floor(1000 + Math.random() * 9000);
    if (invQty) invQty.value = '150';
    if (invGross) invGross.value = '22500.00';
}
</script>
@endsection
