<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';
import { UserCircleIcon } from '@heroicons/vue/24/outline';

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
    <Head title="Hồ sơ cá nhân" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="mb-6 flex items-center gap-3">
                    <UserCircleIcon class="w-8 h-8 text-blue-600" />
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 leading-tight">Cài đặt Hồ sơ Cá nhân</h2>
                        <p class="text-sm text-gray-500">Quản lý thông tin tài khoản và bảo mật của bạn.</p>
                    </div>
                </div>

                <div class="p-6 sm:p-8 bg-white shadow-sm sm:rounded-xl border border-gray-200">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="p-6 sm:p-8 bg-white shadow-sm sm:rounded-xl border border-gray-200">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>