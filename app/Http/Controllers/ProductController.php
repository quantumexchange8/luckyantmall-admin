<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBundlePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {

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
            'master' => ['nullable'],
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
            'master' => trans('public.master'),
        ])->validate();

        $category = $request->category;
        $master = $request->master;
        $bundles = $request->bundle_price;

        $product = Product::create([
            'name' => $request->name,
            'descriptions' => $request->description,
            'base_price' => $request->base_price,
            'discount_type' => $request->discount_type,
            'quantity' => $request->quantity,
            'sku' => $request->sku,
            'category_id' => $category['id'],
            'master_id' => $master['id'],
        ]);

        if ($bundles) {
            foreach ($bundles as $bundle) {
                ProductBundlePrice::create([
                    'product_id' => $product->id,
                    'min_quantity' => $bundle['min_unit'],
                    'price_per_unit' => $bundle['price_per_unit'],
                ]);
            }
        }

        return back()->with('toast', [
            'title' => trans('public.success'),
            'message' => trans('public.toast_add_product_success'),
            'type' => 'success',
        ]);
    }
}
