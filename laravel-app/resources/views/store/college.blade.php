@extends('layouts.app')

@section('title', $college->code.' Merchandise')

@section('content')
<section class="page-intro"><div><p class="eyebrow">COLLEGE COLLECTION</p><h1>{{ $college->code }}</h1><p class="muted">{{ $college->name }} merchandise for campus and beyond.</p></div></section>
@if($courses->isNotEmpty())
<form class="course-filter" method="get" action="{{ route('colleges.show', $college->code) }}"><label for="course-filter">Shop by course</label><select id="course-filter" name="course" onchange="this.form.submit()"><option value="0">All courses</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected($courseId === $course->id)>{{ $course->name }}</option>@endforeach</select>@if($category)<input type="hidden" name="category" value="{{ $category }}">@endif</form>
@endif
<div class="tabs">@foreach([''=>'All','Shirts'=>'Shirts','Jackets'=>'Jackets','Accessories'=>'Accessories'] as $value=>$label)<a class="btn {{ $category === $value ? '' : 'alt' }}" href="{{ route('colleges.show', ['college'=>$college->code,'course'=>$courseId,'category'=>$value]) }}">{{ $label }}</a>@endforeach</div>
<div class="grid">@forelse($products as $product) @include('store._product-card', ['product' => $product]) @empty
    <div class="empty-state"><span>📦</span><b>No merchandise in this collection yet</b><p>Try another course or category, or check back later.</p></div>
@endforelse</div>
@endsection
