<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\WizardProgress;
use Illuminate\Http\Request;
use Illuminate\View\View;

// §5.2 P1 — Selamat datang / sambung wizard PIC.
class PicController extends Controller
{
    public function home(Request $request, WizardProgress $progress): View
    {
        /** @var Project $project */
        $project = $request->attributes->get('project');

        return view('pic.home', [
            'progress' => $progress->forProject($project),
            'token' => $request->route('token'),
        ]);
    }
}
