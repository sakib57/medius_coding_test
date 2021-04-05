<?php

namespace App\Models;
use App\Models\ProductVariantPrice;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    //use \Awobaz\Compoships\Compoships;
    protected $guarded = [];

    public function productVariantPrice(){
        return $this->hasMany(ProductVariantPrice::class,'product_variant_one');
    }

    
}
