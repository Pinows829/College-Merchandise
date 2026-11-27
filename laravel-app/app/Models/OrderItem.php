<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    public $timestamps = false;
    protected $fillable = ['order_id', 'product_size_id', 'quantity', 'unit_price'];
    protected $casts = ['unit_price' => 'decimal:2'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function productSize(): BelongsTo
    {
        return $this->belongsTo(ProductSize::class);
    }
}
