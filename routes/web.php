<?php

use App\Http\Controllers\PrinterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.pages.dashboard');
});

// Route::get('/print', function () {
//     return view('pages.print-nota');
// })->name('print.note');

Route::prefix('print')->group(function () {
    Route::get('/purchase/{inv}', [PrinterController::class, 'printInvoicePurchase'])->name('print.purchase');
});
