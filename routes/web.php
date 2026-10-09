<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CheckoutController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\OrderTrackingController;
use App\Http\Controllers\Store\ProductController;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/favicon.ico', function () {
    $path = Setting::getValue('site_logo');
    $disk = Storage::disk('public');

    if (! $path || ! $disk->exists($path)) {
        abort(404);
    }

    return response()->file($disk->path($path), [
        'Content-Type' => $disk->mimeType($path) ?: 'image/png',
        'Cache-Control' => 'public, max-age=300',
    ]);
})->name('favicon');

Route::get('/', HomeController::class)->name('home');
Route::get('/bazar', [ProductController::class, 'index'])->name('products.index');
Route::get('/produk/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
Route::patch('/keranjang', [CartController::class, 'update'])->name('cart.update');
Route::delete('/keranjang', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::get('/checkout/sukses/{orderCode}', [CheckoutController::class, 'success'])
    ->middleware('throttle:60,1')
    ->name('checkout.success');

Route::get('/pesanan', [OrderTrackingController::class, 'index'])->name('orders.index');
Route::post('/pesanan/lacak', [OrderTrackingController::class, 'lookup'])
    ->middleware('throttle:20,1')
    ->name('orders.lookup');
Route::get('/pesanan/{orderCode}', [OrderTrackingController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('orders.show');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/proof', [AdminOrderController::class, 'proof'])->name('orders.proof');
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');

    Route::middleware('inti')->group(function () {
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::resource('users', AdminUserController::class)->except(['show']);
    });
});

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
