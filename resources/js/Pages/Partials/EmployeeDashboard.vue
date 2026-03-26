<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

// Tự động "móc cáp" lấy dữ liệu từ Controller mà không cần file cha truyền xuống
const page = usePage();
const dashboardData = computed(() => page.props.dashboardData || {});
const user = computed(() => page.props.auth.user || {});
</script>

<template>
    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8 border-b border-gray-300 pb-4">
                <h1 class="text-2xl font-bold text-gray-800 mb-1">Xin chào, {{ user.name }} 👋</h1>
                <p class="text-[15px] font-bold text-gray-800">Phòng ban: {{ dashboardData.employeeStats?.department || 'Chưa cập nhật' }}</p>
            </div>

            <div class="mb-10">
                <h3 class="text-base font-bold text-gray-800 mb-4 uppercase">TỔNG QUAN HỌC TẬP</h3>
                <div class="flex gap-8">
                    <div class="border border-gray-300 w-48 h-32 flex flex-col items-center justify-center rounded-sm shadow-sm bg-white">
                        <p class="text-sm font-bold text-gray-600 mb-1">ĐANG HỌC</p>
                        <p class="text-3xl font-extrabold text-[#d97706] mb-1">{{ dashboardData.employeeStats?.learning || 0 }}</p>
                        <p class="text-sm font-bold text-gray-800">Lớp học</p>
                    </div>
                    <div class="border border-gray-300 w-48 h-32 flex flex-col items-center justify-center rounded-sm shadow-sm bg-white">
                        <p class="text-sm font-bold text-gray-600 mb-1">SẮP HỌC</p>
                        <p class="text-3xl font-extrabold text-[#d97706] mb-1">{{ dashboardData.employeeStats?.upcoming || 0 }}</p>
                        <p class="text-sm font-bold text-gray-800">Lớp học</p>
                    </div>
                </div>
            </div>

            <div class="mb-10">
                <h3 class="text-base font-bold text-gray-800 mb-4 uppercase">Lớp học sắp diễn ra</h3>
                
                <div v-if="!dashboardData.upcomingClasses || dashboardData.upcomingClasses.length === 0" class="bg-gray-50 border border-gray-200 border-dashed p-6 text-center text-gray-500 text-sm italic rounded-sm">
                    Hiện tại bạn không có lịch học nào sắp diễn ra.
                </div>

                <div class="space-y-4">
                    <div v-for="cls in dashboardData.upcomingClasses" :key="cls.id" class="bg-white border border-gray-300 p-5 rounded-sm shadow-sm relative">
                        <p class="text-sm font-bold text-gray-800 mb-3">{{ cls.date }}</p>
                        <p class="text-base font-bold text-gray-800 mb-2">{{ cls.title }}</p>
                        <p class="text-[14px] text-gray-700">
                            {{ cls.time }} | {{ cls.format }} <span v-if="cls.instructor">| GV: {{ cls.instructor }}</span>
                        </p>
                        
                        <Link v-if="cls.canJoin" :href="cls.url" class="absolute right-5 bottom-5 bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] font-bold py-1.5 px-6 rounded-sm shadow-sm transition text-sm text-center">
                            {{ cls.action }}
                        </Link>
                        <button v-else disabled class="absolute right-5 bottom-5 bg-gray-100 text-gray-500 border border-gray-200 font-bold py-1.5 px-6 rounded-sm text-sm cursor-not-allowed">
                            {{ cls.action }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="mb-10">
                <h3 class="text-base font-bold text-gray-800 mb-4 uppercase">Lớp đang học</h3>
                
                <div v-if="!dashboardData.inProgressClasses || dashboardData.inProgressClasses.length === 0" class="bg-gray-50 border border-gray-200 border-dashed p-6 text-center text-gray-500 text-sm italic rounded-sm">
                    Bạn chưa tham gia lớp học nào.
                </div>

                <div class="space-y-4">
                    <div v-for="cls in dashboardData.inProgressClasses" :key="cls.id" class="bg-white border border-gray-300 p-5 rounded-sm shadow-sm relative pr-40 group hover:border-sky-300 transition-colors">
                        <p class="text-[14px] font-bold text-gray-800 mb-1">{{ cls.course }}</p>
                        <p class="text-[15px] font-bold text-gray-800 mb-4">{{ cls.class }}</p>
                        
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-bold text-gray-800">Tiến độ:</span>
                            <div class="w-64 bg-gray-200 rounded-full h-3">
                                <div class="bg-gray-800 h-3 rounded-full transition-all duration-1000" :style="{ width: cls.progress + '%' }"></div>
                            </div>
                            <span class="text-sm font-bold text-gray-800">{{ cls.progress }}%</span>
                        </div>

                        <Link :href="cls.url" class="absolute right-5 bottom-5 bg-[#bae6fd] hover:bg-[#7dd3fc] text-gray-900 border border-[#7dd3fc] font-bold py-1.5 px-6 rounded-sm shadow-sm transition text-sm text-center">
                            Tiếp tục học
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>