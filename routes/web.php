<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FrontEndController;
use App\Http\Controllers\OrganizationProfileController;
use App\Http\Controllers\OrganizationRegistrationController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TicketCategoryController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WithdrawalController;

// =========================================================
// 1. HALAMAN PUBLIK
// =========================================================

// Halaman utama
Route::get('/', [FrontEndController::class, 'index'])
    ->name('home');

// Marketplace / Jelajahi
Route::get('/jelajahi', [FrontEndController::class, 'marketplace'])
    ->name('marketplace.index');

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

// Status organisasi
Route::get('/organisasi/status', [OrganizationRegistrationController::class, 'pending'])
    ->middleware('auth')
    ->name('organizations.pending');

// =========================================================
// 6. AREA ADMIN & PANITIA
// Login + organisasi sudah disetujui
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
            // Profil Organisasi
            // -------------------------------------------------

            Route::get('/organisasi', [OrganizationProfileController::class, 'edit'])
                ->name('organization.edit');

            Route::put('/organisasi', [OrganizationProfileController::class, 'update'])
                ->name('organization.update');

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

            // -------------------------------------------------
            // Penarikan Dana
            // -------------------------------------------------

            Route::get('/penarikan', [WithdrawalController::class, 'index'])
                ->name('withdrawals.index');

            Route::post('/penarikan', [WithdrawalController::class, 'store'])
                ->name('withdrawals.store');
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

        // -------------------------------------------------
        // Manajemen Organisasi
        // -------------------------------------------------

        Route::get('/organizations', [SuperAdminController::class, 'organizationIndex'])
            ->name('organizations.index');

        Route::post('/organizations/{organization}/approve', [SuperAdminController::class, 'organizationApprove'])
            ->name('organizations.approve');

        Route::post('/organizations/{organization}/reject', [SuperAdminController::class, 'organizationReject'])
            ->name('organizations.reject');

        // -------------------------------------------------
        // Manajemen Penarikan
        // -------------------------------------------------

        Route::get('/withdrawals', [SuperAdminController::class, 'withdrawalIndex'])
            ->name('withdrawals.index');

        Route::post('/withdrawals/{withdrawalRequest}/complete', [SuperAdminController::class, 'withdrawalComplete'])
            ->name('withdrawals.complete');

        Route::post('/withdrawals/{withdrawalRequest}/reject', [SuperAdminController::class, 'withdrawalReject'])
            ->name('withdrawals.reject');
    });
