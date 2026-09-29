<script setup>
import MoshiaIcon from '@/Components/MoshiaIcon.vue';
import { onMounted, ref } from 'vue';
const light = ref(false);
function applyTheme() {
 document.documentElement.dataset.theme = light.value ? 'light' : 'dark';
 document.querySelector('meta[name="theme-color"]')?.setAttribute('content', light.value ? '#faf8f8' : '#09090c');
}
onMounted(() => { try { light.value = localStorage.getItem('moshia-theme') === 'light'; } catch {} applyTheme(); });
function toggleTheme() { light.value = !light.value; applyTheme(); try { localStorage.setItem('moshia-theme', light.value ? 'light' : 'dark'); } catch {} }
</script>
<template><button type="button" class="icon-button" :aria-label="light ? 'Aktifkan tema gelap' : 'Aktifkan tema terang'" :aria-pressed="light" @click="toggleTheme"><MoshiaIcon name="sun"/></button></template>
