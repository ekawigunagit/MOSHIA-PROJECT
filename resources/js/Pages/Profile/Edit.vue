<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';

import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { dateTime, rupiah, statusLabels } from '@/billing';
defineProps({ mustVerifyEmail: Boolean, status: String, billingHistory: { type: Object, default: null }, subscriptions: { type: Object, default: null } });
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
const billingTabs = [
    { key: 'subscription', label: 'Subscription' },
    { key: 'history', label: 'Payment History' },
    { key: 'method', label: 'Payment Method' },
];
const billingTab = computed(() => {
    const key = new URL(page.url, 'https://moshia.local').searchParams.get('billing_tab');
    return billingTabs.some(tab => tab.key === key) ? key : 'subscription';
});
const billingTitle = computed(() => billingTabs.find(tab => tab.key === billingTab.value).label);
</script>
<template>
    <Head :title="section === 'billing' ? billingTitle : title" />
    <MoshiaLayout>
        <template #header><h2>{{ section === 'billing' ? 'Billing' : 'Pengaturan akun' }}</h2></template>
        <div class="shell console-profile space-y-6">
            <div v-if="section === 'billing'">
                <h1 class="console-profile-title">{{ billingTitle }}</h1>
            </div>
            <div v-else><h1 class="console-profile-title">{{ title }}</h1><p class="console-profile-subtitle">Kelola informasi dan preferensi akun Moshia Anda.</p></div>
            <nav v-if="section !== 'billing'" class="console-profile-tabs" aria-label="Bagian pengaturan akun">
                <Link v-for="item in sections" :key="item.key" :href="route('profile.edit', { section: item.key })" :class="{ active: section === item.key }" :aria-current="section === item.key ? 'page' : undefined">{{ item.label }}</Link>
            </nav>
            <section v-if="section === 'information'" class="detail-card">
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" class="max-w-xl" />
            </section>
            <template v-else-if="section === 'security'">
                <section class="detail-card"><UpdatePasswordForm class="max-w-xl" /></section>
                <section class="detail-card"><DeleteUserForm class="max-w-xl" /></section>
            </template>
            <div v-else-if="section === 'billing'" class="space-y-6">
                <nav class="console-profile-tabs" aria-label="Menu Billing">
                    <Link v-for="tab in billingTabs" :key="tab.key" :href="route('profile.edit', { section: 'billing', billing_tab: tab.key })" :class="{ active: billingTab === tab.key }" :aria-current="billingTab === tab.key ? 'page' : undefined">{{ tab.label }}</Link>
                </nav>
                <section v-if="billingTab === 'subscription'" class="detail-card">
                    <h2 class="text-xl font-semibold">Subscription</h2>
                    <p class="mb-6 mt-2">Paket aktif pada workspace milik Anda, termasuk paket dibayar yang menunggu Publish.</p>
                    <div v-if="subscriptions?.data?.length" class="space-y-4">
                        <article v-for="item in subscriptions.data" :key="item.id" class="rounded-xl border border-gray-200 p-5 dark:border-gray-700">
                            <div class="flex flex-wrap items-center justify-between gap-3"><h3>Wedding {{ item.package }}</h3><span class="console-table-status">{{ statusLabels[item.status] }}</span></div>
                            <p>{{ item.workspace }} · {{ item.months }} bulan · sekali bayar</p>
                            <p v-if="item.starts_at">{{ dateTime(item.starts_at) }} — {{ dateTime(item.ends_at) }}</p>
                            <p v-else>Masa aktif dimulai saat Publish pertama.</p>
                            <Link v-if="$page.props.developmentPayments" :href="route('wedding.edit', item.tenant_id)" class="button button-outline mt-4">Kelola undangan</Link>
                        </article>
                        <nav v-if="subscriptions.last_page > 1" class="flex flex-wrap items-center gap-4" aria-label="Halaman paket aktif">
                            <Link v-if="subscriptions.prev_page_url" :href="subscriptions.prev_page_url" class="button button-outline">Sebelumnya</Link><span>{{ subscriptions.current_page }} / {{ subscriptions.last_page }}</span><Link v-if="subscriptions.next_page_url" :href="subscriptions.next_page_url" class="button button-outline">Berikutnya</Link>
                        </nav>
                    </div>
                    <div v-else class="py-6"><h3>Belum ada paket aktif</h3><p>Paket muncul setelah pembayaran diterima. Pesanan belum dibayar atau kedaluwarsa dapat dilihat di Payment History.</p><Link :href="route('products.plans', 'wedding')" class="button button-outline mt-4">Lihat paket Wedding</Link></div>
                </section>
                <section v-else-if="billingTab === 'method'" class="detail-card">
                    <h2 class="text-xl font-semibold">Payment Method</h2>
                    <div class="mt-6 rounded-xl border border-gray-200 p-5 dark:border-gray-700"><strong>Manual payment</strong></div>
                </section>
                <section v-else class="detail-card">
                <h2 class="text-xl font-semibold">Payment History</h2>
                <p class="mb-6 mt-2">Riwayat pesanan dan pembayaran produk pada workspace milik Anda.</p>
                <div v-if="billingHistory?.data?.length" class="console-table-wrap">
                    <table class="console-table">
                        <thead><tr><th>ID pesanan</th><th>Service</th><th>Workspace</th><th>Paid at</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody><tr v-for="order in billingHistory.data" :key="order.id"><td>#{{ order.id }}</td><td>Wedding {{ order.package }}</td><td>{{ order.workspace }}</td><td>{{ order.paid_at ? dateTime(order.paid_at) : 'Belum dibayar' }}</td><td>{{ rupiah(order.amount) }}</td><td><span class="console-table-status">{{ statusLabels[order.status] ?? order.status }}</span></td></tr></tbody>
                    </table>
                    <nav v-if="billingHistory.last_page > 1" class="mt-5 flex items-center justify-between" aria-label="Halaman riwayat pembayaran">
                        <Link v-if="billingHistory.prev_page_url" :href="billingHistory.prev_page_url" class="button button-outline">Sebelumnya</Link><span>{{ billingHistory.current_page }} / {{ billingHistory.last_page }}</span><Link v-if="billingHistory.next_page_url" :href="billingHistory.next_page_url" class="button button-outline">Berikutnya</Link>
                    </nav>
                </div>
                <div v-else class="py-8"><h3>Belum ada riwayat pembayaran</h3><p>Pesanan produk akan tampil di sini setelah Anda membuatnya melalui workspace.</p><Link :href="route('dashboard')" class="button button-outline mt-5">Buka workspace</Link></div>
                </section>
            </div>
            <section v-else-if="section === 'activity'" class="detail-card">
                <h2 class="text-xl font-semibold">Account activity</h2><p class="mt-3">Riwayat login dan aktivitas keamanan akun belum tersedia. Aktivitas workspace dan draft paket yang sudah didukung dapat dilihat melalui lonceng notifikasi.</p>
            </section>
            <section v-else class="detail-card">
                <h2 class="text-xl font-semibold">Notification settings</h2><p class="mt-3">Notifikasi dalam aplikasi tersedia melalui lonceng di kanan atas. Anda dapat membaca dan menandai notifikasi sebagai sudah dibaca.</p><p class="mt-3">Pengaturan kanal email, WhatsApp, dan preferensi pengiriman belum tersedia.</p>
            </section>
        </div>
    </MoshiaLayout>
</template>
