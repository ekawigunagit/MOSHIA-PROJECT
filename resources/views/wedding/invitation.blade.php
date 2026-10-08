<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="{{ ($content['template'] ?? '') === 'midnight' ? 'dark' : 'light' }}">
    <title>{{ $content['partner_one'] }} &amp; {{ $content['partner_two'] }} — Moshia Wedding</title>
    <style>
        :root{--paper:{{ $theme['background'] }};--ink:{{ $theme['text'] }};--accent:{{ $theme['accent'] }}}
        *{box-sizing:border-box}html{scroll-behavior:smooth;scroll-padding-top:60px}body{margin:0;background:var(--paper);color:var(--ink);font-family:Georgia,serif;overflow-wrap:anywhere}a{color:inherit}button,input,select,textarea{font:inherit}a:focus-visible,button:focus-visible,input:focus-visible,textarea:focus-visible,select:focus-visible{outline:3px solid var(--accent);outline-offset:4px}
        .preview{position:sticky;top:0;z-index:10;text-align:center;background:var(--accent);color:var(--paper);padding:13px 20px;font:12px Arial,sans-serif}
        .hero{min-height:88vh;position:relative;display:flex;align-items:center;justify-content:center;padding:70px 24px;text-align:center;border-bottom:1px solid color-mix(in srgb,var(--accent) 25%,transparent)}
        .hero-photo{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}.hero.has-photo::after{content:'';position:absolute;inset:0;background:linear-gradient(#0005,#0009)}.hero.has-photo{color:white}
        .hero-content{position:relative;z-index:1;width:min(720px,100%)}.eyebrow{font:10px Arial,sans-serif;letter-spacing:.28em;text-transform:uppercase}.hero h1{font-size:clamp(42px,8vw,84px);font-weight:400;line-height:1.08;margin:30px 0}.hero h1 em{display:block;font-size:.5em;margin:16px;font-weight:400}.hero p{font-size:16px;line-height:1.8}.button{display:inline-block;border:1px solid currentColor;border-radius:30px;padding:13px 26px;text-decoration:none;background:transparent;color:inherit;font:12px Arial,sans-serif;cursor:pointer}.button.solid{background:var(--accent);color:var(--paper);border-color:var(--accent)}
        .hero .button{margin-top:22px}.section{width:min(850px,calc(100% - 40px));margin:0 auto;padding:70px 0;text-align:center;border-bottom:1px solid color-mix(in srgb,var(--accent) 22%,transparent)}.section h2{font-weight:400;font-size:clamp(30px,5vw,44px);margin:16px 0 24px}.prose{font-size:16px;line-height:2;white-space:pre-line;max-width:640px;margin:20px auto}.ornament{color:var(--accent);font-size:28px;margin:20px}
        .couple{display:grid;grid-template-columns:1fr 1fr;gap:40px;margin-top:36px}.person img{width:180px;height:240px;border-radius:100px 100px 8px 8px;object-fit:cover}.person h3{font-size:28px;font-weight:400;margin:20px 0 12px}.person p{font-size:14px;white-space:pre-line;line-height:1.8}
        .events{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:20px;margin:32px 0}.event{padding:32px 20px;border:1px solid color-mix(in srgb,var(--accent) 28%,transparent);border-radius:90px 90px 12px 12px}.event h3{font-size:23px;font-weight:400}.event p{line-height:1.8;white-space:pre-line}.countdown{font:13px Arial,sans-serif;color:var(--accent);margin-top:25px}
        .gallery{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}.gallery a{display:block}.gallery img{width:100%;height:260px;object-fit:cover;border-radius:6px}.gallery a:nth-child(3n+2){padding-top:22px}
        .guest-form{max-width:520px;margin:30px auto;text-align:left;padding:26px;border:1px solid color-mix(in srgb,var(--accent) 28%,transparent);border-radius:16px}.guest-form label{display:block;font:12px Arial,sans-serif;margin-bottom:8px}.guest-form input,.guest-form select,.guest-form textarea{display:block;width:100%;margin-bottom:20px;border:1px solid color-mix(in srgb,var(--accent) 35%,transparent);border-radius:8px;padding:12px;color:var(--ink);background:var(--paper);font:14px Arial,sans-serif}.guest-form textarea{resize:vertical}.guest-form small{display:block;font:11px/1.7 Arial,sans-serif;opacity:.8;margin:16px 0}.honey{position:absolute;left:-10000px}.feedback{font:13px/1.8 Arial,sans-serif;padding:18px;border:1px solid var(--accent);border-radius:10px;margin-bottom:24px}
        .wishes{display:grid;gap:16px;text-align:left;margin-top:35px}.wish{padding:22px;border-bottom:1px solid color-mix(in srgb,var(--accent) 22%,transparent)}.wish p{white-space:pre-line;line-height:1.8}.wish strong{font:600 13px Arial,sans-serif;color:var(--accent)}
        .music{position:sticky;bottom:12px;z-index:5;display:flex;justify-content:center;pointer-events:none}.music audio{width:min(310px,90vw);pointer-events:auto;box-shadow:0 5px 30px #0002;border-radius:30px}
        footer{text-align:center;padding:60px 20px;font:10px/2 Arial,sans-serif;letter-spacing:.18em;opacity:.8}
        @media(max-width:600px){.couple{grid-template-columns:1fr;gap:35px}.gallery{grid-template-columns:repeat(2,1fr)}.gallery a:nth-child(3n+2){padding-top:0}.gallery img{height:200px}.hero{min-height:80vh}.section{padding:50px 0}.guest-form{padding:20px}}
        @media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}}
    </style>
</head>
<body>
@php
    $photo = fn ($key) => isset($content[$key], $mediaUrls[$content[$key]]) ? $mediaUrls[$content[$key]] : null;
    $zone = $content['timezone'] ?? 'Asia/Jakarta';
    $zoneLabel = ['Asia/Jakarta' => 'WIB', 'Asia/Makassar' => 'WITA', 'Asia/Jayapura' => 'WIT'][$zone] ?? 'WIB';
    $formatDate = fn ($date) => \Carbon\CarbonImmutable::parse($date)->locale('id')->translatedFormat('l, d F Y');
    $eventAt = \Carbon\CarbonImmutable::parse($content['event_date'].' '.($content['event_time'] ?? '00:00'), $zone)->toIso8601String();
@endphp
@if($preview)<div class="preview">Preview privat — belum mengubah undangan publik. Form tamu tidak mengirim respons pada preview.</div>@endif
<header class="hero {{ $photo('cover_id') ? 'has-photo' : '' }}">
    @if($photo('cover_id'))<img class="hero-photo" src="{{ $photo('cover_id') }}" alt="Foto sampul pasangan">@endif
    <div class="hero-content"><span class="eyebrow">The Wedding of</span><h1>{{ $content['partner_one'] }}<em>&amp;</em>{{ $content['partner_two'] }}</h1><p>{{ $formatDate($content['event_date']) }}</p><a class="button" href="#couple">Buka undangan ↓</a></div>
</header>
<main>
    <section id="couple" class="section"><span class="eyebrow">Dengan penuh kebahagiaan</span><h2>Kami mengundang Anda</h2><p class="prose">{{ $content['message'] ?? '' }}</p><div class="ornament" aria-hidden="true">❦</div>
        <div class="couple">
            @foreach(['partner_one','partner_two'] as $partner)
            <article class="person">@if($photo($partner.'_photo_id'))<img src="{{ $photo($partner.'_photo_id') }}" alt="Foto {{ $content[$partner] }}" loading="lazy">@endif<h3>{{ $content[$partner] }}</h3><p>{{ $content[$partner.'_parents'] ?? '' }}</p></article>
            @endforeach
        </div>
    </section>
    @if(!empty($content['story']))<section class="section"><span class="eyebrow">Our story</span><h2>Perjalanan kami</h2><p class="prose">{{ $content['story'] }}</p></section>@endif
    <section class="section" id="event"><span class="eyebrow">Save the date</span><h2>Hari istimewa kami</h2><p class="countdown" data-countdown="{{ $eventAt }}"></p>
        <div class="events"><article class="event"><div class="ornament" aria-hidden="true">◇</div><h3>Acara utama</h3><p>{{ $formatDate($content['event_date']) }}</p>@if(!empty($content['event_time']))<p>{{ $content['event_time'] }} {{ $zoneLabel }}</p>@endif<p>{{ $content['venue'] }}</p></article>
        @if(!empty($content['reception_date']))<article class="event"><div class="ornament" aria-hidden="true">◇</div><h3>Resepsi</h3><p>{{ $formatDate($content['reception_date']) }}</p>@if(!empty($content['reception_time']))<p>{{ $content['reception_time'] }} {{ $zoneLabel }}</p>@endif<p>{{ $content['reception_venue'] ?? '' }}</p></article>@endif</div>
        @if(!empty($content['map_url']) && preg_match('/^https?:\/\//i', $content['map_url']))<a href="{{ $content['map_url'] }}" target="_blank" rel="noopener noreferrer" class="button solid">Buka peta lokasi ↗</a>@endif
    </section>
    @if(!empty($content['gallery_ids']))<section class="section"><span class="eyebrow">Our moments</span><h2>Potret kebahagiaan</h2><div class="gallery">@foreach($content['gallery_ids'] as $id)@if(isset($mediaUrls[$id]))<a href="{{ $mediaUrls[$id] }}" target="_blank" rel="noopener"><img src="{{ $mediaUrls[$id] }}" alt="Galeri pasangan {{ $loop->iteration }}" loading="lazy"></a>@endif @endforeach</div></section>@endif
    @if(($content['rsvp_enabled'] ?? false) || ($content['wishes_enabled'] ?? false))
    <section class="section" id="guest"><span class="eyebrow">Your presence means everything</span><h2>{{ ($content['rsvp_enabled'] ?? false) ? 'Konfirmasi kehadiran' : 'Ucapan & doa' }}</h2>
        @if(session('guest_success'))<div class="feedback" role="status">{{ session('guest_success') }}</div>@endif
        @if($errors->any())<div class="feedback" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <form class="guest-form" method="POST" action="{{ $preview ? '#' : route('wedding.responses.store', $invitation->slug) }}">
            @csrf
            <input type="hidden" name="submission_id" value="{{ old('submission_id', (string) \Illuminate\Support\Str::uuid()) }}">
            <div class="honey" aria-hidden="true"><label for="guest-website">Website</label><input id="guest-website" name="website" tabindex="-1" autocomplete="off"></div>
            <label for="guest-name">Nama Anda</label><input id="guest-name" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name" {{ $preview ? 'disabled' : '' }}>
            @if($content['rsvp_enabled'] ?? false)
            <label for="guest-attendance">Konfirmasi kehadiran</label><select id="guest-attendance" name="attendance" required {{ $preview ? 'disabled' : '' }}><option value="">Pilih konfirmasi</option><option value="yes" @selected(old('attendance') === 'yes')>Hadir</option><option value="no" @selected(old('attendance') === 'no')>Tidak hadir</option><option value="maybe" @selected(old('attendance') === 'maybe')>Belum pasti</option></select>
            <label for="guest-number">Jumlah tamu jika hadir (termasuk Anda)</label><input id="guest-number" name="guests" type="number" min="1" max="10" value="{{ old('guests', 1) }}" {{ $preview ? 'disabled' : '' }}>
            @endif
            @if($content['wishes_enabled'] ?? false)<label for="guest-wish">Ucapan & doa (opsional)</label><textarea id="guest-wish" name="wish" rows="4" maxlength="1000" {{ $preview ? 'disabled' : '' }}>{{ old('wish') }}</textarea>@endif
            <small>Respons kehadiran hanya terlihat oleh pemilik undangan. Nama dan ucapan dapat ditampilkan setelah persetujuan pemilik.</small>
            <button class="button solid" type="submit" {{ $preview ? 'disabled' : '' }}>{{ $preview ? 'Form nonaktif pada preview' : 'Kirim respons' }}</button>
        </form>
        @if($content['wishes_enabled'] ?? false)<div class="wishes">@forelse($wishes as $wish)<article class="wish"><strong>{{ $wish->name }}</strong><p>{{ $wish->wish }}</p></article>@empty<p>Belum ada ucapan yang ditampilkan.</p>@endforelse</div>@endif
    </section>
    @endif
    <section class="section"><span class="eyebrow">Terima kasih</span><h2>{{ $content['partner_one'] }} &amp; {{ $content['partner_two'] }}</h2><p class="prose">Kehadiran dan doa Anda menjadi bagian dari kebahagiaan kami.</p></section>
</main>
<footer>MADE WITH LOVE<br>MOSHIA WEDDING</footer>
@if($photo('music_id'))<div class="music"><audio src="{{ $photo('music_id') }}" controls loop preload="none" aria-label="Musik undangan"></audio></div>@endif
<script>
    const countdown = document.querySelector('[data-countdown]');
    if (countdown) {
        const target = new Date(countdown.dataset.countdown).getTime();
        const update = () => {
            const remaining = target - Date.now();
            if (!Number.isFinite(remaining)) return;
            if (remaining <= 0) { countdown.textContent = 'Hari istimewa telah tiba'; return; }
            const days = Math.floor(remaining / 86400000);
            const hours = Math.floor(remaining / 3600000) % 24;
            const minutes = Math.floor(remaining / 60000) % 60;
            countdown.textContent = days + ' hari · ' + hours + ' jam · ' + minutes + ' menit';
        };
        update(); setInterval(update, 60000);
    }
</script>
</body>
</html>
