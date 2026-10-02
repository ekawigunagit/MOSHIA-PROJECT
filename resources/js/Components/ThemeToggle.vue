<script setup>
import { onMounted, ref } from 'vue';

const isDark = ref(true);

const applyTheme = (dark) => {
    isDark.value = dark;
    const theme = dark ? 'dark' : 'light';
    document.documentElement.classList.toggle('dark', dark);
    document.documentElement.dataset.theme = theme;
    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', dark ? '#0b0b0d' : '#ffffff');
    try {
        localStorage.setItem('theme', theme);
        localStorage.setItem('moshia-theme', theme);
    } catch { /* Theme remains usable when browser storage is unavailable. */ }
};

const toggleTheme = () => applyTheme(!isDark.value);

onMounted(() => {
    let savedTheme = 'dark';
    try {
        savedTheme = localStorage.getItem('theme') ?? localStorage.getItem('moshia-theme') ?? 'dark';
    } catch { /* Default to the Moshia dark theme. */ }
    applyTheme(savedTheme !== 'light');
});
</script>

<template>
    <button
        type="button"
        class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-[#232326] dark:hover:text-white"
        :aria-pressed="isDark"
        :aria-label="isDark ? 'Gunakan tema terang' : 'Gunakan tema gelap'"
        :title="isDark ? 'Gunakan tema terang' : 'Gunakan tema gelap'"
        @click="toggleTheme"
    >
        <svg
            v-if="isDark"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            class="h-5 w-5"
        >
            <circle cx="12" cy="12" r="4" />
            <path
                stroke-linecap="round"
                d="M12 2v2 M12 20v2 M4.93 4.93l1.41 1.41 M17.66 17.66l1.41 1.41 M2 12h2 M20 12h2 M4.93 19.07l1.41-1.41 M17.66 6.34l1.41-1.41"
            />
        </svg>

        <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            class="h-5 w-5"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"
            />
        </svg>
    </button>
</template>