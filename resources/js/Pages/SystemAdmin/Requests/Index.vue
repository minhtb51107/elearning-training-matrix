<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// Nhận dữ liệu THẬT từ backend thay vì mock
const props = defineProps({
    requests: Object,
    filters: Object,
});

// Form tìm kiếm / Lọc tab
const searchForm = ref({
    tab: props.filters?.tab || 'all',
});

// Tự động load dữ liệu khi đổi Tab
watch(() => searchForm.value.tab, (newTab) => {
    router.get(route('system.requests.index'), { tab: newTab }, { preserveState: true, replace: true, preserveScroll: true });
});

const tabs = [
    { id: 'all', name: 'Tất Cả' },
    { id: 'approved', name: 'Đã duyệt' },
    { id: 'pending', name: 'Cần duyệt' },
    { id: 'processed', name: 'Đã xử lý' },
    { id: 'rejected', name: 'Từ chối' },
];

// Xử lý logic Đổi trạng thái trực tiếp trên bảng
const updateStatus = (id, status) => {
    let feedback = '';
    // Nếu bấm Từ chối, bắt buộc nhập lý do bằng popup native (rất tiện lợi cho UX Admin)
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

// Map trạng thái DB sang Tiếng Việt
const formatStatus = (status) => {
    const labels = { 'pending': 'Cần duyệt', 'approved': 'Đã duyệt', 'processed': 'Đã xử lý', 'rejected': 'Từ chối' };
    return labels[status] || status;
};

// Xử lý màu sắc Text trạng thái
const getStatusClass = (status) => {
    const classes = {
        'pending': 'text-[#d97706] font-bold', // Vàng cam
        'approved': 'text-[#16a34a] font-bold',  // Xanh lá
        'processed': 'text-[#0d9488] font-bold', // Xanh ngọc
        'rejected': 'text-[#dc2626] font-bold',  // Đỏ
    };
    return classes[status] || 'text-gray-800';
};
</script>

<template>
    <Head title="Yêu cầu đào tạo" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                    {{ $page.props.flash.error }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-8">Yêu cầu đào tạo</h2>

                    <div class="flex justify-center items-center gap-4 mb-10 text-[15px]">
                        <template v-for="(tab, index) in tabs" :key="tab.id">
                            <button 
                                @click="searchForm.tab = tab.id"
                                :class="[
                                    'transition', 
                                    searchForm.tab === tab.id ? 'text-gray-900 font-extrabold border-b-2 border-gray-900 pb-0.5' : 'text-gray-500 hover:text-gray-800 font-medium'
                                ]"
                            >
                                {{ tab.name }}
                            </button>
                            <span v-if="index < tabs.length - 1" class="text-gray-300 font-bold">|</span>
                        </template>
                    </div>

                    <div class="flex justify-between items-end mb-4">
                        <h3 class="text-lg font-bold text-gray-800">Danh sách yêu cầu:</h3>
                        
                        <button v-if="searchForm.tab === 'pending'" class="bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] px-4 py-1.5 rounded text-sm font-bold shadow-sm transition">
                            Duyệt tất cả
                        </button>
                        <button v-if="searchForm.tab === 'approved'" class="bg-[#fcd38e] hover:bg-[#ffd666] text-gray-900 border border-[#faad14] px-4 py-1.5 rounded text-sm font-bold shadow-sm transition">
                            + Tạo khóa học
                        </button>
                    </div>

                    <div class="overflow-x-auto border border-gray-300">
                        <table class="min-w-full divide-y divide-gray-300 text-center text-[13px]">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-3 w-10 border-r border-gray-300">
                                        <input type="checkbox" class="rounded border-gray-400 text-blue-600 focus:ring-blue-500">
                                    </th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Mã YC</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Phòng ban</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300 text-left">Tên khóa</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300 w-16">Giờ</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Ngày gửi</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Ngày xử lý</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Trạng thái</th>
                                    <th class="px-3 py-3 font-bold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-300">
                                <tr v-if="!requests || !requests.data || requests.data.length === 0">
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500 italic">Không có dữ liệu trong mục này.</td>
                                </tr>
                                
                                <tr v-for="req in requests.data" :key="req.id" class="hover:bg-gray-50 transition cursor-pointer" @click="router.get(route('system.requests.show', req.id))">
                                    <td class="px-3 py-3 border-r border-gray-300">
                                        <input @click.stop type="checkbox" class="rounded border-gray-400 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ req.code }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-800">{{ req.department?.name }}</td>
                                    
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-800 text-left" :class="{ 'text-blue-600': req.status !== 'pending' && req.status !== 'rejected' }">
                                        {{ req.course_name }}
                                    </td>
                                    
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ req.expected_duration }}h</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ new Date(req.created_at).toLocaleDateString('vi-VN') }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">
                                        {{ req.status !== 'pending' ? new Date(req.updated_at).toLocaleDateString('vi-VN') : '--' }}
                                    </td>
                                    
                                    <td class="px-3 py-3 border-r border-gray-300" :class="getStatusClass(req.status)">
                                        {{ formatStatus(req.status) }}
                                    </td>
                                    
                                    <td class="px-2 py-3 min-w-[120px]">
                                        <div v-if="req.status === 'pending'" class="flex justify-center gap-1">
                                            <button @click.stop="updateStatus(req.id, 'approved')" class="bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] px-2 py-1 rounded text-xs font-semibold shadow-sm transition">Duyệt</button>
                                            <button @click.stop="updateStatus(req.id, 'rejected')" class="bg-[#ff4d4f] hover:bg-[#f5222d] text-white border border-[#ff4d4f] px-2 py-1 rounded text-xs font-semibold shadow-sm transition">Từ chối</button>
                                        </div>
                                        
                                        <div v-else-if="req.status === 'approved'" class="flex justify-center">
                                            <button @click.stop="router.get(route('system.courses.create', { request_id: req.id }))" class="bg-[#fcd38e] hover:bg-[#ffd666] text-gray-900 border border-[#faad14] px-3 py-1 rounded text-xs font-semibold shadow-sm transition">
                                                + Tạo khóa học
                                            </button>
                                        </div>
                                        
                                        <div v-else-if="req.status === 'rejected' || req.status === 'processed'" class="flex justify-center">
                                            <Link @click.stop :href="route('system.requests.show', req.id)" class="text-[#0ea5e9] hover:underline text-sm font-medium">
                                                {{ req.status === 'processed' ? 'Xem khóa học' : 'Xem chi tiết' }}
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="requests?.links?.length > 3" class="mt-8 flex justify-center items-center gap-2 text-sm text-[#0ea5e9] font-medium">
                        <template v-for="(link, index) in requests.links" :key="index">
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