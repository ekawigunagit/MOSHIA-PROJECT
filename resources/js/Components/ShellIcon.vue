<script setup>
defineProps({ name: { type: String, default: 'grid' } });
const paths = {
 heart: 'M20 4a5 5 0 0 0-8 2 5 5 0 0 0-8-2c-6 5 8 16 8 16S26 9 20 4Z',
 bag: 'M4 7h16l1 14H3ZM8 7V5a4 4 0 0 1 8 0v2',
 camera: 'M3 7h4l2-3h6l2 3h4v14H3ZM16 14a4 4 0 1 0-8 0 4 4 0 0 0 8 0',
 restaurant: 'M5 2v7m4-7v7M3 2v5a4 4 0 0 0 8 0V2M7 11v11M20 2c-4 3-5 7-5 10h5m0-10v20',
 home: 'm3 10 9-7 9 7v10H3ZM9 20v-7h6v7',
 grid: 'M3 3h7v7H3ZM14 3h7v7h-7ZM3 14h7v7H3ZM14 14h7v7h-7Z',
 user: 'M20 21v-2a7 7 0 0 0-14 0v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8',
 users: 'M16 21v-2a6 6 0 0 0-12 0v2M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M17 4a4 4 0 0 1 0 7m3 10v-2a6 6 0 0 0-3-5',
 card: 'M3 5h18v14H3ZM3 10h18M7 15h3',
 box: 'm12 3 9 5v9l-9 5-9-5V8Zm-9 5 9 5 9-5M12 13v9M7 5l9 5',
 shield: 'M12 3 3 7v5c0 5 9 10 9 10s9-5 9-10V7ZM8 12l3 3 5-6',
 clock: 'M12 8v5l3 2M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20',
 bell: 'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M9 21h6',
 moon: 'M21 13A9 9 0 0 1 11 3a9 9 0 1 0 10 10',
 logout: 'M9 4H3v16h6m5-12 4 4-4 4m-7-4h15',
 menu: 'M4 6h16M4 12h16M4 18h16',
 close: 'm6 6 12 12M6 18 18 6',
 chevron: 'm9 5 7 7-7 7',
};
</script>
<template><svg class="shell-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path :d="paths[name] || paths.grid" /></svg></template>
