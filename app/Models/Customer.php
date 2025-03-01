<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;
    
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
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
}