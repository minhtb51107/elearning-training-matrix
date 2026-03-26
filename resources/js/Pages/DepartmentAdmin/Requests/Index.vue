<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
// Tích hợp Heroicons cho xịn xò
import { PlusIcon, MagnifyingGlassIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    requests: Object,
    filters: Object,
});

const searchForm = ref({
    keyword: props.filters?.keyword || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    status: props.filters?.status || '',
});

// Tự động lọc sau khi ngừng gõ/chọn 300ms
let searchTimeout = null;
watch(searchForm, (newValue) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('department.requests.index'), newValue, {
            preserveState: true, 
            replace: true, 
            preserveScroll: true 
        });
    }, 300);
}, { deep: true });

// Đổi sang màu nền Pastel hiện đại dạng Pill (Viên thuốc)
const getStatusBgClass = (status) => {
    const classes = {
        'draft': 'bg-gray-100 text-gray-700 border-gray-200',
        'pending': 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'approved': 'bg-green-50 text-green-700 border-green-200',
        'processed': 'bg-blue-50 text-blue-700 border-blue-200',
        'rejected': 'bg-red-50 text-red-700 border-red-200',
    };
    return classes[status] || 'bg-gray-50 text-gray-600 border-gray-200';
};

const getStatusLabel = (status) => {
    const labels = {
        'draft': 'Nháp', 
        'pending': 'Chờ duyệt', 
        'approved': 'Đã duyệt',
        'processed': 'Đã xử lý', 
        'rejected': 'Từ chối'
    };
    return labels[status] || status;
};
</script>

<template>
    <Head title="Danh sách Yêu cầu đào tạo" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    <span class="font-medium">{{ $page.props.flash.success }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 p-6 sm:p-8">
                    
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Yêu cầu đào tạo</h2>
                            <p class="text-sm text-gray-500 mt-1">Quản lý và theo dõi các đề xuất đào tạo của bộ phận.</p>
                        </div>
                        <Link :href="route('department.requests.create')" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors shadow-sm shadow-blue-600/20 text-sm">
                            <PlusIcon class="w-5 h-5" />
                            Tạo yêu cầu mới
                        </Link>
                    </div>

                    <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="relative">
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Khóa học / Mã YC</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <MagnifyingGlassIcon class="h-4 w-4 text-gray-400" />
                                    </div>
                                    <input v-model="searchForm.keyword" type="text" class="pl-9 w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm" placeholder="Tìm kiếm..." />
                                </div>
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Từ ngày</label>
                                <input v-model="searchForm.start_date" type="date" class="w-full border-gray-300 rounded-lg text-sm text-gray-600 focus:ring-blue-500 focus:border-blue-500 shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Đến ngày</label>
                                <input v-model="searchForm.end_date" type="date" class="w-full border-gray-300 rounded-lg text-sm text-gray-600 focus:ring-blue-500 focus:border-blue-500 shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Trạng thái</label>
                                <select v-model="searchForm.status" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="draft">Nháp</option>
                                    <option value="pending">Chờ duyệt</option>
                                    <option value="approved">Đã duyệt</option>
                                    <option value="processed">Đã xử lý</option>
                                    <option value="rejected">Từ chối</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Mã YC</th>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Tên khóa học</th>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Thời lượng</th>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Ngày gửi</th>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="!requests || !requests.data || requests.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 bg-gray-50/30">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <p>Chưa có yêu cầu nào được tìm thấy.</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="req in requests.data" :key="req.id" 
                                    class="hover:bg-blue-50/50 cursor-pointer transition-colors duration-150" 
                                    @click="router.get(route('department.requests.show', req.id))">
                                    
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ req.code }}</td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="text-gray-900 font-medium">{{ req.course_name }}</div>
                                        <div class="text-gray-500 text-xs mt-0.5 truncate max-w-xs">{{ req.content }}</div>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ req.expected_duration ? req.expected_duration + ' giờ' : '--' }}
                                    </td>
                                    
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ new Date(req.created_at).toLocaleDateString('vi-VN') }}
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border', getStatusBgClass(req.status)]">
                                            {{ getStatusLabel(req.status) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="requests?.links?.length > 3" class="mt-6 flex justify-between items-center text-sm text-gray-600">
                        <div>Hiển thị trang hiện tại</div>
                        <div class="flex gap-1">
                            <Component v-for="link in requests.links" :key="link.label"
                                       :is="link.url ? 'Link' : 'span'" 
                                       :href="link.url" 
                                       v-html="link.label" 
                                       class="px-3 py-1 border rounded-md transition-colors" 
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