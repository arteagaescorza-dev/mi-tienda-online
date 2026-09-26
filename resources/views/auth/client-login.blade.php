@extends('layouts.app')

@section('title', 'Ingreso Cliente')

@section('content')
    <section class="auth-shell">
        <article class="auth-panel">
            <h2>Ingreso de Cliente</h2>
            <p class="muted">Revisa pedidos, historial de compras y estado de envío en un solo lugar.</p>

            <form class="stack" method="POST" action="#">
                @csrf
                <div class="field-row">
                    <label for="client-email">Correo de compra</label>
                    <input id="client-email" name="email" type="email" placeholder="cliente@email.com" required>
                </div>

                <div class="field-row">
                    <label for="client-password">Contraseña</label>
                    <input id="client-password" name="password" type="password" placeholder="********" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="primary">Entrar a mi cuenta</button>
                </div>
            </form>

            <p class="muted auth-note">Demo UI: en el siguiente paso podemos activar recuperación de contraseña y registro.</p>
        </article>
    </section>
@endsection
