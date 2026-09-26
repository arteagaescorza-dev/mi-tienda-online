@extends('layouts.app')

@section('title','Nuevo Lead')

@section('content')
    <a href="{{ route('leads.index') }}" class="back-link">← Volver</a>
    <div class="field">
        <h2>Crear Lead</h2>
        <form method="POST" action="{{ route('leads.store') }}">
            @csrf
            <div class="field-row">
                <label>Nombre</label>
                <input name="name" />
            </div>
            <div class="field-row">
                <label>Email</label>
                <input name="email" type="email" />
            </div>
            <div class="field-row">
                <label>Teléfono</label>
                <input name="phone" />
            </div>
            <div class="field-row">
                <label>Fuente</label>
                <input name="source" />
            </div>
            <div class="field-row">
                <label>Pipeline</label>
                <select name="status">
                    @foreach(App\Models\Lead::statusOptions() as $key => $label)
                        <option value="{{ $key }}" {{ $key === App\Models\Lead::STATUS_NEW ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field-row">
                <label>Notas</label>
                <textarea name="notes" rows="4"></textarea>
            </div>
            <div class="form-actions">
                <button class="primary" type="submit">Crear</button>
            </div>
        </form>
    </div>
@endsection
