<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public $timestamps = false;
    protected $fillable = ['college_id', 'course_id', 'name', 'category', 'description', 'price'];
    protected $casts = ['price' => 'decimal:2'];

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class);
    }
}
