<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import InputError from '@/Components/InputError.vue';
defineProps({ banks: Array });
const form = useForm({ name: '' });
function submit() { form.post(route('admin.banks.store'), { onSuccess: () => form.reset() }); }
</script>
<template>
<Head title="Bank Wedding Gift" /><MoshiaLayout><template #header><h2>Bank Wedding Gift</h2></template>
<div class="shell account-main"><section class="detail-card"><h1>Daftar bank</h1><p class="studio-help">Pilihan bank untuk rekening hadiah pada undangan. Bukan rekening pembayaran paket Moshia.</p>
<form @submit.prevent="submit" class="mt-6"><label for="bank-name" class="moshia-label">Nama bank</label><input id="bank-name" v-model="form.name" maxlength="100" required class="moshia-input w-full" placeholder="Bank lainnya" /><InputError :message="form.errors.name" /><button class="button button-primary mt-4" :disabled="form.processing">Tambah bank</button></form>
<ul class="mt-6"><li v-for="bank in banks" :key="bank.id" class="py-3 border-b">{{ bank.name }}</li></ul>
</section></div></MoshiaLayout></template>
