<script setup>
import { ref, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    requests: Object,
    filters: Object,
});

const searchForm = ref({
    tab: props.filters?.tab || 'all',
});

// DANH SÁCH LƯU TRỮ CÁC ID ĐƯỢC CHỌN
const selectedRequests = ref([]);

// Tự động load dữ liệu & Xóa checkbox khi đổi Tab
watch(() => searchForm.value.tab, (newTab) => {
    selectedRequests.value = []; // Reset danh sách đã chọn
    router.get(route('system.requests.index'), { tab: newTab }, { preserveState: true, replace: true, preserveScroll: true });
});

const tabs = [
    { id: 'all', name: 'Tất Cả' },
    { id: 'approved', name: 'Đã duyệt' },
    { id: 'pending', name: 'Cần duyệt' },
    { id: 'processed', name: 'Đã xử lý' },
    { id: 'rejected', name: 'Từ chối' },
];

// Hàm Xử lý Checkbox Chọn tất cả
const toggleAll = (event) => {
    if (event.target.checked) {
        // Lấy tất cả ID của các yêu cầu đang hiển thị trên trang này
        selectedRequests.value = props.requests.data.map(req => req.id);
    } else {
        selectedRequests.value = [];
    }
};

// Hàm Duyệt hàng loạt (Bulk Approve)
const approveSelected = () => {
    if (selectedRequests.value.length === 0) return;
    
    if (confirm(`Xác nhận DUYỆT ${selectedRequests.value.length} yêu cầu đã chọn?`)) {
        router.post(route('system.requests.bulk-approve'), {
            ids: selectedRequests.value
        }, { 
            preserveScroll: true,
            onSuccess: () => {
                selectedRequests.value = []; // Xóa danh sách sau khi duyệt thành công
            }
        });
    }
};

// Xử lý logic Đổi trạng thái từng dòng
const updateStatus = (id, status) => {
    let feedback = '';
    if (status === 'rejected') {
        feedback = prompt('Vui lòng nhập lý do từ chối yêu cầu này:');
        if (feedback === null || feedback.trim() === '') {
            alert('Bạn phải nhập lý do để từ chối!');
            return;
        }
    }

    if (confirm(status === 'approved' ? 'Xác nhận DUYỆT yêu cầu này?' : 'Xác nhận TỪ CHỐI yêu cầu này?')) {
        router.put(route('system.requests.update-status', id), {
            status: status,
            hr_feedback: feedback
        }, { preserveScroll: true });
    }
};

const formatStatus = (status) => {
    const labels = { 'pending': 'Cần duyệt', 'approved': 'Đã duyệt', 'processed': 'Đã xử lý', 'rejected': 'Từ chối' };
    return labels[status] || status;
};

const getStatusBadge = (status) => {
    const badges = {
        'pending': 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'approved': 'bg-green-50 text-green-700 border-green-200',
        'processed': 'bg-blue-50 text-blue-700 border-blue-200',
        'rejected': 'bg-red-50 text-red-700 border-red-200',
    };
    return badges[status] || 'bg-gray-50 text-gray-600 border-gray-200';
};
</script>

<template>
    <Head title="Yêu cầu đào tạo" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm flex items-center gap-3">
                    <CheckIcon class="w-5 h-5 text-green-500" />
                    <span class="font-medium">{{ $page.props.flash.success }}</span>
                </div>
                <div v-if="$page.props.flash?.error" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm flex items-center gap-3">
                    <XMarkIcon class="w-5 h-5 text-red-500" />
                    <span class="font-medium">{{ $page.props.flash.error }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 p-6 sm:p-8">
                    
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-gray-900">Quản lý Yêu cầu đào tạo</h2>
                        <p class="text-sm text-gray-500 mt-1">Phê duyệt và tổ chức các khóa học theo đề xuất từ các phòng ban.</p>
                    </div>

                    <div class="flex gap-2 mb-8 bg-gray-50/50 p-1.5 rounded-xl border border-gray-100 overflow-x-auto">
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

                    <div class="flex justify-between items-center mb-4 min-h-[40px]">
                        <h3 class="text-base font-bold text-gray-800">Danh sách yêu cầu</h3>
                        
                        <button v-if="searchForm.tab === 'pending'" 
                                @click="approveSelected"
                                :disabled="selectedRequests.length === 0"
                                :class="[
                                    'inline-flex justify-center items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all',
                                    selectedRequests.length > 0 
                                        ? 'text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-600/30 cursor-pointer' 
                                        : 'text-gray-400 bg-gray-100 cursor-not-allowed border border-gray-200'
                                ]">
                            <CheckIcon class="w-4 h-4" />
                            Duyệt đã chọn {{ selectedRequests.length > 0 ? `(${selectedRequests.length})` : '' }}
                        </button>
                        
                        <button v-if="searchForm.tab === 'approved'" class="inline-flex justify-center items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none shadow-sm shadow-blue-600/30 transition-all">
                            <PlusIcon class="w-4 h-4" />
                            Tạo khóa học từ yêu cầu
                        </button>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th v-if="searchForm.tab === 'pending'" class="px-4 py-3 w-10 text-center">
                                        <input type="checkbox" 
                                               :checked="requests?.data?.length > 0 && selectedRequests.length === requests?.data?.length"
                                               @change="toggleAll"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    </th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Mã YC</th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Phòng ban</th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Khóa học đề xuất</th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Ngày gửi</th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Trạng thái</th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="!requests || !requests.data || requests.data.length === 0">
                                    <td :colspan="searchForm.tab === 'pending' ? 7 : 6" class="px-6 py-12 text-center text-gray-500 bg-gray-50/30">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <p>Chưa có yêu cầu nào trong thư mục này.</p>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr v-for="req in requests.data" :key="req.id" class="hover:bg-blue-50/50 cursor-pointer transition-colors duration-150" @click="router.get(route('system.requests.show', req.id))">
                                    
                                    <td v-if="searchForm.tab === 'pending'" class="px-4 py-4 text-center" @click.stop>
                                        <input type="checkbox" :value="req.id" v-model="selectedRequests" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    </td>
                                    
                                    <td class="px-4 py-4 font-medium text-gray-900">{{ req.code }}</td>
                                    <td class="px-4 py-4 text-gray-600 font-medium">{{ req.department?.name }}</td>
                                    <td class="px-4 py-4">
                                        <div class="font-semibold text-gray-900" :class="{ 'text-blue-700': req.status !== 'pending' && req.status !== 'rejected' }">
                                            {{ req.course_name }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ req.expected_duration }} giờ học dự kiến</div>
                                    </td>
                                    <td class="px-4 py-4 text-gray-600">{{ new Date(req.created_at).toLocaleDateString('vi-VN') }}</td>
                                    <td class="px-4 py-4 text-center">
                                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border', getStatusBadge(req.status)]">
                                            {{ formatStatus(req.status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center min-w-[140px]">
                                        <div v-if="req.status === 'pending'" class="flex justify-center gap-2">
                                            <button @click.stop="updateStatus(req.id, 'approved')" class="px-3 py-1.5 bg-white border border-green-500 text-green-600 hover:bg-green-50 rounded-lg text-[13px] font-semibold transition-colors shadow-sm">
                                                Duyệt
                                            </button>
                                            <button @click.stop="updateStatus(req.id, 'rejected')" class="px-3 py-1.5 bg-white border border-red-300 text-red-600 hover:bg-red-50 rounded-lg text-[13px] font-semibold transition-colors shadow-sm">
                                                Từ chối
                                            </button>
                                        </div>
                                        <div v-else-if="req.status === 'approved'" class="flex justify-center">
                                            <button @click.stop="router.get(route('system.courses.create', { request_id: req.id }))" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100 rounded-lg text-[13px] font-semibold transition-colors shadow-sm">
                                                <PlusIcon class="w-4 h-4" /> Lập khóa học
                                            </button>
                                        </div>
                                        <div v-else-if="req.status === 'rejected' || req.status === 'processed'" class="flex justify-center">
                                            <Link @click.stop :href="route('system.requests.show', req.id)" class="text-gray-500 hover:text-blue-600 hover:underline text-[13px] font-medium transition-colors">
                                                {{ req.status === 'processed' ? 'Xem khóa học đã tạo' : 'Xem chi tiết' }}
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="requests?.links?.length > 3" class="mt-6 flex justify-between items-center text-sm text-gray-600">
                        <div>Hiển thị trang hiện tại</div>
                        <div class="flex gap-1">
                            <Component v-for="(link, index) in requests.links" :key="index"
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