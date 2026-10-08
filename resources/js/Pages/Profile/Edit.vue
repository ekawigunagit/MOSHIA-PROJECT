<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { dateTime, rupiah, statusLabels } from '@/billing';
defineProps({ mustVerifyEmail: Boolean, status: String, billingHistory: { type: Object, default: null } });
const page = usePage();
const sections = [
    { key: 'information', label: 'Account information' },
    { key: 'billing', label: 'Billing' },
    { key: 'security', label: 'Security' },
    { key: 'activity', label: 'Account activity' },
    { key: 'notifications', label: 'Notification settings' },
];
const section = computed(() => {
    const key = new URL(page.url, 'https://moshia.local').searchParams.get('section');
    return sections.some(s => s.key === key) ? key : 'information';
});
const title = computed(() => sections.find(s => s.key === section.value).label);
</script>
<template>
    <Head :title="title" />
    <MoshiaLayout>
        <template #header><h2>Pengaturan akun</h2></template>
        <div class="shell console-profile space-y-6">
            <div><h1 class="console-profile-title">{{ title }}</h1><p class="console-profile-subtitle">Kelola informasi dan preferensi akun Moshia Anda.</p></div>
            <nav class="console-profile-tabs" aria-label="Bagian pengaturan akun">
                <Link v-for="item in sections" :key="item.key" :href="route('profile.edit', { section: item.key })" :class="{ active: section === item.key }" :aria-current="section === item.key ? 'page' : undefined">{{ item.label }}</Link>
            </nav>
            <section v-if="section === 'information'" class="detail-card">
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" class="max-w-xl" />
            </section>
            <template v-else-if="section === 'security'">
                <section class="detail-card"><UpdatePasswordForm class="max-w-xl" /></section>
                <section class="detail-card"><DeleteUserForm class="max-w-xl" /></section>
            </template>
            <section v-else-if="section === 'billing'" class="detail-card">
                <h2 class="text-xl font-semibold">History Payment Product</h2>
                <p class="mb-6 mt-2">Riwayat pesanan dan pembayaran produk pada workspace milik Anda.</p>
                <div v-if="billingHistory?.data?.length" class="console-table-wrap">
                    <table class="console-table">
                        <thead><tr><th>Pesanan / produk</th><th>Workspace</th><th>Jumlah</th><th>Status</th><th>Tanggal dibayar</th></tr></thead>
                        <tbody><tr v-for="order in billingHistory.data" :key="order.id"><td>#{{ order.id }} · Wedding {{ order.package }}</td><td>{{ order.workspace }}</td><td>{{ rupiah(order.amount) }}</td><td><span class="console-table-status">{{ statusLabels[order.status] ?? order.status }}</span></td><td>{{ order.paid_at ? dateTime(order.paid_at) : 'Belum dibayar' }}</td></tr></tbody>
                    </table>
                    <nav v-if="billingHistory.last_page > 1" class="mt-5 flex items-center justify-between" aria-label="Halaman riwayat pembayaran">
                        <Link v-if="billingHistory.prev_page_url" :href="billingHistory.prev_page_url" class="button button-outline">Sebelumnya</Link><span>{{ billingHistory.current_page }} / {{ billingHistory.last_page }}</span><Link v-if="billingHistory.next_page_url" :href="billingHistory.next_page_url" class="button button-outline">Berikutnya</Link>
                    </nav>
                </div>
                <div v-else class="py-8"><h3>Belum ada riwayat pembayaran</h3><p>Pesanan produk akan tampil di sini setelah Anda membuatnya melalui workspace.</p><Link :href="route('dashboard')" class="button button-outline mt-5">Buka workspace</Link></div>
            </section>
            <section v-else-if="section === 'activity'" class="detail-card">
                <h2 class="text-xl font-semibold">Account activity</h2><p class="mt-3">Riwayat login dan aktivitas keamanan akun belum tersedia. Aktivitas workspace dan draft paket yang sudah didukung dapat dilihat melalui lonceng notifikasi.</p>
            </section>
            <section v-else class="detail-card">
                <h2 class="text-xl font-semibold">Notification settings</h2><p class="mt-3">Notifikasi dalam aplikasi tersedia melalui lonceng di kanan atas. Anda dapat membaca dan menandai notifikasi sebagai sudah dibaca.</p><p class="mt-3">Pengaturan kanal email, WhatsApp, dan preferensi pengiriman belum tersedia.</p>
            </section>
        </div>
    </MoshiaLayout>
</template>
