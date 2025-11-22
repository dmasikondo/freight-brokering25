<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\Auth\DashboardController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login-as', function() {
    abort(501, 'Unauthorized access to this resource.');
    $user = App\Models\User::where('email', 'd@taraz')->first();
    auth()->login($user);
    return redirect('/users/create');
}); 

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');
Route::view('register2','livewire.auth.register2');

Volt::route('freights','freight.index')->name('freights.index');
Volt::route('lanes','lane.index')->name('lanes.index');


Route::view('about-us','pages.about')->name('about-us');
Route::view('faq','pages.faq')->name('faq');
Route::view('consultancy','pages.consultancy')->name('consultancy');
Route::view('terms','pages.terms')->name('terms');


Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('dashboard',[DashboardController::class, 'dashboard'])->name('dashboard');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Route::get('users/create',[UserController::class, 'create'])->name('users.create');
    Route::get('users',[UserController::class, 'index'])->name('users.index');
    Route::get('users/{user:slug}', [UserController::class, 'show'])->name('users.show');
    Volt::route('users/{slug}/edit', 'auth.register')->name('users.edit');

    Volt::route('territories/create', 'territory.create')->name('territories.create');
    Volt::route('territories','territory.index')->name('territories.index');
    Volt::route('territories/{territory}/edit','territory.create')->name('territories.edit');

    Volt::route('freights/create','freight.create')->name('freights.create');
    Volt::route('freights/{freight}/edit','freight.create')->name('freights.edit');
    Volt::route('freights/{freight}/edit','freight.create')->name('freights.edit');

    Volt::route('lanes/create','lane.create')->name('lane.create');
    Volt::route('lanes/{lane}/edit','lane.create')->name('lanes.edit');


    });

require __DIR__.'/auth.php';
