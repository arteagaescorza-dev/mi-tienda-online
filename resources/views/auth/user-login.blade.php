@extends('layouts.app')

@section('title', 'Ingreso Usuario')

@section('content')
    <section class="auth-shell">
        <article class="auth-panel">
            <h2>Ingreso de Usuario</h2>
            <p class="muted">Accede al panel comercial para gestionar leads, seguimiento y reportes.</p>

            <form class="stack" method="POST" action="#">
                @csrf
                <div class="field-row">
                    <label for="user-email">Correo corporativo</label>
                    <input id="user-email" name="email" type="email" placeholder="equipo@mitienda.com" required>
                </div>

                <div class="field-row">
                    <label for="user-password">Contraseña</label>
                    <input id="user-password" name="password" type="password" placeholder="********" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="primary">Entrar al panel</button>
                </div>
            </form>

            <p class="muted auth-note">Demo UI: conecta este formulario al flujo de autenticación cuando lo implementemos.</p>
        </article>
    </section>
@endsection
