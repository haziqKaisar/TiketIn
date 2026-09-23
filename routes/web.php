<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FrontEndController;
use App\Http\Controllers\OrganizationRegistrationController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TicketCategoryController;
use App\Http\Controllers\TicketController;

// =========================================================
// 1. HALAMAN PUBLIK
// =========================================================

// Halaman utama
Route::get('/', [FrontEndController::class, 'index'])
    ->name('home');

// Detail event
Route::get('/event/{event}', [FrontEndController::class, 'show'])
    ->name('event.show');

// Halaman e-ticket
Route::get('/e-ticket/{merchant_ref}', [FrontEndController::class, 'ticket'])
    ->name('ticket.show');

// =========================================================
// 2. CEK TIKET
// =========================================================

// Form cek tiket
Route::get('/cek-tiket', [TicketController::class, 'check'])
    ->name('ticket.check');

// Proses pencarian tiket
Route::post('/cek-tiket', [TicketController::class, 'search'])
    ->middleware('throttle:5,1')
    ->name('ticket.search');

// Verifikasi tiket melalui link email
Route::get('/cek-tiket/verifikasi', [TicketController::class, 'verify'])
    ->middleware('signed')
    ->name('ticket.check.verify');

// =========================================================
// 3. PROSES TRANSAKSI & TRIPAY
// =========================================================

// Proses checkout
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->name('checkout.store');

// Callback pembayaran Tripay
Route::post('/tripay/callback', [CheckoutController::class, 'callback'])
    ->name('tripay.callback');

// =========================================================
// 4. AUTHENTICATION
// =========================================================

// Form login
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

// Proses login
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.post');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

// =========================================================
// 5. PENDAFTARAN ORGANISASI
// =========================================================

// Form pendaftaran organisasi
Route::get('/daftar-organisasi', [OrganizationRegistrationController::class, 'create'])
    ->name('organizations.register');

// Proses pendaftaran organisasi
Route::post('/daftar-organisasi', [OrganizationRegistrationController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('organizations.store');

// Status organisasi untuk user yang sudah login
// tetapi organisasinya belum disetujui
Route::get('/organisasi/status', [OrganizationRegistrationController::class, 'pending'])
    ->middleware('auth')
    ->name('organizations.pending');

// =========================================================
// 6. AREA ADMIN & PANITIA
// Hanya user login + organisasi sudah disetujui
// =========================================================

Route::middleware(['auth', 'org_approved'])->group(function () {

    // -----------------------------------------------------
    // Scanner Gate
    // -----------------------------------------------------

    Route::get('/scan', [FrontEndController::class, 'scanIndex'])
        ->name('scan.index');

    Route::post('/scan/validate', [FrontEndController::class, 'scanValidate'])
        ->name('scan.validate');

    // -----------------------------------------------------
    // Panel Admin
    // -----------------------------------------------------

    Route::prefix('admin')
        ->name('admin.')
        ->group(function () {

            // Dashboard
            Route::get('/dashboard', [AdminController::class, 'dashboard'])
                ->name('dashboard');

            // -------------------------------------------------
            // CRUD Event
            // -------------------------------------------------

            Route::get('/events', [AdminController::class, 'eventIndex'])
                ->name('events.index');

            Route::get('/events/create', [AdminController::class, 'eventCreate'])
                ->name('events.create');

            Route::post('/events', [AdminController::class, 'eventStore'])
                ->name('events.store');

            Route::get('/events/{event}/edit', [AdminController::class, 'eventEdit'])
                ->name('events.edit');

            Route::put('/events/{event}', [AdminController::class, 'eventUpdate'])
                ->name('events.update');

            Route::delete('/events/{event}', [AdminController::class, 'eventDestroy'])
                ->name('events.destroy');

            // -------------------------------------------------
            // CRUD Kategori Tiket
            // -------------------------------------------------

            Route::get('/events/{event}/categories', [TicketCategoryController::class, 'index'])
                ->name('categories.index');

            Route::post('/events/{event}/categories', [TicketCategoryController::class, 'store'])
                ->name('categories.store');

            Route::delete('/categories/{category}', [TicketCategoryController::class, 'destroy'])
                ->name('categories.destroy');

            // -------------------------------------------------
            // Riwayat Transaksi & Konfirmasi Pembayaran
            // -------------------------------------------------

            Route::get('/orders', [AdminController::class, 'orderIndex'])
                ->name('orders.index');

            Route::post('/orders/{order}/mark-paid', [AdminController::class, 'orderMarkPaid'])
                ->name('orders.markPaid');
        });
});

// =========================================================
// 7. AREA KHUSUS SUPER ADMIN
// Login + harus super_admin
// =========================================================

Route::middleware(['auth', 'super_admin'])
    ->prefix('super-admin')
    ->name('superadmin.')
    ->group(function () {

        // Daftar organisasi
        Route::get('/organizations', [SuperAdminController::class, 'organizationIndex'])
            ->name('organizations.index');

        // Setujui organisasi
        Route::post('/organizations/{organization}/approve', [SuperAdminController::class, 'organizationApprove'])
            ->name('organizations.approve');

        // Tolak organisasi
        Route::post('/organizations/{organization}/reject', [SuperAdminController::class, 'organizationReject'])
            ->name('organizations.reject');
    });
