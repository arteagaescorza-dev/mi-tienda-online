@extends('layouts.portal-panel')

@section('title', 'Logística & Fulfillment')
@section('hub_kicker', 'Hub 3')
@section('hub_title', 'Logística, Expedición & Post-Venta')
@section('hub_status', count($packages) . ' Bultos en Rampa')

@section('content')
<div id="logisticsAppRoot" class="w-full text-slate-800 font-sans antialiased" data-screen="logistics">

    {{-- ALERTAS DE SESIÓN --}}
    @if(session('success'))
        <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 cursor-pointer font-bold">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold shadow-xs">
            <p class="font-bold mb-1">Ocurrieron errores de validación:</p>
            <ul class="list-disc pl-5 space-y-0.5 font-normal">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- REGLA 10: BARRA DE PESTAÑAS DE SUBMÓDULO PRIMERO                         --}}
    {{-- ========================================================================= --}}
    <div class="mb-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <nav class="flex gap-2 -mb-px overflow-x-auto" aria-label="Submódulos de Logística">
            <!-- TAB 1: MESA DE EXPEDICIÓN -->
            <button 
                type="button"
                data-logistics-tab="expedition"
                class="logistics-tab-btn py-3 px-4 border-b-2 border-indigo-600 text-indigo-600 font-semibold bg-white shadow-xs rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2m10 0H9m10 0h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14v10Z"/><circle cx="17" cy="18.5" r="2.5"/><circle cx="7" cy="18.5" r="2.5"/></svg>
                <span>Mesa de Expedición</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700 font-semibold">{{ count($packages) }} Bultos</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Mesa de Expedición" data-help-title="Mesa de Expedición" data-help-desc="Agrupa los pedidos empacados y rotulados que se encuentran en rampa esperando recolección por transportistas (DHL, Estafeta, FedEx, 99 Minutos).">?</button>
            </button>

            <!-- TAB 2: TORRE DE CONTROL & RASTREO -->
            <button 
                type="button"
                data-logistics-tab="tracking"
                class="logistics-tab-btn py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
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
                class="logistics-tab-btn py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Garantías & RMA</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-medium">{{ count($rmaRequests) }} Casos</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Garantías y RMA" data-help-title="Garantías & RMA" data-help-desc="Gestión post-venta de cambios de talla e inspección higiénica de ropa interior con retorno contable automático a Kardex.">?</button>
            </button>

            <!-- TAB 4: CAJAS & EMPAQUES -->
            <button 
                type="button"
                data-logistics-tab="packaging"
                class="logistics-tab-btn py-3 px-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 rounded-t-lg text-sm transition-all flex items-center gap-2 cursor-pointer group relative"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Cajas & Empaques</span>
                <span class="ml-0.5 px-1.5 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-medium">{{ count($packageTypes) }} Insumos</span>
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
                <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden group hover:border-indigo-300 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $kpi['label'] }}</span>
                        <button class="section-help-btn" type="button" aria-label="Ayuda de {{ $kpi['label'] }}" data-help-title="{{ $kpi['label'] }}" data-help-desc="Indicador operativo consolidado de la rampa de salida y recolección.">?</button>
                    </div>
                    <div class="mt-3 flex items-baseline justify-between">
                        <span class="text-2xl font-black font-mono tracking-tight text-slate-900">{{ $kpi['value'] }}</span>
                        <span class="text-xs font-bold font-mono {{ $index === 0 ? 'text-indigo-600' : ($index === 1 ? 'text-emerald-600' : ($index === 2 ? 'text-amber-600' : 'text-slate-600')) }}">
                            {{ $kpi['badge'] ?? '' }}
                        </span>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-400">
                        {{ $kpi['subtext'] ?? '' }}
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- PANEL 1: MESA DE EXPEDICIÓN & MANIFIESTOS (TASK-LOG-01)                  --}}
    {{-- ========================================================================= --}}
    <div id="panel-expedition" data-logistics-panel="expedition" class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
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
                        id="openManifestModalBtn"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Cerrar Manifiesto de Lote</span>
                    </button>
                </div>
            </div>

            @if(count($packages) > 0)
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
                                        @if($pkg['packaging_fulfilled_at'])
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 mt-1">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Kardex OK
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('portal.logistics.shipments.fulfill_packaging', $pkg['db_id']) }}" class="inline-block mt-1">
                                                @csrf
                                                <button type="submit" class="px-2 py-0.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded text-[9px] font-bold border border-indigo-200 transition cursor-pointer" title="Deducir empaque en Kardex">
                                                    Deducir Insumo
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="font-bold text-slate-900 block font-mono">{{ number_format($pkg['weight_kg'], 2) }} kg</span>
                                        <span class="text-[10px] text-slate-400 font-mono">Vol: {{ number_format($pkg['volumetric_kg'], 2) }} kg (IATA)</span>
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
                                            <span class="w-2 h-2 rounded-full {{ $pkg['fraud_score'] < 30 ? 'bg-emerald-500' : ($pkg['fraud_score'] < 75 ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                                            <span class="font-mono font-bold text-[11px] text-slate-700">{{ $pkg['fraud_score'] }} pts</span>
                                        </div>
                                        <span class="text-[10px] {{ $pkg['fraud_score'] < 30 ? 'text-emerald-600' : ($pkg['fraud_score'] < 75 ? 'text-amber-600' : 'text-rose-600') }} font-medium">
                                            {{ $pkg['fraud_score'] < 30 ? 'Riesgo Normal' : ($pkg['fraud_score'] < 75 ? 'Revisión Manual' : 'Alerta Radar') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <button 
                                            type="button" 
                                            onclick="openThermalModal('{{ $pkg['order_number'] }}', '{{ $pkg['tracking'] }}', '{{ $pkg['courier'] }}', '{{ $pkg['customer'] }}', '{{ $pkg['destination'] }}')"
                                            class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition cursor-pointer" 
                                            title="Ver / Imprimir Guía Térmica 100x150mm"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-10 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                    <p class="text-slate-500 text-xs font-medium">No hay bultos pendientes de recolección en rampa. Todos los lotes han sido recolectados por transportistas.</p>
                </div>
            @endif
        </div>

        {{-- MANIFIESTOS DE EXPEDICIÓN RECIENTES --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Historial de Manifiestos Diarios de Salida</span>
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-semibold uppercase tracking-wider">
                            <th class="py-2 px-3">Folio Manifiesto</th>
                            <th class="py-2 px-3">Paquetería</th>
                            <th class="py-2 px-3">Chofer / Unidad</th>
                            <th class="py-2 px-3">Bultos</th>
                            <th class="py-2 px-3">Estado</th>
                            <th class="py-2 px-3">Hora Firma</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($manifests as $m)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-2.5 px-3 font-mono font-bold text-slate-900">{{ $m->manifest_number }}</td>
                                <td class="py-2.5 px-3 font-semibold text-slate-700">{{ strtoupper($m->courier) }}</td>
                                <td class="py-2.5 px-3 text-slate-600">{{ $m->driver_name ?? 'Pendiente de Chofer' }} <span class="text-[10px] text-slate-400 block font-mono">{{ $m->driver_badge_or_truck }}</span></td>
                                <td class="py-2.5 px-3 font-mono font-bold text-slate-800">{{ $m->total_packages }} bultos</td>
                                <td class="py-2.5 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $m->status === 'signed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $m->status === 'signed' ? 'Firmado & Entregado' : 'Abierto en Rampa' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-slate-500 font-mono text-[11px]">{{ $m->signed_at ? $m->signed_at->format('d/m/Y H:i') : '--' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-slate-400">Sin manifiestos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- PANEL 2: TORRE DE CONTROL & RASTREO (TASK-LOG-03)                        --}}
    {{-- ========================================================================= --}}
    <div id="panel-tracking" data-logistics-panel="tracking" class="hidden space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <span>Torre de Control & Telemetría Nacional de Envíos</span>
                    </h3>
                    <p class="text-xs text-slate-500">Supervisión en tiempo real de checkpoints, SLA de entrega y paqueterías (DHL, Estafeta, FedEx, 99 Minutos)</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-semibold uppercase tracking-wider">
                            <th class="py-2.5 px-3">Número de Guía</th>
                            <th class="py-2.5 px-3">Pedido & Destinatario</th>
                            <th class="py-2.5 px-3">Transportista</th>
                            <th class="py-2.5 px-3">Estado de En Ruta</th>
                            <th class="py-2.5 px-3">Último Checkpoint</th>
                            <th class="py-2.5 px-3">Estimación (ETA)</th>
                            <th class="py-2.5 px-3 text-right">Actualizar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($trackingList as $trk)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-3">
                                    <span class="font-mono font-bold text-indigo-600 block">{{ $trk['tracking_number'] }}</span>
                                    <span class="text-[10px] text-slate-400">Despacho: {{ $trk['dispatched_at'] ?? 'Hoy' }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-bold text-slate-900 block">{{ $trk['order_number'] }}</span>
                                    <span class="text-slate-600 block font-medium">{{ $trk['customer'] }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $trk['city'] }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-bold text-slate-800">{{ $trk['courier'] }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold 
                                        {{ $trk['status'] === 'delivered' ? 'bg-emerald-100 text-emerald-800' : ($trk['status'] === 'out_for_delivery' ? 'bg-indigo-100 text-indigo-800' : 'bg-blue-100 text-blue-800') }}">
                                        {{ $trk['status_label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-600 max-w-xs">
                                    <span class="block text-[11px] font-medium leading-relaxed">{{ $trk['last_checkpoint'] }}</span>
                                </td>
                                <td class="py-3 px-3 font-mono font-semibold text-slate-800 text-[11px]">
                                    {{ $trk['eta'] }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    @if($trk['status'] !== 'delivered')
                                        <form method="POST" action="{{ route('portal.logistics.shipments.status', $trk['id']) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="delivered">
                                            <button type="submit" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded text-[10px] font-bold transition cursor-pointer" title="Confirmar entrega">
                                                Marcar Entregado
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-emerald-600 text-[11px] font-bold">Completado</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-6 text-slate-400">No hay envíos activos en ruta.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- PANEL 3: GARANTÍAS & RMA (TASK-LOG-04, TASK-LOG-04B)                     --}}
    {{-- ========================================================================= --}}
    <div id="panel-rma" data-logistics-panel="rma" class="hidden space-y-6">
        <!-- BANNER PROTOCOLO SANITARIO -->
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs shadow-xs flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <h4 class="font-bold text-amber-900 mb-0.5">Protocolo Sanitario Cofepris para Ropa Interior & Prendas Íntimas</h4>
                <p class="text-amber-800 leading-relaxed">
                    Por estricta regulación de salubridad e higiene, las prendas textiles íntimas (bóxers, tangas, suspensorios, arneses de contacto) únicamente admiten garantía o cambio si el <strong>precinto protector termosellado está 100% intacto</strong> y conserva las etiquetas de fábrica. Si el precinto fue retirado o muestra indicios de uso, el dictamen debe ser <strong>RECHAZADO</strong> y registrarse como <code>MERMA_DESTRUCCION_HIGIENE</code> en Kardex.
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Solicitudes de Garantía & Devolución RMA</span>
                    </h3>
                    <p class="text-xs text-slate-500">Inspección técnica de higiene, dictamen pericial y reingreso contable a Kardex</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-semibold uppercase tracking-wider">
                            <th class="py-2.5 px-3">Folio RMA</th>
                            <th class="py-2.5 px-3">Pedido & Cliente</th>
                            <th class="py-2.5 px-3">Producto / SKU</th>
                            <th class="py-2.5 px-3">Motivo</th>
                            <th class="py-2.5 px-3">Estado & Resolución</th>
                            <th class="py-2.5 px-3">Dictamen / Bitácora</th>
                            <th class="py-2.5 px-3 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rmaRequests as $rma)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-3">
                                    <span class="font-mono font-bold text-slate-900 block">{{ $rma->rma_number }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $rma->created_at->format('d/m/Y') }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-bold text-slate-800 block">{{ $rma->order?->order_number ?? ('ORD-' . $rma->order_id) }}</span>
                                    <span class="text-slate-600 block">{{ $rma->customer_name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $rma->customer_phone }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-medium text-slate-900 block">{{ $rma->product_name }}</span>
                                    <span class="font-mono text-[10px] text-indigo-600">{{ $rma->product_sku }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="text-slate-700 font-medium">
                                        {{ match($rma->reason) {
                                            'sizing_wrong' => 'Cambio de Talla',
                                            'factory_defect' => 'Defecto de Fábrica',
                                            'allergic_reaction' => 'Reacción Alérgica',
                                            default => $rma->reason
                                        } }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="space-y-1">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold
                                            {{ $rma->sanitary_status === 'approved_replacement' || $rma->sanitary_status === 'approved_store_credit' ? 'bg-emerald-100 text-emerald-800' : ($rma->sanitary_status === 'rejected_used_hygiene' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ match($rma->sanitary_status) {
                                                'approved_replacement' => 'Aprobado: Reemplazo',
                                                'approved_store_credit' => 'Aprobado: Crédito',
                                                'rejected_used_hygiene' => 'Rechazado: Violación Higiene',
                                                default => 'Pendiente Inspección'
                                            } }}
                                        </span>
                                        @if($rma->replacement_order_number)
                                            <span class="block font-mono text-[10px] text-indigo-700 font-semibold bg-indigo-50 border border-indigo-200 rounded px-1.5 py-0.5">
                                                Reemplazo: {{ $rma->replacement_order_number }}
                                            </span>
                                        @elseif($rma->store_credit_code)
                                            <span class="block font-mono text-[10px] text-emerald-700 font-semibold bg-emerald-50 border border-emerald-200 rounded px-1.5 py-0.5">
                                                Cupón: {{ $rma->store_credit_code }} (${{ number_format($rma->refund_or_credit_amount, 2) }})
                                            </span>
                                        @elseif($rma->sanitary_status === 'rejected_used_hygiene')
                                            <span class="block font-mono text-[10px] text-rose-700 font-semibold bg-rose-50 border border-rose-200 rounded px-1.5 py-0.5">
                                                Merma Sanitaria PROFECO
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-3 max-w-xs text-slate-600">
                                    @if($rma->inspectionLogs->isNotEmpty())
                                        @php $lastLog = $rma->inspectionLogs->first(); @endphp
                                        <div class="text-[11px] leading-tight">
                                            <span class="font-semibold block text-slate-800">{{ $lastLog->inspector_name }}</span>
                                            <span class="text-slate-500">{{ Str::limit($lastLog->technical_notes, 90) }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[11px] italic">En espera de peritaje en mesa de empaque.</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    @if($rma->status === 'open' || $rma->sanitary_status === 'pending_inspection')
                                        <button 
                                            type="button"
                                            onclick="openRmaModal({{ $rma->id }}, '{{ $rma->rma_number }}', '{{ addslashes($rma->product_name) }}', '{{ $rma->product_sku }}', '{{ addslashes($rma->customer_name) }}', '{{ $rma->customer_phone }}', {{ (float)($rma->refund_or_credit_amount > 0 ? $rma->refund_or_credit_amount : 399.00) }})"
                                            class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-[11px] font-bold transition shadow-2xs cursor-pointer"
                                        >
                                            Inspeccionar & Resolver
                                        </button>
                                    @else
                                        <div class="flex items-center justify-end gap-1.5">
                                            <span class="text-[11px] font-bold {{ $rma->status === 'resolved' ? 'text-emerald-600' : 'text-slate-400' }}">
                                                {{ $rma->status === 'resolved' ? 'Resuelto' : 'Rechazado' }}
                                            </span>
                                            @if($rma->customer_phone && $rma->resolution)
                                                @php
                                                    $cleanPhone = preg_replace('/[^0-9]/', '', $rma->customer_phone);
                                                    $waText = urlencode($rma->resolution->whatsapp_payload['message'] ?? '');
                                                @endphp
                                                @if($cleanPhone && $waText)
                                                    <a 
                                                        href="https://wa.me/{{ $cleanPhone }}?text={{ $waText }}" 
                                                        target="_blank" 
                                                        rel="noopener noreferrer"
                                                        class="p-1 text-emerald-600 hover:bg-emerald-50 rounded transition" 
                                                        title="Contactar al cliente por WhatsApp"
                                                    >
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-6 text-slate-400">No hay solicitudes RMA activas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- PANEL 4: CAJAS & EMPAQUES (TASK-LOG-06)                                  --}}
    {{-- ========================================================================= --}}
    <div id="panel-packaging" data-logistics-panel="packaging" class="hidden space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
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
                    @php
                        $volumeLiters = round(($box->length_cm * $box->width_cm * $box->height_cm) / 1000, 2);
                        $volumetricIata = round(($box->length_cm * $box->width_cm * $box->height_cm) / 5000, 2);
                        $isLow = $box->stock_available <= ($box->reorder_point ?? 40);
                    @endphp
                    <div class="p-4 bg-slate-50 border {{ $isLow ? 'border-amber-300 ring-1 ring-amber-200' : 'border-slate-200/60' }} rounded-xl flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-mono text-xs font-bold text-indigo-600">{{ $box->code }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $isLow ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $isLow ? 'ALERTA ROP: ' . $box->stock_available : $box->stock_available . ' en stock' }}
                                </span>
                            </div>
                            <h4 class="text-xs font-bold text-slate-900">{{ $box->name }}</h4>
                            <span class="text-[11px] text-slate-500 font-mono">{{ $box->length_cm }} x {{ $box->width_cm }} x {{ $box->height_cm }} cm</span>
                            
                            <div class="mt-3 space-y-1.5 text-[11px] text-slate-600">
                                <div class="flex justify-between">
                                    <span>Volumen:</span>
                                    <span class="font-mono font-bold">{{ $volumeLiters }} L</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Peso IATA:</span>
                                    <span class="font-mono font-bold">{{ $volumetricIata }} kg</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Punto Reorden (ROP):</span>
                                    <span class="font-mono font-bold text-slate-700">{{ $box->reorder_point ?? 40 }} u</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Costo Unitario:</span>
                                    <span class="font-mono font-bold text-emerald-700">MXN ${{ number_format($box->unit_cost, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-200/80">
                            <form method="POST" action="{{ route('portal.logistics.packages.stock', $box->id) }}" class="flex items-center gap-1.5">
                                @csrf
                                <input type="hidden" name="unit_cost" value="{{ $box->unit_cost }}">
                                <div class="flex items-center gap-1">
                                    <span class="text-[9px] text-slate-400 font-semibold uppercase">Stock</span>
                                    <input 
                                        type="number" 
                                        name="stock_available" 
                                        value="{{ $box->stock_available }}" 
                                        min="0"
                                        class="w-14 px-1.5 py-1 bg-white border border-slate-300 rounded text-xs font-mono font-bold text-slate-800 focus:outline-indigo-600"
                                        title="Ajustar stock de insumos"
                                    >
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="text-[9px] text-slate-400 font-semibold uppercase">ROP</span>
                                    <input 
                                        type="number" 
                                        name="reorder_point" 
                                        value="{{ $box->reorder_point ?? 40 }}" 
                                        min="0"
                                        class="w-12 px-1.5 py-1 bg-white border border-slate-300 rounded text-xs font-mono font-bold text-slate-800 focus:outline-indigo-600"
                                        title="Punto de reorden (ROP)"
                                    >
                                </div>
                                <button type="submit" class="px-2 py-1 bg-slate-800 hover:bg-slate-900 text-white rounded text-[10px] font-bold transition cursor-pointer shrink-0">
                                    Guardar
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- KARDEX DE MOVIMIENTOS DE INSUMOS DE EMPAQUE (TASK-INT-13) --}}
            <div class="mt-8 pt-6 border-t border-slate-200/80">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Kardex de Movimientos de Insumos de Empaque (Auditoría Inmutable)</span>
                    </h4>
                    <span class="text-[11px] text-slate-500 font-medium">Últimos registros de consumo y reabastecimiento</span>
                </div>

                @if(isset($packagingMovements) && count($packagingMovements) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-400 font-semibold uppercase tracking-wider text-[10px]">
                                    <th class="py-2 px-3">Fecha</th>
                                    <th class="py-2 px-3">Insumo</th>
                                    <th class="py-2 px-3">Tipo Movimiento</th>
                                    <th class="py-2 px-3 text-center">Cantidad</th>
                                    <th class="py-2 px-3 text-center">Stock Anterior</th>
                                    <th class="py-2 px-3 text-center">Stock Resultante</th>
                                    <th class="py-2 px-3">Envío / Referencia</th>
                                    <th class="py-2 px-3">Operador</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($packagingMovements as $pm)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-2 px-3 font-mono text-[11px] text-slate-500">{{ $pm->created_at->format('d/m H:i') }}</td>
                                        <td class="py-2 px-3">
                                            <span class="font-bold text-slate-900 block">{{ $pm->packageType?->name ?? 'Insumo' }}</span>
                                            <span class="font-mono text-[10px] text-indigo-600">{{ $pm->packageType?->code }}</span>
                                        </td>
                                        <td class="py-2 px-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $pm->quantity < 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                                {{ $pm->type }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-3 text-center font-mono font-bold {{ $pm->quantity < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                            {{ $pm->quantity > 0 ? '+' . $pm->quantity : $pm->quantity }}
                                        </td>
                                        <td class="py-2 px-3 text-center font-mono text-slate-500">{{ $pm->stock_before }} u</td>
                                        <td class="py-2 px-3 text-center font-mono font-bold text-slate-900">{{ $pm->stock_after }} u</td>
                                        <td class="py-2 px-3 font-mono text-[11px]">
                                            @if($pm->shipment)
                                                <span class="text-indigo-600 block">{{ $pm->shipment->tracking_number }}</span>
                                                <span class="text-[10px] text-slate-400">{{ $pm->order?->order_number }}</span>
                                            @else
                                                <span class="text-slate-500">{{ $pm->notes ?? 'Ajuste manual' }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-3 text-slate-600 text-[11px]">{{ $pm->user?->name ?? 'Sistema Automático' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-6 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        <p class="text-slate-500 text-xs">Aún no se registran movimientos de empaque. Las salidas se generan automáticamente al empacar pedidos o cerrar manifiestos.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- PROTOCOLOS DE EMBALAJE SEGURO Y PRIVACIDAD -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <h4 class="text-xs font-bold text-slate-900 mb-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Protocolo de Embalaje Neutro & Confidencial</span>
                </h4>
                <p class="text-slate-600 text-xs leading-relaxed">
                    Todo envío debe despacharse en embalaje opaco y sellado sin referencias gráficas explícitas a lencería erótica o arneses. La etiqueta de paquetería consigna el remitente corporativo neutro: <strong>"Operadora Logística Urbana"</strong> para garantizar el 100% de confidencialidad del cliente ante porteros o familiares.
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <h4 class="text-xs font-bold text-slate-900 mb-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    <span>Protocolo de Cosmética Líquida & Grooming</span>
                </h4>
                <p class="text-slate-600 text-xs leading-relaxed">
                    Los productos de cuidado corporal, aceites y lociones requieren sellado hermético individual en bolsa ziploc antes de ser colocados en la caja junto a prendas textiles, previniendo derrames por cambios de presión en rutas aéreas IATA.
                </p>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 1: CERRAR MANIFIESTO DE EXPEDICIÓN (TASK-LOG-01)                   --}}
    {{-- ========================================================================= --}}
    <div id="manifestModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Cerrar Manifiesto de Recolección</span>
                </h3>
                <button type="button" id="closeManifestModalBtn" class="text-slate-400 hover:text-slate-600 cursor-pointer font-bold text-lg">&times;</button>
            </div>

            <form method="POST" action="{{ route('portal.logistics.manifests.close') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Empresa de Paquetería / Transportista</label>
                    <select name="courier" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-white text-slate-800 font-semibold focus:outline-indigo-600">
                        <option value="dhl_express">DHL Express</option>
                        <option value="estafeta">Estafeta</option>
                        <option value="fedex">FedEx</option>
                        <option value="99minutos">99 Minutos</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nombre Completo del Chofer</label>
                    <input type="text" name="driver_name" required placeholder="Ej. Roberto Gómez Hernández" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:outline-indigo-600 text-slate-800">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Gafete de Conductor / Placas de la Unidad</label>
                    <input type="text" name="driver_badge_or_truck" required placeholder="Ej. GAF-9921 / Placas LC-492-X" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:outline-indigo-600 text-slate-800 font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Seleccionar Bultos a Entregar</label>
                    <div class="max-h-40 overflow-y-auto border border-slate-200 rounded-xl p-2 space-y-1.5 bg-slate-50">
                        @foreach($packages as $pkg)
                            <label class="flex items-center gap-2 p-1.5 hover:bg-white rounded cursor-pointer transition">
                                <input type="checkbox" name="shipment_ids[]" value="{{ $pkg['db_id'] }}" checked class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span class="font-mono font-bold text-slate-900">{{ $pkg['order_number'] }}</span>
                                <span class="text-slate-500">({{ $pkg['courier'] }})</span>
                                <span class="text-slate-400 font-mono text-[10px] ml-auto">{{ $pkg['tracking'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" id="cancelManifestModalBtn" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50 transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-xs transition cursor-pointer">
                        Confirmar Custodia & Cerrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 2: VISTA PREVIA GUÍA TÉRMICA 100x150mm (TASK-LOG-02)               --}}
    {{-- ========================================================================= --}}
    <div id="thermalModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 font-mono text-xs">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200 mb-3">
                <span class="font-bold uppercase tracking-wider text-slate-500 text-[10px]">Guía Térmica Oficial 100x150mm</span>
                <button type="button" onclick="closeThermalModal()" class="text-slate-400 hover:text-slate-600 font-bold text-base cursor-pointer">&times;</button>
            </div>

            <!-- ETIQUETA ROTULADA NEUTRA -->
            <div class="border-2 border-slate-800 p-4 rounded bg-white text-slate-900 space-y-3">
                <div class="flex justify-between items-start border-b border-slate-400 pb-2">
                    <div>
                        <span class="font-black text-sm block tracking-wider" id="labelCourier">DHL EXPRESS</span>
                        <span class="text-[9px] text-slate-500">DOMESTIC EXPRESS 12:00</span>
                    </div>
                    <span class="text-[10px] font-bold border border-slate-800 px-1 rounded">MX-STD</span>
                </div>

                <div class="text-[10px] space-y-1">
                    <span class="block text-slate-400 text-[9px] uppercase">Remitente (Neutro Confidencial):</span>
                    <span class="font-bold block">OPERADORA LOGISTICA URBANA</span>
                    <span class="block text-slate-600">Av. Insurgentes Sur 105, CDMX</span>
                </div>

                <div class="text-[10px] space-y-1 border-t border-slate-300 pt-2">
                    <span class="block text-slate-400 text-[9px] uppercase">Destinatario:</span>
                    <span class="font-bold block text-sm" id="labelCustomer">Cliente</span>
                    <span class="block text-slate-700" id="labelDestination">Destino</span>
                    <span class="block text-slate-500 font-bold" id="labelOrderNumber">ORD-0000</span>
                </div>

                <!-- CÓDIGO DE BARRAS SIMULADO -->
                <div class="border-t-2 border-dashed border-slate-800 pt-3 text-center">
                    <div class="h-12 bg-slate-900 flex items-center justify-center text-white text-[10px] tracking-widest font-mono">
                        ||| | ||||| || |||| |||||| ||| ||||
                    </div>
                    <span class="font-bold text-xs mt-1 block tracking-wider" id="labelTrackingNumber">DHL-0000000000</span>
                </div>
            </div>

            <div class="mt-4 flex gap-2 justify-end">
                <button type="button" onclick="closeThermalModal()" class="px-3 py-1.5 border border-slate-200 rounded text-slate-600 font-semibold hover:bg-slate-50 cursor-pointer">
                    Cerrar
                </button>
                <button type="button" onclick="window.print()" class="px-3 py-1.5 bg-slate-900 text-white rounded font-bold hover:bg-slate-800 cursor-pointer flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Imprimir</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 3: INSPECCIÓN TÉCNICA RMA & MOTOR DE RESOLUCIONES (TASK-INT-09)    --}}
    {{-- ========================================================================= --}}
    <div id="rmaModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 overflow-y-auto max-h-[90vh]">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Mesa Pericial RMA & Motor de Resoluciones</span>
                    </h3>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span id="rmaModalFolio" class="font-mono text-xs text-indigo-600 font-bold">RMA-0000</span>
                        <span class="text-slate-300">•</span>
                        <span id="rmaModalCustomer" class="text-xs text-slate-600 font-medium">Cliente</span>
                    </div>
                </div>
                <button type="button" onclick="closeRmaModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer font-bold text-lg">&times;</button>
            </div>

            <form id="rmaInspectForm" method="POST" action="" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <span class="block text-slate-400 text-[10px] uppercase font-semibold">Prenda en Evaluación:</span>
                        <span id="rmaModalProductName" class="font-bold text-slate-900 block text-xs truncate">Nombre Producto</span>
                        <span id="rmaModalProductSku" class="font-mono text-indigo-600 text-[11px] block">SKU-0000</span>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Inspector de Calidad Autorizado</label>
                        <input type="text" name="inspector_name" required value="Jefe de Calidad Almacén CDMX" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:outline-indigo-600 text-slate-800 bg-white">
                    </div>
                </div>

                <!-- CHECKLIST SANITARIO PROFECO / COFEPRIS -->
                <div class="p-3.5 bg-amber-50/60 border border-amber-200 rounded-xl space-y-2">
                    <span class="block font-bold text-amber-900 text-[11px] uppercase tracking-wider">Protocolo Sanitario Obligatorio (COFEPRIS):</span>
                    <label class="flex items-center gap-2.5 p-2 bg-white border border-amber-200 rounded-lg cursor-pointer">
                        <input type="checkbox" name="has_hygiene_seal_intact" value="1" id="sealCheckbox" class="rounded text-indigo-600 focus:ring-indigo-500">
                        <div class="text-slate-700">
                            <span class="font-bold block">¿Precinto higiénico termosellado 100% intacto?</span>
                            <span class="text-[11px] text-slate-500 block">Si está roto o retirado, se bloqueará automáticamente el reingreso al inventario comercial.</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 p-2 bg-white border border-amber-200 rounded-lg cursor-pointer">
                        <input type="checkbox" name="has_factory_tag" value="1" checked class="rounded text-indigo-600 focus:ring-indigo-500">
                        <div class="text-slate-700">
                            <span class="font-bold block">¿Conserva etiquetas textiles de fábrica adheridas?</span>
                            <span class="text-[11px] text-slate-500 block">Garantiza autenticidad y condición original de manufactura.</span>
                        </div>
                    </label>
                </div>

                <!-- DICTAMEN Y RESOLUCION -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Dictamen Pericial</label>
                        <select name="verdict" id="verdictSelect" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-white font-semibold text-slate-800 focus:outline-indigo-600">
                            <option value="approved">Aprobado (Sellos Intactos - Proceder a Reposición)</option>
                            <option value="rejected_sanitary_violation">Rechazado (Sello Violado / Merma Destructiva)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tipo de Resolución Automática</label>
                        <select name="resolution_type" id="resolutionTypeSelect" class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-white font-semibold text-slate-800 focus:outline-indigo-600">
                            <option value="replacement_order">Orden de Reemplazo (#ORD-RMA a $0.00)</option>
                            <option value="store_credit">Monedero Digital (Cupón WhatsApp)</option>
                        </select>
                    </div>
                </div>

                <!-- CAMPOS CONDICIONALES DE RESOLUCION -->
                <div id="replacementField" class="p-3 bg-indigo-50/60 border border-indigo-200 rounded-xl space-y-1">
                    <label class="block font-bold text-indigo-900 text-[11px]">SKU / Variante de Sustitución a Empacar:</label>
                    <input type="text" name="replacement_sku" id="replacementSkuInput" class="w-full px-3 py-2 border border-indigo-300 rounded-lg text-slate-800 bg-white font-mono" placeholder="SKU de reemplazo">
                    <span class="text-[10px] text-indigo-700 block">Se inyectará orden #ORD-XXXX-RMA con costo $0.00 a la cola de empaque y se descontará 1 unidad en Kardex.</span>
                </div>

                <div id="storeCreditField" class="hidden p-3 bg-emerald-50/60 border border-emerald-200 rounded-xl space-y-1">
                    <label class="block font-bold text-emerald-900 text-[11px]">Monto del Monedero Digital (MXN):</label>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-emerald-800">$</span>
                        <input type="number" step="0.01" name="credit_amount" id="creditAmountInput" class="w-full px-3 py-2 border border-emerald-300 rounded-lg text-slate-800 bg-white font-mono" placeholder="399.00">
                    </div>
                    <span class="text-[10px] text-emerald-700 block">Genera un cupón digital único registrado en CRM de 1 solo uso con 90 días de vigencia.</span>
                </div>

                <!-- LIVE PREVIEW DE MENSAJE WHATSAPP -->
                <div class="p-3.5 bg-slate-900 text-slate-100 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-[11px] text-emerald-400 flex items-center gap-1.5 uppercase tracking-wider">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <span>Plantilla Oficial de WhatsApp para el Cliente</span>
                        </span>
                        <span id="rmaModalPhone" class="font-mono text-[10px] text-slate-400">Tel: --</span>
                    </div>
                    <div id="whatsappPreviewBox" class="p-2.5 bg-slate-800/80 rounded-lg text-[11px] text-slate-200 leading-relaxed font-mono whitespace-pre-wrap">
                        Cargando mensaje...
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Notas Técnicas de la Inspección</label>
                    <textarea name="technical_notes" rows="2" placeholder="Observaciones físicas del tejido, precinto e integridad..." class="w-full px-3 py-2 border border-slate-300 rounded-xl text-slate-800 focus:outline-indigo-600 bg-white"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeRmaModal()" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50 transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-xs transition cursor-pointer">
                        Dictaminar & Generar Resolución
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

{{-- SCRIPT VANILLA JS DE GESTIÓN LOGÍSTICA --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Selector de Pestañas (Subnav Bar)
    const tabButtons = document.querySelectorAll('.logistics-tab-btn');
    const panels = {
        expedition: document.getElementById('panel-expedition'),
        tracking: document.getElementById('panel-tracking'),
        rma: document.getElementById('panel-rma'),
        packaging: document.getElementById('panel-packaging')
    };

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetTab = this.getAttribute('data-logistics-tab');

            // Reset tab styles
            tabButtons.forEach(b => {
                b.classList.remove('border-indigo-600', 'text-indigo-600', 'bg-white', 'shadow-xs');
                b.classList.add('border-transparent', 'text-slate-500');
            });

            // Activate current tab button
            this.classList.remove('border-transparent', 'text-slate-500');
            this.classList.add('border-indigo-600', 'text-indigo-600', 'bg-white', 'shadow-xs');

            // Hide all panels & show active panel
            Object.values(panels).forEach(p => {
                if (p) p.classList.add('hidden');
            });

            if (panels[targetTab]) {
                panels[targetTab].classList.remove('hidden');
            }
        });
    });

    // 2. Modal de Manifiesto
    const manifestModal = document.getElementById('manifestModal');
    const openManifestBtn = document.getElementById('openManifestModalBtn');
    const closeManifestBtn = document.getElementById('closeManifestModalBtn');
    const cancelManifestBtn = document.getElementById('cancelManifestModalBtn');

    if (openManifestBtn && manifestModal) {
        openManifestBtn.addEventListener('click', () => manifestModal.classList.remove('hidden'));
    }
    if (closeManifestBtn && manifestModal) {
        closeManifestBtn.addEventListener('click', () => manifestModal.classList.add('hidden'));
    }
    if (cancelManifestBtn && manifestModal) {
        cancelManifestBtn.addEventListener('click', () => manifestModal.classList.add('hidden'));
    }

    // Cerrar modal al dar clic fuera
    if (manifestModal) {
        manifestModal.addEventListener('click', function(e) {
            if (e.target === manifestModal) manifestModal.classList.add('hidden');
        });
    }

    // 3. Reglas reactivas del Motor de Resoluciones RMA
    const sealCheckbox = document.getElementById('sealCheckbox');
    const verdictSelect = document.getElementById('verdictSelect');
    const resolutionTypeSelect = document.getElementById('resolutionTypeSelect');
    const replacementField = document.getElementById('replacementField');
    const storeCreditField = document.getElementById('storeCreditField');
    const replacementSkuInput = document.getElementById('replacementSkuInput');
    const creditAmountInput = document.getElementById('creditAmountInput');

    function updateRmaUiState() {
        if (!verdictSelect || !resolutionTypeSelect) return;

        const isApproved = verdictSelect.value === 'approved';
        const isReplacement = resolutionTypeSelect.value === 'replacement_order';

        if (!isApproved) {
            if (replacementField) replacementField.classList.add('hidden');
            if (storeCreditField) storeCreditField.classList.add('hidden');
        } else {
            if (replacementField) replacementField.classList.toggle('hidden', !isReplacement);
            if (storeCreditField) storeCreditField.classList.toggle('hidden', isReplacement);
        }

        updateWhatsAppPreview();
    }

    if (sealCheckbox && verdictSelect) {
        sealCheckbox.addEventListener('change', function () {
            if (!this.checked) {
                verdictSelect.value = 'rejected_sanitary_violation';
            } else if (verdictSelect.value === 'rejected_sanitary_violation') {
                verdictSelect.value = 'approved';
            }
            updateRmaUiState();
        });
    }

    if (verdictSelect) {
        verdictSelect.addEventListener('change', updateRmaUiState);
    }

    if (resolutionTypeSelect) {
        resolutionTypeSelect.addEventListener('change', updateRmaUiState);
    }

    if (creditAmountInput) {
        creditAmountInput.addEventListener('input', updateWhatsAppPreview);
    }
    if (replacementSkuInput) {
        replacementSkuInput.addEventListener('input', updateWhatsAppPreview);
    }
});

// Variables de estado del modal RMA
let currentRmaData = {
    id: null,
    folio: '',
    productName: '',
    productSku: '',
    customerName: '',
    customerPhone: '',
    amount: 399.00
};

function updateWhatsAppPreview() {
    const previewBox = document.getElementById('whatsappPreviewBox');
    if (!previewBox) return;

    const verdict = document.getElementById('verdictSelect')?.value || 'approved';
    const resType = document.getElementById('resolutionTypeSelect')?.value || 'replacement_order';
    const amountVal = parseFloat(document.getElementById('creditAmountInput')?.value || currentRmaData.amount);
    const amountFmt = '$' + (isNaN(amountVal) ? '399.00' : amountVal.toFixed(2)) + ' MXN';
    const skuVal = document.getElementById('replacementSkuInput')?.value || currentRmaData.productSku;

    let text = '';
    if (verdict === 'rejected_sanitary_violation') {
        text = `Estimado/a ${currentRmaData.customerName || 'Cliente'},\n\nTe informamos que tras la inspección técnica de tu solicitud ${currentRmaData.folio}, no fue posible admitir la prenda para devolución debido a que el precinto higiénico protector fue vulnerado o retirado, conforme a la normativa sanitaria de COFEPRIS y PROFECO para prendas íntimas y de contacto corporal.`;
    } else if (resType === 'store_credit') {
        text = `Hola ${currentRmaData.customerName || 'Cliente'},\n\nEn Mi Tienda Online tu devolución ${currentRmaData.folio} fue procesada con éxito.\nTu saldo a favor de ${amountFmt} está disponible con un cupón digital de un solo uso (vigencia 90 días).\nCanjéalo directamente en tu carrito al finalizar compra.`;
    } else {
        text = `Hola ${currentRmaData.customerName || 'Cliente'},\n\nEn Mi Tienda Online tu solicitud de garantía ${currentRmaData.folio} ha sido APROBADA.\nHemos generado tu orden de reemplazo sin costo (Variante: ${skuVal}).\nEl equipo de almacén ya prepara tu nuevo paquete. Consulta el rastreo en tu panel.`;
    }

    previewBox.textContent = text;
}

// Funciones globales para modales
function openThermalModal(order, tracking, courier, customer, destination) {
    document.getElementById('labelOrderNumber').textContent = order;
    document.getElementById('labelTrackingNumber').textContent = tracking;
    document.getElementById('labelCourier').textContent = courier.toUpperCase();
    document.getElementById('labelCustomer').textContent = customer;
    document.getElementById('labelDestination').textContent = destination;
    document.getElementById('thermalModal').classList.remove('hidden');
}

function closeThermalModal() {
    const modal = document.getElementById('thermalModal');
    if (modal) modal.classList.add('hidden');
}

function openRmaModal(id, rmaNumber, productName, productSku, customerName = 'Cliente', customerPhone = '--', defaultAmount = 399.00) {
    currentRmaData = {
        id: id,
        folio: rmaNumber,
        productName: productName,
        productSku: productSku,
        customerName: customerName,
        customerPhone: customerPhone,
        amount: defaultAmount
    };

    document.getElementById('rmaModalFolio').textContent = rmaNumber;
    document.getElementById('rmaModalProductName').textContent = productName;
    document.getElementById('rmaModalProductSku').textContent = productSku;
    document.getElementById('rmaModalCustomer').textContent = customerName;
    document.getElementById('rmaModalPhone').textContent = 'Tel: ' + (customerPhone || 'No registrado');
    
    const form = document.getElementById('rmaInspectForm');
    form.action = `/admin/logistica/rma/${id}/inspeccionar`;

    const repSkuInput = document.getElementById('replacementSkuInput');
    if (repSkuInput) repSkuInput.value = productSku;

    const amtInput = document.getElementById('creditAmountInput');
    if (amtInput) amtInput.value = defaultAmount.toFixed(2);

    const sealBox = document.getElementById('sealCheckbox');
    if (sealBox) sealBox.checked = true;

    const vSelect = document.getElementById('verdictSelect');
    if (vSelect) vSelect.value = 'approved';

    const rSelect = document.getElementById('resolutionTypeSelect');
    if (rSelect) rSelect.value = 'replacement_order';

    const repField = document.getElementById('replacementField');
    if (repField) repField.classList.remove('hidden');

    const credField = document.getElementById('storeCreditField');
    if (credField) credField.classList.add('hidden');

    updateWhatsAppPreview();

    document.getElementById('rmaModal').classList.remove('hidden');
}

function closeRmaModal() {
    const modal = document.getElementById('rmaModal');
    if (modal) modal.classList.add('hidden');
}
</script>
@endsection
