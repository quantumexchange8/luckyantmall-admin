<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SelectOptionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('locale/{locale}', function ($locale) {
    App::setLocale($locale);
    Session::put("locale", $locale);

    return redirect()->back();
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/getCountries', [SelectOptionController::class, 'getCountries'])->name('getCountries');
    Route::get('/getUsers', [SelectOptionController::class, 'getUsers'])->name('getUsers');
    Route::get('/getAvailableLeader', [SelectOptionController::class, 'getAvailableLeader'])->name('getAvailableLeader');
    Route::get('/getSettingRanks', [SelectOptionController::class, 'getSettingRanks'])->name('getSettingRanks');

    Route::get('/getPendingCounts', [DashboardController::class, 'getPendingCounts'])->name('dashboard.getPendingCounts');

    /**
     * ==============================
     *           Customers
     * ==============================
     */
    Route::prefix('customer')->group(function () {
        // listing
        Route::get('/listing', [CustomerController::class, 'index'])->name('customer.listing');
        Route::get('/getCustomersData', [CustomerController::class, 'getCustomersData'])->name('customer.getCustomersData');

        Route::post('/addNewCustomer', [CustomerController::class, 'addNewCustomer'])->name('customer.addNewCustomer');
    });

    /**
     * ==============================
     *            Groups
     * ==============================
     */
    Route::prefix('group')->group(function () {
        // listing
        Route::get('/', [GroupController::class, 'index'])->name('group');
        Route::get('/getGroupsData', [GroupController::class, 'getGroupsData'])->name('group.getGroupsData');

        Route::post('/addGroup', [GroupController::class, 'addGroup'])->name('group.addGroup');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
