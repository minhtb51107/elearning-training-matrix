<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { MagnifyingGlassIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    courses: Object,
    filters: Object
});

// BIẾN CHO BỘ LỌC
const searchKeyword = ref(props.filters?.keyword || '');
const filterFromDate = ref(props.filters?.from_date || '');
const filterToDate = ref(props.filters?.to_date || '');
const filterFormat = ref(props.filters?.format || 'all');
const filterStatus = ref(props.filters?.status || 'all');

const doSearch = () => {
    router.get(route('department.courses.index'), {
        keyword: searchKeyword.value,
        from_date: filterFromDate.value,
        to_date: filterToDate.value,
        format: filterFormat.value,
        status: filterStatus.value
    }, { preserveState: true, replace: true });
};

// HELPER: Format Ngày tháng (từ DB ra DD/MM/YYYY)
const formatDate = (dateString) => {
    if (!dateString) return '--';
    const d = new Date(dateString);
    return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
};

// HELPER: Dịch Trạng thái từ Database ra Tiếng Việt
const getStatusText = (status) => {
    const map = {
        'published': 'Đang mở',
        'draft': 'Đang triển khai',
        'archived': 'Kết thúc'
    };
    return map[status] || status;
};

// HELPER: Badge màu Pastel hiện đại dạng Pill
const getStatusBadgeClass = (dbStatus) => {
    const status = getStatusText(dbStatus);
    if (status === 'Đang mở') return 'bg-green-50 text-green-700 border-green-200';
    if (status === 'Đang triển khai') return 'bg-yellow-50 text-yellow-700 border-yellow-200';
    if (status === 'Kết thúc') return 'bg-gray-100 text-gray-600 border-gray-200';
    return 'bg-gray-50 text-gray-600 border-gray-200';
};
</script>

<template>
    <Head title="Khóa học của phòng ban" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 p-6 sm:p-8">
                    
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-gray-900">Khóa học của phòng ban</h2>
                        <p class="text-sm text-gray-500 mt-1">Danh sách các khóa học đang được áp dụng hoặc triển khai cho bộ phận của bạn.</p>
                    </div>

                    <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 mb-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                            <div class="relative md:col-span-2">
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Khóa học (Enter để tìm)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <MagnifyingGlassIcon class="h-4 w-4 text-gray-400" />
                                    </div>
                                    <input v-model="searchKeyword" @keyup.enter="doSearch" type="text" class="pl-9 w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm" placeholder="Nhập tên / Mã khóa học..." />
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Từ ngày</label>
                                <input v-model="filterFromDate" @change="doSearch" type="date" class="w-full border-gray-300 rounded-lg text-sm text-gray-600 focus:ring-blue-500 focus:border-blue-500 shadow-sm" />
                            </div>
                            
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Hình thức</label>
                                <select v-model="filterFormat" @change="doSearch" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    <option value="all">Tất cả</option>
                                    <option value="offline">Offline</option>
                                    <option value="online">Online</option>
                                    <option value="blended">Blended</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Trạng thái</label>
                                <select v-model="filterStatus" @change="doSearch" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    <option value="all">Tất cả trạng thái</option>
                                    <option value="published">Đang mở</option>
                                    <option value="draft">Đang triển khai</option>
                                    <option value="archived">Kết thúc</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Mã KH</th>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Tên khóa học</th>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Hình thức</th>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Số lớp</th>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="courses.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 bg-gray-50/30">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                            <p>Không tìm thấy khóa học nào phù hợp.</p>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr v-for="course in courses.data" :key="course.id" 
                                    class="hover:bg-blue-50/50 transition-colors duration-150 cursor-pointer" 
                                    @click="router.get(route('department.courses.show', course.id))">
                                    
                                    <td class="px-6 py-4 text-gray-900 font-medium">{{ course.code }}</td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="text-gray-900 font-semibold">{{ course.name }}</div>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-gray-600">{{ formatDate(course.created_at) }}</td>
                                    
                                    <td class="px-6 py-4 text-gray-600 capitalize">
                                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-medium border border-gray-200">{{ course.format }}</span>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-gray-900 text-center font-bold">{{ course.course_classes_count || 0 }}</td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border', getStatusBadgeClass(course.status)]">
                                            {{ getStatusText(course.status) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="courses?.links?.length > 3" class="mt-6 flex justify-between items-center text-sm text-gray-600">
                        <div>Hiển thị trang hiện tại</div>
                        <div class="flex gap-1">
                            <Component v-for="link in courses.links" :key="link.label"
                                       :is="link.url ? 'a' : 'span'" 
                                       :href="link.url" 
                                       v-html="link.label" 
                                       class="px-3 py-1.5 border rounded-md transition-colors" 
                                       :class="{
                                           'bg-blue-600 text-white border-blue-600 font-medium': link.active, 
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