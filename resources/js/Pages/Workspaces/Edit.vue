<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ workspace: { type: Object, required: true } });
const form = useForm({ name: props.workspace.name });
</script>
<template>
    <Head title="Kelola workspace" />
    <MoshiaLayout>
        <template #header><h2>Kelola workspace</h2></template>
        <div class="shell account-main">
            <section class="detail-card">
                <h1>Kelola workspace</h1>
                <p>Ubah nama {{ workspace.name }}.</p>
                <form class="mt-6 max-w-xl space-y-4" @submit.prevent="form.patch(route('workspaces.update', workspace.id), { preserveScroll: true })">
                    <label for="workspace-name" class="moshia-label">Nama workspace</label>
                    <input id="workspace-name" v-model="form.name" required maxlength="100" class="moshia-input w-full" :aria-invalid="!!form.errors.name" aria-describedby="workspace-name-error" />
                    <InputError id="workspace-name-error" :message="form.errors.name" />
                    <div class="flex flex-wrap gap-3">
                        <button class="button button-primary" type="submit" :disabled="form.processing">{{ form.processing ? 'Menyimpan…' : 'Simpan perubahan' }}</button>
                        <Link :href="route('workspaces.select', workspace.id)" method="post" as="button" class="button button-outline" :disabled="form.processing">Kembali ke workspace</Link>
                    </div>
                </form>
            </section>
        </div>
    </MoshiaLayout>
</template>
