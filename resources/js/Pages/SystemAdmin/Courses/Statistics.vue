<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeftIcon, AcademicCapIcon, UsersIcon, CheckBadgeIcon, XCircleIcon } from '@heroicons/vue/20/solid';

const course = {
    name: 'KỸ NĂNG BÁN HÀNG CHUYÊN SÂU',
    code: 'TRN-2026-SALES-01',
    time: '01/03/2026 - 30/03/2026',
    status: 'ĐÃ KẾT THÚC',
    source: 'Yêu cầu đào tạo (YC-2026-00001)',
    scope: 'Phòng Kinh doanh',
    audience: 'Toàn phòng'
};

const stats = {
    classes: 3,
    students: 120,
    completed: '98 (82%)',
    failed: '22 (18%)'
};

const classResults = ref([
    { code: 'L01', instructor: 'Nguyễn Văn Nam', students: 40, completed: 35, failed: 5, percent: '88%' },
    { code: 'L02', instructor: 'Nguyễn Văn Nam', students: 40, completed: 30, failed: 10, percent: '75%' },
    { code: 'L03', instructor: 'Trần Thị Hoa', students: 40, completed: 33, failed: 7, percent: '83%' },
]);

const failedStudents = ref([
    { emp_code: 'NV001', name: 'Nguyễn A', class: 'L02', result: 'Không đạt' },
    { emp_code: 'NV014', name: 'Trần B', class: 'L02', result: 'Không đạt' },
]);
</script>

<template>
    <Head :title="'Thống kê: ' + course.name" />

    <AuthenticatedLayout>
        <template #header>
            <Link :href="route('system.courses.index')" class="flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                <ChevronLeftIcon class="w-4 h-4" />
                Quay lại danh sách
            </Link>
        </template>

        <div class="py-8">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                    
                    <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Thống kê Khóa học</h2>
                            <p class="text-sm text-gray-500 mt-1">Báo cáo kết quả đào tạo chi tiết.</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border bg-blue-50 text-blue-700 border-blue-200 shadow-sm">
                            {{ course.status }}
                        </span>
                    </div>

                    <div class="p-8">
                        <div class="mb-10 bg-gray-50 rounded-xl p-6 border border-gray-100">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ course.name }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4 text-sm text-gray-600">
                                <div class="space-y-3">
                                    <p class="flex"><span class="font-medium text-gray-500 w-32">Mã khóa học:</span> <span class="font-semibold text-gray-900">{{ course.code }}</span></p>
                                    <p class="flex"><span class="font-medium text-gray-500 w-32">Thời gian:</span> <span class="font-semibold text-gray-900">{{ course.time }}</span></p>
                                </div>
                                <div class="space-y-3">
                                    <p class="flex"><span class="font-medium text-gray-500 w-32">Nguồn tạo:</span> <span class="font-semibold text-gray-900">{{ course.source }}</span></p>
                                    <p class="flex"><span class="font-medium text-gray-500 w-32">Phạm vi:</span> <span class="font-semibold text-gray-900">{{ course.scope }} ({{ course.audience }})</span></p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-12">
                            <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Tổng quan kết quả</h4>
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="bg-white border border-gray-200 p-5 rounded-xl shadow-sm relative overflow-hidden">
                                    <div class="absolute top-0 right-0 p-3 opacity-10"><AcademicCapIcon class="w-12 h-12 text-blue-600"/></div>
                                    <p class="text-3xl font-black text-gray-900 mb-1">{{ stats.classes }}</p>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tổng số lớp</p>
                                </div>
                                <div class="bg-white border border-gray-200 p-5 rounded-xl shadow-sm relative overflow-hidden">
                                    <div class="absolute top-0 right-0 p-3 opacity-10"><UsersIcon class="w-12 h-12 text-gray-600"/></div>
                                    <p class="text-3xl font-black text-gray-900 mb-1">{{ stats.students }}</p>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Học viên tham gia</p>
                                </div>
                                <div class="bg-green-50/50 border border-green-200 p-5 rounded-xl shadow-sm relative overflow-hidden">
                                    <div class="absolute top-0 right-0 p-3 opacity-10"><CheckBadgeIcon class="w-12 h-12 text-green-600"/></div>
                                    <p class="text-2xl font-black text-green-600 mb-1">{{ stats.completed }}</p>
                                    <p class="text-xs font-semibold text-green-700 uppercase tracking-wider">Hoàn thành</p>
                                </div>
                                <div class="bg-red-50/50 border border-red-200 p-5 rounded-xl shadow-sm relative overflow-hidden">
                                    <div class="absolute top-0 right-0 p-3 opacity-10"><XCircleIcon class="w-12 h-12 text-red-600"/></div>
                                    <p class="text-2xl font-black text-red-600 mb-1">{{ stats.failed }}</p>
                                    <p class="text-xs font-semibold text-red-700 uppercase tracking-wider">Không đạt</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-12">
                            <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Kết quả theo lớp</h4>
                            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200 text-left text-sm whitespace-nowrap">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Mã Lớp</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Giảng viên</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Học viên</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Hoàn thành</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Không đạt</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Tỷ lệ %</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(cls, index) in classResults" :key="index" class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 font-semibold text-blue-700">{{ cls.code }}</td>
                                            <td class="px-6 py-4 text-gray-900">{{ cls.instructor }}</td>
                                            <td class="px-6 py-4 text-center font-medium text-gray-700">{{ cls.students }}</td>
                                            <td class="px-6 py-4 text-center font-medium text-green-600">{{ cls.completed }}</td>
                                            <td class="px-6 py-4 text-center font-medium text-red-600">{{ cls.failed }}</td>
                                            <td class="px-6 py-4 text-center font-bold text-gray-900">{{ cls.percent }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-end mb-4">
                                <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Học viên chưa đạt</h4>
                                <Link href="#" class="text-blue-600 hover:underline text-sm font-medium">Xem tất cả &rarr;</Link>
                            </div>
                            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200 text-left text-sm whitespace-nowrap">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Mã NV</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Tên nhân viên</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Lớp</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Kết quả</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(stu, index) in failedStudents" :key="index" class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 text-gray-500 font-medium">{{ stu.emp_code }}</td>
                                            <td class="px-6 py-4 font-semibold text-gray-900">{{ stu.name }}</td>
                                            <td class="px-6 py-4 text-gray-600">{{ stu.class }}</td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-red-50 text-red-700 border-red-200">
                                                    {{ stu.result }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>