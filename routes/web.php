<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\MinatController;
use App\Http\Controllers\PicController;
use App\Http\Controllers\WizardController;
use Illuminate\Support\Facades\Route;

// --- Awam (tiada auth) — §5.1 ---

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/minat', [MinatController::class, 'create'])->name('minat.create');
Route::post('/minat', [MinatController::class, 'store'])
    ->middleware('throttle:5,1') // §11.2 — 5/min/IP
    ->name('minat.store');
Route::get('/minat/terima-kasih', [MinatController::class, 'thanks'])->name('minat.terima-kasih');

// Notis privasi / terma dwibahasa — kandungan penuh dibina Fasa 9.
Route::view('/privasi', 'legal.privasi')->name('privasi');
Route::view('/terma', 'legal.terma')->name('terma');

// --- PIC (bertoken) — §5.2. Middleware resolve.invitation ---

Route::prefix('/b/{token}')->middleware('resolve.invitation')->name('pic.')->group(function () {
    Route::get('/', [PicController::class, 'home'])->name('home'); // P1

    // P2 wizard langkah.
    Route::get('/langkah/{step}', [WizardController::class, 'show'])
        ->whereNumber('step')->name('step');
});
