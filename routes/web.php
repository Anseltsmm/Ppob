<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepositController as AdminDepositController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\CetakVoucherController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\EwalletController;
use App\Http\Controllers\Customer\GameController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\PageController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\PulsaController;
use App\Http\Controllers\Customer\PulsaTransferController;
use App\Http\Controllers\Customer\ShopController;
use App\Http\Controllers\Customer\TagihanController;
use App\Http\Controllers\Customer\TokenPlnController;
use App\Http\Controllers\Customer\TopupController;
use App\Http\Controllers\Customer\VoucherDataController;
use App\Http\Controllers\Webhook\OkeconnectCallbackController;
use App\Http\Controllers\Webhook\TripayCallbackController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// ==================== AUTH ====================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==================== WEBHOOK (tanpa CSRF & auth) ====================
Route::prefix('webhook')->name('webhook.')->group(function () {
    Route::post('/tripay', [TripayCallbackController::class, 'handle'])->name('tripay');
    Route::get('/okeconnect', [OkeconnectCallbackController::class, 'handle'])->name('okeconnect');
});

// ==================== CUSTOMER ====================
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');

    // Shop
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/shop/category/{category}', [ShopController::class, 'byCategory'])->name('shop.category');
    Route::get('/shop/{product}', [ShopController::class, 'show'])->name('shop.show');

    // Pulsa (deteksi operator otomatis)
    Route::get('/pulsa', [PulsaController::class, 'index'])->name('customer.pulsa.index');
    Route::post('/pulsa/detect', [PulsaController::class, 'detect'])->name('customer.pulsa.detect');
    Route::get('/pulsa/products', [PulsaController::class, 'products'])->name('customer.pulsa.products');

    // Voucher Data (sistem hampir sama dengan Pulsa, khusus kategori Paket Data)
    Route::get('/voucher-data', [VoucherDataController::class, 'index'])->name('customer.voucher.index');

    // Token PLN (sistem hampir sama dengan Pulsa, khusus kategori Token PLN)
    Route::get('/token-pln', [TokenPlnController::class, 'index'])->name('customer.token-pln.index');
    Route::post('/token-pln/cek-id', [TokenPlnController::class, 'cekId'])->name('customer.token-pln.cek-id');
    Route::post('/token-pln/cek-id/result', [TokenPlnController::class, 'cekIdResult'])->name('customer.token-pln.cek-id-result');

    // E-Wallet (brand dulu → nomor → nominal)
    Route::get('/ewallet', [EwalletController::class, 'index'])->name('customer.ewallet.index');
    Route::get('/ewallet/products', [EwalletController::class, 'products'])->name('customer.ewallet.products');

    // Game (brand dulu → user ID → nominal)
    Route::get('/game', [GameController::class, 'index'])->name('customer.game.index');
    Route::get('/game/products', [GameController::class, 'products'])->name('customer.game.products');

    // Cetak Voucher (brand → pilih voucher → SN ditampilkan setelah bayar)
    Route::get('/cetak-voucher', [CetakVoucherController::class, 'index'])->name('customer.cetak-voucher.index');
    Route::get('/cetak-voucher/products', [CetakVoucherController::class, 'products'])->name('customer.cetak-voucher.products');

    // Pulsa Transfer (nomor tujuan → nominal)
    Route::get('/pulsa-transfer', [PulsaTransferController::class, 'index'])->name('customer.pulsa-transfer.index');
    Route::get('/pulsa-transfer/products', [PulsaTransferController::class, 'products'])->name('customer.pulsa-transfer.products');

    // Tagihan & Pascabayar (jenis → biller → ID → cek tagihan → bayar)
    Route::get('/tagihan', [TagihanController::class, 'index'])->name('customer.tagihan.index');
    Route::post('/tagihan/inquiry', [TagihanController::class, 'inquiry'])->name('customer.tagihan.inquiry');

    // Order
    Route::post('/order/{product}', [CustomerOrderController::class, 'store'])->name('customer.orders.store');
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('customer.orders.index');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');

    // Topup saldo
    Route::get('/topup', [TopupController::class, 'index'])->name('customer.topup.index');
    Route::post('/topup', [TopupController::class, 'store'])->name('customer.topup.store');
    Route::get('/topup/{deposit}', [TopupController::class, 'pay'])->name('customer.topup.pay');
    Route::get('/topup/{deposit}/check', [TopupController::class, 'checkStatus'])->name('customer.topup.check');
    Route::get('/deposits', [TopupController::class, 'history'])->name('customer.deposits.index');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('customer.profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('customer.profile.update');

    // Halaman QRIS & Info
    Route::get('/qris', [PageController::class, 'qris'])->name('customer.qris');
    Route::get('/info', [PageController::class, 'info'])->name('customer.info');
});

// ==================== ADMIN ====================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Produk
    Route::get('products/import', [AdminProductController::class, 'import'])->name('products.import');
    Route::post('products/import', [AdminProductController::class, 'importStore'])->name('products.import-store');
    Route::post('products/sync-prices', [AdminProductController::class, 'syncPrices'])->name('products.sync-prices');
    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::post('products/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('products.toggle');

    // Brand
    Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
    Route::post('brands', [BrandController::class, 'store'])->name('brands.store');
    Route::put('brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
    Route::delete('brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');

    // Kategori
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Order
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/check-status', [AdminOrderController::class, 'checkStatus'])->name('orders.check-status');

    // Deposit
    Route::get('deposits', [AdminDepositController::class, 'index'])->name('deposits.index');
    Route::get('deposits/{deposit}', [AdminDepositController::class, 'show'])->name('deposits.show');

    // Customer
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::post('customers/{customer}/adjust-saldo', [CustomerController::class, 'adjustSaldo'])->name('customers.adjust-saldo');
    Route::post('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle');

    // Pengaturan
    Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [AdminSettingController::class, 'update'])->name('settings.update');
    Route::post('settings/check-balance', [AdminSettingController::class, 'checkBalance'])->name('settings.check-balance');
    Route::post('settings/test-tripay', [AdminSettingController::class, 'testTripay'])->name('settings.test-tripay');
    Route::post('settings/regenerate-callback-token', [AdminSettingController::class, 'regenerateCallbackToken'])->name('settings.regenerate-token');
});
