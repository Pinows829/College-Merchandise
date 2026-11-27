@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<section class="page-intro"><div><p class="eyebrow">ONE LAST STEP</p><h1>Checkout</h1><p class="muted">Confirm your order and payment method.</p></div></section>
<div class="card checkout-card">
    @if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
    <h2>Order summary</h2>
    @foreach($lines as $line)@php($size = $line['size'])@php($qty = $line['quantity'])<div class="row"><span>{{ $size->product->name }} ({{ $size->size }}) × {{ $qty }}</span><b style="margin-left:auto">₱{{ number_format((float)$size->product->price * $qty, 2) }}</b></div>@endforeach
    <h3><span>Total</span><span>₱{{ number_format($total, 2) }}</span></h3>
    <form method="post" action="{{ route('checkout.place') }}">@csrf
        <label>Payment method</label><select name="payment_method" id="payment-method"><option value="GCash">GCash</option><option value="Cash on Pickup">Cash on Pickup</option></select>
        <label>GCash reference number (required for GCash)</label><input name="gcash_ref" value="{{ old('gcash_ref') }}" maxlength="100">
        <button class="full" type="submit">Place order</button>
    </form>
</div>
@endsection
