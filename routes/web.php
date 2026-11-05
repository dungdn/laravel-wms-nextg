<?php

use App\Livewire\Category\Index as CategoryIndex;
use App\Livewire\Product\Index as ProductIndex;
use App\Livewire\Supplier\Index as SupplierIndex;
use App\Livewire\Warehouse\Index as WarehouseIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/suppliers', SupplierIndex::class)
        ->name('suppliers.index');

    Route::get('/categories', CategoryIndex::class)
        ->name('categories.index');

    Route::get('/products', ProductIndex::class)
        ->name('products.index');

    Route::get('/warehouses', WarehouseIndex::class)
        ->name('warehouses.index');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('dev/ui-components', 'dev.ui-components')
    ->middleware(['auth'])
    ->name('dev.ui-components');

require __DIR__.'/auth.php';
