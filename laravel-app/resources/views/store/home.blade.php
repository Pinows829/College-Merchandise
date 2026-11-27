@extends('layouts.app')

@section('title', 'College Merchandise')

@section('content')
<section class="hero-panel"><div class="hero-copy"><p class="eyebrow">YOUR CAMPUS. YOUR PRIDE.</p><h1>Wear your<br><span>college spirit.</span></h1><p>Find official college merchandise made for campus life.</p><a class="btn" href="#catalog">Explore the collection <span aria-hidden="true">→</span></a></div></section>
<form class="search-form" role="search" method="get" action="{{ route('home') }}"><span class="search-icon" aria-hidden="true">⌕</span><input name="q" value="{{ $search }}" placeholder="Search shirts, hoodies, accessories..." aria-label="Search products"><button type="submit">Search</button></form>

@if($search === '')
<div class="section-heading"><div><p class="eyebrow">FIND YOUR COLLECTION</p><h2>Shop by college</h2></div></div>
<div class="college-grid">
    @foreach($colleges as $college)
        <a class="college-tile" href="{{ route('colleges.show', $college->code) }}"><span class="college-emblem">{{ substr($college->code, 0, 1) }}</span><span><b>{{ $college->code }}</b><small>{{ $college->name }}</small></span><span class="tile-arrow">↗</span></a>
    @endforeach
</div>
@endif

<section id="catalog" class="catalog-section"><div class="section-heading"><div><p class="eyebrow">{{ $search ? 'SEARCH' : 'CAMPUS FAVORITES' }}</p><h2>{{ $search ? 'Search results' : 'Featured merchandise' }}</h2></div><span class="result-count">{{ $products->count() }} items</span></div>
    <div class="grid">@forelse($products as $product) @include('store._product-card', ['product' => $product]) @empty
        <div class="empty-state"><span>⌕</span><b>No products found</b><p>{{ $search ? 'Try another search term.' : 'Merchandise will appear here when added by the store team.' }}</p></div>
    @endforelse</div>
</section>
@endsection
