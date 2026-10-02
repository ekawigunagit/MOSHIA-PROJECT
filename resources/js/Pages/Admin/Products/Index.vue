<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import MoshiaIcon from '@/Components/MoshiaIcon.vue';

defineProps({ products: { type: Array, required: true }, statuses: { type: Object, required: true } });
</script>

<template>
    <Head title="Katalog Produk" />
    <MoshiaLayout>
        <template #header><h2 class="text-xl font-semibold">Katalog Produk</h2></template>
        <div class="shell account-main space-y-6">
            <section class="detail-card">
                <span class="section-kicker">EKOSISTEM MOSHIA</span>
                <h1 class="mt-4">Kelola katalog produk</h1>
                <p>Atur informasi, urutan, dan tampilan produk di landing serta dashboard. Produk yang ditampilkan masih berstatus segera hadir.</p>
            </section>
            <div v-if="products.length" class="grid gap-5 sm:grid-cols-2">
                <article v-for="product in products" :key="product.id" class="detail-card">
                    <div class="detail-top"><MoshiaIcon :name="product.icon" /><span>{{ statuses[product.status] }}</span></div>
                    <h3>{{ product.title }}</h3>
                    <p>{{ product.summary }}</p>
                    <p class="mt-4 text-sm">Urutan {{ product.sort_order }} · Fase {{ product.phase }}</p>
                    <div class="dashboard-actions">
                        <Link :href="route('admin.products.edit', product.slug)" class="button button-outline" :aria-label="`Edit ${product.title}`">Edit produk</Link>
                    </div>
                </article>
            </div>
            <p v-else class="detail-card">Belum ada produk dalam katalog.</p>
        </div>
    </MoshiaLayout>
</template>
