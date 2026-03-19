<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

// MOCK DATA: Chép chính xác 100% từ thiết kế
const employees = ref([
    { 
        id: 1, code: 'NV-2023-015', name: 'Nguyễn A', position: 'Nhân viên', status: 'Đang thực hiện', progress: '2/5',
        email: 'a@company.com', department: 'Kinh doanh',
        overview: { learning: 2, completed: 3 },
        activeClasses: [
            { name: 'Sales nâng cao - L1', status: 'Đang học' },
            { name: 'Sales cơ bản - L1', status: 'Đang học' }
        ]
    },
    { 
        id: 2, code: 'NV-2023-016', name: 'Trần B', position: 'Trưởng nhóm', status: 'Hoàn thành', progress: '5/5',
        email: 'b@company.com', department: 'Kinh doanh',
        overview: { learning: 0, completed: 5 },
        activeClasses: []
    },
    { 
        id: 3, code: 'NV-2023-017', name: 'Lê C', position: 'Nhân viên', status: 'Chưa hoàn thành', progress: '0/5',
        email: 'c@company.com', department: 'Kinh doanh',
        overview: { learning: 0, completed: 0 },
        activeClasses: []
    },
]);

// Xử lý bật tắt Modal
const selectedEmployee = ref(null);
const showModal = ref(false);

const openModal = (emp) => {
    selectedEmployee.value = emp;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    // Đợi hiệu ứng đóng modal xong (200ms) rồi mới xóa dữ liệu để không bị giật
    setTimeout(() => selectedEmployee.value = null, 200);
};
</script>

<template>
    <Head title="Danh sách nhân viên" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-200">
                    
                    <h2 class="text-xl font-bold text-gray-800 border-b pb-4">Danh sách nhân viên</h2>
                    <h3 class="font-bold text-gray-800 mt-4 mb-6">Phòng ban: KINH DOANH</h3>

                    <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                        <div class="flex items-center gap-6">
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600 font-medium">Bộ lọc:</label>
                                <label class="text-sm text-gray-600 ml-2">Trạng thái học:</label>
                                <select class="border-gray-400 rounded-md text-sm py-1.5 focus:ring-blue-500 focus:border-blue-500 w-32">
                                    <option>Tất cả</option>
                                    <option>Đang thực hiện</option>
                                    <option>Hoàn thành</option>
                                    <option>Chưa hoàn thành</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600 ml-4">Từ khóa:</label>
                                <input type="text" class="border-gray-400 rounded-md text-sm py-1.5 focus:ring-blue-500 focus:border-blue-500 w-48" placeholder="Nhập tên / Mã NV" />
                            </div>
                        </div>

                        <div class="flex bg-[#e5e7eb] rounded border border-gray-400 overflow-hidden">
                            <button class="px-4 py-1 text-sm font-bold text-gray-700 hover:bg-gray-300 border-r border-gray-400 transition">Print</button>
                            <button class="px-4 py-1 text-sm font-bold text-gray-700 hover:bg-gray-300 border-r border-gray-400 transition">Excel</button>
                            <button class="px-4 py-1 text-sm font-bold text-gray-700 hover:bg-gray-300 border-r border-gray-400 transition">PDF</button>
                            <button class="px-4 py-1 text-sm font-bold text-gray-700 hover:bg-gray-300 transition">CSV</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-400">
                        <table class="min-w-full divide-y divide-gray-400 text-left text-sm">
                            <thead class="bg-[#fcd38e]">
                                <tr>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-1/6">Mã NV</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-1/5">Họ tên</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-1/6">Vị trí</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-1/4">Tình trạng đào tạo</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 w-1/6">Tiến độ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-400">
                                <tr v-for="emp in employees" :key="emp.id" class="hover:bg-gray-50 transition cursor-pointer" @click="openModal(emp)">
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800">{{ emp.code }}</td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800">{{ emp.name }}</td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800">{{ emp.position }}</td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800">{{ emp.status }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ emp.progress }}</td>
                                </tr>
                                <tr v-for="i in 3" :key="'empty-'+i">
                                    <td class="px-4 py-5 border-r border-gray-400"></td>
                                    <td class="px-4 py-5 border-r border-gray-400"></td>
                                    <td class="px-4 py-5 border-r border-gray-400"></td>
                                    <td class="px-4 py-5 border-r border-gray-400"></td>
                                    <td class="px-4 py-5"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-center items-center gap-2 text-sm text-[#0ea5e9] font-bold">
                        <button class="hover:underline">&lt; Prev</button>
                        <button class="w-7 h-7 rounded bg-blue-100 flex items-center justify-center">1</button>
                        <button class="w-7 h-7 rounded hover:bg-gray-100 flex items-center justify-center font-normal">2</button>
                        <button class="w-7 h-7 rounded hover:bg-gray-100 flex items-center justify-center font-normal">3</button>
                        <button class="hover:underline">Next &gt;</button>
                    </div>

                </div>
            </div>
        </div>

        <Modal :show="showModal" @close="closeModal" maxWidth="md">
            <div class="p-8 bg-white" v-if="selectedEmployee">
                <h3 class="text-lg font-bold text-gray-800 uppercase mb-4 border-b border-gray-300 pb-2">Thông tin đào tạo nhân viên</h3>
                
                <div class="mb-5 text-[15px] text-gray-800">
                    <p class="font-bold text-base mb-1">{{ selectedEmployee.name }}</p>
                    <p><span class="font-bold">Mã NV:</span> {{ selectedEmployee.code }}</p>
                    <p><span class="font-bold">Phòng ban:</span> [ {{ selectedEmployee.department }} ]</p>
                    <p><span class="font-bold">Email:</span> {{ selectedEmployee.email }}</p>
                </div>

                <div class="mb-5 text-[15px] text-gray-800">
                    <p class="font-bold uppercase mb-1">Tổng quan đào tạo</p>
                    <p>- Đang học: {{ selectedEmployee.overview.learning }} lớp</p>
                    <p>- Đã hoàn thành: {{ selectedEmployee.overview.completed }} lớp</p>
                </div>

                <div class="mb-8 text-[15px] text-gray-800">
                    <p class="font-bold uppercase mb-2">Lớp đang tham gia</p>
                    <template v-if="selectedEmployee.activeClasses.length > 0">
                        <div v-for="(cls, index) in selectedEmployee.activeClasses" :key="index" class="mb-3 leading-snug">
                            <p>Tên lớp: {{ cls.name }}</p>
                            <p>Trạng thái: [ {{ cls.status }} ]</p>
                        </div>
                    </template>
                    <template v-else>
                        <p class="text-gray-500 italic">- Không có -</p>
                    </template>
                </div>

                <div class="flex justify-end mt-4">
                    <button @click="closeModal" class="text-[#b91c1c] font-bold text-base uppercase tracking-wider hover:underline transition">
                        [ ĐÓNG ]
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>