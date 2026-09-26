@extends('layouts.app')

@section('title','Lead')

@section('content')
    <a href="{{ route('leads.index') }}" class="back-link">← Volver</a>
    <div class="card">
        <h2>{{ $lead->name ?? '—' }}</h2>
        <div class="detail-grid">
            <div><strong>Status:</strong> <span class="{{ $lead->status_class }}">{{ $lead->status_label }}</span></div>
            <div><strong>Email:</strong> {{ $lead->email ?? '—' }}</div>
            <div><strong>Teléfono:</strong> {{ $lead->phone ?? '—' }}</div>
            <div><strong>Fuente:</strong> {{ $lead->source ?? '—' }}</div>
            <div><strong>Notas:</strong><div class="muted">{{ $lead->notes ?? '—' }}</div></div>
            <div class="muted">Creado: {{ $lead->created_at->toDayDateTimeString() }}</div>
        </div>
    </div>

    <div class="card">
        <h3>Cambiar estado rápido</h3>
        <form method="POST" action="{{ route('leads.update', $lead) }}">
            @csrf
            @method('PATCH')
            <div class="inline-form">
                <select name="status">
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" {{ $lead->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="primary" type="submit">Actualizar estado</button>
            </div>
        </form>
    </div>
@endsection
