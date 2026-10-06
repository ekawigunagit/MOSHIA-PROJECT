<script setup>
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import ProductCatalog from '@/Components/ProductCatalog.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    products: { type: Array, default: () => [] },
    workspaces: { type: Array, default: () => [] },
    activeWorkspace: { type: Object, default: null },
});
const form = useForm({ name: '' });
function createWorkspace() {
    form.post(route('workspaces.store'), { onSuccess: () => form.reset() });
}
function selectWorkspace(event) {
    if (Number(event.target.value) !== props.activeWorkspace?.id) {
        router.post(route('workspaces.select', event.target.value), {}, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Dashboard Workspace" />
    <MoshiaLayout>
        <template #header><h2 class="text-xl font-semibold">Dashboard Workspace</h2></template>
        <div class="shell account-main space-y-8">
            <section class="detail-card dashboard-intro">
                <span class="section-kicker">WELCOME TO YOUR MOSHIA</span>
                <h1 class="mt-5">Halo, <span>{{ $page.props.auth.user.name }}.</span></h1>
                <p>Satu akun untuk seluruh ekosistem Moshia. Pilih workspace untuk memisahkan aktivitas pribadi atau bisnis Anda.</p>
                <div class="dashboard-actions">
                    <a href="#ecosystem" class="button button-primary">Lihat produk</a>
                    <Link :href="route('profile.edit')" class="button button-outline">Kelola profil</Link>
                </div>
            </section>

            <section class="detail-card">
                <h3>Workspace Anda</h3>
                <div v-if="workspaces.length" class="mb-6">
                    <label for="active-workspace" class="moshia-label mb-2">Workspace aktif</label>
                    <select id="active-workspace" class="moshia-input w-full" :value="activeWorkspace?.id" @change="selectWorkspace">
                        <option v-for="workspace in workspaces" :key="workspace.id" :value="workspace.id">{{ workspace.name }}</option>
                    </select>
                </div>
                <p v-else class="mb-5">Buat workspace pertama untuk menyiapkan ruang kerja Anda.</p>
                <form @submit.prevent="createWorkspace" class="space-y-3">
                    <label for="workspace-name" class="moshia-label">Nama workspace baru</label>
                    <input id="workspace-name" v-model="form.name" required maxlength="100" class="moshia-input w-full" placeholder="Contoh: Bisnis Saya" />
                    <InputError :message="form.errors.name" />
                    <button type="submit" class="button button-outline" :disabled="form.processing">Buat workspace</button>
                </form>
            </section>

            <section id="ecosystem" class="scroll-mt-24">
                <div class="section-heading">
                    <div><span class="section-kicker">MOSHIA PRODUCTS</span><h2>Produk Moshia, satu akun.</h2></div>
                </div>
                <ProductCatalog :products="products" show-access>
                    <template #actions="{ product }">
                        <div v-if="product.slug === 'wedding' && $page.props.developmentPayments" class="dashboard-actions">
                            <Link v-if="activeWorkspace" :href="route('billing.index', activeWorkspace.id)" class="button button-primary">Lihat paket</Link>
                            <p v-else>Buat workspace terlebih dahulu untuk melihat paket.</p>
                        </div>
                    </template>
                </ProductCatalog>
            </section>
        </div>
    </MoshiaLayout>
</template>
