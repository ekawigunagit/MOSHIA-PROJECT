<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import MoshiaIcon from '@/Components/MoshiaIcon.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    product: { type: Object, required: true },
    statuses: { type: Object, required: true },
    icons: { type: Object, required: true },
});
const fields = ['title', 'summary', 'description', 'icon', 'status', 'sort_order'];
const values = () => Object.fromEntries(fields.map((key) => [key, props.product[key]]));
const form = useForm(values());
const additionalErrors = computed(() => Object.entries(form.errors).filter(([key]) => !fields.includes(key)).map(([, message]) => message));
function save() {
    form.patch(route('admin.products.update', props.product.slug), {
        preserveScroll: true,
        onSuccess: () => { form.defaults(values()); form.reset(); },
    });
}
</script>

<template>
    <Head :title="`Edit ${product.title}`" />
    <MoshiaLayout>
        <template #header><h2 class="text-xl font-semibold">Edit Produk</h2></template>
        <div class="shell account-main space-y-6">
            <Link :href="route('admin.products.index')" class="button button-outline">Kembali ke katalog</Link>
            <div class="grid items-start gap-6 lg:grid-cols-3">
                <section class="detail-card lg:col-span-2">
                    <h1>{{ product.title }}</h1>
                    <p class="mt-3">ID: {{ product.id }} ? Slug: {{ product.slug }} · Fase roadmap: {{ product.phase }}</p>
                    <form class="mt-6 space-y-5" @submit.prevent="save">
                        <div>
                            <label for="product-title" class="moshia-label mb-2">Nama produk</label>
                            <input id="product-title" v-model="form.title" required maxlength="100" class="moshia-input w-full" />
                            <InputError :message="form.errors.title" />
                        </div>
                        <div>
                            <label for="product-summary" class="moshia-label mb-2">Ringkasan</label>
                            <textarea id="product-summary" v-model="form.summary" required maxlength="255" rows="2" class="moshia-input w-full" />
                            <InputError :message="form.errors.summary" />
                        </div>
                        <div>
                            <label for="product-description" class="moshia-label mb-2">Deskripsi</label>
                            <textarea id="product-description" v-model="form.description" required maxlength="3000" rows="5" class="moshia-input w-full" />
                            <InputError :message="form.errors.description" />
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="product-icon" class="moshia-label mb-2">Ikon</label>
                                <select id="product-icon" v-model="form.icon" class="moshia-input w-full"><option v-for="(label, value) in icons" :key="value" :value="value">{{ label }}</option></select>
                                <InputError :message="form.errors.icon" />
                            </div>
                            <div>
                                <label for="product-order" class="moshia-label mb-2">Urutan tampilan</label>
                                <input id="product-order" v-model="form.sort_order" type="number" min="0" max="65535" step="1" required class="moshia-input w-full" />
                                <InputError :message="form.errors.sort_order" />
                            </div>
                        </div>
                        <p class="text-sm">Angka lebih kecil tampil lebih dahulu. Jika sama, urutan mengikuti slug produk.</p>
                        <div>
                            <label for="product-status" class="moshia-label mb-2">Status tampilan</label>
                            <select id="product-status" v-model="form.status" class="moshia-input w-full"><option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option></select>
                            <InputError :message="form.errors.status" />
                            <p class="mt-2 text-sm">Produk tersembunyi hanya terlihat di admin. Hak akses workspace yang sudah tersimpan tetap dipertahankan.</p>
                        </div>
                        <InputError v-for="(message, index) in additionalErrors" :key="index" :message="message" />
                        <button class="button button-primary" :disabled="form.processing">Simpan perubahan</button>
                    </form>
                </section>
                <aside class="detail-card" aria-label="Pratinjau konten produk">
                    <span class="section-kicker">PRATINJAU KONTEN</span>
                    <div class="detail-top mt-5"><MoshiaIcon :name="form.icon" /><span>{{ statuses[form.status] }}</span></div>
                    <h3 class="break-words">{{ form.title }}</h3>
                    <p class="break-words">{{ form.summary }}</p>
                    <p class="mt-4 whitespace-pre-line break-words">{{ form.description }}</p>
                </aside>
            </div>
        </div>
    </MoshiaLayout>
</template>
