<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, CheckIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    classes: Object,       
    courses: Array,        
    departments: Array,    
    filters: Object,       
});

// THÊM start_date VÀ end_date VÀO BỘ LỌC
const searchForm = ref({
    tab: props.filters?.tab || 'all', 
    course_id: props.filters?.course_id || '',
    department_id: props.filters?.department_id || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    keyword: props.filters?.keyword || '',
});

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

const tabs = [
    { id: 'all', name: 'Tất Cả' }, 
    { id: 'Nháp', name: 'Nháp' },
    { id: 'Mở đăng ký', name: 'Mở đăng ký' },
    { id: 'Đang học', name: 'Đang học' },
    { id: 'Kết thúc', name: 'Kết thúc' },
];

const formatTimeRange = (start, end) => {
    if (!start || !end) return '--';
    const startDate = new Date(start).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: '2-digit' });
    const endDate = new Date(end).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: '2-digit' });
    return `${startDate} - ${endDate}`;
};

const getStatusBadge = (status) => {
    const badges = {
        'Nháp': 'bg-gray-100 text-gray-600 border-gray-200',
        'Mở đăng ký': 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'Đang học': 'bg-green-50 text-green-700 border-green-200',
        'Kết thúc': 'bg-blue-50 text-blue-700 border-blue-200',
    };
    return badges[status] || 'bg-gray-50 text-gray-600 border-gray-200';
};

// HÀM XÓA LỚP HỌC
const deleteClass = (id) => {
    if (confirm('Bạn có chắc chắn muốn xóa lớp học này không? Hành động này không thể hoàn tác!')) {
        router.delete(route('system.classes.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Quản lý lớp học" />

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
                            <h2 class="text-xl font-bold text-gray-900">Quản lý Lớp học</h2>
                            <p class="text-sm text-gray-500 mt-1">Tổ chức và theo dõi tiến độ của các lớp đang diễn ra.</p>
                        </div>
                        <Link :href="route('system.classes.create')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-sm shadow-blue-600/30">
                            <PlusIcon class="w-5 h-5" />
                            Tạo lớp học mới
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
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Khóa học</label>
                                <select v-model="searchForm.course_id" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    <option value="">-- Tất cả khóa học --</option>
                                    <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Phạm vi Phòng ban</label>
                                <select v-model="searchForm.department_id" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    <option value="">-- Toàn công ty --</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Từ ngày</label>
                                <input v-model="searchForm.start_date" type="date" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm text-gray-700">
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Đến ngày</label>
                                <input v-model="searchForm.end_date" type="date" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm text-gray-700">
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Tìm kiếm</label>
                                <input v-model="searchForm.keyword" type="text" placeholder="Mã lớp, Tên lớp..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Mã Lớp</th>
                                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Lớp / Khóa học</th>
                                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Giảng viên</th>
                                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Thời gian</th>
                                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Sĩ số</th>
                                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Trạng thái</th>
                                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="!classes || !classes.data || classes.data.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 bg-gray-50/30">
                                        Không tìm thấy lớp học nào phù hợp với bộ lọc.
                                    </td>
                                </tr>
                                
                                <tr v-for="cls in classes?.data" :key="cls.id" class="hover:bg-blue-50/50 transition-colors cursor-pointer" @click="router.get(route('system.classes.show', cls.id))">
                                    <td class="px-5 py-4 font-semibold text-gray-900">{{ cls.code }}</td>
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-blue-600 hover:text-blue-800 transition-colors">{{ cls.name }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5 truncate max-w-[200px]" :title="cls.course?.name">{{ cls.course?.name || '--' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-gray-700">{{ cls.instructor?.name || 'Chưa phân công' }}</td>
                                    <td class="px-5 py-4 text-gray-600 text-xs font-medium">{{ formatTimeRange(cls.start_date, cls.end_date) }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center justify-center px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs font-bold font-mono">
                                            {{ cls.enrollments_count || cls.enrollments?.length || 0 }} / {{ cls.max_students }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wide border', getStatusBadge(cls.status)]">
                                            {{ cls.status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="flex justify-center gap-3 items-center">
                                            <Link @click.stop :href="route('system.classes.show', cls.id)" class="text-blue-600 hover:text-blue-800 hover:underline text-[13px] font-medium">
                                                Chi tiết
                                            </Link>
                                            <Link @click.stop :href="route('system.classes.edit', cls.id)" class="text-amber-600 hover:text-amber-800 hover:underline text-[13px] font-medium">
                                                Sửa
                                            </Link>
                                            <button @click.stop="deleteClass(cls.id)" class="text-red-600 hover:text-red-800 hover:underline text-[13px] font-medium">
                                                Xóa
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="classes?.links?.length > 3" class="mt-6 flex justify-between items-center text-sm text-gray-600">
                        <div>Hiển thị trang hiện tại</div>
                        <div class="flex gap-1">
                            <Component v-for="(link, index) in classes.links" :key="index"
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