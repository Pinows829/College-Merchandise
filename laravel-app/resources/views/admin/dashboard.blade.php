@extends('layouts.app')

@section('title', 'Admin dashboard')

@section('content')
<section class="page-intro"><div><p class="eyebrow">STORE MANAGEMENT</p><h1>Dashboard</h1><p class="muted">A quick view of your campus merchandise store.</p></div><a class="btn" href="{{ route('admin.products.create') }}">＋ Add product</a></section>
<section class="stats-grid">
    <article class="stat-card"><span class="stat-icon">▦</span><span class="stat-label">Products</span><strong>{{ $summary['products'] }}</strong><small>Listed in your store</small></article>
    <article class="stat-card"><span class="stat-icon gold-icon">↗</span><span class="stat-label">Orders</span><strong>{{ $summary['orders'] }}</strong><small>{{ $summary['pending'] }} pending attention</small></article>
    <article class="stat-card"><span class="stat-icon red-icon">₱</span><span class="stat-label">Confirmed sales</span><strong>₱{{ number_format((float)$summary['revenue'], 2) }}</strong><small>Paid and fulfilled orders</small></article>
    <article class="stat-card"><span class="stat-icon">□</span><span class="stat-label">Units in stock</span><strong>{{ $summary['stock'] }}</strong><small>Across all sizes</small></article>
</section>
<div class="admin-columns"><section class="panel"><div class="panel-heading"><div><p class="eyebrow">LATEST ACTIVITY</p><h2>Recent orders</h2></div><a class="text-link" href="{{ route('admin.orders.index') }}">View all →</a></div>
    @if($recent->isEmpty())<div class="empty-state"><span>🧾</span><b>No orders yet</b><p>New student orders will appear here.</p></div>@else<div class="table-wrap"><table><thead><tr><th>Order</th><th>Student</th><th>Date</th><th>Total</th><th>Status</th></tr></thead><tbody>@foreach($recent as $order)<tr><td><a class="text-link" href="{{ route('admin.orders.show',$order) }}">{{ $order->order_code ?: '#'.$order->id }}</a></td><td>{{ $order->user->full_name }}</td><td>{{ $order->created_at?->format('M j, Y') }}</td><td>₱{{ number_format((float)$order->total,2) }}</td><td><span class="status status-{{ \Illuminate\Support\Str::slug($order->status) }}">{{ $order->status }}</span></td></tr>@endforeach</tbody></table></div>@endif
</section><aside class="panel stock-panel"><div class="panel-heading"><div><p class="eyebrow">INVENTORY</p><h2>Low stock</h2></div><a class="text-link" href="{{ route('admin.products.index') }}">Manage →</a></div>
    @if($lowStock->isEmpty())<div class="empty-state compact"><span>✓</span><b>Stock looks good</b><p>No sizes are running low.</p></div>@else<ul class="stock-list">@foreach($lowStock as $size)<li><span><b>{{ $size->product->name }}</b><small>Size {{ $size->size }}</small></span><span class="stock-pill {{ $size->stock <= 2 ? 'stock-critical' : '' }}">{{ $size->stock }} left</span></li>@endforeach</ul>@endif
</aside></div>
@endsection
