<?php

use App\Http\Controllers\Admin\Billing\AiPricingController as AdminAiPricingController;
use App\Http\Controllers\Admin\Billing\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\Billing\SubscriptionPlanController as AdminSubscriptionPlanController;
use App\Http\Controllers\Admin\PromptController as AdminPromptController;
use App\Http\Controllers\Billing\BillingPortalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobConversationController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\AiConfigController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('jobs', JobController::class);
    Route::post('jobs/{job}/sessions', [JobSessionController::class, 'store'])
        ->name('jobs.sessions.store')
        ->middleware('subscription');
    Route::post('jobs/{job}/conversations', [JobConversationController::class, 'store'])
        ->name('jobs.conversations.store')
        ->middleware('subscription');

    Route::get('/billing', [BillingPortalController::class, 'index'])->name('billing.index');
    Route::post('/billing/plans/{plan}/checkout', [BillingPortalController::class, 'startCheckout'])->name('billing.checkout');
    Route::get('/billing/checkout/{plan}/success', [BillingPortalController::class, 'checkoutSuccess'])->name('billing.checkout.success');
    Route::get('/billing/checkout/cancel', [BillingPortalController::class, 'checkoutCancel'])->name('billing.checkout.cancel');
    Route::post('/billing/plans/{plan}', [BillingPortalController::class, 'subscribe'])->name('billing.subscribe');

    Route::get('/settings/ai', [AiConfigController::class, 'index'])->middleware('admin')->name('settings.ai');
    Route::put('/settings/ai', [AiConfigController::class, 'update'])->middleware('admin')->name('settings.ai.update');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::resource('prompts', AdminPromptController::class);
        Route::post('prompts/{prompt}/clone', [AdminPromptController::class, 'clone'])->name('prompts.clone');

        Route::prefix('billing')->name('billing.')->group(function () {
            Route::resource('plans', AdminSubscriptionPlanController::class)->except(['show', 'destroy']);
            Route::get('ai-pricing', [AdminAiPricingController::class, 'edit'])->name('ai-pricing.edit');
            Route::put('ai-pricing', [AdminAiPricingController::class, 'update'])->name('ai-pricing.update');
            Route::get('invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
        });
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
