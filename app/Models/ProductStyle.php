<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductStyle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_style';
    protected $fillable = ['product_id','style_id'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $casts = [
        'product_id' => 'integer',
       'style_id' => 'integer',
    ];
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_id');
    }
    public function styles()
    {
        return $this->belongsToMany(Style::class, 'style_id');
    }
}
