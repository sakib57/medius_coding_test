<?php

namespace App\Models;
use App\Models\ProductVariantPrice;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function productVariant(){
        return $this->hasMany(ProductVariant::class);
    }

    public function productImage(){
        return $this->hasMany(ProductImage::class);
    }

    public function productVariantPrice(){
        return $this->hasMany(ProductVariantPrice::class);
    }



}
