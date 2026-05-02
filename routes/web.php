<?php

use App\Http\Controllers\Admin\PropertyController as AdminPropertyController;
use App\Http\Controllers\Admin\RevenueController as AdminRevenueController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LineChatController;
use App\Http\Controllers\ChargeController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PropertySwitchController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ParcelController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// ─── Public Payment (tenant-facing, no auth required) ─────────────────────────
Route::get('/pay/{invoice}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/pay/{invoice}/promptpay', [PaymentController::class, 'createPromptPay'])->name('payment.promptpay');
Route::post('/pay/{invoice}/card', [PaymentController::class, 'createCreditCard'])->name('payment.card');
Route::get('/pay/{invoice}/callback', [PaymentController::class, 'callback'])->name('payment.callback');
Route::get('/pay/{invoice}/success', [PaymentController::class, 'success'])->name('payment.success');

// ─── Omise Webhook (no auth, no CSRF) ─────────────────────────────────────────
Route::post('/webhooks/omise', [PaymentController::class, 'webhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.omise');

// ─── LINE OA Webhook per-property (no auth, no CSRF) ──────────────────────────
Route::post('/webhooks/line/{property}', [LineChatController::class, 'webhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.line');

// ─── Authenticated routes ──────────────────────────────────────────────────────
Route::middleware(['auth', 'role:super_admin,owner,staff'])->group(function () {
    Route::redirect('/', '/dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/switch-property', [PropertySwitchController::class, 'switch'])->name('property.switch');

    // Rooms
    Route::resource('room-types', RoomTypeController::class);
    Route::resource('rooms', RoomController::class);

    // Tenants & Rentals
    Route::resource('tenants', TenantController::class);
    Route::resource('rentals', RentalController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('rentals/{rental}/terminate', [RentalController::class, 'terminate'])->name('rentals.terminate');

    // Contracts
    Route::resource('contracts', ContractController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('contracts/{contract}/sign', [ContractController::class, 'sign'])->name('contracts.sign');
    Route::get('contracts/{contract}/documents/{type}/{index?}', [ContractController::class, 'document'])
        ->whereIn('type', ['contract', 'id-card', 'paper'])
        ->whereNumber('index')
        ->name('contracts.documents.show');

    // Meter readings
    Route::get('meter-readings', [MeterReadingController::class, 'index'])->name('meter-readings.index');
    Route::post('meter-readings', [MeterReadingController::class, 'store'])->name('meter-readings.store');

    // Invoices
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/generate', [InvoiceController::class, 'generateForm'])->name('invoices.generate-form');
    Route::post('invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('invoices/{invoice}/mark-paid', [PaymentController::class, 'markPaidManual'])->name('invoices.mark-paid');
    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

    // Charges
    Route::resource('charges', ChargeController::class)->except(['show']);
    Route::post('charges/{charge}/assign-room', [ChargeController::class, 'assignRoom'])->name('charges.assign-room');
    Route::delete('charges/{charge}/rooms/{room}', [ChargeController::class, 'detachRoom'])->name('charges.detach-room');

    // Parcels / Mail
    Route::get('parcels', [ParcelController::class, 'index'])->name('parcels.index');
    Route::get('parcels/create', [ParcelController::class, 'create'])->name('parcels.create');
    Route::post('parcels', [ParcelController::class, 'store'])->name('parcels.store');
    Route::patch('parcels/{parcel}/collect', [ParcelController::class, 'markCollected'])->name('parcels.collect');
    Route::post('parcels/{parcel}/resend', [ParcelController::class, 'resendNotify'])->name('parcels.resend');

    // LINE OA Chat
    Route::get('line-chat', [LineChatController::class, 'index'])->name('line-chat.index');
    Route::get('line-chat/broadcast', [LineChatController::class, 'broadcast'])->name('line-chat.broadcast');
    Route::post('line-chat/broadcast', [LineChatController::class, 'sendBroadcast'])->name('line-chat.broadcast.send');
    Route::get('line-chat/{conversation}', [LineChatController::class, 'show'])->name('line-chat.show');
    Route::post('line-chat/{conversation}/reply', [LineChatController::class, 'reply'])->name('line-chat.reply');
    Route::patch('line-chat/{conversation}/label', [LineChatController::class, 'updateLabel'])->name('line-chat.label');

    // Maintenance
    Route::get('maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::get('maintenance/create', [MaintenanceController::class, 'create'])->name('maintenance.create');
    Route::post('maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::get('maintenance/{maintenance}', [MaintenanceController::class, 'show'])->name('maintenance.show');
    Route::patch('maintenance/{maintenance}', [MaintenanceController::class, 'update'])->name('maintenance.update');

    // Settings
    Route::get('settings/rates', [SettingController::class, 'rates'])->name('settings.rates');
    Route::post('settings/rates', [SettingController::class, 'storeRate'])->name('settings.rates.store');
    Route::get('settings/line', [SettingController::class, 'line'])->name('settings.line');
    Route::post('settings/line', [SettingController::class, 'storeLine'])->name('settings.line.store');
    Route::middleware('admin')->group(function () {
        Route::get('settings/line/rich-menu', [SettingController::class, 'richMenu'])->name('settings.line.rich-menu');
        Route::post('settings/line/rich-menu', [SettingController::class, 'storeRichMenu'])->name('settings.line.rich-menu.store');
    });
    Route::get('settings/payment', [SettingController::class, 'payment'])->name('settings.payment');
    Route::post('settings/payment', [SettingController::class, 'storePayment'])->name('settings.payment.store');

    // Super Admin only
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', AdminUserController::class);
        Route::resource('properties', AdminPropertyController::class);
        Route::resource('revenues', AdminRevenueController::class)->only(['index', 'show']);
    });
});
