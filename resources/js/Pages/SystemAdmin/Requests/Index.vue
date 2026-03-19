<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// MOCK DATA: Chép chính xác 100% từ thiết kế của bạn
const allRequests = ref([
    { id: 1, code: 'YC-2026-000001', department: 'Kinh doanh', course: 'Kỹ năng bán hàng', hours: '8h', date: '12/01/2026', processed_date: '', status: 'Cần duyệt' },
    { id: 2, code: 'YC-2026-000002', department: 'IT', course: 'Bảo mật thông tin', hours: '6h', date: '11/01/2026', processed_date: '', status: 'Cần duyệt' },
    { id: 3, code: 'YC-2026-000003', department: 'Kế toán', course: 'Cập nhật chính sách thuế', hours: '3h', date: '10/01/2026', processed_date: '10/01/2026', status: 'Từ chối' },
    { id: 4, code: 'YC-2026-000004', department: 'Nhân sự', course: 'Kỹ năng phỏng vấn', hours: '4h', date: '06/01/2026', processed_date: '08/01/2026', status: 'Đã duyệt' },
    { id: 5, code: 'YC-2026-000005', department: 'Kinh doanh', course: 'Kỹ năng chăm sóc KH', hours: '6h', date: '07/01/2026', processed_date: '09/01/2026', status: 'Đã duyệt' },
    { id: 6, code: 'YC-2026-000006', department: 'IT', course: 'Lập trình Spring Boot', hours: '12h', date: '08/01/2026', processed_date: '', status: 'Cần duyệt' },
    { id: 7, code: 'YC-2026-000007', department: 'Marketing', course: 'Digital Marketing', hours: '8h', date: '08/01/2026', processed_date: '10/01/2026', status: 'Từ chối' },
    { id: 8, code: 'YC-2026-000008', department: 'Kho vận', course: 'An toàn lao động', hours: '4h', date: '09/01/2026', processed_date: '', status: 'Cần duyệt' },
    { id: 9, code: 'YC-2026-000009', department: 'Kinh doanh', course: 'Kỹ năng đàm phán', hours: '6h', date: '10/01/2026', processed_date: '', status: 'Cần duyệt' },
    { id: 10, code: 'YC-2026-000010', department: 'IT', course: 'Quản lý dự án Agile', hours: '8h', date: '10/01/2026', processed_date: '12/01/2026', status: 'Đã duyệt' },
    { id: 11, code: 'YC-2026-000011', department: 'Nhân sự', course: 'Kỹ năng đào tạo', hours: '6h', date: '11/01/2026', processed_date: '', status: 'Cần duyệt' },
]);

// Xử lý Logic chuyển Tab
const activeTab = ref('Tất Cả');

const tabs = computed(() => {
    return [
        { name: 'Tất Cả', count: allRequests.value.length },
        { name: 'Đã duyệt', count: allRequests.value.filter(r => r.status === 'Đã duyệt').length },
        { name: 'Cần duyệt', count: allRequests.value.filter(r => r.status === 'Cần duyệt').length },
        { name: 'Đã xử lý', count: allRequests.value.filter(r => r.status === 'Đã xử lý').length },
        { name: 'Từ chối', count: allRequests.value.filter(r => r.status === 'Từ chối').length },
    ];
});

const filteredRequests = computed(() => {
    if (activeTab.value === 'Tất Cả') return allRequests.value;
    return allRequests.value.filter(r => r.status === activeTab.value);
});

// Xử lý màu sắc Text trạng thái
const getStatusClass = (status) => {
    const classes = {
        'Cần duyệt': 'text-[#d97706] font-bold', // Vàng cam
        'Đã duyệt': 'text-[#16a34a] font-bold',  // Xanh lá
        'Đã xử lý': 'text-[#0d9488] font-bold',  // Xanh ngọc
        'Từ chối': 'text-[#dc2626] font-bold',   // Đỏ
    };
    return classes[status] || 'text-gray-800';
};
</script>

<template>
    <Head title="Yêu cầu đào tạo" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-8">Yêu cầu đào tạo</h2>

                    <div class="flex justify-center items-center gap-4 mb-10 text-[15px]">
                        <template v-for="(tab, index) in tabs" :key="tab.name">
                            <button 
                                @click="activeTab = tab.name"
                                :class="[
                                    'transition', 
                                    activeTab === tab.name ? 'text-gray-900 font-extrabold border-b-2 border-gray-900 pb-0.5' : 'text-gray-500 hover:text-gray-800 font-medium'
                                ]"
                            >
                                {{ tab.name }} ({{ tab.count }})
                            </button>
                            <span v-if="index < tabs.length - 1" class="text-gray-300 font-bold">|</span>
                        </template>
                    </div>

                    <div class="flex justify-between items-end mb-4">
                        <h3 class="text-lg font-bold text-gray-800">Danh sách yêu cầu:</h3>
                        
                        <button v-if="activeTab === 'Cần duyệt'" class="bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] px-4 py-1.5 rounded text-sm font-bold shadow-sm transition">
                            Duyệt tất cả
                        </button>
                        <button v-if="activeTab === 'Đã duyệt'" class="bg-[#fcd38e] hover:bg-[#ffd666] text-gray-900 border border-[#faad14] px-4 py-1.5 rounded text-sm font-bold shadow-sm transition">
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
                                <tr v-if="filteredRequests.length === 0">
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500 italic">Không có dữ liệu trong mục này.</td>
                                </tr>
                                <tr v-for="req in filteredRequests" :key="req.id" class="hover:bg-gray-50 transition cursor-pointer" @click="router.get(route('system.requests.show', req.id))">
                                    <td class="px-3 py-3 border-r border-gray-300">
                                        <input @click.stop type="checkbox" class="rounded border-gray-400 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ req.code }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-800">{{ req.department }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-800 text-left" :class="{ 'text-blue-600': req.status !== 'Cần duyệt' && req.status !== 'Từ chối' }">
                                        {{ req.course }}
                                    </td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ req.hours }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ req.date }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ req.processed_date }}</td>
                                    
                                    <td class="px-3 py-3 border-r border-gray-300" :class="getStatusClass(req.status)">
                                        {{ req.status }}
                                    </td>
                                    
                                    <td class="px-2 py-3 min-w-[120px]">
                                        <div v-if="req.status === 'Cần duyệt'" class="flex justify-center gap-1">
                                            <button @click.stop class="bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] px-2 py-1 rounded text-xs font-semibold shadow-sm transition">Duyệt</button>
                                            <button @click.stop class="bg-[#ff4d4f] hover:bg-[#f5222d] text-white border border-[#ff4d4f] px-2 py-1 rounded text-xs font-semibold shadow-sm transition">Từ chối</button>
                                        </div>
                                        
                                        <div v-else-if="req.status === 'Đã duyệt'" class="flex justify-center">
                                            <button @click.stop class="bg-[#fcd38e] hover:bg-[#ffd666] text-gray-900 border border-[#faad14] px-3 py-1 rounded text-xs font-semibold shadow-sm transition">
                                                + Tạo khóa học
                                            </button>
                                        </div>
                                        
                                        <div v-else-if="req.status === 'Từ chối' || req.status === 'Đã xử lý'" class="flex justify-center">
                                            <Link @click.stop :href="route('system.requests.show', req.id)" class="text-[#0ea5e9] hover:underline text-sm font-medium">
                                                {{ req.status === 'Đã xử lý' ? 'Xem khóa học' : 'Xem chi tiết' }}
                                            </Link>
                                        </div>
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