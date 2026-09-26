@extends('layouts.portal-panel')

@section('title', 'Tienda & CMS Multiverso')
@section('hub_kicker', 'Hub 2')
@section('hub_title', 'Tienda & CMS Multiverso')
@section('hub_status', '4 Vitrinas Activas')

@section('content')
<div id="storeAppRoot" class="w-full text-slate-800 font-sans antialiased" data-screen="store">
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
    <div class="store-subnav-bar mb-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <nav class="flex gap-2 -mb-px overflow-x-auto" aria-label="Submódulos de Tienda y CMS">
            <!-- TAB 1: EDITOR VISUAL -->
            <button class="store-subnav-btn active" data-action="switch-store-tab" data-tab="visual" type="button">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                <span>1. Editor Visual Multiverso</span>
                <span class="store-subnav-count">4 Vitrinas</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Editor Visual" data-help-title="Editor Visual Multiverso" data-help-desc="Ajusta la paleta, copies y apariencia visual de cada vitrina manteniendo un inventario unificado.">?</button>
            </button>

            <!-- TAB 2: BANNERS & MARQUESINAS -->
            <button class="store-subnav-btn" data-action="switch-store-tab" data-tab="banners" type="button">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                <span>2. Banners &amp; Marquesinas</span>
                <span class="store-subnav-count">{{ count($banners) }} Banners</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Banners" data-help-title="Banners y Marquesinas" data-help-desc="Gestor de avisos rotativos superiores y carruseles hero con validación WebP (< 100 KB).">?</button>
            </button>

            <!-- TAB 3: REGLAS DE FLETE -->
            <button class="store-subnav-btn" data-action="switch-store-tab" data-tab="shipping" type="button">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                <span>3. Reglas Comerciales &amp; Fletes</span>
                <span class="store-subnav-count">Umbral ${{ number_format($shippingRule['freeShippingThreshold'], 0) }}</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Reglas de Flete" data-help-title="Reglas Comerciales &amp; Fletes" data-help-desc="Configuración de envío gratis, entrega en Metro Chabacano y simulador financiero de elasticidad (TASK-INT-18).">?</button>
            </button>

            <!-- TAB 4: CONTENIDO EDITORIAL & TALLAS -->
            <button class="store-subnav-btn" data-action="switch-store-tab" data-tab="editorial" type="button">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                <span>4. Contenido Editorial &amp; Tallas</span>
                <span class="store-subnav-count">Guía Torzo</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Contenido Editorial" data-help-title="Contenido Editorial &amp; Tallas" data-help-desc="Guía anatómica de tallas textiles Torzo, protocolos de cuidado y aviso sanitario PROFECO (TASK-INT-10).">?</button>
            </button>
        </nav>

        <div class="flex items-center gap-3 text-xs pb-2 sm:pb-0">
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
                <span class="text-2xl font-black font-mono tracking-tight text-indigo-600">{{ $storefronts['marte']['activeSkus'] }} Modelos</span>
                <span class="text-xs font-bold text-indigo-700 font-mono">Marte Store</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">Desacoplados de stock físico único (TASK-INT-10)</div>
        </div>

        <!-- KPI 3: PALETA DE COLOR -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Color Primario &amp; Acento</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Color" data-help-title="Paleta de Marca" data-help-desc="Tokens cromáticos compilados para la cabecera, botones y marquesinas de esta vitrina.">?</button>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-full border border-slate-300" style="background: {{ $storefronts['marte']['accentColor'] }};"></span>
                    <span class="text-xl font-bold font-mono text-slate-900">{{ $storefronts['marte']['accentColor'] }}</span>
                </div>
                <span class="text-xs font-bold text-slate-600 font-mono">Indigo 600</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">Fondo: {{ $storefronts['marte']['backgroundColor'] }}</div>
        </div>

        <!-- KPI 4: PREVISUALIZADOR EN VIVO -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Previsualización en Vivo</span>
                <button class="section-help-btn" type="button" aria-label="Ayuda Previsualizador" data-help-title="Previsualizador" data-help-desc="Vista previa responsive a escala sin necesidad de abrir una ventana incógnito.">?</button>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black font-mono tracking-tight text-emerald-600">Desktop</span>
                <span class="text-xs font-bold text-emerald-700 font-mono">375px &bull; 100%</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400">Escala adaptativa en tiempo real</div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- REGLA 10: SECCIÓN 3: PANELES Y ESPACIO DE TRABAJO OPERATIVO (NIVEL 3)     --}}
    {{-- ========================================================================= --}}
    
    <!-- SUBMÓDULO 1: EDITOR VISUAL MULTIVERSO -->
    <div id="storeVisualPanel" class="tab-panel">
        <!-- SELECTOR DE VITRINA MULTIVERSO -->
        <div class="universe-selector-container mb-6 flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-2">Seleccionar Vitrina:</span>
            <button class="universe-pill-btn active" data-action="switch-store-universe" data-universe="marte" type="button">
                <span class="universe-dot-indicator" style="background: #4f46e5;"></span>
                <span>Marte (Masculino / Queer)</span>
            </button>
            <button class="universe-pill-btn" data-action="switch-store-universe" data-universe="venus" type="button">
                <span class="universe-dot-indicator" style="background: #db2777;"></span>
                <span>Venus (Lencería / Beauty)</span>
            </button>
            <button class="universe-pill-btn" data-action="switch-store-universe" data-universe="tiresias" type="button">
                <span class="universe-dot-indicator" style="background: #8b5cf6;"></span>
                <span>Tiresias (Moda Afirmativa)</span>
            </button>
            <button class="universe-pill-btn" data-action="switch-store-universe" data-universe="terra" type="button">
                <span class="universe-dot-indicator" style="background: #0284c7;"></span>
                <span>Terra (Matriz / Unisex)</span>
            </button>
        </div>

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

                <form id="vitrinaConfigForm" method="POST" action="{{ route('portal.store.vitrinas.update', ['universe' => 'marte']) }}" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nombre Comercial de la Vitrina</label>
                        <input type="text" name="name" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-indigo-500" data-store-name-input value="{{ $storefronts['marte']['name'] }}">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Color de Acento (Botones/Links)</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="accentColor" class="w-8 h-8 rounded border border-slate-200 cursor-pointer p-0" data-store-accent-color-input value="{{ $storefronts['marte']['accentColor'] }}">
                                <span class="text-slate-600 font-mono text-[11px]" data-store-accent-label>{{ $storefronts['marte']['accentColor'] }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Color Fondo Lienzo</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="backgroundColor" class="w-8 h-8 rounded border border-slate-200 cursor-pointer p-0" data-store-bg-color-input value="{{ $storefronts['marte']['backgroundColor'] }}">
                                <span class="text-slate-600 font-mono text-[11px]" data-store-bg-label>{{ $storefronts['marte']['backgroundColor'] }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Título Hero Principal (H1)</label>
                        <input type="text" name="heroTitle" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-indigo-500 font-bold" data-store-hero-title-input value="{{ $storefronts['marte']['heroTitle'] }}">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Bajada / Subtítulo Persuasivo</label>
                        <textarea name="heroSubtitle" rows="3" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-indigo-500 resize-y max-w-full leading-relaxed" style="resize: vertical; max-width: 100%;" data-store-hero-sub-input>{{ $storefronts['marte']['heroSubtitle'] }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Texto del Botón CTA</label>
                            <input type="text" name="heroCtaText" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold" data-store-hero-cta-input value="{{ $storefronts['marte']['heroCtaText'] }}">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Enlace de Destino</label>
                            <input type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs text-slate-500 bg-slate-50" data-store-hero-url-input value="{{ $storefronts['marte']['heroCtaUrl'] }}" readonly>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button class="w-full button button-quiet py-2 text-xs font-bold bg-slate-900 text-white rounded-lg cursor-pointer" data-action="save-storefront-config" type="submit">
                            Guardar Cambios de Vitrina
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
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold text-white uppercase tracking-wider" style="background: {{ $storefronts['marte']['accentColor'] }};">{{ $storefronts['marte']['name'] }}</span>
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
                <div class="storefront-preview-frame desktop" data-storefront-preview-frame>
                    <!-- Topbar Simulado -->
                    <div class="preview-top-bar">
                        <div class="flex items-center gap-2">
                            <span class="font-bold tracking-tight">{{ $storefronts['marte']['name'] }}</span>
                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-300">Terra Ecosystem</span>
                        </div>
                        <div class="flex items-center gap-3 text-[10px] text-slate-300">
                            <span>Catálogo</span>
                            <span>Suscripciones</span>
                            <span>Carrito (0)</span>
                        </div>
                    </div>

                    <!-- Marquesina Dinámica Superior -->
                    <div class="preview-marquee-strip" style="background: {{ $storefronts['marte']['accentColor'] }};">
                        {{ $announcements[0]['text'] }}
                    </div>

                    <!-- Hero Section Simulado -->
                    <div class="preview-hero-section" style="background: {{ $storefronts['marte']['backgroundColor'] }};">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider mb-2" style="background: #ffffff; color: {{ $storefronts['marte']['accentColor'] }}; border: 1px solid {{ $storefronts['marte']['accentColor'] }}30;">
                            {{ $storefronts['marte']['kicker'] }}
                        </span>
                        <h2 class="preview-hero-title text-slate-900">{{ $storefronts['marte']['heroTitle'] }}</h2>
                        <p class="preview-hero-sub">{{ $storefronts['marte']['heroSubtitle'] }}</p>
                        <a href="javascript:void(0)" class="preview-hero-cta" style="background: {{ $storefronts['marte']['accentColor'] }};">
                            {{ $storefronts['marte']['heroCtaText'] }} &rarr;
                        </a>
                    </div>

                    <!-- Estante de Productos Muestra -->
                    <div class="preview-grid-shelf">
                        @foreach($storefronts['marte']['sampleProducts'] as $p)
                            <div class="preview-product-card">
                                <div class="preview-product-img">
                                    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                </div>
                                <div class="font-bold text-slate-900 truncate">{{ $p['name'] }}</div>
                                <div class="text-[10px] text-slate-500 mb-1">{{ $p['tag'] }}</div>
                                <div class="font-bold font-mono" style="color: {{ $storefronts['marte']['accentColor'] }};">MXN ${{ $p['price'] }}.00</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- FIN SUBMÓDULO 1: EDITOR VISUAL -->

    <!-- SUBMÓDULO 2: BANNERS & MARQUESINAS -->
    <div id="storeBannersPanel" class="tab-panel hidden">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm mb-6">
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
    <!-- FIN SUBMÓDULO 2: BANNERS & MARQUESINAS -->

    <!-- SUBMÓDULO 3: REGLAS DE FLETE & SIMULADOR FINANCIERO (TASK-INT-18) -->
    <div id="storeShippingPanel" class="tab-panel hidden">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
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
    <!-- FIN SUBMÓDULO 3: REGLAS DE FLETE -->

    <!-- SUBMÓDULO 4: CONTENIDO EDITORIAL & TALLAS (TASK-INT-10) -->
    <div id="storeEditorialPanel" class="tab-panel hidden">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm mb-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        <span>Guía Oficial de Tallas Textiles Torzo (TASK-INT-10)</span>
                    </h3>
                <p class="text-xs text-slate-500">Matriz interactiva de medidas anatómicas desplegada en la ficha de producto (PDP) de boxers, jocks y bralettes.</p>
            </div>
            <button class="section-help-btn" type="button" aria-label="Ayuda Guía de Tallas" data-help-title="Guía de Tallas" data-help-desc="Medidas de cintura y cadera tomadas en reposo sobre cinta métrica flexible.">?</button>
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
                    <span class="text-[10px] text-slate-400 font-semibold">3 Fibras</span>
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
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Blindado</span>
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
    <!-- FIN SUBMÓDULO 4 -->

    {{-- Formularios Ocultos para Banners y Marquesinas --}}
    <form id="toggleAnnouncementForm" method="POST" action="{{ route('portal.store.announcement.toggle') }}" style="display:none;">
        @csrf
    </form>
    <form id="toggleBannerForm" method="POST" style="display:none;">
        @csrf
    </form>
</div>

{{-- SCRIPT REACTIVO OPERATIVO TIENDA & CMS MULTIVERSO (VANILLA JS LIGERO) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const storefrontsData = @json($storefronts);
    let activeUniverse = '{{ $activeUniverse ?? "marte" }}';

    // 1. GESTOR DE PESTAÑAS (SUBNAV BAR)
    const tabPanels = {
        'visual': document.getElementById('storeVisualPanel'),
        'banners': document.getElementById('storeBannersPanel'),
        'shipping': document.getElementById('storeShippingPanel'),
        'editorial': document.getElementById('storeEditorialPanel')
    };

    const tabButtons = document.querySelectorAll('[data-action="switch-store-tab"]');

    function switchStoreTab(targetTab) {
        Object.keys(tabPanels).forEach(tabKey => {
            const panel = tabPanels[tabKey];
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

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (e.target.classList.contains('section-help-btn')) return;
            const tab = this.getAttribute('data-tab');
            if (tab) switchStoreTab(tab);
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab') || 'visual';
    if (initialTab && tabPanels[initialTab]) {
        switchStoreTab(initialTab);
    }

    // 2. CONMUTADOR DE VITRINAS MULTIVERSO & LIVE PREVIEW
    const universeButtons = document.querySelectorAll('[data-action="switch-store-universe"]');
    const vitrinaForm = document.getElementById('vitrinaConfigForm');
    const vitrinaTitle = document.getElementById('vitrinaEditorTitle');
    const vitrinaKicker = document.getElementById('vitrinaEditorKicker');

    const nameInput = document.querySelector('[data-store-name-input]');
    const accentInput = document.querySelector('[data-store-accent-color-input]');
    const accentLabel = document.querySelector('[data-store-accent-label]');
    const bgInput = document.querySelector('[data-store-bg-color-input]');
    const bgLabel = document.querySelector('[data-store-bg-label]');
    const heroTitleInput = document.querySelector('[data-store-hero-title-input]');
    const heroSubInput = document.querySelector('[data-store-hero-sub-input]');
    const heroCtaInput = document.querySelector('[data-store-hero-cta-input]');
    const heroUrlInput = document.querySelector('[data-store-hero-url-input]');

    // Preview DOM elements
    const previewFrame = document.querySelector('[data-storefront-preview-frame]');
    const previewHeroSection = document.querySelector('.preview-hero-section');
    const previewHeroTitle = document.querySelector('.preview-hero-title');
    const previewHeroSub = document.querySelector('.preview-hero-sub');
    const previewHeroCta = document.querySelector('.preview-hero-cta');
    const previewMarquee = document.querySelector('.preview-marquee-strip');
    const previewTopBarName = document.querySelector('.preview-top-bar .font-bold');
    const previewShelf = document.querySelector('.preview-grid-shelf');

    function updateVitrinaUI(universe) {
        activeUniverse = universe;
        const data = storefrontsData[universe];
        if (!data) return;

        // Update pills
        universeButtons.forEach(btn => {
            if (btn.getAttribute('data-universe') === universe) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Update form action and inputs
        if (vitrinaForm) {
            vitrinaForm.action = `/admin/tienda-cms/vitrinas/${universe}`;
        }
        if (vitrinaTitle) vitrinaTitle.textContent = `Personalizar Vitrina: ${data.name}`;
        if (vitrinaKicker) vitrinaKicker.textContent = data.kicker;

        if (nameInput) nameInput.value = data.name;
        if (accentInput) accentInput.value = data.accentColor;
        if (accentLabel) accentLabel.textContent = data.accentColor;
        if (bgInput) bgInput.value = data.backgroundColor;
        if (bgLabel) bgLabel.textContent = data.backgroundColor;
        if (heroTitleInput) heroTitleInput.value = data.heroTitle;
        if (heroSubInput) heroSubInput.value = data.heroSubtitle;
        if (heroCtaInput) heroCtaInput.value = data.heroCtaText;
        if (heroUrlInput) heroUrlInput.value = data.heroCtaUrl;

        // Update Preview
        if (previewTopBarName) previewTopBarName.textContent = data.name;
        if (previewMarquee) previewMarquee.style.background = data.accentColor;
        if (previewHeroSection) previewHeroSection.style.background = data.backgroundColor;
        if (previewHeroTitle) previewHeroTitle.textContent = data.heroTitle;
        if (previewHeroSub) previewHeroSub.textContent = data.heroSubtitle;
        if (previewHeroCta) {
            previewHeroCta.textContent = `${data.heroCtaText} \u2192`;
            previewHeroCta.style.background = data.accentColor;
        }

        // Update sample products
        if (previewShelf && data.sampleProducts) {
            previewShelf.innerHTML = data.sampleProducts.map(p => `
                <div class="preview-product-card">
                    <div class="preview-product-img">
                        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    </div>
                    <div class="font-bold text-slate-900 truncate">${p.name}</div>
                    <div class="text-[10px] text-slate-500 mb-1">${p.tag}</div>
                    <div class="font-bold font-mono" style="color: ${data.accentColor};">MXN $${p.price.toFixed(2)}</div>
                </div>
            `).join('');
        }
    }

    universeButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const u = this.getAttribute('data-universe');
            if (u) updateVitrinaUI(u);
        });
    });

    // Real-time typing in vitrina inputs updates preview
    if (nameInput) {
        nameInput.addEventListener('input', function () {
            if (previewTopBarName) previewTopBarName.textContent = this.value;
        });
    }
    if (heroTitleInput) {
        heroTitleInput.addEventListener('input', function () {
            if (previewHeroTitle) previewHeroTitle.textContent = this.value;
        });
    }
    if (heroSubInput) {
        heroSubInput.addEventListener('input', function () {
            if (previewHeroSub) previewHeroSub.textContent = this.value;
        });
    }
    if (heroCtaInput) {
        heroCtaInput.addEventListener('input', function () {
            if (previewHeroCta) previewHeroCta.textContent = `${this.value} \u2192`;
        });
    }
    if (accentInput) {
        accentInput.addEventListener('input', function () {
            if (accentLabel) accentLabel.textContent = this.value;
            if (previewMarquee) previewMarquee.style.background = this.value;
            if (previewHeroCta) previewHeroCta.style.background = this.value;
        });
    }
    if (bgInput) {
        bgInput.addEventListener('input', function () {
            if (bgLabel) bgLabel.textContent = this.value;
            if (previewHeroSection) previewHeroSection.style.background = this.value;
        });
    }

    // 3. PREVIEW DEVICE TOGGLE (DESKTOP vs MOBILE)
    const deviceButtons = document.querySelectorAll('[data-action="set-preview-device"]');
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

    // 4. MARQUESINA & BANNERS TOGGLES
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

    // 5. SIMULADOR FINANCIERO DE ELASTICIDAD DE ENVÍO GRATIS (TASK-INT-18)
    const slider = document.querySelector('[data-action="simulate-threshold-slider"]');
    const thresholdVal = document.querySelector('[data-simulator-threshold-val]');
    const ordersVal = document.querySelector('[data-simulator-orders-val]');
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
});
</script>
@endsection
