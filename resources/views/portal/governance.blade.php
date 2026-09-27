@extends('layouts.portal-panel')

@section('title', 'Hub 8: Gobernanza, Auditoria & RBAC')
@section('hub_kicker', 'Hub 8')
@section('hub_title', 'Gobernanza Global, Auditoria Forense & RBAC')
@section('hub_status', 'Activo')

@section('content')
<div id="governanceAppRoot" class="w-full text-slate-800 font-sans antialiased" data-screen="governance">

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

    {{-- BARRA DE PESTAÑAS DE SUBMÓDULOS DE GOBERNANZA --}}
    <div class="mb-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <nav class="flex gap-2 -mb-px overflow-x-auto" aria-label="Submódulos de Gobernanza">
            <!-- PESTAÑA 1: PRIVACIDAD & ROLES RBAC -->
            <a 
                href="{{ route('portal.governance', ['tab' => 'privacidad-rbac']) }}"
                class="py-3 px-4 border-b-2 font-semibold text-sm transition-all flex items-center gap-2 {{ $currentTab === 'privacidad-rbac' ? 'border-indigo-600 text-indigo-600 bg-white shadow-xs rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Privacidad &amp; Roles RBAC</span>
                <span class="px-1.5 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-medium">{{ count($rolesMatrix) }} Roles</span>
            </a>

            <!-- PESTAÑA 2: AUDITORÍA MASTER -->
            <a 
                href="{{ route('portal.governance', ['tab' => 'auditoria']) }}"
                class="py-3 px-4 border-b-2 font-semibold text-sm transition-all flex items-center gap-2 {{ $currentTab === 'auditoria' ? 'border-indigo-600 text-indigo-600 bg-white shadow-xs rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span>Auditoria Master</span>
                <span class="px-1.5 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-medium">{{ $auditStats['total_events'] ?? 0 }} Eventos</span>
            </a>

            <!-- PESTAÑA 3: CENTRO DE NOTIFICACIONES & TAREAS -->
            <a 
                href="{{ route('portal.governance', ['tab' => 'notificaciones']) }}"
                class="py-3 px-4 border-b-2 font-semibold text-sm transition-all flex items-center gap-2 {{ $currentTab === 'notificaciones' ? 'border-indigo-600 text-indigo-600 bg-white shadow-xs rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Notificaciones &amp; Tareas</span>
                <span class="px-1.5 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700 font-semibold">{{ $notificationsFeed['unread_count'] ?? 0 }} Pendientes</span>
            </a>

            <!-- PESTAÑA 4: MENSAJERÍA INTERDEPARTAMENTAL -->
            <a 
                href="{{ route('portal.governance', ['tab' => 'mensajeria']) }}"
                class="py-3 px-4 border-b-2 font-semibold text-sm transition-all flex items-center gap-2 {{ $currentTab === 'mensajeria' ? 'border-indigo-600 text-indigo-600 bg-white shadow-xs rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span>Mensajeria Inter-Areas</span>
                <span class="px-1.5 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-medium">{{ $chatSummary['total_unresolved'] ?? 0 }} Sin Resolver</span>
            </a>

            <!-- PESTAÑA 5: AJUSTES GLOBALES & ENTORNO -->
            <a 
                href="{{ route('portal.governance', ['tab' => 'ajustes-entorno']) }}"
                class="py-3 px-4 border-b-2 font-semibold text-sm transition-all flex items-center gap-2 {{ $currentTab === 'ajustes-entorno' ? 'border-indigo-600 text-indigo-600 bg-white shadow-xs rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                <span>Ajustes &amp; Entorno</span>
                <span class="px-1.5 py-0.5 text-xs rounded-full bg-amber-100 text-amber-800 font-semibold">Sandbox / Prod</span>
            </a>
        </nav>
    </div>

    {{-- ========================================================================= --}}
    {{-- CONTENIDO SEGÚN LA PESTAÑA SELECCIONADA                                   --}}
    {{-- ========================================================================= --}}

    @if($currentTab === 'privacidad-rbac')
        {{-- ===================================================================== --}}
        {{-- PESTAÑA 1: PRIVACIDAD FINANCIERA & MATRIZ DE ROLES RBAC              --}}
        {{-- ===================================================================== --}}
        <div class="space-y-6">
            {{-- SIMULADOR ACTIVO DE ROLES (TASK-RBAC-02) --}}
            <div class="bg-gradient-to-r from-slate-900 to-indigo-950 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-xs uppercase tracking-widest text-indigo-300 font-mono font-bold">Simulador de Acceso RBAC Activo</span>
                        </div>
                        <h2 class="text-lg font-bold">Rol Operativo en Sesion: <span class="text-indigo-400 font-mono uppercase">{{ $rolesMatrix[$activeRole]['name'] ?? $activeRole }}</span></h2>
                        <p class="text-xs text-slate-300 mt-1 max-w-2xl">
                            Cambia de rol en 1 clic para validar que los candados de privacidad financiera (TASK-RBAC-03) y las restricciones de acceso al Hub se apliquen de forma estricta.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($rolesMatrix as $rKey => $rData)
                            <form method="POST" action="{{ route('portal.roles.switch') }}">
                                @csrf
                                <input type="hidden" name="role" value="{{ $rKey }}">
                                <button 
                                    type="submit" 
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border {{ $activeRole === $rKey ? 'bg-indigo-600 text-white border-indigo-400 ring-2 ring-indigo-400' : 'bg-slate-800/80 text-slate-200 border-slate-700 hover:bg-slate-700' }}"
                                >
                                    {{ $rData['name'] }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- MATRIZ DE PERMISOS Y PRIVACIDAD FINANCIERA --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Matriz de Gobernanza y Privacidad Estricta</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Control de acceso a informacion confidencial (margenes netos, costos unitarios, cuentas por pagar).</p>
                    </div>
                    <span class="px-2.5 py-1 text-xs rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold">Middleware Activo</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-mono tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="p-3.5">Rol Operativo</th>
                                <th class="p-3.5">Hubs Autorizados</th>
                                <th class="p-3.5 text-center">Acceso Financiero</th>
                                <th class="p-3.5 text-center">Auditoria Master</th>
                                <th class="p-3.5 text-center">Conmutador Entorno</th>
                                <th class="p-3.5">Politica de Seguridad</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($rolesMatrix as $rKey => $rData)
                                <tr class="hover:bg-slate-50/80 transition-colors {{ $activeRole === $rKey ? 'bg-indigo-50/40 font-semibold' : '' }}">
                                    <td class="p-3.5 font-bold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded text-[11px] font-mono border {{ $rData['badge_class'] }}">{{ $rKey }}</span>
                                            <span>{{ $rData['name'] }}</span>
                                            @if($activeRole === $rKey)
                                                <span class="text-[10px] bg-indigo-600 text-white px-1.5 py-0.2 rounded font-bold">ACTIVO</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-3.5 text-slate-600">
                                        {{ implode(', ', $rData['allowed_hubs']) }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        @if($rData['financial_access'])
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[11px]">Permitido</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold text-[11px]">Bloqueado (403)</span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-center">
                                        @if($rData['audit_access'])
                                            <span class="px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 font-bold text-[11px]">Lectura Completa</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium text-[11px]">Restringido</span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-center">
                                        @if($rData['env_switch'])
                                            <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold text-[11px]">Autorizado</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium text-[11px]">Sin Permiso</span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-slate-500">
                                        {{ $rData['description'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @elseif($currentTab === 'auditoria')
        {{-- ===================================================================== --}}
        {{-- PESTAÑA 2: MASTER LOG DE AUDITORÍA OMNICANAL                          --}}
        {{-- ===================================================================== --}}
        <div class="space-y-6">
            {{-- TARJETAS DE ESTADÍSTICAS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <span class="text-xs font-mono uppercase text-slate-500 font-semibold">Total Asientos Forenses</span>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($auditStats['total_events'] ?? 0) }}</div>
                    <span class="text-[11px] text-emerald-600 font-medium mt-1 block">Inmutabilidad Criptografica</span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <span class="text-xs font-mono uppercase text-slate-500 font-semibold">Eventos Registrados Hoy</span>
                    <div class="text-2xl font-black text-indigo-600 mt-1">{{ number_format($auditStats['today_events'] ?? 0) }}</div>
                    <span class="text-[11px] text-slate-500 font-medium mt-1 block">Trazabilidad en tiempo real</span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <span class="text-xs font-mono uppercase text-slate-500 font-semibold">Modulos Supervisados</span>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ count($auditStats['by_module'] ?? []) }}</div>
                    <span class="text-[11px] text-slate-500 font-medium mt-1 block">Omnicanalidad integral</span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <span class="text-xs font-mono uppercase text-slate-500 font-semibold">Candado Forense</span>
                    <div class="text-2xl font-black text-emerald-600 mt-1">Activo</div>
                    <span class="text-[11px] text-slate-500 font-medium mt-1 block">Edicion/Borrado Prohibido</span>
                </div>
            </div>

            {{-- TABLA DE LOGS --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Bitacora de Operaciones Criticas (TASK-DSH-09)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Historial inalterable de ajustes de precio, mermas, liberaciones SPEI y conmutacion de roles.</p>
                    </div>
                    <span class="text-xs font-mono bg-slate-100 text-slate-700 px-3 py-1 rounded-lg">Pagina {{ $auditLogs->currentPage() }} de {{ $auditLogs->lastPage() }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-mono tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="p-3.5">Fecha &amp; Hora</th>
                                <th class="p-3.5">Modulo</th>
                                <th class="p-3.5">Accion</th>
                                <th class="p-3.5">Folio / Referencia</th>
                                <th class="p-3.5">Operador</th>
                                <th class="p-3.5">Detalle / Diferencial</th>
                                <th class="p-3.5">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($auditLogs as $log)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="p-3.5 text-slate-500 whitespace-nowrap font-mono">
                                        {{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : 'N/A' }}
                                    </td>
                                    <td class="p-3.5">
                                        <span class="px-2 py-0.5 rounded font-mono text-[11px] bg-slate-100 text-slate-700 font-semibold">{{ $log->module }}</span>
                                    </td>
                                    <td class="p-3.5 font-bold text-slate-900 font-mono">
                                        {{ $log->action }}
                                    </td>
                                    <td class="p-3.5">
                                        @if($log->reference_code)
                                            <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200 font-mono font-bold">{{ $log->reference_code }}</span>
                                        @else
                                            <span class="text-slate-400 font-mono">-</span>
                                        @endif
                                    </td>
                                    <td class="p-3.5">
                                        <div class="font-semibold text-slate-800">{{ $log->user_name }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $log->user_role }}</div>
                                    </td>
                                    <td class="p-3.5 text-slate-700 max-w-xs">
                                        <div>{{ $log->description }}</div>
                                        @if($log->diff_summary)
                                            <div class="mt-1 text-[11px] text-slate-500 font-mono bg-slate-50 p-1 rounded border border-slate-200">
                                                {{ $log->diff_summary }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-slate-400 font-mono whitespace-nowrap">
                                        {{ $log->ip_address ?: '127.0.0.1' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-400 text-xs">
                                        No hay asientos de auditoria registrados todavia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-200 flex justify-between items-center text-xs">
                    <span class="text-slate-500">Mostrando {{ count($auditLogs) }} registros</span>
                    <div class="flex gap-2">
                        @if($auditLogs->previousPageUrl())
                            <a href="{{ $auditLogs->previousPageUrl() }}&tab=auditoria" class="px-3 py-1.5 rounded border border-slate-300 hover:bg-slate-50 font-semibold">Anterior</a>
                        @endif
                        @if($auditLogs->nextPageUrl())
                            <a href="{{ $auditLogs->nextPageUrl() }}&tab=auditoria" class="px-3 py-1.5 rounded border border-slate-300 hover:bg-slate-50 font-semibold">Siguiente</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    @elseif($currentTab === 'notificaciones')
        {{-- ===================================================================== --}}
        {{-- PESTAÑA 3: CENTRO DE NOTIFICACIONES & TAREAS OPERATIVAS              --}}
        {{-- ===================================================================== --}}
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <span class="text-xs font-mono uppercase text-slate-500 font-semibold">Alertas No Leidas</span>
                    <div class="text-2xl font-black text-rose-600 mt-1">{{ $notificationsFeed['unread_count'] }}</div>
                    <span class="text-[11px] text-slate-500 font-medium">Asignadas a tu rol actual</span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <span class="text-xs font-mono uppercase text-slate-500 font-semibold">Total Alertas en Feed</span>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ $notificationsFeed['total'] }}</div>
                    <span class="text-[11px] text-slate-500 font-medium">Historial reciente</span>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                    <span class="text-xs font-mono uppercase text-slate-500 font-semibold">Tareas de Turno Asignadas</span>
                    <div class="text-2xl font-black text-indigo-600 mt-1">{{ count($operationalTasks) }}</div>
                    <span class="text-[11px] text-slate-500 font-medium">SLA Activo</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- LISTADO DE ALERTAS / NOTIFICACIONES --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                        <h3 class="text-sm font-bold text-slate-900">Alertas Operativas con Deep Links</h3>
                        <span class="text-xs text-slate-500 font-mono">1 Clic para Atender</span>
                    </div>

                    <div class="space-y-3">
                        @forelse($notificationsFeed['items'] as $notif)
                            <div class="p-3.5 rounded-xl border {{ $notif->is_read ? 'bg-slate-50 border-slate-200 opacity-75' : 'bg-white border-indigo-200 shadow-xs' }} flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="w-2 h-2 rounded-full {{ $notif->badge_color === 'red' ? 'bg-rose-500' : ($notif->badge_color === 'amber' ? 'bg-amber-500' : 'bg-sky-500') }}"></span>
                                        <span class="text-xs font-bold text-slate-900">{{ $notif->title }}</span>
                                        <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-slate-100 text-slate-600">{{ $notif->category }}</span>
                                    </div>
                                    <p class="text-xs text-slate-600">{{ $notif->message }}</p>
                                    <span class="text-[10px] text-slate-400 mt-1 block">{{ $notif->time_ago }}</span>
                                </div>
                                <div class="shrink-0 flex flex-col gap-1.5 items-end">
                                    @if($notif->deep_link)
                                        <a href="{{ $notif->deep_link }}" class="button button-light text-xs font-semibold px-2.5 py-1 rounded-lg">
                                            {{ $notif->action_label ?: 'Atender' }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-xs text-slate-400 py-6">No hay alertas activas en este momento.</p>
                        @endforelse
                    </div>
                </div>

                {{-- TAREAS OPERATIVAS DE TURNO --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                        <h3 class="text-sm font-bold text-slate-900">Tareas Operativas Asignadas al Rol</h3>
                        <span class="text-xs text-slate-500 font-mono">{{ $activeRole }}</span>
                    </div>

                    <div class="space-y-3">
                        @forelse($operationalTasks as $task)
                            <div class="p-3.5 rounded-xl border border-slate-200 bg-white shadow-xs flex items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="text-xs font-bold text-slate-900">{{ $task->title }}</span>
                                        <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded {{ $task->priority === 'critical' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">{{ strtoupper($task->priority) }}</span>
                                    </div>
                                    <p class="text-xs text-slate-500">{{ $task->description ?: 'Atencion requerida en rampa de operaciones.' }}</p>
                                    <div class="flex items-center gap-3 text-[10px] text-slate-400 mt-1">
                                        <span>Vencimiento: {{ $task->due_time_formatted }}</span>
                                        @if($task->reference_code)
                                            <span class="font-mono text-indigo-600 font-bold">Ref: {{ $task->reference_code }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="px-2 py-1 rounded text-xs font-mono font-bold {{ $task->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ strtoupper($task->status) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-center text-xs text-slate-400 py-6">No hay tareas pendientes asignadas a tu rol.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    @elseif($currentTab === 'mensajeria')
        {{-- ===================================================================== --}}
        {{-- PESTAÑA 4: MENSAJERÍA INTERDEPARTAMENTAL                             --}}
        {{-- ===================================================================== --}}
        <div class="space-y-6">
            {{-- RESUMEN DE CANALES --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('portal.governance', ['tab' => 'mensajeria', 'channel' => 'almacen']) }}" class="p-4 rounded-xl border {{ $chatChannel === 'almacen' ? 'border-indigo-500 bg-indigo-50/30' : 'border-slate-200 bg-white' }} shadow-xs block hover:border-slate-300">
                    <span class="text-xs font-mono font-bold text-slate-600 block"># almacen-compras</span>
                    <div class="text-xl font-black text-slate-900 mt-1">{{ $chatSummary['almacen'] ?? 0 }} pendientes</div>
                    <span class="text-[11px] text-slate-500 mt-0.5 block">Almacen &amp; Proveedores</span>
                </a>
                <a href="{{ route('portal.governance', ['tab' => 'mensajeria', 'channel' => 'ventas']) }}" class="p-4 rounded-xl border {{ $chatChannel === 'ventas' ? 'border-indigo-500 bg-indigo-50/30' : 'border-slate-200 bg-white' }} shadow-xs block hover:border-slate-300">
                    <span class="text-xs font-mono font-bold text-slate-600 block"># atencion-ventas</span>
                    <div class="text-xl font-black text-slate-900 mt-1">{{ $chatSummary['ventas'] ?? 0 }} pendientes</div>
                    <span class="text-[11px] text-slate-500 mt-0.5 block">CRM &amp; Soporte D2C</span>
                </a>
                <a href="{{ route('portal.governance', ['tab' => 'mensajeria', 'channel' => 'logistica']) }}" class="p-4 rounded-xl border {{ $chatChannel === 'logistica' ? 'border-indigo-500 bg-indigo-50/30' : 'border-slate-200 bg-white' }} shadow-xs block hover:border-slate-300">
                    <span class="text-xs font-mono font-bold text-slate-600 block"># logistica-guias</span>
                    <div class="text-xl font-black text-slate-900 mt-1">{{ $chatSummary['logistica'] ?? 0 }} pendientes</div>
                    <span class="text-[11px] text-slate-500 mt-0.5 block">Fleteros &amp; Manifiestos</span>
                </a>
                <a href="{{ route('portal.governance', ['tab' => 'mensajeria', 'channel' => 'general']) }}" class="p-4 rounded-xl border {{ $chatChannel === 'general' ? 'border-indigo-500 bg-indigo-50/30' : 'border-slate-200 bg-white' }} shadow-xs block hover:border-slate-300">
                    <span class="text-xs font-mono font-bold text-slate-600 block"># revops-general</span>
                    <div class="text-xl font-black text-slate-900 mt-1">{{ $chatSummary['general'] ?? 0 }} pendientes</div>
                    <span class="text-[11px] text-slate-500 mt-0.5 block">Avisos de Direccion</span>
                </a>
            </div>

            {{-- TIMELINE DE MENSAJES DEL CANAL SELECCIONADO --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Timeline Oficial: #{{ $chatChannel }}</h3>
                        <p class="text-xs text-slate-500">Trazabilidad formal de notas operativas y resolucion de incidencias.</p>
                    </div>
                    <button type="button" class="button button-light text-xs font-semibold px-3 py-1.5" onclick="document.querySelector('[data-action=\'toggle-chat\']')?.click()">
                        Abrir Cajon Flotante
                    </button>
                </div>

                <div class="space-y-3">
                    @forelse($chatData['messages'] as $msg)
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold font-mono text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($msg['sender'] ?? 'OP', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="text-xs font-bold text-slate-900">{{ $msg['sender'] }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">({{ $msg['role'] }})</span>
                                        <span class="text-[10px] text-slate-400">&bull; {{ $msg['time'] }}</span>
                                        @if(($msg['priority'] ?? 'normal') === 'urgent')
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Urgente</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-700">{{ $msg['text'] }}</p>
                                    @if(!empty($msg['tag']))
                                        <span class="inline-block mt-1 px-2 py-0.5 rounded bg-indigo-50 border border-indigo-200 text-indigo-700 font-mono text-[10px] font-bold">
                                            {{ $msg['tag'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                @if(!empty($msg['is_resolved']))
                                    <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Resuelto</span>
                                @else
                                    <span class="text-[11px] font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">Pendiente</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-xs text-slate-400 py-8">No hay notas registradas en este canal.</p>
                    @endforelse
                </div>
            </div>
        </div>

    @elseif($currentTab === 'ajustes-entorno')
        {{-- ===================================================================== --}}
        {{-- PESTAÑA 5: AJUSTES GLOBALES & ENTORNO (TASK-SYS-ENV)                 --}}
        {{-- ===================================================================== --}}
        <div class="space-y-6">
            {{-- CONMUTADOR VISUAL DE ENTORNO --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-slate-200">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-3 h-3 rounded-full bg-amber-400 animate-pulse"></span>
                            <span class="text-xs font-mono font-bold uppercase text-amber-700">Entorno del Sistema: Sandbox Activo</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Conmutador de Entorno de Ejecucion (TASK-SYS-ENV)</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                            En modo SANDBOX todos los cargos bancarios, cotizaciones de guias, dispersion SPEI y facturas SAT se envian a simuladores seguros con dinero ficticio. En PRODUCCION se conectan las APIs reales.
                        </p>
                    </div>
                </div>

                {{-- GRILLA DE MODULOS Y SU ENTORNO INDIVIDUAL --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
                    @foreach($envConfigs as $cfg)
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-mono font-bold uppercase text-slate-700">Modulo: {{ $cfg->module }}</span>
                                    @if($cfg->active_environment === 'production')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">PRODUCCION REAL</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">SANDBOX</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 mb-3">
                                    {{ $cfg->active_environment === 'production' ? 'Procesando transacciones monetarias reales.' : 'Aislado contra transacciones reales. Pruebas sin riesgo.' }}
                                </p>
                            </div>

                            <form method="POST" action="{{ route('portal.governance.environment.update') }}" class="mt-2">
                                @csrf
                                <input type="hidden" name="module" value="{{ $cfg->module }}">
                                <input type="hidden" name="target_environment" value="{{ $cfg->active_environment === 'sandbox' ? 'production' : 'sandbox' }}">
                                <button 
                                    type="submit" 
                                    class="w-full text-xs font-semibold py-1.5 px-3 rounded-lg border transition-colors {{ $cfg->active_environment === 'sandbox' ? 'bg-white text-slate-800 border-slate-300 hover:bg-slate-100' : 'bg-amber-600 text-white border-amber-700 hover:bg-amber-700' }}"
                                >
                                    Conmutar a {{ $cfg->active_environment === 'sandbox' ? 'PRODUCCION' : 'SANDBOX' }}
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- BITÁCORA DE CONMUTACIONES DE ENTORNO --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <h4 class="text-sm font-bold text-slate-900 mb-3">Historial de Conmutaciones de Entorno</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-mono tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="p-3">Fecha &amp; Hora</th>
                                <th class="p-3">Modulo</th>
                                <th class="p-3">De</th>
                                <th class="p-3">A</th>
                                <th class="p-3">IP Origen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-mono">
                            @forelse($envAuditLogs as $envLog)
                                <tr>
                                    <td class="p-3 text-slate-500">{{ $envLog->created_at ? $envLog->created_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                    <td class="p-3 font-bold text-slate-800">{{ $envLog->module }}</td>
                                    <td class="p-3 text-slate-500">{{ strtoupper($envLog->from_environment) }}</td>
                                    <td class="p-3 font-bold {{ $envLog->to_environment === 'production' ? 'text-rose-600' : 'text-amber-600' }}">{{ strtoupper($envLog->to_environment) }}</td>
                                    <td class="p-3 text-slate-400">{{ $envLog->ip_address ?: '127.0.0.1' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-slate-400">No hay registros de cambio de entorno recientes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
