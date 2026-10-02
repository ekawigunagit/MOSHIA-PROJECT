<script setup>
import { Head } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import ProductCatalog from '@/Components/ProductCatalog.vue';
defineProps({ products: { type: Array, default: () => [] } });
</script>
<template>
    <Head title="Admin Platform" />
    <MoshiaLayout>
        <template #header><h2 class="text-xl font-semibold">Admin Platform</h2></template>
        <div class="shell account-main space-y-8">
            <section class="detail-card">
                <span class="section-kicker">MOSHIA CORE</span>
                <h3 class="mt-4">Katalog platform</h3>
                <p>Ringkasan produk yang direncanakan. Pengelolaan paket, pembayaran, dan penerbitan produk akan tersedia bertahap.</p>
            </section>
            <ProductCatalog :products="products" />
        </div>
    </MoshiaLayout>
</template>
