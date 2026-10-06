<script setup>
import { dateTime } from '@/billing';
defineProps({ order: { type: Object, required: true } });
</script>

<template>
    <div v-if="order.status === 'pending_payment'" class="my-5 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/20">
        <p class="text-sm">Sisa waktu pembayaran</p>
        <div role="timer" aria-label="Sisa waktu pembayaran" class="mt-2 font-mono text-3xl font-semibold tabular-nums tracking-wider text-red-600 dark:text-red-400">{{ order.countdown }}</div>
        <p class="mt-2 text-sm">Batas pembayaran: {{ dateTime(order.payment_expires_at) }}</p>
        <p class="text-sm">Pembayaran harus diterima admin dalam 24 jam sejak pesanan dibuat.</p>
    </div>
    <p v-else-if="order.status === 'payment_expired'" role="status" class="my-5 text-red-600 dark:text-red-400">Waktu pembayaran habis. Pesanan kedaluwarsa; silakan buat pesanan baru.</p>
</template>
