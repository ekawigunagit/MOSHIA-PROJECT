@php
    use App\Modules\Products\Wedding\Services\InvitationLayout;
    $partnerKeys = array_search('bride', array_column($layout, 'key')) < array_search('groom', array_column($layout, 'key')) ? ['partner_two','partner_one'] : ['partner_one','partner_two'];
    $photo = fn ($key) => isset($content[$key], $mediaUrls[$content[$key]]) ? $mediaUrls[$content[$key]] : null;
    $zone = $content['timezone'] ?? 'Asia/Jakarta';
    $zoneLabel = ['Asia/Jakarta'=>'WIB','Asia/Makassar'=>'WITA','Asia/Jayapura'=>'WIT'][$zone] ?? 'WIB';
    $date = fn ($value) => \Carbon\CarbonImmutable::parse($value)->locale('id')->translatedFormat('l, d F Y');
    $eventAt = \Carbon\CarbonImmutable::parse($content['event_date'].' '.(($content['event_time'] ?? '') ?: '00:00'), $zone)->toIso8601String();
    $color = preg_match('/^#[0-9a-fA-F]{6}$/', $content['background_color'] ?? '') ? $content['background_color'] : $theme['accent'];
    $opacity = max(.2, min(.95, (float) ($content['background_opacity'] ?? .75)));
    $hasCover = InvitationLayout::enabled($content, 'cover');
    $labels = ['cover'=>'Pembuka','header'=>'Home','quote'=>'Wedding','groom'=>'Pria','bride'=>'Wanita','event'=>'Acara','story'=>'Cerita','gallery'=>'Galeri','rsvp'=>'RSVP','messages'=>'Ucapan','gift'=>'Gift','thanks'=>'Penutup'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="{{ $color }}">
<title>{{ $content[$partnerKeys[0]] }} &amp; {{ $content[$partnerKeys[1]] }} — Moshia Wedding</title>
<style>
:root{--paper:{{ $theme['background'] }};--ink:{{ $theme['text'] }};--accent:{{ $theme['accent'] }};--tint:{{ $color }};--veil:{{ $opacity }};--gold:#b58c4b;--line:color-mix(in srgb,var(--gold) 35%,transparent)}
@include('wedding.partials.style')
</style>
</head>
<body>
<div class="desktop-backdrop" aria-hidden="true">@if($photo('cover_background_id'))<img src="{{ $photo('cover_background_id') }}" alt="">@elseif($photo('cover_id'))<img src="{{ $photo('cover_id') }}" alt="">@endif</div>
<div class="phone-frame">
    <div class="phone-notch" aria-hidden="true"></div>
    <div id="invitation-scroll" class="invitation-scroll" tabindex="-1">
    @if($preview)<div class="preview">Preview privat · Simpan lalu Publish untuk memperbarui publik.</div>@endif
    <main data-feedback="{{ $errors->any() ? '1' : '0' }}" id="invitation" data-has-cover="{{ $hasCover ? '1' : '0' }}">
    @foreach($layout as $section)
    @continue(!$section['enabled'])
    @php $key = $section['key']; @endphp
    @switch($key)
    @case('cover')
        <section id="cover" class="page-section cover ornamental" data-label="Pembuka">
            <span class="eyebrow">{{ $content['cover_title'] ?? 'Wedding Invitation' }}</span>
            @if($photo('cover_id'))<img class="arch-photo cover-photo" src="{{ $photo('cover_id') }}" alt="Foto sampul pasangan">@endif
            <h1>{{ $content[$partnerKeys[0]] }} &amp; {{ $content[$partnerKeys[1]] }}</h1>
            <p class="date-label">{{ \Carbon\CarbonImmutable::parse($content['event_date'])->format('d · m · Y') }}</p>
            <div class="guest-salutation"><p>{{ $content['cover_greeting'] ?? 'Kepada Bapak/Ibu/Saudara/i' }}</p><strong>{{ $guestName ?: 'Tamu Undangan' }}</strong></div>
            <p class="prose">{{ $content['cover_opening'] ?? $content['message'] ?? 'Dengan penuh kebahagiaan, kami mengundang Anda untuk merayakan hari istimewa bersama kami.' }}</p>
            <button id="open-invitation" type="button" class="button solid">{{ ($content['cover_button'] ?? '') ?: 'Buka undangan' }}</button>
            <p class="cover-closing">{{ $content['cover_closing'] ?? 'Awal yang baru, untuk selamanya.' }}</p>
        </section>
        @break
    @case('header')
        <section id="header" class="page-section full-photo" data-label="Home">
            @if(($content['header_type'] ?? 'image') === 'video' && $photo('header_video_id'))
                <video id="header-video" class="background-media" src="{{ $photo('header_video_id') }}" @if($photo('header_image_id')) poster="{{ $photo('header_image_id') }}" @endif controls muted loop playsinline preload="metadata" aria-label="Video header pernikahan"></video>
            @elseif($photo('header_image_id'))<img class="background-media" src="{{ $photo('header_image_id') }}" alt="Background header undangan">@endif
            <div class="photo-caption"><span class="eyebrow">The Wedding of</span><h2>{{ $content[$partnerKeys[0]] }}<span class="ampersand">&amp;</span>{{ $content[$partnerKeys[1]] }}</h2><p>{{ $date($content['event_date']) }}</p><small>SCROLL TO DISCOVER ↓</small></div>
        </section>
        @break
    @case('quote')
        <section id="quote" class="page-section ornamental" data-label="Wedding"><span class="ornament" aria-hidden="true">❦</span><span class="eyebrow">Every love has a story</span><h2>Bersama, selamanya.</h2>
        @if($photo('wedding_photo_id'))<img class="arch-photo" src="{{ $photo('wedding_photo_id') }}" alt="Foto Wedding" loading="lazy">@endif
        <p class="prose quote">{{ $content['wedding_quote'] ?? 'Dengan kasih, kami melangkah bersama dalam perjalanan yang baru.' }}</p></section>
        @break
    @case('groom')
    @case('bride')
        @php $partner = $key === 'groom' ? 'partner_one' : 'partner_two'; @endphp
        <section id="{{ $key }}" class="page-section ornamental" data-label="{{ $labels[$key] }}"><span class="eyebrow">{{ $key === 'groom' ? 'The Groom' : 'The Bride' }}</span><h2>{{ $key === 'groom' ? 'Pengantin Pria' : 'Pengantin Wanita' }}</h2>
        @if($photo($partner.'_photo_id'))<img class="arch-photo" src="{{ $photo($partner.'_photo_id') }}" alt="Foto {{ $content[$partner] }}" loading="lazy">@endif
        <h3>{{ $content[$partner] }}</h3>
        @if(!empty($content[$partner.'_father']) || !empty($content[$partner.'_mother']))
            @if(!empty($content[$partner.'_father']))<p>Bapak {{ $content[$partner.'_father'] }}</p>@endif
            @if(!empty($content[$partner.'_mother']))<p>Ibu {{ $content[$partner.'_mother'] }}</p>@endif
        @else<p>{{ $content[$partner.'_parents'] ?? '' }}</p>@endif
        </section>
        @break
    @case('event')
        <section id="event" class="page-section ornamental" data-label="Acara"><span class="eyebrow">A day to remember</span><h2>{{ $content['save_date_title'] ?? 'Save the Date' }}</h2><p class="prose">{{ $content['save_date_message'] ?? '' }}</p><p>{{ $date($content['event_date']) }}</p>
        <div class="countdown" data-countdown="{{ $eventAt }}">@foreach(['Hari','Jam','Menit','Detik'] as $unit)<div><strong data-unit="{{ $loop->index }}">00</strong><small>{{ $unit }}</small></div>@endforeach</div>
        <p id="countdown-status" class="muted"></p>
        <button id="save-date" type="button" class="button" data-event-at="{{ $eventAt }}" data-title="{{ $content['event_name'] ?? 'Pernikahan' }}" data-location="{{ $content['venue'] }}">Simpan tanggal</button>
        @foreach(['main','reception'] as $event)
            @continue($event === 'reception' && !(($content['reception_enabled'] ?? !empty($content['reception_date'])) && !empty($content['reception_date'])))
            @php
                $mainEvent = $event === 'main';
                $name = $mainEvent ? ($content['event_name'] ?? 'Pemberkatan Pernikahan') : ($content['reception_name'] ?? 'Resepsi');
                $when = $content[$mainEvent ? 'event_date' : 'reception_date'];
                $time = $content[$mainEvent ? 'event_time' : 'reception_time'] ?? '';
                $venue = $content[$mainEvent ? 'venue' : 'reception_venue'] ?? '';
                $map = $content[$mainEvent ? 'map_url' : 'reception_map_url'] ?? '';
            @endphp
            <article class="event-card"><span class="ornament" aria-hidden="true">♡</span><h3>{{ $name }}</h3><span class="rule"></span><p>{{ $date($when) }}</p>@if($time)<p>{{ $time }} {{ $zoneLabel }}</p>@endif<p class="prose">{{ $venue }}</p>
            @if(str_starts_with($map,'https://') || str_starts_with($map,'http://'))<a class="button solid" href="{{ $map }}" target="_blank" rel="noopener noreferrer">{{ $mainEvent ? 'Buka peta lokasi' : 'Buka peta resepsi' }} ↗</a>@endif</article>
        @endforeach
        </section>
        @break
    @case('story')
        @if(!empty($content['stories']) || (!array_key_exists('stories',$content) && !empty($content['story'])))
        <section id="story" class="page-section ornamental" data-label="Cerita"><span class="eyebrow">The way we found each other</span><h2>Our Love Story</h2>
        @foreach($content['stories'] ?? [['title'=>'Perjalanan kami','body'=>$content['story'] ?? '']] as $chapter)
            <article class="story-card">@if(!empty($chapter['image_id']) && isset($mediaUrls[$chapter['image_id']]))<img src="{{ $mediaUrls[$chapter['image_id']] }}" alt="{{ $chapter['title'] }}" loading="lazy">@endif<h3>{{ $chapter['title'] }}</h3><p class="prose">{{ $chapter['body'] ?? '' }}</p></article>
        @endforeach
        </section>@endif
        @break
    @case('gallery')
        @if(!empty($content['gallery_ids']))
        <section id="gallery" class="page-section ornamental" data-label="Galeri"><span class="eyebrow">Moments we will keep forever</span><h2>Our Gallery</h2><p class="prose">Kenangan indah dalam perjalanan kami.</p>
        <div class="gallery-track">@foreach($content['gallery_ids'] as $id)@if(isset($mediaUrls[$id]))<a href="{{ $mediaUrls[$id] }}" target="_blank" rel="noopener"><img class="arch-photo" src="{{ $mediaUrls[$id] }}" alt="Galeri pasangan {{ $loop->iteration }}" loading="lazy"></a>@endif @endforeach</div>
        <div class="gallery-controls"><button type="button" data-gallery="-1" aria-label="Foto sebelumnya">←</button><span>Geser untuk melihat foto</span><button type="button" data-gallery="1" aria-label="Foto berikutnya">→</button></div></section>@endif
        @break
    @case('rsvp')
        @if(($content['rsvp_enabled'] ?? false) || ($content['wishes_enabled'] ?? false))
        <section id="rsvp" class="page-section ornamental" data-label="RSVP"><span class="eyebrow">Your presence means the world to us</span><h2>Be Our Guest</h2><p class="prose">Kehadiran dan doa Anda melengkapi kebahagiaan kami.</p>
        @if(session('guest_success'))<div class="feedback" role="status">{{ session('guest_success') }}</div>@endif
        @if($errors->any())<div class="feedback" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <form class="guest-form" method="POST" action="{{ $preview ? '#' : route('wedding.responses.store', $invitation->slug) }}">
        @csrf
        <input type="hidden" name="submission_id" value="{{ old('submission_id', (string) \Illuminate\Support\Str::uuid()) }}">
        <div class="honey" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
        <label for="guest-name">Nama Anda</label><input id="guest-name" name="name" required maxlength="100" autocomplete="name" value="{{ old('name', $guestName) }}" @disabled($preview)>
        @if($content['rsvp_enabled'] ?? false)
        <label for="attendance">Konfirmasi kehadiran</label><select id="attendance" name="attendance" required @disabled($preview)><option value="">Pilih konfirmasi</option>@foreach(['yes'=>'Hadir','no'=>'Tidak hadir','maybe'=>'Belum pasti'] as $value=>$label)<option value="{{ $value }}" @selected(old('attendance')===$value)>{{ $label }}</option>@endforeach</select>
        <label for="guests">Jumlah tamu jika hadir</label><input id="guests" name="guests" type="number" min="1" max="10" value="{{ old('guests', 1) }}" @disabled($preview)>
        @endif
        @if($content['wishes_enabled'] ?? false)<label for="wish">Ucapan & doa</label><textarea id="wish" name="wish" rows="4" maxlength="1000" @disabled($preview)>{{ old('wish') }}</textarea>@endif
        <input type="hidden" name="captcha_token" value="{{ $captcha['token'] ?? '' }}">
        <label for="captcha-answer">Verifikasi sederhana: {{ $captcha['question'] ?? '2 + 3 = ?' }}</label><input id="captcha-answer" name="captcha_answer" inputmode="numeric" pattern="[0-9]{1,2}" maxlength="2" required autocomplete="off" @disabled($preview)>
        <small>Konfirmasi kehadiran hanya terlihat pemilik. Nama dan ucapan tampil setelah disetujui.</small>
        <button class="button solid" type="submit" @disabled($preview)>{{ $preview ? 'Form nonaktif pada preview' : 'Kirim respons' }}</button>
        </form></section>@endif
        @break
    @case('messages')
        @if($content['wishes_enabled'] ?? false)
        <section id="messages" class="page-section ornamental" data-label="Ucapan"><span class="eyebrow">Words from the heart</span><h2>Wishes & Prayers</h2>
        <div class="wishes">@forelse($wishes as $wish)<article class="wish"><span class="avatar" aria-hidden="true">{{ mb_substr($wish->name,0,1) }}</span><div><strong>{{ $wish->name }}</strong><time>{{ $wish->created_at?->format('d.m.Y') }}</time><p>{{ $wish->wish }}</p></div></article>@empty<p>Belum ada ucapan yang ditampilkan.</p>@endforelse</div>
        @if($wishes->hasPages())<nav class="pagination" aria-label="Halaman ucapan">@if($wishes->previousPageUrl())<a class="button" href="{{ $wishes->previousPageUrl() }}">← Sebelumnya</a>@endif<span>{{ $wishes->currentPage() }} / {{ $wishes->lastPage() }}</span>@if($wishes->nextPageUrl())<a class="button" href="{{ $wishes->nextPageUrl() }}">Berikutnya →</a>@endif</nav>@endif
        </section>@endif
        @break
    @case('gift')
        @if($content['gift_enabled'] ?? false)
        <section id="gift" class="page-section ornamental" data-label="Gift"><span class="ornament" aria-hidden="true">❦</span><span class="eyebrow">A little token of love</span><h2>Wedding Gift</h2><p class="prose">Kehadiran dan doa Anda adalah hadiah terindah bagi kami.</p>
        @foreach($content['gift_accounts'] ?? [] as $gift)@continue(!($gift['enabled'] ?? true))<article class="gift-card"><h3>{{ $gift['bank_name'] ?? 'Bank' }}</h3><small>Nomor rekening</small><p class="account-number">{{ $gift['account_number'] }}</p><small>Atas nama</small><p>{{ $gift['account_holder'] }}</p><button type="button" class="button" data-copy="{{ $gift['account_number'] }}">Salin nomor rekening</button></article>@endforeach
        <p id="copy-status" role="status"></p></section>@endif
        @break
    @case('thanks')
        <section id="thanks" class="page-section full-photo" data-label="Penutup">@if($photo('thank_you_image_id'))<img class="background-media" src="{{ $photo('thank_you_image_id') }}" alt="Background ucapan terima kasih" loading="lazy">@endif
        <div class="photo-caption"><span class="eyebrow">With all our love</span><h2>{{ $content['thank_you_title'] ?? 'Thank You' }}</h2><p class="prose">{{ $content['thank_you_message'] ?? 'Terima kasih atas kehadiran dan doa Anda.' }}</p><h3>{{ $content[$partnerKeys[0]] }} &amp; {{ $content[$partnerKeys[1]] }}</h3></div></section>
        @break
    @case('footer')
        <footer id="footer">Made with love by Moshia</footer>
        @break
    @endswitch
    @endforeach
    </main>
    </div>
    <div class="bottom-controls" hidden>
        <nav class="bottom-menu" aria-label="Bagian undangan">
        @foreach($layout as $section)@if($section['enabled'] && !in_array($section['key'],['cover','footer']))<a href="#{{ $section['key'] }}"><span aria-hidden="true">{{ ['header'=>'⌂','groom'=>'♡','bride'=>'♡','event'=>'▦','gallery'=>'▧','rsvp'=>'✉','messages'=>'✉','gift'=>'♧'][$section['key']] ?? '◇' }}</span>{{ $labels[$section['key']] }}</a>@endif @endforeach
        </nav>
        @if(($content['music_enabled'] ?? true) && $photo('music_id'))<button id="music-toggle" type="button" aria-label="Putar musik" aria-pressed="false">♪</button><audio id="invitation-music" src="{{ $photo('music_id') }}" preload="none" loop></audio>@endif
    </div>
</div>
<script>
@include('wedding.partials.script')
</script>
</body></html>
