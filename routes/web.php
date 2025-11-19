<?php

use App\Http\Controllers\Admin\PromptController as AdminPromptController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobConversationController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\AiConfigController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');

Route::get('/auth/redirect/google/redirect', [SocialiteController::class, 'redirect'])->name('oauth.google.redirect');
Route::get('/auth/redirect/google/callback', [SocialiteController::class, 'callback'])->name('oauth.google.callback');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('jobs', JobController::class);
    Route::post('jobs/{job}/sessions', [JobSessionController::class, 'store'])->name('jobs.sessions.store');
    Route::post('jobs/{job}/conversations', [JobConversationController::class, 'store'])->name('jobs.conversations.store');

    Route::get('/settings/ai', [AiConfigController::class, 'index'])->middleware('admin')->name('settings.ai');
    Route::put('/settings/ai', [AiConfigController::class, 'update'])->middleware('admin')->name('settings.ai.update');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::resource('prompts', AdminPromptController::class);
        Route::post('prompts/{prompt}/clone', [AdminPromptController::class, 'clone'])->name('prompts.clone');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
