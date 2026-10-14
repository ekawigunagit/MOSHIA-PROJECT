<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import MoshiaLoading from '@/Components/MoshiaLoading.vue';

const dialog = ref(null);
const label = ref('Memuat halaman…');
const visits = new Set();
let stopStart;
let stopFinish;

onMounted(() => {
    stopStart = router.on('start', event => {
        visits.add(event.detail.visit);
        label.value = event.detail.visit.method === 'get' ? 'Memuat halaman…' : 'Memproses…';
        if (!dialog.value.open) dialog.value.showModal();
    });
    stopFinish = router.on('finish', event => {
        visits.delete(event.detail.visit);
        if (!visits.size) dialog.value?.close();
    });
});
onUnmounted(() => {
    stopStart?.();
    stopFinish?.();
    visits.clear();
    dialog.value?.close();
});
</script>

<template>
    <Teleport to="body">
        <dialog ref="dialog" class="moshia-global-loading" aria-label="Mohon tunggu" aria-busy="true" @cancel.prevent>
            <MoshiaLoading :label="label" />
        </dialog>
    </Teleport>
</template>

<style scoped>
.moshia-global-loading { position: fixed; inset: 0; margin: auto; padding: 0 24px; width: min(260px, calc(100vw - 32px)); border: 1px solid #e3e3e9; border-radius: 20px; background: #fff; color: #25252c; box-shadow: 0 16px 60px #17171c20; }
.moshia-global-loading::backdrop { background: rgb(20 20 25 / 35%); }
:global(html.dark) .moshia-global-loading { background: #1d1d24; border-color: #34343f; color: #f0f0f4; }
</style>
