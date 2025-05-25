<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\PaymentNotificationController;

Route::get('/', function () {
    return redirect()->route('filament.admin.pages.dashboard');
});

// Route::get('/print', function () {
//     return view('pages.print-nota');
// })->name('print.note');

Route::prefix('print')->group(function () {
    Route::get('/purchase/{inv}', [PrinterController::class, 'printInvoicePurchase'])->name('print.purchase');
    Route::get('/sale/{inv}', [PrinterController::class, 'printInvoiceSale'])->name('print.sale');
})->middleware('auth');

// Route::get('/private/storage/{path}', function ($path) {
//     if (!Auth::check()) {
//         abort(403, 'Unauthorized.');
//     }

//     $path = str_replace('..', '', $path); // simple protection
//     return Storage::disk('private')->response($path);
// })->where('path', '.*')->middleware('auth')->name('private');
Route::post('/payed', PaymentNotificationController::class);
