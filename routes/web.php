<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login-as-latest', function() {
    $user = App\Models\User::latest('id')->first();
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
   
    });

require __DIR__.'/auth.php';
