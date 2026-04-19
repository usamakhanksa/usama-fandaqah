<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\CartDraft;
use App\Models\POSChannel;
use App\Models\POSOrder;
use App\Models\POSOrderItem;
use App\Models\POSStore;
use App\Models\POSTransaction;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function stores(): JsonResponse
    {
        return response()->json(POSChannel::query()->where('is_active', true)->get());
    }

    public function products(Request $request): JsonResponse
    {
        $query = Product::query()->with(['brand:id,name', 'category:id,name', 'subCategory:id,name']);
        if ($request->filled('channel_id')) {
            $query->where('p_o_s_channel_id', $request->integer('channel_id'));
        }
        if ($request->filled('category_id')) {
            $query->where('product_category_id', $request->integer('category_id'));
        }
        if ($request->filled('sub_category_id')) {
            $query->where('product_sub_category_id', $request->integer('sub_category_id'));
        }
        if ($request->filled('search')) {
            $s = $request->string('search');
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"));
        }

        return response()->json($query->orderBy('name')->paginate(12));
    }

    public function categories(Request $request): JsonResponse
    {
        $query = ProductCategory::query()->withCount('products');
        if ($request->filled('channel_id')) {
            $query->whereHas('products', fn($q) => $q->where('p_o_s_channel_id', $request->integer('channel_id')));
        }
        return response()->json($query->get());
    }

    public function subCategories(Request $request): JsonResponse
    {
        $query = ProductSubCategory::query()->with('category:id,name')->withCount('products');
        if ($request->filled('category_id')) {
            $query->where('product_category_id', $request->integer('category_id'));
        }
        return response()->json($query->get());
    }

    public function brands(): JsonResponse
    {
        return response()->json(Brand::query()->withCount('products')->orderBy('name')->paginate(10));
    }

    public function services(): JsonResponse
    {
        return response()->json(Service::query()->orderByDesc('id')->paginate(10));
    }

    public function createService(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'show_in_reservation' => ['boolean'],
            'show_in_pos' => ['boolean'],
            'status' => ['required', 'in:active,inactive'],
        ]);
        $service = Service::query()->create([
            'name' => $data['name_en'],
            'name_en' => $data['name_en'],
            'name_ar' => $data['name_ar'],
            'price' => 0,
            'show_in_reservation' => $data['show_in_reservation'] ?? true,
            'show_in_pos' => $data['show_in_pos'] ?? true,
            'status' => $data['status'],
        ]);

        return response()->json($service, 201);
    }

    public function transactions(): JsonResponse
    {
        return response()->json(POSTransaction::query()->latest()->paginate(10));
    }

    public function cart(Request $request): JsonResponse
    {
        $user = $request->user();
        $cart = CartDraft::query()->firstOrCreate(['user_id' => $user->id], ['items' => []]);
        return response()->json($this->cartPayload($cart));
    }

    public function updateCart(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:0'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'store_id' => ['nullable', 'integer', 'exists:p_o_s_stores,id'],
        ]);

        $user = $request->user();
        $cart = CartDraft::query()->firstOrCreate(['user_id' => $user->id], ['items' => []]);
        $items = collect($cart->items ?? []);

        if (! empty($payload['product_id'])) {
            $existingIndex = $items->search(fn($item) => (int) ($item['product_id'] ?? 0) === (int) $payload['product_id']);
            if ($payload['quantity'] === 0) {
                if ($existingIndex !== false) {
                    $items->forget($existingIndex);
                }
            } elseif ($existingIndex === false) {
                $items->push(['product_id' => $payload['product_id'], 'quantity' => $payload['quantity']]);
            } else {
                $item = $items->get($existingIndex);
                $item['quantity'] = $payload['quantity'];
                $items->put($existingIndex, $item);
            }
        }

        $cart->update([
            'items' => $items->values()->all(),
            'customer_name' => $payload['customer_name'] ?? $cart->customer_name,
            'p_o_s_store_id' => $payload['store_id'] ?? $cart->p_o_s_store_id,
        ]);

        return response()->json($this->cartPayload($cart->fresh()));
    }

    public function clearCart(Request $request): JsonResponse
    {
        $cart = CartDraft::query()->firstOrCreate(['user_id' => $request->user()->id], ['items' => []]);
        $cart->update(['items' => []]);
        return response()->json($this->cartPayload($cart->fresh()));
    }

    public function checkout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'payment_method' => ['required', 'in:cash,pos,on_arrival'],
        ]);

        $cart = CartDraft::query()->firstOrCreate(['user_id' => $request->user()->id], ['items' => []]);
        $payload = $this->cartPayload($cart);
        if (count($payload['items']) === 0) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        $order = DB::transaction(function () use ($request, $cart, $payload, $data) {
            $order = POSOrder::query()->create([
                'guest_id' => null,
                'user_id' => $request->user()->id,
                'p_o_s_store_id' => $cart->p_o_s_store_id,
                'customer_name' => $cart->customer_name,
                'amount' => $payload['total'],
                'subtotal' => $payload['subtotal'],
                'tax_amount' => $payload['tax'],
                'discount_amount' => $payload['discount'],
                'status' => 'completed',
                'payment_method' => $data['payment_method'],
            ]);

            foreach ($payload['items'] as $item) {
                POSOrderItem::query()->create([
                    'p_o_s_order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'line_total' => $item['quantity'] * $item['price'],
                ]);
            }

            POSTransaction::query()->create([
                'p_o_s_order_id' => $order->id,
                'transaction_number' => 'POSTRX'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                'customer_name' => $cart->customer_name,
                'cashier_name' => $request->user()->name,
                'payment_method' => $data['payment_method'],
                'total' => $payload['total'],
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            $cart->update(['items' => []]);

            return $order;
        });

        return response()->json($order, 201);
    }

    private function cartPayload(CartDraft $cart): array
    {
        $products = Product::query()->whereIn('id', collect($cart->items ?? [])->pluck('product_id'))->get()->keyBy('id');
        $items = collect($cart->items ?? [])->map(function ($item) use ($products) {
            $product = $products->get($item['product_id']);
            if (! $product) {
                return null;
            }
            return [
                'id' => $product->id,
                'name' => $product->name,
                'thumbnail' => $product->thumbnail,
                'price' => (float) $product->price,
                'old_price' => $product->old_price,
                'quantity' => (int) $item['quantity'],
            ];
        })->filter()->values()->all();

        $subtotal = collect($items)->sum(fn($item) => $item['price'] * $item['quantity']);
        $tax = round($subtotal * 0.12, 2);
        $discount = (float) ($cart->discount_amount ?? 0);

        return [
            'id' => $cart->id,
            'customer_name' => $cart->customer_name,
            'store_id' => $cart->p_o_s_store_id,
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'tax' => $tax,
            'discount' => $discount,
            'total' => round($subtotal + $tax - $discount, 2),
        ];
    }
}
