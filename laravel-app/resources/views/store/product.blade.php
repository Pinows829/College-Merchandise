@extends('layouts.app')

@section('title', $product->name)

@section('content')
<a class="page-back" href="{{ route('colleges.show', $product->college->code) }}">← Back to {{ $product->college->code }}</a>
<section class="product-detail card">
    <div class="detail-visual"><img src="{{ asset('assets/merch-'.strtolower($product->category).'.jpg') }}" alt="{{ $product->name }}" class="detail-photo"><span class="product-category">{{ $product->category }}</span><span class="product-college">{{ $product->college->code }} COLLECTION</span></div>
    <div class="detail-copy"><p class="eyebrow">{{ $product->college->code }}@if($product->course) · {{ $product->course->name }}@endif · {{ $product->category }}</p><h1>{{ $product->name }}</h1><strong class="detail-price">₱{{ number_format((float)$product->price, 2) }}</strong><p class="detail-description">{{ $product->description }}</p>
        @auth
            @if(auth()->user()->role === 'student')
                <form method="post" action="{{ route('cart.store') }}">@csrf
                    <label>Size</label><div class="chips">@php($hasStock = false)@foreach($product->sizes as $size)@php($available = $size->stock > 0)@if($available)@php($hasStock = true)@endif
                        <label><input type="radio" name="size_id" value="{{ $size->id }}" @disabled(!$available) @checked($available && !$product->sizes->where('stock', '>', 0)->where('id', '<', $size->id)->count()) required><span>{{ $size->size }} · {{ $size->stock }} left</span></label>
                    @endforeach</div>
                    <label>Quantity</label><input type="number" name="quantity" min="1" max="20" value="1" required>
                    <button class="full" type="submit" @disabled(!$hasStock)>{{ $hasStock ? 'Add to cart' : 'Out of stock' }}</button>
                </form>
            @else <p class="muted">Administrator view</p> @endif
        @else <a class="btn full" href="{{ route('login') }}">Log in to add to cart</a> @endauth
    </div>
</section>
@endsection
