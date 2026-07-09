<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// §12.8 — pemadaman berkala mengikut tempoh penyimpanan PDPA (harian).
Schedule::command('reka:prune')->daily();
