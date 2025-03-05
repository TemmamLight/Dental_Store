<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = [
        'customer_id',
        'favoriteable_id',
        'favoriteable_type',
    ];
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function favoriteable()
    {
        return $this->morphTo();
    }
    
}