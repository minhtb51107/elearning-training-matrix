<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// MOCK DATA
const allCourses = ref([
    { id: 1, code: 'TRN-2026-SALES-01', name: 'Kỹ năng bán hàng chuyên sâu', time: '', source: 'YC', scope: 'Phòng Kinh doanh', total_classes: 2, active_classes: 1, status: 'ĐANG MỞ' },
    { id: 2, code: 'TRN-2026-IT-02', name: 'DevOps cơ bản', time: '', source: 'Nội bộ', scope: 'Toàn công ty', total_classes: 0, active_classes: 0, status: 'CHƯA CÓ LỚP' },
    { id: 3, code: 'TRN-2026-HR-01', name: 'Tuyển dụng hiệu quả', time: '', source: 'YC', scope: 'Phòng HR', total_classes: 1, active_classes: 0, status: 'ĐÃ KẾT THÚC' },
    { id: 4, code: 'TRN-2026-MKT-01', name: 'Branding nội bộ', time: '', source: 'Nội bộ', scope: 'Marketing', total_classes: 1, active_classes: 1, status: 'ĐANG TRIỂN KHAI' },
]);

const activeTab = ref('Tất Cả');

const tabs = computed(() => {
    return [
        { name: 'Tất Cả', count: allCourses.value.length },
        { name: 'Chưa có lớp', count: allCourses.value.filter(c => c.status === 'CHƯA CÓ LỚP').length },
        { name: 'Đang mở', count: allCourses.value.filter(c => c.status === 'ĐANG MỞ').length },
        { name: 'Đang triển khai', count: allCourses.value.filter(c => c.status === 'ĐANG TRIỂN KHAI').length },
        { name: 'Đã kết thúc', count: allCourses.value.filter(c => c.status === 'ĐÃ KẾT THÚC').length },
    ];
});

const filteredCourses = computed(() => {
    if (activeTab.value === 'Tất Cả') return allCourses.value;
    return allCourses.value.filter(c => c.status.toLowerCase() === activeTab.value.toLowerCase());
});

const getActionLink = (status) => {
    if (status === 'CHƯA CÓ LỚP') return 'Tạo lớp';
    if (status === 'ĐÃ KẾT THÚC') return 'Thống kê';
    return 'Xem chi tiết';
};

// HÀM XỬ LÝ CLICK VÀO DÒNG -> RẼ NHÁNH ROUTE THEO TRẠNG THÁI
const goToDetail = (course) => {
    if (course.status === 'ĐÃ KẾT THÚC') {
        router.get(route('system.courses.statistics', course.id));
    } else {
        router.get(route('system.courses.show', course.id));
    }
};
</script>

<template>
    <Head title="Quản lý khóa học" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-8">Quản lý khóa học</h2>

                    <div class="flex items-center gap-4 mb-10 text-[15px]">
                        <template v-for="(tab, index) in tabs" :key="tab.name">
                            <button 
                                @click="activeTab = tab.name"
                                :class="['transition', activeTab === tab.name ? 'text-gray-900 font-extrabold border-b-2 border-gray-900 pb-0.5' : 'text-gray-500 hover:text-gray-800 font-medium']"
                            >
                                {{ tab.name }} ({{ tab.count }})
                            </button>
                            <span v-if="index < tabs.length - 1" class="text-gray-300 font-bold">|</span>
                        </template>
                    </div>

                    <div class="mb-8">
                        <label class="block text-base font-bold text-gray-800 mb-2">Bộ lọc:</label>
                        <div class="grid grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Thời gian:</label>
                                <input type="date" class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500 text-gray-500" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Phạm vi:</label>
                                <select class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500">
                                    <option>Phòng kinh doanh</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Nguồn tạo:</label>
                                <select class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500">
                                    <option>Yêu cầu đào tạo</option>
                                    <option>Nội bộ</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Tình trạng lớp:</label>
                                <select class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500">
                                    <option>Có lớp</option>
                                    <option>Chưa có lớp</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Từ khóa:</label>
                                <input type="text" placeholder="Nhập và chọn" class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500" />
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
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Thời gian</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Nguồn</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Phạm vi</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Số lớp</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Lớp đang mở</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Trạng thái</th>
                                    <th class="px-3 py-3 font-bold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-300">
                                <tr v-if="filteredCourses.length === 0">
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500 italic">Không có dữ liệu trong mục này.</td>
                                </tr>
                                <tr v-for="course in filteredCourses" :key="course.id" class="hover:bg-gray-50 transition cursor-pointer" @click="goToDetail(course)">
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ course.code }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-800 text-left">{{ course.name }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ course.time }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ course.source }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ course.scope }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ course.total_classes }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ course.active_classes }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 font-bold text-gray-800">{{ course.status }}</td>
                                    <td class="px-3 py-3 min-w-[100px]">
                                        <Link @click.stop :href="course.status === 'ĐÃ KẾT THÚC' ? route('system.courses.statistics', course.id) : route('system.courses.show', course.id)" class="text-[#0ea5e9] hover:underline text-sm font-medium">
                                            {{ getActionLink(course.status) }}
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-center items-center gap-4 text-sm text-[#0ea5e9] font-medium">
                        <button class="hover:underline">&lt; Prev</button>
                        <button class="w-7 h-7 rounded bg-blue-100 font-bold flex items-center justify-center">1</button>
                        <button class="w-7 h-7 rounded hover:bg-gray-100 flex items-center justify-center text-gray-600">2</button>
                        <button class="w-7 h-7 rounded hover:bg-gray-100 flex items-center justify-center text-gray-600">3</button>
                        <button class="hover:underline">Next &gt;</button>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>