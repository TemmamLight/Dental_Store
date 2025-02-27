<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'slug', 
        'brand_id',
        'description',
        'image',
        'sku',
        'published_at',
        'quantity',
        'price',
        'image',
        'is_featured',
        'is_visible',
        'type', // ['deliverable', 'downloadable']
        'category_id',
        
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
    
    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = convertArabicToEnglishNumbers($value);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class);
    }
    
    public function getTotalSoldAttribute(): int
    {
        return $this->orderItems()->sum('quantity');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function favirites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }
}