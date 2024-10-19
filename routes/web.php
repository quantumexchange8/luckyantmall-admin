<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositProfileController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SelectOptionController;
use App\Http\Controllers\TransactionController;
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
    Route::get('/getItems', [SelectOptionController::class, 'getItems'])->name('getItems');

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
     *         Transactions
     * ==============================
     */
    Route::prefix('transaction')->group(function () {
        /**
         * ==============================
         *        Pending Deposit
         * ==============================
         */
        Route::prefix('pending_deposit')->group(function () {
            // listing
            Route::get('/', [TransactionController::class, 'pending_deposit'])->name('transaction.pending_deposit');
            Route::get('/getRecentApprovals', [TransactionController::class, 'getRecentApprovals'])->name('transaction.getRecentApprovals');
            Route::get('/getPendingDeposits', [TransactionController::class, 'getPendingDeposits'])->name('transaction.getPendingDeposits');

            Route::post('/pendingApproval', [TransactionController::class, 'pendingApproval'])->name('transaction.pendingApproval');
        });

    });

    /**
     * ==============================
     *             Item
     * ==============================
     */
    Route::prefix('item')->group(function () {
        // listing
        Route::get('/listing', [ItemController::class, 'index'])->name('item.listing');
        Route::get('/getItemsData', [ItemController::class, 'getItemsData'])->name('item.getCustomersData');

        Route::post('/addItem', [ItemController::class, 'addItem'])->name('item.addItem');
        Route::patch('/updateItemStatus', [ItemController::class, 'updateItemStatus'])->name('item.updateItemStatus');
    });

    /**
     * ==============================
     *           Category
     * ==============================
     */
    Route::prefix('category')->group(function () {
        // listing
        Route::get('/listing', [CategoryController::class, 'index'])->name('category.listing');
        Route::get('/getCategoriesData', [CategoryController::class, 'getCategoriesData'])->name('category.getCustomersData');

        Route::post('/addCategory', [CategoryController::class, 'addCategory'])->name('category.addCategory');
        Route::patch('/updateCategoryStatus', [CategoryController::class, 'updateCategoryStatus'])->name('category.updateCategoryStatus');
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

    /**
     * ==============================
     *           Settings
     * ==============================
     */
    Route::prefix('settings')->group(function () {
        Route::prefix('deposit_profile')->group(function () {
            // listing
            Route::get('/', [DepositProfileController::class, 'deposit_profile'])->name('deposit_profile');
            Route::get('/getDepositProfileData', [DepositProfileController::class, 'getDepositProfileData'])->name('deposit_profile.getDepositProfileData');

            Route::post('/addDepositProfile', [DepositProfileController::class, 'addDepositProfile'])->name('deposit_profile.addDepositProfile');
            Route::patch('/updateDepositProfileStatus', [DepositProfileController::class, 'updateDepositProfileStatus'])->name('deposit_profile.updateDepositProfileStatus');
        });

    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
