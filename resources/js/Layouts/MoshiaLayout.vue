<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import NotificationBell from '@/Components/NotificationBell.vue';
import ShellIcon from '@/Components/ShellIcon.vue';
import ConsoleBreadcrumb from '@/Components/ConsoleBreadcrumb.vue';

const page = usePage();
const drawer = ref(false);
const workspaceOpen = ref(true);
const selectedWorkspaceId = computed(() => page.props.activeWorkspace?.id ?? page.props.selectedWorkspaceId ?? page.props.workspaceNavigation?.[0]?.id);
const accountOpen = ref(false);
const accountRoot = ref(null);
const accountPanel = ref(null);
const accountTrigger = ref(null);
const drawerPanel = ref(null);
const drawerTrigger = ref(null);
const dark = ref(false);
const user = computed(() => page.props.auth?.user ?? {});
const admin = computed(() => (page.props.auth?.roles ?? []).includes('super-admin'));
const navigation = computed(() => [
    ...(admin.value ? [
        { route: 'admin.index', label: 'Dashboard Admin', icon: 'home' },
        { route: 'admin.users.index', active: 'admin.users.*', label: 'Pengguna & Role', icon: 'users' },
        { route: 'admin.products.index', active: 'admin.products.*', label: 'Katalog Produk', icon: 'box' },
        { route: 'admin.plans.index', active: 'admin.plans.*', label: 'Draft Paket', icon: 'grid' },
        ...(page.props.developmentPayments ? [{ route: 'admin.payments.index', active: 'admin.payments.*', label: 'Pembayaran Manual', icon: 'card' }] : []),
    ] : []),
    { route: 'dashboard', label: admin.value ? 'Workspace Saya' : 'Dashboard Workspace', icon: 'grid' },
    ...(!admin.value ? [
        { route: 'products.plans', params: { slug: 'wedding' }, label: 'Wedding Invitation', icon: 'heart' },
        { route: 'products.plans', params: { slug: 'jastip' }, label: 'Jastip Manager', icon: 'bag' },
        { route: 'products.plans', params: { slug: 'photobooth' }, label: 'Photo Booth System', icon: 'camera' },
        { route: 'products.plans', params: { slug: 'restaurant' }, label: 'Restaurant Manager', icon: 'restaurant' },
    ] : []),
]);
function itemActive(item) {
    return item.params ? route().current(item.route, item.params) : route().current(item.active ?? item.route);
}
const accountItems = [
    { section: 'information', label: 'Account information', icon: 'user' },
    { section: 'billing', label: 'Billing', icon: 'card' },
    { section: 'security', label: 'Security', icon: 'shield' },
    { section: 'activity', label: 'Account activity', icon: 'clock' },
    { section: 'notifications', label: 'Notification settings', icon: 'bell' },
];
function applyTheme(value, persist = false) {
    dark.value = value;
    document.documentElement.classList.toggle('dark', value);
    document.documentElement.dataset.theme = value ? 'dark' : 'light';
    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', value ? '#17171c' : '#ffffff');
    if (persist) {
        try { localStorage.setItem('moshia-dashboard-theme', value ? 'dark' : 'light'); } catch {}
    }
}
function closeAccount(focus = false) {
    accountOpen.value = false;
    if (focus) accountTrigger.value?.focus();
}
async function toggleAccount() {
    drawer.value = false;
    accountOpen.value = !accountOpen.value;
    if (accountOpen.value) {
        await nextTick();
        accountPanel.value?.querySelector('a, button')?.focus();
    }
}
async function openDrawer() {
    closeAccount();
    drawer.value = true;
    await nextTick();
    drawerPanel.value?.querySelector('button')?.focus();
}
function closeDrawer(focus = false) {
    drawer.value = false;
    if (focus) drawerTrigger.value?.focus();
}
function outside(event) {
    if (!accountRoot.value?.contains(event.target)) closeAccount();
}
function keyboard(event) {
    if (event.key === 'Escape') {
        if (accountOpen.value) closeAccount(true);
        if (drawer.value) closeDrawer(true);
    }
    if (event.key === 'Tab' && drawer.value) {
        const items = [...drawerPanel.value.querySelectorAll('button, a[href]')].filter(el => el.getClientRects().length);
        const first = items[0], last = items.at(-1);
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    }
}
function focusOutside(event) {
    if (accountOpen.value && !accountRoot.value?.contains(event.target)) closeAccount();
}
watch(() => page.url, () => { closeAccount(); closeDrawer(); });
watch(drawer, value => { document.body.style.overflow = value ? 'hidden' : ''; });
function resize() { if (window.innerWidth >= 900) closeDrawer(); }
onMounted(() => {
    let theme = 'light';
    try { theme = localStorage.getItem('moshia-dashboard-theme') ?? 'light'; } catch {}
    applyTheme(theme === 'dark');
    document.addEventListener('pointerdown', outside);
    document.addEventListener('keydown', keyboard);
    document.addEventListener('focusin', focusOutside);
    window.addEventListener('resize', resize);
});
onUnmounted(() => {
    document.body.style.overflow = '';
    document.removeEventListener('pointerdown', outside);
    document.removeEventListener('keydown', keyboard);
    document.removeEventListener('focusin', focusOutside);
    window.removeEventListener('resize', resize);
});
</script>
<template>
    <div class="moshia-auth-layout moshia-console">
        <a class="console-skip" href="#console-content">Lewati navigasi</a>
        <header class="console-topbar">
            <div class="console-brand-area">
                <button ref="drawerTrigger" class="console-icon-button console-mobile-only" type="button" aria-label="Buka navigasi" :aria-expanded="drawer" aria-controls="console-sidebar" @click="openDrawer"><ShellIcon name="menu" /></button>
                <Link href="/" class="console-brand"><img src="/images/moshia-mark.svg" width="29" height="29" alt="" /><span>MOSHIA<span class="brand-dot">.</span></span></Link>
            </div>
            <div class="console-header-title"><slot name="header" /></div>
            <div class="console-top-actions">
                <span class="console-role">{{ admin ? 'Admin platform' : 'My workspace' }}</span>
                <NotificationBell />
                <div ref="accountRoot" class="console-account-root">
                    <button ref="accountTrigger" class="console-avatar" :class="{ 'is-open': accountOpen }" type="button" :aria-expanded="accountOpen" aria-controls="console-account" aria-label="Buka menu akun" @click="toggleAccount"><ShellIcon name="user" /></button>
                    <section v-if="accountOpen" id="console-account" ref="accountPanel" class="console-account-panel" aria-label="Menu akun">
                        <div class="console-account-heading"><strong>{{ user.name }}</strong><span>{{ user.email }}</span></div>
                        <nav aria-label="Pengaturan akun">
                            <Link v-for="item in accountItems" :key="item.section" :href="route('profile.edit', { section: item.section })" class="console-account-link" @click="closeAccount()"><ShellIcon :name="item.icon" /><span>{{ item.label }}</span></Link>
                        </nav>
                        <div class="console-account-divider">
                            <button class="console-account-link" type="button" role="switch" :aria-checked="dark" @click="applyTheme(!dark, true)"><ShellIcon name="moon" /><span>Dark mode</span><span class="console-switch" :class="{ on: dark }" aria-hidden="true"><span /></span></button>
                        </div>
                        <div class="console-account-divider">
                            <Link :href="route('logout')" method="post" as="button" class="console-account-link console-logout"><ShellIcon name="logout" /><span>Log out</span></Link>
                        </div>
                    </section>
                </div>
            </div>
        </header>
        <button v-if="drawer" type="button" class="console-overlay" aria-label="Tutup navigasi" tabindex="-1" @click="closeDrawer(true)" />
        <aside id="console-sidebar" ref="drawerPanel" class="console-sidebar" :class="{ 'is-open': drawer }" :role="drawer ? 'dialog' : undefined" :aria-modal="drawer ? true : undefined" aria-label="Navigasi Moshia">
            <div class="console-sidebar-heading"><span>{{ admin ? 'MOSHIA PLATFORM' : 'RUANG KERJA' }}</span><button type="button" class="console-icon-button console-mobile-only" aria-label="Tutup navigasi" @click="closeDrawer(true)"><ShellIcon name="close" /></button></div>
            <nav class="console-navigation" aria-label="Navigasi utama">
                <template v-for="item in navigation" :key="item.params?.slug ?? item.route">
                    <template v-if="item.route === 'dashboard'">
                        <button type="button" class="console-nav-item console-workspace-toggle" :class="{ active: page.component === 'Dashboard' || page.component === 'Workspaces/Create' }" :aria-expanded="workspaceOpen" aria-controls="workspace-submenu" @click="workspaceOpen = !workspaceOpen"><ShellIcon :name="item.icon" /><span>{{ item.label }}</span><ShellIcon name="chevron" class="console-nav-chevron" :class="{ 'is-expanded': workspaceOpen }" /></button>
                        <nav v-show="workspaceOpen" id="workspace-submenu" class="console-workspace-submenu" aria-label="Workspace">
                            <Link :href="route('workspaces.create')" class="console-nav-item" :class="{ active: page.component === 'Workspaces/Create' }" :aria-current="page.component === 'Workspaces/Create' ? 'page' : undefined" @click="closeDrawer()"><span aria-hidden="true">+</span><span>Tambah Workspace</span></Link>
                            <Link v-for="workspace in page.props.workspaceNavigation" :key="workspace.id" :href="route('workspaces.select', workspace.id)" method="post" as="button" class="console-nav-item" :class="{ active: page.component === 'Dashboard' && Number(selectedWorkspaceId) === workspace.id }" :aria-current="page.component === 'Dashboard' && Number(selectedWorkspaceId) === workspace.id ? 'page' : undefined" @click="closeDrawer()"><ShellIcon name="grid" /><span>{{ workspace.name }}</span></Link>
                        </nav>
                    </template>
                    <Link v-else :href="route(item.route, item.params)" :aria-current="itemActive(item) ? 'page' : undefined" class="console-nav-item" :class="{ active: itemActive(item) }" @click="closeDrawer()"><ShellIcon :name="item.icon" /><span>{{ item.label }}</span><ShellIcon v-if="itemActive(item)" name="chevron" class="console-nav-chevron" /></Link>
                </template>
            </nav>
            <div class="console-sidebar-footer"><span class="console-brand-dot" /><div><strong>Moshia Core</strong><span>{{ admin ? 'Pengelolaan platform' : 'Satu akun, seluruh produk' }}</span></div></div>
        </aside>
        <main id="console-content" class="moshia-main-content console-content" tabindex="-1">
            <ConsoleBreadcrumb />
            <div v-if="page.props.flash?.success || page.props.flash?.error" class="shell console-flash">
                <p v-if="page.props.flash?.success" class="console-alert" role="status">{{ page.props.flash.success }}</p>
                <p v-if="page.props.flash?.error" class="console-alert is-error" role="alert">{{ page.props.flash.error }}</p>
            </div>
            <slot />
        </main>
    </div>
</template>
