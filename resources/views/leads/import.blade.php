@extends('layouts.app')

@section('title','Importar Leads')

@section('content')
    <div class="card">
        <h3>Importar Leads desde CSV</h3>
        <p class="muted">El archivo debe tener encabezados como <strong>name,email,phone,source,notes</strong> o equivalentes en español.</p>

        @if($errors->any())
            <div class="alert alert-error">
                <strong>Errores:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('leads.import.process') }}" enctype="multipart/form-data" class="field">
            @csrf
            <div class="field-row">
                <label>Archivo CSV</label>
                <input type="file" name="csv_file" accept=".csv,text/csv" />
            </div>
            <div class="form-actions">
                <button class="primary" type="submit">Subir e importar</button>
            </div>
        </form>
    </div>

    @if(session('import_summary'))
        @php($s = session('import_summary'))
        <div class="card">
            <h4>Resumen de importación</h4>
            <div>Registros creados: <strong>{{ $s['created'] }}</strong></div>
            <div>Registros saltados: <strong>{{ $s['skipped'] }}</strong></div>
            @if(!empty($s['errors']))
                <div class="field-row"><strong>Errores:</strong>
                    <ul>
                        @foreach($s['errors'] as $err)
                            <li class="muted">{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
@endsection
