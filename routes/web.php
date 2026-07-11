<?php

use App\Http\Controllers\AdminFileController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MinatController;
use App\Http\Controllers\PicController;
use App\Http\Controllers\SemakController;
use App\Http\Controllers\TweakController;
use App\Http\Controllers\WizardController;
use App\Models\Project;
use App\Services\DraftGenerationService;
use Illuminate\Http\Request;
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

    // P3 Semak & Hantar.
    Route::get('/semak', [SemakController::class, 'show'])->name('semak');
    Route::post('/hantar', [SemakController::class, 'submit'])->name('submit');

    // P4 Hab penjanaan draf.
    Route::get('/jana', function (Request $request) {
        // §Fasa 16 — mod templat: tiada penjanaan AI → alih ke Status.
        if (DraftGenerationService::pipelineMode() === 'template') {
            return redirect()->route('pic.status', ['token' => $request->route('token')]);
        }

        return view('pic.jana', ['token' => $request->route('token')]);
    })->name('jana');

    // P5/P6 Pemapar draf.
    Route::get('/draf/{generation}', [DraftController::class, 'show'])->name('draf');
    Route::get('/draf/{generation}/penuh', [DraftController::class, 'raw'])->name('draf.raw');

    // Aset bertoken (thumbnail hero/logo/galeri dalam wizard) — semak milik projek.
    Route::get('/aset/{asset}', [DraftController::class, 'asset'])->name('aset');

    // P7 Tweak reka bentuk (percuma).
    Route::get('/tweak/reka', [TweakController::class, 'reka'])->name('tweak.reka');
    Route::post('/tweak/reka', [TweakController::class, 'rekaRender'])->name('tweak.reka.render');

    // P8 Tweak kandungan (AI).
    Route::get('/tweak/kandungan', [TweakController::class, 'kandungan'])->name('tweak.kandungan');
    Route::post('/tweak/kandungan', [TweakController::class, 'kandunganSubmit'])->name('tweak.kandungan.submit');

    // P9 Kelulusan.
    Route::get('/lulus', [ApprovalController::class, 'show'])->name('lulus');
    Route::post('/lulus', [ApprovalController::class, 'store'])->name('lulus.store');

    // P10 Status + thread nota (§5.2 P10/P11).
    Route::get('/status', function (Request $request) {
        /** @var Project $project */
        $project = $request->attributes->get('project');

        return view('pic.status', [
            'token' => $request->route('token'),
            'notes' => $project->notes()->oldest()->get(),
            'templateMode' => DraftGenerationService::pipelineMode() === 'template',
            'step2' => $project->sections()->where('section_key', 'step_2')->first()?->data ?? [],
        ]);
    })->name('status');
    Route::post('/nota', [PicController::class, 'storeNote'])->name('nota');
});

// --- Fail admin (aset + draf dari panel) — sesi web + semakan auth dalam controller (Fasa 12 W2) ---
Route::middleware('web')->prefix('/admin-fail')->name('admin.')->group(function () {
    Route::get('/aset/{asset}', [AdminFileController::class, 'asset'])->name('aset');
    Route::get('/draf/{generation}', [AdminFileController::class, 'draft'])->name('draf');
    Route::get('/prompt/{generation}', [AdminFileController::class, 'prompt'])->name('prompt');           // §Fasa 13
    Route::get('/draf/{generation}/muat', [AdminFileController::class, 'draftDownload'])->name('draf.muat'); // §Fasa 13
});
