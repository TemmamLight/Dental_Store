<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'customer_id',
        'product_id',
        'quantity',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function getTotalAttribute()
    {
        // التحقق من وجود المنتج قبل الحساب
        if (!$this->product) {
            return 0;
        }
        return $this->quantity * $this->product->price;
    }
}