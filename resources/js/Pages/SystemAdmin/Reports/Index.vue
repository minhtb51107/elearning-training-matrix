<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// KHAI BÁO NHẬN DỮ LIỆU THẬT TỪ CONTROLLER
const props = defineProps({
    stats: Object,
    deptKPIs: Array,
    courseKPIs: Array,
    filters: Object
});

// Giữ lại các Ref cho thanh bộ lọc (Nếu sau này bác muốn làm logic lọc chi tiết hơn)
const filterYear = ref('2026');
</script>

<template>
    <Head title="Báo cáo - KPI Đào tạo" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Báo cáo - KPI Đào tạo</h2>

                    <div class="mb-8">
                        <label class="block text-base font-bold text-gray-800 mb-4">Bộ lọc:</label>
                        <div class="grid grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Năm:</label>
                                <select v-model="filterYear" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                    <option value="2026">2026</option>
                                    <option value="2025">2025</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Từ khóa:</label>
                                <input type="text" placeholder="Tên khóa học / lớp / phòng ban" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 italic placeholder-gray-400">
                            </div>
                        </div>
                    </div>

                    <div class="mb-10">
                        <label class="block text-base font-bold text-gray-800 mb-4">Tổng quan KPI:</label>
                        <div class="grid grid-cols-5 gap-4">
                            <div class="border border-gray-400 rounded p-4 text-center shadow-sm">
                                <p class="text-sm font-bold text-gray-800 mb-2">Tổng khóa học</p>
                                <p class="text-2xl font-bold text-[#0ea5e9]">{{ stats.courses }}</p>
                            </div>
                            <div class="border border-gray-400 rounded p-4 text-center shadow-sm">
                                <p class="text-sm font-bold text-gray-800 mb-2">Tổng lớp học</p>
                                <p class="text-2xl font-bold text-[#8b5cf6]">{{ stats.classes }}</p>
                            </div>
                            <div class="border border-gray-400 rounded p-4 text-center shadow-sm">
                                <p class="text-sm font-bold text-gray-800 mb-2">Tổng lượt học viên</p>
                                <p class="text-2xl font-bold text-[#16a34a]">{{ stats.students }}</p>
                            </div>
                            <div class="border border-gray-400 rounded p-4 text-center shadow-sm">
                                <p class="text-sm font-bold text-gray-800 mb-2">Hoàn thành %</p>
                                <p class="text-2xl font-bold text-[#d97706]">{{ stats.completed_percent }}</p>
                            </div>
                            <div class="border border-gray-400 rounded p-4 text-center shadow-sm">
                                <p class="text-sm font-bold text-gray-800 mb-2">Không đạt %</p>
                                <p class="text-2xl font-bold text-[#dc2626]">{{ stats.failed_percent }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-10">
                        <h3 class="text-base font-bold text-gray-800 mb-3">KPI theo phòng ban:</h3>
                        <div class="overflow-x-auto border border-gray-300">
                            <table class="min-w-full divide-y divide-gray-300 text-center text-[14px]">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/6">Phòng ban</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/6">Số lớp tham gia</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/6">Lượt học viên</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/6">Hoàn thành</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/6">% Hoàn thành</th>
                                        <th class="px-4 py-3 font-bold text-gray-900">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-300">
                                    <tr v-if="deptKPIs.length === 0">
                                        <td colspan="6" class="px-4 py-6 text-gray-500 italic">Chưa có dữ liệu học tập của phòng ban nào.</td>
                                    </tr>
                                    <tr v-for="(dept, index) in deptKPIs" :key="index" class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-800 font-semibold">{{ dept.department }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ dept.classes }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ dept.students }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-green-600 font-bold">{{ dept.completed }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-800 font-bold">{{ dept.percent }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-800 font-bold" :class="dept.status === 'Đạt KPI' ? 'text-green-600' : 'text-red-600'">
                                            {{ dept.status }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h3 class="text-base font-bold text-gray-800 mb-3">Chi tiết theo khóa học:</h3>
                        <div class="overflow-x-auto border border-gray-300">
                            <table class="min-w-full divide-y divide-gray-300 text-center text-[14px]">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/4 text-left pl-6">Khóa học</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/5">Số lớp đã mở</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/5">Lượt học viên</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/5">Hoàn thành</th>
                                        <th class="px-4 py-3 font-bold text-gray-900">% Hoàn thành</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-300">
                                    <tr v-if="courseKPIs.length === 0">
                                        <td colspan="5" class="px-4 py-6 text-gray-500 italic">Chưa có khóa học nào được triển khai lớp.</td>
                                    </tr>
                                    <tr v-for="(course, index) in courseKPIs" :key="index" class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-3 border-r border-gray-300 text-gray-800 text-left font-semibold">{{ course.course }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ course.classes }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ course.students }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-green-600 font-bold">{{ course.completed }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-800 font-bold">{{ course.percent }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>