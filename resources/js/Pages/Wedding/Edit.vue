<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import InputError from '@/Components/InputError.vue';
import ShellIcon from '@/Components/ShellIcon.vue';
import { dateTime } from '@/billing';

const props = defineProps({
    workspace: Object, invitation: Object, order: Object, publicUrl: String,
    templates: Array, media: Array, responses: Object, responseStats: Object, limits: Object,
});
const draft = props.invitation.draft_content ?? {};
const form = useForm({
    template: draft.template ?? 'classic',
    partner_one: draft.partner_one ?? '', partner_two: draft.partner_two ?? '',
    partner_one_parents: draft.partner_one_parents ?? '', partner_two_parents: draft.partner_two_parents ?? '',
    partner_one_photo_id: draft.partner_one_photo_id ?? null, partner_two_photo_id: draft.partner_two_photo_id ?? null,
    event_date: draft.event_date ?? '', event_time: draft.event_time ?? '', timezone: draft.timezone ?? 'Asia/Jakarta',
    venue: draft.venue ?? '', map_url: draft.map_url ?? '',
    reception_date: draft.reception_date ?? '', reception_time: draft.reception_time ?? '', reception_venue: draft.reception_venue ?? '',
    message: draft.message ?? '', story: draft.story ?? '',
    cover_id: draft.cover_id ?? null, gallery_ids: draft.gallery_ids ?? [], music_id: draft.music_id ?? null,
    rsvp_enabled: draft.rsvp_enabled ?? true, wishes_enabled: draft.wishes_enabled ?? true,
});
const steps = ['Template', 'Pasangan', 'Acara', 'Foto & musik', 'RSVP & ucapan', 'Preview & publish'];
const step = ref(0);
const confirmPublish = ref(false);
const uploadInput = ref(null);
const upload = useForm({ kind: 'image', file: null });
const publication = useForm({});
const moderation = useForm({ approved: false });
const images = computed(() => props.media.filter(m => m.kind === 'image'));
const music = computed(() => props.media.filter(m => m.kind === 'music'));
const chosenTheme = computed(() => props.templates.find(t => t.key === form.template) ?? props.templates[0]);
const busy = computed(() => form.processing || publication.processing || upload.processing);
const mediaUrl = id => props.media.find(m => m.id === id)?.url;
const requiredReady = computed(() => !!(form.partner_one.trim() && form.partner_two.trim() && form.event_date && form.venue.trim()));
function save() {
    confirmPublish.value = false;
    form.patch(route('wedding.update', props.workspace.id), {
        preserveScroll: true, onSuccess: () => form.defaults(),
        onError: (errors) => {
            const first = Object.keys(errors)[0];
            step.value = first?.startsWith('partner') ? 1
                : ['event_date','event_time','timezone','venue','map_url','reception_date','reception_time','reception_venue'].includes(first) ? 2
                : ['cover_id','music_id','gallery_ids'].some(k => first?.startsWith(k)) ? 3 : step.value;
        },
    });
}
function sendUpload() {
    if (!upload.file) return;
    upload.post(route('wedding.media.store', props.workspace.id), {
        preserveScroll: true, forceFormData: true,
        onSuccess: () => { upload.reset('file'); if (uploadInput.value) uploadInput.value.value = ''; },
    });
}
function toggleGallery(id) {
    const index = form.gallery_ids.indexOf(id);
    if (index >= 0) form.gallery_ids.splice(index, 1);
    else if (form.gallery_ids.length < props.limits.gallery) form.gallery_ids.push(id);
}
function publish() {
    publication.post(route('wedding.publish', props.workspace.id), {
        preserveScroll: true, onSuccess: () => { confirmPublish.value = false; },
    });
}
function moderate(response) {
    moderation.approved = !response.approved;
    moderation.patch(route('wedding.responses.moderate', [props.workspace.id, response.id]), { preserveScroll: true });
}
function leave(event) { if (form.isDirty) { event.preventDefault(); event.returnValue = ''; } }
onMounted(() => window.addEventListener('beforeunload', leave));
onUnmounted(() => window.removeEventListener('beforeunload', leave));
</script>
<template>
    <Head title="Studio Wedding" />
    <MoshiaLayout>
        <template #header><h2>Wedding · {{ workspace.name }}</h2></template>
        <div class="shell account-main wedding-studio">
            <header class="studio-heading">
                <div><span class="section-kicker">MOSHIA WEDDING STUDIO</span><h1>Undangan, dengan cerita Anda.</h1><p>{{ order.terms.label }} · {{ order.terms.validity_months }} bulan sejak publish.</p></div>
                <Link :href="route('billing.index', workspace.id)" class="button button-outline">Paket & pembayaran</Link>
            </header>
            <div class="studio-period">
                <span v-if="!order.first_published_at">Pembayaran diterima. Masa aktif belum dimulai sampai Publish.</span>
                <span v-else>Masa aktif sampai {{ dateTime(order.ends_at) }}. Publish ulang tidak memperpanjang periode.</span>
                <span class="studio-save-state">{{ form.isDirty ? 'Ada perubahan belum disimpan' : invitation.draft_content ? 'Draft tersimpan' : 'Mulai isi undangan' }}</span>
            </div>
            <nav class="studio-steps" aria-label="Tahapan undangan">
                <button v-for="(label, i) in steps" :key="label" type="button" :class="{ active: step === i }" :aria-current="step === i ? 'step' : undefined" @click="step = i"><span>{{ i + 1 }}</span>{{ label }}</button>
            </nav>
            <div class="studio-grid">
                <div>
                    <div v-if="Object.keys(form.errors).length" class="console-alert is-error mb-5" role="alert"><strong>Periksa isian berikut:</strong><p v-for="(error, key) in form.errors" :key="key">{{ error }}</p></div>
                    <form class="detail-card studio-editor" @submit.prevent="save" novalidate>
                        <section v-show="step === 0">
                            <h2>Pilih suasana undangan</h2><p class="studio-help">Semua tema menggunakan konten yang sama. Anda dapat mengganti tema sebelum publish.</p>
                            <div class="studio-templates">
                                <button v-for="theme in templates" :key="theme.key" type="button" class="studio-template" :class="{ selected: form.template === theme.key }" :aria-pressed="form.template === theme.key" @click="form.template = theme.key">
                                    <span class="template-art" :style="{ background: theme.background, color: theme.accent }"><span class="template-arch">The Wedding of<br /><b>A & B</b><small>TOGETHER, FOREVER</small></span></span>
                                    <strong>{{ theme.label }}</strong><small>{{ theme.description }}</small><span class="template-choice">{{ form.template === theme.key ? '✓ Dipilih' : 'Pilih tema' }}</span>
                                </button>
                            </div>
                            <label class="moshia-label mt-6" for="w-message">Pesan pembuka</label><textarea id="w-message" v-model="form.message" rows="3" maxlength="2000" class="moshia-input w-full" placeholder="Dengan bahagia kami mengundang Anda..." />
                            <label class="moshia-label mt-5" for="w-story">Cerita kami</label><textarea id="w-story" v-model="form.story" rows="5" maxlength="5000" class="moshia-input w-full" placeholder="Ceritakan perjalanan Anda berdua (opsional)." />
                        </section>
                        <section v-show="step === 1">
                            <h2>Kenalkan pasangan</h2><p class="studio-help">Nama wajib diisi. Foto dan informasi keluarga bersifat opsional.</p>
                            <div v-for="(label, key) in { partner_one: 'Pasangan pertama', partner_two: 'Pasangan kedua' }" :key="key" class="studio-field-group">
                                <h3>{{ label }}</h3>
                                <label :for="key" class="moshia-label">Nama lengkap *</label><input :id="key" v-model="form[key]" maxlength="100" class="moshia-input w-full" />
                                <InputError :message="form.errors[key]" />
                                <label :for="key + '-parents'" class="moshia-label mt-4">Nama orang tua / keluarga</label><input :id="key + '-parents'" v-model="form[key + '_parents']" maxlength="200" class="moshia-input w-full" />
                                <label :for="key + '-photo'" class="moshia-label mt-4">Foto pasangan</label>
                                <select :id="key + '-photo'" v-model="form[key + '_photo_id']" class="moshia-input w-full"><option :value="null">Tanpa foto</option><option v-for="m in images" :key="m.id" :value="m.id">Foto #{{ m.id }}</option></select>
                                <img v-if="form[key + '_photo_id']" :src="mediaUrl(form[key + '_photo_id'])" class="studio-portrait" :alt="'Foto ' + label" />
                            </div>
                            <button type="button" class="studio-text-link" @click="step = 3">Unggah foto di tahap Foto & musik →</button>
                        </section>
                        <section v-show="step === 2">
                            <h2>Detail acara</h2><p class="studio-help">Tanggal dan lokasi utama wajib diisi. Waktu ditampilkan sesuai zona yang Anda pilih.</p>
                            <div class="studio-two-fields">
                                <div><label for="w-date" class="moshia-label">Tanggal acara utama *</label><input id="w-date" v-model="form.event_date" type="date" class="moshia-input w-full" /><InputError :message="form.errors.event_date" /></div>
                                <div><label for="w-time" class="moshia-label">Jam acara</label><input id="w-time" v-model="form.event_time" type="time" class="moshia-input w-full" /></div>
                            </div>
                            <label for="w-zone" class="moshia-label mt-5">Zona waktu</label><select id="w-zone" v-model="form.timezone" class="moshia-input w-full"><option value="Asia/Jakarta">WIB</option><option value="Asia/Makassar">WITA</option><option value="Asia/Jayapura">WIT</option></select>
                            <label for="w-venue" class="moshia-label mt-5">Nama tempat dan alamat *</label><textarea id="w-venue" v-model="form.venue" maxlength="300" rows="3" class="moshia-input w-full" /><InputError :message="form.errors.venue" />
                            <label for="w-map" class="moshia-label mt-5">Tautan peta (https://…)</label><input id="w-map" v-model="form.map_url" type="url" maxlength="1000" class="moshia-input w-full" placeholder="https://maps.google.com/..." />
                            <div class="studio-field-group"><h3>Resepsi / acara kedua (opsional)</h3><div class="studio-two-fields"><div><label for="r-date" class="moshia-label">Tanggal</label><input id="r-date" v-model="form.reception_date" type="date" class="moshia-input w-full" /></div><div><label for="r-time" class="moshia-label">Jam</label><input id="r-time" v-model="form.reception_time" type="time" class="moshia-input w-full" /></div></div><label for="r-venue" class="moshia-label mt-4">Nama tempat dan alamat</label><textarea id="r-venue" v-model="form.reception_venue" rows="3" maxlength="300" class="moshia-input w-full" /></div>
                        </section>
                        <section v-show="step === 3">
                            <h2>Foto, galeri & musik</h2><p class="studio-help">Unggah media, lalu pilih penempatannya. File draft tetap privat sampai Anda melakukan Publish.</p>
                            <div class="studio-upload">
                                <label for="upload-kind" class="moshia-label">Jenis media</label><select id="upload-kind" v-model="upload.kind" class="moshia-input w-full" :disabled="upload.processing" @change="upload.file = null; uploadInput.value = ''"><option value="image">Foto — JPG, PNG, WebP</option><option value="music">Musik — MP3</option></select>
                                <label for="upload-file" class="moshia-label mt-4">Pilih file</label><input id="upload-file" ref="uploadInput" type="file" :accept="upload.kind === 'image' ? 'image/jpeg,image/png,image/webp' : 'audio/mpeg,.mp3'" :disabled="upload.processing" @change="upload.file = $event.target.files[0] ?? null" />
                                <p class="studio-help">Batas teknis: foto {{ limits.imageKb / 1024 }} MB, musik {{ limits.musicKb / 1024 }} MB, {{ limits.files }} file/workspace. Gunakan media yang boleh Anda publikasikan.</p>
                                <InputError v-for="(error, key) in upload.errors" :key="key" :message="error" />
                                <progress v-if="upload.progress" :value="upload.progress.percentage" max="100" class="w-full">{{ upload.progress.percentage }}%</progress>
                                <button type="button" class="button button-outline" :disabled="!upload.file || busy" @click="sendUpload">{{ upload.processing ? 'Mengunggah…' : 'Unggah media' }}</button>
                            </div>
                            <label for="w-cover" class="moshia-label mt-5">Foto sampul</label><select id="w-cover" v-model="form.cover_id" class="moshia-input w-full"><option :value="null">Tanpa foto sampul</option><option v-for="m in images" :key="m.id" :value="m.id">Foto #{{ m.id }}</option></select>
                            <h3 class="mt-6">Galeri · {{ form.gallery_ids.length }} / {{ limits.gallery }}</h3><p class="studio-help">Klik foto untuk memilih/melepas. Urutan mengikuti urutan pilihan.</p>
                            <div v-if="images.length" class="studio-media-grid"><button v-for="m in images" :key="m.id" type="button" :aria-pressed="form.gallery_ids.includes(m.id)" :disabled="!form.gallery_ids.includes(m.id) && form.gallery_ids.length >= limits.gallery" :class="{ selected: form.gallery_ids.includes(m.id) }" @click="toggleGallery(m.id)"><img :src="m.url" :alt="'Foto #' + m.id" loading="lazy" /><span>#{{ m.id }} {{ form.gallery_ids.includes(m.id) ? '✓ ' + (form.gallery_ids.indexOf(m.id) + 1) : '' }}</span></button></div><p v-else>Belum ada foto. Unggah foto untuk memilih sampul dan galeri.</p>
                            <label for="w-music" class="moshia-label mt-6">Musik latar</label><select id="w-music" v-model="form.music_id" class="moshia-input w-full"><option :value="null">Tanpa musik</option><option v-for="m in music" :key="m.id" :value="m.id">Musik #{{ m.id }}</option></select><audio v-if="form.music_id" :key="form.music_id" :src="mediaUrl(form.music_id)" controls preload="none" class="mt-3 w-full" />
                            <p class="studio-help mt-3">Musik diputar setelah tamu menekan tombol putar. Menghapus pilihan tidak menghapus file atau mengubah undangan yang sudah terbit.</p>
                        </section>
                        <section v-show="step === 4">
                            <h2>RSVP & ucapan tamu</h2><p class="studio-help">Tamu dapat mengirim respons tanpa akun Moshia. Kehadiran hanya terlihat oleh Anda; ucapan memerlukan persetujuan sebelum tampil publik.</p>
                            <label class="studio-setting"><span><strong>Konfirmasi kehadiran (RSVP)</strong><small>Nama, status hadir, dan jumlah tamu (maksimal 10 per respons).</small></span><input v-model="form.rsvp_enabled" type="checkbox" /></label>
                            <label class="studio-setting"><span><strong>Ucapan & doa</strong><small>Moderasi ucapan pada daftar respons di bawah editor.</small></span><input v-model="form.wishes_enabled" type="checkbox" /></label>
                            <p class="studio-help mt-5">Pengaturan baru berlaku di halaman publik setelah Publish. Respons tamu belum diverifikasi identitasnya.</p>
                        </section>
                        <section v-show="step === 5">
                            <h2>Tinjau sebelum publish</h2><p class="studio-help">Simpan draft, buka preview privat, kemudian publish jika semuanya sudah sesuai.</p>
                            <dl class="studio-review"><div><dt>Template</dt><dd>{{ chosenTheme.label }}</dd></div><div><dt>Pasangan</dt><dd>{{ form.partner_one || 'Belum diisi' }} & {{ form.partner_two || 'Belum diisi' }}</dd></div><div><dt>Acara</dt><dd>{{ form.event_date || 'Belum diisi' }} {{ form.event_time }} · {{ form.timezone }}</dd></div><div><dt>Lokasi</dt><dd>{{ form.venue || 'Belum diisi' }}</dd></div><div><dt>Galeri / musik</dt><dd>{{ form.gallery_ids.length }} foto · {{ form.music_id ? 'Musik dipilih' : 'Tanpa musik' }}</dd></div></dl>
                            <p v-if="!requiredReady" class="studio-help">Lengkapi nama pasangan, tanggal dan lokasi untuk menyimpan draft.</p>
                            <p v-if="form.isDirty" class="studio-help">Ada perubahan belum disimpan. Preview membuka draft terakhir yang tersimpan.</p>
                            <div class="dashboard-actions"><a v-if="invitation.draft_content" :href="route('wedding.preview', workspace.id)" target="_blank" rel="noopener" class="button button-outline">Buka preview privat ↗</a><button type="button" class="button button-primary" :disabled="!invitation.draft_content || form.isDirty || busy" @click="confirmPublish = true">Publish undangan</button></div>
                            <div v-if="confirmPublish" class="studio-confirm" role="region" aria-label="Konfirmasi publish"><p>{{ order.first_published_at ? 'Konten publik akan diperbarui. Masa aktif tetap sama.' : 'Publish akan membuka undangan untuk publik dan memulai masa aktif paket.' }}</p><button type="button" class="button button-primary" :disabled="busy || form.isDirty" @click="publish">Ya, publish sekarang</button><button type="button" class="button button-outline" :disabled="busy" @click="confirmPublish = false">Batal</button></div>
                            <InputError :message="publication.errors.publish" />
                            <div v-if="order.first_published_at" class="studio-confirm"><strong>Tautan undangan</strong><a :href="publicUrl" target="_blank" rel="noopener" class="mt-2 block break-all underline">{{ publicUrl }}</a><p>Salin tautan ini untuk dibagikan kepada tamu.</p></div>
                        </section>
                        <footer class="studio-form-footer"><button v-if="step > 0" type="button" class="button button-outline" @click="step--">Kembali</button><button type="submit" class="button button-outline" :disabled="busy">{{ form.processing ? 'Menyimpan…' : 'Simpan draft' }}</button><button v-if="step < steps.length - 1" type="button" class="button button-primary" @click="step++">Lanjut →</button></footer>
                    </form>
                </div>
                <aside class="studio-summary" aria-label="Ringkasan tampilan"><div class="studio-mini-preview" :style="{ background: chosenTheme.background, color: chosenTheme.text }"><img v-if="form.cover_id" :src="mediaUrl(form.cover_id)" alt="Foto sampul pilihan" /><small>THE WEDDING OF</small><h2>{{ form.partner_one || 'Nama pertama' }}<span>&</span>{{ form.partner_two || 'Nama kedua' }}</h2><p>{{ form.event_date || 'Tanggal acara Anda' }}</p><p>{{ form.venue || 'Lokasi acara' }}</p></div><p class="studio-help">Ringkasan isian, bukan preview final. Gunakan preview privat untuk memeriksa hasil tersimpan.</p></aside>
            </div>
            <section id="responses" class="detail-card mt-8">
                <h2 class="text-xl font-semibold">Respons tamu & moderasi ucapan</h2>
                <p class="mt-2 mb-5">{{ responseStats.total }} respons · {{ responseStats.attending }} tamu menyatakan hadir · {{ responseStats.pendingWishes }} ucapan menunggu tinjauan.</p>
                <div v-if="responses.data.length" class="console-table-wrap"><table class="console-table"><thead><tr><th>Nama</th><th>Kehadiran</th><th>Jumlah</th><th>Ucapan</th><th>Visibilitas</th></tr></thead><tbody><tr v-for="response in responses.data" :key="response.id"><td>{{ response.name }}</td><td>{{ ({ yes: 'Hadir', no: 'Tidak hadir', maybe: 'Belum pasti' })[response.attendance] ?? 'Tidak diisi' }}</td><td>{{ response.guests }}</td><td class="studio-wish-cell">{{ response.wish || '—' }}</td><td><button v-if="response.wish" type="button" class="button button-outline" :disabled="moderation.processing" @click="moderate(response)">{{ response.approved ? 'Sembunyikan' : 'Tampilkan ucapan' }}</button><span v-else>—</span></td></tr></tbody></table></div>
                <p v-else>Belum ada respons. Form tamu tersedia setelah undangan dipublish dengan RSVP atau ucapan aktif.</p>
                <InputError v-for="(error, key) in moderation.errors" :key="key" :message="error" />
                <nav v-if="responses.last_page > 1" class="mt-5 flex items-center justify-between" aria-label="Halaman respons"><Link v-if="responses.prev_page_url" :href="responses.prev_page_url" preserve-state preserve-scroll :only="['responses', 'responseStats']" class="button button-outline">Sebelumnya</Link><span>{{ responses.current_page }} / {{ responses.last_page }}</span><Link v-if="responses.next_page_url" :href="responses.next_page_url" preserve-state preserve-scroll :only="['responses', 'responseStats']" class="button button-outline">Berikutnya</Link></nav>
            </section>
        </div>
    </MoshiaLayout>
</template>
