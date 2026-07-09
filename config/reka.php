<?php

// Konfigurasi khusus REKA (§16.C). Pusatkan akses env supaya boleh di-cache.
return [

    // Email admin untuk notifikasi (lead baharu, submitted, dll).
    'admin_notify_email' => env('ADMIN_NOTIFY_EMAIL'),

    // Nama perniagaan untuk notis privasi / consent PDPA (§12.4, §16.B).
    'business_name' => env('REKA_BUSINESS_NAME', 'Wehdah Solution'),

    // Cloudflare Turnstile — kosong = dilangkau sepenuhnya (§5.1).
    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

];
