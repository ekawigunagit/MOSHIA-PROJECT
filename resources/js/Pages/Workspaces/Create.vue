<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import InputError from '@/Components/InputError.vue';

const form = useForm({ name: '' });
</script>
<template>
    <Head title="Tambah Workspace" />
    <MoshiaLayout>
        <template #header><h2>Tambah Workspace</h2></template>
        <div class="shell account-main">
            <section class="detail-card">
                <h1>Tambah Workspace</h1>
                <p>Buat ruang kerja untuk mengelola produk dan proyek Anda.</p>
                <form class="mt-6 max-w-xl space-y-4" @submit.prevent="form.post(route('workspaces.store'))">
                    <label for="workspace-name" class="moshia-label">Nama workspace</label>
                    <input id="workspace-name" v-model="form.name" required maxlength="100" class="moshia-input w-full" placeholder="Contoh: Pernikahan Eka" />
                    <InputError :message="form.errors.name" />
                    <button class="button button-primary" type="submit" :disabled="form.processing">{{ form.processing ? 'Membuat workspace…' : 'Buat workspace' }}</button>
                </form>
            </section>
        </div>
    </MoshiaLayout>
</template>
