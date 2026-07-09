<?php

namespace App\Http\Controllers;

use App\Support\WizardSteps;
use Illuminate\Http\Request;
use Illuminate\View\View;

// §5.2 P2 — halaman wizard (membenam komponen Livewire WizardStep).
class WizardController extends Controller
{
    public function show(Request $request, string $token, int $step): View
    {
        abort_unless($step >= 0 && $step < WizardSteps::count(), 404);

        return view('wizard.page', [
            'token' => $token,
            'step' => $step,
        ]);
    }
}
