<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import MoshiaIcon from '@/Components/MoshiaIcon.vue';
const visible = ref(false);
const button = ref(null);
function update() {
 visible.value = window.scrollY > 300;
 if (!visible.value && document.activeElement === button.value) document.querySelector('.site-header .brand')?.focus({ preventScroll: true });
}
function goTop() {
 document.querySelector('.site-header .brand')?.focus({ preventScroll: true });
 window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
}
onMounted(() => { update(); window.addEventListener('scroll', update, { passive: true }); });
onUnmounted(() => window.removeEventListener('scroll', update));
</script>
<template><button v-show="visible" ref="button" type="button" class="scroll-top" aria-label="Kembali ke header" title="Kembali ke atas" @click="goTop"><MoshiaIcon/></button></template>
