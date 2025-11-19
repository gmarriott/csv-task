<?php

use App\Http\Controllers\CsvUploadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Homepage');
})->name('home');

Route::post('/csv-upload', [CsvUploadController::class, 'parse'])
    ->name('csv.upload');
