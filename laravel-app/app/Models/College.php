<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class College extends Model
{
    public $timestamps = false;
    protected $fillable = ['code', 'name'];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
