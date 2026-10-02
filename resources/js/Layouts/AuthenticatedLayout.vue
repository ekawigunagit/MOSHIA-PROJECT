<script setup>
import {
    Link
} from '@inertiajs/vue3';
import ThemeToggle from '@/Components/ThemeToggle.vue';
</script>
<template>
<div class="account-page">
    <header class="site-header account-header shell">
        <Link href="/" class="brand"><img src="/images/moshia-mark.svg" width="35" height="35" alt=""><span>MOSHIA<span class="brand-dot">.</span></span></Link>
        <nav class="account-links" aria-label="Navigasi akun">
            <Link :href="route('dashboard')" :class="{ active: route().current('dashboard') }">Dashboard</Link>
            <Link :href="route('profile.edit')" :class="{ active: route().current('profile.edit') }">Profil</Link><span class="account-user">{{ $page.props.auth.user.name }}</span>
            <ThemeToggle />
            <Link :href="route('logout')" method="post" as="button" class="button button-outline header-cta">Keluar</Link>
        </nav>
    </header>
    <div v-if="$slots.header" class="account-title shell">
        <slot name="header" />
    </div>
    <main>
        <slot />
    </main>
</div>
</template>
