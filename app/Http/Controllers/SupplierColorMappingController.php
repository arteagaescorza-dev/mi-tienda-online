<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\SupplierColorMapping;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierColorMappingController extends Controller
{
    public function index(): View
    {
        $localColors = ProductVariant::query()
            ->get()
            ->flatMap(fn ($variant) => [data_get($variant->attributes, 'color')])
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('portal.supplier-colors', [
            'suppliers' => Supplier::query()->orderBy('name')->get(['id', 'name']),
            'mappings' => SupplierColorMapping::query()
                ->with('supplier')
                ->orderBy('supplier_id')
                ->orderBy('external_color')
                ->get(),
            'localColors' => $localColors,
        ]);
    }

    public function store(Request $request, Supplier $supplier): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'external_color' => ['required', 'string', 'max:100'],
            'local_color' => ['required', 'string', 'max:100'],
        ]);

        $mapping = SupplierColorMapping::updateOrCreate(
            ['supplier_id' => $supplier->id, 'external_color' => $validated['external_color']],
            ['local_color' => $validated['local_color']]
        );

        if ($request->expectsJson()) {
            return response()->json(['mapping' => $mapping], 201);
        }

        return redirect()->route('portal.suppliers.colors')->with('success', 'Regla de color guardada correctamente.');
    }
}