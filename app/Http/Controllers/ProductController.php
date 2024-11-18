<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBundlePrice;
use App\Models\ProductHasMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Product/ProductListing', [
            'productsCount' => Product::count(),
        ]);
    }

    public function add_product(Request $request)
    {
        return Inertia::render('Product/AddProduct');
    }

    public function addProduct(Request $request)
    {
        Validator::make($request->all(), [
            'name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'description' => ['required'],
            'base_price' => ['required', 'numeric'],
            'discount_type' => ['nullable', 'in:bundle,percent,points'],
            'bundle_price' => ['required_if:discount_type,bundle'],
            'quantity' => ['required'],
            'sku' => ['nullable'],
            'images' => ['required'],
            'category' => ['required'],
            'masters' => ['nullable'],
            'required_delivery' => ['nullable'],
        ])->setAttributeNames([
            'name' => trans('public.name'),
            'description' => trans('public.description'),
            'base_price' => trans('public.base_price'),
            'discount_type' => trans('public.discount_type'),
            'bundle_price' => trans('public.bundle_price'),
            'quantity' => trans('public.quantity'),
            'sku' => trans('public.sku'),
            'images' => trans('public.images'),
            'category' => trans('public.category'),
            'masters' => trans('public.master'),
        ])->validate();

        $category = $request->category;
        $masters = $request->masters;
        $bundles = $request->bundle_price;

        $product = Product::create([
            'name' => $request->name,
            'descriptions' => $request->description,
            'slug' => \Str::slug($request->name),
            'base_price' => $request->base_price,
            'discount_type' => $request->discount_type,
            'quantity' => $request->quantity,
            'sku' => $request->sku,
            'item_id' => $category['item_id'],
            'category_id' => $category['id'],
            'required_delivery' => $request->required_delivery,
        ]);

        if ($masters) {
            foreach ($masters as $master) {
                ProductHasMaster::create([
                    'product_id' => $product->id,
                    'trading_master_id' => $master['id'],
                ]);
            }
        }

        if ($bundles) {
            foreach ($bundles as $bundle) {
                ProductBundlePrice::create([
                    'product_id' => $product->id,
                    'min_quantity' => $bundle['min_unit'],
                    'price_per_unit' => $bundle['price_per_unit'],
                ]);
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $product->addMedia($image)->toMediaCollection('product_images');
            }
        }

        return Redirect::route('product')->with('toast', [
            'title' => trans('public.success'),
            'message' => trans('public.toast_add_product_success'),
            'type' => 'success',
        ]);
    }

    public function getProductData(Request $request)
    {
        $product = Product::with([
            'category:id,name',
            'media',
            'masters',
        ])
            ->withSum('success_orders', 'quantity')
            ->latest()
            ->get();

        return response()->json([
            'products' => $product
        ]);
    }
}
