<script setup>
import { computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    plan: { type: Object, default: null },
    products: { type: Array, required: true },
    weddingPackages: { type: Array, default: () => [] },
});
const title = computed(() => props.plan ? 'Edit Draft Paket' : 'Buat Draft Paket');
const form = useForm({
    product_id: props.plan?.product_id ?? '',
    name: props.plan?.name ?? '',
    description: props.plan?.description ?? '',
    wedding_package: props.plan?.commercial_terms?.key ?? '',
});
const isWedding = computed(() => props.products.find(p => String(p.id) === String(form.product_id))?.slug === 'wedding');
const selectedPackage = computed(() => {
    if (props.plan?.commercial_terms?.key === form.wedding_package) return props.plan.commercial_terms;
    return props.weddingPackages.find(p => p.key === form.wedding_package);
});
const knownFields = ['product_id', 'name', 'description', 'wedding_package'];
const extraErrors = computed(() => Object.entries(form.errors).filter(([key]) => !knownFields.includes(key)).map(([, message]) => message));
watch(isWedding, (value) => { if (!value) form.wedding_package = ''; });
function selectPackage() {
    if (!form.name && selectedPackage.value) form.name = 'Paket ' + selectedPackage.value.label;
}
function rupiah(amount) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount);
}
function save() {
    const options = { preserveScroll: true };
    if (props.plan) form.patch(route('admin.plans.update', props.plan.id), options);
    else form.post(route('admin.plans.store'), options);
}
</script>
<template>
    <Head :title="title" />
    <MoshiaLayout>
        <template #header><h2 class="text-xl font-semibold">{{ title }}</h2></template>
        <div class="shell account-main space-y-6">
            <Link :href="route('admin.plans.index')" class="button button-outline">Kembali ke draft paket</Link>
            <section class="detail-card max-w-3xl">
                <h1>{{ title }}</h1>
                <p>Siapkan draft paket. Untuk Wedding, pilih aturan Gold, Emerald, atau Diamond yang telah ditetapkan.</p>
                <form class="mt-6 space-y-5" @submit.prevent="save">
                    <div>
                        <label for="plan-product" class="moshia-label mb-2">Produk</label>
                        <select id="plan-product" v-model="form.product_id" class="moshia-input w-full" required>
                            <option disabled value="">Pilih produk</option>
                            <option v-for="product in products" :key="product.id" :value="product.id">{{ product.title }}{{ product.status === 'hidden' ? ' (disembunyikan)' : '' }}</option>
                        </select>
                        <InputError :message="form.errors.product_id" />
                    </div>
                    <div v-if="isWedding">
                        <label for="wedding-package" class="moshia-label mb-2">Aturan paket Wedding</label>
                        <select id="wedding-package" v-model="form.wedding_package" class="moshia-input w-full" @change="selectPackage">
                            <option value="">Draft tanpa aturan harga</option>
                            <option v-for="option in weddingPackages" :key="option.key" :value="option.key">{{ option.label }} — {{ rupiah(option.price_amount) }}</option>
                        </select>
                        <InputError :message="form.errors.wedding_package" />
                        <div v-if="selectedPackage" class="mt-4 space-y-2 text-sm" aria-live="polite">
                            <p><strong>{{ selectedPackage.label }} · {{ rupiah(selectedPackage.price_amount) }}</strong> — sekali bayar untuk satu undangan per workspace.</p>
                            <p>Masa aktif {{ selectedPackage.validity_months }} bulan sejak publish pertama. Edit atau publish ulang tidak mengulang masa aktif.</p>
                            <ul class="list-disc space-y-1 pl-5">
                                <li>Akses template standard.</li>
                                <li v-if="selectedPackage.video_header_request">Request video header invitation.</li>
                                <li v-if="selectedPackage.custom_domain">Pembelian domain .com termasuk paket. Tim memeriksa ketersediaan secara manual dan memproses setelah pelanggan menyetujui.</li>
                            </ul>
                            <p>Setelah kedaluwarsa, akses publik ditutup dan data tetap disimpan. Pembayaran kembali diperlukan untuk mengaktifkan undangan lama.</p>
                            <p v-if="selectedPackage.custom_domain">Ketentuan domain premium dan perpanjangan domain belum ditetapkan.</p>
                            <p>Ini aturan draft. Pembayaran, masa aktif undangan, dan proses domain belum dijalankan oleh form ini.</p>
                        </div>
                    </div>
                    <div>
                        <label for="plan-name" class="moshia-label mb-2">Nama paket</label>
                        <input id="plan-name" v-model="form.name" required maxlength="100" class="moshia-input w-full" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div>
                        <label for="plan-description" class="moshia-label mb-2">Deskripsi / catatan rencana</label>
                        <textarea id="plan-description" v-model="form.description" rows="6" maxlength="5000" class="moshia-input w-full" />
                        <InputError :message="form.errors.description" />
                    </div>
                    <p>Status: <strong>Draft</strong>. Menyimpan draft tidak memberikan akses produk dan tidak menerbitkan paket ke pelanggan.</p>
                    <InputError v-for="(message, index) in extraErrors" :key="index" :message="message" />
                    <button class="button button-primary" :disabled="form.processing || !products.length">Simpan draft</button>
                </form>
            </section>
        </div>
    </MoshiaLayout>
</template>
