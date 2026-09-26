document.addEventListener('DOMContentLoaded', function () {
    const productList = document.getElementById('product-list');
    const placeholder = `<div class="rounded-[1.75rem] border border-dashed border-[#e7d3ce] bg-[#fff6f3] p-6 text-center text-[#6a594f] shadow-sm">Cargando productos...</div>`;

    if (productList) {
        productList.innerHTML = placeholder;

        fetch('/api/products?per_page=9')
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Error al cargar productos');
                }
                return response.json();
            })
            .then(function (data) {
                const items = data.data || data;
                if (!items || items.length === 0) {
                    productList.innerHTML = `<div class="rounded-[1.75rem] border border-dashed border-[#e7d3ce] bg-[#fff6f3] p-6 text-center text-[#6a594f] shadow-sm">No hay productos disponibles aun.</div>`;
                    return;
                }

                productList.innerHTML = items.map(function (product) {
                    const price = product.price ? `MXN $${parseFloat(product.price).toFixed(2)}` : 'Consultar precio';
                    const subtitle = product.description ? product.description : 'Lenceria elegante y comoda para tu tienda.';

                    return `
                        <article class="group overflow-hidden rounded-[1.75rem] border border-[#f0d6cf] bg-white shadow-[0_18px_45px_rgba(217,158,145,0.12)] transition hover:-translate-y-1 hover:shadow-[0_22px_50px_rgba(217,158,145,0.2)]">
                            <div class="h-56 bg-[radial-gradient(circle_at_top_left,_rgba(194,46,27,0.14),_transparent_55%)]">
                                <div class="flex h-full items-end justify-end p-4">
                                    <span class="rounded-full bg-[#ffebe8] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#c22e1b]">Nuevo</span>
                                </div>
                            </div>
                            <div class="p-5 sm:p-6">
                                <h3 class="text-lg font-semibold text-[#231d1a]">${product.name}</h3>
                                <p class="mt-2 text-sm leading-6 text-[#6b5750]">${subtitle}</p>
                                <div class="mt-5 flex items-center justify-between gap-4">
                                    <span class="text-lg font-semibold text-[#c22e1b]">${price}</span>
                                    <button class="inline-flex items-center rounded-full bg-[#f6e3e0] px-4 py-2 text-sm font-semibold text-[#7f4b42] transition hover:bg-[#f0d0c8]">Agregar</button>
                                </div>
                            </div>
                        </article>
                    `;
                }).join('');
            })
            .catch(function () {
                productList.innerHTML = `<div class="rounded-[1.75rem] border border-dashed border-[#e7d3ce] bg-[#fff6f3] p-6 text-center text-[#6a594f] shadow-sm">No se pudo cargar el catalogo. Intenta recargar la pagina.</div>`;
            });
    }
});
