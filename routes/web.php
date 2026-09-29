<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Backend\ChangeLogController;
use App\Http\Controllers\Backend\DataRelationController;
use App\Http\Controllers\Backend\ModuleController;
use App\Http\Controllers\Backend\OptionController;
use App\Http\Controllers\Backend\ProfileController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\TelescopeLogController;
use App\Http\Controllers\Backend\ThemeController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\WelcomeController;
use App\Http\Controllers\Frontend\CustomerController;
use App\Http\Controllers\Frontend\OAuth\GoogleController;
use App\Http\Controllers\Frontend\HomeController as FrontendHomeController;
use App\Http\Controllers\Frontend\LoginController as FrontendLogin;
use App\Http\Controllers\Frontend\RegisterController as FrontendRegister;
use App\Http\Controllers\Frontend\CustomerResetPasswordController;
use App\Http\Controllers\Frontend\CustomerForgotPasswordController;
use App\Http\Controllers\Frontend\ShippingController;
use App\Http\Controllers\Generator\BasicController;
use App\Http\Controllers\Generator\LogGeneratorController;
use App\Http\Controllers\HomeController;use App\Http\Controllers\Master\ImportStatusController;
use App\Http\Controllers\Master\SalesTransactionEcommerceController;
use App\Http\Controllers\Frontend\SitemapController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('frontend.sitemap');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
Route::post('/login/custom', [LoginController::class, 'loginCustom'])->name('login.custom');
Route::get('index/{locale}', [HomeController::class, 'lang']);

Route::get('/toggle-lang', function () {
    $lang = session('lang', 'en') === 'en' ? 'id' : 'en';
    session(['lang' => $lang]);
    return redirect()->back();
})->name('toggle.lang');

// Frontend Routes
Route::get('/', [FrontendHomeController::class, 'index'])->name('frontend.index');
Route::get('/page/{title}/{id}', [FrontendHomeController::class, 'topPages'])->name('frontend.topPages');
Route::get('/bottom/{title}/{id}', [FrontendHomeController::class, 'bottomPages'])->name('frontend.bottomPages');

// 301 Redirects for Old SEO URLs
Route::get('/product/category/{id}', [FrontendHomeController::class, 'redirectOldCategory']);
Route::get('/product/main-category/{id}', [FrontendHomeController::class, 'redirectOldMainCategory']);


Route::get('/product/category', [FrontendHomeController::class, 'newestProduct'])->name('frontend.newest');
Route::get('/product/detail/{id}/{productDetailId?}', [FrontendHomeController::class, 'redirectOldProductDetail']);
Route::get('/product/cart', [FrontendHomeController::class, 'cart'])->name('frontend.product.cart');
Route::get('/products/search', [FrontendHomeController::class, 'searchProduct'])->name('frontend.product.search');
Route::post('/chatbot/message', [\App\Http\Controllers\Frontend\ChatbotController::class, 'sendMessage'])->middleware('throttle:5,1')->name('frontend.chatbot.message');
Route::get('/checkout/contact', [FrontendHomeController::class, 'checkoutContact'])->name('checkout.contact');
Route::get('/checkout/shipping', [FrontendHomeController::class, 'checkoutShipping'])->name('checkout.shipping');
Route::get('/checkout/review', [FrontendHomeController::class, 'checkoutReview'])->name('checkout.review');

Route::get('/wishlist', [FrontendHomeController::class, 'wishlist'])->name('frontend.wishlist');
Route::get('/blog-page', [FrontendHomeController::class, 'BlogPage'])->name('frontend.blogPage');
Route::get('/blog-page/{id}', [FrontendHomeController::class, 'detailBlog'])->name('frontend.blogDetail');
// API
Route::get('/api/product/price/{color_id}/{product_id}', [FrontendHomeController::class, 'getProductPriceByColor'])->name('frontend.product.price');
Route::post('/api/product/addToCart', [FrontendHomeController::class, 'addToCart'])->name('frontend.product.addToCart');
Route::post('api/wishlist/store', [FrontendHomeController::class, 'storeWishlist'])->name('frontend.wishlist.store');
Route::get('/api/cart', [FrontendHomeController::class, 'getCartData'])->name('frontend.product.cartData');
Route::post('/api/cart/remove', [FrontendHomeController::class, 'removeFromCart'])->name('frontend.product.removeFromCart');
Route::post('/api/cart/update', [FrontendHomeController::class, 'updateCart'])->name('frontend.product.updateCart');
Route::get('/api/customer', [FrontendHomeController::class, 'getCustomerData'])->name('frontend.customerData');
Route::put('/api/customer', [FrontendHomeController::class, 'checkoutStoreCustomerProfile'])->name('frontend.customerProfile.update');
Route::post('api/shipping/calculate', [ShippingController::class, 'calculate'])->name('shipping.calculate');
Route::post('api/shipping/store', [ShippingController::class, 'storeSelected'])->name('shipping.storeSelected');
Route::get('/api/selected-shipment-method', [FrontendHomeController::class, 'getSelectedShipmentMethodData'])->name('frontend.selectedShipmentData');
Route::post('/api/invoice/create', [ShippingController::class, 'createInvoiceController'])->name('frontend.invoice.create');
Route::get('/api/invoice/{id}', [ShippingController::class, 'getInvoiceById'])->name('frontend.invoice.id');

// Auth Routes
Auth::routes([
    'register' => true,
    'reset'    => true,
    'verify'   => true,
]);

Route::get('/register-customer', [FrontendRegister::class, 'index'])->name('registerForm.customer');
Route::post('/register-cust', [FrontendRegister::class, 'register'])->name('register.customer');
Route::get('/login-customer', [FrontendLogin::class, 'index'])->name('loginForm.customer');
Route::post('/login-cust', [FrontendLogin::class, 'login'])->name('login.customer');
Route::post('/logout-cust', [FrontendLogin::class, 'logout'])->name('logout.customer');
Route::get('/search-destination', [FrontendRegister::class, 'searchDestination']);

Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('customer.google.callback');
Route::prefix('customer')->group(function () {
    // Login via Google
    Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('customer.google.redirect');

    // Reset and Forget Password
    Route::get('forgot-password', [CustomerForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('customer.password.request');
    Route::post('forgot-password', [CustomerForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('customer.password.email');
    Route::get('reset-password/{token}', [CustomerResetPasswordController::class, 'showResetForm'])
        ->name('customer.password.reset');
    Route::post('reset-password', [CustomerResetPasswordController::class, 'reset'])
        ->name('customer.password.update');

    Route::middleware('auth:customer')->group(function () {
        Route::get('/account', [CustomerController::class, 'index'])->name('customer.account');
        Route::get('/profile', [CustomerController::class, 'profile'])->name('customer.profile');
        Route::post('/profile', [CustomerController::class, 'updateProfile'])->name('customer.updateProfile');
        Route::get('/address', [CustomerController::class, 'address'])->name('customer.address');
        Route::post('/address', [CustomerController::class, 'updateAddress'])->name('customer.updateAddress');
        Route::get('/change-password', [CustomerController::class, 'change_password_form'])->name('customer.changePassword');
        Route::post('/change-password', [CustomerController::class, 'updatePassword'])->name('customer.updatePassword');
        Route::get('/order-history', [CustomerController::class, 'order_history'])->name('customer.orderHistory');
    });
});

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::match(['get', 'post'], 'logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard.index');
    Route::get('/welcome', [WelcomeController::class, 'index'])->name('home');

    // Modul Akuntansi - Head Account
    Route::post('/head-account/delete', [\App\Http\Controllers\Master\HeadAccountController::class, 'delete'])->name('head-account.delete');
    Route::post('/head-account/restore', [\App\Http\Controllers\Master\HeadAccountController::class, 'restore'])->name('head-account.restore');
    Route::post('/head-account/restore_action', [\App\Http\Controllers\Master\HeadAccountController::class, 'restore_action'])->name('head-account.restore_action');
    Route::get('/head-account/get_datatable', [\App\Http\Controllers\Master\HeadAccountController::class, 'get_datatable'])->name('head-account.get_datatable');
    Route::get('/head-account/get_datatable_trash', [\App\Http\Controllers\Master\HeadAccountController::class, 'get_datatable_trash'])->name('head-account.get_datatable_trash');
    Route::get('/head-account/trash', [\App\Http\Controllers\Master\HeadAccountController::class, 'trash'])->name('head-account.trash');
    Route::resource('head-account', \App\Http\Controllers\Master\HeadAccountController::class);
    Route::get('/get-head-accounts-template', function() { return response()->json(['status' => 'ok']); })->name('get.head_accountsTemplate');
    Route::get('/download-head-accounts-template', function() { return redirect()->back(); })->name('download.head_accountsTemplate');
    Route::post('/import/head_account', function() { return redirect()->back(); })->name('import.head_account');
    Route::post('/import/download_template', function() { return redirect()->back(); })->name('import.download_template');

    Route::get('/income', [\App\Http\Controllers\Master\AccountingController::class, 'incomeIndex'])->name('pemasukan.index');
    Route::get('/income/get_datatable', function() { return response()->json(['data' => []]); })->name('pemasukan.get_datatable');
    Route::post('/income/store', function() {})->name('pemasukan.store');
    Route::post('/income/delete', function() {})->name('pemasukan.delete');

    Route::get('/expenditure', [\App\Http\Controllers\Master\AccountingController::class, 'expenditureIndex'])->name('pengeluaran.index');
    Route::get('/expenditure/get_datatable', function() { return response()->json(['data' => []]); })->name('pengeluaran.get_datatable');
    Route::post('/expenditure/store', function() {})->name('pengeluaran.store');
    Route::post('/expenditure/delete', function() {})->name('pengeluaran.delete');
    
    Route::get('/dashboard-finance', [\App\Http\Controllers\Master\AccountingController::class, 'dashboardFinanceIndex'])->name('dashboard-finance.index');
    Route::post('/dashboard-filter', function() { return redirect()->back(); })->name('dashboard-filter');

    Route::get('/journal-integration', [\App\Http\Controllers\Master\AccountingController::class, 'journalIntegrationIndex'])->name('journal-integration.index');
    Route::put('/journal-integration', [\App\Http\Controllers\Master\AccountingController::class, 'journalIntegrationUpdate'])->name('journal-integration.edit');
    Route::get('/outletsetting', function() {})->name('outletsetting.index');
    
    Route::get('/general-journal', [\App\Http\Controllers\Master\AccountingController::class, 'generalJournalIndex'])->name('general-journal.index');
    Route::get('/general-journal/create', function() {})->name('general-journal.create');
    Route::get('/general-journal/trash', function() {})->name('general-journal.trash');
    Route::get('/general-journal/download-template', function() {})->name('general-journal.download_template');
    Route::post('/general-journal/import', function() {})->name('import.general_journal');
    Route::get('/general-journal/get-template', function() {})->name('get.general_journalsTemplate');
    Route::get('/general-journal/download-template-file', function() {})->name('download.general_journalsTemplate');
    Route::post('/general-journal/delete', function() {})->name('general-journal.delete');
    Route::get('/general-journal/get-datatable', function() { return response()->json(['data' => []]); })->name('general-journal.get_datatable');

    // Buku Besar (Ledger)
    Route::get('/ledger', [\App\Http\Controllers\Master\AccountingController::class, 'bukuBesarIndex'])->name('buku-besar.index');
    Route::post('/ledger/get-data', [\App\Http\Controllers\Master\AccountingController::class, 'bukuBesarGetData'])->name('buku-besar.getData');
    Route::post('/ledger/get-data-export', [\App\Http\Controllers\Master\AccountingController::class, 'bukuBesarExport'])->name('export.buku-besar');

    // Inventarisasi Alat (Asset Inventory)
    Route::get('/asset_inventories/export', [\App\Http\Controllers\Master\AssetInventoryController::class, 'export'])->name('asset_inventories.export');
    Route::get('/asset_inventories/datatable', [\App\Http\Controllers\Master\AssetInventoryController::class, 'get_datatable'])->name('asset_inventories.get_datatable');
    Route::get('/asset_inventories/get_datatable_asset/{id}', [\App\Http\Controllers\Master\AssetInventoryController::class, 'get_datatable_alat'])->name('asset_inventories.get_datatable_alat');
    Route::post('/asset_inventories/delete', [\App\Http\Controllers\Master\AssetInventoryController::class, 'delete'])->name('asset_inventories.delete');
    Route::resource('asset_inventories', \App\Http\Controllers\Master\AssetInventoryController::class);

    // Laporan Penyusutan Inventaris (Asset Inventory Report)
    Route::get('/asset_inventories_report/export', [\App\Http\Controllers\Master\AssetInventoryReportController::class, 'export'])->name('asset_inventories_report.export');
    Route::get('/asset_inventories_report/datatable', [\App\Http\Controllers\Master\AssetInventoryReportController::class, 'get_datatable'])->name('asset_inventories_report.get_datatable');
    Route::resource('asset_inventories_report', \App\Http\Controllers\Master\AssetInventoryReportController::class);

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/{user}/update', [ProfileController::class, 'update'])->name('profile.update');

    // Theme Management
    Route::get('/theme', [ThemeController::class, 'index'])->name('theme.index');
    Route::post('/theme-change-image', [ThemeController::class, 'change_image'])->name('theme.changeImage');
    Route::get('/theme-change-layout', [ThemeController::class, 'change_layout'])->name('theme.changeLayout');
    Route::put('/theme/{theme}/update', [ThemeController::class, 'update'])->name('theme.update');

    // Role Management
    Route::prefix('role')->group(function () {
        Route::put('/{role}/position_save', [RoleController::class, 'position_save'])->name('role.position_save');
        Route::get('/{role}/setting-theme', [RoleController::class, 'setting_theme'])->name('role.settingTheme');
        Route::get('/{role}/reset-theme', [RoleController::class, 'reset_theme'])->name('role.resetTheme');
        Route::get('/change-theme', [RoleController::class, 'setting_theme_save'])->name('role.settingThemeSave');
        Route::get('/{role}/position', [RoleController::class, 'position'])->name('role.position');
        Route::post('/delete', [RoleController::class, 'delete'])->name('role.delete');
        Route::post('/restore', [RoleController::class, 'restore'])->name('role.restore');
        Route::post('/restore_action', [RoleController::class, 'restore_action'])->name('role.restore_action');
        Route::get('/get_datatable', [RoleController::class, 'get_datatable'])->name('role.get_datatable');
        Route::get('/get_datatable_trash', [RoleController::class, 'get_datatable_trash'])->name('role.get_datatable_trash');
        Route::get('/trash', [RoleController::class, 'trash'])->name('role.trash');
    });

    Route::resource('/role', RoleController::class);

    // User Management
    Route::prefix('user')->group(function () {
        Route::post('/delete', [UserController::class, 'delete'])->name('user.delete');
        Route::post('/restore', [UserController::class, 'restore'])->name('user.restore');
        Route::post('/restore_action', [UserController::class, 'restore_action'])->name('user.restore_action');
        Route::get('/get_datatable', [UserController::class, 'get_datatable'])->name('user.get_datatable');
        Route::get('/get_datatable_trash', [UserController::class, 'get_datatable_trash'])->name('user.get_datatable_trash');
        Route::get('/trash', [UserController::class, 'trash'])->name('user.trash');
    });

    Route::resource('/user', UserController::class);

    // Module Management
    Route::prefix('module')->group(function () {
        Route::post('/delete', [ModuleController::class, 'delete'])->name('module.delete');
        Route::post('/restore', [ModuleController::class, 'restore'])->name('module.restore');
        Route::post('/restore_action', [ModuleController::class, 'restore_action'])->name('module.restore_action');
        Route::get('/get_datatable', [ModuleController::class, 'get_datatable'])->name('module.get_datatable');
        Route::get('/get_datatable_trash', [ModuleController::class, 'get_datatable_trash'])->name('module.get_datatable_trash');
        Route::get('/trash', [ModuleController::class, 'trash'])->name('module.trash');
    });

    Route::resource('/module', ModuleController::class);

    // Change Log
    Route::prefix('change-log')->group(function () {
        Route::post('/delete', [ChangeLogController::class, 'delete'])->name('change-log.delete');
        Route::get('/get_datatable', [ChangeLogController::class, 'get_datatable'])->name('change-log.get_datatable');
    });

    Route::resource('/change-log', ChangeLogController::class);

    // Settings
    Route::prefix('setting')->group(function () {
        Route::post('/delete', [OptionController::class, 'delete'])->name('setting.delete');
        Route::post('/restore', [OptionController::class, 'restore'])->name('setting.restore');
        Route::post('/restore_action', [OptionController::class, 'restore_action'])->name('setting.restore_action');
        Route::get('/get_datatable', [OptionController::class, 'get_datatable'])->name('setting.get_datatable');
        Route::get('/get_datatable_trash', [OptionController::class, 'get_datatable_trash'])->name('setting.get_datatable_trash');
        Route::get('/trash', [OptionController::class, 'trash'])->name('setting.trash');
    });

    Route::resource('setting', OptionController::class);

    // Data Relation
    Route::prefix('datarelation')->group(function () {
        Route::get('/', [DataRelationController::class, 'index'])->name('datarelation.index');
        Route::get('/{id}', [DataRelationController::class, 'getcolumn'])->name('datarelation.getcolumn');
        Route::put('/update', [DataRelationController::class, 'update'])->name('datarelation.update');
        Route::post('/delete', [DataRelationController::class, 'delete'])->name('datarelation.delete');
        Route::post('/store', [DataRelationController::class, 'store'])->name('datarelation.store');
        Route::delete('/{datarelation}', [DataRelationController::class, 'destroy'])->name('datarelation.destroy');
    });

    // Data Generator
    Route::prefix('generator')->group(function () {
        Route::get('basic', [BasicController::class, 'index'])->name('generator_basic.index');
        Route::post('generate', [BasicController::class, 'generate'])->name('generator_basic.generate');
        Route::get('basic/table-attribute', [BasicController::class, 'getAttribute'])->name('get.attribute');
        Route::get('basic/table-check', [BasicController::class, 'checkTable'])->name('check.table');
        Route::get('basic/get-join', [BasicController::class, 'get_join'])->name('basic.getJoin');
    });

    // Data Generator Log
    Route::prefix('generator-log')->group(function () {
        Route::post('delete', [LogGeneratorController::class, 'delete'])->name('generator-log.delete');
        Route::get('get_datatable', [LogGeneratorController::class, 'get_datatable'])->name('generator-log.get_datatable');
    });

    Route::get('/generator-log-get-module', [LogGeneratorController::class, 'get_module'])->name('generator-log.getModule');
    Route::resource('generator-log', LogGeneratorController::class);

    // Telescope Log
    Route::get('/system-log', [TelescopeLogController::class, 'index'])->name('telescope.index');

    Route::get('/change-role/{id}', [WelcomeController::class, 'changeRole'])->name('welcome.changeRole');
});

//route for main-category
Route::post('/main-category/delete', [App\Http\Controllers\Master\MainCategoryController::class, 'delete'])->name('main-category.delete');
Route::post('/main-category/restore', [App\Http\Controllers\Master\MainCategoryController::class, 'restore'])->name('main-category.restore');
Route::post('/main-category/restore_action', [App\Http\Controllers\Master\MainCategoryController::class, 'restore_action'])->name('main-category.restore_action');
Route::get('/main-category/get_datatable', [App\Http\Controllers\Master\MainCategoryController::class, 'get_datatable'])->name('main-category.get_datatable');
Route::get('/main-category/get_datatable_trash', [App\Http\Controllers\Master\MainCategoryController::class, 'get_datatable_trash'])->name('main-category.get_datatable_trash');
Route::get('/main-category/trash', [App\Http\Controllers\Master\MainCategoryController::class, 'trash'])->name('main-category.trash');
Route::resource('main-category', App\Http\Controllers\Master\MainCategoryController::class);
Route::get('/download-main_categories-template', [App\Http\Controllers\Master\MainCategoryController::class, 'download'])->name('download.main_categoriesTemplate');
Route::get('/get-main_categories-template', [App\Http\Controllers\Master\MainCategoryController::class, 'get_template'])->name('get.main_categoriesTemplate');
Route::get('/main-category/edit/{id}', [App\Http\Controllers\Master\MainCategoryController::class, 'edit'])->name('main-category.edit');

//route for sales-transaction-ecommerce
Route::post('/ecommerce-transaction/delete', [SalesTransactionEcommerceController::class, 'delete'])->name('ecommerce-transaction.delete');
Route::post('/ecommerce-transaction/restore', [SalesTransactionEcommerceController::class, 'restore'])->name('ecommerce-transaction.restore');
Route::post('/ecommerce-transaction/restore_action', [SalesTransactionEcommerceController::class, 'restore_action'])->name('ecommerce-transaction.restore_action');
Route::get('/ecommerce-transaction/get_datatable', [SalesTransactionEcommerceController::class, 'get_datatable'])->name('ecommerce-transaction.get_datatable');
Route::post('/ecommerce-transaction/send-email', [SalesTransactionEcommerceController::class, 'sendEmail'])->name('ecommerce-transaction.send_email');
Route::get('/ecommerce-transaction/get_datatable_trash', [SalesTransactionEcommerceController::class, 'get_datatable_trash'])->name('ecommerce-transaction.get_datatable_trash');
Route::get('/ecommerce-transaction/trash', [SalesTransactionEcommerceController::class, 'trash'])->name('ecommerce-transaction.trash');
Route::put('/ecommerce-transaction/update-payment/{id}', [SalesTransactionEcommerceController::class, 'updatePayment'])
    ->name('ecommerce-transaction.updatePayment');
Route::resource('/ecommerce-transaction', SalesTransactionEcommerceController::class);
//route for profit-loss-report
Route::get('/profit-loss-report/get_datatable', [App\Http\Controllers\Master\ProfitLossReportController::class, 'get_datatable'])->name('profit-loss-report.get_datatable');
Route::post('/profit-loss-report/export', [App\Http\Controllers\Master\ProfitLossReportController::class, 'export'])->name('profit-loss-report.export');
Route::resource('/profit-loss-report', App\Http\Controllers\Master\ProfitLossReportController::class);

//route for category
Route::post('/category/delete', [App\Http\Controllers\Master\CategoryController::class, 'delete'])->name('category.delete');
Route::post('/category/restore', [App\Http\Controllers\Master\CategoryController::class, 'restore'])->name('category.restore');
Route::post('/category/restore_action', [App\Http\Controllers\Master\CategoryController::class, 'restore_action'])->name('category.restore_action');
Route::get('/category/get_datatable', [App\Http\Controllers\Master\CategoryController::class, 'get_datatable'])->name('category.get_datatable');
Route::get('/category/get_datatable_trash', [App\Http\Controllers\Master\CategoryController::class, 'get_datatable_trash'])->name('category.get_datatable_trash');
Route::get('/category/trash', [App\Http\Controllers\Master\CategoryController::class, 'trash'])->name('category.trash');
Route::resource('category', App\Http\Controllers\Master\CategoryController::class)->where(['category' => '[0-9a-fA-F\-]{36}']);
Route::get('/download-categories-template', [App\Http\Controllers\Master\CategoryController::class, 'download'])->name('download.categoriesTemplate');
Route::get('/get-categories-template', [App\Http\Controllers\Master\CategoryController::class, 'get_template'])->name('get.categoriesTemplate');
Route::get('/category/edit/{id}', [App\Http\Controllers\Master\CategoryController::class, 'edit'])->name('category.edit');

// Frontend category routes - diletakkan SETELAH semua rute admin category
Route::get('/category/{main_slug}/{sub_slug}', [FrontendHomeController::class, 'product'])->name('frontend.product');
Route::get('/category/{main_slug}', [FrontendHomeController::class, 'productByMainCategory'])->name('frontend.productByMainCategory');

//route for material
Route::post('/material/delete', [App\Http\Controllers\Master\MaterialController::class, 'delete'])->name('material.delete');
Route::post('/material/restore', [App\Http\Controllers\Master\MaterialController::class, 'restore'])->name('material.restore');
Route::post('/material/restore_action', [App\Http\Controllers\Master\MaterialController::class, 'restore_action'])->name('material.restore_action');
Route::get('/material/get_datatable', [App\Http\Controllers\Master\MaterialController::class, 'get_datatable'])->name('material.get_datatable');
Route::get('/material/get_datatable_trash', [App\Http\Controllers\Master\MaterialController::class, 'get_datatable_trash'])->name('material.get_datatable_trash');
Route::get('/material/trash', [App\Http\Controllers\Master\MaterialController::class, 'trash'])->name('material.trash');
Route::resource('material', App\Http\Controllers\Master\MaterialController::class);
Route::get('/material/edit/{id}', [App\Http\Controllers\Master\MaterialController::class, 'edit'])->name('material.edit');


//route for featured-category
Route::post('/featured-category/delete', [App\Http\Controllers\Master\FeaturedCategoryController::class, 'delete'])->name('featured-category.delete');
Route::post('/featured-category/restore', [App\Http\Controllers\Master\FeaturedCategoryController::class, 'restore'])->name('featured-category.restore');
Route::post('/featured-category/restore_action', [App\Http\Controllers\Master\FeaturedCategoryController::class, 'restore_action'])->name('featured-category.restore_action');
Route::get('/featured-category/get_datatable', [App\Http\Controllers\Master\FeaturedCategoryController::class, 'get_datatable'])->name('featured-category.get_datatable');
Route::get('/featured-category/get_datatable_trash', [App\Http\Controllers\Master\FeaturedCategoryController::class, 'get_datatable_trash'])->name('featured-category.get_datatable_trash');
Route::get('/featured-category/trash', [App\Http\Controllers\Master\FeaturedCategoryController::class, 'trash'])->name('featured-category.trash');
Route::resource('featured-category', App\Http\Controllers\Master\FeaturedCategoryController::class);
Route::get('/download-featured_categories-template', [App\Http\Controllers\Master\FeaturedCategoryController::class, 'download'])->name('download.featured_categoriesTemplate');
Route::get('/get-featured_categories-template', [App\Http\Controllers\Master\FeaturedCategoryController::class, 'get_template'])->name('get.featured_categoriesTemplate');
Route::get('/featured-category/edit/{id}', [App\Http\Controllers\Master\FeaturedCategoryController::class, 'edit'])->name('featured-category.edit');

//route for hero
Route::post('/hero/delete', [App\Http\Controllers\Master\HeroController::class, 'delete'])->name('hero.delete');
Route::post('/hero/restore', [App\Http\Controllers\Master\HeroController::class, 'restore'])->name('hero.restore');
Route::post('/hero/restore_action', [App\Http\Controllers\Master\HeroController::class, 'restore_action'])->name('hero.restore_action');
Route::get('/hero/get_datatable', [App\Http\Controllers\Master\HeroController::class, 'get_datatable'])->name('hero.get_datatable');
Route::get('/hero/get_datatable_trash', [App\Http\Controllers\Master\HeroController::class, 'get_datatable_trash'])->name('hero.get_datatable_trash');
Route::get('/hero/trash', [App\Http\Controllers\Master\HeroController::class, 'trash'])->name('hero.trash');
Route::resource('hero', App\Http\Controllers\Master\HeroController::class);
Route::get('/download-heroes-template', [App\Http\Controllers\Master\HeroController::class, 'download'])->name('download.heroesTemplate');
Route::get('/get-heroes-template', [App\Http\Controllers\Master\HeroController::class, 'get_template'])->name('get.heroesTemplate');
Route::get('/hero/edit/{id}', [App\Http\Controllers\Master\HeroController::class, 'edit'])->name('hero.edit');

//route for top-page
Route::post('/top-page/delete', [App\Http\Controllers\Master\TopPageController::class, 'delete'])->name('top-page.delete');
Route::post('/top-page/restore', [App\Http\Controllers\Master\TopPageController::class, 'restore'])->name('top-page.restore');
Route::post('/top-page/restore_action', [App\Http\Controllers\Master\TopPageController::class, 'restore_action'])->name('top-page.restore_action');
Route::get('/top-page/get_datatable', [App\Http\Controllers\Master\TopPageController::class, 'get_datatable'])->name('top-page.get_datatable');
Route::get('/top-page/get_datatable_trash', [App\Http\Controllers\Master\TopPageController::class, 'get_datatable_trash'])->name('top-page.get_datatable_trash');
Route::get('/top-page/trash', [App\Http\Controllers\Master\TopPageController::class, 'trash'])->name('top-page.trash');
Route::resource('top-page', App\Http\Controllers\Master\TopPageController::class);
Route::get('/download-top_pages-template', [App\Http\Controllers\Master\TopPageController::class, 'download'])->name('download.top_pagesTemplate');
Route::get('/get-top_pages-template', [App\Http\Controllers\Master\TopPageController::class, 'get_template'])->name('get.top_pagesTemplate');
Route::get('/top-page/edit/{id}', [App\Http\Controllers\Master\TopPageController::class, 'edit'])->name('top-page.edit');

Route::post('/top-page/upload', [App\Http\Controllers\Master\TopPageController::class, 'upload'])->name('top_page.upload');

//route for signature
Route::post('/signature/delete', [App\Http\Controllers\Master\SignatureController::class, 'delete'])->name('signature.delete');
Route::post('/signature/restore', [App\Http\Controllers\Master\SignatureController::class, 'restore'])->name('signature.restore');
Route::post('/signature/restore_action', [App\Http\Controllers\Master\SignatureController::class, 'restore_action'])->name('signature.restore_action');
Route::get('/signature/get_datatable', [App\Http\Controllers\Master\SignatureController::class, 'get_datatable'])->name('signature.get_datatable');
Route::get('/signature/get_datatable_trash', [App\Http\Controllers\Master\SignatureController::class, 'get_datatable_trash'])->name('signature.get_datatable_trash');
Route::get('/signature/trash', [App\Http\Controllers\Master\SignatureController::class, 'trash'])->name('signature.trash');
Route::resource('signature', App\Http\Controllers\Master\SignatureController::class);
Route::get('/download-signatures-template', [App\Http\Controllers\Master\SignatureController::class, 'download'])->name('download.signaturesTemplate');
Route::get('/get-signatures-template', [App\Http\Controllers\Master\SignatureController::class, 'get_template'])->name('get.signaturesTemplate');
Route::get('/signature/edit/{id}', [App\Http\Controllers\Master\SignatureController::class, 'edit'])->name('signature.edit');

//route for bottom-page
Route::post('/bottom-page/delete', [App\Http\Controllers\Master\BottomPageController::class, 'delete'])->name('bottom-page.delete');
Route::post('/bottom-page/restore', [App\Http\Controllers\Master\BottomPageController::class, 'restore'])->name('bottom-page.restore');
Route::post('/bottom-page/restore_action', [App\Http\Controllers\Master\BottomPageController::class, 'restore_action'])->name('bottom-page.restore_action');
Route::get('/bottom-page/get_datatable', [App\Http\Controllers\Master\BottomPageController::class, 'get_datatable'])->name('bottom-page.get_datatable');
Route::get('/bottom-page/get_datatable_trash', [App\Http\Controllers\Master\BottomPageController::class, 'get_datatable_trash'])->name('bottom-page.get_datatable_trash');
Route::get('/bottom-page/trash', [App\Http\Controllers\Master\BottomPageController::class, 'trash'])->name('bottom-page.trash');
Route::resource('bottom-page', App\Http\Controllers\Master\BottomPageController::class);
Route::get('/download-bottom_pages-template', [App\Http\Controllers\Master\BottomPageController::class, 'download'])->name('download.bottom_pagesTemplate');
Route::get('/get-bottom_pages-template', [App\Http\Controllers\Master\BottomPageController::class, 'get_template'])->name('get.bottom_pagesTemplate');
Route::get('/bottom-page/edit/{id}', [App\Http\Controllers\Master\BottomPageController::class, 'edit'])->name('bottom-page.edit');

Route::post('/bottom-page/upload', [App\Http\Controllers\Master\BottomPageController::class, 'upload'])->name('bottom_page.upload');

//route for inspiration
Route::post('/inspiration/delete', [App\Http\Controllers\Master\InspirationController::class, 'delete'])->name('inspiration.delete');
Route::post('/inspiration/restore', [App\Http\Controllers\Master\InspirationController::class, 'restore'])->name('inspiration.restore');
Route::post('/inspiration/restore_action', [App\Http\Controllers\Master\InspirationController::class, 'restore_action'])->name('inspiration.restore_action');
Route::get('/inspiration/get_datatable', [App\Http\Controllers\Master\InspirationController::class, 'get_datatable'])->name('inspiration.get_datatable');
Route::get('/inspiration/get_datatable_trash', [App\Http\Controllers\Master\InspirationController::class, 'get_datatable_trash'])->name('inspiration.get_datatable_trash');
Route::get('/inspiration/trash', [App\Http\Controllers\Master\InspirationController::class, 'trash'])->name('inspiration.trash');
Route::resource('inspiration', App\Http\Controllers\Master\InspirationController::class);
Route::get('/download-inspirations-template', [App\Http\Controllers\Master\InspirationController::class, 'download'])->name('download.inspirationsTemplate');
Route::get('/get-inspirations-template', [App\Http\Controllers\Master\InspirationController::class, 'get_template'])->name('get.inspirationsTemplate');
Route::get('/inspiration/edit/{id}', [App\Http\Controllers\Master\InspirationController::class, 'edit'])->name('inspiration.edit');

//route for blog
Route::post('/blog/delete', [App\Http\Controllers\Master\BlogController::class, 'delete'])->name('blog.delete');
Route::post('/blog/restore', [App\Http\Controllers\Master\BlogController::class, 'restore'])->name('blog.restore');
Route::post('/blog/restore_action', [App\Http\Controllers\Master\BlogController::class, 'restore_action'])->name('blog.restore_action');
Route::get('/blog/get_datatable', [App\Http\Controllers\Master\BlogController::class, 'get_datatable'])->name('blog.get_datatable');
Route::get('/blog/get_datatable_trash', [App\Http\Controllers\Master\BlogController::class, 'get_datatable_trash'])->name('blog.get_datatable_trash');
Route::get('/blog/trash', [App\Http\Controllers\Master\BlogController::class, 'trash'])->name('blog.trash');
Route::resource('blog', App\Http\Controllers\Master\BlogController::class);
Route::get('/download-blogs-template', [App\Http\Controllers\Master\BlogController::class, 'download'])->name('download.blogsTemplate');
Route::get('/get-blogs-template', [App\Http\Controllers\Master\BlogController::class, 'get_template'])->name('get.blogsTemplate');
Route::get('/blog/edit/{id}', [App\Http\Controllers\Master\BlogController::class, 'edit'])->name('blog.edit');

Route::post('/blog/upload', [App\Http\Controllers\Master\BlogController::class, 'upload'])->name('blog.upload');

//route for courier
Route::post('/courier/delete', [App\Http\Controllers\Master\CourierController::class, 'delete'])->name('courier.delete');
Route::post('/courier/restore', [App\Http\Controllers\Master\CourierController::class, 'restore'])->name('courier.restore');
Route::post('/courier/restore_action', [App\Http\Controllers\Master\CourierController::class, 'restore_action'])->name('courier.restore_action');
Route::get('/courier/get_datatable', [App\Http\Controllers\Master\CourierController::class, 'get_datatable'])->name('courier.get_datatable');
Route::get('/courier/get_datatable_trash', [App\Http\Controllers\Master\CourierController::class, 'get_datatable_trash'])->name('courier.get_datatable_trash');
Route::get('/courier/trash', [App\Http\Controllers\Master\CourierController::class, 'trash'])->name('courier.trash');
Route::resource('courier', App\Http\Controllers\Master\CourierController::class);
Route::get('/download-couriers-template', [App\Http\Controllers\Master\CourierController::class, 'download'])->name('download.couriersTemplate');
Route::get('/get-couriers-template', [App\Http\Controllers\Master\CourierController::class, 'get_template'])->name('get.couriersTemplate');
Route::get('/courier/edit/{id}', [App\Http\Controllers\Master\CourierController::class, 'edit'])->name('courier.edit');

//route for payment-type
Route::post('/payment-type/delete', [App\Http\Controllers\Master\PaymentTypeController::class, 'delete'])->name('payment-type.delete');
Route::post('/payment-type/restore', [App\Http\Controllers\Master\PaymentTypeController::class, 'restore'])->name('payment-type.restore');
Route::post('/payment-type/restore_action', [App\Http\Controllers\Master\PaymentTypeController::class, 'restore_action'])->name('payment-type.restore_action');
Route::get('/payment-type/get_datatable', [App\Http\Controllers\Master\PaymentTypeController::class, 'get_datatable'])->name('payment-type.get_datatable');
Route::get('/payment-type/get_datatable_trash', [App\Http\Controllers\Master\PaymentTypeController::class, 'get_datatable_trash'])->name('payment-type.get_datatable_trash');
Route::get('/payment-type/trash', [App\Http\Controllers\Master\PaymentTypeController::class, 'trash'])->name('payment-type.trash');
Route::resource('payment-type', App\Http\Controllers\Master\PaymentTypeController::class);
Route::get('/download-payment_types-template', [App\Http\Controllers\Master\PaymentTypeController::class, 'download'])->name('download.payment_typesTemplate');
Route::get('/get-payment_types-template', [App\Http\Controllers\Master\PaymentTypeController::class, 'get_template'])->name('get.payment_typesTemplate');
Route::get('/payment-type/edit/{id}', [App\Http\Controllers\Master\PaymentTypeController::class, 'edit'])->name('payment-type.edit');

//route for store
Route::post('/store/delete', [App\Http\Controllers\Master\StoreController::class, 'delete'])->name('store.delete');
Route::post('/store/restore', [App\Http\Controllers\Master\StoreController::class, 'restore'])->name('store.restore');
Route::post('/store/restore_action', [App\Http\Controllers\Master\StoreController::class, 'restore_action'])->name('store.restore_action');
Route::get('/store/get_datatable', [App\Http\Controllers\Master\StoreController::class, 'get_datatable'])->name('store.get_datatable');
Route::get('/store/get_datatable_trash', [App\Http\Controllers\Master\StoreController::class, 'get_datatable_trash'])->name('store.get_datatable_trash');
Route::get('/store/trash', [App\Http\Controllers\Master\StoreController::class, 'trash'])->name('store.trash');
Route::resource('store', App\Http\Controllers\Master\StoreController::class);
Route::get('/download-stores-template', [App\Http\Controllers\Master\StoreController::class, 'download'])->name('download.storesTemplate');
Route::get('/get-stores-template', [App\Http\Controllers\Master\StoreController::class, 'get_template'])->name('get.storesTemplate');
Route::get('/store/edit/{id}', [App\Http\Controllers\Master\StoreController::class, 'edit'])->name('store.edit');
Route::post('/store/ganti-store', [App\Http\Controllers\Master\StoreController::class, 'ganti_store'])->name('store.gantiDefault');

//route for discount
Route::get('/discount/search-product', [App\Http\Controllers\Master\DiscountController::class, 'search_product'])->name('discount.searchProduct');
Route::post('/discount/delete', [App\Http\Controllers\Master\DiscountController::class, 'delete'])->name('discount.delete');
Route::post('/discount/restore', [App\Http\Controllers\Master\DiscountController::class, 'restore'])->name('discount.restore');
Route::post('/discount/restore_action', [App\Http\Controllers\Master\DiscountController::class, 'restore_action'])->name('discount.restore_action');
Route::get('/discount/get_datatable', [App\Http\Controllers\Master\DiscountController::class, 'get_datatable'])->name('discount.get_datatable');
Route::get('/discount/get_datatable_trash', [App\Http\Controllers\Master\DiscountController::class, 'get_datatable_trash'])->name('discount.get_datatable_trash');
Route::get('/discount/trash', [App\Http\Controllers\Master\DiscountController::class, 'trash'])->name('discount.trash');
Route::resource('discount', App\Http\Controllers\Master\DiscountController::class);
Route::get('/download-discounts-template', [App\Http\Controllers\Master\DiscountController::class, 'download'])->name('download.discountsTemplate');
Route::get('/get-discounts-template', [App\Http\Controllers\Master\DiscountController::class, 'get_template'])->name('get.discountsTemplate');
Route::get('/discount/edit/{id}', [App\Http\Controllers\Master\DiscountController::class, 'edit'])->name('discount.edit');
Route::get('/discount/{id}/product-datatable', [App\Http\Controllers\Master\DiscountController::class, 'get_product_discount_datatable'])->name('discount.productDatatable');
Route::post('/discount/{id}/add-product', [App\Http\Controllers\Master\DiscountController::class, 'add_product_discount'])->name('discount.addProduct');
Route::delete('/discount/{id}/remove-product', [App\Http\Controllers\Master\DiscountController::class, 'remove_product_discount'])->name('discount.removeProduct');

//route for sale-person
Route::post('/sale-person/delete', [App\Http\Controllers\Master\SalePersonController::class, 'delete'])->name('sale-person.delete');
Route::post('/sale-person/restore', [App\Http\Controllers\Master\SalePersonController::class, 'restore'])->name('sale-person.restore');
Route::post('/sale-person/restore_action', [App\Http\Controllers\Master\SalePersonController::class, 'restore_action'])->name('sale-person.restore_action');
Route::get('/sale-person/get_datatable', [App\Http\Controllers\Master\SalePersonController::class, 'get_datatable'])->name('sale-person.get_datatable');
Route::get('/sale-person/get_datatable_trash', [App\Http\Controllers\Master\SalePersonController::class, 'get_datatable_trash'])->name('sale-person.get_datatable_trash');
Route::get('/sale-person/trash', [App\Http\Controllers\Master\SalePersonController::class, 'trash'])->name('sale-person.trash');
Route::resource('sale-person', App\Http\Controllers\Master\SalePersonController::class);
Route::get('/download-sale_people-template', [App\Http\Controllers\Master\SalePersonController::class, 'download'])->name('download.sale_peopleTemplate');
Route::get('/get-sale_people-template', [App\Http\Controllers\Master\SalePersonController::class, 'get_template'])->name('get.sale_peopleTemplate');
Route::get('/sale-person/edit/{id}', [App\Http\Controllers\Master\SalePersonController::class, 'edit'])->name('sale-person.edit');
Route::post('/sale-person/delete', [App\Http\Controllers\Master\SalePersonController::class, 'delete'])->name('sale-person.delete');
Route::post('/sale-person/restore', [App\Http\Controllers\Master\SalePersonController::class, 'restore'])->name('sale-person.restore');
Route::post('/sale-person/restore_action', [App\Http\Controllers\Master\SalePersonController::class, 'restore_action'])->name('sale-person.restore_action');
Route::get('/sale-person/get_datatable', [App\Http\Controllers\Master\SalePersonController::class, 'get_datatable'])->name('sale-person.get_datatable');
Route::get('/sale-person/get_datatable_trash', [App\Http\Controllers\Master\SalePersonController::class, 'get_datatable_trash'])->name('sale-person.get_datatable_trash');
Route::get('/sale-person/trash', [App\Http\Controllers\Master\SalePersonController::class, 'trash'])->name('sale-person.trash');
Route::resource('sale-person', App\Http\Controllers\Master\SalePersonController::class);
Route::get('/download-sale_people-template', [App\Http\Controllers\Master\SalePersonController::class, 'download'])->name('download.sale_peopleTemplate');
Route::get('/get-sale_people-template', [App\Http\Controllers\Master\SalePersonController::class, 'get_template'])->name('get.sale_peopleTemplate');
Route::get('/sale-person/edit/{id}', [App\Http\Controllers\Master\SalePersonController::class, 'edit'])->name('sale-person.edit');

//route for customer
Route::post('/customer/login-as', [App\Http\Controllers\Master\CustomerController::class, 'loginAsCustomer'])->name('customer.loginAs');
Route::post('/customer/delete', [App\Http\Controllers\Master\CustomerController::class, 'delete'])->name('customer.delete');
Route::post('/customer/restore', [App\Http\Controllers\Master\CustomerController::class, 'restore'])->name('customer.restore');
Route::post('/customer/restore_action', [App\Http\Controllers\Master\CustomerController::class, 'restore_action'])->name('customer.restore_action');
Route::get('/customer/get_datatable', [App\Http\Controllers\Master\CustomerController::class, 'get_datatable'])->name('customer.get_datatable');
Route::get('/customer/get_datatable_trash', [App\Http\Controllers\Master\CustomerController::class, 'get_datatable_trash'])->name('customer.get_datatable_trash');
Route::get('/customer/trash', [App\Http\Controllers\Master\CustomerController::class, 'trash'])->name('customer.trash');
Route::resource('customer', App\Http\Controllers\Master\CustomerController::class);
Route::get('/download-customers-template', [App\Http\Controllers\Master\CustomerController::class, 'download'])->name('download.customersTemplate');
Route::get('/get-customers-template', [App\Http\Controllers\Master\CustomerController::class, 'get_template'])->name('get.customersTemplate');
Route::get('/customer/edit/{id}', [App\Http\Controllers\Master\CustomerController::class, 'edit'])->name('customer.edit');

//route for product
Route::get('/product/categories/{mainCategoryId}', [App\Http\Controllers\Master\ProductController::class, 'get_category_by_main'])->name('product.getCategoryByMain');
Route::post('/product/toggle_status', [App\Http\Controllers\Master\ProductController::class, 'toggle_status'])->name('product.toggle_status');
Route::post('/product/delete', [App\Http\Controllers\Master\ProductController::class, 'delete'])->name('product.delete');
Route::post('/product/restore', [App\Http\Controllers\Master\ProductController::class, 'restore'])->name('product.restore');
Route::post('/product/restore_action', [App\Http\Controllers\Master\ProductController::class, 'restore_action'])->name('product.restore_action');
Route::get('/product/asset-breakdown', [App\Http\Controllers\Master\ProductController::class, 'asset_breakdown'])->name('product.assetBreakdown');
Route::get('/product/asset-breakdown-datatable', [App\Http\Controllers\Master\ProductController::class, 'asset_breakdown_datatable'])->name('product.assetBreakdownDatatable');
Route::get('/product/get_datatable', [App\Http\Controllers\Master\ProductController::class, 'get_datatable'])->name('product.get_datatable');
Route::get('/product/get_datatable_trash', [App\Http\Controllers\Master\ProductController::class, 'get_datatable_trash'])->name('product.get_datatable_trash');
Route::get('/product/trash', [App\Http\Controllers\Master\ProductController::class, 'trash'])->name('product.trash');
Route::resource('product', App\Http\Controllers\Master\ProductController::class)->except(['show', 'edit', 'update']);
Route::get('/download-products-template', [App\Http\Controllers\Master\ProductController::class, 'download'])->name('download.productsTemplate');
Route::get('/get-products-template', [App\Http\Controllers\Master\ProductController::class, 'get_template'])->name('get.productsTemplate');
Route::get('/product/edit/{id}', [App\Http\Controllers\Master\ProductController::class, 'edit'])->name('product.edit')->where('id', '[0-9a-fA-F\-]{36}');
Route::get('/product/{id}/basic-information', [App\Http\Controllers\Master\ProductController::class, 'product_basic_information'])->name('product.basicInformation')->where('id', '[0-9a-fA-F\-]{36}');
Route::post('/product/{id}/basic-information', [App\Http\Controllers\Master\ProductController::class, 'update_product_basic_information'])->name('product.updateBasicInformation')->where('id', '[0-9a-fA-F\-]{36}');
Route::get('/product/{id}/price-and-stock', [App\Http\Controllers\Master\ProductController::class, 'product_price_and_stock'])->name('product.priceAndStock')->where('id', '[0-9a-fA-F\-]{36}');
Route::post('/product/{id}/price-and-stock', [App\Http\Controllers\Master\ProductController::class, 'update_product_price_and_stock'])->name('product.updatePriceAndStock')->where('id', '[0-9a-fA-F\-]{36}');
Route::get('/product/{id}/product-detail', [App\Http\Controllers\Master\ProductController::class, 'product_detail'])->name('product.productDetail')->where('id', '[0-9a-fA-F\-]{36}');
Route::post('/product/{id}/product-detail', [App\Http\Controllers\Master\ProductController::class, 'update_product_detail'])->name('product.updateProductDetail')->where('id', '[0-9a-fA-F\-]{36}');
Route::get('/product/{id}/detail/{detailId}', [App\Http\Controllers\Master\ProductController::class, 'product_detail_show'])->name('product.productDetailShow')->where(['id' => '[0-9a-fA-F\-]{36}', 'detailId' => '[0-9a-fA-F\-]{36}']);
Route::get('/product/{id}/detail/{detailId}/stock-movements', [App\Http\Controllers\Master\ProductController::class, 'product_detail_stock_movement_datatable'])->name('product.productDetailStockMovements')->where(['id' => '[0-9a-fA-F\-]{36}', 'detailId' => '[0-9a-fA-F\-]{36}']);

// Frontend product detail - diletakkan SETELAH semua rute admin agar tidak shadow admin routes
Route::get('/product/{slug_and_id}/{productDetailId?}', [FrontendHomeController::class, 'productDetail'])
    ->name('frontend.product.detail')
    ->where([
        'slug_and_id' => '(.*-p\.[0-9a-fA-F\-]{36}|[0-9a-fA-F\-]{36})',
        'productDetailId' => '[0-9a-fA-F\-]{36}'
    ]);

Route::get('/product/download-image-template', [App\Http\Controllers\Master\ProductController::class, 'download_image_template'])->name('product.download_image_template');
Route::post('/product/import-images', [App\Http\Controllers\Master\ImportController::class, 'import_product_image'])->name('product.import_images');


Route::post('/import/download-template', [App\Http\Controllers\Master\ImportController::class, 'download_template'])->name('import.downloadTemplate');
Route::post('/import/download-product-detail-template', [App\Http\Controllers\Master\ImportController::class, 'download_product_detail_template'])->name('import.downloadProductDetailTemplate');
Route::post('/import/product', [App\Http\Controllers\Master\ImportController::class, 'import_product'])->name('import.product');
Route::post('/import/opname-product', [App\Http\Controllers\Master\ImportController::class, 'import_product_opname'])->name('import.opnameproduct');

Route::get('/import/sync-old-sku', [App\Http\Controllers\Master\ProductController::class, 'sync_old_sku_view'])->name('import.syncOldSkuView');
Route::get('/import/sync-old-sku/search', [App\Http\Controllers\Master\ProductController::class, 'search_product_ajax'])->name('import.syncOldSkuSearch');
Route::post('/import/sync-old-sku/preview', [App\Http\Controllers\Master\ProductController::class, 'sync_old_sku_preview'])->name('import.syncOldSkuPreview');
Route::post('/import/sync-old-sku/process-single', [App\Http\Controllers\Master\ProductController::class, 'sync_old_sku_process_single'])->name('import.syncOldSkuProcessSingle');
Route::post('/import/sync-old-sku/process', [App\Http\Controllers\Master\ProductController::class, 'sync_old_sku_process'])->name('import.syncOldSkuProcess');
Route::get('/product/copy-product-details', [App\Http\Controllers\Master\ProductController::class, 'copy_product_details_view'])->name('product.copyProductDetailsView');
Route::post('/product/copy-product-details', [App\Http\Controllers\Master\ProductController::class, 'copy_product_details_process'])->name('product.copyProductDetailsProcess');
Route::get('/product/bulk-cogm', [App\Http\Controllers\Master\ProductController::class, 'bulk_cogm_view'])->name('product.bulkCogmView');
Route::post('/product/bulk-cogm/lookup', [App\Http\Controllers\Master\ProductController::class, 'bulk_cogm_lookup'])->name('product.bulkCogmLookup');
Route::post('/product/bulk-cogm/save', [App\Http\Controllers\Master\ProductController::class, 'bulk_cogm_save'])->name('product.bulkCogmSave');
Route::post('/import/product-detail', [App\Http\Controllers\Master\ImportController::class, 'import_product_detail'])->name('import.productDetail');

Route::get('/import-status/datatable', [ImportStatusController::class, 'datatable'])->name('import_status.datatable');
Route::post('/import-status/delete', [ImportStatusController::class, 'delete'])->name('import_status.delete');
Route::delete('/import-status/destroy', [ImportStatusController::class, 'destroy'])->name('import_status.destroy');
Route::delete('/import-status/clear', [ImportStatusController::class, 'clear'])->name('import_status.clear');
Route::get('/import-status/{id}', [ImportStatusController::class, 'show'])->name('import_status.show');
Route::get('/import-status/export-product/{id}', [ImportStatusController::class, 'export_product'])->name('import_status.export_product');
Route::get('/import-status', [ImportStatusController::class, 'index'])->name('import_status.index');

Route::post('/import-status/failed-import-data/delete', [ImportStatusController::class, 'delete_failed_import_data'])->name('failed_import_data.delete');
Route::delete('/import-status/failed-import-data/destroy', [ImportStatusController::class, 'destroy_failed_import_data'])->name('failed_import_data.destroy');
Route::post('/import-status/failed-import-data/replace', [ImportStatusController::class, 'replace_failed_import_data'])->name('failed_import_data.replace');
Route::post('/import-status/failed-import-data/load-more', [ImportStatusController::class, 'load_more_failed_import_data'])->name('failed_import_data.load_more');
Route::post('/import-status/failed-import-data/download/{import_status_id}', [ImportStatusController::class, 'download_failed_import_data'])->name('failed_import_data.download');

//route for sales-transaction
Route::post('/sales-transaction/delete', [App\Http\Controllers\Master\SalesTransactionController::class, 'delete'])->name('sales-transaction.delete');
Route::post('/sales-transaction/restore', [App\Http\Controllers\Master\SalesTransactionController::class, 'restore'])->name('sales-transaction.restore');
Route::post('/sales-transaction/restore_action', [App\Http\Controllers\Master\SalesTransactionController::class, 'restore_action'])->name('sales-transaction.restore_action');
Route::get('/sales-transaction/get_datatable', [App\Http\Controllers\Master\SalesTransactionController::class, 'get_datatable'])->name('sales-transaction.get_datatable');
Route::get('/sales-transaction/get_datatable_trash', [App\Http\Controllers\Master\SalesTransactionController::class, 'get_datatable_trash'])->name('sales-transaction.get_datatable_trash');
Route::get('/sales-transaction/trash', [App\Http\Controllers\Master\SalesTransactionController::class, 'trash'])->name('sales-transaction.trash');

// Refund (harus sebelum Route::resource agar tidak tertangkap oleh wildcard {id})
Route::get('/sales-transaction/refund', [App\Http\Controllers\Master\RefundController::class, 'index'])->name('refund.index');
Route::get('/sales-transaction/refund/get_datatable', [App\Http\Controllers\Master\RefundController::class, 'get_datatable'])->name('refund.get_datatable');
Route::get('/sales-transaction/refund/create/{sales_transaction_id}', [App\Http\Controllers\Master\RefundController::class, 'create'])->name('refund.create');
Route::post('/sales-transaction/refund', [App\Http\Controllers\Master\RefundController::class, 'store'])->name('refund.store');
Route::get('/sales-transaction/refund/{id}', [App\Http\Controllers\Master\RefundController::class, 'show'])->name('refund.show');

Route::resource('sales-transaction', App\Http\Controllers\Master\SalesTransactionController::class);
Route::get('/download-sale_transactions-template', [App\Http\Controllers\Master\SalesTransactionController::class, 'download'])->name('download.sale_transactionsTemplate');
Route::get('/get-sale_transactions-template', [App\Http\Controllers\Master\SalesTransactionController::class, 'get_template'])->name('get.sale_transactionsTemplate');
Route::get('/sales-transaction/edit/{id}', [App\Http\Controllers\Master\SalesTransactionController::class, 'edit'])->name('sales-transaction.edit');
Route::get('/sales-transaction/products/filter-search', [App\Http\Controllers\Master\SalesTransactionController::class, 'listFilteredProducts'])->name('sales-transaction.products.filter-search');
Route::get('/sales-transaction/products/detail', [App\Http\Controllers\Master\SalesTransactionController::class, 'productDetail'])->name('sales-transaction.products.detail');
Route::post('/sales-transaction/products/update-stock', [App\Http\Controllers\Master\SalesTransactionController::class, 'updateStock'])->name('sales-transaction.products.update-stock');
Route::get('/sales-transaction/cart/list', [App\Http\Controllers\Master\SalesTransactionController::class, 'cartList'])->name('sales-transaction.cart.list');
Route::post('/sales-transaction/cart/add', [App\Http\Controllers\Master\SalesTransactionController::class, 'cartAdd'])->name('sales-transaction.cart.add');
Route::post('/sales-transaction/cart/add-by-sku', [App\Http\Controllers\Master\SalesTransactionController::class, 'cartAddBySku'])->name('sales-transaction.cart.add-by-sku');
Route::post('/sales-transaction/cart/update', [App\Http\Controllers\Master\SalesTransactionController::class, 'cartUpdate'])->name('sales-transaction.cart.update');
Route::post('/sales-transaction/cart/remove', [App\Http\Controllers\Master\SalesTransactionController::class, 'cartRemove'])->name('sales-transaction.cart.remove');
Route::get('/sales-transaction/cart/paperbag-detail', [App\Http\Controllers\Master\SalesTransactionController::class, 'getPaperbagDetail'])->name('sales-transaction.cart.paperbag-detail');
Route::get('/sales-transaction/success/{id}', [App\Http\Controllers\Master\SalesTransactionController::class, 'payment_success'])->name('sales-transaction.payment_success');
Route::get('sales-transaction/{id}/pdf', [App\Http\Controllers\Master\SalesTransactionController::class, 'download_pdf_58'])->name('sales-transaction.download_pdf_58');
Route::get('sales-transaction/{id}/print-thermal-58', [App\Http\Controllers\Master\SalesTransactionController::class, 'print_thermal_58'])->name('sales-transaction.print_thermal_58');
Route::post('sales-transaction/{id}/whatsapp-text', [App\Http\Controllers\Master\SalesTransactionController::class, 'sendWhatsAppReceiptText'])->name('sales-transaction.whatsapp_text');



Route::get('/transaction-report', [App\Http\Controllers\Master\SaleReportController::class, 'index'])->name('sale-transaction.report');
Route::post('/transaction-report/previewPenjualan', [App\Http\Controllers\Master\SaleReportController::class, 'preview_penjualan'])->name('sale-transaction.previewPenjualan');
Route::post('/transaction-report/exportPenjualan', [App\Http\Controllers\Master\SaleReportController::class, 'export_penjualan'])->name('sale-transaction.exportPenjualan');

Route::get('/product-mutation-report', [App\Http\Controllers\Master\ProductMutationReportController::class, 'report'])->name('product-mutation.report');
Route::get('/product-mutation-report/preview', [App\Http\Controllers\Master\ProductMutationReportController::class, 'preview_datatable'])->name('product-mutation.preview');
Route::post('/product-mutation-report/export', [App\Http\Controllers\Master\ProductMutationReportController::class, 'export'])->name('product-mutation.export');

//route for party
Route::post('/party/delete', [App\Http\Controllers\Master\PartyController::class, 'delete'])->name('party.delete');
Route::post('/party/restore', [App\Http\Controllers\Master\PartyController::class, 'restore'])->name('party.restore');
Route::post('/party/restore_action', [App\Http\Controllers\Master\PartyController::class, 'restore_action'])->name('party.restore_action');
Route::get('/party/get_datatable', [App\Http\Controllers\Master\PartyController::class, 'get_datatable'])->name('party.get_datatable');
Route::get('/party/get_datatable_trash', [App\Http\Controllers\Master\PartyController::class, 'get_datatable_trash'])->name('party.get_datatable_trash');
Route::get('/party/trash', [App\Http\Controllers\Master\PartyController::class, 'trash'])->name('party.trash');
Route::resource('party', App\Http\Controllers\Master\PartyController::class);
Route::get('/download-parties-template', [App\Http\Controllers\Master\PartyController::class, 'download'])->name('download.partiesTemplate');
Route::get('/get-parties-template', [App\Http\Controllers\Master\PartyController::class, 'get_template'])->name('get.partiesTemplate');
Route::get('/party/edit/{id}', [App\Http\Controllers\Master\PartyController::class, 'edit'])->name('party.edit');

Route::get('/product-mutation/detail/{id}', [App\Http\Controllers\Master\InventoryMovementController::class, 'detail'])->name('product-mutation.detail');
Route::get('/product-mutation/{id}/print-thermal-58', [App\Http\Controllers\Master\InventoryMovementController::class, 'print_thermal_58'])->name('product-mutation.print_thermal_58');
Route::get('/product-mutation/get_datatable', [App\Http\Controllers\Master\InventoryMovementController::class, 'get_datatable'])->name('product-mutation.get_datatable');
Route::get('/product-mutation/create', [App\Http\Controllers\Master\InventoryMovementController::class, 'product_mutation'])->name('product-mutation');
Route::get('/product-mutation', [App\Http\Controllers\Master\InventoryMovementController::class, 'index'])->name('product-mutation.index');
Route::get('/product-mutation/product-detail', [App\Http\Controllers\Master\InventoryMovementController::class, 'product_detail_information'])->name('product-mutation.productDetail');
Route::get('/product-mutation/list', [App\Http\Controllers\Master\InventoryMovementController::class, 'product_mutation_list'])->name('product-mutation.list');
Route::post('/product-mutation/list', [App\Http\Controllers\Master\InventoryMovementController::class, 'create_product_mutation'])->name('product-mutation.store');
Route::post('/product-mutation/list/add', [App\Http\Controllers\Master\InventoryMovementController::class, 'add_product_to_mutation_list'])->name('product-mutation.list.store');
Route::post('/product-mutation/list/update', [App\Http\Controllers\Master\InventoryMovementController::class, 'update_product_mutation_list'])->name('product-mutation.list.update');
Route::post('/product-mutation/list/delete', [App\Http\Controllers\Master\InventoryMovementController::class, 'remove_product_from_mutation_list'])->name('product-mutation.list.delete');
Route::get('/product-mutation/list/filter', [App\Http\Controllers\Master\InventoryMovementController::class, 'filter_product'])->name('product-mutation.list.filter');
Route::post('/product-mutation/clear', [App\Http\Controllers\Master\InventoryMovementController::class, 'clear_product_mutation_list'])->name('product-mutation.list.clear');
Route::get('/product-mutation/incoming', [App\Http\Controllers\Master\InventoryMovementController::class, 'get_incoming_product_for_current_store'])->name('product-mutation.incoming');
Route::post('/product-mutation/incoming', [App\Http\Controllers\Master\InventoryMovementController::class, 'add_incoming_product_to_mutation_list'])->name('product-mutation.incoming.addToList');
Route::get('/product-mutation/get_datatable_trash', [App\Http\Controllers\Master\InventoryMovementController::class, 'get_datatable_trash'])->name('product-mutation.get_datatable_trash');
Route::get('/product-mutation/trash', [App\Http\Controllers\Master\InventoryMovementController::class, 'trash'])->name('product-mutation.trash');
Route::post('/product-mutation/delete', [App\Http\Controllers\Master\InventoryMovementController::class, 'delete'])->name('product-mutation.delete');
Route::delete('/product-mutation/{delete_type}', [App\Http\Controllers\Master\InventoryMovementController::class, 'destroy'])->name('product-mutation.destroy');
Route::post('/product-mutation/restore', [App\Http\Controllers\Master\InventoryMovementController::class, 'restore'])->name('product-mutation.restore');
Route::post('/product-mutation/restore_action', [App\Http\Controllers\Master\InventoryMovementController::class, 'restore_action'])->name('product-mutation.restore_action');

// route for purchasing report
Route::get('/purchasing-report', [App\Http\Controllers\Master\PurchasingReportController::class, 'report'])->name('purchasing-report.report');
Route::get('/purchasing-report/preview', [App\Http\Controllers\Master\PurchasingReportController::class, 'preview_datatable'])->name('purchasing-report.preview');
Route::post('/purchasing-report/export', [App\Http\Controllers\Master\PurchasingReportController::class, 'export'])->name('purchasing-report.export');

// route for purchasing
Route::get('/purchasing-transaction/detail/{id}', [App\Http\Controllers\Master\PurchasingController::class, 'detail'])->name('purchasing-transaction.detail');
Route::get('/purchasing-transaction/{id}/print-thermal-58', [App\Http\Controllers\Master\PurchasingController::class, 'print_thermal_58'])->name('purchasing-transaction.print_thermal_58');
Route::get('/purchasing-transaction/get_datatable', [App\Http\Controllers\Master\PurchasingController::class, 'get_datatable'])->name('purchasing-transaction.get_datatable');
Route::get('/purchasing-transaction/create', [App\Http\Controllers\Master\PurchasingController::class, 'product_mutation'])->name('purchasing-transaction');
Route::get('/purchasing-transaction', [App\Http\Controllers\Master\PurchasingController::class, 'index'])->name('purchasing-transaction.index');
Route::get('/purchasing-transaction/product-detail', [App\Http\Controllers\Master\PurchasingController::class, 'product_detail_information'])->name('purchasing-transaction.productDetail');
Route::get('/purchasing-transaction/list', [App\Http\Controllers\Master\PurchasingController::class, 'product_mutation_list'])->name('purchasing-transaction.list');
Route::post('/purchasing-transaction/list', [App\Http\Controllers\Master\PurchasingController::class, 'create_product_mutation'])->name('purchasing-transaction.store');
Route::post('/purchasing-transaction/list/add', [App\Http\Controllers\Master\PurchasingController::class, 'add_product_to_mutation_list'])->name('purchasing-transaction.list.store');
Route::post('/purchasing-transaction/list/update', [App\Http\Controllers\Master\PurchasingController::class, 'update_product_mutation_list'])->name('purchasing-transaction.list.update');
Route::post('/purchasing-transaction/list/delete', [App\Http\Controllers\Master\PurchasingController::class, 'remove_product_from_mutation_list'])->name('purchasing-transaction.list.delete');
Route::get('/purchasing-transaction/list/filter', [App\Http\Controllers\Master\PurchasingController::class, 'filter_product'])->name('purchasing-transaction.list.filter');
Route::post('/purchasing-transaction/clear', [App\Http\Controllers\Master\PurchasingController::class, 'clear_product_mutation_list'])->name('purchasing-transaction.list.clear');
Route::get('/purchasing-transaction/incoming', [App\Http\Controllers\Master\PurchasingController::class, 'get_incoming_product_for_current_store'])->name('purchasing-transaction.incoming');
Route::post('/purchasing-transaction/incoming', [App\Http\Controllers\Master\PurchasingController::class, 'add_incoming_product_to_mutation_list'])->name('purchasing-transaction.incoming.addToList');
Route::get('/purchasing-transaction/get_datatable_trash', [App\Http\Controllers\Master\PurchasingController::class, 'get_datatable_trash'])->name('purchasing-transaction.get_datatable_trash');
Route::get('/purchasing-transaction/trash', [App\Http\Controllers\Master\PurchasingController::class, 'trash'])->name('purchasing-transaction.trash');
Route::post('/purchasing-transaction/delete', [App\Http\Controllers\Master\PurchasingController::class, 'delete'])->name('purchasing-transaction.delete');
Route::delete('/purchasing-transaction/{delete_type}', [App\Http\Controllers\Master\PurchasingController::class, 'destroy'])->name('purchasing-transaction.destroy');
Route::post('/purchasing-transaction/restore', [App\Http\Controllers\Master\PurchasingController::class, 'restore'])->name('purchasing-transaction.restore');
Route::post('/purchasing-transaction/restore_action', [App\Http\Controllers\Master\PurchasingController::class, 'restore_action'])->name('purchasing-transaction.restore_action');

//route for stockopname
Route::post('/stockopname/delete', [App\Http\Controllers\Master\StockopnameController::class, 'delete'])->name('stockopname.delete');
Route::post('/stockopname/restore', [App\Http\Controllers\Master\StockopnameController::class, 'restore'])->name('stockopname.restore');
Route::post('/stockopname/restore_action', [App\Http\Controllers\Master\StockopnameController::class, 'restore_action'])->name('stockopname.restore_action');
Route::get('/stockopname/get_datatable', [App\Http\Controllers\Master\StockopnameController::class, 'get_datatable'])->name('stockopname.get_datatable');
Route::get('/stockopname/get_datatable_trash', [App\Http\Controllers\Master\StockopnameController::class, 'get_datatable_trash'])->name('stockopname.get_datatable_trash');
Route::get('/stockopname/trash', [App\Http\Controllers\Master\StockopnameController::class, 'trash'])->name('stockopname.trash');
Route::get('/download-stockopnames-template', [App\Http\Controllers\Master\StockopnameController::class, 'download'])->name('download.stockopnamesTemplate');
Route::get('/get-stockopnames-template', [App\Http\Controllers\Master\StockopnameController::class, 'get_template'])->name('get.stockopnamesTemplate');
Route::get('/stockopname/edit/{id}', [App\Http\Controllers\Master\StockopnameController::class, 'edit'])->name('stockopname.edit');
Route::post('/stockopname/import-template/{id_opname}', [App\Http\Controllers\Master\StockopnameController::class, 'import_template'])->name('stockopname.importTemplate');
Route::post('/stockopname/update-qty', [App\Http\Controllers\Master\StockopnameController::class, 'update_qty'])->name('stockopname.updateQty');
Route::get('/stockopname/search', [App\Http\Controllers\Master\StockopnameController::class, 'search_product'])->name('stockopname.searchProduct');
Route::get('/stockopname/search-opname', [App\Http\Controllers\Master\StockopnameController::class, 'search_product'])->name('stockopname.searchProductOpname');
Route::get('/stockopname/filter', [App\Http\Controllers\Master\StockopnameController::class, 'filter'])->name('stockopname.filter');
Route::get('/stockopname/filter-opname', [App\Http\Controllers\Master\StockopnameController::class, 'filter_opname'])->name('stockopname.filterOpname');
Route::post('/stockopname/delete-item', [App\Http\Controllers\Master\StockopnameController::class, 'delete_item'])->name('stockopname.deleteItem');

Route::get('/stockopname/{id}/print-thermal-58', [App\Http\Controllers\Master\StockopnameController::class, 'print_thermal_58'])->name('stockopname.print_thermal_58');
Route::resource('stockopname', App\Http\Controllers\Master\StockopnameController::class);

//route for seasonal
Route::post('/seasonal/delete', [App\Http\Controllers\Master\SeasonalController::class, 'delete'])->name('seasonal.delete');
Route::post('/seasonal/restore', [App\Http\Controllers\Master\SeasonalController::class, 'restore'])->name('seasonal.restore');
Route::post('/seasonal/restore_action', [App\Http\Controllers\Master\SeasonalController::class, 'restore_action'])->name('seasonal.restore_action');
Route::get('/seasonal/get_datatable', [App\Http\Controllers\Master\SeasonalController::class, 'get_datatable'])->name('seasonal.get_datatable');
Route::get('/seasonal/get_datatable_trash', [App\Http\Controllers\Master\SeasonalController::class, 'get_datatable_trash'])->name('seasonal.get_datatable_trash');
Route::get('/seasonal/trash', [App\Http\Controllers\Master\SeasonalController::class, 'trash'])->name('seasonal.trash');
Route::get('/seasonal/search-product', [App\Http\Controllers\Master\SeasonalController::class, 'search_product'])->name('seasonal.search_product');
Route::post('/seasonal/add-product', [App\Http\Controllers\Master\SeasonalController::class, 'add_product'])->name('seasonal.add_product');
Route::post('/seasonal/remove-product', [App\Http\Controllers\Master\SeasonalController::class, 'remove_product'])->name('seasonal.remove_product');
Route::resource('seasonal', App\Http\Controllers\Master\SeasonalController::class)->whereUuid('seasonal');
Route::get('/seasonal/edit/{id}', [App\Http\Controllers\Master\SeasonalController::class, 'edit'])->name('seasonal.edit');

// Frontend route ditaruh di bawah agar tidak menabrak backend route
Route::get('/seasonal/{slug}', [App\Http\Controllers\Frontend\HomeController::class, 'seasonal'])->name('frontend.seasonal');

Route::get('/print-barcode', [App\Http\Controllers\Master\PrintBarcodeController::class, 'index'])->name('print-barcode.index');
Route::get('/print-barcode/preview', [App\Http\Controllers\Master\PrintBarcodeController::class, 'preview_datatable'])->name('print-barcode.preview');
Route::get('/print-barcode/search-products', [App\Http\Controllers\Master\PrintBarcodeController::class, 'search_products'])->name('print-barcode.searchProducts');
Route::post('/print-barcode/print', [App\Http\Controllers\Master\PrintBarcodeController::class, 'print'])->name('print-barcode.print');

Route::get('/test-pdf', function () {
    $html = '
    <html>
    <body>
        <div style="position: relative; width: 200px; height: 100px; border: 1px solid black; margin-bottom: 10px;">
            <div style="position: absolute; top: 10px; left: 10px; border: 1px solid red;">Item 1</div>
            <div style="position: absolute; top: 50px; left: 50px; border: 1px solid blue;">Item 2</div>
        </div>
        <div style="position: relative; width: 200px; height: 100px; border: 1px solid black; margin-bottom: 10px;">
            <div style="position: absolute; top: 20px; left: 80px; border: 1px solid green;">Item 3</div>
        </div>
    </body>
    </html>
    ';
    $pdf = Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
    return $pdf->stream('test.pdf');
});
