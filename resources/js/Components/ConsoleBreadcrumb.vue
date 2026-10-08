<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ShellIcon from '@/Components/ShellIcon.vue';

const page = usePage();
const items = computed(() => {
    const component = page.component;
    const query = new URL(page.url, 'https://moshia.local').searchParams;
    if (component === 'Profile/Edit') {
        const sections = { information: 'Account information', security: 'Security', activity: 'Account activity', notifications: 'Notification settings' };
        if (query.get('section') === 'billing') {
            const tabs = { subscription: 'Subscription', history: 'Payment History', method: 'Payment Method' };
            return [{ label: 'Billing', href: route('profile.edit', { section: 'billing' }) }, { label: tabs[query.get('billing_tab')] ?? 'Subscription' }];
        }
        return [{ label: sections[query.get('section')] ?? 'Account information' }];
    }
    if (component === 'Workspaces/Create') return [{ label: 'Dashboard Workspace', href: route('dashboard') }, { label: 'Tambah Workspace' }];
    if (component === 'Workspaces/Edit') return [{ label: 'Dashboard Workspace', href: route('dashboard') }, { label: page.props.workspace.name }, { label: 'Kelola workspace' }];
    if (component === 'Dashboard') return page.props.activeWorkspace
        ? [{ label: 'Dashboard Workspace', href: route('dashboard') }, { label: page.props.activeWorkspace.name }]
        : [{ label: 'Dashboard Workspace' }];
    if (component === 'Products/Plans') return [{ label: page.props.product?.title ?? 'Produk' }];
    if (component === 'Billing/Index' || component === 'Wedding/Edit') {
        return [
            { label: 'Wedding Invitation', href: route('products.plans', 'wedding') },
            { label: component === 'Billing/Index' ? 'Keranjang & Pesanan' : 'Wedding Studio' },
        ];
    }
    const adminPages = {
        Users: { label: 'Pengguna & Role', route: 'admin.users.index', edit: 'Edit Pengguna' },
        Products: { label: 'Katalog Produk', route: 'admin.products.index', edit: 'Edit Produk' },
        Plans: { label: 'Draft Paket', route: 'admin.plans.index', edit: page.props.plan ? 'Edit Draft Paket' : 'Buat Draft Paket' },
        Payments: { label: 'Pembayaran Manual', route: 'admin.payments.index' },
    };
    if (component.startsWith('Admin/')) {
        const [, group, view] = component.split('/');
        const crumbs = [{ label: 'Dashboard Admin', href: route('admin.index') }];
        const entry = adminPages[group];
        if (entry) {
            crumbs.push({ label: entry.label, href: route(entry.route) });
            if (view === 'Edit' || view === 'Form') crumbs.push({ label: entry.edit });
        }
        return crumbs;
    }
    return [];
});
</script>

<template>
    <nav v-if="items.length" class="shell console-breadcrumb" aria-label="Breadcrumb">
        <ol>
            <li><Link :href="route('dashboard')" aria-label="Beranda"><ShellIcon name="home" /></Link></li>
            <li v-for="(item, index) in items" :key="index">
                <ShellIcon name="chevron" class="console-breadcrumb-separator" />
                <Link v-if="index < items.length - 1 && item.href" :href="item.href">{{ item.label }}</Link>
                <span v-else :aria-current="index === items.length - 1 ? 'page' : undefined">{{ item.label }}</span>
            </li>
        </ol>
    </nav>
</template>
