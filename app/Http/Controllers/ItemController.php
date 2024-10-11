<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Str;

class ItemController extends Controller
{
    public function index()
    {
        return Inertia::render('Item/ItemListing', [
            'itemCounts' => Item::count(),
            'locales' => config('app.setting_locales'),
        ]);
    }

    public function getItemsData(Request $request)
    {
        $items = Item::withCount('categories')
            ->get();

        return response()->json([
            'items' => $items
        ]);
    }

    public function addItem(Request $request)
    {
        $locales = $request->input('locales', []);

        $rules = [
            'locales' => ['required', 'array'],
            'locales.*' => ['in:' . implode(',', config('app.setting_locales'))],
            'name_translations' => ['required', 'array'],
        ];

        foreach ($locales as $locale) {
            $rules["name_translations.$locale"] = ['required'];
        }

        $attributeNames = [
            'locales' => trans('public.languages'),
            'name_translations.*' => trans('public.name'),
        ];

        $validator = Validator::make($request->all(), $rules)
            ->setAttributeNames($attributeNames);

        $validator->validate();

        Item::create([
            'name' => $request->name_translations,
            'slug' => Str::slug($request->name_translations['en']),
        ]);

        return back()->with('toast', [
            'title' => trans('public.success'),
            'message' => trans('public.toast_create_item_success'),
            'type' => 'success',
        ]);
    }

    public function updateItemStatus(Request $request)
    {
        $item = Item::find($request->id);

        $item->status = $item->status == 'active' ? 'inactive' : 'active';
        $item->save();

        return back()->with('toast', [
            'title' => trans("public.success"),
            'message' => $item->status == 'active' ? trans("public.toast_item_has_activated") : trans("public.toast_item_has_deactivated"),
            'type' => 'success',
        ]);
    }
}
