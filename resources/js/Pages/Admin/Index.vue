<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import ProductCatalog from '@/Components/ProductCatalog.vue';
defineProps({
    products: { type: Array, default: () => [] },
    stats: { type: Object, required: true },
});
</script>
<template>
    <Head title="Dashboard Admin" />
    <MoshiaLayout>
        <template #header><h2 class="text-xl font-semibold">Dashboard Admin</h2></template>
        <div class="shell account-main space-y-8">
            <section class="detail-card">
                <span class="section-kicker">MOSHIA CORE</span>
                <h1 class="mt-4">Ringkasan platform Moshia</h1>
                <p>Pantau pengguna, workspace, dan katalog seluruh platform dari satu tempat.</p>
                <div class="dashboard-actions">
                    <Link :href="route('dashboard')" class="button button-outline">Buka workspace saya</Link>
                </div>
            </section>
            <section class="grid gap-4 sm:grid-cols-3" aria-label="Statistik platform">
                <article v-for="item in [
                    { label: 'Pengguna terdaftar', value: stats.users },
                    { label: 'Workspace', value: stats.workspaces },
                    { label: 'Produk dalam katalog', value: stats.products },
                ]" :key="item.label" class="detail-card">
                    <h3>{{ item.label }}</h3>
                    <p class="mt-3 text-3xl font-semibold">{{ item.value }}</p>
                </article>
            </section>
            <section class="detail-card">
                <h3>Pengelolaan platform</h3>
                <p>Kelola pengguna, hak akses, katalog produk, draft paket, dan verifikasi pembayaran.</p>
                <div class="dashboard-actions">
                    <Link :href="route('admin.users.index')" class="button button-primary">Kelola pengguna &amp; role</Link>
                    <Link :href="route('admin.products.index')" class="button button-outline">Kelola katalog produk</Link>
                    <Link :href="route('admin.plans.index')" class="button button-outline">Kelola draft paket</Link>
                    <Link v-if="$page.props.developmentPayments" :href="route('admin.payments.index')" class="button button-outline">Pembayaran manual</Link>
                </div>
            </section>
            <h2 class="text-2xl font-semibold">Katalog produk</h2>
            <ProductCatalog :products="products" />
        </div>
    </MoshiaLayout>
</template>
