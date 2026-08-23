<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\QrCodeController;
use App\Http\Controllers\Admin\WeddingController as AdminWeddingController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\WeddingArchiveController;
use App\Http\Controllers\WeddingGuestController;
use App\Http\Controllers\WeddingMediaController;
use App\Http\Controllers\WeddingQrAccessController;
use App\Http\Controllers\WeddingUploadController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/h/blende6')->name('marketing.home');
Route::get('/ueber-mich', [MarketingController::class, 'about'])->name('marketing.about');
Route::get('/portfolio', [MarketingController::class, 'portfolio'])->name('marketing.portfolio');
Route::get('/shootings', [MarketingController::class, 'shootings'])->name('marketing.shootings');
Route::get('/kontakt', [MarketingController::class, 'contact'])->name('marketing.contact');
Route::get('/impressum', [MarketingController::class, 'imprint'])->name('marketing.imprint');
Route::get('/datenschutz', [MarketingController::class, 'privacy'])->name('marketing.privacy');
Route::redirect('/about-us', '/ueber-mich', 301);
Route::redirect('/galerie', '/portfolio', 301);
Route::redirect('/galerie-hochzeit', '/portfolio#hochzeiten', 301);
Route::redirect('/home-2', '/', 301);

Route::get('/g/{token}', WeddingQrAccessController::class)
    ->where('token', '[A-Za-z0-9]{48}')
    ->middleware('throttle:30,1')
    ->name('weddings.qr-access');

Route::prefix('h/{wedding:slug}')->group(function () {
    Route::get('/', [WeddingGuestController::class, 'show'])->name('weddings.show');
    Route::post('/pin', [WeddingGuestController::class, 'unlock'])->middleware('throttle:6,1')->name('weddings.unlock');
    Route::get('/cover', [WeddingMediaController::class, 'cover'])->name('weddings.cover');

    Route::middleware('wedding.access')->group(function () {
        Route::post('/upload', [WeddingUploadController::class, 'store'])->middleware('throttle:30,1')->name('weddings.upload');
        Route::get('/download', [WeddingArchiveController::class, 'all'])->name('weddings.archive.download');
        Route::get('/album/download', [WeddingArchiveController::class, 'guest'])->name('weddings.guest-album.download');
        Route::get('/qr', [QrCodeController::class, 'show'])->name('weddings.qr');
        Route::get('/qr/download', [QrCodeController::class, 'download'])->name('weddings.qr.download');
        Route::get('/m/{media}/view', [WeddingMediaController::class, 'view'])->name('weddings.media.view');
        Route::get('/m/{media}/thumb', [WeddingMediaController::class, 'thumbnail'])->name('weddings.media.thumbnail');
        Route::get('/m/{media}/download', [WeddingMediaController::class, 'download'])->name('weddings.media.download');
    });
});

Route::prefix('admin')->group(function () {
    Route::get('/', fn () => auth()->user()?->is_admin
        ? redirect()->route('admin.weddings.index')
        : redirect()->route('admin.login'))->name('admin.home');

    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'create'])->name('admin.login');
        Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:8,1')->name('admin.login.store');
    });

    Route::middleware(['auth', 'admin'])->name('admin.')->group(function () {
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
        Route::resource('weddings', AdminWeddingController::class)->except(['show']);
        Route::get('/weddings/{wedding:slug}/media', [AdminMediaController::class, 'index'])->name('weddings.media.index');
        Route::delete('/weddings/{wedding:slug}/media/{media}', [AdminMediaController::class, 'destroy'])->name('weddings.media.destroy');
        Route::delete('/weddings/{wedding:slug}/albums', [AdminMediaController::class, 'destroyGuestAlbum'])->name('weddings.albums.destroy');
        Route::delete('/weddings/{wedding:slug}/uploads/{uploadSession}', [AdminMediaController::class, 'destroyUpload'])->name('weddings.uploads.destroy');
        Route::post('/weddings/{wedding:slug}/media/bulk', [AdminMediaController::class, 'bulk'])->name('weddings.media.bulk');
        Route::get('/weddings/{wedding:slug}/media.zip', [AdminMediaController::class, 'zip'])->name('weddings.media.zip');
        Route::get('/weddings/{wedding:slug}/qr', [QrCodeController::class, 'show'])->name('weddings.qr');
        Route::get('/weddings/{wedding:slug}/qr/download', [QrCodeController::class, 'download'])->name('weddings.qr.download');
    });
});
