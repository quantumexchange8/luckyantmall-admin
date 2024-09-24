<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index()
    {
        return Inertia::render('Customer/Listing/CustomerListing', [
            'customerCounts' => User::count()
        ]);
    }

    public function getCustomersData(Request $request)
    {
        $user = User::with([
            'country:id,name,emoji',
            'rank:id,name'
        ])->get();

        return response()->json([
            'users' => $user,
            'total_users' => User::count()
        ]);
    }
}
