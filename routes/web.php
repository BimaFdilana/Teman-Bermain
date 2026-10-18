<?php

use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RedeemController;
use Illuminate\Support\Facades\Route;

// Publik: landing + katalog (Fase 2)
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/harga', [LandingController::class, 'harga'])->name('harga');
Route::get('/produk', [ProductController::class, 'index'])->name('produk.index');
Route::get('/produk/{slug}', [ProductController::class, 'show'])->name('produk.show');

Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('/ebook', function () {
        return view('ebook', ['ebooks' => request()->user()->ownedProducts('ebook')]);
    })->name('ebook');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Checkout & pembayaran (Fase 3, PRD §5.4)
    Route::post('/checkout/{slug}', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/bayar/{kode_order}', [CheckoutController::class, 'bayar'])->name('checkout.bayar');
    Route::post('/bayar/{kode_order}/bukti', [CheckoutController::class, 'uploadBukti'])->name('checkout.bukti');
    Route::get('/pesanan-saya', [CheckoutController::class, 'riwayat'])->name('checkout.riwayat');
    Route::get('/pesanan/{kode_order}', [CheckoutController::class, 'show'])->name('checkout.show');

    // Redeem code
    Route::post('/redeem', [RedeemController::class, 'store'])->name('redeem.store');
});

// Admin (gaya Kidza, PRD §6.6)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/pembayaran');
    Route::get('/pembayaran', [PaymentController::class, 'index'])->name('pembayaran.index');
    Route::get('/pembayaran/{order}', [PaymentController::class, 'show'])->name('pembayaran.show');
    Route::post('/pembayaran/{order}/setujui', [PaymentController::class, 'approve'])->name('pembayaran.approve');
    Route::post('/pembayaran/{order}/tolak', [PaymentController::class, 'reject'])->name('pembayaran.reject');
    Route::resource('/diskon', DiscountController::class)->except(['show']);
    Route::get('/pengaturan', [SettingController::class, 'index'])->name('pengaturan.index');
    Route::post('/pengaturan', [SettingController::class, 'update'])->name('pengaturan.update');
});

require __DIR__.'/auth.php';
