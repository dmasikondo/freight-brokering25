<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\DashboardController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Volt::route('freights', 'freight.index')->name('freights.index');
Volt::route('lanes', 'lane.index')->name('lanes.index');

Route::view('register', 'users.register')->name('register')->middleware('guest');
Route::view('about-us', 'pages.about')->name('about-us');
Route::view('faq', 'pages.faq')->name('faq');
Route::view('consultancy', 'pages.consultancy')->name('consultancy');
Route::view('terms', 'pages.terms')->name('terms');


Route::middleware(['auth',])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user:slug}', [UserController::class, 'show'])->name('users.show');
    //Volt::route('users/{slug}/edit', 'auth.register')->name('users.edit');
    Route::get('users/{user:slug}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::post('users/{user:slug}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
    Route::post('users/{user:slug}/unsuspend', [UserController::class, 'unsuspend'])->name('users.unsuspend');
    Route::patch('/users/{user:slug}/approve', [UserController::class, 'approve'])->name('users.approve');

    Volt::route('territories/create', 'territory.create')->name('territories.create');
    Volt::route('territories', 'territory.index')->name('territories.index');
    Volt::route('territories/{territory}/edit', 'territory.create')->name('territories.edit');

    Volt::route('freights/create', 'freight.create')->name('freights.create');
    Volt::route('freights/{freight}/edit', 'freight.create')->name('freights.edit');
    Volt::route('freights/{freight}/edit', 'freight.create')->name('freights.edit');

    Volt::route('lanes/create', 'lane.create')->name('lanes.create');
    Volt::route('lanes/{lane}/edit', 'lane.create')->name('lanes.edit');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::get('/notifications/{notification}/read-and-view', [NotificationController::class, 'readAndView'])
    ->name('notifications.readAndView');
});

require __DIR__ . '/auth.php';
