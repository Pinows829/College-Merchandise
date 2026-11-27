@extends('layouts.app')

@section('title', 'Order '.$order->order_code)

@section('content')
<a class="page-back" href="{{ route('admin.orders.index') }}">← Back to orders</a>
<section class="panel order-detail"><div class="panel-heading"><div><p class="eyebrow">ORDER DETAILS</p><h2>{{ $order->order_code ?: '#'.$order->id }}</h2></div><span class="status status-{{ \Illuminate\Support\Str::slug($order->status) }}">{{ $order->status }}</span></div>
    <div class="detail-grid"><div><small>Student</small><b>{{ $order->user->full_name }}</b><span>{{ $order->user->student_id }} · {{ $order->user->email }}</span></div><div><small>Payment</small><b>{{ $order->payment_method }}</b><span>{{ $order->gcash_ref ? 'Ref: '.$order->gcash_ref : 'Pickup payment' }}</span></div><div><small>Placed</small><b>{{ $order->created_at?->format('M j, Y · g:i a') }}</b><span>Total ₱{{ number_format((float)$order->total,2) }}</span></div></div>
    <div class="table-wrap"><table><thead><tr><th>Item</th><th>Size</th><th>Qty</th><th>Unit price</th></tr></thead><tbody>@foreach($order->items as $item)<tr><td>{{ $item->productSize->product->name }}</td><td>{{ $item->productSize->size }}</td><td>{{ $item->quantity }}</td><td>₱{{ number_format((float)$item->unit_price,2) }}</td></tr>@endforeach</tbody></table></div>
    <form class="status-form" method="post" action="{{ route('admin.orders.update',$order) }}">@csrf @method('PATCH')<label for="status">Update status</label><select id="status" name="status">@foreach($statuses as $status)<option value="{{ $status }}" @selected($order->status===$status)>{{ $status }}</option>@endforeach</select><button type="submit">Save status</button></form>
</section>
@endsection
