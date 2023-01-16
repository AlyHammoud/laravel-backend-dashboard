<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductInfo extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'quantity', 'size', 'color', 'sale'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
