<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import InputError from '@/Components/InputError.vue';
import PaymentCountdown from '@/Components/PaymentCountdown.vue';
import { usePaymentCountdown } from '@/Composables/usePaymentCountdown';
import { statusLabels, rupiah, dateTime } from '@/billing';

const props = defineProps({ orders: Object, bank: Object, serverNow: String });
const liveOrders = usePaymentCountdown(() => props.orders.data, () => props.serverNow);
const form = useForm({});
const confirming = ref(null);
function accept(order) {
    form.post(route('admin.payments.accept', order.id), {
        preserveScroll: true, onSuccess: () => { confirming.value = null; },
    });
}
</script>

<template>
    <Head title="Pembayaran Manual" />
    <MoshiaLayout>
        <template #header><h2 class="text-xl font-semibold">Pembayaran Manual</h2></template>
        <div class="shell account-main space-y-6">
            <section class="detail-card">
                <span class="section-kicker">MOSHIA BILLING</span>
                <h1 class="mt-4">Verifikasi pembayaran</h1>
                <p>Mode development · {{ bank.bank }} {{ bank.account_number }} · {{ bank.account_name }}.</p>
                <p>Rekening dummy untuk simulasi. Payment accepted membuka editor; masa aktif baru dimulai saat pelanggan menekan Publish.</p>
            </section>
            <InputError :message="form.errors.payment" />
            <p v-if="!orders.data.length" class="detail-card">Belum ada pesanan pembayaran.</p>
            <article v-for="order in liveOrders" :key="order.id" class="detail-card">
                <div class="detail-top"><span>Pesanan #{{ order.id }}</span><span>{{ statusLabels[order.status] }}</span></div>
                <h3>{{ order.workspace.name }} · {{ order.terms.label }}</h3>
                <p>{{ rupiah(order.terms.price_amount) }} · {{ order.terms.validity_months }} bulan</p>
                <p>Dibuat {{ dateTime(order.created_at) }}</p>
                <PaymentCountdown :order="order" />
                <p v-if="order.paid_at">Diterima {{ dateTime(order.paid_at) }} oleh {{ order.acceptedBy?.name || 'Admin terdahulu' }}</p>
                <p v-if="order.ends_at">Berakhir {{ dateTime(order.ends_at) }}</p>
                <div v-if="order.status === 'pending_payment'" class="mt-5">
                    <button v-if="confirming !== order.id" class="button button-primary" :disabled="form.processing" @click="confirming = order.id">Payment accepted</button>
                    <div v-else class="space-y-3" role="group" :aria-label="`Konfirmasi pesanan ${order.id}`">
                        <p>Terima pembayaran pesanan #{{ order.id }} sejumlah {{ rupiah(order.terms.price_amount) }} untuk {{ order.workspace.name }}?</p>
                        <div class="flex flex-wrap gap-3">
                            <button class="button button-primary" :disabled="form.processing" @click="accept(order)">Ya, payment accepted</button>
                            <button class="button button-outline" :disabled="form.processing" @click="confirming = null">Kembali</button>
                        </div>
                    </div>
                </div>
            </article>
            <nav v-if="orders.last_page > 1" class="flex flex-wrap items-center gap-4" aria-label="Halaman pembayaran">
                <Link v-if="orders.prev_page_url" :href="orders.prev_page_url" class="button button-outline">Sebelumnya</Link>
                <span>{{ orders.current_page }} / {{ orders.last_page }}</span>
                <Link v-if="orders.next_page_url" :href="orders.next_page_url" class="button button-outline">Berikutnya</Link>
            </nav>
        </div>
    </MoshiaLayout>
</template>
