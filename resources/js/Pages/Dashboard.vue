<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// ==========================================
// MOCK DATA: ADMIN HỆ THỐNG (ROLE 1)
// ==========================================
const sysStats = {
    total_requests: 45,
    pending_requests: 12,
    created_courses: 18,
    opened_classes: 22,
    participated_employees: 320
};
const sysPendingRequests = [
    { department: 'Kinh doanh', name: 'Kỹ năng bán..', date: '12/01/2026', status: 'Chờ' },
    { department: 'IT', name: 'Bảo mật', date: '11/01/2026', status: 'Chờ' },
    { department: 'Kế toán', name: 'Thuế mới', date: '10/01/2026', status: 'Chờ' }
];
const sysUpcomingClasses = [
    { code: 'L001', name: 'Lớp bán hàng 1', course: 'Kỹ năng bán hàng', department: 'Kinh doanh', date: '15/01/26', students: 25 },
    { code: 'L002', name: 'Lớp bảo mật cơ bản', course: 'An toàn bảo mật', department: 'IT', date: '16/01/26', students: 20 }
];

// ==========================================
// MOCK DATA: ADMIN PHÒNG BAN (ROLE 2)
// ==========================================
const deptStats = { total_requests: 12, pending_requests: 5, opened_courses: 2, participated_employees: 12 };
const deptRecentRequests = [
    { name: 'Kỹ năng bán hàng', date: '12/01/2026', status: 'Đang chờ duyệt', statusClass: 'text-yellow-600 bg-yellow-50 px-2 py-1 rounded' },
    { name: 'An toàn lao động', date: '10/01/2026', status: 'Đã duyệt', statusClass: 'text-green-600 bg-green-50 px-2 py-1 rounded' },
    { name: 'Excel nâng cao', date: '05/01/2026', status: 'Từ chối', statusClass: 'text-red-600 bg-red-50 px-2 py-1 rounded' }
];
const deptActiveCourses = [
    { name: 'Kỹ năng giao tiếp nội bộ', time: '15/01 - 20/01/2026', classes: 2, employees: 12, status: 'Đang diễn ra', statusClass: 'text-blue-600' },
    { name: 'An toàn lao động cơ bản', time: '20/01 - 25/01/2026', classes: 1, employees: 15, status: 'Sắp mở', statusClass: 'text-gray-600' }
];

// ==========================================
// MOCK DATA: NHÂN VIÊN (ROLE 3)
// ==========================================
const employeeData = {
    overview: { learning: 2, upcoming: 3 },
    upcomingClasses: [
        { id: 1, date: 'Hôm nay - 19/01/2026', title: 'Sales nâng cao - Lớp L1', time: '08:00 - 10:00', format: 'Online', instructor: 'Nguyễn Văn Nam', action: 'Tham gia lớp học', canJoin: true },
        { id: 2, date: 'Ngày mai - 20/01/2026', title: 'DevOps cơ bản - Lớp L2', time: '13:30 - 15:30', format: 'Offline - Phòng 402', instructor: '', action: '', canJoin: false }
    ],
    inProgressClasses: [
        { id: 1, course: 'Kỹ năng bán hàng chuyên sâu', class: 'Sales nâng cao - Lớp L1', progress: 45 }
    ]
};
</script>

<template>
    <Head title="Trang chủ Dashboard" />

    <AuthenticatedLayout>
        
        <div v-if="user.role === 1" class="py-6 bg-white min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Tổng quan đào tạo toàn công ty</h2>

                <div class="mb-6">
                    <label class="block text-base font-medium text-gray-800 mb-2">Bộ lọc:</label>
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="w-48">
                            <label class="block text-sm text-gray-600 mb-1">Từ ngày:</label>
                            <input type="date" class="w-full border-gray-400 rounded-sm text-sm text-gray-600 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div class="w-48">
                            <label class="block text-sm text-gray-600 mb-1">Đến ngày:</label>
                            <input type="date" class="w-full border-gray-400 rounded-sm text-sm text-gray-600 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-base font-medium text-gray-800 mb-2">Hệ thống đào tạo:</label>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="border border-gray-300 p-4 rounded-sm text-center shadow-sm">
                            <p class="text-sm font-bold text-gray-800 mb-2">Tổng yêu cầu đào tạo</p>
                            <p class="text-2xl font-bold text-[#d97706]">{{ sysStats.total_requests }}</p>
                        </div>
                        <div class="border border-gray-300 p-4 rounded-sm text-center shadow-sm">
                            <p class="text-sm font-bold text-gray-800 mb-2">Đang chờ duyệt</p>
                            <p class="text-2xl font-bold text-[#d97706]">{{ sysStats.pending_requests }}</p>
                        </div>
                        <div class="border border-gray-300 p-4 rounded-sm text-center shadow-sm">
                            <p class="text-sm font-bold text-gray-800 mb-2">Khóa học đã tạo</p>
                            <p class="text-2xl font-bold text-[#65a30d]">{{ sysStats.created_courses }}</p>
                        </div>
                        <div class="border border-gray-300 p-4 rounded-sm text-center shadow-sm">
                            <p class="text-sm font-bold text-gray-800 mb-2">Lớp học đang mở</p>
                            <p class="text-2xl font-bold text-[#65a30d]">{{ sysStats.opened_classes }}</p>
                        </div>
                        <div class="border border-gray-300 p-4 rounded-sm text-center shadow-sm">
                            <p class="text-sm font-bold text-gray-800 mb-2">NV đã tham gia đào tạo</p>
                            <p class="text-2xl font-bold text-[#0ea5e9]">{{ sysStats.participated_employees }}</p>
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-base font-medium text-gray-800 mb-2">Yêu cầu đào tạo cần xử lý:</h3>
                    <div class="overflow-x-auto border border-gray-400">
                        <table class="min-w-full divide-y divide-gray-400 text-center text-sm">
                            <thead class="bg-[#e5e7eb]">
                                <tr>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-1/4">Phòng ban</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-1/4">Tên yêu cầu</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-1/4">Ngày gửi</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 w-1/4">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-400">
                                <tr v-for="(req, index) in sysPendingRequests" :key="index" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800">{{ req.department }}</td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800">{{ req.name }}</td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800">{{ req.date }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ req.status }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-center">
                        <Link :href="route('system.requests.index')" class="text-[#0ea5e9] hover:underline font-medium text-sm uppercase">
                            [ XEM TẤT CẢ YÊU CẦU ]
                        </Link>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-base font-medium text-gray-800 mb-2">Lớp học sắp diễn ra:</h3>
                    <div class="overflow-x-auto border border-gray-400">
                        <table class="min-w-full divide-y divide-gray-400 text-center text-sm">
                            <thead class="bg-[#e5e7eb]">
                                <tr>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-[10%]">Mã lớp</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-[25%]">Tên lớp</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-[25%]">Khóa học</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-[15%]">Phòng ban</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-[15%]">Ngày học</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 w-[10%]">Số NV</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-400">
                                <tr v-for="(cls, index) in sysUpcomingClasses" :key="index" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800">{{ cls.code }}</td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800">{{ cls.name }}</td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800">{{ cls.course }}</td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800">{{ cls.department }}</td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800">{{ cls.date }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ cls.students }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-center">
                        <Link :href="route('system.classes.index')" class="text-[#0ea5e9] hover:underline font-medium text-sm uppercase">
                            [ XEM THÊM ]
                        </Link>
                    </div>
                </div>

            </div>
        </div>

        <div v-else-if="user.role === 2" class="py-6">
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
                        <p class="text-3xl font-bold text-blue-700">{{ deptStats.total_requests }}</p>
                        <p class="text-sm font-medium text-gray-600 mt-1">Tổng yêu cầu đã gửi</p>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-100 p-4 rounded-lg text-center shadow-sm">
                        <p class="text-3xl font-bold text-yellow-600">{{ deptStats.pending_requests }}</p>
                        <p class="text-sm font-medium text-gray-600 mt-1">Đang chờ duyệt</p>
                    </div>
                    <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-lg text-center shadow-sm">
                        <p class="text-3xl font-bold text-indigo-700">{{ deptStats.opened_courses }}</p>
                        <p class="text-sm font-medium text-gray-600 mt-1">Khóa học đã được mở</p>
                    </div>
                    <div class="bg-green-50 border border-green-100 p-4 rounded-lg text-center shadow-sm">
                        <p class="text-3xl font-bold text-green-700">{{ deptStats.participated_employees }}</p>
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
                            <tr v-for="(req, index) in deptRecentRequests" :key="index" class="hover:bg-gray-50">
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
                            <tr v-for="(course, index) in deptActiveCourses" :key="index" class="hover:bg-gray-50">
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

        <div v-else-if="user.role === 3" class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-8 border-b border-gray-300 pb-4">
                    <h1 class="text-2xl font-bold text-gray-800 mb-1">Xin chào, {{ user.name }} 👋</h1>
                    <p class="text-[15px] font-bold text-gray-800">Phòng ban: Kinh doanh</p>
                </div>

                <div class="mb-10">
                    <h3 class="text-base font-bold text-gray-800 mb-4 uppercase">TỔNG QUAN HỌC TẬP</h3>
                    <div class="flex gap-8">
                        <div class="border border-gray-300 w-48 h-32 flex flex-col items-center justify-center rounded-sm shadow-sm bg-white">
                            <p class="text-sm font-bold text-gray-600 mb-1">ĐANG HỌC</p>
                            <p class="text-3xl font-extrabold text-[#d97706] mb-1">{{ employeeData.overview.learning }}</p>
                            <p class="text-sm font-bold text-gray-800">Lớp học</p>
                        </div>
                        <div class="border border-gray-300 w-48 h-32 flex flex-col items-center justify-center rounded-sm shadow-sm bg-white">
                            <p class="text-sm font-bold text-gray-600 mb-1">SẮP HỌC</p>
                            <p class="text-3xl font-extrabold text-[#d97706] mb-1">{{ employeeData.overview.upcoming }}</p>
                            <p class="text-sm font-bold text-gray-800">Lớp học</p>
                        </div>
                    </div>
                </div>

                <div class="mb-10">
                    <h3 class="text-base font-bold text-gray-800 mb-4 uppercase">Lớp học sắp diễn ra</h3>
                    <div class="space-y-4">
                        <div v-for="cls in employeeData.upcomingClasses" :key="cls.id" class="bg-white border border-gray-300 p-5 rounded-sm shadow-sm relative">
                            <p class="text-sm font-bold text-gray-800 mb-3">{{ cls.date }}</p>
                            <p class="text-base font-bold text-gray-800 mb-2">{{ cls.title }}</p>
                            <p class="text-[14px] text-gray-700">
                                {{ cls.time }} | {{ cls.format }} <span v-if="cls.instructor">| GV: {{ cls.instructor }}</span>
                            </p>
                            
                            <button v-if="cls.canJoin" class="absolute right-5 bottom-5 bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] font-bold py-1.5 px-6 rounded-sm shadow-sm transition text-sm">
                                {{ cls.action }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mb-10">
                    <h3 class="text-base font-bold text-gray-800 mb-4 uppercase">Lớp đang học</h3>
                    <div class="space-y-4">
                        <div v-for="cls in employeeData.inProgressClasses" :key="cls.id" class="bg-white border border-gray-300 p-5 rounded-sm shadow-sm relative pr-40">
                            <p class="text-[14px] font-bold text-gray-800 mb-1">{{ cls.course }}</p>
                            <p class="text-[15px] font-bold text-gray-800 mb-4">{{ cls.class }}</p>
                            
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-bold text-gray-800">Tiến độ:</span>
                                <div class="w-64 bg-gray-200 rounded-full h-3">
                                    <div class="bg-gray-800 h-3 rounded-full" :style="{ width: cls.progress + '%' }"></div>
                                </div>
                                <span class="text-sm font-bold text-gray-800">{{ cls.progress }}%</span>
                            </div>

                            <button class="absolute right-5 bottom-5 bg-[#bae6fd] hover:bg-[#7dd3fc] text-gray-900 border border-[#7dd3fc] font-bold py-1.5 px-6 rounded-sm shadow-sm transition text-sm">
                                Tiếp tục học
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div v-else class="py-12 text-center text-gray-500">
            <p>Trang chủ cho Vai trò này đang được xây dựng...</p>
        </div>

    </AuthenticatedLayout>
</template>