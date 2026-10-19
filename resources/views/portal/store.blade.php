@extends('layouts.portal-panel')

@section('title', 'Tienda & CMS Multiverso')
@section('hub_kicker', 'Hub 2')
@section('hub_title', 'Tienda & CMS Multiverso')
@section('hub_status', '4 Vitrinas Activas')

@section('content')
@php
$curatedGoogleFonts = [
    'Outfit' => 'Outfit (Minimalista / Contemporánea / Lujo)',
    'Plus Jakarta Sans' => 'Plus Jakarta Sans (Geométrica / Moderna / UI)',
    'Inter' => 'Inter (Neo-grotesque / Ultra Limpia)',
    'Montserrat' => 'Montserrat (Urbana / Versátil / Moda)',
    'Poppins' => 'Poppins (Geométrica Fresca / Comercial)',
    'Playfair Display' => 'Playfair Display (Serif de Lujo / Editorial)',
    'Cinzel' => 'Cinzel (Serif Clásica / Joyería / Distinción)',
    'Cormorant Garamond' => 'Cormorant Garamond (Sensual / Alta Costura)',
    'Oswald' => 'Oswald (Condensada / Deportiva / Atlético)',
    'Anton' => 'Anton (Streetwear Bold / Contundente)',
    'Bebas Neue' => 'Bebas Neue (Display / Titulares Imponentes)',
    'Space Grotesk' => 'Space Grotesk (Vanguardista / Techwear)',
    'Syne' => 'Syne (Diseño de Autor / Vanguardia)',
    'Merriweather' => 'Merriweather (Serif de Lectura / Suave)',
    'Space Mono' => 'Space Mono (Monoespaciada / Técnica)',
];
@endphp
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
    @if(session('error') || $errors->any())
        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-start justify-between shadow-xs">
            <div class="flex flex-col gap-1">
                @if(session('error'))
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="text-sm font-semibold">{{ session('error') }}</span>
                    </div>
                @endif
                @if($errors->any())
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <ul class="text-sm font-medium list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
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

            <!-- TAB 6: GESTOR DE IMÁGENES (HUB 2) -->
            <button class="store-subnav-btn" data-action="switch-store-tab" data-tab="media" onclick="window.switchStoreTab('media')" type="button">
                <div class="flex items-center gap-2 overflow-hidden">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <span class="truncate">6. Gestor de Imágenes</span>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <span class="store-subnav-count" id="cmsMediaTabCount">{{ $cmsMediaCount ?? 0 }} Fotos</span>
                    <span class="section-help-btn" role="button" tabindex="0" aria-label="Ayuda Gestor de Imágenes" data-help-title="Gestor de Medios CMS (Hub 2)" data-help-desc="Almacén exclusivo de imágenes para banners, carruseles y contenido visual del CMS, independiente del catálogo de productos.">?</span>
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

                    <!-- 4. TIPOGRAFÍAS DE MARCA (GOOGLE FONTS) -->
                    <div class="space-y-3 pt-3 border-t border-slate-200">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                4. Tipografías de Marca (Google Fonts)
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium">Headings, bajadas y acentos</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                            <!-- Tipografía Títulos (H1, H2, H3) -->
                            <div class="p-2.5 border border-slate-200 rounded-lg bg-slate-50/50">
                                <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Títulos (H1, H2, H3)</label>
                                <select name="typography_heading" id="vitrinaTypoHeading" class="w-full px-2 py-1.5 text-xs font-medium border border-slate-200 rounded-md bg-white text-slate-800" onchange="window.loadGoogleFont && window.loadGoogleFont(this.value); window.updateVitrinaFontPreview && window.updateVitrinaFontPreview();">
                                    @foreach($curatedGoogleFonts as $fKey => $fLabel)
                                        <option value="{{ $fKey }}" {{ ($storefronts['marte']['typography_heading'] ?? 'Outfit') === $fKey ? 'selected' : '' }}>{{ $fLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipografía Bajadas / Subtítulos -->
                            <div class="p-2.5 border border-slate-200 rounded-lg bg-slate-50/50">
                                <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Bajadas / Subtítulos</label>
                                <select name="typography_subtitle" id="vitrinaTypoSubtitle" class="w-full px-2 py-1.5 text-xs font-medium border border-slate-200 rounded-md bg-white text-slate-800" onchange="window.loadGoogleFont && window.loadGoogleFont(this.value); window.updateVitrinaFontPreview && window.updateVitrinaFontPreview();">
                                    @foreach($curatedGoogleFonts as $fKey => $fLabel)
                                        <option value="{{ $fKey }}" {{ ($storefronts['marte']['typography_subtitle'] ?? 'Outfit') === $fKey ? 'selected' : '' }}>{{ $fLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipografía Párrafos / Texto Base -->
                            <div class="p-2.5 border border-slate-200 rounded-lg bg-slate-50/50">
                                <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Texto Base / Párrafos</label>
                                <select name="typography_body" id="vitrinaTypoBody" class="w-full px-2 py-1.5 text-xs font-medium border border-slate-200 rounded-md bg-white text-slate-800" onchange="window.loadGoogleFont && window.loadGoogleFont(this.value); window.updateVitrinaFontPreview && window.updateVitrinaFontPreview();">
                                    @foreach($curatedGoogleFonts as $fKey => $fLabel)
                                        <option value="{{ $fKey }}" {{ ($storefronts['marte']['typography_body'] ?? 'Plus Jakarta Sans') === $fKey ? 'selected' : '' }}>{{ $fLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipografía Acentos / Badges / Botones -->
                            <div class="p-2.5 border border-slate-200 rounded-lg bg-slate-50/50">
                                <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Acentos / Badges / Botones</label>
                                <select name="typography_accent" id="vitrinaTypoAccent" class="w-full px-2 py-1.5 text-xs font-medium border border-slate-200 rounded-md bg-white text-slate-800" onchange="window.loadGoogleFont && window.loadGoogleFont(this.value); window.updateVitrinaFontPreview && window.updateVitrinaFontPreview();">
                                    @foreach($curatedGoogleFonts as $fKey => $fLabel)
                                        <option value="{{ $fKey }}" {{ ($storefronts['marte']['typography_accent'] ?? 'Plus Jakarta Sans') === $fKey ? 'selected' : '' }}>{{ $fLabel }}</option>
                                    @endforeach
                                </select>
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
                                @if(in_array($sec->type, ['catalog_section', 'curated_products', 'vitrina_web']))
                                    <span class="px-2 py-1 rounded text-[10px] font-bold font-mono bg-purple-50 text-purple-700 border border-purple-200">
                                        Vitrina Web Hub 4
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded text-[10px] font-bold font-mono bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        {{ $sec->type }}
                                    </span>
                                @endif
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
                                    @if(in_array($sec->type, ['catalog_section', 'curated_products', 'vitrina_web']))
                                        <button class="button button-light py-1 px-2 text-[11px] font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg shadow-2xs cursor-pointer inline-flex items-center gap-1" data-action="open-edit-section-modal" data-section-id="{{ $sec->id }}" onclick="window.openEditSectionModal({{ $sec->id }})" type="button" title="Configurar vitrina y filtros de catálogo en Hub 4 (Abre en nueva pestaña)">
                                            <span>Editar Vitrina</span>
                                            <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </button>
                                    @else
                                        <button class="button button-light py-1 px-2 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-50 rounded-lg shadow-2xs cursor-pointer" data-action="open-edit-section-modal" data-section-id="{{ $sec->id }}" onclick="window.openEditSectionModal({{ $sec->id }})" type="button" title="Editar información, diseño visual y contenido">
                                            Editar
                                        </button>
                                    @endif
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

        <!-- SECCIÓN 1 ACORDEÓN: REGLAS DE FLETE & SIMULADOR FINANCIERO -->
        <div class="store-accordion-section border border-slate-200/80 rounded-2xl bg-white shadow-sm mb-4 overflow-hidden" id="accordionSecShipping">
            <button type="button" class="store-accordion-header w-full flex items-center justify-between p-4 bg-slate-50/70 hover:bg-slate-100/70 transition cursor-pointer text-left" onclick="window.toggleStoreAccordion('accordionSecShipping')">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">1. Reglas de Flete, Despacho &amp; Simulador Financiero (TASK-INT-18)</div>
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

        <!-- SECCIÓN 2 ACORDEÓN: PROTOCOLOS DE CUIDADO TEXTIL & AVISO PROFECO -->
        <div class="store-accordion-section border border-slate-200/80 rounded-2xl bg-white shadow-sm mb-4 overflow-hidden" id="accordionSecEditorial">
            <button type="button" class="store-accordion-header w-full flex items-center justify-between p-4 bg-slate-50/70 hover:bg-slate-100/70 transition cursor-pointer text-left" onclick="window.toggleStoreAccordion('accordionSecEditorial')">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">2. Protocolos de Cuidado Textil &amp; Aviso Normativo PROFECO</div>
                        <div class="text-[11px] text-slate-500">Protocolos de lavado de fibras sincronizados con Hub 4 y leyendas sanitarias PROFECO.</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded bg-amber-50 text-amber-700 font-mono">{{ count($careProtocols) }} Fibras</span>
                    <svg class="store-accordion-chevron w-5 h-5 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </button>
            <div class="store-accordion-body p-6 border-t border-slate-100 hidden" id="accordionSecEditorialBody" style="display: none;">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
            <!-- Cuidados de Lavado -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2 mb-3">
                    <h4 class="text-xs font-bold text-slate-900">Protocolos de Cuidado de Ropa Delicada</h4>
                    <button class="text-indigo-600 hover:text-indigo-800 text-[11px] font-bold cursor-pointer" data-action="open-edit-care-modal" type="button">Editar Protocolos</button>
                </div>
                <div class="space-y-2.5 text-xs">
                    @php
                        $careSymbolsLookup = \App\Models\CmsOfficialDocument::getStandardCareSymbols();
                    @endphp
                    @foreach($careProtocols as $c)
                        @php
                            $cSymbols = is_array($c['symbols'] ?? null) ? $c['symbols'] : [];
                        @endphp
                        <div class="p-3 bg-white border border-slate-200/80 rounded-lg shadow-2xs">
                            <div class="flex items-center justify-between">
                                <strong class="text-slate-900 font-bold text-xs">{{ $c['fiber'] }}</strong>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700">
                                    {{ count($cSymbols) }} {{ count($cSymbols) === 1 ? 'regla SVG' : 'reglas SVG' }}
                                </span>
                            </div>
                            <p class="text-slate-600 text-[11px] mt-1">{{ $c['rule'] }}</p>
                            @if(!empty($c['warning']))
                                <span class="text-[10px] text-amber-900 font-semibold block bg-amber-50 p-1.5 rounded border border-amber-200 mt-2">
                                    Advertencia: {{ $c['warning'] }}
                                </span>
                            @endif
                            @if(!empty($cSymbols))
                                <div class="flex flex-wrap gap-1.5 mt-2 pt-2 border-t border-slate-100">
                                    @foreach($cSymbols as $sKey)
                                        @if(isset($careSymbolsLookup[$sKey]))
                                            <span class="inline-flex items-center gap-1 text-[10px] bg-slate-50 border border-slate-200 px-2 py-0.5 rounded text-slate-700 font-medium">
                                                <span class="text-indigo-600 scale-75 origin-center">{!! $careSymbolsLookup[$sKey]['svg'] !!}</span>
                                                <span>{{ $careSymbolsLookup[$sKey]['name'] }}</span>
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
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

        <!-- SECCIÓN 3 ACORDEÓN: EDITOR OFICIAL DE PIE DE PÁGINA (FOOTER DE TIENDA) -->
        <div class="store-accordion-section border border-slate-200/80 rounded-2xl bg-white shadow-sm mb-4 overflow-hidden" id="accordionSecFooter">
            <button type="button" class="store-accordion-header w-full flex items-center justify-between p-4 bg-slate-50/70 hover:bg-slate-100/70 transition cursor-pointer text-left" onclick="window.toggleStoreAccordion('accordionSecFooter')">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">3. Editor Oficial de Pie de Página (Footer &amp; Contacto)</div>
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
                    <div class="grid grid-cols-1 gap-4">
                        <label class="flex items-start gap-3 p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-teal-300 transition">
                            <input type="hidden" name="payload[show_revops_link]" value="0">
                            <input type="checkbox" name="payload[show_revops_link]" value="1" {{ !empty($footerConfig['show_revops_link']) ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                            <div>
                                <strong class="text-slate-800 font-semibold block">Mostrar Acceso a Consola RevOps</strong>
                                <span class="text-[11px] text-slate-500 block mt-0.5">Muestra la tarjeta de acceso rápido al Panel de Administración en la cuarta columna del footer.</span>
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
                            <input type="hidden" name="announcement_active" value="0">
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
                                <input type="text" name="announcements[{{ $idx }}][text]" value="{{ $an['text'] ?? '' }}" placeholder="Texto del aviso (Ej: ENVÍO GRATIS EN COMPRAS +$999)" class="w-full px-2.5 py-1.5 border border-slate-200 rounded text-xs bg-white">
                            </div>
                            <div class="w-1/3">
                                <input type="text" name="announcements[{{ $idx }}][link]" value="{{ $an['link'] ?? '' }}" placeholder="URL enlace (Opcional, ej: /ofertas)" class="w-full px-2.5 py-1.5 border border-slate-200 rounded text-xs bg-white">
                            </div>
                            <label class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-600 px-2 cursor-pointer">
                                <input type="hidden" name="announcements[{{ $idx }}][active]" value="0">
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
                                        @php
                                            $imgCover = !empty($pg->image_url) ? $pg->image_url : null;
                                        @endphp
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
                                    @if(in_array($pg->slug, ['aliados', 'convenios', 'convenios-con-aliados']))
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            REPOSITORIO AUTO HUB 7
                                        </span>
                                    @elseif(!empty($pg->filter_universe) || !empty($pg->filter_category))
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
                                        
                                        @if(in_array($pg->slug, ['aliados', 'convenios', 'convenios-con-aliados']))
                                            <!-- Página del Sistema Hub 7 Auto: Sin configuración manual -->
                                            <a href="/admin/convenios" target="_blank" class="px-2 py-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded text-[11px] font-bold border border-indigo-200" title="Gestionar convenios y eventos en Hub 7">
                                                Convenios Hub 7 &rarr;
                                            </a>
                                        @else
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
                                        @endif
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
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Filtros conformados por Checkbox</span>
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
                                        <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60 shadow-xs">
                                            <svg class="w-2.5 h-2.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                            <span>CHECKBOX</span>
                                        </div>
                                        <input type="hidden" name="filters[{{ $flt->id }}][display_type]" value="checkbox">
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
    <!-- SUBMÓDULO 6: GESTOR DE MEDIOS & IMÁGENES CMS (HUB 2)                      -->
    <!-- ========================================================================= -->
    <div id="storeMediaPanel" class="tab-panel hidden" style="display: none;">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm mb-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5 mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </span>
                        <span>6. Gestor de Medios &amp; Galería Visual CMS (Hub 2)</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Biblioteca oficial de fotos y activos gráficos para banners, carruseles y vitrinas. Separado e independiente del catálogo de productos.
                    </p>
                </div>
                
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Buscador rápido en vivo -->
                    <div class="relative">
                        <input type="text" id="cmsMediaSearchInput" placeholder="Buscar por nombre..." oninput="window.filterCmsMediaGrid(this.value)" class="pl-8 pr-3 py-1.5 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none w-48 sm:w-60">
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>

                    <!-- Botón Subir Imágenes -->
                    <label class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs cursor-pointer shadow-xs transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Subir Imágenes</span>
                        <input type="file" id="cmsMediaPanelUploadInput" class="hidden" accept="image/*" multiple onchange="window.uploadCmsMediaFiles(this.files)">
                    </label>

                    <!-- Botón Refrescar -->
                    <button type="button" onclick="window.loadCmsMedia()" class="p-2 border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-600 transition cursor-pointer" title="Refrescar Galería">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>
            </div>

            <!-- Zona Drag & Drop -->
            <div id="cmsMediaDropZone" class="border-2 border-dashed border-slate-200 hover:border-indigo-400 bg-slate-50/60 hover:bg-indigo-50/30 rounded-2xl p-6 mb-6 text-center transition cursor-pointer" onclick="document.getElementById('cmsMediaPanelUploadInput').click()">
                <div class="flex flex-col items-center justify-center gap-2 pointer-events-none">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    </div>
                    <div class="text-xs font-semibold text-slate-700">Arrastra y suelta imágenes aquí, o <span class="text-indigo-600 font-bold underline">haz clic para explorar archivos</span></div>
                    <div class="text-[11px] text-slate-400">Formatos JPG, PNG, WEBP, GIF hasta 10MB por archivo (soporta selección múltiple)</div>
                </div>
            </div>

            <!-- Estado de carga / progreso -->
            <div id="cmsMediaPanelUploading" class="hidden mb-6 p-4 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-indigo-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="text-xs font-bold text-indigo-900" id="cmsMediaUploadStatusText">Subiendo archivos al Gestor CMS...</span>
                </div>
            </div>

            <!-- Grilla Principal de Medios -->
            <div id="cmsMediaPanelGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @forelse($cmsMedia as $item)
                    <div class="cms-media-card bg-white border border-slate-200/90 hover:border-indigo-400 rounded-xl overflow-hidden shadow-2xs hover:shadow-md transition-all group relative flex flex-col" data-media-id="{{ $item->id }}" data-file-name="{{ strtolower($item->file_name) }}">
                        <div class="aspect-square bg-slate-100 relative overflow-hidden flex items-center justify-center">
                            <img src="{{ asset(ltrim($item->file_path, '/')) }}" alt="{{ $item->file_name }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                            <!-- Overlay de acciones en hover -->
                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2 p-2">
                                <button type="button" onclick="window.copyMediaUrl('{{ asset(ltrim($item->file_path, '/')) }}', this)" class="px-2.5 py-1.5 bg-white hover:bg-slate-100 text-slate-800 text-[11px] font-bold rounded-lg shadow-sm flex items-center gap-1.5 cursor-pointer w-full justify-center">
                                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    <span>Copiar URL</span>
                                </button>
                                <a href="{{ asset(ltrim($item->file_path, '/')) }}" target="_blank" class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold rounded-lg shadow-sm flex items-center gap-1.5 w-full justify-center text-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                    <span>Ver Original</span>
                                </a>
                                <button type="button" onclick="window.deleteCmsMedia({{ $item->id }}, this)" class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold rounded-lg shadow-sm flex items-center gap-1.5 cursor-pointer w-full justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    <span>Eliminar</span>
                                </button>
                            </div>
                        </div>
                        <div class="p-2.5 bg-white flex-1 flex flex-col justify-between">
                            <span class="text-[11px] font-bold text-slate-800 truncate block" title="{{ $item->file_name }}">{{ $item->file_name }}</span>
                            <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1">
                                <span>{{ $item->size ? round($item->size / 1024, 1) . ' KB' : 'Img' }}</span>
                                <span>{{ $item->created_at ? $item->created_at->format('d/m/y') : '' }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div id="cmsMediaPanelEmpty" class="col-span-full py-16 text-center flex flex-col items-center justify-center">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-700">Sin imágenes en el Gestor</h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm">Aún no has cargado fotos para tus banners y carruseles. Haz clic en "Subir Imágenes" o arrastra archivos arriba.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODALES DE CONTROL OPERATIVO CMS                                          -->
    <!-- ========================================================================= -->

    <!-- 1. MODAL CREAR SECCIÓN MODULAR BIFURCADO -->
    <!-- 1. MODAL CREAR SECCIÓN MODULAR BIFURCADO -->
    <div id="createSectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-0 bg-slate-900/60 backdrop-blur-xs hidden" style="display: none;">
        <div class="bg-white w-full h-full flex flex-col md:flex-row shadow-2xl overflow-hidden relative">
            
            <!-- Botón de cerrar general (Solo visible en Desktop, en mobile va en el header) -->
            <button type="button" class="hidden md:flex absolute top-4 right-4 z-50 w-10 h-10 items-center justify-center bg-white border border-slate-200 rounded-full text-slate-500 hover:text-slate-900 hover:bg-slate-100 shadow-md transition font-bold text-2xl cursor-pointer" data-action="close-modal" data-modal="createSectionModal">&times;</button>

            <!-- COLUMNA IZQUIERDA: CONFIGURACIONES -->
            <div class="w-full md:w-[720px] shrink-0 p-4 md:p-6 overflow-y-auto border-b md:border-b-0 md:border-r border-slate-200 bg-white flex flex-col relative z-20 custom-scrollbar">
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

                                


                        <!-- CONTENIDO Y ESTILOS DE TEXTO DEL HERO -->
                        <div id="createCommonFieldsWrap" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-4 mb-4">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                <span class="font-bold text-slate-800 text-xs flex items-center gap-1.5 uppercase tracking-wider">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Contenido y Estilos de Texto
                                </span>
                                <span class="text-[10px] text-slate-400 font-medium">Textos, tipografías y alineación</span>
                            </div>

                            <!-- TÍTULO PRINCIPAL (H2) + TIPOGRAFÍA + COLOR -->
                            <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-2">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1 text-xs">Título Público (H2)</label>
                                    <input type="text" name="title" id="inpCreateTitle" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Ej: BOXERS & JOCKS DEPORTIVOS" oninput="window.updatePreviewCreate && window.updatePreviewCreate()">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 pt-1 border-t border-slate-100">
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Tipografía del Título</label>
                                        <select name="settings[title_font]" id="inpCreateTitleFont" class="w-full px-2 py-1.5 text-xs border border-slate-200 rounded-lg bg-white" onchange="window.loadGoogleFont && window.loadGoogleFont(this.value); window.updatePreviewCreate && window.updatePreviewCreate()">
                                            <option value="inherit">Heredar de Vitrina (Default)</option>
                                            @foreach($curatedGoogleFonts as $fKey => $fLabel)
                                                <option value="{{ $fKey }}">{{ $fLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color del Título</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="color" name="settings[title_color]" id="inpCreateTitleColor" value="#ffffff" class="w-8 h-7 p-0 border border-slate-200 rounded cursor-pointer shrink-0" oninput="document.getElementById('inpCreateTitleColorHex').value = this.value; window.updatePreviewCreate && window.updatePreviewCreate()">
                                            <input type="text" id="inpCreateTitleColorHex" value="#ffffff" class="w-full px-2 py-1 text-xs font-mono border border-slate-200 rounded-lg" oninput="document.getElementById('inpCreateTitleColor').value = this.value; window.updatePreviewCreate && window.updatePreviewCreate()">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- BADGE / KICKER SUPERIOR -->
                            <div class="p-3 bg-white border border-slate-200 rounded-xl">
                                <label class="block font-semibold text-slate-700 mb-1 text-xs">Badge / Kicker Superior</label>
                                <input type="text" name="badge" id="inpCreateBadge" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Ej: LANZAMIENTO EXCLUSIVO" oninput="window.updatePreviewCreate && window.updatePreviewCreate()">
                            </div>

                            <!-- BAJADA / SUBTÍTULO + TIPOGRAFÍA + COLOR -->
                            <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-2">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1 text-xs">Bajada / Subtítulo</label>
                                    <input type="text" name="subtitle" id="inpCreateSubtitle" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Ej: Confeccionados con modal pima de secado ultra rápido." oninput="window.updatePreviewCreate && window.updatePreviewCreate()">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 pt-1 border-t border-slate-100">
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Tipografía de Bajada</label>
                                        <select name="settings[subtitle_font]" id="inpCreateSubtitleFont" class="w-full px-2 py-1.5 text-xs border border-slate-200 rounded-lg bg-white" onchange="window.loadGoogleFont && window.loadGoogleFont(this.value); window.updatePreviewCreate && window.updatePreviewCreate()">
                                            <option value="inherit">Heredar de Vitrina (Default)</option>
                                            @foreach($curatedGoogleFonts as $fKey => $fLabel)
                                                <option value="{{ $fKey }}">{{ $fLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color de Bajada</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="color" name="settings[subtitle_color]" id="inpCreateSubtitleColor" value="#e2e8f0" class="w-8 h-7 p-0 border border-slate-200 rounded cursor-pointer shrink-0" oninput="document.getElementById('inpCreateSubtitleColorHex').value = this.value; window.updatePreviewCreate && window.updatePreviewCreate()">
                                            <input type="text" id="inpCreateSubtitleColorHex" value="#e2e8f0" class="w-full px-2 py-1 text-xs font-mono border border-slate-200 rounded-lg" oninput="document.getElementById('inpCreateSubtitleColor').value = this.value; window.updatePreviewCreate && window.updatePreviewCreate()">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ALINEACIÓN DE TEXTO -->
                            <div class="p-3 bg-white border border-slate-200 rounded-xl flex items-center justify-between">
                                <div>
                                    <span class="block font-semibold text-slate-800 text-xs">Alineación del Texto</span>
                                    <span class="text-[10px] text-slate-500">Orientación de títulos, bajadas y botones</span>
                                </div>
                                <div class="grid grid-cols-3 gap-1 p-0.5 bg-slate-100 rounded-lg border border-slate-200 w-32" id="inpCreateTextAlignGroup">
                                    <label class="relative flex items-center justify-center py-1.5 rounded-md cursor-pointer text-slate-500 transition">
                                        <input type="radio" name="settings[text_align]" value="text-left" class="peer hidden" onchange="window.updatePreviewCreate && window.updatePreviewCreate()">
                                        <div class="peer-checked:bg-white peer-checked:shadow-sm peer-checked:text-indigo-600 absolute inset-0 rounded-md transition"></div>
                                        <svg class="w-4 h-4 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16"/></svg>
                                    </label>
                                    <label class="relative flex items-center justify-center py-1.5 rounded-md cursor-pointer text-slate-500 transition">
                                        <input type="radio" name="settings[text_align]" value="text-center" class="peer hidden" checked onchange="window.updatePreviewCreate && window.updatePreviewCreate()">
                                        <div class="peer-checked:bg-white peer-checked:shadow-sm peer-checked:text-indigo-600 absolute inset-0 rounded-md transition"></div>
                                        <svg class="w-4 h-4 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M4 18h16"/></svg>
                                    </label>
                                    <label class="relative flex items-center justify-center py-1.5 rounded-md cursor-pointer text-slate-500 transition">
                                        <input type="radio" name="settings[text_align]" value="text-right" class="peer hidden" onchange="window.updatePreviewCreate && window.updatePreviewCreate()">
                                        <div class="peer-checked:bg-white peer-checked:shadow-sm peer-checked:text-indigo-600 absolute inset-0 rounded-md transition"></div>
                                        <svg class="w-4 h-4 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M4 18h16"/></svg>
                                    </label>
                                </div>
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

                                <!-- 2. PANTALLA, ALTO Y PARALLAX SCROLLING -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div>
                                        <input type="hidden" name="settings[full_width]" value="0">
                                        <label class="flex items-center justify-between cursor-pointer p-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm h-full">
                                            <div>
                                                <span class="font-bold text-slate-800 block text-xs">Hero Fullpage</span>
                                                <span class="text-[10px] text-slate-500">100% de alto</span>
                                            </div>
                                            <div class="relative shrink-0 ml-2">
                                                <input type="checkbox" name="settings[full_width]" id="inpCreateFullWidth" value="1" class="peer sr-only" onchange="window.updatePreviewCreate && window.updatePreviewCreate()">
                                                <div class="block bg-slate-200 w-10 h-6 rounded-full peer-checked:bg-indigo-600 transition"></div>
                                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition peer-checked:translate-x-4"></div>
                                            </div>
                                        </label>
                                    </div>
                                    <div>
                                        <input type="hidden" name="settings[full_width_horizontal]" value="0">
                                        <label class="flex items-center justify-between cursor-pointer p-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm h-full">
                                            <div>
                                                <span class="font-bold text-slate-800 block text-xs">Ancho Completo</span>
                                                <span class="text-[10px] text-slate-500">100% horizontal</span>
                                            </div>
                                            <div class="relative shrink-0 ml-2">
                                                <input type="checkbox" name="settings[full_width_horizontal]" id="inpCreateFullWidthHorizontal" value="1" class="peer sr-only" onchange="window.updatePreviewCreate && window.updatePreviewCreate()">
                                                <div class="block bg-slate-200 w-10 h-6 rounded-full peer-checked:bg-indigo-600 transition"></div>
                                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition peer-checked:translate-x-4"></div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="p-3 bg-white border border-slate-200 rounded-xl shadow-sm">
                                        <label class="block font-bold text-slate-800 text-xs mb-1">Alto Personalizado</label>
                                        <div class="flex gap-2">
                                            <input type="number" name="settings[hero_height_value]" id="inpCreateHeroHeightValue" placeholder="Ej: 400" class="w-2/3 px-2 py-1.5 border border-slate-200 rounded-lg text-xs" oninput="window.updatePreviewCreate && window.updatePreviewCreate()">
                                            <select name="settings[hero_height_unit]" id="inpCreateHeroHeightUnit" class="w-1/3 px-2 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold" onchange="window.updatePreviewCreate && window.updatePreviewCreate()">
                                                <option value="px">px</option>
                                                <option value="vh">vh</option>
                                                <option value="%">%</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <input type="hidden" name="settings[parallax_scrolling]" value="0">
                                        <label class="flex items-center justify-between cursor-pointer p-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm h-full">
                                            <div>
                                                <span class="font-bold text-slate-800 block text-xs">Efecto Parallax</span>
                                                <span class="text-[10px] text-slate-500">Scroll fijo dinámico</span>
                                            </div>
                                            <div class="relative shrink-0 ml-2">
                                                <input type="checkbox" name="settings[parallax_scrolling]" id="inpCreateParallax" value="1" class="peer sr-only" onchange="window.updatePreviewCreate && window.updatePreviewCreate()">
                                                <div class="block bg-slate-200 w-10 h-6 rounded-full peer-checked:bg-indigo-600 transition"></div>
                                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition peer-checked:translate-x-4"></div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
<!-- 3. IMAGEN DE FONDO -->
                                <div class="space-y-2 p-3 bg-white border border-slate-200 rounded-xl">
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block font-bold text-slate-800 text-xs">Tipo de Fondo</label>
                                        <select name="settings[background_type]" id="createHeroBgType" class="px-2 py-1 text-xs font-semibold border border-slate-200 rounded-lg" onchange="window.toggleHeroBgType(this.value, 'create')">
                                            <option value="image">Imagen</option>
                                            <option value="video">Video</option>
                                        </select>
                                    </div>
                                    <div id="createHeroBgImageWrap" class="grid grid-cols-1 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Subir Archivo Local</label>
                                            <input type="file" name="background_image_file" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" onchange="window.previewHeroBgImage(this, 'createHeroBgPreview')">
                                        </div>
                                        <div>
                                            <div class="flex items-center justify-between mb-1">
                                                <label class="block text-[11px] font-semibold text-slate-600">O Ingresar URL Externa</label>
                                                <button type="button" class="text-[10px] text-indigo-600 font-bold hover:underline" onclick="window.openCmsMediaModal('createHeroBgUrl')">Seleccionar del Gestor</button>
                                            </div>
                                            <input type="text" name="settings[background_image]" id="createHeroBgUrl" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" placeholder="https://ejemplo.com/hero-bg.webp" oninput="window.previewHeroBgUrl(this.value, 'createHeroBgPreview')">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Texto Alternativo (Alt SEO)</label>
                                            <input type="text" name="settings[background_image_alt]" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs" placeholder="Breve descripción de la imagen">
                                        </div>
                                        <div>
                                            <div class="flex items-center justify-between mb-1.5">
                                                <label class="block text-[11px] font-semibold text-slate-700 flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h2m14 0h2M12 3v2m0 14v2"/></svg>
                                                    Foco de Atención de la Imagen (Punto Focal)
                                                </label>
                                                <span id="createHeroFocalPointLabel" class="text-[10px] font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                                                    Centro Centro (50% 50%)
                                                </span>
                                            </div>
                                            <input type="hidden" name="settings[image_focal_point]" id="inpCreateHeroFocalPoint" value="center center">
                                            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-4">
                                                <!-- MATRIZ 3x3 DE 9 CELDAS -->
                                                <div id="createHeroFocalMatrix" class="w-24 h-24 p-1 bg-slate-200/90 border border-slate-300 rounded-xl grid grid-cols-3 gap-1 shadow-inner select-none shrink-0" role="radiogroup" aria-label="Punto focal de la imagen">
                                                    <button type="button" data-focal="left top" onclick="window.setHeroFocalPoint('create', 'left top')" title="Izquierda Superior (0% 0%)" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer"><span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span></button>
                                                    <button type="button" data-focal="center top" onclick="window.setHeroFocalPoint('create', 'center top')" title="Centro Superior (50% 0%)" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer"><span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span></button>
                                                    <button type="button" data-focal="right top" onclick="window.setHeroFocalPoint('create', 'right top')" title="Derecha Superior (100% 0%)" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer"><span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span></button>

                                                    <button type="button" data-focal="left center" onclick="window.setHeroFocalPoint('create', 'left center')" title="Izquierda Centro (0% 50%)" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer"><span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span></button>
                                                    <button type="button" data-focal="center center" onclick="window.setHeroFocalPoint('create', 'center center')" title="Centro Centro (50% 50%) - Predeterminado" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-indigo-600 border border-indigo-700 ring-2 ring-indigo-300 text-white transition cursor-pointer shadow-xs"><span class="w-2.5 h-2.5 rounded-full bg-white ring-2 ring-indigo-400 pointer-events-none"></span></button>
                                                    <button type="button" data-focal="right center" onclick="window.setHeroFocalPoint('create', 'right center')" title="Derecha Centro (100% 50%)" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer"><span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span></button>

                                                    <button type="button" data-focal="left bottom" onclick="window.setHeroFocalPoint('create', 'left bottom')" title="Izquierda Inferior (0% 100%)" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer"><span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span></button>
                                                    <button type="button" data-focal="center bottom" onclick="window.setHeroFocalPoint('create', 'center bottom')" title="Centro Inferior (50% 100%)" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer"><span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span></button>
                                                    <button type="button" data-focal="right bottom" onclick="window.setHeroFocalPoint('create', 'right bottom')" title="Derecha Inferior (100% 100%)" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer"><span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span></button>
                                                </div>
                                                <div class="text-[11px] text-slate-500 leading-snug">
                                                    <span class="font-bold text-slate-700 block text-xs">Matriz de 9 Celdas</span>
                                                    Haz clic en un cuadrante para anclar el punto visual clave al redimensionarse la imagen en pantallas móviles y monitores ultrawide.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="createHeroBgVideoWrap" class="hidden grid grid-cols-1 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Subir Video (MP4)</label>
                                            <input type="file" name="background_video_file" accept="video/mp4,video/webm" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">O Ingresar URL de Video</label>
                                            <input type="text" name="settings[background_video]" id="createHeroBgVideoUrl" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" placeholder="https://ejemplo.com/hero-bg.mp4">
                                        </div>
                                        <div class="flex gap-4 mt-2">
                                            <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600"><input type="checkbox" name="settings[video_autoplay]" value="1" checked class="rounded border-slate-300 text-indigo-600"> Autoplay</label>
                                            <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600"><input type="checkbox" name="settings[video_loop]" value="1" checked class="rounded border-slate-300 text-indigo-600"> Loop</label>
                                            <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600"><input type="checkbox" name="settings[video_muted]" value="1" checked class="rounded border-slate-300 text-indigo-600"> Muted</label>
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
    <div class="grid grid-cols-2 gap-3 mb-3">
        <div class="space-y-2 p-2 bg-slate-50 border border-slate-100 rounded">
            <div>
                <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color Inicio & Opacidad</label>
                <div class="flex items-center gap-1.5 mb-2">
                    <input type="color" id="createGradColorStartPicker" value="#0f172a" class="w-7 h-7 p-0 border border-slate-200 rounded cursor-pointer" oninput="document.getElementById('createGradColorStart').value = this.value; window.updateHeroGradientPreview('create')">
                    <input type="text" name="settings[gradient_color_start]" id="createGradColorStart" value="#0f172a" class="w-full px-2 py-1 text-[11px] font-mono border border-slate-200 rounded" oninput="document.getElementById('createGradColorStartPicker').value = this.value; window.updateHeroGradientPreview('create')">
                </div>
                <div class="flex items-center gap-2">
                    <input type="range" name="settings[gradient_opacity_start]" id="createGradOpacityStart" min="0" max="100" value="80" class="w-full cursor-pointer accent-indigo-600" oninput="document.getElementById('createGradOpacityStartVal').textContent = this.value + '%'; window.updateHeroGradientPreview('create')">
                    <span id="createGradOpacityStartVal" class="text-[10px] font-semibold text-slate-600 w-8 text-right">80%</span>
                </div>
            </div>
        </div>
        <div class="space-y-2 p-2 bg-slate-50 border border-slate-100 rounded">
            <div>
                <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color Fin & Opacidad</label>
                <div class="flex items-center gap-1.5 mb-2">
                    <input type="color" id="createGradColorEndPicker" value="#1e1b4b" class="w-7 h-7 p-0 border border-slate-200 rounded cursor-pointer" oninput="document.getElementById('createGradColorEnd').value = this.value; window.updateHeroGradientPreview('create')">
                    <input type="text" name="settings[gradient_color_end]" id="createGradColorEnd" value="#1e1b4b" class="w-full px-2 py-1 text-[11px] font-mono border border-slate-200 rounded" oninput="document.getElementById('createGradColorEndPicker').value = this.value; window.updateHeroGradientPreview('create')">
                </div>
                <div class="flex items-center gap-2">
                    <input type="range" name="settings[gradient_opacity_end]" id="createGradOpacityEnd" min="0" max="100" value="80" class="w-full cursor-pointer accent-indigo-600" oninput="document.getElementById('createGradOpacityEndVal').textContent = this.value + '%'; window.updateHeroGradientPreview('create')">
                    <span id="createGradOpacityEndVal" class="text-[10px] font-semibold text-slate-600 w-8 text-right">80%</span>
                </div>
            </div>
        </div>
        <div class="col-span-2">
            <label class="block text-[10px] font-semibold text-slate-600 mb-1">Dirección del Degradado</label>
            <select name="settings[gradient_direction]" id="createGradDir" class="w-full px-2 py-1.5 text-[11px] border border-slate-200 rounded" onchange="window.updateHeroGradientPreview('create')">
                <option value="to bottom">Vertical</option>
                <option value="to right">Horizontal</option>
                <option value="135deg">Diagonal</option>
                <option value="to top">Invertido</option>
            </select>
        </div>
    </div>
    <div id="createGradPreviewBar" class="h-6 rounded-lg border border-slate-200 shadow-inner" style="background: linear-gradient(to bottom, rgba(15,23,42,0.8), rgba(30,27,75,0.8));"></div>
</div>
                                </div>


                                <!-- 6. ACCIONES Y BOTONES DEL HERO -->
                                <div id="createHeroButtonsWrap" class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                        <div>
                                            <span class="block font-bold text-slate-800 text-xs">Botones de Acción (Call to Action)</span>
                                            <span class="text-[10px] text-slate-500">Conmuta entre botones independientes o clic en todo el banner</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="hidden" name="settings[hero_has_buttons]" value="0">
                                            <label class="flex items-center gap-2 cursor-pointer bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200 shadow-2xs hover:bg-slate-100 transition">
                                                <span class="text-[11px] font-bold text-slate-700">Incluir Botones</span>
                                                <div class="relative shrink-0">
                                                    <input type="checkbox" name="settings[hero_has_buttons]" id="inpCreateHeroHasButtons" value="1" checked class="peer sr-only" onchange="window.toggleHeroButtonsMode && window.toggleHeroButtonsMode('create'); window.updatePreviewCreate && window.updatePreviewCreate()">
                                                    <div class="block bg-slate-200 w-9 h-5 rounded-full peer-checked:bg-indigo-600 transition"></div>
                                                    <div class="dot absolute left-0.5 top-0.5 bg-white w-4 h-4 rounded-full transition peer-checked:translate-x-4"></div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- CONTENEDOR DE BOTONES (VISIBLE SI hero_has_buttons ESTÁ ACTIVO) -->
                                    <div id="createHeroButtonsControls" class="space-y-3">

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
                                                <div class="flex items-center gap-1.5">
                                                    <input type="text" name="settings[cta_primary_url]" id="createCtaPrimaryUrl" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" placeholder="Ej: #section-2 o /catalogo">
                                                    <select onchange="if(this.value){ document.getElementById('createCtaPrimaryUrl').value = this.value; this.value=''; }" class="px-2 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-600 font-semibold bg-white cursor-pointer" title="Seleccionar una ruta de la tienda">
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
                                            <div class="grid grid-cols-2 gap-2 mt-1">
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Fondo</label>
                                                    <input type="color" name="settings[cta_primary_bg]" id="inpCreateCtaPrimaryBg" value="#0f172a" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Texto</label>
                                                    <input type="color" name="settings[cta_primary_color]" id="inpCreateCtaPrimaryColor" value="#ffffff" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Borde</label>
                                                    <input type="color" name="settings[cta_primary_border_color]" id="inpCreateCtaPrimaryBorderColor" value="#0f172a" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Grosor Borde</label>
                                                    <select name="settings[cta_primary_border_width]" id="inpCreateCtaPrimaryBorderWidth" class="w-full px-2 py-1 border border-slate-200 rounded text-[11px] h-8 bg-white">
                                                        <option value="border-0">Sin Borde</option>
                                                        <option value="border">Fino (1px)</option>
                                                        <option value="border-2">Medio (2px)</option>
                                                        <option value="border-4">Grueso (4px)</option>
                                                    </select>
                                                </div>
                                                <div class="col-span-2">
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Sombra</label>
                                                    <select name="settings[cta_primary_shadow]" id="inpCreateCtaPrimaryShadow" class="w-full px-2 py-1.5 border border-slate-200 rounded text-[11px] bg-white">
                                                        <option value="">Sin Sombra</option>
                                                        <option value="shadow-sm">Pequeña</option>
                                                        <option value="shadow-md">Media</option>
                                                        <option value="shadow-lg">Grande</option>
                                                        <option value="shadow-xl">Extra Grande</option>
                                                    </select>
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
                                                <div class="flex items-center gap-1.5">
                                                    <input type="text" name="settings[cta_secondary_url]" id="createCtaSecondaryUrl" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" placeholder="Ej: /pagina/guia-tallas">
                                                    <select onchange="if(this.value){ document.getElementById('createCtaSecondaryUrl').value = this.value; this.value=''; }" class="px-2 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-600 font-semibold bg-white cursor-pointer" title="Seleccionar una ruta de la tienda">
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
                                            <div class="grid grid-cols-2 gap-2 mt-1">
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Fondo</label>
                                                    <input type="color" name="settings[cta_secondary_bg]" id="inpCreateCtaSecondaryBg" value="#ffffff" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Texto</label>
                                                    <input type="color" name="settings[cta_secondary_color]" id="inpCreateCtaSecondaryColor" value="#0f172a" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Borde</label>
                                                    <input type="color" name="settings[cta_secondary_border_color]" id="inpCreateCtaSecondaryBorderColor" value="#0f172a" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Grosor Borde</label>
                                                    <select name="settings[cta_secondary_border_width]" id="inpCreateCtaSecondaryBorderWidth" class="w-full px-2 py-1 border border-slate-200 rounded text-[11px] h-8 bg-white">
                                                        <option value="border-0">Sin Borde</option>
                                                        <option value="border">Fino (1px)</option>
                                                        <option value="border-2">Medio (2px)</option>
                                                        <option value="border-4">Grueso (4px)</option>
                                                    </select>
                                                </div>
                                                <div class="col-span-2">
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Sombra</label>
                                                    <select name="settings[cta_secondary_shadow]" id="inpCreateCtaSecondaryShadow" class="w-full px-2 py-1.5 border border-slate-200 rounded text-[11px] bg-white">
                                                        <option value="">Sin Sombra</option>
                                                        <option value="shadow-sm">Pequeña</option>
                                                        <option value="shadow-md">Media</option>
                                                        <option value="shadow-lg">Grande</option>
                                                        <option value="shadow-xl">Extra Grande</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </div>

                                    <!-- CONTENEDOR DE CLIC EN TODO EL HERO (VISIBLE CUANDO hero_has_buttons ESTÁ DESACTIVADO) -->
                                    <div id="createHeroFullClickWrap" class="p-3 bg-amber-50/60 border border-amber-200/80 rounded-xl space-y-2 hidden">
                                        <div class="flex items-center gap-2 text-amber-900 font-bold text-xs">
                                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                                            <span>Enlace de Clic en Todo el Hero</span>
                                        </div>
                                        <p class="text-[11px] text-amber-800 leading-relaxed">
                                            Al no incluir botones, cualquier clic sobre la imagen o el contenido del Hero redirigirá automáticamente a la siguiente ruta o URL seleccionada.
                                        </p>
                                        <div>
                                            <label class="block text-[10px] font-semibold text-slate-600 mb-1">URL / Ruta de Destino</label>
                                            <div class="flex items-center gap-1.5">
                                                <input type="text" name="settings[hero_click_url]" id="createHeroClickUrl" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-mono bg-white" placeholder="Ej: #section-2 o /catalogo" oninput="window.updatePreviewCreate && window.updatePreviewCreate()">
                                                <select onchange="if(this.value){ document.getElementById('createHeroClickUrl').value = this.value; this.value=''; window.updatePreviewCreate && window.updatePreviewCreate(); }" class="px-2 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-600 font-semibold bg-white cursor-pointer shrink-0" title="Seleccionar una ruta de la tienda">
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

                        <!-- 2. MODO CAROUSEL -->
                        <div id="createCarouselWrap" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-4 hidden">
                            <input type="hidden" name="settings[carousel_global_content]" value="0">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                <span class="font-bold text-slate-800 text-xs flex items-center gap-1.5 uppercase tracking-wider">Configuración de Carrusel</span>
                                <span class="text-[10px] text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded-full font-semibold">Contenido integrado por Slide</span>
                            </div>
                            <div class="space-y-4">
                                <!-- PANTALLA, ALTO PERSONALIZADO Y EFECTO REVELADO DEL CARRUSEL -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div>
                                        <label class="flex items-center justify-between cursor-pointer p-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm h-full">
                                            <div>
                                                <span class="font-bold text-slate-800 block text-xs">Full Page</span>
                                                <span class="text-[10px] text-slate-500">100% de alto</span>
                                            </div>
                                            <div class="relative shrink-0 ml-2">
                                                <input type="checkbox" id="inpCreateCarouselFullWidth" value="1" class="peer sr-only" onchange="window.syncCarouselLayoutSettings && window.syncCarouselLayoutSettings('create')">
                                                <div class="block bg-slate-200 w-10 h-6 rounded-full peer-checked:bg-indigo-600 transition"></div>
                                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition peer-checked:translate-x-4"></div>
                                            </div>
                                        </label>
                                    </div>
                                    <div>
                                        <label class="flex items-center justify-between cursor-pointer p-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm h-full">
                                            <div>
                                                <span class="font-bold text-slate-800 block text-xs">Ancho Completo</span>
                                                <span class="text-[10px] text-slate-500">100% horizontal</span>
                                            </div>
                                            <div class="relative shrink-0 ml-2">
                                                <input type="checkbox" id="inpCreateCarouselFullWidthHorizontal" value="1" class="peer sr-only" onchange="window.syncCarouselLayoutSettings && window.syncCarouselLayoutSettings('create')">
                                                <div class="block bg-slate-200 w-10 h-6 rounded-full peer-checked:bg-indigo-600 transition"></div>
                                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition peer-checked:translate-x-4"></div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="p-3 bg-white border border-slate-200 rounded-xl shadow-sm">
                                        <label class="block font-bold text-slate-800 text-xs mb-1">Alto Personalizado</label>
                                        <div class="flex gap-1.5">
                                            <input type="number" id="inpCreateCarouselHeightValue" placeholder="Ej: 440" class="w-2/3 px-2 py-1.5 border border-slate-200 rounded-lg text-xs" oninput="window.syncCarouselLayoutSettings && window.syncCarouselLayoutSettings('create')">
                                            <select id="inpCreateCarouselHeightUnit" class="w-1/3 px-1.5 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold bg-white" onchange="window.syncCarouselLayoutSettings && window.syncCarouselLayoutSettings('create')">
                                                <option value="px">px</option>
                                                <option value="vh">vh</option>
                                                <option value="%">%</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="flex items-center justify-between cursor-pointer p-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm h-full">
                                            <div>
                                                <span class="font-bold text-slate-800 block text-xs">Efecto Revelado (Cortina)</span>
                                                <span class="text-[10px] text-slate-500">Revela imagen de fondo</span>
                                            </div>
                                            <div class="relative shrink-0 ml-2">
                                                <input type="checkbox" id="inpCreateCarouselParallax" value="1" class="peer sr-only" onchange="window.syncCarouselLayoutSettings && window.syncCarouselLayoutSettings('create')">
                                                <div class="block bg-slate-200 w-10 h-6 rounded-full peer-checked:bg-indigo-600 transition"></div>
                                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition peer-checked:translate-x-4"></div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between bg-white p-2.5 rounded-xl border border-slate-200">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" id="createCarouselAutoPlay" name="settings[autoplay]" checked value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4" onchange="window.updatePreviewCreate && window.updatePreviewCreate()">
                                        <span class="text-xs text-slate-700 font-bold">Autoplay Animado</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs text-slate-600 font-semibold">Intervalo (seg):</label>
                                        <input type="number" name="settings[autoplay_time]" id="createCarouselAutoplayTime" value="5" min="1" max="60" class="w-16 px-2 py-1 border border-slate-200 rounded text-xs font-bold text-center" oninput="window.updatePreviewCreate && window.updatePreviewCreate()">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-1">
                                    <label class="block font-bold text-slate-700 text-xs uppercase tracking-wider">Slides del Carrusel</label>
                                    <button type="button" class="px-3 py-1.5 bg-indigo-600 text-white text-xs rounded-lg hover:bg-indigo-700 font-bold shadow-xs cursor-pointer transition" onclick="window.addCarouselSlide('create')">+ Añadir Slide</button>
                                </div>
                                
                                <div id="createCarouselSlidesList" class="space-y-3">
                                    <!-- Dynamic Slides go here -->
                                </div>
                            </div>
                        </div>

                        <!-- 3. MODO POP UP FULLSTACK (Hub 2 / Hub 7 / Hub 3 / Meta CAPI) -->
                        <div id="createPopUpWrap" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-4 hidden">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                <span class="font-bold text-slate-800 text-xs flex items-center gap-1.5 uppercase tracking-wider">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    Configurador de Pop-up y Captura de Leads (CAPI Score 9.8)
                                </span>
                            </div>

                            <!-- Selector de Accion Principal -->
                            <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-2">
                                <label class="block font-bold text-slate-800 text-xs">Acción Principal del Pop-up</label>
                                <select name="settings[popup_action]" id="createPopupAction" class="w-full px-3 py-2 text-xs font-semibold border border-slate-200 rounded-lg bg-slate-50 focus:bg-white" onchange="window.handlePopupActionChange('create')">
                                    <option value="ally_discount">2.1 Suscripción con Código de Descuento de Aliado (Convenio Hub 7)</option>
                                    <option value="newsletter">2.2 Suscripción para Actualizaciones de Newsletter (Sin Cupón)</option>
                                    <option value="store_discount">2.3 Suscripción para Obtener Cupón Directo de Tienda</option>
                                    <option value="presale">2.4 Acceder a una Preventa Exclusiva (Redirección Inmediata)</option>
                                </select>
                                <span class="block text-[10px] text-slate-500">
                                    Todos los registros crean una cuenta de comprador en Hub 3 con envío instantáneo a WhatsApp y sincronizan eventos con Meta CAPI.
                                </span>
                            </div>

                            <!-- Sub-bloque 2.1: Campanas de Aliados Aprobadas en Hub 7 -->
                            <div id="createPopupAllyBlock" class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-1.5">
                                    <label class="block font-bold text-slate-800 text-xs">Evento o Beneficio del Aliado (Hub 7)</label>
                                    <a href="/admin/convenios" target="_blank" class="text-[10px] font-bold text-indigo-600 hover:underline">Ver Alianzas en Hub 7 &rarr;</a>
                                </div>
                                <div>
                                    <select name="settings[popup_ally_campaign_id]" id="createPopupAllyCampaignId" class="w-full px-3 py-2 text-xs font-semibold border border-slate-200 rounded-lg" onchange="window.handlePopupAllyCampaignChange('create')">
                                        @if(isset($approvedAllyCampaigns) && count($approvedAllyCampaigns) > 0)
                                            <option value="">-- Seleccionar Campaña Aprobada --</option>
                                            @foreach($approvedAllyCampaigns as $camp)
                                                <option value="{{ $camp->id }}" data-banner="{{ $camp->banner_image }}" data-title="{{ $camp->title }}" data-benefit="{{ $camp->benefit }}" data-ally="{{ $camp->ally->name ?? 'Aliado' }}">
                                                    {{ $camp->ally->name ?? 'Aliado' }} - {{ $camp->title }} ({{ $camp->benefit }})
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="">(No hay eventos aprobados en Hub 7 - Crea uno en Convenios)</option>
                                        @endif
                                    </select>
                                </div>

                                <!-- Conmutador entre Poster Oficial del Aliado o Cargar Imagen -->
                                <div class="pt-1">
                                    <label class="block font-bold text-slate-700 text-[11px] mb-1.5">Origen de la Imagen del Pop-up</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50 cursor-pointer hover:bg-slate-100 text-xs font-semibold text-slate-700">
                                            <input type="radio" name="settings[popup_ally_image_source]" value="ally_poster" checked class="text-indigo-600 focus:ring-indigo-500" onchange="window.handlePopupImageSourceToggle('create')">
                                            <span>Póster Oficial del Aliado</span>
                                        </label>
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50 cursor-pointer hover:bg-slate-100 text-xs font-semibold text-slate-700">
                                            <input type="radio" name="settings[popup_ally_image_source]" value="custom" class="text-indigo-600 focus:ring-indigo-500" onchange="window.handlePopupImageSourceToggle('create')">
                                            <span>Cargar Imagen en Creador</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Sub-bloque 2.3: Configuracion de Cupon de Tienda -->
                            <div id="createPopupStoreDiscountBlock" class="p-3 bg-white border border-slate-200 rounded-xl space-y-3 hidden">
                                <label class="block font-bold text-slate-800 text-xs">Parámetros del Descuento de Tienda (Cupón Único)</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Monto / Valor</label>
                                        <input type="number" name="settings[popup_store_discount_value]" id="createPopupStoreDiscountValue" value="15" min="1" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-bold" oninput="window.renderPopUpLivePreview('create')">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Tipo de Descuento</label>
                                        <select name="settings[popup_store_discount_type]" id="createPopupStoreDiscountType" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold" onchange="window.renderPopUpLivePreview('create')">
                                            <option value="percent">Porcentaje (%)</option>
                                            <option value="fixed">Monto Fijo ($ MXN)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Compra Mínima ($ MXN)</label>
                                        <input type="number" name="settings[popup_min_order]" id="createPopupMinOrder" value="499" min="0" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Días de Vigencia</label>
                                        <input type="number" name="settings[popup_days_valid]" id="createPopupDaysValid" value="30" min="1" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                    </div>
                                </div>
                            </div>

                            <!-- Sub-bloque 2.4: URL de Preventa -->
                            <div id="createPopupPresaleBlock" class="p-3 bg-white border border-slate-200 rounded-xl space-y-2 hidden">
                                <label class="block font-bold text-slate-800 text-xs">URL del Producto o Colección en Preventa</label>
                                <input type="text" name="settings[popup_presale_url]" id="createPopupPresaleUrl" placeholder="/producto/binder-pro-v2" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-mono">
                                <span class="block text-[10px] text-slate-500">Al completar el formulario, el usuario será redirigido inmediatamente a este destino con sus datos guardados en Hub 3.</span>
                            </div>

                            <!-- Imagen y Matriz de Punto Focal 3x3 -->
                            <div id="createPopupCustomImageBlock" class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                <label class="block font-bold text-slate-800 text-xs">Imagen de Fondo del Pop-up</label>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Subir Archivo Local</label>
                                    <input type="file" name="popup_image_file" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-semibold text-slate-600">O Ingresar URL / Gestor Multimedia</label>
                                        <button type="button" class="text-[10px] text-indigo-600 font-bold hover:underline" onclick="window.openCmsMediaModal('createPopupImageUrl')">Seleccionar del Gestor</button>
                                    </div>
                                    <input type="text" name="settings[popup_image_url]" id="createPopupImageUrl" placeholder="https://ejemplo.com/poster-evento.webp" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" oninput="window.renderPopUpLivePreview('create')">
                                </div>

                                <!-- Matriz 3x3 de Punto Focal -->
                                <div class="pt-1">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Matriz de Punto Focal de la Imagen</label>
                                    <input type="hidden" name="settings[popup_image_focal_point]" id="inpCreatePopupFocalPoint" value="center center">
                                    <div id="createPopupFocalMatrix" class="grid grid-cols-3 gap-1 w-36 mx-auto p-1 bg-slate-100 border border-slate-200 rounded-lg">
                                        <button type="button" data-point="top left" class="h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition" onclick="window.setPopupFocalPoint('create', 'top left', this)">TL</button>
                                        <button type="button" data-point="top center" class="h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition" onclick="window.setPopupFocalPoint('create', 'top center', this)">TC</button>
                                        <button type="button" data-point="top right" class="h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition" onclick="window.setPopupFocalPoint('create', 'top right', this)">TR</button>
                                        <button type="button" data-point="center left" class="h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition" onclick="window.setPopupFocalPoint('create', 'center left', this)">CL</button>
                                        <button type="button" data-point="center center" class="h-7 text-[10px] font-bold rounded border border-indigo-600 bg-indigo-600 text-white transition" onclick="window.setPopupFocalPoint('create', 'center center', this)">CC</button>
                                        <button type="button" data-point="center right" class="h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition" onclick="window.setPopupFocalPoint('create', 'center right', this)">CR</button>
                                        <button type="button" data-point="bottom left" class="h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition" onclick="window.setPopupFocalPoint('create', 'bottom left', this)">BL</button>
                                        <button type="button" data-point="bottom center" class="h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition" onclick="window.setPopupFocalPoint('create', 'bottom center', this)">BC</button>
                                        <button type="button" data-point="bottom right" class="h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition" onclick="window.setPopupFocalPoint('create', 'bottom right', this)">BR</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Colores de Título y Subtítulo del Pop-up -->
                            <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                <label class="block font-bold text-slate-800 text-xs">Colores de Texto del Pop-up</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="block text-[10px] font-semibold text-slate-600">Color del Título</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="color" id="createPopupTitleColorPicker" value="#ffffff" class="w-7 h-7 p-0 border border-slate-200 rounded cursor-pointer" oninput="document.getElementById('createPopupTitleColor').value = this.value; window.renderPopUpLivePreview('create')">
                                            <input type="text" name="settings[popup_title_color]" id="createPopupTitleColor" value="#ffffff" class="w-full px-2 py-1 text-[11px] font-mono border border-slate-200 rounded" oninput="document.getElementById('createPopupTitleColorPicker').value = this.value; window.renderPopUpLivePreview('create')">
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-[10px] font-semibold text-slate-600">Color de Bajada / Subtítulo</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="color" id="createPopupSubtitleColorPicker" value="#e2e8f0" class="w-7 h-7 p-0 border border-slate-200 rounded cursor-pointer" oninput="document.getElementById('createPopupSubtitleColor').value = this.value; window.renderPopUpLivePreview('create')">
                                            <input type="text" name="settings[popup_subtitle_color]" id="createPopupSubtitleColor" value="#e2e8f0" class="w-full px-2 py-1 text-[11px] font-mono border border-slate-200 rounded" oninput="document.getElementById('createPopupSubtitleColorPicker').value = this.value; window.renderPopUpLivePreview('create')">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Capa de Degradado Overlay para Pop-up -->
                            <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="settings[popup_enable_gradient]" id="createPopupEnableGradient" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4" onchange="window.renderPopUpLivePreview('create')">
                                    <span class="font-bold text-slate-800 text-xs">Habilitar Capa de Degradado Overlay</span>
                                </label>

                                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                                    <div class="space-y-2 p-2 bg-slate-50 border border-slate-100 rounded">
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color Inicio & Opacidad</label>
                                        <div class="flex items-center gap-1.5 mb-2">
                                            <input type="color" id="createPopupGradColorStartPicker" value="#0f172a" class="w-7 h-7 p-0 border border-slate-200 rounded cursor-pointer" oninput="document.getElementById('createPopupGradColorStart').value = this.value; window.renderPopUpLivePreview('create')">
                                            <input type="text" name="settings[popup_gradient_color_start]" id="createPopupGradColorStart" value="#0f172a" class="w-full px-2 py-1 text-[11px] font-mono border border-slate-200 rounded" oninput="document.getElementById('createPopupGradColorStartPicker').value = this.value; window.renderPopUpLivePreview('create')">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="range" name="settings[popup_gradient_opacity_start]" id="createPopupGradOpacityStart" min="0" max="100" value="85" class="w-full cursor-pointer accent-indigo-600" oninput="document.getElementById('createPopupGradOpacityStartVal').textContent = this.value + '%'; window.renderPopUpLivePreview('create')">
                                            <span id="createPopupGradOpacityStartVal" class="text-[10px] font-semibold text-slate-600 w-8 text-right">85%</span>
                                        </div>
                                    </div>

                                    <div class="space-y-2 p-2 bg-slate-50 border border-slate-100 rounded">
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color Fin & Opacidad</label>
                                        <div class="flex items-center gap-1.5 mb-2">
                                            <input type="color" id="createPopupGradColorEndPicker" value="#000000" class="w-7 h-7 p-0 border border-slate-200 rounded cursor-pointer" oninput="document.getElementById('createPopupGradColorEnd').value = this.value; window.renderPopUpLivePreview('create')">
                                            <input type="text" name="settings[popup_gradient_color_end]" id="createPopupGradColorEnd" value="#000000" class="w-full px-2 py-1 text-[11px] font-mono border border-slate-200 rounded" oninput="document.getElementById('createPopupGradColorEndPicker').value = this.value; window.renderPopUpLivePreview('create')">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="range" name="settings[popup_gradient_opacity_end]" id="createPopupGradOpacityEnd" min="0" max="100" value="95" class="w-full cursor-pointer accent-indigo-600" oninput="document.getElementById('createPopupGradOpacityEndVal').textContent = this.value + '%'; window.renderPopUpLivePreview('create')">
                                            <span id="createPopupGradOpacityEndVal" class="text-[10px] font-semibold text-slate-600 w-8 text-right">95%</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-600 mb-1">Dirección del Degradado</label>
                                    <select name="settings[popup_gradient_direction]" id="createPopupGradDir" class="w-full px-2 py-1.5 text-xs font-semibold border border-slate-200 rounded-lg" onchange="window.renderPopUpLivePreview('create')">
                                        <option value="to bottom">De Arriba hacia Abajo (Vertical Standard)</option>
                                        <option value="to top">De Abajo hacia Arriba</option>
                                        <option value="to right">De Izquierda a Derecha</option>
                                        <option value="to left">De Derecha a Izquierda</option>
                                        <option value="to bottom right">Diagonal (Superior Izq a Inferior Der)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Boton CTA y Comportamiento -->
                            <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1 text-xs">Texto del Botón de Suscripción (CTA)</label>
                                    <input type="text" name="settings[popup_cta_text]" id="createPopupCtaText" placeholder="Ej: Obtener mi Descuento Único" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-bold" oninput="window.renderPopUpLivePreview('create')">
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1 text-[11px]">Comportamiento de Cierre</label>
                                        <select name="settings[popup_close_behavior]" id="createPopupCloseBehavior" class="w-full px-2 py-1.5 text-xs font-semibold border border-slate-200 rounded-lg">
                                            <option value="outside">Permitir cerrar clic fuera (Overlay)</option>
                                            <option value="button">Solo botón cerrar (&times;)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1 text-[11px]">Retardo al Cargar (Seg)</label>
                                        <input type="number" name="settings[popup_delay_seconds]" id="createPopupDelaySeconds" value="3" min="0" max="60" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1 text-[11px]">Frecuencia</label>
                                        <select name="settings[popup_frequency]" id="createPopupFrequency" class="w-full px-2 py-1.5 text-xs font-semibold border border-slate-200 rounded-lg">
                                            <option value="once_per_session">Una vez por sesión</option>
                                            <option value="once_per_day">Una vez al día</option>
                                            <option value="always">Cada recarga (Testing)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. MODO BOTONES / BLOQUE PERSONALIZADO -->
                        <div id="createButtonsWrap" class="space-y-4 hidden">
                            <!-- EDITOR DE CÓDIGO CUSTOM -->
                        </div>
                        <div id="createCustomCodeWrap" class="p-3 bg-slate-900 text-slate-200 border border-slate-800 rounded-xl space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="block font-bold text-emerald-400 text-[11px] uppercase tracking-wider">Bloque Custom / HTML Libre</span>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1 text-xs">Ingresa tu código HTML / CSS</label>
                                <textarea name="custom_html" id="createCustomHtml" rows="5" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 text-slate-100 font-mono text-[11px] rounded-lg" placeholder="<!-- Tu código HTML aquí -->&#10;<div class='flex gap-2 justify-center'>&#10;  <button class='btn'>Botón 1</button>&#10;</div>" oninput="window.updatePreviewCreate && window.updatePreviewCreate()"></textarea>
                            </div>
                        </div>

                        <!-- 5. GESTIÓN DE ESPACIADOS ENTRE SECCIONES (TASK-SPACING-MODULAR) -->
                        <div id="createSpacingWrap" class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <span class="block font-bold text-slate-800 text-[11px] uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                    <span>Espaciado Vertical de Sección (Márgenes)</span>
                                </span>
                                <span class="text-[10px] text-slate-400 font-medium">Por defecto: 0px (Sin espacios)</span>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Espaciado Superior -->
                                <div class="p-2.5 bg-slate-50 border border-slate-100 rounded-lg space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                            <span>Margen Superior</span>
                                        </label>
                                        <div class="flex items-center gap-1">
                                            <input type="number" id="inpCreateSpacingTop" name="settings[spacing_top]" min="0" max="160" step="1" value="0" class="w-14 px-1.5 py-0.5 text-center text-xs font-mono font-bold bg-white border border-slate-200 rounded" oninput="window.setSectionSpacing('create', 'top', this.value)">
                                            <span class="text-[10px] text-slate-500 font-semibold">px</span>
                                        </div>
                                    </div>
                                    <input type="range" id="rangeCreateSpacingTop" min="0" max="120" step="2" value="0" class="w-full accent-indigo-600 cursor-pointer h-1.5 bg-slate-200 rounded-lg" oninput="window.setSectionSpacing('create', 'top', this.value)">
                                    <!-- Presets rápidos -->
                                    <div class="flex items-center gap-1">
                                        <span class="text-[9px] text-slate-400 uppercase font-bold mr-0.5">Presets:</span>
                                        <button type="button" onclick="window.setSectionSpacing('create', 'top', 0)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">0px</button>
                                        <button type="button" onclick="window.setSectionSpacing('create', 'top', 12)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">12px</button>
                                        <button type="button" onclick="window.setSectionSpacing('create', 'top', 24)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">24px</button>
                                        <button type="button" onclick="window.setSectionSpacing('create', 'top', 36)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">36px</button>
                                        <button type="button" onclick="window.setSectionSpacing('create', 'top', 48)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">48px</button>
                                    </div>
                                </div>

                                <!-- Espaciado Inferior -->
                                <div class="p-2.5 bg-slate-50 border border-slate-100 rounded-lg space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                            <span>Margen Inferior</span>
                                        </label>
                                        <div class="flex items-center gap-1">
                                            <input type="number" id="inpCreateSpacingBottom" name="settings[spacing_bottom]" min="0" max="160" step="1" value="0" class="w-14 px-1.5 py-0.5 text-center text-xs font-mono font-bold bg-white border border-slate-200 rounded" oninput="window.setSectionSpacing('create', 'bottom', this.value)">
                                            <span class="text-[10px] text-slate-500 font-semibold">px</span>
                                        </div>
                                    </div>
                                    <input type="range" id="rangeCreateSpacingBottom" min="0" max="120" step="2" value="0" class="w-full accent-indigo-600 cursor-pointer h-1.5 bg-slate-200 rounded-lg" oninput="window.setSectionSpacing('create', 'bottom', this.value)">
                                    <!-- Presets rápidos -->
                                    <div class="flex items-center gap-1">
                                        <span class="text-[9px] text-slate-400 uppercase font-bold mr-0.5">Presets:</span>
                                        <button type="button" onclick="window.setSectionSpacing('create', 'bottom', 0)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">0px</button>
                                        <button type="button" onclick="window.setSectionSpacing('create', 'bottom', 12)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">12px</button>
                                        <button type="button" onclick="window.setSectionSpacing('create', 'bottom', 24)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">24px</button>
                                        <button type="button" onclick="window.setSectionSpacing('create', 'bottom', 36)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">36px</button>
                                        <button type="button" onclick="window.setSectionSpacing('create', 'bottom', 48)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">48px</button>
                                    </div>
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

            <!-- COLUMNA DERECHA: PREVIEW EN VIVO -->
            <div class="flex-1 bg-slate-100 p-0 sm:p-6 flex flex-col relative z-10">
                <!-- Toggle de Dispositivo -->
                <div class="absolute top-6 left-6 z-40 flex bg-white rounded-lg shadow-sm p-1 border border-slate-200" id="createPreviewDeviceGroup">
                    <button type="button" class="create-preview-device-btn active px-3 py-1.5 rounded text-xs font-bold bg-indigo-50 text-indigo-700 cursor-pointer" data-device="desktop">Desktop</button>
                    <button type="button" class="create-preview-device-btn px-3 py-1.5 rounded text-xs font-bold text-slate-500 hover:text-slate-700 cursor-pointer" data-device="mobile">Mobile</button>
                </div>
                
                <!-- Contenedor del Preview Visual -->
                <div class="flex-1 flex items-center justify-center p-4 overflow-hidden mt-16">
                    <div id="livePreviewContainer" class="w-full h-[50vh] md:h-[55vh] max-h-[500px] bg-white rounded-xl shadow-md border border-slate-200 overflow-y-auto overflow-x-hidden transition-all duration-300 relative" style="max-width: 100%;">
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
                        <div id="livePreviewContent" class="w-full min-h-[250px] flex flex-col bg-white">
                            
                            <!-- 1. Hero / Sección Preview -->
                            <div id="previewHeroArea" class="w-full p-8 md:p-12 flex flex-col items-center justify-center text-center relative bg-slate-900 min-h-[250px] overflow-hidden transition-all duration-300">
                                <video id="createPreviewHeroVideo" class="absolute inset-0 w-full h-full object-cover z-0 hidden" autoplay loop muted playsinline></video>
                                <div id="previewOverlay" class="absolute inset-0 z-0 hidden"></div>
                                <div class="relative z-10 max-w-2xl mx-auto space-y-4 w-full">
                                    <span id="previewBadge" class="inline-block px-3 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-bold rounded-full uppercase tracking-widest hidden"></span>
                                    <h2 id="previewTitle" class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Tu Título Aquí</h2>
                                    <p id="previewSubtitle" class="text-slate-300 md:text-lg max-w-xl mx-auto hidden">Tu bajada o subtítulo aquí.</p>
                                    
                                    <div id="previewActions" class="flex flex-wrap justify-center gap-3 pt-4 hidden">
                                        <button id="previewBtnPrimary" class="px-6 py-2.5 bg-slate-900 text-white rounded-lg font-bold text-sm shadow-sm hidden transition-colors"></button>
                                        <button id="previewBtnSecondary" class="px-6 py-2.5 bg-white text-slate-900 border border-slate-200 rounded-lg font-bold text-sm hidden transition-colors"></button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 2. Carrusel Preview -->
                            <div id="previewCarouselArea" class="hidden w-full h-[320px] min-h-[320px] bg-slate-900 relative overflow-hidden"></div>

                            <!-- 3. Pop-up Preview (Full Image + Gradient Overlay + Floating Content) -->
                            <div id="previewPopUpArea" class="hidden w-full min-h-[500px] bg-slate-900/60 backdrop-blur-xs relative flex items-center justify-center p-4">
                                <div id="createPopupCardPreview" class="relative w-full max-w-sm rounded-2xl overflow-hidden shadow-2xl border border-white/10 flex flex-col justify-end p-5 min-h-[460px] text-white bg-slate-900 transition-all">
                                    <!-- Background Layer with focal point -->
                                    <div id="createPopupBgLayer" class="absolute inset-0 bg-cover bg-no-repeat transition-all" style="background-position: center center; background-image: none;"></div>
                                    <!-- Gradient Overlay Layer -->
                                    <div id="createPopupOverlayLayer" class="absolute inset-0 transition-opacity" style="background: linear-gradient(to bottom, rgba(15, 23, 42, 0.4) 0%, rgba(0, 0, 0, 0.95) 100%);"></div>
                                    <!-- Simulated Close Button -->
                                    <button type="button" class="absolute top-3 right-3 z-20 w-7 h-7 rounded-full bg-black/40 text-white/80 flex items-center justify-center backdrop-blur-xs text-xs font-bold pointer-events-none">&times;</button>
                                    <!-- Floating Content on top of image -->
                                    <div class="relative z-10 flex flex-col gap-2.5">
                                        <div id="createPopupBadgePreview" class="self-start px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-indigo-500/80 backdrop-blur-xs text-white border border-white/20">
                                            BENEFICIO EXCLUSIVO
                                        </div>
                                        <h3 id="createPopupTitlePreview" class="text-xl font-black leading-tight text-white drop-shadow-md">
                                            Descuento Especial
                                        </h3>
                                        <p id="createPopupSubtitlePreview" class="text-xs text-slate-200 drop-shadow-sm leading-relaxed">
                                            Suscríbete con tu WhatsApp para recibir tu código único y guardarlo en tu cuenta.
                                        </p>
                                        <!-- Simulated Form -->
                                        <div class="space-y-2 pt-1">
                                            <input type="text" placeholder="Tu Nombre Completo" class="w-full px-2.5 py-1.5 text-xs rounded-lg bg-white/90 text-slate-800 placeholder-slate-400 border border-white/20 pointer-events-none" disabled>
                                            <div class="flex gap-1.5">
                                                <span class="px-2 py-1.5 text-xs font-bold rounded-lg bg-emerald-600 text-white flex items-center gap-1">+52</span>
                                                <input type="tel" placeholder="WhatsApp (10 dígitos)" class="w-full px-2.5 py-1.5 text-xs rounded-lg bg-white/90 text-slate-800 placeholder-slate-400 border border-white/20 pointer-events-none" disabled>
                                            </div>
                                            <input type="email" placeholder="Correo Electrónico" class="w-full px-2.5 py-1.5 text-xs rounded-lg bg-white/90 text-slate-800 placeholder-slate-400 border border-white/20 pointer-events-none" disabled>
                                            <button type="button" id="createPopupCtaBtnPreview" class="w-full py-2 bg-indigo-600 text-white font-bold text-xs rounded-lg shadow-md pointer-events-none uppercase tracking-wide">
                                                Obtener Código Único
                                            </button>
                                            <div class="flex items-center justify-center gap-1 text-[9px] text-slate-300 text-center">
                                                <svg class="w-2.5 h-2.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                <span>Código único de un solo uso &bull; Envío instantáneo a WhatsApp</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Botones / Custom Preview -->
                            <div id="previewButtonsArea" class="hidden w-full p-8 md:p-12 flex flex-col items-center text-center bg-white">
                                <h2 class="preview-common-title text-2xl font-bold text-slate-900 mb-3"></h2>
                                <p class="preview-common-subtitle text-slate-600 mb-8 max-w-lg mx-auto"></p>
                                <div id="createCustomHtmlPreviewContainer" class="w-full">
                                    <div class="p-8 bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl w-full text-slate-400 text-xs flex items-center justify-center flex-col gap-2">
                                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                        Área de Bloque Personalizado / Código HTML Inyectado
                                    </div>
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
                        const createSectionType = document.getElementById('createSectionType');
                        if (!createSectionType) return;
                        const val = createSectionType.value;
                        const isCustom = document.querySelector('input[name="settings[hero_mode]"]:checked')?.value === 'custom';
                        
                        const createHeroWrap = document.getElementById('createHeroWrap');
                        const createCarouselWrap = document.getElementById('createCarouselWrap');
                        const createPopUpWrap = document.getElementById('createPopUpWrap');
                        const createButtonsWrap = document.getElementById('createButtonsWrap');
                        const createCustomCodeWrap = document.getElementById('createCustomCodeWrap');
                        const createCommonFieldsWrap = document.getElementById('createCommonFieldsWrap');
                        const createSpacingWrap = document.getElementById('createSpacingWrap');

                        if (isCustom) {
                            if(createHeroWrap) createHeroWrap.style.display = 'none';
                            if(createCarouselWrap) createCarouselWrap.style.display = 'none';
                            if(createPopUpWrap) createPopUpWrap.style.display = 'none';
                            if(createButtonsWrap) createButtonsWrap.style.display = 'none';
                            if(createCommonFieldsWrap) createCommonFieldsWrap.style.display = 'none';
                            if(createCustomCodeWrap) createCustomCodeWrap.style.display = 'block';
                            if(createSpacingWrap) createSpacingWrap.style.display = 'block';
                        } else {
                            if(createCommonFieldsWrap) createCommonFieldsWrap.style.display = (val === 'carousel') ? 'none' : 'block';
                            if(createCustomCodeWrap) createCustomCodeWrap.style.display = 'none';
                            if(createHeroWrap) createHeroWrap.style.display = val === 'hero' ? 'block' : 'none';
                            if(createCarouselWrap) createCarouselWrap.style.display = val === 'carousel' ? 'block' : 'none';
                            if(createPopUpWrap) createPopUpWrap.style.display = val === 'pop_up' ? 'block' : 'none';
                            if(createButtonsWrap) createButtonsWrap.style.display = val === 'buttons' ? 'block' : 'none';
                            if(createSpacingWrap) createSpacingWrap.style.display = val === 'pop_up' ? 'none' : 'block';
                            if (val === 'carousel') {
                                const slidesList = document.getElementById('createCarouselSlidesList');
                                if (slidesList && slidesList.querySelectorAll('.slide-item').length === 0 && typeof window.addCarouselSlide === 'function') {
                                    window.addCarouselSlide('create');
                                }
                            }
                            if (val === 'pop_up') {
                                if (typeof window.handlePopupActionChange === 'function') {
                                    window.handlePopupActionChange('create');
                                }
                            }
                        }
                    }

                    if (createSectionType) {
                        createSectionType.addEventListener('change', () => {
                            switchSectionType();
                            if(typeof window.updatePreviewCreate === 'function') window.updatePreviewCreate();
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
                    const previewCarouselArea = document.getElementById('previewCarouselArea');
                    const previewPopUpArea = document.getElementById('previewPopUpArea');
                    const previewButtonsArea = document.getElementById('previewButtonsArea');
                    
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

                    window.updatePreviewCreate = function() {

                        const typeVal = createSectionType ? createSectionType.value : 'hero';

                        // Toggle preview areas
                        if (previewHeroArea) previewHeroArea.style.display = typeVal === 'hero' ? 'flex' : 'none';
                        if (previewCarouselArea) previewCarouselArea.style.display = typeVal === 'carousel' ? 'flex' : 'none';
                        if (previewPopUpArea) previewPopUpArea.style.display = typeVal === 'pop_up' ? 'flex' : 'none';
                        if (previewButtonsArea) previewButtonsArea.style.display = typeVal === 'buttons' ? 'flex' : 'none';

                        // Espaciado vertical (Márgenes superior e inferior en preview)
                        const spTop = document.getElementById('inpCreateSpacingTop') ? (parseInt(document.getElementById('inpCreateSpacingTop').value, 10) || 0) : 0;
                        const spBottom = document.getElementById('inpCreateSpacingBottom') ? (parseInt(document.getElementById('inpCreateSpacingBottom').value, 10) || 0) : 0;
                        [previewHeroArea, previewCarouselArea, previewButtonsArea].forEach(area => {
                            if (area) {
                                area.style.marginTop = spTop + 'px';
                                area.style.marginBottom = spBottom + 'px';
                            }
                        });

                        // Actualizar textos globales para las nuevas áreas
                        const defaultTitle = typeVal === 'buttons' ? 'Bloque Personalizado' : 'Tu Título Aquí';
                        document.querySelectorAll('#createSectionModal .preview-common-title').forEach(el => {
                            el.textContent = titleInput && titleInput.value ? titleInput.value : defaultTitle;
                        });
                        document.querySelectorAll('#createSectionModal .preview-common-subtitle').forEach(el => {
                            el.textContent = subtitleInput && subtitleInput.value ? subtitleInput.value : 'Tu bajada o subtítulo aquí.';
                            el.style.display = subtitleInput && subtitleInput.value ? 'block' : 'none';
                        });

                        // Textos y Tipografías Independientes para Hero
                        if (titleInput && previewTitle) {
                            previewTitle.textContent = titleInput.value || 'Tu Hero Principal';
                            if (inpCreateTitleColor && inpCreateTitleColor.value) {
                                previewTitle.style.color = inpCreateTitleColor.value;
                            } else {
                                previewTitle.style.color = '';
                            }
                            const tFontInp = document.getElementById('inpCreateTitleFont');
                            if (tFontInp && tFontInp.value && tFontInp.value !== 'inherit') {
                                if (window.loadGoogleFont) window.loadGoogleFont(tFontInp.value);
                                previewTitle.style.fontFamily = `'${tFontInp.value}', sans-serif`;
                            } else {
                                previewTitle.style.fontFamily = '';
                            }
                        }
                        if (subtitleInput && previewSubtitle) {
                            previewSubtitle.textContent = subtitleInput.value;
                            previewSubtitle.style.display = subtitleInput.value ? 'block' : 'none';
                            if (inpCreateSubtitleColor && inpCreateSubtitleColor.value) {
                                previewSubtitle.style.color = inpCreateSubtitleColor.value;
                            } else {
                                previewSubtitle.style.color = '';
                            }
                            const sFontInp = document.getElementById('inpCreateSubtitleFont');
                            if (sFontInp && sFontInp.value && sFontInp.value !== 'inherit') {
                                if (window.loadGoogleFont) window.loadGoogleFont(sFontInp.value);
                                previewSubtitle.style.fontFamily = `'${sFontInp.value}', sans-serif`;
                            } else {
                                previewSubtitle.style.fontFamily = '';
                            }
                        }
                        if (badgeInput && previewBadge) {
                            previewBadge.textContent = badgeInput.value;
                            previewBadge.style.display = badgeInput.value ? 'inline-block' : 'none';
                        }
                        
                        // Alineación, altura y efecto Parallax del contenedor de texto
                        if (previewHeroArea) {
                            const inpAlign = document.querySelector('#createSectionModal input[name="settings[text_align]"]:checked');
                            const alignVal = inpAlign ? inpAlign.value : 'text-center';
                            
                            const innerWrapper = previewHeroArea.querySelector('.relative.z-10');
                            
                            previewHeroArea.classList.remove('text-center', 'text-left', 'text-right', 'items-center', 'items-start', 'items-end');
                            if (innerWrapper) innerWrapper.classList.remove('mx-auto', 'ml-0', 'mr-auto', 'mr-0', 'ml-auto');
                            if (previewSubtitle) previewSubtitle.classList.remove('mx-auto', 'ml-0', 'mr-auto', 'mr-0', 'ml-auto');
                            if (previewActions) previewActions.classList.remove('justify-center', 'justify-start', 'justify-end');
                            
                            const isFullPage = document.getElementById('inpCreateFullWidth') && document.getElementById('inpCreateFullWidth').checked;
                            const isFullWidthH = document.getElementById('inpCreateFullWidthHorizontal') && document.getElementById('inpCreateFullWidthHorizontal').checked;
                            const heightVal = document.getElementById('inpCreateHeroHeightValue') ? document.getElementById('inpCreateHeroHeightValue').value : '';
                            const heightUnit = document.getElementById('inpCreateHeroHeightUnit') ? document.getElementById('inpCreateHeroHeightUnit').value : 'px';
                            const customHeight = heightVal ? heightVal + heightUnit : '';
                            
                            if (isFullPage) {
                                previewHeroArea.style.minHeight = '400px';
                            } else if (customHeight.trim() !== '') {
                                previewHeroArea.style.minHeight = customHeight;
                            } else {
                                previewHeroArea.style.minHeight = '150px';
                            }

                            if (isFullWidthH) {
                                previewHeroArea.classList.add('w-full', '!rounded-none');
                                previewHeroArea.classList.remove('rounded-xl');
                            } else {
                                previewHeroArea.classList.remove('!rounded-none');
                            }

                            // Efecto Parallax Scrolling
                            const inpParallax = document.getElementById('inpCreateParallax');
                            if (inpParallax && inpParallax.checked) {
                                previewHeroArea.style.backgroundAttachment = 'fixed';
                            } else {
                                previewHeroArea.style.backgroundAttachment = 'scroll';
                            }

                            if (alignVal === 'text-left') {
                                previewHeroArea.classList.add('text-left', 'items-start');
                                if (innerWrapper) innerWrapper.classList.add('ml-0', 'mr-auto');
                                if (previewSubtitle) previewSubtitle.classList.add('ml-0', 'mr-auto');
                                if (previewActions) previewActions.classList.add('justify-start');
                            } else if (alignVal === 'text-right') {
                                previewHeroArea.classList.add('text-right', 'items-end');
                                if (innerWrapper) innerWrapper.classList.add('ml-auto', 'mr-0');
                                if (previewSubtitle) previewSubtitle.classList.add('ml-auto', 'mr-0');
                                if (previewActions) previewActions.classList.add('justify-end');
                            } else {
                                previewHeroArea.classList.add('text-center', 'items-center');
                                if (innerWrapper) innerWrapper.classList.add('mx-auto');
                                if (previewSubtitle) previewSubtitle.classList.add('mx-auto');
                                if (previewActions) previewActions.classList.add('justify-center');
                            }
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
                                
                                const bW = document.getElementById('inpCreateCtaPrimaryBorderWidth') ? document.getElementById('inpCreateCtaPrimaryBorderWidth').value : 'border-0';
                                const bC = document.getElementById('inpCreateCtaPrimaryBorderColor') ? document.getElementById('inpCreateCtaPrimaryBorderColor').value : 'transparent';
                                const shadow = document.getElementById('inpCreateCtaPrimaryShadow') ? document.getElementById('inpCreateCtaPrimaryShadow').value : '';
                                
                                previewBtnPrimary.className = `px-6 py-2.5 rounded-lg font-bold text-sm transition-colors ${bW} ${shadow}`;
                                if(bW !== 'border-0') {
                                    previewBtnPrimary.style.borderColor = bC;
                                } else {
                                    previewBtnPrimary.style.borderColor = 'transparent';
                                }
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
                                
                                const bW2 = document.getElementById('inpCreateCtaSecondaryBorderWidth') ? document.getElementById('inpCreateCtaSecondaryBorderWidth').value : 'border-0';
                                const bC2 = document.getElementById('inpCreateCtaSecondaryBorderColor') ? document.getElementById('inpCreateCtaSecondaryBorderColor').value : 'transparent';
                                const shadow2 = document.getElementById('inpCreateCtaSecondaryShadow') ? document.getElementById('inpCreateCtaSecondaryShadow').value : '';
                                
                                previewBtnSecondary.className = `px-6 py-2.5 rounded-lg font-bold text-sm transition-colors ${bW2} ${shadow2}`;
                                if(bW2 !== 'border-0') {
                                    previewBtnSecondary.style.borderColor = bC2;
                                } else {
                                    previewBtnSecondary.style.borderColor = 'transparent';
                                }
                            }
                        } else {
                            if(previewBtnSecondary) previewBtnSecondary.style.display = 'none';
                        }
                        
                        const inpHasButtons = document.getElementById('inpCreateHeroHasButtons');
                        const hasButtons = inpHasButtons ? inpHasButtons.checked : true;
                        const clickUrlInp = document.getElementById('createHeroClickUrl');
                        const heroClickUrl = clickUrlInp ? clickUrlInp.value.trim() : '';

                        if (previewActions) {
                            previewActions.style.display = (hasButtons && hasBtn) ? 'flex' : 'none';
                        }

                        let clickBadge = document.getElementById('createPreviewHeroClickBadge');
                        if (previewHeroArea) {
                            const innerWrapper = previewHeroArea.querySelector('.relative.z-10');
                            if (!hasButtons) {
                                previewHeroArea.style.cursor = 'pointer';
                                previewHeroArea.title = heroClickUrl ? ('Clic redirige a: ' + heroClickUrl) : 'Hero clicable en toda el área';
                                if (!clickBadge) {
                                    clickBadge = document.createElement('div');
                                    clickBadge.id = 'createPreviewHeroClickBadge';
                                    clickBadge.className = 'mt-3 inline-flex items-center gap-1.5 px-3 py-1 bg-amber-500/20 text-amber-200 border border-amber-400/40 rounded-full text-[11px] font-semibold backdrop-blur-xs relative z-10';
                                    if (innerWrapper) innerWrapper.appendChild(clickBadge);
                                    else previewHeroArea.appendChild(clickBadge);
                                }
                                clickBadge.style.display = 'inline-flex';
                                clickBadge.innerHTML = `<svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg> Clic en todo el Hero ${heroClickUrl ? '→ ' + heroClickUrl : '(Sin URL asignada)'}`;
                            } else {
                                previewHeroArea.style.cursor = 'default';
                                previewHeroArea.title = '';
                                if (clickBadge) clickBadge.style.display = 'none';
                            }
                        }

                        // Estilo Hero Completo
                        if (previewHeroArea) {
                            previewHeroArea.classList.remove('bg-white', 'min-h-[150px]', 'border-b', 'border-slate-100', '!p-6', 'text-slate-900');
                            previewHeroArea.classList.add('bg-slate-900', 'min-h-[250px]', 'p-8');
                            
                            const bgType = document.getElementById('createHeroBgType')?.value || 'image';
                            const videoEl = document.getElementById('createPreviewHeroVideo');
                            const videoFileInp = document.querySelector('#createSectionModal input[name="background_video_file"]');
                            const videoUrlInp = document.getElementById('createHeroBgVideoUrl');
                            const videoAutoplay = document.getElementById('createHeroVideoAutoplay')?.checked ?? true;
                            const videoLoop = document.getElementById('createHeroVideoLoop')?.checked ?? true;
                            const videoMuted = document.getElementById('createHeroVideoMuted')?.checked ?? true;

                            const focalInpCreate = document.getElementById('inpCreateHeroFocalPoint');
                            const focalValCreate = focalInpCreate && focalInpCreate.value ? focalInpCreate.value : 'center center';

                            if (bgType === 'video' && videoEl) {
                                let vidSrc = '';
                                if (videoFileInp && videoFileInp.files && videoFileInp.files[0]) {
                                    vidSrc = URL.createObjectURL(videoFileInp.files[0]);
                                } else if (videoUrlInp && videoUrlInp.value) {
                                    vidSrc = videoUrlInp.value;
                                }
                                if (vidSrc) {
                                    if (videoEl.src !== vidSrc) videoEl.src = vidSrc;
                                    videoEl.autoplay = videoAutoplay;
                                    videoEl.loop = videoLoop;
                                    videoEl.muted = videoMuted;
                                    videoEl.style.objectPosition = focalValCreate;
                                    videoEl.classList.remove('hidden');
                                    videoEl.play().catch(() => {});
                                    previewHeroArea.style.backgroundImage = 'none';
                                } else {
                                    videoEl.classList.add('hidden');
                                    videoEl.pause();
                                }
                            } else {
                                if (videoEl) {
                                    videoEl.classList.add('hidden');
                                    videoEl.pause();
                                }
                                if (window._currentHeroPreviewBg_create) {
                                    previewHeroArea.style.backgroundImage = window._currentHeroPreviewBg_create;
                                    previewHeroArea.classList.add('bg-cover');
                                    previewHeroArea.classList.remove('bg-center');
                                    previewHeroArea.style.setProperty('background-position', focalValCreate, 'important');
                                } else {
                                    previewHeroArea.style.backgroundImage = 'none';
                                }
                            }
                        }

                        if (previewTitle) previewTitle.className = 'text-3xl md:text-4xl font-extrabold tracking-tight drop-shadow-md relative z-10';
                        if (previewSubtitle) previewSubtitle.className = 'md:text-base max-w-xl mx-auto mt-4 drop-shadow-sm block relative z-10';

                        // Renderizado de Carrusel en Vivo (Viewport Animado Completo)
                        if (typeVal === 'carousel' && previewCarouselArea) {
                            if (typeof window.renderCarouselLivePreview === 'function') {
                                window.renderCarouselLivePreview('create');
                            }
                        }

                        // Renderizado de Pop-up en Vivo (Imagen Completa, Overlay y 2FA)
                        if (typeVal === 'pop_up' && previewPopUpArea) {
                            if (typeof window.renderPopUpLivePreview === 'function') {
                                window.renderPopUpLivePreview('create');
                            }
                        }

                        // Renderizado de Código Custom / HTML Libre en Vivo
                        const isCustom = document.querySelector('#createSectionModal input[name="settings[hero_mode]"]:checked')?.value === 'custom';
                        const customHtmlInp = document.getElementById('createCustomHtml');
                        const customPreviewContainer = document.getElementById('createCustomHtmlPreviewContainer');
                        if (customPreviewContainer && customHtmlInp) {
                            if (customHtmlInp.value && customHtmlInp.value.trim()) {
                                customPreviewContainer.innerHTML = customHtmlInp.value;
                            } else {
                                customPreviewContainer.innerHTML = `
                                    <div class="p-8 bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl w-full text-slate-400 text-xs flex items-center justify-center flex-col gap-2">
                                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                        Área de Bloque Personalizado / Código HTML Inyectado
                                    </div>`;
                            }
                        }

                        if (isCustom) {
                            if (previewHeroArea) previewHeroArea.style.display = 'none';
                            if (previewCarouselArea) previewCarouselArea.style.display = 'none';
                            if (previewPopUpArea) previewPopUpArea.style.display = 'none';
                            if (previewButtonsArea) previewButtonsArea.style.display = 'flex';
                        }
                    }

                    // Handlers para la imagen y video de fondo
                    const bgFileInput = document.querySelector('#createSectionModal input[name="background_image_file"]');
                    const bgUrlInput = document.getElementById('createHeroBgUrl');
                    const videoFileInput = document.querySelector('#createSectionModal input[name="background_video_file"]');
                    const videoUrlInput = document.getElementById('createHeroBgVideoUrl');

                    if (bgFileInput) {
                        bgFileInput.addEventListener('change', function(e) {
                            if (this.files && this.files[0]) {
                                const reader = new FileReader();
                                reader.onload = function(evt) {
                                    window._currentHeroPreviewBg_create = `url('${evt.target.result}')`;
                                    window.updatePreviewCreate();
                                }
                                reader.readAsDataURL(this.files[0]);
                            } else {
                                window._currentHeroPreviewBg_create = '';
                                window.updatePreviewCreate();
                            }
                        });
                    }

                    if (bgUrlInput) {
                        bgUrlInput.addEventListener('input', function(e) {
                            if (this.value) {
                                window._currentHeroPreviewBg_create = `url('${this.value}')`;
                            } else {
                                window._currentHeroPreviewBg_create = '';
                            }
                            window.updatePreviewCreate();
                        });
                    }

                    if (videoFileInput) {
                        videoFileInput.addEventListener('change', () => { window.updatePreviewCreate && window.updatePreviewCreate(); });
                    }
                    if (videoUrlInput) {
                        videoUrlInput.addEventListener('input', () => { window.updatePreviewCreate && window.updatePreviewCreate(); });
                    }

                    // Attach universal event listeners para todos los inputs del modal de creación
                    const createForm = document.getElementById('createSectionForm') || document.getElementById('createSectionModal');
                    if (createForm) {
                        createForm.addEventListener('input', () => { window.updatePreviewCreate && window.updatePreviewCreate(); });
                        createForm.addEventListener('change', () => { window.updatePreviewCreate && window.updatePreviewCreate(); });
                    }

                    // Device toggles
                    document.querySelectorAll('.create-preview-device-btn').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            document.querySelectorAll('.create-preview-device-btn').forEach(b => {
                                b.classList.remove('bg-indigo-50', 'text-indigo-700', 'active');
                                b.classList.add('text-slate-500');
                            });
                            e.currentTarget.classList.add('bg-indigo-50', 'text-indigo-700', 'active');
                            e.currentTarget.classList.remove('text-slate-500');
                            
                            const container = document.getElementById('livePreviewContainer');
                            if (container) {
                                if (e.currentTarget.dataset.device === 'mobile') {
                                    container.style.maxWidth = '375px';
                                    container.classList.add('mx-auto');
                                } else {
                                    container.style.maxWidth = '100%';
                                    container.classList.remove('mx-auto');
                                }
                            }
                        });
                    });

                    // Overlay / Degradado interactivo
                    const gradEnableInput = document.getElementById('createEnableGradient');
                    const gradStartInput = document.getElementById('createGradColorStartPicker');
                    const gradEndInput = document.getElementById('createGradColorEndPicker');
                    const gradDirInput = document.getElementById('createGradDir');
                    const gradOpacityStartInput = document.getElementById('createGradOpacityStart');
                    const gradOpacityEndInput = document.getElementById('createGradOpacityEnd');
                    const previewOverlay = document.getElementById('previewOverlay');

                    function hexToRgba(hex, alpha) {
                        let c = (hex || '#000000').replace('#', '');
                        if (c.length === 3) c = c.split('').map(x => x + x).join('');
                        const num = parseInt(c, 16) || 0;
                        return `rgba(${(num >> 16) & 255}, ${(num >> 8) & 255}, ${num & 255}, ${alpha})`;
                    }

                    function updateOverlay() {
                        if (gradEnableInput && gradEnableInput.checked && previewOverlay) {
                            previewOverlay.style.display = 'block';
                            previewOverlay.className = "absolute inset-0 z-0";
                            
                            let dir = gradDirInput ? gradDirInput.value : 'to bottom';
                            let startHex = gradStartInput ? gradStartInput.value : '#0f172a';
                            let endHex = gradEndInput ? gradEndInput.value : '#1e1b4b';
                            let startOp = gradOpacityStartInput ? parseInt(gradOpacityStartInput.value, 10) / 100 : 0.8;
                            let endOp = gradOpacityEndInput ? parseInt(gradOpacityEndInput.value, 10) / 100 : 0.8;
                            
                            let startRgba = hexToRgba(startHex, startOp);
                            let endRgba = hexToRgba(endHex, endOp);
                            
                            previewOverlay.style.background = `linear-gradient(${dir}, ${startRgba}, ${endRgba})`;
                            previewOverlay.style.opacity = 1;
                        } else {
                            if (previewOverlay) previewOverlay.style.display = 'none';
                        }
                    }

                    const gradInputs = [gradEnableInput, gradStartInput, gradEndInput, gradDirInput, gradOpacityStartInput, gradOpacityEndInput];
                    gradInputs.forEach(el => {
                        if (el) el.addEventListener('input', updateOverlay);
                        if (el) el.addEventListener('change', updateOverlay);
                    });

                    const origUpdatePreview = window.updatePreviewCreate;
                    window.updatePreviewCreate = function() {
                        origUpdatePreview();
                        updateOverlay();
                    };

                    // Trigger initial paint
                    setTimeout(window.updatePreviewCreate, 100);
                    document.querySelectorAll('#createSectionModal input[name="settings[text_align]"]').forEach(r => r.addEventListener('change', window.updatePreviewCreate));
                });
            </script>
        </div>
    </div>

    <!-- 1B. MODAL EDITAR SECCIÓN EXISTENTE -->
    <div id="editSectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-0 bg-slate-900/60 backdrop-blur-xs hidden" style="display: none;">
        <div class="bg-white w-full h-full flex flex-col md:flex-row shadow-2xl overflow-hidden relative">
            
            <!-- Botón de cerrar general (Solo visible en Desktop, en mobile va en el header) -->
            <button type="button" class="hidden md:flex absolute top-4 right-4 z-50 w-10 h-10 items-center justify-center bg-white border border-slate-200 rounded-full text-slate-500 hover:text-slate-900 hover:bg-slate-100 shadow-md transition font-bold text-2xl cursor-pointer" data-action="close-modal" data-modal="editSectionModal">&times;</button>

            <!-- COLUMNA IZQUIERDA: CONFIGURACIONES -->
            <div class="w-full md:w-[720px] shrink-0 p-4 md:p-6 overflow-y-auto border-b md:border-b-0 md:border-r border-slate-200 bg-white flex flex-col relative z-20 custom-scrollbar">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4 shrink-0">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Editar Sección Modular</span>
                    </h3>
                    <button type="button" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer md:hidden" data-action="close-modal" data-modal="editSectionModal">&times;</button>
                </div>



                <form method="POST" action="" id="editSectionForm" enctype="multipart/form-data" class="space-y-4 text-xs flex-1 flex flex-col">
                    @csrf
                    @method('PUT')
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



                        <div id="editTypeWrap" class="mb-4">
                            <label class="block font-semibold text-slate-700 mb-1">Tipo de Sección de Contenido</label>
                            <select name="type" id="editSectionType" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="hero">Hero Principal de Vitrina</option>
                                <option value="carousel">Carrusel de Banners Promocionales</option>
                                <option value="pop_up">Modal Promocional / Pop-up Flotante</option>
                                <option value="buttons">Botones CTA / Bloque Personalizado</option>
                            </select>
                        </div>

<!-- 1. BIFURCADOR DE MODO -->
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Modo de Renderizado</label>
                                    <div class="grid grid-cols-2 gap-2 p-1 bg-slate-200/70 rounded-xl text-xs font-semibold">
                                        <label id="editHeroModeStructuredLbl" class="flex items-center justify-center py-2 px-3 rounded-lg cursor-pointer transition bg-white text-indigo-700 shadow-xs text-center">
                                            <input type="radio" name="settings[hero_mode]" value="structured" checked class="hidden" onchange="window.toggleHeroModeEdit(this.value)">
                                            <span>Visual Estructurado</span>
                                        </label>
                                        <label id="editHeroModeCustomLbl" class="flex items-center justify-center py-2 px-3 rounded-lg cursor-pointer transition text-slate-600 hover:text-slate-900 text-center">
                                            <input type="radio" name="settings[hero_mode]" value="custom" class="hidden" onchange="window.toggleHeroModeEdit(this.value)">
                                            <span>Código Custom</span>
                                        </label>
                                    </div>
                                </div>

                                


                        <!-- CONTENIDO Y ESTILOS DE TEXTO DEL HERO -->
                        <div id="editCommonFieldsWrap" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-4 mb-4">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                <span class="font-bold text-slate-800 text-xs flex items-center gap-1.5 uppercase tracking-wider">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Contenido y Estilos de Texto
                                </span>
                                <span class="text-[10px] text-slate-400 font-medium">Textos, tipografías y alineación</span>
                            </div>

                            <!-- TÍTULO PRINCIPAL (H2) + TIPOGRAFÍA + COLOR -->
                            <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-2">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1 text-xs">Título Público (H2)</label>
                                    <input type="text" name="title" id="inpEditTitle" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Ej: BOXERS & JOCKS DEPORTIVOS" oninput="window.updatePreviewEdit && window.updatePreviewEdit()">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 pt-1 border-t border-slate-100">
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Tipografía del Título</label>
                                        <select name="settings[title_font]" id="inpEditTitleFont" class="w-full px-2 py-1.5 text-xs border border-slate-200 rounded-lg bg-white" onchange="window.loadGoogleFont && window.loadGoogleFont(this.value); window.updatePreviewEdit && window.updatePreviewEdit()">
                                            <option value="inherit">Heredar de Vitrina (Default)</option>
                                            @foreach($curatedGoogleFonts as $fKey => $fLabel)
                                                <option value="{{ $fKey }}">{{ $fLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color del Título</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="color" name="settings[title_color]" id="inpEditTitleColor" value="#ffffff" class="w-8 h-7 p-0 border border-slate-200 rounded cursor-pointer shrink-0" oninput="document.getElementById('inpEditTitleColorHex').value = this.value; window.updatePreviewEdit && window.updatePreviewEdit()">
                                            <input type="text" id="inpEditTitleColorHex" value="#ffffff" class="w-full px-2 py-1 text-xs font-mono border border-slate-200 rounded-lg" oninput="document.getElementById('inpEditTitleColor').value = this.value; window.updatePreviewEdit && window.updatePreviewEdit()">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- BADGE / KICKER SUPERIOR -->
                            <div class="p-3 bg-white border border-slate-200 rounded-xl">
                                <label class="block font-semibold text-slate-700 mb-1 text-xs">Badge / Kicker Superior</label>
                                <input type="text" name="badge" id="inpEditBadge" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Ej: LANZAMIENTO EXCLUSIVO" oninput="window.updatePreviewEdit && window.updatePreviewEdit()">
                            </div>

                            <!-- BAJADA / SUBTÍTULO + TIPOGRAFÍA + COLOR -->
                            <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-2">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1 text-xs">Bajada / Subtítulo</label>
                                    <input type="text" name="subtitle" id="inpEditSubtitle" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Ej: Confeccionados con modal pima de secado ultra rápido." oninput="window.updatePreviewEdit && window.updatePreviewEdit()">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 pt-1 border-t border-slate-100">
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Tipografía de Bajada</label>
                                        <select name="settings[subtitle_font]" id="inpEditSubtitleFont" class="w-full px-2 py-1.5 text-xs border border-slate-200 rounded-lg bg-white" onchange="window.loadGoogleFont && window.loadGoogleFont(this.value); window.updatePreviewEdit && window.updatePreviewEdit()">
                                            <option value="inherit">Heredar de Vitrina (Default)</option>
                                            @foreach($curatedGoogleFonts as $fKey => $fLabel)
                                                <option value="{{ $fKey }}">{{ $fLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color de Bajada</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="color" name="settings[subtitle_color]" id="inpEditSubtitleColor" value="#e2e8f0" class="w-8 h-7 p-0 border border-slate-200 rounded cursor-pointer shrink-0" oninput="document.getElementById('inpEditSubtitleColorHex').value = this.value; window.updatePreviewEdit && window.updatePreviewEdit()">
                                            <input type="text" id="inpEditSubtitleColorHex" value="#e2e8f0" class="w-full px-2 py-1 text-xs font-mono border border-slate-200 rounded-lg" oninput="document.getElementById('inpEditSubtitleColor').value = this.value; window.updatePreviewEdit && window.updatePreviewEdit()">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ALINEACIÓN DE TEXTO -->
                            <div class="p-3 bg-white border border-slate-200 rounded-xl flex items-center justify-between">
                                <div>
                                    <span class="block font-semibold text-slate-800 text-xs">Alineación del Texto</span>
                                    <span class="text-[10px] text-slate-500">Orientación de títulos, bajadas y botones</span>
                                </div>
                                <div class="grid grid-cols-3 gap-1 p-0.5 bg-slate-100 rounded-lg border border-slate-200 w-32" id="inpEditTextAlignGroup">
                                    <label class="relative flex items-center justify-center py-1.5 rounded-md cursor-pointer text-slate-500 transition">
                                        <input type="radio" name="settings[text_align]" value="text-left" class="peer hidden" onchange="window.updatePreviewEdit && window.updatePreviewEdit()">
                                        <div class="peer-checked:bg-white peer-checked:shadow-sm peer-checked:text-indigo-600 absolute inset-0 rounded-md transition"></div>
                                        <svg class="w-4 h-4 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16"/></svg>
                                    </label>
                                    <label class="relative flex items-center justify-center py-1.5 rounded-md cursor-pointer text-slate-500 transition">
                                        <input type="radio" name="settings[text_align]" value="text-center" class="peer hidden" checked onchange="window.updatePreviewEdit && window.updatePreviewEdit()">
                                        <div class="peer-checked:bg-white peer-checked:shadow-sm peer-checked:text-indigo-600 absolute inset-0 rounded-md transition"></div>
                                        <svg class="w-4 h-4 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M4 18h16"/></svg>
                                    </label>
                                    <label class="relative flex items-center justify-center py-1.5 rounded-md cursor-pointer text-slate-500 transition">
                                        <input type="radio" name="settings[text_align]" value="text-right" class="peer hidden" onchange="window.updatePreviewEdit && window.updatePreviewEdit()">
                                        <div class="peer-checked:bg-white peer-checked:shadow-sm peer-checked:text-indigo-600 absolute inset-0 rounded-md transition"></div>
                                        <svg class="w-4 h-4 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M4 18h16"/></svg>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- 1. MODO HERO -->
                        <div id="editHeroWrap" class="space-y-4">
                            <!-- BLOQUE MOTOR DE HERO DINÁMICO -->
                            <div id="editHeroEngineWrap" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-4">
                                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                    <span class="font-bold text-slate-800 text-xs flex items-center gap-1.5 uppercase tracking-wider">
                                        Motor de Hero Dinámico
                                    </span>
                                </div>

                                <!-- 2. PANTALLA, ALTO Y PARALLAX SCROLLING -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div>
                                        <input type="hidden" name="settings[full_width]" value="0">
                                        <label class="flex items-center justify-between cursor-pointer p-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm h-full">
                                            <div>
                                                <span class="font-bold text-slate-800 block text-xs">Hero Fullpage</span>
                                                <span class="text-[10px] text-slate-500">100% de alto</span>
                                            </div>
                                            <div class="relative shrink-0 ml-2">
                                                <input type="checkbox" name="settings[full_width]" id="inpEditFullWidth" value="1" class="peer sr-only" onchange="window.updatePreviewEdit && window.updatePreviewEdit()">
                                                <div class="block bg-slate-200 w-10 h-6 rounded-full peer-checked:bg-indigo-600 transition"></div>
                                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition peer-checked:translate-x-4"></div>
                                            </div>
                                        </label>
                                    </div>
                                    <div>
                                        <input type="hidden" name="settings[full_width_horizontal]" value="0">
                                        <label class="flex items-center justify-between cursor-pointer p-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm h-full">
                                            <div>
                                                <span class="font-bold text-slate-800 block text-xs">Ancho Completo</span>
                                                <span class="text-[10px] text-slate-500">100% horizontal</span>
                                            </div>
                                            <div class="relative shrink-0 ml-2">
                                                <input type="checkbox" name="settings[full_width_horizontal]" id="inpEditFullWidthHorizontal" value="1" class="peer sr-only" onchange="window.updatePreviewEdit && window.updatePreviewEdit()">
                                                <div class="block bg-slate-200 w-10 h-6 rounded-full peer-checked:bg-indigo-600 transition"></div>
                                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition peer-checked:translate-x-4"></div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="p-3 bg-white border border-slate-200 rounded-xl shadow-sm">
                                        <label class="block font-bold text-slate-800 text-xs mb-1">Alto Personalizado</label>
                                        <div class="flex gap-2">
                                            <input type="number" name="settings[hero_height_value]" id="inpEditHeroHeightValue" placeholder="Ej: 400" class="w-2/3 px-2 py-1.5 border border-slate-200 rounded-lg text-xs" oninput="window.updatePreviewEdit && window.updatePreviewEdit()">
                                            <select name="settings[hero_height_unit]" id="inpEditHeroHeightUnit" class="w-1/3 px-2 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold" onchange="window.updatePreviewEdit && window.updatePreviewEdit()">
                                                <option value="px">px</option>
                                                <option value="vh">vh</option>
                                                <option value="%">%</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <input type="hidden" name="settings[parallax_scrolling]" value="0">
                                        <label class="flex items-center justify-between cursor-pointer p-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm h-full">
                                            <div>
                                                <span class="font-bold text-slate-800 block text-xs">Efecto Parallax</span>
                                                <span class="text-[10px] text-slate-500">Scroll fijo dinámico</span>
                                            </div>
                                            <div class="relative shrink-0 ml-2">
                                                <input type="checkbox" name="settings[parallax_scrolling]" id="inpEditParallax" value="1" class="peer sr-only" onchange="window.updatePreviewEdit && window.updatePreviewEdit()">
                                                <div class="block bg-slate-200 w-10 h-6 rounded-full peer-checked:bg-indigo-600 transition"></div>
                                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition peer-checked:translate-x-4"></div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
<!-- 3. IMAGEN DE FONDO -->
                                <div class="space-y-2 p-3 bg-white border border-slate-200 rounded-xl">
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block font-bold text-slate-800 text-xs">Tipo de Fondo</label>
                                        <select name="settings[background_type]" id="editHeroBgType" class="px-2 py-1 text-xs font-semibold border border-slate-200 rounded-lg" onchange="window.toggleHeroBgType(this.value, 'edit')">
                                            <option value="image">Imagen</option>
                                            <option value="video">Video</option>
                                        </select>
                                    </div>
                                    <div id="editHeroBgImageWrap" class="grid grid-cols-1 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Subir Archivo Local</label>
                                            <input type="file" name="background_image_file" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" onchange="window.previewHeroBgImage(this, 'editHeroBgPreview')">
                                        </div>
                                        <div>
                                            <div class="flex items-center justify-between mb-1">
                                                <label class="block text-[11px] font-semibold text-slate-600">O Ingresar URL Externa</label>
                                                <button type="button" class="text-[10px] text-indigo-600 font-bold hover:underline" onclick="window.openCmsMediaModal('editHeroBgUrl')">Seleccionar del Gestor</button>
                                            </div>
                                            <input type="text" name="settings[background_image]" id="editHeroBgUrl" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" placeholder="https://ejemplo.com/hero-bg.webp" oninput="window.previewHeroBgUrl(this.value, 'editHeroBgPreview')">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Texto Alternativo (Alt SEO)</label>
                                            <input type="text" name="settings[background_image_alt]" id="editHeroBgAlt" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs" placeholder="Breve descripción de la imagen">
                                        </div>
                                        <div>
                                            <div class="flex items-center justify-between mb-1.5">
                                                <label class="block text-[11px] font-semibold text-slate-700 flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h2m14 0h2M12 3v2m0 14v2"/></svg>
                                                    Foco de Atención de la Imagen (Punto Focal)
                                                </label>
                                                <span id="editHeroFocalPointLabel" class="text-[10px] font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                                                    Centro Centro (50% 50%)
                                                </span>
                                            </div>
                                            <input type="hidden" name="settings[image_focal_point]" id="inpEditHeroFocalPoint" value="center center">
                                            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-4">
                                                <!-- MATRIZ 3x3 DE 9 CELDAS -->
                                                <div id="editHeroFocalMatrix" class="w-24 h-24 p-1 bg-slate-200/90 border border-slate-300 rounded-xl grid grid-cols-3 gap-1 shadow-inner select-none shrink-0" role="radiogroup" aria-label="Punto focal de la imagen">
                                                    <button type="button" data-focal="left top" onclick="window.setHeroFocalPoint('edit', 'left top')" title="Izquierda Superior (0% 0%)" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer"><span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span></button>
                                                    <button type="button" data-focal="center top" onclick="window.setHeroFocalPoint('edit', 'center top')" title="Centro Superior (50% 0%)" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer"><span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span></button>
                                                    <button type="button" data-focal="right top" onclick="window.setHeroFocalPoint('edit', 'right top')" title="Derecha Superior (100% 0%)" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer"><span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span></button>

                                                    <button type="button" data-focal="left center" onclick="window.setHeroFocalPoint('edit', 'left center')" title="Izquierda Centro (0% 50%)" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer"><span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span></button>
                                                    <button type="button" data-focal="center center" onclick="window.setHeroFocalPoint('edit', 'center center')" title="Centro Centro (50% 50%) - Predeterminado" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-indigo-600 border border-indigo-700 ring-2 ring-indigo-300 text-white transition cursor-pointer shadow-xs"><span class="w-2.5 h-2.5 rounded-full bg-white ring-2 ring-indigo-400 pointer-events-none"></span></button>
                                                    <button type="button" data-focal="right center" onclick="window.setHeroFocalPoint('edit', 'right center')" title="Derecha Centro (100% 50%)" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer"><span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span></button>

                                                    <button type="button" data-focal="left bottom" onclick="window.setHeroFocalPoint('edit', 'left bottom')" title="Izquierda Inferior (0% 100%)" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer"><span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span></button>
                                                    <button type="button" data-focal="center bottom" onclick="window.setHeroFocalPoint('edit', 'center bottom')" title="Centro Inferior (50% 100%)" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer"><span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span></button>
                                                    <button type="button" data-focal="right bottom" onclick="window.setHeroFocalPoint('edit', 'right bottom')" title="Derecha Inferior (100% 100%)" class="focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer"><span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span></button>
                                                </div>
                                                <div class="text-[11px] text-slate-500 leading-snug">
                                                    <span class="font-bold text-slate-700 block text-xs">Matriz de 9 Celdas</span>
                                                    Haz clic en un cuadrante para anclar el punto visual clave al redimensionarse la imagen en pantallas móviles y monitores ultrawide.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="editHeroBgVideoWrap" class="hidden grid grid-cols-1 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Subir Video (MP4)</label>
                                            <input type="file" name="background_video_file" accept="video/mp4,video/webm" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">O Ingresar URL de Video</label>
                                            <input type="text" name="settings[background_video]" id="editHeroBgVideoUrl" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" placeholder="https://ejemplo.com/hero-bg.mp4">
                                        </div>
                                        <div class="flex gap-4 mt-2">
                                            <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600"><input type="checkbox" name="settings[video_autoplay]" id="editHeroVideoAutoplay" value="1" checked class="rounded border-slate-300 text-indigo-600"> Autoplay</label>
                                            <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600"><input type="checkbox" name="settings[video_loop]" id="editHeroVideoLoop" value="1" checked class="rounded border-slate-300 text-indigo-600"> Loop</label>
                                            <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600"><input type="checkbox" name="settings[video_muted]" id="editHeroVideoMuted" value="1" checked class="rounded border-slate-300 text-indigo-600"> Muted</label>
                                        </div>
                                    </div>
                                    <div id="editHeroBgPreview" class="hidden mt-2 p-2 border border-dashed border-slate-300 rounded-lg bg-slate-50 flex items-center gap-3">
                                        <img src="" alt="Vista previa fondo" class="w-16 h-10 object-cover rounded shadow-xs bg-slate-200">
                                        <span class="text-[11px] text-slate-600 truncate flex-1 font-mono"></span>
                                        <button type="button" onclick="window.clearHeroBgPreview('editSectionModal', 'editHeroBgPreview', 'editHeroBgUrl')" class="text-rose-600 font-bold text-xs cursor-pointer">Eliminar</button>
                                    </div>
                                </div>

                                <!-- 4. CAPA DE DEGRADADOS OVERLAY -->
                                <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                    <input type="hidden" name="settings[enable_gradient]" value="0">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="settings[enable_gradient]" id="editEnableGradient" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4" onchange="document.getElementById('editGradientControls').style.display = this.checked ? 'block' : 'none'">
                                        <span class="font-bold text-slate-800 text-xs">Habilitar Capa de Degradado Overlay</span>
                                    </label>

                                    <div id="editGradientControls" style="display: none;" class="space-y-3 pt-2 border-t border-slate-100">
    <div class="grid grid-cols-2 gap-3 mb-3">
        <div class="space-y-2 p-2 bg-slate-50 border border-slate-100 rounded">
            <div>
                <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color Inicio & Opacidad</label>
                <div class="flex items-center gap-1.5 mb-2">
                    <input type="color" id="editGradColorStartPicker" value="#0f172a" class="w-7 h-7 p-0 border border-slate-200 rounded cursor-pointer" oninput="document.getElementById('editGradColorStart').value = this.value; window.updateHeroGradientPreview('edit')">
                    <input type="text" name="settings[gradient_color_start]" id="editGradColorStart" value="#0f172a" class="w-full px-2 py-1 text-[11px] font-mono border border-slate-200 rounded" oninput="document.getElementById('editGradColorStartPicker').value = this.value; window.updateHeroGradientPreview('edit')">
                </div>
                <div class="flex items-center gap-2">
                    <input type="range" name="settings[gradient_opacity_start]" id="editGradOpacityStart" min="0" max="100" value="80" class="w-full cursor-pointer accent-indigo-600" oninput="document.getElementById('editGradOpacityStartVal').textContent = this.value + '%'; window.updateHeroGradientPreview('edit')">
                    <span id="editGradOpacityStartVal" class="text-[10px] font-semibold text-slate-600 w-8 text-right">80%</span>
                </div>
            </div>
        </div>
        <div class="space-y-2 p-2 bg-slate-50 border border-slate-100 rounded">
            <div>
                <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color Fin & Opacidad</label>
                <div class="flex items-center gap-1.5 mb-2">
                    <input type="color" id="editGradColorEndPicker" value="#1e1b4b" class="w-7 h-7 p-0 border border-slate-200 rounded cursor-pointer" oninput="document.getElementById('editGradColorEnd').value = this.value; window.updateHeroGradientPreview('edit')">
                    <input type="text" name="settings[gradient_color_end]" id="editGradColorEnd" value="#1e1b4b" class="w-full px-2 py-1 text-[11px] font-mono border border-slate-200 rounded" oninput="document.getElementById('editGradColorEndPicker').value = this.value; window.updateHeroGradientPreview('edit')">
                </div>
                <div class="flex items-center gap-2">
                    <input type="range" name="settings[gradient_opacity_end]" id="editGradOpacityEnd" min="0" max="100" value="80" class="w-full cursor-pointer accent-indigo-600" oninput="document.getElementById('editGradOpacityEndVal').textContent = this.value + '%'; window.updateHeroGradientPreview('edit')">
                    <span id="editGradOpacityEndVal" class="text-[10px] font-semibold text-slate-600 w-8 text-right">80%</span>
                </div>
            </div>
        </div>
        <div class="col-span-2">
            <label class="block text-[10px] font-semibold text-slate-600 mb-1">Dirección del Degradado</label>
            <select name="settings[gradient_direction]" id="editGradDir" class="w-full px-2 py-1.5 text-[11px] border border-slate-200 rounded" onchange="window.updateHeroGradientPreview('edit')">
                <option value="to bottom">Vertical</option>
                <option value="to right">Horizontal</option>
                <option value="135deg">Diagonal</option>
                <option value="to top">Invertido</option>
            </select>
        </div>
    </div>
    <div id="editGradPreviewBar" class="h-6 rounded-lg border border-slate-200 shadow-inner" style="background: linear-gradient(to bottom, rgba(15,23,42,0.8), rgba(30,27,75,0.8));"></div>
</div>
                                </div>


                                <!-- 6. ACCIONES Y BOTONES DEL HERO -->
                                <div id="editHeroButtonsWrap" class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                        <div>
                                            <span class="block font-bold text-slate-800 text-xs">Botones de Acción (Call to Action)</span>
                                            <span class="text-[10px] text-slate-500">Conmuta entre botones independientes o clic en todo el banner</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="hidden" name="settings[hero_has_buttons]" value="0">
                                            <label class="flex items-center gap-2 cursor-pointer bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200 shadow-2xs hover:bg-slate-100 transition">
                                                <span class="text-[11px] font-bold text-slate-700">Incluir Botones</span>
                                                <div class="relative shrink-0">
                                                    <input type="checkbox" name="settings[hero_has_buttons]" id="inpEditHeroHasButtons" value="1" checked class="peer sr-only" onchange="window.toggleHeroButtonsMode && window.toggleHeroButtonsMode('edit'); window.updatePreviewEdit && window.updatePreviewEdit()">
                                                    <div class="block bg-slate-200 w-9 h-5 rounded-full peer-checked:bg-indigo-600 transition"></div>
                                                    <div class="dot absolute left-0.5 top-0.5 bg-white w-4 h-4 rounded-full transition peer-checked:translate-x-4"></div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- CONTENEDOR DE BOTONES (VISIBLE SI hero_has_buttons ESTÁ ACTIVO) -->
                                    <div id="editHeroButtonsControls" class="space-y-3">

                                    <!-- Botón Primario -->
                                    <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg space-y-2">
                                        <span class="block font-bold text-indigo-700 text-[11px]">Botón Primario</span>
                                        <div class="grid grid-cols-1 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Texto del Botón</label>
                                                <input type="text" name="settings[cta_primary_text]" id="inpEditCtaPrimary" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs" placeholder="Ej: EXPLORAR CATÁLOGO" value="EXPLORAR CATÁLOGO">
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
                                            <div class="grid grid-cols-2 gap-2 mt-1">
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Fondo</label>
                                                    <input type="color" name="settings[cta_primary_bg]" id="inpEditCtaPrimaryBg" value="#0f172a" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Texto</label>
                                                    <input type="color" name="settings[cta_primary_color]" id="inpEditCtaPrimaryColor" value="#ffffff" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Borde</label>
                                                    <input type="color" name="settings[cta_primary_border_color]" id="inpEditCtaPrimaryBorderColor" value="#0f172a" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Grosor Borde</label>
                                                    <select name="settings[cta_primary_border_width]" id="inpEditCtaPrimaryBorderWidth" class="w-full px-2 py-1 border border-slate-200 rounded text-[11px] h-8 bg-white">
                                                        <option value="border-0">Sin Borde</option>
                                                        <option value="border">Fino (1px)</option>
                                                        <option value="border-2">Medio (2px)</option>
                                                        <option value="border-4">Grueso (4px)</option>
                                                    </select>
                                                </div>
                                                <div class="col-span-2">
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Sombra</label>
                                                    <select name="settings[cta_primary_shadow]" id="inpEditCtaPrimaryShadow" class="w-full px-2 py-1.5 border border-slate-200 rounded text-[11px] bg-white">
                                                        <option value="">Sin Sombra</option>
                                                        <option value="shadow-sm">Pequeña</option>
                                                        <option value="shadow-md">Media</option>
                                                        <option value="shadow-lg">Grande</option>
                                                        <option value="shadow-xl">Extra Grande</option>
                                                    </select>
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
                                                <input type="text" name="settings[cta_secondary_text]" id="inpEditCtaSecondary" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs" placeholder="Ej: GUÍA DE TALLAS" value="">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">URL / Ruta de Destino</label>
                                                <div class="flex items-center gap-1.5">
                                                    <input type="text" name="settings[cta_secondary_url]" id="editCtaSecondaryUrl" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" placeholder="Ej: /pagina/guia-tallas">
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
                                            <div class="grid grid-cols-2 gap-2 mt-1">
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Fondo</label>
                                                    <input type="color" name="settings[cta_secondary_bg]" id="inpEditCtaSecondaryBg" value="#ffffff" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Texto</label>
                                                    <input type="color" name="settings[cta_secondary_color]" id="inpEditCtaSecondaryColor" value="#0f172a" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Color Borde</label>
                                                    <input type="color" name="settings[cta_secondary_border_color]" id="inpEditCtaSecondaryBorderColor" value="#0f172a" class="w-full h-8 p-0 border border-slate-200 rounded cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Grosor Borde</label>
                                                    <select name="settings[cta_secondary_border_width]" id="inpEditCtaSecondaryBorderWidth" class="w-full px-2 py-1 border border-slate-200 rounded text-[11px] h-8 bg-white">
                                                        <option value="border-0">Sin Borde</option>
                                                        <option value="border">Fino (1px)</option>
                                                        <option value="border-2">Medio (2px)</option>
                                                        <option value="border-4">Grueso (4px)</option>
                                                    </select>
                                                </div>
                                                <div class="col-span-2">
                                                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Sombra</label>
                                                    <select name="settings[cta_secondary_shadow]" id="inpEditCtaSecondaryShadow" class="w-full px-2 py-1.5 border border-slate-200 rounded text-[11px] bg-white">
                                                        <option value="">Sin Sombra</option>
                                                        <option value="shadow-sm">Pequeña</option>
                                                        <option value="shadow-md">Media</option>
                                                        <option value="shadow-lg">Grande</option>
                                                        <option value="shadow-xl">Extra Grande</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </div>

                                    <!-- CONTENEDOR DE CLIC EN TODO EL HERO (VISIBLE CUANDO hero_has_buttons ESTÁ DESACTIVADO) -->
                                    <div id="editHeroFullClickWrap" class="p-3 bg-amber-50/60 border border-amber-200/80 rounded-xl space-y-2 hidden">
                                        <div class="flex items-center gap-2 text-amber-900 font-bold text-xs">
                                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                                            <span>Enlace de Clic en Todo el Hero</span>
                                        </div>
                                        <p class="text-[11px] text-amber-800 leading-relaxed">
                                            Al no incluir botones, cualquier clic sobre la imagen o el contenido del Hero redirigirá automáticamente a la siguiente ruta o URL seleccionada.
                                        </p>
                                        <div>
                                            <label class="block text-[10px] font-semibold text-slate-600 mb-1">URL / Ruta de Destino</label>
                                            <div class="flex items-center gap-1.5">
                                                <input type="text" name="settings[hero_click_url]" id="editHeroClickUrl" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-mono bg-white" placeholder="Ej: #section-2 o /catalogo" oninput="window.updatePreviewEdit && window.updatePreviewEdit()">
                                                <select onchange="if(this.value){ document.getElementById('editHeroClickUrl').value = this.value; this.value=''; window.updatePreviewEdit && window.updatePreviewEdit(); }" class="px-2 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-600 font-semibold bg-white cursor-pointer shrink-0" title="Seleccionar una ruta de la tienda">
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

                        <!-- 2. MODO CAROUSEL -->
                        <div id="editCarouselWrap" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-4 hidden">
                            <input type="hidden" name="settings[carousel_global_content]" value="0">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                <span class="font-bold text-slate-800 text-xs flex items-center gap-1.5 uppercase tracking-wider">Configuración de Carrusel</span>
                                <span class="text-[10px] text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded-full font-semibold">Contenido integrado por Slide</span>
                            </div>
                            <div class="space-y-4">
                                <!-- PANTALLA, ALTO PERSONALIZADO Y EFECTO REVELADO DEL CARRUSEL -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                     <div>
                                         <label class="flex items-center justify-between cursor-pointer p-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm h-full">
                                             <div>
                                                 <span class="font-bold text-slate-800 block text-xs">Full Page</span>
                                                 <span class="text-[10px] text-slate-500">100% de alto</span>
                                             </div>
                                             <div class="relative shrink-0 ml-2">
                                                 <input type="checkbox" id="inpEditCarouselFullWidth" value="1" class="peer sr-only" onchange="window.syncCarouselLayoutSettings && window.syncCarouselLayoutSettings('edit')">
                                                 <div class="block bg-slate-200 w-10 h-6 rounded-full peer-checked:bg-indigo-600 transition"></div>
                                                 <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition peer-checked:translate-x-4"></div>
                                             </div>
                                         </label>
                                     </div>
                                     <div>
                                         <label class="flex items-center justify-between cursor-pointer p-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm h-full">
                                             <div>
                                                 <span class="font-bold text-slate-800 block text-xs">Ancho Completo</span>
                                                 <span class="text-[10px] text-slate-500">100% horizontal</span>
                                             </div>
                                             <div class="relative shrink-0 ml-2">
                                                 <input type="checkbox" id="inpEditCarouselFullWidthHorizontal" value="1" class="peer sr-only" onchange="window.syncCarouselLayoutSettings && window.syncCarouselLayoutSettings('edit')">
                                                 <div class="block bg-slate-200 w-10 h-6 rounded-full peer-checked:bg-indigo-600 transition"></div>
                                                 <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition peer-checked:translate-x-4"></div>
                                             </div>
                                         </label>
                                     </div>
                                     <div class="p-3 bg-white border border-slate-200 rounded-xl shadow-sm">
                                        <label class="block font-bold text-slate-800 text-xs mb-1">Alto Personalizado</label>
                                        <div class="flex gap-1.5">
                                            <input type="number" id="inpEditCarouselHeightValue" placeholder="Ej: 440" class="w-2/3 px-2 py-1.5 border border-slate-200 rounded-lg text-xs" oninput="window.syncCarouselLayoutSettings && window.syncCarouselLayoutSettings('edit')">
                                            <select id="inpEditCarouselHeightUnit" class="w-1/3 px-1.5 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold bg-white" onchange="window.syncCarouselLayoutSettings && window.syncCarouselLayoutSettings('edit')">
                                                <option value="px">px</option>
                                                <option value="vh">vh</option>
                                                <option value="%">%</option>
                                            </select>
                                        </div>
                                     </div>
                                     <div>
                                         <label class="flex items-center justify-between cursor-pointer p-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm h-full">
                                             <div>
                                                 <span class="font-bold text-slate-800 block text-xs">Efecto Revelado (Cortina)</span>
                                                 <span class="text-[10px] text-slate-500">Revela imagen de fondo</span>
                                             </div>
                                             <div class="relative shrink-0 ml-2">
                                                 <input type="checkbox" id="inpEditCarouselParallax" value="1" class="peer sr-only" onchange="window.syncCarouselLayoutSettings && window.syncCarouselLayoutSettings('edit')">
                                                 <div class="block bg-slate-200 w-10 h-6 rounded-full peer-checked:bg-indigo-600 transition"></div>
                                                 <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition peer-checked:translate-x-4"></div>
                                             </div>
                                         </label>
                                     </div>
                                 </div>

                                <div class="flex items-center justify-between bg-white p-2.5 rounded-xl border border-slate-200">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" id="editCarouselAutoPlay" name="settings[autoplay]" checked value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4" onchange="window.updatePreviewEdit && window.updatePreviewEdit()">
                                        <span class="text-xs text-slate-700 font-bold">Autoplay Animado</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs text-slate-600 font-semibold">Intervalo (seg):</label>
                                        <input type="number" name="settings[autoplay_time]" id="editCarouselAutoplayTime" value="5" min="1" max="60" class="w-16 px-2 py-1 border border-slate-200 rounded text-xs font-bold text-center" oninput="window.updatePreviewEdit && window.updatePreviewEdit()">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-1">
                                    <label class="block font-bold text-slate-700 text-xs uppercase tracking-wider">Slides del Carrusel</label>
                                    <button type="button" class="px-3 py-1.5 bg-indigo-600 text-white text-xs rounded-lg hover:bg-indigo-700 font-bold shadow-xs cursor-pointer transition" onclick="window.addCarouselSlide('edit')">+ Añadir Slide</button>
                                </div>
                                
                                <div id="editCarouselSlidesList" class="space-y-3">
                                    <!-- Dynamic Slides go here -->
                                </div>
                            </div>
                        </div>

                        <!-- 3. MODO POP UP FULLSTACK (Hub 2 / Hub 7 / Hub 3 / Meta CAPI) -->
                        <div id="editPopUpWrap" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-4 hidden">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                <span class="font-bold text-slate-800 text-xs flex items-center gap-1.5 uppercase tracking-wider">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    Configurador de Pop-up y Captura de Leads (CAPI Score 9.8)
                                </span>
                            </div>

                            <!-- Selector de Accion Principal -->
                            <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-2">
                                <label class="block font-bold text-slate-800 text-xs">Acción Principal del Pop-up</label>
                                <select name="settings[popup_action]" id="editPopupAction" class="w-full px-3 py-2 text-xs font-semibold border border-slate-200 rounded-lg bg-slate-50 focus:bg-white" onchange="window.handlePopupActionChange('edit')">
                                    <option value="ally_discount">2.1 Suscripción con Código de Descuento de Aliado (Convenio Hub 7)</option>
                                    <option value="newsletter">2.2 Suscripción para Actualizaciones de Newsletter (Sin Cupón)</option>
                                    <option value="store_discount">2.3 Suscripción para Obtener Cupón Directo de Tienda</option>
                                    <option value="presale">2.4 Acceder a una Preventa Exclusiva (Redirección Inmediata)</option>
                                </select>
                                <span class="block text-[10px] text-slate-500">
                                    Todos los registros crean una cuenta de comprador en Hub 3 con envío instantáneo a WhatsApp y sincronizan eventos con Meta CAPI.
                                </span>
                            </div>

                            <!-- Sub-bloque 2.1: Campanas de Aliados Aprobadas en Hub 7 -->
                            <div id="editPopupAllyBlock" class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-1.5">
                                    <label class="block font-bold text-slate-800 text-xs">Evento o Beneficio del Aliado (Hub 7)</label>
                                    <a href="/admin/convenios" target="_blank" class="text-[10px] font-bold text-indigo-600 hover:underline">Ver Alianzas en Hub 7 &rarr;</a>
                                </div>
                                <div>
                                    <select name="settings[popup_ally_campaign_id]" id="editPopupAllyCampaignId" class="w-full px-3 py-2 text-xs font-semibold border border-slate-200 rounded-lg" onchange="window.handlePopupAllyCampaignChange('edit')">
                                        @if(isset($approvedAllyCampaigns) && count($approvedAllyCampaigns) > 0)
                                            <option value="">-- Seleccionar Campaña Aprobada --</option>
                                            @foreach($approvedAllyCampaigns as $camp)
                                                <option value="{{ $camp->id }}" data-banner="{{ $camp->banner_image }}" data-title="{{ $camp->title }}" data-benefit="{{ $camp->benefit }}" data-ally="{{ $camp->ally->name ?? 'Aliado' }}">
                                                    {{ $camp->ally->name ?? 'Aliado' }} - {{ $camp->title }} ({{ $camp->benefit }})
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="">(No hay eventos aprobados en Hub 7 - Crea uno en Convenios)</option>
                                        @endif
                                    </select>
                                </div>

                                <!-- Conmutador entre Poster Oficial del Aliado o Cargar Imagen -->
                                <div class="pt-1">
                                    <label class="block font-bold text-slate-700 text-[11px] mb-1.5">Origen de la Imagen del Pop-up</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50 cursor-pointer hover:bg-slate-100 text-xs font-semibold text-slate-700">
                                            <input type="radio" name="settings[popup_ally_image_source]" value="ally_poster" checked class="text-indigo-600 focus:ring-indigo-500" onchange="window.handlePopupImageSourceToggle('edit')">
                                            <span>Póster Oficial del Aliado</span>
                                        </label>
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50 cursor-pointer hover:bg-slate-100 text-xs font-semibold text-slate-700">
                                            <input type="radio" name="settings[popup_ally_image_source]" value="custom" class="text-indigo-600 focus:ring-indigo-500" onchange="window.handlePopupImageSourceToggle('edit')">
                                            <span>Cargar Imagen en Creador</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Sub-bloque 2.3: Configuracion de Cupon de Tienda -->
                            <div id="editPopupStoreDiscountBlock" class="p-3 bg-white border border-slate-200 rounded-xl space-y-3 hidden">
                                <label class="block font-bold text-slate-800 text-xs">Parámetros del Descuento de Tienda (Cupón Único)</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Monto / Valor</label>
                                        <input type="number" name="settings[popup_store_discount_value]" id="editPopupStoreDiscountValue" value="15" min="1" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-bold" oninput="window.renderPopUpLivePreview('edit')">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Tipo de Descuento</label>
                                        <select name="settings[popup_store_discount_type]" id="editPopupStoreDiscountType" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold" onchange="window.renderPopUpLivePreview('edit')">
                                            <option value="percent">Porcentaje (%)</option>
                                            <option value="fixed">Monto Fijo ($ MXN)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Compra Mínima ($ MXN)</label>
                                        <input type="number" name="settings[popup_min_order]" id="editPopupMinOrder" value="499" min="0" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Días de Vigencia</label>
                                        <input type="number" name="settings[popup_days_valid]" id="editPopupDaysValid" value="30" min="1" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs">
                                    </div>
                                </div>
                            </div>

                            <!-- Sub-bloque 2.4: URL de Preventa -->
                            <div id="editPopupPresaleBlock" class="p-3 bg-white border border-slate-200 rounded-xl space-y-2 hidden">
                                <label class="block font-bold text-slate-800 text-xs">URL del Producto o Colección en Preventa</label>
                                <input type="text" name="settings[popup_presale_url]" id="editPopupPresaleUrl" placeholder="/producto/binder-pro-v2" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-mono">
                                <span class="block text-[10px] text-slate-500">Al completar el formulario, el usuario será redirigido inmediatamente a este destino con sus datos guardados en Hub 3.</span>
                            </div>

                            <!-- Imagen y Matriz de Punto Focal 3x3 -->
                            <div id="editPopupCustomImageBlock" class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                <label class="block font-bold text-slate-800 text-xs">Imagen de Fondo del Pop-up</label>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Subir Archivo Local</label>
                                    <input type="file" name="popup_image_file" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-semibold text-slate-600">O Ingresar URL / Gestor Multimedia</label>
                                        <button type="button" class="text-[10px] text-indigo-600 font-bold hover:underline" onclick="window.openCmsMediaModal('editPopupImageUrl')">Seleccionar del Gestor</button>
                                    </div>
                                    <input type="text" name="settings[popup_image_url]" id="editPopupImageUrl" placeholder="https://ejemplo.com/poster-evento.webp" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-mono" oninput="window.renderPopUpLivePreview('edit')">
                                </div>

                                <!-- Matriz 3x3 de Punto Focal -->
                                <div class="pt-1">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Matriz de Punto Focal de la Imagen</label>
                                    <input type="hidden" name="settings[popup_image_focal_point]" id="inpEditPopupFocalPoint" value="center center">
                                    <div id="editPopupFocalMatrix" class="grid grid-cols-3 gap-1 w-36 mx-auto p-1 bg-slate-100 border border-slate-200 rounded-lg">
                                        <button type="button" data-point="top left" class="h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition" onclick="window.setPopupFocalPoint('edit', 'top left', this)">TL</button>
                                        <button type="button" data-point="top center" class="h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition" onclick="window.setPopupFocalPoint('edit', 'top center', this)">TC</button>
                                        <button type="button" data-point="top right" class="h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition" onclick="window.setPopupFocalPoint('edit', 'top right', this)">TR</button>
                                        <button type="button" data-point="center left" class="h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition" onclick="window.setPopupFocalPoint('edit', 'center left', this)">CL</button>
                                        <button type="button" data-point="center center" class="h-7 text-[10px] font-bold rounded border border-indigo-600 bg-indigo-600 text-white transition" onclick="window.setPopupFocalPoint('edit', 'center center', this)">CC</button>
                                        <button type="button" data-point="center right" class="h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition" onclick="window.setPopupFocalPoint('edit', 'center right', this)">CR</button>
                                        <button type="button" data-point="bottom left" class="h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition" onclick="window.setPopupFocalPoint('edit', 'bottom left', this)">BL</button>
                                        <button type="button" data-point="bottom center" class="h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition" onclick="window.setPopupFocalPoint('edit', 'bottom center', this)">BC</button>
                                        <button type="button" data-point="bottom right" class="h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition" onclick="window.setPopupFocalPoint('edit', 'bottom right', this)">BR</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Colores de Título y Subtítulo del Pop-up -->
                            <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                <label class="block font-bold text-slate-800 text-xs">Colores de Texto del Pop-up</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="block text-[10px] font-semibold text-slate-600">Color del Título</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="color" id="editPopupTitleColorPicker" value="#ffffff" class="w-7 h-7 p-0 border border-slate-200 rounded cursor-pointer" oninput="document.getElementById('editPopupTitleColor').value = this.value; window.renderPopUpLivePreview('edit')">
                                            <input type="text" name="settings[popup_title_color]" id="editPopupTitleColor" value="#ffffff" class="w-full px-2 py-1 text-[11px] font-mono border border-slate-200 rounded" oninput="document.getElementById('editPopupTitleColorPicker').value = this.value; window.renderPopUpLivePreview('edit')">
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-[10px] font-semibold text-slate-600">Color de Bajada / Subtítulo</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="color" id="editPopupSubtitleColorPicker" value="#e2e8f0" class="w-7 h-7 p-0 border border-slate-200 rounded cursor-pointer" oninput="document.getElementById('editPopupSubtitleColor').value = this.value; window.renderPopUpLivePreview('edit')">
                                            <input type="text" name="settings[popup_subtitle_color]" id="editPopupSubtitleColor" value="#e2e8f0" class="w-full px-2 py-1 text-[11px] font-mono border border-slate-200 rounded" oninput="document.getElementById('editPopupSubtitleColorPicker').value = this.value; window.renderPopUpLivePreview('edit')">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Capa de Degradado Overlay para Pop-up -->
                            <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="settings[popup_enable_gradient]" id="editPopupEnableGradient" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4" onchange="window.renderPopUpLivePreview('edit')">
                                    <span class="font-bold text-slate-800 text-xs">Habilitar Capa de Degradado Overlay</span>
                                </label>

                                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                                    <div class="space-y-2 p-2 bg-slate-50 border border-slate-100 rounded">
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color Inicio & Opacidad</label>
                                        <div class="flex items-center gap-1.5 mb-2">
                                            <input type="color" id="editPopupGradColorStartPicker" value="#0f172a" class="w-7 h-7 p-0 border border-slate-200 rounded cursor-pointer" oninput="document.getElementById('editPopupGradColorStart').value = this.value; window.renderPopUpLivePreview('edit')">
                                            <input type="text" name="settings[popup_gradient_color_start]" id="editPopupGradColorStart" value="#0f172a" class="w-full px-2 py-1 text-[11px] font-mono border border-slate-200 rounded" oninput="document.getElementById('editPopupGradColorStartPicker').value = this.value; window.renderPopUpLivePreview('edit')">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="range" name="settings[popup_gradient_opacity_start]" id="editPopupGradOpacityStart" min="0" max="100" value="85" class="w-full cursor-pointer accent-indigo-600" oninput="document.getElementById('editPopupGradOpacityStartVal').textContent = this.value + '%'; window.renderPopUpLivePreview('edit')">
                                            <span id="editPopupGradOpacityStartVal" class="text-[10px] font-semibold text-slate-600 w-8 text-right">85%</span>
                                        </div>
                                    </div>

                                    <div class="space-y-2 p-2 bg-slate-50 border border-slate-100 rounded">
                                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color Fin & Opacidad</label>
                                        <div class="flex items-center gap-1.5 mb-2">
                                            <input type="color" id="editPopupGradColorEndPicker" value="#000000" class="w-7 h-7 p-0 border border-slate-200 rounded cursor-pointer" oninput="document.getElementById('editPopupGradColorEnd').value = this.value; window.renderPopUpLivePreview('edit')">
                                            <input type="text" name="settings[popup_gradient_color_end]" id="editPopupGradColorEnd" value="#000000" class="w-full px-2 py-1 text-[11px] font-mono border border-slate-200 rounded" oninput="document.getElementById('editPopupGradColorEndPicker').value = this.value; window.renderPopUpLivePreview('edit')">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="range" name="settings[popup_gradient_opacity_end]" id="editPopupGradOpacityEnd" min="0" max="100" value="95" class="w-full cursor-pointer accent-indigo-600" oninput="document.getElementById('editPopupGradOpacityEndVal').textContent = this.value + '%'; window.renderPopUpLivePreview('edit')">
                                            <span id="editPopupGradOpacityEndVal" class="text-[10px] font-semibold text-slate-600 w-8 text-right">95%</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-600 mb-1">Dirección del Degradado</label>
                                    <select name="settings[popup_gradient_direction]" id="editPopupGradDir" class="w-full px-2 py-1.5 text-xs font-semibold border border-slate-200 rounded-lg" onchange="window.renderPopUpLivePreview('edit')">
                                        <option value="to bottom">De Arriba hacia Abajo (Vertical Standard)</option>
                                        <option value="to top">De Abajo hacia Arriba</option>
                                        <option value="to right">De Izquierda a Derecha</option>
                                        <option value="to left">De Derecha a Izquierda</option>
                                        <option value="to bottom right">Diagonal (Superior Izq a Inferior Der)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Boton CTA y Comportamiento -->
                            <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1 text-xs">Texto del Botón de Suscripción (CTA)</label>
                                    <input type="text" name="settings[popup_cta_text]" id="editPopupCtaText" placeholder="Ej: Obtener mi Descuento Único" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-bold" oninput="window.renderPopUpLivePreview('edit')">
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1 text-[11px]">Comportamiento de Cierre</label>
                                        <select name="settings[popup_close_behavior]" id="editPopupCloseBehavior" class="w-full px-2 py-1.5 text-xs font-semibold border border-slate-200 rounded-lg">
                                            <option value="outside">Permitir cerrar clic fuera (Overlay)</option>
                                            <option value="button">Solo botón cerrar (&times;)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1 text-[11px]">Retardo al Cargar (Seg)</label>
                                        <input type="number" name="settings[popup_delay_seconds]" id="editPopupDelaySeconds" value="3" min="0" max="60" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1 text-[11px]">Frecuencia</label>
                                        <select name="settings[popup_frequency]" id="editPopupFrequency" class="w-full px-2 py-1.5 text-xs font-semibold border border-slate-200 rounded-lg">
                                            <option value="once_per_session">Una vez por sesión</option>
                                            <option value="once_per_day">Una vez al día</option>
                                            <option value="always">Cada recarga (Testing)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. MODO BOTONES / BLOQUE PERSONALIZADO -->
                        <div id="editButtonsWrap" class="space-y-4 hidden">
                            <!-- EDITOR DE CÓDIGO CUSTOM -->
                        </div>
                        <div id="editCustomCodeWrap" class="p-3 bg-slate-900 text-slate-200 border border-slate-800 rounded-xl space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="block font-bold text-emerald-400 text-[11px] uppercase tracking-wider">Bloque Custom / HTML Libre</span>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1 text-xs">Ingresa tu código HTML / CSS</label>
                                <textarea name="custom_html" id="editCustomHtml" rows="5" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 text-slate-100 font-mono text-[11px] rounded-lg" placeholder="<!-- Tu código HTML aquí -->&#10;<div class='flex gap-2 justify-center'>&#10;  <button class='btn'>Botón 1</button>&#10;</div>" oninput="window.updatePreviewEdit && window.updatePreviewEdit()"></textarea>
                            </div>
                        </div>

                        <!-- 5. GESTIÓN DE ESPACIADOS ENTRE SECCIONES (TASK-SPACING-MODULAR) -->
                        <div id="editSpacingWrap" class="p-3 bg-white border border-slate-200 rounded-xl space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <span class="block font-bold text-slate-800 text-[11px] uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                    <span>Espaciado Vertical de Sección (Márgenes)</span>
                                </span>
                                <span class="text-[10px] text-slate-400 font-medium">Por defecto: 0px (Sin espacios)</span>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Espaciado Superior -->
                                <div class="p-2.5 bg-slate-50 border border-slate-100 rounded-lg space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                            <span>Margen Superior</span>
                                        </label>
                                        <div class="flex items-center gap-1">
                                            <input type="number" id="inpEditSpacingTop" name="settings[spacing_top]" min="0" max="160" step="1" value="0" class="w-14 px-1.5 py-0.5 text-center text-xs font-mono font-bold bg-white border border-slate-200 rounded" oninput="window.setSectionSpacing('edit', 'top', this.value)">
                                            <span class="text-[10px] text-slate-500 font-semibold">px</span>
                                        </div>
                                    </div>
                                    <input type="range" id="rangeEditSpacingTop" min="0" max="120" step="2" value="0" class="w-full accent-indigo-600 cursor-pointer h-1.5 bg-slate-200 rounded-lg" oninput="window.setSectionSpacing('edit', 'top', this.value)">
                                    <!-- Presets rápidos -->
                                    <div class="flex items-center gap-1">
                                        <span class="text-[9px] text-slate-400 uppercase font-bold mr-0.5">Presets:</span>
                                        <button type="button" onclick="window.setSectionSpacing('edit', 'top', 0)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">0px</button>
                                        <button type="button" onclick="window.setSectionSpacing('edit', 'top', 12)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">12px</button>
                                        <button type="button" onclick="window.setSectionSpacing('edit', 'top', 24)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">24px</button>
                                        <button type="button" onclick="window.setSectionSpacing('edit', 'top', 36)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">36px</button>
                                        <button type="button" onclick="window.setSectionSpacing('edit', 'top', 48)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">48px</button>
                                    </div>
                                </div>

                                <!-- Espaciado Inferior -->
                                <div class="p-2.5 bg-slate-50 border border-slate-100 rounded-lg space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                            <span>Margen Inferior</span>
                                        </label>
                                        <div class="flex items-center gap-1">
                                            <input type="number" id="inpEditSpacingBottom" name="settings[spacing_bottom]" min="0" max="160" step="1" value="0" class="w-14 px-1.5 py-0.5 text-center text-xs font-mono font-bold bg-white border border-slate-200 rounded" oninput="window.setSectionSpacing('edit', 'bottom', this.value)">
                                            <span class="text-[10px] text-slate-500 font-semibold">px</span>
                                        </div>
                                    </div>
                                    <input type="range" id="rangeEditSpacingBottom" min="0" max="120" step="2" value="0" class="w-full accent-indigo-600 cursor-pointer h-1.5 bg-slate-200 rounded-lg" oninput="window.setSectionSpacing('edit', 'bottom', this.value)">
                                    <!-- Presets rápidos -->
                                    <div class="flex items-center gap-1">
                                        <span class="text-[9px] text-slate-400 uppercase font-bold mr-0.5">Presets:</span>
                                        <button type="button" onclick="window.setSectionSpacing('edit', 'bottom', 0)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">0px</button>
                                        <button type="button" onclick="window.setSectionSpacing('edit', 'bottom', 12)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">12px</button>
                                        <button type="button" onclick="window.setSectionSpacing('edit', 'bottom', 24)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">24px</button>
                                        <button type="button" onclick="window.setSectionSpacing('edit', 'bottom', 36)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">36px</button>
                                        <button type="button" onclick="window.setSectionSpacing('edit', 'bottom', 48)" class="px-1.5 py-0.5 bg-white border border-slate-200 hover:border-indigo-400 rounded text-[10px] font-semibold text-slate-600 hover:text-indigo-600 transition">48px</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer del Formulario Flotante en la parte inferior de la columna -->
                    <div class="flex items-center justify-between gap-3 pt-4 pb-2 border-t border-slate-100 mt-auto bg-white sticky bottom-0 z-30">
                        <button type="button" class="px-4 py-2 text-slate-600 font-semibold cursor-pointer" data-action="close-modal" data-modal="editSectionModal">Cancelar</button>
                        <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg cursor-pointer flex items-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Guardar</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- COLUMNA DERECHA: PREVIEW EN VIVO -->
            <div class="flex-1 bg-slate-100 p-0 sm:p-6 flex flex-col relative z-10">
                <!-- Toggle de Dispositivo -->
                <div class="absolute top-6 left-6 z-40 flex bg-white rounded-lg shadow-sm p-1 border border-slate-200" id="editPreviewDeviceGroup">
                    <button type="button" class="edit-preview-device-btn active px-3 py-1.5 rounded text-xs font-bold bg-indigo-50 text-indigo-700 cursor-pointer" data-device="desktop">Desktop</button>
                    <button type="button" class="edit-preview-device-btn px-3 py-1.5 rounded text-xs font-bold text-slate-500 hover:text-slate-700 cursor-pointer" data-device="mobile">Mobile</button>
                </div>
                
                <!-- Contenedor del Preview Visual -->
                <div class="flex-1 flex items-center justify-center p-4 overflow-hidden mt-16">
                    <div id="editLivePreviewContainer" class="w-full h-[50vh] md:h-[55vh] max-h-[500px] bg-white rounded-xl shadow-md border border-slate-200 overflow-y-auto overflow-x-hidden transition-all duration-300 relative" style="max-width: 100%;">
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
                        <div id="editLivePreviewContent" class="w-full min-h-[250px] flex flex-col bg-white">
                            
                            <!-- 1. Hero / Sección Preview -->
                            <div id="editPreviewHeroArea" class="w-full p-8 md:p-12 flex flex-col items-center justify-center text-center relative bg-slate-900 min-h-[250px] overflow-hidden transition-all duration-300">
                                <video id="editPreviewHeroVideo" class="absolute inset-0 w-full h-full object-cover z-0 hidden" autoplay loop muted playsinline></video>
                                <div id="editPreviewOverlay" class="absolute inset-0 z-0 hidden"></div>
                                <div class="relative z-10 max-w-2xl mx-auto space-y-4 w-full">
                                    <span id="editPreviewBadge" class="inline-block px-3 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-bold rounded-full uppercase tracking-widest hidden"></span>
                                    <h2 id="editPreviewTitle" class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Tu Título Aquí</h2>
                                    <p id="editPreviewSubtitle" class="text-slate-300 md:text-lg max-w-xl mx-auto hidden">Tu bajada o subtítulo aquí.</p>
                                    
                                    <div id="editPreviewActions" class="flex flex-wrap justify-center gap-3 pt-4 hidden">
                                        <button id="editPreviewBtnPrimary" class="px-6 py-2.5 bg-slate-900 text-white rounded-lg font-bold text-sm shadow-sm hidden transition-colors"></button>
                                        <button id="editPreviewBtnSecondary" class="px-6 py-2.5 bg-white text-slate-900 border border-slate-200 rounded-lg font-bold text-sm hidden transition-colors"></button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 2. Carrusel Preview -->
                            <div id="editPreviewCarouselArea" class="hidden w-full h-[320px] min-h-[320px] bg-slate-900 relative overflow-hidden"></div>

                            <!-- 3. Pop-up Preview (Full Image + Gradient Overlay + Floating Content) -->
                            <div id="editPreviewPopUpArea" class="hidden w-full min-h-[500px] bg-slate-900/60 backdrop-blur-xs relative flex items-center justify-center p-4">
                                <div id="editPopupCardPreview" class="relative w-full max-w-sm rounded-2xl overflow-hidden shadow-2xl border border-white/10 flex flex-col justify-end p-5 min-h-[460px] text-white bg-slate-900 transition-all">
                                    <!-- Background Layer with focal point -->
                                    <div id="editPopupBgLayer" class="absolute inset-0 bg-cover bg-no-repeat transition-all" style="background-position: center center; background-image: none;"></div>
                                    <!-- Gradient Overlay Layer -->
                                    <div id="editPopupOverlayLayer" class="absolute inset-0 transition-opacity" style="background: linear-gradient(to bottom, rgba(15, 23, 42, 0.4) 0%, rgba(0, 0, 0, 0.95) 100%);"></div>
                                    <!-- Simulated Close Button -->
                                    <button type="button" class="absolute top-3 right-3 z-20 w-7 h-7 rounded-full bg-black/40 text-white/80 flex items-center justify-center backdrop-blur-xs text-xs font-bold pointer-events-none">&times;</button>
                                    <!-- Floating Content on top of image -->
                                    <div class="relative z-10 flex flex-col gap-2.5">
                                        <div id="editPopupBadgePreview" class="self-start px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-indigo-500/80 backdrop-blur-xs text-white border border-white/20">
                                            BENEFICIO EXCLUSIVO
                                        </div>
                                        <h3 id="editPopupTitlePreview" class="text-xl font-black leading-tight text-white drop-shadow-md">
                                            Descuento Especial
                                        </h3>
                                        <p id="editPopupSubtitlePreview" class="text-xs text-slate-200 drop-shadow-sm leading-relaxed">
                                            Suscríbete con tu WhatsApp para recibir tu código único y guardarlo en tu cuenta.
                                        </p>
                                        <!-- Simulated Form -->
                                        <div class="space-y-2 pt-1">
                                            <input type="text" placeholder="Tu Nombre Completo" class="w-full px-2.5 py-1.5 text-xs rounded-lg bg-white/90 text-slate-800 placeholder-slate-400 border border-white/20 pointer-events-none" disabled>
                                            <div class="flex gap-1.5">
                                                <span class="px-2 py-1.5 text-xs font-bold rounded-lg bg-emerald-600 text-white flex items-center gap-1">+52</span>
                                                <input type="tel" placeholder="WhatsApp (10 dígitos)" class="w-full px-2.5 py-1.5 text-xs rounded-lg bg-white/90 text-slate-800 placeholder-slate-400 border border-white/20 pointer-events-none" disabled>
                                            </div>
                                            <input type="email" placeholder="Correo Electrónico" class="w-full px-2.5 py-1.5 text-xs rounded-lg bg-white/90 text-slate-800 placeholder-slate-400 border border-white/20 pointer-events-none" disabled>
                                            <button type="button" id="editPopupCtaBtnPreview" class="w-full py-2 bg-indigo-600 text-white font-bold text-xs rounded-lg shadow-md pointer-events-none uppercase tracking-wide">
                                                Obtener Código Único
                                            </button>
                                            <div class="flex items-center justify-center gap-1 text-[9px] text-slate-300 text-center">
                                                <svg class="w-2.5 h-2.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                <span>Código único de un solo uso &bull; Envío instantáneo a WhatsApp</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Botones / Custom Preview -->
                            <div id="editPreviewButtonsArea" class="hidden w-full p-8 md:p-12 flex flex-col items-center text-center bg-white">
                                <h2 class="preview-common-title text-2xl font-bold text-slate-900 mb-3"></h2>
                                <p class="preview-common-subtitle text-slate-600 mb-8 max-w-lg mx-auto"></p>
                                <div id="editCustomHtmlPreviewContainer" class="w-full">
                                    <div class="p-8 bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl w-full text-slate-400 text-xs flex items-center justify-center flex-col gap-2">
                                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                        Área de Bloque Personalizado / Código HTML Inyectado
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const editSectionType = document.getElementById('editSectionType');
                    const editHeroWrap = document.getElementById('editHeroWrap');
                    const editCarouselWrap = document.getElementById('editCarouselWrap');
                    const editPopUpWrap = document.getElementById('editPopUpWrap');
                    const editButtonsWrap = document.getElementById('editButtonsWrap');

                    function switchSectionType() {
                        if (!editSectionType) return;
                        const val = editSectionType.value;
                        const isCustom = document.querySelector('#editSectionModal input[name="settings[hero_mode]"]:checked')?.value === 'custom';
                        const editCustomCodeWrap = document.getElementById('editCustomCodeWrap');
                        const editCommonFieldsWrap = document.getElementById('editCommonFieldsWrap');
                        const editSpacingWrap = document.getElementById('editSpacingWrap');

                        if (isCustom) {
                            if(editHeroWrap) editHeroWrap.style.display = 'none';
                            if(editCarouselWrap) editCarouselWrap.style.display = 'none';
                            if(editPopUpWrap) editPopUpWrap.style.display = 'none';
                            if(editButtonsWrap) editButtonsWrap.style.display = 'none';
                            if(editCommonFieldsWrap) editCommonFieldsWrap.style.display = 'none';
                            if(editCustomCodeWrap) editCustomCodeWrap.style.display = 'block';
                            if(editSpacingWrap) editSpacingWrap.style.display = 'block';
                        } else {
                            if(editCommonFieldsWrap) editCommonFieldsWrap.style.display = (val === 'carousel') ? 'none' : 'block';
                            if(editCustomCodeWrap) editCustomCodeWrap.style.display = (val === 'buttons') ? 'block' : 'none';
                            if(editHeroWrap) editHeroWrap.style.display = val === 'hero' ? 'block' : 'none';
                            if(editCarouselWrap) editCarouselWrap.style.display = val === 'carousel' ? 'block' : 'none';
                            if(editPopUpWrap) editPopUpWrap.style.display = val === 'pop_up' ? 'block' : 'none';
                            if(editButtonsWrap) editButtonsWrap.style.display = val === 'buttons' ? 'block' : 'none';
                            if(editSpacingWrap) editSpacingWrap.style.display = val === 'pop_up' ? 'none' : 'block';
                            if (val === 'carousel') {
                                const slidesList = document.getElementById('editCarouselSlidesList');
                                if (slidesList && slidesList.querySelectorAll('.slide-item').length === 0 && typeof window.addCarouselSlide === 'function') {
                                    window.addCarouselSlide('edit');
                                }
                            }
                        }
                    }

                    if (editSectionType) {
                        editSectionType.addEventListener('change', () => {
                            switchSectionType();
                            if(typeof window.updatePreviewEdit === 'function') window.updatePreviewEdit();
                        });
                        switchSectionType(); // Init state
                    }

                    const titleInput = document.getElementById('inpEditTitle');
                    const subtitleInput = document.getElementById('inpEditSubtitle');
                    const badgeInput = document.getElementById('inpEditBadge');
                    
                    const previewTitle = document.getElementById('editPreviewTitle');
                    const previewSubtitle = document.getElementById('editPreviewSubtitle');
                    const previewBadge = document.getElementById('editPreviewBadge');
                    
                    const previewHeroArea = document.getElementById('editPreviewHeroArea');
                    const previewCarouselArea = document.getElementById('editPreviewCarouselArea');
                    const previewPopUpArea = document.getElementById('editPreviewPopUpArea');
                    const previewButtonsArea = document.getElementById('editPreviewButtonsArea');
                    
                    const ctaPrimaryInput = document.getElementById('inpEditCtaPrimary');
                    const ctaSecondaryInput = document.getElementById('inpEditCtaSecondary');
                    
                    const previewBtnPrimary = document.getElementById('editPreviewBtnPrimary');
                    const previewBtnSecondary = document.getElementById('editPreviewBtnSecondary');
                    const previewActions = document.getElementById('editPreviewActions');

                    const inpEditTitleColor = document.getElementById('inpEditTitleColor');
                    const inpEditSubtitleColor = document.getElementById('inpEditSubtitleColor');
                    const inpEditTextAlign = document.getElementById('inpEditTextAlign');
                    const inpEditFontFamily = document.getElementById('inpEditFontFamily');
                    const inpEditCtaPrimaryBg = document.getElementById('inpEditCtaPrimaryBg');
                    const inpEditCtaPrimaryColor = document.getElementById('inpEditCtaPrimaryColor');
                    const inpEditCtaSecondaryBg = document.getElementById('inpEditCtaSecondaryBg');
                    const inpEditCtaSecondaryColor = document.getElementById('inpEditCtaSecondaryColor');

                    window.updatePreviewEdit = function() {

                        const typeVal = editSectionType ? editSectionType.value : 'hero';

                        // Toggle preview areas
                        if (previewHeroArea) previewHeroArea.style.display = typeVal === 'hero' ? 'flex' : 'none';
                        if (previewCarouselArea) previewCarouselArea.style.display = typeVal === 'carousel' ? 'flex' : 'none';
                        if (previewPopUpArea) previewPopUpArea.style.display = typeVal === 'pop_up' ? 'flex' : 'none';
                        if (previewButtonsArea) previewButtonsArea.style.display = typeVal === 'buttons' ? 'flex' : 'none';

                        // Espaciado vertical (Márgenes superior e inferior en preview)
                        const spTop = document.getElementById('inpEditSpacingTop') ? (parseInt(document.getElementById('inpEditSpacingTop').value, 10) || 0) : 0;
                        const spBottom = document.getElementById('inpEditSpacingBottom') ? (parseInt(document.getElementById('inpEditSpacingBottom').value, 10) || 0) : 0;
                        [previewHeroArea, previewCarouselArea, previewButtonsArea].forEach(area => {
                            if (area) {
                                area.style.marginTop = spTop + 'px';
                                area.style.marginBottom = spBottom + 'px';
                            }
                        });

                        // Actualizar textos globales para las nuevas áreas
                        const defaultTitle = typeVal === 'buttons' ? 'Bloque Personalizado' : 'Tu Título Aquí';
                        document.querySelectorAll('#editSectionModal .preview-common-title').forEach(el => {
                            el.textContent = titleInput && titleInput.value ? titleInput.value : defaultTitle;
                        });
                        document.querySelectorAll('#editSectionModal .preview-common-subtitle').forEach(el => {
                            el.textContent = subtitleInput && subtitleInput.value ? subtitleInput.value : 'Tu bajada o subtítulo aquí.';
                            el.style.display = subtitleInput && subtitleInput.value ? 'block' : 'none';
                        });

                        // Textos y Tipografías Independientes para Hero
                        if (titleInput && previewTitle) {
                            previewTitle.textContent = titleInput.value || 'Tu Hero Principal';
                            if (inpEditTitleColor && inpEditTitleColor.value) {
                                previewTitle.style.color = inpEditTitleColor.value;
                            } else {
                                previewTitle.style.color = '';
                            }
                            const tFontInp = document.getElementById('inpEditTitleFont');
                            if (tFontInp && tFontInp.value && tFontInp.value !== 'inherit') {
                                if (window.loadGoogleFont) window.loadGoogleFont(tFontInp.value);
                                previewTitle.style.fontFamily = `'${tFontInp.value}', sans-serif`;
                            } else {
                                previewTitle.style.fontFamily = '';
                            }
                        }
                        if (subtitleInput && previewSubtitle) {
                            previewSubtitle.textContent = subtitleInput.value;
                            previewSubtitle.style.display = subtitleInput.value ? 'block' : 'none';
                            if (inpEditSubtitleColor && inpEditSubtitleColor.value) {
                                previewSubtitle.style.color = inpEditSubtitleColor.value;
                            } else {
                                previewSubtitle.style.color = '';
                            }
                            const sFontInp = document.getElementById('inpEditSubtitleFont');
                            if (sFontInp && sFontInp.value && sFontInp.value !== 'inherit') {
                                if (window.loadGoogleFont) window.loadGoogleFont(sFontInp.value);
                                previewSubtitle.style.fontFamily = `'${sFontInp.value}', sans-serif`;
                            } else {
                                previewSubtitle.style.fontFamily = '';
                            }
                        }
                        if (badgeInput && previewBadge) {
                            previewBadge.textContent = badgeInput.value;
                            previewBadge.style.display = badgeInput.value ? 'inline-block' : 'none';
                        }
                        
                        // Alineación, altura y efecto Parallax del contenedor de texto
                        if (previewHeroArea) {
                            const inpAlign = document.querySelector('#editSectionModal input[name="settings[text_align]"]:checked');
                            const alignVal = inpAlign ? inpAlign.value : 'text-center';
                            
                            const innerWrapper = previewHeroArea.querySelector('.relative.z-10');
                            
                            previewHeroArea.classList.remove('text-center', 'text-left', 'text-right', 'items-center', 'items-start', 'items-end');
                            if (innerWrapper) innerWrapper.classList.remove('mx-auto', 'ml-0', 'mr-auto', 'mr-0', 'ml-auto');
                            if (previewSubtitle) previewSubtitle.classList.remove('mx-auto', 'ml-0', 'mr-auto', 'mr-0', 'ml-auto');
                            if (previewActions) previewActions.classList.remove('justify-center', 'justify-start', 'justify-end');
                            
                            const isFullPage = document.getElementById('inpEditFullWidth') && document.getElementById('inpEditFullWidth').checked;
                            const isFullWidthH = document.getElementById('inpEditFullWidthHorizontal') && document.getElementById('inpEditFullWidthHorizontal').checked;
                            const heightVal = document.getElementById('inpEditHeroHeightValue') ? document.getElementById('inpEditHeroHeightValue').value : '';
                            const heightUnit = document.getElementById('inpEditHeroHeightUnit') ? document.getElementById('inpEditHeroHeightUnit').value : 'px';
                            const customHeight = heightVal ? heightVal + heightUnit : '';
                            
                            if (isFullPage) {
                                previewHeroArea.style.minHeight = '400px';
                            } else if (customHeight.trim() !== '') {
                                previewHeroArea.style.minHeight = customHeight;
                            } else {
                                previewHeroArea.style.minHeight = '150px';
                            }

                            if (isFullWidthH) {
                                previewHeroArea.classList.add('w-full', '!rounded-none');
                                previewHeroArea.classList.remove('rounded-xl');
                            } else {
                                previewHeroArea.classList.remove('!rounded-none');
                            }

                            // Efecto Parallax Scrolling
                            const inpParallax = document.getElementById('inpEditParallax');
                            if (inpParallax && inpParallax.checked) {
                                previewHeroArea.style.backgroundAttachment = 'fixed';
                            } else {
                                previewHeroArea.style.backgroundAttachment = 'scroll';
                            }

                            if (alignVal === 'text-left') {
                                previewHeroArea.classList.add('text-left', 'items-start');
                                if (innerWrapper) innerWrapper.classList.add('ml-0', 'mr-auto');
                                if (previewSubtitle) previewSubtitle.classList.add('ml-0', 'mr-auto');
                                if (previewActions) previewActions.classList.add('justify-start');
                            } else if (alignVal === 'text-right') {
                                previewHeroArea.classList.add('text-right', 'items-end');
                                if (innerWrapper) innerWrapper.classList.add('ml-auto', 'mr-0');
                                if (previewSubtitle) previewSubtitle.classList.add('ml-auto', 'mr-0');
                                if (previewActions) previewActions.classList.add('justify-end');
                            } else {
                                previewHeroArea.classList.add('text-center', 'items-center');
                                if (innerWrapper) innerWrapper.classList.add('mx-auto');
                                if (previewSubtitle) previewSubtitle.classList.add('mx-auto');
                                if (previewActions) previewActions.classList.add('justify-center');
                            }
                        }

                        // Botones
                        let hasBtn = false;
                        if (ctaPrimaryInput && previewBtnPrimary) {
                            previewBtnPrimary.textContent = ctaPrimaryInput.value;
                            previewBtnPrimary.style.display = ctaPrimaryInput.value ? 'inline-block' : 'none';
                            if(ctaPrimaryInput.value) {
                                hasBtn = true;
                                if(inpEditCtaPrimaryBg) previewBtnPrimary.style.backgroundColor = inpEditCtaPrimaryBg.value;
                                if(inpEditCtaPrimaryColor) previewBtnPrimary.style.color = inpEditCtaPrimaryColor.value;
                                
                                const bW = document.getElementById('inpEditCtaPrimaryBorderWidth') ? document.getElementById('inpEditCtaPrimaryBorderWidth').value : 'border-0';
                                const bC = document.getElementById('inpEditCtaPrimaryBorderColor') ? document.getElementById('inpEditCtaPrimaryBorderColor').value : 'transparent';
                                const shadow = document.getElementById('inpEditCtaPrimaryShadow') ? document.getElementById('inpEditCtaPrimaryShadow').value : '';
                                
                                previewBtnPrimary.className = `px-6 py-2.5 rounded-lg font-bold text-sm transition-colors ${bW} ${shadow}`;
                                if(bW !== 'border-0') {
                                    previewBtnPrimary.style.borderColor = bC;
                                } else {
                                    previewBtnPrimary.style.borderColor = 'transparent';
                                }
                            }
                        } else {
                            if(previewBtnPrimary) previewBtnPrimary.style.display = 'none';
                        }

                        if (ctaSecondaryInput && previewBtnSecondary) {
                            previewBtnSecondary.textContent = ctaSecondaryInput.value;
                            previewBtnSecondary.style.display = ctaSecondaryInput.value ? 'inline-block' : 'none';
                            if(ctaSecondaryInput.value) {
                                hasBtn = true;
                                if(inpEditCtaSecondaryBg) previewBtnSecondary.style.backgroundColor = inpEditCtaSecondaryBg.value;
                                if(inpEditCtaSecondaryColor) previewBtnSecondary.style.color = inpEditCtaSecondaryColor.value;
                                
                                const bW2 = document.getElementById('inpEditCtaSecondaryBorderWidth') ? document.getElementById('inpEditCtaSecondaryBorderWidth').value : 'border-0';
                                const bC2 = document.getElementById('inpEditCtaSecondaryBorderColor') ? document.getElementById('inpEditCtaSecondaryBorderColor').value : 'transparent';
                                const shadow2 = document.getElementById('inpEditCtaSecondaryShadow') ? document.getElementById('inpEditCtaSecondaryShadow').value : '';
                                
                                previewBtnSecondary.className = `px-6 py-2.5 rounded-lg font-bold text-sm transition-colors ${bW2} ${shadow2}`;
                                if(bW2 !== 'border-0') {
                                    previewBtnSecondary.style.borderColor = bC2;
                                } else {
                                    previewBtnSecondary.style.borderColor = 'transparent';
                                }
                            }
                        } else {
                            if(previewBtnSecondary) previewBtnSecondary.style.display = 'none';
                        }
                        
                        const inpHasButtons = document.getElementById('inpEditHeroHasButtons');
                        const hasButtons = inpHasButtons ? inpHasButtons.checked : true;
                        const clickUrlInp = document.getElementById('editHeroClickUrl');
                        const heroClickUrl = clickUrlInp ? clickUrlInp.value.trim() : '';

                        if (previewActions) {
                            previewActions.style.display = (hasButtons && hasBtn) ? 'flex' : 'none';
                        }

                        let clickBadge = document.getElementById('editPreviewHeroClickBadge');
                        if (previewHeroArea) {
                            const innerWrapper = previewHeroArea.querySelector('.relative.z-10');
                            if (!hasButtons) {
                                previewHeroArea.style.cursor = 'pointer';
                                previewHeroArea.title = heroClickUrl ? ('Clic redirige a: ' + heroClickUrl) : 'Hero clicable en toda el área';
                                if (!clickBadge) {
                                    clickBadge = document.createElement('div');
                                    clickBadge.id = 'editPreviewHeroClickBadge';
                                    clickBadge.className = 'mt-3 inline-flex items-center gap-1.5 px-3 py-1 bg-amber-500/20 text-amber-200 border border-amber-400/40 rounded-full text-[11px] font-semibold backdrop-blur-xs relative z-10';
                                    if (innerWrapper) innerWrapper.appendChild(clickBadge);
                                    else previewHeroArea.appendChild(clickBadge);
                                }
                                clickBadge.style.display = 'inline-flex';
                                clickBadge.innerHTML = `<svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg> Clic en todo el Hero ${heroClickUrl ? '→ ' + heroClickUrl : '(Sin URL asignada)'}`;
                            } else {
                                previewHeroArea.style.cursor = 'default';
                                previewHeroArea.title = '';
                                if (clickBadge) clickBadge.style.display = 'none';
                            }
                        }

                        // Estilo Hero Completo
                        if (previewHeroArea) {
                            previewHeroArea.classList.remove('bg-white', 'min-h-[150px]', 'border-b', 'border-slate-100', '!p-6', 'text-slate-900');
                            previewHeroArea.classList.add('bg-slate-900', 'min-h-[250px]', 'p-8');
                            
                            const bgType = document.getElementById('editHeroBgType')?.value || 'image';
                            const videoEl = document.getElementById('editPreviewHeroVideo');
                            const videoFileInp = document.querySelector('#editSectionModal input[name="background_video_file"]');
                            const videoUrlInp = document.getElementById('editHeroBgVideoUrl');
                            const videoAutoplay = document.getElementById('editHeroVideoAutoplay')?.checked ?? true;
                            const videoLoop = document.getElementById('editHeroVideoLoop')?.checked ?? true;
                            const videoMuted = document.getElementById('editHeroVideoMuted')?.checked ?? true;

                            const focalInpEdit = document.getElementById('inpEditHeroFocalPoint');
                            const focalValEdit = focalInpEdit && focalInpEdit.value ? focalInpEdit.value : 'center center';

                            if (bgType === 'video' && videoEl) {
                                let vidSrc = '';
                                if (videoFileInp && videoFileInp.files && videoFileInp.files[0]) {
                                    vidSrc = URL.createObjectURL(videoFileInp.files[0]);
                                } else if (videoUrlInp && videoUrlInp.value) {
                                    vidSrc = videoUrlInp.value;
                                }
                                if (vidSrc) {
                                    if (videoEl.src !== vidSrc) videoEl.src = vidSrc;
                                    videoEl.autoplay = videoAutoplay;
                                    videoEl.loop = videoLoop;
                                    videoEl.muted = videoMuted;
                                    videoEl.style.objectPosition = focalValEdit;
                                    videoEl.classList.remove('hidden');
                                    videoEl.play().catch(() => {});
                                    previewHeroArea.style.backgroundImage = 'none';
                                } else {
                                    videoEl.classList.add('hidden');
                                    videoEl.pause();
                                }
                            } else {
                                if (videoEl) {
                                    videoEl.classList.add('hidden');
                                    videoEl.pause();
                                }
                                if (window._currentHeroPreviewBg_edit) {
                                    previewHeroArea.style.backgroundImage = window._currentHeroPreviewBg_edit;
                                    previewHeroArea.classList.add('bg-cover');
                                    previewHeroArea.classList.remove('bg-center');
                                    previewHeroArea.style.setProperty('background-position', focalValEdit, 'important');
                                } else {
                                    previewHeroArea.style.backgroundImage = 'none';
                                }
                            }
                        }

                        if (previewTitle) previewTitle.className = 'text-3xl md:text-4xl font-extrabold tracking-tight drop-shadow-md relative z-10';
                        if (previewSubtitle) previewSubtitle.className = 'md:text-base max-w-xl mx-auto mt-4 drop-shadow-sm block relative z-10';

                        // Renderizado de Carrusel en Vivo (Viewport Animado Completo)
                        if (typeVal === 'carousel' && previewCarouselArea) {
                            if (typeof window.renderCarouselLivePreview === 'function') {
                                window.renderCarouselLivePreview('edit');
                            }
                        }

                        // Renderizado de Pop-up en Vivo (Imagen Completa, Overlay y 2FA)
                        if (typeVal === 'pop_up' && previewPopUpArea) {
                            if (typeof window.renderPopUpLivePreview === 'function') {
                                window.renderPopUpLivePreview('edit');
                            }
                        }

                        // Renderizado de Código Custom / HTML Libre en Vivo
                        const isCustom = document.querySelector('#editSectionModal input[name="settings[hero_mode]"]:checked')?.value === 'custom';
                        const customHtmlInp = document.getElementById('editCustomHtml');
                        const customPreviewContainer = document.getElementById('editCustomHtmlPreviewContainer');
                        if (customPreviewContainer && customHtmlInp) {
                            if (customHtmlInp.value && customHtmlInp.value.trim()) {
                                customPreviewContainer.innerHTML = customHtmlInp.value;
                            } else {
                                customPreviewContainer.innerHTML = `
                                    <div class="p-8 bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl w-full text-slate-400 text-xs flex items-center justify-center flex-col gap-2">
                                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                        Área de Bloque Personalizado / Código HTML Inyectado
                                    </div>`;
                            }
                        }

                        if (isCustom) {
                            if (previewHeroArea) previewHeroArea.style.display = 'none';
                            if (previewCarouselArea) previewCarouselArea.style.display = 'none';
                            if (previewPopUpArea) previewPopUpArea.style.display = 'none';
                            if (previewButtonsArea) previewButtonsArea.style.display = 'flex';
                        }
                    }

                    // Handlers para la imagen y video de fondo
                    const bgFileInput = document.querySelector('#editSectionModal input[name="background_image_file"]');
                    const bgUrlInput = document.getElementById('editHeroBgUrl');
                    const videoFileInput = document.querySelector('#editSectionModal input[name="background_video_file"]');
                    const videoUrlInput = document.getElementById('editHeroBgVideoUrl');

                    if (bgFileInput) {
                        bgFileInput.addEventListener('change', function(e) {
                            if (this.files && this.files[0]) {
                                const reader = new FileReader();
                                reader.onload = function(evt) {
                                    window._currentHeroPreviewBg_edit = `url('${evt.target.result}')`;
                                    window.updatePreviewEdit();
                                }
                                reader.readAsDataURL(this.files[0]);
                            } else {
                                window._currentHeroPreviewBg_edit = '';
                                window.updatePreviewEdit();
                            }
                        });
                    }

                    if (bgUrlInput) {
                        bgUrlInput.addEventListener('input', function(e) {
                            if (this.value) {
                                window._currentHeroPreviewBg_edit = `url('${this.value}')`;
                            } else {
                                window._currentHeroPreviewBg_edit = '';
                            }
                            window.updatePreviewEdit();
                        });
                    }

                    if (videoFileInput) {
                        videoFileInput.addEventListener('change', () => { window.updatePreviewEdit && window.updatePreviewEdit(); });
                    }
                    if (videoUrlInput) {
                        videoUrlInput.addEventListener('input', () => { window.updatePreviewEdit && window.updatePreviewEdit(); });
                    }

                    // Attach universal event listeners para todos los inputs del modal de edición
                    const editForm = document.getElementById('editSectionForm') || document.getElementById('editSectionModal');
                    if (editForm) {
                        editForm.addEventListener('input', () => { window.updatePreviewEdit && window.updatePreviewEdit(); });
                        editForm.addEventListener('change', () => { window.updatePreviewEdit && window.updatePreviewEdit(); });
                    }

                    // Device toggles
                    document.querySelectorAll('.edit-preview-device-btn').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            document.querySelectorAll('.edit-preview-device-btn').forEach(b => {
                                b.classList.remove('bg-indigo-50', 'text-indigo-700', 'active');
                                b.classList.add('text-slate-500');
                            });
                            e.currentTarget.classList.add('bg-indigo-50', 'text-indigo-700', 'active');
                            e.currentTarget.classList.remove('text-slate-500');
                            
                            const container = document.getElementById('editLivePreviewContainer');
                            if (container) {
                                if (e.currentTarget.dataset.device === 'mobile') {
                                    container.style.maxWidth = '375px';
                                    container.classList.add('mx-auto');
                                } else {
                                    container.style.maxWidth = '100%';
                                    container.classList.remove('mx-auto');
                                }
                            }
                        });
                    });

                    // Overlay / Degradado interactivo
                    const gradEnableInput = document.getElementById('editEnableGradient');
                    const gradStartInput = document.getElementById('editGradColorStartPicker');
                    const gradEndInput = document.getElementById('editGradColorEndPicker');
                    const gradDirInput = document.getElementById('editGradDir');
                    const gradOpacityStartInput = document.getElementById('editGradOpacityStart');
                    const gradOpacityEndInput = document.getElementById('editGradOpacityEnd');
                    const previewOverlay = document.getElementById('editPreviewOverlay');

                    function hexToRgba(hex, alpha) {
                        let c = (hex || '#000000').replace('#', '');
                        if (c.length === 3) c = c.split('').map(x => x + x).join('');
                        const num = parseInt(c, 16) || 0;
                        return `rgba(${(num >> 16) & 255}, ${(num >> 8) & 255}, ${num & 255}, ${alpha})`;
                    }

                    function updateOverlay() {
                        if (gradEnableInput && gradEnableInput.checked && previewOverlay) {
                            previewOverlay.style.display = 'block';
                            previewOverlay.className = "absolute inset-0 z-0";
                            
                            let dir = gradDirInput ? gradDirInput.value : 'to bottom';
                            let startHex = gradStartInput ? gradStartInput.value : '#0f172a';
                            let endHex = gradEndInput ? gradEndInput.value : '#1e1b4b';
                            let startOp = gradOpacityStartInput ? parseInt(gradOpacityStartInput.value, 10) / 100 : 0.8;
                            let endOp = gradOpacityEndInput ? parseInt(gradOpacityEndInput.value, 10) / 100 : 0.8;
                            
                            let startRgba = hexToRgba(startHex, startOp);
                            let endRgba = hexToRgba(endHex, endOp);
                            
                            previewOverlay.style.background = `linear-gradient(${dir}, ${startRgba}, ${endRgba})`;
                            previewOverlay.style.opacity = 1;
                        } else {
                            if (previewOverlay) previewOverlay.style.display = 'none';
                        }
                    }

                    const gradInputs = [gradEnableInput, gradStartInput, gradEndInput, gradDirInput, gradOpacityStartInput, gradOpacityEndInput];
                    gradInputs.forEach(el => {
                        if (el) el.addEventListener('input', updateOverlay);
                        if (el) el.addEventListener('change', updateOverlay);
                    });

                    const origUpdatePreview = window.updatePreviewEdit;
                    window.updatePreviewEdit = function() {
                        origUpdatePreview();
                        updateOverlay();
                    };

                    // Trigger initial paint
                    setTimeout(window.updatePreviewEdit, 100);
                    document.querySelectorAll('#editSectionModal input[name="settings[text_align]"]').forEach(r => r.addEventListener('change', window.updatePreviewEdit));
                });
            </script>
        </div>
    </div>

    <!-- 2. MODAL EDITAR PROTOCOLOS DE CUIDADO DE TELAS & FIBRAS (REGLAS SVG + ADVERTENCIAS) -->
    <div id="editCareModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Protocolos de Cuidado Textil &amp; Simbología SVG (Sincronizado con Hub 4)</h3>
                    <p class="text-[11px] text-slate-500">Configura advertencias y simbología oficial de lavado por fibra registrada en Telas &amp; Fibras de Hub 4.</p>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer" data-action="close-modal" data-modal="editCareModal">&times;</button>
            </div>

            <form method="POST" action="{{ route('portal.store.documents.update', ['code' => 'CARE_PROTOCOLS']) }}" class="space-y-4 text-xs" id="careProtocolsForm">
                @csrf
                <div class="space-y-4" id="careProtocolsList">
                    @php
                        $standardCareSymbols = \App\Models\CmsOfficialDocument::getStandardCareSymbols();
                    @endphp

                    @foreach($careProtocols as $idx => $c)
                        @php
                            $selectedSymbols = is_array($c['symbols'] ?? null) ? $c['symbols'] : [];
                        @endphp
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3 care-protocol-card" data-index="{{ $idx }}">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-indigo-900 uppercase tracking-wide">Protocolo de Fibra #{{ $idx + 1 }}</span>
                                <button type="button" class="text-rose-600 hover:text-rose-800 text-[11px] font-semibold cursor-pointer" onclick="this.closest('.care-protocol-card').remove()">Eliminar</button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Tela / Fibra (Hub 4)</label>
                                    <div class="flex items-center gap-2">
                                        <select name="payload[{{ $idx }}][fiber]" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg bg-white font-semibold text-xs focus:ring-1 focus:ring-indigo-500">
                                            @foreach($fabrics as $fab)
                                                <option value="{{ $fab->name }}" {{ ($c['fiber'] ?? '') === $fab->name ? 'selected' : '' }}>{{ $fab->name }}</option>
                                            @endforeach
                                            @if(!empty($c['fiber']) && !$fabrics->contains('name', $c['fiber']))
                                                <option value="{{ $c['fiber'] }}" selected>{{ $c['fiber'] }} (Manual)</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Regla Resumen de Lavado</label>
                                    <input type="text" name="payload[{{ $idx }}][rule]" value="{{ $c['rule'] ?? 'Lavar con agua fría en ciclo delicado.' }}" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg bg-white text-xs" placeholder="Ej: Lavar con agua fría (< 30°C) en ciclo delicado">
                                </div>
                            </div>

                            <!-- Campo de Texto Libre para Advertencias de Cuidado -->
                            <div>
                                <label class="block text-amber-900 font-bold mb-1">Advertencia Especial de Cuidado (Texto Libre)</label>
                                <textarea name="payload[{{ $idx }}][warning]" rows="2" class="w-full px-3 py-1.5 border border-amber-300 bg-amber-50/50 rounded-lg text-xs text-amber-950 font-medium placeholder-amber-800/50" placeholder="Ej: Nunca usar secadora térmica ni blanqueadores clorados; secar a la sombra en superficie horizontal plana.">{{ $c['warning'] ?? '' }}</textarea>
                            </div>

                            <!-- Matriz de Checkboxes con Reglas Típicas y Simbología SVG -->
                            <div>
                                <label class="block text-slate-800 font-bold mb-1.5">Simbología Oficial de Cuidado (Selecciona para mostrar en Storefront PDP)</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 p-2.5 bg-white border border-slate-200 rounded-lg">
                                    @foreach($standardCareSymbols as $symKey => $symData)
                                        <label class="flex items-center gap-2 p-1.5 rounded-md hover:bg-slate-50 border border-slate-100 cursor-pointer transition text-[11px]">
                                            <input type="checkbox" name="payload[{{ $idx }}][symbols][]" value="{{ $symKey }}" {{ in_array($symKey, $selectedSymbols) ? 'checked' : '' }} class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                            <span class="text-slate-700">{!! $symData['svg'] !!}</span>
                                            <span class="text-slate-800 font-medium truncate">{{ $symData['name'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-lg cursor-pointer" onclick="window.addCareProtocolRow()">
                        + Añadir Otra Fibra
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" class="px-4 py-2 text-slate-600 font-semibold cursor-pointer" data-action="close-modal" data-modal="editCareModal">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg cursor-pointer shadow-xs">Guardar Protocolos</button>
                    </div>
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
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block font-semibold text-slate-700 text-[11px]">Imagen Principal</label>
                                        <button type="button" class="text-[10px] text-indigo-600 font-bold hover:underline" onclick="window.openCmsMediaModal('create_page_image_url')">Seleccionar del Gestor</button>
                                    </div>
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
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block font-semibold text-slate-700 text-[11px]">Imagen Principal</label>
                                        <button type="button" class="text-[10px] text-indigo-600 font-bold hover:underline" onclick="window.openCmsMediaModal('edit_page_image_url')">Seleccionar del Gestor</button>
                                    </div>
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
<!-- CMS MEDIA MODAL (GESTOR DE IMÁGENES) -->
<div id="cmsMediaModal" class="fixed inset-0 z-[100] hidden">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="window.closeCmsMediaModal()"></div>
    <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden transform transition-all">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-base font-bold text-slate-800 flex items-center">
                    <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Gestor de Imágenes Hub 2
                </h3>
                <div class="flex items-center gap-3">
                    <label class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-lg cursor-pointer hover:bg-indigo-700 transition-colors">
                        Subir Imagen
                        <input type="file" id="cmsMediaUploadInput" class="hidden" accept="image/*" onchange="window.uploadCmsMedia(this)">
                    </label>
                    <button type="button" class="text-slate-400 hover:text-rose-500 transition-colors" onclick="window.closeCmsMediaModal()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-5 bg-slate-50/50">
                <div id="cmsMediaGrid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <!-- Images will be rendered here via JS -->
                </div>
                <div id="cmsMediaLoading" class="hidden flex flex-col items-center justify-center py-10">
                    <svg class="w-8 h-8 text-indigo-500 animate-spin mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="text-xs font-semibold text-slate-500">Cargando...</span>
                </div>
                <div id="cmsMediaEmpty" class="hidden flex flex-col items-center justify-center py-12 text-center">
                    <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="text-sm font-semibold text-slate-600 block">No hay imágenes en el gestor</span>
                    <span class="text-xs text-slate-400 mt-1 block">Sube una nueva imagen para comenzar.</span>
                </div>
            </div>
            <div class="px-5 py-3 border-t border-slate-100 bg-white flex justify-end">
                <button type="button" class="px-4 py-2 bg-slate-200 text-slate-700 text-xs font-bold rounded-lg hover:bg-slate-300 transition-colors" onclick="window.closeCmsMediaModal()">Cancelar</button>
            </div>
        </div>
    </div>
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

    // Helper reactivo para carga dinámica de Google Fonts en el CMS
    window.loadGoogleFont = function(fontFamily) {
        if (!fontFamily || typeof fontFamily !== 'string') return;
        const fontName = fontFamily.trim();
        if (['inherit', 'inherit / vitrina', 'sans-serif', 'font-sans', 'font-serif', 'font-mono'].includes(fontName.toLowerCase())) return;
        const fontId = 'gfont-' + fontName.toLowerCase().replace(/[^a-z0-9]/g, '-');
        if (document.getElementById(fontId)) return;
        const link = document.createElement('link');
        link.id = fontId;
        link.rel = 'stylesheet';
        link.href = `https://fonts.googleapis.com/css2?family=${encodeURIComponent(fontName).replace(/%20/g, '+')}:wght@300;400;500;600;700;800;900&display=swap`;
        document.head.appendChild(link);
    };

    window.updateVitrinaFontPreview = function() {
        const headingFont = document.getElementById('vitrinaTypoHeading')?.value;
        const subFont = document.getElementById('vitrinaTypoSubtitle')?.value;
        const bodyFont = document.getElementById('vitrinaTypoBody')?.value;
        const accentFont = document.getElementById('vitrinaTypoAccent')?.value;

        const previewFrame = document.querySelector('[data-storefront-preview-frame]');
        if (!previewFrame) return;

        if (headingFont) {
            if (window.loadGoogleFont) window.loadGoogleFont(headingFont);
            previewFrame.querySelectorAll('[data-preview-title], [data-preview-product-title], h1, h2, h3').forEach(el => {
                el.style.fontFamily = `'${headingFont}', sans-serif`;
            });
        }
        if (subFont) {
            if (window.loadGoogleFont) window.loadGoogleFont(subFont);
            previewFrame.querySelectorAll('[data-preview-sub], .preview-sub, [data-preview-product-tag], p').forEach(el => {
                el.style.fontFamily = `'${subFont}', sans-serif`;
            });
        }
        if (bodyFont) {
            if (window.loadGoogleFont) window.loadGoogleFont(bodyFont);
            previewFrame.style.fontFamily = `'${bodyFont}', sans-serif`;
        }
        if (accentFont) {
            if (window.loadGoogleFont) window.loadGoogleFont(accentFont);
            previewFrame.querySelectorAll('[data-preview-cta], [data-preview-product-btn], [data-preview-badge], [data-preview-kicker-badge], [data-preview-product-price]').forEach(el => {
                el.style.fontFamily = `'${accentFont}', sans-serif`;
            });
        }
    };

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

    window.addCareProtocolRow = function() {
        const list = document.getElementById('careProtocolsList');
        if (!list) return;
        const count = list.querySelectorAll('.care-protocol-card').length;
        const newIdx = count;
        const fabricsOpts = [
            @foreach($fabrics as $fab)
                `<option value="{{ addslashes($fab->name) }}">{{ addslashes($fab->name) }}</option>`,
            @endforeach
        ].join('');

        const syms = [
            { key: 'wash_30', name: 'Lavado frío (máx 30°C)', svg: '<svg viewBox="0 0 24 24" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h18l-2 10a3 3 0 01-3 3H8a3 3 0 01-3-3L3 8z"/><path d="M3 11c3 1 6-1 9 0s6-1 9 0"/></svg>' },
            { key: 'wash_hand', name: 'Lavado a mano', svg: '<svg viewBox="0 0 24 24" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h18l-2 10a3 3 0 01-3 3H8a3 3 0 01-3-3L3 8z"/><path d="M12 4v4m-2-3l2-2 2 2"/><path d="M3 12c3 1 6-1 9 0s6-1 9 0"/></svg>' },
            { key: 'no_bleach', name: 'No usar cloro / blanqueador', svg: '<svg viewBox="0 0 24 24" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l10 17H2L12 3z"/><line x1="5" y1="18" x2="19" y2="6"/></svg>' },
            { key: 'dry_flat', name: 'Secado en plano horizontal', svg: '<svg viewBox="0 0 24 24" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="7" y1="12" x2="17" y2="12"/></svg>' },
            { key: 'dry_shade', name: 'Secar a la sombra', svg: '<svg viewBox="0 0 24 24" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="4" y1="8" x2="8" y2="4"/><line x1="4" y1="12" x2="12" y2="4"/></svg>' },
            { key: 'no_tumble', name: 'No usar secadora térmica', svg: '<svg viewBox="0 0 24 24" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="12" cy="12" r="5"/><line x1="5" y1="19" x2="19" y2="5"/></svg>' },
            { key: 'iron_low', name: 'Plancha suave (máx 110°C)', svg: '<svg viewBox="0 0 24 24" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 17h18a1 1 0 001-1c0-4-3-8-8-8H4a1 1 0 00-1 1v8z"/><circle cx="10" cy="14" r="1" fill="currentColor"/></svg>' },
            { key: 'no_iron', name: 'No planchar elásticos / apliques', svg: '<svg viewBox="0 0 24 24" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 17h18a1 1 0 001-1c0-4-3-8-8-8H4a1 1 0 00-1 1v8z"/><line x1="4" y1="19" x2="20" y2="5"/></svg>' },
            { key: 'no_dryclean', name: 'No lavar en seco', svg: '<svg viewBox="0 0 24 24" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="8"/><line x1="6" y1="18" x2="18" y2="6"/></svg>' },
        ];

        const symsHtml = syms.map(s => `
            <label class="flex items-center gap-2 p-1.5 rounded-md hover:bg-slate-50 border border-slate-100 cursor-pointer transition text-[11px]">
                <input type="checkbox" name="payload[${newIdx}][symbols][]" value="${s.key}" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                <span class="text-slate-700">${s.svg}</span>
                <span class="text-slate-800 font-medium truncate">${s.name}</span>
            </label>
        `).join('');

        const card = document.createElement('div');
        card.className = 'p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3 care-protocol-card';
        card.dataset.index = newIdx;
        card.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="font-bold text-xs text-indigo-900 uppercase tracking-wide">Protocolo de Fibra #${newIdx + 1}</span>
                <button type="button" class="text-rose-600 hover:text-rose-800 text-[11px] font-semibold cursor-pointer" onclick="this.closest('.care-protocol-card').remove()">Eliminar</button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tela / Fibra (Hub 4)</label>
                    <select name="payload[${newIdx}][fiber]" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg bg-white font-semibold text-xs focus:ring-1 focus:ring-indigo-500">
                        ${fabricsOpts}
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Regla Resumen de Lavado</label>
                    <input type="text" name="payload[${newIdx}][rule]" value="Lavar con agua fría en ciclo delicado." class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg bg-white text-xs" placeholder="Ej: Lavar con agua fría (< 30°C) en ciclo delicado">
                </div>
            </div>
            <div>
                <label class="block text-amber-900 font-bold mb-1">Advertencia Especial de Cuidado (Texto Libre)</label>
                <textarea name="payload[${newIdx}][warning]" rows="2" class="w-full px-3 py-1.5 border border-amber-300 bg-amber-50/50 rounded-lg text-xs text-amber-950 font-medium placeholder-amber-800/50" placeholder="Ej: Nunca usar secadora térmica ni blanqueadores clorados; secar a la sombra en superficie horizontal plana."></textarea>
            </div>
            <div>
                <label class="block text-slate-800 font-bold mb-1.5">Simbología Oficial de Cuidado (Selecciona para mostrar en Storefront PDP)</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 p-2.5 bg-white border border-slate-200 rounded-lg">
                    ${symsHtml}
                </div>
            </div>
        `;
        list.appendChild(card);
    };

    // ==========================================
    // 2. GESTIÓN DE SUBNAV TABS (6 PESTAÑAS PRINCIPALES)
    // ==========================================
    const tabPanelIds = {
        'visual': 'storeVisualPanel',
        'content_rules': 'storeContentRulesPanel',
        'pages': 'storePagesPanel',
        'tracking': 'storeTrackingPanel',
        'filters': 'storeFiltersPanel',
        'media': 'storeMediaPanel'
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

        if (activeTabKey === 'media') {
            if (typeof window.loadCmsMedia === 'function') window.loadCmsMedia();
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

        if (typeof window.updateVitrinaFontPreview === 'function') {
            window.updateVitrinaFontPreview();
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

        // 4.4 Tipografías de Vitrina (Google Fonts)
        const typoHeading = document.getElementById('vitrinaTypoHeading');
        const typoSubtitle = document.getElementById('vitrinaTypoSubtitle');
        const typoBody = document.getElementById('vitrinaTypoBody');
        const typoAccent = document.getElementById('vitrinaTypoAccent');

        if (typoHeading) typoHeading.value = data.typography_heading || 'Outfit';
        if (typoSubtitle) typoSubtitle.value = data.typography_subtitle || 'Outfit';
        if (typoBody) typoBody.value = data.typography_body || 'Plus Jakarta Sans';
        if (typoAccent) typoAccent.value = data.typography_accent || 'Plus Jakarta Sans';

        [data.typography_heading, data.typography_subtitle, data.typography_body, data.typography_accent].forEach(f => {
            if (f && window.loadGoogleFont) window.loadGoogleFont(f);
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
        if (page.slug === 'aliados' || page.slug === 'convenios' || page.slug === 'convenios-con-aliados') {
            alert('Esta página es el repositorio automático de eventos y convenios con aliados de Hub 7. Se sincroniza automáticamente con los eventos aprobados.');
            return;
        }
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
        const isCustom = mode === 'custom';
        const lblStruct = document.getElementById('createHeroModeStructuredLbl');
        const lblCustom = document.getElementById('createHeroModeCustomLbl');
        if (lblCustom) lblCustom.className = isCustom ? 'flex-1 py-2 rounded-lg bg-white text-indigo-700 shadow-xs text-center cursor-pointer transition font-semibold' : 'flex-1 py-2 rounded-lg text-slate-600 hover:text-slate-900 text-center cursor-pointer transition font-semibold';
        if (lblStruct) lblStruct.className = isCustom ? 'flex-1 py-2 rounded-lg text-slate-600 hover:text-slate-900 text-center cursor-pointer transition font-semibold' : 'flex-1 py-2 rounded-lg bg-white text-indigo-700 shadow-xs text-center cursor-pointer transition font-semibold';
        
        const commonFields = document.getElementById('createCommonFieldsWrap');
        const heroWrap = document.getElementById('createHeroWrap');
        const carouselWrap = document.getElementById('createCarouselWrap');
        const popUpWrap = document.getElementById('createPopUpWrap');
        const buttonsWrap = document.getElementById('createButtonsWrap');
        const customCodeWrap = document.getElementById('createCustomCodeWrap');
        const spacingWrap = document.getElementById('createSpacingWrap');
        
        if (isCustom) {
            if(commonFields) commonFields.style.display = 'none';
            if(heroWrap) heroWrap.style.display = 'none';
            if(carouselWrap) carouselWrap.style.display = 'none';
            if(popUpWrap) popUpWrap.style.display = 'none';
            if(buttonsWrap) buttonsWrap.style.display = 'none';
            if(customCodeWrap) customCodeWrap.style.display = 'block';
            if(spacingWrap) spacingWrap.style.display = 'block';
        } else {
            const type = document.getElementById('createSectionType')?.value;
            if(commonFields) commonFields.style.display = (type === 'carousel') ? 'none' : 'block';
            if(customCodeWrap) customCodeWrap.style.display = 'none';
            if(heroWrap) heroWrap.style.display = type === 'hero' ? 'block' : 'none';
            if(carouselWrap) carouselWrap.style.display = type === 'carousel' ? 'block' : 'none';
            if(popUpWrap) popUpWrap.style.display = type === 'pop_up' ? 'block' : 'none';
            if(buttonsWrap) buttonsWrap.style.display = type === 'buttons' ? 'block' : 'none';
            if(spacingWrap) spacingWrap.style.display = type === 'pop_up' ? 'none' : 'block';
            if (type === 'carousel') {
                const slidesList = document.getElementById('createCarouselSlidesList');
                if (slidesList && slidesList.querySelectorAll('.slide-item').length === 0 && typeof window.addCarouselSlide === 'function') {
                    window.addCarouselSlide('create');
                }
            }
        }
    };

    window.toggleHeroModeEdit = function(mode) {
        const isCustom = mode === 'custom';
        const lblStruct = document.getElementById('editHeroModeStructuredLbl');
        const lblCustom = document.getElementById('editHeroModeCustomLbl');
        if (lblCustom) lblCustom.className = isCustom ? 'flex-1 py-2 rounded-lg bg-white text-indigo-700 shadow-xs text-center cursor-pointer transition font-semibold' : 'flex-1 py-2 rounded-lg text-slate-600 hover:text-slate-900 text-center cursor-pointer transition font-semibold';
        if (lblStruct) lblStruct.className = isCustom ? 'flex-1 py-2 rounded-lg text-slate-600 hover:text-slate-900 text-center cursor-pointer transition font-semibold' : 'flex-1 py-2 rounded-lg bg-white text-indigo-700 shadow-xs text-center cursor-pointer transition font-semibold';
        
        const commonFields = document.getElementById('editCommonFieldsWrap');
        const heroWrap = document.getElementById('editHeroWrap');
        const carouselWrap = document.getElementById('editCarouselWrap');
        const popUpWrap = document.getElementById('editPopUpWrap');
        const buttonsWrap = document.getElementById('editButtonsWrap');
        const customCodeWrap = document.getElementById('editCustomCodeWrap');
        const spacingWrap = document.getElementById('editSpacingWrap');
        
        if (isCustom) {
            if(commonFields) commonFields.style.display = 'none';
            if(heroWrap) heroWrap.style.display = 'none';
            if(carouselWrap) carouselWrap.style.display = 'none';
            if(popUpWrap) popUpWrap.style.display = 'none';
            if(buttonsWrap) buttonsWrap.style.display = 'none';
            if(customCodeWrap) customCodeWrap.style.display = 'block';
            if(spacingWrap) spacingWrap.style.display = 'block';
        } else {
            const type = document.getElementById('editSectionType')?.value;
            if(commonFields) commonFields.style.display = (type === 'carousel') ? 'none' : 'block';
            if(customCodeWrap) customCodeWrap.style.display = 'none';
            if(heroWrap) heroWrap.style.display = type === 'hero' ? 'block' : 'none';
            if(carouselWrap) carouselWrap.style.display = type === 'carousel' ? 'block' : 'none';
            if(popUpWrap) popUpWrap.style.display = type === 'pop_up' ? 'block' : 'none';
            if(buttonsWrap) buttonsWrap.style.display = type === 'buttons' ? 'block' : 'none';
            if(spacingWrap) spacingWrap.style.display = type === 'pop_up' ? 'none' : 'block';
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
    const startHex = document.getElementById(prefix + 'GradColorStart')?.value || '#0f172a';
    const endHex = document.getElementById(prefix + 'GradColorEnd')?.value || '#1e1b4b';
    const dir = document.getElementById(prefix + 'GradDir')?.value || 'to bottom';
    const opStartStr = document.getElementById(prefix + 'GradOpacityStart')?.value || '80';
    const opEndStr = document.getElementById(prefix + 'GradOpacityEnd')?.value || '80';
    function hexToRgba(hex, alpha) {
        let c = (hex || '#000000').replace('#', '');
        if (c.length === 3) c = c.split('').map(x => x + x).join('');
        const num = parseInt(c, 16) || 0;
        return `rgba(${(num >> 16) & 255}, ${(num >> 8) & 255}, ${num & 255}, ${alpha})`;
    }
    const startRgba = hexToRgba(startHex, parseInt(opStartStr, 10) / 100);
    const endRgba = hexToRgba(endHex, parseInt(opEndStr, 10) / 100);
    const bar = document.getElementById(prefix + 'GradPreviewBar');
    if (bar) {
        bar.style.background = `linear-gradient(${dir}, ${startRgba}, ${endRgba})`;
    }
    if (prefix === 'create' && typeof window.updatePreviewCreate === 'function') window.updatePreviewCreate();
    if (prefix === 'edit' && typeof window.updatePreviewEdit === 'function') window.updatePreviewEdit();
};



            window.toggleEditTypePanels = function(typeVal) {
        const isCustom = document.querySelector('#editSectionForm input[name="settings[hero_mode]"]:checked')?.value === 'custom';
        
        const editHeroWrap = document.getElementById('editHeroWrap');
        const editCarouselWrap = document.getElementById('editCarouselWrap');
        const editPopUpWrap = document.getElementById('editPopUpWrap');
        const editButtonsWrap = document.getElementById('editButtonsWrap');
        const editCustomCodeWrap = document.getElementById('editCustomCodeWrap');
        const editCommonFieldsWrap = document.getElementById('editCommonFieldsWrap');
        const editSpacingWrap = document.getElementById('editSpacingWrap');

        if(isCustom) {
            if(editHeroWrap) editHeroWrap.style.display = 'none';
            if(editCarouselWrap) editCarouselWrap.style.display = 'none';
            if(editPopUpWrap) editPopUpWrap.style.display = 'none';
            if(editButtonsWrap) editButtonsWrap.style.display = 'none';
            if(editCommonFieldsWrap) editCommonFieldsWrap.style.display = 'none';
            if(editCustomCodeWrap) editCustomCodeWrap.style.display = 'block';
            if(editSpacingWrap) editSpacingWrap.style.display = 'block';
        } else {
            if(editCommonFieldsWrap) editCommonFieldsWrap.style.display = (typeVal === 'carousel') ? 'none' : 'block';
            if(editCustomCodeWrap) editCustomCodeWrap.style.display = 'none';
            if(editHeroWrap) editHeroWrap.style.display = typeVal === 'hero' ? 'block' : 'none';
            if(editCarouselWrap) editCarouselWrap.style.display = typeVal === 'carousel' ? 'block' : 'none';
            if(editPopUpWrap) editPopUpWrap.style.display = typeVal === 'pop_up' ? 'block' : 'none';
            if(editButtonsWrap) editButtonsWrap.style.display = typeVal === 'buttons' ? 'block' : 'none';
            if(editSpacingWrap) editSpacingWrap.style.display = typeVal === 'pop_up' ? 'none' : 'block';
            if(typeVal === 'pop_up') {
                if (typeof window.handlePopupActionChange === 'function') {
                    window.handlePopupActionChange('edit');
                }
            }
        }
    };

    window.openCreateSectionModal = function() {
        window.openStoreModal('createSectionModal');
        if (typeof window.setHeroFocalPoint === 'function') {
            const curFocal = document.getElementById('inpCreateHeroFocalPoint')?.value || 'center center';
            window.setHeroFocalPoint('create', curFocal, false);
        }
        if (typeof window.setSectionSpacing === 'function') {
            window.setSectionSpacing('create', 'top', 0, false);
            window.setSectionSpacing('create', 'bottom', 0, false);
        }
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

        // Regla: Las vitrinas web se configuran en Ajustes del Hub 4 (Catálogo e Inventario).
        if (sec.type === 'catalog_section' || sec.type === 'curated_products' || sec.type === 'vitrina_web') {
            window.open(`/admin/inventario-catalogo?tab=sections&edit_section_id=${sec.id}`, '_blank');
            return;
        }

        const form = document.getElementById('editSectionForm');
        if (form) form.action = `/admin/tienda-cms/secciones/${sec.id}`;

        const universeSel = document.querySelector('#editSectionForm select[name="universe"]');
        if (universeSel) universeSel.value = sec.universe || 'all';

        const statusSel = document.querySelector('#editSectionForm [name="status"]');
        if (statusSel) statusSel.value = sec.status || 'active';

        const typeSel = document.getElementById('editSectionType');
        if (typeSel) {
            typeSel.value = sec.type || 'hero';
            window.toggleEditTypePanels(typeSel.value);
        }

        const nameInp = document.querySelector('#editSectionForm input[name="name"]');
        if (nameInp) nameInp.value = sec.name || '';

        const badgeInp = document.getElementById('inpEditBadge');
        if (badgeInp) badgeInp.value = sec.badge || '';

        const titleInp = document.getElementById('inpEditTitle');
        if (titleInp) titleInp.value = sec.title || '';

        const subtitleInp = document.getElementById('inpEditSubtitle');
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
        const htmlArea = document.querySelector('#editSectionForm textarea[name="custom_html"]');
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
        const hmRadio = document.querySelector(`#editSectionModal input[name="settings[hero_mode]"][value="${heroMode}"]`);
        if (hmRadio) hmRadio.checked = true;
        window.toggleHeroModeEdit(heroMode);

        const isFullWidthBool = (st.full_width === 1 || st.full_width === '1' || st.full_width === true);
        const editFullWidth = document.getElementById('inpEditFullWidth');
        if (editFullWidth) editFullWidth.checked = isFullWidthBool;
        const editCarouselFullWidth = document.getElementById('inpEditCarouselFullWidth');
        if (editCarouselFullWidth) editCarouselFullWidth.checked = isFullWidthBool;
        
        const isFullWidthHBool = (st.full_width_horizontal === 1 || st.full_width_horizontal === '1' || st.full_width_horizontal === true);
        const editFullWidthH = document.getElementById('inpEditFullWidthHorizontal');
        if (editFullWidthH) editFullWidthH.checked = isFullWidthHBool;
        const editCarouselFullWidthH = document.getElementById('inpEditCarouselFullWidthHorizontal');
        if (editCarouselFullWidthH) editCarouselFullWidthH.checked = isFullWidthHBool;
        
        const editHeroHeightValue = document.getElementById('inpEditHeroHeightValue');
        const editHeroHeightUnit = document.getElementById('inpEditHeroHeightUnit');
        if (editHeroHeightValue && editHeroHeightUnit) {
            editHeroHeightValue.value = st.hero_height_value || '';
            editHeroHeightUnit.value = st.hero_height_unit || 'px';
        }
        const editCarouselHeightValue = document.getElementById('inpEditCarouselHeightValue');
        const editCarouselHeightUnit = document.getElementById('inpEditCarouselHeightUnit');
        if (editCarouselHeightValue && editCarouselHeightUnit) {
            editCarouselHeightValue.value = st.hero_height_value || '';
            editCarouselHeightUnit.value = st.hero_height_unit || 'px';
        }

        // Tipo de fondo (Imagen vs Video)
        const bgType = st.background_type || 'image';
        const editHeroBgType = document.getElementById('editHeroBgType');
        if (editHeroBgType) {
            editHeroBgType.value = bgType;
            window.toggleHeroBgType(bgType, 'edit');
        }

        const editHeroBgUrl = document.getElementById('editHeroBgUrl');
        if (editHeroBgUrl) {
            editHeroBgUrl.value = st.background_image || '';
            if (st.background_image) {
                window.previewHeroBgUrl(st.background_image, 'editHeroBgPreview');
                window._currentHeroPreviewBg_edit = `url('${st.background_image}')`;
            } else {
                window.clearHeroBgPreview('editSectionModal', 'editHeroBgPreview', 'editHeroBgUrl');
                window._currentHeroPreviewBg_edit = '';
            }
        }
        const editHeroBgAlt = document.getElementById('editHeroBgAlt');
        if (editHeroBgAlt) {
            editHeroBgAlt.value = st.background_image_alt || '';
        }

        // Punto focal de la imagen
        const focalPoint = st.image_focal_point || 'center center';
        if (typeof window.setHeroFocalPoint === 'function') {
            window.setHeroFocalPoint('edit', focalPoint, false);
        }

        // Fondo Video
        const editHeroBgVideoUrl = document.getElementById('editHeroBgVideoUrl');
        if (editHeroBgVideoUrl) editHeroBgVideoUrl.value = st.background_video || '';

        const editHeroVideoAutoplay = document.getElementById('editHeroVideoAutoplay');
        if (editHeroVideoAutoplay) editHeroVideoAutoplay.checked = (st.video_autoplay === undefined || st.video_autoplay == 1 || st.video_autoplay === true);

        const editHeroVideoLoop = document.getElementById('editHeroVideoLoop');
        if (editHeroVideoLoop) editHeroVideoLoop.checked = (st.video_loop === undefined || st.video_loop == 1 || st.video_loop === true);

        const editHeroVideoMuted = document.getElementById('editHeroVideoMuted');
        if (editHeroVideoMuted) editHeroVideoMuted.checked = (st.video_muted === undefined || st.video_muted == 1 || st.video_muted === true);

        // Degradado Overlay del Hero
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

        const opStartVal = (st.gradient_opacity_start !== undefined) ? st.gradient_opacity_start : (st.gradient_opacity || 80);
        const opEndVal = (st.gradient_opacity_end !== undefined) ? st.gradient_opacity_end : (st.gradient_opacity || 80);

        const editGradOpacityStart = document.getElementById('editGradOpacityStart');
        const editGradOpacityStartVal = document.getElementById('editGradOpacityStartVal');
        if (editGradOpacityStart) editGradOpacityStart.value = opStartVal;
        if (editGradOpacityStartVal) editGradOpacityStartVal.textContent = opStartVal + '%';

        const editGradOpacityEnd = document.getElementById('editGradOpacityEnd');
        const editGradOpacityEndVal = document.getElementById('editGradOpacityEndVal');
        if (editGradOpacityEnd) editGradOpacityEnd.value = opEndVal;
        if (editGradOpacityEndVal) editGradOpacityEndVal.textContent = opEndVal + '%';
        window.updateHeroGradientPreview('edit');

        // Carousel Config & Slides (Contenido integrado por Slide)
        const editCarouselAutoPlay = document.getElementById('editCarouselAutoPlay');
        if (editCarouselAutoPlay) editCarouselAutoPlay.checked = (st.autoplay === undefined || st.autoplay == 1 || st.autoplay === true);

        const editCarouselAutoplayTime = document.getElementById('editCarouselAutoplayTime');
        if (editCarouselAutoplayTime) editCarouselAutoplayTime.value = st.autoplay_time || 5;

        if (window._carouselPreviewState && window._carouselPreviewState.edit) {
            window._carouselPreviewState.edit.activeIdx = 0;
        }

        const editCarouselSlidesList = document.getElementById('editCarouselSlidesList');
        if (editCarouselSlidesList) {
            editCarouselSlidesList.innerHTML = '';
            window.slideCounters.edit = 0;
            if (st.carousel_slides && Array.isArray(st.carousel_slides) && st.carousel_slides.length > 0) {
                st.carousel_slides.forEach((slide, idx) => {
                    const hydratedSlide = Object.assign({}, slide);
                    if (idx === 0) {
                        if (!hydratedSlide.title && sec.title) hydratedSlide.title = sec.title;
                        if (!hydratedSlide.subtitle && sec.subtitle) hydratedSlide.subtitle = sec.subtitle;
                        if (!hydratedSlide.badge && sec.badge) hydratedSlide.badge = sec.badge;
                    }
                    window.addCarouselSlide('edit', hydratedSlide);
                });
            } else if ((sec.type || '') === 'carousel') {
                window.addCarouselSlide('edit', {
                    title: sec.title || '',
                    subtitle: sec.subtitle || '',
                    badge: sec.badge || ''
                });
            }
        }

        // Pop-up Config (Hub 2 / Hub 7 / Hub 3)
        const editPopupAction = document.getElementById('editPopupAction');
        if (editPopupAction) editPopupAction.value = st.popup_action || 'ally_discount';

        const editPopupAllyCampaignId = document.getElementById('editPopupAllyCampaignId');
        if (editPopupAllyCampaignId) editPopupAllyCampaignId.value = st.popup_ally_campaign_id || '';

        const editAllyImgSource = st.popup_ally_image_source || 'ally_poster';
        const editAllyImgSourceRadio = document.querySelector(`#editSectionModal input[name="settings[popup_ally_image_source]"][value="${editAllyImgSource}"]`);
        if (editAllyImgSourceRadio) editAllyImgSourceRadio.checked = true;

        const editPopupStoreDiscVal = document.getElementById('editPopupStoreDiscountValue');
        if (editPopupStoreDiscVal) editPopupStoreDiscVal.value = st.popup_store_discount_value || 15;

        const editPopupStoreDiscType = document.getElementById('editPopupStoreDiscountType');
        if (editPopupStoreDiscType) editPopupStoreDiscType.value = st.popup_store_discount_type || 'percent';

        const editPopupMinOrder = document.getElementById('editPopupMinOrder');
        if (editPopupMinOrder) editPopupMinOrder.value = st.popup_min_order || 499;

        const editPopupDaysValid = document.getElementById('editPopupDaysValid');
        if (editPopupDaysValid) editPopupDaysValid.value = st.popup_days_valid || 30;

        const editPopupPresaleUrl = document.getElementById('editPopupPresaleUrl');
        if (editPopupPresaleUrl) editPopupPresaleUrl.value = st.popup_presale_url || '';

        const editPopupImageUrl = document.getElementById('editPopupImageUrl');
        if (editPopupImageUrl) {
            editPopupImageUrl.value = st.popup_image || st.popup_image_url || '';
            window._currentPopupPreviewBg_edit = st.popup_image || st.popup_image_url || '';
        }

        const editPopupFocalPoint = st.popup_image_focal_point || 'center center';
        const editFocalInp = document.getElementById('inpEditPopupFocalPoint');
        if (editFocalInp) editFocalInp.value = editPopupFocalPoint;

        const focalMatrixEdit = document.getElementById('editPopupFocalMatrix');
        if (focalMatrixEdit) {
            focalMatrixEdit.querySelectorAll('button').forEach(b => {
                const isMatch = b.dataset.point === editPopupFocalPoint;
                b.className = isMatch ? 'h-7 text-[10px] font-bold rounded border border-indigo-600 bg-indigo-600 text-white transition' : 'h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition';
            });
        }

        const editPopupEnableGrad = document.getElementById('editPopupEnableGradient');
        if (editPopupEnableGrad) {
            editPopupEnableGrad.checked = (st.popup_enable_gradient === undefined || st.popup_enable_gradient == 1 || st.popup_enable_gradient === '1' || st.popup_enable_gradient === true);
        }

        const editPopupGradColorStart = document.getElementById('editPopupGradColorStart');
        const editPopupGradColorStartPicker = document.getElementById('editPopupGradColorStartPicker');
        if (editPopupGradColorStart) editPopupGradColorStart.value = st.popup_gradient_color_start || '#0f172a';
        if (editPopupGradColorStartPicker) editPopupGradColorStartPicker.value = st.popup_gradient_color_start || '#0f172a';

        const editPopupGradColorEnd = document.getElementById('editPopupGradColorEnd');
        const editPopupGradColorEndPicker = document.getElementById('editPopupGradColorEndPicker');
        if (editPopupGradColorEnd) editPopupGradColorEnd.value = st.popup_gradient_color_end || '#000000';
        if (editPopupGradColorEndPicker) editPopupGradColorEndPicker.value = st.popup_gradient_color_end || '#000000';

        const editPopupGradOpacityStart = document.getElementById('editPopupGradOpacityStart');
        if (editPopupGradOpacityStart) {
            editPopupGradOpacityStart.value = st.popup_gradient_opacity_start || 85;
            const startValEl = document.getElementById('editPopupGradOpacityStartVal');
            if (startValEl) startValEl.textContent = editPopupGradOpacityStart.value + '%';
        }

        const editPopupGradOpacityEnd = document.getElementById('editPopupGradOpacityEnd');
        if (editPopupGradOpacityEnd) {
            editPopupGradOpacityEnd.value = st.popup_gradient_opacity_end || 95;
            const endValEl = document.getElementById('editPopupGradOpacityEndVal');
            if (endValEl) endValEl.textContent = editPopupGradOpacityEnd.value + '%';
        }

        const editPopupGradDir = document.getElementById('editPopupGradDir');
        if (editPopupGradDir) editPopupGradDir.value = st.popup_gradient_direction || 'to bottom';

        const editPopupCtaText = document.getElementById('editPopupCtaText');
        if (editPopupCtaText) editPopupCtaText.value = st.popup_cta_text || '';

        const editPopupDelay = document.getElementById('editPopupDelaySeconds');
        if (editPopupDelay) editPopupDelay.value = st.popup_delay_seconds || 3;

        const editPopupFrequency = document.getElementById('editPopupFrequency');
        if (editPopupFrequency) editPopupFrequency.value = st.popup_frequency || 'once_per_session';

        const editPopupCloseBehavior = document.getElementById('editPopupCloseBehavior');
        if (editPopupCloseBehavior) editPopupCloseBehavior.value = st.popup_close_behavior || 'outside';

        const editPopupTitleColor = document.getElementById('editPopupTitleColor');
        const editPopupTitleColorPicker = document.getElementById('editPopupTitleColorPicker');
        if (editPopupTitleColor) editPopupTitleColor.value = st.popup_title_color || '#ffffff';
        if (editPopupTitleColorPicker) editPopupTitleColorPicker.value = st.popup_title_color || '#ffffff';

        const editPopupSubtitleColor = document.getElementById('editPopupSubtitleColor');
        const editPopupSubtitleColorPicker = document.getElementById('editPopupSubtitleColorPicker');
        if (editPopupSubtitleColor) editPopupSubtitleColor.value = st.popup_subtitle_color || '#e2e8f0';
        if (editPopupSubtitleColorPicker) editPopupSubtitleColorPicker.value = st.popup_subtitle_color || '#e2e8f0';

        if (typeof window.handlePopupActionChange === 'function') {
            window.handlePopupActionChange('edit');
        }

        // Botones CTA vs Clic en Todo el Hero
        const editHasButtons = document.getElementById('inpEditHeroHasButtons');
        if (editHasButtons) {
            const hasBtns = (st.hero_has_buttons === undefined || st.hero_has_buttons === 1 || st.hero_has_buttons === '1' || st.hero_has_buttons === true);
            editHasButtons.checked = hasBtns;
            window.toggleHeroButtonsMode && window.toggleHeroButtonsMode('edit');
        }

        const editHeroClickUrl = document.getElementById('editHeroClickUrl');
        if (editHeroClickUrl) editHeroClickUrl.value = st.hero_click_url || '';

        const ctaPriText = document.getElementById('inpEditCtaPrimary');
        if (ctaPriText) ctaPriText.value = st.cta_primary_text || st.cta_text || qr.cta_text || '';

        const ctaPriUrl = document.getElementById('editCtaPrimaryUrl');
        if (ctaPriUrl) ctaPriUrl.value = st.cta_primary_url || st.cta_url || qr.cta_url || '';
        
        if (document.getElementById('inpEditCtaPrimaryBg')) document.getElementById('inpEditCtaPrimaryBg').value = st.cta_primary_bg || '#0f172a';
        if (document.getElementById('inpEditCtaPrimaryColor')) document.getElementById('inpEditCtaPrimaryColor').value = st.cta_primary_color || '#ffffff';
        if (document.getElementById('inpEditCtaPrimaryBorderColor')) document.getElementById('inpEditCtaPrimaryBorderColor').value = st.cta_primary_border_color || '#0f172a';
        if (document.getElementById('inpEditCtaPrimaryBorderWidth')) document.getElementById('inpEditCtaPrimaryBorderWidth').value = st.cta_primary_border_width || 'border-0';
        if (document.getElementById('inpEditCtaPrimaryShadow')) document.getElementById('inpEditCtaPrimaryShadow').value = st.cta_primary_shadow || '';

        const ctaSecText = document.getElementById('inpEditCtaSecondary');
        if (ctaSecText) ctaSecText.value = st.cta_secondary_text || 'GUÍA DE TALLAS TORZO';

        const ctaSecUrl = document.getElementById('editCtaSecondaryUrl');
        if (ctaSecUrl) ctaSecUrl.value = st.cta_secondary_url || '';
        
        if (document.getElementById('inpEditCtaSecondaryBg')) document.getElementById('inpEditCtaSecondaryBg').value = st.cta_secondary_bg || '#ffffff';
        if (document.getElementById('inpEditCtaSecondaryColor')) document.getElementById('inpEditCtaSecondaryColor').value = st.cta_secondary_color || '#0f172a';
        if (document.getElementById('inpEditCtaSecondaryBorderColor')) document.getElementById('inpEditCtaSecondaryBorderColor').value = st.cta_secondary_border_color || '#0f172a';
        if (document.getElementById('inpEditCtaSecondaryBorderWidth')) document.getElementById('inpEditCtaSecondaryBorderWidth').value = st.cta_secondary_border_width || 'border-0';
        if (document.getElementById('inpEditCtaSecondaryShadow')) document.getElementById('inpEditCtaSecondaryShadow').value = st.cta_secondary_shadow || '';

        // Estilos de Texto, Tipografías Independientes y Parallax Scrolling
        const editTitleColor = document.getElementById('inpEditTitleColor');
        const editTitleColorHex = document.getElementById('inpEditTitleColorHex');
        if (editTitleColor) editTitleColor.value = st.title_color || '#ffffff';
        if (editTitleColorHex) editTitleColorHex.value = st.title_color || '#ffffff';

        const editTitleFont = document.getElementById('inpEditTitleFont');
        if (editTitleFont) {
            editTitleFont.value = st.title_font || st.font_family || 'inherit';
            if (st.title_font && window.loadGoogleFont) window.loadGoogleFont(st.title_font);
        }

        const editSubtitleColor = document.getElementById('inpEditSubtitleColor');
        const editSubtitleColorHex = document.getElementById('inpEditSubtitleColorHex');
        if (editSubtitleColor) editSubtitleColor.value = st.subtitle_color || '#e2e8f0';
        if (editSubtitleColorHex) editSubtitleColorHex.value = st.subtitle_color || '#e2e8f0';

        const editSubtitleFont = document.getElementById('inpEditSubtitleFont');
        if (editSubtitleFont) {
            editSubtitleFont.value = st.subtitle_font || 'inherit';
            if (st.subtitle_font && window.loadGoogleFont) window.loadGoogleFont(st.subtitle_font);
        }

        const alignVal = st.text_align || 'text-center';
        const alignRadio = document.querySelector(`#editSectionModal input[name="settings[text_align]"][value="${alignVal}"]`);
        if (alignRadio) alignRadio.checked = true;

        const isParallaxBool = (st.parallax_scrolling === 1 || st.parallax_scrolling === '1' || st.parallax_scrolling === true || st.reveal_effect === 1 || st.reveal_effect === '1');
        const editParallax = document.getElementById('inpEditParallax');
        if (editParallax) {
            editParallax.checked = isParallaxBool;
        }
        const editCarouselParallax = document.getElementById('inpEditCarouselParallax');
        if (editCarouselParallax) {
            editCarouselParallax.checked = isParallaxBool;
        }

        const spTop = (st.spacing_top !== undefined && st.spacing_top !== '') ? parseInt(st.spacing_top, 10) : 0;
        const spBottom = (st.spacing_bottom !== undefined && st.spacing_bottom !== '') ? parseInt(st.spacing_bottom, 10) : 0;
        if (typeof window.setSectionSpacing === 'function') {
            window.setSectionSpacing('edit', 'top', spTop, false);
            window.setSectionSpacing('edit', 'bottom', spBottom, false);
        }

        window.openStoreModal('editSectionModal');
        if(typeof window.updatePreviewEdit === 'function') window.updatePreviewEdit();
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
        const editTypeSelect = document.getElementById('editSectionType');
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

// --- NEW CAROUSEL JS (FULL ANIMATED VIEWPORT + INTEGRATED SLIDES) ---
window.slideCounters = {
    create: 0,
    edit: 0
};

window._carouselPreviewState = {
    create: { activeIdx: 0 },
    edit: { activeIdx: 0 }
};

window._carouselPreviewTimers = {
    create: null,
    edit: null
};

window.focusCarouselSlidePreview = function(el, mode) {
    const slideItem = el ? el.closest('.slide-item') : null;
    if (!slideItem) return;
    const list = document.getElementById(mode + 'CarouselSlidesList');
    if (!list) return;
    const items = Array.from(list.querySelectorAll('.slide-item'));
    const idx = items.indexOf(slideItem);
    if (idx !== -1 && window._carouselPreviewState[mode]) {
        window._carouselPreviewState[mode].activeIdx = idx;
    }
};

window.handleSlideFileSelect = function(fileInput, mode) {
    const slideItem = fileInput ? fileInput.closest('.slide-item') : null;
    if (!slideItem) return;
    window.focusCarouselSlidePreview(fileInput, mode);

    const thumbWrap = slideItem.querySelector('.slide-img-thumb');
    const thumbImg = slideItem.querySelector('.slide-img-thumb img');
    const thumbLabel = slideItem.querySelector('.slide-img-thumb-label');

    if (fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];
        const blobUrl = URL.createObjectURL(file);
        slideItem.dataset.localPreviewSrc = blobUrl;

        if (thumbWrap && thumbImg) {
            thumbImg.src = blobUrl;
            if (thumbLabel) thumbLabel.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            thumbWrap.classList.remove('hidden');
            thumbWrap.style.display = 'flex';
        }

        if (mode === 'create' && typeof window.updatePreviewCreate === 'function') window.updatePreviewCreate();
        if (mode === 'edit' && typeof window.updatePreviewEdit === 'function') window.updatePreviewEdit();

        const reader = new FileReader();
        reader.onload = function(evt) {
            if (evt.target && evt.target.result) {
                slideItem.dataset.localPreviewSrc = evt.target.result;
                if (thumbImg) thumbImg.src = evt.target.result;
                if (mode === 'create' && typeof window.updatePreviewCreate === 'function') window.updatePreviewCreate();
                if (mode === 'edit' && typeof window.updatePreviewEdit === 'function') window.updatePreviewEdit();
            }
        };
        reader.readAsDataURL(file);
    } else {
        delete slideItem.dataset.localPreviewSrc;
        const urlInp = slideItem.querySelector('.slide-url');
        if (urlInp && urlInp.value && thumbWrap && thumbImg) {
            thumbImg.src = urlInp.value;
            if (thumbLabel) thumbLabel.textContent = urlInp.value;
            thumbWrap.classList.remove('hidden');
            thumbWrap.style.display = 'flex';
        } else if (thumbWrap) {
            thumbWrap.classList.add('hidden');
            thumbWrap.style.display = 'none';
        }
        if (mode === 'create' && typeof window.updatePreviewCreate === 'function') window.updatePreviewCreate();
        if (mode === 'edit' && typeof window.updatePreviewEdit === 'function') window.updatePreviewEdit();
    }
};

window.handleSlideUrlInput = function(urlInput, mode) {
    const slideItem = urlInput ? urlInput.closest('.slide-item') : null;
    if (!slideItem) return;
    window.focusCarouselSlidePreview(urlInput, mode);

    if (!slideItem.dataset.localPreviewSrc) {
        const thumbWrap = slideItem.querySelector('.slide-img-thumb');
        const thumbImg = slideItem.querySelector('.slide-img-thumb img');
        const thumbLabel = slideItem.querySelector('.slide-img-thumb-label');
        const val = (urlInput.value || '').trim();
        if (val && thumbWrap && thumbImg) {
            thumbImg.src = val;
            if (thumbLabel) thumbLabel.textContent = val;
            thumbWrap.classList.remove('hidden');
            thumbWrap.style.display = 'flex';
        } else if (thumbWrap) {
            thumbWrap.classList.add('hidden');
            thumbWrap.style.display = 'none';
        }
    }

    if (mode === 'create' && typeof window.updatePreviewCreate === 'function') window.updatePreviewCreate();
    if (mode === 'edit' && typeof window.updatePreviewEdit === 'function') window.updatePreviewEdit();
};

window.clearSlideImage = function(btn, mode) {
    const slideItem = btn ? btn.closest('.slide-item') : null;
    if (!slideItem) return;
    delete slideItem.dataset.localPreviewSrc;
    const fileInp = slideItem.querySelector('input[type="file"]');
    if (fileInp) fileInp.value = '';
    const urlInp = slideItem.querySelector('.slide-url');
    if (urlInp) urlInp.value = '';
    const thumbWrap = slideItem.querySelector('.slide-img-thumb');
    if (thumbWrap) {
        thumbWrap.classList.add('hidden');
        thumbWrap.style.display = 'none';
    }
    if (mode === 'create' && typeof window.updatePreviewCreate === 'function') window.updatePreviewCreate();
    if (mode === 'edit' && typeof window.updatePreviewEdit === 'function') window.updatePreviewEdit();
};

window.setCarouselPreviewSlide = function(mode, idx) {
    const areaId = mode === 'edit' ? 'editPreviewCarouselArea' : 'previewCarouselArea';
    const area = document.getElementById(areaId);
    if (!area) return;
    const track = area.querySelector('.carousel-live-track');
    const slides = area.querySelectorAll('.carousel-live-slide');
    if (!track || slides.length === 0) return;

    const total = slides.length;
    const prevIdx = (window._carouselPreviewState[mode] && window._carouselPreviewState[mode].activeIdx) || 0;
    const normalizedIdx = ((idx % total) + total) % total;
    window._carouselPreviewState[mode].activeIdx = normalizedIdx;

    const cap = mode === 'edit' ? 'Edit' : 'Create';
    const isReveal = document.getElementById('inp' + cap + 'CarouselParallax')?.checked;

    if (isReveal && slides.length > 1) {
        if (prevIdx !== normalizedIdx) {
            const currentSlide = slides[prevIdx];
            const targetSlide = slides[normalizedIdx];
            const isNext = normalizedIdx > prevIdx || (prevIdx === total - 1 && normalizedIdx === 0);

            targetSlide.style.zIndex = '1';
            targetSlide.style.visibility = 'visible';
            targetSlide.style.clipPath = 'none';

            currentSlide.style.zIndex = '3';
            currentSlide.style.visibility = 'visible';
            currentSlide.style.clipPath = 'inset(0 0 0 0)';
            void currentSlide.offsetHeight;

            currentSlide.style.transition = 'clip-path 0.6s cubic-bezier(0.25, 1, 0.5, 1)';
            currentSlide.style.clipPath = isNext ? 'inset(0 100% 0 0)' : 'inset(0 0 0 100%)';

            setTimeout(() => {
                targetSlide.style.zIndex = '2';
                currentSlide.style.zIndex = '0';
                currentSlide.style.visibility = 'hidden';
                currentSlide.style.clipPath = 'none';
                currentSlide.style.transition = 'none';
                slides.forEach((s, sI) => {
                    if (sI !== normalizedIdx) {
                        s.style.zIndex = '0';
                        s.style.visibility = 'hidden';
                    }
                });
            }, 620);
        }
    } else {
        track.style.transform = `translateX(-${normalizedIdx * 100}%)`;
    }

    area.querySelectorAll('.carousel-live-dot').forEach((dot, dIdx) => {
        if (dIdx === normalizedIdx) {
            dot.style.width = '22px';
            dot.style.backgroundColor = '#ffffff';
            dot.style.opacity = '1';
        } else {
            dot.style.width = '8px';
            dot.style.backgroundColor = 'rgba(255,255,255,0.5)';
            dot.style.opacity = '0.8';
        }
    });

    const counterBadge = area.querySelector('.carousel-live-counter');
    if (counterBadge) {
        counterBadge.textContent = `Slide ${normalizedIdx + 1} / ${total}`;
    }
};

window.stepCarouselPreviewSlide = function(mode, delta) {
    const cur = (window._carouselPreviewState[mode] && window._carouselPreviewState[mode].activeIdx) || 0;
    window.setCarouselPreviewSlide(mode, cur + delta);
};

window.syncCarouselLayoutSettings = function(mode) {
    const cap = mode === 'edit' ? 'Edit' : 'Create';
    const cFull = document.getElementById('inp' + cap + 'CarouselFullWidth');
    const hFull = document.getElementById('inp' + cap + 'FullWidth');
    const cFullH = document.getElementById('inp' + cap + 'CarouselFullWidthHorizontal');
    const hFullH = document.getElementById('inp' + cap + 'FullWidthHorizontal');

    if (cFull && hFull) hFull.checked = cFull.checked;
    if (cFullH && hFullH) hFullH.checked = cFullH.checked;

    const cHeightVal = document.getElementById('inp' + cap + 'CarouselHeightValue');
    const hHeightVal = document.getElementById('inp' + cap + 'HeroHeightValue');
    if (cHeightVal && hHeightVal) hHeightVal.value = cHeightVal.value;

    const cHeightUnit = document.getElementById('inp' + cap + 'CarouselHeightUnit');
    const hHeightUnit = document.getElementById('inp' + cap + 'HeroHeightUnit');
    if (cHeightUnit && hHeightUnit) hHeightUnit.value = cHeightUnit.value;

    const cParallax = document.getElementById('inp' + cap + 'CarouselParallax');
    const hParallax = document.getElementById('inp' + cap + 'Parallax');
    if (cParallax && hParallax) hParallax.checked = cParallax.checked;

    if (mode === 'create' && typeof window.updatePreviewCreate === 'function') window.updatePreviewCreate();
    if (mode === 'edit' && typeof window.updatePreviewEdit === 'function') window.updatePreviewEdit();
};

window.SLIDE_FOCAL_LABELS = {
    'left top': 'Izquierda Superior (0% 0%)',
    'center top': 'Centro Superior (50% 0%)',
    'right top': 'Derecha Superior (100% 0%)',
    'left center': 'Izquierda Centro (0% 50%)',
    'center center': 'Centro Centro (50% 50%)',
    'right center': 'Derecha Centro (100% 50%)',
    'left bottom': 'Izquierda Inferior (0% 100%)',
    'center bottom': 'Centro Inferior (50% 100%)',
    'right bottom': 'Derecha Inferior (100% 100%)'
};

window.setSlideFocalPoint = function(btn, focalValue, mode) {
    const slideItem = btn ? btn.closest('.slide-item') : null;
    if (!slideItem) return;
    window.focusCarouselSlidePreview(btn, mode);

    const hiddenInput = slideItem.querySelector('.slide-focal-input');
    if (hiddenInput) hiddenInput.value = focalValue;

    const labelEl = slideItem.querySelector('.slide-focal-label');
    if (labelEl) {
        labelEl.textContent = window.SLIDE_FOCAL_LABELS[focalValue] || 'Centro Centro (50% 50%)';
    }

    const matrix = slideItem.querySelector('.slide-focal-matrix');
    if (matrix) {
        matrix.querySelectorAll('.focal-cell').forEach(cell => {
            const isSelected = cell.getAttribute('data-focal') === focalValue;
            const dot = cell.querySelector('span');
            if (isSelected) {
                cell.className = 'focal-cell aspect-square flex items-center justify-center rounded-md bg-indigo-600 border border-indigo-700 ring-2 ring-indigo-300 text-white transition cursor-pointer shadow-xs';
                if (dot) dot.className = 'w-2.5 h-2.5 rounded-full bg-white ring-2 ring-indigo-400 pointer-events-none';
            } else {
                cell.className = 'focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer';
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-slate-300 pointer-events-none';
            }
        });
    }

    if (mode === 'create' && typeof window.updatePreviewCreate === 'function') window.updatePreviewCreate();
    if (mode === 'edit' && typeof window.updatePreviewEdit === 'function') window.updatePreviewEdit();
};

window.renderCarouselLivePreview = function(mode) {
    const areaId = mode === 'edit' ? 'editPreviewCarouselArea' : 'previewCarouselArea';
    const previewCarouselArea = document.getElementById(areaId);
    if (!previewCarouselArea) return;

    if (window._carouselPreviewTimers[mode]) {
        clearInterval(window._carouselPreviewTimers[mode]);
        window._carouselPreviewTimers[mode] = null;
    }

    const cap = mode === 'edit' ? 'Edit' : 'Create';
    const isFullPage = document.getElementById('inp' + cap + 'CarouselFullWidth')?.checked || document.getElementById('inp' + cap + 'FullWidth')?.checked || false;
    const isFullWidthH = document.getElementById('inp' + cap + 'CarouselFullWidthHorizontal')?.checked || document.getElementById('inp' + cap + 'FullWidthHorizontal')?.checked || false;
    const customHeightVal = (document.getElementById('inp' + cap + 'CarouselHeightValue')?.value || document.getElementById('inp' + cap + 'HeroHeightValue')?.value || '').trim();
    const customHeightUnit = document.getElementById('inp' + cap + 'CarouselHeightUnit')?.value || document.getElementById('inp' + cap + 'HeroHeightUnit')?.value || 'px';
    const isParallax = document.getElementById('inp' + cap + 'CarouselParallax')?.checked || document.getElementById('inp' + cap + 'Parallax')?.checked || false;

    let previewHeightCss = '320px';
    if (isFullPage) {
        previewHeightCss = '420px';
    } else if (customHeightVal !== '' && !isNaN(parseInt(customHeightVal, 10))) {
        const numH = parseInt(customHeightVal, 10);
        if (customHeightUnit === 'px') {
            previewHeightCss = Math.max(180, Math.min(520, numH)) + 'px';
        } else if (customHeightUnit === 'vh') {
            previewHeightCss = Math.max(180, Math.min(520, Math.round(numH * 4.2))) + 'px';
        }
    }

    const slides = document.querySelectorAll('#' + mode + 'CarouselSlidesList .slide-item');
    previewCarouselArea.innerHTML = '';
    previewCarouselArea.style.display = 'block';
    previewCarouselArea.style.position = 'relative';
    previewCarouselArea.style.width = '100%';
    previewCarouselArea.style.height = previewHeightCss;
    previewCarouselArea.style.minHeight = previewHeightCss;
    previewCarouselArea.style.borderRadius = isFullWidthH ? '0px' : '';
    previewCarouselArea.style.overflow = 'hidden';
    previewCarouselArea.style.backgroundColor = '#0f172a';
    previewCarouselArea.style.backgroundImage = 'none';

    if (slides.length === 0) {
        previewCarouselArea.innerHTML = `
            <div style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8; gap: 8px; padding: 24px; text-align: center;">
                <svg style="width: 36px; height: 36px; color: #64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <span style="font-size: 13px; font-weight: 700; color: #cbd5e1;">Sin slides en el carrusel</span>
                <span style="font-size: 11px; color: #64748b;">Haz clic en "+ Añadir Slide" para agregar imágenes y contenido.</span>
            </div>
        `;
        return;
    }

    function hexToRgba(hex, alpha) {
        let c = (hex || '#0f172a').replace('#', '').trim();
        if (c.length === 3) c = c.split('').map(x => x + x).join('');
        const num = parseInt(c, 16);
        if (isNaN(num)) return `rgba(15, 23, 42, ${alpha})`;
        return `rgba(${(num >> 16) & 255}, ${(num >> 8) & 255}, ${num & 255}, ${alpha})`;
    }

    if (!window._carouselPreviewState[mode]) {
        window._carouselPreviewState[mode] = { activeIdx: 0 };
    }
    if (window._carouselPreviewState[mode].activeIdx >= slides.length) {
        window._carouselPreviewState[mode].activeIdx = Math.max(0, slides.length - 1);
    }
    const activeIdx = window._carouselPreviewState[mode].activeIdx;

    const isReveal = isParallax;
    const track = document.createElement('div');
    track.className = 'carousel-live-track';
    if (isReveal) {
        track.style.cssText = 'position: relative; width: 100%; height: 100%; overflow: hidden;';
    } else {
        track.style.cssText = `display: flex; width: 100%; height: 100%; transition: transform 0.55s cubic-bezier(0.22, 1, 0.36, 1); transform: translateX(-${activeIdx * 100}%); will-change: transform;`;
    }

    slides.forEach((slide, idx) => {
        const uInp = slide.querySelector('.slide-url');
        const fileInp = slide.querySelector('input[type="file"]');
        let bgSrc = '';

        if (slide.dataset.localPreviewSrc) {
            bgSrc = slide.dataset.localPreviewSrc;
        } else if (fileInp && fileInp.files && fileInp.files[0]) {
            bgSrc = URL.createObjectURL(fileInp.files[0]);
            slide.dataset.localPreviewSrc = bgSrc;
        } else if (uInp && uInp.value && uInp.value.trim()) {
            bgSrc = uInp.value.trim();
        }

        const sFocal = slide.querySelector('input[name$="[image_focal_point]"]')?.value || 'center center';
        const sTitle = slide.querySelector('input[name$="[title]"]')?.value || '';
        const sSub = slide.querySelector('input[name$="[subtitle]"]')?.value || '';
        const sBadge = slide.querySelector('input[name$="[badge]"]')?.value || '';
        const sTitleColor = slide.querySelector('input[name$="[title_color]"]')?.value || '#ffffff';
        const sSubColor = slide.querySelector('input[name$="[subtitle_color]"]')?.value || '#e2e8f0';
        const sAlign = slide.querySelector('select[name$="[text_align]"]')?.value || 'text-center';

        const sGradEn = slide.querySelector('input[name$="[enable_gradient]"]')?.checked;
        const sGradStart = slide.querySelector('input[name$="[gradient_color_start]"]')?.value || '#0f172a';
        const sGradEnd = slide.querySelector('input[name$="[gradient_color_end]"]')?.value || '#0f172a';
        const sOpStart = parseInt(slide.querySelector('input[name$="[gradient_opacity_start]"]')?.value ?? '80', 10) / 100;
        const sOpEnd = parseInt(slide.querySelector('input[name$="[gradient_opacity_end]"]')?.value ?? '0', 10) / 100;
        const sGradDir = slide.querySelector('select[name$="[gradient_direction]"]')?.value || 'to bottom';

        const slideDiv = document.createElement('div');
        slideDiv.className = 'carousel-live-slide';
        if (isReveal) {
            slideDiv.style.cssText = `position: absolute; inset: 0; width: 100%; height: 100%; overflow: hidden; display: flex; flex-direction: column; justify-content: center; background-color: #0f172a; z-index: ${idx === activeIdx ? '2' : '0'}; visibility: ${idx === activeIdx ? 'visible' : 'hidden'};`;
        } else {
            slideDiv.style.cssText = 'flex: 0 0 100%; min-width: 100%; width: 100%; height: 100%; position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: center; background-color: #0f172a;';
        }

        if (bgSrc) {
            const imgEl = document.createElement('img');
            imgEl.src = bgSrc;
            imgEl.alt = slide.querySelector('input[name$="[alt_text]"]')?.value || ('Slide ' + (idx + 1));
            imgEl.style.cssText = `position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: ${sFocal}; z-index: 0; pointer-events: none; display: block;`;
            slideDiv.appendChild(imgEl);
        } else {
            const emptyBg = document.createElement('div');
            emptyBg.style.cssText = 'position: absolute; inset: 0; z-index: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%); color: #475569;';
            emptyBg.innerHTML = `<svg style="width: 32px; height: 32px; opacity: 0.45; margin-bottom: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><span style="font-size: 10px; font-weight: 600; color: #64748b;">Slide ${idx + 1}: Sube o selecciona una imagen</span>`;
            slideDiv.appendChild(emptyBg);
        }

        if (sGradEn) {
            const overlay = document.createElement('div');
            overlay.style.cssText = `position: absolute; inset: 0; z-index: 1; pointer-events: none; background: linear-gradient(${sGradDir}, ${hexToRgba(sGradStart, isNaN(sOpStart) ? 0.8 : sOpStart)}, ${hexToRgba(sGradEnd, isNaN(sOpEnd) ? 0 : sOpEnd)});`;
            slideDiv.appendChild(overlay);
        }

        let alignItems = 'center';
        let textAlign = 'center';
        if (sAlign === 'text-left') {
            alignItems = 'flex-start';
            textAlign = 'left';
        } else if (sAlign === 'text-right') {
            alignItems = 'flex-end';
            textAlign = 'right';
        }

        const contentDiv = document.createElement('div');
        contentDiv.style.cssText = `position: relative; z-index: 2; padding: 24px 44px; width: 100%; display: flex; flex-direction: column; align-items: ${alignItems}; text-align: ${textAlign}; pointer-events: none;`;

        if (sBadge) {
            const badgeEl = document.createElement('div');
            badgeEl.style.cssText = 'display: inline-block; background: rgba(79, 70, 229, 0.9); color: #ffffff; font-size: 10px; padding: 3px 10px; border-radius: 9999px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;';
            badgeEl.textContent = sBadge;
            contentDiv.appendChild(badgeEl);
        }
        if (sTitle) {
            const titleEl = document.createElement('h2');
            titleEl.style.cssText = `font-size: 24px; line-height: 1.2; font-weight: 800; color: ${sTitleColor}; text-shadow: 0 2px 10px rgba(0,0,0,0.35); margin: 0; max-width: 540px;`;
            titleEl.textContent = sTitle;
            contentDiv.appendChild(titleEl);
        }
        if (sSub) {
            const subEl = document.createElement('p');
            subEl.style.cssText = `font-size: 13px; line-height: 1.45; color: ${sSubColor}; margin-top: 8px; max-width: 460px; text-shadow: 0 1px 4px rgba(0,0,0,0.3);`;
            subEl.textContent = sSub;
            contentDiv.appendChild(subEl);
        }

        const sBtnText = slide.querySelector('input[name$="[button_text]"]')?.value || '';
        const sBtnBg = slide.querySelector('input[name$="[button_bg]"]')?.value || '#0f172a';
        const sBtnColor = slide.querySelector('input[name$="[button_color]"]')?.value || '#ffffff';
        const sBtnBorderColor = slide.querySelector('input[name$="[button_border_color]"]')?.value || '#0f172a';
        const sBtnBorderWidth = slide.querySelector('select[name$="[button_border_width]"]')?.value || 'border-0';
        const sBtnShadow = slide.querySelector('select[name$="[button_shadow]"]')?.value || '';

        if (sBtnText) {
            let borderPx = '0px';
            if (sBtnBorderWidth === 'border') borderPx = '1px';
            else if (sBtnBorderWidth === 'border-2') borderPx = '2px';
            else if (sBtnBorderWidth === 'border-4') borderPx = '4px';

            let shadowCss = 'none';
            if (sBtnShadow === 'shadow-sm') shadowCss = '0 1px 3px rgba(0,0,0,0.25)';
            else if (sBtnShadow === 'shadow-md') shadowCss = '0 4px 8px rgba(0,0,0,0.3)';
            else if (sBtnShadow === 'shadow-lg') shadowCss = '0 10px 15px rgba(0,0,0,0.35)';
            else if (sBtnShadow === 'shadow-xl') shadowCss = '0 20px 25px rgba(0,0,0,0.4)';

            const btnEl = document.createElement('span');
            btnEl.style.cssText = `display: inline-block; margin-top: 14px; padding: 8px 18px; border-radius: 8px; font-weight: 700; font-size: 12px; background-color: ${sBtnBg}; color: ${sBtnColor}; border: ${borderPx} solid ${sBtnBorderColor}; box-shadow: ${shadowCss};`;
            btnEl.textContent = sBtnText;
            contentDiv.appendChild(btnEl);
        }

        slideDiv.appendChild(contentDiv);
        track.appendChild(slideDiv);
    });

    previewCarouselArea.appendChild(track);

    // Top-right badge: Slide counter + Autoplay state + Layout badges
    const autoPlayCheck = document.getElementById(mode + 'CarouselAutoPlay');
    const autoPlayTimeInp = document.getElementById(mode + 'CarouselAutoplayTime');
    const isAutoplay = autoPlayCheck ? autoPlayCheck.checked : true;
    const autoplaySec = Math.max(1, parseInt(autoPlayTimeInp ? autoPlayTimeInp.value : '5', 10) || 5);

    const statusWrap = document.createElement('div');
    statusWrap.style.cssText = 'position: absolute; top: 10px; right: 12px; z-index: 20; display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 5px; pointer-events: none;';
    statusWrap.innerHTML = `
        <span class="carousel-live-counter" style="background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); color: #ffffff; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 9999px; border: 1px solid rgba(255,255,255,0.15);">Slide ${activeIdx + 1} / ${slides.length}</span>
        ${isAutoplay && slides.length > 1 ? `<span style="background: rgba(79, 70, 229, 0.85); color: #ffffff; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 9999px;">Autoplay ${autoplaySec}s</span>` : ''}
        ${isFullPage ? `<span style="background: rgba(16, 185, 129, 0.85); color: #ffffff; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 9999px;">Full Page</span>` : ''}
        ${isFullWidthH ? `<span style="background: rgba(14, 165, 233, 0.85); color: #ffffff; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 9999px;">100% Ancho</span>` : ''}
        ${isParallax ? `<span style="background: rgba(147, 51, 234, 0.85); color: #ffffff; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 9999px;">Cortina Revelado</span>` : ''}
    `;
    previewCarouselArea.appendChild(statusWrap);

    // Navigation Arrows & Pagination Dots
    if (slides.length > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.title = 'Slide anterior';
        prevBtn.style.cssText = 'position: absolute; left: 10px; top: 50%; transform: translateY(-50%); z-index: 20; width: 32px; height: 32px; border-radius: 9999px; background: rgba(15, 23, 42, 0.65); color: #ffffff; border: 1px solid rgba(255,255,255,0.25); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;';
        prevBtn.innerHTML = '<svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>';
        prevBtn.onclick = function(e) {
            e.preventDefault();
            window.stepCarouselPreviewSlide(mode, -1);
        };
        previewCarouselArea.appendChild(prevBtn);

        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.title = 'Siguiente slide';
        nextBtn.style.cssText = 'position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 20; width: 32px; height: 32px; border-radius: 9999px; background: rgba(15, 23, 42, 0.65); color: #ffffff; border: 1px solid rgba(255,255,255,0.25); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;';
        nextBtn.innerHTML = '<svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>';
        nextBtn.onclick = function(e) {
            e.preventDefault();
            window.stepCarouselPreviewSlide(mode, 1);
        };
        previewCarouselArea.appendChild(nextBtn);
    }

    const dotsWrap = document.createElement('div');
    dotsWrap.style.cssText = 'position: absolute; bottom: 12px; left: 50%; transform: translateX(-50%); z-index: 20; display: flex; align-items: center; gap: 6px; background: rgba(15, 23, 42, 0.45); padding: 4px 10px; border-radius: 9999px; backdrop-filter: blur(4px);';
    slides.forEach((_, idx) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'carousel-live-dot';
        const isCurrent = idx === activeIdx;
        dot.style.cssText = `height: 8px; width: ${isCurrent ? '22px' : '8px'}; border-radius: 9999px; background-color: ${isCurrent ? '#ffffff' : 'rgba(255,255,255,0.5)'}; border: none; cursor: pointer; transition: all 0.3s ease; padding: 0;`;
        dot.onclick = function(e) {
            e.preventDefault();
            window.setCarouselPreviewSlide(mode, idx);
        };
        dotsWrap.appendChild(dot);
    });
    previewCarouselArea.appendChild(dotsWrap);

    if (isAutoplay && slides.length > 1) {
        window._carouselPreviewTimers[mode] = setInterval(() => {
            const modalEl = document.getElementById(mode === 'edit' ? 'editSectionModal' : 'createSectionModal');
            if (!modalEl || modalEl.classList.contains('hidden')) {
                clearInterval(window._carouselPreviewTimers[mode]);
                window._carouselPreviewTimers[mode] = null;
                return;
            }
            window.stepCarouselPreviewSlide(mode, 1);
        }, autoplaySec * 1000);
    }
};

window.addCarouselSlide = function(mode, data = null) {
    const list = document.getElementById(mode + 'CarouselSlidesList');
    if (!list) return;

    const index = window.slideCounters[mode]++;
    const prefix = `settings[carousel_slides][${index}]`;
    const filePrefix = `carousel_slide_files[${index}]`;
    const updateFn = `window.updatePreview${mode.charAt(0).toUpperCase() + mode.slice(1)}`;
    const hasInitialImg = data && data.image_url && String(data.image_url).trim() !== '';
    const initialImgUrl = hasInitialImg ? String(data.image_url).trim() : '';
    const focalVal = data && data.image_focal_point ? String(data.image_focal_point).trim() : 'center center';
    const focalLabel = (window.SLIDE_FOCAL_LABELS && window.SLIDE_FOCAL_LABELS[focalVal]) ? window.SLIDE_FOCAL_LABELS[focalVal] : 'Centro Centro (50% 50%)';
    const titleColorVal = data && data.title_color ? data.title_color : '#ffffff';
    const subColorVal = data && data.subtitle_color ? data.subtitle_color : '#e2e8f0';
    const textAlignVal = data && data.text_align ? data.text_align : 'text-center';

    const focalPositions = [
        'left top', 'center top', 'right top',
        'left center', 'center center', 'right center',
        'left bottom', 'center bottom', 'right bottom'
    ];
    const focalCellsHtml = focalPositions.map(pos => {
        const isSel = pos === focalVal;
        const btnCls = isSel
            ? 'focal-cell aspect-square flex items-center justify-center rounded-md bg-indigo-600 border border-indigo-700 ring-2 ring-indigo-300 text-white transition cursor-pointer shadow-xs'
            : 'focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer';
        const dotCls = isSel
            ? 'w-2.5 h-2.5 rounded-full bg-white ring-2 ring-indigo-400 pointer-events-none'
            : 'w-2 h-2 rounded-full bg-slate-300 pointer-events-none';
        const titleTxt = (window.SLIDE_FOCAL_LABELS && window.SLIDE_FOCAL_LABELS[pos]) || pos;
        return `<button type="button" data-focal="${pos}" onclick="event.stopPropagation(); window.setSlideFocalPoint(this, '${pos}', '${mode}')" title="${titleTxt}" class="${btnCls}"><span class="${dotCls}"></span></button>`;
    }).join('');

    const html = `
        <div class="p-3.5 bg-white border border-slate-200 rounded-xl space-y-3 relative slide-item shadow-2xs" onclick="window.focusCarouselSlidePreview(this, '${mode}')">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 bg-indigo-600 text-white rounded text-[10px] font-extrabold uppercase">Slide ${index + 1}</span>
                    <button type="button" class="text-[10px] text-indigo-600 hover:underline font-semibold cursor-pointer" onclick="event.stopPropagation(); window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}();">Ver en Preview</button>
                </div>
                <button type="button" class="text-rose-500 hover:text-rose-700 text-xs font-bold cursor-pointer" onclick="event.stopPropagation(); this.closest('.slide-item').remove(); ${updateFn} && ${updateFn}()">Eliminar</button>
            </div>
            
            <div class="space-y-2.5">
                <!-- Imagen del Slide + Matriz de Punto Focal -->
                <div class="p-2.5 bg-slate-50 border border-slate-200/80 rounded-lg space-y-2.5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Imagen del Slide (Subir Archivo)</label>
                        <input type="file" name="${filePrefix}" accept="image/*" class="w-full text-xs text-slate-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" onchange="window.handleSlideFileSelect(this, '${mode}')">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-semibold text-slate-600">O URL / Gestor de Medios</label>
                            <button type="button" class="text-[10px] text-indigo-600 font-bold hover:underline cursor-pointer" onclick="window.openCmsMediaModal(this.parentElement.nextElementSibling)">Seleccionar del Gestor</button>
                        </div>
                        <input type="text" name="${prefix}[image_url]" class="w-full px-2 py-1 bg-white border border-slate-200 rounded text-xs slide-url" placeholder="/uploads/sections/... o https://..." oninput="window.handleSlideUrlInput(this, '${mode}')" value="${initialImgUrl}">
                    </div>
                    <!-- Miniatura de Confirmación de Imagen Cargada -->
                    <div class="slide-img-thumb ${hasInitialImg ? 'flex' : 'hidden'} items-center justify-between gap-2 p-1.5 bg-white border border-indigo-200 rounded-lg" style="${hasInitialImg ? 'display: flex;' : 'display: none;'}">
                        <div class="flex items-center gap-2 min-w-0">
                            <img src="${initialImgUrl}" alt="Preview" class="w-12 h-8 object-cover rounded border border-slate-200 flex-shrink-0 bg-slate-900">
                            <span class="slide-img-thumb-label text-[10px] text-slate-600 font-mono truncate">${initialImgUrl || 'Imagen seleccionada'}</span>
                        </div>
                        <button type="button" class="text-[10px] text-rose-600 font-bold hover:underline px-1.5 flex-shrink-0 cursor-pointer" onclick="window.clearSlideImage(this, '${mode}')">Quitar</button>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Texto Alternativo (Alt SEO)</label>
                        <input type="text" name="${prefix}[alt_text]" class="w-full px-2 py-1 bg-white border border-slate-200 rounded text-xs" placeholder="Descripción de la imagen para SEO" oninput="${updateFn} && ${updateFn}()" value="${data ? (data.alt_text || '') : ''}">
                    </div>
                    <!-- MATRIZ 3x3 DE PUNTO FOCAL DEL SLIDE -->
                    <div class="pt-1 border-t border-slate-200/80">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[11px] font-semibold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h2m14 0h2M12 3v2m0 14v2"/></svg>
                                Matriz de Punto Focal de la Imagen
                            </label>
                            <span class="slide-focal-label text-[10px] font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">${focalLabel}</span>
                        </div>
                        <input type="hidden" name="${prefix}[image_focal_point]" class="slide-focal-input" value="${focalVal}">
                        <div class="p-2 bg-white border border-slate-200 rounded-xl flex items-center gap-3">
                            <div class="slide-focal-matrix w-20 h-20 p-1 bg-slate-200/90 border border-slate-300 rounded-xl grid grid-cols-3 gap-1 shadow-inner select-none shrink-0" role="radiogroup" aria-label="Punto focal de la imagen del slide">
                                ${focalCellsHtml}
                            </div>
                            <div class="text-[10px] text-slate-500 leading-snug">
                                <span class="font-bold text-slate-700 block text-[11px]">Foco Visual (9 Celdas)</span>
                                Selecciona el cuadrante clave de este slide para mantener el encuadre óptimo en móvil y escritorio.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenido y Estilos de Texto Integrados en el Slide -->
                <div class="p-2.5 bg-slate-50 border border-slate-200/80 rounded-lg space-y-2">
                    <span class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Contenido y Textos del Slide</span>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Título del Slide</label>
                            <input type="text" name="${prefix}[title]" class="w-full px-2 py-1 bg-white border border-slate-200 rounded text-xs font-semibold" placeholder="Ej: Nueva Colección Verano" oninput="window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()" value="${data ? (data.title || '') : ''}">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Subtítulo / Bajada</label>
                            <input type="text" name="${prefix}[subtitle]" class="w-full px-2 py-1 bg-white border border-slate-200 rounded text-xs" placeholder="Subtítulo opcional" oninput="window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()" value="${data ? (data.subtitle || '') : ''}">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Badge Superior</label>
                            <input type="text" name="${prefix}[badge]" class="w-full px-2 py-1 bg-white border border-slate-200 rounded text-xs" placeholder="Ej: NUEVO" oninput="window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()" value="${data ? (data.badge || '') : ''}">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 pt-1">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1">Color Título</label>
                            <input type="color" name="${prefix}[title_color]" value="${titleColorVal}" class="w-full h-6 p-0 border border-slate-200 rounded cursor-pointer" oninput="window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1">Color Subtítulo</label>
                            <input type="color" name="${prefix}[subtitle_color]" value="${subColorVal}" class="w-full h-6 p-0 border border-slate-200 rounded cursor-pointer" oninput="window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1">Alineación</label>
                            <select name="${prefix}[text_align]" class="w-full px-1.5 py-1 bg-white border border-slate-200 rounded text-[10px] font-semibold" onchange="window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()">
                                <option value="text-left" ${textAlignVal === 'text-left' ? 'selected' : ''}>Izquierda</option>
                                <option value="text-center" ${textAlignVal === 'text-center' ? 'selected' : ''}>Centro</option>
                                <option value="text-right" ${textAlignVal === 'text-right' ? 'selected' : ''}>Derecha</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Overlay Degradado del Slide -->
                <div class="p-2.5 bg-slate-50 border border-slate-200/80 rounded-lg space-y-2 slide-gradient-controls">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="${prefix}[enable_gradient]" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5" ${data && data.enable_gradient ? 'checked' : ''} onchange="this.closest('.slide-gradient-controls').querySelector('.grad-settings').style.display = this.checked ? 'block' : 'none'; window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()">
                        <span class="font-bold text-slate-800 text-[11px]">Habilitar Overlay Degradado en este Slide</span>
                    </label>
                    <div class="grad-settings space-y-2 pt-2 border-t border-slate-200" style="${data && data.enable_gradient ? 'display: block;' : 'display: none;'}">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[9px] font-semibold text-slate-500 mb-1">Color & Op. Inicio</label>
                                <div class="flex items-center gap-1 mb-1">
                                    <input type="color" class="w-5 h-5 p-0 border border-slate-200 rounded cursor-pointer" oninput="this.nextElementSibling.value = this.value; window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()" value="${data ? (data.gradient_color_start || '#0f172a') : '#0f172a'}">
                                    <input type="text" name="${prefix}[gradient_color_start]" class="w-full px-1 py-0.5 text-[9px] font-mono bg-white border border-slate-200 rounded" oninput="this.previousElementSibling.value = this.value; window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()" value="${data ? (data.gradient_color_start || '#0f172a') : '#0f172a'}">
                                </div>
                                <input type="range" name="${prefix}[gradient_opacity_start]" min="0" max="100" class="w-full" oninput="window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()" value="${data ? (data.gradient_opacity_start !== undefined ? data.gradient_opacity_start : '80') : '80'}">
                            </div>
                            <div>
                                <label class="block text-[9px] font-semibold text-slate-500 mb-1">Color & Op. Fin</label>
                                <div class="flex items-center gap-1 mb-1">
                                    <input type="color" class="w-5 h-5 p-0 border border-slate-200 rounded cursor-pointer" oninput="this.nextElementSibling.value = this.value; window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()" value="${data ? (data.gradient_color_end || '#0f172a') : '#0f172a'}">
                                    <input type="text" name="${prefix}[gradient_color_end]" class="w-full px-1 py-0.5 text-[9px] font-mono bg-white border border-slate-200 rounded" oninput="this.previousElementSibling.value = this.value; window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()" value="${data ? (data.gradient_color_end || '#0f172a') : '#0f172a'}">
                                </div>
                                <input type="range" name="${prefix}[gradient_opacity_end]" min="0" max="100" class="w-full" oninput="window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()" value="${data ? (data.gradient_opacity_end !== undefined ? data.gradient_opacity_end : '20') : '20'}">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[9px] font-semibold text-slate-500 mb-1">Dirección</label>
                            <select name="${prefix}[gradient_direction]" class="w-full px-1.5 py-1 bg-white border border-slate-200 rounded text-[10px]" onchange="window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()">
                                <option value="to bottom" ${data && data.gradient_direction === 'to bottom' ? 'selected' : ''}>Arriba hacia Abajo</option>
                                <option value="to top" ${data && data.gradient_direction === 'to top' ? 'selected' : ''}>Abajo hacia Arriba</option>
                                <option value="to right" ${data && data.gradient_direction === 'to right' ? 'selected' : ''}>Izquierda a Derecha</option>
                                <option value="to left" ${data && data.gradient_direction === 'to left' ? 'selected' : ''}>Derecha a Izquierda</option>
                                <option value="to bottom right" ${data && data.gradient_direction === 'to bottom right' ? 'selected' : ''}>Diagonal</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Botón y Enlace del Slide -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Texto Botón (Opcional)</label>
                        <input type="text" name="${prefix}[button_text]" class="w-full px-2 py-1 border border-slate-200 rounded text-xs" placeholder="Ej: Ver Colección" oninput="window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()" value="${data ? (data.button_text || '') : ''}">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Comportamiento del Enlace</label>
                        <select name="${prefix}[link_target]" class="w-full px-2 py-1 border border-slate-200 rounded text-xs bg-white" onchange="${updateFn} && ${updateFn}()">
                            <option value="button" ${data && data.link_target === 'button' ? 'selected' : ''}>Solo en el Botón</option>
                            <option value="image" ${data && data.link_target === 'image' ? 'selected' : ''}>En toda la Imagen</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">URL Enlace</label>
                        <div class="flex items-center gap-1.5">
                            <input type="text" name="${prefix}[button_url]" id="slideUrl_${mode}_${index}" class="flex-1 min-w-0 px-2.5 py-1 border border-slate-200 rounded text-xs font-mono" placeholder="Ej: /catalogo o #section-2" oninput="${updateFn} && ${updateFn}()" value="${data ? (data.button_url || '') : ''}">
                            <select onchange="if(this.value){ document.getElementById('slideUrl_${mode}_${index}').value = this.value; this.value=''; ${updateFn} && ${updateFn}(); }" class="w-32 shrink-0 px-2 py-1 border border-slate-200 rounded text-[10px] text-slate-600 font-semibold bg-white cursor-pointer" title="Seleccionar una ruta">
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
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Fondo Botón</label>
                        <input type="color" name="${prefix}[button_bg]" value="${data ? (data.button_bg || '#0f172a') : '#0f172a'}" class="w-full h-6 p-0 border border-slate-200 rounded cursor-pointer" oninput="window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Texto Botón</label>
                        <input type="color" name="${prefix}[button_color]" value="${data ? (data.button_color || '#ffffff') : '#ffffff'}" class="w-full h-6 p-0 border border-slate-200 rounded cursor-pointer" oninput="window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Color Borde</label>
                        <input type="color" name="${prefix}[button_border_color]" value="${data ? (data.button_border_color || '#0f172a') : '#0f172a'}" class="w-full h-6 p-0 border border-slate-200 rounded cursor-pointer" oninput="window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Grosor Borde</label>
                        <select name="${prefix}[button_border_width]" class="w-full px-1 py-1 border border-slate-200 rounded text-[10px]" onchange="window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()">
                            <option value="border-0" ${data && data.button_border_width === 'border-0' ? 'selected' : ''}>Sin Borde (0px)</option>
                            <option value="border" ${data && data.button_border_width === 'border' ? 'selected' : ''}>Fino (1px)</option>
                            <option value="border-2" ${data && data.button_border_width === 'border-2' ? 'selected' : ''}>Medio (2px)</option>
                            <option value="border-4" ${data && data.button_border_width === 'border-4' ? 'selected' : ''}>Grueso (4px)</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Sombra Botón</label>
                        <select name="${prefix}[button_shadow]" class="w-full px-1 py-1 border border-slate-200 rounded text-[10px]" onchange="window.focusCarouselSlidePreview(this, '${mode}'); ${updateFn} && ${updateFn}()">
                            <option value="" ${!data || !data.button_shadow ? 'selected' : ''}>Sin Sombra</option>
                            <option value="shadow-sm" ${data && data.button_shadow === 'shadow-sm' ? 'selected' : ''}>Pequeña</option>
                            <option value="shadow-md" ${data && data.button_shadow === 'shadow-md' ? 'selected' : ''}>Media</option>
                            <option value="shadow-lg" ${data && data.button_shadow === 'shadow-lg' ? 'selected' : ''}>Grande</option>
                            <option value="shadow-xl" ${data && data.button_shadow === 'shadow-xl' ? 'selected' : ''}>Extra Grande</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    `;

    list.insertAdjacentHTML('beforeend', html);
    const totalNow = list.querySelectorAll('.slide-item').length;
    if (window._carouselPreviewState[mode]) {
        window._carouselPreviewState[mode].activeIdx = Math.max(0, totalNow - 1);
    }
    window.updatePreviewCreate && window.updatePreviewCreate();
    window.updatePreviewEdit && window.updatePreviewEdit();
};

window.toggleHeroBgType = function(type, mode) {
    const imgWrap = document.getElementById(mode + 'HeroBgImageWrap');
    const vidWrap = document.getElementById(mode + 'HeroBgVideoWrap');
    if (type === 'image') {
        if(imgWrap) imgWrap.classList.remove('hidden');
        if(vidWrap) vidWrap.classList.add('hidden');
    } else {
        if(imgWrap) imgWrap.classList.add('hidden');
        if(vidWrap) vidWrap.classList.remove('hidden');
    }
};

window.toggleHeroButtonsMode = function(mode) {
    const hasButtonsInp = document.getElementById(mode === 'create' ? 'inpCreateHeroHasButtons' : 'inpEditHeroHasButtons');
    const controls = document.getElementById(mode === 'create' ? 'createHeroButtonsControls' : 'editHeroButtonsControls');
    const fullClickWrap = document.getElementById(mode === 'create' ? 'createHeroFullClickWrap' : 'editHeroFullClickWrap');
    if (!hasButtonsInp || !controls || !fullClickWrap) return;
    const isChecked = hasButtonsInp.checked;
    controls.style.display = isChecked ? 'block' : 'none';
    fullClickWrap.style.display = isChecked ? 'none' : 'block';
};

window.setHeroFocalPoint = function(mode, focalVal, triggerPreview = true) {
    if (!focalVal) focalVal = 'center center';
    const input = document.getElementById(mode === 'create' ? 'inpCreateHeroFocalPoint' : 'inpEditHeroFocalPoint');
    if (input) input.value = focalVal;

    const labelMap = {
        'left top': 'Izquierda Superior (0% 0%)',
        'center top': 'Centro Superior (50% 0%)',
        'right top': 'Derecha Superior (100% 0%)',
        'left center': 'Izquierda al Centro (0% 50%)',
        'center center': 'Centro Centro (50% 50%)',
        'right center': 'Derecha al Centro (100% 50%)',
        'left bottom': 'Izquierda Inferior (0% 100%)',
        'center bottom': 'Centro Inferior (50% 100%)',
        'right bottom': 'Derecha Inferior (100% 100%)'
    };

    const labelEl = document.getElementById(mode + 'HeroFocalPointLabel');
    if (labelEl) {
        labelEl.textContent = labelMap[focalVal] || focalVal;
    }

    const matrix = document.getElementById(mode + 'HeroFocalMatrix');
    if (matrix) {
        matrix.querySelectorAll('button').forEach(btn => {
            const isMatch = btn.getAttribute('data-focal') === focalVal;
            if (isMatch) {
                btn.className = 'focal-cell aspect-square flex items-center justify-center rounded-md bg-indigo-600 border border-indigo-700 ring-2 ring-indigo-300 text-white transition cursor-pointer shadow-xs';
                btn.innerHTML = '<span class="w-2.5 h-2.5 rounded-full bg-white ring-2 ring-indigo-400 pointer-events-none"></span>';
                btn.setAttribute('aria-checked', 'true');
            } else {
                btn.className = 'focal-cell aspect-square flex items-center justify-center rounded-md bg-white border border-slate-200 hover:bg-indigo-50 transition cursor-pointer';
                btn.innerHTML = '<span class="w-2 h-2 rounded-full bg-slate-300 pointer-events-none"></span>';
                btn.setAttribute('aria-checked', 'false');
            }
        });
    }

    if (triggerPreview) {
        if (mode === 'create' && typeof window.updatePreviewCreate === 'function') {
            window.updatePreviewCreate();
        } else if (mode === 'edit' && typeof window.updatePreviewEdit === 'function') {
            window.updatePreviewEdit();
        }
    }
};

window.setSectionSpacing = function(mode, direction, val, triggerPreview = true) {
    const num = Math.max(0, parseInt(val, 10) || 0);
    const prefix = mode === 'edit' ? 'Edit' : 'Create';
    const capitalizedDir = direction === 'top' ? 'Top' : 'Bottom';
    
    const inp = document.getElementById('inp' + prefix + 'Spacing' + capitalizedDir);
    const range = document.getElementById('range' + prefix + 'Spacing' + capitalizedDir);
    
    if (inp) inp.value = num;
    if (range) range.value = Math.min(120, num);
    
    if (triggerPreview) {
        if (mode === 'edit' && typeof window.updatePreviewEdit === 'function') {
            window.updatePreviewEdit();
        } else if (mode === 'create' && typeof window.updatePreviewCreate === 'function') {
            window.updatePreviewCreate();
        }
    }
};

// --- POP-UP CREATOR & LIVE PREVIEW JS (HUB 2 / HUB 7 / HUB 3) ---
window._currentPopupPreviewBg_create = '';
window._currentPopupPreviewBg_edit = '';

window.handlePopupActionChange = function(mode) {
    const actionEl = document.getElementById(mode + 'PopupAction');
    const action = actionEl ? actionEl.value : 'ally_discount';
    
    const allyBlock = document.getElementById(mode + 'PopupAllyBlock');
    const storeDiscBlock = document.getElementById(mode + 'PopupStoreDiscountBlock');
    const presaleBlock = document.getElementById(mode + 'PopupPresaleBlock');
    const customImageBlock = document.getElementById(mode + 'PopupCustomImageBlock');
    const ctaTextInp = document.getElementById(mode + 'PopupCtaText');
    
    if (allyBlock) allyBlock.style.display = (action === 'ally_discount') ? 'block' : 'none';
    if (storeDiscBlock) storeDiscBlock.style.display = (action === 'store_discount') ? 'block' : 'none';
    if (presaleBlock) presaleBlock.style.display = (action === 'presale') ? 'block' : 'none';
    
    if (action === 'ally_discount') {
        const allyImgSource = document.querySelector(`#${mode}SectionModal input[name="settings[popup_ally_image_source]"]:checked`);
        const isCustomImg = allyImgSource && allyImgSource.value === 'custom';
        if (customImageBlock) customImageBlock.style.display = isCustomImg ? 'block' : 'none';
        if (ctaTextInp && !ctaTextInp.value) ctaTextInp.placeholder = 'Obtener Descuento de Aliado';
    } else {
        if (customImageBlock) customImageBlock.style.display = 'block';
        if (action === 'newsletter' && ctaTextInp && !ctaTextInp.value) ctaTextInp.placeholder = 'Suscribirme al Newsletter';
        if (action === 'store_discount' && ctaTextInp && !ctaTextInp.value) ctaTextInp.placeholder = 'Obtener mi Cupón de Descuento';
        if (action === 'presale' && ctaTextInp && !ctaTextInp.value) ctaTextInp.placeholder = 'Acceder a la Preventa';
    }
    
    window.renderPopUpLivePreview(mode);
};

window.handlePopupAllyCampaignChange = function(mode) {
    const campSel = document.getElementById(mode + 'PopupAllyCampaignId');
    if (!campSel) return;
    const opt = campSel.options[campSel.selectedIndex];
    if (opt && opt.dataset) {
        const titleInp = document.getElementById(mode === 'create' ? 'inpCreateTitle' : 'inpEditTitle');
        const subtitleInp = document.getElementById(mode === 'create' ? 'inpCreateSubtitle' : 'inpEditSubtitle');
        const badgeInp = document.getElementById(mode === 'create' ? 'inpCreateBadge' : 'inpEditBadge');
        if (titleInp && (!titleInp.value || titleInp.dataset.autofilled === '1')) {
            titleInp.value = opt.dataset.title || '';
            titleInp.dataset.autofilled = '1';
        }
        if (subtitleInp && (!subtitleInp.value || subtitleInp.dataset.autofilled === '1')) {
            subtitleInp.value = opt.dataset.benefit || '';
            subtitleInp.dataset.autofilled = '1';
        }
        if (badgeInp && (!badgeInp.value || badgeInp.dataset.autofilled === '1')) {
            badgeInp.value = 'ALIANZA ' + (opt.dataset.ally || 'OFICIAL').toUpperCase();
            badgeInp.dataset.autofilled = '1';
        }
    }
    window.renderPopUpLivePreview(mode);
};

window.handlePopupImageSourceToggle = function(mode) {
    const radio = document.querySelector(`#${mode}SectionModal input[name="settings[popup_ally_image_source]"]:checked`);
    const isCustom = radio && radio.value === 'custom';
    const customImageBlock = document.getElementById(mode + 'PopupCustomImageBlock');
    if (customImageBlock) customImageBlock.style.display = isCustom ? 'block' : 'none';
    window.renderPopUpLivePreview(mode);
};

window.setPopupFocalPoint = function(mode, point, btn) {
    const hiddenInp = document.getElementById(mode === 'create' ? 'inpCreatePopupFocalPoint' : 'inpEditPopupFocalPoint');
    if (hiddenInp) hiddenInp.value = point;
    const matrixEl = document.getElementById(mode + 'PopupFocalMatrix');
    if (matrixEl) {
        matrixEl.querySelectorAll('button').forEach(b => {
            const isMatch = b.dataset.point === point;
            b.className = isMatch ? 'h-7 text-[10px] font-bold rounded border border-indigo-600 bg-indigo-600 text-white transition' : 'h-7 text-[10px] font-bold rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition';
        });
    }
    window.renderPopUpLivePreview(mode);
};

window.renderPopUpLivePreview = function(mode) {
    const modalPrefix = mode === 'create' ? 'create' : 'edit';
    const bgLayer = document.getElementById(modalPrefix + 'PopupBgLayer');
    const overlayLayer = document.getElementById(modalPrefix + 'PopupOverlayLayer');
    const badgeEl = document.getElementById(modalPrefix + 'PopupBadgePreview');
    const titleEl = document.getElementById(modalPrefix + 'PopupTitlePreview');
    const subtitleEl = document.getElementById(modalPrefix + 'PopupSubtitlePreview');
    const ctaBtn = document.getElementById(modalPrefix + 'PopupCtaBtnPreview');
    
    if (!bgLayer) return;
    
    const actionEl = document.getElementById(modalPrefix + 'PopupAction');
    const action = actionEl ? actionEl.value : 'ally_discount';
    
    // 1. Image Resolution
    let imageUrl = '';
    const allyImgSourceRadio = document.querySelector(`#${mode}SectionModal input[name="settings[popup_ally_image_source]"]:checked`);
    const isAllyPoster = !allyImgSourceRadio || allyImgSourceRadio.value === 'ally_poster';
    
    if (action === 'ally_discount' && isAllyPoster) {
        const campSel = document.getElementById(modalPrefix + 'PopupAllyCampaignId');
        if (campSel && campSel.selectedIndex >= 0) {
            const opt = campSel.options[campSel.selectedIndex];
            imageUrl = opt ? (opt.dataset.banner || '') : '';
        }
    } else {
        const customUrlInp = document.getElementById(modalPrefix + 'PopupImageUrl');
        if (customUrlInp && customUrlInp.value) {
            imageUrl = customUrlInp.value;
        } else if (window['_currentPopupPreviewBg_' + mode]) {
            imageUrl = window['_currentPopupPreviewBg_' + mode];
        }
    }
    
    if (imageUrl) {
        bgLayer.style.backgroundImage = imageUrl.startsWith('url(') ? imageUrl : `url('${imageUrl}')`;
    } else {
        bgLayer.style.backgroundImage = 'none';
        bgLayer.style.backgroundColor = '#1e1b4b';
    }
    
    // Focal Point
    const focalInp = document.getElementById(mode === 'create' ? 'inpCreatePopupFocalPoint' : 'inpEditPopupFocalPoint');
    const focalPoint = focalInp && focalInp.value ? focalInp.value : 'center center';
    bgLayer.style.backgroundPosition = focalPoint;
    
    // 2. Overlay Gradient
    const enableGrad = document.getElementById(modalPrefix + 'PopupEnableGradient')?.checked ?? true;
    if (enableGrad && overlayLayer) {
        const colStart = document.getElementById(modalPrefix + 'PopupGradColorStart')?.value || '#0f172a';
        const opStart = (parseInt(document.getElementById(modalPrefix + 'PopupGradOpacityStart')?.value, 10) || 85) / 100;
        const colEnd = document.getElementById(modalPrefix + 'PopupGradColorEnd')?.value || '#000000';
        const opEnd = (parseInt(document.getElementById(modalPrefix + 'PopupGradOpacityEnd')?.value, 10) || 95) / 100;
        const dir = document.getElementById(modalPrefix + 'PopupGradDir')?.value || 'to bottom';
        
        function hexToRgba(hex, alpha) {
            let c = hex.replace('#', '');
            if (c.length === 3) c = c.split('').map(x => x + x).join('');
            const num = parseInt(c, 16);
            return `rgba(${(num >> 16) & 255}, ${(num >> 8) & 255}, ${num & 255}, ${alpha})`;
        }
        
        overlayLayer.style.display = 'block';
        overlayLayer.style.background = `linear-gradient(${dir}, ${hexToRgba(colStart, opStart)} 0%, ${hexToRgba(colEnd, opEnd)} 100%)`;
    } else if (overlayLayer) {
        overlayLayer.style.display = 'none';
    }
    
    // 3. Texts and Colors
    const titleInp = document.getElementById(mode === 'create' ? 'inpCreateTitle' : 'inpEditTitle');
    const subtitleInp = document.getElementById(mode === 'create' ? 'inpCreateSubtitle' : 'inpEditSubtitle');
    const badgeInp = document.getElementById(mode === 'create' ? 'inpCreateBadge' : 'inpEditBadge');
    
    const titleColorInp = document.getElementById(modalPrefix + 'PopupTitleColor');
    const titleColor = titleColorInp && titleColorInp.value ? titleColorInp.value : '#ffffff';

    const subtitleColorInp = document.getElementById(modalPrefix + 'PopupSubtitleColor');
    const subtitleColor = subtitleColorInp && subtitleColorInp.value ? subtitleColorInp.value : '#e2e8f0';

    if (titleEl) {
        titleEl.textContent = titleInp && titleInp.value ? titleInp.value : 'Descuento Exclusivo';
        titleEl.style.setProperty('color', titleColor, 'important');
    }
    if (subtitleEl) {
        subtitleEl.textContent = subtitleInp && subtitleInp.value ? subtitleInp.value : 'Suscríbete con tu WhatsApp para recibir tu código único.';
        subtitleEl.style.setProperty('color', subtitleColor, 'important');
    }
    if (badgeEl) {
        if (badgeInp && badgeInp.value) {
            badgeEl.textContent = badgeInp.value;
            badgeEl.style.display = 'inline-block';
        } else {
            badgeEl.style.display = 'none';
        }
    }
    
    // CTA Button
    const ctaInp = document.getElementById(modalPrefix + 'PopupCtaText');
    if (ctaBtn) {
        if (ctaInp && ctaInp.value) {
            ctaBtn.textContent = ctaInp.value;
        } else if (action === 'ally_discount') {
            ctaBtn.textContent = 'Obtener Descuento de Aliado';
        } else if (action === 'newsletter') {
            ctaBtn.textContent = 'Suscribirme al Newsletter';
        } else if (action === 'store_discount') {
            ctaBtn.textContent = 'Obtener mi Cupón de Descuento';
        } else if (action === 'presale') {
            ctaBtn.textContent = 'Acceder a la Preventa';
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    ['create', 'edit'].forEach(mode => {
        const fileInput = document.querySelector(`#${mode}SectionModal input[name="popup_image_file"]`);
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        window['_currentPopupPreviewBg_' + mode] = evt.target.result;
                        window.renderPopUpLivePreview(mode);
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    });
});

// --- CMS MEDIA MANAGER JS (HUB 2) ---
let targetCmsMediaInput = null;

window.openCmsMediaModal = function(inputId) {
    let inputEl = null;
    if (typeof inputId === 'string') {
        inputEl = document.getElementById(inputId);
    } else {
        inputEl = inputId;
    }
    
    if (inputEl) {
        targetCmsMediaInput = inputEl;
        const modal = document.getElementById('cmsMediaModal');
        if (modal) modal.classList.remove('hidden');
        window.loadCmsMedia();
    }
};

window.closeCmsMediaModal = function() {
    const modal = document.getElementById('cmsMediaModal');
    if (modal) modal.classList.add('hidden');
    targetCmsMediaInput = null;
};

window.copyMediaUrl = function(url, btn) {
    if (!url) return;
    navigator.clipboard.writeText(url).then(() => {
        const span = btn ? btn.querySelector('span') : null;
        const originalText = span ? span.textContent : '';
        if (span) span.textContent = '¡Copiado!';
        if (btn) btn.classList.add('!bg-emerald-600', '!text-white');
        setTimeout(() => {
            if (span) span.textContent = originalText;
            if (btn) btn.classList.remove('!bg-emerald-600', '!text-white');
        }, 1800);
    }).catch(() => {
        prompt('Copia el enlace de la imagen:', url);
    });
};

window.deleteCmsMedia = function(id, btn) {
    if (!id) return;
    if (!confirm('¿Seguro que deseas eliminar esta imagen del Gestor de Medios CMS? Esta acción no se puede deshacer.')) return;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                      || document.querySelector('input[name="_token"]')?.value;

    fetch(`/admin/tienda-cms/media/${id}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.loadCmsMedia();
        } else {
            alert('Error al eliminar: ' + (data.message || 'Error del servidor'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error de red al intentar eliminar la imagen.');
    });
};

window.filterCmsMediaGrid = function(query) {
    const q = (query || '').toLowerCase().trim();
    document.querySelectorAll('#cmsMediaPanelGrid .cms-media-card').forEach(card => {
        const fileName = (card.getAttribute('data-file-name') || '').toLowerCase();
        if (!q || fileName.includes(q)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
};

window.loadCmsMedia = function() {
    const modalGrid = document.getElementById('cmsMediaGrid');
    const panelGrid = document.getElementById('cmsMediaPanelGrid');
    const loading = document.getElementById('cmsMediaLoading');
    const emptyModal = document.getElementById('cmsMediaEmpty');
    const tabCount = document.getElementById('cmsMediaTabCount');

    if (loading) loading.classList.remove('hidden');
    if (emptyModal) emptyModal.classList.add('hidden');

    fetch('/admin/tienda-cms/media', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (loading) loading.classList.add('hidden');
        if (data.success && data.media) {
            const count = data.media.length;
            if (tabCount) tabCount.textContent = `${count} Fotos`;

            // 1. Renderizar en el modal (Picker para crear/editar sección)
            if (modalGrid) {
                modalGrid.innerHTML = '';
                if (count === 0 && emptyModal) {
                    emptyModal.classList.remove('hidden');
                } else {
                    data.media.forEach(media => {
                        modalGrid.insertAdjacentHTML('beforeend', `
                            <div class="relative group aspect-square bg-slate-200 rounded-lg overflow-hidden border border-slate-200 cursor-pointer hover:border-indigo-500 hover:ring-2 hover:ring-indigo-200 transition-all" onclick="window.selectCmsMedia('${media.file_path}')">
                                <img src="${media.file_path}" alt="${media.file_name}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-indigo-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center p-2">
                                    <span class="text-white text-[10px] font-bold text-center break-all px-1 bg-black/50 rounded line-clamp-2">${media.file_name}</span>
                                    <span class="text-white text-[10px] font-bold mt-1 bg-indigo-600 px-2 py-0.5 rounded shadow">Seleccionar</span>
                                </div>
                            </div>
                        `);
                    });
                }
            }

            // 2. Renderizar en la pestaña principal (Submódulo 6: Gestor de Medios)
            if (panelGrid) {
                panelGrid.innerHTML = '';
                if (count === 0) {
                    panelGrid.innerHTML = `
                        <div id="cmsMediaPanelEmpty" class="col-span-full py-16 text-center flex flex-col items-center justify-center">
                            <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </div>
                            <h4 class="text-sm font-bold text-slate-700">Sin imágenes en el Gestor</h4>
                            <p class="text-xs text-slate-400 mt-1 max-w-sm">Aún no has cargado fotos para tus banners y carruseles. Haz clic en "Subir Imágenes" o arrastra archivos arriba.</p>
                        </div>
                    `;
                } else {
                    data.media.forEach(item => {
                        const sizeKb = item.size ? (item.size / 1024).toFixed(1) + ' KB' : 'Img';
                        const createdStr = item.created_at ? new Date(item.created_at).toLocaleDateString() : '';
                        panelGrid.insertAdjacentHTML('beforeend', `
                            <div class="cms-media-card bg-white border border-slate-200/90 hover:border-indigo-400 rounded-xl overflow-hidden shadow-2xs hover:shadow-md transition-all group relative flex flex-col" data-media-id="${item.id}" data-file-name="${(item.file_name || '').toLowerCase()}">
                                <div class="aspect-square bg-slate-100 relative overflow-hidden flex items-center justify-center">
                                    <img src="${item.file_path}" alt="${item.file_name}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                                    <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2 p-2">
                                        <button type="button" onclick="window.copyMediaUrl('${item.file_path}', this)" class="px-2.5 py-1.5 bg-white hover:bg-slate-100 text-slate-800 text-[11px] font-bold rounded-lg shadow-sm flex items-center gap-1.5 cursor-pointer w-full justify-center">
                                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                            <span>Copiar URL</span>
                                        </button>
                                        <a href="${item.file_path}" target="_blank" class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold rounded-lg shadow-sm flex items-center gap-1.5 w-full justify-center text-center">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                            <span>Ver Original</span>
                                        </a>
                                        <button type="button" onclick="window.deleteCmsMedia(${item.id}, this)" class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold rounded-lg shadow-sm flex items-center gap-1.5 cursor-pointer w-full justify-center">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            <span>Eliminar</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="p-2.5 bg-white flex-1 flex flex-col justify-between">
                                    <span class="text-[11px] font-bold text-slate-800 truncate block" title="${item.file_name}">${item.file_name}</span>
                                    <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1">
                                        <span>${sizeKb}</span>
                                        <span>${createdStr}</span>
                                    </div>
                                </div>
                            </div>
                        `);
                    });
                }
            }
        }
    })
    .catch(err => {
        console.error(err);
        if (loading) loading.classList.add('hidden');
    });
};

window.selectCmsMedia = function(url) {
    if (targetCmsMediaInput) {
        targetCmsMediaInput.value = url;
        targetCmsMediaInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    window.closeCmsMediaModal();
};

window.uploadCmsMedia = function(input) {
    if (!input.files || input.files.length === 0) return;
    window.uploadCmsMediaFiles(input.files);
    input.value = '';
};

window.uploadCmsMediaFiles = function(files) {
    if (!files || files.length === 0) return;
    const progressEl = document.getElementById('cmsMediaPanelUploading');
    const statusText = document.getElementById('cmsMediaUploadStatusText');
    if (progressEl) progressEl.classList.remove('hidden');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                      || document.querySelector('input[name="_token"]')?.value;

    const fileList = Array.from(files);

    const uploadNext = (index) => {
        if (index >= fileList.length) {
            if (progressEl) progressEl.classList.add('hidden');
            window.loadCmsMedia();
            return;
        }

        if (statusText) statusText.textContent = `Subiendo archivo ${index + 1} de ${fileList.length} (${fileList[index].name})...`;

        const formData = new FormData();
        formData.append('file', fileList[index]);
        if (csrfToken) formData.append('_token', csrfToken);

        fetch('/admin/tienda-cms/media', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            uploadNext(index + 1);
        })
        .catch(err => {
            console.error(err);
            uploadNext(index + 1);
        });
    };

    uploadNext(0);
};

// Drag & drop listener
const dropZone = document.getElementById('cmsMediaDropZone');
if (dropZone) {
    ['dragenter', 'dragover'].forEach(name => {
        dropZone.addEventListener(name, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('border-indigo-600', 'bg-indigo-50/70');
        });
    });
    ['dragleave', 'drop'].forEach(name => {
        dropZone.addEventListener(name, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('border-indigo-600', 'bg-indigo-50/70');
        });
    });
    dropZone.addEventListener('drop', (e) => {
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            window.uploadCmsMediaFiles(e.dataTransfer.files);
        }
    });
}
// --- END CMS MEDIA MANAGER JS ---

})();
</script>
@endpush
<!-- 1B. MODAL EDITAR SECCI�N EXISTENTE -->











