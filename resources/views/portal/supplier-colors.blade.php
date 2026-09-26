@extends('layouts.portal-panel')

@section('title', 'Reglas de color por proveedor')
@section('hub_kicker','Hub 4')
@section('hub_title','Proveedores · Reglas de Color')
@section('hub_status','Operativo')

@section('content')
    <section class="page-hero">
        <p class="page-kicker">Proveedores</p>
        <h2>Reglas de color por proveedor</h2>
        <p>Mapea los nombres externos de color a los colores locales para mantener sincronizaciones correctas.</p>
    </section>

    <section class="store-section">
        <article class="card">
            <h3>Crear regla de color</h3>
            <form method="POST" action="{{ route('portal.suppliers.colors.store', ['supplier' => $suppliers->first()->id ?? 1]) }}" class="stack">
                @csrf
                <div class="field-row">
                    <label for="supplier_id">Proveedor</label>
                    <select id="supplier_id" name="supplier_id" required>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field-row">
                    <label for="external_color">Color externo</label>
                    <input id="external_color" name="external_color" type="text" placeholder="Black" required>
                </div>
                <div class="field-row">
                    <label for="local_color">Color local</label>
                    <input id="local_color" name="local_color" list="local-color-options" placeholder="negro" required>
                    <datalist id="local-color-options">
                        @foreach($localColors as $localColor)
                            <option value="{{ $localColor }}">
                        @endforeach
                    </datalist>
                </div>
                <div class="form-actions">
                    <button class="btn primary" type="submit">Guardar regla</button>
                </div>
            </form>
        </article>
    </section>

    <section class="store-section">
        <article class="card">
            <h3>Reglas registradas</h3>
            @if($mappings->isEmpty())
                <p class="muted">Aun no hay reglas de color para proveedores.</p>
            @else
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>Proveedor</th>
                            <th>Color externo</th>
                            <th>Color local</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mappings as $mapping)
                            <tr>
                                <td>{{ $mapping->supplier->name }}</td>
                                <td>{{ $mapping->external_color }}</td>
                                <td>{{ $mapping->local_color }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </article>
    </section>
@endsection
