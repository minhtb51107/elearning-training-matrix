<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    employees: Object,
    filters: Object
});

const page = usePage();
const currentDepartment = page.props.auth.user.department?.name || 'Chưa xác định';

// Gắn biến cho bộ lọc
const searchKeyword = ref(props.filters?.keyword || '');
const filterStatus = ref(props.filters?.status || 'all');

const doSearch = () => {
    router.get(route('department.employees.index'), { 
        keyword: searchKeyword.value,
        status: filterStatus.value
    }, { preserveState: true, replace: true });
};

// QUẢN LÝ MODAL (POPUP)
const selectedEmployee = ref(null);
const showModal = ref(false);

const openModal = (emp) => {
    selectedEmployee.value = {
        id: emp.id,
        code: `NV-${String(emp.id).padStart(3, '0')}`,
        name: emp.name,
        position: 'Nhân viên', 
        status: 'Đang đánh giá', 
        progress: '-',
        email: emp.email, 
        department: emp.department?.name || currentDepartment,
        overview: { learning: 0, completed: 0 },
        activeClasses: []
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
                    
                    <h2 class="text-xl font-bold text-gray-800 border-b pb-4">Danh sách nhân viên</h2>
                    <h3 class="font-bold text-gray-800 mt-4 mb-6 uppercase">Phòng ban: {{ currentDepartment }}</h3>

                    <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                        <div class="flex items-center gap-6">
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600 font-medium">Bộ lọc:</label>
                                <label class="text-sm text-gray-600 ml-2">Trạng thái học:</label>
                                <select v-model="filterStatus" @change="doSearch" class="border-gray-400 rounded-md text-sm py-1.5 focus:ring-blue-500 focus:border-blue-500 w-32">
                                    <option value="all">Tất cả</option>
                                    <option value="in_progress">Đang thực hiện</option>
                                    <option value="completed">Hoàn thành</option>
                                    <option value="failed">Chưa hoàn thành</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600 ml-4">Từ khóa (Enter để tìm):</label>
                                <input v-model="searchKeyword" @keyup.enter="doSearch" type="text" class="border-gray-400 rounded-md text-sm py-1.5 focus:ring-blue-500 focus:border-blue-500 w-48" placeholder="Nhập tên / Email" />
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
                        <table class="min-w-full divide-y divide-gray-400 text-left text-sm text-center">
                            <thead class="bg-[#fcd38e]">
                                <tr>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-1/6">Mã NV</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-1/5 text-left">Họ tên</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-1/6">Vị trí</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-1/4">Tình trạng đào tạo</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 w-1/6">Tiến độ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-400">
                                <tr v-if="employees.data.length === 0">
                                    <td colspan="5" class="px-4 py-6 text-gray-500 italic text-center">Phòng ban này chưa có nhân viên nào phù hợp.</td>
                                </tr>
                                
                                <tr v-for="emp in employees.data" :key="emp.id" class="hover:bg-gray-50 transition cursor-pointer" @click="openModal(emp)">
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800">NV-{{ String(emp.id).padStart(3, '0') }}</td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800 text-left font-medium">{{ emp.name }}</td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800">Nhân viên</td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800">Đang đánh giá</td>
                                    <td class="px-4 py-3 text-gray-800">-</td>
                                </tr>
                                
                                <tr v-for="i in Math.max(0, 3 - employees.data.length)" :key="'empty-'+i">
                                    <td class="px-4 py-5 border-r border-gray-400"></td>
                                    <td class="px-4 py-5 border-r border-gray-400"></td>
                                    <td class="px-4 py-5 border-r border-gray-400"></td>
                                    <td class="px-4 py-5 border-r border-gray-400"></td>
                                    <td class="px-4 py-5"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-center items-center gap-2 text-sm text-[#0ea5e9] font-bold" v-if="employees.links.length > 3">
                        <Component v-for="link in employees.links" :key="link.label"
                                   :is="link.url ? 'a' : 'span'" 
                                   :href="link.url" 
                                   v-html="link.label" 
                                   class="px-3 py-1 border border-gray-300 rounded text-sm transition" 
                                   :class="{
                                       'bg-[#0ea5e9] text-white font-bold': link.active, 
                                       'text-gray-400 cursor-not-allowed font-normal': !link.url,
                                       'hover:bg-gray-100 font-normal': link.url && !link.active
                                   }" />
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
                        <p class="text-gray-500 italic">- Đang cập nhật -</p>
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