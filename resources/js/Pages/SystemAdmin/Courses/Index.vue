<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// NHẬN DỮ LIỆU THẬT TỪ BACKEND
const props = defineProps({
    courses: Object,
    departments: Array, // Nhận mảng phòng ban từ Controller
    filters: Object,
});

// FORM TÌM KIẾM & LỌC
const searchForm = ref({
    tab: props.filters?.tab || 'all',
    date: props.filters?.date || '',
    scope: props.filters?.scope || '',
    source: props.filters?.source || '',
    status: props.filters?.status || '',
    keyword: props.filters?.keyword || '',
});

// TỰ ĐỘNG LỌC SAU KHI NGỪNG GÕ/CHỌN 300ms
let searchTimeout = null;
watch(searchForm, (newValue) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('system.courses.index'), newValue, { preserveState: true, replace: true, preserveScroll: true });
    }, 300);
}, { deep: true });

// DANH SÁCH TABS
const tabs = [
    { id: 'all', name: 'Tất Cả' },
    { id: 'Chưa có lớp', name: 'Chưa có lớp' },
    { id: 'Đang mở', name: 'Đang mở' },
    { id: 'Đang triển khai', name: 'Đang triển khai' },
    { id: 'Đã kết thúc', name: 'Đã kết thúc' },
];

const getActionLink = (status) => {
    if (status === 'Chưa có lớp') return 'Tạo lớp';
    if (status === 'Đã kết thúc') return 'Thống kê';
    return 'Xem chi tiết';
};

// HÀM XỬ LÝ CLICK VÀO DÒNG -> RẼ NHÁNH ROUTE THEO TRẠNG THÁI
const goToDetail = (course) => {
    if (course.status === 'Đã kết thúc') {
        router.get(route('system.courses.statistics', course.id));
    } else if (course.status === 'Chưa có lớp') {
        router.get(route('system.classes.create', { course_id: course.id }));
    } else {
        router.get(route('system.courses.show', course.id));
    }
};

// ĐỔ MÀU TRẠNG THÁI
const getStatusClass = (status) => {
    const classes = {
        'Chưa có lớp': 'text-gray-500 font-bold',
        'Đang mở': 'text-[#16a34a] font-bold',  // Xanh lá
        'Đang triển khai': 'text-[#d97706] font-bold', // Vàng cam
        'Đã kết thúc': 'text-blue-600 font-bold',
    };
    return classes[status] || 'text-gray-800 font-bold';
};
</script>

<template>
    <Head title="Quản lý khóa học" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                    {{ $page.props.flash.success }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-8 border-b pb-4">Quản lý khóa học</h2>

                    <div class="flex items-center gap-4 mb-10 text-[15px]">
                        <template v-for="(tab, index) in tabs" :key="tab.id">
                            <button 
                                @click="searchForm.tab = tab.id"
                                :class="['transition', searchForm.tab === tab.id ? 'text-gray-900 font-extrabold border-b-2 border-gray-900 pb-0.5' : 'text-gray-500 hover:text-gray-800 font-medium']"
                            >
                                {{ tab.name }}
                            </button>
                            <span v-if="index < tabs.length - 1" class="text-gray-300 font-bold">|</span>
                        </template>
                    </div>

                    <div class="mb-8 bg-gray-50 p-4 rounded border border-gray-200">
                        <label class="block text-base font-bold text-gray-800 mb-2">Bộ lọc:</label>
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Thời gian tạo:</label>
                                <input v-model="searchForm.date" type="date" class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500 text-gray-700" />
                            </div>
                            
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Phạm vi:</label>
                                <select v-model="searchForm.scope" class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500">
                                    <option value="">-- Tất cả --</option>
                                    <option value="Toàn công ty">Toàn công ty</option>
                                    <option value="Cấp quản lý">Cấp quản lý</option>
                                    <option value="Nhân viên mới">Nhân viên mới</option>
                                    <optgroup label="Theo phòng ban">
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.name">
                                            {{ dept.name }}
                                        </option>
                                    </optgroup>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Nguồn tạo:</label>
                                <select v-model="searchForm.source" class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500">
                                    <option value="">-- Tất cả --</option>
                                    <option value="Yêu cầu">Yêu cầu đào tạo</option>
                                    <option value="Nội bộ">Nội bộ</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Tình trạng lớp:</label>
                                <select v-model="searchForm.status" class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500">
                                    <option value="">-- Tất cả --</option>
                                    <option value="Có lớp">Đã có lớp</option>
                                    <option value="Chưa có lớp">Chưa có lớp</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Từ khóa (Mã, Tên KH):</label>
                                <input v-model="searchForm.keyword" type="text" placeholder="Nhập và chọn" class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center mb-6">
                        <Link :href="route('system.courses.create')" class="text-[#d97706] hover:text-orange-700 font-bold text-[15px] uppercase tracking-wide">
                            [ TẠO KHÓA HỌC MỚI ]
                        </Link>
                    </div>

                    <h3 class="text-base font-bold text-gray-800 mb-3">Danh sách khóa học:</h3>
                    
                    <div class="overflow-x-auto border border-gray-300">
                        <table class="min-w-full divide-y divide-gray-300 text-center text-[13px]">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Mã KH</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300 text-left">Tên khóa học</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Thời lượng</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Nguồn</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Phạm vi</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Số lớp</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Lớp đang mở</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Trạng thái</th>
                                    <th class="px-3 py-3 font-bold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-300">
                                <tr v-if="!courses || !courses.data || courses.data.length === 0">
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500 italic">Không có dữ liệu phù hợp với bộ lọc.</td>
                                </tr>
                                
                                <tr v-for="course in courses?.data" :key="course.id" class="hover:bg-gray-50 transition cursor-pointer" @click="goToDetail(course)">
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700 font-medium">{{ course.code }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-blue-700 font-bold text-left hover:underline">{{ course.name }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ course.duration ? course.duration + 'h' : '--' }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ course.reason ? 'Nội bộ' : 'Yêu cầu' }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ course.target_audience }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">0</td> <td class="px-3 py-3 border-r border-gray-300 text-gray-700">0</td> <td class="px-3 py-3 border-r border-gray-300 uppercase" :class="getStatusClass(course.status)">{{ course.status }}</td>
                                    <td class="px-3 py-3 min-w-[100px]">
                                        <Link @click.stop 
                                            :href="course.status === 'Đã kết thúc' ? route('system.courses.statistics', course.id) : (course.status === 'Chưa có lớp' ? route('system.classes.create', { course_id: course.id }) : route('system.courses.show', course.id))" 
                                            class="text-[#0ea5e9] hover:underline text-sm font-medium">
                                            {{ getActionLink(course.status) }}
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="courses?.links?.length > 3" class="mt-8 flex justify-center items-center gap-2 text-sm text-[#0ea5e9] font-medium">
                        <template v-for="(link, index) in courses.links" :key="index">
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