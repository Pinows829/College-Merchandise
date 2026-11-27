<?php

namespace App\Http\Controllers;

use App\Models\ProductSize;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function requireStudent(): void
    {
        abort_unless(auth()->user()->role === 'student', 403);
    }

    public function index()
    {
        $this->requireStudent();
        $cart = session('cart', []);
        $sizes = ProductSize::with(['product.college', 'product.course'])
            ->whereIn('id', array_map('intval', array_keys($cart)))
            ->get()->keyBy('id');

        $lines = collect($cart)->map(function ($qty, $id) use ($sizes) {
            $size = $sizes->get((int) $id);
            return $size ? ['size' => $size, 'quantity' => (int) $qty] : null;
        })->filter()->values();
        $total = $lines->sum(fn ($line) => (float) $line['size']->product->price * $line['quantity']);

        return view('cart.index', compact('lines', 'total'));
    }

    public function store(Request $request)
    {
        $this->requireStudent();
        $validated = $request->validate([
            'size_id' => ['required', 'integer', 'exists:product_sizes,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $size = ProductSize::findOrFail($validated['size_id']);
        $cart = session('cart', []);
        $current = (int) ($cart[$size->id] ?? 0);

        if ($size->stock < 1 || $current >= $size->stock) {
            return back()->withErrors(['stock' => 'That size is currently out of stock.']);
        }

        $cart[$size->id] = min($current + $validated['quantity'], (int) $size->stock);
        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('success', 'Item added to your cart.');
    }

    public function destroy(int $sizeId)
    {
        $this->requireStudent();
        $cart = session('cart', []);
        unset($cart[$sizeId]);
        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('success', 'Item removed from your cart.');
    }
}
