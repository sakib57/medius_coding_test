<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariant;

class ProductVariantPrice extends Model
{
    //use \Awobaz\Compoships\Compoships;

    protected $guarded = [];

    public function productVariantOne(){
        return $this->belongsTo(ProductVariant::class,'product_variant_one','id');
    }
    public function productVariantTwo(){
        return $this->belongsTo(ProductVariant::class,'product_variant_two','id');
    }
    public function productVariantThree(){
        return $this->belongsTo(ProductVariant::class,'product_variant_three','id');
    }
}
