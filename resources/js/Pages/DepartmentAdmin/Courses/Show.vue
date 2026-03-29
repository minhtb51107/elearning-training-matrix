<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon, InformationCircleIcon, AcademicCapIcon, DocumentTextIcon, LinkIcon, ArrowDownTrayIcon, UsersIcon } from '@heroicons/vue/20/solid';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import axios from 'axios';

// Hứng dữ liệu thật từ Controller truyền sang
const props = defineProps({
    courseInfo: Object,
    classes: Array,
    materials: Array
});

// === LOGIC CHO MODAL CHỈ ĐỊNH NHÂN VIÊN ===
const showingAssignModal = ref(false);
const eligibleEmployees = ref([]);
const isLoadingEmployees = ref(false);
const selectedClass = ref(null);

const assignForm = useForm({
    employee_ids: [],
    deadline: ''
});

const openAssignModal = async (cls) => {
    selectedClass.value = cls;
    showingAssignModal.value = true;
    isLoadingEmployees.value = true;
    assignForm.reset();
    
    try {
        // Gọi API lấy nhân viên chưa đăng ký lớp này
        const response = await axios.get(route('department.classes.eligible-employees', cls.id));
        eligibleEmployees.value = response.data;
    } catch (error) {
        console.error("Lỗi khi tải danh sách nhân viên:", error);
        alert("Không thể tải danh sách nhân viên. Vui lòng thử lại.");
    } finally {
        isLoadingEmployees.value = false;
    }
};

const closeAssignModal = () => {
    showingAssignModal.value = false;
    assignForm.reset();
    eligibleEmployees.value = [];
    selectedClass.value = null;
};

const submitAssignment = () => {
    assignForm.post(route('department.classes.assign-employees', selectedClass.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeAssignModal();
            // Optional: Reload lại trang để cập nhật số lượng Đã ĐK
        }
    });
};
</script>

<template>
    <Head :title="'Chi tiết khóa học: ' + courseInfo.name" />

    <AuthenticatedLayout>
        <template #header>
            <Link :href="route('department.courses.index')" class="flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                <ArrowLeftIcon class="w-4 h-4" />
                Quay lại danh sách Khóa học
            </Link>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">
                    <span class="font-medium">{{ $page.props.flash.success }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                    
                    <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 shadow-sm border border-blue-200 shrink-0">
                                <AcademicCapIcon class="w-8 h-8" />
                            </div>
                            <div class="flex-1 mt-1">
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-md text-xs font-bold border border-blue-200">{{ courseInfo.code }}</span>
                                    <span class="bg-purple-100 text-purple-700 px-2.5 py-0.5 rounded-md text-xs font-bold border border-purple-200">{{ courseInfo.format }}</span>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-900">{{ courseInfo.name }}</h2>
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        
                        <div class="mb-10">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-gray-100 pb-2">
                                <InformationCircleIcon class="w-4 h-4 text-blue-500" />
                                Thông tin triển khai
                            </h3>
                            
                            <div class="bg-gray-50/50 border border-gray-100 p-6 rounded-xl">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-y-6 gap-x-8 text-sm">
                                    <div>
                                        <p class="text-gray-500 font-medium mb-1">Mã khóa học</p>
                                        <p class="font-bold text-gray-900">{{ courseInfo.code }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 font-medium mb-1">Hình thức</p>
                                        <p class="font-bold text-gray-900">{{ courseInfo.format }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 font-medium mb-1">Thời lượng dự kiến</p>
                                        <p class="font-bold text-gray-900">{{ courseInfo.duration }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 font-medium mb-1">Phạm vi đào tạo</p>
                                        <p class="font-bold text-gray-900">{{ courseInfo.scope }}</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <p class="text-gray-500 font-medium mb-1">Đối tượng tham gia</p>
                                        <p class="font-bold text-gray-900">{{ courseInfo.target_audience }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            
                            <div class="lg:col-span-2">
                                <div class="flex justify-between items-end mb-4 border-b border-gray-100 pb-2">
                                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Danh sách lớp học</h3>
                                </div>
                                
                                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Mã lớp</th>
                                                <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Tên lớp</th>
                                                <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Giảng viên</th>
                                                <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Đã ĐK</th>
                                                <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-if="classes.length === 0">
                                                <td colspan="5" class="px-5 py-8 text-center text-gray-500 italic bg-gray-50/30">
                                                    Chưa có lớp học nào được tạo cho khóa học này.
                                                </td>
                                            </tr>
                                            <tr v-for="(cls, index) in classes" :key="index" class="hover:bg-blue-50/50 transition-colors duration-150">
                                                <td class="px-5 py-3 font-semibold text-gray-900">{{ cls.code }}</td>
                                                <td class="px-5 py-3 font-medium text-gray-800">{{ cls.name }}</td>
                                                <td class="px-5 py-3 text-gray-600">{{ cls.instructor }}</td>
                                                <td class="px-5 py-3 text-center">
                                                    <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-1 rounded-md font-bold text-xs border border-blue-100">
                                                        <UsersIcon class="w-3.5 h-3.5" /> {{ cls.capacity }}
                                                    </span>
                                                </td>
                                                <td class="px-5 py-3 text-center">
                                                    <button @click="openAssignModal(cls)" class="text-xs font-bold bg-indigo-600 text-white px-3 py-1.5 rounded hover:bg-indigo-700 transition-colors">
                                                        Chỉ định
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Tài liệu khóa học</h3>
                                
                                <div class="space-y-3">
                                    <div v-if="materials.length === 0" class="text-sm text-gray-400 italic text-center p-6 bg-gray-50/80 rounded-xl border border-gray-100 border-dashed">
                                        Chưa có tài liệu đính kèm.
                                    </div>
                                    
                                    <a v-for="(mat, index) in materials" :key="index" 
                                       :href="mat.url" 
                                       target="_blank" 
                                       class="group block bg-white p-3.5 rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all">
                                        <div class="flex items-start gap-3">
                                            <div class="p-2 bg-blue-50 text-blue-600 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-colors shadow-sm">
                                                <DocumentTextIcon v-if="mat.type === 'file'" class="w-5 h-5" />
                                                <LinkIcon v-else class="w-5 h-5" />
                                            </div>
                                            <div class="flex-1 min-w-0 pt-0.5">
                                                <p class="text-sm font-bold text-gray-900 truncate group-hover:text-blue-700 transition-colors">{{ mat.name }}</p>
                                                <p class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                    <ArrowDownTrayIcon v-if="mat.type === 'file'" class="w-3 h-3" />
                                                    {{ mat.type === 'file' ? 'Tải xuống File' : 'Nhấp để mở Link' }}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showingAssignModal" @close="closeAssignModal" maxWidth="lg">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-2">
                    Chỉ định học viên: <span class="text-blue-600">{{ selectedClass?.name }}</span>
                </h2>
                <p class="text-sm text-gray-500 mb-4">Chọn những nhân sự trong phòng ban bắt buộc phải hoàn thành khóa học này.</p>
                
                <div v-if="isLoadingEmployees" class="text-center py-8 text-gray-500 italic">
                    Đang tải danh sách nhân sự...
                </div>

                <div v-else>
                    <div class="mb-4 border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 flex justify-between">
                            <span>Danh sách nhân viên (Chưa học)</span>
                            <span>{{ assignForm.employee_ids.length }} / {{ eligibleEmployees.length }} Đã chọn</span>
                        </div>
                        <div class="max-h-60 overflow-y-auto p-2">
                            <div v-if="eligibleEmployees.length === 0" class="p-4 text-center text-sm text-gray-500 italic">
                                Tất cả nhân viên trong phòng ban của bạn đã tham gia khóa này.
                            </div>
                            
                            <label v-for="emp in eligibleEmployees" :key="emp.id" class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded cursor-pointer transition-colors">
                                <input type="checkbox" :value="emp.id" v-model="assignForm.employee_ids" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                                <div>
                                    <div class="text-sm font-bold text-gray-900">{{ emp.name }}</div>
                                    <div class="text-xs text-gray-500">{{ emp.email }}</div>
                                </div>
                            </label>
                        </div>
                        <div v-if="assignForm.errors.employee_ids" class="text-red-500 text-xs px-4 pb-2">{{ assignForm.errors.employee_ids }}</div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Hạn chót hoàn thành (Deadline)</label>
                        <input type="date" v-model="assignForm.deadline" :min="new Date().toISOString().split('T')[0]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                        <p class="text-xs text-gray-500 mt-1">Bỏ trống nếu không giới hạn thời gian.</p>
                        <div v-if="assignForm.errors.deadline" class="text-red-500 text-xs mt-1">{{ assignForm.errors.deadline }}</div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeAssignModal">Hủy bỏ</SecondaryButton>
                        <PrimaryButton @click="submitAssignment" :class="{ 'opacity-50': assignForm.employee_ids.length === 0 || assignForm.processing }" :disabled="assignForm.employee_ids.length === 0 || assignForm.processing">
                            Giao việc đào tạo
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>