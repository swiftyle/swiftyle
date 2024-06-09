<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;
    protected $table ='sizes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'stock',
    ];

    protected $dates = [ 'created_at', 'updated_at'];
    public function products()
    {
        return $this->belongsToMany(Product::class,'product_size');
    }
}