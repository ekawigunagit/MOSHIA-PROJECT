<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import InputError from '@/Components/InputError.vue';
const props = defineProps({ plan: { type: Object, default: null }, products: { type: Array, required: true } });
const title = computed(() => props.plan ? 'Edit Draft Paket' : 'Buat Draft Paket');
const form = useForm({ product_id: props.plan?.product_id ?? '', name: props.plan?.name ?? '', description: props.plan?.description ?? '' });
const extraErrors = computed(() => Object.entries(form.errors).filter(([key]) => !['product_id', 'name', 'description'].includes(key)).map(([, message]) => message));
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
                <p>Catat nama dan deskripsi paket terlebih dahulu. Harga dan aturan penggunaan akan dilengkapi pada tahap berikutnya.</p>
                <form class="mt-6 space-y-5" @submit.prevent="save">
                    <div>
                        <label for="plan-product" class="moshia-label mb-2">Produk</label>
                        <select id="plan-product" v-model="form.product_id" class="moshia-input w-full" required>
                            <option disabled value="">Pilih produk</option>
                            <option v-for="product in products" :key="product.id" :value="product.id">{{ product.title }}{{ product.status === 'hidden' ? ' (disembunyikan)' : '' }}</option>
                        </select>
                        <InputError :message="form.errors.product_id" />
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
