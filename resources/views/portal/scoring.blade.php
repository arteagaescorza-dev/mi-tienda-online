@extends('layouts.portal-panel')

@section('title','Scoring')
@section('hub_kicker','Hub 3')
@section('hub_title','Marketing & CRM · Scoring')
@section('hub_status','Vista Ejecutiva')

@section('content')
    <section class="page-hero">
        <p class="page-kicker">Scoring</p>
        <h2>Lead scoring y priorización</h2>
        <p>Boceto inicial para definir reglas y detectar prospectos con mayor intención de compra.</p>
    </section>

    <section class="store-section portal-two-col">
        <article class="card">
            <h3>Reglas activas</h3>
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Puntaje</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rules as $rule)
                        <tr>
                            <td>{{ $rule['event'] }}</td>
                            <td>{{ $rule['score'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </article>

        <article class="card">
            <h3>Top prospectos</h3>
            <ul class="portal-list portal-leads">
                @foreach($topLeads as $lead)
                    <li>
                        <strong>{{ $lead['name'] }}</strong>
                        <span>{{ $lead['segment'] }}</span>
                        <em>Score {{ $lead['score'] }}</em>
                    </li>
                @endforeach
            </ul>
        </article>
    </section>
@endsection
