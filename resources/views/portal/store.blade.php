@extends('layouts.portal-panel')

@section('title', 'Tienda & CMS Multiverso')
@section('hub_kicker', 'Hub 2')
@section('hub_title', 'Tienda & CMS Multiverso')
@section('hub_status', '4 Vitrinas Activas')

@section('content')
<div id="storeAppRoot" class="w-full text-slate-800 font-sans antialiased" data-screen="store">
    <style>
        .tab-panel.hidden {
            display: none !important;
        }
        .tab-panel:not(.hidden) {
            display: block !important;
        }
        textarea {
            resize: vertical !important;
        }
        .store-subnav-grid {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 10px !important;
            width: 100% !important;
        }
        @media (max-width: 900px) {
            .store-subnav-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
        @media (max-width: 580px) {
            .store-subnav-grid {
                grid-template-columns: 1fr !important;
            }
        }
        .universe-pill-btn {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 7px 14px !important;
            border-radius: 9999px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #334155 !important;
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
        }
        .universe-pill-btn:hover {
            background: #f8fafc !important;
            border-color: #94a3b8 !important;
            color: #0f172a !important;
        }
        .universe-pill-btn.active {
            background: #0f172a !important;
            color: #ffffff !important;
            border-color: #0f172a !important;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.2) !important;
        }
        .universe-dot-indicator {
            width: 8px !important;
            height: 8px !important;
            border-radius: 9999px !important;
            flex-shrink: 0 !important;
        }
        .store-accordion-section {
            border-radius: 1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .store-accordion-section:hover {
            border-color: #cbd5e1;
        }
        .store-accordion-header {
            cursor: pointer;
            user-select: none;
        }
        .store-accordion-chevron {
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .store-subnav-grid {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 10px !important;
            width: 100% !important;
        }
        @media (max-width: 1024px) {
            .store-subnav-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }
        @media (max-width: 640px) {
            .store-subnav-grid {
                grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
            }
        }
        .store-subnav-btn {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 10px 14px !important;
            border-radius: 10px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #475569 !important;
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
            text-align: left !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .store-subnav-btn:hover {
            color: #0f172a !important;
            background: #f8fafc !important;
            border-color: #94a3b8 !important;
        }
        .store-subnav-btn.active {
            background-color: #0f172a !important;
            color: #ffffff !important;
            border-color: #0f172a !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.15) !important;
        }
        .store-subnav-btn svg {
            color: #64748b !important;
            flex-shrink: 0 !important;
        }
        .store-subnav-btn.active svg {
            color: #ffffff !important;
        }
        .store-subnav-btn .store-subnav-count {
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            padding: 2px 7px !important;
            border-radius: 6px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            white-space: nowrap !important;
            border: 1px solid #e2e8f0 !important;
        }
        .store-subnav-btn.active .store-subnav-count {
            background-color: #334155 !important;
            color: #ffffff !important;
            border-color: #475569 !important;
        }
        .store-subnav-btn .section-help-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 18px !important;
            height: 18px !important;
            border-radius: 9999px !important;
            background: #e2e8f0 !important;
            color: #475569 !important;
            font-size: 11px !important;
            font-weight: bold !important;
            border: none !important;
            cursor: pointer !important;
            margin-left: 4px !important;
            line-height: 1 !important;
        }
        .store-subnav-btn.active .section-help-btn {
            background: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
        }
        .store-subnav-btn .section-help-btn:hover {
            background: #cbd5e1 !important;
            color: #0f172a !important;
        }
        .store-subnav-btn.active .section-help-btn:hover {
            background: rgba(255, 255, 255, 0.4) !important;
            color: #ffffff !important;
        }
    </style>
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
    {{-- REGLA 10: BARRA DE PESTAÑAS DE SUBMÓDULO EN 2 FILAS (CERO SCROLL HORIZONTAL) --}}
    {{-- ========================================================================= --}}
    <div class="store-subnav-bar mb-6 border-b border-slate-200 pb-3 flex flex-col gap-3">
        <div class="flex items-center justify-between gap-3">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Submódulos de Tienda y CMS (Hub 2)</span>
            <div class="flex items-center gap-3 text-xs">
                <span class="flex items-center gap-1.5 font-medium text-slate-600 bg-white px-2.5 py-1 rounded-full border border-slate-200 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Motor Editorial: Sincronizado</span>
                </span>
                <a href="{{ route('store.home') }}" target="_blank" rel="noopener" class="text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1.5 cursor-pointer transition-colors" style="text-decoration:none;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Ver Storefront D2C</span>
                </a>
            </div>
        </div>

        <!-- PESTAÑAS DE SUBMÓDULO UNIFICADAS -->
        <nav class="store-subnav-grid w-full" aria-label="Submódulos de Tienda y CMS">
            <!-- TAB 1: EDITOR VISUAL -->
            <button class="store-subnav-btn active" data-action="switch-store-tab" data-tab="visual" onclick="window.switchStoreTab('visual')" type="button">
                <div class="flex items-center gap-2 overflow-hidden">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                    <span class="truncate">1. Editor Visual</span>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <span class="store-subnav-count">4 Vitrinas</span>
                    <span class="section-help-btn" role="button" tabindex="0" aria-label="Ayuda Editor Visual" data-help-title="Editor Visual Multiverso" data-help-desc="Ajusta la paleta de 8 colores, logos y apariencia visual de cada vitrina manteniendo un inventario unificado.">?</span>
                </div>
            </button>

            <!-- TAB 2: CONTENIDO & REGLAS (ACORDEÓN UNIFICADO) -->
            <button class="store-subnav-btn" data-action="switch-store-tab" data-tab="content_rules" onclick="window.switchStoreTab('content_rules')" type="button">
                <div class="flex items-center gap-2 overflow-hidden">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><path d="M4 6h16M4 12h16M4 18h10"/><polyline points="14 18 17 21 21 17"/></svg>
                    <span class="truncate">2. Contenido &amp; Reglas</span>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <span class="store-subnav-count">4 Módulos</span>
                    <span class="section-help-btn" role="button" tabindex="0" aria-label="Ayuda Contenido y Reglas" data-help-title="Contenido, Reglas &amp; Editorial" data-help-desc="Panel unificado con 4 módulos expandibles: Banners &amp; Marquesinas, Reglas de Flete, Guía Oficial de Tallas y Editor de Pie de Página (Footer).">?</span>
                </div>
            </button>

            <!-- TAB 3: PÁGINAS DE EXPLORAR -->
            <button class="store-subnav-btn" data-action="switch-store-tab" data-tab="pages" onclick="window.switchStoreTab('pages')" type="button">
                <div class="flex items-center gap-2 overflow-hidden">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span class="truncate">3. Páginas de Explorar</span>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <span class="store-subnav-count">{{ $pages->count() ?? 0 }} Págs</span>
                </div>
            </button>

            <!-- TAB 4: TRACKING & PIXELS -->
            <button class="store-subnav-btn" data-action="switch-store-tab" data-tab="tracking" onclick="window.switchStoreTab('tracking')" type="button">
                <div class="flex items-center gap-2 overflow-hidden">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    <span class="truncate">4. Tracking &amp; Pixels</span>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <span class="store-subnav-count">{{ ($tracking && $tracking->is_active) ? 'Activo' : 'Pausado' }}</span>
                </div>
            </button>

            <!-- TAB 5: FILTROS DE CATÁLOGO -->
            <button class="store-subnav-btn" data-action="switch-store-tab" data-tab="filters" onclick="window.switchStoreTab('filters')" type="button">
                <div class="flex items-center gap-2 overflow-hidden">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span class="truncate">5. Filtros Catálogo</span>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <span class="store-subnav-count">{{ $catalogFilters->count() ?? 0 }} Filtros</span>
                </div>
            </button>
        </nav>
    </div>

    {{-- ========================================================================= --}}
    {{-- REGLA 6.4: CONMUTADOR DE VITRINA MULTIVERSO GLOBAL EN EL ENCABEZADO SUPERIOR DE TODAS LAS PESTAÑAS --}}
    {{-- ========================================================================= --}}
    <div class="universe-selector-bar bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5 mr-1">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                <span>Seleccionar Vitrina:</span>
            </span>
            <button class="universe-pill-btn active" data-action="switch-store-universe" data-universe="marte" onclick="window.switchStoreUniverse('marte')" type="button">
                <span class="universe-dot-indicator" style="background: #4f46e5;"></span>
                <span>Marte (Masculino / Queer)</span>
            </button>
            <button class="universe-pill-btn" data-action="switch-store-universe" data-universe="venus" onclick="window.switchStoreUniverse('venus')" type="button">
                <span class="universe-dot-indicator" style="background: #db2777;"></span>
                <span>Venus (Lencería / Beauty)</span>
            </button>
            <button class="universe-pill-btn" data-action="switch-store-universe" data-universe="tiresias" onclick="window.switchStoreUniverse('tiresias')" type="button">
                <span class="universe-dot-indicator" style="background: #8b5cf6;"></span>
                <span>Tiresias (Moda Afirmativa)</span>
            </button>
            <button class="universe-pill-btn" data-action="switch-store-universe" data-universe="terra" onclick="window.switchStoreUniverse('terra')" type="button">
                <span class="universe-dot-indicator" style="background: #0284c7;"></span>
                <span>Terra (Matriz / Unisex)</span>
            </button>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400 font-medium">Universo Activo:</span>
            <span id="activeUniverseContextBadge" class="px-3 py-1 rounded-full font-bold text-xs bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-2xs flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                <span id="activeUniverseContextText">Marte Store &bull; {{ $storefronts['marte']['activeSkus'] }} Modelos</span>
            </span>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- REGLA 10: SECCIÓN 2: TARJETAS DE KPIS CONTEXTUALES (NIVEL 2)               --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- KPI 1: VITRINAS ACTIVAS -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Vitrinas Multiverso</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Vitrinas" data-help-title="Vitrinas Multiverso" data-help-desc="Marcas segmentadas por identidad que comparten un único inventario central en bodega.">?</button>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono tracking-tight text-slate-900">4 Marcas</span>
                <span class="text-xs font-bold text-emerald-600 font-mono">100% Online</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">Terra &bull; Marte &bull; Venus &bull; Tiresias</div>
        </div>

        <!-- KPI 2: SKUS VISIBLES -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">SKUs Visibles en Vitrina</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda SKUs" data-help-title="SKUs Visibles" data-help-desc="Cantidad de modelos de prendas y cuidado personal filtrados para esta vitrina.">?</button>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono tracking-tight text-indigo-600" data-kpi-skus-count>{{ $storefronts['marte']['activeSkus'] }} Modelos</span>
                <span class="text-xs font-bold text-indigo-700 font-mono" data-kpi-skus-brand>Marte Store</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">Desacoplados de stock físico único (TASK-INT-10)</div>
        </div>

        <!-- KPI 3: PALETA DE COLOR (8 TOKENS) -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Paleta Multitoken</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Color" data-help-title="Paleta Multitoken (8 Tokens)" data-help-desc="Tokens cromáticos compilados para cabecera, acentos, superficies, fondos, bordes y textos de esta vitrina.">?</button>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div class="flex items-center gap-1.5" data-kpi-palette-swatches>
                    <span class="w-3.5 h-3.5 rounded-full border border-slate-300 shadow-2xs" data-kpi-token="primary" title="Primario" style="background: {{ $storefronts['marte']['primaryColor'] ?? '#0f172a' }};"></span>
                    <span class="w-3.5 h-3.5 rounded-full border border-slate-300 shadow-2xs" data-kpi-token="secondary" title="Secundario" style="background: {{ $storefronts['marte']['secondaryColor'] ?? '#334155' }};"></span>
                    <span class="w-3.5 h-3.5 rounded-full border border-slate-300 shadow-2xs" data-kpi-token="accent" title="Acento" style="background: {{ $storefronts['marte']['accentColor'] }};"></span>
                    <span class="w-3.5 h-3.5 rounded-full border border-slate-300 shadow-2xs" data-kpi-token="background" title="Fondo" style="background: {{ $storefronts['marte']['backgroundColor'] }};"></span>
                    <span class="w-3.5 h-3.5 rounded-full border border-slate-300 shadow-2xs" data-kpi-token="surface" title="Superficie" style="background: {{ $storefronts['marte']['surfaceColor'] ?? '#ffffff' }};"></span>
                    <span class="w-3.5 h-3.5 rounded-full border border-slate-300 shadow-2xs" data-kpi-token="text" title="Texto" style="background: {{ $storefronts['marte']['textColor'] ?? '#0f172a' }};"></span>
                    <span class="w-3.5 h-3.5 rounded-full border border-slate-300 shadow-2xs" data-kpi-token="border" title="Borde" style="background: {{ $storefronts['marte']['borderColor'] ?? '#e2e8f0' }};"></span>
                </div>
                <span class="text-xs font-bold text-indigo-700 font-mono">8 Tokens</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">Tokens CSS sincronizados con Storefront</div>
        </div>

        <!-- KPI 4: PREVISUALIZADOR EN VIVO -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Previsualización en Vivo</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Previsualizador" data-help-title="Previsualizador" data-help-desc="Vista previa responsive a escala sin necesidad de abrir una ventana incógnito.">?</button>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono tracking-tight text-emerald-600">Desktop</span>
                <span class="text-xs font-bold text-emerald-700 font-mono" data-kpi-preview-universe>Marte Store</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">Escala adaptativa en tiempo real</div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- REGLA 10: SECCIÓN 3: PANELES Y ESPACIO DE TRABAJO OPERATIVO (NIVEL 3)     --}}
    {{-- ========================================================================= --}}
    
    <!-- SUBMÓDULO 1: EDITOR VISUAL MULTIVERSO -->
    <div id="storeVisualPanel" class="tab-panel">

        <!-- PANEL PRINCIPAL A 2 COLUMNAS: FORMULARIO vs PREVIEW EN VIVO -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
            <!-- COLUMNA IZQUIERDA: CONFIGURADOR EDITORIAL & IDENTIDAD -->
            <div class="lg:col-span-5 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900" id="vitrinaEditorTitle">Personalizar Vitrina: {{ $storefronts['marte']['name'] }}</h3>
                        <p class="text-xs text-slate-500" id="vitrinaEditorKicker">{{ $storefronts['marte']['kicker'] }}</p>
                    </div>
                    <button class="section-help-btn" type="button" aria-label="Ayuda Editor de Vitrina" data-help-title="Editor de Vitrina" data-help-desc="Ajusta los copies, llamados a la acción y paleta de la vitrina seleccionada.">?</button>
                </div>

                <form id="vitrinaConfigForm" method="POST" action="{{ route('portal.store.vitrinas.update', ['universe' => 'marte']) }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                    @csrf

                    <!-- Callout: Desacoplamiento de Hero -->
                    <div class="p-3 bg-amber-50/80 border border-amber-200/90 rounded-xl text-amber-900 flex items-start gap-2.5 shadow-2xs">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div class="text-[11px] leading-relaxed">
                            <span class="font-bold">Desacoplamiento Modular:</span> El Hero principal y los carruseles ya no se configuran como textos fijos aquí. Se gestionan dinámicamente con <strong>Drag & Drop</strong>, HTML/CSS personalizado y filtros en el constructor modular inferior.
                        </div>
                    </div>

                    <!-- 1. Identidad de Vitrina -->
                    <div class="border-b border-slate-100 pb-3 space-y-3">
                        <div class="font-bold text-slate-800 text-[11px] uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>1. Identidad de Vitrina</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Nombre Comercial</label>
                                <input type="text" name="brand_name" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-indigo-500 font-semibold" data-store-name-input value="{{ $storefronts['marte']['name'] }}">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Kicker / Tagline</label>
                                <input type="text" name="kicker" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-indigo-500" data-store-kicker-input value="{{ $storefronts['marte']['kicker'] }}">
                            </div>
                        </div>
                    </div>

                    <!-- 2. Logos & Favicon del Sitio -->
                    <div class="border-b border-slate-100 pb-3 space-y-3">
                        <div class="font-bold text-slate-800 text-[11px] uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>2. Logos & Favicon del Sitio</span>
                        </div>

                        <!-- Logo Principal (Desktop) -->
                        <div class="bg-slate-50/70 p-2.5 rounded-xl border border-slate-200/70">
                            <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Logo Desktop (Header)</label>
                            <div class="flex items-center gap-2 mb-1.5">
                                <div class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center overflow-hidden shrink-0" data-preview-thumb="logo">
                                    @if(!empty($storefronts['marte']['logoUrl']))
                                        <img src="{{ $storefronts['marte']['logoUrl'] }}" class="max-h-full max-w-full object-contain" alt="Logo">
                                    @else
                                        <span class="text-[9px] text-slate-400 font-mono">None</span>
                                    @endif
                                </div>
                                <input type="text" name="logo_url" placeholder="https://... o ruta relativa" class="flex-1 px-2.5 py-1.5 border border-slate-200 rounded-lg text-[11px]" data-store-logo-input value="{{ $storefronts['marte']['logoUrl'] ?? '' }}">
                            </div>
                            <input type="file" name="logo_file" accept="image/*" class="w-full text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[11px] file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" data-store-logo-file>
                        </div>

                        <!-- Logo Móvil (PWA) -->
                        <div class="bg-slate-50/70 p-2.5 rounded-xl border border-slate-200/70">
                            <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Logo Móvil (Header PWA)</label>
                            <div class="flex items-center gap-2 mb-1.5">
                                <div class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center overflow-hidden shrink-0" data-preview-thumb="logo-mobile">
                                    @if(!empty($storefronts['marte']['logoMobileUrl']))
                                        <img src="{{ $storefronts['marte']['logoMobileUrl'] }}" class="max-h-full max-w-full object-contain" alt="Logo Móvil">
                                    @else
                                        <span class="text-[9px] text-slate-400 font-mono">None</span>
                                    @endif
                                </div>
                                <input type="text" name="logo_mobile_url" placeholder="https://... o ruta relativa" class="flex-1 px-2.5 py-1.5 border border-slate-200 rounded-lg text-[11px]" data-store-logo-mobile-input value="{{ $storefronts['marte']['logoMobileUrl'] ?? '' }}">
                            </div>
                            <input type="file" name="logo_mobile_file" accept="image/*" class="w-full text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[11px] file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" data-store-logo-mobile-file>
                        </div>

                        <!-- Favicon del Sitio -->
                        <div class="bg-slate-50/70 p-2.5 rounded-xl border border-slate-200/70">
                            <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Favicon (.ico, .png, .svg)</label>
                            <div class="flex items-center gap-2 mb-1.5">
                                <div class="w-7 h-7 rounded-md bg-white border border-slate-200 flex items-center justify-center overflow-hidden shrink-0" data-preview-thumb="favicon">
                                    @if(!empty($storefronts['marte']['faviconUrl']))
                                        <img src="{{ $storefronts['marte']['faviconUrl'] }}" class="w-4 h-4 object-contain" alt="Favicon">
                                    @else
                                        <span class="text-[8px] text-slate-400 font-mono">.ico</span>
                                    @endif
                                </div>
                                <input type="text" name="favicon_url" placeholder="https://... o /favicon.ico" class="flex-1 px-2.5 py-1.5 border border-slate-200 rounded-lg text-[11px]" data-store-favicon-input value="{{ $storefronts['marte']['faviconUrl'] ?? '' }}">
                            </div>
                            <input type="file" name="favicon_file" accept="image/x-icon,image/png,image/svg+xml" class="w-full text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[11px] file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" data-store-favicon-file>
                        </div>
                    </div>

                    <!-- 3. Paleta Cromática Integral (8 Tokens) -->
                    <div class="space-y-3">
                        <div class="font-bold text-slate-800 text-[11px] uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 4 4 0 014-4 4 4 0 014 4 4 4 0 01-4 4zm0 0l1.293-1.293a1 1 0 011.414 0L12 22m7-9a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>3. Paleta Cromática Integral (8 Tokens)</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <!-- Primario -->
                            <div class="p-2 border border-slate-200 rounded-lg bg-slate-50/50">
                                <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Primario (Brand)</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" name="primary_color" class="w-7 h-7 rounded border border-slate-200 cursor-pointer p-0" data-color-token="primary" value="{{ $storefronts['marte']['primaryColor'] ?? '#0f172a' }}">
                                    <input type="text" class="w-20 px-1.5 py-1 text-[10px] font-mono border border-slate-200 rounded bg-white" data-color-label="primary" value="{{ $storefronts['marte']['primaryColor'] ?? '#0f172a' }}">
                                </div>
                            </div>

                            <!-- Secundario -->
                            <div class="p-2 border border-slate-200 rounded-lg bg-slate-50/50">
                                <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Secundario (Badges)</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" name="secondary_color" class="w-7 h-7 rounded border border-slate-200 cursor-pointer p-0" data-color-token="secondary" value="{{ $storefronts['marte']['secondaryColor'] ?? '#334155' }}">
                                    <input type="text" class="w-20 px-1.5 py-1 text-[10px] font-mono border border-slate-200 rounded bg-white" data-color-label="secondary" value="{{ $storefronts['marte']['secondaryColor'] ?? '#334155' }}">
                                </div>
                            </div>

                            <!-- Acento -->
                            <div class="p-2 border border-slate-200 rounded-lg bg-slate-50/50">
                                <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Acento (CTA/Precios)</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" name="accent_color" class="w-7 h-7 rounded border border-slate-200 cursor-pointer p-0" data-color-token="accent" value="{{ $storefronts['marte']['accentColor'] }}">
                                    <input type="text" class="w-20 px-1.5 py-1 text-[10px] font-mono border border-slate-200 rounded bg-white" data-color-label="accent" value="{{ $storefronts['marte']['accentColor'] }}">
                                </div>
                            </div>

                            <!-- Fondo Lienzo -->
                            <div class="p-2 border border-slate-200 rounded-lg bg-slate-50/50">
                                <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Fondo Lienzo (Body)</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" name="background_color" class="w-7 h-7 rounded border border-slate-200 cursor-pointer p-0" data-color-token="background" value="{{ $storefronts['marte']['backgroundColor'] }}">
                                    <input type="text" class="w-20 px-1.5 py-1 text-[10px] font-mono border border-slate-200 rounded bg-white" data-color-label="background" value="{{ $storefronts['marte']['backgroundColor'] }}">
                                </div>
                            </div>

                            <!-- Superficie -->
                            <div class="p-2 border border-slate-200 rounded-lg bg-slate-50/50">
                                <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Superficie (Cards/Modales)</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" name="surface_color" class="w-7 h-7 rounded border border-slate-200 cursor-pointer p-0" data-color-token="surface" value="{{ $storefronts['marte']['surfaceColor'] ?? '#ffffff' }}">
                                    <input type="text" class="w-20 px-1.5 py-1 text-[10px] font-mono border border-slate-200 rounded bg-white" data-color-label="surface" value="{{ $storefronts['marte']['surfaceColor'] ?? '#ffffff' }}">
                                </div>
                            </div>

                            <!-- Texto Principal -->
                            <div class="p-2 border border-slate-200 rounded-lg bg-slate-50/50">
                                <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Texto Principal</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" name="text_color" class="w-7 h-7 rounded border border-slate-200 cursor-pointer p-0" data-color-token="text" value="{{ $storefronts['marte']['textColor'] ?? '#0f172a' }}">
                                    <input type="text" class="w-20 px-1.5 py-1 text-[10px] font-mono border border-slate-200 rounded bg-white" data-color-label="text" value="{{ $storefronts['marte']['textColor'] ?? '#0f172a' }}">
                                </div>
                            </div>

                            <!-- Texto Secundario / Muted -->
                            <div class="p-2 border border-slate-200 rounded-lg bg-slate-50/50">
                                <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Texto Secundario (Muted)</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" name="muted_text_color" class="w-7 h-7 rounded border border-slate-200 cursor-pointer p-0" data-color-token="muted_text" value="{{ $storefronts['marte']['mutedTextColor'] ?? '#64748b' }}">
                                    <input type="text" class="w-20 px-1.5 py-1 text-[10px] font-mono border border-slate-200 rounded bg-white" data-color-label="muted_text" value="{{ $storefronts['marte']['mutedTextColor'] ?? '#64748b' }}">
                                </div>
                            </div>

                            <!-- Bordes / Separadores -->
                            <div class="p-2 border border-slate-200 rounded-lg bg-slate-50/50">
                                <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Bordes & Separadores</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="color" name="border_color" class="w-7 h-7 rounded border border-slate-200 cursor-pointer p-0" data-color-token="border" value="{{ $storefronts['marte']['borderColor'] ?? '#e2e8f0' }}">
                                    <input type="text" class="w-20 px-1.5 py-1 text-[10px] font-mono border border-slate-200 rounded bg-white" data-color-label="border" value="{{ $storefronts['marte']['borderColor'] ?? '#e2e8f0' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button class="w-full button button-quiet py-2.5 text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white rounded-lg cursor-pointer flex items-center justify-center gap-2 transition shadow-sm" data-action="save-storefront-config" type="submit" id="saveVitrinaBtn">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Guardar Identidad y Paleta de Vitrina</span>
                        </button>
                    </div>
                </form>
            </div>

        <!-- COLUMNA DERECHA: PREVISUALIZADOR EN VIVO A ESCALA -->
        <div class="lg:col-span-7">
            <div class="storefront-preview-container bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
                <!-- Barra de Controles de Previsualización -->
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-700">Previsualizador en Vivo:</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold text-white uppercase tracking-wider" style="background: {{ $storefronts['marte']['accentColor'] }};" data-preview-universe-badge>{{ $storefronts['marte']['name'] }}</span>
                    </div>
                    <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-md p-1 shadow-2xs">
                        <button class="px-2 py-1 rounded text-xs font-semibold bg-slate-900 text-white cursor-pointer" data-preview-device-btn="desktop" data-action="set-preview-device" data-device="desktop" type="button">
                            Desktop
                        </button>
                        <button class="px-2 py-1 rounded text-xs font-semibold text-slate-600 cursor-pointer" data-preview-device-btn="mobile" data-action="set-preview-device" data-device="mobile" type="button">
                            Mobile (375px)
                        </button>
                    </div>
                </div>

                <!-- Marco de la Tienda -->
                <div class="storefront-preview-frame desktop" data-storefront-preview-frame style="background-color: {{ $storefronts['marte']['backgroundColor'] }}; border-color: {{ $storefronts['marte']['borderColor'] ?? '#e2e8f0' }};">
                    <!-- Topbar Simulado -->
                    <div class="preview-top-bar" data-preview-topbar style="background: {{ $storefronts['marte']['primaryColor'] ?? '#09090b' }}; color: #ffffff; border-bottom: 1px solid {{ $storefronts['marte']['borderColor'] ?? '#334155' }};">
                        <div class="flex items-center gap-2">
                            <!-- Logo render o SVG / Marca -->
                            <div class="preview-brand-logo flex items-center" data-preview-logo-container>
                                @if(!empty($storefronts['marte']['logoUrl']))
                                    <img src="{{ $storefronts['marte']['logoUrl'] }}" alt="Logo" class="h-5 max-w-[120px] object-contain" data-preview-logo-img>
                                @else
                                    <span class="font-black tracking-tight text-white text-sm" data-preview-brand-name>{{ $storefronts['marte']['name'] }}</span>
                                @endif
                            </div>
                            <span class="text-[9px] px-1.5 py-0.5 rounded font-mono" style="background: {{ $storefronts['marte']['secondaryColor'] ?? '#334155' }}; color: #ffffff;" data-preview-kicker-badge>{{ $storefronts['marte']['kicker'] }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-[10px]" style="color: {{ $storefronts['marte']['mutedTextColor'] ?? '#94a3b8' }};">
                            <span>Catálogo</span>
                            <span>Revista</span>
                            <span class="font-bold" style="color: #ffffff;">Carrito (0)</span>
                        </div>
                    </div>

                    <!-- Marquesina Dinámica Superior -->
                    <div class="preview-marquee-strip" style="background: {{ $storefronts['marte']['accentColor'] }}; color: #ffffff;" data-preview-marquee>
                        {{ $announcements[0]['text'] ?? 'Envíos asegurados y compras discretas' }}
                    </div>

                    <!-- Showcase / Vitrina Activa Banner (Desacoplado de Hero) -->
                    <div class="preview-showcase-section p-6 text-center transition-all" data-preview-showcase style="background: {{ $storefronts['marte']['surfaceColor'] ?? '#ffffff' }}; border-bottom: 1px solid {{ $storefronts['marte']['borderColor'] ?? '#e2e8f0' }};">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider mb-2" data-preview-badge style="background: {{ $storefronts['marte']['backgroundColor'] }}; color: {{ $storefronts['marte']['accentColor'] }}; border: 1px solid {{ $storefronts['marte']['borderColor'] ?? '#e2e8f0' }};">
                            Vitrina Activa &bull; <span data-preview-kicker-text>{{ $storefronts['marte']['kicker'] }}</span>
                        </span>
                        <h2 class="preview-showcase-title text-lg font-black mb-1" data-preview-title style="color: {{ $storefronts['marte']['textColor'] ?? '#0f172a' }};">
                            {{ $storefronts['marte']['name'] }}
                        </h2>
                        <p class="preview-showcase-sub text-xs max-w-md mx-auto mb-3" data-preview-sub style="color: {{ $storefronts['marte']['mutedTextColor'] ?? '#64748b' }};">
                            Paleta cromática de 8 tokens y activos de marca sincronizados en tiempo real con la tienda en línea.
                        </p>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold text-white shadow-xs" data-preview-cta style="background: {{ $storefronts['marte']['accentColor'] }};">
                            <span>Explorar Catálogo</span>
                            <span>&rarr;</span>
                        </div>
                    </div>

                    <!-- Estante de Productos Muestra -->
                    <div class="preview-grid-shelf" data-preview-grid-shelf style="background: {{ $storefronts['marte']['backgroundColor'] }};">
                        @foreach($storefronts['marte']['sampleProducts'] as $p)
                            <div class="preview-product-card" data-preview-product-card style="background: {{ $storefronts['marte']['surfaceColor'] ?? '#ffffff' }}; border: 1px solid {{ $storefronts['marte']['borderColor'] ?? '#e2e8f0' }};">
                                <div class="preview-product-img" style="background: {{ $storefronts['marte']['backgroundColor'] }};">
                                    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" style="color: {{ $storefronts['marte']['mutedTextColor'] ?? '#64748b' }};"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                </div>
                                <div class="font-bold truncate text-[11px]" data-preview-product-title style="color: {{ $storefronts['marte']['textColor'] ?? '#0f172a' }};">{{ $p['name'] }}</div>
                                <div class="text-[10px] mb-1" data-preview-product-tag style="color: {{ $storefronts['marte']['mutedTextColor'] ?? '#64748b' }};">{{ $p['tag'] }}</div>
                                <div class="font-bold font-mono text-xs" data-preview-product-price style="color: {{ $storefronts['marte']['accentColor'] }};">MXN ${{ $p['price'] }}.00</div>
                                <button type="button" class="mt-2 w-full py-1 rounded text-[10px] font-bold text-white" data-preview-product-btn style="background: {{ $storefronts['marte']['primaryColor'] ?? '#0f172a' }};">Añadir</button>
                            </div>
                        @endforeach
                    </div>
                </div>
                </div>
            </div>
        </div>

    <!-- GESTOR DE SECCIONES MODULARES DEL HOME (DRAG & DROP) -->
    <div class="w-full bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm mb-6 mt-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-4 mb-4 gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                    <span>Secciones Modulares del Home (Drag &amp; Drop Reordenable)</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Arrastra y suelta las filas para cambiar su posición en la plantilla. Se sincroniza en tiempo real con la tienda.</p>
            </div>
            <div class="flex items-center gap-2">
                <span id="reorderSyncStatus" class="text-[11px] font-semibold text-emerald-600 hidden flex items-center gap-1 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Orden sincronizado</span>
                </span>
                <button class="button button-quiet py-2 px-3 text-xs font-bold bg-slate-900 text-white rounded-lg shadow-sm flex items-center gap-1.5 cursor-pointer hover:bg-slate-800 transition" data-action="open-create-section-modal" onclick="window.openCreateSectionModal()" type="button">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Crear Nueva Sección</span>
                </button>
            </div>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse min-w-[700px]" id="sectionsTable">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase tracking-wider bg-slate-50/75">
                        <th class="py-3 px-3 font-bold text-center w-32">Reordenar</th>
                        <th class="py-3 px-4 font-bold">Nombre &amp; Título</th>
                        <th class="py-3 px-3 font-bold w-44">Tipo de Sección</th>
                        <th class="py-3 px-3 font-bold w-36">Vitrina Destino</th>
                        <th class="py-3 px-3 font-bold text-center w-28">Estado</th>
                        <th class="py-3 px-4 font-bold text-right w-40">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="sectionsSortableBody">
                    @forelse($sections as $sec)
                        <tr class="hover:bg-slate-50/80 transition-colors select-none" data-section-id="{{ $sec->id }}" draggable="true">
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <div class="drag-handle p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded cursor-grab active:cursor-grabbing transition" title="Arrastra para reordenar">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="9" cy="5" r="1.5"/>
                                            <circle cx="9" cy="12" r="1.5"/>
                                            <circle cx="9" cy="19" r="1.5"/>
                                            <circle cx="15" cy="5" r="1.5"/>
                                            <circle cx="15" cy="12" r="1.5"/>
                                            <circle cx="15" cy="19" r="1.5"/>
                                        </svg>
                                    </div>
                                    <span class="font-mono font-bold text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-700 section-order-badge">#{{ $sec->sort_order }}</span>
                                    <div class="flex flex-col gap-0.5">
                                        <button type="button" class="btn-move-section p-0.5 hover:bg-slate-200 rounded text-[9px] text-slate-500 cursor-pointer leading-none" data-action="move-section-up" onclick="window.moveSectionRow(this, -1)" data-section-id="{{ $sec->id }}" title="Mover arriba">&#9650;</button>
                                        <button type="button" class="btn-move-section p-0.5 hover:bg-slate-200 rounded text-[9px] text-slate-500 cursor-pointer leading-none" data-action="move-section-down" onclick="window.moveSectionRow(this, 1)" data-section-id="{{ $sec->id }}" title="Mover abajo">&#9660;</button>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <strong class="text-slate-900 block font-semibold text-xs">{{ $sec->name }}</strong>
                                <span class="text-slate-500 text-[11px] block mt-0.5 truncate max-w-md">{{ $sec->title ?? 'Sin título visible' }}</span>
                                <div class="mt-1 flex items-center gap-1.5">
                                    <code class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-[10px] font-mono text-slate-700">#section-{{ $sec->id }}</code>
                                    <button type="button" onclick="window.copyStoreRoute('#section-{{ $sec->id }}')" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-semibold cursor-pointer underline flex items-center gap-0.5" title="Copiar ruta ancla para vincular en botones del Hero">
                                        <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg> Copiar Ruta
                                    </button>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-1 rounded text-[10px] font-bold font-mono bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ $sec->type }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-1 rounded text-[10px] font-bold uppercase {{
                                    $sec->universe === 'marte' ? 'bg-blue-100 text-blue-800' :
                                    ($sec->universe === 'venus' ? 'bg-pink-100 text-pink-800' :
                                    ($sec->universe === 'tiresias' ? 'bg-purple-100 text-purple-800' :
                                    ($sec->universe === 'terra' ? 'bg-sky-100 text-sky-800' :
                                    'bg-slate-100 text-slate-800')))
                                }}">
                                    {{ $sec->universe }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($sec->status === 'active')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Activa</span>
                                @elseif($sec->status === 'archived')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Archivada</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Borrador</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    <button class="button button-light py-1 px-2 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-50 rounded-lg shadow-2xs cursor-pointer" data-action="open-edit-section-modal" data-section-id="{{ $sec->id }}" onclick="window.openEditSectionModal({{ $sec->id }})" type="button" title="Editar información, vitrina y contenido">
                                        Editar
                                    </button>
                                    @if($sec->status !== 'archived')
                                        <button class="button button-light py-1 px-2 text-[11px] font-semibold text-amber-700 hover:bg-amber-50 rounded-lg shadow-2xs cursor-pointer" data-action="archive-section" data-section-id="{{ $sec->id }}" data-section-name="{{ $sec->name }}" onclick="window.archiveStoreSection(this.dataset.sectionId, this.dataset.sectionName)" type="button" title="Archivar sección (no se muestra en tienda)">
                                            Archivar
                                        </button>
                                    @endif
                                    <button class="button button-light py-1 px-2 text-[11px] font-semibold rounded-lg shadow-2xs cursor-pointer hover:bg-slate-100" data-action="toggle-section-status" data-section-id="{{ $sec->id }}" onclick="window.toggleSectionStatus(this.dataset.sectionId)" type="button" title="Alternar entre activo y borrador">
                                         {{ $sec->status === 'active' ? 'Pausar' : 'Activar' }}
                                    </button>
                                    <button class="button button-light py-1 px-2 text-[11px] font-semibold text-rose-600 hover:bg-rose-50 rounded-lg shadow-2xs cursor-pointer" data-action="delete-section" data-section-id="{{ $sec->id }}" data-section-name="{{ $sec->name }}" onclick="window.deleteSectionDirect(this.dataset.sectionId, this.dataset.sectionName)" type="button" title="Eliminar sección definitivamente">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 text-xs">No hay secciones registradas en el CMS para esta vitrina.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
    <!-- FIN SUBMÓDULO 1: EDITOR VISUAL -->

    <!-- ========================================================================= -->
    <!-- SUBMÓDULO 2: CONTENIDO, REGLAS & PUBLICACIONES (ACORDEÓN UNIFICADO)        -->
    <!-- ========================================================================= -->
    <div id="storeContentRulesPanel" class="tab-panel hidden" style="display: none;">
        <!-- Header de Control de Secciones Acordeón -->
        <div class="flex items-center justify-between bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    <span>Gestión de Contenidos, Políticas y Publicaciones</span>
                </h3>
                <p class="text-xs text-slate-500">Expande o contrae cada sección para configurar banners, fletes, guías de tallas y la revista digital.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="button button-light py-1 px-3 text-xs font-semibold rounded-lg cursor-pointer" onclick="window.toggleAllStoreAccordions(true)">
                    Expandir Todo
                </button>
                <button type="button" class="button button-light py-1 px-3 text-xs font-semibold rounded-lg cursor-pointer" onclick="window.toggleAllStoreAccordions(false)">
                    Contraer Todo
                </button>
            </div>
        </div>

        <!-- SECCIÓN 1 ACORDEÓN: BANNERS & MARQUESINAS -->
        <div class="store-accordion-section border border-slate-200/80 rounded-2xl bg-white shadow-sm mb-4 overflow-hidden" id="accordionSecBanners">
            <button type="button" class="store-accordion-header w-full flex items-center justify-between p-4 bg-slate-50/70 hover:bg-slate-100/70 transition cursor-pointer text-left" onclick="window.toggleStoreAccordion('accordionSecBanners')">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">1. Banners Promocionales &amp; Marquesinas Superiores</div>
                        <div class="text-[11px] text-slate-500">Avisos destacados en cabecera y carruseles hero segmentados por vitrina.</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-mono">{{ count($banners) }} Banners</span>
                    <svg class="store-accordion-chevron w-5 h-5 text-slate-400 transition-transform duration-200 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </button>
            <div class="store-accordion-body p-6 border-t border-slate-100" id="accordionSecBannersBody">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-3 mb-4 gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/></svg>
                        <span>Marquesina Superior de Anuncios (Announcement Bar)</span>
                    </h3>
                    <p class="text-xs text-slate-500">Avisos destacados en el encabezado global de la tienda para promociones, envíos y puntos de entrega.</p>
                </div>
                <button class="button button-light py-1.5 px-3 text-xs font-semibold rounded-lg cursor-pointer" data-action="toggle-announcement-bar" type="button">
                    {{ $announcementActive ? 'Pausar Marquesina' : 'Activar Marquesina' }}
                </button>
            </div>

            <div class="space-y-2 mb-6">
                @foreach($announcements as $index => $a)
                    <div class="flex items-center justify-between p-3 border border-slate-200 rounded-xl bg-slate-50 text-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-slate-900 text-white font-bold flex items-center justify-center text-[10px] font-mono">{{ $index + 1 }}</span>
                            <div>
                                <span class="font-bold text-slate-800">{{ $a['text'] }}</span>
                                <span class="text-[10px] text-slate-400 block mt-0.5 font-mono">Enlace: {{ $a['link'] }}</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">{{ $a['badge'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-slate-100 pt-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Carruseles Hero &amp; Banners de Colección (Regla 14 WebP)</h3>
                        <p class="text-xs text-slate-500">Imágenes editoriales segmentadas por vitrina multiverso con orden de aparición.</p>
                    </div>
                    <button class="section-help-btn" type="button" aria-label="Ayuda Banners Hero" data-help-title="Banners Hero" data-help-desc="Banners de portada con relación de aspecto 16:9 optimizados para dispositivos móviles y escritorio.">?</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 uppercase tracking-wider bg-slate-50/50">
                                <th class="p-3 font-semibold">Orden</th>
                                <th class="p-3 font-semibold">Título del Banner</th>
                                <th class="p-3 font-semibold">Vitrina / Badge</th>
                                <th class="p-3 font-semibold">Ubicación</th>
                                <th class="p-3 font-semibold">Archivo WebP</th>
                                <th class="p-3 font-semibold">Estado</th>
                                <th class="p-3 font-semibold text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($banners as $b)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="p-3 font-mono font-bold text-slate-700">#{{ $b['sortOrder'] }}</td>
                                    <td class="p-3">
                                        <strong class="text-slate-900 block">{{ $b['title'] }}</strong>
                                        <span class="text-slate-500 text-[11px] block mt-0.5 truncate max-w-sm">{{ $b['subtitle'] }}</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{
                                            $b['universe'] === 'marte' ? 'bg-blue-100 text-blue-800' :
                                            ($b['universe'] === 'venus' ? 'bg-pink-100 text-pink-800' :
                                            ($b['universe'] === 'tiresias' ? 'bg-purple-100 text-purple-800' :
                                            ($b['universe'] === 'terra' ? 'bg-sky-100 text-sky-800' :
                                            'bg-slate-100 text-slate-800')))
                                        }}">{{ $b['badge'] }}</span>
                                    </td>
                                    <td class="p-3 font-mono text-[11px] text-slate-600">{{ $b['placement'] }}</td>
                                    <td class="p-3 font-mono text-[11px] text-slate-500">{{ $b['imageUrl'] }}</td>
                                    <td class="p-3">
                                        @if($b['active'])
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Activo</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-600">Pausado</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-right">
                                        <button class="button button-light py-1 px-2.5 text-[11px] rounded shadow-2xs cursor-pointer" data-action="toggle-banner-status" data-banner-id="{{ $b['id'] }}" type="button">
                                            {{ $b['active'] ? 'Pausar' : 'Activar' }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- FIN SECCIÓN 1 ACORDEÓN -->

        <!-- SECCIÓN 2 ACORDEÓN: REGLAS DE FLETE & SIMULADOR FINANCIERO -->
        <div class="store-accordion-section border border-slate-200/80 rounded-2xl bg-white shadow-sm mb-4 overflow-hidden" id="accordionSecShipping">
            <button type="button" class="store-accordion-header w-full flex items-center justify-between p-4 bg-slate-50/70 hover:bg-slate-100/70 transition cursor-pointer text-left" onclick="window.toggleStoreAccordion('accordionSecShipping')">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">2. Reglas de Flete, Despacho &amp; Simulador Financiero (TASK-INT-18)</div>
                        <div class="text-[11px] text-slate-500">Umbrales de envío gratis, tarifas fijas, punto físico y simulador de elasticidad.</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-mono">Umbral ${{ number_format($shippingRule['freeShippingThreshold']) }}</span>
                    <svg class="store-accordion-chevron w-5 h-5 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </button>
            <div class="store-accordion-body p-6 border-t border-slate-100 hidden" id="accordionSecShippingBody" style="display: none;">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- CONFIGURADOR DE REGLAS DE DESPACHO -->
            <div class="lg:col-span-5 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Configuración de Reglas de Envío</span>
                        </h3>
                        <p class="text-xs text-slate-500">Parámetros aplicados en checkout para paquetería y puntos de entrega.</p>
                    </div>
                    <button class="section-help-btn" type="button" aria-label="Ayuda Reglas de Envío" data-help-title="Reglas de Envío" data-help-desc="Determina cuándo se cobra flete estándar ($149 MXN) y cuándo se bonifica en automático.">?</button>
                </div>

                <form id="shippingRuleForm" method="POST" action="{{ route('portal.store.shipping.update') }}" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Umbral para Envío Gratis Nacional (MXN $)</label>
                        <input type="number" step="1" name="freeShippingThreshold" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-bold text-slate-900 font-mono" data-shipping-threshold-input value="{{ $shippingRule['freeShippingThreshold'] }}">
                        <small class="text-[11px] text-slate-500 block mt-1">Carritos con subtotal igual o superior a esta cifra no pagan flete.</small>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tarifa Flete Estándar (Compras menores al umbral)</label>
                        <input type="number" step="1" name="flatRate" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-bold text-slate-900 font-mono" data-shipping-flat-input value="{{ $shippingRule['flatRate'] }}">
                        <small class="text-[11px] text-slate-500 block mt-1">Cobro fijo adicionado a la orden si no califica para envío gratis.</small>
                    </div>

                    <div class="p-4 border border-slate-200 rounded-xl bg-slate-50">
                        <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
                            <input type="hidden" name="localPickupEnabled" value="0">
                            <input type="checkbox" name="localPickupEnabled" value="1" class="rounded border-slate-300 text-indigo-600" data-shipping-pickup-check {{ $shippingRule['localPickupEnabled'] ? 'checked' : '' }}>
                            <span>Habilitar Entrega en Punto Físico (Metro Chabacano)</span>
                        </label>
                        <div class="mt-2 text-[11px] text-slate-600 pl-5 space-y-1">
                            <div><strong class="text-slate-800">Ubicación:</strong> {{ $shippingRule['localPickupLocation'] }}</div>
                            <div><strong class="text-slate-800">Horarios:</strong> {{ $shippingRule['localPickupSchedule'] }}</div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button class="w-full button button-quiet py-2 text-xs font-bold bg-slate-900 text-white rounded-lg cursor-pointer" data-action="save-shipping-rule" type="submit">
                            Guardar Reglas de Flete
                        </button>
                    </div>
                </form>
            </div>

        <!-- SIMULADOR FINANCIERO DE ELASTICIDAD -->
        <div class="lg:col-span-7">
            <div class="simulator-card bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            <span>Simulador de Elasticidad de Envío Gratis (TASK-INT-18)</span>
                        </h3>
                        <p class="text-xs text-slate-500">Modela cómo impacta variar el umbral en el volumen de ventas y en la rentabilidad neta.</p>
                    </div>
                    <button class="section-help-btn" type="button" aria-label="Ayuda Simulador de Elasticidad" data-help-title="Simulador de Elasticidad" data-help-desc="Calcula la relación entre ticket promedio, porcentaje de órdenes bonificadas y absorción del flete courier.">?</button>
                </div>

                <!-- Slider -->
                <div class="mb-6 bg-slate-50 border border-slate-200 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-700">Umbral Simulado:</span>
                        <span class="text-base font-bold text-indigo-600 font-mono" data-simulator-threshold-val>MXN ${{ number_format($simulator['currentThreshold']) }}</span>
                    </div>
                    <input type="range" min="699" max="1499" step="50" value="{{ $simulator['currentThreshold'] }}" class="slider-range-input w-full cursor-pointer" data-action="simulate-threshold-slider">
                    <div class="flex justify-between text-[10px] text-slate-400 mt-1 font-mono">
                        <span>$699 (Agresivo)</span>
                        <span>$999 (Estándar Actual)</span>
                        <span>$1,499 (Conservador)</span>
                    </div>
                </div>

                <!-- Métricas Proyectadas -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs mb-4">
                    <div class="simulator-metric-card p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="text-[11px] text-slate-500 font-semibold block">Pedidos Estimados</span>
                        <strong class="text-sm text-slate-900 block mt-1 font-mono" data-simulator-orders-val>{{ $simulator['projectedOrders'] }} pedidos/mes</strong>
                        <small class="text-[10px] text-slate-400 block mt-0.5">Basado en elasticidad 1.15x</small>
                    </div>
                    <div class="simulator-metric-card p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="text-[11px] text-slate-500 font-semibold block">% Pedidos Bonificados</span>
                        <strong class="text-sm text-indigo-600 block mt-1 font-mono" data-simulator-ratio-val>{{ $simulator['freeShippingRatio'] }}% de pedidos</strong>
                        <small class="text-[10px] text-slate-400 block mt-0.5">Órdenes sobre umbral</small>
                    </div>
                    <div class="simulator-metric-card p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="text-[11px] text-slate-500 font-semibold block">Fletes Absorbidos</span>
                        <strong class="text-sm text-red-600 block mt-1 font-mono" data-simulator-cost-val>MXN ${{ number_format($simulator['totalShippingAbsorbed']) }}</strong>
                        <small class="text-[10px] text-slate-400 block mt-0.5">Costo mensual absorbido</small>
                    </div>
                    <div class="simulator-metric-card p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="text-[11px] text-slate-500 font-semibold block">Margen EBITDA Proyectado</span>
                        <strong class="text-sm {{ $simulator['isHealthy'] ? 'text-emerald-600' : 'text-amber-600' }} block mt-1 font-mono" data-simulator-margin-val>{{ $simulator['projectedEbitdaMargin'] }}%</strong>
                        <small class="text-[10px] text-slate-400 block mt-0.5">Mínimo saludable: &ge; 30%</small>
                    </div>
                </div>

                <!-- Diagnóstico -->
                <div class="p-3.5 rounded-xl border {{ $simulator['isHealthy'] ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-amber-50 border-amber-200 text-amber-900' }} text-xs" data-simulator-diag-box>
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <strong class="font-bold block">Diagnóstico de Absorción:</strong>
                            <p class="mt-0.5 text-[11px] leading-relaxed">
                                @if($simulator['isHealthy'])
                                    El umbral actual protege el margen operativo por encima del umbral mínimo de viabilidad (30% EBITDA) absorbiendo costos de paquetería sin comprometer flujo de caja.
                                @else
                                    Precaución: El umbral simulado compromete el margen neto debido a que una alta proporción de órdenes diluye la utilidad frente al flete courier de $145 MXN.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<!-- FIN SECCIÓN 2 ACORDEÓN -->

        <!-- SECCIÓN 3 ACORDEÓN: CONTENIDO EDITORIAL & TALLAS -->
        <div class="store-accordion-section border border-slate-200/80 rounded-2xl bg-white shadow-sm mb-4 overflow-hidden" id="accordionSecEditorial">
            <button type="button" class="store-accordion-header w-full flex items-center justify-between p-4 bg-slate-50/70 hover:bg-slate-100/70 transition cursor-pointer text-left" onclick="window.toggleStoreAccordion('accordionSecEditorial')">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">3. Guía Oficial de Tallas &amp; Protocolos de Cuidado (TASK-INT-10)</div>
                        <div class="text-[11px] text-slate-500">Matriz de medidas anatómicas en PDP, lavado de fibras y leyendas sanitarias PROFECO.</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded bg-amber-50 text-amber-700 font-mono">{{ count($sizeGuide) }} Tallas</span>
                    <svg class="store-accordion-chevron w-5 h-5 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </button>
            <div class="store-accordion-body p-6 border-t border-slate-100 hidden" id="accordionSecEditorialBody" style="display: none;">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        <span>Guía Oficial de Tallas Textiles Torzo (TASK-INT-10)</span>
                    </h3>
                    <p class="text-xs text-slate-500">Matriz interactiva de medidas anatómicas desplegada en la ficha de producto (PDP) de boxers, jocks y bralettes.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="button button-quiet py-1 px-3 text-xs font-semibold bg-slate-900 text-white rounded-lg cursor-pointer" data-action="open-edit-size-guide-modal" type="button">
                        Editar Guía
                    </button>
                    <button class="section-help-btn" type="button" aria-label="Ayuda Guía de Tallas" data-help-title="Guía de Tallas" data-help-desc="Medidas de cintura y cadera tomadas en reposo sobre cinta métrica flexible.">?</button>
                </div>
            </div>

        <div class="overflow-x-auto mb-6">
            <table class="size-guide-table w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 uppercase tracking-wider bg-slate-50/50">
                        <th class="p-3">Talla</th>
                        <th class="p-3">Cintura (cm)</th>
                        <th class="p-3">Cadera (cm)</th>
                        <th class="p-3">Equivalencia Pantalón</th>
                        <th class="p-3">Recomendación de Calce</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($sizeGuide as $s)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="p-3">
                                <strong class="font-bold text-slate-900">{{ $s['size'] }}</strong>
                                @if($s['isCore'])
                                    <span class="size-core-pill ml-2 px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">Talla Core</span>
                                @endif
                            </td>
                            <td class="p-3 font-mono text-slate-700">{{ $s['waistCm'] }}</td>
                            <td class="p-3 font-mono text-slate-700">{{ $s['hipsCm'] }}</td>
                            <td class="p-3 font-mono font-bold text-slate-800">{{ $s['pantsSize'] }}</td>
                            <td class="p-3 text-slate-600 text-[11px]">{{ $s['advice'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
            <!-- Cuidados de Lavado -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2 mb-3">
                    <h4 class="text-xs font-bold text-slate-900">Protocolos de Cuidado de Ropa Delicada</h4>
                    <button class="text-indigo-600 hover:text-indigo-800 text-[11px] font-bold cursor-pointer" data-action="open-edit-care-modal" type="button">Editar Protocolos</button>
                </div>
                <div class="space-y-2.5 text-xs">
                    @foreach($careProtocols as $c)
                        <div class="p-2.5 bg-white border border-slate-200/80 rounded-lg">
                            <strong class="text-slate-900 block font-bold">{{ $c['fiber'] }}</strong>
                            <p class="text-slate-600 text-[11px] mt-0.5">{{ $c['rule'] }}</p>
                            <span class="text-[10px] text-amber-800 font-semibold block bg-amber-50 p-1.5 rounded border border-amber-200 mt-1.5">
                                Advertencia: {{ $c['warning'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Aviso Sanitario PROFECO -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2 mb-3">
                    <h4 class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#059669" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span>Aviso Normativo y Sanitario PROFECO</span>
                    </h4>
                    <button class="text-indigo-600 hover:text-indigo-800 text-[11px] font-bold cursor-pointer" data-action="open-edit-profeco-modal" type="button">Editar Aviso</button>
                </div>
                <div class="space-y-3 text-xs">
                    <p class="text-slate-600 text-[11px] leading-relaxed">
                        Por razones estrictas de protección a la salud e higiene pública, la ropa interior (boxers, tangas, jockstraps, bralettes y binders) y los cosméticos en contacto íntimo no son susceptibles de devolución o cambio una vez violado su precinto termosellado original de fábrica.
                    </p>
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-blue-900 text-[11px] leading-relaxed">
                        <strong>Transparencia con el Cliente:</strong> Esta leyenda se muestra de manera visible en el checkout, confirmación de pedido y en el exterior del paquete rotulado por Almacén.
                    </div>
                </div>
            </div>
            </div>
        </div>
        <!-- FIN SECCIÓN 3 ACORDEÓN -->

        <!-- SECCIÓN 4 ACORDEÓN: EDITOR OFICIAL DE PIE DE PÁGINA (FOOTER DE TIENDA) -->
        <div class="store-accordion-section border border-slate-200/80 rounded-2xl bg-white shadow-sm mb-4 overflow-hidden" id="accordionSecFooter">
            <button type="button" class="store-accordion-header w-full flex items-center justify-between p-4 bg-slate-50/70 hover:bg-slate-100/70 transition cursor-pointer text-left" onclick="window.toggleStoreAccordion('accordionSecFooter')">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">4. Editor Oficial de Pie de Página (Footer &amp; Contacto)</div>
                        <div class="text-[11px] text-slate-500">Configuración global del pie de página del Storefront: narrativa corporativa, canales de soporte, badges de confianza y derechos.</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded bg-teal-50 text-teal-700 font-mono">Storefront PWA</span>
                    <svg class="store-accordion-chevron w-5 h-5 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </button>
            <div class="store-accordion-body p-6 border-t border-slate-100 hidden" id="accordionSecFooterBody" style="display: none;">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-6">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Configuración y Contenidos del Footer Storefront</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Control directo sobre los textos corporativos, números de atención al cliente y distintivos de seguridad que se renderizan en el pie de página.</p>
                    </div>
                    <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-mono">CmsOfficialDocument: STORE_FOOTER_CONFIG</span>
                </div>

                <form method="POST" action="{{ route('portal.store.documents.update', ['code' => 'STORE_FOOTER_CONFIG']) }}" class="space-y-6 text-xs">
                    @csrf

                    <!-- Grid 1: Narrativa y Contacto -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-4">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Narrativa de Marca / Resumen Corporativo</label>
                                <textarea style="resize: vertical !important;" name="payload[brand_summary]" rows="4" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs leading-relaxed focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">{{ $footerConfig['brand_summary'] ?? '' }}</textarea>
                                <span class="text-[10px] text-slate-400 block mt-1">Párrafo institucional descriptivo visible debajo del nombre de marca en la primera columna del footer.</span>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Texto de Copyright &amp; Titularidad Legal</label>
                                <input type="text" name="payload[copyright_text]" value="{{ $footerConfig['copyright_text'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                                <span class="text-[10px] text-slate-400 block mt-1">Línea de derechos reservada al pie de la página antes de los enlaces legales.</span>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">WhatsApp de Atención al Cliente</label>
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-emerald-50 text-emerald-700 rounded-lg border border-emerald-200 shrink-0">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                                    </div>
                                    <input type="text" name="payload[whatsapp_contact]" value="{{ $footerConfig['whatsapp_contact'] ?? '' }}" placeholder="+52 55 7510 2015" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                                </div>
                                <span class="text-[10px] text-slate-400 block mt-1">Número telefónico con código internacional para contacto directo con concierge.</span>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Correo Electrónico de Soporte</label>
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-blue-50 text-blue-700 rounded-lg border border-blue-200 shrink-0">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                    </div>
                                    <input type="email" name="payload[support_email]" value="{{ $footerConfig['support_email'] ?? '' }}" placeholder="hola@mitienda.online" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                                </div>
                                <span class="text-[10px] text-slate-400 block mt-1">Email institucional para consultas de facturación y seguimiento.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Grid 2: Badges de Confianza / Pagos y Envíos -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                        <h4 class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#0d9488" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <span>Distintivos de Seguridad &amp; Políticas (Columna 2)</span>
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Badge de Paquetería &amp; Flete</label>
                                <input type="text" name="payload[shipping_badge_text]" value="{{ $footerConfig['shipping_badge_text'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Badge de Empaque Discreto</label>
                                <input type="text" name="payload[packaging_badge_text]" value="{{ $footerConfig['packaging_badge_text'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Badge de Pasarela de Pago</label>
                                <input type="text" name="payload[payments_badge_text]" value="{{ $footerConfig['payments_badge_text'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Grid 3: Switches de Elementos Opcionales -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="flex items-start gap-3 p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-teal-300 transition">
                            <input type="hidden" name="payload[show_revops_link]" value="0">
                            <input type="checkbox" name="payload[show_revops_link]" value="1" {{ !empty($footerConfig['show_revops_link']) ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                            <div>
                                <strong class="text-slate-800 font-semibold block">Mostrar Acceso a Consola RevOps</strong>
                                <span class="text-[11px] text-slate-500 block mt-0.5">Muestra la tarjeta de acceso rápido al Panel de Administración en la cuarta columna del footer.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-teal-300 transition">
                            <input type="hidden" name="payload[show_zero_emojis_badge]" value="0">
                            <input type="checkbox" name="payload[show_zero_emojis_badge]" value="1" {{ !empty($footerConfig['show_zero_emojis_badge']) ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                            <div>
                                <strong class="text-slate-800 font-semibold block">Mostrar Badge Política Cero Emojis</strong>
                                <span class="text-[11px] text-slate-500 block mt-0.5">Renderiza la etiqueta &ldquo;Política Estricta Cero Emojis // Vectorial Only&rdquo; en el sub-footer legal.</span>
                            </div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <a href="{{ url('/?universo=marte') }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1">
                            <span>Previsualizar Tienda Online</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl shadow-xs transition flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Guardar Configuración del Footer</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- FIN SECCIÓN 4 ACORDEÓN -->
    </div>
</div>
<!-- FIN SUBMÓDULO 2: CONTENIDO & REGLAS -->

    <!-- ========================================================================= -->
    <!-- SUBMÓDULO 3: GESTOR DE PÁGINAS DE EXPLORAR & NAVBAR                       -->
    <!-- ========================================================================= -->
    <div id="storePagesPanel" class="tab-panel hidden" style="display: none;">
        <!-- 1. CINTA BANNER SUPERIOR (MARQUESINA) & CONTROL DEL NAVBAR -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm mb-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        <span>Cinta Banner Superior (Marquesina) &amp; Navbar</span>
                    </h3>
                    <p class="text-xs text-slate-500">Configura la marquesina animada que corre por encima del navbar y revisa la adherencia sticky permanente del menú.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Sticky position: sticky; top: 0; z-index: 999 activo">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Navbar Sticky Visible</span>
                    </span>
                </div>
            </div>

            <form method="POST" action="{{ route('portal.store.announcements.update') }}" class="space-y-4">
                @csrf
                <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200 rounded-xl mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-700">Estado de la Cinta Superior:</span>
                        <label class="inline-flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" name="announcement_active" value="1" {{ ($announcementActive ?? true) ? 'checked' : '' }} class="rounded text-indigo-600">
                            <span class="font-semibold">Activar Marquesina en Storefront</span>
                        </label>
                    </div>
                    <span class="text-[11px] text-slate-500 font-mono">Scroll infinito continuo (2x loop)</span>
                </div>

                <div class="space-y-2.5" id="announcementsListContainer">
                    <label class="block font-semibold text-slate-700 text-xs">Mensajes de Anuncios Activos</label>
                    @php
                        $announcementsItems = $announcements ?? [];
                        if (empty($announcementsItems)) {
                            $announcementsItems = [
                                ['id' => 1, 'text' => 'ENVÍOS EXPRESS A TODO MÉXICO', 'link' => '', 'active' => true],
                                ['id' => 2, 'text' => 'PAGOS 100% SEGUROS CON TARJETA & OXXO', 'link' => '', 'active' => true],
                                ['id' => 3, 'text' => 'STOCK FÍSICO CON ENTREGA INMEDIATA', 'link' => '', 'active' => true],
                                ['id' => 4, 'text' => 'ENVÍO GRATIS EN COMPRAS MAYORES A $999', 'link' => '', 'active' => true],
                            ];
                        }
                    @endphp

                    @foreach($announcementsItems as $idx => $an)
                        <div class="flex items-center gap-2 p-2 bg-slate-50/80 border border-slate-200/80 rounded-lg text-xs">
                            <span class="w-5 text-slate-400 font-bold font-mono text-[11px] text-center">#{{ $idx + 1 }}</span>
                            <div class="flex-1">
                                <input type="text" name="announcements[{{ $idx }}][text]" value="{{ $an['text'] ?? '' }}" placeholder="Texto del aviso (Ej: ENVÍO GRATIS EN COMPRAS +$999)" class="w-full px-2.5 py-1.5 border border-slate-200 rounded text-xs bg-white" required>
                            </div>
                            <div class="w-1/3">
                                <input type="text" name="announcements[{{ $idx }}][link]" value="{{ $an['link'] ?? '' }}" placeholder="URL enlace (Opcional, ej: /ofertas)" class="w-full px-2.5 py-1.5 border border-slate-200 rounded text-xs bg-white">
                            </div>
                            <label class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-600 px-2 cursor-pointer">
                                <input type="checkbox" name="announcements[{{ $idx }}][active]" value="1" {{ ($an['active'] ?? true) ? 'checked' : '' }} class="rounded text-indigo-600">
                                <span>Activo</span>
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-[11px] text-slate-400">Los cambios se reflejan inmediatamente en la cinta superior del storefront en Desktop y Móvil.</span>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs cursor-pointer shadow-xs transition">
                        Guardar Cinta Banner
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. PÁGINAS INSTITUCIONALES (DESKTOP & MÓVIL) -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm mb-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <span>Páginas de Explorar</span>
                    </h3>
                    <p class="text-xs text-slate-500">Crea y edita tarjetas interactivas de la vista Explorar (con imágenes y filtros de catálogo directos), y sincroniza enlaces en el Navbar y Footer.</p>
                </div>
                    <div class="flex items-center gap-2">
                        <span id="pageReorderSyncStatus" class="hidden text-[11px] font-semibold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-200"></span>
                        <button class="button button-quiet py-1 px-3 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg cursor-pointer flex items-center gap-1.5" data-action="open-modal" data-modal="createPageModal" type="button">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Nueva Página</span>
                        </button>
                    </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                            <th class="py-2.5 px-3 text-center w-12" title="Arrastra para reordenar en vivo">Orden</th>
                            <th class="py-2.5 px-3">Tarjeta de Explorar</th>
                            <th class="py-2.5 px-3 text-center">Filtros / Catálogo</th>
                            <th class="py-2.5 px-3 text-center">Estado</th>
                            <th class="py-2.5 px-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="pagesSortableBody" class="divide-y divide-slate-100">
                        @forelse($pages as $pg)
                            <tr class="hover:bg-slate-50/60 transition group cursor-grab active:cursor-grabbing" data-page-id="{{ $pg->id }}">
                                <td class="py-3 px-3 text-center drag-handle-page">
                                    <div class="inline-flex items-center gap-1 text-slate-400 group-hover:text-indigo-600 font-mono text-xs">
                                        <svg class="w-4 h-4 cursor-grab" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                        <span class="page-order-badge font-bold">#{{ $loop->iteration }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-3">
                                        @php($imgCover = !empty($pg->image_url) ? $pg->image_url : null)
                                        <div class="w-10 h-12 rounded-lg overflow-hidden bg-slate-900 border border-slate-200 shrink-0 relative flex items-center justify-center">
                                            @if($imgCover)
                                                <img src="{{ $imgCover }}" alt="{{ $pg->title }}" class="w-full h-full object-cover">
                                            @else
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                            @endif
                                            <div class="absolute inset-0 pointer-events-none" style="background: {!! $pg->getOverlayGradientCss() !!}; opacity: 0.6;"></div>
                                        </div>
                                        <div>
                                            <strong class="font-bold text-slate-900 block text-xs">{{ $pg->title }}</strong>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="text-[11px] font-mono text-slate-500">/pagina/{{ $pg->slug }}</span>
                                                <button type="button" onclick="window.copyStoreRoute('/pagina/{{ $pg->slug }}')" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-semibold cursor-pointer underline flex items-center gap-0.5" title="Copiar ruta">
                                                    <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg> Copiar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if(!empty($pg->filter_universe) || !empty($pg->filter_category))
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-violet-50 text-violet-700 border border-violet-200">
                                            {{ strtoupper($pg->filter_universe ?? 'TODOS') }} {{ !empty($pg->filter_category) ? '// ' . strtoupper($pg->filter_category) : '' }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">EDITORIAL</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($pg->status === 'active')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">ACTIVO</span>
                                    @elseif($pg->status === 'archived')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">ARCHIVADO</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">{{ strtoupper($pg->status) }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ url('/pagina/' . $pg->slug) }}" target="_blank" class="px-2 py-1 text-slate-600 hover:text-slate-900 text-[11px] font-semibold border border-slate-200 rounded">Ver</a>
                                        
                                        <!-- Botón Editar (Modal depth=2) -->
                                        <button type="button" class="px-2 py-1 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded text-[11px] font-semibold cursor-pointer border border-amber-200" onclick='window.openEditPageModal(@json($pg))'>
                                            Editar
                                        </button>

                                        <!-- Botón Pausar / Activar -->
                                        <form method="POST" action="{{ route('portal.store.pages.toggle-status', $pg->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 text-[11px] font-semibold rounded border cursor-pointer {{ $pg->status === 'active' ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}">
                                                {{ $pg->status === 'active' ? 'Pausar' : 'Activar' }}
                                            </button>
                                        </form>

                                        <!-- Botón Archivar -->
                                        @if($pg->status !== 'archived')
                                            <form method="POST" action="{{ route('portal.store.pages.archive', $pg->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 text-[11px] font-semibold rounded bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 cursor-pointer" onclick="return confirm('¿Archivar esta página CMS?')">
                                                    Archivar
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Botón Eliminar -->
                                        <form method="POST" action="{{ route('portal.store.pages.destroy', $pg->id) }}" onsubmit="return confirm('¿Eliminar esta página CMS definitivamente?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 text-rose-600 hover:text-rose-800 text-[11px] font-semibold cursor-pointer border border-rose-200 rounded bg-rose-50 hover:bg-rose-100">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400 text-xs">No hay páginas registradas aún.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- FIN SUBMÓDULO 4: GESTOR DE PÁGINAS CMS & NAVBAR -->

    <!-- ========================================================================= -->
    <!-- SUBMÓDULO 5: TRACKING & ANALÍTICA (TASK-H2-11)                            -->
    <!-- ========================================================================= -->
    <div id="storeTrackingPanel" class="tab-panel hidden" style="display: none;">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm mb-6 max-w-3xl">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        <span>Suite de Medición: GTM, GA4, Meta Pixel &amp; CAPI</span>
                    </h3>
                    <p class="text-xs text-slate-500">Inyección automática de tags analíticos y tokens server-side en el storefront y checkout.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ ($tracking && $tracking->is_active) ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                    {{ ($tracking && $tracking->is_active) ? 'Rastreo Activo' : 'Rastreo Desactivado' }}
                </span>
            </div>

            <form method="POST" action="{{ route('portal.store.tracking.update') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Google Tag Manager (GTM ID)</label>
                        <input type="text" name="gtm_id" value="{{ old('gtm_id', $tracking->gtm_id ?? '') }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-mono" placeholder="GTM-XXXXXXX">
                        <small class="text-[10px] text-slate-400">Inyecta script en el head y noscript en el body.</small>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Google Analytics 4 (Measurement ID)</label>
                        <input type="text" name="ga4_measurement_id" value="{{ old('ga4_measurement_id', $tracking->ga4_measurement_id ?? '') }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-mono" placeholder="G-XXXXXXXXXX">
                        <small class="text-[10px] text-slate-400">Medición de eventos de compra y tráfico multiverso.</small>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Facebook / Meta Pixel ID</label>
                        <input type="text" name="facebook_pixel_id" value="{{ old('facebook_pixel_id', $tracking->facebook_pixel_id ?? '') }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-mono" placeholder="123456789012345">
                        <small class="text-[10px] text-slate-400">Pixel client-side para audiencias de retargeting.</small>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Test Event Code (Meta Graph API)</label>
                        <input type="text" name="facebook_test_event_code" value="{{ old('facebook_test_event_code', $tracking->facebook_test_event_code ?? '') }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-mono" placeholder="TEST12345">
                        <small class="text-[10px] text-slate-400">Código de pruebas del Administrador de Eventos.</small>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Meta Conversions API (CAPI Access Token)</label>
                    <textarea style="resize: vertical !important;" name="facebook_capi_token" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-mono" placeholder="EAA... Token de acceso de larga duración">{{ old('facebook_capi_token', $tracking->facebook_capi_token ?? '') }}</textarea>
                    <small class="text-[10px] text-slate-400">Utilizado por el backend en CheckoutController para registrar Purchase sin bloqueadores de anuncios.</small>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" id="trackingIsActive" value="1" {{ ($tracking && $tracking->is_active) ? 'checked' : '' }} class="rounded text-indigo-600">
                    <label for="trackingIsActive" class="font-bold text-slate-800">Activar inyección de etiquetas de seguimiento en Storefront</label>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg cursor-pointer">
                        Guardar Configuración de Tracking
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- FIN SUBMÓDULO 5: TRACKING & ANALÍTICA -->

    <!-- ========================================================================= -->
    <!-- SUBMÓDULO 6: FILTROS DE CATÁLOGO (TASK-H2-09)                             -->
    <!-- ========================================================================= -->
    <div id="storeFiltersPanel" class="tab-panel hidden" style="display: none;">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm mb-6 max-w-4xl">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        <span>Editor de Filtros y Facetas del Catálogo</span>
                    </h3>
                    <p class="text-xs text-slate-500">Configura la visibilidad, nombres y orden jerárquico de los filtros de la tienda PWA.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('portal.store.filters.update') }}" class="space-y-4 text-xs">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                                <th class="py-2.5 px-2 text-center w-8" title="Arrastrar para reordenar">Reordenar</th>
                                <th class="py-2.5 px-3">Clave de Filtro</th>
                                <th class="py-2.5 px-3">Etiqueta Pública</th>
                                <th class="py-2.5 px-3 text-center">Tipo de Control</th>
                                <th class="py-2.5 px-3 text-center">Orden</th>
                                <th class="py-2.5 px-3 text-center">Visible</th>
                            </tr>
                        </thead>
                        <tbody id="catalogFiltersSortableBody" class="divide-y divide-slate-100">
                            @forelse($catalogFilters as $flt)
                                <tr class="catalog-filter-row transition-colors hover:bg-slate-50/80 cursor-default" draggable="true">
                                    <td class="py-2.5 px-2 text-center cursor-grab active:cursor-grabbing text-slate-400 hover:text-indigo-600 select-none drag-handle" title="Arrastra para reordenar visualmente">
                                        <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                    </td>
                                    <td class="py-2.5 px-3 font-mono font-bold text-slate-800">
                                        {{ $flt->filter_key }}
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input type="text" name="filters[{{ $flt->id }}][label]" value="{{ $flt->label }}" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">{{ strtoupper($flt->display_type) }}</span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <input type="number" name="filters[{{ $flt->id }}][sort_order]" value="{{ $flt->sort_order }}" class="filter-sort-input w-16 px-2 py-1 border border-slate-200 rounded-lg text-center text-xs">
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <input type="checkbox" name="filters[{{ $flt->id }}][is_visible]" value="1" {{ $flt->is_visible ? 'checked' : '' }} class="rounded text-indigo-600">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-slate-400">No hay filtros configurados en la base de datos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg cursor-pointer">
                        Guardar Filtros del Catálogo
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- FIN SUBMÓDULO 8 -->

    <!-- ========================================================================= -->
    <!-- MODALES DE CONTROL OPERATIVO CMS                                          -->
    <!-- ========================================================================= -->

    <!-- 1. MODAL CREAR SECCIÓN MODULAR BIFURCADO -->
    <!-- 1. MODAL CREAR SECCIÓN MODULAR BIFURCADO -->
    <div id="createSectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-0 bg-slate-900/60 backdrop-blur-xs hidden" style="display: none;">
        <div class="bg-white w-full h-full flex flex-col md:flex-row shadow-2xl overflow-hidden relative">
            
            <!-- Botón de cerrar general (Solo visible en Desktop, en mobile va en el header) -->
            <button type="button" class="hidden md:flex absolute top-4 right-4 z-50 w-10 h-10 items-center justify-center bg-white border border-slate-200 rounded-full text-slate-500 hover:text-slate-900 hover:bg-slate-100 shadow-md transition font-bold text-2xl cursor-pointer" data-action="close-modal" data-modal="createSectionModal">&times;</button>

            <!-- COLUMNA IZQUIERDA: CONFIGURACIONES (1/3) -->
            <div class="w-full md:w-[420px] shrink-0 p-4 md:p-6 overflow-y-auto border-b md:border-b-0 md:border-r border-slate-200 bg-white flex flex-col relative z-20 custom-scrollbar">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4 shrink-0">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Crear Nueva Sección Modular</span>
                    </h3>
                    <button type="button" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer md:hidden" data-action="close-modal" data-modal="createSectionModal">&times;</button>
                </div>



                <form method="POST" action="{{ route('portal.store.sections.store') }}" id="createSectionForm" enctype="multipart/form-data" class="space-y-4 text-xs flex-1 flex flex-col">
                    @csrf
                    <input type="hidden" name="status" value="active">
                    
                    <div class="flex-1 space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Nombre Interno (Gestión CMS)</label>
                                <input type="text" name="name" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Ej: Colección Verano Jocks">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Vitrina Destino</label>
                                <select name="universe" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold">
                                    <option value="all">Todas las vitrinas (Global / Multiverso)</option>
                                    <option value="marte">Marte (Masculino)</option>
                                    <option value="venus">Venus (Femenino)</option>
                                    <option value="tiresias">Tiresias (Afirmativo)</option>
                                    <option value="terra">Terra (Unisex / Matriz)</option>
                                </select>
                            </div>
                        </div>



                        <div id="createTypeWrap" class="mb-4">
                            <label class="block font-semibold text-slate-700 mb-1">Tipo de Sección de Contenido</label>
                            <select name="type" id="createSectionType" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="hero">Hero Principal de Vitrina</option>
                                <option value="carousel">Carrusel de Banners Promocionales</option>
                                <option value="pop_up">Modal Promocional / Pop-up Flotante</option>
                                <option value="buttons">Botones CTA / Bloque Personalizado</option>
                            </select>
                        </div>

                        <!-- CAMPOS COMUNES -->
                        <div id="createCommonFieldsWrap" class="space-y-4 mb-4">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">Título Público (H2)</label>
                                    <input type="text" name="title" id="inpCreateTitle" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Ej: BOXERS & JOCKS DEPORTIVOS">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">Badge / Kicker Superior</label>
                                    <input type="text" name="badge" id="inpCreateBadge" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Ej: LANZAMIENTO EXCLUSIVO">
                                </div>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Bajada / Subtítulo</label>
                                <input type="text" name="subtitle" id="inpCreateSubtitle" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Ej: Confeccionados con modal pima de secado ultra rápido.">
                            </div>
                        </div>

                        <!-- 1. MODO HERO -->
                        <div id="createHeroWrap" class="space-y-4">
                            <!-- BLOQUE MOTOR DE HERO DINÁMICO -->
                            <div id="createHeroEngineWrap" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-4">
                                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                    <span class="font-bold text-slate-800 text-xs flex items-center gap-1.5 uppercase tracking-wider">
                                        Motor de Hero Dinámico
                                    </span>
                                </div>

                                <!-- 1. BIFURCADOR DE MODO -->
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Modo de Renderizado</label>
                                    <div class="grid grid-cols-2 gap-2 p-1 bg-slate-200/70 rounded-xl text-xs font-semibold">
                                        <label id="createHeroModeStructuredLbl" class="flex items-center justify-center py-2 px-3 rounded-lg cursor-pointer transition bg-white text-indigo-700 shadow-xs text-center">
                                            <input type="radio" name="settings[hero_mode]" value="structured" checked class="hidden" onchange="window.toggleHeroModeCreate(this.value)">
                                            <span>Visual Estructurado</span>
                                        </label>
                                        <label id="createHeroModeCustomLbl" class="flex items-center justify-center py-2 px-3 rounded-lg cursor-pointer transition text-slate-600 hover:text-slate-900 text-center">
                                            <input type="radio" name="settings[hero_mode]" value="custom" class="hidden" onchange="window.toggleHeroModeCreate(this.value)">
                                            <span>Código Custom</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- 2. ANCHO DE PANTALLA -->
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Ancho en Pantalla</label>
                                    <input type="hidden" name="settings[full_width]" value="0">
                                    <label class="flex items-center gap-2.5 cursor-pointer p-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                                        <input type="checkbox" name="settings[full_width]" id="inpCreateFullWidth" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                        <span class="font-bold text-slate-800 block text-xs">Full-Width</span>
                                    </label>
                                </div>

                                <!-- 3. IMAGEN DE FONDO -->
                                <div class="space-y-2 p-3 bg-white border border-slate-200 rounded-xl">
                                    <label class="block font-bold text-slate-800 text-xs">Imagen de Fondo</label>
                                    <div class="grid grid-cols-1 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Subir Archivo Local</label>
                                            <input type="file" name="background_image_file" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" onchange="window.previewHeroBgImage(this, 'createHeroBgPreview')">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">O Ingresar URL Externa</label>
                                            <input type="text" name="settings[background_image]" id="createHeroBgUrl" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" placeholder="https://ejemplo.com/hero-bg.webp" oninput="window.previewHeroBgUrl(this.value, 'createHeroBgPreview')">
                                        </div>
                                    </div>
                                    <div id="createHeroBgPreview" class="hidden mt-2 p-2 border border-dashed border-slate-300 rounded-lg bg-slate-50 flex items-center gap-3">
                                        <img src="" alt="Vista previa fondo" class="w-16 h-10 object-cover rounded shadow-xs bg-slate-200">
                                        <span class="text-[11px] text-slate-600 truncate flex-1 font-mono"></span>
                                        <button type="button" onclick="window.clearHeroBgPreview('createSectionModal', 'createHeroBgPreview', 'createHeroBgUrl')" class="text-rose-600 font-bold text-xs cursor-pointer">Eliminar</button>
                                    </div>
                                </div>

                                <!-- 4. CAPA DE DEGRADADOS OVERLAY -->
                                <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                    <input type="hidden" name="settings[enable_gradient]" value="0">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="settings[enable_gradient]" id="createEnableGradient" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4" onchange="document.getElementById('createGradientControls').style.display = this.checked ? 'block' : 'none'">
                                        <span class="font-bold text-slate-800 text-xs">Habilitar Capa de Degradado Overlay</span>
                                    </label>

                                    <div id="createGradientControls" style="display: none;" class="space-y-3 pt-2 border-t border-slate-100">
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color Inicio</label>
                                                <div class="flex items-center gap-1.5">
                                                    <input type="color" id="createGradColorStartPicker" value="#0f172a" class="w-7 h-7 p-0 border border-slate-200 rounded cursor-pointer" oninput="document.getElementById('createGradColorStart').value = this.value; window.updateHeroGradientPreview('create')">
                                                    <input type="text" name="settings[gradient_color_start]" id="createGradColorStart" value="#0f172a" class="w-full px-2 py-1 text-[11px] font-mono border border-slate-200 rounded" oninput="document.getElementById('createGradColorStartPicker').value = this.value; window.updateHeroGradientPreview('create')">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color Fin</label>
                                                <div class="flex items-center gap-1.5">
                                                    <input type="color" id="createGradColorEndPicker" value="#1e1b4b" class="w-7 h-7 p-0 border border-slate-200 rounded cursor-pointer" oninput="document.getElementById('createGradColorEnd').value = this.value; window.updateHeroGradientPreview('create')">
                                                    <input type="text" name="settings[gradient_color_end]" id="createGradColorEnd" value="#1e1b4b" class="w-full px-2 py-1 text-[11px] font-mono border border-slate-200 rounded" oninput="document.getElementById('createGradColorEndPicker').value = this.value; window.updateHeroGradientPreview('create')">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-600 mb-1">Dirección</label>
                                                <select name="settings[gradient_direction]" id="createGradDir" class="w-full px-2 py-1.5 text-[11px] border border-slate-200 rounded" onchange="window.updateHeroGradientPreview('create')">
                                                    <option value="to bottom">Vertical ↓</option>
                                                    <option value="to right">Horizontal →</option>
                                                    <option value="135deg">Diagonal ↘</option>
                                                    <option value="to top">Invertido ↑</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-600 mb-1">Opacidad: <span id="createGradOpacityVal">80%</span></label>
                                                <input type="range" name="settings[gradient_opacity]" id="createGradOpacity" min="10" max="100" value="80" class="w-full cursor-pointer accent-indigo-600" oninput="document.getElementById('createGradOpacityVal').textContent = this.value + '%'; window.updateHeroGradientPreview('create')">
                                            </div>
                                        </div>
                                        <div id="createGradPreviewBar" class="h-6 rounded-lg border border-slate-200 shadow-inner" style="background: linear-gradient(to bottom, #0f172a, #1e1b4b);"></div>
                                    </div>
                                </div>

                                <!-- 5. ESTILOS DE TEXTO Y TIPOGRAFÍA -->
                                <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                    <label class="block font-bold text-slate-800 text-xs">Estilos de Texto (Hero)</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Título</label>
                                            <input type="color" name="settings[title_color]" id="inpCreateTitleColor" value="#ffffff" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Bajada</label>
                                            <input type="color" name="settings[subtitle_color]" id="inpCreateSubtitleColor" value="#e2e8f0" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Alineación</label>
                                            <select name="settings[text_align]" id="inpCreateTextAlign" class="w-full px-2 py-1.5 text-[11px] border border-slate-200 rounded">
                                                <option value="text-center">Centrado</option>
                                                <option value="text-left">Izquierda</option>
                                                <option value="text-right">Derecha</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Tipografía</label>
                                            <select name="settings[font_family]" id="inpCreateFontFamily" class="w-full px-2 py-1.5 text-[11px] border border-slate-200 rounded">
                                                <option value="font-sans">Sans (Default)</option>
                                                <option value="font-serif">Serif</option>
                                                <option value="font-mono">Monospace</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- 6. ACCIONES Y BOTONES DEL HERO -->
                                <div id="createHeroButtonsWrap" class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                    <label class="block font-bold text-slate-800 text-xs">Botones de Acción (Call to Action)</label>

                                    <!-- Botón Primario -->
                                    <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg space-y-2">
                                        <span class="block font-bold text-indigo-700 text-[11px]">Botón Primario</span>
                                        <div class="grid grid-cols-1 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Texto del Botón</label>
                                                <input type="text" name="settings[cta_primary_text]" id="inpCreateCtaPrimary" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs" placeholder="Ej: EXPLORAR CATÁLOGO" value="EXPLORAR CATÁLOGO">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">URL / Ruta de Destino</label>
                                                <div class="flex flex-col gap-1.5">
                                                    <input type="text" name="settings[cta_primary_url]" id="createCtaPrimaryUrl" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" placeholder="Ej: #section-2 o /catalogo">
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 mt-1">
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Fondo</label>
                                                    <input type="color" name="settings[cta_primary_bg]" id="inpCreateCtaPrimaryBg" value="#0f172a" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Texto</label>
                                                    <input type="color" name="settings[cta_primary_color]" id="inpCreateCtaPrimaryColor" value="#ffffff" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botón Secundario -->
                                    <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg space-y-2">
                                        <span class="block font-bold text-slate-700 text-[11px]">Botón Secundario</span>
                                        <div class="grid grid-cols-1 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Texto del Botón</label>
                                                <input type="text" name="settings[cta_secondary_text]" id="inpCreateCtaSecondary" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs" placeholder="Ej: GUÍA DE TALLAS" value="">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">URL / Ruta de Destino</label>
                                                <div class="flex flex-col gap-1.5">
                                                    <input type="text" name="settings[cta_secondary_url]" id="createCtaSecondaryUrl" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" placeholder="Ej: /pagina/guia-tallas">
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 mt-1">
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Fondo</label>
                                                    <input type="color" name="settings[cta_secondary_bg]" id="inpCreateCtaSecondaryBg" value="#ffffff" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Texto</label>
                                                    <input type="color" name="settings[cta_secondary_color]" id="inpCreateCtaSecondaryColor" value="#0f172a" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. MODO CAROUSEL -->
                        <div id="createCarouselWrap" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-4 hidden">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                <span class="font-bold text-slate-800 text-xs flex items-center gap-1.5 uppercase tracking-wider">Configuración de Carrusel</span>
                            </div>
                            <div class="space-y-2">
                                <label class="block font-bold text-slate-700 mb-1 text-xs">Añadir Banners (Boceto)</label>
                                <div class="p-6 bg-white border-2 border-dashed border-slate-300 rounded-xl text-center text-slate-500 text-xs hover:border-indigo-400 hover:bg-indigo-50 cursor-pointer transition">
                                    <div class="mb-2">
                                        <svg class="mx-auto h-8 w-8 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                    </div>
                                    + Subir Imágenes para Carrusel
                                </div>
                                <div class="flex items-center gap-2 mt-4">
                                    <input type="checkbox" id="carouselAutoPlay" name="settings[autoplay]" checked value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                    <label class="text-xs text-slate-700 font-bold cursor-pointer" for="carouselAutoPlay">Autoplay (Transición automática)</label>
                                </div>
                            </div>
                        </div>

                        <!-- 3. MODO POP UP -->
                        <div id="createPopUpWrap" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-4 hidden">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                <span class="font-bold text-slate-800 text-xs flex items-center gap-1.5 uppercase tracking-wider">Configuración de Pop-up</span>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1 text-xs">Imagen del Modal (Opcional)</label>
                                    <input type="file" name="settings[popup_image]" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1 text-xs">Comportamiento de Cierre</label>
                                    <select name="settings[popup_close_behavior]" class="w-full px-2 py-1.5 text-xs font-semibold border border-slate-200 rounded-lg">
                                        <option value="outside">Permitir cerrar al hacer clic fuera (Overlay)</option>
                                        <option value="button">Requerir acción (Solo Botón Cerrar)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 4. MODO BOTONES / BLOQUE PERSONALIZADO -->
                        <div id="createButtonsWrap" class="space-y-4 hidden">
                            <!-- EDITOR DE CÓDIGO CUSTOM -->
                            <div id="createCustomCodeWrap" class="p-3 bg-slate-900 text-slate-200 border border-slate-800 rounded-xl space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="block font-bold text-emerald-400 text-[11px] uppercase tracking-wider">Bloque Custom / HTML Libre</span>
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1 text-xs">Ingresa tu código HTML / CSS</label>
                                    <textarea name="custom_html" rows="5" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 text-slate-100 font-mono text-[11px] rounded-lg" placeholder="<!-- Tu código HTML aquí -->&#10;<div class='flex gap-2 justify-center'>&#10;  <button class='btn'>Botón 1</button>&#10;</div>"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer del Formulario Flotante en la parte inferior de la columna -->
                    <div class="flex items-center justify-between gap-3 pt-4 pb-2 border-t border-slate-100 mt-auto bg-white sticky bottom-0 z-30">
                        <button type="button" class="px-4 py-2 text-slate-600 font-semibold cursor-pointer" data-action="close-modal" data-modal="createSectionModal">Cancelar</button>
                        <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg cursor-pointer flex items-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Guardar</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- COLUMNA DERECHA: PREVIEW EN VIVO (2/3) -->
            <div class="w-full md:w-2/3 bg-slate-100 p-0 sm:p-6 flex flex-col relative z-10">
                <!-- Toggle de Dispositivo -->
                <div class="absolute top-6 left-6 z-40 flex bg-white rounded-lg shadow-sm p-1 border border-slate-200">
                    <button type="button" class="preview-device-btn active px-3 py-1.5 rounded text-xs font-bold bg-indigo-50 text-indigo-700 cursor-pointer" data-device="desktop">Desktop</button>
                    <button type="button" class="preview-device-btn px-3 py-1.5 rounded text-xs font-bold text-slate-500 hover:text-slate-700 cursor-pointer" data-device="mobile">Mobile</button>
                </div>
                
                <!-- Contenedor del Preview Visual -->
                <div class="flex-1 flex items-center justify-center p-4 overflow-hidden mt-16">
                    <div id="livePreviewContainer" class="w-full h-full bg-white rounded-xl shadow-md border border-slate-200 overflow-y-auto overflow-x-hidden transition-all duration-300 relative" style="max-width: 100%;">
                        <!-- Skeleton Header de la tienda -->
                        <div class="w-full border-b border-slate-100 p-4 flex items-center justify-between bg-white sticky top-0 z-30">
                            <div class="w-24 h-6 bg-slate-200 rounded"></div>
                            <div class="hidden md:flex gap-4">
                                <div class="w-12 h-2 bg-slate-200 rounded"></div>
                                <div class="w-12 h-2 bg-slate-200 rounded"></div>
                                <div class="w-12 h-2 bg-slate-200 rounded"></div>
                            </div>
                            <div class="w-8 h-8 bg-slate-200 rounded-full md:hidden"></div>
                        </div>
                        
                        <!-- Contenido Renderizado en Vivo -->
                        <div id="livePreviewContent" class="w-full min-h-[250px] flex flex-col">
                            
                            <!-- Hero / Sección Preview -->
                            <div id="previewHeroArea" class="w-full p-8 md:p-12 flex flex-col items-center justify-center text-center relative bg-slate-50 min-h-[150px] overflow-hidden transition-all duration-300">
                                
                                <div id="previewOverlay" class="absolute inset-0 z-0 hidden"></div>

                                <div class="relative z-10 max-w-2xl mx-auto space-y-4 w-full">
                                    <span id="previewBadge" class="inline-block px-3 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-bold rounded-full uppercase tracking-widest hidden"></span>
                                    
                                    <h2 id="previewTitle" class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Tu Título Aquí</h2>
                                    
                                    <p id="previewSubtitle" class="text-slate-600 md:text-lg max-w-xl mx-auto hidden">Tu bajada o subtítulo aquí.</p>
                                    
                                    <div id="previewActions" class="flex flex-wrap justify-center gap-3 pt-4 hidden">
                                        <button id="previewBtnPrimary" class="px-6 py-2.5 bg-slate-900 text-white rounded-lg font-bold text-sm shadow-sm hidden transition-colors"></button>
                                        <button id="previewBtnSecondary" class="px-6 py-2.5 bg-white text-slate-900 border border-slate-200 rounded-lg font-bold text-sm hidden transition-colors"></button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Grid de Catálogo Preview -->
                            <div id="previewCatalogArea" class="w-full p-6 bg-white hidden">
                                <div id="previewGrid" class="grid gap-4 w-full">
                                    <!-- Items generados por JS -->
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const createSectionType = document.getElementById('createSectionType');
                    const createHeroWrap = document.getElementById('createHeroWrap');
                    const createCarouselWrap = document.getElementById('createCarouselWrap');
                    const createPopUpWrap = document.getElementById('createPopUpWrap');
                    const createButtonsWrap = document.getElementById('createButtonsWrap');

                    function switchSectionType() {
                        if (!createSectionType) return;
                        const val = createSectionType.value;
                        if(createHeroWrap) createHeroWrap.style.display = val === 'hero' ? 'block' : 'none';
                        if(createCarouselWrap) createCarouselWrap.style.display = val === 'carousel' ? 'block' : 'none';
                        if(createPopUpWrap) createPopUpWrap.style.display = val === 'pop_up' ? 'block' : 'none';
                        if(createButtonsWrap) createButtonsWrap.style.display = val === 'buttons' ? 'block' : 'none';
                    }

                    if (createSectionType) {
                        createSectionType.addEventListener('change', () => {
                            switchSectionType();
                            if(typeof updatePreview === 'function') updatePreview();
                        });
                        switchSectionType(); // Init state
                    }

                    const titleInput = document.getElementById('inpCreateTitle');
                    const subtitleInput = document.getElementById('inpCreateSubtitle');
                    const badgeInput = document.getElementById('inpCreateBadge');
                    
                    const previewTitle = document.getElementById('previewTitle');
                    const previewSubtitle = document.getElementById('previewSubtitle');
                    const previewBadge = document.getElementById('previewBadge');
                    
                    const previewHeroArea = document.getElementById('previewHeroArea');
                    
                    const ctaPrimaryInput = document.getElementById('inpCreateCtaPrimary');
                    const ctaSecondaryInput = document.getElementById('inpCreateCtaSecondary');
                    
                    const previewBtnPrimary = document.getElementById('previewBtnPrimary');
                    const previewBtnSecondary = document.getElementById('previewBtnSecondary');
                    const previewActions = document.getElementById('previewActions');

                    const inpCreateTitleColor = document.getElementById('inpCreateTitleColor');
                    const inpCreateSubtitleColor = document.getElementById('inpCreateSubtitleColor');
                    const inpCreateTextAlign = document.getElementById('inpCreateTextAlign');
                    const inpCreateFontFamily = document.getElementById('inpCreateFontFamily');
                    const inpCreateCtaPrimaryBg = document.getElementById('inpCreateCtaPrimaryBg');
                    const inpCreateCtaPrimaryColor = document.getElementById('inpCreateCtaPrimaryColor');
                    const inpCreateCtaSecondaryBg = document.getElementById('inpCreateCtaSecondaryBg');
                    const inpCreateCtaSecondaryColor = document.getElementById('inpCreateCtaSecondaryColor');

                    function updatePreview() {
                        // Textos
                        if (titleInput && previewTitle) {
                            previewTitle.textContent = titleInput.value || 'Tu Hero Principal';
                            if (inpCreateTitleColor) {
                                previewTitle.style.color = inpCreateTitleColor.value;
                            } else {
                                previewTitle.style.color = '';
                            }
                        }
                        if (subtitleInput && previewSubtitle) {
                            previewSubtitle.textContent = subtitleInput.value;
                            previewSubtitle.style.display = subtitleInput.value ? 'block' : 'none';
                            if (inpCreateSubtitleColor) {
                                previewSubtitle.style.color = inpCreateSubtitleColor.value;
                            } else {
                                previewSubtitle.style.color = '';
                            }
                        }
                        if (badgeInput && previewBadge) {
                            previewBadge.textContent = badgeInput.value;
                            previewBadge.style.display = badgeInput.value ? 'inline-block' : 'none';
                        }
                        
                        // Alineación y tipografía del contenedor de texto
                        if (previewHeroArea) {
                            previewHeroArea.classList.remove('text-center', 'text-left', 'text-right', 'font-sans', 'font-serif', 'font-mono');
                            if (inpCreateTextAlign) previewHeroArea.classList.add(inpCreateTextAlign.value);
                            if (inpCreateFontFamily) previewHeroArea.classList.add(inpCreateFontFamily.value);
                        }

                        // Botones
                        let hasBtn = false;
                        if (ctaPrimaryInput && previewBtnPrimary) {
                            previewBtnPrimary.textContent = ctaPrimaryInput.value;
                            previewBtnPrimary.style.display = ctaPrimaryInput.value ? 'inline-block' : 'none';
                            if(ctaPrimaryInput.value) {
                                hasBtn = true;
                                if(inpCreateCtaPrimaryBg) previewBtnPrimary.style.backgroundColor = inpCreateCtaPrimaryBg.value;
                                if(inpCreateCtaPrimaryColor) previewBtnPrimary.style.color = inpCreateCtaPrimaryColor.value;
                                previewBtnPrimary.style.borderColor = inpCreateCtaPrimaryBg ? inpCreateCtaPrimaryBg.value : '';
                            }
                        } else {
                            if(previewBtnPrimary) previewBtnPrimary.style.display = 'none';
                        }

                        if (ctaSecondaryInput && previewBtnSecondary) {
                            previewBtnSecondary.textContent = ctaSecondaryInput.value;
                            previewBtnSecondary.style.display = ctaSecondaryInput.value ? 'inline-block' : 'none';
                            if(ctaSecondaryInput.value) {
                                hasBtn = true;
                                if(inpCreateCtaSecondaryBg) previewBtnSecondary.style.backgroundColor = inpCreateCtaSecondaryBg.value;
                                if(inpCreateCtaSecondaryColor) previewBtnSecondary.style.color = inpCreateCtaSecondaryColor.value;
                                if(inpCreateCtaSecondaryColor) previewBtnSecondary.style.borderColor = inpCreateCtaSecondaryColor.value; // Assuming border is text color
                            }
                        } else {
                            if(previewBtnSecondary) previewBtnSecondary.style.display = 'none';
                        }
                        
                        if (previewActions) {
                            previewActions.style.display = hasBtn ? 'flex' : 'none';
                        }

                        // Estilo Hero Completo
                        if (previewHeroArea) {
                            previewHeroArea.classList.remove('bg-white', 'min-h-[150px]', 'border-b', 'border-slate-100', '!p-6', 'text-slate-900');
                            previewHeroArea.classList.add('bg-slate-900', 'min-h-[250px]', 'p-8');
                            
                            if (window._currentHeroPreviewBg) {
                                previewHeroArea.style.backgroundImage = window._currentHeroPreviewBg;
                                previewHeroArea.classList.add('bg-cover', 'bg-center');
                            } else {
                                previewHeroArea.style.backgroundImage = 'none';
                            }
                        }

                        if (previewTitle) previewTitle.className = 'text-3xl md:text-4xl font-extrabold tracking-tight drop-shadow-md relative z-10';
                        if (previewSubtitle) previewSubtitle.className = 'md:text-base max-w-xl mx-auto mt-4 drop-shadow-sm block relative z-10';
                    }

                    // Handlers para la imagen de fondo
                    const bgFileInput = document.querySelector('#createSectionModal input[name="background_image_file"]');
                    const bgUrlInput = document.getElementById('createHeroBgUrl');

                    if (bgFileInput) {
                        bgFileInput.addEventListener('change', function(e) {
                            if (this.files && this.files[0]) {
                                const reader = new FileReader();
                                reader.onload = function(evt) {
                                    window._currentHeroPreviewBg = `url('${evt.target.result}')`;
                                    updatePreview();
                                }
                                reader.readAsDataURL(this.files[0]);
                            } else {
                                window._currentHeroPreviewBg = '';
                                updatePreview();
                            }
                        });
                    }

                    if (bgUrlInput) {
                        bgUrlInput.addEventListener('input', function(e) {
                            if (this.value) {
                                window._currentHeroPreviewBg = `url('${this.value}')`;
                            } else {
                                window._currentHeroPreviewBg = '';
                            }
                            updatePreview();
                        });
                    }

                    // Attach event listeners para el resto de inputs
                    const inputs = [
                        titleInput, subtitleInput, badgeInput, ctaPrimaryInput, ctaSecondaryInput,
                        inpCreateTitleColor, inpCreateSubtitleColor, inpCreateTextAlign, inpCreateFontFamily,
                        inpCreateCtaPrimaryBg, inpCreateCtaPrimaryColor, inpCreateCtaSecondaryBg, inpCreateCtaSecondaryColor
                    ];
                    inputs.forEach(el => {
                        if (el) el.addEventListener('input', updatePreview);
                        if (el && el.tagName === 'SELECT') el.addEventListener('change', updatePreview);
                    });

                    // Device toggles
                    document.querySelectorAll('.preview-device-btn').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            document.querySelectorAll('.preview-device-btn').forEach(b => {
                                b.classList.remove('bg-indigo-50', 'text-indigo-700', 'active');
                                b.classList.add('text-slate-500');
                            });
                            e.target.classList.add('bg-indigo-50', 'text-indigo-700', 'active');
                            e.target.classList.remove('text-slate-500');
                            
                            const container = document.getElementById('livePreviewContainer');
                            if (e.target.dataset.device === 'mobile') {
                                container.style.maxWidth = '375px';
                                container.classList.add('mx-auto');
                            } else {
                                container.style.maxWidth = '100%';
                                container.classList.remove('mx-auto');
                            }
                        });
                    });

                    // Overlay / Degradado interactivo
                    const gradEnableInput = document.getElementById('createEnableGradient');
                    const gradStartInput = document.getElementById('createGradColorStartPicker');
                    const gradEndInput = document.getElementById('createGradColorEndPicker');
                    const gradDirInput = document.getElementById('createGradDir');
                    const gradOpacityInput = document.getElementById('createGradOpacity');
                    const previewOverlay = document.getElementById('previewOverlay');

                    function updateOverlay() {
                        if (gradEnableInput && gradEnableInput.checked && previewOverlay) {
                            previewOverlay.style.display = 'block';
                            previewOverlay.className = "absolute inset-0 z-0";
                            
                            let dir = gradDirInput ? gradDirInput.value : 'to bottom';
                            let start = gradStartInput ? gradStartInput.value : '#0f172a';
                            let end = gradEndInput ? gradEndInput.value : '#1e1b4b';
                            let opacity = gradOpacityInput ? gradOpacityInput.value / 100 : 0.8;
                            
                            previewOverlay.style.background = `linear-gradient(${dir}, ${start}, ${end})`;
                            previewOverlay.style.opacity = opacity;
                        } else {
                            if (previewOverlay) previewOverlay.style.display = 'none';
                        }
                    }

                    const gradInputs = [gradEnableInput, gradStartInput, gradEndInput, gradDirInput, gradOpacityInput];
                    gradInputs.forEach(el => {
                        if (el) el.addEventListener('input', updateOverlay);
                        if (el) el.addEventListener('change', updateOverlay);
                    });

                    const origUpdatePreview = updatePreview;
                    updatePreview = function() {
                        origUpdatePreview();
                        updateOverlay();
                    };

                    // Trigger initial paint
                    setTimeout(updatePreview, 100);
                });
            </script>
        </div>
    </div>

    <!-- 1B. MODAL EDITAR SECCIÓN EXISTENTE -->
    <div id="editSectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden" style="display: none;">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Editar Sección Modular</span>
                </h3>
                <button type="button" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer" data-action="close-modal" data-modal="editSectionModal">&times;</button>
            </div>

            <form method="POST" id="editSectionForm" action="" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Vitrina Destino</label>
                        <select name="universe" id="editUniverseSelect" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold">
                            <option value="all">Todas las vitrinas (Global)</option>
                            <option value="marte">Marte (Masculino)</option>
                            <option value="venus">Venus (Femenino)</option>
                            <option value="tiresias">Tiresias (Afirmativo)</option>
                            <option value="terra">Terra (Unisex / Matriz)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Estado de la Sección</label>
                        <select name="status" id="editStatusSelect" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold">
                            <option value="active">Activa (Visible en tienda)</option>
                            <option value="draft">Borrador (Oculta)</option>
                            <option value="archived">Archivada (Desactivada)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tipo de Sección</label>
                        <select name="type" id="editTypeSelect" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold">
                            <option value="curated_products">Prendas Destacadas / Colección</option>
                            <option value="categories_grid">Cuadrícula de Categorías</option>
                            <option value="family_showcase">Vitrina de Familias Textiles</option>
                            <option value="tags_cloud">Nube de Tags / Estilos</option>
                            <option value="subcategories_slider">Slider de Subcategorías</option>
                            <option value="hero_carousel">Hero Principal de Vitrina</option>
                            <option value="editorial_banner">Banner Editorial / Promoción</option>
                            <option value="brand_pillars">Pilares de Marca (Garantías)</option>
                            <option value="promo_modal">Modal Promocional / Pop-up</option>
                            <option value="custom_html_css_js">Bloque Custom (HTML/CSS/JS)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nombre Interno</label>
                        <input type="text" name="name" id="editNameInput" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Badge Superior</label>
                        <input type="text" name="badge" id="editBadgeInput" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Título Público (H2)</label>
                        <input type="text" name="title" id="editTitleInput" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Bajada / Subtítulo</label>
                        <input type="text" name="subtitle" id="editSubtitleInput" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                    </div>
                </div>

                <!-- TAXONOMÍAS HUB 4 PARA EDICIÓN -->
                <div id="editCatalogTaxonomies" class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                    <span class="block font-bold text-slate-800 text-[11px] uppercase tracking-wider">Reglas de Catálogo (Hub 4)</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Categoría Principal</label>
                            <select name="query_rules[category_id]" id="editCategoryId" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                                <option value="">Todas las categorías</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Subcategoría</label>
                            <select name="query_rules[subcategory_id]" id="editSubcategoryId" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                                <option value="">Todas las subcategorías</option>
                                @foreach($subcategories as $scat)
                                    <option value="{{ $scat->id }}">{{ $scat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Proveedor</label>
                            <select name="query_rules[supplier]" id="editSupplier" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                                <option value="">Todos los proveedores</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->trade_name ?? $sup->name }}">{{ $sup->trade_name ?? $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Marca</label>
                            <select name="query_rules[brand]" id="editBrand" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                                <option value="">Todas las marcas</option>
                                @foreach($brands as $br)
                                    <option value="{{ $br }}">{{ $br }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Familia Textil</label>
                            <select name="query_rules[family]" id="editFamily" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                                <option value="">Cualquier fibra</option>
                                @foreach($textileFamilies as $tf)
                                    <option value="{{ $tf }}">{{ $tf }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Tipo de Prenda</label>
                            <select name="query_rules[garment_type]" id="editGarmentType" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                                <option value="">Cualquier tipo</option>
                                @foreach($garmentTypes as $gt)
                                    <option value="{{ $gt }}">{{ $gt }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Tag de Estilo</label>
                            <select name="query_rules[tag]" id="editTag" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                                <option value="">Cualquier estilo</option>
                                @foreach($styleTags as $st)
                                    <option value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Filtro de Descuento</label>
                            <select name="query_rules[discount]" id="editDiscount" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                                @foreach($discountOptions as $dVal => $dLabel)
                                    <option value="{{ $dVal }}">{{ $dLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Límite de Prendas</label>
                            <select name="query_rules[limit]" id="editLimit" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                                <option value="4">4 Prendas</option>
                                <option value="8">8 Prendas</option>
                                <option value="12">12 Prendas</option>
                                <option value="16">16 Prendas</option>
                                <option value="24">24 Prendas</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- CÓDIGO CUSTOM Y MOTOR DE HERO PARA EDICIÓN -->
                <div id="editContentOptions" class="space-y-4 hidden" style="display: none;">
                    
                    <!-- BLOQUE MOTOR DE HERO DINÁMICO (EDICIÓN) -->
                    <div id="editHeroEngineWrap" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <span class="font-bold text-slate-800 text-xs flex items-center gap-1.5 uppercase tracking-wider">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Motor de Hero Dinámico &amp; Vitrina
                            </span>
                            <span class="text-[10px] text-slate-500 font-medium">Bifurcación Visual / Código &amp; Sangrado</span>
                        </div>

                        <!-- 1. BIFURCADOR DE MODO: ESTRUCTURADO VS CÓDIGO CUSTOM -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Modo de Renderizado del Hero</label>
                            <div class="grid grid-cols-2 gap-2 p-1 bg-slate-200/70 rounded-xl text-xs font-semibold">
                                <label id="editHeroModeStructuredLbl" class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg cursor-pointer transition bg-white text-indigo-700 shadow-xs text-center">
                                    <input type="radio" name="settings[hero_mode]" id="editHeroModeStructured" value="structured" checked class="hidden" onchange="window.toggleHeroModeEdit(this.value)">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                    <span>Visual Estructurado</span>
                                </label>
                                <label id="editHeroModeCustomLbl" class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg cursor-pointer transition text-slate-600 hover:text-slate-900 text-center">
                                    <input type="radio" name="settings[hero_mode]" id="editHeroModeCustom" value="custom" class="hidden" onchange="window.toggleHeroModeEdit(this.value)">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                    <span>Código Custom</span>
                                </label>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">Conmuta de forma mutuamente excluyente qué contenido se renderiza en la tienda para evitar duplicados.</p>
                        </div>

                        <!-- 2. ANCHO DE PANTALLA: FULL WIDTH VS BOXED -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Ancho en Pantalla (Sangrado Edge-to-Edge)</label>
                            <input type="hidden" name="settings[full_width]" value="0">
                            <label class="flex items-center gap-2.5 cursor-pointer p-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                                <input type="checkbox" name="settings[full_width]" id="editFullWidth" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                <div>
                                    <span class="font-bold text-slate-800 block text-xs">Abarcar 100% del Ancho de Pantalla (Full-Width)</span>
                                    <span class="text-[11px] text-slate-500">Expande el hero de borde a borde rompiendo los límites del contenedor principal de la tienda.</span>
                                </div>
                            </label>
                        </div>

                        <!-- 3. IMAGEN DE FONDO (AMBOS MODOS) -->
                        <div class="space-y-2 p-3 bg-white border border-slate-200 rounded-xl">
                            <label class="block font-bold text-slate-800 text-xs">Imagen de Fondo (Background Image)</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Subir Archivo Local</label>
                                    <input type="file" name="background_image_file" id="editHeroBgFile" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" onchange="window.previewHeroBgImage(this, 'editHeroBgPreview')">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">O Ingresar URL Externa</label>
                                    <input type="text" name="settings[background_image]" id="editHeroBgUrl" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" placeholder="https://ejemplo.com/hero-bg.webp" oninput="window.previewHeroBgUrl(this.value, 'editHeroBgPreview')">
                                </div>
                            </div>
                            <div id="editHeroBgPreview" class="hidden mt-2 p-2 border border-dashed border-slate-300 rounded-lg bg-slate-50 flex items-center gap-3">
                                <img src="" alt="Vista previa fondo" class="w-16 h-10 object-cover rounded shadow-xs bg-slate-200">
                                <span class="text-[11px] text-slate-600 truncate flex-1 font-mono"></span>
                                <button type="button" onclick="window.clearHeroBgPreview('editSectionModal', 'editHeroBgPreview', 'editHeroBgUrl')" class="text-rose-600 hover:text-rose-800 font-bold text-xs cursor-pointer">Eliminar</button>
                            </div>
                        </div>

                        <!-- 4. CAPA DE DEGRADADOS OVERLAY (AMBOS MODOS) -->
                        <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                            <input type="hidden" name="settings[enable_gradient]" value="0">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="settings[enable_gradient]" id="editEnableGradient" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4" onchange="document.getElementById('editGradientControls').style.display = this.checked ? 'block' : 'none'">
                                <span class="font-bold text-slate-800 text-xs">Habilitar Capa de Degradado / Overlay sobre el Fondo</span>
                            </label>

                            <div id="editGradientControls" style="display: none;" class="space-y-3 pt-2 border-t border-slate-100">
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color Inicio</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="color" id="editGradColorStartPicker" value="#0f172a" class="w-7 h-7 p-0 border border-slate-200 rounded cursor-pointer" oninput="document.getElementById('editGradColorStart').value = this.value; window.updateHeroGradientPreview('edit')">
                                            <input type="text" name="settings[gradient_color_start]" id="editGradColorStart" value="#0f172a" class="w-full px-2 py-1 text-[11px] font-mono border border-slate-200 rounded" oninput="document.getElementById('editGradColorStartPicker').value = this.value; window.updateHeroGradientPreview('edit')">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color Fin</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="color" id="editGradColorEndPicker" value="#1e1b4b" class="w-7 h-7 p-0 border border-slate-200 rounded cursor-pointer" oninput="document.getElementById('editGradColorEnd').value = this.value; window.updateHeroGradientPreview('edit')">
                                            <input type="text" name="settings[gradient_color_end]" id="editGradColorEnd" value="#1e1b4b" class="w-full px-2 py-1 text-[11px] font-mono border border-slate-200 rounded" oninput="document.getElementById('editGradColorEndPicker').value = this.value; window.updateHeroGradientPreview('edit')">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Dirección</label>
                                        <select name="settings[gradient_direction]" id="editGradDir" class="w-full px-2 py-1.5 text-[11px] border border-slate-200 rounded" onchange="window.updateHeroGradientPreview('edit')">
                                            <option value="to bottom">Vertical ↓</option>
                                            <option value="to right">Horizontal →</option>
                                            <option value="135deg">Diagonal ↘</option>
                                            <option value="to top">Invertido ↑</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Opacidad: <span id="editGradOpacityVal">80%</span></label>
                                        <input type="range" name="settings[gradient_opacity]" id="editGradOpacity" min="10" max="100" value="80" class="w-full cursor-pointer accent-indigo-600" oninput="document.getElementById('editGradOpacityVal').textContent = this.value + '%'; window.updateHeroGradientPreview('edit')">
                                    </div>
                                </div>

                                <div id="editGradPreviewBar" class="h-6 rounded-lg border border-slate-200 shadow-inner" style="background: linear-gradient(to bottom, #0f172a, #1e1b4b);"></div>
                            </div>
                        </div>

                        <!-- 5. ACCIONES Y BOTONES DEL HERO (SOLO MODO ESTRUCTURADO) -->
                        <div id="editHeroButtonsWrap" class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                            <label class="block font-bold text-slate-800 text-xs">Botones de Acción (Call to Action)</label>

                            <!-- Botón Primario -->
                            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg space-y-2">
                                <span class="block font-bold text-indigo-700 text-[11px]">Botón Primario (Destacado)</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Texto del Botón</label>
                                        <input type="text" name="settings[cta_primary_text]" id="editCtaPrimaryText" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs" placeholder="Ej: EXPLORAR CATÁLOGO">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">URL / Ruta de Destino</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="text" name="settings[cta_primary_url]" id="editCtaPrimaryUrl" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" placeholder="Ej: #section-2 o /catalogo">
                                            <select onchange="if(this.value){ document.getElementById('editCtaPrimaryUrl').value = this.value; this.value=''; }" class="px-2 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-600 font-semibold bg-white cursor-pointer" title="Seleccionar una ruta de la tienda">
                                                <option value="">+ Insertar Ruta</option>
                                                <optgroup label="Secciones Modulares">
                                                    @foreach($sections as $s)
                                                        <option value="#section-{{ $s->id }}">#section-{{ $s->id }} ({{ $s->name }})</option>
                                                    @endforeach
                                                </optgroup>
                                                <optgroup label="Páginas Institucionales">
                                                    @foreach($pages as $p)
                                                        <option value="/pagina/{{ $p->slug }}">/pagina/{{ $p->slug }} ({{ $p->title }})</option>
                                                    @endforeach
                                                </optgroup>
                                                <optgroup label="Vitrinas Multiverso">
                                                    <option value="/marte">/marte (Masculino)</option>
                                                    <option value="/venus">/venus (Femenino)</option>
                                                    <option value="/tiresias">/tiresias (Afirmativo)</option>
                                                    <option value="/terra">/terra (Unisex)</option>
                                                    <option value="/catalogo">/catalogo (General)</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón Secundario -->
                            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg space-y-2">
                                <span class="block font-bold text-slate-700 text-[11px]">Botón Secundario (Opcional)</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Texto del Botón</label>
                                        <input type="text" name="settings[cta_secondary_text]" id="editCtaSecondaryText" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs" placeholder="Ej: GUÍA DE TALLAS TORZO">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">URL / Ruta de Destino</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="text" name="settings[cta_secondary_url]" id="editCtaSecondaryUrl" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" placeholder="Ej: /pagina/guia-tallas o #section-3">
                                            <select onchange="if(this.value){ document.getElementById('editCtaSecondaryUrl').value = this.value; this.value=''; }" class="px-2 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-600 font-semibold bg-white cursor-pointer" title="Seleccionar una ruta de la tienda">
                                                <option value="">+ Insertar Ruta</option>
                                                <optgroup label="Secciones Modulares">
                                                    @foreach($sections as $s)
                                                        <option value="#section-{{ $s->id }}">#section-{{ $s->id }} ({{ $s->name }})</option>
                                                    @endforeach
                                                </optgroup>
                                                <optgroup label="Páginas Institucionales">
                                                    @foreach($pages as $p)
                                                        <option value="/pagina/{{ $p->slug }}">/pagina/{{ $p->slug }} ({{ $p->title }})</option>
                                                    @endforeach
                                                </optgroup>
                                                <optgroup label="Vitrinas Multiverso">
                                                    <option value="/marte">/marte (Masculino)</option>
                                                    <option value="/venus">/venus (Femenino)</option>
                                                    <option value="/tiresias">/tiresias (Afirmativo)</option>
                                                    <option value="/terra">/terra (Unisex)</option>
                                                    <option value="/catalogo">/catalogo (General)</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- EDITOR DE CÓDIGO CUSTOM (SCOPED) -->
                    <div id="editCustomCodeWrap" class="p-3 bg-slate-900 text-slate-200 border border-slate-800 rounded-xl space-y-3">
                        <span class="block font-bold text-emerald-400 text-[11px] uppercase tracking-wider">Editor de Código Custom (Aislado)</span>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">HTML Personalizado</label>
                            <textarea style="resize: vertical !important;" name="custom_html" id="editCustomHtml" rows="3" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 text-slate-100 font-mono text-[11px] rounded-lg"></textarea>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">CSS Scoped</label>
                                <textarea style="resize: vertical !important;" name="custom_css" id="editCustomCss" rows="3" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 text-slate-100 font-mono text-[11px] rounded-lg"></textarea>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">JavaScript Custom</label>
                                <textarea style="resize: vertical !important;" name="custom_js" id="editCustomJs" rows="3" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 text-slate-100 font-mono text-[11px] rounded-lg"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" class="px-4 py-2 text-slate-600 font-semibold cursor-pointer" data-action="close-modal" data-modal="editSectionModal">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg cursor-pointer flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Guardar Cambios de Sección</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. MODAL EDITAR GUÍA DE TALLAS TORZO -->
    <div id="editSizeGuideModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-sm font-bold text-slate-900">Editar Guía Oficial de Tallas Textiles Torzo</h3>
                <button type="button" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer" data-action="close-modal" data-modal="editSizeGuideModal">&times;</button>
            </div>

            <form method="POST" action="{{ route('portal.store.documents.update', ['code' => 'SIZE_GUIDE_TORZO']) }}" class="space-y-4 text-xs">
                @csrf
                <div class="space-y-3">
                    @foreach($sizeGuide as $idx => $s)
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl grid grid-cols-1 sm:grid-cols-5 gap-2 items-center">
                            <div>
                                <label class="block font-bold text-slate-700 mb-0.5">Talla</label>
                                <input type="text" name="payload[{{ $idx }}][size]" value="{{ $s['size'] }}" class="w-full px-2 py-1.5 border border-slate-200 rounded font-bold text-xs">
                                <input type="hidden" name="payload[{{ $idx }}][isCore]" value="{{ !empty($s['isCore']) ? 1 : 0 }}">
                            </div>
                            <div>
                                <label class="block text-slate-600 mb-0.5">Cintura (cm)</label>
                                <input type="text" name="payload[{{ $idx }}][waistCm]" value="{{ $s['waistCm'] }}" class="w-full px-2 py-1.5 border border-slate-200 rounded text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-slate-600 mb-0.5">Cadera (cm)</label>
                                <input type="text" name="payload[{{ $idx }}][hipsCm]" value="{{ $s['hipsCm'] }}" class="w-full px-2 py-1.5 border border-slate-200 rounded text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-slate-600 mb-0.5">Pantalón</label>
                                <input type="text" name="payload[{{ $idx }}][pantsSize]" value="{{ $s['pantsSize'] }}" class="w-full px-2 py-1.5 border border-slate-200 rounded text-xs font-mono font-bold">
                            </div>
                            <div>
                                <label class="block text-slate-600 mb-0.5">Consejo Calce</label>
                                <input type="text" name="payload[{{ $idx }}][advice]" value="{{ $s['advice'] }}" class="w-full px-2 py-1.5 border border-slate-200 rounded text-[11px]">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" class="px-4 py-2 text-slate-600 font-semibold cursor-pointer" data-action="close-modal" data-modal="editSizeGuideModal">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-lg cursor-pointer">Guardar Matriz de Tallas</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. MODAL EDITAR PROTOCOLOS DE CUIDADO -->
    <div id="editCareModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-sm font-bold text-slate-900">Editar Protocolos Oficiales de Preservación Textil</h3>
                <button type="button" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer" data-action="close-modal" data-modal="editCareModal">&times;</button>
            </div>

            <form method="POST" action="{{ route('portal.store.documents.update', ['code' => 'CARE_PROTOCOLS']) }}" class="space-y-4 text-xs">
                @csrf
                <div class="space-y-3">
                    @foreach($careProtocols as $idx => $c)
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                            <div>
                                <label class="block font-bold text-slate-800 mb-1">Fibra / Prenda</label>
                                <input type="text" name="payload[{{ $idx }}][fiber]" value="{{ $c['fiber'] }}" class="w-full px-3 py-1.5 border border-slate-200 rounded font-bold text-xs">
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1">Regla de Lavado</label>
                                <textarea style="resize: vertical !important;" name="payload[{{ $idx }}][rule]" rows="2" class="w-full px-3 py-1.5 border border-slate-200 rounded text-xs">{{ $c['rule'] }}</textarea>
                            </div>
                            <div>
                                <label class="block text-amber-800 font-semibold mb-1">Advertencia de Cuidado</label>
                                <input type="text" name="payload[{{ $idx }}][warning]" value="{{ $c['warning'] }}" class="w-full px-3 py-1.5 border border-amber-200 bg-amber-50/50 rounded text-xs text-amber-900">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" class="px-4 py-2 text-slate-600 font-semibold cursor-pointer" data-action="close-modal" data-modal="editCareModal">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-lg cursor-pointer">Guardar Protocolos</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 4. MODAL EDITAR AVISO SANITARIO PROFECO -->
    <div id="editProfecoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-sm font-bold text-slate-900">Editar Aviso Normativo y Sanitario PROFECO</h3>
                <button type="button" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer" data-action="close-modal" data-modal="editProfecoModal">&times;</button>
            </div>

            <form method="POST" action="{{ route('portal.store.documents.update', ['code' => 'PROFECO_SANITARY_NOTICE']) }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Fundamento Legal</label>
                    <input type="text" name="payload[legal_basis]" value="{{ $profecoNotice['legal_basis'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Declaración de Higiene / No Retorno</label>
                    <textarea style="resize: vertical !important;" name="payload[statement]" rows="4" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs leading-relaxed">{{ $profecoNotice['statement'] ?? '' }}</textarea>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Garantía de Empaque Discreto</label>
                    <input type="text" name="payload[discreet_shipping]" value="{{ $profecoNotice['discreet_shipping'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" class="px-4 py-2 text-slate-600 font-semibold cursor-pointer" data-action="close-modal" data-modal="editProfecoModal">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg cursor-pointer">Guardar Aviso Sanitario</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 5. MODAL CREAR PÁGINA DE EXPLORAR / INSTITUCIONAL (TASK-H2-03) -->
    <div id="createPageModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Crear Nueva Página de Explorar / Institucional</span>
                </h3>
                <button type="button" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer" data-action="close-modal" data-modal="createPageModal">&times;</button>
            </div>

            <form method="POST" action="{{ route('portal.store.pages.store') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Título de la Página</label>
                        <input type="text" name="title" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Ej: Curaduría de Lino & Seda">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Slug URL (Opcional)</label>
                        <input type="text" name="slug" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-mono" placeholder="curaduria-lino-seda">
                    </div>
                </div>

                <!-- CONFIGURACIÓN DE EXPLORAR & FILTROS DE CATÁLOGO -->
                <div class="p-3 bg-violet-50/60 border border-violet-200 rounded-xl space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-violet-900 text-xs flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                            Tarjeta de Explorar &amp; Filtros de Catálogo
                        </span>
                        <span class="text-[10px] text-violet-700 bg-violet-100 px-2 py-0.5 rounded-full font-semibold">Vista /explorar</span>
                    </div>

                    <!-- 1. MODO DE IMÁGENES & CARRUSEL -->
                    <div class="space-y-2 bg-white/80 p-3 rounded-lg border border-violet-200/80">
                        <div class="flex items-center justify-between">
                            <label class="block font-semibold text-slate-800 text-xs">Modo de Imágenes &amp; Carrusel</label>
                            <span class="text-[10px] text-slate-500 font-mono">Carrusel o Catálogo</span>
                        </div>
                        <select name="image_source_mode" id="create_page_image_mode" onchange="window.togglePageImageMode(this.value, 'create')" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold bg-white">
                            <option value="custom">Imágenes Manuales / Carrusel Personalizado</option>
                            <option value="catalog">Conmutación Dinámica desde el Catálogo (según Filtros de Prenda)</option>
                        </select>

                        <div id="create_page_custom_images_wrap" class="space-y-2 mt-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-700 text-[11px] mb-1">Imagen Principal</label>
                                    <input type="text" name="image_url" id="create_page_image_url" oninput="window.updatePageModalPreview('create')" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs" placeholder="https://... o /uploads/...">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 text-[11px] mb-1">O Subir Archivo</label>
                                    <input type="file" name="image_file" accept="image/*" class="w-full px-2 py-1 border border-slate-200 rounded-lg text-xs bg-white">
                                </div>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 text-[11px] mb-1">Imágenes Adicionales para Carrusel Automático (URLs por línea o comas)</label>
                                <textarea style="resize: vertical !important;" name="gallery_images_urls" rows="2" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" placeholder="https://.../foto2.jpg&#10;https://.../foto3.jpg"></textarea>
                                <p class="text-[10px] text-slate-400 mt-0.5">Si se configuran 2 o más imágenes, rotarán en carrusel automático suave cada 3.5 segundos.</p>
                            </div>
                        </div>
                        <div id="create_page_catalog_images_info" class="hidden p-2 rounded-lg bg-indigo-50/70 border border-indigo-200 text-indigo-900 text-[11px]">
                            <svg class="w-3.5 h-3.5 inline mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            El carrusel rotará automáticamente fotografías de las prendas activas en el catálogo que coincidan con el universo y categoría configurados.
                        </div>
                    </div>

                    <!-- 2. OVERLAY, DEGRADADOS Y CONTRASTE DE TEXTO (REGLA 1.1) -->
                    <div class="space-y-3 bg-white/80 p-3 rounded-lg border border-violet-200/80">
                        <div class="flex items-center justify-between">
                            <label class="block font-semibold text-slate-800 text-xs">Overlay con Degradado Personalizable &amp; Contraste</label>
                            <span class="text-[10px] text-indigo-600 font-semibold">100% Ajustable</span>
                        </div>

                        <!-- Presets rápidos -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] text-slate-500 font-semibold">Presets:</span>
                            <button type="button" onclick="window.applyPageGradientPreset('noir', 'create')" class="px-2 py-0.5 rounded text-[10px] bg-slate-900 text-white font-medium hover:bg-black">Noir</button>
                            <button type="button" onclick="window.applyPageGradientPreset('wine', 'create')" class="px-2 py-0.5 rounded text-[10px] bg-rose-900 text-white font-medium hover:bg-rose-950">Vino</button>
                            <button type="button" onclick="window.applyPageGradientPreset('indigo', 'create')" class="px-2 py-0.5 rounded text-[10px] bg-indigo-900 text-white font-medium hover:bg-indigo-950">Índigo</button>
                            <button type="button" onclick="window.applyPageGradientPreset('emerald', 'create')" class="px-2 py-0.5 rounded text-[10px] bg-emerald-900 text-white font-medium hover:bg-emerald-950">Esmeralda</button>
                            <button type="button" onclick="window.applyPageGradientPreset('amber', 'create')" class="px-2 py-0.5 rounded text-[10px] bg-amber-900 text-white font-medium hover:bg-amber-950">Ámbar</button>
                            <button type="button" onclick="window.applyPageGradientPreset('high_contrast', 'create')" class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-amber-300 font-semibold hover:bg-black">Alto Contraste</button>
                        </div>

                        <!-- Controles de Color y Opacidad por cada Stop -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 space-y-2">
                                <span class="text-[11px] font-bold text-slate-700 block">Color Superior (Inicio)</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="overlay_color_start" id="create_overlay_color_start" value="#000000" onchange="window.updatePageModalPreview('create')" class="w-8 h-8 rounded border border-slate-300 cursor-pointer p-0.5 bg-white">
                                    <input type="text" id="create_overlay_color_start_txt" value="#000000" oninput="document.getElementById('create_overlay_color_start').value=this.value; window.updatePageModalPreview('create');" class="w-20 px-2 py-1 text-xs font-mono border border-slate-200 rounded">
                                </div>
                                <div>
                                    <div class="flex justify-between text-[10px] text-slate-500 mb-0.5">
                                        <span>Opacidad Superior:</span>
                                        <strong id="create_overlay_opacity_start_val">25%</strong>
                                    </div>
                                    <input type="range" name="overlay_opacity_start" id="create_overlay_opacity_start" min="0" max="100" value="25" oninput="document.getElementById('create_overlay_opacity_start_val').textContent=this.value+'%'; window.updatePageModalPreview('create');" class="w-full accent-indigo-600">
                                </div>
                            </div>

                            <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 space-y-2">
                                <span class="text-[11px] font-bold text-slate-700 block">Color Inferior (Fin)</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="overlay_color_end" id="create_overlay_color_end" value="#000000" onchange="window.updatePageModalPreview('create')" class="w-8 h-8 rounded border border-slate-300 cursor-pointer p-0.5 bg-white">
                                    <input type="text" id="create_overlay_color_end_txt" value="#000000" oninput="document.getElementById('create_overlay_color_end').value=this.value; window.updatePageModalPreview('create');" class="w-20 px-2 py-1 text-xs font-mono border border-slate-200 rounded">
                                </div>
                                <div>
                                    <div class="flex justify-between text-[10px] text-slate-500 mb-0.5">
                                        <span>Opacidad Inferior:</span>
                                        <strong id="create_overlay_opacity_end_val">85%</strong>
                                    </div>
                                    <input type="range" name="overlay_opacity_end" id="create_overlay_opacity_end" min="0" max="100" value="85" oninput="document.getElementById('create_overlay_opacity_end_val').textContent=this.value+'%'; window.updatePageModalPreview('create');" class="w-full accent-indigo-600">
                                </div>
                            </div>
                        </div>

                        <!-- Color de Texto para Contraste Garantizado -->
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div>
                                <strong class="text-[11px] text-slate-800 block">Color del Texto de la Tarjeta</strong>
                                <span class="text-[10px] text-slate-400">Garantiza contraste óptimo para que no se pierda sobre la fotografía.</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="color" name="text_color" id="create_page_text_color" value="#ffffff" onchange="window.updatePageModalPreview('create')" class="w-8 h-8 rounded border border-slate-300 cursor-pointer p-0.5 bg-white">
                                <button type="button" onclick="document.getElementById('create_page_text_color').value='#ffffff'; window.updatePageModalPreview('create');" class="px-2 py-1 text-[10px] font-bold bg-white border border-slate-300 rounded text-slate-800">Blanco</button>
                                <button type="button" onclick="document.getElementById('create_page_text_color').value='#fef08a'; window.updatePageModalPreview('create');" class="px-2 py-1 text-[10px] font-bold bg-amber-100 border border-amber-300 rounded text-amber-900">Cálido</button>
                                <button type="button" onclick="document.getElementById('create_page_text_color').value='#09090b'; window.updatePageModalPreview('create');" class="px-2 py-1 text-[10px] font-bold bg-slate-900 border border-slate-900 rounded text-white">Negro</button>
                            </div>
                        </div>

                        <!-- Miniatura Live Preview 4:5 -->
                        <div class="p-3 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-between gap-4">
                            <div>
                                <span class="text-[11px] font-bold text-slate-700 block">Vista Previa en Vivo (Proporción 4:5)</span>
                                <p class="text-[10px] text-slate-400">Simulación del contraste y legibilidad con la configuración actual.</p>
                            </div>
                            <div id="create_preview_card" class="w-24 h-30 rounded-xl overflow-hidden relative shrink-0 border border-white/20 shadow-sm flex flex-col justify-end p-2 bg-slate-900" style="aspect-ratio: 4 / 5;">
                                <div id="create_preview_bg" class="absolute inset-0 bg-cover bg-center"></div>
                                <div id="create_preview_overlay" class="absolute inset-0 pointer-events-none" style="background: linear-gradient(180deg, rgba(0,0,0,0.25) 0%, rgba(0,0,0,0.85) 100%);"></div>
                                <div class="relative z-10">
                                    <span id="create_preview_badge" class="text-[8px] font-bold px-1.5 py-0.5 rounded-full bg-black/60 text-white uppercase border border-white/30 block w-fit mb-1">Colección</span>
                                    <strong id="create_preview_title" class="text-[9px] font-extrabold leading-tight block text-white drop-shadow">Título</strong>
                                    <span class="text-[7px] text-white/90 bg-white/20 px-1 py-0.5 rounded-full mt-1 inline-block border border-white/30 font-bold">Ver &rarr;</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Filtrar Universo</label>
                            <select name="filter_universe" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold">
                                <option value="">-- Sin filtro (General) --</option>
                                <option value="all">Todos los Universos</option>
                                <option value="dama">Dama</option>
                                <option value="caballero">Caballero</option>
                                <option value="accesorios">Accesorios</option>
                                <option value="calzado">Calzado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Filtrar Categoría (Hub 4)</label>
                            <select name="filter_category" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold bg-white text-slate-800">
                                <option value="">-- Sin Filtro (Todo el Catálogo) --</option>
                                <optgroup label="Familias Principales (Hub 4)">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                                    @endforeach
                                </optgroup>
                                @if(!empty($subcategories) && $subcategories->isNotEmpty())
                                    <optgroup label="Subcategorías (Hub 4)">
                                        @foreach($subcategories as $subcat)
                                            <option value="{{ $subcat->slug }}">{{ $subcat->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Filtrar Proveedor (Hub 4)</label>
                            <select name="filter_supplier" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold bg-white text-slate-800">
                                <option value="">-- Sin Filtro (Todos) --</option>
                                @if(!empty($suppliers))
                                    @foreach($suppliers as $sup)
                                        <option value="{{ $sup->name }}">{{ $sup->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Etiquetas (Tags)</label>
                            <input type="text" name="filter_tags" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Ej: nuevo, lino, verano">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Contenido de la Página (HTML o Texto)</label>
                    <textarea style="resize: vertical !important;" name="content" rows="6" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-mono" placeholder="<p>Explora nuestras colecciones curadas con materiales sostenibles...</p>"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <div class="space-y-2">
                        <label class="inline-flex items-center gap-2 font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" name="show_in_navbar" value="1" class="rounded text-indigo-600">
                            <span>Visible en Desktop</span>
                        </label>
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-0.5">Orden Navbar</label>
                            <input type="number" name="navbar_order" value="0" class="w-20 px-2 py-1 border border-slate-200 rounded text-xs">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="inline-flex items-center gap-2 font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" name="show_in_mobile" value="1" checked class="rounded text-indigo-600">
                            <span>Visible en Móvil</span>
                        </label>
                        <p class="text-[11px] text-slate-400">Aparece en barra rápida y drawer móvil.</p>
                    </div>
                    <div class="space-y-2">
                        <label class="inline-flex items-center gap-2 font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" name="show_in_footer" value="1" checked class="rounded text-indigo-600">
                            <span>Mostrar en Footer</span>
                        </label>
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-0.5">Orden Footer</label>
                            <input type="number" name="footer_order" value="0" class="w-20 px-2 py-1 border border-slate-200 rounded text-xs">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Meta Título (SEO)</label>
                        <input type="text" name="meta_title" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Título para buscadores">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Estado de Publicación</label>
                        <select name="status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold">
                            <option value="active">Activo (Visible al público)</option>
                            <option value="draft">Borrador (Oculto)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Meta Descripción (SEO)</label>
                    <textarea style="resize: vertical !important;" name="meta_description" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Breve resumen optimizado para Google (máx 160 caracteres)"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" class="px-4 py-2 text-slate-600 font-semibold cursor-pointer" data-action="close-modal" data-modal="createPageModal">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg cursor-pointer">Guardar Página</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 5.1 MODAL EDITAR PÁGINA DE EXPLORAR / INSTITUCIONAL (TASK-STR-09) DIRECT SIBLING DEPTH=2 -->
    <div id="editPageModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Editar Página de Explorar / Institucional</span>
                </h3>
                <button type="button" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer" data-action="close-modal" data-modal="editPageModal">&times;</button>
            </div>

            <form id="editPageForm" method="POST" action="" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Título de la Página</label>
                        <input type="text" id="edit_page_title" name="title" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Ej: Política de Envíos Seguros">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Slug URL</label>
                        <input type="text" id="edit_page_slug" name="slug" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-mono" placeholder="envios-seguros">
                    </div>
                </div>

                <!-- CONFIGURACIÓN DE EXPLORAR & FILTROS DE CATÁLOGO -->
                <div class="p-3 bg-violet-50/60 border border-violet-200 rounded-xl space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-violet-900 text-xs flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                            Tarjeta de Explorar &amp; Filtros de Catálogo
                        </span>
                        <span class="text-[10px] text-violet-700 bg-violet-100 px-2 py-0.5 rounded-full font-semibold">Vista /explorar</span>
                    </div>

                    <!-- 1. MODO DE IMÁGENES & CARRUSEL -->
                    <div class="space-y-2 bg-white/80 p-3 rounded-lg border border-violet-200/80">
                        <div class="flex items-center justify-between">
                            <label class="block font-semibold text-slate-800 text-xs">Modo de Imágenes &amp; Carrusel</label>
                            <span class="text-[10px] text-slate-500 font-mono">Carrusel o Catálogo</span>
                        </div>
                        <select name="image_source_mode" id="edit_page_image_mode" onchange="window.togglePageImageMode(this.value, 'edit')" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold bg-white">
                            <option value="custom">Imágenes Manuales / Carrusel Personalizado</option>
                            <option value="catalog">Conmutación Dinámica desde el Catálogo (según Filtros de Prenda)</option>
                        </select>

                        <div id="edit_page_custom_images_wrap" class="space-y-2 mt-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-700 text-[11px] mb-1">Imagen Principal</label>
                                    <input type="text" id="edit_page_image_url" name="image_url" oninput="window.updatePageModalPreview('edit')" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs" placeholder="https://... o /uploads/...">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 text-[11px] mb-1">O Subir Nueva Imagen</label>
                                    <input type="file" name="image_file" accept="image/*" class="w-full px-2 py-1 border border-slate-200 rounded-lg text-xs bg-white">
                                </div>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 text-[11px] mb-1">Imágenes Adicionales para Carrusel Automático (URLs por línea o comas)</label>
                                <textarea style="resize: vertical !important;" name="gallery_images_urls" id="edit_page_gallery_images_urls" rows="2" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" placeholder="https://.../foto2.jpg&#10;https://.../foto3.jpg"></textarea>
                                <p class="text-[10px] text-slate-400 mt-0.5">Si se configuran 2 o más imágenes, rotarán en carrusel automático suave cada 3.5 segundos.</p>
                            </div>
                        </div>
                        <div id="edit_page_catalog_images_info" class="hidden p-2 rounded-lg bg-indigo-50/70 border border-indigo-200 text-indigo-900 text-[11px]">
                            <svg class="w-3.5 h-3.5 inline mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            El carrusel rotará automáticamente fotografías de las prendas activas en el catálogo que coincidan con el universo y categoría configurados.
                        </div>
                    </div>

                    <!-- 2. OVERLAY, DEGRADADOS Y CONTRASTE DE TEXTO (REGLA 1.1) -->
                    <div class="space-y-3 bg-white/80 p-3 rounded-lg border border-violet-200/80">
                        <div class="flex items-center justify-between">
                            <label class="block font-semibold text-slate-800 text-xs">Overlay con Degradado Personalizable &amp; Contraste</label>
                            <span class="text-[10px] text-indigo-600 font-semibold">100% Ajustable</span>
                        </div>

                        <!-- Presets rápidos -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] text-slate-500 font-semibold">Presets:</span>
                            <button type="button" onclick="window.applyPageGradientPreset('noir', 'edit')" class="px-2 py-0.5 rounded text-[10px] bg-slate-900 text-white font-medium hover:bg-black">Noir</button>
                            <button type="button" onclick="window.applyPageGradientPreset('wine', 'edit')" class="px-2 py-0.5 rounded text-[10px] bg-rose-900 text-white font-medium hover:bg-rose-950">Vino</button>
                            <button type="button" onclick="window.applyPageGradientPreset('indigo', 'edit')" class="px-2 py-0.5 rounded text-[10px] bg-indigo-900 text-white font-medium hover:bg-indigo-950">Índigo</button>
                            <button type="button" onclick="window.applyPageGradientPreset('emerald', 'edit')" class="px-2 py-0.5 rounded text-[10px] bg-emerald-900 text-white font-medium hover:bg-emerald-950">Esmeralda</button>
                            <button type="button" onclick="window.applyPageGradientPreset('amber', 'edit')" class="px-2 py-0.5 rounded text-[10px] bg-amber-900 text-white font-medium hover:bg-amber-950">Ámbar</button>
                            <button type="button" onclick="window.applyPageGradientPreset('high_contrast', 'edit')" class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-amber-300 font-semibold hover:bg-black">Alto Contraste</button>
                        </div>

                        <!-- Controles de Color y Opacidad por cada Stop -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 space-y-2">
                                <span class="text-[11px] font-bold text-slate-700 block">Color Superior (Inicio)</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="overlay_color_start" id="edit_overlay_color_start" value="#000000" onchange="window.updatePageModalPreview('edit')" class="w-8 h-8 rounded border border-slate-300 cursor-pointer p-0.5 bg-white">
                                    <input type="text" id="edit_overlay_color_start_txt" value="#000000" oninput="document.getElementById('edit_overlay_color_start').value=this.value; window.updatePageModalPreview('edit');" class="w-20 px-2 py-1 text-xs font-mono border border-slate-200 rounded">
                                </div>
                                <div>
                                    <div class="flex justify-between text-[10px] text-slate-500 mb-0.5">
                                        <span>Opacidad Superior:</span>
                                        <strong id="edit_overlay_opacity_start_val">25%</strong>
                                    </div>
                                    <input type="range" name="overlay_opacity_start" id="edit_overlay_opacity_start" min="0" max="100" value="25" oninput="document.getElementById('edit_overlay_opacity_start_val').textContent=this.value+'%'; window.updatePageModalPreview('edit');" class="w-full accent-indigo-600">
                                </div>
                            </div>

                            <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 space-y-2">
                                <span class="text-[11px] font-bold text-slate-700 block">Color Inferior (Fin)</span>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="overlay_color_end" id="edit_overlay_color_end" value="#000000" onchange="window.updatePageModalPreview('edit')" class="w-8 h-8 rounded border border-slate-300 cursor-pointer p-0.5 bg-white">
                                    <input type="text" id="edit_overlay_color_end_txt" value="#000000" oninput="document.getElementById('edit_overlay_color_end').value=this.value; window.updatePageModalPreview('edit');" class="w-20 px-2 py-1 text-xs font-mono border border-slate-200 rounded">
                                </div>
                                <div>
                                    <div class="flex justify-between text-[10px] text-slate-500 mb-0.5">
                                        <span>Opacidad Inferior:</span>
                                        <strong id="edit_overlay_opacity_end_val">85%</strong>
                                    </div>
                                    <input type="range" name="overlay_opacity_end" id="edit_overlay_opacity_end" min="0" max="100" value="85" oninput="document.getElementById('edit_overlay_opacity_end_val').textContent=this.value+'%'; window.updatePageModalPreview('edit');" class="w-full accent-indigo-600">
                                </div>
                            </div>
                        </div>

                        <!-- Color de Texto para Contraste Garantizado -->
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div>
                                <strong class="text-[11px] text-slate-800 block">Color del Texto de la Tarjeta</strong>
                                <span class="text-[10px] text-slate-400">Garantiza contraste óptimo para que no se pierda sobre la fotografía.</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="color" name="text_color" id="edit_page_text_color" value="#ffffff" onchange="window.updatePageModalPreview('edit')" class="w-8 h-8 rounded border border-slate-300 cursor-pointer p-0.5 bg-white">
                                <button type="button" onclick="document.getElementById('edit_page_text_color').value='#ffffff'; window.updatePageModalPreview('edit');" class="px-2 py-1 text-[10px] font-bold bg-white border border-slate-300 rounded text-slate-800">Blanco</button>
                                <button type="button" onclick="document.getElementById('edit_page_text_color').value='#fef08a'; window.updatePageModalPreview('edit');" class="px-2 py-1 text-[10px] font-bold bg-amber-100 border border-amber-300 rounded text-amber-900">Cálido</button>
                                <button type="button" onclick="document.getElementById('edit_page_text_color').value='#09090b'; window.updatePageModalPreview('edit');" class="px-2 py-1 text-[10px] font-bold bg-slate-900 border border-slate-900 rounded text-white">Negro</button>
                            </div>
                        </div>

                        <!-- Miniatura Live Preview 4:5 -->
                        <div class="p-3 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-between gap-4">
                            <div>
                                <span class="text-[11px] font-bold text-slate-700 block">Vista Previa en Vivo (Proporción 4:5)</span>
                                <p class="text-[10px] text-slate-400">Simulación del contraste y legibilidad con la configuración actual.</p>
                            </div>
                            <div id="edit_preview_card" class="w-24 h-30 rounded-xl overflow-hidden relative shrink-0 border border-white/20 shadow-sm flex flex-col justify-end p-2 bg-slate-900" style="aspect-ratio: 4 / 5;">
                                <div id="edit_preview_bg" class="absolute inset-0 bg-cover bg-center"></div>
                                <div id="edit_preview_overlay" class="absolute inset-0 pointer-events-none" style="background: linear-gradient(180deg, rgba(0,0,0,0.25) 0%, rgba(0,0,0,0.85) 100%);"></div>
                                <div class="relative z-10">
                                    <span id="edit_preview_badge" class="text-[8px] font-bold px-1.5 py-0.5 rounded-full bg-black/60 text-white uppercase border border-white/30 block w-fit mb-1">Colección</span>
                                    <strong id="edit_preview_title" class="text-[9px] font-extrabold leading-tight block text-white drop-shadow">Título</strong>
                                    <span class="text-[7px] text-white/90 bg-white/20 px-1 py-0.5 rounded-full mt-1 inline-block border border-white/30 font-bold">Ver &rarr;</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Filtrar Universo</label>
                            <select id="edit_page_filter_universe" name="filter_universe" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold">
                                <option value="">-- Sin filtro (General) --</option>
                                <option value="all">Todos los Universos</option>
                                <option value="dama">Dama</option>
                                <option value="caballero">Caballero</option>
                                <option value="accesorios">Accesorios</option>
                                <option value="calzado">Calzado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Filtrar Categoría (Hub 4)</label>
                            <select id="edit_page_filter_category" name="filter_category" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold bg-white text-slate-800">
                                <option value="">-- Sin Filtro (Todo el Catálogo) --</option>
                                <optgroup label="Familias Principales (Hub 4)">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                                    @endforeach
                                </optgroup>
                                @if(!empty($subcategories) && $subcategories->isNotEmpty())
                                    <optgroup label="Subcategorías (Hub 4)">
                                        @foreach($subcategories as $subcat)
                                            <option value="{{ $subcat->slug }}">{{ $subcat->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Filtrar Proveedor (Hub 4)</label>
                            <select id="edit_page_filter_supplier" name="filter_supplier" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold bg-white text-slate-800">
                                <option value="">-- Sin Filtro (Todos) --</option>
                                @if(!empty($suppliers))
                                    @foreach($suppliers as $sup)
                                        <option value="{{ $sup->name }}">{{ $sup->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Etiquetas (Tags)</label>
                            <input type="text" id="edit_page_filter_tags" name="filter_tags" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Ej: nuevo, lino, verano">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Contenido de la Página (HTML o Texto)</label>
                    <textarea style="resize: vertical !important;" id="edit_page_content" name="content" rows="6" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-mono"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <div class="space-y-2">
                        <label class="inline-flex items-center gap-2 font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" id="edit_page_show_in_navbar" name="show_in_navbar" value="1" class="rounded text-indigo-600">
                            <span>Visible en Desktop</span>
                        </label>
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-0.5">Orden Navbar</label>
                            <input type="number" id="edit_page_navbar_order" name="navbar_order" value="0" class="w-20 px-2 py-1 border border-slate-200 rounded text-xs">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="inline-flex items-center gap-2 font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" id="edit_page_show_in_mobile" name="show_in_mobile" value="1" class="rounded text-indigo-600">
                            <span>Visible en Móvil</span>
                        </label>
                        <p class="text-[11px] text-slate-400">Barra rápida y drawer móvil.</p>
                    </div>
                    <div class="space-y-2">
                        <label class="inline-flex items-center gap-2 font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" id="edit_page_show_in_footer" name="show_in_footer" value="1" class="rounded text-indigo-600">
                            <span>Mostrar en Footer</span>
                        </label>
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-0.5">Orden Footer</label>
                            <input type="number" id="edit_page_footer_order" name="footer_order" value="0" class="w-20 px-2 py-1 border border-slate-200 rounded text-xs">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Meta Título (SEO)</label>
                        <input type="text" id="edit_page_meta_title" name="meta_title" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Título para buscadores">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Estado de Publicación</label>
                        <select id="edit_page_status" name="status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold">
                            <option value="active">Activo (Visible al público)</option>
                            <option value="paused">Pausado (Oculto)</option>
                            <option value="archived">Archivado</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Meta Descripción (SEO)</label>
                    <textarea style="resize: vertical !important;" id="edit_page_meta_description" name="meta_description" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Breve resumen optimizado para Google (máx 160 caracteres)"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" class="px-4 py-2 text-slate-600 font-semibold cursor-pointer" data-action="close-modal" data-modal="editPageModal">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg cursor-pointer">Actualizar Página</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Formularios Ocultos para Acciones Rápidas --}}
    <form id="toggleAnnouncementForm" method="POST" action="{{ route('portal.store.announcement.toggle') }}" style="display:none;">
        @csrf
    </form>
    <form id="toggleBannerForm" method="POST" style="display:none;">
        @csrf
    </form>
    <form id="toggleSectionForm" method="POST" style="display:none;">
        @csrf
    </form>
    <form id="deleteSectionForm" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
    <form id="archiveSectionForm" method="POST" style="display:none;">
        @csrf
    </form>
</div>
@endsection

@push('scripts')
{{-- SORTABLEJS PARA DRAG & DROP ERGONÓMICO EN VIVO --}}
<script src="{{ asset('js/vendor/sortable.min.js') }}"></script>

{{-- SCRIPT REACTIVO OPERATIVO TIENDA & CMS MULTIVERSO (VANILLA JS LIGERO Y SEGURO) --}}
<script>
(function() {
    'use strict';

    const storefrontsData = @json($storefronts);
    const sectionsData = @json($sections->keyBy('id'));
    let activeUniverse = '{{ $activeUniverse ?? "marte" }}';

    // ==========================================
    // 1. GESTIÓN GLOBAL DE MODALES (FAIL-SAFE)
    // ==========================================
    window.openStoreModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            console.warn(`[Store CMS] Modal no encontrado: ${modalId}`);
            return;
        }
        modal.classList.remove('hidden');
        modal.style.removeProperty('display');
        modal.style.display = 'flex';
        document.body.classList.add('overflow-hidden');
    };

    window.closeStoreModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        modal.classList.add('hidden');
        modal.style.display = 'none';
        document.body.classList.remove('overflow-hidden');
    };

    // ==========================================
    // 2. GESTIÓN DE SUBNAV TABS (5 PESTAÑAS PRINCIPALES)
    // ==========================================
    const tabPanelIds = {
        'visual': 'storeVisualPanel',
        'content_rules': 'storeContentRulesPanel',
        'pages': 'storePagesPanel',
        'tracking': 'storeTrackingPanel',
        'filters': 'storeFiltersPanel'
    };

    // Mapeo retrocompatible para enlaces a subsecciones dentro del acordeón unificado
    const subSectionMap = {
        'banners': { tab: 'content_rules', accordionId: 'accordionSecBanners' },
        'shipping': { tab: 'content_rules', accordionId: 'accordionSecShipping' },
        'editorial': { tab: 'content_rules', accordionId: 'accordionSecEditorial' },
        'footer': { tab: 'content_rules', accordionId: 'accordionSecFooter' }
    };

    // Funciones operativas de Acordeón
    window.toggleStoreAccordion = function(sectionId) {
        const section = document.getElementById(sectionId);
        if (!section) return;
        const body = document.getElementById(sectionId + 'Body');
        const chevron = section.querySelector('.store-accordion-chevron');
        if (!body) return;

        const isHidden = body.classList.contains('hidden') || body.style.display === 'none';
        if (isHidden) {
            body.classList.remove('hidden');
            body.style.display = 'block';
            if (chevron) chevron.classList.add('rotate-180');
        } else {
            body.classList.add('hidden');
            body.style.display = 'none';
            if (chevron) chevron.classList.remove('rotate-180');
        }
    };

    window.toggleAllStoreAccordions = function(expand) {
        const sections = ['accordionSecBanners', 'accordionSecShipping', 'accordionSecEditorial', 'accordionSecFooter'];
        sections.forEach(secId => {
            const section = document.getElementById(secId);
            const body = document.getElementById(secId + 'Body');
            const chevron = section ? section.querySelector('.store-accordion-chevron') : null;
            if (!body) return;
            if (expand) {
                body.classList.remove('hidden');
                body.style.display = 'block';
                if (chevron) chevron.classList.add('rotate-180');
            } else {
                body.classList.add('hidden');
                body.style.display = 'none';
                if (chevron) chevron.classList.remove('rotate-180');
            }
        });
    };

    window.switchStoreTab = function(targetTab) {
        let activeTabKey = targetTab;
        let accordionToOpen = null;

        if (subSectionMap[targetTab]) {
            activeTabKey = subSectionMap[targetTab].tab;
            accordionToOpen = subSectionMap[targetTab].accordionId;
        }

        Object.keys(tabPanelIds).forEach(tabKey => {
            const panel = document.getElementById(tabPanelIds[tabKey]);
            if (!panel) return;
            if (tabKey === activeTabKey) {
                panel.classList.remove('hidden');
                panel.style.display = 'block';
            } else {
                panel.classList.add('hidden');
                panel.style.display = 'none';
            }
        });

        const tabButtons = document.querySelectorAll('[data-action="switch-store-tab"]');
        tabButtons.forEach(btn => {
            const btnTab = btn.getAttribute('data-tab');
            if (btnTab === activeTabKey || (subSectionMap[btnTab] && subSectionMap[btnTab].tab === activeTabKey)) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        if (accordionToOpen) {
            const body = document.getElementById(accordionToOpen + 'Body');
            const sec = document.getElementById(accordionToOpen);
            const chevron = sec ? sec.querySelector('.store-accordion-chevron') : null;
            if (body) {
                body.classList.remove('hidden');
                body.style.display = 'block';
                if (chevron) chevron.classList.add('rotate-180');
                sec.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        try {
            const url = new URL(window.location);
            url.searchParams.set('tab', targetTab);
            window.history.replaceState({}, '', url);
        } catch (e) {}
    };

    // ==========================================
    // 3. CONMUTADOR DE VITRINAS MULTIVERSO & LIVE PREVIEW (8 TOKENS + BRANDING)
    // ==========================================
    window.applyStorefrontPreviewTokens = function(tokens, data) {
        const previewUniverseBadge = document.querySelector('[data-preview-universe-badge]');
        const previewFrame = document.querySelector('[data-storefront-preview-frame]');
        const previewTopbar = document.querySelector('[data-preview-topbar]');
        const logoContainer = document.querySelector('[data-preview-logo-container]');
        const previewKickerBadge = document.querySelector('[data-preview-kicker-badge]');
        const previewMarquee = document.querySelector('[data-preview-marquee]');
        const previewShowcase = document.querySelector('[data-preview-showcase]');
        const previewBadge = document.querySelector('[data-preview-badge]');
        const previewKickerText = document.querySelector('[data-preview-kicker-text]');
        const previewTitle = document.querySelector('[data-preview-title]');
        const previewSub = document.querySelector('[data-preview-sub]');
        const previewCta = document.querySelector('[data-preview-cta]');
        const previewGridShelf = document.querySelector('[data-preview-grid-shelf]');

        if (previewUniverseBadge) {
            previewUniverseBadge.textContent = data.name;
            previewUniverseBadge.style.background = tokens.accent;
        }

        if (previewFrame) {
            previewFrame.style.backgroundColor = tokens.background;
            previewFrame.style.borderColor = tokens.border;
        }

        if (previewTopbar) {
            previewTopbar.style.background = tokens.primary;
            previewTopbar.style.borderColor = tokens.border;
        }

        if (logoContainer) {
            if (data.logoUrl) {
                logoContainer.innerHTML = `<img src="${data.logoUrl}" alt="Logo" class="h-5 max-w-[120px] object-contain" data-preview-logo-img>`;
            } else {
                logoContainer.innerHTML = `<span class="font-black tracking-tight text-white text-sm" data-preview-brand-name>${data.name}</span>`;
            }
        }

        if (previewKickerBadge) {
            previewKickerBadge.textContent = data.kicker;
            previewKickerBadge.style.background = tokens.secondary;
        }

        if (previewMarquee) {
            previewMarquee.style.background = tokens.accent;
        }

        if (previewShowcase) {
            previewShowcase.style.background = tokens.surface;
            previewShowcase.style.borderColor = tokens.border;
        }

        if (previewBadge) {
            previewBadge.style.background = tokens.background;
            previewBadge.style.borderColor = tokens.border;
            previewBadge.style.color = tokens.accent;
        }

        if (previewKickerText) previewKickerText.textContent = data.kicker;
        if (previewTitle) {
            previewTitle.textContent = data.name;
            previewTitle.style.color = tokens.text;
        }
        if (previewSub) previewSub.style.color = tokens.muted_text;
        if (previewCta) previewCta.style.background = tokens.accent;

        if (previewGridShelf) {
            previewGridShelf.style.background = tokens.background;
            if (data.sampleProducts) {
                previewGridShelf.innerHTML = data.sampleProducts.map(p => `
                    <div class="preview-product-card" data-preview-product-card style="background: ${tokens.surface}; border: 1px solid ${tokens.border};">
                        <div class="preview-product-img" style="background: ${tokens.background};">
                            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" style="color: ${tokens.muted_text};"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                        <div class="font-bold truncate text-[11px]" data-preview-product-title style="color: ${tokens.text};">${p.name}</div>
                        <div class="text-[10px] mb-1" data-preview-product-tag style="color: ${tokens.muted_text};">${p.tag}</div>
                        <div class="font-bold font-mono text-xs" data-preview-product-price style="color: ${tokens.accent};">MXN $${parseFloat(p.price).toFixed(2)}</div>
                        <button type="button" class="mt-2 w-full py-1 rounded text-[10px] font-bold text-white cursor-pointer" data-preview-product-btn style="background: ${tokens.primary};">Añadir</button>
                    </div>
                `).join('');
            }
        }
    };

    window.switchStoreUniverse = function(universe) {
        activeUniverse = universe;
        const data = storefrontsData[universe];
        if (!data) return;

        // 1. Selector de Vitrinas Global: Activar botón correspondiente
        const universeButtons = document.querySelectorAll('[data-action="switch-store-universe"]');
        universeButtons.forEach(btn => {
            if (btn.getAttribute('data-universe') === universe) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // 2. Actualizar Badge de Contexto Global en barra de universos
        const contextBadge = document.getElementById('activeUniverseContextBadge');
        if (contextBadge) {
            contextBadge.textContent = `Vitrina Activa: ${data.name} (${universe.toUpperCase()})`;
            contextBadge.style.borderColor = data.accentColor || '#4f46e5';
            contextBadge.style.color = data.accentColor || '#4f46e5';
        }

        // 3. Actualizar KPIs reactivos en Header
        const kpiSkusCount = document.querySelector('[data-kpi-skus-count]');
        const kpiSkusBrand = document.querySelector('[data-kpi-skus-brand]');
        const kpiPreviewUniverse = document.querySelector('[data-kpi-preview-universe]');
        if (kpiSkusCount && data.activeSkus !== undefined) {
            kpiSkusCount.textContent = `${data.activeSkus} SKUs`;
        }
        if (kpiSkusBrand) {
            kpiSkusBrand.textContent = data.name;
        }
        if (kpiPreviewUniverse) {
            kpiPreviewUniverse.textContent = data.name;
        }

        // 4. Formulario de configuración de vitrina (Editor Visual)
        const vitrinaForm = document.getElementById('vitrinaConfigForm');
        const vitrinaTitle = document.getElementById('vitrinaEditorTitle');
        const vitrinaKicker = document.getElementById('vitrinaEditorKicker');

        if (vitrinaForm) vitrinaForm.action = `/admin/tienda-cms/vitrinas/${universe}`;
        if (vitrinaTitle) vitrinaTitle.textContent = `Personalizar Vitrina: ${data.name}`;
        if (vitrinaKicker) vitrinaKicker.textContent = data.kicker;

        // 4.1 Identidad inputs
        const nameInput = document.querySelector('[data-store-name-input]');
        const kickerInput = document.querySelector('[data-store-kicker-input]');
        if (nameInput) nameInput.value = data.name || '';
        if (kickerInput) kickerInput.value = data.kicker || '';

        // 4.2 Logos & Favicon inputs & previews
        const logoInput = document.querySelector('[data-store-logo-input]');
        const logoMobileInput = document.querySelector('[data-store-logo-mobile-input]');
        const faviconInput = document.querySelector('[data-store-favicon-input]');
        const logoThumb = document.querySelector('[data-preview-thumb="logo"]');
        const logoMobileThumb = document.querySelector('[data-preview-thumb="logo-mobile"]');
        const faviconThumb = document.querySelector('[data-preview-thumb="favicon"]');

        if (logoInput) logoInput.value = data.logoUrl || '';
        if (logoMobileInput) logoMobileInput.value = data.logoMobileUrl || '';
        if (faviconInput) faviconInput.value = data.faviconUrl || '';

        if (logoThumb) {
            logoThumb.innerHTML = data.logoUrl ? `<img src="${data.logoUrl}" class="max-h-full max-w-full object-contain" alt="Logo">` : `<span class="text-[9px] text-slate-400 font-mono">None</span>`;
        }
        if (logoMobileThumb) {
            logoMobileThumb.innerHTML = data.logoMobileUrl ? `<img src="${data.logoMobileUrl}" class="max-h-full max-w-full object-contain" alt="Logo Móvil">` : `<span class="text-[9px] text-slate-400 font-mono">None</span>`;
        }
        if (faviconThumb) {
            faviconThumb.innerHTML = data.faviconUrl ? `<img src="${data.faviconUrl}" class="w-4 h-4 object-contain" alt="Favicon">` : `<span class="text-[8px] text-slate-400 font-mono">.ico</span>`;
        }

        // 4.3 Paleta Multitoken de 8 colores
        const tokensMap = {
            'primary': data.primaryColor || '#0f172a',
            'secondary': data.secondaryColor || '#334155',
            'accent': data.accentColor || '#4f46e5',
            'background': data.backgroundColor || '#ffffff',
            'surface': data.surfaceColor || '#ffffff',
            'text': data.textColor || '#0f172a',
            'muted_text': data.mutedTextColor || '#64748b',
            'border': data.borderColor || '#e2e8f0'
        };

        Object.keys(tokensMap).forEach(token => {
            const picker = document.querySelector(`[data-color-token="${token}"]`);
            const label = document.querySelector(`[data-color-label="${token}"]`);
            const val = tokensMap[token];
            if (picker) picker.value = val;
            if (label) label.value = val;
        });

        // 5. Actualizar Live Preview Frame
        window.applyStorefrontPreviewTokens(tokensMap, data);

        // 6. Actualizar KPI 3 Swatches
        Object.keys(tokensMap).forEach(token => {
            const swatch = document.querySelector(`[data-kpi-token="${token}"]`);
            if (swatch) swatch.style.background = tokensMap[token];
        });

        // 7. Filtrar o destacar filas de Banners y Secciones asociadas a esta vitrina
        document.querySelectorAll('tr[data-banner-universe]').forEach(row => {
            const bUniverse = row.getAttribute('data-banner-universe');
            if (!bUniverse || bUniverse === 'all' || bUniverse === universe) {
                row.style.opacity = '1';
                row.classList.remove('bg-slate-100/30');
            } else {
                row.style.opacity = '0.45';
                row.classList.add('bg-slate-100/30');
            }
        });

        // 8. Reflejar universo en URL (sin recargar)
        try {
            const url = new URL(window.location);
            url.searchParams.set('universe', universe);
            window.history.replaceState({}, '', url);
        } catch (e) {}
    };

    // ==========================================
    // 3B. HERO & RUTAS HELPERS (TASK-H2-HERO-ENGINE)
    // ==========================================
    window.copyStoreRoute = function(text) {
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-5 right-5 z-50 bg-slate-900 text-white px-4 py-2.5 rounded-xl shadow-2xl font-sans text-xs flex items-center gap-2 border border-slate-700 animate-bounce';
            toast.innerHTML = `<svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Ruta copiada al portapapeles: <strong>${text}</strong></span>`;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.transition = 'opacity 0.4s ease';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 400);
            }, 2200);
        }).catch(err => {
            prompt('Copia la ruta:', text);
        });
    };

    window.togglePageImageMode = function(mode, context) {
        const pfx = context === 'edit' ? 'edit_' : 'create_';
        const customWrap = document.getElementById(pfx + 'page_custom_images_wrap');
        const catalogInfo = document.getElementById(pfx + 'page_catalog_images_info');
        if (mode === 'catalog') {
            if (customWrap) customWrap.classList.add('hidden');
            if (catalogInfo) catalogInfo.classList.remove('hidden');
        } else {
            if (customWrap) customWrap.classList.remove('hidden');
            if (catalogInfo) catalogInfo.classList.add('hidden');
        }
        window.updatePageModalPreview(context);
    };

    window.applyPageGradientPreset = function(preset, context) {
        const pfx = context === 'edit' ? 'edit_' : 'create_';
        const colorStart = document.getElementById(pfx + 'overlay_color_start');
        const colorStartTxt = document.getElementById(pfx + 'overlay_color_start_txt');
        const opStart = document.getElementById(pfx + 'overlay_opacity_start');
        const opStartVal = document.getElementById(pfx + 'overlay_opacity_start_val');

        const colorEnd = document.getElementById(pfx + 'overlay_color_end');
        const colorEndTxt = document.getElementById(pfx + 'overlay_color_end_txt');
        const opEnd = document.getElementById(pfx + 'overlay_opacity_end');
        const opEndVal = document.getElementById(pfx + 'overlay_opacity_end_val');

        const textColor = document.getElementById(pfx + 'page_text_color');

        const presets = {
            noir: { start: '#000000', end: '#000000', opStart: 25, opEnd: 85, text: '#ffffff' },
            wine: { start: '#4a0404', end: '#0f0505', opStart: 30, opEnd: 90, text: '#ffffff' },
            indigo: { start: '#1e1b4b', end: '#09090b', opStart: 30, opEnd: 90, text: '#ffffff' },
            emerald: { start: '#064e3b', end: '#022c22', opStart: 30, opEnd: 90, text: '#ffffff' },
            amber: { start: '#78350f', end: '#1c1917', opStart: 30, opEnd: 90, text: '#fef08a' },
            high_contrast: { start: '#000000', end: '#000000', opStart: 60, opEnd: 95, text: '#fde047' },
        };

        const cfg = presets[preset] || presets.noir;
        if (colorStart) colorStart.value = cfg.start;
        if (colorStartTxt) colorStartTxt.value = cfg.start;
        if (opStart) opStart.value = cfg.opStart;
        if (opStartVal) opStartVal.textContent = cfg.opStart + '%';

        if (colorEnd) colorEnd.value = cfg.end;
        if (colorEndTxt) colorEndTxt.value = cfg.end;
        if (opEnd) opEnd.value = cfg.opEnd;
        if (opEndVal) opEndVal.textContent = cfg.opEnd + '%';

        if (textColor) textColor.value = cfg.text;

        window.updatePageModalPreview(context);
    };

    window.updatePageModalPreview = function(context) {
        const pfx = context === 'edit' ? 'edit_' : 'create_';
        const card = document.getElementById(pfx + 'preview_card');
        const bg = document.getElementById(pfx + 'preview_bg');
        const overlay = document.getElementById(pfx + 'preview_overlay');
        const titleEl = document.getElementById(pfx + 'preview_title');
        const badgeEl = document.getElementById(pfx + 'preview_badge');

        const inputTitle = document.querySelector(context === 'edit' ? '#edit_page_title' : '#createPageModal input[name="title"]');
        const inputImg = document.getElementById(pfx + 'page_image_url');
        const cStart = document.getElementById(pfx + 'overlay_color_start')?.value || '#000000';
        const cEnd = document.getElementById(pfx + 'overlay_color_end')?.value || '#000000';
        const opStart = (parseInt(document.getElementById(pfx + 'overlay_opacity_start')?.value || '25', 10) / 100);
        const opEnd = (parseInt(document.getElementById(pfx + 'overlay_opacity_end')?.value || '85', 10) / 100);
        const txtColor = document.getElementById(pfx + 'page_text_color')?.value || '#ffffff';

        function hexToRgba(hex, alpha) {
            let c = (hex || '#000000').replace('#', '');
            if (c.length === 3) c = c.split('').map(x => x + x).join('');
            const num = parseInt(c, 16);
            return `rgba(${(num >> 16) & 255}, ${(num >> 8) & 255}, ${num & 255}, ${alpha})`;
        }

        if (bg) {
            const url = inputImg ? inputImg.value.trim() : '';
            if (url) {
                bg.style.backgroundImage = `url("${url}")`;
            } else {
                bg.style.backgroundImage = 'none';
            }
        }

        if (overlay) {
            overlay.style.background = `linear-gradient(180deg, ${hexToRgba(cStart, opStart)} 0%, ${hexToRgba(cEnd, opEnd)} 100%)`;
        }

        if (titleEl) {
            titleEl.textContent = inputTitle && inputTitle.value.trim() ? inputTitle.value : 'Título de Página';
            titleEl.style.color = txtColor;
        }

        if (badgeEl) {
            badgeEl.style.color = txtColor;
            badgeEl.style.borderColor = hexToRgba(txtColor, 0.4);
        }
    };

    window.openEditPageModal = function(page) {
        if (!page) return;
        const form = document.getElementById('editPageForm');
        if (form) {
            form.action = `/admin/tienda-cms/paginas/${page.id}`;
        }
        const titleEl = document.getElementById('edit_page_title');
        const slugEl = document.getElementById('edit_page_slug');
        const contentEl = document.getElementById('edit_page_content');
        const inNavbarEl = document.getElementById('edit_page_show_in_navbar');
        const inMobileEl = document.getElementById('edit_page_show_in_mobile');
        const inFooterEl = document.getElementById('edit_page_show_in_footer');
        const navOrderEl = document.getElementById('edit_page_navbar_order');
        const footerOrderEl = document.getElementById('edit_page_footer_order');
        const metaTitleEl = document.getElementById('edit_page_meta_title');
        const metaDescEl = document.getElementById('edit_page_meta_description');
        const statusEl = document.getElementById('edit_page_status');
        const imageUrlEl = document.getElementById('edit_page_image_url');
        const imageModeEl = document.getElementById('edit_page_image_mode');
        const galleryUrlsEl = document.getElementById('edit_page_gallery_images_urls');
        const universeEl = document.getElementById('edit_page_filter_universe');
        const categoryEl = document.getElementById('edit_page_filter_category');
        const tagsEl = document.getElementById('edit_page_filter_tags');

        // Color & Opacity controls
        const colorStartEl = document.getElementById('edit_overlay_color_start');
        const colorStartTxtEl = document.getElementById('edit_overlay_color_start_txt');
        const opStartEl = document.getElementById('edit_overlay_opacity_start');
        const opStartValEl = document.getElementById('edit_overlay_opacity_start_val');
        const colorEndEl = document.getElementById('edit_overlay_color_end');
        const colorEndTxtEl = document.getElementById('edit_overlay_color_end_txt');
        const opEndEl = document.getElementById('edit_overlay_opacity_end');
        const opEndValEl = document.getElementById('edit_overlay_opacity_end_val');
        const textColorEl = document.getElementById('edit_page_text_color');

        if (titleEl) titleEl.value = page.title || '';
        if (slugEl) slugEl.value = page.slug || '';
        if (contentEl) contentEl.value = page.content_html || '';
        if (inNavbarEl) inNavbarEl.checked = Boolean(page.show_in_navbar);
        if (inMobileEl) inMobileEl.checked = Boolean(page.show_in_mobile);
        if (inFooterEl) inFooterEl.checked = Boolean(page.show_in_footer);
        if (navOrderEl) navOrderEl.value = page.navbar_order ?? 0;
        if (footerOrderEl) footerOrderEl.value = page.footer_order ?? 0;
        if (metaTitleEl) metaTitleEl.value = page.seo_title || page.meta_title || '';
        if (metaDescEl) metaDescEl.value = page.seo_description || page.meta_description || '';
        if (statusEl) statusEl.value = page.status || 'active';
        if (imageUrlEl) imageUrlEl.value = page.image_url || '';

        const mode = page.image_source_mode || 'custom';
        if (imageModeEl) imageModeEl.value = mode;
        window.togglePageImageMode(mode, 'edit');

        if (galleryUrlsEl) {
            if (Array.isArray(page.gallery_images)) {
                galleryUrlsEl.value = page.gallery_images.join('\n');
            } else if (typeof page.gallery_images === 'string') {
                galleryUrlsEl.value = page.gallery_images;
            } else {
                galleryUrlsEl.value = '';
            }
        }

        const cStart = page.overlay_color_start || '#000000';
        const cEnd = page.overlay_color_end || '#000000';
        const opStart = page.overlay_opacity_start !== undefined && page.overlay_opacity_start !== null ? page.overlay_opacity_start : 25;
        const opEnd = page.overlay_opacity_end !== undefined && page.overlay_opacity_end !== null ? page.overlay_opacity_end : 85;
        const txtColor = page.text_color || '#ffffff';

        if (colorStartEl) colorStartEl.value = cStart;
        if (colorStartTxtEl) colorStartTxtEl.value = cStart;
        if (opStartEl) opStartEl.value = opStart;
        if (opStartValEl) opStartValEl.textContent = opStart + '%';

        if (colorEndEl) colorEndEl.value = cEnd;
        if (colorEndTxtEl) colorEndTxtEl.value = cEnd;
        if (opEndEl) opEndEl.value = opEnd;
        if (opEndValEl) opEndValEl.textContent = opEnd + '%';

        if (textColorEl) textColorEl.value = txtColor;

        if (universeEl) universeEl.value = page.filter_universe || '';
        if (categoryEl) categoryEl.value = page.filter_category || '';
        const supplierEl = document.getElementById('edit_page_filter_supplier');
        if (supplierEl) supplierEl.value = page.filter_supplier || '';
        if (tagsEl) tagsEl.value = page.filter_tags || '';

        window.updatePageModalPreview('edit');
        window.openStoreModal('editPageModal');
    };

    window.toggleHeroModeCreate = function(mode) {
        const lblStruct = document.getElementById('createHeroModeStructuredLbl');
        const lblCustom = document.getElementById('createHeroModeCustomLbl');
        const buttonsWrap = document.getElementById('createHeroButtonsWrap');
        const customCodeWrap = document.getElementById('createCustomCodeWrap');

        if (mode === 'custom') {
            if (lblCustom) lblCustom.className = 'flex-1 py-2 rounded-lg bg-white text-indigo-700 shadow-xs text-center cursor-pointer transition font-semibold';
            if (lblStruct) lblStruct.className = 'flex-1 py-2 rounded-lg text-slate-600 hover:text-slate-900 text-center cursor-pointer transition font-semibold';
            if (buttonsWrap) { buttonsWrap.classList.add('hidden'); buttonsWrap.style.display = 'none'; }
            if (customCodeWrap) { customCodeWrap.classList.remove('hidden'); customCodeWrap.style.display = 'block'; }
        } else {
            if (lblStruct) lblStruct.className = 'flex-1 py-2 rounded-lg bg-white text-indigo-700 shadow-xs text-center cursor-pointer transition font-semibold';
            if (lblCustom) lblCustom.className = 'flex-1 py-2 rounded-lg text-slate-600 hover:text-slate-900 text-center cursor-pointer transition font-semibold';
            if (buttonsWrap) { buttonsWrap.classList.remove('hidden'); buttonsWrap.style.display = 'block'; }
            if (customCodeWrap) { customCodeWrap.classList.add('hidden'); customCodeWrap.style.display = 'none'; }
        }
    };

    window.toggleHeroModeEdit = function(mode) {
        const lblStruct = document.getElementById('editHeroModeStructuredLbl');
        const lblCustom = document.getElementById('editHeroModeCustomLbl');
        const buttonsWrap = document.getElementById('editHeroButtonsWrap');
        const customCodeWrap = document.getElementById('editCustomCodeWrap');
        const radioStruct = document.getElementById('editHeroModeStructured');
        const radioCustom = document.getElementById('editHeroModeCustom');

        if (mode === 'custom') {
            if (radioCustom) radioCustom.checked = true;
            if (lblCustom) lblCustom.className = 'flex-1 py-2 rounded-lg bg-white text-indigo-700 shadow-xs text-center cursor-pointer transition font-semibold';
            if (lblStruct) lblStruct.className = 'flex-1 py-2 rounded-lg text-slate-600 hover:text-slate-900 text-center cursor-pointer transition font-semibold';
            if (buttonsWrap) { buttonsWrap.classList.add('hidden'); buttonsWrap.style.display = 'none'; }
            if (customCodeWrap) { customCodeWrap.classList.remove('hidden'); customCodeWrap.style.display = 'block'; }
        } else {
            if (radioStruct) radioStruct.checked = true;
            if (lblStruct) lblStruct.className = 'flex-1 py-2 rounded-lg bg-white text-indigo-700 shadow-xs text-center cursor-pointer transition font-semibold';
            if (lblCustom) lblCustom.className = 'flex-1 py-2 rounded-lg text-slate-600 hover:text-slate-900 text-center cursor-pointer transition font-semibold';
            if (buttonsWrap) { buttonsWrap.classList.remove('hidden'); buttonsWrap.style.display = 'block'; }
            if (customCodeWrap) { customCodeWrap.classList.add('hidden'); customCodeWrap.style.display = 'none'; }
        }
    };

    window.previewHeroBgImage = function(fileInput, previewContainerId) {
        if (!fileInput || !fileInput.files || !fileInput.files[0]) return;
        const file = fileInput.files[0];
        const container = document.getElementById(previewContainerId);
        if (!container) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = container.querySelector('img');
            const span = container.querySelector('span');
            if (img) img.src = e.target.result;
            if (span) span.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            container.classList.remove('hidden');
            container.style.display = 'flex';
        };
        reader.readAsDataURL(file);
    };

    window.previewHeroBgUrl = function(url, previewContainerId) {
        const container = document.getElementById(previewContainerId);
        if (!container) return;
        if (!url || !url.trim()) {
            container.classList.add('hidden');
            container.style.display = 'none';
            return;
        }
        const img = container.querySelector('img');
        const span = container.querySelector('span');
        if (img) img.src = url;
        if (span) span.textContent = url;
        container.classList.remove('hidden');
        container.style.display = 'flex';
    };

    window.clearHeroBgPreview = function(modalId, previewContainerId, urlInputId) {
        const container = document.getElementById(previewContainerId);
        if (container) {
            container.classList.add('hidden');
            container.style.display = 'none';
            const img = container.querySelector('img');
            if (img) img.src = '';
        }
        if (urlInputId) {
            const urlInput = document.getElementById(urlInputId);
            if (urlInput) urlInput.value = '';
        }
        const modal = document.getElementById(modalId);
        if (modal) {
            const fileInput = modal.querySelector('input[type="file"][name="background_image_file"]');
            if (fileInput) fileInput.value = '';
        }
    };

    window.updateHeroGradientPreview = function(prefix) {
        const start = document.getElementById(prefix + 'GradColorStart')?.value || '#0f172a';
        const end = document.getElementById(prefix + 'GradColorEnd')?.value || '#1e1b4b';
        const dir = document.getElementById(prefix + 'GradDir')?.value || 'to bottom';
        const bar = document.getElementById(prefix + 'GradPreviewBar');
        if (bar) {
            bar.style.background = `linear-gradient(${dir}, ${start}, ${end})`;
        }
    };



    window.toggleEditTypePanels = function(typeVal) {
        const contentTypes = ['hero_carousel', 'editorial_banner', 'brand_pillars', 'promo_modal', 'custom_html_css_js'];
        const editCatTax = document.getElementById('editCatalogTaxonomies');
        const editContOpt = document.getElementById('editContentOptions');
        const editHeroWrap = document.getElementById('editHeroEngineWrap');

        if (contentTypes.includes(typeVal)) {
            if (editCatTax) { editCatTax.classList.add('hidden'); editCatTax.style.display = 'none'; }
            if (editContOpt) { editContOpt.classList.remove('hidden'); editContOpt.style.display = 'block'; }
            if (editHeroWrap) {
                if (typeVal === 'hero_carousel') {
                    editHeroWrap.classList.remove('hidden');
                    editHeroWrap.style.display = 'block';
                } else {
                    editHeroWrap.classList.add('hidden');
                    editHeroWrap.style.display = 'none';
                }
            }
        } else {
            if (editCatTax) { editCatTax.classList.remove('hidden'); editCatTax.style.display = 'block'; }
            if (editContOpt) { editContOpt.classList.add('hidden'); editContOpt.style.display = 'none'; }
            if (editHeroWrap) { editHeroWrap.classList.add('hidden'); editHeroWrap.style.display = 'none'; }
        }
    };

    window.openCreateSectionModal = function() {
        window.openStoreModal('createSectionModal');
    };

    window.openEditSectionModal = function(secIdOrData) {
        let sec = null;
        if (typeof secIdOrData === 'number' || (typeof secIdOrData === 'string' && /^\d+$/.test(secIdOrData.trim()))) {
            const id = parseInt(secIdOrData);
            sec = sectionsData[id];
        } else if (typeof secIdOrData === 'object' && secIdOrData !== null) {
            sec = secIdOrData;
        } else if (typeof secIdOrData === 'string') {
            try {
                sec = JSON.parse(secIdOrData);
            } catch (err) {
                const id = parseInt(secIdOrData);
                if (!isNaN(id) && sectionsData[id]) {
                    sec = sectionsData[id];
                } else {
                    console.error('[Store CMS] Error al parsear JSON de sección:', err);
                    return;
                }
            }
        }

        if (!sec || !sec.id) {
            console.error('[Store CMS] No se pudo encontrar la sección para editar:', secIdOrData);
            return;
        }

        const form = document.getElementById('editSectionForm');
        if (form) form.action = `/admin/tienda-cms/secciones/${sec.id}`;

        const universeSel = document.getElementById('editUniverseSelect');
        if (universeSel) universeSel.value = sec.universe || 'all';

        const statusSel = document.getElementById('editStatusSelect');
        if (statusSel) statusSel.value = sec.status || 'active';

        const typeSel = document.getElementById('editTypeSelect');
        if (typeSel) {
            typeSel.value = sec.type || 'curated_products';
            window.toggleEditTypePanels(typeSel.value);
        }

        const nameInp = document.getElementById('editNameInput');
        if (nameInp) nameInp.value = sec.name || '';

        const badgeInp = document.getElementById('editBadgeInput');
        if (badgeInp) badgeInp.value = sec.badge || '';

        const titleInp = document.getElementById('editTitleInput');
        if (titleInp) titleInp.value = sec.title || '';

        const subtitleInp = document.getElementById('editSubtitleInput');
        if (subtitleInp) subtitleInp.value = sec.subtitle || '';

        // Taxonomías
        let qr = sec.query_rules || {};
        if (typeof qr === 'string') {
            try {
                qr = JSON.parse(qr);
            } catch (e) {
                qr = {};
            }
        }
        const catSel = document.getElementById('editCategoryId');
        if (catSel) catSel.value = qr.category_id || '';

        const scatSel = document.getElementById('editSubcategoryId');
        if (scatSel) scatSel.value = qr.subcategory_id || '';

        const brandSel = document.getElementById('editBrand');
        if (brandSel) brandSel.value = qr.brand || '';

        const supSel = document.getElementById('editSupplier');
        if (supSel) supSel.value = qr.supplier || '';

        const famSel = document.getElementById('editFamily');
        if (famSel) famSel.value = qr.family || '';

        const gtSel = document.getElementById('editGarmentType');
        if (gtSel) gtSel.value = qr.garment_type || '';

        const tagSel = document.getElementById('editTag');
        if (tagSel) tagSel.value = qr.tag || '';

        const discSel = document.getElementById('editDiscount');
        if (discSel) discSel.value = qr.discount || 'all';

        const limSel = document.getElementById('editLimit');
        if (limSel) limSel.value = qr.limit || 8;

        // Código custom
        const htmlArea = document.getElementById('editCustomHtml');
        if (htmlArea) htmlArea.value = sec.custom_html || '';

        const cssArea = document.getElementById('editCustomCss');
        if (cssArea) cssArea.value = sec.custom_css || '';

        const jsArea = document.getElementById('editCustomJs');
        if (jsArea) jsArea.value = sec.custom_js || '';

        // Settings del Hero & Vitrina
        let st = sec.settings || {};
        if (typeof st === 'string') {
            try { st = JSON.parse(st); } catch(e) { st = {}; }
        }

        const heroMode = st.hero_mode || (!sec.title && sec.custom_html ? 'custom' : 'structured');
        window.toggleHeroModeEdit(heroMode);

        const editFullWidth = document.getElementById('editFullWidth');
        if (editFullWidth) editFullWidth.checked = (st.full_width === 1 || st.full_width === '1' || st.full_width === true);

        const editHeroBgUrl = document.getElementById('editHeroBgUrl');
        if (editHeroBgUrl) {
            editHeroBgUrl.value = st.background_image || '';
            if (st.background_image) {
                window.previewHeroBgUrl(st.background_image, 'editHeroBgPreview');
            } else {
                window.clearHeroBgPreview('editSectionModal', 'editHeroBgPreview', 'editHeroBgUrl');
            }
        }

        const editEnableGrad = document.getElementById('editEnableGradient');
        const editGradControls = document.getElementById('editGradientControls');
        const hasGrad = (st.enable_gradient === 1 || st.enable_gradient === '1' || st.enable_gradient === true);
        if (editEnableGrad) editEnableGrad.checked = hasGrad;
        if (editGradControls) editGradControls.style.display = hasGrad ? 'block' : 'none';

        const editGradStart = document.getElementById('editGradColorStart');
        const editGradStartPicker = document.getElementById('editGradColorStartPicker');
        if (editGradStart) editGradStart.value = st.gradient_color_start || '#0f172a';
        if (editGradStartPicker) editGradStartPicker.value = st.gradient_color_start || '#0f172a';

        const editGradEnd = document.getElementById('editGradColorEnd');
        const editGradEndPicker = document.getElementById('editGradColorEndPicker');
        if (editGradEnd) editGradEnd.value = st.gradient_color_end || '#1e1b4b';
        if (editGradEndPicker) editGradEndPicker.value = st.gradient_color_end || '#1e1b4b';

        const editGradDir = document.getElementById('editGradDir');
        if (editGradDir) editGradDir.value = st.gradient_direction || 'to bottom';

        const editGradOpacity = document.getElementById('editGradOpacity');
        const editGradOpacityVal = document.getElementById('editGradOpacityVal');
        const opVal = st.gradient_opacity || 80;
        if (editGradOpacity) editGradOpacity.value = opVal;
        if (editGradOpacityVal) editGradOpacityVal.textContent = opVal + '%';
        window.updateHeroGradientPreview('edit');

        // Botones CTA
        const ctaPriText = document.getElementById('editCtaPrimaryText');
        if (ctaPriText) ctaPriText.value = st.cta_primary_text || st.cta_text || qr.cta_text || '';

        const ctaPriUrl = document.getElementById('editCtaPrimaryUrl');
        if (ctaPriUrl) ctaPriUrl.value = st.cta_primary_url || st.cta_url || qr.cta_url || '';

        const ctaSecText = document.getElementById('editCtaSecondaryText');
        if (ctaSecText) ctaSecText.value = st.cta_secondary_text || 'GUÍA DE TALLAS TORZO';

        const ctaSecUrl = document.getElementById('editCtaSecondaryUrl');
        if (ctaSecUrl) ctaSecUrl.value = st.cta_secondary_url || '';

        window.openStoreModal('editSectionModal');
    };

    window.archiveStoreSection = function(secId, secName) {
        if (confirm(`¿Archivar la sección "${secName || 'seleccionada'}"? Pasará a estado archivado y dejará de mostrarse en la tienda.`)) {
            const archiveForm = document.getElementById('archiveSectionForm');
            if (archiveForm && secId) {
                archiveForm.action = `/admin/tienda-cms/secciones/${secId}/archivar`;
                archiveForm.submit();
            }
        }
    };

    window.toggleSectionStatus = function(secId) {
        const form = document.getElementById('toggleSectionForm');
        if (form && secId) {
            form.action = `/admin/tienda-cms/secciones/${secId}/toggle`;
            form.submit();
        }
    };

    window.deleteSectionDirect = function(secId, secName) {
        if (confirm(`¿Estás seguro de eliminar la sección "${secName || 'seleccionada'}"?`)) {
            const form = document.getElementById('deleteSectionForm');
            if (form && secId) {
                form.action = `/admin/tienda-cms/secciones/${secId}`;
                form.submit();
            }
        }
    };

    window.moveSectionRow = function(btn, direction) {
        const tr = btn.closest('tr');
        if (!tr) return;
        if (direction === -1 && tr.previousElementSibling) {
            tr.parentNode.insertBefore(tr, tr.previousElementSibling);
            window.syncSectionsOrder();
        } else if (direction === 1 && tr.nextElementSibling) {
            tr.parentNode.insertBefore(tr.nextElementSibling, tr);
            window.syncSectionsOrder();
        }
    };

    // ==========================================
    // 5. SINCRONIZACIÓN DRAG & DROP DE SECCIONES
    // ==========================================
    window.syncSectionsOrder = function() {
        const sectionsBody = document.getElementById('sectionsSortableBody');
        const syncBadge = document.getElementById('reorderSyncStatus');
        if (!sectionsBody) return;

        const rows = sectionsBody.querySelectorAll('tr[data-section-id]');
        const order = [];
        rows.forEach((row, idx) => {
            const id = parseInt(row.getAttribute('data-section-id'));
            if (!isNaN(id)) order.push(id);
            const badge = row.querySelector('.section-order-badge');
            if (badge) badge.textContent = `#${idx + 1}`;
        });

        if (syncBadge) {
            syncBadge.classList.remove('hidden');
            syncBadge.className = 'text-[11px] font-semibold text-indigo-600 flex items-center gap-1.5 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-200 animate-pulse';
            syncBadge.innerHTML = `
                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span>Sincronizando orden...</span>
            `;
        }

        fetch('{{ route("portal.store.sections.reorder") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ order })
        }).then(res => res.json()).then(data => {
            if (syncBadge) {
                syncBadge.className = 'text-[11px] font-semibold text-emerald-700 flex items-center gap-1.5 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200';
                syncBadge.innerHTML = `
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Orden guardado exitosamente</span>
                `;
                setTimeout(() => {
                    syncBadge.classList.add('hidden');
                }, 3000);
            }
        }).catch(err => {
            console.error('[Store CMS] Error al sincronizar orden:', err);
            if (syncBadge) {
                syncBadge.className = 'text-[11px] font-semibold text-rose-700 flex items-center gap-1.5 bg-rose-50 px-2.5 py-1 rounded-full border border-rose-200';
                syncBadge.innerHTML = `<span>Error al guardar orden</span>`;
            }
        });
    };

    window.syncPagesOrder = function() {
        const pagesBody = document.getElementById('pagesSortableBody');
        if (!pagesBody) return;

        const rows = pagesBody.querySelectorAll('tr[data-page-id]');
        const order = [];
        rows.forEach((row, idx) => {
            const id = parseInt(row.getAttribute('data-page-id'), 10);
            if (!isNaN(id)) order.push(id);
            const badge = row.querySelector('.page-order-badge');
            if (badge) badge.textContent = `#${idx + 1}`;
        });

        fetch('{{ route("portal.store.pages.reorder") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ order })
        }).then(res => res.json()).then(data => {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-5 right-5 z-50 bg-emerald-900 text-white px-4 py-2.5 rounded-xl shadow-2xl font-sans text-xs flex items-center gap-2 border border-emerald-700 animate-bounce';
            toast.innerHTML = `<svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Orden de Páginas de Explorar sincronizado con éxito</span>`;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.transition = 'opacity 0.4s ease';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 400);
            }, 2500);
        }).catch(err => {
            console.error('[Store CMS] Error al sincronizar orden de páginas:', err);
        });
    };

    // ==========================================
    // 6. INICIALIZADOR DE INTERACCIÓN Y EVENTOS
    // ==========================================
    function initStoreCMS() {
        // Inicializar pestaña activa desde URL o default
        const urlParams = new URLSearchParams(window.location.search);
        const initialTab = urlParams.get('tab') || 'visual';
        const initialUniverse = urlParams.get('universe') || activeUniverse;
        window.switchStoreTab(initialTab);
        window.switchStoreUniverse(initialUniverse);

        // Listener en select de tipo en Modal Editar
        const editTypeSelect = document.getElementById('editTypeSelect');
        if (editTypeSelect) {
            editTypeSelect.addEventListener('change', function() {
                window.toggleEditTypePanels(this.value);
            });
        }

        // Listener en select de tipo contenido en Modal Crear
        const createTypeContent = document.getElementById('createSectionTypeContent');
        if (createTypeContent) {
            createTypeContent.addEventListener('change', function() {
                const createHeroWrap = document.getElementById('createHeroEngineWrap');
                if (createHeroWrap) {
                    if (this.value === 'hero_carousel') {
                        createHeroWrap.classList.remove('hidden');
                        createHeroWrap.style.display = 'block';
                    } else {
                        createHeroWrap.classList.add('hidden');
                        createHeroWrap.style.display = 'none';
                    }
                }
            });
        }

        // Inicializar SortableJS para Drag & Drop
        const sectionsBody = document.getElementById('sectionsSortableBody');
        if (sectionsBody) {
            if (typeof Sortable !== 'undefined') {
                new Sortable(sectionsBody, {
                    handle: '.drag-handle',
                    animation: 200,
                    ghostClass: 'bg-indigo-50/80',
                    chosenClass: 'bg-indigo-100/60',
                    dragClass: 'shadow-2xl',
                    onEnd: function () {
                        window.syncSectionsOrder();
                    }
                });
            }

            // Fallback nativo HTML5 Drag & Drop
            let draggedRow = null;
            sectionsBody.addEventListener('dragstart', function(e) {
                draggedRow = e.target.closest('tr[data-section-id]');
                if (draggedRow) {
                    if (e.dataTransfer) {
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', draggedRow.getAttribute('data-section-id'));
                    }
                    draggedRow.classList.add('opacity-50');
                }
            });
            sectionsBody.addEventListener('dragend', function() {
                if (draggedRow) {
                    draggedRow.classList.remove('opacity-50');
                    draggedRow = null;
                }
            });
            sectionsBody.addEventListener('dragover', function(e) {
                e.preventDefault();
                if (e.dataTransfer) e.dataTransfer.dropEffect = 'move';
                const targetRow = e.target.closest('tr[data-section-id]');
                if (targetRow && targetRow !== draggedRow && draggedRow) {
                    const bounding = targetRow.getBoundingClientRect();
                    const offset = e.clientY - bounding.top;
                    if (offset > bounding.height / 2) {
                        targetRow.parentNode.insertBefore(draggedRow, targetRow.nextSibling);
                    } else {
                        targetRow.parentNode.insertBefore(draggedRow, targetRow);
                    }
                }
            });
            sectionsBody.addEventListener('drop', function(e) {
                e.preventDefault();
                window.syncSectionsOrder();
            });
        }

        // Inicializar SortableJS para Drag & Drop de Páginas de Explorar
        const pagesBody = document.getElementById('pagesSortableBody');
        if (pagesBody) {
            if (typeof Sortable !== 'undefined') {
                new Sortable(pagesBody, {
                    handle: '.drag-handle-page',
                    animation: 200,
                    ghostClass: 'bg-indigo-50/80',
                    chosenClass: 'bg-indigo-100/60',
                    dragClass: 'shadow-2xl',
                    onEnd: function () {
                        window.syncPagesOrder();
                    }
                });
            }

            // Fallback nativo HTML5 Drag & Drop
            let draggedPageRow = null;
            pagesBody.addEventListener('dragstart', function(e) {
                draggedPageRow = e.target.closest('tr[data-page-id]');
                if (draggedPageRow) {
                    if (e.dataTransfer) {
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', draggedPageRow.getAttribute('data-page-id'));
                    }
                    draggedPageRow.classList.add('opacity-50');
                }
            });
            pagesBody.addEventListener('dragend', function() {
                if (draggedPageRow) {
                    draggedPageRow.classList.remove('opacity-50');
                    draggedPageRow = null;
                }
            });
            pagesBody.addEventListener('dragover', function(e) {
                e.preventDefault();
                if (e.dataTransfer) e.dataTransfer.dropEffect = 'move';
                const targetRow = e.target.closest('tr[data-page-id]');
                if (targetRow && targetRow !== draggedPageRow && draggedPageRow) {
                    const bounding = targetRow.getBoundingClientRect();
                    const offset = e.clientY - bounding.top;
                    if (offset > bounding.height / 2) {
                        targetRow.parentNode.insertBefore(draggedPageRow, targetRow.nextSibling);
                    } else {
                        targetRow.parentNode.insertBefore(draggedPageRow, targetRow);
                    }
                }
            });
            pagesBody.addEventListener('drop', function(e) {
                e.preventDefault();
                window.syncPagesOrder();
            });
        }

        // Inicializar SortableJS para Drag & Drop de Filtros de Catálogo (TASK-H2-18)
        const filtersBody = document.getElementById('catalogFiltersSortableBody');
        function reindexFilterInputs() {
            if (!filtersBody) return;
            const rows = filtersBody.querySelectorAll('tr.catalog-filter-row');
            rows.forEach((row, idx) => {
                const sortInput = row.querySelector('.filter-sort-input');
                if (sortInput) {
                    sortInput.value = idx + 1;
                }
            });
        }
        if (filtersBody) {
            if (typeof Sortable !== 'undefined') {
                new Sortable(filtersBody, {
                    handle: '.drag-handle',
                    animation: 200,
                    ghostClass: 'bg-indigo-50/80',
                    chosenClass: 'bg-indigo-100/60',
                    dragClass: 'shadow-2xl',
                    onEnd: function () {
                        reindexFilterInputs();
                    }
                });
            }

            // Fallback nativo HTML5 Drag & Drop
            let draggedFilterRow = null;
            filtersBody.addEventListener('dragstart', function(e) {
                draggedFilterRow = e.target.closest('tr.catalog-filter-row');
                if (draggedFilterRow) {
                    if (e.dataTransfer) {
                        e.dataTransfer.effectAllowed = 'move';
                    }
                    draggedFilterRow.classList.add('opacity-50');
                }
            });
            filtersBody.addEventListener('dragend', function() {
                if (draggedFilterRow) {
                    draggedFilterRow.classList.remove('opacity-50');
                    draggedFilterRow = null;
                }
            });
            filtersBody.addEventListener('dragover', function(e) {
                e.preventDefault();
                if (e.dataTransfer) e.dataTransfer.dropEffect = 'move';
                const targetRow = e.target.closest('tr.catalog-filter-row');
                if (targetRow && targetRow !== draggedFilterRow && draggedFilterRow) {
                    const bounding = targetRow.getBoundingClientRect();
                    const offset = e.clientY - bounding.top;
                    if (offset > bounding.height / 2) {
                        targetRow.parentNode.insertBefore(draggedFilterRow, targetRow.nextSibling);
                    } else {
                        targetRow.parentNode.insertBefore(draggedFilterRow, targetRow);
                    }
                }
            });
            filtersBody.addEventListener('drop', function(e) {
                e.preventDefault();
                reindexFilterInputs();
            });
        }

        // Formulario de Vitrina: Inputs reactivos a tiempo real (Identidad, Logos & 8 Tokens)
        const nameInput = document.querySelector('[data-store-name-input]');
        const kickerInput = document.querySelector('[data-store-kicker-input]');
        const logoInput = document.querySelector('[data-store-logo-input]');
        const logoFileInput = document.querySelector('[data-store-logo-file]');
        const logoMobileFileInput = document.querySelector('[data-store-logo-mobile-file]');
        const faviconFileInput = document.querySelector('[data-store-favicon-file]');

        if (nameInput) {
            nameInput.addEventListener('input', function() {
                const currentData = storefrontsData[activeUniverse] || {};
                currentData.name = this.value;
                const pTitle = document.querySelector('[data-preview-title]');
                const pBrand = document.querySelector('[data-preview-brand-name]');
                const pBadge = document.querySelector('[data-preview-universe-badge]');
                if (pTitle) pTitle.textContent = this.value;
                if (pBrand) pBrand.textContent = this.value;
                if (pBadge) pBadge.textContent = this.value;
            });
        }

        if (kickerInput) {
            kickerInput.addEventListener('input', function() {
                const currentData = storefrontsData[activeUniverse] || {};
                currentData.kicker = this.value;
                const pKBadge = document.querySelector('[data-preview-kicker-badge]');
                const pKText = document.querySelector('[data-preview-kicker-text]');
                if (pKBadge) pKBadge.textContent = this.value;
                if (pKText) pKText.textContent = this.value;
            });
        }

        if (logoInput) {
            logoInput.addEventListener('input', function() {
                const currentData = storefrontsData[activeUniverse] || {};
                currentData.logoUrl = this.value;
                const logoThumb = document.querySelector('[data-preview-thumb="logo"]');
                const logoContainer = document.querySelector('[data-preview-logo-container]');
                if (logoThumb) {
                    logoThumb.innerHTML = this.value ? `<img src="${this.value}" class="max-h-full max-w-full object-contain">` : `<span class="text-[9px] text-slate-400 font-mono">None</span>`;
                }
                if (logoContainer) {
                    if (this.value) {
                        logoContainer.innerHTML = `<img src="${this.value}" alt="Logo" class="h-5 max-w-[120px] object-contain" data-preview-logo-img>`;
                    } else {
                        logoContainer.innerHTML = `<span class="font-black tracking-tight text-white text-sm" data-preview-brand-name>${currentData.name || ''}</span>`;
                    }
                }
            });
        }

        // Live preview para subida de archivos (Logos & Favicon)
        function handleFilePreview(fileInput, thumbSelector, previewSelector) {
            if (!fileInput) return;
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const objectUrl = URL.createObjectURL(file);
                    const thumb = document.querySelector(thumbSelector);
                    if (thumb) thumb.innerHTML = `<img src="${objectUrl}" class="max-h-full max-w-full object-contain">`;
                    if (previewSelector) {
                        const previewEl = document.querySelector(previewSelector);
                        if (previewEl) previewEl.innerHTML = `<img src="${objectUrl}" class="h-5 max-w-[120px] object-contain">`;
                    }
                }
            });
        }
        handleFilePreview(logoFileInput, '[data-preview-thumb="logo"]', '[data-preview-logo-container]');
        handleFilePreview(logoMobileFileInput, '[data-preview-thumb="logo-mobile"]', null);
        handleFilePreview(faviconFileInput, '[data-preview-thumb="favicon"]', null);

        // Live preview reactivo para los 8 tokens cromáticos
        const colorTokensList = ['primary', 'secondary', 'accent', 'background', 'surface', 'text', 'muted_text', 'border'];
        colorTokensList.forEach(token => {
            const picker = document.querySelector(`[data-color-token="${token}"]`);
            const label = document.querySelector(`[data-color-label="${token}"]`);

            function updateToken(val) {
                if (picker) picker.value = val;
                if (label) label.value = val;
                const swatch = document.querySelector(`[data-kpi-token="${token}"]`);
                if (swatch) swatch.style.background = val;

                const currentData = storefrontsData[activeUniverse] || {};
                const tokens = {
                    'primary': document.querySelector('[data-color-token="primary"]')?.value || '#0f172a',
                    'secondary': document.querySelector('[data-color-token="secondary"]')?.value || '#334155',
                    'accent': document.querySelector('[data-color-token="accent"]')?.value || '#4f46e5',
                    'background': document.querySelector('[data-color-token="background"]')?.value || '#ffffff',
                    'surface': document.querySelector('[data-color-token="surface"]')?.value || '#ffffff',
                    'text': document.querySelector('[data-color-token="text"]')?.value || '#0f172a',
                    'muted_text': document.querySelector('[data-color-token="muted_text"]')?.value || '#64748b',
                    'border': document.querySelector('[data-color-token="border"]')?.value || '#e2e8f0'
                };
                window.applyStorefrontPreviewTokens(tokens, currentData);
            }

            if (picker) {
                picker.addEventListener('input', function() {
                    updateToken(this.value);
                });
            }
            if (label) {
                label.addEventListener('input', function() {
                    if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                        updateToken(this.value);
                    }
                });
            }
        });

        // Guardado asíncrono de Vitrina
        const vitrinaForm = document.getElementById('vitrinaConfigForm');
        if (vitrinaForm) {
            vitrinaForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const submitBtn = document.getElementById('saveVitrinaBtn');
                const origHtml = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Guardando cambios...</span>
                    `;
                }

                const formData = new FormData(vitrinaForm);

                fetch(vitrinaForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = `
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>¡Guardado Exitosamente!</span>
                        `;
                        setTimeout(() => {
                            submitBtn.innerHTML = origHtml;
                        }, 2500);
                    }
                    if (data.storefront) {
                        storefrontsData[activeUniverse] = Object.assign(storefrontsData[activeUniverse] || {}, data.storefront);
                        window.switchStoreUniverse(activeUniverse);
                    }
                })
                .catch(err => {
                    console.error('[Store CMS] Error al guardar vitrina:', err);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = origHtml;
                    }
                    vitrinaForm.submit();
                });
            });
        }

        // Preview Device Toggle (Desktop / Mobile)
        const deviceButtons = document.querySelectorAll('[data-action="set-preview-device"]');
        const previewFrame = document.querySelector('[data-storefront-preview-frame]');
        deviceButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const device = this.getAttribute('data-device');
                deviceButtons.forEach(b => {
                    b.classList.remove('bg-slate-900', 'text-white');
                    b.classList.add('text-slate-600');
                });
                this.classList.add('bg-slate-900', 'text-white');
                this.classList.remove('text-slate-600');

                if (previewFrame) {
                    previewFrame.classList.remove('desktop', 'mobile');
                    previewFrame.classList.add(device);
                }
            });
        });

        // Banners & Marquesina Toggles
        const announcementToggleBtn = document.querySelector('[data-action="toggle-announcement-bar"]');
        const announcementForm = document.getElementById('toggleAnnouncementForm');
        if (announcementToggleBtn && announcementForm) {
            announcementToggleBtn.addEventListener('click', function () {
                announcementForm.submit();
            });
        }

        const bannerToggleBtns = document.querySelectorAll('[data-action="toggle-banner-status"]');
        const bannerForm = document.getElementById('toggleBannerForm');
        bannerToggleBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const bannerId = this.getAttribute('data-banner-id');
                if (bannerId && bannerForm) {
                    bannerForm.action = `/admin/tienda-cms/banners/${bannerId}/toggle`;
                    bannerForm.submit();
                }
            });
        });

        // Simulador de elasticidad de envío
        const slider = document.querySelector('[data-action="simulate-threshold-slider"]');
        const thresholdVal = document.querySelector('[data-simulator-threshold-val]');
        const ratioVal = document.querySelector('[data-simulator-ratio-val]');
        const costVal = document.querySelector('[data-simulator-cost-val]');
        const marginVal = document.querySelector('[data-simulator-margin-val]');
        const diagBox = document.querySelector('[data-simulator-diag-box]');

        if (slider) {
            slider.addEventListener('input', function () {
                const t = parseFloat(this.value);
                if (thresholdVal) thresholdVal.textContent = `MXN $${t.toLocaleString()}`;

                const formThreshold = document.querySelector('[data-shipping-threshold-input]');
                if (formThreshold) formThreshold.value = t;

                const projectedOrders = 120;
                const ratio = Math.max(10, Math.min(85, Math.round(75 - ((t - 699) / 800) * 45)));
                const ordersAbsorbed = Math.round(projectedOrders * (ratio / 100));
                const absorbedCost = ordersAbsorbed * 145;
                const projectedGmv = projectedOrders * 680;
                const projectedMargin = Math.max(15, (52.0 - (absorbedCost / projectedGmv * 100))).toFixed(1);
                const isHealthy = projectedMargin >= 30.0;

                if (ratioVal) ratioVal.textContent = `${ratio}% de pedidos`;
                if (costVal) costVal.textContent = `MXN $${absorbedCost.toLocaleString()}`;
                if (marginVal) {
                    marginVal.textContent = `${projectedMargin}%`;
                    marginVal.className = `text-sm font-mono font-bold block mt-1 ${isHealthy ? 'text-emerald-600' : 'text-amber-600'}`;
                }
                if (diagBox) {
                    diagBox.className = `p-3.5 rounded-xl border text-xs ${isHealthy ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-amber-50 border-amber-200 text-amber-900'}`;
                    const p = diagBox.querySelector('p');
                    if (p) {
                        p.textContent = isHealthy
                            ? 'El umbral actual protege el margen operativo por encima del umbral mínimo de viabilidad (30% EBITDA) absorbiendo costos de paquetería sin comprometer flujo de caja.'
                            : 'Precaución: El umbral simulado compromete el margen neto debido a que una alta proporción de órdenes diluye la utilidad frente al flete courier de $145 MXN.';
                    }
                }
            });
        }
    }

    // ==========================================
    // 7. DELEGACIÓN GLOBAL DE EVENTOS DE CLICK
    // ==========================================
    document.addEventListener('click', function(e) {
        // Pestañas Subnav
        const tabBtn = e.target.closest('[data-action="switch-store-tab"]');
        if (tabBtn && !e.target.classList.contains('section-help-btn')) {
            const targetTab = tabBtn.getAttribute('data-tab');
            if (targetTab) window.switchStoreTab(targetTab);
            return;
        }

        // Vitrina Universo Pills
        const univBtn = e.target.closest('[data-action="switch-store-universe"]');
        if (univBtn) {
            const u = univBtn.getAttribute('data-universe');
            if (u) window.switchStoreUniverse(u);
            return;
        }

        // Botón Crear Sección
        if (e.target.closest('[data-action="open-create-section-modal"]')) {
            window.openCreateSectionModal();
            return;
        }

        // Botón Editar Sección
        const editBtn = e.target.closest('[data-action="open-edit-section-modal"]');
        if (editBtn) {
            const secId = editBtn.getAttribute('data-section-id');
            const secData = editBtn.getAttribute('data-section');
            window.openEditSectionModal(secId || secData);
            return;
        }

        // Archivar Sección
        const archiveBtn = e.target.closest('[data-action="archive-section"]');
        if (archiveBtn) {
            const secId = archiveBtn.getAttribute('data-section-id');
            const secName = archiveBtn.getAttribute('data-section-name');
            window.archiveStoreSection(secId, secName);
            return;
        }

        // Activar / Pausar Sección
        const toggleBtn = e.target.closest('[data-action="toggle-section-status"]');
        if (toggleBtn) {
            const secId = toggleBtn.getAttribute('data-section-id');
            window.toggleSectionStatus(secId);
            return;
        }

        // Eliminar Sección
        const deleteBtn = e.target.closest('[data-action="delete-section"]');
        if (deleteBtn) {
            const secId = deleteBtn.getAttribute('data-section-id');
            const secName = deleteBtn.getAttribute('data-section-name');
            window.deleteSectionDirect(secId, secName);
            return;
        }

        // Mover Sección Arriba / Abajo
        const moveUpBtn = e.target.closest('[data-action="move-section-up"]');
        if (moveUpBtn) {
            window.moveSectionRow(moveUpBtn, -1);
            return;
        }
        const moveDownBtn = e.target.closest('[data-action="move-section-down"]');
        if (moveDownBtn) {
            window.moveSectionRow(moveDownBtn, 1);
            return;
        }

        // Apertura de modales generales
        const openModalBtn = e.target.closest('[data-action="open-modal"]');
        if (openModalBtn) {
            const modalId = openModalBtn.getAttribute('data-modal');
            if (modalId) window.openStoreModal(modalId);
            return;
        }

        // Modales específicos editorial
        if (e.target.closest('[data-action="open-edit-size-guide-modal"]')) {
            window.openStoreModal('editSizeGuideModal');
            return;
        }
        if (e.target.closest('[data-action="open-edit-care-modal"]')) {
            window.openStoreModal('editCareModal');
            return;
        }
        if (e.target.closest('[data-action="open-edit-profeco-modal"]')) {
            window.openStoreModal('editProfecoModal');
            return;
        }

        // Cierre de modales (botón X o Cancelar o clic fuera del modal)
        const closeBtn = e.target.closest('[data-action="close-modal"]');
        if (closeBtn) {
            const modalId = closeBtn.getAttribute('data-modal');
            if (modalId) window.closeStoreModal(modalId);
            return;
        }

        // Clic en el backdrop oscuro del modal para cerrar
        if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0') && e.target.id) {
            window.closeStoreModal(e.target.id);
        }
    });

    // Tecla Escape cierra modales abiertos
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.fixed.inset-0:not(.hidden)');
            openModals.forEach(m => window.closeStoreModal(m.id));
        }
    });

    // ==========================================
    // 8. AUTO-BOOTSTRAP INMEDIATO
    // ==========================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initStoreCMS);
    } else {
        initStoreCMS();
    }
})();
</script>
@endpush
