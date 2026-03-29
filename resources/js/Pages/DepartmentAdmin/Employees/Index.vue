<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { MagnifyingGlassIcon, FunnelIcon, PrinterIcon, DocumentArrowDownIcon, XMarkIcon, UserCircleIcon, BookOpenIcon, CheckBadgeIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    employees: Object,
    filters: Object
});

const page = usePage();
const currentDepartment = page.props.auth.user.department?.name || 'Chưa xác định';

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
        // Cập nhật lấy dữ liệu thực tế
        position: emp.job_title || 'Chưa cập nhật', 
        is_manager: emp.is_manager,
        join_date: emp.join_date,

        status: emp.overview?.completed > 0 ? 'Đã có chứng chỉ' : 'Đang đào tạo', 
        progress: '-',
        email: emp.email, 
        department: emp.department?.name || currentDepartment,
        
        overview: emp.overview || { learning: 0, completed: 0 },
        activeClasses: emp.activeClasses || [],
        history: emp.history || []
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(() => selectedEmployee.value = null, 200);
};

const getAvatar = (name) => {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&color=1d4ed8&background=eff6ff`;
};
</script>

<template>
    <Head title="Danh sách nhân viên" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 p-6 sm:p-8">
                    
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-1">
                            <h2 class="text-xl font-bold text-gray-900">Danh sách nhân sự</h2>
                            <span class="bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-full text-xs font-semibold">{{ currentDepartment }}</span>
                        </div>
                        <p class="text-sm text-gray-500">Quản lý và theo dõi tiến độ đào tạo của các nhân sự trong bộ phận.</p>
                    </div>

                    <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-4 mb-6 bg-gray-50/50 p-4 rounded-xl border border-gray-100">
                        <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
                            
                            <div class="w-full sm:w-64 relative">
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Tìm kiếm nhân sự</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <MagnifyingGlassIcon class="h-4 w-4 text-gray-400" />
                                    </div>
                                    <input v-model="searchKeyword" @keyup.enter="doSearch" type="text" class="pl-9 w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm" placeholder="Nhập tên / Chức danh..." />
                                </div>
                            </div>

                            <div class="w-full sm:w-48 relative">
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Trạng thái học tập</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <FunnelIcon class="h-4 w-4 text-gray-400" />
                                    </div>
                                    <select v-model="filterStatus" @change="doSearch" class="pl-9 w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm appearance-none">
                                        <option value="all">Tất cả trạng thái</option>
                                        <option value="in_progress">Đang đào tạo</option>
                                        <option value="completed">Đã có chứng chỉ</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                                <DocumentArrowDownIcon class="w-4 h-4 text-green-600" /> Excel
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-24">Mã NV</th>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Nhân viên</th>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Vai trò / Chức danh</th>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Tình trạng đào tạo</th>
                                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Đang học</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="employees.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 bg-gray-50/30">
                                        <div class="flex flex-col items-center">
                                            <UserCircleIcon class="w-10 h-10 text-gray-300 mb-3" />
                                            <p>Chưa có nhân viên nào trong danh sách.</p>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr v-for="emp in employees.data" :key="emp.id" 
                                    class="hover:bg-blue-50/50 transition-colors duration-150 cursor-pointer" 
                                    @click="openModal(emp)">
                                    
                                    <td class="px-6 py-4 text-gray-500 text-xs font-medium">NV-{{ String(emp.id).padStart(3, '0') }}</td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <img :src="getAvatar(emp.name)" alt="Avatar" class="w-8 h-8 rounded-full shadow-sm border border-gray-100">
                                            <div>
                                                <div class="font-semibold text-gray-900">{{ emp.name }}</div>
                                                <div class="text-gray-500 text-[13px]">{{ emp.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col items-start gap-1">
                                            <span v-if="emp.is_manager" class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700 border border-purple-200 uppercase">
                                                Cấp Quản lý
                                            </span>
                                            <span v-else class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-200 uppercase">
                                                Nhân viên
                                            </span>
                                            <span class="text-sm font-medium text-gray-800">{{ emp.job_title || 'Chưa cập nhật' }}</span>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        <span v-if="emp.overview?.completed > 0" class="bg-green-50 text-green-700 px-2.5 py-1 rounded-full text-xs font-medium border border-green-200">Đã có chứng chỉ</span>
                                        <span v-else class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full text-xs font-medium border border-blue-200">Đang đánh giá</span>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        <span v-if="emp.overview?.learning > 0" class="text-blue-600 font-bold">{{ emp.overview.learning }} lớp</span>
                                        <span v-else class="text-gray-400 font-medium">-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="employees?.links?.length > 3" class="mt-6 flex justify-between items-center text-sm text-gray-600">
                        <div>Hiển thị trang hiện tại</div>
                        <div class="flex gap-1">
                            <Component v-for="link in employees.links" :key="link.label"
                                       :is="link.url ? 'a' : 'span'" 
                                       :href="link.url" 
                                       v-html="link.label" 
                                       class="px-3 py-1.5 border rounded-md transition-colors" 
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

        <Modal :show="showModal" @close="closeModal" maxWidth="2xl">
            <div v-if="selectedEmployee" class="bg-white rounded-xl overflow-hidden shadow-2xl relative">
                
                <button @click="closeModal" class="absolute top-4 right-4 p-2 text-white/70 hover:text-white hover:bg-white/20 rounded-full transition-colors z-10">
                    <XMarkIcon class="w-5 h-5" />
                </button>

                <div class="h-28 bg-gradient-to-r from-blue-700 to-indigo-600 relative"></div>
                
                <div class="px-8 pb-8 h-[75vh] overflow-y-auto">
                    <div class="relative -mt-12 mb-4 flex items-end justify-between">
                        <img :src="getAvatar(selectedEmployee.name)" alt="Avatar" class="w-24 h-24 rounded-full border-4 border-white shadow-md bg-white">
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold border border-green-200 mb-2">Đang hoạt động</span>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ selectedEmployee.name }}</h3>
                        <p class="text-gray-500 text-sm flex items-center gap-2">
                            <span>{{ selectedEmployee.code }}</span> • <span>{{ selectedEmployee.email }}</span>
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 mb-8">
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Thông tin công việc</h4>
                            <div class="bg-gray-50 border border-gray-100 rounded-lg p-4 space-y-3 text-sm">
                                <div>
                                    <p class="text-gray-500 mb-0.5 text-xs">Cấp bậc / Vai trò</p>
                                    <p class="font-bold text-gray-900" :class="selectedEmployee.is_manager ? 'text-purple-700' : ''">
                                        {{ selectedEmployee.is_manager ? 'Quản lý cấp trung' : 'Nhân viên' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-500 mb-0.5 text-xs">Chức danh chi tiết</p>
                                    <p class="font-bold text-gray-900">{{ selectedEmployee.position }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 mb-0.5 text-xs">Ngày gia nhập (Onboard)</p>
                                    <p class="font-bold text-gray-900">{{ selectedEmployee.join_date ? new Date(selectedEmployee.join_date).toLocaleDateString('vi-VN') : 'Chưa cập nhật' }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Tiến độ tổng quan</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-blue-50/50 border border-blue-100 p-4 rounded-xl flex flex-col items-center justify-center text-center">
                                    <BookOpenIcon class="w-6 h-6 text-blue-500 mb-2" />
                                    <span class="text-2xl font-black text-blue-700 leading-none">{{ selectedEmployee.overview.learning }}</span>
                                    <span class="text-xs font-medium text-blue-600 mt-1">Lớp đang học</span>
                                </div>
                                <div class="bg-green-50/50 border border-green-100 p-4 rounded-xl flex flex-col items-center justify-center text-center">
                                    <CheckBadgeIcon class="w-6 h-6 text-green-500 mb-2" />
                                    <span class="text-2xl font-black text-green-700 leading-none">{{ selectedEmployee.overview.completed }}</span>
                                    <span class="text-xs font-medium text-green-600 mt-1">Hoàn thành</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Khóa đào tạo đang tham gia</h4>
                        <div v-if="selectedEmployee.activeClasses.length === 0" class="bg-gray-50 border border-gray-100 rounded-lg p-6 text-center text-sm text-gray-500 italic">
                            Nhân viên này chưa tham gia lớp học nào.
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="cls in selectedEmployee.activeClasses" :key="cls.id" class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
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

                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>