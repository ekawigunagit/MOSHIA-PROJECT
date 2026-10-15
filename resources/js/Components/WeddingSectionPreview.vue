<script setup>
import { computed } from 'vue';
const props = defineProps({ compact: Boolean, section: String, form: Object, theme: Object, media: Array, banks: Array, names: String, guest: String });
const url = id => props.media.find(m => m.id === id)?.url;
const person = computed(() => props.section === 'bride' ? 'partner_two' : 'partner_one');
const image = computed(() => ({ cover: props.form.cover_id, header: props.form.header_image_id, quote: props.form.wedding_photo_id, groom: props.form.partner_one_photo_id, bride: props.form.partner_two_photo_id, thanks: props.form.thank_you_image_id })[props.section]);
const hidden = computed(() => props.form.sections.find(s => s.key === props.section)?.enabled === false);
</script>
<template>
<div class="section-preview-wrap" :class="{ compact }">
<p v-if="hidden" class="studio-help" role="status">Bagian ini disembunyikan saat publish. Isian tetap dapat ditinjau di sini.</p>
<div :key="section" class="section-live-preview" :style="{ background: theme.background, color: theme.text, '--preview-accent': theme.accent }">
<video v-if="section === 'header' && form.header_type === 'video' && url(form.header_video_id)" :src="url(form.header_video_id)" controls muted playsinline preload="metadata" />
<img v-else-if="url(image)" :src="url(image)" :alt="'Foto ' + section" />
<div v-else-if="['cover','header','quote','groom','bride','thanks'].includes(section)" class="preview-placeholder">Foto {{ section === 'thanks' ? 'penutup' : 'pilihan' }} belum dipilih</div>
<template v-if="section === 'cover'">
<small>{{ form.cover_title }}</small><h2>{{ names }}</h2><p>{{ form.event_date || 'Tanggal acara' }}</p><p>{{ form.cover_greeting }}</p><strong>{{ guest || 'Nama tamu undangan' }}</strong><p>{{ form.cover_opening }}</p><span class="preview-button">{{ form.cover_button }}</span><p><em>{{ form.cover_closing }}</em></p>
</template>
<template v-else-if="section === 'header'"><small>THE WEDDING OF</small><h2>{{ names }}</h2><p>{{ form.event_date || 'Tanggal acara' }}</p></template>
<template v-else-if="section === 'quote'"><h2>Our Wedding</h2><p>{{ form.wedding_quote || 'Ayat / kata-kata Wedding Anda' }}</p></template>
<template v-else-if="['groom','bride'].includes(section)"><small>{{ section === 'groom' ? 'THE GROOM' : 'THE BRIDE' }}</small><h2>{{ form[person] || 'Nama pengantin' }}</h2><p>{{ [form[person + '_father'], form[person + '_mother']].filter(Boolean).join(' & ') || form[person + '_parents'] || 'Nama orang tua' }}</p></template>
<template v-else-if="section === 'event'"><h2>{{ form.save_date_title }}</h2><p>{{ form.save_date_message }}</p><small>Countdown menuju {{ form.event_date || 'tanggal acara' }}</small><article><h3>{{ form.event_name }}</h3><p>{{ form.event_date }} {{ form.event_time }} · {{ form.timezone }}</p><p>{{ form.venue || 'Tempat dan alamat acara' }}</p><span v-if="form.map_url" class="preview-button">Lihat lokasi</span></article><article v-if="form.reception_enabled"><h3>{{ form.reception_name }}</h3><p>{{ form.reception_date }} {{ form.reception_time }}</p><p>{{ form.reception_venue }}</p><span v-if="form.reception_map_url" class="preview-button">Lihat lokasi</span></article></template>
<template v-else-if="section === 'story'"><h2>Our Love Story</h2><article v-for="(story,i) in form.stories" :key="i"><img v-if="url(story.image_id)" :src="url(story.image_id)" alt="Foto cerita" /><h3>{{ story.title }}</h3><p>{{ story.body || 'Tuliskan cerita Anda' }}</p></article><p v-if="!form.stories.length">Belum ada cerita.</p></template>
<template v-else-if="section === 'gallery'"><h2>Our Gallery</h2><img v-for="id in form.gallery_ids" :key="id" :src="url(id)" alt="Foto galeri pilihan" /><p v-if="!form.gallery_ids.length">Pilih atau unggah foto galeri.</p></template>
<template v-else-if="section === 'rsvp'"><h2>Be Our Guest</h2><fieldset disabled><label>Nama Anda<input placeholder="Nama tamu" /></label><label v-if="form.rsvp_enabled">Konfirmasi kehadiran<select><option>Hadir / Tidak hadir / Belum pasti</option></select></label><label v-if="form.wishes_enabled">Ucapan & doa<textarea placeholder="Ucapan tamu" /></label><label>Verifikasi: 2 + 3 = ?<input placeholder="Jawaban" /></label><button>Kirim respons</button></fieldset><p v-if="!form.rsvp_enabled && !form.wishes_enabled">RSVP dan pengiriman ucapan nonaktif.</p></template>
<template v-else-if="section === 'messages'"><h2>Wishes & Prayers</h2><p>{{ form.wishes_enabled ? 'Ucapan yang Anda setujui ditampilkan di sini, 5 per halaman.' : 'Ucapan & doa sedang nonaktif.' }}</p></template>
<template v-else-if="section === 'gift'"><h2>Wedding Gift</h2><template v-if="form.gift_enabled"><article v-for="(gift,i) in form.gift_accounts.filter(g => g.enabled)" :key="i"><h3>{{ banks.find(b => b.id === gift.bank_id)?.name || 'Pilih bank' }}</h3><p>{{ gift.account_number || 'Nomor rekening' }}</p><p>{{ gift.account_holder || 'Nama pemilik' }}</p><span class="preview-button">Salin nomor rekening</span></article><p v-if="!form.gift_accounts.some(g => g.enabled)">Belum ada rekening aktif.</p></template><p v-else>Wedding Gift nonaktif.</p></template>
<template v-else-if="section === 'thanks'"><small>WITH ALL OUR LOVE</small><h2>{{ form.thank_you_title }}</h2><p>{{ form.thank_you_message }}</p><h3>{{ names }}</h3></template>
<template v-else-if="section === 'footer'"><p>Made with love by Moshia</p></template>
<template v-else-if="section === 'music'"><h2>Musik latar</h2><audio v-if="form.music_enabled && url(form.music_id)" :src="url(form.music_id)" controls preload="none" /><p v-else>{{ form.music_enabled ? 'Pilih musik untuk mendengarkan preview.' : 'Musik nonaktif.' }}</p></template>
</div>
<p v-if="!compact" class="studio-help">Preview bagian mengikuti isian saat ini. Simpan draft untuk menyimpan perubahan; gunakan Preview privat untuk melihat layout undangan lengkap.</p>
</div>
</template>
<style scoped>
.section-live-preview{padding:24px 18px;border:1px solid var(--line,#ddd);border-radius:26px;text-align:center;font-family:Georgia,serif;overflow-wrap:anywhere;max-height:75vh;overflow-y:auto}
.section-live-preview h2{font-size:25px;margin:20px 0;color:var(--preview-accent)}.section-live-preview h3{font-size:19px;margin:14px 0}.section-live-preview p{white-space:pre-wrap;line-height:1.7;margin:14px 0}.section-live-preview small{font-size:10px;letter-spacing:2px}.section-live-preview img,.section-live-preview video{width:100%;max-height:280px;object-fit:cover;border-radius:70px 70px 8px 8px;margin-bottom:16px}.section-live-preview article{padding:14px 0;border-bottom:1px solid #8884}.preview-placeholder{border:1px dashed #8886;border-radius:70px 70px 8px 8px;padding:50px 12px;margin-bottom:20px;font:12px sans-serif}.preview-button{display:inline-block;padding:10px 16px;background:var(--preview-accent);color:white;border-radius:6px;font:12px sans-serif}.section-live-preview label{display:block;text-align:left;font:12px sans-serif;margin:12px 0}.section-live-preview input,.section-live-preview select,.section-live-preview textarea{width:100%;margin-top:8px;background:#fff;color:#333;border:1px solid #ddd;border-radius:6px;padding:8px}.section-live-preview audio{width:100%}.section-live-preview fieldset{min-width:0}
.compact .section-live-preview{max-height:none;overflow:visible;border:0;border-bottom:1px solid #8883;border-radius:0}
</style>
