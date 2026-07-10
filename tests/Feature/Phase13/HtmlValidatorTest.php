<?php

use App\Services\Ai\DraftValidationException;
use App\Services\Ai\HtmlDraftValidator;

// §Fasa 13 W3 — validasi HTML draf.

function validateHtml(string $raw): string
{
    return app(HtmlDraftValidator::class)->validate($raw);
}

it('extracts the document from fenced output with surrounding prose', function () {
    $raw = "Berikut drafnya:\n```html\n<!DOCTYPE html><html><head><title>X</title></head><body>Hai</body></html>\n```\nSelesai.";
    expect(validateHtml($raw))->toStartWith('<!DOCTYPE html>')->toEndWith('</html>')->toContain('Hai');
});

it('rejects truncated HTML with no closing tag', function () {
    validateHtml('<!DOCTYPE html><html><body>separuh sahaja');
})->throws(DraftValidationException::class);

it('rejects Arabic characters', function () {
    validateHtml('<html><body>بسم الله</body></html>');
})->throws(DraftValidationException::class);

it('rejects a script tag', function () {
    validateHtml('<html><body><script>alert(1)</script></body></html>');
})->throws(DraftValidationException::class);

it('rejects inline event handlers', function () {
    validateHtml('<html><body><button onclick="x()">Klik</button></body></html>');
})->throws(DraftValidationException::class);

it('rejects a disallowed external url', function () {
    validateHtml('<html><body><img src="https://evil.example/a.png"></body></html>');
})->throws(DraftValidationException::class);

it('allows Google Fonts, data URIs and inline SVG namespace', function () {
    $raw = '<!DOCTYPE html><html><head><link href="https://fonts.googleapis.com/css2?family=Inter" rel="stylesheet"></head>'
        .'<body><img src="data:image/png;base64,AAAA"><svg xmlns="http://www.w3.org/2000/svg"></svg></body></html>';
    expect(validateHtml($raw))->toContain('fonts.googleapis.com');
});

it('rejects HTML larger than the byte cap', function () {
    validateHtml('<!DOCTYPE html><html><body>'.str_repeat('a', 400_001).'</body></html>');
})->throws(DraftValidationException::class);
