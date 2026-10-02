<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

import ThemeToggle from '@/Components/ThemeToggle.vue';
import NotificationBell from '@/Components/NotificationBell.vue';

const page = usePage();
const mobileMenuOpen = ref(false);

const user = computed(() => page.props.auth?.user ?? null);
const roles = computed(() => page.props.auth?.roles ?? []);
const isSuperAdmin = computed(() => roles.value.includes('super-admin'));

const navigation = computed(() => [
    ...(isSuperAdmin.value ? [
        { route: 'admin.index', label: 'Dashboard Admin' },
        { route: 'admin.users.index', active: 'admin.users.*', label: 'Pengguna & Role' },
    ] : []),
    { route: 'dashboard', label: isSuperAdmin.value ? 'Workspace Saya' : 'Dashboard Workspace' },
    { route: 'profile.edit', label: 'Profil' },
]);

const flashSuccess = computed(() => page.props.flash?.success ?? null);
const flashError = computed(() => page.props.flash?.error ?? null);

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};
</script>

<template>
    <div
        class="moshia-auth-layout min-h-screen bg-white text-gray-900 transition-colors duration-200 dark:bg-[#0b0b0d] dark:text-gray-100"
    >
        <!-- DESKTOP SIDEBAR -->
        <aside
            class="fixed inset-y-0 left-0 z-30 hidden w-64 border-r border-gray-200 bg-white text-gray-900 transition-colors duration-200 dark:border-[#27272a] dark:bg-[#111113] dark:text-gray-100 md:flex md:flex-col"
        >
            <!-- Logo -->
            <div class="flex h-16 items-center border-b border-gray-200 px-6 dark:border-[#27272a]">
                <Link href="/" class="brand flex items-center gap-2">
                    <img src="/images/moshia-mark.svg" width="35" height="35" alt="MOSHIA" />
                    <span class="font-semibold text-gray-900 dark:text-white">
                        MOSHIA<span class="brand-dot">.</span>
                    </span>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 space-y-1 px-3 py-5" aria-label="Navigasi utama">
                <Link
                    v-for="item in navigation"
                    :key="item.route"
                    :href="route(item.route)"
                    :aria-current="route().current(item.active ?? item.route) ? 'page' : undefined"
                    class="block rounded-lg px-4 py-3 text-sm transition-colors"
                    :class="route().current(item.active ?? item.route)
                        ? 'bg-gray-100 font-semibold text-gray-900 dark:bg-[#232326] dark:text-white'
                        : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-[#1a1a1d] dark:hover:text-white'"
                    @click="closeMobileMenu"
                >
                    {{ item.label }}
                </Link>
            </nav>

            <!-- User / Logout -->
            <div class="border-t border-gray-200 p-4 dark:border-[#27272a]">
                <div class="mb-3">
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ user?.name }}
                    </div>
                    <div
                        v-if="isSuperAdmin"
                        class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                    >
                        Super Admin
                    </div>
                </div>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="w-full rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-900 transition-colors hover:bg-gray-100 dark:border-[#343439] dark:bg-[#0b0b0d] dark:text-white dark:hover:bg-[#1a1a1d]"
                >
                    Keluar
                </Link>
            </div>
        </aside>

        <!-- MOBILE DRAWER -->
        <div v-if="mobileMenuOpen" class="fixed inset-0 z-50 md:hidden">
            <div class="absolute inset-0 bg-black/60" @click="closeMobileMenu" />

            <aside
                class="relative z-10 flex h-full w-72 flex-col bg-white text-gray-900 shadow-2xl dark:bg-[#111113] dark:text-gray-100"
            >
                <div class="flex h-16 items-center justify-between border-b border-gray-200 px-5 dark:border-[#27272a]">
                    <Link href="/" class="brand flex items-center gap-2" @click="closeMobileMenu">
                        <img src="/images/moshia-mark.svg" width="35" height="35" alt="MOSHIA" />
                        <span class="font-semibold text-gray-900 dark:text-white">
                            MOSHIA<span class="brand-dot">.</span>
                        </span>
                    </Link>

                    <button
                        type="button"
                        class="rounded-lg p-2 text-xl text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-[#232326]"
                        aria-label="Tutup menu"
                        @click="closeMobileMenu"
                    >
                        ×
                    </button>
                </div>

                <nav class="flex-1 space-y-1 px-3 py-5" aria-label="Navigasi mobile">
                <Link
                    v-for="item in navigation"
                    :key="item.route"
                    :href="route(item.route)"
                    :aria-current="route().current(item.active ?? item.route) ? 'page' : undefined"
                    class="block rounded-lg px-4 py-3 text-sm transition-colors"
                    :class="route().current(item.active ?? item.route)
                        ? 'bg-gray-100 font-semibold text-gray-900 dark:bg-[#232326] dark:text-white'
                        : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-[#1a1a1d] dark:hover:text-white'"
                    @click="closeMobileMenu"
                >
                    {{ item.label }}
                </Link>
            </nav>

                <div class="border-t border-gray-200 p-4 dark:border-[#27272a]">
                    <div class="mb-3">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ user?.name }}
                        </div>
                        <div
                            v-if="isSuperAdmin"
                            class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                        >
                            Super Admin
                        </div>
                    </div>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="w-full rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-900 hover:bg-gray-100 dark:border-[#343439] dark:bg-[#0b0b0d] dark:text-white dark:hover:bg-[#1a1a1d]"
                    >
                        Keluar
                    </Link>
                </div>
            </aside>
        </div>

        <!-- MAIN -->
        <div class="min-h-screen bg-white md:pl-64 dark:bg-[#0b0b0d]">
            <!-- TOP BAR -->
            <header
                class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 text-gray-900 transition-colors dark:border-[#27272a] dark:bg-[#111113] dark:text-gray-100 md:px-6"
            >
                <div class="flex items-center gap-4">
                    <button
                        type="button"
                        class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-[#232326] md:hidden"
                        aria-label="Buka menu"
                        @click="mobileMenuOpen = true"
                    >
                        ☰
                    </button>

                    <div
                        v-if="$slots.header"
                        class="font-semibold text-gray-900 dark:text-gray-100"
                    >
                        <slot name="header" />
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <NotificationBell />
                    <ThemeToggle />

                    <span class="hidden text-sm text-gray-600 dark:text-gray-300 sm:block">
                        {{ user?.name }}
                    </span>
                </div>
            </header>

            <!-- CONTENT -->
            <main
                class="moshia-main-content min-h-[calc(100vh-4rem)] bg-white text-gray-900 transition-colors dark:bg-[#0b0b0d] dark:text-gray-100"
            >
                <div v-if="flashSuccess" class="shell pt-5">
                    <div
                        class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900/70 dark:bg-green-950/40 dark:text-green-300"
                        role="alert"
                    >
                        {{ flashSuccess }}
                    </div>
                </div>

                <div v-if="flashError" class="shell pt-5">
                    <div
                        class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/70 dark:bg-red-950/40 dark:text-red-300"
                        role="alert"
                    >
                        {{ flashError }}
                    </div>
                </div>

                <slot />
            </main>
        </div>
    </div>
</template>
