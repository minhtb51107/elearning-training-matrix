<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { AcademicCapIcon, ArrowRightIcon } from '@heroicons/vue/20/solid';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    }
});
</script>

<template>
    <Head title="Hệ thống Quản lý Đào tạo Doanh nghiệp" />

    <div class="min-h-screen bg-gray-50 flex flex-col justify-center items-center p-6 selection:bg-blue-500 selection:text-white relative overflow-hidden">
        
        <div class="absolute top-0 -left-4 w-72 h-72 bg-blue-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-0 -right-4 w-72 h-72 bg-indigo-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>

        <div class="max-w-3xl text-center z-10">
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-xl shadow-blue-600/30">
                    <AcademicCapIcon class="w-12 h-12 text-white" />
                </div>
            </div>
            
            <h1 class="text-4xl md:text-6xl font-black text-gray-900 mb-6 tracking-tight leading-tight">
                Training Matrix <br/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Enterprise Edition</span>
            </h1>
            
            <p class="text-lg text-gray-600 mb-10 max-w-2xl mx-auto font-medium">
                Nền tảng quản lý, vận hành và tự động hóa quy trình đào tạo nhân sự nội bộ chuyên nghiệp. Đánh giá năng lực và theo dõi lộ trình thăng tiến toàn diện.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4" v-if="canLogin">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="inline-flex items-center gap-2 px-8 py-3.5 bg-blue-600 text-white rounded-full font-bold text-base hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/30 hover:shadow-xl hover:shadow-blue-600/40 hover:-translate-y-1 transform duration-200">
                    Vào bảng điều khiển <ArrowRightIcon class="w-5 h-5" />
                </Link>

                <template v-else>
                    <Link :href="route('login')" class="inline-flex items-center gap-2 px-10 py-3.5 bg-blue-600 text-white rounded-full font-bold text-base hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/30 hover:shadow-xl hover:shadow-blue-600/40 hover:-translate-y-1 transform duration-200">
                        Đăng nhập Hệ thống <ArrowRightIcon class="w-5 h-5" />
                    </Link>
                </template>
            </div>
        </div>

        <div class="absolute bottom-6 text-sm text-gray-400 font-medium">
            &copy; {{ new Date().getFullYear() }} E-learning Training Matrix System. All rights reserved.
        </div>
    </div>
</template>

<style>
@keyframes blob {
  0% { transform: translate(0px, 0px) scale(1); }
  33% { transform: translate(30px, -50px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
  100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
  animation: blob 7s infinite;
}
.animation-delay-2000 {
  animation-delay: 2s;
}
.animation-delay-4000 {
  animation-delay: 4s;
}
</style>