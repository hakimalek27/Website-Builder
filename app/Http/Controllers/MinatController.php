<?php

namespace App\Http\Controllers;

use App\Mail\NewLeadMail;
use App\Models\Lead;
use App\Services\Turnstile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

// §5.1 — Borang Daftar Minat (lead). Anti-spam: honeypot + rate limit + Turnstile (env-gated).
class MinatController extends Controller
{
    public function create(): View
    {
        return view('minat.form', [
            'states' => config('reka.states'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot: medan tersembunyi 'website_url' — jika terisi, terima SENYAP tanpa simpan (§5.1).
        if (filled($request->input('website_url'))) {
            return redirect()->route('minat.terima-kasih');
        }

        // Turnstile (env-gated) — dilangkau jika tidak dikonfigurasi.
        if (! Turnstile::verify($request->input('cf-turnstile-response'), $request->ip())) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Pengesahan keselamatan gagal. Sila cuba lagi.',
            ]);
        }

        $data = $request->validate([
            'mosque_name' => ['required', 'string', 'max:150'],
            'org_type' => ['required', 'in:masjid,surau,ngo'],
            'state' => ['required', 'string', 'in:'.implode(',', config('reka.states'))],
            'pic_name' => ['required', 'string', 'max:100'],
            'pic_phone' => ['required', 'string', 'regex:/^01[0-9]{8,9}$/'],
            'pic_email' => ['nullable', 'email', 'max:150'],
            'current_website' => ['nullable', 'url', 'max:200'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'pic_phone.regex' => 'Nombor telefon mesti format 01x (cth: 0123456789).',
        ]);

        $lead = Lead::create($data);

        // Notifikasi admin (mail, dibaris).
        $adminEmail = config('reka.admin_notify_email');
        if (filled($adminEmail)) {
            Mail::to($adminEmail)->queue(new NewLeadMail($lead));
        }

        return redirect()->route('minat.terima-kasih');
    }

    public function thanks(): View
    {
        return view('minat.terima-kasih');
    }
}
