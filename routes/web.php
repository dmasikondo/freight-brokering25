<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login-as', function() {
    $user = App\Models\User::where('email', 'd@taraz')->first();
    auth()->login($user);
    return redirect('/users/create');
}); 

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
Route::view('register2','livewire.auth.register2');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

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


    });

require __DIR__.'/auth.php';
