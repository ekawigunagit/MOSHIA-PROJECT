<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    users: { type: Object, required: true },
    filters: { type: Object, required: true },
    roles: { type: Array, required: true },
});
const form = useForm({ search: props.filters.search, role: props.filters.role });
function search() {
    form.get(route('admin.users.index'), { preserveState: true, replace: true });
}
function reset() {
    form.search = '';
    form.role = '';
    search();
}
</script>

<template>
    <Head title="Pengguna & Role" />
    <MoshiaLayout>
        <template #header><h2 class="text-xl font-semibold">Pengguna &amp; Role</h2></template>
        <div class="shell account-main space-y-6">
            <section class="detail-card">
                <span class="section-kicker">AKSES PLATFORM</span>
                <h1 class="mt-4">Pengguna Moshia</h1>
                <p>Kelola identitas akun dan role platform. Role super-admin memberi akses pengelolaan platform; akses produk tetap mengikuti workspace dan paket.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div><h3>User</h3><p>Mengelola akun dan workspace sendiri.</p></div>
                    <div><h3>Super Admin</h3><p>Mengelola pengguna dan role seluruh platform.</p></div>
                </div>
            </section>
            <section class="detail-card">
                <form class="grid items-end gap-4 md:grid-cols-3" @submit.prevent="search">
                    <div>
                        <label class="moshia-label mb-2" for="user-search">Cari nama atau email</label>
                        <input id="user-search" v-model="form.search" type="search" maxlength="100" class="moshia-input w-full" />
                        <InputError :message="form.errors.search" />
                    </div>
                    <div>
                        <label class="moshia-label mb-2" for="role-filter">Role</label>
                        <select id="role-filter" v-model="form.role" class="moshia-input w-full">
                            <option value="">Semua role</option>
                            <option v-for="role in roles" :key="role.name" :value="role.name">{{ role.name }}</option>
                        </select>
                        <InputError :message="form.errors.role" />
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button class="button button-primary" :disabled="form.processing">Cari</button>
                        <button type="button" class="button button-outline" :disabled="form.processing" @click="reset">Reset</button>
                    </div>
                </form>
                <p class="my-5" role="status">{{ users.total }} pengguna ditemukan.</p>
                <div v-if="users.data.length" class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <caption class="sr-only">Daftar pengguna platform Moshia</caption>
                        <thead><tr class="border-b border-[var(--line)]">
                            <th scope="col" class="p-3">Pengguna</th>
                            <th scope="col" class="p-3">Role</th>
                            <th scope="col" class="p-3">Email</th>
                            <th scope="col" class="p-3">Aksi</th>
                        </tr></thead>
                        <tbody><tr v-for="account in users.data" :key="account.id" class="border-b border-[var(--line)]">
                            <td class="p-3"><div class="font-semibold">{{ account.name }}</div><div class="break-all">{{ account.email }}</div></td>
                            <td class="p-3">{{ account.roles.join(', ') || 'Belum memiliki role' }}</td>
                            <td class="p-3">{{ account.email_verified_at ? 'Terverifikasi' : 'Belum diverifikasi' }}</td>
                            <td class="p-3"><Link :href="route('admin.users.edit', account.id)" class="button button-outline" :aria-label="`Kelola ${account.name}`">Kelola</Link></td>
                        </tr></tbody>
                    </table>
                </div>
                <p v-else>Belum ada pengguna yang cocok. Coba ubah pencarian atau filter role.</p>
                <nav v-if="users.last_page > 1" aria-label="Halaman daftar pengguna" class="mt-5 flex flex-wrap items-center gap-4">
                    <Link v-if="users.prev_page_url" :href="users.prev_page_url" class="button button-outline">Sebelumnya</Link>
                    <span>Halaman {{ users.current_page }} dari {{ users.last_page }}</span>
                    <Link v-if="users.next_page_url" :href="users.next_page_url" class="button button-outline">Berikutnya</Link>
                </nav>
            </section>
        </div>
    </MoshiaLayout>
</template>
