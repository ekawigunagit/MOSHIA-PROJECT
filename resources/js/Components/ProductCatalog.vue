<script setup>
import MoshiaIcon from '@/Components/MoshiaIcon.vue';
defineProps({ products: { type: Array, default: () => [] }, showAccess: Boolean });
</script>

<template>
    <div class="grid gap-5 sm:grid-cols-2">
        <article v-for="product in products" :key="product.id" class="detail-card">
            <div class="detail-top">
                <MoshiaIcon :name="product.icon" />
                <span>{{ product.status === 'hidden' ? 'Disembunyikan' : 'Segera hadir' }}</span>
            </div>
            <h3>{{ product.title }}</h3>
            <p>{{ product.description }}</p>
            <p v-if="showAccess" class="mt-4">
                {{ product.hasAccess ? 'Hak akses workspace tersedia. Produk belum diluncurkan.' : 'Belum ada hak akses untuk workspace ini.' }}
            </p>
            <div class="detail-label">
                <span>{{ product.slug === 'wedding' ? 'PRODUK PERTAMA YANG DIKEMBANGKAN' : 'DALAM ROADMAP MOSHIA' }}</span>
            </div>
            <slot name="actions" :product="product" />
        </article>
    </div>
    <p v-if="!products.length" class="detail-card">Katalog sedang diperbarui. Silakan periksa kembali nanti.</p>
</template>
