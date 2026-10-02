<script setup>
import MoshiaLayout from '@/Layouts/MoshiaLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <MoshiaLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-[var(--text)]"
            >
                Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div
                    class="rounded-2xl border border-[var(--line)] bg-[var(--surface)] p-5 shadow-sm sm:p-8"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div
                    class="rounded-2xl border border-[var(--line)] bg-[var(--surface)] p-5 shadow-sm sm:p-8"
                >
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div
                    class="rounded-2xl border border-[var(--line)] bg-[var(--surface)] p-5 shadow-sm sm:p-8"
                >
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </MoshiaLayout>
</template>
