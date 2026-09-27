@extends('layouts.app')

@section('title', 'Ingreso Usuario')

@section('content')
    <section class="auth-shell">
        <article class="auth-panel">
            <h2>Ingreso de Usuario</h2>
            <p class="muted">Accede al panel comercial para gestionar leads, seguimiento y reportes.</p>

            @if($errors->any())
                <div class="alert alert-error" role="alert" style="color: #dc2626; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px 14px; font-size: 0.875rem; margin-bottom: 1rem;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form class="stack" method="POST" action="{{ route('login') }}">
                @csrf
                <div class="field-row">
                    <label for="user-email">Correo corporativo</label>
                    <input id="user-email" name="email" type="email" value="{{ old('email') }}" placeholder="equipo@mitienda.com" required autofocus>
                </div>

                <div class="field-row">
                    <label for="user-password">Contraseña</label>
                    <input id="user-password" name="password" type="password" placeholder="********" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="primary">Entrar al panel</button>
                </div>
            </form>

            <p class="muted auth-note">Credenciales de acceso al portal RevOps.</p>
        </article>
    </section>
@endsection
