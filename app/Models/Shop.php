<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;
    protected $table = "shops";
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'phone',
        'email',
        'logo',
        'rating',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function coupons()
    {
        return $this->hasMany(ShopCoupon::class);
    }
}