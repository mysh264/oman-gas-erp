<?php

use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/pdf/invoice/{invoice}', [PdfController::class, 'invoice'])->name('pdf.invoice');
Route::get('/pdf/contract/{contract}', [PdfController::class, 'contract'])->name('pdf.contract');

require __DIR__.'/auth.php';
