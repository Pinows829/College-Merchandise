@extends('layouts.app')

@section('title', 'My orders')

@section('content')
<section class="page-intro"><div><p class="eyebrow">YOUR CAMPUS PURCHASES</p><h1>My orders</h1><p class="muted">Follow each order from confirmation to pickup.</p></div><a class="btn alt" href="{{ route('home') }}">Continue shopping</a></section>
@if(session('placed'))<div class="ok">Order {{ session('placed') }} was placed. We’ll update its status here.</div>@endif
@forelse($orders as $order)
    <article class="card order-card"><div class="row"><div><small class="eyebrow">ORDER</small><b>{{ $order->order_code ?: '#'.$order->id }}</b></div><span class="status status-{{ \Illuminate\Support\Str::slug($order->status) }}">{{ $order->status }}</span><span class="muted order-date">{{ $order->created_at?->format('d M Y') }}</span></div>
        <div class="order-lines">@foreach($order->items as $item)@php($size = $item->productSize)<div><span>{{ $size->product->name }}<small>{{ $size->size }} · Qty {{ $item->quantity }}</small></span><b>₱{{ number_format((float)$item->unit_price * $item->quantity, 2) }}</b></div>@endforeach</div>
        <div class="order-total"><span>{{ $order->payment_method }}@if($order->gcash_ref) · Ref {{ $order->gcash_ref }}@endif</span><b>Total ₱{{ number_format((float)$order->total, 2) }}</b></div>
        @php($steps = ['Pending','Paid','Preparing','Ready for Pickup','Completed'])@php($activeStep = array_search($order->status, $steps, true))
        <div class="order-progress">@foreach($steps as $stepIndex => $step)<span class="progress-step {{ $activeStep !== false && $stepIndex <= $activeStep ? 'is-done' : '' }}"><i>{{ $activeStep !== false && $stepIndex < $activeStep ? '✓' : $stepIndex + 1 }}</i><small>{{ $step }}</small></span>@endforeach</div>
    </article>
@empty
    <div class="empty-state"><span>🧾</span><b>No orders yet</b><p>Your orders will appear here after checkout.</p><a class="btn" href="{{ route('home') }}">Browse merchandise</a></div>
@endforelse
@endsection
