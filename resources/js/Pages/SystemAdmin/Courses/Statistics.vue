<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// MOCK DATA: Theo đúng mockup "THỐNG KÊ KHÓA HỌC"
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
    { emp_code: 'NV001', name: 'Nguyễn A', class: 'L02', result: 'Fail' },
    { emp_code: 'NV014', name: 'Trần B', class: 'L02', result: 'Fail' },
]);
</script>

<template>
    <Head :title="'Thống kê: ' + course.name" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-200">
                    
                    <div class="mb-6">
                        <Link :href="route('system.courses.index')" class="text-lg font-bold text-gray-800 hover:text-gray-500 transition">
                            &lt; Quay lại
                        </Link>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-800 uppercase mb-8 border-b pb-4">THỐNG KÊ KHÓA HỌC</h2>

                    <h3 class="text-[17px] font-bold text-gray-800 uppercase mb-4">{{ course.name }}</h3>
                    <div class="grid grid-cols-2 gap-x-12 gap-y-2 text-[14px] text-gray-800 mb-8">
                        <div>
                            <p><span class="font-bold inline-block w-24">Mã khóa học:</span> {{ course.code }}</p>
                            <p><span class="font-bold inline-block w-24">Thời gian:</span> {{ course.time }}</p>
                            <p><span class="font-bold inline-block w-24">Trạng thái:</span> <span class="font-bold text-red-600">{{ course.status }}</span></p>
                        </div>
                        <div>
                            <p><span class="font-bold inline-block w-36">Nguồn tạo:</span> {{ course.source }}</p>
                            <p><span class="font-bold inline-block w-36">Phạm vi đào tạo:</span> {{ course.scope }}</p>
                            <p><span class="font-bold inline-block w-36">Đối tượng đào tạo:</span> {{ course.audience }}</p>
                        </div>
                    </div>

                    <div class="mb-10">
                        <h4 class="text-[15px] font-bold text-gray-800 mb-3">Tổng quan:</h4>
                        <div class="grid grid-cols-4 gap-4">
                            <div class="border border-gray-400 rounded p-4 text-center shadow-sm">
                                <p class="text-[13px] font-bold text-gray-800 mb-1">Tổng lớp học</p>
                                <p class="text-2xl font-bold text-[#0ea5e9]">{{ stats.classes }}</p>
                            </div>
                            <div class="border border-gray-400 rounded p-4 text-center shadow-sm">
                                <p class="text-[13px] font-bold text-gray-800 mb-1">Tổng học viên</p>
                                <p class="text-2xl font-bold text-[#16a34a]">{{ stats.students }}</p>
                            </div>
                            <div class="border border-gray-400 rounded p-4 text-center shadow-sm">
                                <p class="text-[13px] font-bold text-gray-800 mb-1">Hoàn thành %</p>
                                <p class="text-xl font-bold text-[#d97706]">{{ stats.completed }}</p>
                            </div>
                            <div class="border border-gray-400 rounded p-4 text-center shadow-sm">
                                <p class="text-[13px] font-bold text-gray-800 mb-1">Không đạt</p>
                                <p class="text-xl font-bold text-[#9333ea]">{{ stats.failed }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-10">
                        <h4 class="text-[15px] font-bold text-gray-800 mb-3">Kết quả theo lớp học:</h4>
                        <div class="overflow-x-auto border border-gray-300">
                            <table class="min-w-full divide-y divide-gray-300 text-center text-[14px]">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/6">Lớp học</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/4">Giảng viên</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-[12%]">Học viên</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-[15%]">Hoàn thành</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-[15%]">Không đạt</th>
                                        <th class="px-4 py-3 font-bold text-gray-900">Hoàn thành %</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-300">
                                    <tr v-for="(cls, index) in classResults" :key="index" class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-800">{{ cls.code }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-800">{{ cls.instructor }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ cls.students }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ cls.completed }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ cls.failed }}</td>
                                        <td class="px-4 py-3 text-gray-800 font-bold">{{ cls.percent }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 text-center">
                            <Link href="#" class="text-[#0ea5e9] hover:underline font-bold text-[13px] uppercase tracking-wide">
                                [ XEM TẤT CẢ ]
                            </Link>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-[15px] font-bold text-gray-800 mb-3">Danh sách học viên không đạt:</h4>
                        <div class="overflow-x-auto border border-gray-300">
                            <table class="min-w-full divide-y divide-gray-300 text-center text-[14px]">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/4">Mã NV</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/3 text-left pl-6">Tên nhân viên</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/5">Lớp</th>
                                        <th class="px-4 py-3 font-bold text-gray-900">Kết quả</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-300">
                                    <tr v-for="(stu, index) in failedStudents" :key="index" class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-800">{{ stu.emp_code }}</td>
                                        <td class="px-6 py-3 border-r border-gray-300 text-gray-800 text-left">{{ stu.name }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ stu.class }}</td>
                                        <td class="px-4 py-3 text-gray-800">{{ stu.result }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 text-center">
                            <Link href="#" class="text-[#0ea5e9] hover:underline font-bold text-[13px] uppercase tracking-wide">
                                [ XEM TẤT CẢ ]
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>