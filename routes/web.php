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

// Halaman promosi per produk ala link-in-bio: /p/{slug}
Route::get('/p/{slug}', [App\Http\Controllers\PromoController::class, 'show'])->name('promo.show');
Route::get('/p/{slug}/pesan', [App\Http\Controllers\PromoController::class, 'pesan'])->name('promo.pesan');

// Halaman iklan/promo multi-produk dengan hitungan mundur
Route::get('/iklan/{slug}', [App\Http\Controllers\PromoController::class, 'iklan'])->name('iklan.show');

Route::middleware(['auth', 'nonadmin'])->group(function () {
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

    // Ebook reader (Fase 4, PRD §5.5)
    Route::get('/baca/{product}', [App\Http\Controllers\EbookController::class, 'baca'])->name('ebook.baca');

    // MiniApp player + PWA (Fase 4, PRD §5.6)
    Route::get('/main/{product}', [App\Http\Controllers\MiniappController::class, 'main'])->name('miniapp.main');
    Route::get('/miniapp/{product}/manifest.json', [App\Http\Controllers\MiniappController::class, 'manifest'])->name('miniapp.manifest');
});

// File privat via signed URL (di luar grup auth: otorisasi via signature + entitlement)
Route::get('/file/ebook/{product}', [App\Http\Controllers\EbookController::class, 'file'])->name('file.ebook');
Route::get('/file/miniapp/{product}', [App\Http\Controllers\MiniappController::class, 'file'])->name('file.miniapp');

// Launcher PWA: install token -> sesi (PRD §5.6, tanpa login ulang)
Route::get('/pwa/{token}', [App\Http\Controllers\MiniappController::class, 'launch'])->name('pwa.launch');

// Admin (gaya Kidza, PRD §6)
Route::prefix('admin')->name('admin.')->group(function () {
    // Login khusus admin: email + password saja (PRD §6.1)
    Route::get('/login', [App\Http\Controllers\Admin\AuthController::class, 'showLogin'])
        ->name('login')->middleware('guest');
    Route::post('/login', [App\Http\Controllers\Admin\AuthController::class, 'login'])
        ->name('login.store')->middleware(['guest', 'throttle:5,1']);
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // CRUD Ebook (PRD §6.4) & MiniApps (PRD §6.5)
    Route::resource('ebook', App\Http\Controllers\Admin\EbookController::class)->except(['show']);
    Route::resource('miniapp', App\Http\Controllers\Admin\MiniappController::class)->except(['show']);
    Route::get('/miniapp/{miniapp}/preview', [App\Http\Controllers\Admin\MiniappController::class, 'preview'])->name('miniapp.preview');

    // Manage Product — 3 tab + bundle builder (PRD §6.7)
    Route::get('/produk', [App\Http\Controllers\Admin\ProdukController::class, 'index'])->name('produk.index');
    Route::put('/produk/{produk}/harga', [App\Http\Controllers\Admin\ProdukController::class, 'updateHarga'])->name('produk.harga');
    Route::get('/produk/bundle/baru', [App\Http\Controllers\Admin\ProdukController::class, 'bundleCreate'])->name('produk.bundle.create');
    Route::post('/produk/bundle', [App\Http\Controllers\Admin\ProdukController::class, 'bundleStore'])->name('produk.bundle.store');
    Route::get('/produk/bundle/{bundle}/edit', [App\Http\Controllers\Admin\ProdukController::class, 'bundleEdit'])->name('produk.bundle.edit');
    Route::put('/produk/bundle/{bundle}', [App\Http\Controllers\Admin\ProdukController::class, 'bundleUpdate'])->name('produk.bundle.update');
    Route::delete('/produk/bundle/{bundle}', [App\Http\Controllers\Admin\ProdukController::class, 'bundleDestroy'])->name('produk.bundle.destroy');

    // Manage Laporan (PRD §6.8)
    Route::get('/laporan', [App\Http\Controllers\Admin\LaporanController::class, 'index'])->name('laporan.index');
    Route::post('/laporan/target', [App\Http\Controllers\Admin\LaporanController::class, 'simpanTarget'])->name('laporan.target');
    Route::get('/laporan/export/excel', [App\Http\Controllers\Admin\LaporanController::class, 'exportExcel'])->name('laporan.excel');
    Route::get('/laporan/export/pdf', [App\Http\Controllers\Admin\LaporanController::class, 'exportPdf'])->name('laporan.pdf');

    // Manage Landing Page (PRD §6.9)
    Route::resource('iklan', App\Http\Controllers\Admin\IklanController::class)->except(['show']);

    Route::get('/landing', [App\Http\Controllers\Admin\LandingController::class, 'index'])->name('landing.index');
    Route::post('/landing/teks', [App\Http\Controllers\Admin\LandingController::class, 'simpanTeks'])->name('landing.teks');
    Route::post('/landing/foto', [App\Http\Controllers\Admin\LandingController::class, 'simpanFoto'])->name('landing.foto');
    Route::post('/landing/paket', [App\Http\Controllers\Admin\LandingController::class, 'simpanPaket'])->name('landing.paket');
    Route::post('/landing/testimoni', [App\Http\Controllers\Admin\LandingController::class, 'testimoniStore'])->name('landing.testimoni.store');
    Route::put('/landing/testimoni/{testimoni}', [App\Http\Controllers\Admin\LandingController::class, 'testimoniUpdate'])->name('landing.testimoni.update');
    Route::delete('/landing/testimoni/{testimoni}', [App\Http\Controllers\Admin\LandingController::class, 'testimoniDestroy'])->name('landing.testimoni.destroy');

    Route::get('/pembayaran', [PaymentController::class, 'index'])->name('pembayaran.index');
    Route::get('/pembayaran/{order}', [PaymentController::class, 'show'])->name('pembayaran.show');
    Route::post('/pembayaran/{order}/setujui', [PaymentController::class, 'approve'])->name('pembayaran.approve');
    Route::post('/pembayaran/{order}/tolak', [PaymentController::class, 'reject'])->name('pembayaran.reject');
    Route::resource('/diskon', DiscountController::class)->except(['show']);
    Route::get('/pengaturan', [SettingController::class, 'index'])->name('pengaturan.index');
    Route::post('/pengaturan', [SettingController::class, 'update'])->name('pengaturan.update');
});

require __DIR__.'/auth.php';
