<script setup>
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    dashboardData: Object,
    filters: Object // Nhận bộ lọc từ trang cha
});

// BIẾN LƯU TRỮ TRẠNG THÁI BỘ LỌC
const searchKeyword = ref(props.filters?.keyword || '');
const filterFromDate = ref(props.filters?.from_date || '');
const filterToDate = ref(props.filters?.to_date || '');

// Hàm gửi yêu cầu lọc lên server
const doFilter = () => {
    router.get(route('dashboard'), {
        keyword: searchKeyword.value,
        from_date: filterFromDate.value,
        to_date: filterToDate.value
    }, { preserveState: true, replace: true });
};

// 1. Lấy dữ liệu thống kê
const deptStats = computed(() => props.dashboardData?.deptStats || { 
    total_requests: 0, pending_requests: 0, opened_courses: 0, participated_employees: 0 
});

// Helper xử lý trạng thái
const getRequestStatusText = (status) => {
    const map = { 'draft': 'Nháp', 'pending': 'Đang chờ duyệt', 'approved': 'Đã duyệt', 'processed': 'Đã xử lý', 'rejected': 'Từ chối' };
    return map[status] || status;
};

const getRequestStatusClass = (status) => {
    const map = {
        'draft': 'text-gray-600 bg-gray-50 px-2 py-1 rounded',
        'pending': 'text-yellow-600 bg-yellow-50 px-2 py-1 rounded',
        'approved': 'text-green-600 bg-green-50 px-2 py-1 rounded',
        'processed': 'text-blue-600 bg-blue-50 px-2 py-1 rounded',
        'rejected': 'text-red-600 bg-red-50 px-2 py-1 rounded'
    };
    return map[status] || 'text-gray-600 bg-gray-50 px-2 py-1 rounded';
};

// 2. Chuyển đổi dữ liệu
const deptRecentRequests = computed(() => {
    const requests = props.dashboardData?.deptRecentRequests || [];
    return requests.map(req => ({
        name: req.name,
        date: req.date,
        status: getRequestStatusText(req.status),
        statusClass: getRequestStatusClass(req.status)
    }));
});

const deptActiveCourses = computed(() => {
    const courses = props.dashboardData?.deptActiveCourses || [];
    return courses.map(course => ({
        name: course.name,
        time: course.time,
        classes: course.classes,
        employees: course.employees,
        status: course.status,
        statusClass: course.status === 'Đang mở' ? 'text-green-600' : 'text-gray-600'
    }));
});
</script>

<template>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800 uppercase">Tổng quan đào tạo phòng ban</h2>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6 flex flex-wrap gap-6 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm khóa học/yêu cầu:</label>
                    <input v-model="searchKeyword" @keyup.enter="doFilter" type="text" placeholder="Nhập từ khóa và Enter..." class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Từ ngày:</label>
                    <input v-model="filterFromDate" @change="doFilter" type="date" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Đến ngày:</label>
                    <input v-model="filterToDate" @change="doFilter" type="date" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-600">
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
                    <p class="text-sm font-medium text-gray-600 mt-1">Khóa học đã phân bổ</p>
                </div>
                <div class="bg-green-50 border border-green-100 p-4 rounded-lg text-center shadow-sm">
                    <p class="text-3xl font-bold text-green-700">{{ deptStats.participated_employees }}</p>
                    <p class="text-sm font-medium text-gray-600 mt-1">NV đã tham gia đào tạo</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Yêu cầu đào tạo:</h3>
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
                        <tr v-if="deptRecentRequests.length === 0">
                            <td colspan="3" class="px-6 py-6 text-center text-gray-500 italic">Chưa có yêu cầu nào phù hợp.</td>
                        </tr>
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
                        <tr v-if="deptActiveCourses.length === 0">
                            <td colspan="5" class="px-6 py-6 text-center text-gray-500 italic">Chưa có khóa học nào phù hợp.</td>
                        </tr>
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
</template>