<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import MoshiaIcon from '@/Components/MoshiaIcon.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import ScrollToTop from '@/Components/ScrollToTop.vue';
const props = defineProps({ canLogin: Boolean, canRegister: Boolean, products: { type: Array, default: () => [] } });
const menuOpen = ref(false);
const menuButton = ref(null);
const activeSection = ref('home');
const sections = [['home', 'Beranda'], ['ecosystem', 'Ekosistem'], ['about', 'Tentang Kami'], ['explore', 'Eksplorasi']];
const features = computed(() => props.products);

function onScroll() { for (const [id] of sections) { if (document.getElementById(id)?.getBoundingClientRect().top <= window.innerHeight * .4) activeSection.value = id; } }
function escapeMenu(event) { if (event.key === 'Escape' && menuOpen.value) { menuOpen.value = false; menuButton.value?.focus(); } }
function outsideMenu(event) { if (!event.target.closest('.site-header')) menuOpen.value = false; }
function resizeMenu() { if (window.innerWidth > 800) menuOpen.value = false; }
onMounted(() => { onScroll(); window.addEventListener('scroll', onScroll, { passive: true }); document.addEventListener('keydown', escapeMenu); document.addEventListener('click', outsideMenu); window.addEventListener('resize', resizeMenu); });
onUnmounted(() => { window.removeEventListener('scroll', onScroll); document.removeEventListener('keydown', escapeMenu); document.removeEventListener('click', outsideMenu); window.removeEventListener('resize', resizeMenu); });
</script>
<template>
 <Head title="Better Together. Beyond Tomorrow."><meta name="description" content="Moshia — ekosistem digital untuk menghubungkan ide, teknologi, dan peluang baru."/></Head>
 <a class="skip-link" href="#main">Langsung ke konten</a>
 <header class="site-header shell">
  <a href="#home" class="brand" aria-label="Moshia beranda"><img src="/images/moshia-mark.svg" width="35" height="35" alt=""><span>MOSHIA<span class="brand-dot">.</span></span></a>
  <nav id="navigation" class="navigation" :class="{ 'is-open': menuOpen }" aria-label="Navigasi utama"><a v-for="[id, label] in sections" :key="id" :href="`#${id}`" :class="{ active: activeSection === id }" :aria-current="activeSection === id ? 'location' : undefined" @click="menuOpen = false">{{ label }}</a></nav>
  <div class="header-actions"><ThemeToggle/><Link v-if="$page.props.auth?.user" :href="route('dashboard')" class="button button-primary header-cta">Dashboard <MoshiaIcon/></Link><Link v-else-if="canLogin" :href="route('login')" class="button button-primary header-cta">Masuk <MoshiaIcon/></Link><button ref="menuButton" class="icon-button menu-toggle" :aria-label="menuOpen ? 'Tutup navigasi' : 'Buka navigasi'" :aria-expanded="menuOpen" aria-controls="navigation" @click="menuOpen = !menuOpen"><MoshiaIcon name="menu"/></button></div>
 </header>
 <main id="main">
  <section id="home" class="hero shell">
   <div class="hero-copy"><div class="eyebrow"><span>✦</span> ONE ECOSYSTEM. ENDLESS POSSIBILITIES.</div><h1>Better Together.<br>Beyond <span>Tomorrow.</span></h1><p>Menghubungkan ide, teknologi, dan peluang dalam satu ekosistem. Bersama Moshia, wujudkan langkah besar berikutnya.</p><div class="hero-actions"><a href="#ecosystem" class="button button-primary">Jelajahi Moshia <MoshiaIcon/></a><a href="#about" class="button button-outline">Kenali Kami <MoshiaIcon/></a></div><div class="hero-note"><i></i> Dibangun untuk tumbuh bersama Anda</div></div>
   <div class="hero-visual" role="img" aria-label="Robot Moshia di tengah ekosistem digital dengan panel analitik dan pertumbuhan">
    <div class="orbit orbit-one"></div><div class="orbit orbit-two"></div>
    <div class="floating-panel analytics-panel"><div class="panel-heading">Growth Analytics <span>↗ 24.5%</span></div><svg viewBox="0 0 180 90" aria-hidden="true"><defs><linearGradient id="chart-fill" x2="0" y2="1"><stop stop-color="#f52e36" stop-opacity=".4"/><stop offset="1" stop-color="#f52e36" stop-opacity="0"/></linearGradient></defs><path d="M0 70 Q15 69 26 51 T51 54 T78 43 T106 37 T137 30 T164 15 L180 25 V90 H0Z" fill="url(#chart-fill)"/><path d="M0 70 Q15 69 26 51 T51 54 T78 43 T106 37 T137 30 T164 15 L180 25" fill="none" stroke="#ff4d59" stroke-width="2"/></svg><div class="chart-caption">MON <span>WED</span><span>FRI</span><span>SUN</span></div></div>
    <div class="floating-panel automation-panel"><div class="panel-heading">Connected Ecosystem</div><div class="connection-tree"><span><MoshiaIcon name="grid"/></span><div class="tree-line"></div><div class="tree-nodes"><span v-for="icon in ['chip', 'bolt', 'link']" :key="icon"><MoshiaIcon :name="icon"/></span></div></div></div>
    <img class="mascot" src="/images/moshia-bot.svg" alt="" width="360" height="380">
    <div class="floating-panel insight-panel"><div class="panel-heading">Limitless Possibilities</div><div class="insight-body"><div class="progress-ring"><span>82<small>%</small></span></div><div class="insight-lines"><i v-for="n in 4" :key="n"></i></div></div></div>
    <div class="floating-panel performance-panel"><div class="panel-heading">Growing Together <span>↗</span></div><div class="bar-chart"><i v-for="(height, index) in [25,49,36,76,57,95,72]" :key="index" :style="{ height: `${height}%` }"></i></div></div>
    <div class="platform"><div class="platform-ring"></div><div class="platform-core"></div></div><div class="visual-caption">✦ THE MOSHIA UNIVERSE</div>
   </div>
  </section>
  <section id="ecosystem" class="ecosystem shell" aria-label="Ekosistem Moshia"><a v-for="(feature, index) in features" :key="feature.id" :href="`#${feature.id}`" class="feature-card" :class="{ featured: index === 0 }"><span class="feature-icon"><MoshiaIcon :name="feature.icon"/></span><h2>{{ feature.title }}</h2><p>{{ feature.summary }}</p><span class="card-arrow"><MoshiaIcon/></span></a></section>
  <section class="values-strip"><div class="shell"><p>ONE VISION. A WORLD OF POSSIBILITIES.</p><div class="value-words"><span v-for="[icon, label] in [['grid','Innovation'],['link','Connection'],['bolt','Simplicity'],['chart','Growth'],['sun','Impact']]" :key="label"><MoshiaIcon :name="icon"/>{{ label }}</span></div></div></section>
  <section id="about" class="about-section shell"><div><span class="section-kicker">THIS IS MOSHIA</span><h2>Satu visi.<br><span>Banyak kemungkinan.</span></h2></div><div><p>Kami percaya hal besar dimulai dari koneksi yang tepat. Moshia hadir sebagai rumah bagi ide, produk, dan solusi digital yang membantu kita melangkah lebih jauh.</p><p>Dari eksplorasi pertama hingga kesempatan berikutnya, mari membangun masa depan yang lebih terhubung.</p><a class="text-link" href="#explore">Temukan kemungkinan Anda <MoshiaIcon/></a></div></section>
  <section id="explore" class="explore-section shell"><div class="section-heading"><div><span class="section-kicker">EXPLORE OUR UNIVERSE</span><h2>Ruang untuk setiap ide.</h2></div><p>Temukan apa yang bisa kita bangun bersama.</p></div><div class="detail-grid"><article v-for="(feature, index) in features" :id="feature.id" :key="feature.id" class="detail-card"><div class="detail-top"><MoshiaIcon :name="feature.icon"/><span>0{{ index + 1 }}</span></div><h3>{{ feature.title }}</h3><p>{{ feature.description }}</p><div class="detail-label">PART OF MOSHIA <span>↗</span></div></article></div></section>
  <section class="closing-section shell"><span class="section-kicker">LET’S GO FURTHER, TOGETHER</span><h2>Masa depan dimulai<br>dari <span>satu langkah.</span></h2><Link v-if="canRegister && !$page.props.auth?.user" :href="route('register')" class="button button-primary">Bergabung dengan Moshia <MoshiaIcon/></Link><a v-else href="#ecosystem" class="button button-primary">Temukan Ekosistem Moshia <MoshiaIcon/></a></section>
 </main>
 <footer class="site-footer shell"><a href="#home" class="brand"><img src="/images/moshia-mark.svg" width="28" height="28" alt=""><span>MOSHIA.</span></a><p>© {{ new Date().getFullYear() }} Moshia. All rights reserved.</p><a href="#home">Kembali ke atas ↑</a></footer><ScrollToTop/>
</template>
