<?php

namespace App\Models;
use App\Models\ProductVariantPrice;
use App\Models\ProductImage;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $guarded = [];

    public function productVariantPrice(){
        return $this->hasMany(ProductVariantPrice::class);
    }

    public function productImage(){
        return $this->hasOne(ProductImage::class);
    }

    public function varients(){
        return $this->hasOne(Variant::class);
    }
}
