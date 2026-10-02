<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    account: { type: Object, required: true },
    roles: { type: Array, required: true },
    canUpdateRoles: { type: Boolean, required: true },
});
const form = useForm({ name: props.account.name, email: props.account.email, roles: [...props.account.roles] });
const confirmed = ref(false);
const roleChanged = computed(() => JSON.stringify([...form.roles].sort()) !== JSON.stringify([...props.account.roles].sort()));
watch(() => [...form.roles], () => { confirmed.value = false; });
const roleErrors = computed(() => Object.entries(form.errors).filter(([key]) => key === 'roles' || key.startsWith('roles.')).map(([, message]) => message));
function save() {
    if (roleChanged.value && !confirmed.value) return;
    form.transform(({ roles, ...data }) => props.canUpdateRoles ? { ...data, roles } : data)
        .patch(route('admin.users.update', props.account.id), {
            preserveScroll: true,
            onSuccess: () => {
                form.defaults({ name: props.account.name, email: props.account.email, roles: [...props.account.roles] });
                form.reset();
                confirmed.value = false;
            },
        });
}
</script>

<template>
    <Head :title="`Kelola ${account.name}`" />
    <MoshiaLayout>
        <template #header><h2 class="text-xl font-semibold">Kelola Pengguna</h2></template>
        <div class="shell account-main space-y-6">
            <Link :href="route('admin.users.index')" class="button button-outline">Kembali ke pengguna</Link>
            <section class="detail-card max-w-3xl">
                <h1>{{ account.name }}</h1>
                <p class="mt-3">Perubahan email akan menghapus status verifikasi email sebelumnya.</p>
                <form class="mt-6 space-y-6" @submit.prevent="save">
                    <div>
                        <label for="account-name" class="moshia-label mb-2">Nama</label>
                        <input id="account-name" v-model="form.name" class="moshia-input w-full" maxlength="255" required autocomplete="off" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div>
                        <label for="account-email" class="moshia-label mb-2">Email</label>
                        <input id="account-email" v-model="form.email" type="email" class="moshia-input w-full" maxlength="255" required autocomplete="off" />
                        <InputError :message="form.errors.email" />
                    </div>
                    <fieldset class="space-y-3" :disabled="!canUpdateRoles || form.processing">
                        <legend class="moshia-label mb-2">Role platform</legend>
                        <label v-for="role in roles" :key="role.name" class="flex items-center gap-3">
                            <input v-model="form.roles" type="checkbox" :value="role.name" class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
                            <span>{{ role.name }}</span>
                        </label>
                    </fieldset>
                    <p v-if="!canUpdateRoles">Role akun sendiri hanya dapat diubah oleh superadmin lain.</p>
                    <InputError v-for="(message, index) in roleErrors" :key="index" :message="message" />
                    <div v-if="canUpdateRoles && roleChanged" class="rounded-lg border border-[var(--line)] p-4">
                        <p>Role {{ account.email }} akan diubah dari <strong>{{ account.roles.join(', ') || 'tanpa role' }}</strong> menjadi <strong>{{ form.roles.join(', ') || 'tanpa role' }}</strong>.</p>
                        <p v-if="form.roles.includes('super-admin')" class="mt-2">Super-admin dapat mengelola pengguna dan role seluruh platform.</p>
                        <label class="mt-3 flex items-start gap-3">
                            <input v-model="confirmed" type="checkbox" required class="mt-1 rounded text-red-600 focus:ring-red-500" />
                            <span>Saya mengonfirmasi perubahan role akun ini.</span>
                        </label>
                    </div>
                    <button class="button button-primary" :disabled="form.processing || (roleChanged && !confirmed)">Simpan perubahan</button>
                </form>
            </section>
        </div>
    </MoshiaLayout>
</template>
