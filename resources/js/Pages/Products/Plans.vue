<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import ShellIcon from '@/Components/ShellIcon.vue';
import { rupiah } from '@/billing';
defineProps({ product: Object, packages: Array, workspace: Object, canPurchase: Boolean });
</script>
<template>
    <Head :title="product.title" />
    <MoshiaLayout>
        <template #header><h2>{{ product.title }}</h2></template>
        <div class="shell account-main product-plans-page">
            <div class="plans-page-heading"><div><span class="section-kicker">MOSHIA PRODUCTS</span><h1>{{ product.title }}</h1><p>{{ product.summary }}</p></div><span class="plans-page-label">Satu akun, seluruh produk</span></div>
            <section v-if="packages.length" class="plans-comparison" aria-label="Perbandingan paket">
                <div class="plans-toolbar"><span class="plans-category"><ShellIcon name="grid" />Paket Wedding Invitation</span><span>Pembayaran <strong>sekali bayar</strong></span></div>
                <div class="plans-workspace"><span v-if="workspace">Workspace: <strong>{{ workspace.name }}</strong></span><span v-else>Pilih atau buat workspace untuk melanjutkan.</span><Link :href="route('dashboard')">{{ workspace ? 'Ganti workspace' : 'Buat workspace' }} →</Link></div>
                <div class="plans-grid">
                    <article v-for="plan in packages" :key="plan.key" class="plan-column" :class="{ featured: plan.key === 'diamond' }">
                        <div class="plan-name"><div><h2>{{ plan.label }}</h2><p>{{ plan.custom_domain ? 'Undangan dengan identitas domain sendiri' : plan.video_header_request ? 'Lengkapi cerita dengan video header' : 'Mulai undangan digital Anda' }}</p></div><span v-if="plan.custom_domain" class="plan-badge">Domain .com</span></div>
                        <div class="plan-price">{{ rupiah(plan.price_amount) }}<span>/ undangan</span></div>
                        <p class="plan-duration">{{ plan.validity_months }} bulan sejak publish pertama</p>
                        <Link v-if="canPurchase" :href="route('billing.index', { tenant: workspace.id, package: plan.key })" class="plan-select">Pilih {{ plan.label }}</Link>
                        <Link v-else-if="!workspace" :href="route('dashboard')" class="plan-select">Siapkan workspace</Link>
                        <span v-else class="plan-select unavailable">Pembelian belum tersedia</span>
                        <p class="plan-price-note">Satu workspace untuk satu undangan.<br />Masa aktif dimulai saat undangan dipublish.</p>
                        <ul class="plan-features">
                            <li><span>✓</span>1 undangan per workspace</li>
                            <li><span>✓</span>Akses template standard</li>
                            <li><span>✓</span>Masa aktif {{ plan.validity_months }} bulan</li>
                            <li v-if="plan.video_header_request"><span>✓</span>Request video header invitation</li>
                            <li v-if="plan.custom_domain"><span>✓</span>Pembelian custom domain .com</li>
                            <li><span>✓</span>Data tetap tersimpan setelah masa aktif habis</li>
                        </ul>
                        <div class="plan-footer"><ShellIcon :name="plan.custom_domain ? 'box' : 'card'" /><span>{{ plan.custom_domain ? 'Domain diperiksa dan diproses manual' : 'Sekali bayar · tanpa tagihan otomatis' }}</span></div>
                    </article>
                </div>
                <div class="plans-notice"><strong>Informasi paket</strong><p>Undangan tambahan memerlukan workspace dan pembelian paket tersendiri. Untuk mengaktifkan kembali undangan yang kedaluwarsa, lakukan pembayaran kembali.</p><p>Domain Diamond hanya .com, diproses tim setelah pemeriksaan ketersediaan dan persetujuan Anda. Ketentuan domain premium belum ditetapkan.</p><p v-if="$page.props.developmentPayments">Saat ini alur pembayaran masih simulasi development. Template, video header dan domain merupakan cakupan paket; fitur lengkapnya sedang dikembangkan.</p><p v-else>Pembelian belum dibuka. Fitur lengkap paket sedang dipersiapkan.</p></div>
            </section>
            <section v-else class="product-coming-soon"><div class="coming-soon-icon"><ShellIcon name="box" /></div><span class="section-kicker">{{ product.title }}</span><h2>Product Coming Soon</h2><p>Daftar paket {{ product.title }} sedang dipersiapkan.<br />Anda dapat melihat produk Moshia lainnya melalui menu di samping.</p><Link :href="route('dashboard')" class="button button-outline">Kembali ke dashboard</Link></section>
        </div>
    </MoshiaLayout>
</template>
