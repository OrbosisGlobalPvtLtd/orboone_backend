<?php

use App\Http\Controllers\Web\CRM\ClientController;


/*
|--------------------------------------------------------------------------
| CRM / Client / Customer Routes
|--------------------------------------------------------------------------
| Agar future me CRM alag module banega to inko separate file me move kar dena.
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/client_data', [ClientController::class, 'client_data'])->name('client_data');
    Route::get('/client_add', [ClientController::class, 'showForm'])->name('pages.client_add');
    Route::post('/client_add', [ClientController::class, 'client_add'])->name('pages.client_add');
    Route::get('/client_data_print', [ClientController::class, 'client_data_print'])->name('pages.client_data_print');

    Route::get('/customer_data', [CustomerController::class, 'customer_data'])->name('customer_data');
    Route::get('/customer_add', [CustomerController::class, 'showForm'])->name('pages.customer_add');
    Route::post('/customer_add', [CustomerController::class, 'customer_add'])->name('cpages.customer_add');
    Route::get('/customer_print', [CustomerController::class, 'customer_print'])->name('pages.customer_print');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
});