@extends('layouts.portal-panel')

@section('title', 'Logística & Post-Venta')
@section('hub_kicker', 'Hub 6')
@section('hub_title', 'Logística, Expedición & Post-Venta')
@section('hub_status', '4 Bultos Listos')

@section('content')
<div id="logisticsAppRoot" class="w-full text-slate-800 font-sans antialiased" data-screen="logistics">
    {{-- ========================================================================= --}}
    {{-- REGLA 10: BARRA DE PESTAÑAS DE SUBMÓDULO PRIMERO                         --}}
    {{-- ========================================================================= --}}
    <div class="mb-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <nav class="flex gap-2 -mb-px overflow-x-auto" aria-label="Submódulos de Logística">
            <!-- TAB 1: MESA DE EXPEDICIÓN -->
            <button 
                type="button"
                data-logistics-tab="expedition"
                class="py-3 px-4 border-b-2 border-indigo-600 text-indigo-600 font-semibold bg-white shadow-sm rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2m10 0H9m10 0h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14v10Z"/><circle cx="17" cy="18.5" r="2.5"/><circle cx="7" cy="18.5" r="2.5"/></svg>
                <span>Mesa de Expedición</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700 font-semibold">{{ count($packages) }} Bultos</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Mesa de Expedición" data-help-title="Mesa de Expedición" data-help-desc="Agrupa los pedidos empacados y rotulados que se encuentran en rampa esperando recolección por transportistas (DHL, Estafeta, FedEx).">?</button>
            </button>

            <!-- TAB 2: TORRE DE CONTROL & RASTREO -->
            <button 
                type="button"
                data-logistics-tab="tracking"
                class="py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span>Torre de Control & Rastreo</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-medium">{{ count($trackingList) }} En Ruta</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Torre de Control" data-help-title="Torre de Control" data-help-desc="Supervisión en tiempo real de telemetría de paqueterías, checkpoints y semáforo predictivo de entregas a nivel nacional.">?</button>
            </button>

            <!-- TAB 3: GARANTÍAS & RMA -->
            <button 
                type="button"
                data-logistics-tab="rma"
                class="py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Garantías & RMA</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-medium">{{ count($rmaRequests) }} Abierta</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Garantías y RMA" data-help-title="Garantías & RMA" data-help-desc="Gestión post-venta de cambios de talla e inspección higiénica de ropa interior con retorno contable automático a Kardex.">?</button>
            </button>

            <!-- TAB 4: CAJAS & EMPAQUES -->
            <button 
                type="button"
                data-logistics-tab="packaging"
                class="py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Cajas & Empaques</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-medium">{{ count($packageTypes) }} Cajas</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Cajas y Empaques" data-help-title="Cajas & Empaques" data-help-desc="Gestor de insumos de empaque, peso tara, cubicaje IATA 5000 y costo unitario de materiales.">?</button>
            </button>
        </nav>

        <div class="flex items-center gap-3 text-xs pb-2 sm:pb-0">
            <span class="flex items-center gap-1.5 font-medium text-slate-600 bg-white px-2.5 py-1 rounded-full border border-slate-200 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Rampa CDMX: Activa</span>
            </span>
            <span class="text-xs font-mono font-bold text-amber-800 bg-amber-50 px-2 py-1 rounded border border-amber-200 flex items-center gap-1">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>Corte DHL: 14:00 hrs</span>
            </span>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- REGLA 10: SECCIÓN 2: TARJETAS DE KPIS CONTEXTUALES                        --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @if(isset($summary))
            @foreach($summary as $index => $kpi)
                <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $kpi['label'] }}</span>
                        <button class="section-help-btn" type="button" aria-label="Ayuda de {{ $kpi['label'] }}" data-help-title="{{ $kpi['label'] }}" data-help-desc="Indicador operativo consolidado de la rampa de salida y recolección.">?</button>
                    </div>
                    <div class="mt-3 flex items-baseline justify-between">
                        <span class="text-2xl font-black font-mono tracking-tight text-slate-900">{{ $kpi['value'] }}</span>
                        @if($index === 0)
                            <span class="text-xs font-bold text-indigo-600 font-mono">En Rampa</span>
                        @elseif($index === 1)
                            <span class="text-xs font-bold text-emerald-600 font-mono">100% OK</span>
                        @elseif($index === 2)
                            <span class="text-xs font-bold text-amber-600 font-mono">48 min</span>
                        @else
                            <span class="text-xs font-bold text-slate-600 font-mono">~0.045 m³</span>
                        @endif
                    </div>
                    <div class="mt-2 text-[11px] text-slate-400">
                        @if($index === 0)
                            2 DHL &bull; 1 Estafeta &bull; 1 99Minutos
                        @elseif($index === 1)
                            Cero guías térmicas pendientes
                        @elseif($index === 2)
                            Camioneta DHL en aproximación
                        @else
                            Cubicaje total lote de hoy
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- SECCIÓN 3: PESTAÑA 1 — MESA DE EXPEDICIÓN & MANIFIESTOS                   --}}
    {{-- ========================================================================= --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Bultos en Espera de Recolección (Mesa de Salida)</span>
                </h3>
                <p class="text-xs text-slate-500">Paquetes sellados y rotulados con etiqueta térmica listos para entrega al transportista</p>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    type="button"
                    onclick="window.dispatchEvent(new CustomEvent('open-manifest-modal'))"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer flex items-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Cerrar Manifiesto de Lote</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 font-semibold uppercase tracking-wider">
                        <th class="py-2.5 px-3">Folio / Pedido</th>
                        <th class="py-2.5 px-3">Cliente & Destino</th>
                        <th class="py-2.5 px-3">Empaque / Medidas</th>
                        <th class="py-2.5 px-3">Peso Báscula / Vol.</th>
                        <th class="py-2.5 px-3">Transportista & Guía</th>
                        <th class="py-2.5 px-3">Escudo Antifraude</th>
                        <th class="py-2.5 px-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($packages as $pkg)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-900 block font-mono">{{ $pkg['order_number'] }}</span>
                                <span class="text-[10px] text-slate-400">ID: {{ $pkg['id'] }} &bull; {{ $pkg['created_at'] }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-semibold text-slate-800 block">{{ $pkg['customer'] }}</span>
                                <span class="text-[11px] text-slate-500">{{ $pkg['destination'] }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="text-slate-800 font-medium block">{{ $pkg['box_type'] }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $pkg['dimensions'] }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-900 block font-mono">{{ $pkg['weight_kg'] }} kg</span>
                                <span class="text-[10px] text-slate-400 font-mono">Vol: {{ $pkg['volumetric_kg'] }} kg (IATA)</span>
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-slate-800">{{ $pkg['courier'] }}</span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Guía Lista</span>
                                </div>
                                <span class="font-mono text-[10px] text-indigo-600 block">{{ $pkg['tracking'] }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full {{ $pkg['fraud_score'] < 20 ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    <span class="font-mono font-bold text-[11px] text-slate-700">{{ $pkg['fraud_score'] }} pts</span>
                                </div>
                                <span class="text-[10px] text-emerald-600 font-medium">Riesgo Bajo</span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <button 
                                    type="button" 
                                    onclick="window.dispatchEvent(new CustomEvent('print-thermal-label', { detail: { order: '{{ $pkg['order_number'] }}', tracking: '{{ $pkg['tracking'] }}', courier: '{{ $pkg['courier'] }}' } }))"
                                    class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" 
                                    title="Reimprimir Guía Térmica 100x150mm"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SECCIÓN 4: PESTAÑA 4 — CATÁLOGO DE CAJAS & CUBICAJE IATA (TASK-LOG-06)   --}}
    {{-- ========================================================================= --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>Gestor de Cajas & Empaques Estándar (Volumetría IATA 5000)</span>
                </h3>
                <p class="text-xs text-slate-500">Configuración de cubicaje, peso tara, costo de embalaje y stock disponible de insumos</p>
            </div>
            <button class="section-help-btn" type="button" aria-label="Ayuda de Cajas" data-help-title="Cajas & Cubicaje IATA" data-help-desc="El factor IATA 5000 divide (Largo x Ancho x Alto)/5000 para determinar el peso volumétrico cobrado por las paqueterías aéreas.">?</button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($packageTypes as $box)
                <div class="p-4 bg-slate-50 border border-slate-200/60 rounded-xl flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-mono text-xs font-bold text-indigo-600">{{ $box['id'] }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">{{ $box['stock'] }} en stock</span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-900">{{ $box['name'] }}</h4>
                        <span class="text-[11px] text-slate-500 font-mono">{{ $box['dimensions'] }}</span>
                        
                        <div class="mt-3 space-y-1 text-[11px] text-slate-600">
                            <div class="flex justify-between">
                                <span>Volumen:</span>
                                <span class="font-mono font-bold">{{ $box['volume_liters'] }} L</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Peso IATA:</span>
                                <span class="font-mono font-bold">{{ $box['volumetric_weight_kg'] }} kg</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Costo Unitario:</span>
                                <span class="font-mono font-bold text-emerald-700">MXN ${{ number_format($box['cost_mxn'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-slate-200/80 text-[10px] text-slate-500">
                        Capacidad: {{ $box['capacity'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
