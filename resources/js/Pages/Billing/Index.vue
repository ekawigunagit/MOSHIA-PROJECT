<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import InputError from '@/Components/InputError.vue';
import PaymentCountdown from '@/Components/PaymentCountdown.vue';
import MoshiaLoading from '@/Components/MoshiaLoading.vue';
import { usePaymentCountdown } from '@/Composables/usePaymentCountdown';
import { statusLabels, rupiah, dateTime } from '@/billing';

const props = defineProps({ workspace: Object, packages: Array, bank: Object, orders: Object, serverNow: String });
const liveOrders = usePaymentCountdown(() => props.orders.data, () => props.serverNow);
const page = usePage();
const requestedPackage = computed(() => new URL(page.url, 'https://moshia.local').searchParams.get('package'));
const form = useForm({ package: '' });
const cancellation = useForm({});
const selectedPackage = computed(() => props.packages.find((plan) => plan.key === requestedPackage.value));
const cartDialog = ref(null);
let previousOverflow;
function syncCart() {
    if (selectedPackage.value && !cartDialog.value?.open) {
        previousOverflow = document.body.style.overflow;
        cartDialog.value?.showModal();
        document.body.style.overflow = 'hidden';
    } else if (!selectedPackage.value) {
        cartDialog.value?.close();
        restoreScroll();
    }
}
function restoreScroll() {
    if (previousOverflow !== undefined) {
        document.body.style.overflow = previousOverflow;
        previousOverflow = undefined;
    }
}
function closeCart() {
    if (form.processing) return;
    cartDialog.value?.close();
    restoreScroll();
    router.get(route('billing.index', props.workspace.id), {}, { preserveScroll: true, replace: true });
}
onMounted(syncCart);
watch(selectedPackage, syncCart, { flush: 'post' });
onUnmounted(restoreScroll);
const hasPendingOrder = computed(() => liveOrders.value.some((order) => order.status === 'pending_payment'));
function purchase() {
    if (!selectedPackage.value || form.processing) return;
    form.package = selectedPackage.value.key;
    form.post(route('billing.store', props.workspace.id), {
        preserveScroll: true,
        onSuccess: async () => {
            await nextTick();
            document.getElementById('order-history')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
    });
}
function cancel(order) {
    cancellation.post(route('billing.cancel', [props.workspace.id, order.id]), { preserveScroll: true });
}
</script>

<template>
    <Head title="Keranjang & Pesanan Wedding" />
    <MoshiaLayout>
        <template #header><h2 class="text-xl font-semibold">Keranjang &amp; Pesanan</h2></template>
        <div class="shell account-main space-y-6">
            <section class="detail-card">
                <span class="section-kicker">MOSHIA WEDDING</span>
                <h1 class="mt-4">{{ selectedPackage ? 'Ringkasan pesanan' : 'Pesanan Wedding Anda' }}</h1>
                <p>Workspace: {{ workspace.name }}</p>
                <p>Satu workspace untuk satu undangan. Pembayaran diterima admin terlebih dahulu; masa aktif dimulai ketika Anda menekan Publish.</p>
                <Link :href="route('dashboard')" class="mt-4 inline-block underline">Kembali ke workspace</Link>
            </section>
            <dialog ref="cartDialog" class="cart-dialog" aria-labelledby="cart-title" @cancel.prevent="closeCart" @click="($event.target === cartDialog) && closeCart()">
            <form v-if="selectedPackage" class="space-y-6 p-5 sm:p-8" :aria-busy="form.processing" @submit.prevent="purchase">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 id="cart-title" class="text-2xl font-semibold">Keranjang Anda</h2>
                    <button type="button" autofocus aria-label="Tutup keranjang" class="console-cart-close" :disabled="form.processing" @click="closeCart">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg>
                    </button>
                </div>
                <MoshiaLoading v-if="form.processing" />
                <div v-show="!form.processing" class="space-y-6">
                <Link :href="route('products.plans', 'wedding')" class="inline-block text-sm underline">Ganti paket</Link>
                <section class="rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-900 dark:bg-red-950/20" aria-label="Paket pilihan">
                    <div class="flex flex-wrap justify-between gap-3">
                        <strong>Wedding Invitation · {{ selectedPackage.label }}</strong>
                        <strong class="whitespace-nowrap">{{ rupiah(selectedPackage.price_amount) }}</strong>
                    </div>
                    <p class="mt-3">1 undangan · {{ selectedPackage.validity_months }} bulan sejak Publish</p>
                    <p>Sekali bayar, tanpa perpanjangan otomatis.</p>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li>Template standard, editor konten, dan preview privat</li>
                        <li v-if="selectedPackage.video_header_request">Request video header invitation</li>
                        <li v-if="selectedPackage.domain_purchase_included">Pembelian domain .com melalui tim</li>
                    </ul>
                </section>
                <section aria-label="Metode pembayaran">
                    <h3>Metode pembayaran</h3>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                        <div><strong>Transfer bank {{ bank.bank }}</strong><p>{{ bank.account_number }} · {{ bank.account_name }}</p></div>
                        <span class="text-sm">Verifikasi manual</span>
                    </div>
                    <p class="mt-3 text-sm">Rekening dummy untuk simulasi. Jangan transfer uang sungguhan.</p>
                </section>
                <section class="rounded-2xl bg-gray-100 p-5 dark:bg-white/5" aria-label="Total pesanan">
                    <dl class="space-y-4">
                        <div class="flex justify-between gap-4"><dt>Wedding {{ selectedPackage.label }} × 1</dt><dd class="whitespace-nowrap">{{ rupiah(selectedPackage.price_amount) }}</dd></div>
                        <div class="flex justify-between gap-4 border-t border-gray-200 pt-4 text-xl font-semibold dark:border-gray-700"><dt>Total</dt><dd class="whitespace-nowrap">{{ rupiah(selectedPackage.price_amount) }}</dd></div>
                    </dl>
                    <p class="mt-4 text-sm">Make payment membuat pesanan dengan batas pembayaran 24 jam. Masa aktif undangan dimulai setelah pembayaran diterima dan Anda menekan Publish.</p>
                </section>
                <InputError :message="form.errors.package || Object.values(form.errors)[0]" />
                <div class="flex flex-wrap items-center justify-end gap-4">
                    <button type="button" class="button button-outline" :disabled="form.processing" @click="closeCart">Batal</button>
                    <button type="submit" class="button button-primary" :disabled="form.processing">{{ form.processing ? 'Memproses...' : 'Make payment' }}</button>
                </div>
                </div>
            </form>
            </dialog>
            <section v-if="!selectedPackage" class="detail-card flex flex-wrap items-center justify-between gap-4">
                <p>{{ requestedPackage ? 'Paket tidak ditemukan. Silakan pilih paket Wedding yang tersedia.' : 'Lihat status pembayaran dan riwayat pesanan di bawah, atau pilih paket untuk pesanan baru.' }}</p>
                <Link :href="route('products.plans', 'wedding')" class="button button-outline">Lihat paket Wedding</Link>
            </section>
            <section v-if="hasPendingOrder" id="payment-details" class="detail-card scroll-mt-24">
                <h2 class="text-2xl font-semibold">Detail pembayaran</h2>
                <p class="mt-4 font-semibold">Mode development · rekening dummy. Jangan transfer uang sungguhan.</p>
                <div class="mt-4 rounded-xl border border-red-300 p-4 dark:border-red-900">
                    <p class="font-semibold">{{ bank.bank }} · {{ bank.account_number }}</p>
                    <p>Atas nama {{ bank.account_name }}</p>
                </div>
                <p>Pesanan menunggu verifikasi admin. Lihat rincian pesanan di bawah.</p>
            </section>
            <h2 id="order-history" class="scroll-mt-24 text-2xl font-semibold">Riwayat pesanan</h2>
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

<style scoped>
.cart-dialog {
    width: min(48rem, calc(100vw - 2rem));
    max-height: calc(100dvh - 2rem);
    margin: auto;
    padding: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
    border: 1px solid #e5e7eb;
    border-radius: 1.5rem;
    background: #fff;
    color: #111827;
    box-shadow: 0 24px 80px rgb(0 0 0 / 25%);
}
.cart-dialog::backdrop { background: rgb(0 0 0 / 55%); }
:global(.dark) .cart-dialog { background: #111113; color: #f3f4f6; border-color: #374151; }
</style>
