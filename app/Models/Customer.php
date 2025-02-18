<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'password',
        'phone_number',
        'email',
        'verification_code',
        'date_of_birth',
        'city',
        'address',
        'photo',
    ];
    
    protected $hidden = [
        'password',
    ];

     // العلاقة مع المبيعات
    // public function sales(): HasMany
    // {
    //     return $this->hasMany(Sale::class);
    // }

    // العلاقة مع المفضلة
    // public function favorites(): HasMany
    // {
    //     return $this->hasMany(Favorite::class);
    // }
}