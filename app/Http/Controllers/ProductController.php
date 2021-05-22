<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
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
        //dd($products);
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

    public function file_upload(){

     }
    public function store(Request $request)
    {
        // Store product
        $product = new Product;
        $product->title = $request->title;
        $product->sku = $request->sku;
        $product->description = $request->description;
        $product->save();
        $product_id = $product->id;
        $new_variants = [];
        $colors = [];
        $sizes = [];
        $styles = [];
        $i = 1;

        // Store product images
        foreach($request->product_image as $image){
            $product_image = new ProductImage;
            $product_image->product_id = $product_id;
            $product_image->file_path = $image;
            $product_image->save();
        }

        // Store product variants
        foreach($request->product_variant as $variant){
            foreach($variant["tags"] as $tag){
                $product_variant = new ProductVariant;
                $product_variant->variant = $tag;
                $product_variant->variant_id = $variant["option"];
                $product_variant->product_id = $product_id;
                $product_variant->save();

                if($variant["option"] == 1){
                    $colors [$i] = $product_variant->id;
                }
                if($variant["option"] == 2){
                    $sizes [$i] = $product_variant->id;
                }
                if($variant["option"] == 6){
                    $styles [$i] = $product_variant->id;
                }
                $i++;
            }
        }
        // Prepare array to make combination
        if(sizeof($colors) > 0){
            array_push($new_variants,$colors);
        }
        if(sizeof($sizes) > 0){
            array_push($new_variants,$sizes);
        }
        if(sizeof($styles) > 0){
            array_push($new_variants,$styles);
        }
        // Make combinations
        $combined_variants = $this->make_combinations($new_variants);

        // Insert product variation prices & stocks
        foreach($combined_variants as $key=>$items_data)
        {
            $items = array_values($items_data);
            $product_variant_price = new ProductVariantPrice;
            $product_variant_price->product_variant_one = isset($items[0]) ? $items[0] : Null;
            $product_variant_price->product_variant_two = isset($items[1]) ? $items[1] : Null;
            $product_variant_price->product_variant_three = isset($items[2]) ? $items[2] : Null;
            $product_variant_price->price = $request->product_variant_prices[$key]['price'];
            $product_variant_price->stock = $request->product_variant_prices[$key]['stock'];
            $product_variant_price->product_id = $product_id;

            $product_variant_price->save();
        }
        return response()->json([
            'status'=> 'created'
        ], 200);
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
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
        
    }
    public function productEdit($product_id)
    {
        $product = Product::where('id',$product_id)
        //->with('productVariant')
        ->with('productVariantPrice.productVariantOne'
        ,'productVariantPrice.productVariantTwo'
        ,'productVariantPrice.productVariantThree')
        ->first();
        //dd($product);
        return response()->json([
            'product'=> $product
        ], 200);
        //return view('products.edit', compact('variants'));
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
    public function productUpdate(Request $request)
    {
        //dd($request->product_variant_prices);
        Product::where('id', $request->id)
            ->update(
            [
              'title' => $request->title,
              'sku' => $request->sku,
              'description' => $request->description,
            ]
            );
        foreach($request->product_variant_prices as $value){
            // echo '<pre>';
            // print_r($value['id']);
            ProductVariantPrice::where('id', $value['id'])
            ->update(
            [
              'price' => $value['price'],
              'stock' => $value['stock']
            ]
            );
        }
        //die();
        return response()->json([
            'status'=> 'updated'
        ], 200);
        //dd($request->product_variant_prices);
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

    public function make_combinations($arrays) {
        $result = array(array());
        foreach ($arrays as $property => $property_values) {
            $tmp = array();
            foreach ($result as $result_item) {
                foreach ($property_values as $property_key => $property_value) {
                    $tmp[] = $result_item + array($property_key => $property_value);
                }
            }
            $result = $tmp;
        }
        return $result;
    }

    public function imageUpload(Request $request){
        if($request->file('file')){
            $file =$request->file('file');
            $extension = $file->getClientOriginalExtension();
            $file_name = time().rand(100,999).'.'.$extension;
            

            $destinationPath = public_path('/uploads');
            $success = $file->move($destinationPath, $file_name);


            if($success){
                return $file_name;
            }
        }else{
            return response()->json([
                'mesage' => 'upload failed'
            ], 503);
        }
    }
}
