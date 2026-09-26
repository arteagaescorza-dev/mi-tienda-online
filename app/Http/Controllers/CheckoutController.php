<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'exterior_number' => ['required', 'string', 'max:30'],
            'interior_number' => ['nullable', 'string', 'max:30'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'digits:5'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'delivery_references' => ['nullable', 'string', 'max:1000'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['required', 'string', 'max:30'],
            'birth_date' => ['required', 'date', 'before:today'],
            'delivery_method' => ['required', 'in:paqueteria,personal'],
        ]);

        $order = DB::transaction(function () use ($request, $validated): Order {
            $cart = Cart::query()
                ->where('user_id', $request->user()->id)
                ->where('status', 'active')
                ->with('items.variant')
                ->lockForUpdate()
                ->first();

            if (! $cart || $cart->items->isEmpty()) {
                $this->rejectCart('El carrito esta vacio.');
            }

            foreach ($cart->items as $item) {
                $inventories = Inventory::query()
                    ->where('variant_id', $item->variant_id)
                    ->lockForUpdate()
                    ->get();

                $available = $inventories->sum(fn (Inventory $inventory) => $inventory->quantity - $inventory->reserved);

                if ($available < $item->quantity) {
                    $this->rejectCart("Stock insuficiente para la variante {$item->variant_id}.");
                }

                $remainingQuantity = $item->quantity;

                foreach ($inventories as $inventory) {
                    $reservableQuantity = $inventory->quantity - $inventory->reserved;
                    $reservedQuantity = min($remainingQuantity, $reservableQuantity);

                    if ($reservedQuantity > 0) {
                        $inventory->increment('reserved', $reservedQuantity);
                        $remainingQuantity -= $reservedQuantity;
                    }

                    if ($remainingQuantity === 0) {
                        break;
                    }
                }
            }

            $shippingAddress = [
                'name' => $validated['name'],
                'street' => $validated['street'],
                'exterior_number' => $validated['exterior_number'],
                'interior_number' => $validated['interior_number'] ?? null,
                'neighborhood' => $validated['neighborhood'],
                'postal_code' => $validated['postal_code'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'delivery_references' => $validated['delivery_references'] ?? null,
                'email' => $validated['email'],
                'mobile' => $validated['mobile'],
                'birth_date' => $validated['birth_date'],
                'delivery_method' => $validated['delivery_method'],
            ];

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(12)),
                'user_id' => $request->user()->id,
                'shipping_address' => $shippingAddress,
                'billing_address' => $shippingAddress,
                'total' => $cart->items->sum(fn ($item) => $item->quantity * $item->price),
                'payment_method' => 'pending',
                'created_via' => 'web',
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'variant_id' => $item->variant_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'line_total' => $item->quantity * $item->price,
                ]);
            }

            $cart->update(['status' => 'ordered']);
            $cart->items()->delete();

            return $order->load('items.variant');
        });

        return response()->json(['order' => $order], 201);
    }

    private function rejectCart(string $message): never
    {
        throw new HttpResponseException(response()->json([
            'message' => $message,
            'errors' => ['cart' => [$message]],
        ], 422));
    }
}