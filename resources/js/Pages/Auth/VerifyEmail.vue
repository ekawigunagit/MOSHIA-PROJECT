<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi Email" />

        <div class="mb-4 text-sm text-[var(--muted)]">
            Verifikasi email <strong class="break-all text-[var(--text)]">{{ $page.props.auth.user.email }}</strong>
            untuk mengakses dashboard Moshia. Buka email verifikasi, lalu klik tautan di dalamnya.
            Periksa folder spam jika email belum terlihat. Jika tautan kedaluwarsa, kirim ulang melalui tombol di bawah.
        </div>

        <div
            class="mb-4 text-sm font-medium text-green-600"
            v-if="verificationLinkSent"
        >
            Tautan verifikasi baru telah dikirim. Silakan periksa email Anda.
        </div>
        <InputError class="mb-4" :message="$page.props.errors?.email" />
        <Link :href="route('profile.edit')" class="mb-4 inline-block text-sm text-[var(--text)] underline">Perbaiki alamat email</Link>

        <form @submit.prevent="submit">
            <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Kirim ulang email verifikasi
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                    >Keluar</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>
