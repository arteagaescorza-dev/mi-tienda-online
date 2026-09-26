@extends('layouts.app')

@section('title','Leads')

@section('content')
    <div class="toolbar">
        <h2>Leads</h2>
        <div class="actions">
            <a class="btn" href="{{ route('leads.create') }}">Nuevo</a>
            <a class="btn ghost" href="{{ route('leads.board') }}">Ver board</a>
        </div>
    </div>

    <div class="stack">
        @foreach($leads as $lead)
            <a href="{{ route('leads.show', $lead) }}" class="card lead-row">
                <div>
                    <div style="font-weight:600">{{ $lead->name ?? '—' }}</div>
                    <div class="lead-meta">{{ $lead->email ?? $lead->phone ?? '' }}</div>
                </div>
                <div style="text-align:right">
                    <div class="{{ $lead->status_class }}" style="font-weight:700">{{ $lead->status_label }}</div>
                    <div class="muted">{{ $lead->created_at->diffForHumans() }}</div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="form-actions">{{ $leads->links() }}</div>
@endsection
