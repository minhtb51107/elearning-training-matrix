<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, CheckIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    courses: Object,
    departments: Array, 
    filters: Object,
});

const searchForm = ref({
    tab: props.filters?.tab || 'all',
    date: props.filters?.date || '',
    scope: props.filters?.scope || '',
    source: props.filters?.source || '',
    status: props.filters?.status || '',
    keyword: props.filters?.keyword || '',
});

let searchTimeout = null;
watch(searchForm, (newValue) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('system.courses.index'), newValue, { preserveState: true, replace: true, preserveScroll: true });
    }, 300);
}, { deep: true });

const tabs = [
    { id: 'all', name: 'Tất Cả' },
    { id: 'Chưa có lớp', name: 'Chưa có lớp' },
    { id: 'Đang mở', name: 'Đang mở' },
    { id: 'Đang triển khai', name: 'Đang triển khai' },
    { id: 'Đã kết thúc', name: 'Đã kết thúc' },
];

const getActionLink = (status) => {
    if (status === 'Chưa có lớp') return 'Tạo lớp học';
    if (status === 'Đã kết thúc') return 'Thống kê KQ';
    return 'Chi tiết';
};

const goToDetail = (course) => {
    if (course.status === 'Đã kết thúc') {
        router.get(route('system.courses.statistics', course.id));
    } else if (course.status === 'Chưa có lớp') {
        router.get(route('system.classes.create', { course_id: course.id }));
    } else {
        router.get(route('system.courses.show', course.id));
    }
};

const getStatusBadge = (status) => {
    const badges = {
        'Chưa có lớp': 'bg-gray-100 text-gray-600 border-gray-200',
        'Đang mở': 'bg-green-50 text-green-700 border-green-200',
        'Đang triển khai': 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'Đã kết thúc': 'bg-blue-50 text-blue-700 border-blue-200',
    };
    return badges[status] || 'bg-gray-50 text-gray-600 border-gray-200';
};

// HÀM XỬ LÝ XÓA KHÓA HỌC
const deleteCourse = (id) => {
    if (confirm('Bạn có chắc chắn muốn xóa khóa học này không? Toàn bộ tài liệu đính kèm cũng sẽ bị xóa. Hành động này không thể hoàn tác!')) {
        router.delete(route('system.courses.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Quản lý khóa học" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm flex items-center gap-3">
                    <CheckIcon class="w-5 h-5 text-green-500" />
                    <span class="font-medium">{{ $page.props.flash.success }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 p-6 sm:p-8">
                    
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Quản lý Khóa học</h2>
                            <p class="text-sm text-gray-500 mt-1">Danh sách tất cả các chương trình đào tạo của công ty.</p>
                        </div>
                        <Link :href="route('system.courses.create')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-sm shadow-blue-600/30">
                            <PlusIcon class="w-5 h-5" />
                            Tạo khóa học mới
                        </Link>
                    </div>

                    <div class="flex gap-2 mb-6 bg-gray-50/50 p-1.5 rounded-xl border border-gray-100 overflow-x-auto">
                        <button v-for="tab in tabs" :key="tab.id"
                            @click="searchForm.tab = tab.id"
                            :class="[
                                'px-5 py-2 text-sm font-semibold rounded-lg transition-all whitespace-nowrap', 
                                searchForm.tab === tab.id 
                                    ? 'bg-white text-blue-700 shadow-sm border border-gray-200 ring-1 ring-black/5' 
                                    : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100/50'
                            ]">
                            {{ tab.name }}
                        </button>
                    </div>

                    <div class="mb-8 bg-gray-50/80 p-5 rounded-xl border border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Thời gian tạo</label>
                                <input v-model="searchForm.date" type="date" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm text-gray-600" />
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Phạm vi</label>
                                <select v-model="searchForm.scope" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    <option value="">-- Tất cả --</option>
                                    <option value="Toàn công ty">Toàn công ty</option>
                                    <option value="Cấp quản lý">Cấp quản lý</option>
                                    <option value="Nhân viên mới">Nhân viên mới</option>
                                    <optgroup label="Theo phòng ban">
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.name">{{ dept.name }}</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Nguồn tạo</label>
                                <select v-model="searchForm.source" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    <option value="">-- Tất cả --</option>
                                    <option value="Yêu cầu">Từ Yêu cầu ĐT</option>
                                    <option value="Nội bộ">Nội bộ Đề xuất</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Tình trạng lớp</label>
                                <select v-model="searchForm.status" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    <option value="">-- Tất cả --</option>
                                    <option value="Có lớp">Đã có lớp học</option>
                                    <option value="Chưa có lớp">Chưa có lớp</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Tìm kiếm</label>
                                <input v-model="searchForm.keyword" type="text" placeholder="Mã KH, Tên khóa..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm" />
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Mã KH</th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Tên khóa học</th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Nguồn</th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Phạm vi</th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Số lớp</th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Trạng thái</th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="!courses || !courses.data || courses.data.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 bg-gray-50/30">
                                        Không tìm thấy khóa học nào phù hợp với bộ lọc.
                                    </td>
                                </tr>
                                
                                <tr v-for="course in courses?.data" :key="course.id" class="hover:bg-blue-50/50 transition-colors cursor-pointer" @click="goToDetail(course)">
                                    <td class="px-4 py-4 text-gray-500 font-medium text-xs">{{ course.code }}</td>
                                    <td class="px-4 py-4">
                                        <div class="font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">{{ course.name }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ course.duration ? course.duration + ' giờ' : '--' }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-gray-600">{{ course.reason ? 'Nội bộ' : 'Yêu cầu' }}</td>
                                    <td class="px-4 py-4 text-center text-gray-600">{{ course.target_audience }}</td>
                                    <td class="px-4 py-4 text-center font-bold text-gray-700">0</td>
                                    <td class="px-4 py-4 text-center">
                                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border', getStatusBadge(course.status)]">
                                            {{ course.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <div class="flex justify-center gap-3 items-center">
                                            <Link @click.stop :href="course.status === 'Đã kết thúc' ? route('system.courses.statistics', course.id) : (course.status === 'Chưa có lớp' ? route('system.classes.create', { course_id: course.id }) : route('system.courses.show', course.id))" 
                                                  class="text-blue-600 hover:text-blue-800 hover:underline text-[13px] font-medium">
                                                {{ getActionLink(course.status) }}
                                            </Link>
                                            
                                            <Link @click.stop :href="route('system.courses.edit', course.id)" class="text-amber-600 hover:text-amber-800 hover:underline text-[13px] font-medium">
                                                Sửa
                                            </Link>
                                            
                                            <button @click.stop="deleteCourse(course.id)" class="text-red-600 hover:text-red-800 hover:underline text-[13px] font-medium">
                                                Xóa
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="courses?.links?.length > 3" class="mt-6 flex justify-between items-center text-sm text-gray-600">
                        <div>Hiển thị trang hiện tại</div>
                        <div class="flex gap-1">
                            <Component v-for="(link, index) in courses.links" :key="index"
                                       :is="link.url ? 'Link' : 'span'" 
                                       :href="link.url" 
                                       v-html="link.label" 
                                       class="px-3 py-1.5 border rounded-md transition-colors" 
                                       :class="{
                                           'bg-blue-600 text-white border-blue-600 font-medium shadow-sm': link.active, 
                                           'text-gray-400 border-gray-200 bg-gray-50 cursor-not-allowed': !link.url,
                                           'hover:bg-gray-50 text-gray-700 border-gray-300': link.url && !link.active
                                       }" />
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>