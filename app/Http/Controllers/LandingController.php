<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LandingController extends Controller
{
    // §5.1 Landing — nilai tawaran, cara berfungsi, perbandingan vs platform percuma (§1.2).
    public function index(): View
    {
        return view('landing');
    }
}
