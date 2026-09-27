@extends('layouts.app')

@section('title', 'Ingreso Cliente')

@section('content')
    <section class="auth-shell">
        <article class="auth-panel">
            <h2>Ingreso de Cliente</h2>
            <p class="muted">Revisa pedidos, historial de compras y estado de envío en un solo lugar.</p>

            @if($errors->any())
                <div class="alert alert-error" role="alert" style="color: #dc2626; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px 14px; font-size: 0.875rem; margin-bottom: 1rem;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form class="stack" method="POST" action="{{ route('login') }}">
                @csrf
                <div class="field-row">
                    <label for="client-email">Correo de compra</label>
                    <input id="client-email" name="email" type="email" value="{{ old('email') }}" placeholder="cliente@email.com" required autofocus>
                </div>

                <div class="field-row">
                    <label for="client-password">Contraseña</label>
                    <input id="client-password" name="password" type="password" placeholder="********" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="primary">Entrar a mi cuenta</button>
                </div>
            </form>

            <p class="muted auth-note">Acceso a pedidos y compras del cliente.</p>
        </article>
    </section>
@endsection
