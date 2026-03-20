<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    employees: Object,
    departments: Array, // Hứng danh sách phòng ban
    filters: Object
});

// Gắn biến cho các bộ lọc
const searchKeyword = ref(props.filters?.keyword || '');
const filterDept = ref(props.filters?.department_id || 'all');
const filterPosition = ref(props.filters?.position || 'all');
const filterStatus = ref(props.filters?.status || 'all');

// Hàm Search chung
const doSearch = () => {
    router.get(route('system.employees.index'), { 
        keyword: searchKeyword.value,
        department_id: filterDept.value,
        position: filterPosition.value,
        status: filterStatus.value
    }, { preserveState: true, replace: true });
};

// QUẢN LÝ MODAL (POPUP) THÔNG TIN
const selectedEmployee = ref(null);
const showModal = ref(false);

const openModal = (emp) => {
    selectedEmployee.value = {
        id: emp.id,
        code: `NV-${String(emp.id).padStart(3, '0')}`,
        name: emp.name,
        email: emp.email,
        department: emp.department ? emp.department.name : '--',
        position: 'Nhân viên', 
        status: 'Đang đánh giá', 
        overview: { learning: 0, completed: 0, date: '-' },
        activeClasses: [],
        history: []
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(() => selectedEmployee.value = null, 200);
};
</script>

<template>
    <Head title="Danh sách nhân viên" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-200">
                    
                    <h2 class="text-xl font-bold text-gray-800 border-b pb-4 mb-8">Danh sách nhân sự toàn công ty</h2>

                    <div class="mb-8">
                        <label class="block text-base font-bold text-gray-800 mb-4">Bộ lọc:</label>
                        <div class="grid grid-cols-4 gap-6">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Phòng ban:</label>
                                <select v-model="filterDept" @change="doSearch" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 text-gray-600">
                                    <option value="all">Tất cả</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                        {{ dept.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Vị trí:</label>
                                <select v-model="filterPosition" @change="doSearch" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 text-gray-600">
                                    <option value="all">Tất cả</option>
                                    <option value="staff">Nhân viên</option>
                                    <option value="leader">Trưởng nhóm</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Trạng thái đào tạo:</label>
                                <select v-model="filterStatus" @change="doSearch" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 text-gray-600">
                                    <option value="all">Tất cả</option>
                                    <option value="passed">Đạt yêu cầu</option>
                                    <option value="failed">Chưa đạt yêu cầu</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Từ khóa (Enter để tìm):</label>
                                <input v-model="searchKeyword" @keyup.enter="doSearch" type="text" placeholder="Nhập tên / Email..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 italic placeholder-gray-400" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mb-4">
                        <div class="flex bg-[#e5e7eb] rounded border border-gray-400 overflow-hidden">
                            <button class="px-4 py-1 text-sm font-bold text-gray-700 hover:bg-gray-300 border-r border-gray-400 transition">Print</button>
                            <button class="px-4 py-1 text-sm font-bold text-gray-700 hover:bg-gray-300 border-r border-gray-400 transition">Excel</button>
                            <button class="px-4 py-1 text-sm font-bold text-gray-700 hover:bg-gray-300 border-r border-gray-400 transition">PDF</button>
                            <button class="px-4 py-1 text-sm font-bold text-gray-700 hover:bg-gray-300 transition">CSV</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-300">
                        <table class="min-w-full divide-y divide-gray-300 text-center text-[13px]">
                            <thead class="bg-[#fcd38e]">
                                <tr>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300">Mã NV</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 text-left">Họ tên</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300">Email</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300">Phòng ban</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300">Vị trí</th>
                                    <th class="px-4 py-3 font-bold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-300">
                                <tr v-if="employees.data.length === 0">
                                    <td colspan="6" class="px-4 py-6 text-gray-500 italic text-center">Không tìm thấy nhân viên nào phù hợp với bộ lọc.</td>
                                </tr>
                                <tr v-for="emp in employees.data" :key="emp.id" class="hover:bg-gray-50 transition cursor-pointer" @click="openModal(emp)">
                                    <td class="px-4 py-3 border-r border-gray-300 text-gray-700">NV-{{ String(emp.id).padStart(3, '0') }}</td>
                                    <td class="px-4 py-3 border-r border-gray-300 text-gray-800 text-left font-medium">{{ emp.name }}</td>
                                    <td class="px-4 py-3 border-r border-gray-300 text-gray-700 text-left">{{ emp.email }}</td>
                                    <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ emp.department ? emp.department.name : '--' }}</td>
                                    <td class="px-4 py-3 border-r border-gray-300 text-gray-700">Nhân viên</td>
                                    <td class="px-4 py-3">
                                        <button @click.stop="openModal(emp)" class="text-[#0ea5e9] hover:underline font-bold text-[13px]">
                                            [ Xem chi tiết ]
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-center items-center gap-2 text-sm text-[#0ea5e9] font-medium" v-if="employees.links.length > 3">
                        <Component v-for="link in employees.links" :key="link.label"
                                   :is="link.url ? 'a' : 'span'" 
                                   :href="link.url" 
                                   v-html="link.label" 
                                   class="px-3 py-1 border border-gray-300 rounded text-sm transition" 
                                   :class="{
                                       'bg-[#0ea5e9] text-white font-bold': link.active, 
                                       'text-gray-400 cursor-not-allowed': !link.url,
                                       'hover:bg-gray-100': link.url && !link.active
                                   }" />
                    </div>

                </div>
            </div>
        </div>

        <Modal :show="showModal" @close="closeModal" maxWidth="md">
            <div class="p-8 bg-white border-2 border-gray-300 shadow-xl" v-if="selectedEmployee">
                <h3 class="text-lg font-bold text-gray-800 uppercase mb-6 border-b border-gray-300 pb-2">THÔNG TIN ĐÀO TẠO NHÂN VIÊN</h3>
                
                <div class="mb-6 text-[14px] text-gray-800 leading-relaxed">
                    <p class="font-bold text-base mb-1">{{ selectedEmployee.name }}</p>
                    <p><span class="font-bold">Mã NV:</span> {{ selectedEmployee.code }}</p>
                    <p><span class="font-bold">Phòng ban:</span> [ {{ selectedEmployee.department }} ]</p>
                    <p><span class="font-bold">Email:</span> {{ selectedEmployee.email }}</p>
                </div>

                <div class="mb-6 text-[14px] text-gray-800 leading-relaxed">
                    <p class="font-bold uppercase mb-2">TỔNG QUAN ĐÀO TẠO</p>
                    <div class="grid grid-cols-[150px_1fr] gap-y-1">
                        <span>- Lớp đang học:</span> <span class="font-bold">{{ selectedEmployee.overview.learning }}</span>
                        <span>- Lớp hoàn thành:</span> <span class="font-bold">{{ selectedEmployee.overview.completed }}</span>
                    </div>
                </div>

                <div class="mb-6 text-[14px] text-gray-800 leading-relaxed">
                    <p class="font-bold uppercase mb-2">LỚP ĐANG THAM GIA</p>
                    <p class="text-gray-500 italic">- Đang cập nhật -</p>
                </div>

                <div class="mb-8 text-[14px] text-gray-800 leading-relaxed">
                    <p class="font-bold uppercase mb-2">LỊCH SỬ ĐÀO TẠO</p>
                    <p class="text-gray-500 italic">- Đang cập nhật -</p>
                </div>

                <div class="flex justify-center border-t border-gray-200 pt-6 mt-4">
                    <button @click="closeModal" class="text-[#b91c1c] font-bold text-[15px] uppercase tracking-wide hover:underline transition">
                        [ ĐÓNG ]
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>