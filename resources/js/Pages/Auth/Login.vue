<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AcademicCapIcon } from '@heroicons/vue/20/solid';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Đăng nhập Hệ thống" />

    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                <Link href="/">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/30 hover:scale-105 transition-transform cursor-pointer">
                        <AcademicCapIcon class="w-10 h-10 text-white" />
                    </div>
                </Link>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Đăng nhập hệ thống
            </h2>
            <p class="mt-2 text-center text-sm font-medium text-blue-600 uppercase tracking-wider">
                E-learning Training Matrix
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-xl shadow-gray-200/50 sm:rounded-2xl sm:px-10 border border-gray-100">
                
                <div v-if="status" class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-3 rounded-lg border border-green-100">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="email" value="Email công ty" class="font-bold text-gray-700" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="admin@elearning.com"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <InputLabel for="password" value="Mật khẩu" class="font-bold text-gray-700" />
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-xs font-bold text-blue-600 hover:text-blue-500"
                            >
                                Quên mật khẩu?
                            </Link>
                        </div>
                        <TextInput
                            id="password"
                            type="password"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <Checkbox id="remember" name="remember" v-model:checked="form.remember" class="text-blue-600 focus:ring-blue-500 rounded border-gray-300 h-4 w-4" />
                            <label for="remember" class="ml-2 block text-sm text-gray-700 font-medium">
                                Ghi nhớ thiết bị này
                            </label>
                        </div>
                    </div>

                    <div>
                        <button :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm shadow-blue-600/30 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <span v-if="form.processing" class="mr-2 animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                            Đăng nhập
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="mt-8 bg-blue-50 border border-blue-100 rounded-xl p-4 text-xs text-blue-800 shadow-sm">
                <p class="font-bold uppercase tracking-wider mb-2 text-blue-900 border-b border-blue-200 pb-2">Tài khoản Demo hệ thống</p>
                <div class="grid grid-cols-1 gap-1.5 font-medium">
                    <p>👑 <span class="font-bold">Admin:</span> admin@elearning.com</p>
                    <p>💼 <span class="font-bold">Trưởng phòng:</span> manager@sales.com</p>
                    <p>👨‍💻 <span class="font-bold">Nhân viên:</span> employee@sales.com</p>
                    <p class="mt-2 text-red-600">🔑 <span class="font-bold">Mật khẩu chung:</span> password</p>
                </div>
            </div>
        </div>
    </div>
</template>