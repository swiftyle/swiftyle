<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartItem extends Model
{
    use HasFactory, SoftDeletes;

    // Pastikan nama tabel sesuai dengan yang ada di basis data
    protected $table = 'cart_item'; // Atur nama tabel di sini
    protected $fillable = ['cart_id', 'product_id', 'quantity', 'price', 'subtotal', 'coupon_id'];

    protected $hidden = [
        'created_at', 'updated_at','deleted_at'
    ];

    public function coupon()
    {
        return $this->belongsTo(ShopCoupon::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}
