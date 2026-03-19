<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    requests: Object,
    filters: Object,
});

// Form tìm kiếm dùng manual (Bấm nút mới tìm) thay vì tự động
const searchForm = ref({
    keyword: props.filters?.keyword || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    status: props.filters?.status || '',
});

const submitSearch = () => {
    router.get(route('department.requests.index'), searchForm.value, { preserveState: true, replace: true });
};

// Đổ màu full ô (Cell) theo đúng màu của Mockup
const getStatusBgClass = (status) => {
    const classes = {
        'draft': 'bg-white text-gray-800',
        'pending': 'bg-[#fffb8f] text-gray-900', // Màu Vàng
        'approved': 'bg-[#b7eb8f] text-gray-900', // Màu Xanh lá
        'processed': 'bg-[#13c2c2] text-white', // Màu Xanh ngọc
        'rejected': 'bg-[#ff4d4f] text-white', // Màu Đỏ
    };
    return classes[status] || 'bg-white text-gray-800';
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
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                    {{ $page.props.flash.success }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-4">Danh sách yêu cầu đào tạo:</h2>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tìm kiếm:</label>
                        <div class="flex flex-wrap items-end gap-4">
                            <div class="flex-1 min-w-[200px]">
                                <label class="block text-sm text-gray-600 mb-1">Khóa học:</label>
                                <input v-model="searchForm.keyword" type="text" class="w-full border-gray-400 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Nhập và chọn" />
                            </div>
                            <div class="w-36">
                                <label class="block text-sm text-gray-600 mb-1">Từ ngày:</label>
                                <input v-model="searchForm.start_date" type="date" class="w-full border-gray-400 rounded-md text-sm text-gray-600 focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                            <div class="w-36">
                                <label class="block text-sm text-gray-600 mb-1">Đến ngày:</label>
                                <input v-model="searchForm.end_date" type="date" class="w-full border-gray-400 rounded-md text-sm text-gray-600 focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                            <div class="w-40">
                                <label class="block text-sm text-gray-600 mb-1">Trạng thái:</label>
                                <select v-model="searchForm.status" class="w-full border-gray-400 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Tất cả</option>
                                    <option value="draft">Nháp</option>
                                    <option value="pending">Chờ duyệt</option>
                                    <option value="approved">Đã duyệt</option>
                                    <option value="processed">Đã xử lý</option>
                                    <option value="rejected">Từ chối</option>
                                </select>
                            </div>
                            <div>
                                <button @click="submitSearch" class="bg-[#0ea5e9] hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-md text-sm transition shadow-sm">
                                    Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-400 mt-8">
                        <table class="min-w-full divide-y divide-gray-400">
                            <thead class="bg-[#fcd38e]">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 border-r border-gray-400 w-28">Mã yêu cầu</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 border-r border-gray-400">Tên khóa học</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 border-r border-gray-400">Nội dung</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 border-r border-gray-400 w-28">Thời lượng</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 border-r border-gray-400 w-32">Ngày gửi</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 w-32">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-400">
                                <tr v-if="!requests || !requests.data || requests.data.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">Chưa có yêu cầu nào.</td>
                                </tr>
                                <tr v-for="req in requests.data" :key="req.id" class="hover:bg-gray-50 cursor-pointer transition" @click="router.get(route('department.requests.show', req.id))">
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-700">{{ req.code }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-800">{{ req.course_name }}</td>
                                    
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-600 max-w-[200px] truncate">
                                        {{ req.content }}
                                    </td>
                                    
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-700">{{ req.expected_duration || '--' }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-700">{{ new Date(req.created_at).toLocaleDateString('vi-VN') }}</td>
                                    
                                    <td :class="['px-4 py-3 text-sm font-medium border-l border-gray-400', getStatusBgClass(req.status)]">
                                        {{ getStatusLabel(req.status) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-center">
                        <Link :href="route('department.requests.create')" class="text-[#d97706] hover:text-orange-700 font-bold text-[15px] uppercase tracking-wide">
                            [ + GỬI YÊU CẦU ĐÀO TẠO MỚI ]
                        </Link>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>