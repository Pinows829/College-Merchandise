@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<section class="page-intro"><div><p class="eyebrow">FULFILLMENT</p><h1>Orders</h1><p class="muted">Review student purchases and keep each order’s status current.</p></div></section>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow">ALL PURCHASES</p><h2>Order history</h2></div></div>
    @if($orders->isEmpty())<div class="empty-state"><span>🧾</span><b>No orders yet</b><p>Student orders will show up here.</p></div>@else<div class="table-wrap"><table><thead><tr><th>Order</th><th>Student</th><th>Date</th><th>Payment</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody>@foreach($orders as $order)<tr><td><b>{{ $order->order_code ?: '#'.$order->id }}</b></td><td>{{ $order->user->full_name }}</td><td>{{ $order->created_at?->format('M j, Y') }}</td><td>{{ $order->payment_method }}</td><td>₱{{ number_format((float)$order->total,2) }}</td><td><span class="status status-{{ \Illuminate\Support\Str::slug($order->status) }}">{{ $order->status }}</span></td><td><a class="btn btn-small alt" href="{{ route('admin.orders.show',$order) }}">Details</a></td></tr>@endforeach</tbody></table></div>{{ $orders->links() }}@endif
</section>
@endsection
