<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\User;

class SelectOptionController extends Controller
{
    public function getCountries()
    {
        $countries = Country::select('id', 'name', 'phone_code', 'iso2', 'emoji', 'translations')
            ->get();

        return response()->json($countries);
    }

    public function getUsers()
    {
        $users = User::where('role', 'user')
            ->select('id', 'name', 'username')
            ->get();

        return response()->json($users);
    }
}
