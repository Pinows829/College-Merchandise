<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    public $timestamps = false;
    protected $fillable = ['college_id', 'name'];

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
