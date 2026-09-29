<script setup>
defineProps({ name: { type: String, default: 'arrow' } });
const paths = {
 arrow: 'M5 12h14m-5-5 5 5-5 5', sun: 'M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5',
 menu: 'M4 6h16M4 12h16M4 18h16', chip: 'M6 6h12v12H6ZM10 2v4m4-4v4m-4 12v4m4-4v4M2 10h4m-4 4h4m12-4h4m-4 4h4',
 bolt: 'm13 2-9 12h7l-1 8L20 9h-7l1-7Z', chart: 'M4 20V10m6 10V4m6 16v-7m6 7V7',
 link: 'm10 13 4-4m-6 6-1 1a4 4 0 0 1-6-6l4-4a4 4 0 0 1 6 0m2 3 1-1a4 4 0 0 1 6 6l-4 4a4 4 0 0 1-6 0',
 grid: 'M3 3h7v7H3ZM14 3h7v7h-7ZM3 14h7v7H3ZM14 14h7v7h-7Z'
};
</script>
<template><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle v-if="name === 'sun'" cx="12" cy="12" r="4"/><path :d="paths[name] || paths.arrow"/></svg></template>
