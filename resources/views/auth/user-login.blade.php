<!doctype html>
<html lang="es" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Acceso al Panel - RevOps &amp; Administración</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        };
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #080c14;
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.18) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(14, 165, 233, 0.12) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(15, 23, 42, 0.6) 0px, transparent 80%);
            background-attachment: fixed;
        }

        .auth-grid-pattern {
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 flex flex-col justify-between relative selection:bg-indigo-500 selection:text-white">
    <!-- Grid overlay decorativo -->
    <div class="auth-grid-pattern absolute inset-0 pointer-events-none" aria-hidden="true"></div>

    <!-- Header minimalista superior: solo badge de estado -->
    <header class="relative z-10 w-full max-w-6xl mx-auto px-6 py-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-sky-500 flex items-center justify-center font-extrabold text-white text-base shadow-lg shadow-indigo-500/25">
                M
            </div>
            <div>
                <span class="block text-sm font-bold tracking-tight text-white leading-none">MI TIENDA</span>
                <span class="block text-[10px] font-semibold tracking-wider text-slate-400 uppercase mt-0.5">RevOps Commerce Suite</span>
            </div>
        </div>

        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900/80 border border-slate-800 text-[11px] font-medium text-slate-300 backdrop-blur-md">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" aria-hidden="true"></span>
            <span>Sistema Operativo v2.6</span>
        </div>
    </header>

    <!-- Contenedor central: Tarjeta de Acceso -->
    <main class="relative z-10 flex-1 flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-[420px]">
            <div class="bg-slate-900/85 backdrop-blur-xl border border-slate-800/90 rounded-2xl shadow-2xl shadow-black/60 p-7 sm:p-9 relative overflow-hidden">
                <!-- Acento sutil superior -->
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-indigo-500 via-sky-400 to-indigo-600" aria-hidden="true"></div>

                <!-- Encabezado de la tarjeta -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 mb-4 shadow-inner">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight text-white mb-2">Acceso al Panel</h1>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        Ingresa tus credenciales corporativas para gestionar el inventario, ventas y operaciones.
                    </p>
                </div>

                <!-- Alerta de errores -->
                @if($errors->any())
                    <div class="mb-6 p-3.5 rounded-xl bg-rose-950/50 border border-rose-800/60 text-rose-200 text-xs sm:text-sm flex items-start gap-3" role="alert">
                        <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <div class="flex-1 font-medium">
                            {{ $errors->first() }}
                        </div>
                    </div>
                @endif

                @if(session('status'))
                    <div class="mb-6 p-3.5 rounded-xl bg-emerald-950/50 border border-emerald-800/60 text-emerald-200 text-xs sm:text-sm flex items-start gap-3" role="status">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <div class="flex-1 font-medium">
                            {{ session('status') }}
                        </div>
                    </div>
                @endif

                <!-- Formulario de Acceso -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5" autocomplete="on">
                    @csrf

                    <!-- Correo corporativo -->
                    <div>
                        <label for="user-email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                            Correo corporativo
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                </svg>
                            </div>
                            <input 
                                id="user-email" 
                                name="email" 
                                type="email" 
                                value="{{ old('email') }}" 
                                placeholder="equipo@mitienda.com" 
                                required 
                                autofocus 
                                autocomplete="email"
                                class="w-full bg-slate-950/70 border border-slate-800 rounded-xl py-2.5 pl-10 pr-3.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition duration-150"
                            >
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="user-password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                                Contraseña
                            </label>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </div>
                            <input 
                                id="user-password" 
                                name="password" 
                                type="password" 
                                placeholder="••••••••" 
                                required 
                                autocomplete="current-password"
                                class="w-full bg-slate-950/70 border border-slate-800 rounded-xl py-2.5 pl-10 pr-10 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition duration-150"
                            >
                            <button 
                                type="button" 
                                id="toggle-password-btn" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 focus:outline-none transition duration-150"
                                aria-label="Mostrar u ocultar contraseña"
                            >
                                <svg id="eye-icon" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10 7-10 7Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg id="eye-off-icon" class="w-4 h-4 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                                    <line x1="2" y1="2" x2="22" y2="22"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Mantener sesión iniciada -->
                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 text-slate-400 cursor-pointer select-none hover:text-slate-300">
                            <input 
                                type="checkbox" 
                                name="remember" 
                                class="rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500/20 focus:ring-offset-0 focus:ring-1"
                            >
                            <span>Mantener sesión activa</span>
                        </label>
                        <span class="text-slate-500 text-[11px] flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            TLS 1.3
                        </span>
                    </div>

                    <!-- Botón de Envío -->
                    <button 
                        type="submit" 
                        class="w-full mt-3 py-3 px-4 rounded-xl font-semibold text-sm text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 shadow-lg shadow-indigo-600/30 transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span>Entrar al panel</span>
                        <svg class="w-4 h-4 text-indigo-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14"></path>
                            <path d="m12 5 7 7-7 7"></path>
                        </svg>
                    </button>
                </form>

                <!-- Pie de tarjeta -->
                <div class="mt-8 pt-6 border-t border-slate-800/80 text-center">
                    <p class="text-[11px] text-slate-500 leading-tight">
                        Ambiente administrativo restringido. Todas las operaciones y accesos quedan registrados en la bitácora de auditoría.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer minimalista inferior -->
    <footer class="relative z-10 w-full max-w-6xl mx-auto px-6 py-6 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Mi Tienda RevOps Suite. Todos los derechos reservados.</p>
    </footer>

    <!-- Script para alternar visibilidad de contraseña -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toggleBtn = document.getElementById('toggle-password-btn');
            var passwordInput = document.getElementById('user-password');
            var eyeIcon = document.getElementById('eye-icon');
            var eyeOffIcon = document.getElementById('eye-off-icon');

            if (toggleBtn && passwordInput) {
                toggleBtn.addEventListener('click', function() {
                    var isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    if (eyeIcon && eyeOffIcon) {
                        eyeIcon.classList.toggle('hidden', isPassword);
                        eyeOffIcon.classList.toggle('hidden', !isPassword);
                    }
                });
            }
        });
    </script>
</body>
</html>
