
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FrontEndController;
use App\Http\Controllers\OrganizationProfileController;
use App\Http\Controllers\OrganizationRegistrationController;
use App\Http\Controllers\SeatMapController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TicketCategoryController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WithdrawalController;

/*
|--------------------------------------------------------------------------
| 1. Halaman Publik
|--------------------------------------------------------------------------
*/

Route::get('/', [FrontEndController::class, 'index'])
    ->name('home');

Route::get('/jelajahi', [FrontEndController::class, 'marketplace'])
    ->name('marketplace.index');

Route::get('/event/{event}', [FrontEndController::class, 'show'])
    ->name('event.show');

Route::get('/e-ticket/{merchant_ref}', [FrontEndController::class, 'ticket'])
    ->name('ticket.show');

/*
|--------------------------------------------------------------------------
| 2. Cek Tiket
|--------------------------------------------------------------------------
*/

Route::get('/cek-tiket', [TicketController::class, 'check'])
    ->name('ticket.check');

Route::post('/cek-tiket', [TicketController::class, 'search'])
    ->middleware('throttle:5,1')
    ->name('ticket.search');

Route::get('/cek-tiket/verifikasi', [TicketController::class, 'verify'])
    ->middleware('signed')
    ->name('ticket.check.verify');

/*
|--------------------------------------------------------------------------
| 3. Checkout dan Tripay
|--------------------------------------------------------------------------
*/

Route::post('/checkout', [CheckoutController::class, 'store'])
    ->name('checkout.store');

Route::post('/tripay/callback', [CheckoutController::class, 'callback'])
    ->name('tripay.callback');

/*
|--------------------------------------------------------------------------
| 4. Authentication
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.post');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

/*
|--------------------------------------------------------------------------
| 5. Pendaftaran Organisasi
|--------------------------------------------------------------------------
*/

Route::get('/daftar-organisasi', [
    OrganizationRegistrationController::class,
    'create',
])->name('organizations.register');

Route::post('/daftar-organisasi', [
    OrganizationRegistrationController::class,
    'store',
])
    ->middleware('throttle:5,1')
    ->name('organizations.store');

Route::get('/organisasi/status', [
    OrganizationRegistrationController::class,
    'pending',
])
    ->middleware('auth')
    ->name('organizations.pending');

/*
|--------------------------------------------------------------------------
| 6. Area Admin dan Panitia
|--------------------------------------------------------------------------
| Memerlukan login dan organisasi yang telah disetujui.
*/

Route::middleware(['auth', 'org_approved'])->group(function () {

    // Scanner tiket
    Route::get('/scan', [FrontEndController::class, 'scanIndex'])
        ->name('scan.index');

    Route::post('/scan/validate', [
        FrontEndController::class,
        'scanValidate',
    ])->name('scan.validate');

    // Panel admin
    Route::prefix('admin')
        ->name('admin.')
        ->group(function () {

            // Dashboard
            Route::get('/dashboard', [AdminController::class, 'dashboard'])
                ->name('dashboard');

            // Profil organisasi
            Route::get('/organisasi', [
                OrganizationProfileController::class,
                'edit',
            ])->name('organization.edit');

            Route::put('/organisasi', [
                OrganizationProfileController::class,
                'update',
            ])->name('organization.update');

            // CRUD event
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

            // Kategori tiket
            Route::get('/events/{event}/categories', [
                TicketCategoryController::class,
                'index',
            ])->name('categories.index');

            Route::post('/events/{event}/categories', [
                TicketCategoryController::class,
                'store',
            ])->name('categories.store');

            Route::delete('/categories/{category}', [
                TicketCategoryController::class,
                'destroy',
            ])->name('categories.destroy');

            // Peta kursi
            Route::get('/events/{event}/seats', [
                SeatMapController::class,
                'edit',
            ])->name('events.seats');

            Route::post('/events/{event}/seat-map', [
                SeatMapController::class,
                'uploadImage',
            ])->name('events.seatmap.upload');

            Route::post('/events/{event}/seats', [
                SeatMapController::class,
                'storeSeat',
            ])->name('events.seats.store');

            Route::delete('/seats/{seat}', [
                SeatMapController::class,
                'destroySeat',
            ])->name('seats.destroy');

            // Riwayat transaksi
            Route::get('/orders', [AdminController::class, 'orderIndex'])
                ->name('orders.index');

            Route::post('/orders/{order}/mark-paid', [
                AdminController::class,
                'orderMarkPaid',
            ])->name('orders.markPaid');

            // Penarikan dana
            Route::get('/penarikan', [WithdrawalController::class, 'index'])
                ->name('withdrawals.index');

            Route::post('/penarikan', [WithdrawalController::class, 'store'])
                ->name('withdrawals.store');
        });
});

/*
|--------------------------------------------------------------------------
| 7. Area Super Admin
|--------------------------------------------------------------------------
| Memerlukan login dan role super_admin.
*/

Route::middleware(['auth', 'super_admin'])
    ->prefix('super-admin')
    ->name('superadmin.')
    ->group(function () {

        // Manajemen organisasi
        Route::get('/organizations', [
            SuperAdminController::class,
            'organizationIndex',
        ])->name('organizations.index');

        Route::post('/organizations/{organization}/approve', [
            SuperAdminController::class,
            'organizationApprove',
        ])->name('organizations.approve');

        Route::post('/organizations/{organization}/reject', [
            SuperAdminController::class,
            'organizationReject',
        ])->name('organizations.reject');

        // Manajemen penarikan
        Route::get('/withdrawals', [
            SuperAdminController::class,
            'withdrawalIndex',
        ])->name('withdrawals.index');

        Route::post('/withdrawals/{withdrawalRequest}/complete', [
            SuperAdminController::class,
            'withdrawalComplete',
        ])->name('withdrawals.complete');

        Route::post('/withdrawals/{withdrawalRequest}/reject', [
            SuperAdminController::class,
            'withdrawalReject',
        ])->name('withdrawals.reject');
    });
