@php($checkoutSteps = [
    ['label' => 'Carrito', 'url' => route('store.checkout')],
    ['label' => 'Datos', 'url' => route('store.checkout.details')],
    ['label' => 'Pago', 'url' => route('store.checkout.payment')],
    ['label' => 'Confirmacion', 'url' => route('store.checkout.success')],
])

<nav class="checkout-progress" aria-label="Progreso del checkout">
    @foreach($checkoutSteps as $index => $step)
        <a class="checkout-progress-step {{ $index === $currentCheckoutStep ? 'is-active' : '' }}" href="{{ $step['url'] }}" @if($index > $currentCheckoutStep) aria-disabled="true" @endif>
            <span class="checkout-progress-number">{{ $index + 1 }}</span>
            <span>{{ $step['label'] }}</span>
        </a>
    @endforeach
</nav>
