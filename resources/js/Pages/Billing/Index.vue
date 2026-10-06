<script setup>
import { computed, nextTick } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import InputError from '@/Components/InputError.vue';
import PaymentCountdown from '@/Components/PaymentCountdown.vue';
import { usePaymentCountdown } from '@/Composables/usePaymentCountdown';
import { statusLabels, rupiah, dateTime } from '@/billing';

const props = defineProps({ workspace: Object, packages: Array, bank: Object, orders: Object, serverNow: String });
const liveOrders = usePaymentCountdown(() => props.orders.data, () => props.serverNow);
const form = useForm({ package: '' });
const cancellation = useForm({});
const selectedPackage = computed(() => props.packages.find((plan) => plan.key === form.package));
const hasPendingOrder = computed(() => liveOrders.value.some((order) => order.status === 'pending_payment'));
function purchase() {
    if (!selectedPackage.value || form.processing) return;
    form.post(route('billing.store', props.workspace.id), {
        preserveScroll: true,
        onSuccess: async () => {
            await nextTick();
            document.getElementById('payment-details')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
    });
}
function cancel(order) {
    cancellation.post(route('billing.cancel', [props.workspace.id, order.id]), { preserveScroll: true });
}
</script>

<template>
    <Head title="Paket & Pembayaran Wedding" />
    <MoshiaLayout>
        <template #header><h2 class="text-xl font-semibold">Paket &amp; Pembayaran</h2></template>
        <div class="shell account-main space-y-6">
            <section class="detail-card">
                <span class="section-kicker">MOSHIA WEDDING</span>
                <h1 class="mt-4">Undangan untuk {{ workspace.name }}</h1>
                <p>Satu workspace untuk satu undangan. Pembayaran diterima admin terlebih dahulu; masa aktif dimulai ketika Anda menekan Publish.</p>
                <Link :href="route('dashboard')" class="mt-4 inline-block underline">Kembali ke workspace</Link>
            </section>
            <form class="space-y-6" @submit.prevent="purchase">
            <fieldset :disabled="form.processing">
                <legend class="mb-5 text-2xl font-semibold">Pilih paket undangan Anda</legend>
            <div class="grid gap-4 lg:grid-cols-3">
                <label v-for="plan in packages" :key="plan.key" class="detail-card relative flex cursor-pointer flex-col transition-shadow focus-within:ring-2 focus-within:ring-red-500"
                    :class="form.package === plan.key ? 'ring-2 ring-red-500' : ''">
                    <div class="mb-5 flex items-center justify-between gap-4">
                    <span class="section-kicker">{{ plan.label }}</span>
                    <input v-model="form.package" type="radio" name="wedding-package" :value="plan.key" :aria-label="`Pilih paket ${plan.label}`" required class="h-5 w-5 border-gray-400 text-red-600 focus:ring-red-500" />
                    </div>
                    <h3 class="mt-4">{{ rupiah(plan.price_amount) }}</h3>
                    <p>Sekali bayar · {{ plan.validity_months }} bulan sejak Publish</p>
                    <ul class="my-6 space-y-3 text-sm">
                        <li>Satu undangan dalam satu workspace</li>
                        <li>Akses template standard</li>
                        <li>Edit konten dan preview privat</li>
                        <li v-if="plan.video_header_request">Termasuk request video header</li>
                        <li v-if="plan.domain_purchase_included">Termasuk pembelian domain .com melalui tim</li>
                    </ul>
                    <span class="mt-auto pt-4 text-sm font-semibold" :class="form.package === plan.key ? 'text-red-600 dark:text-red-400' : ''">{{ form.package === plan.key ? 'Paket dipilih' : `Pilih ${plan.label}` }}</span>
                </label>
            </div>
            </fieldset>
            <p class="text-sm">Uji coba ini menyediakan editor teks dan satu tampilan undangan dasar. Pemrosesan video header dan domain menyusul.</p>
            <section class="detail-card flex flex-wrap items-center justify-between gap-5">
                <div aria-live="polite">
                    <h3>{{ selectedPackage ? `Paket ${selectedPackage.label}` : 'Pilih salah satu paket' }}</h3>
                    <p v-if="selectedPackage">{{ rupiah(selectedPackage.price_amount) }} · {{ selectedPackage.validity_months }} bulan sejak Publish</p>
                    <p v-else>Pilih Gold, Emerald, atau Diamond untuk melanjutkan pembayaran.</p>
                    <InputError :message="form.errors.package || Object.values(form.errors)[0]" />
                </div>
                <button type="submit" class="button button-primary" :disabled="!selectedPackage || form.processing">{{ form.processing ? 'Memproses...' : 'Make payment' }}</button>
            </section>
            </form>
            <section v-if="hasPendingOrder" id="payment-details" class="detail-card scroll-mt-24">
                <h2 class="text-2xl font-semibold">Detail pembayaran</h2>
                <p class="mt-4 font-semibold">Mode development · rekening dummy. Jangan transfer uang sungguhan.</p>
                <div class="mt-4 rounded-xl border border-red-300 p-4 dark:border-red-900">
                    <p class="font-semibold">{{ bank.bank }} · {{ bank.account_number }}</p>
                    <p>Atas nama {{ bank.account_name }}</p>
                </div>
                <p>Pesanan menunggu verifikasi admin. Lihat rincian pesanan di bawah.</p>
            </section>
            <h2 class="text-2xl font-semibold">Riwayat pesanan</h2>
            <InputError :message="cancellation.errors.payment" />
            <p v-if="!orders.data.length" class="detail-card">Belum ada pesanan. Pilih paket untuk mulai.</p>
            <article v-for="order in liveOrders" :key="order.id" class="detail-card">
                <div class="detail-top"><span>Pesanan #{{ order.id }}</span><span>{{ statusLabels[order.status] }}</span></div>
                <h3>{{ order.terms.label }} · {{ rupiah(order.terms.price_amount) }}</h3>
                <p>Dibuat {{ dateTime(order.created_at) }}</p>
                <PaymentCountdown :order="order" />
                <p v-if="order.paid_at">Pembayaran diterima {{ dateTime(order.paid_at) }}</p>
                <p>Masa aktif: {{ dateTime(order.first_published_at) }}<template v-if="order.ends_at"> — {{ dateTime(order.ends_at) }}</template></p>
                <p v-if="order.status === 'pending_payment'">Admin akan mencocokkan pesanan dan menerima pembayaran secara manual.</p>
                <p v-if="order.status === 'expired'">Data undangan tetap tersimpan. Pilih paket kembali, lalu Publish setelah pembayaran diterima.</p>
                <div class="dashboard-actions">
                    <Link v-if="['awaiting_publish', 'active'].includes(order.status)" :href="route('wedding.edit', workspace.id)" class="button button-primary">Buka editor Wedding</Link>
                    <button v-if="order.status === 'pending_payment'" class="button button-outline" :disabled="cancellation.processing" @click="cancel(order)">Batalkan pesanan</button>
                </div>
            </article>
            <nav v-if="orders.last_page > 1" class="flex flex-wrap items-center gap-4" aria-label="Halaman pesanan">
                <Link v-if="orders.prev_page_url" :href="orders.prev_page_url" class="button button-outline">Sebelumnya</Link>
                <span>{{ orders.current_page }} / {{ orders.last_page }}</span>
                <Link v-if="orders.next_page_url" :href="orders.next_page_url" class="button button-outline">Berikutnya</Link>
            </nav>
        </div>
    </MoshiaLayout>
</template>
