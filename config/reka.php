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

    // 16 negeri Malaysia (§5.1 dropdown borang minat).
    'states' => [
        'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang',
        'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 'Sarawak', 'Selangor',
        'Terengganu', 'W.P. Kuala Lumpur', 'W.P. Labuan', 'W.P. Putrajaya',
    ],

    // Peta negeri → nilai `state` dalam jadual jakim_zones (untuk tapis zon §6 L1).
    // 3 WP dropdown → zon "Wilayah Persekutuan".
    'state_to_zone_state' => [
        'W.P. Kuala Lumpur' => 'Wilayah Persekutuan',
        'W.P. Labuan' => 'Wilayah Persekutuan',
        'W.P. Putrajaya' => 'Wilayah Persekutuan',
    ],

];
