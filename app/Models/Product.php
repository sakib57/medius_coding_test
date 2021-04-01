<?php

namespace App\Models;
use App\Models\ProductVariantPrice;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function productVariantPrice(){
        return $this->hasOne(ProductVariantPrice::class);
    }



}
