<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// MOCK DATA: Chép chính xác 100% từ thiết kế của bạn
const stats = {
    total_requests: 12,
    pending_requests: 5,
    opened_courses: 2,
    participated_employees: 12
};

const recentRequests = [
    { name: 'Kỹ năng bán hàng', date: '12/01/2026', status: 'Đang chờ duyệt', statusClass: 'text-yellow-600 bg-yellow-50 px-2 py-1 rounded' },
    { name: 'An toàn lao động', date: '10/01/2026', status: 'Đã duyệt', statusClass: 'text-green-600 bg-green-50 px-2 py-1 rounded' },
    { name: 'Excel nâng cao', date: '05/01/2026', status: 'Từ chối', statusClass: 'text-red-600 bg-red-50 px-2 py-1 rounded' }
];

const activeCourses = [
    { name: 'Kỹ năng giao tiếp nội bộ', time: '15/01 - 20/01/2026', classes: 2, employees: 12, status: 'Đang diễn ra', statusClass: 'text-blue-600' },
    { name: 'An toàn lao động cơ bản', time: '20/01 - 25/01/2026', classes: 1, employees: 15, status: 'Sắp mở', statusClass: 'text-gray-600' }
];
</script>

<template>
    <Head title="Tổng quan đào tạo phòng ban" />

    <AuthenticatedLayout>
        <div v-if="user.role === 2" class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 uppercase">Tổng quan đào tạo phòng ban</h2>
                </div>

                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6 flex flex-wrap gap-6 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm:</label>
                        <input type="text" placeholder="Nhập từ khóa..." class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Từ ngày:</label>
                        <input type="date" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Đến ngày:</label>
                        <input type="date" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-600">
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-blue-50 border border-blue-100 p-4 rounded-lg text-center shadow-sm">
                        <p class="text-3xl font-bold text-blue-700">{{ stats.total_requests }}</p>
                        <p class="text-sm font-medium text-gray-600 mt-1">Tổng yêu cầu đã gửi</p>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-100 p-4 rounded-lg text-center shadow-sm">
                        <p class="text-3xl font-bold text-yellow-600">{{ stats.pending_requests }}</p>
                        <p class="text-sm font-medium text-gray-600 mt-1">Đang chờ duyệt</p>
                    </div>
                    <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-lg text-center shadow-sm">
                        <p class="text-3xl font-bold text-indigo-700">{{ stats.opened_courses }}</p>
                        <p class="text-sm font-medium text-gray-600 mt-1">Khóa học đã được mở</p>
                    </div>
                    <div class="bg-green-50 border border-green-100 p-4 rounded-lg text-center shadow-sm">
                        <p class="text-3xl font-bold text-green-700">{{ stats.participated_employees }}</p>
                        <p class="text-sm font-medium text-gray-600 mt-1">NV đã tham gia đào tạo</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-800">Yêu cầu đào tạo gần đây:</h3>
                        <div class="flex gap-3">
                            <Link :href="route('department.requests.create')" class="text-sm bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 px-4 rounded transition">
                                [+ TẠO YÊU CẦU ĐÀO TẠO MỚI]
                            </Link>
                        </div>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-6 py-3 text-left font-bold text-gray-700">Tên khóa học</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-700">Ngày gửi</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-700">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(req, index) in recentRequests" :key="index" class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ req.name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ req.date }}</td>
                                <td class="px-6 py-4 font-semibold">
                                    <span :class="req.statusClass">{{ req.status }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="px-6 py-3 bg-white border-t border-gray-200 text-right">
                        <Link :href="route('department.requests.index')" class="text-blue-600 hover:underline font-bold text-sm">
                            [XEM THÊM]
                        </Link>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-bold text-gray-800">Khóa học đang áp dụng cho phòng ban:</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-6 py-3 text-left font-bold text-gray-700">Tên khóa học</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-700">Thời gian</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Số lớp</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Số NV đã tham gia</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-700">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(course, index) in activeCourses" :key="index" class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-blue-600">{{ course.name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ course.time }}</td>
                                <td class="px-6 py-4 text-center font-bold">{{ course.classes }}</td>
                                <td class="px-6 py-4 text-center font-bold">{{ course.employees }}</td>
                                <td class="px-6 py-4 font-semibold">
                                    <span :class="course.statusClass">{{ course.status }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="px-6 py-3 bg-white border-t border-gray-200 text-right">
                        <Link :href="route('department.courses.index')" class="text-blue-600 hover:underline font-bold text-sm">
                            [XEM THÊM]
                        </Link>
                    </div>
                </div>

            </div>
        </div>
        
        <div v-else class="py-12 text-center text-gray-500">
            <p>Trang chủ cho Vai trò này đang được xây dựng...</p>
        </div>
    </AuthenticatedLayout>
</template>