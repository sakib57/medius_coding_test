<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
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
        $productVariant = ProductVariant::distinct()->get(['variant','variant_id']);
        //return $products;
        //return $products;
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
        dd($request);
        $title = $request->title;
        $variant = $request->variant;
        $priceFrom = $request->price_from;
        $priceTo = $request->price_to;
        $date = $request->date;
        if(!is_null($title) ){

        }
        //$products = Product::where()






        //$products = Product::with('variants')->paginate(4);
        //$products = Product::where('title', 'like', '%' . $key . '%')->get();
        //return $products;
    }
}
