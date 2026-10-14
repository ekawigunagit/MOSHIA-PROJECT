<script setup>
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import ProductCatalog from '@/Components/ProductCatalog.vue';
import { dateTime, statusLabels } from '@/billing';

import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    products: { type: Array, default: () => [] },
    workspaces: { type: Array, default: () => [] },
    activeWorkspace: { type: Object, default: null },
    canManageWorkspace: Boolean,
    settledProducts: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Dashboard Workspace" />
    <MoshiaLayout>
        <template #header><h2 class="text-xl font-semibold">Dashboard Workspace</h2></template>
        <div class="shell account-main space-y-8">
            <section class="detail-card dashboard-intro">
                <span class="section-kicker">WELCOME TO YOUR MOSHIA</span>
                <h1 class="mt-5">{{ activeWorkspace?.name ?? 'Workspace Anda' }}</h1>
                <p>Pilih produk yang ingin Anda gunakan di workspace ini.</p>
                <div class="dashboard-actions">
                    <a href="#ecosystem" class="button button-primary">Add product</a>
                    <Link v-if="activeWorkspace && canManageWorkspace" :href="route('workspaces.edit', activeWorkspace.id)" class="button button-outline">Kelola workspace</Link>
                </div>
            </section>



            <section v-if="activeWorkspace" aria-labelledby="workspace-products-title" class="space-y-5">
                <div><span class="section-kicker">PRODUK WORKSPACE</span><h2 id="workspace-products-title">Produk Anda</h2><p>Produk yang pembayarannya sudah diterima di {{ activeWorkspace.name }}.</p></div>
                <article v-for="product in settledProducts" :key="product.id" class="detail-card">
                    <div class="flex flex-wrap items-center justify-between gap-3"><h3>{{ product.title }} · {{ product.package }}</h3><span class="console-table-status">Pembayaran diterima</span></div>
                    <p class="mt-3">Workspace: {{ activeWorkspace.name }} · Pesanan #{{ product.id }}</p>
                    <p>Status paket: {{ statusLabels[product.status] ?? product.status }}</p>
                    <p>Diterima {{ dateTime(product.paid_at) }}</p>
                    <p v-if="product.ends_at">Masa aktif sampai {{ dateTime(product.ends_at) }}</p>
                    <p v-else>Masa aktif {{ product.months }} bulan dimulai saat Publish pertama.</p>
                    <template v-if="product.can_manage">
                        <p class="mt-4">Edit isi undangan dan simulasikan tampilannya melalui Preview di editor. Undangan baru terbuka untuk tamu setelah Anda menekan Publish.</p>
                        <Link :href="route('wedding.edit', activeWorkspace.id)" class="button button-primary mt-4">Manage</Link>
                    </template>
                    <p v-else class="mt-4">Akses pengelolaan belum tersedia atau sudah berakhir. Periksa status paket pada Billing.</p>
                </article>
                <div v-if="!settledProducts.length" class="detail-card"><p>Belum ada produk dengan pembayaran diterima di workspace ini. Setelah admin menerima pembayaran, ringkasan produk dan tombol Manage akan muncul di sini.</p></div>
            </section>

            <section id="ecosystem" class="scroll-mt-24">
                <div class="section-heading">
                    <div><span class="section-kicker">MOSHIA PRODUCTS</span><h2>Add product</h2><p>{{ activeWorkspace ? 'Produk untuk ' + activeWorkspace.name : 'Buat workspace terlebih dahulu untuk menambahkan produk.' }}</p></div>
                </div>
                <Link v-if="!activeWorkspace" :href="route('workspaces.create')" class="button button-primary mb-5">Tambah Workspace</Link><ProductCatalog v-else :products="products" show-access>
                    <template #actions="{ product }">
                        <div v-if="product.slug === 'wedding'" class="dashboard-actions">
                            <Link :href="route('products.plans', { slug: 'wedding' })" class="button button-primary">Add product</Link><Link v-if="product.hasAccess && $page.props.developmentPayments" :href="route('wedding.edit', activeWorkspace.id)" class="button button-outline">Kelola undangan</Link>
                        </div>
                    </template>
                </ProductCatalog>
            </section>
        </div>
    </MoshiaLayout>
</template>
