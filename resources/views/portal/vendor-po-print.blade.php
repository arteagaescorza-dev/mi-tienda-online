<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra {{ $order->po_number }} - Mi Tienda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11pt; color: #000; background: #fff; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen p-4 sm:p-8">

    {{-- BARRA DE ACCIÓN SUPERIOR (SOLO PANTALLA) --}}
    <div class="max-w-4xl mx-auto mb-6 bg-white p-4 rounded-xl shadow-xs border border-slate-200 flex items-center justify-between no-print">
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 text-xs font-bold rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200">
                Documento Oficial B2B
            </span>
            <span class="text-xs text-slate-500">Folio: {{ $order->po_number }}</span>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir / Descargar PDF</span>
            </button>
            <button type="button" onclick="window.close()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors cursor-pointer">
                Cerrar
            </button>
        </div>
    </div>

    {{-- DOCUMENTO FORMAL DE ORDEN DE COMPRA --}}
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-slate-200">
        {{-- ENCABEZADO Y LOGOTIPO --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 border-b border-slate-200 pb-8">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-indigo-600 text-white font-black flex items-center justify-center text-base">M</span>
                    <span class="text-xl font-bold tracking-tight text-slate-900">MI TIENDA ONLINE</span>
                </div>
                <p class="text-xs text-slate-500 mt-2">Operaciones Centrales &amp; Cadena de Suministro</p>
                <p class="text-xs text-slate-500">Bodega &amp; Rampa Inbound: Eje Central Lazaro Cardenas #450, CDMX</p>
                <p class="text-xs text-slate-500">RFC: MTO-260901-ABC | contacto@mitienda.com</p>
            </div>
            <div class="text-left sm:text-right">
                <span class="inline-block px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-md {{ $order->priority === 'critical' ? 'bg-rose-100 text-rose-800' : ($order->priority === 'urgent' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                    Prioridad: {{ strtoupper($order->priority) }}
                </span>
                <h1 class="text-2xl font-black text-slate-900 mt-2">{{ $order->po_number }}</h1>
                <p class="text-xs text-slate-500 mt-1">Fecha Emision: {{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : now()->format('d/m/Y') }}</p>
                <p class="text-xs font-semibold text-indigo-700 mt-0.5">Entrega Prometida: {{ $order->expected_delivery_date ? \Carbon\Carbon::parse($order->expected_delivery_date)->format('d/m/Y') : 'Por definir' }}</p>
            </div>
        </div>

        {{-- DATOS DEL PROVEEDOR / TALLER DE CONFECCIÓN --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 my-8 p-5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
            <div>
                <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1">Proveedor / Fabricante Receptor</span>
                <p class="text-sm font-bold text-slate-900">{{ $order->supplier?->name ?? 'Fabricante B2B' }}</p>
                <p class="text-slate-600 mt-1">Atencion: {{ $order->supplier?->contact_name ?? 'Direccion de Taller' }}</p>
                <p class="text-slate-600">Email: {{ $order->supplier?->email ?? 'contacto@proveedor.com' }}</p>
                <p class="text-slate-600">Telefono: {{ $order->supplier?->phone ?? 'Sin telefono registrado' }}</p>
            </div>
            <div>
                <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1">Destino &amp; Condiciones de Entrega</span>
                <p class="text-sm font-bold text-slate-900">Rampa Inbound Bodega CDMX</p>
                <p class="text-slate-600 mt-1">Horario de Recepcion: Lunes a Viernes 08:00 a 16:00 hrs</p>
                <p class="text-slate-600">Condicion de Pago: Contra entrega y cotejo de 3 vias</p>
                <p class="text-slate-600">Estatus Operativo: <span class="font-bold text-slate-800">{{ strtoupper($order->status) }}</span></p>
            </div>
        </div>

        {{-- TABLA DE PARTIDAS Y FICHAS DE CORTE --}}
        <div class="mb-8 overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-200 rounded-lg overflow-hidden">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">SKU / Ficha</th>
                        <th class="py-3 px-4">Prenda / Modelo</th>
                        <th class="py-3 px-4">Talla / Variante</th>
                        <th class="py-3 px-4 text-center">Unidades</th>
                        <th class="py-3 px-4 text-right">Costo Unit.</th>
                        <th class="py-3 px-4 text-right">Importe Bruto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($order->items as $item)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono font-medium text-slate-800">{{ $item->sku }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-900">
                                {{ $item->product?->title ?? $item->product?->name ?? 'Producto Confeccion' }}
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $item->variant?->color ?? '' }} {{ $item->variant?->size ? '('.$item->variant->size.')' : '' }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-900">{{ $item->quantity_ordered }}</td>
                            <td class="py-3 px-4 text-right text-slate-700">${{ number_format((float)$item->unit_cost, 2) }}</td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900">${{ number_format((float)$item->subtotal, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">Sin partidas registradas en el detalle.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- CLAUSULA 4: COMPENSACIÓN POR NOTA DE CRÉDITO (SI APLICA) --}}
        @if((float)$order->credit_deduction_amount > 0 || $order->creditAgreement)
            <div class="mb-8 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                <span class="font-bold uppercase tracking-wider block mb-1">Clausula 4: Compensacion Mercantil por Minuta de Discrepancia</span>
                <p>
                    Se aplica una deduccion de <strong>${{ number_format((float)($order->credit_deduction_amount ?: $order->creditAgreement?->amount), 2) }} MXN</strong> 
                    amparada bajo la minuta de discrepancia <strong>{{ $order->creditAgreement?->reference_folio ?? 'NC-AMPA' }}</strong>. 
                    Motivo formal: {{ $order->creditAgreement?->resolution_notes ?? 'Ajuste compensatorio acordado por discrepancia en rampa inbound.' }}
                </p>
            </div>
        @endif

        {{-- LIQUIDACIÓN Y TOTALES --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-8 pt-4 border-t border-slate-200">
            <div class="max-w-md text-xs text-slate-500 space-y-1">
                <span class="font-bold text-slate-700 uppercase tracking-wider block mb-1">Instrucciones de Recepcion:</span>
                <p>1. Toda entrega debe estar debidamente empacada con etiquetas visibles por talla y modelo.</p>
                <p>2. El transportista debera presentar esta orden impresa o con folio digital junto con su remision.</p>
                <p>3. En caso de piezas con defecto de costura o mermas, se levantara minuta de discrepancia en rampa.</p>
            </div>
            <div class="w-full sm:w-72 bg-slate-50 p-5 rounded-xl border border-slate-200 text-xs space-y-2">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Bruto:</span>
                    <span class="font-semibold">${{ number_format((float)$order->gross_subtotal ?: (float)$order->subtotal, 2) }}</span>
                </div>
                @if((float)$order->credit_deduction_amount > 0)
                    <div class="flex justify-between text-amber-700 font-semibold">
                        <span>Deduccion Minuta NC:</span>
                        <span>-${{ number_format((float)$order->credit_deduction_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-slate-600">
                    <span>Base Gravable:</span>
                    <span class="font-semibold">${{ number_format((float)$order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>IVA Trasladado (16%):</span>
                    <span class="font-semibold">${{ number_format((float)$order->tax_amount, 2) }}</span>
                </div>
                <div class="border-t border-slate-300 pt-2 flex justify-between text-sm font-black text-slate-900">
                    <span>Total a Liquidar:</span>
                    <span class="text-indigo-700">${{ number_format((float)$order->total_amount, 2) }} MXN</span>
                </div>
            </div>
        </div>

        {{-- FIRMAS DE CONFORMIDAD --}}
        <div class="grid grid-cols-2 gap-12 mt-16 pt-8 border-t border-slate-200 text-center text-xs text-slate-600">
            <div>
                <div class="border-b border-slate-400 w-3/4 mx-auto mb-2"></div>
                <p class="font-bold text-slate-900">Emisor: Mi Tienda Online</p>
                <p class="text-slate-400">Compras &amp; Aprovisionamiento</p>
            </div>
            <div>
                <div class="border-b border-slate-400 w-3/4 mx-auto mb-2"></div>
                <p class="font-bold text-slate-900">Aceptacion: {{ $order->supplier?->name ?? 'Taller Confeccion' }}</p>
                <p class="text-slate-400">Firma y Sello de Fabricante</p>
            </div>
        </div>
    </div>

</body>
</html>
