<?php

use App\Livewire\Supplier\Index;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/suppliers', Index::class)
        ->name('suppliers.index');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('dev/ui-components', 'dev.ui-components')
    ->middleware(['auth'])
    ->name('dev.ui-components');

require __DIR__.'/auth.php';
