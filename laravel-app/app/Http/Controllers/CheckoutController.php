<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    private function requireStudent(): void
    {
        abort_unless(auth()->user()->role === 'student', 403);
    }

    private function lines(): array
    {
        $cart = session('cart', []);
        $sizes = ProductSize::with('product')->whereIn('id', array_map('intval', array_keys($cart)))->get()->keyBy('id');
        $lines = [];
        foreach ($cart as $id => $quantity) {
            $size = $sizes->get((int) $id);
            if ($size) {
                $lines[] = ['size' => $size, 'quantity' => (int) $quantity];
            }
        }

        return $lines;
    }

    public function form()
    {
        $this->requireStudent();
        $lines = $this->lines();
        if (count($lines) === 0) {
            return redirect()->route('cart.index');
        }
        $total = collect($lines)->sum(fn ($line) => (float) $line['size']->product->price * $line['quantity']);

        return view('checkout', compact('lines', 'total'));
    }

    public function place(Request $request)
    {
        $this->requireStudent();
        $validated = $request->validate([
            'payment_method' => ['required', 'in:GCash,Cash on Pickup'],
            'gcash_ref' => ['required_if:payment_method,GCash', 'nullable', 'string', 'max:100'],
        ]);
        $cart = session('cart', []);
        if (count($cart) === 0) {
            return redirect()->route('cart.index');
        }

        $order = DB::transaction(function () use ($cart, $validated) {
                $rows = [];
                $total = 0;
                foreach ($cart as $sizeId => $quantity) {
                    $size = ProductSize::with('product')->whereKey((int) $sizeId)->lockForUpdate()->first();
                    $quantity = (int) $quantity;
                    if (! $size || $quantity < 1 || $size->stock < $quantity) {
                        throw ValidationException::withMessages(['stock' => 'Stock changed while you were checking out. Review your cart and try again.']);
                    }
                    $rows[] = [$size, $quantity];
                    $total += (float) $size->product->price * $quantity;
                }

                $order = Order::create([
                    'user_id' => auth()->id(),
                    'total' => $total,
                    'payment_method' => $validated['payment_method'],
                    'gcash_ref' => $validated['payment_method'] === 'GCash' ? ($validated['gcash_ref'] ?? '') : '',
                    'status' => 'Pending',
                ]);
                $order->order_code = 'ORD-'.now()->format('Y').'-'.str_pad((string) $order->id, 3, '0', STR_PAD_LEFT);
                $order->save();

                foreach ($rows as [$size, $quantity]) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_size_id' => $size->id,
                        'quantity' => $quantity,
                        'unit_price' => $size->product->price,
                    ]);
                    $size->decrement('stock', $quantity);
                }

                return $order;
        });

        session()->forget('cart');

        return redirect()->route('orders.index')->with('placed', $order->order_code);
    }
}
