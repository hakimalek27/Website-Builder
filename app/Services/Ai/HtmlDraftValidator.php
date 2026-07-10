<?php

namespace App\Services\Ai;

/**
 * §Fasa 13 — validasi output HTML draf (Peringkat 2). Selari semangat DraftContentValidator
 * tetapi untuk HTML: (1) ekstrak dokumen doctype hingga penutup html (toleransi prosa/pagar kod);
 * (2) tolak aksara Arab (ayat sebenar disisip pelayan kemudian); (3) tolak skrip, atribut
 * pengendali peristiwa, dan URI javascript; (4) tolak URL luaran selain Google Fonts; (5) had saiz.
 * Gagal → DraftValidationException (gagal percubaan penuh).
 */
class HtmlDraftValidator
{
    public const MAX_BYTES = 400_000;

    /** Julat aksara Arab & bentuk persembahan (sama seperti DraftContentValidator). */
    private const ARABIC_REGEX = '/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u';

    /** Host luaran dibenarkan (fon + ruang nama XML/SVG yang bukan capaian rangkaian). */
    private const ALLOWED_HOSTS = ['fonts.googleapis.com', 'fonts.gstatic.com', 'www.w3.org', 'w3.org'];

    /**
     * @return string Dokumen HTML penuh yang bersih.
     *
     * @throws DraftValidationException
     */
    public function validate(string $raw): string
    {
        // (1) Buang pagar kod + ekstrak <!doctype/<html> … </html>.
        $clean = trim($raw);
        $clean = preg_replace('/^```(?:html)?\s*|\s*```$/m', '', $clean) ?? $clean;
        $html = $this->extractDocument(trim($clean));
        if ($html === null) {
            throw new DraftValidationException('HTML tidak lengkap atau terpotong (tiada <html>…</html>).');
        }

        // (2) Reject aksara Arab (AI dilarang; ayat sebenar disisip pelayan kemudian).
        if (preg_match(self::ARABIC_REGEX, $html)) {
            throw new DraftValidationException('Output HTML mengandungi aksara Arab (dilarang §9.1).');
        }

        // (3) Reject skrip / pengendali peristiwa / javascript: URI.
        if (preg_match('/<script\b/i', $html) || preg_match('/\son\w+\s*=/i', $html) || stripos($html, 'javascript:') !== false) {
            throw new DraftValidationException('Output HTML mengandungi JavaScript (dilarang).');
        }

        // (4) Reject URL luaran selain Google Fonts / ruang nama XML.
        if ($this->hasDisallowedExternalUrl($html)) {
            throw new DraftValidationException('Output HTML memuatkan sumber luaran tidak dibenarkan.');
        }

        // (5) Had saiz (output AI mentah, sebelum suntikan hero data-URI).
        if (strlen($html) > self::MAX_BYTES) {
            throw new DraftValidationException('Output HTML terlalu besar (>'.((int) (self::MAX_BYTES / 1000)).'KB).');
        }

        return $html;
    }

    private function extractDocument(string $s): ?string
    {
        $end = strripos($s, '</html>');
        if ($end === false) {
            return null;
        }
        $startDoctype = stripos($s, '<!doctype');
        $startHtml = stripos($s, '<html');
        $start = $startDoctype !== false ? $startDoctype : $startHtml;
        if ($start === false) {
            return null;
        }

        return trim(substr($s, $start, $end - $start + strlen('</html>')));
    }

    private function hasDisallowedExternalUrl(string $html): bool
    {
        // Padan http(s)://host dan //host (protokol-relatif). data:/anchor/relatif tiada '//host'.
        preg_match_all('#(?:https?:)?//([a-z0-9.\-]+)#i', $html, $m);
        foreach ($m[1] as $host) {
            if (! in_array(strtolower($host), self::ALLOWED_HOSTS, true)) {
                return true;
            }
        }

        return false;
    }
}
