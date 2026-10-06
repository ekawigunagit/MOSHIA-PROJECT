<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
defineProps({ plans: { type: Object, required: true } });
</script>
<template>
    <Head title="Draft Paket" />
    <MoshiaLayout>
        <template #header><h2 class="text-xl font-semibold">Draft Paket</h2></template>
        <div class="shell account-main space-y-6">
            <section class="detail-card">
                <span class="section-kicker">PAKET PRODUK</span>
                <h1 class="mt-4">Siapkan pilihan paket</h1>
                <p>Draft hanya terlihat oleh admin. Pilihan aturan Wedding tersedia; draft ini tidak dipublikasikan otomatis. Simulasi pembayaran memakai pilihan paket Wedding yang ditetapkan untuk development.</p>
                <div class="dashboard-actions"><Link :href="route('admin.plans.create')" class="button button-primary">Buat draft paket</Link></div>
            </section>
            <div v-if="plans.data.length" class="grid gap-5 sm:grid-cols-2">
                <article v-for="plan in plans.data" :key="plan.id" class="detail-card">
                    <div class="detail-top"><span>{{ plan.product.title }}</span><span>Draft</span></div>
                    <h3>{{ plan.name }}</h3>
                    <p class="whitespace-pre-line break-words">{{ plan.description || 'Belum ada deskripsi.' }}</p>
                    <div class="dashboard-actions"><Link :href="route('admin.plans.edit', plan.id)" class="button button-outline" :aria-label="`Edit paket ${plan.name}`">Edit draft</Link></div>
                </article>
            </div>
            <p v-else class="detail-card">Belum ada draft paket. Mulai dengan memilih produk dan memberi nama paket.</p>
            <nav v-if="plans.last_page > 1" aria-label="Halaman draft paket" class="flex flex-wrap items-center gap-4">
                <Link v-if="plans.prev_page_url" :href="plans.prev_page_url" class="button button-outline">Sebelumnya</Link>
                <span>Halaman {{ plans.current_page }} dari {{ plans.last_page }}</span>
                <Link v-if="plans.next_page_url" :href="plans.next_page_url" class="button button-outline">Berikutnya</Link>
            </nav>
        </div>
    </MoshiaLayout>
</template>
