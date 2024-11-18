<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositProfileController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SelectOptionController;
use App\Http\Controllers\TransactionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Redirect::route('login');
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
    Route::get('/getGroupLeaders', [SelectOptionController::class, 'getGroupLeaders'])->name('getGroupLeaders');
    Route::get('/getCategories', [SelectOptionController::class, 'getCategories'])->name('getCategories');
    Route::get('/getMasters', [SelectOptionController::class, 'getMasters'])->name('getMasters');
    Route::get('/getRanks', [SelectOptionController::class, 'getRanks'])->name('getRanks');
    Route::get('/getRoles', [SelectOptionController::class, 'getRoles'])->name('getRoles');

    Route::get('/getPendingCounts', [DashboardController::class, 'getPendingCounts'])->name('dashboard.getPendingCounts');

    /**
     * ==============================
     *           Customers
     * ==============================
     */
    Route::prefix('customer')->group(function () {
        // listing
        Route::get('/listing', [CustomerController::class, 'index'])->name('customer.listing');
        Route::get('/getCustomerOverview', [CustomerController::class, 'getCustomerOverview'])->name('customer.getCustomerOverview');
        Route::get('/getCustomersData', [CustomerController::class, 'getCustomersData'])->name('customer.getCustomersData');

        Route::post('/addNewCustomer', [CustomerController::class, 'addNewCustomer'])->name('customer.addNewCustomer');
        Route::post('/upgradeRank', [CustomerController::class, 'upgradeRank'])->name('customer.upgradeRank');
        Route::post('/upgradeRole', [CustomerController::class, 'upgradeRole'])->name('customer.upgradeRole');

        // details
        Route::prefix('detail')->group(function () {
            Route::get('/{id_number}', [CustomerController::class, 'detail'])->name('customer.detail');
            Route::get('/{id_number}/getUserData', [CustomerController::class, 'getUserData'])->name('customer.detail.getUserData');
            Route::get('/{id_number}/getWalletData', [CustomerController::class, 'getWalletData'])->name('customer.detail.getWalletData');
            Route::get('/{id_number}/getDeliveryAddresses', [CustomerController::class, 'getDeliveryAddresses'])->name('customer.detail.getDeliveryAddresses');
            Route::get('/{id_number}/getRecentTransactionData', [CustomerController::class, 'getRecentTransactionData'])->name('customer.detail.getRecentTransactionData');

            Route::post('/updateCustomerProfile', [CustomerController::class, 'updateCustomerProfile'])->name('customer.detail.updateCustomerProfile');
            Route::post('/walletAdjustment', [CustomerController::class, 'walletAdjustment'])->name('customer.detail.walletAdjustment');
        });
    });

    /**
     * ==============================
     *         Transactions
     * ==============================
     */
    Route::prefix('transaction')->group(function () {
        /**
         * ==============================
         *            Pending
         * ==============================
         */
        Route::prefix('pending')->group(function () {
            /**
             * ==============================
             *            Deposit
             * ==============================
             */
            // listing
            Route::get('/deposit', [TransactionController::class, 'pending_deposit'])->name('transaction.pending.pending_deposit');
            Route::get('/getRecentApprovals', [TransactionController::class, 'getRecentApprovals'])->name('transaction.pending.getRecentApprovals');
            Route::get('/getPendingDeposits', [TransactionController::class, 'getPendingDeposits'])->name('transaction.pending.getPendingDeposits');

            Route::post('/pendingApproval', [TransactionController::class, 'pendingApproval'])->name('transaction.pending.pendingApproval');
        });

        /**
         * ==============================
         *            History
         * ==============================
         */
        Route::prefix('history')->group(function () {
            /**
             * ==============================
             *            Deposit
             * ==============================
             */
            // listing
            Route::get('/deposit', [TransactionController::class, 'deposit_history'])->name('transaction.history.deposit_history');
            Route::get('/getHighestDeposit', [TransactionController::class, 'getHighestDeposit'])->name('transaction.history.getHighestDeposit');
            Route::get('/getDepositHistoryData', [TransactionController::class, 'getDepositHistoryData'])->name('transaction.pending.getDepositHistoryData');
        });

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
     *            Product
     * ==============================
     */
    Route::prefix('product')->group(function () {
        // add product
        Route::get('/add_product', [ProductController::class, 'add_product'])->name('product.add_product');

        Route::post('/addProduct', [ProductController::class, 'addProduct'])->name('product.addProduct');

        // listing
        Route::get('/', [ProductController::class, 'index'])->name('product');
        Route::get('/getProductData', [ProductController::class, 'getProductData'])->name('product.getProductData');
    });

    /**
     * ==============================
     *            Master
     * ==============================
     */
    Route::prefix('master')->group(function () {
        // listing
        Route::get('/', [MasterController::class, 'index'])->name('master');
        Route::get('/getMasterOverview', [MasterController::class, 'getMasterOverview'])->name('master.getMasterOverview');
        Route::get('/getMasters', [MasterController::class, 'getMasters'])->name('master.getMasters');
        Route::get('/getJoiningAccountsData', [MasterController::class, 'getJoiningAccountsData'])->name('master.getJoiningAccountsData');

        Route::post('/addMaster', [MasterController::class, 'addMaster'])->name('master.addMaster');
    });

    /**
     * ==============================
     *           Settings
     * ==============================
     */
    Route::prefix('settings')->group(function () {
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
