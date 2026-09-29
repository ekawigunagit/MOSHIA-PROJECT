<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/ThemeToggle.vue';
const page = usePage();
const content = computed(() => ({
 'Auth/Login': ['Selamat datang kembali.', 'Masuk dan lanjutkan perjalanan Anda bersama Moshia.'],
 'Auth/Register': ['Mulai perjalanan Anda.', 'Buat akun dan jadilah bagian dari ekosistem Moshia.'],
 'Auth/ForgotPassword': ['Lupa kata sandi?', 'Masukkan email Anda untuk menerima tautan pengaturan ulang.'],
 'Auth/ResetPassword': ['Langkah baru, akses baru.', 'Buat kata sandi baru untuk akun Moshia Anda.'],
 'Auth/ConfirmPassword': ['Konfirmasi kata sandi.', 'Konfirmasikan identitas Anda untuk melanjutkan.'],
 'Auth/VerifyEmail': ['Verifikasi email Anda.', 'Satu langkah lagi untuk menyiapkan akun Moshia Anda.']
}[page.component] || ['Akun Moshia', 'Terhubung dalam satu ekosistem.']));
</script>
<template>
 <div class="auth-page"><header class="site-header shell"><Link href="/" class="brand"><img src="/images/moshia-mark.svg" width="35" height="35" alt=""><span>MOSHIA<span class="brand-dot">.</span></span></Link><ThemeToggle/></header>
 <main class="auth-grid shell"><section class="auth-story"><span class="section-kicker">ONE ECOSYSTEM. ENDLESS POSSIBILITIES.</span><h1 class="mt-6">Better Together.<br>Beyond <span>Tomorrow.</span></h1><p>Menghubungkan ide, teknologi, dan peluang. Langkah besar berikutnya dimulai bersama Moshia.</p><img src="/images/moshia-bot.svg" alt="Robot Moshia" width="250" height="265"></section><section class="auth-card"><span class="section-kicker">YOUR MOSHIA ACCOUNT</span><h2>{{ content[0] }}</h2><p class="auth-description">{{ content[1] }}</p><slot/><p v-if="page.component === 'Auth/Login'" class="auth-switch">Belum punya akun? <Link :href="route('register')">Daftar sekarang</Link></p><p v-if="page.component === 'Auth/ForgotPassword'" class="auth-switch"><Link :href="route('login')">Kembali ke halaman masuk</Link></p></section></main></div>
</template>
