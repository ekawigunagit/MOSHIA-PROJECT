<script setup>
defineProps({ label: { type: String, default: 'Memproses pesanan…' } });
</script>

<template>
    <div class="flex min-h-48 flex-col items-center justify-center gap-4 py-8" role="status" aria-live="polite">
        <div class="moshia-loading-mark" aria-hidden="true">
            <span class="moshia-loading-ring" />
            <img src="/images/moshia-mark.svg" width="28" height="28" alt="" />
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-300">{{ label }}</p>
    </div>
</template>

<style scoped>
.moshia-loading-mark { position: relative; display: grid; place-items: center; width: 64px; height: 64px; }
.moshia-loading-ring { position: absolute; inset: 0; border: 2px solid rgb(240 48 58 / 15%); border-top-color: #f0303a; border-right-color: #f0303a; border-radius: 50%; animation: moshia-loading-spin 1s linear infinite; }
@keyframes moshia-loading-spin { to { transform: rotate(360deg); } }
@media (prefers-reduced-motion: reduce) { .moshia-loading-ring { animation: none; } }
</style>
