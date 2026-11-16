<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Mi Tienda') - CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/crm.css?v=20260908">
    <script>
        (function() {
            try {
                var savedTheme = localStorage.getItem('theme_preference') || localStorage.getItem('app_theme') || 'system';
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (savedTheme === 'dark' || (savedTheme === 'system' && prefersDark)) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            } catch(e) {}
        })();
    </script>
</head>
@php($isHome = request()->path() === '/')
@php($isStorePage = request()->is('/') || request()->is('productos') || request()->is('productos/*') || request()->is('categorias') || request()->is('ofertas') || request()->is('nosotros') || request()->is('contacto') || request()->is('carrito') || request()->is('checkout') || request()->is('checkout/*') || request()->is('search') || request()->is('privacidad') || request()->is('cookies'))
@php($authUser = auth()->user())
@php($isPortalUser = auth()->check() && ($authUser->role ?? 'customer') === 'portal')
@php($userType = auth()->check() ? ($isPortalUser ? 'portal' : 'customer') : 'guest')
<body class="@yield('body_class') {{ $isHome ? 'home-page' : '' }} {{ $userType === 'portal' && !$isStorePage ? 'portal-site-style' : '' }}">
    <div class="container {{ $userType === 'portal' && !$isStorePage ? 'portal-admin-shell' : '' }}" data-user-type="{{ $userType }}">
        @if($isStorePage)
            <div class="storefront-announcement" role="status" aria-label="Avisos de la tienda">
                <div class="storefront-announcement-track">
                    <span>Envios seguros a todo Mexico</span><b aria-hidden="true">/</b>
                    <span>Pagos seguros con Tarjeta y OXXO</span><b aria-hidden="true">/</b>
                    <span>Stock fisico con entrega inmediata</span><b aria-hidden="true">/</b>
                    <span>250+ prendas exclusivas</span><b aria-hidden="true">/</b>
                    <span>Pedidos directos por WhatsApp</span><b aria-hidden="true">/</b>
                    <span>Envios seguros a todo Mexico</span><b aria-hidden="true">/</b>
                    <span>Transferencias SPEI sin comision</span><b aria-hidden="true">/</b>
                    <span>Stock fisico con entrega inmediata</span><b aria-hidden="true">/</b>
                    <span>250+ prendas exclusivas</span><b aria-hidden="true">/</b>
                    <span>Pedidos directos por WhatsApp</span><b aria-hidden="true">/</b>
                </div>
            </div>
        @endif
        <header class="app-header">
            <div class="brand">
                <div class="logo"></div>
                <h1>@yield('title','Mi Tienda')</h1>
            </div>
            <button class="menu-toggle" type="button" aria-label="Abrir menu" aria-expanded="false" data-menu-toggle>
                <span></span>
                <span></span>
                <span></span>
            </button>
            <a class="mobile-cart-link" href="/carrito" aria-label="Carrito" title="Carrito">
                <svg class="cart-glyph" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                <span class="cart-badge hidden" data-cart-badge>0</span>
            </a>
            <div class="header-right">
                <nav class="main-nav nav-guest {{ $userType === 'guest' ? '' : 'hidden' }}" data-nav-guest>
                    <a class="{{ request()->routeIs('store.home') ? 'is-active' : '' }}" href="/">Inicio</a>
                    <a class="{{ request()->routeIs('store.offers') ? 'is-active' : '' }}" href="/ofertas">Ofertas</a>
                    <a href="/productos?source=extended">Novedades</a>
                    <a href="/productos">Marcas</a>
                    <a class="{{ request()->routeIs('store.products') || request()->routeIs('store.product') ? 'is-active' : '' }}" href="/productos">Catalogo ({{ \App\Models\Product::where('visible', true)->count() }})</a>
                    <span class="nav-divider" aria-hidden="true"></span>
                    <a class="nav-size-guide" href="/productos#guia-tallas"><svg aria-hidden="true" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="7" width="20" height="10" rx="2"></rect><path d="M6 7v4M10 7v5M14 7v4M18 7v5"></path></svg> Guia de tallas</a>
                    <a class="nav-cart-action" href="/carrito" aria-label="Carrito" title="Carrito"><svg class="cart-glyph" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg> Carrito <span class="cart-badge hidden" data-cart-badge>0</span></a>
                    <a class="nav-whatsapp" href="/contacto"><span class="whatsapp-dot" aria-hidden="true"></span><svg aria-hidden="true" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 4a8 8 0 0 0-6.9 12.05L4 20l3.98-1.05A8 8 0 1 0 12 4Zm0 14.4a6.38 6.38 0 0 1-3.25-.89l-.23-.14-2.36.62.63-2.3-.15-.24A6.4 6.4 0 1 1 12 18.4Z"></path><path d="M15.7 13.7c-.2-.1-1.2-.6-1.38-.67-.18-.07-.32-.1-.46.1-.14.2-.53.67-.65.8-.12.13-.24.15-.44.05-.2-.1-.84-.31-1.6-.99-.59-.52-.99-1.16-1.1-1.36-.12-.2-.01-.3.09-.4.09-.09.2-.24.3-.36.1-.12.13-.2.2-.34.07-.13.03-.25-.02-.35-.05-.1-.46-1.1-.63-1.5-.17-.4-.34-.34-.46-.35h-.4c-.14 0-.35.05-.54.25-.19.2-.72.7-.72 1.7s.74 1.97.84 2.1c.1.13 1.45 2.22 3.51 3.1.49.21.87.34 1.17.43.49.16.94.14 1.3.08.4-.06 1.2-.49 1.37-.96.17-.47.17-.87.12-.96-.05-.09-.18-.14-.38-.24Z"></path></svg> WhatsApp</a>
                </nav>

                <nav class="main-nav nav-customer {{ $userType === 'customer' ? '' : 'hidden' }}" data-nav-customer>
                    <a class="{{ request()->routeIs('store.home') ? 'is-active' : '' }}" href="/">Inicio</a>
                    <a class="{{ request()->routeIs('store.offers') ? 'is-active' : '' }}" href="/ofertas">Ofertas</a>
                    <a href="/productos?source=extended">Novedades</a>
                    <a href="/productos">Marcas</a>
                    <a class="{{ request()->routeIs('store.products') || request()->routeIs('store.product') ? 'is-active' : '' }}" href="/productos">Catalogo ({{ \App\Models\Product::where('visible', true)->count() }})</a>
                    <span class="nav-divider" aria-hidden="true"></span>
                    <a class="nav-size-guide" href="/productos#guia-tallas"><svg aria-hidden="true" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="7" width="20" height="10" rx="2"></rect><path d="M6 7v4M10 7v5M14 7v4M18 7v5"></path></svg> Guia de tallas</a>
                    <a class="nav-cart-action" href="/carrito" aria-label="Carrito" title="Carrito"><svg class="cart-glyph" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg> Carrito <span class="cart-badge hidden" data-cart-badge>0</span></a>
                    <a class="nav-whatsapp" href="/contacto"><span class="whatsapp-dot" aria-hidden="true"></span><svg aria-hidden="true" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 4a8 8 0 0 0-6.9 12.05L4 20l3.98-1.05A8 8 0 1 0 12 4Zm0 14.4a6.38 6.38 0 0 1-3.25-.89l-.23-.14-2.36.62.63-2.3-.15-.24A6.4 6.4 0 1 1 12 18.4Z"></path><path d="M15.7 13.7c-.2-.1-1.2-.6-1.38-.67-.18-.07-.32-.1-.46.1-.14.2-.53.67-.65.8-.12.13-.24.15-.44.05-.2-.1-.84-.31-1.6-.99-.59-.52-.99-1.16-1.1-1.36-.12-.2-.01-.3.09-.4.09-.09.2-.24.3-.36.1-.12.13-.2.2-.34.07-.13.03-.25-.02-.35-.05-.1-.46-1.1-.63-1.5-.17-.4-.34-.34-.46-.35h-.4c-.14 0-.35.05-.54.25-.19.2-.72.7-.72 1.7s.74 1.97.84 2.1c.1.13 1.45 2.22 3.51 3.1.49.21.87.34 1.17.43.49.16.94.14 1.3.08.4-.06 1.2-.49 1.37-.96.17-.47.17-.87.12-.96-.05-.09-.18-.14-.38-.24Z"></path></svg> WhatsApp</a>
                </nav>

                <nav class="main-nav nav-portal {{ $userType === 'portal' ? '' : 'hidden' }}" data-nav-portal>
                    <a class="primary {{ request()->routeIs('portal.dashboard') ? 'is-active' : '' }}" href="{{ route('portal.dashboard') }}">Dashboard</a>
                    <a class="{{ request()->routeIs('portal.inventory') ? 'is-active' : '' }}" href="{{ route('portal.inventory') }}">Inventario</a>
                    <a class="{{ request()->routeIs('portal.suppliers.colors') ? 'is-active' : '' }}" href="{{ route('portal.suppliers.colors') }}">Proveedores</a>
                    <details class="portal-settings-dropdown">
                        <summary>Ajustes</summary>
                        <div class="portal-settings-menu">
                            <a class="{{ request()->routeIs('leads.import.form') ? 'is-active' : '' }}" href="/leads/import">Importar CSV</a>
                        </div>
                    </details>
                    <a class="portal-utility-link" href="{{ route('portal.governance') }}" aria-label="Notificaciones" title="Notificaciones">
                        <svg class="portal-utility-icon" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4"></path></svg>
                        <span class="portal-utility-label">Notificaciones</span>
                    </a>
                    <a class="portal-utility-link" href="{{ route('portal.marketing') }}" aria-label="Bandeja de chat" title="Bandeja de chat">
                        <svg class="portal-utility-icon" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 11.5a7.5 7.5 0 0 1-8 7.5 8.5 8.5 0 0 1-3.4-.7L4 20l1.7-3.8A7.4 7.4 0 0 1 4 11.5 7.5 7.5 0 0 1 12 4a7.5 7.5 0 0 1 8 7.5Z"></path></svg>
                        <span class="portal-utility-label">Bandeja</span>
                    </a>
                </nav>

                <div class="auth-actions {{ auth()->check() ? 'hidden' : '' }}" data-auth-actions>
                    <button class="btn primary" type="button" data-open-login>Iniciar sesión</button>
                </div>

                <div class="account-shell {{ auth()->check() ? '' : 'hidden' }}" data-account-shell>
                    <a class="cart-link cart-link-user {{ $userType === 'portal' ? 'hidden' : '' }}" href="/carrito" aria-label="Carrito" title="Carrito" data-account-cart>
                        <svg class="cart-glyph" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <span class="cart-badge hidden" data-cart-badge>0</span>
                    </a>
                    <div class="account-menu" data-account-menu>
                        <button class="account-trigger" type="button" data-account-trigger aria-expanded="false" aria-label="Abrir opciones de cuenta">
                            <img class="account-avatar" data-account-avatar src="{{ auth()->check() ? 'https://i.pravatar.cc/80?u=' . urlencode(auth()->user()->email) : '' }}" alt="Foto de perfil">
                        </button>
                        <div class="account-dropdown hidden" data-account-dropdown>
                            <div class="account-meta">
                                <strong data-account-name>{{ auth()->user()->name ?? '' }}</strong>
                                <span data-account-role>{{ $userType === 'portal' ? 'Usuario portal' : 'Cliente de la tienda' }}</span>
                            </div>
                            <a href="/mi-perfil" data-account-option-profile>Mi perfil</a>
                            <a href="/mis-pedidos" data-account-option-orders>Seguimiento de pedidos</a>
                            <a class="{{ $userType === 'portal' ? '' : 'hidden' }}" href="/leads" data-account-option-panel>Panel administrador</a>
                            <button class="btn ghost account-logout" type="button" data-account-logout>Salir</button>
                        </div>
                    </div>
                </div>

                @unless($isHome)
                    <div class="search {{ $userType === 'portal' ? 'hidden' : '' }}" data-store-search>
                        <form action="/search" method="GET" class="search-form">
                            <input name="q" placeholder="Buscar productos, SKUs..." />
                        </form>
                    </div>
                @endunless
            </div>
        </header>

        <div class="mobile-nav-backdrop" data-mobile-nav-backdrop aria-hidden="true"></div>
        <aside class="mobile-nav-drawer" data-mobile-nav-drawer aria-hidden="true" aria-label="Navegacion movil">
            <div class="mobile-nav-drawer-header">
                <div class="mobile-nav-drawer-brand">
                    <span class="mobile-nav-drawer-mark">M</span>
                    <span>Mi Tienda</span>
                </div>
                <button class="mobile-nav-close" type="button" aria-label="Cerrar menu" data-mobile-nav-close>
                    <svg aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                </button>
            </div>
            <nav class="mobile-nav-links" aria-label="Menu principal movil">
                <a class="{{ request()->routeIs('store.home') ? 'is-active' : '' }}" href="/">Inicio <span aria-hidden="true">→</span></a>
                <a class="{{ request()->routeIs('store.offers') ? 'is-active' : '' }}" href="/ofertas">Ofertas <span aria-hidden="true">→</span></a>
                <a href="/productos?source=extended">Novedades <span aria-hidden="true">→</span></a>
                <a href="/productos">Marcas <span aria-hidden="true">→</span></a>
                <a class="{{ request()->routeIs('store.products') || request()->routeIs('store.product') ? 'is-active' : '' }}" href="/productos">Catalogo ({{ \App\Models\Product::where('visible', true)->count() }}) <span aria-hidden="true">→</span></a>
            </nav>
            <div class="mobile-nav-utilities">
                <a href="/productos#guia-tallas"><svg aria-hidden="true" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="7" width="20" height="10" rx="2"></rect><path d="M6 7v4M10 7v5M14 7v4M18 7v5"></path></svg> Guia de tallas</a>
                <a href="/carrito"><svg aria-hidden="true" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg> Carrito <span class="mobile-nav-count" data-cart-badge>0</span></a>
                <a href="/contacto"><svg aria-hidden="true" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 11.5a7.5 7.5 0 0 1-8 7.5 8.5 8.5 0 0 1-3.4-.7L4 20l1.7-3.8A7.4 7.4 0 0 1 4 11.5 7.5 7.5 0 0 1 12 4a7.5 7.5 0 0 1 8 7.5Z"></path></svg> WhatsApp</a>
            </div>
            <div class="mobile-nav-account">
                @if($userType === 'guest')
                    <button class="btn primary" type="button" data-open-login>Iniciar sesion</button>
                @else
                    <a href="/mi-perfil">Mi perfil</a>
                    <a href="/mis-pedidos">Mis pedidos</a>
                @endif
            </div>
        </aside>

        @if($isStorePage)
            <section class="main main-full">
                @yield('content')
            </section>
            @include('partials.site-footer')
        @else
            <div class="layout {{ $userType === 'portal' ? 'portal-layout' : '' }}" {{ $userType === 'portal' ? 'data-portal-layout' : '' }}>
                <aside class="sidebar">
                    @if($userType === 'portal')
                        <div class="card portal-modules-sidebar" data-portal-modules-sidebar>
                            <div class="portal-modules-head">
                                <strong>Panel Portal Administrador</strong>
                                <button type="button" class="btn ghost portal-modules-toggle" data-portal-modules-toggle aria-expanded="true">Contraer</button>
                            </div>
                            <div class="muted side-note">Módulos del proyecto</div>

                            <nav class="portal-module-buttons" aria-label="Módulos administrativos">
                                <a class="portal-module-btn {{ request()->routeIs('portal.dashboard') ? 'is-active' : '' }}" href="{{ route('portal.dashboard') }}"><svg class="module-icon" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5M4 19h16M8 16v-5M12 16V7M16 16v-3"></path></svg><span class="module-title">Dashboard</span></a>
                                <a class="portal-module-btn {{ request()->routeIs('store.products') ? 'is-active' : '' }}" href="/productos"><svg class="module-icon" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m4 8 8-4 8 4-8 4-8-4Zm0 0v8l8 4 8-4V8M12 12v8"></path></svg><span class="module-title">Tienda</span></a>
                                <a class="portal-module-btn {{ request()->routeIs('leads.index') || request()->routeIs('leads.show') || request()->routeIs('leads.create') || request()->routeIs('leads.edit') ? 'is-active' : '' }}" href="/leads"><svg class="module-icon" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"></circle><path d="M3 20a6 6 0 0 1 12 0M16 5a3 3 0 0 1 0 6M17 14a5 5 0 0 1 4 6"></path></svg><span class="module-title">Leads</span></a>
                                <a class="portal-module-btn {{ request()->routeIs('leads.board') ? 'is-active' : '' }}" href="/leads/board"><svg class="module-icon" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5M4 19h16M8 16v-4M12 16V8M16 16v-7"></path></svg><span class="module-title">CRM</span></a>
                                <a class="portal-module-btn {{ request()->routeIs('portal.scoring') ? 'is-active' : '' }}" href="{{ route('portal.scoring') }}"><svg class="module-icon" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 14 9l6 2-6 2-2 6-2-6-6-2 6-2 2-6Z"></path></svg><span class="module-title">Scoring</span></a>
                                <a class="portal-module-btn {{ request()->routeIs('portal.inventory') ? 'is-active' : '' }}" href="{{ route('portal.inventory') }}"><svg class="module-icon" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 8 4v10l-8 4-8-4V7l8-4ZM4 7l8 4 8-4M12 11v10"></path></svg><span class="module-title">Inventario</span></a>
                                <a class="portal-module-btn {{ request()->routeIs('portal.marketing') ? 'is-active' : '' }}" href="{{ route('portal.marketing') }}"><svg class="module-icon" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 11.5a7.5 7.5 0 0 1-8 7.5 8.5 8.5 0 0 1-3.4-.7L4 20l1.7-3.8A7.4 7.4 0 0 1 4 11.5 7.5 7.5 0 0 1 12 4a7.5 7.5 0 0 1 8 7.5Z"></path></svg><span class="module-title">Marketing</span></a>
                                <a class="portal-module-btn {{ request()->routeIs('portal.finance') ? 'is-active' : '' }}" href="{{ route('portal.finance') }}"><svg class="module-icon" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 10h18M7 15h3"></path></svg><span class="module-title">Finanzas</span></a>
                                <a class="portal-module-btn {{ request()->routeIs('portal.logistics') ? 'is-active' : '' }}" href="{{ route('portal.logistics') }}"><svg class="module-icon" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h11v10H3zM14 9h4l3 3v4h-7zM7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM17 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"></path></svg><span class="module-title">Logística</span></a>
                                <a class="portal-module-btn {{ request()->routeIs('portal.suppliers.colors') ? 'is-active' : '' }}" href="{{ route('portal.suppliers.colors') }}"><svg class="module-icon" aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20a8 8 0 1 1 8-8c0 2-1 3-3 3h-1.5a2.5 2.5 0 0 0-2.5 2.5V20"></path><circle cx="7.5" cy="11" r=".8"></circle><circle cx="10" cy="7.5" r=".8"></circle><circle cx="14" cy="7.5" r=".8"></circle><circle cx="16.5" cy="11" r=".8"></circle></svg><span class="module-title">Proveedores</span></a>
                            </nav>
                        </div>
                    @else
                        <div class="card">
                            <strong>Filtro rápido</strong>
                            <div class="muted side-note">Categorías, tallas y más</div>
                        </div>
                    @endif
                </aside>

                <section class="main">
                    @yield('content')
                </section>
            </div>
        @endif
    </div>

    <div class="modal-backdrop hidden" data-login-modal aria-hidden="true">
        <div class="login-modal" role="dialog" aria-modal="true" aria-labelledby="login-modal-title">
            <button type="button" class="modal-close" data-close-login aria-label="Cerrar">×</button>
            <p class="eyebrow">BIENVENIDO</p>
            <h3 id="login-modal-title">Iniciar sesión</h3>
            <p class="muted">Tu cuenta define permisos de acceso dentro de la app.</p>
            <div class="alert alert-error hidden" data-login-error></div>

            <form class="stack" data-login-form>
                <div class="field-row">
                    <label for="login-email">Correo</label>
                    <input id="login-email" name="email" type="email" placeholder="tu@email.com" required>
                </div>
                <div class="field-row">
                    <label for="login-password">Contraseña</label>
                    <input id="login-password" name="password" type="password" placeholder="********" required>
                </div>
                <div class="form-actions">
                    <button class="btn primary" type="submit">Ingresar</button>
                </div>
            </form>

            <div class="login-divider" role="presentation"><span>o continuar con</span></div>

            <div class="oauth-actions">
                <button type="button" class="btn ghost oauth-btn" data-google-login>
                    <span class="oauth-mark" aria-hidden="true">G</span>
                    Iniciar sesión con Google
                </button>
                <button type="button" class="btn ghost oauth-btn" data-google-register>
                    <span class="oauth-mark" aria-hidden="true">G</span>
                    Registrarse con Google
                </button>
            </div>

            <p class="modal-footnote muted">¿No tienes cuenta? Tu registro también puede gestionarse desde Google.</p>
        </div>
    </div>

    <div class="modal-backdrop hidden" data-availability-modal aria-hidden="true">
        <div class="login-modal availability-modal" role="dialog" aria-modal="true" aria-labelledby="availability-modal-title">
            <button type="button" class="modal-close" data-close-availability aria-label="Cerrar">×</button>
            <p class="eyebrow">CATALOGO EXTENDIDO</p>
            <h3 id="availability-modal-title">Solicitud de disponibilidad enviada</h3>
            <p class="muted">Antes de confirmar tu compra debemos validar la existencia en stock con el proveedor. Posteriormente te enviaremos un correo electronico para continuar con tu compra.</p>
            <div class="form-actions">
                <button class="btn primary" type="button" data-close-availability>Entendido</button>
            </div>
        </div>
    </div>
    <div id="global-toast-container" class="toast-container fixed bottom-6 right-6 z-[9999] flex flex-col gap-2 pointer-events-none max-w-sm w-full" aria-live="polite"></div>
    <script src="/js/site.js?v=20260909" defer></script>
</body>
</html>
