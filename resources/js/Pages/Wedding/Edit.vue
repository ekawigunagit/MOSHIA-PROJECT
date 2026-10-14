<script setup>
import { watch, computed, onMounted, onUnmounted, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import WeddingMediaField from '@/Components/WeddingMediaField.vue';
import InputError from '@/Components/InputError.vue';
import ShellIcon from '@/Components/ShellIcon.vue';
import { dateTime } from '@/billing';

const props = defineProps({
    workspace: Object, invitation: Object, order: Object, publicUrl: String,
    banks: Array, videoAllowed: Boolean, uploadedMediaId: Number, templates: Array, media: Array, responses: Object, responseStats: Object, limits: Object,
});
const draft = props.invitation.draft_content ?? {};
const form = useForm({
    header_type: props.videoAllowed ? (draft.header_type ?? 'image') : 'image',
    header_image_id: draft.header_image_id ?? null, header_video_id: props.videoAllowed ? (draft.header_video_id ?? null) : null,
    wedding_photo_id: draft.wedding_photo_id ?? null, wedding_quote: draft.wedding_quote ?? '',
    save_date_title: draft.save_date_title ?? 'Hari istimewa kami', save_date_message: draft.save_date_message ?? '',
    stories: draft.stories?.map(s => ({ title: s.title, body: s.body ?? '', image_id: s.image_id ?? null }))
        ?? ['Perkenalan', 'Pacaran', 'Engagement'].map((title, i) => ({ title, body: i === 0 ? (draft.story ?? '') : '', image_id: null })),
    gift_enabled: draft.gift_enabled ?? false,
    gift_accounts: (draft.gift_accounts ?? []).map(g => ({ bank_id: g.bank_id, account_number: g.account_number, account_holder: g.account_holder })),
    thank_you_image_id: draft.thank_you_image_id ?? null, thank_you_title: draft.thank_you_title ?? 'Terima kasih',
    thank_you_message: draft.thank_you_message ?? 'Kehadiran dan doa Anda menjadi bagian dari kebahagiaan kami.',
    template: draft.template ?? 'classic',
    partner_one: draft.partner_one ?? '', partner_two: draft.partner_two ?? '',
    partner_order: draft.partner_order ?? 'groom_first',
    partner_one_father: draft.partner_one_father ?? '', partner_one_mother: draft.partner_one_mother ?? '',
    partner_two_father: draft.partner_two_father ?? '', partner_two_mother: draft.partner_two_mother ?? '',
    partner_one_parents: draft.partner_one_parents ?? '', partner_two_parents: draft.partner_two_parents ?? '',
    partner_one_photo_id: draft.partner_one_photo_id ?? null, partner_two_photo_id: draft.partner_two_photo_id ?? null,
    event_name: draft.event_name ?? 'Pemberkatan Pernikahan',
    reception_enabled: draft.reception_enabled ?? !!draft.reception_date,
    reception_name: draft.reception_name ?? 'Resepsi', reception_map_url: draft.reception_map_url ?? '',
    event_date: draft.event_date ?? '', event_time: draft.event_time ?? '', timezone: draft.timezone ?? 'Asia/Jakarta',
    venue: draft.venue ?? '', map_url: draft.map_url ?? '',
    reception_date: draft.reception_date ?? '', reception_time: draft.reception_time ?? '', reception_venue: draft.reception_venue ?? '',
    message: draft.message ?? '', story: draft.story ?? '',
    cover_id: draft.cover_id ?? null, gallery_ids: draft.gallery_ids ?? [], music_id: draft.music_id ?? null,
    rsvp_enabled: draft.rsvp_enabled ?? true, wishes_enabled: draft.wishes_enabled ?? true,
});
const partners = computed(() => {
    const people = [
        { key: 'partner_one', label: 'Pasangan Pertama (Pengantin Pria)' },
        { key: 'partner_two', label: 'Pasangan Kedua (Pengantin Wanita)' },
    ];
    return form.partner_order === 'bride_first' ? people.reverse() : people;
});
const firstName = computed(() => form[partners.value[0].key] || 'Belum diisi');
const secondName = computed(() => form[partners.value[1].key] || 'Belum diisi');
const steps = ['Template', 'Pasangan', 'Acara', 'Susunan undangan', 'RSVP & ucapan', 'Preview & publish'];
const step = ref(0);
const confirmPublish = ref(false);
const confirmUnpublish = ref(false);
const sectionUploading = ref(false);
const mediaFieldProps = computed(() => ({ media: props.media, workspaceId: props.workspace.id, limitKb: props.limits.imageKb }));
const storyKeys = new WeakMap(); let nextStoryKey = 0;
function storyKey(story) { if (!storyKeys.has(story)) storyKeys.set(story, ++nextStoryKey); return storyKeys.get(story); }
const galleryUploadId = ref(null);
watch(galleryUploadId, id => { if (id && !form.gallery_ids.includes(id) && form.gallery_ids.length < props.limits.gallery) form.gallery_ids.push(id); });
watch(() => form.header_type, kind => { if (kind === 'image') form.header_video_id = null; });
const uploadInput = ref(null);
const upload = useForm({ kind: 'image', file: null });
const portraitUploads = {
    partner_one: useForm({ kind: 'image', file: null }),
    partner_two: useForm({ kind: 'image', file: null }),
};
const portraitInputs = {};
function uploadPortrait(key) {
    const request = portraitUploads[key];
    if (!request.file || busy.value) return;
    request.post(route('wedding.media.store', props.workspace.id), {
        preserveScroll: true, preserveState: true, forceFormData: true,
        onSuccess: (page) => {
            const id = page.props.uploadedMediaId;
            if (id && page.props.media.some(media => media.id === id && media.kind === 'image')) {
                form[key + '_photo_id'] = id;
                form.clearErrors(key + '_photo_id');
            }
            request.reset('file');
            if (portraitInputs[key]) portraitInputs[key].value = '';
        },
    });
}
const publication = useForm({});
const moderation = useForm({ approved: false });
const images = computed(() => props.media.filter(m => m.kind === 'image'));
const music = computed(() => props.media.filter(m => m.kind === 'music'));
const chosenTheme = computed(() => props.templates.find(t => t.key === form.template) ?? props.templates[0]);
const busy = computed(() => sectionUploading.value || form.processing || publication.processing || upload.processing || Object.values(portraitUploads).some(request => request.processing));
const mediaUrl = id => props.media.find(m => m.id === id)?.url;
const requiredReady = computed(() => !!(form.partner_one.trim() && form.partner_two.trim() && form.event_date && form.venue.trim()));
function save() {
    confirmPublish.value = false;
    form.patch(route('wedding.update', props.workspace.id), {
        preserveScroll: true, onSuccess: () => form.defaults(),
        onError: (errors) => {
            const first = Object.keys(errors)[0];
            step.value = first?.startsWith('partner') ? 1
                : ['event_name','reception_enabled','reception_name','reception_map_url','event_date','event_time','timezone','venue','map_url','reception_date','reception_time','reception_venue'].includes(first) ? 2
                : ['cover_id','music_id','gallery_ids','header_','wedding_','save_date_','stories','gift_','thank_you_'].some(k => first?.startsWith(k)) ? 3 : step.value;
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
function unpublish() {
    publication.post(route('wedding.unpublish', props.workspace.id), {
        preserveScroll: true,
        onSuccess: () => { confirmUnpublish.value = false; confirmPublish.value = false; },
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
                        </section>
                        <section v-show="step === 1">
                            <h2>Kenalkan pasangan</h2><p class="studio-help">Nama wajib diisi. Foto dan informasi keluarga bersifat opsional.</p>
                            <button type="button" class="button button-outline mt-4" :disabled="busy" @click="form.partner_order = form.partner_order === 'groom_first' ? 'bride_first' : 'groom_first'">Tukar posisi atas–bawah</button>
                            <p class="studio-help" aria-live="polite">Urutan tampil: {{ partners[0].label }} → {{ partners[1].label }}. Simpan draft untuk memperbarui preview.</p>
                            <InputError :message="form.errors.partner_order" />
                            <div v-for="{ label, key } in partners" :key="key" class="studio-field-group">
                                <h3>{{ label }}</h3>
                                <label :for="key" class="moshia-label">Nama lengkap *</label><input :id="key" v-model="form[key]" @input="form.clearErrors(key)" maxlength="100" class="moshia-input w-full" />
                                <InputError :message="form.errors[key]" />
                                <label :for="key + '-father'" class="moshia-label mt-4">Nama Lengkap Bapak</label>
                                <input :id="key + '-father'" v-model="form[key + '_father']" maxlength="200" class="moshia-input w-full" @input="form.clearErrors(key + '_father')" />
                                <InputError :message="form.errors[key + '_father']" />
                                <label :for="key + '-mother'" class="moshia-label mt-4">Nama Lengkap Ibu</label>
                                <input :id="key + '-mother'" v-model="form[key + '_mother']" maxlength="200" class="moshia-input w-full" @input="form.clearErrors(key + '_mother')" />
                                <InputError :message="form.errors[key + '_mother']" />
                                <div v-if="draft[key + '_parents']" class="mt-4">
                                    <label :for="key + '-parents'" class="moshia-label">Catatan orang tua sebelumnya</label>
                                    <textarea :id="key + '-parents'" v-model="form[key + '_parents']" maxlength="200" class="moshia-input w-full" />
                                    <p class="studio-help">Pisahkan nama di atas sesuai identitasnya. Catatan lama hanya ditampilkan jika nama bapak dan ibu belum diisi; Anda dapat mengosongkannya.</p>
                                    <InputError :message="form.errors[key + '_parents']" />
                                </div>
                                <label :for="key + '-photo'" class="moshia-label mt-4">{{ key === 'partner_one' ? 'Foto Pengantin Pria' : 'Foto Pengantin Wanita' }}</label>
                                <div class="studio-upload mb-4">
                                    <label :for="key + '-upload'" class="moshia-label">Unggah foto baru</label>
                                    <input :id="key + '-upload'" :ref="el => portraitInputs[key] = el" type="file" accept="image/jpeg,image/png,image/webp" :disabled="busy" @change="portraitUploads[key].file = $event.target.files[0] ?? null; portraitUploads[key].clearErrors()" />
                                    <p class="studio-help">JPG, PNG atau WebP, maksimal {{ limits.imageKb / 1024 }} MB. Setelah upload, foto otomatis dipilih. Simpan draft untuk menyimpan pilihan.</p>
                                    <InputError v-for="(error, field) in portraitUploads[key].errors" :key="field" :message="error" />
                                    <progress v-if="portraitUploads[key].progress" :value="portraitUploads[key].progress.percentage" max="100" class="w-full" aria-label="Progres upload foto">{{ portraitUploads[key].progress.percentage }}%</progress>
                                    <button type="button" class="button button-outline mt-3" :disabled="busy || !portraitUploads[key].file" @click="uploadPortrait(key)">{{ portraitUploads[key].processing ? 'Mengunggah…' : key === 'partner_one' ? 'Unggah Foto Pengantin Pria' : 'Unggah Foto Pengantin Wanita' }}</button>
                                </div>
                                <label :for="key + '-photo'" class="moshia-label">Pilih dari foto yang sudah diunggah</label>
                                <InputError :message="form.errors[key + '_photo_id']" />
                                <select :disabled="busy" :id="key + '-photo'" v-model="form[key + '_photo_id']" class="moshia-input w-full"><option :value="null">Tanpa foto</option><option v-for="m in images" :key="m.id" :value="m.id">Foto #{{ m.id }}</option></select>
                                <img v-if="form[key + '_photo_id']" :src="mediaUrl(form[key + '_photo_id'])" class="studio-portrait" :alt="'Foto ' + label" />
                            </div>
                            <button type="button" class="studio-text-link" @click="step = 3">Kelola galeri dan musik →</button>
                        </section>
                        <section v-show="step === 2">
                            <label for="w-event-name" class="moshia-label">Nama acara utama *</label><input id="w-event-name" v-model="form.event_name" maxlength="100" class="moshia-input w-full" placeholder="Pemberkatan Pernikahan / Akad Nikah" /><InputError :message="form.errors.event_name" />
                            <h2>Detail acara</h2><p class="studio-help">Tanggal dan lokasi utama wajib diisi. Waktu ditampilkan sesuai zona yang Anda pilih.</p>
                            <div class="studio-two-fields">
                                <div><label for="w-date" class="moshia-label">Tanggal acara utama *</label><input id="w-date" v-model="form.event_date" type="date" class="moshia-input w-full" /><InputError :message="form.errors.event_date" /></div>
                                <div><label for="w-time" class="moshia-label">Jam acara</label><input id="w-time" v-model="form.event_time" type="time" class="moshia-input w-full" /></div>
                            </div>
                            <label for="w-zone" class="moshia-label mt-5">Zona waktu</label><select id="w-zone" v-model="form.timezone" class="moshia-input w-full"><option value="Asia/Jakarta">WIB</option><option value="Asia/Makassar">WITA</option><option value="Asia/Jayapura">WIT</option></select>
                            <label for="w-venue" class="moshia-label mt-5">Nama tempat dan alamat *</label><textarea id="w-venue" v-model="form.venue" maxlength="300" rows="3" class="moshia-input w-full" /><InputError :message="form.errors.venue" />
                            <label for="w-map" class="moshia-label mt-5">Tautan peta (https://…)</label><input id="w-map" v-model="form.map_url" type="url" maxlength="1000" class="moshia-input w-full" placeholder="https://maps.google.com/..." />
                            <label class="moshia-label mt-5"><input v-model="form.reception_enabled" type="checkbox" aria-controls="reception-fields" :aria-expanded="form.reception_enabled" /> Tambahkan resepsi/acara kedua</label>
                            <div v-show="form.reception_enabled" id="reception-fields" class="studio-field-group">
                                <h3>Resepsi / acara kedua</h3>
                                <label for="r-name" class="moshia-label">Nama acara *</label><input id="r-name" v-model="form.reception_name" maxlength="100" class="moshia-input w-full" /><InputError :message="form.errors.reception_name" />
                                <div class="studio-two-fields mt-4">
                                    <div><label for="r-date" class="moshia-label">Tanggal *</label><input id="r-date" v-model="form.reception_date" type="date" class="moshia-input w-full" /><InputError :message="form.errors.reception_date" /></div>
                                    <div><label for="r-time" class="moshia-label">Jam</label><input id="r-time" v-model="form.reception_time" type="time" class="moshia-input w-full" /><InputError :message="form.errors.reception_time" /></div>
                                </div>
                                <label for="r-venue" class="moshia-label mt-4">Nama tempat dan alamat *</label><textarea id="r-venue" v-model="form.reception_venue" rows="3" maxlength="300" class="moshia-input w-full" placeholder="Nama gedung/restoran, jalan, kota" /><InputError :message="form.errors.reception_venue" />
                                <label for="r-map" class="moshia-label mt-4">Tautan peta lokasi resepsi (https://…)</label><input id="r-map" v-model="form.reception_map_url" type="url" maxlength="1000" class="moshia-input w-full" placeholder="https://maps.google.com/..." /><InputError :message="form.errors.reception_map_url" />
                            </div>
                        </section>

                        <section v-show="step === 3">
                            <h2>Susunan halaman undangan</h2><p class="studio-help">Isi bagian sesuai urutan tamu membacanya. Upload per bagian, lalu simpan draft sebelum preview.</p>
                            <div class="studio-field-group"><h3>1. Foto cover / sampul</h3><p class="studio-help">Foto pembuka undangan sebelum tamu masuk ke halaman utama.</p>
                                <WeddingMediaField v-model="form.cover_id" field-id="cover" label="Upload foto sampul" v-bind="mediaFieldProps" :disabled="busy" @uploading="sectionUploading = $event" />
                            </div>
                            <div class="studio-field-group"><h3>2. Background header</h3>
                                <label for="header-type" class="moshia-label">Jenis background</label><select id="header-type" v-model="form.header_type" class="moshia-input w-full" :disabled="busy"><option value="image">Foto</option><option v-if="videoAllowed" value="video">Video — Emerald / Diamond</option></select>
                                <p v-if="!videoAllowed" class="studio-help">Paket Gold menggunakan foto. Video header tersedia untuk Emerald dan Diamond.</p>
                                <WeddingMediaField v-show="form.header_type === 'image'" v-model="form.header_image_id" field-id="header-image" label="Upload background header" v-bind="mediaFieldProps" :disabled="busy" @uploading="sectionUploading = $event" />
                                <WeddingMediaField v-if="videoAllowed && form.header_type === 'video'" v-model="form.header_video_id" field-id="header-video" label="Upload video header" kind="video" v-bind="{ ...mediaFieldProps, limitKb: limits.videoKb }" :disabled="busy" @uploading="sectionUploading = $event" />
                                <InputError :message="form.errors.header_video_id || form.errors.header_type" />
                            </div>
                            <div class="studio-field-group"><h3>3. Foto Wedding & ayat / kata-kata</h3>
                                <WeddingMediaField v-model="form.wedding_photo_id" field-id="wedding-photo" label="Upload foto Wedding" v-bind="mediaFieldProps" :disabled="busy" @uploading="sectionUploading = $event" />
                                <label for="wedding-quote" class="moshia-label mt-4">Ayat / kata-kata Wedding</label><textarea id="wedding-quote" v-model="form.wedding_quote" rows="5" maxlength="3000" class="moshia-input w-full" placeholder="Tuliskan ayat pilihan atau kata-kata tentang pernikahan..." />
                            </div>
                            <div class="studio-field-group"><h3>4. Save the date</h3>
                                <label for="save-date-title" class="moshia-label">Judul</label><input id="save-date-title" v-model="form.save_date_title" maxlength="150" class="moshia-input w-full" />
                                <label for="save-date-message" class="moshia-label mt-4">Kata-kata</label><textarea id="save-date-message" v-model="form.save_date_message" rows="3" maxlength="2000" class="moshia-input w-full" />
                                <p class="studio-help">Tanggal, countdown, lokasi dan resepsi mengikuti Detail acara.</p><button type="button" class="studio-text-link" @click="step = 2">Edit detail acara →</button>
                            </div>
                            <div class="studio-field-group"><h3>5. Our Story Love</h3><p class="studio-help">Tambah atau hapus bagian cerita sesuai perjalanan Anda.</p>
                                <article v-for="(story, index) in form.stories" :key="storyKey(story)" class="studio-story-card">
                                    <div class="flex items-center justify-between gap-3"><strong>Cerita {{ index + 1 }}</strong><button type="button" class="studio-text-link" :disabled="busy" @click="form.stories.splice(index, 1)">Hapus bagian</button></div>
                                    <label :for="'story-title-' + index" class="moshia-label mt-4">Judul *</label><input :id="'story-title-' + index" v-model="story.title" maxlength="150" class="moshia-input w-full" />
                                    <WeddingMediaField v-model="story.image_id" :field-id="'story-photo-' + index" label="Gambar cerita" v-bind="mediaFieldProps" :disabled="busy" @uploading="sectionUploading = $event" />
                                    <label :for="'story-body-' + index" class="moshia-label mt-4">Tulisan</label><textarea :id="'story-body-' + index" v-model="story.body" rows="4" maxlength="3000" class="moshia-input w-full" />
                                </article>
                                <button type="button" class="button button-outline mt-4" :disabled="busy || form.stories.length >= limits.stories" @click="form.stories.push({ title: 'Cerita baru', body: '', image_id: null })">Tambah bagian cerita</button>
                            </div>
                            <div class="studio-field-group"><h3>6. RSVP & ucapan</h3><p class="studio-help">Menggunakan form konfirmasi dan moderasi ucapan yang sudah tersedia.</p><button type="button" class="studio-text-link" @click="step = 4">Atur RSVP & ucapan →</button></div>
                            <div class="studio-field-group"><h3>7. Wedding Gift</h3><label class="studio-setting"><span>Tampilkan rekening hadiah</span><input v-model="form.gift_enabled" type="checkbox" /></label>
                                <div v-show="form.gift_enabled"><p class="studio-help">Rekening ini ditampilkan kepada tamu undangan. Periksa nomor dan nama pemilik sebelum publish.</p>
                                    <article v-for="(gift, index) in form.gift_accounts" :key="index" class="studio-story-card">
                                        <label :for="'gift-bank-' + index" class="moshia-label">Bank *</label><select :id="'gift-bank-' + index" v-model="gift.bank_id" class="moshia-input w-full"><option :value="null">Pilih bank</option><option v-for="bank in banks" :key="bank.id" :value="bank.id">{{ bank.name }}</option></select>
                                        <label :for="'gift-number-' + index" class="moshia-label mt-4">Nomor rekening *</label><input :id="'gift-number-' + index" v-model="gift.account_number" inputmode="numeric" maxlength="40" class="moshia-input w-full" />
                                        <label :for="'gift-holder-' + index" class="moshia-label mt-4">Nama pemilik rekening *</label><input :id="'gift-holder-' + index" v-model="gift.account_holder" maxlength="150" class="moshia-input w-full" />
                                        <button type="button" class="studio-text-link mt-4" @click="form.gift_accounts.splice(index, 1)">Hapus rekening</button>
                                    </article>
                                    <button type="button" class="button button-outline mt-4" :disabled="form.gift_accounts.length >= limits.gifts" @click="form.gift_accounts.push({ bank_id: null, account_number: '', account_holder: '' })">Tambah rekening</button>
                                </div>
                            </div>
                            <div class="studio-field-group"><h3>8. Thank you page</h3>
                                <WeddingMediaField v-model="form.thank_you_image_id" field-id="thank-you-photo" label="Background penutup" v-bind="mediaFieldProps" :disabled="busy" @uploading="sectionUploading = $event" />
                                <label for="thank-title" class="moshia-label mt-4">Judul Thank you page</label><input id="thank-title" v-model="form.thank_you_title" maxlength="150" class="moshia-input w-full" />
                                <label for="thank-message" class="moshia-label mt-4">Kata-kata penutup</label><textarea id="thank-message" v-model="form.thank_you_message" rows="4" maxlength="3000" class="moshia-input w-full" />
                                <p class="studio-help">Nama pengantin otomatis: {{ firstName }} & {{ secondName }}.</p>
                            </div>
                            <div class="studio-field-group"><h3>9. Footer</h3><p>Made with love - Moshia Invitation</p></div>
                            <div class="studio-field-group"><h3>Galeri foto tambahan</h3>
                                <WeddingMediaField v-model="galleryUploadId" field-id="gallery-upload" label="Upload foto galeri" v-bind="mediaFieldProps" :disabled="busy" @uploading="sectionUploading = $event" />
                                <p class="studio-help">Foto terpilih {{ form.gallery_ids.length }} / {{ limits.gallery }}. Klik foto untuk memilih/melepas.</p>
                                <div class="studio-media-grid"><button v-for="m in images" :key="m.id" type="button" :aria-pressed="form.gallery_ids.includes(m.id)" :disabled="!form.gallery_ids.includes(m.id) && form.gallery_ids.length >= limits.gallery" :class="{ selected: form.gallery_ids.includes(m.id) }" @click="toggleGallery(m.id)"><img :src="m.url" :alt="'Foto #' + m.id" loading="lazy" /><span>#{{ m.id }} {{ form.gallery_ids.includes(m.id) ? '✓' : '' }}</span></button></div>
                            </div>
                            <div class="studio-field-group"><h3>Musik latar</h3><WeddingMediaField v-model="form.music_id" field-id="background-music" label="Upload musik MP3" kind="music" v-bind="{ ...mediaFieldProps, limitKb: limits.musicKb }" :disabled="busy" @uploading="sectionUploading = $event" /><p class="studio-help">Tamu menekan putar untuk mendengarkan musik.</p></div>
                        </section>
                        <section v-show="step === 4">
                            <h2>RSVP & ucapan tamu</h2><p class="studio-help">Tamu dapat mengirim respons tanpa akun Moshia. Kehadiran hanya terlihat oleh Anda; ucapan memerlukan persetujuan sebelum tampil publik.</p>
                            <label class="studio-setting"><span><strong>Konfirmasi kehadiran (RSVP)</strong><small>Nama, status hadir, dan jumlah tamu (maksimal 10 per respons).</small></span><input v-model="form.rsvp_enabled" type="checkbox" /></label>
                            <label class="studio-setting"><span><strong>Ucapan & doa</strong><small>Moderasi ucapan pada daftar respons di bawah editor.</small></span><input v-model="form.wishes_enabled" type="checkbox" /></label>
                            <p class="studio-help mt-5">Pengaturan baru berlaku di halaman publik setelah Publish. Respons tamu belum diverifikasi identitasnya.</p>
                        </section>
                        <section v-show="step === 5">
                            <h2>Tinjau sebelum publish</h2><p class="studio-help">Simpan draft, buka preview privat, kemudian publish jika semuanya sudah sesuai.</p>
                            <dl class="studio-review"><div><dt>Template</dt><dd>{{ chosenTheme.label }}</dd></div><div><dt>Pasangan</dt><dd>{{ firstName }} & {{ secondName }}</dd></div><div><dt>Acara</dt><dd>{{ form.event_name }} · {{ form.event_date || 'Belum diisi' }} {{ form.event_time }} · {{ form.timezone }}</dd></div><div><dt>Lokasi</dt><dd>{{ form.venue || 'Belum diisi' }}</dd></div><div><dt>Galeri / musik</dt><dd>{{ form.gallery_ids.length }} foto · {{ form.music_id ? 'Musik dipilih' : 'Tanpa musik' }}</dd></div></dl>
                            <p v-if="!requiredReady" class="studio-help">Lengkapi nama pasangan, tanggal dan lokasi untuk menyimpan draft.</p>
                            <p v-if="form.isDirty" class="studio-help">Ada perubahan belum disimpan. Preview membuka draft terakhir yang tersimpan.</p>
                            <div class="dashboard-actions"><a v-if="invitation.draft_content" :href="route('wedding.preview', workspace.id)" target="_blank" rel="noopener" class="button button-outline">Buka preview privat ↗</a><button type="button" class="button button-primary" :disabled="!invitation.draft_content || form.isDirty || busy" @click="confirmPublish = true">Publish undangan</button></div>
                            <div v-if="confirmPublish" class="studio-confirm" role="region" aria-label="Konfirmasi publish"><p>{{ order.first_published_at ? 'Konten publik akan diperbarui. Masa aktif tetap sama.' : 'Publish akan membuka undangan untuk publik dan memulai masa aktif paket.' }}</p><button type="button" class="button button-primary" :disabled="busy || form.isDirty" @click="publish">Ya, publish sekarang</button><button type="button" class="button button-outline" :disabled="busy" @click="confirmPublish = false">Batal</button></div>
                            <InputError :message="publication.errors.publish" />
                            <div v-if="invitation.published_at && order.first_published_at" class="studio-confirm">
                                <strong>Tautan undangan</strong><a :href="publicUrl" target="_blank" rel="noopener" class="mt-2 block break-all underline">{{ publicUrl }}</a><p>Salin tautan ini untuk dibagikan kepada tamu.</p>
                                <button type="button" class="button button-outline mt-4" :disabled="busy" @click="confirmUnpublish = true; confirmPublish = false">Unpublish</button>
                                <div v-if="confirmUnpublish" class="mt-4" role="region" aria-label="Konfirmasi unpublish">
                                    <p>Tutup undangan, media publik, RSVP dan ucapan dari pengunjung? Data tetap tersimpan dan masa aktif tetap berjalan.</p>
                                    <button type="button" class="button button-primary" :disabled="busy" @click="unpublish">Ya, tutup undangan</button>
                                    <button type="button" class="button button-outline" :disabled="busy" @click="confirmUnpublish = false">Batal</button>
                                </div>
                            </div>
                            <p v-else-if="order.first_published_at" class="studio-help" role="status">Undangan tidak sedang ditampilkan ke publik. Publish kembali untuk membukanya; tanggal akhir masa aktif tetap sama.</p>
                        </section>
                        <footer class="studio-form-footer"><button v-if="step > 0" type="button" class="button button-outline" @click="step--">Kembali</button><button type="submit" class="button button-outline" :disabled="busy">{{ form.processing ? 'Menyimpan…' : 'Simpan draft' }}</button><button v-if="step < steps.length - 1" type="button" class="button button-primary" @click="step++">Lanjut →</button></footer>
                    </form>
                </div>
                <aside class="studio-summary" aria-label="Ringkasan tampilan"><div class="studio-mini-preview" :style="{ background: chosenTheme.background, color: chosenTheme.text }"><img v-if="form.cover_id" :src="mediaUrl(form.cover_id)" alt="Foto sampul pilihan" /><small>THE WEDDING OF</small><h2>{{ firstName }}<span>&</span>{{ secondName }}</h2><p>{{ form.event_date || 'Tanggal acara Anda' }}</p><p>{{ form.venue || 'Lokasi acara' }}</p></div><p class="studio-help">Ringkasan isian, bukan preview final. Gunakan preview privat untuk memeriksa hasil tersimpan.</p></aside>
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
