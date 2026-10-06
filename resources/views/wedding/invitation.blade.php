<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $content['partner_one'] }} &amp; {{ $content['partner_two'] }} — Moshia Wedding</title>
    <style>
        :root { color-scheme: dark; font-family: Georgia, serif; background: #111113; color: #fafafa; }
        body { margin: 0; padding: 24px; }
        main { max-width: 720px; margin: 8vh auto; text-align: center; padding: 40px 24px; border: 1px solid #453035; border-radius: 24px; background: #191719; overflow-wrap: anywhere; }
        .kicker { color: #f27782; letter-spacing: .2em; font: 12px sans-serif; text-transform: uppercase; }
        h1 { font-size: clamp(32px, 7vw, 64px); font-weight: normal; line-height: 1.2; }
        p { line-height: 1.8; white-space: pre-line; }
        .preview { font: 14px sans-serif; color: #f27782; text-align: center; }
        footer { margin-top: 48px; font: 12px sans-serif; letter-spacing: .2em; color: #aaa; }
    </style>
</head>
<body>
    @if ($preview)<p class="preview">Preview privat — belum mengubah undangan publik</p>@endif
    <main>
        <span class="kicker">The Wedding of</span>
        <h1>{{ $content['partner_one'] }}<br>&amp;<br>{{ $content['partner_two'] }}</h1>
        <p>{{ \Carbon\CarbonImmutable::parse($content['event_date'])->locale('id')->translatedFormat('d F Y') }}</p>
        <p>{{ $content['venue'] }}</p>
        <p>{{ $content['message'] ?? '' }}</p>
        <footer>MOSHIA WEDDING</footer>
    </main>
</body>
</html>
