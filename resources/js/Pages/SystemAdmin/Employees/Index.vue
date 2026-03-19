<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

// MOCK DATA TỪ THIẾT KẾ
const employees = ref([
    { 
        id: 1, code: 'NV-2021-0045', name: 'Nguyễn Văn An', department: 'Kinh doanh', position: 'Nhân viên', status: 'Đạt yêu cầu',
        email: 'a@company.com', overview: { learning: 2, completed: 3, date: '22/01/2026' },
        activeClasses: ['Kỹ năng bán hàng nâng cao - L1', 'Giao tiếp khách hàng DN - L2'],
        history: [
            { course: 'Sales cơ bản', result: 'Đạt', date: '22/01/2026' },
            { course: 'Quy trình nội bộ', result: 'Đạt', date: '10/12/2025' }
        ]
    },
    { 
        id: 2, code: 'NV-2019-0123', name: 'Trần Thị Bích', department: 'Nhân sự', position: 'Trưởng nhóm', status: 'Đạt yêu cầu',
        email: 'b@company.com', overview: { learning: 1, completed: 5, date: '15/12/2025' },
        activeClasses: ['Tuyển dụng hiệu quả - L1'],
        history: [
            { course: 'Kỹ năng phỏng vấn', result: 'Đạt', date: '15/12/2025' }
        ]
    },
    { 
        id: 3, code: 'NV-2023-0078', name: 'Lê Hoàng Minh', department: 'Thiết kế', position: 'Nhân viên', status: 'Chưa đạt yêu cầu',
        email: 'minh@company.com', overview: { learning: 1, completed: 0, date: '-' },
        activeClasses: ['Tư duy thiết kế UI/UX'],
        history: []
    },
    { 
        id: 4, code: 'NV-2020-0091', name: 'Phạm Quốc Huy', department: 'IT', position: 'Trưởng nhóm', status: 'Chưa đạt yêu cầu',
        email: 'huy@company.com', overview: { learning: 0, completed: 1, date: '-' },
        activeClasses: [],
        history: [
            { course: 'Bảo mật thông tin', result: 'Chưa đạt', date: '16/03/2026' }
        ]
    },
]);

// QUẢN LÝ MODAL (POPUP)
const selectedEmployee = ref(null);
const showModal = ref(false);

const openModal = (emp) => {
    selectedEmployee.value = emp;
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
                    
                    <h2 class="text-xl font-bold text-gray-800 border-b pb-4 mb-8">Danh sách nhân viên theo tình trạng đào tạo</h2>

                    <div class="mb-8">
                        <label class="block text-base font-bold text-gray-800 mb-4">Bộ lọc:</label>
                        <div class="grid grid-cols-4 gap-6">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Phòng ban:</label>
                                <select class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 text-gray-600">
                                    <option>Tất cả</option>
                                    <option>Kinh doanh</option>
                                    <option>Nhân sự</option>
                                    <option>Thiết kế</option>
                                    <option>IT</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Vị trí:</label>
                                <select class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 text-gray-600">
                                    <option>Tất cả</option>
                                    <option>Nhân viên</option>
                                    <option>Trưởng nhóm</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Trạng thái đào tạo:</label>
                                <select class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 text-gray-600">
                                    <option>Tất cả</option>
                                    <option>Đạt yêu cầu</option>
                                    <option>Chưa đạt yêu cầu</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Từ khóa:</label>
                                <input type="text" placeholder="Nhập tên / Mã NV" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 italic placeholder-gray-400" />
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
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300">Phòng ban</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300">Vị trí</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300">Tình trạng đào tạo</th>
                                    <th class="px-4 py-3 font-bold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-300">
                                <tr v-for="emp in employees" :key="emp.id" class="hover:bg-gray-50 transition cursor-pointer" @click="openModal(emp)">
                                    <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ emp.code }}</td>
                                    <td class="px-4 py-3 border-r border-gray-300 text-gray-800 text-left font-medium">{{ emp.name }}</td>
                                    <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ emp.department }}</td>
                                    <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ emp.position }}</td>
                                    <td class="px-4 py-3 border-r border-gray-300 font-bold" :class="emp.status === 'Đạt yêu cầu' ? 'text-[#16a34a]' : 'text-gray-800'">{{ emp.status }}</td>
                                    <td class="px-4 py-3">
                                        <button @click.stop="openModal(emp)" class="text-[#0ea5e9] hover:underline font-bold text-[13px]">
                                            [ Xem chi tiết ]
                                        </button>
                                    </td>
                                </tr>
                                <tr v-for="i in 3" :key="'empty-'+i">
                                    <td class="px-4 py-5 border-r border-gray-300"></td>
                                    <td class="px-4 py-5 border-r border-gray-300"></td>
                                    <td class="px-4 py-5 border-r border-gray-300"></td>
                                    <td class="px-4 py-5 border-r border-gray-300"></td>
                                    <td class="px-4 py-5 border-r border-gray-300"></td>
                                    <td class="px-4 py-5"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-center items-center gap-4 text-sm text-[#0ea5e9] font-medium">
                        <button class="hover:underline">&lt; Prev</button>
                        <button class="w-7 h-7 rounded bg-blue-100 font-bold flex items-center justify-center">1</button>
                        <button class="w-7 h-7 rounded hover:bg-gray-100 flex items-center justify-center text-gray-600">2</button>
                        <button class="w-7 h-7 rounded hover:bg-gray-100 flex items-center justify-center text-gray-600">3</button>
                        <button class="hover:underline">Next &gt;</button>
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
                        <span>- Lớp đã hoàn thành:</span> <span class="font-bold">{{ selectedEmployee.overview.completed }}</span>
                        <span>- Tình trạng đào tạo:</span> <span class="font-bold" :class="selectedEmployee.status === 'Đạt yêu cầu' ? 'text-[#16a34a]' : 'text-gray-800'">{{ selectedEmployee.status }}</span>
                        <span v-if="selectedEmployee.status === 'Đạt yêu cầu'">- Ngày đạt:</span> <span v-if="selectedEmployee.status === 'Đạt yêu cầu'">{{ selectedEmployee.overview.date }}</span>
                    </div>
                </div>

                <div class="mb-6 text-[14px] text-gray-800 leading-relaxed">
                    <p class="font-bold uppercase mb-2">LỚP ĐANG THAM GIA</p>
                    <template v-if="selectedEmployee.activeClasses.length > 0">
                        <ul class="list-disc pl-5">
                            <li v-for="(cls, idx) in selectedEmployee.activeClasses" :key="idx">{{ cls }}</li>
                        </ul>
                    </template>
                    <template v-else>
                        <p class="text-gray-500 italic">- Không có -</p>
                    </template>
                </div>

                <div class="mb-8 text-[14px] text-gray-800 leading-relaxed">
                    <p class="font-bold uppercase mb-2">LỊCH SỬ ĐÀO TẠO</p>
                    <template v-if="selectedEmployee.history.length > 0">
                        <ul class="list-disc pl-5">
                            <li v-for="(hist, idx) in selectedEmployee.history" :key="idx">
                                {{ hist.course }} - {{ hist.result }} - {{ hist.date }}
                            </li>
                        </ul>
                    </template>
                    <template v-else>
                        <p class="text-gray-500 italic">- Không có -</p>
                    </template>
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