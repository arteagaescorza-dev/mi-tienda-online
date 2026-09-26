@extends('layouts.app')

@section('title','Datos de envio')

@section('body_class', 'landing-page')

@section('content')
    <section class="page-hero">
        <p class="page-kicker">Checkout</p>
        <h2>Datos de envio</h2>
        <p>Completa la informacion para preparar tu pedido.</p>
    </section>

    @include('partials.checkout-progress')

    <section class="store-section contact-layout checkout-page-layout">
        <article class="checkout-form-shell">
            <h3 class="checkout-page-title">Datos de envio y contacto</h3>
            <form class="stack" data-checkout-form>
                <div class="alert alert-error hidden" data-checkout-error role="alert"></div>
                <div class="alert alert-success hidden" data-checkout-success role="status"></div>

                <section class="checkout-form-section" aria-labelledby="checkout-contact-title">
                    <h4 id="checkout-contact-title">1. Informacion de contacto y cliente</h4>
                    <div>
                        <label for="checkout-name">Nombre completo</label>
                        <input id="checkout-name" name="name" type="text" placeholder="Nombre y apellidos" required maxlength="255">
                    </div>
                    <div>
                        <label for="checkout-email">Correo electronico</label>
                        <input id="checkout-email" name="email" type="email" placeholder="tu@email.com" required maxlength="255">
                    </div>
                    <div class="checkout-field-grid">
                        <div>
                            <label for="checkout-mobile">Numero de movil</label>
                            <input id="checkout-mobile" name="mobile" type="tel" inputmode="tel" placeholder="10 digitos" required maxlength="30">
                        </div>
                        <div>
                            <label for="checkout-birth-date">Fecha de nacimiento</label>
                            <input id="checkout-birth-date" name="birth_date" type="date" required>
                        </div>
                    </div>
                </section>

                <section class="checkout-form-section" aria-labelledby="checkout-address-title">
                    <h4 id="checkout-address-title">2. Direccion de entrega</h4>
                    <div>
                        <label for="checkout-street">Calle</label>
                        <input id="checkout-street" name="street" type="text" placeholder="Nombre de la calle" required maxlength="255">
                    </div>
                    <div class="checkout-field-grid">
                        <div>
                            <label for="checkout-exterior-number">Numero exterior</label>
                            <input id="checkout-exterior-number" name="exterior_number" type="text" placeholder="123" required maxlength="30">
                        </div>
                        <div>
                            <label for="checkout-interior-number">Numero interior</label>
                            <input id="checkout-interior-number" name="interior_number" type="text" placeholder="Depto. 4" maxlength="30">
                        </div>
                    </div>
                    <div class="checkout-field-grid">
                        <div>
                            <label for="checkout-postal-code">Codigo postal</label>
                            <input id="checkout-postal-code" name="postal_code" type="text" inputmode="numeric" pattern="[0-9]{5}" placeholder="64000" required maxlength="5" data-postal-code>
                            <small class="muted" data-postal-status aria-live="polite"></small>
                        </div>
                        <div>
                            <label for="checkout-neighborhood">Colonia</label>
                            <input id="checkout-neighborhood" name="neighborhood" type="text" placeholder="Colonia" required maxlength="255" data-postal-neighborhood>
                        </div>
                    </div>
                    <div class="checkout-field-grid">
                        <div>
                            <label for="checkout-city">Ciudad o municipio</label>
                            <input id="checkout-city" name="city" type="text" placeholder="Ciudad o municipio" required maxlength="255" data-postal-city>
                        </div>
                        <div>
                            <label for="checkout-state">Estado</label>
                            <input id="checkout-state" name="state" type="text" placeholder="Estado" required maxlength="255" data-postal-state>
                        </div>
                    </div>
                    <div>
                        <label for="checkout-references">Referencias o indicaciones de entrega</label>
                        <textarea id="checkout-references" name="delivery_references" rows="3" maxlength="1000" placeholder="Color de la fachada, entre calles, horario... "></textarea>
                    </div>
                </section>

                <section class="checkout-form-section" aria-labelledby="checkout-delivery-title">
                    <h4 id="checkout-delivery-title">3. Metodo de entrega</h4>
                    <p class="muted checkout-section-help">Cotiza las opciones disponibles para tu codigo postal y elige la que prefieras.</p>
                    <div class="delivery-method-tabs" role="tablist" aria-label="Metodo de entrega">
                        <button class="delivery-method-tab" type="button" role="tab" aria-selected="false" aria-controls="shipping-quote-panel" data-delivery-tab="paqueteria">
                            <strong>Paqueteria</strong>
                            <small>Recibe tu pedido en la direccion indicada.</small>
                        </button>
                        <button class="delivery-method-tab" type="button" role="tab" aria-selected="false" aria-controls="personal-delivery-panel" data-delivery-tab="personal">
                            <strong>Entrega personal</strong>
                            <small>Coordina la entrega directamente con nosotros.</small>
                        </button>
                    </div>
                    <input type="hidden" name="delivery_method" value="" data-delivery-method-input>
                    <div class="shipping-quote-panel hidden" id="shipping-quote-panel" role="tabpanel" data-shipping-quote-panel>
                        <div class="shipping-quote-heading">
                            <div>
                                <h5>Cotiza tu envio</h5>
                                <p class="muted">Usaremos tu codigo postal y los datos del pedido para consultar las opciones.</p>
                            </div>
                            <button class="btn ghost" type="button" data-quote-shipping>Cotizar envio</button>
                        </div>
                        <p class="shipping-quote-status muted" data-quote-shipping-status role="status">Aun no has cotizado el envio.</p>
                        <div class="shipping-quote-results" data-quote-shipping-results aria-live="polite"></div>
                    </div>
                    <div class="personal-delivery-panel hidden" id="personal-delivery-panel" role="tabpanel" data-personal-delivery-panel></div>
                </section>

                <div class="form-actions">
                    <a class="btn ghost" href="/checkout">Volver</a>
                    <button class="btn primary" type="submit" data-checkout-submit>Confirmar pedido</button>
                </div>
            </form>
        </article>

        <article class="about-card checkout-order-summary">
            <h3>Resumen del pedido</h3>
            <div class="checkout-summary-placeholder" data-checkout-summary>
                <p class="muted">Tus productos, cantidades y total apareceran aqui.</p>
                <p class="checkout-summary-total">Total pendiente de calcular</p>
            </div>
            <p class="muted">El pago se habilitara cuando integremos la pasarela correspondiente.</p>
        </article>
    </section>
@endsection
