@extends('layouts.app')

@section('title', 'Products')

@section('content')
<section class="page-intro"><div><p class="eyebrow">CATALOG</p><h1>Products</h1><p class="muted">Manage product details, course collections, and inventory.</p></div><a class="btn" href="{{ route('admin.products.create') }}">＋ Add product</a></section>
<section class="panel">
    <form class="admin-search" method="get" action="{{ route('admin.products.index') }}"><input name="q" value="{{ request('q') }}" placeholder="Search products or courses"><button type="submit">Search</button></form>
    @if($products->isEmpty())<div class="empty-state"><span>📦</span><b>No products found</b><p>Add a product or adjust your search.</p><a class="btn" href="{{ route('admin.products.create') }}">Add product</a></div>@else
    <div class="table-wrap"><table><thead><tr><th>Product</th><th>College / course</th><th>Category</th><th>Price</th><th>Stock</th><th></th></tr></thead><tbody>@foreach($products as $product)<tr><td><b>{{ $product->name }}</b></td><td>{{ $product->college->code }}@if($product->course)<br><small>{{ $product->course->name }}</small>@endif</td><td>{{ $product->category }}</td><td>₱{{ number_format((float)$product->price,2) }}</td><td>{{ $product->sizes_sum_stock ?? 0 }} units</td><td><a class="btn btn-small alt" href="{{ route('admin.products.edit',$product) }}">Edit</a></td></tr>@endforeach</tbody></table></div>
    {{ $products->links() }}@endif
</section>
@endsection
