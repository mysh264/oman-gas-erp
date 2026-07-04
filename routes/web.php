<?php

use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/pdf/invoice/{invoice}', [PdfController::class, 'invoice'])->name('pdf.invoice');
Route::get('/pdf/contract/{id}', [PdfController::class, 'printContract'])->name('pdf.contract');
Route::get('/pdf/handoff', [PdfController::class, 'printHandoff'])->name('pdf.handoff');

require __DIR__.'/auth.php';
