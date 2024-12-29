<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PassbookController;


Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }

    return redirect()->route('login');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Volt::route('/home', 'pages.home.home')->name('home');

Route::get('/passbook/{id}/view-pdf', [PassbookController::class, 'viewPdf'])->name('passbook.view_pdf');

require __DIR__.'/auth.php';

Route::get('/test', function(){

});
