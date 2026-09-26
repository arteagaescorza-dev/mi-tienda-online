@extends('layouts.portal-panel')

@php($isLogisticsModule = in_array(request()->route('module'), ['logistica', 'garantias']))

@section('title', $module['title'])
@section('hub_kicker', $isLogisticsModule ? 'Hub 6' : 'Hub 3')
@section('hub_title', ($isLogisticsModule ? 'Logistica & Post-Venta' : 'Marketing & CRM') . ' · ' . $module['title'])
@section('hub_status','Operativo')

@section('content')
    <section class="page-hero">
        <p class="page-kicker">Portal privado</p>
        <h2>{{ $module['title'] }}</h2>
        <p>{{ $module['description'] }}</p>
    </section>

    <section class="store-section portal-module-overview">
        <div class="portal-module-heading">
            <div>
                <p class="portal-module-eyebrow">Modulo administrativo</p>
                <h3>{{ $module['headline'] }}</h3>
            </div>
            <span class="portal-status-badge">{{ $module['status'] }}</span>
        </div>

        <div class="portal-kpi-grid">
            @foreach($module['kpis'] as $kpi)
                <article class="card portal-kpi-card">
                    <p class="muted">{{ $kpi['label'] }}</p>
                    <h3>{{ $kpi['value'] }}</h3>
                    <small class="muted">{{ $kpi['note'] }}</small>
                </article>
            @endforeach
        </div>
    </section>

    <section class="store-section portal-module-workspace">
        <article class="card">
            <div class="portal-panel-header">
                <div>
                    <p class="portal-module-eyebrow">Vista operativa</p>
                    <h3>{{ $module['table_title'] }}</h3>
                </div>
                <button class="btn ghost" type="button" disabled>{{ $module['action'] }}</button>
            </div>
            <div class="portal-empty-state">
                <div class="portal-empty-mark" aria-hidden="true">{{ $module['initial'] }}</div>
                <h3>Modulo preparado para su implementacion</h3>
                <p class="muted">La pantalla ya tiene estructura visual. La persistencia, filtros y acciones se conectaran en la siguiente fase.</p>
            </div>
        </article>

        <aside class="card portal-module-notes">
            <p class="portal-module-eyebrow">Proximo alcance</p>
            <h3>Elementos previstos</h3>
            <ul class="portal-list">
                @foreach($module['features'] as $feature)
                    <li>{{ $feature }}</li>
                @endforeach
            </ul>
        </aside>
    </section>
@endsection
