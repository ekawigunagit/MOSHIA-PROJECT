<script setup>
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import ProductCatalog from '@/Components/ProductCatalog.vue';

import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    products: { type: Array, default: () => [] },
    workspaces: { type: Array, default: () => [] },
    activeWorkspace: { type: Object, default: null },
    canManageWorkspace: Boolean,
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
