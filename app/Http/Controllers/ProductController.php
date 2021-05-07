<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use DB;
//use Illuminate\Contracts\Support\Jsonable;

class ProductController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        $products = Product::paginate(4);
        $productVariant = ProductVariant::groupBy('variant')->get();
        return view('products.index',compact('products'))->with('product_variant',$productVariant);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        dd($request);
        $product = new Product;
        $product->title = $request->title;
        $product->sku = $request->sku;
        $product->description = $request->description;
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //dd($product->id);
        //$products 
        $product = Product::find($product->id)->with('productVariantPrice.productVariants.varients')->first();
        $variants = Variant::all();
        //dd($product);
        return view('products.edit', compact('variants'))->with('product',$product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }

    ////// Custom methods
    public function productFilter(Request $request){
        //dd($request);
        $title = $request->title;
        $variant = $request->variant;
        $priceFrom = $request->price_from;
        $priceTo = $request->price_to;
        $date = $request->date;

        if(
            is_null($title) && is_null($variant) && 
            is_null($priceFrom) && is_null($priceTo) &&
            is_null($date)
        ){
            $products = Product::paginate(4);
        }else{
            $products_query = Product::query();

            if(!is_null($title))
            {
                $products_query->where('products.title','LIKE',"%$title%");
            }
            
            if(!is_null($variant)){
                $products_query->with(['productVariantPrice' => function($query) use ($variant) {
                    $query->where('product_variant_one', $variant)
                    ->orWhere('product_variant_two',$variant)
                    ->orWhere('product_variant_three',$variant);
                }])->whereHas('productVariantPrice', function (Builder $query) use ($variant) {
                    $query->where('product_variant_one', $variant)
                    ->orWhere('product_variant_two',$variant)
                    ->orWhere('product_variant_three',$variant);
                });
            }

            if(!is_null($priceFrom) && !is_null($priceTo)){
                $products_query->with(['productVariantPrice' => function($query) use ($priceFrom,$priceTo) {
                    $query->whereBetween('price', [$priceFrom, $priceTo]);
                }])->whereHas('productVariantPrice', function (Builder $query) use ($priceFrom,$priceTo) {
                    $query->whereBetween('price', [$priceFrom, $priceTo]);
                });
            }else if(is_null($priceFrom) && !is_null($priceTo)){
                $priceFrom = 0;
                $products_query->with(['productVariantPrice' => function($query) use ($priceFrom,$priceTo) {
                    $query->whereBetween('price', [$priceFrom, $priceTo]);
                }])->whereHas('productVariantPrice', function (Builder $query) use ($priceFrom,$priceTo) {
                    $query->whereBetween('price', [$priceFrom, $priceTo]);
                });
            }else if(!is_null($priceFrom) && is_null($priceTo)){
                $priceTo = ProductVariantPrice::max('price');
                $products_query->with(['productVariantPrice' => function($query) use ($priceFrom,$priceTo) {
                    $query->whereBetween('price', [$priceFrom, $priceTo]);
                }])->whereHas('productVariantPrice', function (Builder $query) use ($priceFrom,$priceTo) {
                    $query->whereBetween('price', [$priceFrom, $priceTo]);
                });
            }

            if(!is_null($date)){
                // In products table type of 'created_at' changed from TIMESTAMP to DATE
                $date = date($date);
                $products_query->where('products.created_at',$date);
            }
            $products = $products_query->paginate(4);
        }
        $productVariant = ProductVariant::groupBy('variant')->get();
        return view('products.index',compact('products'))->with('product_variant',$productVariant);
    }
}
