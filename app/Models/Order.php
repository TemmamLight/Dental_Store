<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'number',
        'total_price',
        'status',
        'shipping_price',
        'notes',
        'order_type',
    ];


    public function customer() : BelongsTo
    {
        return $this->belongsTo(customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return OrderStatusEnum::tryFrom($this->status)?->label() ?? $this->status;
    }
    
    
}