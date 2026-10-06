<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import InputError from '@/Components/InputError.vue';
import { dateTime } from '@/billing';

const props = defineProps({ workspace: Object, invitation: Object, order: Object, publicUrl: String });
const form = useForm({
    partner_one: props.invitation.draft_content?.partner_one ?? '',
    partner_two: props.invitation.draft_content?.partner_two ?? '',
    event_date: props.invitation.draft_content?.event_date ?? '',
    venue: props.invitation.draft_content?.venue ?? '',
    message: props.invitation.draft_content?.message ?? '',
});
const publication = useForm({});
function save() {
    form.patch(route('wedding.update', props.workspace.id), {
        preserveScroll: true, onSuccess: () => form.defaults(),
    });
}
</script>

<template>
    <Head title="Editor Wedding" />
    <MoshiaLayout>
        <template #header><h2 class="text-xl font-semibold">Wedding · {{ workspace.name }}</h2></template>
        <div class="shell account-main space-y-6">
            <section class="detail-card">
                <span class="section-kicker">MOSHIA WEDDING</span>
                <h1 class="mt-4">Siapkan undangan Anda</h1>
                <p>Paket {{ order.terms.label }} · {{ order.terms.validity_months }} bulan.</p>
                <p v-if="!order.first_published_at">Pembayaran sudah diterima. Masa aktif paket ini dimulai ketika Anda menekan Publish.</p>
                <p v-else>Masa aktif {{ dateTime(order.first_published_at) }} — {{ dateTime(order.ends_at) }}. Publish ulang tidak mengubah tanggal akhir.</p>
                <Link :href="route('billing.index', workspace.id)" class="mt-4 inline-block underline">Lihat paket &amp; pembayaran</Link>
            </section>
            <form class="detail-card space-y-5" @submit.prevent="save">
                <h2 class="text-2xl font-semibold">Konten undangan</h2>
                <div v-for="field in [{ key: 'partner_one', label: 'Nama pasangan pertama' }, { key: 'partner_two', label: 'Nama pasangan kedua' }]" :key="field.key">
                    <label :for="field.key" class="moshia-label">{{ field.label }}</label>
                    <input :id="field.key" v-model="form[field.key]" required maxlength="100" class="moshia-input w-full" />
                    <InputError :message="form.errors[field.key]" />
                </div>
                <div>
                    <label for="event-date" class="moshia-label">Tanggal acara</label>
                    <input id="event-date" v-model="form.event_date" type="date" required class="moshia-input w-full" />
                    <InputError :message="form.errors.event_date" />
                </div>
                <div>
                    <label for="venue" class="moshia-label">Lokasi acara</label>
                    <textarea id="venue" v-model="form.venue" required maxlength="300" class="moshia-input w-full" />
                    <InputError :message="form.errors.venue" />
                </div>
                <div>
                    <label for="message" class="moshia-label">Pesan undangan</label>
                    <textarea id="message" v-model="form.message" maxlength="2000" rows="5" class="moshia-input w-full" />
                    <InputError :message="form.errors.message" />
                </div>
                <button class="button button-primary" :disabled="form.processing || publication.processing">Simpan draft</button>
            </form>
            <section class="detail-card">
                <h2 class="text-2xl font-semibold">Preview &amp; Publish</h2>
                <p>Preview menampilkan draft terakhir yang disimpan. Publish memperbarui tautan publik.</p>
                <p v-if="form.isDirty" class="mt-3">Simpan perubahan terlebih dahulu sebelum Publish.</p>
                <InputError :message="publication.errors.publish" />
                <div class="dashboard-actions">
                    <a v-if="invitation.draft_content" :href="route('wedding.preview', workspace.id)" target="_blank" rel="noopener" class="button button-outline">Preview privat</a>
                    <button class="button button-primary" :disabled="!invitation.draft_content || form.isDirty || form.processing || publication.processing" @click="publication.post(route('wedding.publish', workspace.id), { preserveScroll: true })">Publish</button>
                </div>
                <a v-if="order.first_published_at" :href="publicUrl" target="_blank" rel="noopener" class="mt-5 block break-all underline">{{ publicUrl }}</a>
            </section>
        </div>
    </MoshiaLayout>
</template>
