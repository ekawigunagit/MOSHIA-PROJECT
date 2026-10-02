<script setup>
import axios from 'axios';
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const pageProps = usePage();
const root = ref(null);
const trigger = ref(null);
const panel = ref(null);
const open = ref(false);
const items = ref([]);
const unread = ref(0);
const page = ref(1);
const lastPage = ref(1);
const loading = ref(false);
const saving = ref(false);
const error = ref('');
let controller;
let refreshTimer;
let stopNavigation;
let alive = true;

async function load(target = page.value) {
    controller?.abort();
    const request = new AbortController();
    controller = request;
    loading.value = true;
    error.value = '';
    try {
        const { data } = await axios.get(route('notifications.index'), {
            params: { page: target }, signal: request.signal,
        });
        if (!alive || request.signal.aborted) return;
        items.value = data.items;
        unread.value = data.unread_count;
        page.value = data.page;
        lastPage.value = data.last_page;
    } catch (exception) {
        if (!axios.isCancel(exception) && alive) {
            error.value = 'Notifikasi belum dapat dimuat. Silakan coba lagi.';
        }
    } finally {
        if (controller === request && alive) loading.value = false;
    }
}

async function markRead(id = null) {
    if (saving.value) return;
    saving.value = true;
    error.value = '';
    try {
        await axios.patch(id ? route('notifications.read', id) : route('notifications.read-all'));
        if (alive) await load();
    } catch {
        if (alive) error.value = 'Gagal menandai notifikasi. Silakan coba lagi.';
    } finally {
        if (alive) saving.value = false;
    }
}

async function toggle() {
    open.value = !open.value;
    if (open.value) {
        load(1);
        await nextTick();
        panel.value?.focus();
    }
}
function close(restoreFocus = false) {
    open.value = false;
    if (restoreFocus) trigger.value?.focus();
}
function outside(event) {
    if (!root.value?.contains(event.target)) close();
}
function escape(event) {
    if (event.key === 'Escape' && open.value) { event.preventDefault(); close(true); }
}
function focusOutside(event) {
    if (open.value && !root.value?.contains(event.target)) close();
}
function refresh() {
    if (!document.hidden && !loading.value && !saving.value) load(open.value ? page.value : 1);
}
function formatDate(value) {
    if (!value) return '';
    return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value));
}

watch(() => pageProps.props.auth?.user?.id, () => {
    controller?.abort();
    items.value = [];
    unread.value = 0;
    close();
    if (pageProps.props.auth?.user) load(1);
});
onMounted(() => {
    load(1);
    refreshTimer = window.setInterval(refresh, 60000);
    stopNavigation = router.on('finish', () => { close(); refresh(); });
    document.addEventListener('click', outside);
    document.addEventListener('keydown', escape);
    document.addEventListener('focusin', focusOutside);
    document.addEventListener('visibilitychange', refresh);
});
onUnmounted(() => {
    alive = false;
    controller?.abort();
    window.clearInterval(refreshTimer);
    stopNavigation?.();
    document.removeEventListener('click', outside);
    document.removeEventListener('keydown', escape);
    document.removeEventListener('focusin', focusOutside);
    document.removeEventListener('visibilitychange', refresh);
});
</script>

<template>
    <div ref="root" class="relative">
        <button ref="trigger" type="button"
            class="relative flex h-9 w-9 items-center justify-center rounded-lg text-[var(--muted)] hover:bg-black/5 dark:hover:bg-white/10"
            :aria-label="unread ? 'Notifikasi, ' + unread + ' belum dibaca' : 'Notifikasi'"
            :aria-expanded="open" aria-controls="notification-panel" title="Notifikasi" @click="toggle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>
            </svg>
            <span v-if="unread" class="absolute -right-1 -top-1 min-w-4 rounded-full bg-red-600 px-1 text-[10px] font-semibold leading-4 text-white" aria-hidden="true">{{ unread > 99 ? '99+' : unread }}</span>
        </button>

        <section v-if="open" id="notification-panel" ref="panel" tabindex="-1" aria-labelledby="notification-title"
            :aria-busy="loading || saving"
            class="fixed left-3 right-3 top-[4.5rem] z-50 max-h-[calc(100dvh-6rem)] overflow-y-auto rounded-2xl border border-[var(--line)] bg-[var(--surface)] text-[var(--text)] shadow-2xl sm:absolute sm:left-auto sm:right-0 sm:top-12 sm:w-96">
            <header class="flex items-center justify-between gap-2 border-b border-[var(--line)] p-4">
                <h2 id="notification-title" class="text-base font-semibold">Notifikasi</h2>
                <button type="button" class="rounded px-2 py-1 text-sm text-[var(--muted)]" aria-label="Tutup notifikasi" @click="close(true)">Tutup</button>
            </header>
            <div class="flex items-center justify-between gap-2 px-4 py-3 text-xs">
                <span aria-live="polite">{{ unread }} belum dibaca</span>
                <button type="button" class="text-red-500 hover:underline disabled:opacity-40" :disabled="!unread || saving || loading" @click="markRead()">Tandai semua dibaca</button>
            </div>
            <div v-if="error" role="alert" class="mx-4 mb-3 rounded-lg border border-red-500/40 p-3 text-sm">
                <p>{{ error }}</p>
                <button type="button" class="mt-2 text-red-500 underline" :disabled="saving" @click="load()">Muat ulang</button>
            </div>
            <p v-if="loading && !items.length" role="status" class="p-5 text-sm text-[var(--muted)]">Memuat notifikasi...</p>
            <p v-else-if="!items.length && !error" class="p-5 text-sm leading-6 text-[var(--muted)]">Belum ada notifikasi. Aktivitas baru seperti membuat workspace atau menyimpan draft paket akan muncul di sini.</p>
            <ul v-if="items.length" class="divide-y divide-[var(--line)]">
                <li v-for="item in items" :key="item.id" class="p-4" :class="{ 'bg-red-500/5': !item.read_at }">
                    <div class="flex items-start gap-2">
                        <span v-if="!item.read_at" class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-red-500" aria-label="Belum dibaca"></span>
                        <div class="min-w-0 flex-1">
                            <h3 class="break-words text-sm font-semibold">{{ item.title }}</h3>
                            <p class="mt-1 whitespace-pre-line break-words text-sm leading-6 text-[var(--muted)]">{{ item.message }}</p>
                            <time :datetime="item.created_at" class="mt-2 block text-xs text-[var(--muted)]">{{ formatDate(item.created_at) }}</time>
                            <button v-if="!item.read_at" type="button" class="mt-2 text-xs text-red-500 hover:underline" :disabled="saving || loading" @click="markRead(item.id)">Tandai dibaca</button>
                            <span v-else class="mt-2 block text-xs text-[var(--muted)]">Sudah dibaca</span>
                        </div>
                    </div>
                </li>
            </ul>
            <footer v-if="lastPage > 1" class="flex items-center justify-between border-t border-[var(--line)] p-4 text-xs">
                <button type="button" :disabled="page <= 1 || loading || saving" class="disabled:opacity-40" @click="load(page - 1)">Sebelumnya</button>
                <span>{{ page }} / {{ lastPage }}</span>
                <button type="button" :disabled="page >= lastPage || loading || saving" class="disabled:opacity-40" @click="load(page + 1)">Berikutnya</button>
            </footer>
        </section>
    </div>
</template>
