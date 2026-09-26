@extends('layouts.portal-panel')

@section('title', 'Finanzas & Conciliación')
@section('hub_kicker', 'Hub 5')
@section('hub_title', 'Finanzas & Conciliación')
@section('hub_status', 'MXN $112,400 MRR Activo')

@section('content')
<div id="financeAppRoot" class="w-full text-slate-800 font-sans antialiased" data-screen="finance">
    {{-- ========================================================================= --}}
    {{-- REGLA 10: BARRA DE PESTAÑAS DE SUBMÓDULO PRIMERO (SUBNAV BAR)             --}}
    {{-- ========================================================================= --}}
    <div class="mb-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <nav class="flex gap-2 -mb-px overflow-x-auto" aria-label="Submódulos de Finanzas">
            <!-- TAB 1: FLUJO DE CAJA & P&L -->
            <button 
                type="button"
                data-finance-tab="cashflow"
                class="py-3 px-4 border-b-2 border-indigo-600 text-indigo-600 font-semibold bg-white shadow-sm rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Flujo de Caja & P&L</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 font-bold">EBITDA 30.5%</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Flujo de Caja" data-help-title="Flujo de Caja & P&L" data-help-desc="Estado de resultados en vivo con cascada de absorción de costos directos (COGS, pasarelas, empaque y fletes).">?</button>
            </button>

            <!-- TAB 2: CONCILIACIÓN PASARELAS & SPEI -->
            <button 
                type="button"
                data-finance-tab="reconciliation"
                class="py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Conciliación Pasarelas</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-medium">BBVA &bull; Stripe</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Conciliación" data-help-title="Conciliación de Pasarelas" data-help-desc="Cruce automatizado de lotes de liquidación bancaria (payouts) contra pedidos cobrados y tasa efectiva de comisiones.">?</button>
            </button>

            <!-- TAB 3: MRR & SUSCRIPCIONES -->
            <button 
                type="button"
                data-finance-tab="subscriptions"
                class="py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>MRR & Suscripciones</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700 font-semibold">184 Activas</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Suscripciones" data-help-title="Suscripciones & Dunning" data-help-desc="Monitoreo de ingresos recurrentes (MRR), cohortes de retención y motor de rescate de cobros fallidos (TASK-SUB-DUN).">?</button>
            </button>

            <!-- TAB 4: MARGEN NETO REAL -->
            <button 
                type="button"
                data-finance-tab="margins"
                class="py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Margen Neto Real</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 font-bold">57.9% Prom</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Margen Neto" data-help-title="Unit Economics por Orden" data-help-desc="Auditoría ticket por ticket deduciendo costo de prenda, pasarela, caja de empaque y flete para asegurar cero ventas a pérdida (TASK-FIN-01).">?</button>
            </button>

            <!-- TAB 5: CUENTAS POR PAGAR -->
            <button 
                type="button"
                data-finance-tab="expenses"
                class="py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Cuentas por Pagar</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-medium">Talleres & Proveedores</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Cuentas por Pagar" data-help-title="Cuentas por Pagar" data-help-desc="Programación de pagos a talleres de confección, proveedores dropship (Torzo), laboratorios cosmética y fleteras.">?</button>
            </button>
        </nav>

        <div class="flex items-center gap-3 text-xs pb-2 sm:pb-0">
            <span class="flex items-center gap-1.5 font-medium text-slate-600 bg-white px-2.5 py-1 rounded-full border border-slate-200 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Cierre Fiscal Mensual: En Curso</span>
            </span>
            <button class="text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1.5 cursor-pointer transition-colors" data-action="export-pnl-report">
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
                <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $card['label'] }}</span>
                        <button class="section-help-btn" type="button" aria-label="Ayuda de {{ $card['label'] }}" data-help-title="{{ $card['label'] }}" data-help-desc="Métrica financiera calculada a partir de las transacciones y libros mayores contables de la plataforma.">?</button>
                    </div>
                    <div class="mt-3 flex items-baseline justify-between">
                        <span class="text-2xl font-black font-mono tracking-tight text-slate-900">{{ $card['value'] }}</span>
                        @if($index === 0)
                            <span class="text-xs font-bold text-emerald-600 font-mono">+14.2%</span>
                        @elseif($index === 1)
                            <span class="text-xs font-bold text-slate-600 font-mono">COGS & Fletes</span>
                        @elseif($index === 2)
                            <span class="text-xs font-bold text-indigo-600 font-mono">Neto Real</span>
                        @else
                            <span class="text-xs font-bold text-rose-600 font-mono">3.0%</span>
                        @endif
                    </div>
                    <div class="mt-2 text-[11px] text-slate-400">
                        @if($index === 0)
                            Total capturado en pasarelas y SPEI
                        @elseif($index === 1)
                            Deducciones directas de operación
                        @elseif($index === 2)
                            Rentabilidad ponderada consolidada
                        @else
                            Garantías y devoluciones procesadas
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- SECCIÓN 3: PESTAÑA 1 — CASCADA FINANCIERA P&L & AUDITORÍA DE RENTABILIDAD --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- CASCADA P&L WATERFALL (2 COLS) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        <span>Cascada de Absorción de Costos (P&L Waterfall)</span>
                    </h3>
                    <p class="text-xs text-slate-500">Desglose secuencial de ingresos brutos hasta la utilidad líquida final</p>
                </div>
                <button class="section-help-btn" type="button" aria-label="Ayuda Cascada P&L" data-help-title="Cascada P&L" data-help-desc="Resta paso a paso los costos variables por cada peso vendido para garantizar márgenes saludables.">?</button>
            </div>

            <div class="space-y-3 text-xs">
                <!-- 1. Ingreso Bruto -->
                <div class="p-3 bg-slate-50 rounded-xl flex items-center justify-between">
                    <span class="font-bold text-slate-900">1. Facturación Bruta Total (Ventas D2C + MRR)</span>
                    <span class="font-mono font-black text-slate-900 text-sm">MXN $486,200.00</span>
                </div>

                <!-- 2. Costo de Mercancía COGS -->
                <div class="p-3 bg-rose-50/50 rounded-xl flex items-center justify-between text-rose-900">
                    <span class="font-medium">(-) Costo Directo de Mercancía (COGS Textil & Cosmética)</span>
                    <span class="font-mono font-bold">-MXN $153,700.00 (31.6%)</span>
                </div>

                <!-- 3. Comisiones Pasarelas -->
                <div class="p-3 bg-rose-50/50 rounded-xl flex items-center justify-between text-rose-900">
                    <span class="font-medium">(-) Comisiones de Pasarelas (Stripe, MP, OXXO Pay - 3.6% prom)</span>
                    <span class="font-mono font-bold">-MXN $17,500.00 (3.6%)</span>
                </div>

                <!-- 4. Cajas e Insumos Empaque -->
                <div class="p-3 bg-rose-50/50 rounded-xl flex items-center justify-between text-rose-900">
                    <span class="font-medium">(-) Cajas de Empaque & Material de Embalaje (Hub 6 Insumos)</span>
                    <span class="font-mono font-bold">-MXN $9,000.00 (1.9%)</span>
                </div>

                <!-- 5. Fletes Courier -->
                <div class="p-3 bg-rose-50/50 rounded-xl flex items-center justify-between text-rose-900">
                    <span class="font-medium">(-) Fletes y Guías de Envío Pagadas a Paqueterías (DHL/Estafeta)</span>
                    <span class="font-mono font-bold">-MXN $62,400.00 (12.8%)</span>
                </div>

                <!-- 6. Pauta Publicitaria -->
                <div class="p-3 bg-rose-50/50 rounded-xl flex items-center justify-between text-rose-900">
                    <span class="font-medium">(-) Pauta Publicitaria Ads (Meta CAPI & Google Ads - ROAS 5.1x)</span>
                    <span class="font-mono font-bold">-MXN $95,450.00 (19.6%)</span>
                </div>

                <!-- 7. Utilidad Neta Real (EBITDA) -->
                <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200/80 flex items-center justify-between text-emerald-950">
                    <div>
                        <span class="font-black text-sm block">(=) Utilidad Neta Real Líquida (EBITDA)</span>
                        <span class="text-[11px] text-emerald-700 font-medium">Margen neto final libre de absorción</span>
                    </div>
                    <div class="text-right">
                        <span class="font-mono font-black text-lg text-emerald-700 block">MXN $148,150.00</span>
                        <span class="text-xs font-bold text-emerald-800 font-mono">30.5% Margen Neto</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- MOVIMIENTOS RECIENTES & LOTES DE DISPERSIÓN (1 COL) -->
        <div class="space-y-6">
            <!-- PANEL: LOTES DE CONCILIACIÓN BANCARIA -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Lotes Bancarios (Payouts)</span>
                    </h3>
                    <span class="text-xs text-slate-500 font-semibold">Stripe / BBVA</span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-slate-800">po_stripe_8832</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Conciliado</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-slate-500 text-[11px]">
                            <span>Depósito BBVA (Hoy 06:00)</span>
                            <span class="font-mono font-bold text-slate-900">MXN $42,310.00</span>
                        </div>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-slate-800">payout_mp_1904</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">En Tránsito</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-slate-500 text-[11px]">
                            <span>Mercado Pago (Llega 14:00)</span>
                            <span class="font-mono font-bold text-slate-900">MXN $18,920.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL: MOVIMIENTOS RECIENTES DEL SISTEMA -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Movimientos Recientes</span>
                    </h3>
                    <span class="text-xs text-slate-500 font-semibold">{{ count($movements) }} registros</span>
                </div>

                <div class="space-y-2 text-xs">
                    @foreach($movements as $m)
                        <div class="p-2.5 bg-slate-50 rounded-xl flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $m['type'] === 'Ingreso' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                <div>
                                    <span class="font-semibold text-slate-800 block">{{ $m['concept'] }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $m['type'] }}</span>
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
@endsection
