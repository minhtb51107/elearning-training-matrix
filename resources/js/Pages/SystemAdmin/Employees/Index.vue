<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { PrinterIcon, DocumentArrowDownIcon, IdentificationIcon, AcademicCapIcon, BriefcaseIcon, XMarkIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    employees: Object,
    departments: Array, 
    filters: Object
});

const searchKeyword = ref(props.filters?.keyword || '');
const filterDept = ref(props.filters?.department_id || 'all');
const filterPosition = ref(props.filters?.position || 'all');
const filterStatus = ref(props.filters?.status || 'all');

const doSearch = () => {
    router.get(route('system.employees.index'), { 
        keyword: searchKeyword.value,
        department_id: filterDept.value,
        position: filterPosition.value,
        status: filterStatus.value
    }, { preserveState: true, replace: true });
};

// QUẢN LÝ MODAL (HỒ SƠ NHÂN VIÊN)
const selectedEmployee = ref(null);
const showModal = ref(false);

const openModal = (emp) => {
    selectedEmployee.value = {
        id: emp.id,
        code: `NV-${String(emp.id).padStart(3, '0')}`,
        name: emp.name,
        email: emp.email,
        department: emp.department ? emp.department.name : 'Chưa phân bổ',
        position: 'Nhân viên', 
        
        // Tự động phân loại trạng thái: Đã có bằng / Đang học
        status: emp.overview?.completed > 0 ? 'Đã có chứng chỉ' : 'Đang đào tạo', 
        
        // Hứng dữ liệu thật từ Backend trả về
        overview: emp.overview || { learning: 0, completed: 0 },
        activeClasses: emp.activeClasses || [],
        history: emp.history || [],
        
        initial: emp.name.charAt(0).toUpperCase()
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
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 p-6 sm:p-8">
                    
                    <div class="flex justify-between items-start mb-8 border-b border-gray-100 pb-5">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Danh sách nhân sự toàn công ty</h2>
                            <p class="text-sm text-gray-500 mt-1">Quản lý thông tin và tiến độ đào tạo của tất cả nhân viên.</p>
                        </div>
                        
                        <div class="flex gap-2">
                            <button class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm">
                                <PrinterIcon class="w-4 h-4 text-gray-500" /> In
                            </button>
                            <button class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-50 border border-green-200 rounded-lg text-sm font-medium text-green-700 hover:bg-green-100 transition shadow-sm">
                                <DocumentArrowDownIcon class="w-4 h-4" /> Excel
                            </button>
                        </div>
                    </div>

                    <div class="mb-8 bg-gray-50/80 p-5 rounded-xl border border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Phòng ban</label>
                                <select v-model="filterDept" @change="doSearch" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm text-gray-700">
                                    <option value="all">Tất cả phòng ban</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Vị trí</label>
                                <select v-model="filterPosition" @change="doSearch" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm text-gray-700">
                                    <option value="all">Tất cả vị trí</option>
                                    <option value="staff">Nhân viên</option>
                                    <option value="leader">Trưởng nhóm / Quản lý</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Trạng thái đào tạo</label>
                                <select v-model="filterStatus" @change="doSearch" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm text-gray-700">
                                    <option value="all">Tất cả trạng thái</option>
                                    <option value="passed">Đạt yêu cầu chung</option>
                                    <option value="failed">Chưa đạt yêu cầu</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Tìm kiếm (Tên/Email)</label>
                                <input v-model="searchKeyword" @keyup.enter="doSearch" type="text" placeholder="Nhập từ khóa và Enter..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm" />
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Mã NV</th>
                                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Họ và tên</th>
                                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Email liên hệ</th>
                                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Phòng ban</th>
                                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Vị trí</th>
                                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="employees.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 bg-gray-50/30">
                                        Không tìm thấy nhân viên nào phù hợp với bộ lọc.
                                    </td>
                                </tr>
                                <tr v-for="emp in employees.data" :key="emp.id" class="hover:bg-blue-50/50 transition-colors cursor-pointer" @click="openModal(emp)">
                                    <td class="px-5 py-4 font-medium text-gray-500 text-xs">NV-{{ String(emp.id).padStart(3, '0') }}</td>
                                    <td class="px-5 py-4 font-semibold text-gray-900 group-hover:text-blue-600">{{ emp.name }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ emp.email }}</td>
                                    <td class="px-5 py-4 text-gray-600 font-medium">{{ emp.department ? emp.department.name : '--' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                            Nhân viên
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <button @click.stop="openModal(emp)" class="text-blue-600 hover:text-blue-800 hover:underline text-[13px] font-medium transition-colors">
                                            Hồ sơ Đào tạo
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-between items-center text-sm text-gray-600" v-if="employees.links.length > 3">
                        <div>Hiển thị trang hiện tại</div>
                        <div class="flex gap-1">
                            <Component v-for="(link, index) in employees.links" :key="index"
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

        <Modal :show="showModal" @close="closeModal" maxWidth="xl">
            <div class="bg-white rounded-xl overflow-hidden shadow-2xl" v-if="selectedEmployee">
                
                <div class="px-6 py-5 bg-gradient-to-r from-blue-50 to-white border-b border-gray-100 flex justify-between items-start">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-full bg-blue-600 text-white flex items-center justify-center text-2xl font-bold shadow-md ring-4 ring-white">
                            {{ selectedEmployee.initial }}
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ selectedEmployee.name }}</h3>
                            <p class="text-sm font-medium text-blue-600">{{ selectedEmployee.code }}</p>
                        </div>
                    </div>
                    <button @click="closeModal" class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>
                
                <div class="p-6 h-[70vh] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <BriefcaseIcon class="w-5 h-5 text-gray-400" />
                            <div>
                                <p class="text-xs text-gray-500">Phòng ban</p>
                                <p class="font-semibold text-gray-900 text-sm">{{ selectedEmployee.department }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <IdentificationIcon class="w-5 h-5 text-gray-400" />
                            <div>
                                <p class="text-xs text-gray-500">Trạng thái</p>
                                <p class="font-semibold text-gray-900 text-sm truncate max-w-[150px]" :class="selectedEmployee.overview.completed > 0 ? 'text-green-600' : 'text-blue-600'">{{ selectedEmployee.status }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Tổng quan Đào tạo</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-yellow-50 rounded-xl border border-yellow-100 flex justify-between items-center shadow-sm">
                                <div>
                                    <p class="text-xs font-medium text-yellow-800 uppercase mb-1">Đang theo học</p>
                                    <p class="text-2xl font-black text-yellow-600">{{ selectedEmployee.overview.learning }} <span class="text-sm font-medium text-yellow-700">lớp</span></p>
                                </div>
                                <AcademicCapIcon class="w-10 h-10 text-yellow-200" />
                            </div>
                            <div class="p-4 bg-green-50 rounded-xl border border-green-100 flex justify-between items-center shadow-sm">
                                <div>
                                    <p class="text-xs font-medium text-green-800 uppercase mb-1">Đã hoàn thành</p>
                                    <p class="text-2xl font-black text-green-600">{{ selectedEmployee.overview.completed }} <span class="text-sm font-medium text-green-700">lớp</span></p>
                                </div>
                                <AcademicCapIcon class="w-10 h-10 text-green-200" />
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Lớp học đang tham gia</h4>
                        <div v-if="selectedEmployee.activeClasses.length === 0" class="bg-gray-50 border border-gray-100 rounded-lg p-6 text-center text-sm text-gray-500 italic">
                            Chưa có dữ liệu lớp học nào đang diễn ra.
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="cls in selectedEmployee.activeClasses" :key="cls.id" class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:border-blue-200 transition-colors">
                                <p class="text-sm font-bold text-gray-900 mb-1">{{ cls.course_name }}</p>
                                <p class="text-xs font-medium text-gray-500 mb-3">Tên lớp: {{ cls.class_name }}</p>
                                <div class="flex items-center gap-3">
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="bg-blue-500 h-2 rounded-full" :style="{ width: cls.progress + '%' }"></div>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700">{{ cls.progress }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Lịch sử Đào tạo</h4>
                        <div v-if="selectedEmployee.history.length === 0" class="bg-gray-50 border border-gray-100 rounded-lg p-6 text-center text-sm text-gray-500 italic">
                            Chưa có dữ liệu lịch sử hoàn thành khóa học.
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="his in selectedEmployee.history" :key="his.id" class="bg-green-50/50 border border-green-100 rounded-lg p-4 shadow-sm flex justify-between items-center hover:bg-green-50 transition-colors">
                                <div>
                                    <p class="text-sm font-bold text-green-900 mb-1">{{ his.course_name }}</p>
                                    <p class="text-xs font-medium text-green-700">Tên lớp: {{ his.class_name }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-2 py-0.5 bg-green-200 text-green-800 text-[10px] font-bold rounded uppercase tracking-wider mb-1">Pass</span>
                                    <p class="text-xs font-bold text-green-600">{{ his.date }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                
                <div class="flex justify-end p-4 border-t border-gray-100 bg-gray-50">
                    <button @click="closeModal" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-100 rounded-lg font-semibold transition-colors shadow-sm">
                        Đóng hồ sơ
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>