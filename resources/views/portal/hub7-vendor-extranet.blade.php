@extends('layouts.portal-panel')

@section('title', 'Hub 7: Extranet de Proveedores & Fabricantes')
@section('hub_kicker', 'Hub 7 - Extranet B2B')
@section('hub_title', 'Portal de Proveedores & Confeccionistas')
@section('hub_status', 'Activo')

@section('content')
<div 
    x-data="vendorExtranetApp({
        supplierId: {{ $supplier->id }},
        activeTab: 'orders',
        orders: {{ Js::from($orders) }},
        creditAgreements: {{ Js::from($creditAgreements) }}
    })" 
    class="w-full text-slate-800 font-sans antialiased"
>

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

    @if(session('error'))
        <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 cursor-pointer font-bold">&times;</button>
        </div>
    @endif

    {{-- MODO ADMINISTRADOR: SELECTOR DE PROVEEDORES (MODO SOPORTE / SIMULACIÓN) --}}
    @if($isGlobalAdmin)
        <div class="mb-6 p-4 rounded-xl bg-indigo-50 border border-indigo-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 animate-pulse"></span>
                <div>
                    <h3 class="text-xs font-bold text-indigo-950 uppercase tracking-wider">Modo Auditoria &amp; Soporte B2B</h3>
                    <p class="text-xs text-indigo-700">Visualizando extranet del fabricante seleccionado como Administrador.</p>
                </div>
            </div>
            <form method="GET" action="{{ route('portal.vendor.extranet') }}" class="flex items-center gap-2">
                <label for="supplierSelect" class="text-xs font-semibold text-indigo-900 shrink-0">Cambiar Proveedor:</label>
                <select 
                    id="supplierSelect" 
                    name="supplier_id" 
                    onchange="this.form.submit()" 
                    class="bg-white border border-indigo-300 text-indigo-900 text-xs rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden font-medium"
                >
                    @foreach($allSuppliers as $sup)
                        <option value="{{ $sup->id }}" {{ $sup->id === $supplier->id ? 'selected' : '' }}>
                            {{ $sup->name }} ({{ $sup->contact_name ?? 'Sin contacto' }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    {{-- CABECERA DE LA EMPRESA PROVEEDORA --}}
    <div class="mb-6 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-xl bg-slate-900 text-white font-black text-xl flex items-center justify-center shrink-0">
                {{ strtoupper(substr($supplier->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-bold text-slate-900">{{ $supplier->name }}</h2>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $supplier->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                        {{ $supplier->is_active ? 'Fabricante Activo' : 'Inactivo' }}
                    </span>
                    <span class="px-2 py-0.5 text-xs font-medium rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200">
                        ID: #{{ $supplier->id }}
                    </span>
                </div>
                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                    <span><strong>Contacto:</strong> {{ $supplier->contact_name ?? 'No especificado' }}</span>
                    <span><strong>Email:</strong> {{ $supplier->email ?? 'Sin correo' }}</span>
                    <span><strong>Telefono:</strong> {{ $supplier->phone ?? 'Sin telefono' }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a 
                href="{{ route('portal.allies') }}" 
                class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors flex items-center gap-1.5"
            >
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Directorio Hub 7</span>
            </a>
        </div>
    </div>

    {{-- TARJETAS KPI DE LA EXTRANET --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total OCs</span>
            <span class="text-xl font-black text-slate-900 mt-1 block">{{ $kpis['total_orders'] }}</span>
            <span class="text-[10px] text-slate-500">Emitidas a la fecha</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block">Por Confirmar</span>
            <span class="text-xl font-black text-amber-600 mt-1 block">{{ $kpis['pending_confirmation'] }}</span>
            <span class="text-[10px] text-slate-500">Aceptacion pendiente</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider block">En Produccion</span>
            <span class="text-xl font-black text-indigo-600 mt-1 block">{{ $kpis['in_production'] }}</span>
            <span class="text-[10px] text-slate-500">Corte / En transito</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block">Recibidas</span>
            <span class="text-xl font-black text-emerald-600 mt-1 block">{{ $kpis['completed_orders'] }}</span>
            <span class="text-[10px] text-slate-500">Cotejadas en rampa</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Prendas / Piezas</span>
            <span class="text-xl font-black text-slate-900 mt-1 block">{{ number_format($kpis['total_units']) }}</span>
            <span class="text-[10px] text-slate-500">Volumen contratado</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider block">Notas de Credito</span>
            <span class="text-xl font-black text-rose-600 mt-1 block">${{ number_format($kpis['pending_credits_amount'], 2) }}</span>
            <span class="text-[10px] text-slate-500">Por compensar</span>
        </div>
    </div>

    {{-- BARRA DE PESTAÑAS INTERNAS --}}
    <div class="mb-6 border-b border-slate-200 flex items-center justify-between">
        <nav class="flex gap-4 -mb-px">
            <button 
                type="button" 
                @click="activeTab = 'orders'"
                :class="activeTab === 'orders' ? 'border-indigo-600 text-indigo-600 bg-white shadow-xs rounded-t-lg font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="py-3 px-4 border-b-2 text-xs transition-all flex items-center gap-2 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span>Ordenes de Compra ({{ count($orders) }})</span>
            </button>
            <button 
                type="button" 
                @click="activeTab = 'credits'"
                :class="activeTab === 'credits' ? 'border-indigo-600 text-indigo-600 bg-white shadow-xs rounded-t-lg font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="py-3 px-4 border-b-2 text-xs transition-all flex items-center gap-2 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Minutas de Discrepancia &amp; NC ({{ count($creditAgreements) }})</span>
            </button>
            <button 
                type="button" 
                @click="activeTab = 'guidelines'"
                :class="activeTab === 'guidelines' ? 'border-indigo-600 text-indigo-600 bg-white shadow-xs rounded-t-lg font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="py-3 px-4 border-b-2 text-xs transition-all flex items-center gap-2 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Protocolo Inbound Rampa</span>
            </button>
        </nav>
    </div>

    {{-- CONTENIDO PESTAÑA 1: BANDEJA DE ORDENES DE COMPRA --}}
    <div x-show="activeTab === 'orders'" class="space-y-4">
        {{-- FILTRO Y BUSCADOR --}}
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-slate-500">Filtrar Estatus:</span>
                <select 
                    x-model="statusFilter" 
                    class="bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                >
                    <option value="all">Todas las OCs</option>
                    <option value="sent">Pendientes de Confirmar (sent/draft)</option>
                    <option value="confirmed">Confirmadas por Taller</option>
                    <option value="in_transit">En Transito a Bodega</option>
                    <option value="received">Recibidas / Concluidas</option>
                </select>
            </div>
            <div class="text-xs text-slate-500">
                Mostrando <strong x-text="filteredOrders.length"></strong> orden(es) asignadas
            </div>
        </div>

        {{-- TABLA DE ORDENES --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Folio OC</th>
                            <th class="py-3 px-4">Prioridad</th>
                            <th class="py-3 px-4">Fecha Emision</th>
                            <th class="py-3 px-4">Entrega Prometida</th>
                            <th class="py-3 px-4 text-center">Piezas</th>
                            <th class="py-3 px-4 text-right">Importe Neto</th>
                            <th class="py-3 px-4 text-center">Estatus</th>
                            <th class="py-3 px-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <template x-for="order in filteredOrders" :key="order.id">
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900" x-text="order.po_number"></td>
                                <td class="py-3.5 px-4">
                                    <span 
                                        :class="{
                                            'bg-rose-100 text-rose-800': order.priority === 'critical',
                                            'bg-amber-100 text-amber-800': order.priority === 'urgent',
                                            'bg-slate-100 text-slate-700': !order.priority || order.priority === 'normal'
                                        }"
                                        class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md"
                                        x-text="order.priority || 'NORMAL'"
                                    ></span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600" x-text="formatDate(order.created_at)"></td>
                                <td class="py-3.5 px-4 font-semibold text-indigo-700" x-text="formatDate(order.expected_delivery_date)"></td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-800" x-text="order.total_units"></td>
                                <td class="py-3.5 px-4 text-right font-black text-slate-900" x-text="'$' + formatMoney(order.total_amount) + ' MXN'"></td>
                                <td class="py-3.5 px-4 text-center">
                                    <span 
                                        :class="getStatusBadgeClass(order.status)"
                                        class="px-2.5 py-1 text-[11px] font-bold rounded-full"
                                        x-text="getStatusLabel(order.status)"
                                    ></span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- BOTON VER PARTIDAS / FICHA --}}
                                        <button 
                                            type="button" 
                                            @click="openOrderDetail(order)" 
                                            class="p-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition-colors cursor-pointer"
                                            title="Ver partidas y ficha de corte"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        {{-- BOTON CONFIRMAR OC --}}
                                        <button 
                                            type="button" 
                                            x-show="order.status === 'sent' || order.status === 'draft'" 
                                            @click="confirmOrder(order)" 
                                            class="px-2 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[11px] transition-colors cursor-pointer"
                                            title="Confirmar recepcion y programar produccion"
                                        >
                                            Confirmar
                                        </button>
                                        {{-- BOTON DESPACHAR / REMISION --}}
                                        <button 
                                            type="button" 
                                            x-show="order.status === 'confirmed' || order.status === 'sent'" 
                                            @click="openDispatchModal(order)" 
                                            class="px-2 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-[11px] transition-colors cursor-pointer"
                                            title="Registrar remision y aviso de despacho a bodega"
                                        >
                                            Despachar
                                        </button>
                                        <span 
                                            x-show="order.status === 'in_transit'" 
                                            class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-800 font-bold text-[10px]"
                                            title="Envio en ruta a bodega central"
                                        >
                                            En Ruta
                                        </span>
                                        {{-- BOTON IMPRIMIR / PDF --}}
                                        <a 
                                            :href="'/admin/extranet/proveedores/ordenes/' + order.id + '/imprimir'" 
                                            target="_blank" 
                                            class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer"
                                            title="Imprimir o descargar orden formal en PDF"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredOrders.length === 0">
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                No se encontraron ordenes de compra con el criterio seleccionado.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- CONTENIDO PESTAÑA 2: MINUTAS DE DISCREPANCIA Y NOTAS DE CRÉDITO --}}
    <div x-show="activeTab === 'credits'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Bitacora de Acuerdos de Credito &amp; Discrepancias</h3>
                    <p class="text-xs text-slate-500">Minutas levantadas en rampa inbound amparando faltantes, mermas o prendas no conformes.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Folio Minuta</th>
                            <th class="py-3 px-4">Tipo Discrepancia</th>
                            <th class="py-3 px-4 text-center">Faltantes</th>
                            <th class="py-3 px-4 text-center">Danadas</th>
                            <th class="py-3 px-4 text-right">Importe Compensatorio</th>
                            <th class="py-3 px-4 text-center">Estatus</th>
                            <th class="py-3 px-4">Aplicado En</th>
                            <th class="py-3 px-4">Fecha Acta</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <template x-for="ag in creditAgreements" :key="ag.id">
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900" x-text="ag.reference_folio"></td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800" x-text="ag.discrepancy_type || ag.agreement_type || 'Ajuste Inbound'"></td>
                                <td class="py-3.5 px-4 text-center text-amber-700 font-bold" x-text="ag.missing_units_count || 0"></td>
                                <td class="py-3.5 px-4 text-center text-rose-700 font-bold" x-text="ag.damaged_units_count || 0"></td>
                                <td class="py-3.5 px-4 text-right font-black text-rose-600" x-text="'-$' + formatMoney(ag.amount) + ' MXN'"></td>
                                <td class="py-3.5 px-4 text-center">
                                    <span 
                                        :class="{
                                            'bg-amber-100 text-amber-800': ag.status === 'pending',
                                            'bg-emerald-100 text-emerald-800': ag.status === 'applied',
                                            'bg-slate-100 text-slate-600': ag.status === 'void'
                                        }"
                                        class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full"
                                        x-text="ag.status === 'applied' ? 'Compensado' : (ag.status === 'pending' ? 'Pendiente' : 'Anulado')"
                                    ></span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 font-mono text-[11px]" x-text="ag.applied_to_po_id ? ('OC #' + ag.applied_to_po_id) : 'Pendiente de aplicar'"></td>
                                <td class="py-3.5 px-4 text-slate-500" x-text="formatDate(ag.created_at)"></td>
                            </tr>
                        </template>
                        <tr x-show="creditAgreements.length === 0">
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                No hay acuerdos compensatorios ni minutas de discrepancia registradas para este proveedor.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- CONTENIDO PESTAÑA 3: PROTOCOLO INBOUND RAMPA --}}
    <div x-show="activeTab === 'guidelines'" class="space-y-4">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs max-w-4xl">
            <h3 class="text-base font-bold text-slate-900 mb-2">Protocolo de Entrega en Rampa &amp; Estandar de Calidad</h3>
            <p class="text-xs text-slate-600 mb-6">Reglas obligatorias para recepcion expedita en Bodega Central y prevencion de minutas de discrepancia.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <span class="font-bold text-slate-800 block uppercase tracking-wider">1. Embalaje e Identificacion</span>
                    <p class="text-slate-600">Cada bulto debe contener una sola referencia de modelo/color con etiqueta de rotulacion indicando: Folio de OC, Modelo, Talla y Conteo de Piezas exacto.</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <span class="font-bold text-slate-800 block uppercase tracking-wider">2. Tolerancia de Medidas</span>
                    <p class="text-slate-600">Margen maximo admisible en tiro y cintura: +/- 0.5 cm segun la ficha tecnica autorizada. Desviaciones mayores causan retencion para revision tecnica.</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <span class="font-bold text-slate-800 block uppercase tracking-wider">3. Remision y Documentacion</span>
                    <p class="text-slate-600">El transportista debe presentar remision fisica firmada citando el numero de OC. Las remisiones registradas digitalmente agilizan el desahogo en rampa.</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <span class="font-bold text-slate-800 block uppercase tracking-wider">4. Cotejo de 3 Vias &amp; Pago</span>
                    <p class="text-slate-600">La dispersion financiera se programa a 7 dias naturales posteriores a la recepcion satisfactoria con folio de inspeccion de calidad aprobado.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL A PANTALLA COMPLETA: DETALLE DE PARTIDAS Y FICHA TÉCNICA DE OC --}}
    <div 
        x-show="isDetailModalOpen" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex flex-col bg-slate-900/80 backdrop-blur-md overflow-y-auto p-4 sm:p-6"
        style="display: none;"
    >
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-5xl w-full mx-auto my-auto flex flex-col overflow-hidden">
            {{-- HEADER DEL MODAL --}}
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <span class="px-2 py-0.5 text-xs font-bold rounded-md bg-indigo-500 text-white">EXPEDIENTE OC</span>
                    <h3 class="text-base font-bold" x-text="selectedOrder ? selectedOrder.po_number : ''"></h3>
                    <span class="text-xs text-slate-400" x-text="selectedOrder ? ('Prioridad: ' + (selectedOrder.priority || 'NORMAL')) : ''"></span>
                </div>
                <button 
                    type="button" 
                    @click="isDetailModalOpen = false" 
                    class="text-slate-400 hover:text-white transition-colors cursor-pointer text-xl font-bold"
                >
                    &times;
                </button>
            </div>

            {{-- CUERPO DEL DETALLE --}}
            <div class="p-6 space-y-6 overflow-y-auto max-h-[75vh]" x-show="selectedOrder">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
                    <div>
                        <span class="text-slate-400 font-bold uppercase block mb-1">Fecha Emision:</span>
                        <p class="font-bold text-slate-800" x-text="formatDate(selectedOrder?.created_at)"></p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold uppercase block mb-1">Fecha Entrega Prometida:</span>
                        <p class="font-bold text-indigo-700" x-text="formatDate(selectedOrder?.expected_delivery_date)"></p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold uppercase block mb-1">Estatus Actual:</span>
                        <p class="font-bold text-slate-900" x-text="getStatusLabel(selectedOrder?.status)"></p>
                    </div>
                </div>

                {{-- PARTIDAS POR TALLA Y COLOR --}}
                <div>
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Partidas Solicitadas &amp; Ficha de Corte</h4>
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 uppercase text-[10px]">
                                <tr>
                                    <th class="py-2.5 px-3">SKU</th>
                                    <th class="py-2.5 px-3">Prenda / Modelo</th>
                                    <th class="py-2.5 px-3">Talla</th>
                                    <th class="py-2.5 px-3">Color</th>
                                    <th class="py-2.5 px-3 text-center">Unidades</th>
                                    <th class="py-2.5 px-3 text-right">Costo Unit.</th>
                                    <th class="py-2.5 px-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <template x-for="item in (selectedOrder?.items || [])" :key="item.id">
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-2.5 px-3 font-mono font-medium text-slate-900" x-text="item.sku"></td>
                                        <td class="py-2.5 px-3 font-semibold text-slate-800" x-text="item.product?.title || item.product?.name || 'Prenda'"></td>
                                        <td class="py-2.5 px-3 text-slate-600 font-bold" x-text="item.variant?.size || '-'"></td>
                                        <td class="py-2.5 px-3 text-slate-600" x-text="item.variant?.color || '-'"></td>
                                        <td class="py-2.5 px-3 text-center font-bold text-slate-900" x-text="item.quantity_ordered"></td>
                                        <td class="py-2.5 px-3 text-right text-slate-700" x-text="'$' + formatMoney(item.unit_cost)"></td>
                                        <td class="py-2.5 px-3 text-right font-black text-slate-900" x-text="'$' + formatMoney(item.subtotal)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TOTALES --}}
                <div class="flex justify-end pt-2">
                    <div class="w-72 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs space-y-1.5">
                        <div class="flex justify-between text-slate-600">
                            <span>Total Unidades:</span>
                            <span class="font-bold text-slate-800" x-text="selectedOrder?.total_units"></span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Base Gravable:</span>
                            <span class="font-semibold" x-text="'$' + formatMoney(selectedOrder?.subtotal) + ' MXN'"></span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>IVA 16%:</span>
                            <span class="font-semibold" x-text="'$' + formatMoney(selectedOrder?.tax_amount) + ' MXN'"></span>
                        </div>
                        <div class="border-t border-slate-300 pt-1.5 flex justify-between font-black text-slate-900 text-sm">
                            <span>Total Facturado:</span>
                            <span class="text-indigo-700" x-text="'$' + formatMoney(selectedOrder?.total_amount) + ' MXN'"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FOOTER DEL MODAL --}}
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                <a 
                    :href="'/admin/extranet/proveedores/ordenes/' + (selectedOrder ? selectedOrder.id : '') + '/imprimir'" 
                    target="_blank" 
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Imprimir Documento Oficial</span>
                </a>
                <button 
                    type="button" 
                    @click="isDetailModalOpen = false" 
                    class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-lg transition-colors cursor-pointer"
                >
                    Cerrar Ventana
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL A PANTALLA COMPLETA: REGISTRO DE REMISIÓN / DESPACHO FLETERO (TASK-INT-20) --}}
    <div 
        x-show="isDispatchModalOpen" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex flex-col bg-slate-900/80 backdrop-blur-md overflow-y-auto p-4 sm:p-6"
        style="display: none;"
    >
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-3xl w-full mx-auto my-auto flex flex-col overflow-hidden">
            {{-- HEADER DEL MODAL --}}
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <span class="px-2 py-0.5 text-xs font-bold rounded-md bg-indigo-500 text-white">AVISO DE EMBARQUE</span>
                    <h3 class="text-base font-bold" x-text="dispatchOrderData ? ('Despachar ' + dispatchOrderData.po_number) : ''"></h3>
                </div>
                <button 
                    type="button" 
                    @click="isDispatchModalOpen = false" 
                    class="text-slate-400 hover:text-white transition-colors cursor-pointer text-xl font-bold"
                >
                    &times;
                </button>
            </div>

            {{-- FORMULARIO DE DESPACHO --}}
            <form @submit.prevent="submitDispatch()" class="p-6 space-y-4 overflow-y-auto max-h-[75vh]">
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900">
                    <span class="font-bold block mb-1">Sincronizacion Automatica con Rampa de Bodega:</span>
                    Este registro notificara inmediatamente a la rampa de recepcion en Hub 4 y asignara una tarea operativa al jefe de almacen para preparar la descarga.
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label for="carrierNameInput" class="font-bold text-slate-700 block mb-1">Empresa Fletera / Transportista: *</label>
                        <input 
                            id="carrierNameInput"
                            type="text" 
                            x-model="dispatchForm.carrier_name" 
                            list="carrierOptions"
                            placeholder="Ej. Transportes Castores, Tres Guerras, DHL..." 
                            required 
                            class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                        >
                        <datalist id="carrierOptions">
                            <option value="Transportes Castores">
                            <option value="Transportes Tres Guerras">
                            <option value="Paquetexpress">
                            <option value="Fletera Propia / Chofer Local">
                            <option value="DHL Express Mexico">
                            <option value="Estafeta Carga">
                            <option value="FedEx Freight">
                        </datalist>
                    </div>

                    <div>
                        <label for="trackingNumberInput" class="font-bold text-slate-700 block mb-1">Numero de Guia Fletera / Rastreo: *</label>
                        <input 
                            id="trackingNumberInput"
                            type="text" 
                            x-model="dispatchForm.tracking_number" 
                            placeholder="Ej. CAS-88492019-MX" 
                            required 
                            class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden font-mono"
                        >
                    </div>

                    <div>
                        <label for="remissionNumberInput" class="font-bold text-slate-700 block mb-1">Folio de Remision Interna del Taller:</label>
                        <input 
                            id="remissionNumberInput"
                            type="text" 
                            x-model="dispatchForm.remission_number" 
                            placeholder="Ej. REM-2026-094" 
                            class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden font-mono"
                        >
                    </div>

                    <div>
                        <label for="packagesCountInput" class="font-bold text-slate-700 block mb-1">Cantidad de Bultos / Cajas: *</label>
                        <input 
                            id="packagesCountInput"
                            type="number" 
                            min="1" 
                            x-model.number="dispatchForm.packages_count" 
                            required 
                            class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden font-bold"
                        >
                    </div>

                    <div>
                        <label for="dispatchedUnitsInput" class="font-bold text-slate-700 block mb-1">Piezas / Prendas Despachadas:</label>
                        <input 
                            id="dispatchedUnitsInput"
                            type="number" 
                            min="1" 
                            x-model.number="dispatchForm.dispatched_units" 
                            class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden font-bold"
                        >
                    </div>

                    <div>
                        <label for="estimatedArrivalInput" class="font-bold text-slate-700 block mb-1">Fecha Estimada de Llegada a Rampa:</label>
                        <input 
                            id="estimatedArrivalInput"
                            type="date" 
                            x-model="dispatchForm.estimated_arrival_date" 
                            class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                        >
                    </div>

                    <div>
                        <label for="driverNameInput" class="font-bold text-slate-700 block mb-1">Nombre del Operador / Conductor:</label>
                        <input 
                            id="driverNameInput"
                            type="text" 
                            x-model="dispatchForm.driver_name" 
                            placeholder="Opcional" 
                            class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                        >
                    </div>

                    <div>
                        <label for="licensePlateInput" class="font-bold text-slate-700 block mb-1">Placas del Vehiculo:</label>
                        <input 
                            id="licensePlateInput"
                            type="text" 
                            x-model="dispatchForm.license_plate" 
                            placeholder="Opcional" 
                            class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden font-mono uppercase"
                        >
                    </div>
                </div>

                <div class="text-xs">
                    <label for="dispatchNotesInput" class="font-bold text-slate-700 block mb-1">Instrucciones Especiales / Notas para Almacen:</label>
                    <textarea 
                        id="dispatchNotesInput"
                        x-model="dispatchForm.notes" 
                        rows="2" 
                        placeholder="Ej. Cajas rotuladas con lote 26-B. Requiere patin hidraulico para descarga."
                        class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    ></textarea>
                </div>

                {{-- FOOTER DEL MODAL FORMULARIO --}}
                <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                    <button 
                        type="button" 
                        @click="isDispatchModalOpen = false" 
                        class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-lg transition-colors cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        :disabled="isSubmittingDispatch" 
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-bold rounded-lg shadow-xs transition-colors flex items-center gap-2 cursor-pointer"
                    >
                        <span x-text="isSubmittingDispatch ? 'Registrando Despacho...' : 'Confirmar Despacho & Notificar a Rampa'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function vendorExtranetApp(config) {
    return {
        supplierId: config.supplierId,
        activeTab: config.activeTab || 'orders',
        orders: config.orders || [],
        creditAgreements: config.creditAgreements || [],
        statusFilter: 'all',
        selectedOrder: null,
        isDetailModalOpen: false,

        // Estado para modal de despacho fletero
        isDispatchModalOpen: false,
        dispatchOrderData: null,
        isSubmittingDispatch: false,
        dispatchForm: {
            carrier_name: '',
            tracking_number: '',
            remission_number: '',
            packages_count: 1,
            dispatched_units: 0,
            driver_name: '',
            license_plate: '',
            estimated_arrival_date: '',
            notes: ''
        },

        init() {
            // Asegurar sidebar contraido por defecto (regla de interfaz)
            if (document.body && !document.body.classList.contains('collapsed')) {
                document.body.classList.add('collapsed');
            }
        },

        get filteredOrders() {
            if (this.statusFilter === 'all') {
                return this.orders;
            }
            if (this.statusFilter === 'sent') {
                return this.orders.filter(o => o.status === 'sent' || o.status === 'draft');
            }
            return this.orders.filter(o => o.status === this.statusFilter);
        },

        openOrderDetail(order) {
            this.selectedOrder = order;
            this.isDetailModalOpen = true;
        },

        openDispatchModal(order) {
            this.dispatchOrderData = order;
            this.dispatchForm = {
                carrier_name: '',
                tracking_number: '',
                remission_number: 'REM-' + new Date().getFullYear() + '-' + String(Math.floor(100 + Math.random() * 900)),
                packages_count: 1,
                dispatched_units: order.total_units || 0,
                driver_name: '',
                license_plate: '',
                estimated_arrival_date: '',
                notes: ''
            };
            this.isDispatchModalOpen = true;
        },

        submitDispatch() {
            if (!this.dispatchOrderData) return;
            this.isSubmittingDispatch = true;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch('/api/v1/hub7/vendor/orders/' + this.dispatchOrderData.id + '/dispatch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(this.dispatchForm)
            })
            .then(res => res.json())
            .then(data => {
                this.isSubmittingDispatch = false;
                if (data && data.success) {
                    this.dispatchOrderData.status = 'in_transit';
                    this.dispatchOrderData.carrier = this.dispatchForm.carrier_name;
                    this.dispatchOrderData.tracking_number = this.dispatchForm.tracking_number;
                    this.isDispatchModalOpen = false;
                    alert(data.message || 'Despacho registrado exitosamente.');
                } else {
                    alert(data.message || 'Error al registrar el despacho.');
                }
            })
            .catch(err => {
                this.isSubmittingDispatch = false;
                console.error('Error al registrar despacho:', err);
                alert('Ocurrio un error de comunicacion con el servidor.');
            });
        },

        confirmOrder(order) {
            if (!confirm('Desea confirmar la aceptacion y programacion de la orden de compra ' + order.po_number + '?')) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch('/api/v1/hub7/vendor/orders/' + order.id + '/confirm', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    order.status = 'confirmed';
                    alert(data.message || 'Orden confirmada exitosamente.');
                } else {
                    alert(data.message || 'Error al confirmar la orden.');
                }
            })
            .catch(err => {
                console.error('Error al confirmar orden:', err);
                alert('Ocurrio un error de comunicacion con el servidor.');
            });
        },

        formatMoney(amount) {
            const val = parseFloat(amount || 0);
            return val.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            return `${day}/${month}/${year}`;
        },

        getStatusBadgeClass(status) {
            switch (status) {
                case 'confirmed':
                    return 'bg-indigo-100 text-indigo-800';
                case 'in_transit':
                    return 'bg-purple-100 text-purple-800';
                case 'received':
                    return 'bg-emerald-100 text-emerald-800';
                case 'cancelled':
                    return 'bg-rose-100 text-rose-800';
                default:
                    return 'bg-amber-100 text-amber-800';
            }
        },

        getStatusLabel(status) {
            switch (status) {
                case 'confirmed':
                    return 'Confirmada / En Corte';
                case 'in_transit':
                    return 'En Transito a Bodega';
                case 'received':
                    return 'Recibida en Rampa';
                case 'cancelled':
                    return 'Cancelada';
                default:
                    return 'Pendiente de Confirmar';
            }
        }
    };
}
</script>
@endsection
