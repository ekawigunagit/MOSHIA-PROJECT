<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MoshiaIcon from '@/Components/MoshiaIcon.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>
<template><Head title="Dashboard"/><AuthenticatedLayout><template #header><h2 class="text-xl font-semibold">Dashboard</h2></template><div class="shell account-main"><section class="detail-card dashboard-intro"><span class="section-kicker">WELCOME TO YOUR MOSHIA</span><h1 class="mt-5">Halo, <span>{{ $page.props.auth.user.name }}.</span></h1><p>Anda sudah terhubung dengan Moshia. Kelola akun Anda atau jelajahi ekosistem untuk menemukan kemungkinan berikutnya.</p><div class="dashboard-actions"><Link href="/#ecosystem" class="button button-primary">Jelajahi ekosistem <MoshiaIcon/></Link><Link :href="route('profile.edit')" class="button button-outline">Kelola profil <MoshiaIcon/></Link></div></section></div></AuthenticatedLayout></template>
