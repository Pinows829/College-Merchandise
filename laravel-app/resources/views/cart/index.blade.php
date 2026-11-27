@extends('layouts.app')

@section('title', 'Your cart')

@section('content')
<section class="page-intro"><div><p class="eyebrow">READY WHEN YOU ARE</p><h1>Your cart</h1><p class="muted">Review your items before checkout.</p></div></section>
@if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
@if($lines->isEmpty())
    <div class="empty-state"><span>🛍</span><b>Your cart is waiting for something nice</b><p>Browse college collections and add your favorites.</p><a class="btn" href="{{ route('home') }}">Browse merchandise</a></div>
@else
    <div class="table-wrap"><table><thead><tr><th>Item</th><th>Qty</th><th>Price</th><th></th></tr></thead><tbody>
        @foreach($lines as $line)@php($size = $line['size'])@php($qty = $line['quantity'])
            <tr><td>{{ $size->product->name }} ({{ $size->size }})</td><td>{{ $qty }}@if($qty > $size->stock)<small class="stock-warning">Only {{ $size->stock }} left</small>@endif</td><td>₱{{ number_format((float)$size->product->price * $qty, 2) }}</td><td><form method="post" action="{{ route('cart.destroy', $size->id) }}">@csrf @method('DELETE')<button class="btn-small" type="submit">Remove</button></form></td></tr>
        @endforeach
        <tr><th colspan="2">Total</th><th colspan="2">₱{{ number_format($total, 2) }}</th></tr>
    </tbody></table></div>
    <div class="cart-actions"><a class="btn alt" href="{{ route('home') }}">Continue shopping</a><a class="btn" href="{{ route('checkout') }}">Proceed to checkout</a></div>
@endif
@endsection
