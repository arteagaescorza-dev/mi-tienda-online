<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Storefront\CartShowRequest;
use App\Http\Requests\Storefront\CartAddItemRequest;
use App\Http\Requests\Storefront\CartSyncRequest;
use App\Http\Requests\Storefront\CartUpdateItemRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function show(CartShowRequest $request): JsonResponse
    {
        $cart = $this->resolveCart($request);

        return response()->json($cart->load('items.variant.product'));
    }

    public function addItem(CartAddItemRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $cart = $this->resolveCart($request);

        $item = $cart->items()->updateOrCreate([
            'variant_id' => $validated['variant_id'],
        ], [
            'quantity' => $validated['quantity'],
            'price' => ProductVariant::findOrFail($validated['variant_id'])->price,
        ]);

        return response()->json($item->load('variant'));
    }

    public function sync(CartSyncRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $cart = DB::transaction(function () use ($request, $validated): Cart {
            $cart = $this->resolveCart($request);
            $cart->items()->delete();

            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::findOrFail($item['variant_id']);
                $cart->items()->create([
                    'variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'price' => $variant->price,
                ]);
            }

            return $cart->load('items.variant.product');
        });

        return response()->json($cart);
    }

    public function updateItem(CartUpdateItemRequest $request, CartItem $item): JsonResponse
    {
        $validated = $request->validated();

        $item->update($validated);

        return response()->json($item->load('variant'));
    }

    public function removeItem(CartItem $item): JsonResponse
    {
        $item->delete();

        return response()->json(['message' => 'Item removed']);
    }

    private function resolveCart(Request $request): Cart
    {
        if ($request->user()) {
            return Cart::firstOrCreate(['user_id' => $request->user()->id], ['session_id' => null]);
        }

        $token = $request->bearerToken() ?? $request->cookie('cart_session_id');
        if (! $token) {
            $token = Str::uuid()->toString();
            cookie()->queue('cart_session_id', $token, 60 * 24 * 30);
        }

        return Cart::firstOrCreate(['session_id' => $token], ['status' => 'active', 'totals' => []]);
    }
}
