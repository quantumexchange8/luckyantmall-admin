<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Str;

class CategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Category/CategoryListing', [
            'categoryCounts' => Category::count(),
            'locales' => config('app.setting_locales'),
        ]);
    }

    public function getCategoriesData(Request $request)
    {
        $categories = Category::withCount('products')
            ->with('item:id,name')
            ->get();

        return response()->json([
            'categories' => $categories
        ]);
    }

    public function addCategory(Request $request)
    {
        $locales = $request->input('locales', []);

        $rules = [
            'locales' => ['required', 'array'],
            'item_id' => ['required'],
            'locales.*' => ['in:' . implode(',', config('app.setting_locales'))],
            'name_translations' => ['required', 'array'],
        ];

        foreach ($locales as $locale) {
            $rules["name_translations.$locale"] = ['required'];
        }

        $attributeNames = [
            'locales' => trans('public.languages'),
            'item_id' => trans('public.item'),
            'name_translations.*' => trans('public.name'),
        ];

        $validator = Validator::make($request->all(), $rules)
            ->setAttributeNames($attributeNames);

        $validator->validate();

        Category::create([
            'name' => $request->name_translations,
            'item_id' => $request->item_id,
            'slug' => Str::slug($request->name_translations['en']),
        ]);

        return back()->with('toast', [
            'title' => trans('public.success'),
            'message' => trans('public.toast_create_category_success'),
            'type' => 'success',
        ]);
    }

    public function updateCategoryStatus(Request $request)
    {
        $category = Category::find($request->id);

        $category->status = $category->status == 'active' ? 'inactive' : 'active';
        $category->save();

        return back()->with('toast', [
            'title' => trans("public.success"),
            'message' => $category->status == 'active' ? trans("public.toast_category_has_activated") : trans("public.toast_category_has_deactivated"),
            'type' => 'success',
        ]);
    }
}
