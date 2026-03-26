<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { 
    DocumentChartBarIcon, 
    ClockIcon, 
    BookOpenIcon, 
    AcademicCapIcon, 
    UserGroupIcon,
    MagnifyingGlassIcon
} from '@heroicons/vue/24/outline';

// DÙNG USEPAGE() ĐỂ LẤY TRỰC TIẾP DỮ LIỆU TỪ CONTROLLER MÀ KHÔNG CẦN PROPS TỪ COMPONENT CHA
const page = usePage();

// LẤY DỮ LIỆU BỘ LỌC
const filters = computed(() => page.props.filters || {});
const searchKeyword = ref(filters.value.keyword || '');
const filterFromDate = ref(filters.value.from_date || '');
const filterToDate = ref(filters.value.to_date || '');

const doFilter = () => {
    router.get(route('dashboard'), {
        keyword: searchKeyword.value,
        from_date: filterFromDate.value,
        to_date: filterToDate.value
    }, { preserveState: true, replace: true });
};

// LẤY DỮ LIỆU THỐNG KÊ (Fallback về 0 nếu chưa có dữ liệu)
const dashboardData = computed(() => page.props.dashboardData || {});

const sysStats = computed(() => dashboardData.value.sysStats || { 
    total_requests: 0, pending_requests: 0, created_courses: 0, opened_classes: 0, participated_employees: 0 
});

const sysPendingRequests = computed(() => dashboardData.value.sysPendingRequests || []);
const sysUpcomingClasses = computed(() => dashboardData.value.sysUpcomingClasses || []);
</script>

<template>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900">Tổng quan hệ thống đào tạo</h2>
                <p class="text-sm text-gray-500 mt-1">Báo cáo tóm tắt các hoạt động đào tạo của toàn bộ công ty.</p>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-8 flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full relative">
                    <label class="block text-[13px] font-medium text-gray-700 mb-1">Tìm kiếm phòng ban / khóa học</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <MagnifyingGlassIcon class="h-4 w-4 text-gray-400" />
                        </div>
                        <input v-model="searchKeyword" @keyup.enter="doFilter" type="text" placeholder="Nhập từ khóa và Enter..." class="pl-9 w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                    </div>
                </div>
                <div class="w-full md:w-auto">
                    <label class="block text-[13px] font-medium text-gray-700 mb-1">Từ ngày</label>
                    <input v-model="filterFromDate" @change="doFilter" type="date" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm text-gray-600">
                </div>
                <div class="w-full md:w-auto">
                    <label class="block text-[13px] font-medium text-gray-700 mb-1">Đến ngày</label>
                    <input v-model="filterToDate" @change="doFilter" type="date" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm text-gray-600">
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-10">
                <div class="bg-white border border-gray-200 p-5 rounded-2xl shadow-sm relative overflow-hidden group hover:border-orange-300 transition-colors">
                    <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:scale-110 transition-transform"><DocumentChartBarIcon class="w-14 h-14 text-orange-600"/></div>
                    <p class="text-3xl font-black text-gray-900 mb-1">{{ sysStats.total_requests }}</p>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tổng Yêu Cầu</p>
                </div>
                
                <div class="bg-orange-50/50 border border-orange-200 p-5 rounded-2xl shadow-sm relative overflow-hidden group hover:border-orange-400 transition-colors">
                    <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:scale-110 transition-transform"><ClockIcon class="w-14 h-14 text-orange-600"/></div>
                    <p class="text-3xl font-black text-orange-600 mb-1">{{ sysStats.pending_requests }}</p>
                    <p class="text-xs font-semibold text-orange-700 uppercase tracking-wider">Đang chờ duyệt</p>
                </div>
                
                <div class="bg-white border border-gray-200 p-5 rounded-2xl shadow-sm relative overflow-hidden group hover:border-green-300 transition-colors">
                    <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:scale-110 transition-transform"><BookOpenIcon class="w-14 h-14 text-green-600"/></div>
                    <p class="text-3xl font-black text-gray-900 mb-1">{{ sysStats.created_courses }}</p>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Khóa học đã tạo</p>
                </div>

                <div class="bg-green-50/50 border border-green-200 p-5 rounded-2xl shadow-sm relative overflow-hidden group hover:border-green-400 transition-colors">
                    <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:scale-110 transition-transform"><AcademicCapIcon class="w-14 h-14 text-green-600"/></div>
                    <p class="text-3xl font-black text-green-600 mb-1">{{ sysStats.opened_classes }}</p>
                    <p class="text-xs font-semibold text-green-700 uppercase tracking-wider">Lớp đang mở</p>
                </div>
                
                <div class="bg-white border border-gray-200 p-5 rounded-2xl shadow-sm relative overflow-hidden group col-span-2 lg:col-span-1 hover:border-blue-300 transition-colors">
                    <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:scale-110 transition-transform"><UserGroupIcon class="w-14 h-14 text-blue-600"/></div>
                    <p class="text-3xl font-black text-gray-900 mb-1">{{ sysStats.participated_employees }}</p>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">NV đã tham gia</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-base font-bold text-gray-800">Yêu cầu đào tạo cần xử lý</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Phòng ban</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tên yêu cầu</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ngày gửi</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-if="sysPendingRequests.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500 bg-gray-50/30">Không có yêu cầu nào đang chờ xử lý.</td>
                            </tr>
                            <tr v-for="(req, index) in sysPendingRequests" :key="index" class="hover:bg-blue-50/50 transition-colors">
                                <td class="px-6 py-4 font-semibold text-blue-700">{{ req.department }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ req.name }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ req.date }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-yellow-50 text-yellow-700 border-yellow-200">
                                        {{ req.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-center">
                    <Link :href="route('system.requests.index')" class="text-blue-600 hover:underline font-bold text-sm">
                        Xem tất cả yêu cầu &rarr;
                    </Link>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-base font-bold text-gray-800">Lớp học sắp diễn ra / Đang học</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mã lớp</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Lớp / Khóa học</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Phòng ban</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ngày học</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Học viên</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-if="sysUpcomingClasses.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 bg-gray-50/30">Chưa có lớp học nào sắp diễn ra.</td>
                            </tr>
                            <tr v-for="(cls, index) in sysUpcomingClasses" :key="index" class="hover:bg-blue-50/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ cls.code }}</td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-blue-600">{{ cls.name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ cls.course }}</p>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ cls.department }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ cls.date }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 bg-gray-100 text-gray-800 rounded text-xs font-bold font-mono">
                                        {{ cls.students }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-center">
                    <Link :href="route('system.classes.index')" class="text-blue-600 hover:underline font-bold text-sm">
                        Quản lý lớp học &rarr;
                    </Link>
                </div>
            </div>

        </div>
    </div>
</template>