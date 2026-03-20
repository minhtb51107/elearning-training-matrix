<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// 1. NHẬN DỮ LIỆU THẬT TỪ CONTROLLER
const props = defineProps({
    classes: Object,       // Danh sách lớp học (có phân trang)
    courses: Array,        // Danh sách khóa học (để filter)
    departments: Array,    // Danh sách phòng ban (để filter)
    filters: Object,       // Các filter đang được áp dụng
});

// 2. KHỞI TẠO STATE BỘ LỌC
const searchForm = ref({
    tab: props.filters?.tab || 'all', // Đã sửa 'Tất Cả' thành 'all' để match với Backend
    course_id: props.filters?.course_id || '',
    department_id: props.filters?.department_id || '',
    keyword: props.filters?.keyword || '',
});

// 3. AUTO-FILTER (Tự động gọi backend khi người dùng thay đổi bộ lọc)
let searchTimeout = null;
watch(searchForm, (newValue) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('system.classes.index'), newValue, { 
            preserveState: true, 
            replace: true, 
            preserveScroll: true 
        });
    }, 300);
}, { deep: true });

// 4. DANH SÁCH TABS
// (Lưu ý: Đã sửa id: 'all' để match với tham số gửi đi)
const tabs = [
    { id: 'all', name: 'Tất Cả' }, 
    { id: 'Nháp', name: 'Nháp' },
    { id: 'Mở đăng ký', name: 'Mở đăng ký' },
    { id: 'Đang học', name: 'Đang học' },
    { id: 'Kết thúc', name: 'Kết thúc' },
];

// 5. HELPER: FORMAT THỜI GIAN
const formatTimeRange = (start, end) => {
    if (!start || !end) return '--';
    const startDate = new Date(start).toLocaleDateString('vi-VN');
    const endDate = new Date(end).toLocaleDateString('vi-VN');
    return `${startDate} - ${endDate}`;
};

// 6. HELPER: MÀU SẮC TRẠNG THÁI
const getStatusClass = (status) => {
    const classes = {
        'Nháp': 'text-gray-500 font-bold',
        'Mở đăng ký': 'text-[#d97706] font-bold', // Vàng cam
        'Đang học': 'text-[#16a34a] font-bold',   // Xanh lá
        'Kết thúc': 'text-blue-600 font-bold',    // Xanh dương
    };
    return classes[status] || 'text-gray-800 font-bold';
};
</script>

<template>
    <Head title="Quản lý lớp học" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                    {{ $page.props.flash.success }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-8">Quản lý lớp học</h2>

                    <div class="flex items-center gap-4 mb-8 text-[15px]">
                        <template v-for="(tab, index) in tabs" :key="tab.id">
                            <button 
                                @click="searchForm.tab = tab.id"
                                :class="['transition', searchForm.tab === tab.id ? 'text-gray-900 font-extrabold border-b-[3px] border-gray-900 pb-0.5' : 'text-gray-500 hover:text-gray-800 font-medium']"
                            >
                                {{ tab.name }}
                            </button>
                            <span v-if="index < tabs.length - 1" class="text-gray-300 font-bold">|</span>
                        </template>
                    </div>

                    <div class="mb-8 border-t border-gray-200 pt-6">
                        <label class="block text-base font-bold text-gray-800 mb-4">Bộ lọc:</label>
                        <div class="grid grid-cols-4 gap-6">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Khóa học:</label>
                                <select v-model="searchForm.course_id" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 text-gray-800">
                                    <option value="">-- Tất cả khóa học --</option>
                                    <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Phạm vi:</label>
                                <select v-model="searchForm.department_id" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 text-gray-800">
                                    <option value="">-- Toàn công ty --</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Thời gian:</label>
                                <input type="text" placeholder="Chọn thời gian..." class="w-full bg-gray-50 border-gray-400 rounded-sm text-sm focus:ring-gray-500 text-gray-500 cursor-not-allowed" disabled>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Từ khóa:</label>
                                <input v-model="searchForm.keyword" type="text" placeholder="Mã lớp, Tên lớp..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 text-gray-800">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center mb-6">
                        <Link :href="route('system.classes.create')" class="text-[#d97706] hover:text-orange-700 font-bold text-[15px] uppercase tracking-wide">
                            [ + TẠO LỚP HỌC MỚI ]
                        </Link>
                    </div>

                    <h3 class="text-base font-bold text-gray-800 mb-3">Danh sách lớp học:</h3>
                    
                    <div class="overflow-x-auto border border-gray-300">
                        <table class="min-w-full divide-y divide-gray-300 text-center text-[13px]">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Mã lớp học</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300 text-left">Tên lớp học</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300 text-left">Khóa học</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Giảng viên</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Thời gian</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Số lượng</th>
                                    <th class="px-3 py-3 font-bold text-gray-900">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-300">
                                <tr v-if="!classes || !classes.data || classes.data.length === 0">
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 italic">Không có dữ liệu lớp học phù hợp.</td>
                                </tr>
                                
                                <tr v-for="cls in classes?.data" :key="cls.id" class="hover:bg-gray-50 transition cursor-pointer" @click="router.get(route('system.classes.show', cls.id))">
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700 font-bold">{{ cls.code }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-blue-700 font-bold text-left hover:underline">{{ cls.name }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700 text-left">{{ cls.course?.name || '--' }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ cls.instructor?.name || 'Chưa phân công' }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ formatTimeRange(cls.start_date, cls.end_date) }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">0 / {{ cls.max_students }}</td>
                                    <td class="px-3 py-3 uppercase text-[11px]" :class="getStatusClass(cls.status)">{{ cls.status }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="classes?.links?.length > 3" class="mt-6 flex justify-center items-center gap-2 text-sm text-[#0ea5e9] font-medium">
                        <template v-for="(link, index) in classes.links" :key="index">
                            <Link 
                                v-if="link.url"
                                :href="link.url" 
                                class="w-7 h-7 rounded flex items-center justify-center transition"
                                :class="link.active ? 'bg-blue-100 font-bold text-gray-900' : 'hover:bg-gray-100 text-gray-600'"
                                v-html="link.label"
                            />
                            <span v-else class="w-7 h-7 flex items-center justify-center text-gray-400" v-html="link.label"></span>
                        </template>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>